<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2026-04-26
 * @author Willker Moraes Silva
 *
*/
class NFSeGenericoInfNFSe {

	/**
	 * Identificador da tag a ser assinada.
	 *
	 * @var $Id
	*/
	public $Id;

	/**
	 * Descrição do código do IBGE do município emissor da NFS-e.
	 *
	 * @var $xLocEmi
	*/
	public $xLocEmi;

	/**
	 * Descrição do local da prestação do serviço.
	 *
	 * @var $xLocPrestacao
	*/
	public $xLocPrestacao;

	/**
	 * Número sequencial por tipo de emitente da NFS-e.
	 *
	 * @var $nNFSe
	*/
	public $nNFSe;

	/**
	 * Código IBGE do município de incidência do ISSQN.
	 * Determinado automaticamente pelo sistema conforme regras da LC 116/03.
	 *
	 * @var $cLocIncid
	*/
	public $cLocIncid;

	/**
	 * Descrição do município de incidência do ISSQN.
	 *
	 * @var $xLocIncid
	*/
	public $xLocIncid;

	/**
	 * Descrição do código de tributação nacional do ISSQN.
	 *
	 * @var $xTribNac
	*/
	public $xTribNac;

	/**
	 * Descrição do código de tributação municipal do ISSQN.
	 *
	 * @var $xTribMun
	*/
	public $xTribMun;

	/**
	 * Descrição do código da NBS.
	 *
	 * @var $xNBS
	*/
	public $xNBS;

	/**
	 * Versão do aplicativo que gerou a NFS-e.
	 *
	 * @var $verAplic
	*/
	public $verAplic;

	/**
	 * Ambiente gerador da NFS-e.
	 *
	 * @var $ambGer
	*/
	public $ambGer;

	/**
	 * Processo de Emissão da DPS:
	 * 1 - Emissão com aplicativo do contribuinte (via Web Service);
	 * 2 - Emissão com aplicativo disponibilizado pelo fisco (Web);
	 * 3 - Emissão com aplicativo disponibilizado pelo fisco (App);
	 *
	 * @var $tpEmis
	*/
	public $tpEmis;

	/**
	 * Processo de Emissão da DPS:
	 * 1 - Emissão com aplicativo do contribuinte (via Web Service);
	 * 2 - Emissão com aplicativo disponibilizado pelo fisco (Web);
	 * 3 - Emissão com aplicativo disponibilizado pelo fisco (App);
	 *
	 * @var $procEmi
	*/
	public $procEmi;

	/**
	 * Código do Status da mensagem.
	 *
	 * @var $cStat
	*/
	public $cStat;

	/**
	 * Data/Hora da validação da DPS e geração da NFS-e.
	 * Data e hora no formato UTC: AAAA-MM-DDThh:mm:ssTZD
	 *
	 * @var $dhProc
	*/
	public $dhProc;

	/**
	 * Número sequencial do documento gerado por ambiente gerador de DFSe do município.
	 *
	 * @var $nDFSe
	*/
	public $nDFSe;

	/**
	 * Grupo de informações da DPS relativas ao emitente da NFS-e.
	 *
	 * @var NFSeGenericoEmitente
	*/
	public $emit;

	/**
	 * Uso da Administração Tributária Municipal.
	 *
	 * @var $xOutInf
	*/
	public $xOutInf;

	/**
	 * Grupo de valores referentes ao Serviço Prestado.
	 *
	 * @var NFSeGenericoInfNFSeValores
	*/
	public $valores;

	/**
	 * Grupo de informações geradas pelo sistema referentes ao IBS e à CBS.
	 *
	 * @var NFSeGenericoInfNFSeRTCIBSCBS
	*/
	public $IBSCBS;

	/**
	 * Grupo de informações da DPS relativas ao serviço prestado.
	 *
	 * @var NFSeGenericoInfDPS
	*/
	public $DPS;

	public function __construct() {
		$this->emit   = new NFSeGenericoEmitente();
		$this->valores = new NFSeGenericoInfNFSeValores();
		// $this->IBSCBS  = new NFSeGenericoInfNFSeRTCIBSCBS();
		$this->DPS     = new NFSeGenericoInfDPS();
	}
}
