<?php

namespace App\Services;

use App\Models\ProvisionamentoModel;
use App\Models\PedidoModel;
use App\Models\ItemPedidoModel;
use App\Models\ProdutoModel;
use App\Models\PrecoModel;
use App\Models\DominioModel;
use App\Models\AlojamentoModel;
use App\Models\ServicoEmailModel;
use App\Models\CertificadoSslModel;
use App\Models\HospedagemVpsModel;
use App\Models\WebsiteModel;
use App\Models\CategoriaModel;

class ProvisionamentoService
{
    protected $provisionamentoModel;
    protected $pedidoModel;
    protected $itemPedidoModel;
    protected $produtoModel;
    protected $precoModel;
    protected $dominioModel;
    protected $alojamentoModel;
    protected $servicoEmailModel;
    protected $certificadoSslModel;
    protected $hospedagemVpsModel;
    protected $websiteModel;
    protected $categoriaModel;

    public function __construct()
    {
        $this->provisionamentoModel = new ProvisionamentoModel();
        $this->pedidoModel = new PedidoModel();
        $this->itemPedidoModel = new ItemPedidoModel();
        $this->produtoModel = new ProdutoModel();
        $this->precoModel = new PrecoModel();
        $this->dominioModel = new DominioModel();
        $this->alojamentoModel = new AlojamentoModel();
        $this->servicoEmailModel = new ServicoEmailModel();
        $this->certificadoSslModel = new CertificadoSslModel();
        $this->hospedagemVpsModel = new HospedagemVpsModel();
        $this->websiteModel = new WebsiteModel();
        $this->categoriaModel = new CategoriaModel();
    }

    /**
     * Executa o provisionamento de um item do pedido.
     */
    public function executar($provisionamentoId)
    {
        // 1. Procurar o provisionamento
        $provisionamento = $this->provisionamentoModel
            ->find($provisionamentoId);

        if (!$provisionamento) {
            return [
                'sucesso' => false,
                'mensagem' => 'Provisionamento não encontrado.'
            ];
        }

        // 2. Não permitir executar novamente
        if ($provisionamento['estado'] === 'Concluido') {
            return [
                'sucesso' => false,
                'mensagem' => 'Este provisionamento já foi concluído.'
            ];
        }

        // 3. Obter o pedido
        $pedido = $this->pedidoModel
            ->find($provisionamento['pedido_id']);

        if (!$pedido) {
            return [
                'sucesso' => false,
                'mensagem' => 'Pedido não encontrado.'
            ];
        }

        // 4. Obter o item do pedido
        $item = $this->itemPedidoModel
            ->find($provisionamento['item_pedido_id']);

        if (!$item) {
            return [
                'sucesso' => false,
                'mensagem' => 'Item do pedido não encontrado.'
            ];
        }

        // 5. Confirmar que o item pertence ao pedido
        if ($item['pedido_id'] != $pedido['id']) {
            return [
                'sucesso' => false,
                'mensagem' => 'O item não pertence ao pedido informado.'
            ];
        }

        // 6. Obter o produto
        $produto = $this->produtoModel
            ->find($item['produto_id']);

        if (!$produto) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado.'
            ];
        }

        // 7. Colocar como Processando
        $this->provisionamentoModel->update(
            $provisionamentoId,
            [
                'estado' => 'Processando',
                'mensagem' => 'Provisionamento em processamento.',
                'data_inicio' => date('Y-m-d H:i:s')
            ]
        );

        try {

            // 8. Identificar o tipo de serviço
            switch ($provisionamento['tipo_servico']) {

                case 'Dominio':
                case 'domínios':
                    $resultado = $this->provisionarDominio(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                case 'Alojamento':
                case 'hosting':
                    $resultado = $this->provisionarAlojamento(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                case 'Servico de Email':
                    $resultado = $this->provisionarServicoEmail(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                case 'Certificado SSL':
                    $resultado = $this->provisionarCertificadoSsl(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                case 'Hospedagem VPS':
                    $resultado = $this->provisionarVps(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                case 'Website':
                    $resultado = $this->provisionarWebsite(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                default:

                    throw new \Exception(
                        'Tipo de serviço não suportado.'
                    );
            }

            // 9. Se chegou aqui, concluímos
            $this->provisionamentoModel->update(
                $provisionamentoId,
                [
                    'estado' => 'Concluido',
                    'mensagem' => $resultado['mensagem'],
                    'data_conclusao' => date('Y-m-d H:i:s')
                ]
            );

            return [
                'sucesso' => true,
                'mensagem' => $resultado['mensagem']
            ];
        } catch (\Exception $e) {

            // 10. Se ocorrer erro
            $this->provisionamentoModel->update(
                $provisionamentoId,
                [
                    'estado' => 'Falhou',
                    'mensagem' => $e->getMessage()
                ]
            );

            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }


    /**
     * Provisionamento de domínio
     */
    private function provisionarDominio(
        $pedido,
        $item,
        $produto
    ) {
        // ==========================================
        // 1. Validar domínio do item
        // ==========================================

        if (empty($item['dominio_id'])) {
            throw new \Exception(
                'O item do pedido não possui domínio associado.'
            );
        }


        // ==========================================
        // 2. Procurar o domínio
        // ==========================================

        $dominio = $this->dominioModel
            ->find($item['dominio_id']);

        if (!$dominio) {
            throw new \Exception(
                'Domínio não encontrado.'
            );
        }


        // ==========================================
        // 3. Confirmar que o domínio pertence
        //    ao cliente do pedido
        // ==========================================

        if (
            $dominio['cliente_id']
            != $pedido['cliente_id']
        ) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }


        // ==========================================
        // 4. Obter o período comprado
        // ==========================================

        $periodo = trim($item['periodo']);

        if (empty($periodo)) {
            throw new \Exception(
                'O período do domínio não foi informado.'
            );
        }


        // ==========================================
        // 5. Domínio já activo → RENOVAÇÃO
        // ==========================================

        if ((int) $dominio['estado'] === 1) {

            $dataBase = new \DateTime(
                $dominio['expiracao']
            );

            $novaExpiracao = $this->calcularExpiracaoDominio(
                $dataBase,
                $periodo
            );

            $this->dominioModel->update(
                $dominio['id'],
                [
                    'expiracao' => $novaExpiracao->format('Y-m-d'),
                    'estado' => 1
                ]
            );

            return [
                'sucesso' => true,
                'mensagem' =>
                'Domínio renovado com sucesso.'
            ];
        }


        // ==========================================
        // 6. Domínio inactivo → ACTIVAR
        // ==========================================

        $dataInicio = new \DateTime();

        $dataExpiracao = $this->calcularExpiracaoDominio(
            $dataInicio,
            $periodo
        );


        $this->dominioModel->update(
            $dominio['id'],
            [
                'inicio' => $dataInicio->format('Y-m-d'),
                'expiracao' => $dataExpiracao->format('Y-m-d'),
                'estado' => 1
            ]
        );


        return [
            'sucesso' => true,
            'mensagem' =>
            'Domínio provisionado com sucesso.'
        ];
    }


    /**
     * Provisionamento de alojamento
     */
    private function provisionarAlojamento(
        $pedido,
        $item,
        $produto
    ) {
        // ==========================================
        // 1. Dados principais
        // ==========================================

        $clienteId = $pedido['cliente_id'];
        $dominioId = $item['dominio_id'];
        $produtoId = $item['produto_id'];
        $periodo   = trim($item['periodo']);


        // ==========================================
        // 2. Validar período
        // ==========================================

        if (empty($periodo)) {
            throw new \Exception(
                'O período do alojamento não foi informado.'
            );
        }


        // ==========================================
        // 3. Procurar alojamento activo
        // ==========================================

        $alojamentoExistente = $this->alojamentoModel
            ->where('cliente_id', $clienteId)
            ->where('dominio_id', $dominioId)
            ->where('produto_id', $produtoId)
            ->where('estado', 'Activo')
            ->first();


        // ==========================================
        // 4. Se já existe → RENOVAÇÃO
        // ==========================================

        if ($alojamentoExistente) {

            $dataBase = new \DateTime(
                $alojamentoExistente['expiracao']
            );

            $novaExpiracao = $this->calcularExpiracao(
                $dataBase,
                $periodo
            );


            $this->alojamentoModel->update(
                $alojamentoExistente['id'],
                [
                    'expiracao' => $novaExpiracao->format('Y-m-d'),
                    'estado' => 'Activo'
                ]
            );


            return [
                'sucesso' => true,
                'mensagem' =>
                'Alojamento renovado com sucesso.'
            ];
        }


        // ==========================================
        // 5. Não existe → NOVO ALOJAMENTO
        // ==========================================

        $dataInicio = new \DateTime();

        $dataExpiracao = $this->calcularExpiracao(
            $dataInicio,
            $periodo
        );


        $dados = [
            'cliente_id' => $clienteId,
            'dominio_id' => $dominioId,
            'produto_id' => $produtoId,
            'inicio' => $dataInicio->format('Y-m-d'),
            'expiracao' => $dataExpiracao->format('Y-m-d'),
            'estado' => 'Activo'
        ];


        $this->alojamentoModel->insert($dados);


        return [
            'sucesso' => true,
            'mensagem' =>
            'Alojamento provisionado com sucesso.'
        ];
    }


    /**
     * Provisionamento de serviço de e-mail
     */

    private function provisionarServicoEmail($pedido, $item, $produto)
    {
        // 1. Validar domínio associado
        if (empty($item['dominio_id'])) {
            throw new \Exception(
                'O item do pedido não possui um domínio associado.'
            );
        }

        // 2. Procurar domínio
        $dominio = $this->dominioModel
            ->find($item['dominio_id']);

        if (!$dominio) {
            throw new \Exception('Domínio não encontrado.');
        }

        // 3. Validar proprietário do domínio
        if ((int) $dominio['cliente_id'] !== (int) $pedido['cliente_id']) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }

        // 4. Validar estado do domínio
        if ((int) $dominio['estado'] !== 1) {
            throw new \Exception(
                'O domínio não está activo.'
            );
        }

        // 5. Validar período
        $periodo = strtolower(trim($item['periodo'] ?? ''));

        if ($periodo === '') {
            throw new \Exception(
                'O período do serviço de email não está definido.'
            );
        }

        // Validar o período antes de modificar qualquer registo.
        $periodosPermitidos = ['1 ano', '2 anos'];

        if (!in_array($periodo, $periodosPermitidos, true)) {
            throw new \Exception(
                'Período de serviço de email não suportado: ' . $periodo
            );
        }

        // 6. Procurar serviço de email activo
        $emailExistente = $this->servicoEmailModel
            ->where('cliente_id', $pedido['cliente_id'])
            ->where('dominio_id', $item['dominio_id'])
            ->where('produto_id', $item['produto_id'])
            ->where('estado', 'Activo')
            ->first();

        // 7. Serviço existente: RENOVAÇÃO
        if ($emailExistente) {

            $hoje = new \DateTimeImmutable('today');

            $expiracaoActual = new \DateTimeImmutable(
                $emailExistente['expiracao']
            );

            // Se ainda não expirou, acrescentar tempo
            // à data de expiração existente.
            $dataBase = $expiracaoActual > $hoje
                ? $expiracaoActual
                : $hoje;

            $novaExpiracao = $this->calcularExpiracaoEmail(
                $dataBase,
                $periodo
            );

            $actualizado = $this->servicoEmailModel->update(
                $emailExistente['id'],
                [
                    'expiracao' => $novaExpiracao->format('Y-m-d'),
                    'estado' => 'Activo'
                ]
            );

            if (!$actualizado) {
                throw new \Exception(
                    'Não foi possível renovar o serviço de email.'
                );
            }

            return [
                'sucesso' => true,
                'mensagem' => 'Serviço de email renovado com sucesso.',
                'servico_email_id' => $emailExistente['id'],
                'inicio' => $emailExistente['inicio'],
                'expiracao_anterior' => $emailExistente['expiracao'],
                'expiracao' => $novaExpiracao->format('Y-m-d')
            ];
        }

        // 8. Serviço inexistente: NOVO PROVISIONAMENTO
        $inicio = new \DateTimeImmutable('today');

        $expiracao = $this->calcularExpiracaoEmail(
            $inicio,
            $periodo
        );

        $dadosEmail = [
            'cliente_id' => $pedido['cliente_id'],
            'dominio_id' => $item['dominio_id'],
            'produto_id' => $item['produto_id'],
            'inicio' => $inicio->format('Y-m-d'),
            'expiracao' => $expiracao->format('Y-m-d'),
            'estado' => 'Activo'
        ];

        $servicoEmailId = $this->servicoEmailModel
            ->insert($dadosEmail);

        if (!$servicoEmailId) {
            throw new \Exception(
                'Não foi possível criar o serviço de email.'
            );
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Serviço de email provisionado com sucesso.',
            'servico_email_id' => $servicoEmailId,
            'inicio' => $inicio->format('Y-m-d'),
            'expiracao' => $expiracao->format('Y-m-d')
        ];
    }


    /**
     * Provisionamento de certificado SSL
     */
    private function provisionarCertificadoSsl($pedido, $item, $produto)
    {
        // ==========================================
        // 1. VERIFICAR DOMÍNIO ASSOCIADO
        // ==========================================

        if (empty($item['dominio_id'])) {
            throw new \Exception(
                'O item do pedido não possui um domínio associado.'
            );
        }


        // ==========================================
        // 2. PROCURAR DOMÍNIO
        // ==========================================

        $dominio = $this->dominioModel
            ->find($item['dominio_id']);

        if (!$dominio) {
            throw new \Exception(
                'Domínio não encontrado.'
            );
        }


        // ==========================================
        // 3. CONFIRMAR CLIENTE
        // ==========================================

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }


        // ==========================================
        // 4. VERIFICAR DOMÍNIO ACTIVO
        // ==========================================

        if (isset($dominio['estado']) && $dominio['estado'] != 1) {
            throw new \Exception(
                'O domínio não está activo. Não é possível activar o certificado SSL.'
            );
        }


        // ==========================================
        // 5. VERIFICAR PERÍODO
        // ==========================================

        if (empty($item['periodo'])) {
            throw new \Exception(
                'O período do certificado SSL não está definido no item do pedido.'
            );
        }

        $periodo = trim(
            strtolower($item['periodo'])
        );


        // ==========================================
        // 6. DATA DE INÍCIO
        // ==========================================

        $inicio = new \DateTime();


        // ==========================================
        // 7. CALCULAR EXPIRAÇÃO
        // ==========================================

        $expiracao = clone $inicio;

        switch ($periodo) {

            case '1 ano':

                $expiracao->modify('+1 year');

                break;


            case '2 anos':

                $expiracao->modify('+2 years');

                break;


            default:

                throw new \Exception(
                    'Período de certificado SSL não suportado: '
                        . $item['periodo']
                );
        }


        // ==========================================
        // 8. FORMATAR DATAS
        // ==========================================

        $dataInicio = $inicio->format('Y-m-d');

        $dataExpiracao = $expiracao->format('Y-m-d');


        // ==========================================
        // 9. VERIFICAR CERTIFICADO EXISTENTE
        // ==========================================

        $certificadoExistente = $this->certificadoSslModel
            ->where('cliente_id', $pedido['cliente_id'])
            ->where('dominio_id', $item['dominio_id'])
            ->where('produto_id', $item['produto_id'])
            ->where('estado', 'Activo')
            ->first();


        if ($certificadoExistente) {
            throw new \Exception(
                'Já existe um certificado SSL activo para este domínio.'
            );
        }


        // ==========================================
        // 10. CRIAR CERTIFICADO SSL
        // ==========================================

        $dadosCertificado = [
            'cliente_id' => $pedido['cliente_id'],
            'dominio_id' => $item['dominio_id'],
            'produto_id' => $item['produto_id'],
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao,
            'estado' => 'Activo'
        ];


        $certificadoId = $this->certificadoSslModel
            ->insert($dadosCertificado);


        // ==========================================
        // 11. CONFIRMAR CRIAÇÃO
        // ==========================================

        if (!$certificadoId) {
            throw new \Exception(
                'Não foi possível criar o certificado SSL.'
            );
        }


        // ==========================================
        // 12. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => true,
            'mensagem' => 'Certificado SSL provisionado com sucesso.',
            'certificado_ssl_id' => $certificadoId,
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao
        ];
    }
    /**
     * Provisionamento de Serviço VPS
     */
    private function provisionarVps($pedido, $item, $produto)
    {
        // ==========================================
        // 1. VERIFICAR CLIENTE
        // ==========================================

        if (empty($pedido['cliente_id'])) {
            throw new \Exception(
                'O pedido não possui um cliente associado.'
            );
        }


        // ==========================================
        // 2. VERIFICAR PRODUTO
        // ==========================================

        if (empty($item['produto_id'])) {
            throw new \Exception(
                'O item do pedido não possui um produto associado.'
            );
        }


        // ==========================================
        // 3. VERIFICAR PERÍODO
        // ==========================================

        if (empty($item['periodo'])) {
            throw new \Exception(
                'O período da hospedagem VPS não está definido no item do pedido.'
            );
        }

        $periodo = trim(
            strtolower($item['periodo'])
        );


        // ==========================================
        // 4. DATA DE INÍCIO
        // ==========================================

        $inicio = new \DateTime();


        // ==========================================
        // 5. CALCULAR EXPIRAÇÃO
        // ==========================================

        $expiracao = clone $inicio;

        switch ($periodo) {

            case '1 ano':

                $expiracao->modify('+1 year');

                break;


            case '2 anos':

                $expiracao->modify('+2 years');

                break;


            default:

                throw new \Exception(
                    'Período de hospedagem VPS não suportado: '
                        . $item['periodo']
                );
        }


        // ==========================================
        // 6. FORMATAR DATAS
        // ==========================================

        $dataInicio = $inicio->format('Y-m-d');

        $dataExpiracao = $expiracao->format('Y-m-d');


        // ==========================================
        // 7. VERIFICAR VPS ACTIVO EXISTENTE
        // ==========================================

        $vpsExistente = $this->hospedagemVpsModel
            ->where('cliente_id', $pedido['cliente_id'])
            ->where('produto_id', $item['produto_id'])
            ->where('estado', 'Activo')
            ->first();

        if ($vpsExistente) {
            throw new \Exception(
                'Já existe uma hospedagem VPS activa para este cliente e produto.'
            );
        }


        // ==========================================
        // 8. CRIAR VPS
        // ==========================================

        $dadosVps = [
            'cliente_id' => $pedido['cliente_id'],
            'produto_id' => $item['produto_id'],
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao,
            'estado' => 'Activo'
        ];


        $vpsId = $this->hospedagemVpsModel
            ->insert($dadosVps);


        // ==========================================
        // 9. CONFIRMAR CRIAÇÃO
        // ==========================================

        if (!$vpsId) {
            throw new \Exception(
                'Não foi possível criar a hospedagem VPS.'
            );
        }


        // ==========================================
        // 10. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => true,
            'mensagem' => 'Hospedagem VPS provisionada com sucesso.',
            'hospedagem_vps_id' => $vpsId,
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao
        ];
    }
    /**
     * Provisionar Website
     */
    private function provisionarWebsite($pedido, $item, $produto)
    {
        // ==========================================
        // 1. VERIFICAR CLIENTE
        // ==========================================

        if (empty($pedido['cliente_id'])) {
            throw new \Exception(
                'O pedido não possui um cliente associado.'
            );
        }


        // ==========================================
        // 2. VERIFICAR PRODUTO
        // ==========================================

        if (empty($item['produto_id'])) {
            throw new \Exception(
                'O item do pedido não possui um produto associado.'
            );
        }


        // ==========================================
        // 3. VERIFICAR PERÍODO/PRAZO
        // ==========================================

        if (empty($item['periodo'])) {
            throw new \Exception(
                'O período do website não está definido no item do pedido.'
            );
        }

        $periodo = trim(
            strtolower($item['periodo'])
        );


        // ==========================================
        // 4. DATA DE INÍCIO
        // ==========================================

        $inicio = new \DateTime();


        // ==========================================
        // 5. CALCULAR PRAZO
        // ==========================================

        $prazo = clone $inicio;

        switch ($periodo) {

            case '1 ano':

                $prazo->modify('+1 year');

                break;


            case '2 anos':

                $prazo->modify('+2 years');

                break;


            default:

                throw new \Exception(
                    'Período de website não suportado: '
                        . $item['periodo']
                );
        }


        // ==========================================
        // 6. FORMATAR DATAS
        // ==========================================

        $dataInicio = $inicio->format('Y-m-d');

        $dataPrazo = $prazo->format('Y-m-d');


        // ==========================================
        // 7. VERIFICAR WEBSITE ACTIVO EXISTENTE
        // ==========================================

        $websiteExistente = $this->websiteModel
            ->where('cliente_id', $pedido['cliente_id'])
            ->where('produto_id', $item['produto_id'])
            ->where('estado', 'Activo')
            ->first();

        if ($websiteExistente) {
            throw new \Exception(
                'Já existe um website activo para este cliente e produto.'
            );
        }


        // ==========================================
        // 8. DEFINIR SITE
        // ==========================================

        if (empty($item['dominio_id'])) {
            throw new \Exception(
                'O item do pedido não possui um domínio associado.'
            );
        }

        $dominio = $this->dominioModel
            ->find($item['dominio_id']);

        if (!$dominio) {
            throw new \Exception(
                'Domínio não encontrado.'
            );
        }

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }

        if (isset($dominio['estado']) && $dominio['estado'] != 1) {
            throw new \Exception(
                'O domínio não está activo.'
            );
        }

        $site = $dominio['nome'];


        // ==========================================
        // 9. CRIAR WEBSITE
        // ==========================================

        $dadosWebsite = [
            'cliente_id' => $pedido['cliente_id'],
            'produto_id' => $item['produto_id'],
            'site' => $site,
            'inicio' => $dataInicio,
            'prazo' => $dataPrazo,
            'estado' => 'Activo'
        ];


        $websiteId = $this->websiteModel
            ->insert($dadosWebsite);


        // ==========================================
        // 10. CONFIRMAR CRIAÇÃO
        // ==========================================

        if (!$websiteId) {
            throw new \Exception(
                'Não foi possível criar o website.'
            );
        }


        // ==========================================
        // 11. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => true,
            'mensagem' => 'Website provisionado com sucesso.',
            'website_id' => $websiteId,
            'site' => $site,
            'inicio' => $dataInicio,
            'prazo' => $dataPrazo
        ];
    }
    public function processarPedido($pedidoId)
    {
        // ==========================================
        // 1. PROCURAR O PEDIDO
        // ==========================================

        $pedido = $this->pedidoModel->find($pedidoId);

        if (!$pedido) {
            return [
                'sucesso' => false,
                'mensagem' => 'Pedido não encontrado.'
            ];
        }


        // ==========================================
        // 2. VERIFICAR SE O PEDIDO ESTÁ PAGO
        // ==========================================

        if (
            $pedido['estado'] !== 'Pago' &&
            $pedido['estado'] !== 'Processando'
        ) {
            return [
                'sucesso' => false,
                'mensagem' => 'O pedido ainda não está pago.'
            ];
        }


        // ==========================================
        // 3. BUSCAR OS ITENS DO PEDIDO
        // ==========================================

        $itens = $this->itemPedidoModel
            ->where('pedido_id', $pedidoId)
            ->findAll();

        if (empty($itens)) {
            return [
                'sucesso' => false,
                'mensagem' => 'O pedido não possui itens.'
            ];
        }


        // ==========================================
        // 4. RESULTADOS
        // ==========================================

        $resultados = [];

        $todosConcluidos = true;


        // ==========================================
        // 5. PROCESSAR CADA ITEM
        // ==========================================

        foreach ($itens as $item) {

            try {

                // ----------------------------------
                // 5.1 Identificar tipo de serviço
                // ----------------------------------

                $tipoServico = $this->identificarTipoServico(
                    $item
                );


                // ----------------------------------
                // 5.2 Verificar provisionamento existente
                // ----------------------------------

                $provisionamento = $this->provisionamentoModel
                    ->where('item_pedido_id', $item['id'])
                    ->first();


                // ----------------------------------
                // 5.3 Se já estiver concluído,
                // não executar novamente
                // ----------------------------------

                if ($provisionamento) {

                    if ($provisionamento['estado'] === 'Concluido') {

                        $resultados[] = [
                            'item_pedido_id' => $item['id'],
                            'provisionamento_id' => $provisionamento['id'],
                            'tipo_servico' => $provisionamento['tipo_servico'],
                            'sucesso' => true,
                            'mensagem' => 'Item já foi provisionado anteriormente.'
                        ];

                        continue;
                    }

                    // Se existe mas não está concluído,
                    // utilizamos o provisionamento existente.

                    $provisionamentoId = $provisionamento['id'];
                } else {

                    // ----------------------------------
                    // 5.4 Criar novo provisionamento
                    // ----------------------------------

                    $provisionamentoId =
                        $this->provisionamentoModel->insert([
                            'pedido_id' => $pedidoId,
                            'item_pedido_id' => $item['id'],
                            'tipo_servico' => $tipoServico,
                            'estado' => 'Pendente',
                            'mensagem' => 'Provisionamento criado automaticamente.'
                        ]);


                    if (!$provisionamentoId) {

                        throw new \Exception(
                            'Não foi possível criar o provisionamento.'
                        );
                    }
                }


                // ==================================
                // 6. EXECUTAR PROVISIONAMENTO
                // ==================================

                $resultado = $this->executar(
                    $provisionamentoId
                );


                // ==================================
                // 7. REGISTAR RESULTADO
                // ==================================

                $resultados[] = [
                    'item_pedido_id' => $item['id'],
                    'provisionamento_id' => $provisionamentoId,
                    'tipo_servico' => $tipoServico,
                    'sucesso' => $resultado['sucesso'],
                    'mensagem' => $resultado['mensagem']
                ];


                // Se algum falhar, o pedido não está
                // totalmente concluído.

                if (!$resultado['sucesso']) {
                    $todosConcluidos = false;
                }
            } catch (\Exception $e) {

                $todosConcluidos = false;

                $resultados[] = [
                    'item_pedido_id' => $item['id'],
                    'sucesso' => false,
                    'mensagem' => $e->getMessage()
                ];
            }
        }


        // ==========================================
        // 8. ACTUALIZAR ESTADO DO PEDIDO
        // ==========================================

        if ($todosConcluidos) {

            $this->pedidoModel->update(
                $pedidoId,
                [
                    'estado' => 'Concluido'
                ]
            );
        } else {

            $this->pedidoModel->update(
                $pedidoId,
                [
                    'estado' => 'Processando'
                ]
            );
        }


        // ==========================================
        // 9. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => $todosConcluidos,
            'mensagem' => $todosConcluidos
                ? 'Todos os serviços do pedido foram provisionados com sucesso.'
                : 'O pedido foi processado, mas existem serviços pendentes ou com erro.',
            'pedido_id' => $pedidoId,
            'estado_pedido' => $todosConcluidos
                ? 'Concluido'
                : 'Processando',
            'resultados' => $resultados
        ];
    }
    private function identificarTipoServico($item)
    {
        // Procurar o produto
        $produto = $this->produtoModel
            ->find($item['produto_id']);

        if (!$produto) {
            throw new \Exception(
                'Produto não encontrado.'
            );
        }

        // Procurar a categoria do produto
        $categoria = $this->categoriaModel
            ->find($produto['categoria_id']);

        if (!$categoria) {
            throw new \Exception(
                'Categoria do produto não encontrada.'
            );
        }

        // Identificar o tipo de serviço
        switch (strtolower(trim($categoria['nome']))) {

            case 'domínios':
            case 'dominios':
                return 'Dominio';

            case 'hosting':
                return 'Alojamento';

            case 'serviço de email':
            case 'servico de email':
                return 'Servico de Email';

            case 'certificado ssl':
                return 'Certificado SSL';

            case 'hospedagem vps':
                return 'Hospedagem VPS';

            case 'website':
                return 'Website';

            default:
                throw new \Exception(
                    'Categoria do produto não corresponde a um serviço provisionável.'
                );
        }
    }
    private function calcularExpiracao(\DateTime $dataBase, string $periodo)
    {
        $periodo = strtolower(trim($periodo));


        switch ($periodo) {

            case '1 ano':

                $dataBase->modify('+1 year');

                break;


            case '2 anos':

                $dataBase->modify('+2 years');

                break;


            case '3 anos':

                $dataBase->modify('+3 years');

                break;


            case '1 mês':
            case '1 mes':

                $dataBase->modify('+1 month');

                break;


            case '3 meses':

                $dataBase->modify('+3 months');

                break;


            case '6 meses':

                $dataBase->modify('+6 months');

                break;


            default:

                throw new \Exception(
                    'Período de alojamento não suportado: '
                        . $periodo
                );
        }


        return $dataBase;
    }

    private function calcularExpiracaoEmail(
        \DateTimeImmutable $dataBase,
        string $periodo
    ): \DateTimeImmutable {

        switch ($periodo) {
            case '1 ano':
                return $dataBase->modify('+1 year');

            case '2 anos':
                return $dataBase->modify('+2 years');

            default:
                throw new \Exception(
                    'Período de serviço de email não suportado: '
                        . $periodo
                );
        }
    }
    private function calcularExpiracaoDominio(
        \DateTime $dataBase,
        string $periodo
    ) {
        $periodo = strtolower(trim($periodo));

        switch ($periodo) {

            case '1 ano':
                $dataBase->modify('+1 year');
                break;

            case '2 anos':
                $dataBase->modify('+2 years');
                break;

            case '3 anos':
                $dataBase->modify('+3 years');
                break;

            case '1 mês':
            case '1 mes':
                $dataBase->modify('+1 month');
                break;

            case '3 meses':
                $dataBase->modify('+3 months');
                break;

            case '6 meses':
                $dataBase->modify('+6 months');
                break;

            default:
                throw new \Exception(
                    'Período de domínio não suportado: '
                        . $periodo
                );
        }

        return $dataBase;
    }
}
