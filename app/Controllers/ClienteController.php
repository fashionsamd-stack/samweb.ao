<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use App\Models\ClienteModel;
use CodeIgniter\RESTful\ResourceController;

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
class ClienteController extends ResourceController
{
    protected $clienteModel;
    public  function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }
    //Listar todos clienetes

    public function index()
    {
         $clientes = $this->clienteModel
         ->where('estado', 1)
         ->findAll();
       
        return $this->response->setJSON([
            'status' => true,
            'dados' => $clientes
        ]);
    }
    //Mostrar apenas um cliente

    public function showClient($id)
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => true,
                    'mensagem' => 'Cliente não encontardado.'
                ]);
        }
        return $this->response->setJSON([
            'status'=>true,
            'dados'=>$cliente
        ]);
    }
    // Cadastrar Clientes
public function storeClient()
{
    $dados = $this->request->getJSON(true);

    // Validação
    if (
        empty($dados['nome']) ||
        empty($dados['email']) ||
        empty($dados['telefone']) ||
        empty($dados['senha']) ||
        empty($dados['tipo'])
    ) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Nome, email, telefone, senha e tipo são obrigatórios.'
            ]);
    }

    // Verificar se o email já existe
    $utilizadorModel = new \App\Models\UtilizadorModel();

    $utilizadorExistente = $utilizadorModel
        ->where('email', $dados['email'])
        ->first();

    if ($utilizadorExistente) {
        return $this->response
            ->setStatusCode(409)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Este email já está registado.'
            ]);
    }

    $db = \Config\Database::connect();

    $db->transStart();

    // Criar utilizador
    $utilizadorModel->insert([
        'nome' => $dados['nome'],
        'email' => $dados['email'],
        'telefone' => $dados['telefone'],
        'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
        'estado' => 1
    ]);

    $utilizadorId = $utilizadorModel->getInsertID();

    // Criar cliente
    $this->clienteModel->insert([
        'utilizador_id' => $utilizadorId,
        'tipo' => $dados['tipo'],
        'nome' => $dados['nome'],
        'nif' => $dados['nif'] ?? null,
        'estado' => 1
    ]);

    $clienteId = $this->clienteModel->getInsertID();

    $db->transComplete();

    if ($db->transStatus() === false) {
        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Não foi possível criar o cliente.'
            ]);
    }

    return $this->response
        ->setStatusCode(201)
        ->setJSON([
            'status' => true,
            'mensagem' => 'Cliente criado com sucesso.',
            'cliente_id' => $clienteId,
            'utilizador_id' => $utilizadorId
        ]);
}
//Atualizar cliente
public function updateClient($id)
{
    // Procurar o cliente
    $cliente = $this->clienteModel->find($id);

    if (!$cliente) {
        return $this->response
            ->setStatusCode(404)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Cliente não encontrado.'
            ]);
    }

    // Receber os dados JSON
    $dados = $this->request->getJSON(true);

    if (!$dados) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Dados inválidos.'
            ]);
    }

    // Verificar campos obrigatórios
    if (
        empty($dados['nome']) ||
        empty($dados['email']) ||
        empty($dados['telefone']) ||
        empty($dados['tipo'])
    ) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Nome, email, telefone e tipo são obrigatórios.'
            ]);
    }

    $utilizadorModel = new \App\Models\UtilizadorModel();

    // Verificar se o novo email já pertence a outro utilizador
    $emailExistente = $utilizadorModel
        ->where('email', $dados['email'])
        ->where('id !=', $cliente['utilizador_id'])
        ->first();

    if ($emailExistente) {
        return $this->response
            ->setStatusCode(409)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Este email já está associado a outro utilizador.'
            ]);
    }

    $db = \Config\Database::connect();

    $db->transStart();

    // Actualizar utilizador
    $utilizadorModel->update(
        $cliente['utilizador_id'],
        [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone']
        ]
    );

    // Actualizar cliente
    $this->clienteModel->update(
        $id,
        [
            'tipo' => $dados['tipo'],
            'nome' => $dados['nome'],
            'nif' => $dados['nif'] ?? null
        ]
    );

    $db->transComplete();

    if ($db->transStatus() === false) {
        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Não foi possível actualizar o cliente.'
            ]);
    }

    return $this->response
        ->setJSON([
            'status' => true,
            'mensagem' => 'Cliente actualizado com sucesso.'
        ]);
}
   
//Deletar Cliente
public function deleteClient($id)
{
    // Procurar o cliente
    $cliente = $this->clienteModel->find($id);

    if (!$cliente) {
        return $this->response
            ->setStatusCode(404)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Cliente não encontrado.'
            ]);
    }

    $utilizadorModel = new \App\Models\UtilizadorModel();

    $db = \Config\Database::connect();

    // Iniciar transacção
    $db->transStart();

    // Desactivar o cliente
    $this->clienteModel->update($id, [
        'estado' => 0
    ]);

    // Desactivar o utilizador associado
    $utilizadorModel->update($cliente['utilizador_id'], [
        'estado' => 0
    ]);

    // Finalizar transacção
    $db->transComplete();

    // Verificar se ocorreu algum erro
    if ($db->transStatus() === false) {
        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'status' => false,
                'mensagem' => 'Não foi possível desactivar o cliente.'
            ]);
    }

    return $this->response
        ->setJSON([
            'status' => true,
            'mensagem' => 'Cliente desactivado com sucesso.'
        ]);
}
}
