<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriver;
use Nette\PhpGenerator\ClassType;
use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Meta\IMetaTableColumnsForeinKeys;
use NOrmGenerator\ClassModelGenerator\Meta\Traits\TraitMethodBuilder;

class AddClassRowMethodGenerator extends AbstractMethodGenerator{

	use TraitMethodBuilder;
	public function generate(array $config=[]) {
		$id = isset($config['id']) ? $config['id'] : '';
		$array= (isset($config['array']) && is_array($config['array'])) ? $config['array'] : [];
		$classRowName=$this->metaVariable->getClassRowNameFromString($this->tableName);
		$fullClassRow=$this->metaVariable->getClassRowNameFromString($this->tableName,true);
		$dataProperty=$this->getDataProperty($this->tableName);
		$subClassRow = $this->metaVariable->getClassRowNameFromString($this->tableName);






		$method=$this->class->addMethod($this->getAddClassMethod($classRowName));
		$method->addParameter($this->getAddClassMethodParameter($classRowName))
		        ->setType($fullClassRow);

		$method->addBody('$this->data['.$id.'] = '.$this->getAddClassMethodParameter($classRowName,true) . ';');


		foreach ($array as $metaTableColumnsForeinKeysMySqlDriver) {
			/**
			 * @var IMetaTableColumnsForeinKeys $metaTableColumnsForeinKeysMySqlDriver
			 */
			$tableName=$metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName();
			$subTableName=StringManipulator::underscoreToCamelCase($tableName,true);

			$this->addDataProperty($tableName);

			$dataProperty=$this->getDataProperty($tableName);

			$vatiable=$this->getAddClassMethodParameter($classRowName,true);
			$method->addBody('$this->'.$dataProperty.'['.$vatiable.'->get'.$subTableName.'Id()]['.$id.'] = '.$vatiable. ';');
		}
		$method->addBody( $this->getAddClassMethodParameter($classRowName,true) . '->setParent($this);');
	}

	private function addDataProperty( $tableName ) {
		$dataProperty=$this->getDataProperty($tableName);
		$subClassRow = $this->metaVariable->getClassRowNameFromString($tableName);
		$this->class->addProperty($dataProperty,[])
		            ->setComment('@var '.$subClassRow.'[]')
		            ->setVisibility(ClassType::VISIBILITY_PRIVATE);
	}


}