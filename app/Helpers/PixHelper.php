<?php

namespace App\Helpers;

use Exception;
use App\Models\Integration;
use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;
use Gerencianet\Endpoints;

// Edimilson Assis - Desenvolvedor
// 75e4199575f0819c5d4ec469c9232bbc
// Client_Id_b80de2caf31c4dfa5e47dd4a016da4b03d2c6fc7
// Client_Secret_91a0498f3638db8b7b21bad1db9659dd115f16be

class PixHelper
{
    private $business_id = null;
    private $sandbox = false;

    private array $options = [];
    private array $body = [];
    private string $webhook_url;

    private object $integration;

    private ?Endpoints $gerencianet;

    public function __construct($business_id)
    {
        $this->business_id = $business_id;
    }

    public function &getIntegration()
    {
        if (!isset($this->integration)) {
            $this->integration = (object) Integration::where('business_id', $this->business_id)
                ->where('integration', 'efi')
                ->firstOrFail()
                ->toArray();
        }

        return $this->integration;
    }

    public function genQRLoc($loc_id)
    {
        $api = $this->getApi();
        return $api->pixGenerateQRCode(['id' => $loc_id]);
    }

    public function getApi()
    {
        if (!isset($this->gerencianet))
            $this->gerencianet = Gerencianet::getInstance(
                $this->getOptions()
            );
        return $this->gerencianet;
    }

    public function create(): object
    {
        $body        = $this->getBody();
        $integration = $this->getIntegration();

        if (empty($body['valor']['original']))
            throw new Exception('Defina o valor');

        if (empty($body['solicitacaoPagador']))
            throw new Exception('Defina a solicitação');

        // if (empty($body['devedor']))
        //     throw new Exception('Nenhum cliente definido');

        if (!$integration->pix_key ?? null)
            $this->getKey();

        try {
            $api = $this->getApi();
            $pix = $api->pixCreateImmediateCharge([], $body);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $pix['qrcode'] = $this->genQRLoc($pix['loc']['id']);

        if (!empty($pix['txid']) and !empty($integration->pix_split_plan))
            $this->splitLink($pix['txid'], $integration->pix_split_plan);

        return json_decode(json_encode($pix));
    }

    public function genQRCode(int $loc_id)
    {
        $api = $this->getApi();

        return $api->pixGenerateQRCode(['id' => $loc_id]);
    }

    public function detailByTxID(string $txid)
    {
        $api = $this->getApi();

        $pix = $api->pixDetailCharge(['txid' => $txid]);

        return json_decode(json_encode($pix));
    }

    public function genQRCodeTxID(string $txid)
    {
        $pix = $this->detailByTxID($txid);

        $pix->qrcode = self::genQRCode($pix->loc->id);

        return json_decode(json_encode($pix));
    }

    public function setDescription(string $texto)
    {
        $body                       = &$this->getBody();
        $body['solicitacaoPagador'] = strlen($texto) >= 140 ? (substr($texto, 0, 137) . '...') : $texto;
    }

    public function setAmount($valor)
    {
        $body  = &$this->getBody();
        $valor = number_format($valor, 2, '.', '');

        if ($valor < 0.01)
            throw new Exception('O valor do PIX tem que ser pelo menos R$ 0.01');

        $body['valor']['original'] = $valor;
    }

    public function setDevedor(?string $nome, ?string $cpf_cnpj)
    {
        $body = &$this->getBody();

        $devedor = [
            'nome' => $nome
        ];

        $cpf_cnpj = preg_replace('/[^0-9]/', '', $cpf_cnpj);

        if (strlen($cpf_cnpj) == 11)
            $devedor['cpf'] = $cpf_cnpj;
        else
            $devedor['cnpj'] = $cpf_cnpj;

        if (empty($devedor['nome']))
            throw new Exception('Nome do cliente está incorreto');

        if (empty($devedor['cpf']) and empty($devedor['cnpj']))
            throw new Exception('Documento do cliente está incorreto');

        return $body['devedor'] = $devedor;
    }

    public function getKey()
    {
        $api     = $this->getApi();
        $options = $this->getOptions();

        if ($options['sandbox'])
            throw new Exception('Não é possível listar Chaves no modo Sandbox');

        try {
            $chaves = $this->getKeys();
        } catch (Exception $th) {
            throw new Exception('Não conseguimos permissão para obter nenhuma chaves PIX');
        }

        try {
            $chave = $chaves[0] ?? $api->pixCreateEvp([])['chave'];
        } catch (Exception $th) {
            throw new Exception('Não conseguimos permissão para gerar uma Chave PIX');
        }

        $this->setKey($chave);

        // $this->configWebhook();

        return $chave;
    }

    public function configWebhookURL()
    {
        $api = $this->getApi();

        $integration = $this->getIntegration();
        $webhook_url = $this->getWebhookUrl();

        return $api->pixConfigWebhook(
            ['chave' => $integration->pix_key],
            ['webhookUrl' => $webhook_url]
        );
    }

    public function getWebhookUrl()
    {
        if (!isset($this->webhook_url))
            throw new Exception('Defina a URL de notificações ::setWebhookUrl');

        return $this->webhook_url;
    }

    public function setWebhookUrl($url)
    {
        $this->webhook_url = $url;
    }

    public function setKey($key)
    {
        $integration          = &$this->getIntegration();
        $integration->pix_key = $key;
        return $key;
    }

    public function getKeys()
    {
        $api = $this->getApi();

        try {
            if (!$chaves = $api->pixListEvp([]))
                throw new Exception('Não foi possível obter a lista de chaves');
        } catch (GerencianetException $e) {
            throw new Exception($e->error);
        }

        return $chaves['chaves'];
    }

    public function &getBody()
    {
        if (empty($this->body))
            $this->body = $this->newBody();

        return $this->body;
    }

    public function &getOptions()
    {
        if (empty($this->options))
            $this->options = $this->newOptions();

        return $this->options;
    }

    private function newOptions()
    {
        $integration = $this->getIntegration();

        $options = [
            "debug"         => false,
            'sandbox'       => $this->sandbox,
            "timeout"       => 30,
            'client_id'     => &$integration->key_client_id,
            'client_secret' => &$integration->key_client_secret,
            'pix_cert'      => realpath(storage_path('app/certificates') . '/' . $integration->certificate),
            'headers'       => [
                'x-skip-mtls-checking' => 'true' // IMPORTANTE PRA GERAR NOTIFICAÇÃO
            ],
        ];

        if (!file_exists($options['pix_cert']))
            throw new Exception('Certificado não encontrado. Cadastrar um novo Certificado em configurações');

        if (empty($options['client_id']) or empty($options['client_secret']))
            throw new Exception('Erro interno ao localizar conta do banco EFI');

        return $options;
    }

    private function newBody()
    {
        $integration = $this->getIntegration();

        if ($this->sandbox)
            $integration->pix_key = 'd2b0b0b0-0b0b-0b0b-0b0b-0b0b0b0b0b0b';

        $body = [
            'calendario'         => [
                'expiracao' => 3600 * 24 * 30
            ],
            'chave'              => &$integration->pix_key,
            'valor'              => [
                'original' => null
            ],
            'solicitacaoPagador' => null
        ];

        return $body;
    }

    public function getPixList(string $fromDate = 'now -1 day', string $toData = 'now')
    {
        $params = [
            "inicio" => date(DATE_RFC3339, strtotime($fromDate)),
            "fim"    => date(DATE_RFC3339, strtotime($toData)),
            // "status" => "CONCLUIDA", // "ATIVA","CONCLUIDA", "REMOVIDA_PELO_USUARIO_RECEBEDOR", "REMOVIDA_PELO_PSP"
            // "cpf" => "12345678909", // Filter by payer's CPF. It cannot be used at the same time as the CNPJ.
            // "cnpj" => "12345678909", // Filter by payer's CNPJ. It cannot be used at the same time as the CPF.
            // "paginacao.paginaAtual" => 1,
            // "paginacao.itensPorPagina" => 10
        ];

        try {
            $api      = $this->getApi();
            $response = $api->pixListCharges($params);

            return $response;
        } catch (GerencianetException $e) {
            throw new Exception($e->error);
        }
    }

    public function splitLink(string $txid, string $splitConfigId)
    {
        $params = [
            "txid"          => $txid,
            "splitConfigId" => $splitConfigId
        ];

        try {
            $api      = $this->getApi();
            $response = $api->pixSplitLinkCharge($params);

            return $response;
        } catch (GerencianetException $e) {
            throw new Exception($e->error);
        }
    }

    public function splitConfig(float $partnerPercent, string $splitConfigId = null)
    {
        $partnerPercent  = number_format($partnerPercent, 2);
        $transferPercent = number_format(100 - $partnerPercent, 2);

        $body = [
            "descricao"  => "Private Sistemas - Plan 1",
            "lancamento" => [
                "imediato" => true
            ],
            "split"      => [
                "divisaoTarifa" => "assumir_total",
                //"assumir_total", "proporcional"
                "minhaParte"    => [
                    "tipo"  => "porcentagem",
                    "valor" => "$partnerPercent"
                ],
                "repasses"      => [
                    [
                        "tipo"       => "porcentagem",
                        "valor"      => "$transferPercent",
                        "favorecido" => [
                            "cpf"   => "70036923176",
                            "conta" => "4299353"
                        ]
                    ]
                ]
            ]
        ];

        try {
            $api = $this->getApi();

            $params = [];

            if (!empty($splitConfigId)) {
                $params["id"] = $splitConfigId;
                return $api->pixSplitConfigId($params, $body);
            }

            return $api->pixSplitConfig($params, $body);
        } catch (GerencianetException $e) {
            throw new Exception('Ocorreu um erro ao configurar o Split de Pagamento');
        }
    }
}
