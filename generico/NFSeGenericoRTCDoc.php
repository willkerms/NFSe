<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoRTCDoc {

	/**
	 * Grupo de informações de documentos fiscais eletrônicos que se encontram no repositório nacional
	 * 
	 * @var NFSeGenericoDocDFe
	*/
	public $dFeNacional;
	
	/**
	 * Grupo de informações de documento fiscais, eletrônicos ou não, que não se encontram no repositório nacional
	 * 
	 * @var NFSeGenericoDocFiscalOutro
	*/
	public $docFiscalOutroDocOutro;

	/**
	 * Grupo de informações de documento não fiscal.
	 * 
	 * @var NFSeGenericoDocOutro 
	*/
	public $docOutro;

	/**
	 *  
	 * @var NFSeGenericoFornecDoc
	*/
	public $fornec;

	/**
	 * Data da emissão do documento dedutível
	 * Ano, mês e dia (AAAA-MM-DD)
	 * 
	 * @var $dtEmiDoc  
	*/
	public $dtEmiDoc;

	/**
	 * Data da competência do documento dedutível
	 * Ano, mês e dia (AAAA-MM-DD)
	 * 
	 * @var $dtCompDoc  
	*/
	public $dtCompDoc;

	/**
	 * Tipo de valor incluído neste documento, recebido por motivo de estarem relacionadas a operações de terceiros, 
	 * objeto de reembolso, repasse ou ressarcimento pelo recebedor, já tributados e aqui referenciados
	 * 
	 * @var $tpReeRepRes  
	*/
	public $tpReeRepRes;

	/**
	 * Descrição do reembolso ou ressarcimento quando a opção é 
	 * "99 – Outros reembolsos ou ressarcimentos recebidos por valores pagos relativos a operações por conta e ordem de terceiro"
	 * 
	 * @var $xTpReeRepRes  
	*/
	public $xTpReeRepRes;

	/**
	 * Valor monetário (total ou parcial, conforme documento informado) utilizado para não inclusão na base de cálculo 
	 * do ISS e do IBS e da CBS da NFS-e que está sendo emitida (R$)
	 * 
	 * @var $vlrReeRepRes  
	*/
	public $vlrReeRepRes;

	public function __construct() {
		$this->fornec = new NFSeGenericoFornecDoc();
	}
}