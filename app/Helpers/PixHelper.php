<?php

namespace App\Helpers;

use Exception;
use App\Models\Integration;
use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;
use Gerencianet\Endpoints;

class PixHelper
{
    private $business_id = null;
    private $sandbox = false;

    private array $options = [];
    private array $body = [];
    private object $conta;

    private string $webhook_url;

    private ?Endpoints $gerencianet;

    public function __construct($business_id)
    {
        $this->business_id = $business_id;
    }

    public function &getConta()
    {
        if (!isset($this->conta)) {
            $this->conta = (object) Integration::where('business_id', $this->business_id)
                ->where('integration', 'efi')
                ->firstOrFail()
                ->toArray();
        }

        return $this->conta;
    }

    public function gerarQRLoc($loc_id)
    {
        $api = $this->getGerencinet();
        return $api->pixGenerateQRCode(['id' => $loc_id]);
    }

    public function getGerencinet()
    {
        if (!isset($this->gerencianet))
            $this->gerencianet = Gerencianet::getInstance(
                $this->getOptions()
            );

        return $this->gerencianet;
    }

    public function gerar(): object
    {
        $body  = $this->getBody();
        $conta = $this->getConta();

        if (empty($body['valor']['original']))
            throw new Exception('Defina o valor');

        if (empty($body['solicitacaoPagador']))
            throw new Exception('Defina a solicitação');

        if (empty($body['devedor']))
            throw new Exception('Nenhum cliente definido');

        if (!$conta->key_pix ?? null)
            $this->gerarChave();

        try {
            $api = $this->getGerencinet();
            $pix = $api->pixCreateImmediateCharge([], $body);
        } catch (Exception $e) {
            throw new Exception('Erro interno ao Gerar PIX. ' . $e->getMessage());
        }

        $pix['qrcode'] = $this->gerarQRLoc($pix['loc']['id']);

        return json_decode(json_encode($pix));
    }

    public function generateQRCode(int $loc_id)
    {
        $api = $this->getGerencinet();

        return $api->pixGenerateQRCode(['id' => $loc_id]);
    }

    public function consultarTxID(string $txid)
    {
        $api = $this->getGerencinet();

        $pix = $api->pixDetailCharge(['txid' => $txid]);

        return json_decode(json_encode($pix));
    }

    public function generateQRCodeTxID(string $txid)
    {
        $pix = $this->consultarTxID($txid);

        $pix->qrcode = self::generateQRCode($pix->loc->id);

        return json_decode(json_encode($pix));
    }

    public function setSolicitacao(string $texto)
    {
        $body                       = &$this->getBody();
        $body['solicitacaoPagador'] = strlen($texto) >= 140 ? (substr($texto, 0, 137) . '...') : $texto;
    }

    public function setValor($valor)
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

    public function gerarChave()
    {
        $api     = $this->getGerencinet();
        $options = $this->getOptions();

        if ($options['sandbox'])
            throw new Exception('Não é possível listar Chaves no modo Sandbox');

        try {
            $chaves = $this->listarChaves();
        } catch (Exception $th) {
            throw new Exception('Não conseguimos permissão para obter nenhuma chaves PIX');
        }

        try {
            $chave = $chaves[0] ?? $api->pixCreateEvp([])['chave'];
        } catch (Exception $th) {
            throw new Exception('Não conseguimos permissão para gerar uma Chave PIX');
        }

        $this->setChave($chave);

        $this->configWebhook();

        return $chave;
    }

    public function updateWebhookURL()
    {
        $api = $this->getGerencinet();

        $conta       = $this->getConta();
        $webhook_url = $this->getWebhookUrl();

        return $api->pixConfigWebhook(
            ['chave' => $conta->key_pix],
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

    public function setChave($chave)
    {
        $conta          = &$this->getConta();
        $conta->key_pix = $chave;
        return $chave;
    }

    public function listarChaves()
    {
        $api = $this->getGerencinet();

        try {
            $chaves = $api->pixListEvp([]) ?: throw new Exception('Não foi possível obter a lista de chaves');
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
        $conta = $this->getConta();

        // $conta->CNPJ = preg_replace('/^[0-9]/', '', $conta->CNPJ ?? '');
        // dd($infoCertificado->);

        $options = [
            "debug"         => false,
            'sandbox'       => false,
            "timeout"       => 30,
            'partner_token' => 'f0f2abeb04f61409150f93b60d1763c7',
            'client_id'     => &$conta->key_client_id,
            'client_secret' => &$conta->key_client_secret,
            'pix_cert'      => realpath(storage_path('app/certificates') . '/' . $conta->certificate),
            'headers'       => [
                'x-skip-mtls-checking' => 'true' // IMPORTANTE PRA GERAR NOTIFICAÇÃO
            ],
        ];

        if (!file_exists($options['pix_cert']))
            throw new Exception('Certificado não encontrado. Cadastrar um novo Certificado em configurações');

        if (empty($options['client_id']) or empty($options['client_secret']))
            throw new Exception('Erro interno ao localizar Conta');

        return $options;
    }

    private function newBody()
    {
        $conta = $this->getConta();

        $body = [
            'calendario'         => [
                'expiracao' => 3600 * 24 * 30
            ],
            'chave'              => &$conta->key_pix,
            'valor'              => [
                'original' => null
            ],
            'solicitacaoPagador' => null
        ];

        return $body;
    }
}
