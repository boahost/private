<?php
namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\Business;
use App\Models\Transaction;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;
use App\Models\Ibpt;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeService{

	private $config;
	private $tools;

	public function __construct($config, $certificado){

		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado->certificado, base64_decode($certificado->senha_certificado)));
		$this->tools->model('55');

	}


    public function pegaPagamento($value){
        $firstType = $value[0]->method;
        foreach($value as $pay){
            if($pay->method != $firstType){
                return "99";
            }
        }

        if($firstType == 'cash') return  '01';
        elseif($firstType == 'credit') return  '03';
        elseif($firstType == 'debit' || $firstType == 'card') return  '04';
        elseif($firstType == 'cheque') return  '02';
        elseif($firstType == 'bank_transfer') return  '16';
        elseif($firstType == 'other') return  '99';
        elseif($firstType == 'boleto') return  '15';
        elseif($firstType == 'pix') return  '17';
        elseif($firstType == 'custom_pay_1') return  '99';
        elseif($firstType == 'custom_pay_2') return  '99';
        elseif($firstType == 'custom_pay_3') return  '99';
    }



	public function gerarNFe($venda){
		date_default_timezone_set('America/Belem');
		$business_id = request()->session()->get('user.business_id');
		// $config = Business::find($business_id);
		$config = Business::getConfig($business_id, $venda);

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00';
		$stdInNFe->Id = null;
		$stdInNFe->pk_nItem = '';
		$infNFe = $nfe->taginfNFe($stdInNFe);

		$vendaLast = $venda->lastNFe($venda);
		$lastNumero = $vendaLast;

		$stdIde = new \stdClass();
		$stdIde->cUF = $config->getcUF($config->cidade->uf);
		$stdIde->cNF = rand(11111,99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $this->retiraAcentos($venda->natureza->natureza);

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

		$stdIde->mod = 55;
		$stdIde->serie = $config->numero_serie_nfe;
		$stdIde->nNF = (int)$lastNumero+1;
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = $venda->natureza->tipo;
		if($venda->contact->cod_pais == 1058){
			$stdIde->idDest = $config->cidade->uf != $venda->contact->cidade->uf ? 2 : 1;
		}else{
			$stdIde->idDest = 3;
		}
		$stdIde->cMunFG = $config->cidade->codigo;

		$stdIde->tpImp = 1;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = $config->ambiente;
		$stdIde->finNFe = $venda->natureza->finNFe;
		$stdIde->indFinal = $venda->contact->consumidor_final;
		$stdIde->indPres = 1;
		// $stdIde->procEmi = '0';
		// $stdIde->verProc = '2.0';
		// $stdIde->dhCont = null;
		// $stdIde->xJust = null;
		if($config->ambiente == 2){
			$stdIde->indIntermed = 0;
			if($venda->pedido_ecommerce_id > 0){
				$stdIde->indIntermed = 1;
			}else{
				$stdIde->indIntermed = 0;
			}
		}
		$stdIde->procEmi = '0';
		$stdIde->verProc = '3.10.31';

		//
		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->name;

		$ie = preg_replace('/[^0-9]/', '', $config->ie);

		$stdEmit->IE = $ie;
		$stdEmit->CRT = $config->regime;

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		if(strlen($cnpj) == 11){
			$stdEmit->CPF = $cnpj;
		}else{
			$stdEmit->CNPJ = $cnpj;
		}

		// $stdEmit->IM = $ie;

		$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $config->rua;
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = "";

		$stdEnderEmit->xBairro = $config->bairro;
		$stdEnderEmit->cMun = $config->cidade->codigo;
		$stdEnderEmit->xMun = $config->cidade->nome;
		$stdEnderEmit->UF = $config->cidade->uf;

		$cep = str_replace("-", "", $config->cep);
		$cep = str_replace(".", "", $cep);

		$fone = str_replace(" ", "", $config->telefone);
		$stdEnderEmit->fone = $fone;

		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = '1058';
		$stdEnderEmit->xPais = 'BRASIL';

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);


		// DESTINATARIO
		$stdDest = new \stdClass();
		$stdDest->xNome = $venda->contact->name;

		if($venda->contact->cod_pais != 1058){
			$stdDest->indIEDest = "9";
			$stdDest->idEstrangeiro = $venda->contact->id_estrangeiro;
		}else{
			if($venda->contact->contribuinte){
				if($venda->contact->ie_rg == 'ISENTO' || $venda->contact->ie_rg == NULL){
					$stdDest->indIEDest = "2";
				}else{
					$stdDest->indIEDest = "1";
				}

			}else{
				$stdDest->indIEDest = "9";
			}

			$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->contact->cpf_cnpj);

			if(strlen($cnpj_cpf) == 14){
				$stdDest->CNPJ = $cnpj_cpf;

				$ie = preg_replace('/[^0-9]/', '', $venda->contact->ie_rg);

				$stdDest->IE = $ie;
			}
			else{

				$stdDest->CPF = $cnpj_cpf;
				if($venda->contact->ie_rg != 'ISENTO' && $venda->contact->ie_rg != ''){

					$ie = preg_replace('/[^0-9]/', '', $venda->contact->ie_rg);

					$stdDest->IE = $ie;
				}
			}
		}

		$dest = $nfe->tagdest($stdDest);

		$stdEnderDest = new \stdClass();
		$stdEnderDest->xLgr = $venda->contact->rua;
		$stdEnderDest->nro = $venda->contact->numero;
		$stdEnderDest->xCpl = "";
		$stdEnderDest->xBairro = $venda->contact->bairro;

		if($venda->contact->cod_pais == 1058){

			$stdEnderDest->cMun = $venda->contact->cidade->codigo;
			$stdEnderDest->xMun = strtoupper($venda->contact->cidade->nome);
			$stdEnderDest->UF = $venda->contact->cidade->uf;

			$cep = str_replace("-", "", $venda->contact->cep);
			$cep = str_replace(".", "", $cep);
			$stdEnderDest->CEP = $cep;
			$stdEnderDest->cPais = "1058";
			$stdEnderDest->xPais = "BRASIL";
		}else{
			$stdEnderDest->cMun = 9999999;
			$stdEnderDest->xMun = "EXTERIOR";
			$stdEnderDest->UF = "EX";
			$stdEnderDest->cPais = $venda->contact->cod_pais;
			$stdEnderDest->xPais = $venda->contact->getPais();
		}

		$enderDest = $nfe->tagenderDest($stdEnderDest);

		if($venda->contact->rua_entrega != "" && $venda->transportadora != null){
			$stdEnderEntrega = new \stdClass();
			$stdEnderEntrega->xLgr = $venda->contact->rua_entrega;
			$stdEnderEntrega->nro = $venda->contact->numero_entrega;
			$stdEnderEntrega->xBairro = $venda->contact->bairro_entrega;
			$cep_entrega = preg_replace('/[^0-9]/', '', $venda->contact->cep_entrega);

			$stdEnderEntrega->CEP = $cep_entrega;
			$stdEnderEntrega->cMun = $venda->contact->cidade_entrega->codigo;
			$stdEnderEntrega->xMun = strtoupper($venda->contact->cidade_entrega->nome);
			$stdEnderEntrega->UF = $venda->contact->cidade_entrega->uf;
			$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->contact->cpf_cnpj);

			if(strlen($cnpj_cpf) == 14){
				$stdEnderEntrega->CNPJ = $cnpj_cpf;
			}else{
				$stdEnderEntrega->CPF = $cnpj_cpf;
			}

			$enderEntrega = $nfe->tagentrega($stdEnderEntrega);
		}

		$somaProdutos = 0;
		$somaICMS = 0;
		//PRODUTOS
		$itemCont = 0;

		$totalItens = count($venda->sell_lines);
		$somaFrete = 0;
		$somaDesconto = 0;
		$somaISS = 0;
		$somaServico = 0;
		$totalDesconto = $venda->total_before_tax - $venda->final_total + $venda->valor_frete;
		$vbc = 0;
		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;
		foreach($venda->sell_lines as $i){

			$itemCont++;

			$ncm = $i->product->ncm;
			$ncm = str_replace(".", "", $ncm);

			$ibpt = Ibpt::getIBPT($config->cidade->uf, $ncm);

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;
			$stdProd->cEAN = $i->product->codigo_barras != '' ? $i->product->codigo_barras : 'SEM GTIN';
			$stdProd->cEANTrib = $i->product->codigo_barras != '' ? $i->product->codigo_barras : 'SEM GTIN';
			$stdProd->cProd = $i->product->sku;
			$stdProd->xProd = $i->product->name;

			if($i->variations){
				$stdProd->xProd .= " " . ($i->variations->name != "DUMMY" ? $i->variations->name : '');
			}
			// $ncm = $i->product->ncm;
			// $ncm = str_replace(".", "", $ncm);
			if($i->product->cst_csosn == '500' || $i->product->cst_csosn == '60'){
				$stdProd->cBenef = 'SEM CBENEF';
			}
			if($i->product->perc_iss > 0){
				$stdProd->NCM = '00';
			}else{
				$stdProd->NCM = $ncm;
			}

			if($venda->natureza->sobrescreve_cfop == 0){
				$stdProd->CFOP = $config->cidade->uf != $venda->contact->cidade->uf ?
				$i->product->cfop_externo : $i->product->cfop_interno;
			}else{
				$stdProd->CFOP = $config->cidade->uf != $venda->contact->cidade->uf ?
				$venda->natureza->cfop_saida_inter_estadual : $venda->natureza->cfop_saida_estadual;
			}

			if($venda->contact->cod_pais == 1058){
				$stdProd->uCom = $i->product->unit->short_name;
			}else{
				$stdProd->uCom = $i->product->unit->short_name == 'UNID' ? 'UN' :
				$i->product->unit->short_name;
			}
			$stdProd->qCom = $i->quantity;
			$stdProd->vUnCom = $this->format($i->unit_price);
			$stdProd->vProd = $this->format2(($i->quantity * $i->unit_price));

			if($venda->contact->cod_pais == 1058){
				if($i->product->unidade_tributavel == ''){
					$stdProd->uTrib = $i->product->unit->short_name;
				}else{
					$stdProd->uTrib = $i->product->unidade_tributavel;
				}
			}else{
				if($i->product->unidade_tributavel == ''){
					$stdProd->uTrib = $i->product->unit->short_name == 'UNID' ? 'UN' :
					$i->product->unit->short_name;
				}else{
					$stdProd->uTrib = $i->product->unidade_tributavel;
				}
			}

			if($i->product->quantidade_tributavel == 0){
				$stdProd->qTrib = $i->quantity;
			}else{
				$stdProd->qTrib = $i->product->quantidade_tributavel * $i->quantity;
			}

			$stdProd->vUnTrib = $this->format($i->unit_price);
			$stdProd->indTot = 1;
			$somaProdutos += ($i->quantity * $i->unit_price);

			$vDesc = 0;
			if($totalDesconto > 0){
				if($itemCont < sizeof($venda->sell_lines)){

                    $quantidade = (int) $i->quantity;

                    if($quantidade > 1){
                        $novoValor = (float) ($totalDesconto/$totalItens)/$quantidade;
                        $stdProd->vDesc = number_format($novoValor, 2, '.', '');
                        $somaDesconto += $vDesc = (float) $novoValor;
                    } else {
                        $stdProd->vDesc =(float) $totalDesconto/$totalItens;
                        $a = (float) $totalDesconto/$totalItens;

                        $somaDesconto += $vDesc = number_format($a, 2, '.', '');
                    }



				}else{
					$stdProd->vDesc = $somaDesconto = $vDesc = $totalDesconto - $somaDesconto;
				}
			}

			// if($totalDesconto >= 0.1){
			// 	if($itemCont < sizeof($venda->sell_lines)){
			// 		$totalVenda = $venda->final_total;

			// 		$media = (((($stdProd->vProd - $totalVenda)/$totalVenda))*100);
			// 		$media = 100 - ($media * -1);

			// 		// $tempDesc = ($totalDesconto*$media)/100;

			// 		$tempDesc = ($totalDesconto*$media)/100;
            //         if($tempDesc <= 0){
            //             dump($tempDesc);
            //             //$tempDesc = $tempDesc * -1;
            //             dd($tempDesc);
            //         }

			// 		$somaDesconto += $tempDesc;

			// 		$stdProd->vDesc = $this->format($tempDesc);
			// 	}else{
			// 		$stdProd->vDesc = $this->format($totalDesconto - $somaDesconto);
			// 	}
			// }


			if($venda->valor_frete > 0){
				$somaFrete += $vFt = $venda->valor_frete/$totalItens;
				$stdProd->vFrete = $this->format($vFt);
				//$somaProdutos += $vFt;
			}
			// return $stdProd;
			$prod = $nfe->tagprod($stdProd);


			if($i->sell_line_note != ''){
				$std = new \stdClass();
				$std->item = $itemCont;
				$std->infAdProd = $i->sell_line_note;
				$nfe->taginfAdProd($std);
			}

		//TAG IMPOSTO

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;
			if($i->product->perc_iss > 0){
				$stdImposto->vTotTrib = 0.00;
			}

			if($ibpt != null){
				$vProd = $stdProd->vProd;

				$federal = ($vProd*($ibpt->nacional_federal/100));
				$somaFederal += $federal;

				$estadual = ($vProd*($ibpt->estadual/100));
				$somaEstadual += $estadual;

				$municipal = ($vProd*($ibpt->municipal/100));
				$somaMunicipal += $municipal;
				$soma = $federal + $estadual + $municipal;

				$stdImposto->vTotTrib = $soma;
			}

			$imposto = $nfe->tagimposto($stdImposto);

			// ICMS
			if($i->product->perc_iss == 0){
				// regime normal
				if($config->regime == 3){

				//$venda->produto->CST  CST

					$stdICMS = new \stdClass();
					$stdICMS->item = $itemCont;
					// $stdICMS->orig = 0;
					$stdICMS->orig = $i->product->origem;

					$stdICMS->CST = $i->product->cst_csosn;
					$stdICMS->modBC = 0;

					// if($i->product->perc_icms == 0) $stdICMS->vBC = 0;
					// else $stdICMS->vBC = $this->format($i->unit_price * $i->quantity);


					if($i->product->cst_csosn == '60'){
						$stdICMS->vBCSTRet = 0.00;
						$stdICMS->vICMSSTRet = 0.00;
						$stdICMS->vBCSTDest = 0.00;
						$stdICMS->vICMSSTDest = 0.00;
						$stdICMS->vBC = 0;
					}else{
						if($i->product->cst_csosn == '51'){
							$vbc += $this->format($i->unit_price * $i->quantity);
							$stdICMS->vBC = $this->format($i->unit_price * $i->quantity);
						}else{
							if($i->product->perc_icms > 0){
								$stdICMS->vBC = $this->format($i->unit_price * $i->quantity);
							}else{
								$stdICMS->vBC = 0.00;
							}
						}
					}

					$stdICMS->pICMS = $this->format($i->product->perc_icms);
					$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

					$somaICMS += $stdICMS->vICMS;
					// $ICMS = $nfe->tagICMS($stdICMS);

					if($i->product->cst_csosn == '60'){
						$ICMS = $nfe->tagICMSST($stdICMS);
					}else{
						$ICMS = $nfe->tagICMS($stdICMS);
					}


					// regime simples
				}else{

				//$venda->produto->CST CSOSN

					$stdICMS = new \stdClass();

					$stdICMS->item = $itemCont;
					$stdICMS->orig = $i->product->origem;
					$stdICMS->CSOSN = $i->product->cst_csosn;

					if($i->product->cst_csosn == '500'){
						$stdICMS->vBCSTRet = 0.00;
						$stdICMS->pST = 0.00;
						$stdICMS->vICMSSTRet = 0.00;
					}

					$stdICMS->pCredSN = $this->format($i->product->perc_icms);
					$stdICMS->vCredICMSSN = $this->format($i->product->perc_icms);
					$ICMS = $nfe->tagICMSSN($stdICMS);

					$somaICMS = 0;
				}
			}

			else
			{
				$valorIss = ($i->unit_price * $i->quantidade) - $vDesc;
				$somaServico += $valorIss;
				$valorIss = $valorIss * ($i->product->perc_iss/100);
				$somaISS += $valorIss;


				$std = new \stdClass();
				$std->item = $itemCont;
				$std->vBC = $stdProd->vProd;
				$std->vAliq = $i->product->perc_iss;
				$std->vISSQN = $this->format($valorIss);
				$std->cMunFG = $config->codMun;
				$std->cListServ = $i->product->cListServ;
				$std->indISS = 1;
				$std->indIncentivo = 1;

				$nfe->tagISSQN($std);
			}


				//PIS
			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont;
			$stdPIS->CST = $i->product->cst_pis;
			$stdPIS->vBC = $this->format($i->product->perc_pis) > 0 ? $stdProd->vProd : 0.00;
			$stdPIS->pPIS = $this->format($i->product->perc_pis);
			$stdPIS->vPIS = $this->format(($stdProd->vProd * $i->quantity) *
				($i->product->perc_pis/100));
			$PIS = $nfe->tagPIS($stdPIS);

				//COFINS
			$stdCOFINS = new \stdClass();
			$stdCOFINS->item = $itemCont;
			$stdCOFINS->CST = $i->product->cst_cofins;
			$stdCOFINS->vBC = $this->format($i->product->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->product->perc_cofins);
			$stdCOFINS->vCOFINS = $this->format(($stdProd->vProd * $i->quantity) *
				($i->product->perc_cofins/100));
			$COFINS = $nfe->tagCOFINS($stdCOFINS);


				//IPI

			$stdIPI = new \stdClass();
			$stdIPI->item = $itemCont;
				//999 – para tributação normal IPI
			$stdIPI->cEnq = '999';
			$stdIPI->CST = $i->product->cst_ipi;
			$stdIPI->vBC = $this->format($i->product->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
			$stdIPI->pIPI = $this->format($i->product->perc_ipi);
			$stdIPI->vIPI = $stdProd->vProd * $this->format(($i->product->perc_ipi/100));


			$nfe->tagIPI($stdIPI);


			//TAG ANP

			// if(strlen($i->product->descricao_anp) > 5){
			// 	$stdComb = new \stdClass();
			// 	$stdComb->item = 1;
			// 	$stdComb->cProdANP = $i->product->codigo_anp;
			// 	$stdComb->descANP = $i->product->descricao_anp;
			// 	$stdComb->UFCons = $venda->cliente->cidade->uf;

			// 	$nfe->tagcomb($stdComb);
			// }

			if($i->product->codigo_anp != ''){

				$stdComb = new \stdClass();
				$stdComb->item = $itemCont;
				$stdComb->cProdANP = $i->product->codigo_anp;
				$stdComb->descANP = $i->product->getDescricaoAnp();
				$stdComb->pGLP = $this->format($i->product->perc_glp);
				$stdComb->pGNn = $this->format($i->product->perc_gnn);
				$stdComb->pGNi = $this->format($i->product->perc_gni);
				$stdComb->vPart = $this->format($i->product->valor_partida);

				$stdComb->UFCons = $venda->contact->cidade ? $venda->contact->cidade->uf : $config->cidade->uf;

				$nfe->tagcomb($stdComb);
			}


			$cest = $i->product->cest;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			if(strlen($cest) > 2){
				$std = new \stdClass();
				$std->item = $itemCont;
				$std->CEST = $cest;
				$nfe->tagCEST($std);
			}


			//tag veiculo
			if($i->product->tipo == 'veiculo'){
				$strVeic = new \stdClass();
				$strVeic->item = $itemCont; //item da NFe
				$strVeic->tpOp = $i->product->tpOp;
				$strVeic->chassi = $i->product->chassi;
				$strVeic->cCor = $i->product->cCor;
				$strVeic->xCor = $i->product->xCor;
				$strVeic->pot = $i->product->pot;
				$strVeic->cilin = $i->product->cilin;
				$strVeic->pesoL = $i->product->pesoL;
				$strVeic->pesoB = $i->product->pesoB;
				$strVeic->nSerie = $i->product->nSerie;
				$strVeic->tpComb = $i->product->tpComb;
				$strVeic->nMotor = $i->product->nMotor;
				$strVeic->CMT = $i->product->CMT;
				$strVeic->dist = number_format($i->product->dist, 2);
				$strVeic->anoMod = $i->product->anoMod;
				$strVeic->anoFab = $i->product->anoFab;
				$strVeic->tpPint = $i->product->tpPint;
				$strVeic->tpVeic = $i->product->tpVeic;
				$strVeic->espVeic = $i->product->espVeic;
				$strVeic->VIN = $i->product->VIN;
				$strVeic->condVeic = $i->product->condVeic;
				$strVeic->cMod = $i->product->cMod;
				$strVeic->cCorDENATRAN = $i->product->cCorDENATRAN;
				$strVeic->lota = $i->product->lota;
				$strVeic->tpRest = $i->product->tpRest;

				$nfe->tagveicProd($strVeic);
			}

		}

        //dd($somaDesconto);


		$stdICMSTot = new \stdClass();
		$stdICMSTot->vProd = $this->format($somaProdutos);

		$stdICMSTot->vBC = $vbc;

		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;

		$stdICMSTot->vFrete = $this->format($venda->valor_frete);

		$stdICMSTot->vSeg = 0.00;


		if($totalDesconto <= 0) $totalDesconto = 0;

		$stdICMSTot->vDesc = $this->format($totalDesconto);

		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = 0.00;

		$stdICMSTot->vNF = $this->format(($somaProdutos + $somaFrete) - $totalDesconto );

		$stdICMSTot->vTotTrib = 0.00;
		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);

		//inicio totalizao issqn

		if($somaISS > 0){
			$std = new \stdClass();
			$std->vServ = $this->format($somaServico + $venda->desconto);
			$std->vBC = $this->format($somaServico);
			$std->vISS = $this->format($somaISS);
			$std->dCompet = date('Y-m-d');

			$std->cRegTrib = 6;

			$nfe->tagISSQNTot($std);
		}

		//fim totalizao issqn

		$stdTransp = new \stdClass();
		$stdTransp->modFrete = $venda->tipo;

		$transp = $nfe->tagtransp($stdTransp);

		if($venda->transportadora != null){
			$std = new \stdClass();
			$std->xNome = $venda->transportadora->razao_social;

			$std->xEnder = $venda->transportadora->logradouro;
			$std->xMun = strtoupper($venda->transportadora->cidade->nome);
			$std->UF = $venda->transportadora->cidade->uf;


			$cnpj_cpf = $venda->transportadora->cnpj_cpf;
			$cnpj_cpf = str_replace(".", "", $venda->transportadora->cnpj_cpf);
			$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
			$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

			if(strlen($cnpj_cpf) == 14) $std->CNPJ = $cnpj_cpf;
			else $std->CPF = $cnpj_cpf;

			$nfe->tagtransporta($std);
		}

		if($venda->placa != '' && $venda->uf != ''){


			$std = new \stdClass();
			$placa = str_replace("-", "", $venda->placa);
			$std->placa = strtoupper($placa);
			$std->UF = $venda->uf;

			// if($config->cidade->uf == $venda->contact->cidade->uf){
			$nfe->tagveicTransp($std);
			// }
		}

		if($venda->qtd_volumes > 0 && $venda->peso_liquido > 0
			&& $venda->peso_bruto > 0){
			$stdVol = new \stdClass();
			$stdVol->item = 1;
			$stdVol->qVol = $venda->qtd_volumes;
			$stdVol->esp = $venda->especie;

			$stdVol->nVol = $venda->numeracao_volumes ?? 0;
			$stdVol->pesoL = $venda->peso_liquido;
			$stdVol->pesoB = $venda->peso_bruto;
			$vol = $nfe->tagvol($stdVol);
		}


		$stdFat = new \stdClass();
		$stdFat->nFat = (int)$lastNumero+1;
		$stdFat->vOrig = $this->format($somaProdutos + $somaFrete);
		if($totalDesconto > 0)
			$stdFat->vDesc = $this->format($totalDesconto);
		$stdFat->vLiq = $this->format($somaProdutos + $somaFrete - $totalDesconto);

		$fatura = $nfe->tagfat($stdFat);


		if(count($venda->payment_lines) > 1){
			$contFatura = 1;

			foreach($venda->payment_lines as $ft){

				$stdDup = new \stdClass();
				$stdDup->nDup = "00".$contFatura;
				$stdDup->dVenc = substr($ft->vencimento, 0, 10);
				$stdDup->vDup = $this->format($ft->amount + $somaFrete );

				$nfe->tagdup($stdDup);
				$contFatura++;
			}
		}else{
			$pay = $venda->payment_lines[0];
			$stdDup = new \stdClass();
			$stdDup->nDup = '001';
			// if($pay->paid_on) $stdDup->dVenc = substr($pay->paid_on, 0, 10);
			if($pay->vencimento) $stdDup->dVenc = $pay->vencimento;
			else $stdDup->dVenc = date('Y-m-d');
			$stdDup->vDup =  $this->format($somaProdutos + $somaFrete - $totalDesconto);

			$nfe->tagdup($stdDup);
		}


		// 'cash
		// 'card
		// 'debit
		// 'cheque
		// 'bank_transfer
		// 'boleto
		// 'pix
		// 'other

		// '01' => 'Dinheiro', ok
		// '02' => 'Cheque',ok
		// '03' => 'Cartão de Crédito',ok
		// '04' => 'Cartão de Débito',ok
		// '05' => 'Crédito Loja',
		// '10' => 'Vale Alimentação',
		// '11' => 'Vale Refeição',
		// '12' => 'Vale Presente',
		// '13' => 'Vale Combustível',
		// '14' => 'Duplicata Mercantil',
		// '15' => 'Boleto', ok
		// '16' => 'Depósito Bancário',
		// '17' => 'Pagamento Instantâneo (PIX)',ok
		// '18' => 'Transferência Bancária, Carteira Digit.',ok
		// '19' => 'Fidelidade, Cashback, Crédito Virtual',
		// '90' => 'Sem pagamento',
		// '99' => 'Outros'

		$stdPag = new \stdClass();
		$pag = $nfe->tagpag($stdPag);

		$stdDetPag = new \stdClass();


		$tipoPagamento = $this->pegaPagamento($venda->payment_lines);

        $custom_labels = !empty(session('business.custom_labels')) ? json_decode(session('business.custom_labels'), true) : [];

        $payment_types['custom_pay_1'] = !empty($custom_labels['payments']['custom_pay_1']) ? $custom_labels['payments']['custom_pay_1'] : __('lang_v1.custom_payment_1');
        $payment_types['custom_pay_2'] = !empty($custom_labels['payments']['custom_pay_2']) ? $custom_labels['payments']['custom_pay_2'] : __('lang_v1.custom_payment_2');
        $payment_types['custom_pay_3'] = !empty($custom_labels['payments']['custom_pay_3']) ? $custom_labels['payments']['custom_pay_3'] : __('lang_v1.custom_payment_3');


        if($tipoPagamento == '99' and $venda->payment_lines[0]->method == 'custom_pay_1'){
            $stdDetPag->xPag = $custom_labels['payments']['custom_pay_1'];
        }elseif($tipoPagamento == '99' and $venda->payment_lines[0]->method == 'custom_pay_2') {
            $stdDetPag->xPag = $custom_labels['payments']['custom_pay_2'];
        }elseif($tipoPagamento == '99' and $venda->payment_lines[0]->method == 'custom_pay_3') {
            $stdDetPag->xPag = $custom_labels['payments']['custom_pay_3'];
        }

		$stdDetPag->tPag = $tipoPagamento;
		// $stdDetPag->vPag = $venda->tipo_pagamento != '90' ? $this->format($stdProd->vProd - $venda->desconto) : 0.00;
		$stdDetPag->vPag = $this->format($somaProdutos + $somaFrete - $totalDesconto);

        //TODO caso seja necessario para NFe
        /*
           if($det->method == 'cash'){
                    $tipo = '01';
                }else if($det->method == 'cheque'){
                    $tipo = '02';
                }else if($det->method == 'card'){
                    $tipo = '03';
                }else if($det->method == 'debit'){
                    $tipo = '04';
                }else if($det->method == 'boleto'){
                    $tipo = '15';
                }else if($det->method == 'pix'){
                    $tipo = '17';
                }else if($det->method == 'bank_transfer'){
                    $tipo = '18';
                }else{
                    $tipo = '99';
                }
         */

		if($venda->tipo_pagamento == '03' || $venda->tipo_pagamento == '04'){
			// $stdDetPag->CNPJ = '12345678901234';
			// $stdDetPag->tBand = '01';
			// $stdDetPag->cAut = '3333333';
			$stdDetPag->tpIntegra = 2;
		}
		$stdDetPag->indPag = $venda->forma_pagamento == 'a_vista' ?  0 : 1;

		$detPag = $nfe->tagdetPag($stdDetPag);

		if($config->ambiente == 2){
			if($venda->pedido_ecommerce_id > 0){
				$stdPag = new \stdClass();
				$stdPag->CNPJ = getenv("RESP_CNPJ");
				$stdPag->idCadIntTran = getenv("RESP_NOME");
				$detInf = $nfe->infIntermed($stdPag);
			}
		}

		$stdInfoAdic = new \stdClass();
		$obs = $venda->additional_notes;

		if($somaEstadual > 0 || $somaFederal > 0 || $somaMunicipal > 0){
			$obs .= " Trib. aprox. ";
			if($somaFederal > 0){
				$obs .= "R$ " . number_format($somaFederal, 2, ',', '.') ." Federal";
			}
			if($somaEstadual > 0){
				$obs .= ", R$ ".number_format($somaEstadual, 2, ',', '.')." Estadual";
			}
			if($somaMunicipal > 0){
				$obs .= ", R$ ".number_format($somaMunicipal, 2, ',', '.')." Municipal";
			}

			$obs .= " FONTE: " . ($ibpt->versao ?? '');
		}
		$stdInfoAdic->infCpl = $this->retiraAcentos($obs);
		// $stdInfoAdic->infCpl = $venda->additional_notes;

		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		if($venda->contact->cod_pais != 1058){
			$std = new \stdClass();
			$std->UFSaidaPais = $config->cidade->uf;
			$std->xLocExporta = $config->cidade->nome;
			// $std->xLocDespacho = 'Informação do Recinto Alfandegado';

			$nfe->tagexporta($std);
		}


		$std = new \stdClass();
		$std->CNPJ = getenv('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato= getenv('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = getenv('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = getenv('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$nfe->taginfRespTec($std);

		if($venda->referencia_nfe != ''){
			$std = new \stdClass();
			$std->refNFe = $venda->referencia_nfe;
			$nfe->tagrefNFe($std);
		}

		$aut_xml = preg_replace('/[^0-9]/', '', $config->aut_xml);

		if(strlen($aut_xml) > 10){

			$std = new \stdClass();
			$std->CNPJ = $aut_xml;
			$nfe->tagautXML($std);
		}

		try{
			$nfe->montaNFe();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'nNf' => $stdIde->nNF
			];
			return $arr;

		}catch(\Exception $e){
			return [
				'xml_erros' => $nfe->getErrors()
			];
		}
	}

	public function format($number, $dec = 4){
		return number_format((float) $number, $dec, ".", "");
	}

	public function format2($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	private function retiraAcentos($texto){
		return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/", "/(Ç)/", "/(°)/"),explode(" ","a A e E i I o O u U n N c C o"),$texto);
	}

	public function consultaCadastro($cnpj, $uf){
		try {

			$iest = '';
			$cpf = '';
			$response = $this->tools->sefazCadastro($uf, $cnpj, $iest, $cpf);

			$stdCl = new Standardize($response);

			$json = $stdCl->toJson();

			echo $json;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function consultaChave($chave){
		$response = $this->tools->sefazConsultaChave($chave);

		$stdCl = new Standardize($response);
		$arr = $stdCl->toArray();
		return $arr;
	}

	public function consultar($venda){
		try {
			$chave = $venda->chave;
			$this->tools->model('55');

			$chave = $venda->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			// $arr = json_decode($json);
			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function inutilizar($nInicio, $nFinal, $justificativa){
		try{

			$nSerie = $config->numero_serie_nfe;
			$nIni = $nInicio;
			$nFin = $nFinal;
			$xJust = $justificativa;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function cancelar($venda, $justificativa, $cnpj){
		try {

			$chave = $venda->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(4);
				// return $arr;
			$xJust = $justificativa;


			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);

					if(!is_dir(public_path('xml_nfe_cancelada/'.$cnpj))){
						mkdir(public_path('xml_nfe_cancelada/'.$cnpj), 0777, true);
					}
					file_put_contents(public_path('xml_nfe_cancelada/'.$cnpj.'/'.$chave.'.xml'), $xml);

					return $arr;
				} else {
            //houve alguma falha no evento
            //TRATAR
					return ['erro' => true, 'data' => $arr, 'status' => 402];
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
    //TRATAR
		}
	}

	public function cartaCorrecao($venda, $correcao, $cnpj){
		try {

			$chave = $venda->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $venda->sequencia_cce+1;
			$response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
			sleep(5);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '135' || $cStat == '136') {
					$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
            //SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);

					if(!is_dir(public_path('xml_nfe_correcao/'.$cnpj))){
						mkdir(public_path('xml_nfe_correcao/'.$cnpj), 0777, true);
					}
					file_put_contents(public_path('xml_nfe_correcao/'.$cnpj.'/'.$chave.'.xml'), $xml);

					$venda->sequencia_cce = $venda->sequencia_cce + 1;
					$venda->save();
					return $arr;

				} else {
					return ['erro' => true, 'data' => $arr, 'status' => 402];

				}
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}


	public function sign($xml){
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave, $cnpj){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);
			sleep(6);
			$st = new Standardize();
			$std = $st->toStd($resp);

			if ($std->cStat != 103) {

				// return "[$std->cStat] - $std->xMotivo";
				return ['erro' => true, 'protocolo' => "[$std->cStat] - $std->xMotivo", 'status' => 402];
			}

			$recibo = $std->infRec->nRec;
			sleep(2);

			$protocolo = $this->tools->sefazConsultaRecibo($recibo);

			$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
			try {
				$xml = Complements::toAuthorize($signXml, $protocolo);
				header('Content-type: text/xml; charset=UTF-8');

				if(!is_dir(public_path('xml_nfe/'.$cnpj))){
					mkdir(public_path('xml_nfe/'.$cnpj), 0777, true);
				}
				file_put_contents(public_path('xml_nfe/'.$cnpj.'/'.$chave.'.xml'), $xml);
				// return $recibo;
				return ['successo' => true, 'recibo' => $recibo];

				// $this->printDanfe($xml);
			} catch (\Exception $e) {
				return ['erro' => true, 'protocolo' => $st->toJson($protocolo), 'status' => 401];
			}

		} catch(\Exception $e){
			return "erro: ".$e->getMessage() ;
		}

	}

	private function validaEan13($code){
		if(strlen($code) < 10) return true;
		$weightflag = true;
		$sum = 0;
		for ($i = strlen($code) - 1; $i >= 0; $i--) {
			$sum += (int)$code[$i] * ($weightflag?3:1);
			$weightflag = !$weightflag;
		}
		return (10 - ($sum % 10)) % 10;
	}


}
