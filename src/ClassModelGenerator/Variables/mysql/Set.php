<?php


namespace NOrmGenerator\ClassModelGenerator\Variables\MySql;


use NOrmGenerator\ClassModelGenerator\Variables\AbstractVariables;

class Set extends AbstractVariables {

	public static function create( $value ) {
		$class= new static();
		$class->value= explode(',',$value);
		return $class;
	}

	public function __toString():string {
		return implode(',',$this->value);
	}
}