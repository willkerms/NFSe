<?php
namespace NFSe\sigep;

class NFSeSigepEnviarLoteRpsResposta{
	public $NumeroLote;
	public $DataRecebimento;
	public $Protocolo;
	/**
	 * 
	 * @var array[NFSeSigepMensagemRetorno]
	 */
	public $ListaMensagemRetorno = array();
}