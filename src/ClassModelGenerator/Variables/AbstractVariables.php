<?php


namespace NOrmGenerator\ClassModelGenerator\Variables;


abstract class AbstractVariables implements IVariable {

	const META_SELECT='%column%';

	const META_SUBSTITUTION='%column%';

	protected $value;

	public static function create(  $value ) {
		$class= new static();
		$class->value= $value;
		return $class;
	}

	public function __toString():string {
		return $this->value;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
}