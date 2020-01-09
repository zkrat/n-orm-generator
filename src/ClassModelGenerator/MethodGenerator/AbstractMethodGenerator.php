<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use Nette\PhpGenerator\ClassType;
use NOrmGenerator\ClassModelGenerator\Meta\Traits\TraitMethodBuilder;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;

abstract class AbstractMethodGenerator implements IMethodGenerator {

	use TraitMethodBuilder;
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


	public static function create(ClassType $class,MetaVariable $metaVariable,$tableName):IMethodGenerator {
		return new static($class,$metaVariable,$tableName);
	}

	public static function quickGenerate(ClassType $class,MetaVariable $metaVariable,$tableName,array $config=[]){
		$class= new static($class,$metaVariable,$tableName);
		$class->generate($config);
	}

	protected function getDataPropertyByClass(string $tableName):string {
		if ($this->class->getName()==$this->metaVariable->getClassListFromString($tableName)){
			// fix property
			$property=$this->getDataProperty();
		}else{
			$property=$this->getDataProperty($tableName);
		}
		return $property;
	}

	abstract public function generate(array $config=[]);

}