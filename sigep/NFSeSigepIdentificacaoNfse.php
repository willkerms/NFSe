<?php
namespace NFSe\sigep;

class NFSeSigepIdentificacaoNfse{

	public $Numero;

	private $tpPessoa;

	private $CpfCnpj;

	public $InscricaoMunicipal;

	public $CodigoVerificacao;

	/**
	 *
	 * EE - Erro de Emissão
	 * ED - Erro de Digitação
	 * OU - Outros
	 * SB - Substituição
	 *
	 * @var string
	 */
	public $CodigoCancelamento;


	public $DescricaoCancelamento;

	public function setCpfCnpj($cpfCnpj, $tpPessoa = 1){
		switch ($tpPessoa){
			case 0:
			case "F":
			case "PF":
			case "f":
				$this->CpfCnpj = $cpfCnpj;
				$this->tpPessoa = 0;
			break;
			case 1:
			case 'J':
			case 'PJ':
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