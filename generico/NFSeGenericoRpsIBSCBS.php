<?php
namespace NFSe\generico;

/**
 * Grupo IBS/CBS do RPS (padrão ABRASF)
 *
 * @since 2026-08-14
 *
*/
class NFSeGenericoRpsIBSCBS {

	/**
	 * Indicador da finalidade da emissão de NFS-e
	 *
	 * @var $finNFSe
	*/
	public $finNFSe;

	/**
	 * Indica operação de uso ou consumo pessoal (art. 57)
	 *
	 * @var $indFinal
	*/
	public $indFinal;

	/**
	 * Código indicador da operação de fornecimento, conforme tabela "código indicador de operação"
	 *
	 * @var $cIndOp
	*/
	public $cIndOp;

	/**
	 * A respeito do Destinatário dos serviços
	 *
	 * @var $indDest
	*/
	public $indDest;

	/**
	 * Grupo de informações relativas aos valores do serviço prestado para IBS e CBS
	 *
	 * @var NFSeGenericoRpsIBSCBSValores
	*/
	public $valores;

	public function __construct() {
		$this->valores = new NFSeGenericoRpsIBSCBSValores();
	}
}
