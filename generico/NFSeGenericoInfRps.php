<?php
namespace NFSe\generico;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoInfRps {

	/**
	 * @var NFSeGenericoIdentificacaoRps
	 */
	public $IdentificacaoRps;

	/**
	 * @var string
	 */
	public $DataEmissao;

	/**
	 * 1 - Microempresa Municipal
	 * 2 - Estimativa
	 * 3 - Sociedade de Profissionais
	 * 4 - Cooperativa
	 * 5 - Microempres�rio Individual (MEI)
	 * 6 - Microempres�rio e Empresa de Pequeno Porte (ME EPP)
	 *
	 * Obs: Quando a empresa n�o se enquadra em nenhum dos regimes especial de tributa��o acima, a tag que corresponde a essa informa��o deve ser suprimida do XML.
	 *
	 * @var number
	 */
	public $RegimeEspecialTributacao = 1;

	/**
	 * 1 - Sim
	 * 2 - Não
	 *
	 * @var number
	 */
	public $OptanteSimplesNacional = 1;

	/**
	 * 1 - Sim
	 * 2 - Não
	 *
	 * @var number
	 */
	public $IncentivoFiscal = 2;

	/**
	 * 1 - Normal
	 * 2 - Cancelada
	 *
	 * @var number
	 */
	public $Status = 1;

	public $RpsSubstituido;

	public $Servico;

	public $Prestador;

	public $Tomador;

	public $IntermediarioServico;

	public $ConstrucaoCivil;

	public function __construct() {

		$this->IdentificacaoRps 		= new NFSeGenericoIdentificacaoRps();
		$this->RpsSubstituido			= new NFSeGenericoIdentificacaoRps();
		$this->Servico 					= new NFSeGenericoServico();
		$this->Prestador 				= new NFSeGenericoPrestador();
		$this->Tomador 					= new NFSeGenericoTomador();
		$this->IntermediarioServico 	= new NFSeGenericoIntermediarioServico();
		$this->ConstrucaoCivil 			= new NFSeGenericoConstrucaoCivil();

	}

}
