<?php
namespace NFSe\ginfes;

class NFSeGinfesEnviarLoteRpsResposta{
	public $NumeroLote;
	public $DataRecebimento;
	public $Protocolo;
	/**
	 * 
	 * @var array[NFSeGinfesMensagemRetorno]
	 */
	public $ListaMensagemRetorno = array();
}