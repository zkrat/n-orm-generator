<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;

use NOrmGenerator\ClassModelGenerator\Meta\IMetaTableColumnsForeinKeys;

class ArrayKeysMethodMethodGenerator extends AbstractMethodGenerator {

	public function generate( array $config = [] ) {
		$array= (isset($config['array']) && is_array($config['array'])) ? $config['array'] : [];
		$this->createMethod($this->tableName);

		foreach ($array as $metaTableColumnsForeinKeysMySqlDriver){
			/**
			 * @var IMetaTableColumnsForeinKeys $metaTableColumnsForeinKeysMySqlDriver
			 */
			if ($metaTableColumnsForeinKeysMySqlDriver instanceof IMetaTableColumnsForeinKeys)
			$this->createMethod($metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName());
		}
	}

	private function createMethod(string  $tableName) {
		$property=$this->getDataPropertyByClass($tableName);
		$getAllIdsMethod=$this->metaVariable->getClassListGetAllIdsArrayMethodFromString($tableName);
		$getIdsMethod=$this->class->addMethod($getAllIdsMethod);
		$getIdsMethod->setBody('return array_keys($this->'.$property.');');
	}


}