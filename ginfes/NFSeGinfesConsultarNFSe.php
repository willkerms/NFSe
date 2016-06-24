<?php
namespace NFSe\ginfes;


/**
 * 
 * @author Willker Moraes Silva
 * @since 2016-02-12
 *
 */
class NFSeGinfesConsultarNFSe{
	
	public $NumeroNfse;
	
	public $PeriodoEmissao;
	
	public $Tomador;
	
	public $IntermediarioServico;
	
	public function __construct(){
		
		$this->PeriodoEmissao 		= new NFSeGinfesPeriodoEmissao();
		$this->Tomador 				= new NFSeGinfesIdentificacaoTomador();
		$this->IntermediarioServico = new NFSeGinfesIntermediarioServico();
	}
}