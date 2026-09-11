<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\AlojamentoModel;
use App\Models\ClienteModel;
use App\Models\ProdutoModel;
use App\Models\DominioModel;

class AlojamentoController extends BaseController
{
    protected $alojamentoModel;
    protected $clienteModel;
    protected $produtoModel;
    protected $dominioModel;

    public function __construct()
    {
        $this->alojamentoModel = new AlojamentoModel();
        $this->clienteModel = new ClienteModel();
        $this->produtoModel = new ProdutoModel();
        $this->dominioModel = new DominioModel();
    }

    // Listar alojamentos activos
    public function index()
    {
        $alojamentos = $this->alojamentoModel
            ->where('estado', 'Activo')
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $alojamentos
        ]);
    }

    // Consultar alojamento
    public function show($id)
    {
        $alojamento = $this->alojamentoModel
            ->where('id', $id)
            ->where('estado', 'Activo')
            ->first();

        if (!$alojamento) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Alojamento não encontrado.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $alojamento
        ]);
    }

    // Criar alojamento
    public function storeAlojamento()
    {
        $dados = $this->request->getJSON(true);

        if (
            empty($dados['cliente_id']) ||
            empty($dados['produto_id']) ||
            empty($dados['dominio_id']) ||
            empty($dados['inicio']) ||
            empty($dados['expiracao'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Cliente, produto, domínio, início e expiração são obrigatórios.'
                ]);
        }

        // Verificar cliente activo
        $cliente = $this->clienteModel
            ->where('id', $dados['cliente_id'])
            ->where('estado', 1)
            ->first();

        if (!$cliente) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O cliente não existe ou está inactivo.'
                ]);
        }

        // Verificar produto activo
        $produto = $this->produtoModel
            ->where('id', $dados['produto_id'])
            ->where('estado', 1)
            ->first();

        if (!$produto) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O produto não existe ou está inactivo.'
                ]);
        }

        // Verificar domínio activo
        $dominio = $this->dominioModel
            ->where('id', $dados['dominio_id'])
            ->where('estado', 'Activo')
            ->first();

        if (!$dominio) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O domínio não existe ou está inactivo.'
                ]);
        }

        // Garantir que o domínio pertence ao cliente
        if ($dominio['cliente_id'] != $dados['cliente_id']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O domínio indicado não pertence ao cliente.'
                ]);
        }

        // Validar datas
        if ($dados['expiracao'] <= $dados['inicio']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'A data de expiração deve ser posterior à data de início.'
                ]);
        }

        $this->alojamentoModel->insert([
            'cliente_id' => $dados['cliente_id'],
            'produto_id' => $dados['produto_id'],
            'dominio_id' => $dados['dominio_id'],
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao'],
            'estado' => 'Activo'
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Alojamento criado com sucesso.',
                'id' => $this->alojamentoModel->getInsertID()
            ]);
    }

    // Actualizar alojamento
    public function update($id)
    {
        $alojamento = $this->alojamentoModel->find($id);

        if (!$alojamento) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Alojamento não encontrado.'
                ]);
        }

        $dados = $this->request->getJSON(true);

        if (
            empty($dados['inicio']) ||
            empty($dados['expiracao'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Início e expiração são obrigatórios.'
                ]);
        }

        if ($dados['expiracao'] <= $dados['inicio']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'A data de expiração deve ser posterior à data de início.'
                ]);
        }

        $this->alojamentoModel->update($id, [
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Alojamento actualizado com sucesso.'
        ]);
    }

    // Desactivar alojamento
    public function delete($id)
    {
        $alojamento = $this->alojamentoModel->find($id);

        if (!$alojamento) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Alojamento não encontrado.'
                ]);
        }

        $this->alojamentoModel->update($id, [
            'estado' => 'Inactivo'
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Alojamento desactivado com sucesso.'
        ]);
    }
}
