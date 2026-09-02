<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\CategoriaModel;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
class CategoriaController extends BaseController
{
    protected $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new CategoriaModel();
    }

    // Listar categorias activas
    public function index()
    {
        $categorias = $this->categoriaModel
            ->where('estado', 1)
            ->findAll();

        return $this->response->setJSON([
            'status' => true,
            'dados' => $categorias
        ]);
    }

    // Consultar uma categoria
    public function showCategoria($id)
    {
        $categoria = $this->categoriaModel
            ->where('id', $id)
            ->where('estado', 1)
            ->first();

        if (!$categoria) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Categoria não encontrada.'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'dados' => $categoria
        ]);
    }

    // Criar categoria
    public function storeCategoria()
    {
        $dados = $this->request->getJSON(true);

        if (empty($dados['nome'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O nome da categoria é obrigatório.'
                ]);
        }

        $categoriaExistente = $this->categoriaModel
            ->where('nome', $dados['nome'])
            ->first();

        if ($categoriaExistente) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Esta categoria já existe.'
                ]);
        }

        $this->categoriaModel->insert([
            'nome' => $dados['nome'],
            'estado' => 1
        ]);

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status' => true,
                'mensagem' => 'Categoria criada com sucesso.',
                'id' => $this->categoriaModel->getInsertID()
            ]);
    }

    // Actualizar categoria
    public function updateCategoria($id)
    {
        $categoria = $this->categoriaModel->find($id);

        if (!$categoria) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Categoria não encontrada.'
                ]);
        }

        $dados = $this->request->getJSON(true);

        if (empty($dados['nome'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'O nome da categoria é obrigatório.'
                ]);
        }

        $this->categoriaModel->update($id, [
            'nome' => $dados['nome']
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Categoria actualizada com sucesso.'
        ]);
    }

    // Desactivar categoria
    public function deleteCategoria($id)
    {
        $categoria = $this->categoriaModel->find($id);

        if (!$categoria) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'mensagem' => 'Categoria não encontrada.'
                ]);
        }

        $this->categoriaModel->update($id, [
            'estado' => 0
        ]);

        return $this->response->setJSON([
            'status' => true,
            'mensagem' => 'Categoria desactivada com sucesso.'
        ]);
    }
}
