<?php
namespace NOrmGenerator\ClassModelGenerator\Traits;

use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;

trait TraitClassFill {


	public static function create(array $array){
		$class=new static();
		foreach ($array as $property => $value){
			$variable=StringManipulator::underscoreToCamelCase($property);
			$class->$variable=$value;
		}
		return $class;
	}

}