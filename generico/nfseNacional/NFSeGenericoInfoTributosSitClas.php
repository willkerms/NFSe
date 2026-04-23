<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoTributosSitClas {

	/**
	 * Código de Situação Tributária do IBS e da CBS
	 * 
	 * @var $CST 
	*/
	public $CST;

	/**
	 * Código de Classificação Tributária do IBS e da CBS
	 * 
	 * @var $cClassTrib 
	*/
	public $cClassTrib;

	/**
	 * Código e Classificação do Crédito Presumido: IBS e CBS
	 * 
	 * @var $cCredPres 
	*/
	public $cCredPres;

	/**
	 * Grupo de informações da Tributação Regular
	 * 
	 * @var NFSeGenericoInfoTributosTribRegular
	*/
	public $gTribRegular;

	/**
	 * Grupo de informações relacionadas ao diferimento para IBS e CBS
	 * 
	 * @var NFSeGenericoInfoTributosDif
	*/
	public $gDif;


	public function __construct() {
		$this->gTribRegular = new NFSeGenericoInfoTributosTribRegular();
		$this->gDif 		= new NFSeGenericoInfoTributosDif();
	}
}