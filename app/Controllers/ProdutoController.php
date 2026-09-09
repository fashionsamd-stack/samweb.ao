<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\ProdutoModel;

class ProdutoController extends BaseController
{
    protected $produtoModel;

    public function __construct()
    {
        $this->produtoModel = new ProdutoModel();
    }

    // Listar produtos activos
    public function index()
    {
        $produtos = $this->produtoModel
            ->where('estado', 1)
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $produtos
        ]);
    }

    // Consultar produto
    public function show($id)
    {
        $produto = $this->produtoModel
            ->where('id', $id)
            ->where('estado', 1)
            ->first();

        if (!$produto) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Produto não encontrado.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $produto
        ]);
    }

    // Criar produto
    public function storeProduto()
    {
        $dados = $this->request->getJSON(true);

        if (
            empty($dados['categoria_id']) ||
            empty($dados['nome']) ||
            empty($dados['tipo'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Categoria, nome e tipo são obrigatórios.'
                ]);
        }

        // Verificar se a categoria existe e está activa
        $categoriaModel = new \App\Models\CategoriaModel();

        $categoria = $categoriaModel
            ->where('id', $dados['categoria_id'])
            ->where('estado', 1)
            ->first();

        if (!$categoria) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'A categoria indicada não existe ou está inactiva.'
                ]);
        }

        // Verificar produto duplicado
        $produtoExistente = $this->produtoModel
            ->where('nome', $dados['nome'])
            ->where('categoria_id', $dados['categoria_id'])
            ->first();

        if ($produtoExistente) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Este produto já existe nesta categoria.'
                ]);
        }

        $this->produtoModel->insert([
            'categoria_id' => $dados['categoria_id'],
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'tipo' => $dados['tipo'],
            'estado' => 1
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Produto criado com sucesso.',
                'id' => $this->produtoModel->getInsertID()
            ]);
    }

    // Actualizar produto
    public function update($id)
    {
        $produto = $this->produtoModel->find($id);

        if (!$produto) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Produto não encontrado.'
                ]);
        }

        $dados = $this->request->getJSON(true);

        if (
            empty($dados['categoria_id']) ||
            empty($dados['nome']) ||
            empty($dados['tipo'])
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Categoria, nome e tipo são obrigatórios.'
                ]);
        }

        // Verificar categoria
        $categoriaModel = new \App\Models\CategoriaModel();

        $categoria = $categoriaModel
            ->where('id', $dados['categoria_id'])
            ->where('estado', 1)
            ->first();

        if (!$categoria) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'A categoria indicada não existe ou está inactiva.'
                ]);
        }

        $this->produtoModel->update($id, [
            'categoria_id' => $dados['categoria_id'],
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'tipo' => $dados['tipo']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Produto actualizado com sucesso.'
        ]);
    }

    // Desactivar produto
    public function delete($id)
    {
        $produto = $this->produtoModel->find($id);

        if (!$produto) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Produto não encontrado.'
                ]);
        }

        $this->produtoModel->update($id, [
            'estado' => 0
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Produto desactivado com sucesso.'
        ]);
    }
}
