<?php
namespace NFSe;

class NFSeReturn {

	/**
	 * checkForFault
	 * Verifica se a mensagem de retorno é uma FAULT
	 * Normalmente essas falhas ocorrem devido a falhas internas
	 * nos servidores da SEFAZ
	 *
	 * Função retirada do projeto nfephp-org
	 * @link https://github.com/nfephp-org
	 * @param NFePHP\Common\Dom\Dom $dom
	 * @return string
	 */
	protected static function checkForFault(NFSeDocument $dom) {

		$fault = $dom->getElementsByTagName('Fault')->item(0);
		$reason = '';
		if (isset($fault)) {

			$text = $fault->getElementsByTagName('Text');

			if($text->length == 1)
				$reason = $text->item(0)->nodeValue;
			else{

				$text = $fault->getElementsByTagName('faultcode');

				if($text->length == 1){
					$reason = $text->item(0)->nodeValue;
					$reason .= " " . $fault->getElementsByTagName('faultstring')->item(0)->nodeValue;;
				}
				else{
					$reason = "Fault fora do especificado!";
				}
			}
		}

		return $reason;
	}
}