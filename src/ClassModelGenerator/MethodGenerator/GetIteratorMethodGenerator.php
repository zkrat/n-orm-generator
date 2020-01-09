<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use NOrmGenerator\ClassModelGenerator\ConstantDefinition;
use NOrmGenerator\ClassModelGenerator\Meta\Traits\TraitMethodBuilder;

class GetIteratorMethodGenerator extends AbstractMethodGenerator {



	public function generate(array $array=[]) {
		$classRow = $this->metaVariable->getClassRowNameFromString($this->tableName);
		$classRowWithNamespace= $this->metaVariable->getClassRowNameFromString($this->tableName,true);

		if(!in_array($classRowWithNamespace,$this->class->getNamespace()->getUses()))
			$this->class->getNamespace()->addUse($classRowWithNamespace);

		 $method= $this->class->addMethod('getIterator');
		 $method->addBody('return parent::getIterator();');
		$method->addComment('@return '.$classRow.'[]');
	}
}