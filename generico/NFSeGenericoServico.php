<?php
namespace NFSe\generico;

/**
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGenericoServico {

	/**
	 * @var NFSeGenericoValores
	 */
	public $Valores;

	/**
	 * @var string
	 */
	public $ItemListaServico;

	/**
	 * @var string
	 */
	public $CodigoCnae;

	/**
	 * @var string
	 */
	public $CodigoTributacaoMunicipio;

	/**
	 * @var string
	 */
	public $Discriminacao;

	/**
	 * @var string
	 */
	public $CodigoMunicipio;

	/**
	 * Exigibilidade do ISS da NFS-e
	 *
	 *  01 - TRIBUTA��O NO MUNIC�PIO
	 *  02 - TRIBUTA��O FORA DO MUNIC�PIO
	 *  03 - ISEN��O
	 *  05 - IMUNE;
	 *
	 * @var number
	 */
	public $ExigibilidadeISS = '01';

	/**
	 * @var string
	 */
	public $MunicipioIncidencia;

	/**
	 * @var string
	 */
	public $NumeroProcesso;

	public function __construct(){
		$this->Valores = new NFSeGenericoValores();
	}

}
