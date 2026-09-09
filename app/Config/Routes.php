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
$routes->get('produtos', 'ProdutoController::index');
$routes->get('produtos/(:num)', 'ProdutoController::show/$1');
$routes->post('produtos', 'ProdutoController::storeProduto');
$routes->put('produtos/(:num)', 'ProdutoController::update/$1');
$routes->delete('produtos/(:num)', 'ProdutoController::delete/$1');