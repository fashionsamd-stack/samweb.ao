<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\PrecoModel;
use App\Models\ProdutoModel;

class PrecoController extends BaseController
{
    protected $precoModel;

    protected $produtoModel;

    public function __construct()
    {
        $this->precoModel = new PrecoModel();
        $this->produtoModel = new ProdutoModel();
    }

    // Listar preços
    public function index()
    {
        $precos = $this->precoModel
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $precos
        ]);
    }

    // Consultar preço
    public function show($id)
    {
        $preco = $this->precoModel
            ->find($id);

        if (!$preco) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Preço não encontrado.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $preco
        ]);
    }

    // Criar preço
    public function storePedido()
    {
        $dados = $this->request->getJSON(true);

        if (
            empty($dados['produto_id']) ||
            empty($dados['periodo']) ||
            !isset($dados['valor'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Produto, período e valor são obrigatórios.'
                ]);
        }

        // Verificar se o produto existe e está activo
        $produto = $this->produtoModel
            ->where('id', $dados['produto_id'])
            ->where('estado', 1)
            ->first();

        if (!$produto) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O produto indicado não existe ou está inactivo.'
                ]);
        }

        // Verificar se o valor é válido
        if (!is_numeric($dados['valor']) || $dados['valor'] <= 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O valor deve ser superior a zero.'
                ]);
        }

        // Verificar preço duplicado para o mesmo período
        $precoExistente = $this->precoModel
            ->where('produto_id', $dados['produto_id'])
            ->where('periodo', $dados['periodo'])
            ->first();

        if ($precoExistente) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Já existe um preço para este produto e período.'
                ]);
        }

        $this->precoModel->insert([
            'produto_id' => $dados['produto_id'],
            'periodo' => $dados['periodo'],
            'valor' => $dados['valor']
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Preço criado com sucesso.',
                'id' => $this->precoModel->getInsertID()
            ]);
    }

    // Actualizar preço
    public function update($id)
    {
        $preco = $this->precoModel->find($id);

        if (!$preco) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Preço não encontrado.'
                ]);
        }

        $dados = $this->request->getJSON(true);

        if (
            empty($dados['produto_id']) ||
            empty($dados['periodo']) ||
            !isset($dados['valor'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Produto, período e valor são obrigatórios.'
                ]);
        }

        $produto = $this->produtoModel
            ->where('id', $dados['produto_id'])
            ->where('estado', 1)
            ->first();

        if (!$produto) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O produto indicado não existe ou está inactivo.'
                ]);
        }

        if (!is_numeric($dados['valor']) || $dados['valor'] <= 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O valor deve ser superior a zero.'
                ]);
        }

        $this->precoModel->update($id, [
            'produto_id' => $dados['produto_id'],
            'periodo' => $dados['periodo'],
            'valor' => $dados['valor']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Preço actualizado com sucesso.'
        ]);
    }

    // Eliminar preço
    public function delete($id)
    {
        $preco = $this->precoModel->find($id);

        if (!$preco) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Preço não encontrado.'
                ]);
        }

        $this->precoModel->delete($id);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Preço eliminado com sucesso.'
        ]);
    }
}
