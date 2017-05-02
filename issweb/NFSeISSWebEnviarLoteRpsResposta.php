<?php
namespace NFSe\issweb;

class NFSeISSWebEnviarLoteRpsResposta{
	public $NumeroLote;
	public $DataRecebimento;
	public $Protocolo;
	/**
	 * 
	 * @var array[NFSeISSWebMensagemRetorno]
	 */
	public $ListaMensagemRetorno = array();
}