<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//CRUD de Cliente
$routes->get('/', 'Home::index');
$routes->get('teste', 'TesteController::index');
$routes->get('clientes', 'ClienteController::index');
$routes->get('cliente/(:num)', 'ClienteController::showClient/$1');
$routes->post('clientes', 'ClienteController::storeClient');
$routes->put('clientes/(:num)', 'ClienteController::updateClient/$1');
$routes->delete('clientes/(:num)', 'ClienteController::deleteClient/$1');

//CRUD de Categria
$routes->get('categorias', 'CategoriaController::index');
$routes->get('categorias/(:num)', 'CategoriaController::showCategoria/$1');
$routes->post('categorias', 'CategoriaController::storeCategoria');
$routes->put('categorias/(:num)', 'CategoriaController::updateCategoria/$1');
$routes->delete('categorias/(:num)', 'CategoriaController::deleteCategoria/$1');
//CRUD de Produtos
$routes->get('produtos', 'ProdutoController::index');
$routes->get('produtos/(:num)', 'ProdutoController::show/$1');
$routes->post('produtos', 'ProdutoController::storeProduto');
$routes->put('produtos/(:num)', 'ProdutoController::update/$1');
$routes->delete('produtos/(:num)', 'ProdutoController::delete/$1');
//CRUD de Pedidos
$routes->get('precos', 'PrecoController::index');
$routes->get('precos/(:num)', 'PrecoController::show/$1');
$routes->post('precos', 'PrecoController::storePreco');
$routes->put('precos/(:num)', 'PrecoController::update/$1');
$routes->delete('precos/(:num)', 'PrecoController::delete/$1');

//CRUD de Dominio
$routes->get('dominios', 'DominioController::index');
$routes->get('dominios/(:num)', 'DominioController::show/$1');
$routes->post('dominios', 'DominioController::storeDominio');
$routes->put('dominios/(:num)', 'DominioController::update/$1');
$routes->delete('dominios/(:num)', 'DominioController::delete/$1');

//CRUD do Alojamento 
$routes->get('alojamentos', 'AlojamentoController::index');
$routes->get('alojamentos/(:num)', 'AlojamentoController::show/$1');
$routes->post('alojamentos', 'AlojamentoController::storeAlojamento');
$routes->put('alojamentos/(:num)', 'AlojamentoController::update/$1');
$routes->delete('alojamentos/(:num)', 'AlojamentoController::delete/$1');

//CRUD do Serviço de Email

$routes->get('servicosEmail', 'ServicoEmailController::index');
$routes->get('servicosEmail/(:num)', 'ServicoEmailController::show/$1');
$routes->post('servicosEmail', 'ServicoEmailController::store');
$routes->put('servicosEmail/(:num)', 'ServicoEmailController::update/$1');
$routes->delete('servicosEmail/(:num)', 'ServicoEmailController::delete/$1');

//CRUD do Certificado SSL

$routes->get('certificadosSsl', 'CertificadoSslController::index');
$routes->get('certificadosSsl/(:num)', 'CertificadoSslController::show/$1');
$routes->post('certificadosSsl', 'CertificadoSslController::store');
$routes->put('certificadosSsl/(:num)', 'CertificadoSslController::update/$1');
$routes->delete('certificadosSsl/(:num)', 'CertificadoSslController::delete/$1');

//CRUD do Hospedagem VPS
$routes->get('hospedagensVps', 'HospedagemVpsController::index');
$routes->get('hospedagensVps', 'HospedagemVpsController::index');
$routes->get('hospedagensVps/(:num)', 'HospedagemVpsController::show/$1');
$routes->post('hospedagensVps', 'HospedagemVpsController::store');
$routes->put('hospedagensVps/(:num)', 'HospedagemVpsController::update/$1');
$routes->delete('hospedagensVps/(:num)', 'HospedagemVpsController::delete/$1');

//CRUD de Pedidos
$routes->get('pedido', 'PedidoController::index');
$routes->get('pedido/(:num)', 'PedidoController::show/$1');
$routes->post('pedido', 'PedidoController::create');
$routes->put('pedido/(:num)', 'PedidoController::update/$1');
$routes->delete('pedido/(:num)', 'PedidoController::delete/$1');