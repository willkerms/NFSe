<?php
namespace NFSe\generico;

class NFSeGenericoEnviarLoteRpsResposta {
	
	public $NumeroLote;
	public $DataRecebimento;
	public $Protocolo;
	
	/**
	 * 
	 * @var array[NFSeGenericoMensagemRetorno]
	 */
	public $ListaMensagemRetorno = array();
	
}
