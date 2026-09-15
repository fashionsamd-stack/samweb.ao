<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\ItemPedidoModel;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Models\PrecoModel;
use CodeIgniter\RESTful\ResourceController;

class ItemPedidoController extends ResourceController
{
    protected $format = 'json';

    protected $itemPedidoModel;
    protected $pedidoModel;
    protected $produtoModel;
    protected $precoModel;

    public function __construct()
    {
        $this->itemPedidoModel = new ItemPedidoModel();
        $this->pedidoModel = new PedidoModel();
        $this->produtoModel = new ProdutoModel();
        $this->precoModel = new PrecoModel();
    }

    /**
     * GET /item-pedido
     * Lista todos os itens dos pedidos
     */
    public function index()
    {
        $itens = $this->itemPedidoModel
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => true,
            'dados' => $itens
        ]);
    }

    /**
     * GET /item-pedido/{id}
     * Mostra um item específico
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do item é obrigatório.'
            ]);
        }

        $item = $this->itemPedidoModel->find($id);

        if (!$item) {
            return $this->failNotFound(
                'Item do pedido não encontrado.'
            );
        }

        return $this->respond([
            'status' => true,
            'dados' => $item
        ]);
    }

    /**
     * POST /item-pedido
     * Adiciona um produto a um pedido
     */
    public function create()
    {
        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        // ---------------------------------------
        // 1. Validar pedido_id
        // ---------------------------------------

        if (empty($dados['pedido_id'])) {
            return $this->failValidationErrors([
                'pedido_id' => 'O pedido é obrigatório.'
            ]);
        }

        $pedido = $this->pedidoModel->find($dados['pedido_id']);

        if (!$pedido) {
            return $this->failNotFound(
                'Pedido não encontrado.'
            );
        }

        // Não permitir adicionar produtos a pedido cancelado
        if ($pedido['estado'] === 'Cancelado') {
            return $this->failValidationErrors([
                'pedido_id' => 'Não é possível adicionar produtos a um pedido cancelado.'
            ]);
        }

        // Não permitir alteração de pedido já pago
        if ($pedido['estado'] === 'Pago') {
            return $this->failValidationErrors([
                'pedido_id' => 'Não é possível adicionar produtos a um pedido já pago.'
            ]);
        }

        // ---------------------------------------
        // 2. Validar produto_id
        // ---------------------------------------

        if (empty($dados['produto_id'])) {
            return $this->failValidationErrors([
                'produto_id' => 'O produto é obrigatório.'
            ]);
        }

        $produto = $this->produtoModel->find($dados['produto_id']);

        if (!$produto) {
            return $this->failNotFound(
                'Produto não encontrado.'
            );
        }

        // ---------------------------------------
        // 3. Validar quantidade
        // ---------------------------------------

        if (
            !isset($dados['quantidade']) ||
            !is_numeric($dados['quantidade']) ||
            (int)$dados['quantidade'] <= 0
        ) {
            return $this->failValidationErrors([
                'quantidade' => 'A quantidade deve ser maior que zero.'
            ]);
        }

        $quantidade = (int)$dados['quantidade'];

        // ---------------------------------------
        // 4. Buscar o preço do produto
        // ---------------------------------------

        $preco = $this->precoModel
            ->where('produto_id', $produto['id'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$preco) {
            return $this->failValidationErrors([
                'produto_id' => 'Este produto não possui preço cadastrado.'
            ]);
        }

        $valorUnitario = (float)$preco['valor'];

        // ---------------------------------------
        // 5. Calcular subtotal
        // ---------------------------------------

        $subtotal = $quantidade * $valorUnitario;

        // ---------------------------------------
        // 6. Preparar item
        // ---------------------------------------

        $novoItem = [
            'pedido_id' => $pedido['id'],
            'produto_id' => $produto['id'],
            'quantidade' => $quantidade,
            'preco' => $valorUnitario,
            'subtotal' => $subtotal,
        ];

        // ---------------------------------------
        // 7. Gravar item
        // ---------------------------------------

        $id = $this->itemPedidoModel->insert($novoItem);

        if (!$id) {
            return $this->failServerError(
                'Não foi possível adicionar o item ao pedido.'
            );
        }

        // ---------------------------------------
        // 8. Recalcular total do pedido
        // ---------------------------------------

        $this->actualizarTotalPedido($pedido['id']);

        // Buscar novamente o item
        $item = $this->itemPedidoModel->find($id);

        // Buscar pedido actualizado
        $pedidoActualizado = $this->pedidoModel->find($pedido['id']);

        return $this->respondCreated([
            'status' => true,
            'mensagem' => 'Item adicionado ao pedido com sucesso.',
            'dados' => [
                'item' => $item,
                'pedido' => $pedidoActualizado
            ]
        ]);
    }

    /**
     * PUT /item-pedido/{id}
     * Actualiza a quantidade do item
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do item é obrigatório.'
            ]);
        }

        $item = $this->itemPedidoModel->find($id);

        if (!$item) {
            return $this->failNotFound(
                'Item do pedido não encontrado.'
            );
        }

        $pedido = $this->pedidoModel->find($item['pedido_id']);

        if (!$pedido) {
            return $this->failNotFound(
                'Pedido associado não encontrado.'
            );
        }

        if ($pedido['estado'] === 'Cancelado') {
            return $this->failValidationErrors([
                'pedido' => 'Não é possível alterar um pedido cancelado.'
            ]);
        }

        if ($pedido['estado'] === 'Pago') {
            return $this->failValidationErrors([
                'pedido' => 'Não é possível alterar um pedido já pago.'
            ]);
        }

        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        if (
            !isset($dados['quantidade']) ||
            !is_numeric($dados['quantidade']) ||
            (int)$dados['quantidade'] <= 0
        ) {
            return $this->failValidationErrors([
                'quantidade' => 'A quantidade deve ser maior que zero.'
            ]);
        }

        $quantidade = (int)$dados['quantidade'];

        // Manter o preço original do item
        $preco = (float)$item['preco'];

        $subtotal = $quantidade * $preco;

        $this->itemPedidoModel->update($id, [
            'quantidade' => $quantidade,
            'subtotal' => $subtotal,
        ]);

        // Recalcular total
        $this->actualizarTotalPedido($item['pedido_id']);

        $itemActualizado = $this->itemPedidoModel->find($id);

        $pedidoActualizado = $this->pedidoModel->find(
            $item['pedido_id']
        );

        return $this->respond([
            'status' => true,
            'mensagem' => 'Item actualizado com sucesso.',
            'dados' => [
                'item' => $itemActualizado,
                'pedido' => $pedidoActualizado
            ]
        ]);
    }

    /**
     * DELETE /item-pedido/{id}
     * Remove o item do pedido
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do item é obrigatório.'
            ]);
        }

        $item = $this->itemPedidoModel->find($id);

        if (!$item) {
            return $this->failNotFound(
                'Item do pedido não encontrado.'
            );
        }

        $pedido = $this->pedidoModel->find($item['pedido_id']);

        if (!$pedido) {
            return $this->failNotFound(
                'Pedido associado não encontrado.'
            );
        }

        if ($pedido['estado'] === 'Pago') {
            return $this->failValidationErrors([
                'pedido' => 'Não é possível remover itens de um pedido já pago.'
            ]);
        }

        if ($pedido['estado'] === 'Cancelado') {
            return $this->failValidationErrors([
                'pedido' => 'Não é possível alterar um pedido cancelado.'
            ]);
        }

        $pedidoId = $item['pedido_id'];

        $this->itemPedidoModel->delete($id);

        // Recalcular total do pedido
        $this->actualizarTotalPedido($pedidoId);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Item removido do pedido com sucesso.'
        ]);
    }

    /**
     * Recalcula o total do pedido
     */
    private function actualizarTotalPedido($pedidoId)
    {
        $itens = $this->itemPedidoModel
            ->where('pedido_id', $pedidoId)
            ->findAll();

        $total = 0;

        foreach ($itens as $item) {
            $total += (float)$item['subtotal'];
        }

        $this->pedidoModel->update($pedidoId, [
            'total' => $total
        ]);
    }
}
