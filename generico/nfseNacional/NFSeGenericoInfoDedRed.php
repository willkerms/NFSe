<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoDedRed {

	/**
	 * Valor percentual padrão para dedução/redução do valor do serviço
	 * 
	 * @var $pDR
	*/
	public $pDR;

	/**
	 * Valor monetário padrão para dedução/redução do valor do serviço
	 * 
	 * @var $vDR
	*/
	public $vDR;

	/**
	 * Grupo de informações de documento utilizado para Dedução/Redução do valor do serviço
	 * 
	 * @var array[NFSeGenericoDocDedRed]
	*/
	public $documentos = [];

}