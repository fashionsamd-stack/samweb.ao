<?php

namespace App\Controllers;


use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Models\PrecoModel;
use App\Models\DominioModel;
use CodeIgniter\RESTful\ResourceController;

class ItemPedidoController extends ResourceController
{
    protected $modelName = 'App\Models\ItemPedidoModel';

    protected $format = 'json';


    /**
     * LISTAR TODOS OS ITENS
     */
    public function index()
    {
        $itens = $this->model->findAll();

        return $this->respond([
            'sucesso' => true,
            'dados' => $itens
        ]);
    }


    /**
     * MOSTRAR UM ITEM
     */
    public function show($id = null)
    {
        $item = $this->model->find($id);

        if (!$item) {
            return $this->failNotFound(
                'Item do pedido não encontrado.'
            );
        }

        return $this->respond([
            'sucesso' => true,
            'dados' => $item
        ]);
    }


    /**
     * CRIAR ITEM DO PEDIDO
     */
    public function create()
    {
        $dados = $this->request->getJSON(true);

        // ==============================
        // 1. VALIDAR CAMPOS OBRIGATÓRIOS
        // ==============================

        if (
            empty($dados['pedido_id']) ||
            empty($dados['produto_id']) ||
            empty($dados['dominio_id']) ||
            empty($dados['quantidade']) ||
            empty($dados['periodo'])
        ) {
            return $this->failValidationErrors(
                'pedido_id, produto_id, dominio_id, quantidade e periodo são obrigatórios.'
            );
        }


        // ==============================
        // 2. VALIDAR QUANTIDADE
        // ==============================

        if ($dados['quantidade'] <= 0) {
            return $this->failValidationErrors(
                'A quantidade deve ser maior que zero.'
            );
        }


        // ==============================
        // 3. MODELS
        // ==============================

        $pedidoModel = new PedidoModel();
        $produtoModel = new ProdutoModel();
        $precoModel = new PrecoModel();
        $dominioModel = new DominioModel();


        // ==============================
        // 4. VERIFICAR PEDIDO
        // ==============================

        $pedido = $pedidoModel->find($dados['pedido_id']);

        if (!$pedido) {
            return $this->failNotFound(
                'Pedido não encontrado.'
            );
        }


        // ==============================
        // 5. VERIFICAR PRODUTO
        // ==============================

        $produto = $produtoModel->find($dados['produto_id']);

        if (!$produto) {
            return $this->failNotFound(
                'Produto não encontrado.'
            );
        }

        // Produto activo
        if (isset($produto['estado']) && $produto['estado'] != 1) {
            return $this->failValidationErrors(
                'O produto seleccionado não está activo.'
            );
        }


        // ==============================
        // 6. VERIFICAR DOMÍNIO
        // ==============================

        $dominio = $dominioModel->find($dados['dominio_id']);

        if (!$dominio) {
            return $this->failNotFound(
                'Domínio não encontrado.'
            );
        }


        // ==============================
        // 7. VERIFICAR SE O DOMÍNIO
        //    PERTENCE AO CLIENTE DO PEDIDO
        // ==============================

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            return $this->failValidationErrors(
                'O domínio seleccionado não pertence ao cliente deste pedido.'
            );
        }


        // ==============================
        // 8. VERIFICAR DOMÍNIO ACTIVO
        // ==============================

        if (isset($dominio['estado']) && $dominio['estado'] != 1) {
            return $this->failValidationErrors(
                'O domínio seleccionado não está activo.'
            );
        }


        // ==============================
        // 9. BUSCAR PREÇO DO PRODUTO
        // ==============================

        $periodo = trim($dados['periodo']);

        $preco = $precoModel
            ->where('produto_id', $dados['produto_id'])
            ->where('periodo', $periodo)
            ->first();

        if (!$preco) {
            return $this->failValidationErrors(
                'Não existe preço definido para este produto no período seleccionado.'
            );
        }


        // ==============================
        // 10. CALCULAR SUBTOTAL
        // ==============================

        $valorUnitario = $preco['valor'];

        $subtotal = $valorUnitario * $dados['quantidade'];


        // ==============================
        // 11. CRIAR ITEM
        // ==============================

        $itemData = [
            'pedido_id' => $dados['pedido_id'],
            'produto_id' => $dados['produto_id'],
            'dominio_id' => $dados['dominio_id'],
            'quantidade' => $dados['quantidade'],
            'preco' => $valorUnitario,
            'periodo' => $preco['periodo'],
            'subtotal' => $subtotal
        ];


        // ==============================
        // 12. INSERIR ITEM
        // ==============================

        $itemId = $this->model->insert($itemData);

        if (!$itemId) {
            return $this->failServerError(
                'Não foi possível criar o item do pedido.'
            );
        }


        // ==============================
        // 13. RECALCULAR TOTAL DO PEDIDO
        // ==============================

        $totalPedido = $this->model
            ->where('pedido_id', $dados['pedido_id'])
            ->selectSum('subtotal')
            ->first();

        $total = $totalPedido['subtotal'] ?? 0;


        // ==============================
        // 14. ACTUALIZAR PEDIDO
        // ==============================

        $pedidoModel->update(
            $dados['pedido_id'],
            [
                'total' => $total
            ]
        );


        // ==============================
        // 15. RESPOSTA
        // ==============================

        $itemCriado = $this->model->find($itemId);

        return $this->respondCreated([
            'sucesso' => true,
            'mensagem' => 'Item do pedido criado com sucesso.',
            'dados' => $itemCriado,
            'total_pedido' => $total
        ]);
    }


    /**
     * ACTUALIZAR ITEM DO PEDIDO
     */
    public function update($id = null)
    {
        $item = $this->model->find($id);

        if (!$item) {
            return $this->failNotFound(
                'Item do pedido não encontrado.'
            );
        }

        $dados = $this->request->getJSON(true);


        // ==============================
        // MODELS
        // ==============================

        $pedidoModel = new PedidoModel();
        $produtoModel = new ProdutoModel();
        $precoModel = new PrecoModel();
        $dominioModel = new DominioModel();


        // ==============================
        // PEDIDO
        // ==============================

        $pedido = $pedidoModel->find($item['pedido_id']);

        if (!$pedido) {
            return $this->failNotFound(
                'Pedido associado não encontrado.'
            );
        }


        // ==============================
        // PRODUTO
        // ==============================

        $produtoId = $dados['produto_id'] ?? $item['produto_id'];

        $produto = $produtoModel->find($produtoId);

        if (!$produto) {
            return $this->failNotFound(
                'Produto não encontrado.'
            );
        }


        // ==============================
        // DOMÍNIO
        // ==============================

        $dominioId = $dados['dominio_id'] ?? $item['dominio_id'];

        $dominio = $dominioModel->find($dominioId);

        if (!$dominio) {
            return $this->failNotFound(
                'Domínio não encontrado.'
            );
        }


        // ==============================
        // DOMÍNIO PERTENCE AO CLIENTE
        // ==============================

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            return $this->failValidationErrors(
                'O domínio seleccionado não pertence ao cliente deste pedido.'
            );
        }


        // ==============================
        // QUANTIDADE
        // ==============================

        $quantidade = $dados['quantidade'] ?? $item['quantidade'];

        if ($quantidade <= 0) {
            return $this->failValidationErrors(
                'A quantidade deve ser maior que zero.'
            );
        }


        // ==============================
        // BUSCAR PREÇO ACTUAL
        // ==============================

        $periodo = trim($dados['periodo']);

        $preco = $precoModel
            ->where('produto_id', $dados['produto_id'])
            ->where('periodo', $periodo)
            ->first();

        if (!$preco) {
            return $this->failValidationErrors(
                'Não existe preço definido para este produto no período seleccionado.'
            );
        }


        // ==============================
        // CALCULAR NOVO SUBTOTAL
        // ==============================

        $valorUnitario = $preco['valor'];

        $subtotal = $valorUnitario * $quantidade;


        // ==============================
        // ACTUALIZAR ITEM
        // ==============================

        /*$this->model->update(
            $id,
            [
                'produto_id' => $produtoId,
                'dominio_id' => $dominioId,
                'quantidade' => $quantidade,
                'preco' => $valorUnitario,
                'subtotal' => $subtotal
            ]
        );*/
        $this->model->update(
            $id,
            [
                'produto_id' => $produtoId,
                'dominio_id' => $dominioId,
                'quantidade' => $quantidade,
                'preco' => $valorUnitario,
                'periodo' => $preco['periodo'],
                'subtotal' => $subtotal
            ]
        );


        // ==============================
        // RECALCULAR TOTAL DO PEDIDO
        // ==============================

        $totalPedido = $this->model
            ->where('pedido_id', $item['pedido_id'])
            ->selectSum('subtotal')
            ->first();

        $total = $totalPedido['subtotal'] ?? 0;


        // ==============================
        // ACTUALIZAR PEDIDO
        // ==============================

        $pedidoModel->update(
            $item['pedido_id'],
            [
                'total' => $total
            ]
        );


        return $this->respond([
            'sucesso' => true,
            'mensagem' => 'Item do pedido actualizado com sucesso.',
            'dados' => $this->model->find($id),
            'total_pedido' => $total
        ]);
    }


    /**
     * ELIMINAR ITEM DO PEDIDO
     */
    public function delete($id = null)
    {
        $item = $this->model->find($id);

        if (!$item) {
            return $this->failNotFound(
                'Item do pedido não encontrado.'
            );
        }


        // Guardar pedido antes de eliminar
        $pedidoId = $item['pedido_id'];


        // Eliminar item
        if (!$this->model->delete($id)) {
            return $this->failServerError(
                'Não foi possível eliminar o item do pedido.'
            );
        }


        // ==============================
        // RECALCULAR TOTAL DO PEDIDO
        // ==============================

        $pedidoModel = new PedidoModel();

        $totalPedido = $this->model
            ->where('pedido_id', $pedidoId)
            ->selectSum('subtotal')
            ->first();

        $total = $totalPedido['subtotal'] ?? 0;


        // ==============================
        // ACTUALIZAR PEDIDO
        // ==============================

        $pedidoModel->update(
            $pedidoId,
            [
                'total' => $total
            ]
        );


        return $this->respondDeleted([
            'sucesso' => true,
            'mensagem' => 'Item do pedido eliminado com sucesso.',
            'total_pedido' => $total
        ]);
    }
}
