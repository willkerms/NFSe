<?php
namespace NFSe\issweb;

class NFSeISSWebInfNFSe{

	/**
	 * @var string
	 */
	public $Numero;

	/**
	 * @var string
	 */
	public $CodigoVerificacao;

	/**
	 * @var string
	 */
	public $DataEmissao;

	/**
	 * @var string
	 */
	public $Url;

	/**
	 *
	 * @var NFSeISSWebIdentificacaoRps
	 */
	public $IdentificacaoRps;

	/**
	 *
	 * @var string
	 */
	public $DataEmissaoRps;

	/**
	 *
	 * @var string
	 */
	public $StatusNfse;

	/**
	 *
	 * @var string
	 */
	public $NfseSubstituida;

	/**
	 * @var string
	 */
	public $OutrasInformacoes;

	/**
	 * @var NFSeISSWebServico
	 */
	public $Servico;


	public $ValorCredito;

	/**
	 * @var NFSeISSWebPrestadorServico
	 */
	public $PrestadorServico;

	/**
	 * @var NFSeISSWebTomador
	 */
	public $TomadorServico;

	/**
	 *
	 * @var NFSeISSWebIntermediarioServico
	 */
	public $IntermediarioServico;

	public function __construct(){
		$this->IdentificacaoRps = new NFSeISSWebIdentificacaoRps();
		$this->IntermediarioServico = new NFSeISSWebIntermediarioServico();
		$this->Servico = new NFSeISSWebServico();
		$this->TomadorServico = new NFSeISSWebTomador();
		$this->PrestadorServico = new NFSeISSWebPrestadorServico();
	}
}