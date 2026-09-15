<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;


use App\Models\DominioModel;
use App\Models\ClienteModel;

class DominioController extends BaseController
{
    protected $dominioModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->dominioModel = new DominioModel();
        $this->clienteModel = new ClienteModel();
    }

    // Listar domínios activos
    public function index()
    {
        $dominios = $this->dominioModel
            ->where('estado', 1)
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $dominios
        ]);
    }

    // Consultar domínio
    public function show($id)
    {
        $dominio = $this->dominioModel
            ->where('id', $id)
            ->where('estado', 'Activo')
            ->first();

        if (!$dominio) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Domínio não encontrado.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $dominio
        ]);
    }

    // Registar domínio para um cliente
    public function storeDominio()
    {
        $dados = $this->request->getJSON(true);

        if (
            empty($dados['cliente_id']) ||
            empty($dados['nome']) ||
            empty($dados['inicio']) ||
            empty($dados['expiracao'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Cliente, domínio, início e expiração são obrigatórios.'
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

        // Verificar se o domínio já existe
        $dominioExistente = $this->dominioModel
            ->where('nome', $dados['nome'])
            ->first();

        if ($dominioExistente) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Este domínio já está registado no sistema.'
                ]);
        }

        $this->dominioModel->insert([
            'cliente_id' => $dados['cliente_id'],
            'nome' => strtolower(trim($dados['nome'])),
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao'],
            'renovacao_auto' => $dados['renovacao_auto'] ?? 0,
            'estado' => 'Activo'
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Domínio registado com sucesso.',
                'id' => $this->dominioModel->getInsertID()
            ]);
    }

    // Actualizar domínio
    public function update($id)
    {
        $dominio = $this->dominioModel->find($id);

        if (!$dominio) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Domínio não encontrado.'
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

        $this->dominioModel->update($id, [
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao'],
            'renovacao_auto' => $dados['renovacao_auto'] ?? 0
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Domínio actualizado com sucesso.'
        ]);
    }

    // Desactivar domínio
    public function delete($id)
    {
        $dominio = $this->dominioModel->find($id);

        if (!$dominio) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Domínio não encontrado.'
                ]);
        }

        $this->dominioModel->update($id, [
            'estado' => 'Inactivo'
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Domínio desactivado com sucesso.'
        ]);
    }
}
