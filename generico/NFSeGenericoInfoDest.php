<?php
namespace NFSe\generico;

/**
 *
 * @since 2025-12-29
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoInfoDest {


	/**
	 * Número de CNPJ
	 * 
	 * @var $CNPJ
	 */
	public $CNPJ;

	/**
	 * Número de CPF
	 * 
	 * @var $CPF
	 */
	public $CPF;

	/**
	 * NIF - Número de Identificação Fiscal fornecido por órgão de administração tributária no exterior
	 * 
	 * @var $NIF
	 */
	public $NIF;

	/**
	 * Motivo para não informação do NIF:
	 * 1 - Dispensado do NIF;
	 * 2 - Não exigência do NIF;
	 * 
	 * @var $cNaoNIF
	 */
	public $cNaoNif;

	/**
	 * Nome/Nome Empresarial
	 * 
	 * @var $xNome
	*/
	public $xNome;


	/**
	 * Dados de endereço
	 * 
	 * @var NFSeGenericoEnderecoDPS
	*/
	public $end;

	/**
	 * Número do telefone:
	 * Preencher com o Código DDD + número do telefone.
	 * Nas operações com exterior é permitido informar o código do país + código da localidade + número do telefone)
	 * 
	 * @var $fone
	*/
	public $fone;

	/**
	 * E-mail
	 * 
	 * @var $email
	*/
	public $email;

	public function __construct() {
		$this->end = new NFSeGenericoEnderecoDPS();
	}
}