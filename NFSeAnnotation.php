<?php
namespace NFSe;

use PQD\PQDAnnotation;

/**
 * Anotações das classes filhas de NFSeGenericoInfRps/NFSeGenericoInfDPS (campos exclusivos de um emissor).
 * Acrescenta ao PQDAnnotation a anotação @if(<expressão PHP>): condição do {@if<Campo>} do campo anotado com @field,
 * disponível em getField(<Campo>)['if'].
 *
 * @since 2026-09-28
 * @author Victor Hugo Benatti
 */
class NFSeAnnotation extends PQDAnnotation {

	/**
	 * Campos anotados com @field, com a chave 'if' nos que têm @if
	 *
	 * @return array
	 */
	public function getFields(){

		if(isset(self::$annotation[$this->class]['fields']))
			return self::$annotation[$this->class]['fields'];

		parent::getFields();

		preg_match_all('/(\/\*(.|\s)+?(\*\/))/', file_get_contents($this->class), $matches);

		foreach($matches[0] as $comment){
			if(preg_match('/\@field\([^)]*name=([^,)]+)/', $comment, $field) && preg_match('/\@if\((.*)\)\s*$/m', $comment, $if) && isset(self::$annotation[$this->class]['fields'][$field[1]]))
				self::$annotation[$this->class]['fields'][$field[1]]['if'] = $if[1];
		}

		return self::$annotation[$this->class]['fields'];
	}
}
