<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoServ {

	/**
	 * Grupo de informações relativas ao local da prestação do serviço
	 * 
	 * @var NFSeGenericoLocPrest
	*/
	public $locPrest;

	/**
	 * Grupo de informações relativas ao código do serviço prestado
	 * 
	 * @var NFSeGenericoCServ
	*/
	public $cServ;

	/**
	 * Grupo de informações relativas à exportação/importação de serviço prestado
	 * 
	 * @var NFSeGenericoComExterior
	*/
	public $comExt;

	/**
	 * Grupo de informações do DPS relativas à serviço de obra
	 * 
	 * @var NFSeGenericoObra
	*/
	public $obra;

	/**
	 * Grupo de informações do DPS relativas à Evento
	 * 
	 * @var NFSeGenericoAtvEvento
	*/
	public $atvEvento;

	/**
	 * Grupo de informações relativas a pedágio 
	 * 
	 * @var NFSeGenericoExploracaoRodoviaria
	*/
	public $explRod;

	/**
	 * Grupo de informações complementares disponível para todos os serviços prestados
	 * 
	 * @var NFSeGenericoInfoCompl
	*/
	public $infoCompl;

	public function __construct() {
		$this->locPrest 	= new NFSeGenericoLocPrest();
		$this->cServ	 	= new NFSeGenericoCServ();
		$this->comExt 		= new NFSeGenericoComExterior();
		$this->obra 		= new NFSeGenericoObra();
		$this->atvEvento 	= new NFSeGenericoAtvEvento();
		$this->explRod 		= new NFSeGenericoExploracaoRodoviaria();
		$this->infoCompl	= new NFSeGenericoInfoCompl();
	}
}