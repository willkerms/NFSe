<?php
namespace NFSe\generico;



/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoCServ  {

	/**
	 * Código de tributação nacional do ISSQN, nos termos da LC 116/2003, conforme aba MUN.INCID_INFO.SERV. do ANEXO I
	 * Regra de formação - 6 dígitos numéricos sendo: 2 para Item (LC 116/2003), 2 para Subitem (LC 116/2003) e 2 para Desdobro Nacional
	 * 
	 * @var $cTribNac  
	*/
	public $cTribNac;

	/**
	 * Código de tributação municipal do ISSQN
	 * 
	 * @var $cTribMun  
	*/
	public $cTribMun;

	/**
	 * Descrição completa do serviço prestado
	 * 
	 * @var $xDescServ  
	*/
	public $xDescServ;

	/**
	 * Código NBS correspondente ao serviço prestado, seguindo a versão 2.0, conforme Anexo B
	 * 
	 * @var $cNBS  
	*/
	public $cNBS;

	/**
	 * Código interno do contribuinte
	 * 
	 * @var $cIntContrib  
	*/
	public $cIntContrib;
}