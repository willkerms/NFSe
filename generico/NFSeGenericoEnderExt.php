<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoEnderExt {

	/**
	 * Código do país (Tabela de Países ISO)
	 * 
	 * @var $cPais 
	*/
	public $cPais;

	/**
	 * Código alfanumérico do Endereçamento Postal no exterior do prestador do serviço.
	 * 
	 * @var $cEndPost 
	*/
	public $cEndPost;

	/**
	 * Nome da cidade no exterior do prestador do serviço.
	 * 
	 * @var $xCidade 
	*/
	public $xCidade;

	/**
	 * Estado, província ou região da cidade no exterior do prestador do serviço.
	 * 
	 * @var $xEstProvReg 
	*/
	public $xEstProvReg;
	
}