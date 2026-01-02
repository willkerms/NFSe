<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoDocDedRed  {

	/**
	 * Chave de Acesso da NFS-e (Padrão Nacional)
	 * ou
	 * Chave de Acesso da NFe
	 * ou
	 * Grupo de informações de Outras NFS-e (Padrão anterior de NFS-e)
	 * ou
	 * Grupo de informações de NF ou NFS (Modelo não eletrônico)
	 * ou
	 * Número de documento fiscal
	 * ou
	 * Número de documento não fiscal
	 * 
	 * @var NFSeGenericoDocOutNFSe | NFSeGenericoDocNFNFS | $chNFSe | $chNFe | $nDocFisc | $nDoc | 
	*/
	public $chNFSeChNFeNFSeMunNFNFSNdocFiscNdoc;

	/**
	 * 1 – Alimentação e bebidas/frigobar;
	 * 2 – Materiais;
	 * 5 – Repasse consorciado;
	 * 6 – Repasse plano de saúde;
	 * 7 – Serviços;
	 * 8 – Subempreitada de mão de obra;
	 * 99 – Outras deduções;
	 * 
	 * @var $tpDedRed 
	*/
	public $tpDedRed;

	/**
	 * Descrição da Dedução/Redução quando a opção é "99 – Outras Deduções"
	 * 
	 * @var $xDescOutDed 
	*/
	public $xDescOutDed;

	/**
	 * Data da emissão do documento dedutível. Ano, mês e dia (AAAA-MM-DD)
	 * 
	 * @var $dtEmiDoc 
	*/
	public $dtEmiDoc;

	/**
	 * Valor monetário total dedutível/redutível no documento informado (R$).
	 * Este é o valor total no documento informado que é passível de dedução/redução.
	 * 
	 * @var $vDedutivelRedutivel 
	*/
	public $vDedutivelRedutivel;

	/**
	 * Valor monetário utilizado para dedução/redução do valor do serviço da NFS-e que está sendo emitida (R$).
	 * Deve ser menor ou igual ao valor deduzível/redutível (vDedutivelRedutivel).
	 * 
	 * @var $vDeducaoReducao 
	*/
	public $vDeducaoReducao;

	/**
	 * Grupo de informações do Fornecedor em Deduções de Serviços
	 * 
	 * @var NFSeGenericoFornec
	*/
	public $fornec;

}