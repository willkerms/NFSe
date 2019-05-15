<?php
namespace NFSe\sigep;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeSigepServico {

	/**
	 * @var NFSeSigepValores
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
	 *  01 - TRIBUTAÇÃO NO MUNICÍPIO
	 *  02 - TRIBUTAÇÃO FORA DO MUNICÍPIO
	 *  03 - ISENÇÃO
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
		$this->Valores = new NFSeSigepValores();
	}
}