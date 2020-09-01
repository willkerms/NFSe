<?php
namespace NFSe\generico;

class NFSeGenericoInfNFSe{

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
	 * @var NFSeGenericoIdentificacaoRps
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
	 * @var NFSeGenericoServico
	 */
	public $Servico;


	public $ValorCredito;

	/**
	 * @var NFSeGenericoPrestadorServico
	 */
	public $PrestadorServico;

	/**
	 * @var NFSeGenericoTomador
	 */
	public $TomadorServico;

	/**
	 *
	 * @var NFSeGenericoIntermediarioServico
	 */
	public $IntermediarioServico;

	public function __construct(){
		$this->IdentificacaoRps = new NFSeGenericoIdentificacaoRps();
		$this->IntermediarioServico = new NFSeGenericoIntermediarioServico();
		$this->Servico = new NFSeGenericoServico();
		$this->TomadorServico = new NFSeGenericoTomador();
		$this->PrestadorServico = new NFSeGenericoPrestadorServico();
	}
}
