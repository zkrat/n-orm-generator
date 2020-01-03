<?php


namespace NOrmGenerator\ClassModelGenerator\Meta\Traits;


trait TraitMethodBuilder {

	protected  function getRefClassMethod(string $className):string{
		return 'ref'.$className;
	}

	protected  function getAssocClassMethod(string $className):string{
		if ($className=='NumbersList'){
//			throw new \Exception();
		}



		return 'assoc'.$className;
	}

	protected  function getAddClassMethod(string $className):string{



		return 'add'.$className;
	}

}