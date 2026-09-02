<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\ProdutoModel;
use App\Models\EmpresaModel;

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
class TesteController extends Controller
{
   public function index()
    {
        $produtoModel = new ProdutoModel();
        $empresaModel = new EmpresaModel();

        $produtos = $produtoModel->findAll();
        $empresa = $empresaModel->findAll();

        return $this->response->setJSON($empresa);
    }
}