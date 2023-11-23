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

class PixCobrancaCalendario
{
    public string $criacao;
    public int $expiracao;
}

class PixCobrancaLoc
{
    public int $id;
    public string $location;
    public string $tipoCob;
    public string $criacao;
}

class PixCobrancaValor
{
    public string $original;
}


class PixCobrancaQrcode
{
    public string $qrcode;
    public string $imagemQrcode;
    public string $linkVisualizacao;
}

class PixCobrancaResponse
{
    public PixCobrancaCalendario $calendario;
    public PixCobrancaQrcode $qrcode;
    public string $txid;
    public int $revisao;
    public PixCobrancaLoc $loc;
    public string $location;
    public string $status;
    public PixCobrancaValor $valor;
    public string $chave;
    public string $solicitacaoPagador;

    public function __construct(array $pix)
    {
        $pix = json_decode(json_encode($pix));

        $this->calendario = new PixCobrancaCalendario();
        $this->qrcode     = new PixCobrancaQrcode();
        $this->valor      = new PixCobrancaValor();
        $this->loc        = new PixCobrancaLoc();

        $this->calendario->criacao   = $pix->calendario->criacao;
        $this->calendario->expiracao = $pix->calendario->expiracao;

        $this->loc->id       = $pix->loc->id;
        $this->loc->location = $pix->loc->location;
        $this->loc->tipoCob  = $pix->loc->tipoCob;
        $this->loc->criacao  = $pix->loc->criacao;

        $this->qrcode->qrcode           = $pix->qrcode->qrcode;
        $this->qrcode->imagemQrcode     = $pix->qrcode->imagemQrcode;
        $this->qrcode->linkVisualizacao = $pix->qrcode->linkVisualizacao;

        $this->txid               = $pix->txid;
        $this->revisao            = $pix->revisao;
        $this->location           = $pix->location;
        $this->status             = $pix->status;
        $this->valor->original    = $pix->valor->original;
        $this->chave              = $pix->chave;
        $this->solicitacaoPagador = $pix->solicitacaoPagador;
    }

    public function isPaid()
    {
        return $this->status == 'CONCLUIDA';
    }
}

class PixHelper
{
    private $business_id = null;
    private $sandbox = false;

    private array $options = [];
    private array $body = [];

    private ?Integration $integration;

    private ?Endpoints $gerencianet;

    public function __construct($business_id)
    {
        $this->business_id = $business_id;
    }

    public function &getIntegration()
    {
        if (!isset($this->integration)) {
            $this->integration = Integration::where('business_id', $this->business_id)
                ->where('integration', 'efi')
                ->firstOrFail();
        }

        return $this->integration;
    }

    public static function isPixEnabled(int $business_id)
    {
        $integration = Integration::where('business_id', $business_id)
            ->where('integration', 'efi')
            ->firstOrFail();

        return (!empty($integration->key_client_id) and !empty($integration->key_client_secret));

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

    public function create(): PixCobrancaResponse
    {
        $body        = $this->getBody();
        $integration = $this->getIntegration();

        if (empty($body['valor']['original']))
            throw new Exception('Defina o valor');

        if (empty($body['solicitacaoPagador']))
            throw new Exception('Defina a solicitação');

        if (!$integration->pix_key ?? null)
            $body['chave'] = $this->getKey();

        $api = $this->getApi();
        $pix = $api->pixCreateImmediateCharge([], $body);

        $pix['qrcode'] = $this->genQRLoc($pix['loc']['id']);

        if (!empty($pix['txid']) and !empty($integration->pix_split_plan))
            $this->splitLink($pix['txid'], $integration->pix_split_plan);

        // dd($pix);

        return new PixCobrancaResponse($pix);
    }

    public function genQRCode(int $loc_id)
    {
        $api = $this->getApi();

        return $api->pixGenerateQRCode(['id' => $loc_id]);
    }

    public function detailByTxID(string $txid): PixCobrancaResponse
    {
        $api = $this->getApi();

        $pix = $api->pixDetailCharge(['txid' => $txid]);

        $pix['qrcode'] = $this->genQRLoc($pix['loc']['id']);

        return new PixCobrancaResponse($pix);
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
        } catch (Exception $e) {
            throw new Exception('Não conseguimos permissão para gerar uma Chave PIX');
        }


        $this->setKey($chave);

        return (string) $chave;
    }

    public function configWebhookURL(string $pix_key, string $webhook_url)
    {
        $api = $this->getApi();

        return $api->pixConfigWebhook(
            ['chave' => $pix_key],
            ['webhookUrl' => $webhook_url]
        );
    }

    public function setKey(string $key)
    {
        $integration          = &$this->getIntegration();
        $integration->pix_key = $key;
        $integration->save();

        $webhook_url = url('/efi/pix/webhook/' . $this->business_id) . '/';

        logger('PIX - Nova Chave', [
            'key'         => $key,
            'webhook_url' => $webhook_url,
        ]);

        $webhook = $this->configWebhookURL(
            $key,
            $webhook_url
        );

        logger('Definindo WebHook - Response', [
            $webhook
        ]);

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
            'client_id'     => $integration->key_client_id,
            'client_secret' => $integration->key_client_secret,
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
            'chave'              => $integration->pix_key,
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

    public function splitConfig(float $taxPercent = 0.70, string $splitConfigId = null)
    {
        $partnerPercent  = number_format(100 - $taxPercent, 2);
        $transferPercent = number_format($taxPercent, 2);

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
                            // vitoria
                            "cpf"   => "03665578230",
                            // vitoria
                            "conta" => "4919289"
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
