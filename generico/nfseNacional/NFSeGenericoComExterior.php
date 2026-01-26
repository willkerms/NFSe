<?php
namespace NFSe\generico\nfseNacional;

/**
 *
 * @since 2025-12-30
 * @author Norton Almeida Pontes
 *
*/
class NFSeGenericoComExterior  {

	/**
	 * 0 - Desconhecido (tipo não informado na nota de origem);
	 * 1 - Transfronteiriço;
	 * 2 - Consumo no Brasil;
	 * 3 - Movimento Temporário de Pessoas Físicas;
	 * 4 - Consumo no Exterior;
	 * 
	 * @var $mdPrestacao  
	*/
	public $mdPrestacao;

	/**
	 * 0 - Sem vínculo com o Tomador/Prestador
	 * 1 - Controlada;
	 * 2 - Controladora;
	 * 3 - Coligada;
	 * 4 - Matriz;
	 * 5 - Filial ou sucursal;
	 * 6 - Outro vínculo;
	 * 9 - Desconhecido;
	 * 
	 * @var $vincPrest  
	*/
	public $vincPrest;

	/**
	 * Identifica a moeda da transação comercial
	 * 
	 * @var $tpMoeda  
	*/
	public $tpMoeda;

	/**
	 * Valor do serviço prestado expresso em moeda estrangeira especificada em tpMoeda
	 * 
	 * @var $vServMoeda  
	*/
	public $vServMoeda;

	/**
	 * 00 - Desconhecido (tipo não informado na nota de origem);
	 * 01 - Nenhum;
	 * 02 - ACC - Adiantamento sobre Contrato de Câmbio – Redução a Zero do IR e do IOF;
	 * 03 - ACE – Adiantamento sobre Cambiais Entregues - Redução a Zero do IR e do IOF;
	 * 04 - BNDES-Exim Pós-Embarque – Serviços;
	 * 05 - BNDES-Exim Pré-Embarque - Serviços;
	 * 06 - FGE - Fundo de Garantia à Exportação;
	 * 07 - PROEX - EQUALIZAÇÃO
	 * 08 - PROEX - Financiamento;
	 * 
	 * @var $mecAFComexP  
	*/
	public $mecAFComexP;

	/**
	 * 00 - Desconhecido (tipo não informado na nota de origem);
	 * 01 - Nenhum;
	 * 02 - Adm. Pública e Repr. Internacional;
	 * 03 - Alugueis e Arrend. Mercantil de maquinas, equip., embarc. e aeronaves;
	 * 04 - Arrendamento Mercantil de aeronave para empresa de transporte aéreo público;
	 * 05 - Comissão a agentes externos na exportação;
	 * 06 - Despesas de armazenagem, mov. e transporte de carga no exterior;
	 * 07 - Eventos FIFA (subsidiária);
	 * 08 - Eventos FIFA;
	 * 09 - Fretes, arrendamentos de embarcações ou aeronaves e outros;
	 * 10 - Material Aeronáutico;
	 * 11 - Promoção de Bens no Exterior;
	 * 12 - Promoção de Dest. Turísticos Brasileiros;
	 * 13 - Promoção do Brasil no Exterior;
	 * 14 - Promoção Serviços no Exterior;
	 * 15 - RECINE;
	 * 16 - RECOPA;
	 * 17 - Registro e Manutenção de marcas, patentes e cultivares;
	 * 18 - REICOMP;
	 * 19 - REIDI;
	 * 20 - REPENEC;
	 * 21 - REPES;
	 * 22 - RETAERO; 
	 * 23 - RETID;
	 * 24 - Royalties, Assistência Técnica, Científica e Assemelhados;
	 * 25 - Serviços de avaliação da conformidade vinculados aos Acordos da OMC;
	 * 26 - ZPE;
	 * 
	 * @var $mecAFComexT  
	*/
	public $mecAFComexT;

	/**
	 * 0 - Desconhecido (tipo não informado na nota de origem);
	 * 1 - Não;
	 * 2 - Vinculada - Declaração de Importação;
	 * 3 - Vinculada - Declaração de Exportação;
	 * 
	 * @var $movTempBens  
	*/
	public $movTempBens;

	/**
	 * Número da Declaração de Importação (DI/DSI/DA/DRI-E) averbado
	 * 
	 * @var $nDI  
	*/
	public $nDI;

	/**
	 * Número do Registro de Exportação (RE) averbado
	 * 
	 * @var $nRE  
	*/
	public $nRE;

	/**
	 * 0 - Não enviar para o MDIC;
	 * 1 - Enviar para o MDIC;
	 * 
	 * @var $mdic  
	*/
	public $mdic;
}