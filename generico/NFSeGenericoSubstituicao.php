<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoSubstituicao {

	/**
	 * Chave de acesso da NFS-e a ser substituída.
	 * 
	 * @var $chSubstda 
	*/
	public $chSubstda;

	/**
     * 01 - Desenquadramento de NFS-e do Simples Nacional;
     * 02 - Enquadramento de NFS-e no Simples Nacional;
	 * 03 - Inclusão Retroativa de Imunidade/Isenção para NFS-e;
	 * 04 - Exclusão Retroativa de Imunidade/Isenção para NFS-e;
	 * 05 - Rejeição de NFS-e pelo tomador ou pelo intermediário se responsável pelo recolhimento do tributo;
	 * 99 - Outros;
	 * 
	 * @var $cMotivo 
	*/
	public $cMotivo;

	/**
	 * Descrição do motivo da substituição da NFS-e
	 * O emitente deve descrever o motivo da substituição para outros motivos (cMotivo = 99).
	 * 
	 * @var $xMotivo 
	*/
	public $xMotivo;
}