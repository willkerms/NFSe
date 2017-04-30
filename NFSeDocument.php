<?php
namespace NFSe;

class NFSeDocument extends \DOMDocument {

	public function __construct($version = "1.0", $encoding = "UTF-8") {
		parent::__construct($version, $encoding);
		$this->registerNodeClass("DOMElement", 'NFSe\NFSeElement');
	}

	/**
	 * getValue
	 *
	 * @param  DOMElement $node
	 * @param  string     $name
	 * @return string
	 */
	public function getValue($node, $name){

		if (empty($node))
			return null;

		//return !empty($node->getElementsByTagName($name)->item(0)->nodeValue) ? html_entity_decode($node->getElementsByTagName($name)->item(0)->nodeValue, ENT_QUOTES, $this->encoding) : null;
		return !empty($node->getElementsByTagName($name)->item(0)->nodeValue) ? $node->getElementsByTagName($name)->item(0)->nodeValue : null;
	}
}