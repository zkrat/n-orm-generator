<?php


namespace NOrmGenerator\ClassModelGenerator\Meta\Traits;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;

trait TraitMethodBuilder {


	protected function getDataProperty(string  $tableName=''):string {
		$subTableName= $tableName=='' ? '' : StringManipulator::underscoreToCamelCase($tableName,true);
		return 'data'.$subTableName;
	}


	protected  function getRefClassMethod(string $className):string{
		return 'ref'.$className;
	}

	protected  function getAssocClassMethod(string $className):string{
		return 'assoc'.$className;
	}

	protected function getAddClassMethodParameter(string $className,bool $includeDolar=false):string{
		$parameter=lcfirst($className);
		if ($includeDolar)
			$parameter='$'.$parameter;

		return $parameter;
	}

	protected  function getAddClassMethod(string $className):string{
		return 'add'.$className;
	}

}