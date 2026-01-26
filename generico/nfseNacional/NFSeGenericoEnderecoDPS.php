<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoEnderecoDPS {

	/**
	 * Grupo de informações específicas de endereço nacional
	 * ou
	 * Grupo de informações específicas de endereço no exterior
	 * 
	 * @var NFSeGenericoEnderNacEnderExt
	*/
	public $endNacEndExt;

	/**
	 * Tipo e nome do logradouro da localização do imóvel
	 * 
	 * @var $xLgr 
	*/
	public $xLgr;

	/**
	 * Número do imóvel
	 * 
	 * @var $nro 
	*/
	public $nro;

	/**
	 * Complemento do endereço
	 * 
	 * @var $xCpl 
	*/
	public $xCpl;

	/**
	 * Bairro
	 * 
	 * @var $xBairro 
	*/
	public $xBairro;
	
}