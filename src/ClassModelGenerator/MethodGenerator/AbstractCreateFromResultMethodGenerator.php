<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use Nette\PhpGenerator\Method;
use NOrmGenerator\DataCollection\DataCollection;

abstract class AbstractCreateFromResultMethodGenerator extends AbstractMethodGenerator implements IMethodGenerator {



	public function generate(array $config=[]){
		$typeRow= $this->getTypeRow();

		$classRowName=$this->metaVariable->getClassRowNameFromString($this->tableName);

		$variableName=$this->metaVariable->addDolar($classRowName);

		$phpNamespace=$this->class->getNamespace();
		$typeList=$this->getTypeList();
		if (!is_null($typeList))
			$phpNamespace->addUse($typeList);


		$phpNamespace->addUse($typeRow);
		$commentTypeRow=array_search($typeRow,$phpNamespace->getUses());

		$phpNamespace->addUse(DataCollection::class);
		$this->class->setExtends(DataCollection::class);

		$method=$this->class->addMethod('createFromResult');
		$method->setStatic(true);
		$method->addParameter('resultSet')->setType($this->getTypeList());
		$method->addBody('$class= new static();');
		$method->addBody('foreach ($resultSet as $row){');
		$method->addBody('  /**');
		$method->addBody('  * @var '.$commentTypeRow.' $row');
		$method->addBody('  */');

		$this->addArrayRowFromObject($method);

		$method->addBody( '  '.$variableName.' =' . $classRowName . '::create($arrayRow);');
		$method->addBody( '  $class->add' . $classRowName . '('.$variableName.');// TODO: change check');
		$method->addBody('}');
		$method->addBody('return $class;');
	}

	abstract protected function addArrayRowFromObject( Method $method );

	abstract protected function getTypeList():string;

	abstract protected function getTypeRow():string;
}