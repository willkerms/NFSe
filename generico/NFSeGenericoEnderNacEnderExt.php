<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoEnderNacEnderExt {

	/**
	 * Código do município, conforme Tabela do IBGE
	 * 
	 * @var $cMun 
	*/
	public $cMun;

	/**
	 * Número do CEP
	 * 
	 * @var $CEP 
	*/
	public $CEP;


	# Endereço no exterior

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