<?php
namespace NFSe\ginfes;

class NFSeGinfesInfNFSe{

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
	 *
	 * @var NFSeGinfesIdentificacaoRps
	 */
	public $IdentificacaoRps;

	/**
	 *
	 * @var string
	 */
	public $DataEmissaoRps;

	/**
	 * @var number
	 */
	public $NaturezaOperacao;

	/**
	 * @var number
	 */
	public $RegimeEspecialTributacao;

	/**
	 * @var number
	 */
	public $OptanteSimplesNacional;

	/**
	 * @var number
	 */
	public $IncentivadorCultural;

	/**
	 * @var string
	 */
	public $Competencia;

	public $NfseSubstituida;

	/**
	 * @var string
	 */
	public $OutrasInformacoes;

	/**
	 * @var NFSeGinfesServico
	 */
	public $Servico;

	public $ValorCredito;

	/**
	 * @var NFSeGinfesPrestadorServico
	 */
	public $PrestadorServico;

	/**
	 * @var NFSeGinfesTomador
	 */
	public $TomadorServico;

	/**
	 *
	 * @var NFSeGinfesIntermediarioServico
	 */
	public $IntermediarioServico;

	/**
	 * @var NFSeGinfesConstrucaoCivil
	 */
	public $ConstrucaoCivil;

	public function __construct(){
		$this->IdentificacaoRps = new NFSeGinfesIdentificacaoRps();
		$this->IntermediarioServico = new NFSeGinfesIntermediarioServico();
		$this->ConstrucaoCivil = new NFSeGinfesConstrucaoCivil();
		$this->Servico = new NFSeGinfesServico();
		$this->TomadorServico = new NFSeGinfesTomador();
		$this->PrestadorServico = new NFSeGinfesPrestadorServico();
	}
}