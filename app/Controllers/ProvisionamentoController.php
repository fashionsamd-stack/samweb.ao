<?php

namespace App\Controllers;
use App\Services\ProvisionamentoService;
use App\Models\ProvisionamentoModel;
use App\Models\PedidoModel;
use App\Models\ItemPedidoModel;
use CodeIgniter\RESTful\ResourceController;

class ProvisionamentoController extends ResourceController
{
    protected $format = 'json';

    protected $provisionamentoModel;
    protected $pedidoModel;
    protected $itemPedidoModel;

    public function __construct()
    {
        $this->provisionamentoModel = new ProvisionamentoModel();
        $this->pedidoModel = new PedidoModel();
        $this->itemPedidoModel = new ItemPedidoModel();
    }

    /**
     * GET /provisionamento
     */
    public function index()
    {
        $provisionamentos = $this->provisionamentoModel
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => true,
            'dados' => $provisionamentos
        ]);
    }

    /**
     * GET /provisionamento/{id}
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' => 'O ID do provisionamento é obrigatório.'
            ]);
        }

        $provisionamento =
            $this->provisionamentoModel->find($id);

        if (!$provisionamento) {
            return $this->failNotFound(
                'Provisionamento não encontrado.'
            );
        }

        return $this->respond([
            'status' => true,
            'dados' => $provisionamento
        ]);
    }

    /**
     * POST /provisionamento
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

        $pedido = $this->pedidoModel
            ->find($dados['pedido_id']);

        if (!$pedido) {
            return $this->failNotFound(
                'Pedido não encontrado.'
            );
        }

        // ---------------------------------------
        // 2. Validar item do pedido
        // ---------------------------------------

        if (empty($dados['item_pedido_id'])) {
            return $this->failValidationErrors([
                'item_pedido_id' =>
                    'O item do pedido é obrigatório.'
            ]);
        }

        $item = $this->itemPedidoModel
            ->where('id', $dados['item_pedido_id'])
            ->where('pedido_id', $pedido['id'])
            ->first();

        if (!$item) {
            return $this->failValidationErrors([
                'item_pedido_id' =>
                    'O item não pertence ao pedido informado.'
            ]);
        }

        // ---------------------------------------
        // 3. Validar tipo de serviço
        // ---------------------------------------

        if (empty($dados['tipo_servico'])) {
            return $this->failValidationErrors([
                'tipo_servico' =>
                    'O tipo de serviço é obrigatório.'
            ]);
        }

        $tiposPermitidos = [
            'Dominio',
            'Alojamento',
            'Servico de Email',
            'Certificado SSL',
            'Hospedagem VPS',
            'Website'
        ];

        if (!in_array(
            $dados['tipo_servico'],
            $tiposPermitidos
        )) {
            return $this->failValidationErrors([
                'tipo_servico' =>
                    'Tipo de serviço inválido.'
            ]);
        }

        // ---------------------------------------
        // 4. Verificar duplicação
        // ---------------------------------------

        $existente = $this->provisionamentoModel
            ->where('item_pedido_id', $item['id'])
            ->first();

        if ($existente) {
            return $this->failValidationErrors([
                'item_pedido_id' =>
                    'Este item já possui um provisionamento.'
            ]);
        }

        // ---------------------------------------
        // 5. Estado inicial
        // ---------------------------------------

        $estado = $dados['estado'] ?? 'Pendente';

        $estadosPermitidos = [
            'Pendente',
            'Processando',
            'Concluido',
            'Falhou'
        ];

        if (!in_array(
            $estado,
            $estadosPermitidos
        )) {
            return $this->failValidationErrors([
                'estado' =>
                    'Estado de provisionamento inválido.'
            ]);
        }

        // ---------------------------------------
        // 6. Mensagem
        // ---------------------------------------

        $mensagem = $dados['mensagem']
            ?? 'Provisionamento pendente.';

        // ---------------------------------------
        // 7. Data de início
        // ---------------------------------------

        $dataInicio = null;

        if (!empty($dados['data_inicio'])) {

            $dataInicioObj =
                \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $dados['data_inicio']
                );

            if (
                !$dataInicioObj ||
                $dataInicioObj->format(
                    'Y-m-d H:i:s'
                ) !== $dados['data_inicio']
            ) {
                return $this->failValidationErrors([
                    'data_inicio' =>
                        'Data de início inválida. Use Y-m-d H:i:s.'
                ]);
            }

            $dataInicio =
                $dataInicioObj->format(
                    'Y-m-d H:i:s'
                );
        }

        // ---------------------------------------
        // 8. Data de conclusão
        // ---------------------------------------

        $dataConclusao = null;

        if (!empty($dados['data_conclusao'])) {

            $dataConclusaoObj =
                \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $dados['data_conclusao']
                );

            if (
                !$dataConclusaoObj ||
                $dataConclusaoObj->format(
                    'Y-m-d H:i:s'
                ) !== $dados['data_conclusao']
            ) {
                return $this->failValidationErrors([
                    'data_conclusao' =>
                        'Data de conclusão inválida.'
                ]);
            }

            $dataConclusao =
                $dataConclusaoObj->format(
                    'Y-m-d H:i:s'
                );
        }

        // ---------------------------------------
        // 9. Criar provisionamento
        // ---------------------------------------

        $novoProvisionamento = [
            'pedido_id' => $pedido['id'],
            'item_pedido_id' => $item['id'],
            'tipo_servico' => $dados['tipo_servico'],
            'estado' => $estado,
            'mensagem' => $mensagem,
            'data_inicio' => $dataInicio,
            'data_conclusao' => $dataConclusao
        ];

        $id = $this->provisionamentoModel
            ->insert($novoProvisionamento);

        if (!$id) {
            return $this->failServerError(
                'Não foi possível criar o provisionamento.'
            );
        }

        $provisionamento =
            $this->provisionamentoModel->find($id);

        return $this->respondCreated([
            'status' => true,
            'mensagem' =>
                'Provisionamento criado com sucesso.',
            'dados' => $provisionamento
        ]);
    }

    /**
     * PUT /provisionamento/{id}
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' =>
                    'O ID do provisionamento é obrigatório.'
            ]);
        }

        $provisionamento =
            $this->provisionamentoModel->find($id);

        if (!$provisionamento) {
            return $this->failNotFound(
                'Provisionamento não encontrado.'
            );
        }

        $dados = $this->request->getJSON(true);

        if (!$dados) {
            return $this->failValidationErrors([
                'dados' => 'Nenhum dado foi enviado.'
            ]);
        }

        $dadosActualizar = [];

        // ---------------------------------------
        // Estado
        // ---------------------------------------

        if (isset($dados['estado'])) {

            $estadosPermitidos = [
                'Pendente',
                'Processando',
                'Concluido',
                'Falhou'
            ];

            if (!in_array(
                $dados['estado'],
                $estadosPermitidos
            )) {
                return $this->failValidationErrors([
                    'estado' =>
                        'Estado de provisionamento inválido.'
                ]);
            }

            $dadosActualizar['estado'] =
                $dados['estado'];

            // Começou a processar
            if (
                $dados['estado'] === 'Processando' &&
                empty($provisionamento['data_inicio'])
            ) {
                $dadosActualizar['data_inicio'] =
                    date('Y-m-d H:i:s');
            }

            // Foi concluído
            if ($dados['estado'] === 'Concluido') {

                if (
                    empty($provisionamento['data_inicio'])
                ) {
                    $dadosActualizar['data_inicio'] =
                        date('Y-m-d H:i:s');
                }

                $dadosActualizar['data_conclusao'] =
                    date('Y-m-d H:i:s');
            }
        }

        // ---------------------------------------
        // Mensagem
        // ---------------------------------------

        if (isset($dados['mensagem'])) {
            $dadosActualizar['mensagem'] =
                trim($dados['mensagem']);
        }

        if (empty($dadosActualizar)) {
            return $this->failValidationErrors([
                'dados' =>
                    'Nenhum campo válido foi enviado.'
            ]);
        }

        $this->provisionamentoModel->update(
            $id,
            $dadosActualizar
        );

        $actualizado =
            $this->provisionamentoModel->find($id);

        return $this->respond([
            'status' => true,
            'mensagem' =>
                'Provisionamento actualizado com sucesso.',
            'dados' => $actualizado
        ]);
    }

    /**
     * DELETE /provisionamento/{id}
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors([
                'id' =>
                    'O ID do provisionamento é obrigatório.'
            ]);
        }

        $provisionamento =
            $this->provisionamentoModel->find($id);

        if (!$provisionamento) {
            return $this->failNotFound(
                'Provisionamento não encontrado.'
            );
        }

        if (
            $provisionamento['estado'] ===
            'Concluido'
        ) {
            return $this->failValidationErrors([
                'estado' =>
                    'Um provisionamento concluído não pode ser cancelado.'
            ]);
        }

        $this->provisionamentoModel->update(
            $id,
            [
                'estado' => 'Falhou',
                'mensagem' =>
                    'Provisionamento interrompido.'
            ]
        );

        return $this->respond([
            'status' => true,
            'mensagem' =>
                'Provisionamento interrompido com sucesso.'
        ]);
    }
    public function executar($id = null)
{
    // ==========================================
    // 1. VALIDAR ID
    // ==========================================

    if (!$id) {
        return $this->failValidationErrors(
            'ID do provisionamento é obrigatório.'
        );
    }


    // ==========================================
    // 2. CHAMAR O SERVICE
    // ==========================================

    $service = new ProvisionamentoService();

    $resultado = $service->executar($id);


    // ==========================================
    // 3. VERIFICAR RESULTADO
    // ==========================================

    if (!$resultado['sucesso']) {
        return $this->fail(
            $resultado['mensagem']
        );
    }


    // ==========================================
    // 4. RESPONDER
    // ==========================================

    return $this->respond([
        'sucesso' => true,
        'mensagem' => $resultado['mensagem'],
        'dados' => $resultado['dados'] ?? null
    ]);
}
}