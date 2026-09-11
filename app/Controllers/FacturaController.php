<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\FacturaModel;
use App\Models\PedidoModel;
use App\Models\ClienteModel;
use CodeIgniter\RESTful\ResourceController;

class FacturaController extends ResourceController
{
    protected $format = 'json';

    protected $facturaModel;
    protected $pedidoModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->facturaModel = new FacturaModel();
        $this->pedidoModel = new PedidoModel();
        $this->clienteModel = new ClienteModel();
    }

    /**
     * GET /factura
     */
    public function index()
    {
        $facturas = $this->facturaModel
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => true,
            'dados' => $facturas
        ]);
    }

    /**
     * GET /factura/{id}
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID da factura é obrigatório.'
            ]);
        }

        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            return $this->failNotFound(
                'Factura não encontrada.'
            );
        }

        return $this->respond([
            'status' => true,
            'dados' => $factura
        ]);
    }

    /**
     * POST /factura
     *
     * Cria uma factura a partir de um pedido.
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
        // 1. Validar pedido
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

        // ---------------------------------------
        // 2. Verificar cliente
        // ---------------------------------------

        $cliente = $this->clienteModel
            ->where('id', $pedido['cliente_id'])
            ->where('estado', 1)
            ->first();

        if (!$cliente) {
            return $this->failValidationErrors([
                'cliente_id' => 'O cliente do pedido não existe ou está inactivo.'
            ]);
        }

        // ---------------------------------------
        // 3. Verificar total do pedido
        // ---------------------------------------

        if ((float)$pedido['total'] <= 0) {
            return $this->failValidationErrors([
                'pedido_id' => 'Não é possível emitir factura para um pedido sem valor.'
            ]);
        }

        // ---------------------------------------
        // 4. Tipo da factura
        // ---------------------------------------

        if (empty($dados['tipo'])) {
            return $this->failValidationErrors([
                'tipo' => 'O tipo da factura é obrigatório.'
            ]);
        }

        $tiposPermitidos = [
            'FT - Factura',
            'FR - Factura-Recibo',
            'FP - Factura Proforma'
        ];

        if (!in_array($dados['tipo'], $tiposPermitidos)) {
            return $this->failValidationErrors([
                'tipo' => 'Tipo de factura inválido.'
            ]);
        }

        // ---------------------------------------
        // 5. Verificar se já existe factura
        // para este pedido
        // ---------------------------------------

        $facturaExistente = $this->facturaModel
            ->where('pedido_id', $pedido['id'])
            ->first();

        if ($facturaExistente) {
            return $this->failValidationErrors([
                'pedido_id' => 'Este pedido já possui uma factura.'
            ]);
        }

        // ---------------------------------------
        // 6. Gerar número
        // ---------------------------------------

        $numero = $this->gerarNumeroFactura();

        // ---------------------------------------
        // 7. Data de vencimento
        // ---------------------------------------

        $vencimento = $dados['vencimento'] ?? date(
            'Y-m-d',
            strtotime('+7 days')
        );

        // ---------------------------------------
        // 8. Criar factura
        // ---------------------------------------

        $novaFactura = [
            'cliente_id' => $pedido['cliente_id'],
            'pedido_id' => $pedido['id'],
            'tipo' => $dados['tipo'],
            'numero' => $numero,
            'total' => $pedido['total'],
            'vencimento' => $vencimento,
            'estado' => 'Emitida',
        ];

        $id = $this->facturaModel->insert($novaFactura);

        if (!$id) {
            return $this->failServerError(
                'Não foi possível criar a factura.'
            );
        }

        $factura = $this->facturaModel->find($id);

        return $this->respondCreated([
            'status' => true,
            'mensagem' => 'Factura criada com sucesso.',
            'dados' => $factura
        ]);
    }

    /**
     * PUT /factura/{id}
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID da factura é obrigatório.'
            ]);
        }

        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            return $this->failNotFound(
                'Factura não encontrada.'
            );
        }

        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        $dadosActualizar = [];

        // Alterar estado
        if (isset($dados['estado'])) {

            $estadosPermitidos = [
                'Emitida',
                'Paga',
                'Vencida',
                'Cancelada'
            ];

            if (!in_array($dados['estado'], $estadosPermitidos)) {
                return $this->failValidationErrors([
                    'estado' => 'Estado da factura inválido.'
                ]);
            }

            $dadosActualizar['estado'] = $dados['estado'];
        }

        // Alterar vencimento
        if (isset($dados['vencimento'])) {

            $data = \DateTime::createFromFormat(
                'Y-m-d',
                $dados['vencimento']
            );

            if (!$data || $data->format('Y-m-d') !== $dados['vencimento']) {
                return $this->failValidationErrors([
                    'vencimento' => 'Data de vencimento inválida.'
                ]);
            }

            $dadosActualizar['vencimento'] = $dados['vencimento'];
        }

        if (empty($dadosActualizar)) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum campo válido foi enviado.'
            ]);
        }

        $this->facturaModel->update(
            $id,
            $dadosActualizar
        );

        $facturaActualizada = $this->facturaModel->find($id);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Factura actualizada com sucesso.',
            'dados' => $facturaActualizada
        ]);
    }

    /**
     * DELETE /factura/{id}
     *
     * Cancelamento lógico.
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID da factura é obrigatório.'
            ]);
        }

        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            return $this->failNotFound(
                'Factura não encontrada.'
            );
        }

        if ($factura['estado'] === 'Paga') {
            return $this->failValidationErrors([
                'estado' => 'Uma factura paga não pode ser cancelada desta forma.'
            ]);
        }

        $this->facturaModel->update($id, [
            'estado' => 'Cancelada'
        ]);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Factura cancelada com sucesso.'
        ]);
    }

    /**
     * Gera número único da factura.
     */
    private function gerarNumeroFactura()
    {
        do {
            $numero = 'FT-' . date('YmdHis') . '-' . rand(100, 999);

            $existe = $this->facturaModel
                ->where('numero', $numero)
                ->first();

        } while ($existe);

        return $numero;
    }
}
