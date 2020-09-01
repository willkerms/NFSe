<?php
namespace NFSe\sigep;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeSigepInfRps{

	/**
	 * @var NFSeSigepIdentificacaoRps
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
	 * CO - Convertida
	 * CA - Cancelada
	 *
	 * @var number
	 */
	public $Status = 'CO';

	public $RpsSubstituido;

	public $Servico;

	public $Prestador;

	public $Tomador;

	public $IntermediarioServico;

	public $ConstrucaoCivil;

	public function __construct(){

		$this->IdentificacaoRps 		= new NFSeSigepIdentificacaoRps();
		$this->RpsSubstituido			= new NFSeSigepIdentificacaoRps();
		$this->Servico 					= new NFSeSigepServico();
		$this->Prestador 				= new NFSeSigepPrestador();
		$this->Tomador 					= new NFSeSigepTomador();
		$this->IntermediarioServico 	= new NFSeSigepIntermediarioServico();
		$this->ConstrucaoCivil 			= new NFSeSigepConstrucaoCivil();
	}
}