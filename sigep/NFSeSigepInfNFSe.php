<?php
namespace NFSe\sigep;

class NFSeSigepInfNFSe{

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
	 * @var NFSeSigepIdentificacaoRps
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
	 * @var NFSeSigepServico
	 */
	public $Servico;


	public $ValorCredito;

	/**
	 * @var NFSeSigepPrestadorServico
	 */
	public $PrestadorServico;

	/**
	 * @var NFSeSigepTomador
	 */
	public $TomadorServico;

	/**
	 *
	 * @var NFSeSigepIntermediarioServico
	 */
	public $IntermediarioServico;

	public function __construct(){
		$this->IdentificacaoRps = new NFSeSigepIdentificacaoRps();
		$this->IntermediarioServico = new NFSeSigepIntermediarioServico();
		$this->Servico = new NFSeSigepServico();
		$this->TomadorServico = new NFSeSigepTomador();
		$this->PrestadorServico = new NFSeSigepPrestadorServico();
	}
}