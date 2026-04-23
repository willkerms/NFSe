<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoExploracaoRodoviaria {

	/**
	 * 00 - Categoria de veículos (tipo não informado na nota de origem)
	 * 01 - Automóvel, caminhonete e furgão;
	 * 02 - Caminhão leve, ônibus, caminhão trator e furgão;
	 * 03 - Automóvel e caminhonete com semireboque;
	 * 04 - Caminhão, caminhão-trator, caminhão-trator com semi-reboque e ônibus;
	 * 05 - Automóvel e caminhonete com reboque;
	 * 06 - Caminhão com reboque;
	 * 07 - Caminhão trator com semi-reboque;
	 * 08 - Motocicletas, motonetas e bicicletas motorizadas;
	 * 09 - Veículo especial;
	 * 10 - Veículo Isento;
	 * 
	 * @var $categVeic 
	*/
	public $categVeic;

	/**
	 * Número de eixos para fins de cobrança
	 * 
	 * @var $nEixos 
	*/
	public $nEixos;

	/**
	 * Tipo de rodagem
	 * 
	 * @var $rodagem 
	*/
	public $rodagem;

	/**
	 * Placa do veículo
	 * 
	 * @var $sentido 
	*/
	public $sentido;

	/**
	 * Placa do veículo
	 * 
	 * @var $placa 
	*/
	public $placa;

	/**
	 * Código de acesso gerado automaticamente pelo sistema emissor da concessionária.
	 * 
	 * @var $codAcessoPed 
	*/
	public $codAcessoPed;

	/**
	 * Código de contrato gerado automaticamente pelo sistema nacional no cadastro da concessionária.
	 * 
	 * @var $codContrato 
	*/
	public $codContrato;

}