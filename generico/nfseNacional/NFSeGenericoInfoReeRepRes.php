<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoReeRepRes {

	/**
	 * Grupo relativo aos documentos referenciados nos casos de reembolso, repasse e ressarcimento que serão 
	 * considerados na base de cálculo do ISSQN, do IBS e da CBS
	 * 
	 * @var array[NFSeGenericoRTCDoc]
	*/
	public $documentos = [];
}