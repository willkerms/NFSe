<?php
namespace NFSe\sigep;


/**
 * 
 * @author Willker Moraes Silva
 * @since 2016-02-12
 *
 */
class NFSeSigepConsultarNFSe{
	
	public $NumeroNfse;
	
	public $PeriodoEmissao;
	
	public $Tomador;
	
	public $IntermediarioServico;
	
	public function __construct(){
		
		$this->PeriodoEmissao 		= new NFSeSigepPeriodoEmissao();
		$this->Tomador 				= new NFSeSigepIdentificacaoTomador();
		$this->IntermediarioServico = new NFSeSigepIntermediarioServico();
	}
}