<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use NOrmGenerator\ClassModelGenerator\ConstantDefinition;
use NOrmGenerator\ClassModelGenerator\Meta\IMetaTableColumnsForeinKeys;

class AssocClassMethodGenerator extends AbstractMethodGenerator {

	public function generate( array $config = [] ) {


		$array= (isset($config['array']) && is_array($config['array'])) ? $config['array'] : [];

		foreach ($array as $metaTableColumnsForeinKeysMySqlDriver){
			/**
			 * @var IMetaTableColumnsForeinKeys $metaTableColumnsForeinKeysMySqlDriver
			 */
			$this->createMethod($metaTableColumnsForeinKeysMySqlDriver);
		}

	}

	private function createMethod( IMetaTableColumnsForeinKeys $metaTableColumnsForeinKeysMySqlDriver ) {
		$classRowName=$this->metaVariable->getClassRowNameFromString($this->tableName);

		$tableName=$metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName();

		$subClassListVariable=$this->metaVariable->getClassListVariableFromString($tableName,false);
		$subClassListDollarVariable=$this->metaVariable->getClassListVariableFromString($tableName);
		$subClassRowDollarVariable=$this->metaVariable->getClassRowVariableNameFromString($tableName);
		$subClassRow=$this->metaVariable->getClassRowNameFromString($tableName);

		$fullSubClassList=$this->metaVariable->getClassListFromString($tableName,true);
		$subMethodName=$this->getAssocClassMethod($this->metaVariable->getClassListFromString($tableName));

		$subMethod=$this->class->addMethod($subMethodName);
		$subMethod->addParameter($subClassListVariable)->setType($fullSubClassList);
		$subMethod->addBody('foreach ('.$subClassListDollarVariable.' as '.$subClassRowDollarVariable.'){');
		$subMethod->addBody(ConstantDefinition::TAB1.'/**');
		$subMethod->addBody(ConstantDefinition::TAB1.'* @var '.$subClassRow.' '.$subClassRowDollarVariable);
		$subMethod->addBody(ConstantDefinition::TAB1.'*/'.PHP_EOL);

		$dataProperty=$this->getDataProperty($tableName);
		$classRowDollarVariable=$this->metaVariable->getClassRowVariableNameFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName());
		// todo: PrimaryKey id by referce table


		$subMethod->addBody(ConstantDefinition::TAB1.'if(isset($this->'.$dataProperty.'['.$subClassRowDollarVariable.'->getId()])){');
		$subMethod->addBody(ConstantDefinition::TAB2.'$array = $this->'.$dataProperty.'['.$subClassRowDollarVariable.'->getId()];');

		$subMethod->addBody(ConstantDefinition::TAB2.'/**');
		$subMethod->addBody(ConstantDefinition::TAB2.'* @var '.$classRowName.'[] $array');
		$subMethod->addBody(ConstantDefinition::TAB2.'*/'.PHP_EOL);
		$subMethod->addBody(ConstantDefinition::TAB2.' foreach ($array as '.$classRowDollarVariable.'){');

		$subMethod->addBody(ConstantDefinition::TAB3.'/**');
		$subMethod->addBody(ConstantDefinition::TAB3.'* @var '.$classRowName.' '.$classRowDollarVariable);
		$subMethod->addBody(ConstantDefinition::TAB3.'*/'.PHP_EOL);
		$subMethod->addBody(ConstantDefinition::TAB3.$classRowDollarVariable.'->set'.$subClassRow.'('.$subClassRowDollarVariable.');');
		$subMethod->addBody(ConstantDefinition::TAB3.$subClassRowDollarVariable.'->'.$this->getAddClassMethod($classRowName).'('.$classRowDollarVariable.');');

		$subMethod->addBody(ConstantDefinition::TAB2.'}');
		$subMethod->addBody(ConstantDefinition::TAB1.'}');
		$subMethod->addBody('}');
	}
}