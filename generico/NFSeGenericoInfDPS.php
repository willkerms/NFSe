<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfDPS {

	/**
	 * 1 - Produção;
	 * 2 - Homologação;
	 *
	 * @var $tpAmb
	*/
	public $tpAmb;

	/**
	 * Data e hora da emissão do DPS. Data e hora no formato UTC (Universal Coordinated Time): AAAA-MM-DDThh:mm:ssTZD
	 *
	 * @var $dhEmi
	*/
	public $dhEmi;

	/**
	 * Versão do aplicativo que gerou o DPS
	 *
	 * @var $verAplic
	*/
	public $verAplic;

	/**
	 * Número do equipamento emissor do DPS ou série do DPS
	 *
	 * @var $serie
	*/
	public $serie;

	/**
	 * Número do DPS
	 *
	 * @var $nDPS
	*/
	public $nDPS;

	/**
	 * Data em que se iniciou a prestação do serviço: Dia, mês e ano (AAAAMMDD)
	 *
	 * @var $dCompet
	*/
	public $dCompet;

	/**
	 * 1 - Prestador;
	 * 2 - Tomador;
	 * 3 - Intermediário
	 *
	 * @var $tpEmit
	*/
	public $tpEmit;

	/**
	 * 1 - Importação de Serviço;
	 * 2 - Tomador/Intermediário obrigado a emitir NFS-e por legislação municipal;
	 * 3 - Tomador/Intermediário emitindo NFS-e por recusa de emissão pelo prestador;
	 * 4 - Tomador/Intermediário emitindo por rejeitar a NFS-e emitida pelo prestador;
	 * 
	 * @var $cMotivoEmisTI
	*/
	public $cMotivoEmisTI;

	/**
	 * Chave de Acesso da NFS-e rejeitada pelo Tomador/Intermediário.
	 * 
	 * @var $chNFSeRej
	*/
	public $chNFSeRej;

	/**
	 * O código de município utilizado pelo Sistema Nacional NFS-e é o código definido para cada município pertencente ao
	 * "Anexo V – Tabela de Código de Municípios do IBGE", que consta ao final do Manual de Orientação ao Contribuinte do
	 * ISSQN para a Sefin Nacional NFS-e.
	 *
	 * O município emissor da NFS-e é aquele município em que o emitente da DPS está cadastrado e autorizado a "emitir
	 * uma NFS-e", ou seja, emitir uma DPS para que o sistema nacional valide as informações nela prestadas e gere a NFS-e
	 * correspondente para o emitente.
	 *
	 * Para que o sistema nacional emita a NFS-e o município emissor deve ser conveniado e estar ativo no sistema nacional.
	 * Além disso o convênio do município deve permitir que os contribuintes do município utilize os emissores públicos do
	 * Sistema Nacional NFS-e.
	 *
	 * @var $cLocEmi
	*/
	public $cLocEmi;

	/**
	 * Dados da NFS-e a ser substituída
	 * 
	 * @var NFSeGenericoSubstituicao
	*/
	public $subst;

	/**
	 * Informações do prestador da NFS-e. Difere das demais pessoas por causa das informações de regimes de tributação
	 * 
	 * @var NFSeGenericoPrest
	*/
	public $prest;

	/**
	 * Grupo de informações do DPS relativas ao Tomador de Serviços
	 * 
	 * @var NFSeGenericoToma
	*/
	public $toma;

	/**
	 * Grupo de informações do DPS relativas ao Intermediário de Serviços
	 * 
	 * @var NFSeGenericoInterm
	*/
	public $interm;

	/**
	 * Grupo de informações do DPS relativas ao Serviço Prestado
	 * 
	 * @var NFSeGenericoServ
	*/
	public $serv;

	/**
	 * Grupo de informações relativas à valores do serviço prestado
	 * 
	 * @var NFSeGenericoDPSValores
	*/
	public $valores;

	/**
	 * Grupo de informações declaradas pelo emitente referentes ao IBS e à CBS
	 * 
	 * @var NFSeGenericoIBSCBS
	*/
	public $IBSCBS;

	public function __construct() {
		$this->subst 	= new NFSeGenericoSubstituicao();
		$this->prest 	= new NFSeGenericoPrest();
		$this->toma 	= new NFSeGenericoToma();
		$this->interm 	= new NFSeGenericoInterm();
		$this->serv 	= new NFSeGenericoServ();
		$this->valores 	= new NFSeGenericoDPSValores();
		$this->IBSCBS 	= new NFSeGenericoIBSCBS();
	}

}