<?php
namespace NFSe\generico;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoInfRps {
	
	public $idInfDeclaracaoPrestacaoServico;

	public $idRps;


	/**
	 * @var NFSeGenericoIdentificacaoRps
	 */
	public $IdentificacaoRps;

	/**
	 * @var string
	 */
	public $DataEmissao;

	/**
	 * @var string
	 */
	public $NaturezaOperacao;

	/**
	 * @var string
	 */
	public $IncentivadorCultural;

	/**
	 * 1 - Normal
	 * 2 - Cancelada
	 *
	 * @var number
	 */
	public $Status = 1;

	/**
	 * @var NFSeGenericoIdentificacaoRps
	 */
	public $RpsSubstituido;

	/**
	 * @var string
	 */
	public $Competencia;

	/**
	 * @var NFSeGenericoServico
	 */
	public $Servico;

	/**
	 * @var NFSeGenericoPrestador
	 */
	public $Prestador;

	/**
	 * @var NFSeGenericoTomador
	 */
	public $Tomador;

	/**
	 * NFSeGenericoIntermediarioServico
	 */
	public $IntermediarioServico;

	/**
	 * @var NFSeGenericoConstrucaoCivil
	 */
	public $ConstrucaoCivil;

	/**
	 * 1 - Microempresa Municipal
	 * 2 - Estimativa
	 * 3 - Sociedade de Profissionais
	 * 4 - Cooperativa
	 * 5 - Microempresário Individual (MEI)
	 * 6 - Microempresário e Empresa de Pequeno Porte (ME EPP)
	 *
	 * Obs: Quando a empresa não se enquadra em nenhum dos regimes especial de tributação acima, a tag que corresponde a essa informação deve ser suprimida do XML.
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
	 * @var NFSeGenericoEvento
	 */
	public $Evento;

	/**
	 * @var string
	 */
	public $InformacoesComplementares;
	
	/**
	 * @var array[NFSeGenericoDeducao]
	 */
	public $aDeducoes = array();

	public function __construct() {

		$this->IdentificacaoRps 		= new NFSeGenericoIdentificacaoRps();
		$this->RpsSubstituido			= new NFSeGenericoIdentificacaoRps();
		$this->Servico 					= new NFSeGenericoServico();
		$this->Prestador 				= new NFSeGenericoPrestador();
		$this->Tomador 					= new NFSeGenericoTomador();
		$this->IntermediarioServico 	= new NFSeGenericoIntermediarioServico();
		$this->ConstrucaoCivil 			= new NFSeGenericoConstrucaoCivil();
		$this->Evento 					= new NFSeGenericoEvento();
	}

}
