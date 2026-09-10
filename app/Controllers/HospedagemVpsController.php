<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\HospedagemVpsModel;
use App\Models\ClienteModel;
use App\Models\ProdutoModel;

class HospedagemVpsController extends BaseController
{
    protected $hospedagemVpsModel;
    protected $clienteModel;
    protected $produtoModel;

    public function __construct()
    {
        $this->hospedagemVpsModel = new HospedagemVpsModel();
        $this->clienteModel = new ClienteModel();
        $this->produtoModel = new ProdutoModel();
    }

    // Listar hospedagens VPS activas
    public function index()
    {
        $hospedagens = $this->hospedagemVpsModel
            ->where('estado', 'Activo')
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $hospedagens
        ]);
    }

    // Consultar hospedagem VPS
    public function show($id)
    {
        $hospedagem = $this->hospedagemVpsModel
            ->where('id', $id)
            ->where('estado', 'Activo')
            ->first();

        if (!$hospedagem) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Hospedagem VPS não encontrada.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $hospedagem
        ]);
    }

    // Criar hospedagem VPS
    public function store()
    {
        $dados = $this->request->getJSON(true);

        if (
            empty($dados['cliente_id']) ||
            empty($dados['produto_id']) ||
            empty($dados['inicio']) ||
            empty($dados['expiracao'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Cliente, produto, início e expiração são obrigatórios.'
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

        // Validar datas
        if ($dados['expiracao'] <= $dados['inicio']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'A data de expiração deve ser posterior à data de início.'
                ]);
        }

        $this->hospedagemVpsModel->insert([
            'cliente_id' => $dados['cliente_id'],
            'produto_id' => $dados['produto_id'],
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao'],
            'estado' => 'Activo'
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Hospedagem VPS criada com sucesso.',
                'id' => $this->hospedagemVpsModel->getInsertID()
            ]);
    }

    // Actualizar hospedagem VPS
    public function update($id)
    {
        $hospedagem = $this->hospedagemVpsModel->find($id);

        if (!$hospedagem) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Hospedagem VPS não encontrada.'
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

        $this->hospedagemVpsModel->update($id, [
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Hospedagem VPS actualizada com sucesso.'
        ]);
    }

    // Desactivar hospedagem VPS
    public function delete($id)
    {
        $hospedagem = $this->hospedagemVpsModel->find($id);

        if (!$hospedagem) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Hospedagem VPS não encontrada.'
                ]);
        }

        $this->hospedagemVpsModel->update($id, [
            'estado' => 'Inactivo'
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Hospedagem VPS desactivada com sucesso.'
        ]);
    }
}
