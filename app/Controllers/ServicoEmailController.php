<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\ServicoEmailModel;
use App\Models\ClienteModel;
use App\Models\DominioModel;
use App\Models\ProdutoModel;

class ServicoEmailController extends BaseController
{
    protected $servicoEmailModel;
    protected $clienteModel;
    protected $dominioModel;
    protected $produtoModel;

    public function __construct()
    {
        $this->servicoEmailModel = new ServicoEmailModel();
        $this->clienteModel = new ClienteModel();
        $this->dominioModel = new DominioModel();
        $this->produtoModel = new ProdutoModel();
    }

    // Listar serviços de e-mail activos
    public function index()
    {
        $servicos = $this->servicoEmailModel
            ->where('estado', 'Activo')
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $servicos
        ]);
    }

    // Consultar serviço de e-mail
    public function show($id)
    {
        $servico = $this->servicoEmailModel
            ->where('id', $id)
            ->where('estado', 'Activo')
            ->first();

        if (!$servico) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Serviço de e-mail não encontrado.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $servico
        ]);
    }

    // Criar serviço de e-mail
    public function store()
    {
        $dados = $this->request->getJSON(true);

        if (
            empty($dados['cliente_id']) ||
            empty($dados['dominio_id']) ||
            empty($dados['produto_id']) ||
            empty($dados['inicio']) ||
            empty($dados['expiracao'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Cliente, domínio, produto, início e expiração são obrigatórios.'
                ]);
        }

        // Verificar cliente
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

        // Verificar domínio
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
                    'mensagem' => 'O domínio indicado não pertence ao cliente ou Está indisponivél.'
                ]);
        }

        // Verificar produto
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

        // Validar datas
        if ($dados['expiracao'] <= $dados['inicio']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'A data de expiração deve ser posterior à data de início.'
                ]);
        }

        $this->servicoEmailModel->insert([
            'cliente_id' => $dados['cliente_id'],
            'dominio_id' => $dados['dominio_id'],
            'produto_id' => $dados['produto_id'],
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao'],
            'estado' => 'Activo'
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Serviço de e-mail criado com sucesso.',
                'id' => $this->servicoEmailModel->getInsertID()
            ]);
    }

    // Actualizar serviço de e-mail
    public function update($id)
    {
        $servico = $this->servicoEmailModel->find($id);

        if (!$servico) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Serviço de e-mail não encontrado.'
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

        $this->servicoEmailModel->update($id, [
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Serviço de e-mail actualizado com sucesso.'
        ]);
    }

    // Desactivar serviço de e-mail
    public function delete($id)
    {
        $servico = $this->servicoEmailModel->find($id);

        if (!$servico) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Serviço de e-mail não encontrado.'
                ]);
        }

        $this->servicoEmailModel->update($id, [
            'estado' => 'Inactivo'
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Serviço de e-mail desactivado com sucesso.'
        ]);
    }
}