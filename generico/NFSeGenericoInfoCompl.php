<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoCompl  {

	/**
	 * Identificador de Documento de Responsabilidade Técnica: ART, RRT, DRT, Outros.
	 * 
	 * @var $idDocTec 
	*/
	public $idDocTec;

	/**
	 * Chave da nota, número identificador da nota, número do contrato ou outro identificador
	 * de documento emitido pelo prestador de serviços, que subsidia a emissão dessa nota pelo
	 * tomador do serviço ou intermediário (preenchimento obrigatório caso a nota esteja sendo
	 * emitida pelo Tomador ou intermediário do serviço).
	 * 
	 * @var $docRef 
	*/
	public $docRef;

	/**
	 * Número do  pedido/ordem de compra/ordem de serviço/projeto que autorize
	 * a prestação do serviço em operações B2B - Informação de interesse do
	 * tomador do serviço para controle e gestão da Negociação
	 * 
	 * @var $xPed 
	*/
	public $xPed;

	/**
	 * Grupo de itens do pedido/ordem de compra/ordem de serviço/projeto
	 * 
	 * @var array[NFSeGenericoInfoItemPed]
	*/
	public $aItensPed = [];

	/**
	 * Informações complementares
	 * 
	 * @var $xInfComp 
	*/
	public $xInfComp;
}