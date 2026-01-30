<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoIBSCBS {

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
	 * Tipo de Operação com Entes Governamentais ou outros serviços sobre bens imóveis
	 * 
	 * @var $tpOper 
	*/
	public $tpOper;

	/**
	 * Grupo de NFS-e referenciadas
	 * 
	 * @var array[NFSeGenericoInfoRefNFSe]
	*/
	public $aRefNFSe = [];

	/**
	 * Tipo de ente governamental
	 * 
	 * @var $tpEnteGov 
	*/
	public $tpEnteGov;

	/**
	 * A respeito do Destinatário dos serviços
	 * 
	 * @var $indDest 
	*/
	public $indDest;

	/**
	 * Grupo de informações relativas ao Destinatário
	 * 
	 * @var NFSeGenericoInfoDest
	*/
	public $dest;

	/**
	 * Grupo de informações de operações relacionadas a bens imóveis, exceto obras
	 * 
	 * @var NFSeGenericoInfoImovel
	*/
	public $imovel;

	/**
	 * Grupo de informações relativas aos valores do serviço prestado para IBS e CBS
	 * 
	 * @var NFSeGenericoIBSCBSValores
	*/
	public $valores;

	public function __construct() {
		$this->dest 	= new NFSeGenericoInfoDest();
		$this->imovel 	= new NFSeGenericoInfoImovel();
		$this->valores 	= new NFSeGenericoIBSCBSValores();
		$this->aRefNFSe = [];
	}
}