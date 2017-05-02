<?php
namespace NFSe\issweb;


/**
 * 
 * @author Willker Moraes Silva
 * @since 2016-02-12
 *
 */
class NFSeISSWebConsultarNFSe{
	
	public $NumeroNfse;
	
	public $PeriodoEmissao;
	
	public $Tomador;
	
	public $IntermediarioServico;
	
	public function __construct(){
		
		$this->PeriodoEmissao 		= new NFSeISSWebPeriodoEmissao();
		$this->Tomador 				= new NFSeISSWebIdentificacaoTomador();
		$this->IntermediarioServico = new NFSeISSWebIntermediarioServico();
	}
}