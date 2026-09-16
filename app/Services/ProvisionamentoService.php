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
                    $resultado = $this->provisionarDominio(
                        $pedido,
                        $item,
                        $produto
                    );
                    break;

                case 'Alojamento':
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
    private function provisionarDominio($pedido, $item, $produto)
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
        // 3. CONFIRMAR PROPRIETÁRIO
        // ==========================================

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }


        // ==========================================
        // 4. VERIFICAR PERÍODO
        // ==========================================

        if (empty($item['periodo'])) {
            throw new \Exception(
                'O período do domínio não está definido no item do pedido.'
            );
        }

        $periodo = trim(
            strtolower($item['periodo'])
        );


        // ==========================================
        // 5. DATA DE INÍCIO
        // ==========================================

        $inicio = new \DateTime();


        // ==========================================
        // 6. CALCULAR EXPIRAÇÃO
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
                    'Período de domínio não suportado: '
                        . $item['periodo']
                );
        }


        // ==========================================
        // 7. FORMATAR DATAS
        // ==========================================

        $dataInicio = $inicio->format('Y-m-d');

        $dataExpiracao = $expiracao->format('Y-m-d');


        // ==========================================
        // 8. VERIFICAR SE JÁ ESTÁ ACTIVO
        // ==========================================

        if (isset($dominio['estado']) && $dominio['estado'] == 1) {
            throw new \Exception(
                'O domínio já está activo.'
            );
        }


        // ==========================================
        // 9. ACTIVAR DOMÍNIO
        // ==========================================

        $dadosDominio = [
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao,
            'renovacao_auto' => 1,
            'estado' => 1
        ];


        $actualizado = $this->dominioModel
            ->update(
                $dominio['id'],
                $dadosDominio
            );


        // ==========================================
        // 10. CONFIRMAR ACTUALIZAÇÃO
        // ==========================================

        if (!$actualizado) {
            throw new \Exception(
                'Não foi possível activar o domínio.'
            );
        }


        // ==========================================
        // 11. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => true,
            'mensagem' => 'Domínio provisionado com sucesso.',
            'dominio_id' => $dominio['id'],
            'nome' => $dominio['nome'],
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao
        ];
    }


    /**
     * Provisionamento de alojamento
     */
    private function provisionarAlojamento($pedido, $item, $produto)
    {
        // ==========================================
        // 1. VERIFICAR DOMÍNIO
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


        // ==========================================
        // 2. CONFIRMAR QUE O DOMÍNIO PERTENCE
        //    AO CLIENTE DO PEDIDO
        // ==========================================

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }


        // ==========================================
        // 3. VERIFICAR SE O DOMÍNIO ESTÁ ACTIVO
        // ==========================================

        if (isset($dominio['estado']) && $dominio['estado'] != 1) {
            throw new \Exception(
                'O domínio não está activo.'
            );
        }


        // ==========================================
        // 4. VERIFICAR PERÍODO
        // ==========================================

        if (empty($item['periodo'])) {
            throw new \Exception(
                'O período do alojamento não está definido no item do pedido.'
            );
        }

        $periodo = trim(
            strtolower($item['periodo'])
        );


        // ==========================================
        // 5. DATA DE INÍCIO
        // ==========================================

        $inicio = new \DateTime();


        // ==========================================
        // 6. CALCULAR DATA DE EXPIRAÇÃO
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
                    'Período de alojamento não suportado: '
                        . $item['periodo']
                );
        }


        // ==========================================
        // 7. FORMATAR DATAS
        // ==========================================

        $dataInicio = $inicio->format('Y-m-d');

        $dataExpiracao = $expiracao->format('Y-m-d');


        // ==========================================
        // 8. VERIFICAR SE JÁ EXISTE ALOJAMENTO
        //    ACTIVO PARA O MESMO CLIENTE E DOMÍNIO
        // ==========================================

        $alojamentoExistente = $this->alojamentoModel
            ->where('cliente_id', $pedido['cliente_id'])
            ->where('dominio_id', $item['dominio_id'])
            ->where('estado', 'Activo')
            ->first();

        if ($alojamentoExistente) {
            throw new \Exception(
                'Já existe um alojamento activo para este cliente e domínio.'
            );
        }


        // ==========================================
        // 9. CRIAR ALOJAMENTO
        // ==========================================

        $dadosAlojamento = [
            'cliente_id' => $pedido['cliente_id'],
            'produto_id' => $item['produto_id'],
            'dominio_id' => $item['dominio_id'],
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao,
            'estado' => 'Activo'
        ];


        $alojamentoId = $this->alojamentoModel
            ->insert($dadosAlojamento);


        // ==========================================
        // 10. CONFIRMAR CRIAÇÃO
        // ==========================================

        if (!$alojamentoId) {
            throw new \Exception(
                'Não foi possível criar o alojamento.'
            );
        }


        // ==========================================
        // 11. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => true,
            'mensagem' => 'Alojamento provisionado com sucesso.',
            'alojamento_id' => $alojamentoId,
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao
        ];
    }


    /**
     * Provisionamento de serviço de e-mail
     */
    private function provisionarServicoEmail($pedido, $item, $produto)
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
        // 3. CONFIRMAR QUE O DOMÍNIO PERTENCE
        //    AO CLIENTE DO PEDIDO
        // ==========================================

        if ($dominio['cliente_id'] != $pedido['cliente_id']) {
            throw new \Exception(
                'O domínio não pertence ao cliente do pedido.'
            );
        }


        // ==========================================
        // 4. CONFIRMAR QUE O DOMÍNIO ESTÁ ACTIVO
        // ==========================================

        if (isset($dominio['estado']) && $dominio['estado'] != 1) {
            throw new \Exception(
                'O domínio não está activo. Não é possível activar o serviço de email.'
            );
        }


        // ==========================================
        // 5. VERIFICAR PERÍODO
        // ==========================================

        if (empty($item['periodo'])) {
            throw new \Exception(
                'O período do serviço de email não está definido no item do pedido.'
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
                    'Período de serviço de email não suportado: '
                        . $item['periodo']
                );
        }


        // ==========================================
        // 8. FORMATAR DATAS
        // ==========================================

        $dataInicio = $inicio->format('Y-m-d');

        $dataExpiracao = $expiracao->format('Y-m-d');


        // ==========================================
        // 9. VERIFICAR SE JÁ EXISTE SERVIÇO
        //    DE EMAIL ACTIVO
        // ==========================================

        $emailExistente = $this->servicoEmailModel
            ->where('cliente_id', $pedido['cliente_id'])
            ->where('dominio_id', $item['dominio_id'])
            ->where('produto_id', $item['produto_id'])
            ->where('estado', 'Activo')
            ->first();

        if ($emailExistente) {
            throw new \Exception(
                'Já existe um serviço de email activo para este domínio.'
            );
        }


        // ==========================================
        // 10. CRIAR SERVIÇO DE EMAIL
        // ==========================================

        $dadosEmail = [
            'cliente_id' => $pedido['cliente_id'],
            'dominio_id' => $item['dominio_id'],
            'produto_id' => $item['produto_id'],
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao,
            'estado' => 'Activo'
        ];


        $servicoEmailId = $this->servicoEmailModel
            ->insert($dadosEmail);


        // ==========================================
        // 11. CONFIRMAR CRIAÇÃO
        // ==========================================

        if (!$servicoEmailId) {
            throw new \Exception(
                'Não foi possível criar o serviço de email.'
            );
        }


        // ==========================================
        // 12. RETORNAR RESULTADO
        // ==========================================

        return [
            'sucesso' => true,
            'mensagem' => 'Serviço de email provisionado com sucesso.',
            'servico_email_id' => $servicoEmailId,
            'inicio' => $dataInicio,
            'expiracao' => $dataExpiracao
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
}
