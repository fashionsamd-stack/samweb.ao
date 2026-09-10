<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\PedidoModel;
use App\Models\ClienteModel;
use CodeIgniter\RESTful\ResourceController;

class PedidoController extends ResourceController
{
    protected $format = 'json';

    protected $pedidoModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->pedidoModel = new PedidoModel();
        $this->clienteModel = new ClienteModel();
    }

    /**
     * GET /pedido
     * Lista todos os pedidos activos
     */
    public function index()
    {
        $pedidos = $this->pedidoModel
            ->whereIn('estado', ['Pendente', 'Pago', 'Processando', 'Concluido'])
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => true,
            'dados' => $pedidos
        ]);
    }

    /**
     * GET /pedido/{id}
     * Mostra um pedido específico
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do pedido é obrigatório.'
            ]);
        }

        $pedido = $this->pedidoModel->find($id);

        if (!$pedido) {
            return $this->failNotFound('Pedido não encontrado.');
        }

        return $this->respond([
            'status' => true,
            'dados' => $pedido
        ]);
    }

    /**
     * POST /pedido
     * Cria um novo pedido
     */
    public function create()
    {
        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        // Validar cliente
        if (empty($dados['id_cliente'])) {
            return $this->failValidationErrors([
                'id_cliente' => 'O cliente é obrigatório.'
            ]);
        }

        // Verificar se o cliente existe
        $cliente = $this->clienteModel
            ->where('id', $dados['id_cliente'])
            ->where('estado', 1)
            ->first();

        if (!$cliente) {
            return $this->failValidationErrors([
                'id_cliente' => 'Cliente não encontrado ou está inactivo.'
            ]);
        }

        // Gerar número do pedido
        $numero = $this->gerarNumeroPedido();

        $novoPedido = [
            'id_cliente' => $dados['id_cliente'],
            'numero'     => $numero,
            'total'      => 0,
            'estado'     => 'Pendente',
        ];

        $id = $this->pedidoModel->insert($novoPedido);

        if (!$id) {
            return $this->failServerError(
                'Não foi possível criar o pedido.'
            );
        }

        $pedido = $this->pedidoModel->find($id);

        return $this->respondCreated([
            'status' => true,
            'mensagem' => 'Pedido criado com sucesso.',
            'dados' => $pedido
        ]);
    }

    /**
     * PUT /pedido/{id}
     * Actualiza um pedido
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do pedido é obrigatório.'
            ]);
        }

        $pedido = $this->pedidoModel->find($id);

        if (!$pedido) {
            return $this->failNotFound('Pedido não encontrado.');
        }

        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        /*
         * O total NÃO é actualizado aqui.
         * O ItemPedidoController será responsável
         * pelo cálculo do total.
         */

        $dadosActualizar = [];

        // Permitir alteração do estado
        if (isset($dados['estado'])) {

            $estadosPermitidos = [
                'Pendente',
                'Pago',
                'Processando',
                'Concluido',
                'Cancelado'
            ];

            if (!in_array($dados['estado'], $estadosPermitidos)) {
                return $this->failValidationErrors([
                    'estado' => 'Estado do pedido inválido.'
                ]);
            }

            $dadosActualizar['estado'] = $dados['estado'];
        }

        // Permitir alteração do cliente apenas se necessário
        if (isset($dados['id_cliente'])) {

            $cliente = $this->clienteModel
                ->where('id', $dados['id_cliente'])
                ->where('estado', 1)
                ->first();

            if (!$cliente) {
                return $this->failValidationErrors([
                    'id_cliente' => 'Cliente não encontrado ou está inactivo.'
                ]);
            }

            $dadosActualizar['id_cliente'] = $dados['id_cliente'];
        }

        if (empty($dadosActualizar)) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum campo válido foi enviado para actualizar.'
            ]);
        }

        $this->pedidoModel->update($id, $dadosActualizar);

        $pedidoActualizado = $this->pedidoModel->find($id);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Pedido actualizado com sucesso.',
            'dados' => $pedidoActualizado
        ]);
    }

    /**
     * DELETE /pedido/{id}
     * Cancela logicamente o pedido
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do pedido é obrigatório.'
            ]);
        }

        $pedido = $this->pedidoModel->find($id);

        if (!$pedido) {
            return $this->failNotFound('Pedido não encontrado.');
        }

        if ($pedido['estado'] === 'Pago') {
            return $this->failValidationErrors([
                'estado' => 'Um pedido pago não pode ser cancelado desta forma.'
            ]);
        }

        $this->pedidoModel->update($id, [
            'estado' => 'Cancelado'
        ]);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Pedido cancelado com sucesso.'
        ]);
    }

    /**
     * Gera número único do pedido
     */
    private function gerarNumeroPedido()
    {
        do {
            $numero = 'PED-' . date('YmdHis') . '-' . rand(100, 999);

            $existe = $this->pedidoModel
                ->where('numero', $numero)
                ->first();

        } while ($existe);

        return $numero;
    }
}
