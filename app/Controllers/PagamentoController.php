<?php

namespace App\Controllers;




use App\Models\PagamentoModel;
use App\Models\FacturaModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PedidoModel;
use App\Services\ProvisionamentoService;

class PagamentoController extends ResourceController
{
    protected $format = 'json';

    protected $pagamentoModel;
    protected $facturaModel;
    protected $pedidoModel;
    protected $provisionamentoService;


    public function __construct()
    {
        $this->pagamentoModel = new PagamentoModel();
        $this->facturaModel = new FacturaModel();
        $this->pedidoModel = new PedidoModel();
        $this->provisionamentoService = new ProvisionamentoService();
    }

    /**
     * GET /pagamento
     */
    public function index()
    {
        $pagamentos = $this->pagamentoModel
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => true,
            'dados' => $pagamentos
        ]);
    }

    /**
     * GET /pagamento/{id}
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do pagamento é obrigatório.'
            ]);
        }

        $pagamento = $this->pagamentoModel->find($id);

        if (!$pagamento) {
            return $this->failNotFound(
                'Pagamento não encontrado.'
            );
        }

        return $this->respond([
            'status' => true,
            'dados' => $pagamento
        ]);
    }

    /**
     * POST /pagamento
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
        // 1. Validar factura
        // ---------------------------------------

        if (empty($dados['factura_id'])) {
            return $this->failValidationErrors([
                'factura_id' => 'A factura é obrigatória.'
            ]);
        }

        $factura = $this->facturaModel->find($dados['factura_id']);

        if (!$factura) {
            return $this->failNotFound(
                'Factura não encontrada.'
            );
        }

        // ---------------------------------------
        // 2. Verificar estado da factura
        // ---------------------------------------

        if ($factura['estado'] === 'Cancelada') {
            return $this->failValidationErrors([
                'factura_id' => 'Não é possível efectuar pagamento de uma factura cancelada.'
            ]);
        }

        if ($factura['estado'] === 'Paga') {
            return $this->failValidationErrors([
                'factura_id' => 'Esta factura já está totalmente paga.'
            ]);
        }

        // ---------------------------------------
        // 3. Validar método
        // ---------------------------------------

        if (empty($dados['metodo'])) {
            return $this->failValidationErrors([
                'metodo' => 'O método de pagamento é obrigatório.'
            ]);
        }

        $metodosPermitidos = [
            'Multicaixa Express',
            'Multicaixa Referência',
            'Transferência Bancária',
            'Outro'
        ];

        if (!in_array($dados['metodo'], $metodosPermitidos)) {
            return $this->failValidationErrors([
                'metodo' => 'Método de pagamento inválido.'
            ]);
        }

        // ---------------------------------------
        // 4. Validar valor
        // ---------------------------------------

        if (
            !isset($dados['valor']) ||
            !is_numeric($dados['valor']) ||
            (float)$dados['valor'] <= 0
        ) {
            return $this->failValidationErrors([
                'valor' => 'O valor do pagamento deve ser maior que zero.'
            ]);
        }

        $valorPagamento = round((float)$dados['valor'], 2);

        // ---------------------------------------
        // 5. Calcular pagamentos já confirmados
        // ---------------------------------------

        $pagamentosConfirmados = $this->pagamentoModel
            ->where('factura_id', $factura['id'])
            ->where('estado', 'Confirmado')
            ->findAll();

        $totalPago = 0;

        foreach ($pagamentosConfirmados as $pagamento) {
            $totalPago += (float)$pagamento['valor'];
        }

        $totalFactura = (float)$factura['total'];

        $saldo = $totalFactura - $totalPago;

        // ---------------------------------------
        // 6. Impedir pagamento acima do saldo
        // ---------------------------------------

        if ($valorPagamento > $saldo) {
            return $this->failValidationErrors([
                'valor' => 'O valor do pagamento é superior ao saldo da factura.'
            ]);
        }

        // ---------------------------------------
        // 7. Referência
        // ---------------------------------------

        $referencia = $dados['referencia'] ?? null;

        if ($referencia !== null) {
            $referencia = trim($referencia);
        }

        // ---------------------------------------
        // 8. Data do pagamento
        // ---------------------------------------

        $dataPagamento = $dados['data_pagamento'] ?? date('Y-m-d H:i:s');

        $data = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $dataPagamento
        );

        if (!$data || $data->format('Y-m-d H:i:s') !== $dataPagamento) {
            return $this->failValidationErrors([
                'data_pagamento' => 'Data de pagamento inválida. Use o formato Y-m-d H:i:s.'
            ]);
        }

        // ---------------------------------------
        // 9. Criar pagamento
        // ---------------------------------------

        $novoPagamento = [
            'factura_id' => $factura['id'],
            'metodo' => $dados['metodo'],
            'referencia' => $referencia,
            'valor' => $valorPagamento,
            'data_pagamento' => $dataPagamento,
            'estado' => 'Confirmado',
        ];

        $id = $this->pagamentoModel->insert($novoPagamento);

        if (!$id) {
            return $this->failServerError(
                'Não foi possível registar o pagamento.'
            );
        }

        // ---------------------------------------
        // 10. Calcular novo total pago
        // ---------------------------------------

        $novoTotalPago = $totalPago + $valorPagamento;
        $resultadoProvisionamento = null;

        // ---------------------------------------
        // 11. Actualizar estado da factura
        // ---------------------------------------

        /**if ($novoTotalPago >= $totalFactura) {

            $this->facturaModel->update(
                $factura['id'],
                [
                    'estado' => 'Paga'
                ]
            );
        }*/

        if ($novoTotalPago >= $totalFactura) {

            // ==========================================
            // 11. FACTURA PAGA
            // ==========================================

            $this->facturaModel->update(
                $factura['id'],
                [
                    'estado' => 'Paga'
                ]
            );


            // ==========================================
            // 12. OBTER PEDIDO DA FACTURA
            // ==========================================

            $pedido = $this->pedidoModel
                ->find($factura['pedido_id']);


            if (!$pedido) {

                return $this->failServerError(
                    'Pagamento registado, mas o pedido da factura não foi encontrado.'
                );
            }


            // ==========================================
            // 13. ALTERAR PEDIDO PARA PAGO
            // ==========================================

            $this->pedidoModel->update(
                $pedido['id'],
                [
                    'estado' => 'Pago'
                ]
            );


            // ==========================================
            // 14. PROCESSAR PROVISIONAMENTO
            // ==========================================

            $resultadoProvisionamento =
                $this->provisionamentoService
                ->processarPedido($pedido['id']);
        }

        $pagamento = $this->pagamentoModel->find($id);

        $facturaActualizada = $this->facturaModel
            ->find($factura['id']);

        $novoSaldo = $totalFactura - $novoTotalPago;

        return $this->respondCreated([
            'status' => true,
            'mensagem' => 'Pagamento registado com sucesso.',
            'dados' => [
                'pagamento' => $pagamento,
                'factura' => $facturaActualizada,
                'total_pago' => number_format(
                    $novoTotalPago,
                    2,
                    '.',
                    ''
                ),
                'saldo' => number_format(
                    max(0, $novoSaldo),
                    2,
                    '.',
                    ''
                ),
                 'provisionamento' => $resultadoProvisionamento
            ]
        ]);
    }

    /**
     * PUT /pagamento/{id}
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do pagamento é obrigatório.'
            ]);
        }

        $pagamento = $this->pagamentoModel->find($id);

        if (!$pagamento) {
            return $this->failNotFound(
                'Pagamento não encontrado.'
            );
        }

        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        $dadosActualizar = [];

        // Actualizar estado
        if (isset($dados['estado'])) {

            $estadosPermitidos = [
                'Confirmado',
                'Pendente',
                'Recusado',
                'Estornado'
            ];

            if (!in_array($dados['estado'], $estadosPermitidos)) {
                return $this->failValidationErrors([
                    'estado' => 'Estado do pagamento inválido.'
                ]);
            }

            $dadosActualizar['estado'] = $dados['estado'];
        }

        // Actualizar referência
        if (isset($dados['referencia'])) {
            $dadosActualizar['referencia'] =
                trim($dados['referencia']);
        }

        if (empty($dadosActualizar)) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum campo válido foi enviado.'
            ]);
        }

        $this->pagamentoModel->update(
            $id,
            $dadosActualizar
        );

        $pagamentoActualizado =
            $this->pagamentoModel->find($id);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Pagamento actualizado com sucesso.',
            'dados' => $pagamentoActualizado
        ]);
    }

    /**
     * DELETE /pagamento/{id}
     *
     * Não apagamos fisicamente o pagamento.
     * O pagamento passa para Estornado.
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do pagamento é obrigatório.'
            ]);
        }

        $pagamento = $this->pagamentoModel->find($id);

        if (!$pagamento) {
            return $this->failNotFound(
                'Pagamento não encontrado.'
            );
        }

        if ($pagamento['estado'] === 'Estornado') {
            return $this->failValidationErrors([
                'estado' => 'Este pagamento já foi estornado.'
            ]);
        }

        $this->pagamentoModel->update($id, [
            'estado' => 'Estornado'
        ]);

        return $this->respond([
            'status' => true,
            'mensagem' => 'Pagamento estornado com sucesso.'
        ]);
    }
}
