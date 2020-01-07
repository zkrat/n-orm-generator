<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use Nette\Database\Table\ActiveRow;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;
use NOrmGenerator\DataCollection\DataCollection;

abstract class AbstractCreateFromResultMethodGenerator implements IMethodGenerator {


	/**
	 * @var MetaVariable
	 */
	protected  $metaVariable;

	/**
	 * @var ClassType
	 */
	protected $class;

	/**
	 * @var string
	 */
	protected $tableName;


	public function __construct(ClassType $class,MetaVariable $metaVariable,$tableName) {
		$this->class = $class;
		$this->metaVariable = $metaVariable;
		$this->tableName = $tableName;
	}


	public function generate(){
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