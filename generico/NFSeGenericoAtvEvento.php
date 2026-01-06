<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoAtvEvento  {

	/**
	 * Descrição do evento Artístico, Cultural, Esportivo, etc
	 * 
	 * @var $xNome 
	*/
	public $xNome;

	/**
	 * Data de início da atividade de evento. Ano, Mês e Dia (AAAA-MM-DD)
	 * 
	 * @var $dtIni 
	*/
	public $dtIni;

	/**
	 * Data de fim da atividade de evento. Ano, Mês e Dia (AAAA-MM-DD)
	 * 
	 * @var $dtFim 
	*/
	public $dtFim;

	/**
	 * Identificação da Atividade de Evento (código identificador de evento determinado pela Administração Tributária Municipal)
	 * 
	 * @var $idAtvEvt
	*/
	public $idAtvEvt;

	/**
	 * Grupo de informações relativas ao endereço da atividade, evento ou local do serviço prestado
	 * 
	 * @var NFSeGenericoEnderecoSimples
	*/
	public $end;
}