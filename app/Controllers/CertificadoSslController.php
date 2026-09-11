<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\CertificadoSslModel;
use App\Models\ClienteModel;
use App\Models\DominioModel;
use App\Models\ProdutoModel;


class CertificadoSslController extends BaseController
{
    protected $certificadoSslModel;
    protected $clienteModel;
    protected $dominioModel;
    protected $produtoModel;

    public function __construct()
    {
        $this->certificadoSslModel = new CertificadoSslModel();
        $this->clienteModel = new ClienteModel();
        $this->dominioModel = new DominioModel();
        $this->produtoModel = new ProdutoModel();
    }

    // Listar certificados SSL activos
    public function index()
    {
        $certificados = $this->certificadoSslModel
            ->where('estado', 'Activo')
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $certificados
        ]);
    }

    // Consultar certificado SSL
    public function show($id)
    {
        $certificado = $this->certificadoSslModel
            ->where('id', $id)
            ->where('estado', 'Activo')
            ->first();

        if (!$certificado) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Certificado SSL não encontrado.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $certificado
        ]);
    }

    // Criar certificado SSL
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

        // Verificar se o domínio pertence ao cliente
        if ($dominio['cliente_id'] != $dados['cliente_id']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O domínio indicado não pertence ao cliente.'
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

        $this->certificadoSslModel->insert([
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
                'mensagem' => 'Certificado SSL criado com sucesso.',
                'id' => $this->certificadoSslModel->getInsertID()
            ]);
    }

    // Actualizar certificado SSL
    public function update($id)
    {
        $certificado = $this->certificadoSslModel->find($id);

        if (!$certificado) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Certificado SSL não encontrado.'
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

        $this->certificadoSslModel->update($id, [
            'inicio' => $dados['inicio'],
            'expiracao' => $dados['expiracao']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Certificado SSL actualizado com sucesso.'
        ]);
    }

    // Desactivar certificado SSL
    public function delete($id)
    {
        $certificado = $this->certificadoSslModel->find($id);

        if (!$certificado) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Certificado SSL não encontrado.'
                ]);
        }

        $this->certificadoSslModel->update($id, [
            'estado' => 'Inactivo'
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Certificado SSL desactivado com sucesso.'
        ]);
    }
}
