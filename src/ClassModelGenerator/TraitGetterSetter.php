<?php


namespace NOrmGenerator\ClassModelGenerator;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;
use Exception;
use Model\DbDriver\MetaTableColumnsMySqlDriver;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

trait TraitGetterSetter {

	protected $getter=false;

	protected $setter=false;

	/**
	 * @var MetaVariable
	 */
	protected $metaVariable;

	/**
	 *
	 */
	public function generateGetter(): void {
		$this->getter=true;
	}

	/**
	 *
	 */
	public function generateSetter(): void {
		$this->setter=true;
	}


	protected function createSetter(ClassType $class,string $propertyName,string $propertyType,string $commentPropertyType=null,$selfReturm=false) {
		if ('ClassModelGenerator\Variables\MySql\Set'==$commentPropertyType)
			throw new Exception();
		$this->createProperty($class,$propertyName,$commentPropertyType);
		if($this->setter){
			$method=$class->addMethod('set'.ucfirst($propertyName));


			$parameter=$method->addParameter($propertyName);

			if (strlen($propertyType)>0)
				$parameter->setTypeHint($propertyType);

			$method->addBody('$this->'.$propertyName.' = $'.$propertyName.';');

			if (is_string($commentPropertyType) && strlen($commentPropertyType) > 0)
				$method->addComment( '@var ' . $commentPropertyType . ' $' . $propertyName);

			if($selfReturm && !is_null($class->getName()) && $class->getNamespace() instanceof PhpNamespace){
				$method->setReturnType($class->getNamespace()->getName().'\\'.$class->getName());
				$method->addComment('@return '.$class->getName());
				$method->addBody('return $this;');
			}else{
				$method->setReturnType('void');
			}
		}
	}

	protected function createGetter(ClassType $class,string $propertyName,string $propertyType,string $commentPropertyType=null ,bool $nullable=false) {
		if($this->getter){
			$method=$class->addMethod('get'.ucfirst($propertyName));
			$method->setBody('return $this->'.$propertyName.';');


			if (is_string($commentPropertyType) &&strlen($commentPropertyType)>0){
				$method->addComment('@return '.$commentPropertyType);
				$method->setReturnType($propertyType)
				       ->setReturnNullable($nullable);
			}

		}

		if (!$class->hasProperty($propertyName)){
			$property=$class->addProperty($propertyName);
			$property->addComment('@var '.$commentPropertyType);
		}
	}

	protected function createGetterFromMetaTableColoumnsMySqlDriver(ClassType $class,MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver){
		$dbClassName =$this->metaVariable->getClassRowName($metaTableColoumnsMySqlDriver);
		$propertyName=$this->metaVariable->getClassRowVariableName($metaTableColoumnsMySqlDriver,false);
		$namespaceDbClassName=$this->metaVariable->getClassRowName($metaTableColoumnsMySqlDriver,true);
		$this->createGetter($class,$propertyName,$namespaceDbClassName,$dbClassName);
	}



	private function createProperty(ClassType $class,string $propertyName,string $commentPropertyType=null){
		if(!$class->hasProperty($propertyName)){
			$propertyClass= $class->addProperty($propertyName);

			if (is_string($commentPropertyType) && strlen($commentPropertyType)>0)
				$propertyClass->setComment('@var '.$commentPropertyType);
			$propertyClass->setVisibility( ClassType::VISIBILITY_PRIVATE);

		}
	}



	private function getBasicPropertyName(string $propertyName):string {
		if(strpos($propertyName,'_')!==FALSE){
			$basicProperty=StringManipulator::underscoreToCamelCase($propertyName);
		}
		else
			$basicProperty=lcfirst($propertyName);
		if($basicProperty!==$propertyName)
			throw new \Exception();
		return $basicProperty;
	}

}