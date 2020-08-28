<?php
namespace NFSe\generico;

class NFSeGenericoCancelarNfseEnvio extends NFSeGenericoIdentificacaoNfse{
	/**
	 *
	* 1 - Erro na emissao;
	* 2 - Servico nao prestado;
	* 3 - Erro de assinatura;
	* 4 - Duplicidade da nota;
	* 5 - Erro de processamento
	 *
	 * @var string
	 */
	public $CodigoCancelamento;

	public $DescricaoCancelamento;
}