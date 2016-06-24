<?php
namespace NFSe\ginfes;

/**
 *
 * @since 2016-02-10
 * @author Willker Moraes Silva
 *
 */
class NFSeGinfesIdentificacao{

	private $CpfCnpj;

	private $tpPessoa;

	/**
	 * Este elemtno só deverá ser preenchido para tomadores cadastrados no município
	 *
	 * @var string
	 */
	public $InscricaoMunicipal;

	public function setCpfCnpj($cpfCnpj, $tpPessoa = 1){
		switch ($tpPessoa){
			case 0:
			case "PF":
			case "F":
			case "f":
				$this->CpfCnpj = $cpfCnpj;
				$this->tpPessoa = 0;
			break;
			case 1:
			case 'PJ':
			case 'J':
			case 'j':
				$this->CpfCnpj = $cpfCnpj;
				$this->tpPessoa = 1;
			break;
		}
	}

	/**
	 * @return string $CpfCnpj
	 */
	public function getCpfCnpj() {

		return $this->CpfCnpj;
	}

	/**
	 * @return number $tpPessoa
	 */
	public function getTpPessoa() {

		return $this->tpPessoa;
	}
}