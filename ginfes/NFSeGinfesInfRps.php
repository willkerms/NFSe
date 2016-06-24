<?php
namespace NFSe\ginfes;

/**
 * 
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGinfesInfRps{
	
	public $IdentificacaoRps;
	
	/**
	 * @var string
	 */
	public $DataEmissao;
	
	/**
	 * 1 - Tributação no município
	 * 2 - Tributação fora do município
	 * 3 - Isenção
	 * 4 - Imune
	 * 5 - Exigibilidade suspensa por decisão judicial
	 * 6 - Exigibilidade suspensa por procedimento administrativo
	 * 
	 * Obs: Quando a natureza da operação for Tributação fora do município a tag referente ao município da prestação de 
	 * serviço deve ser preenchida com o código referente ao município onde o serviço foi realizado.
	 * 
	 * @var number
	 */
	public $NaturezaOperacao;
	
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
	public $IncentivadorCultural = 2;
	
	/**
	 * 1 - Normal
	 * 2 - Cancelado
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
	
	public function __construct(){
		
		$this->IdentificacaoRps 		= new NFSeGinfesIdentificacaoRps();
		$this->RpsSubstituido			= new NFSeGinfesIdentificacaoRps();
		$this->Servico 					= new NFSeGinfesServico();
		$this->Prestador 				= new NFSeGinfesPrestador();
		$this->Tomador 					= new NFSeGinfesTomador();
		$this->IntermediarioServico 	= new NFSeGinfesIntermediarioServico();
		$this->ConstrucaoCivil 			= new NFSeGinfesConstrucaoCivil();
	}
}