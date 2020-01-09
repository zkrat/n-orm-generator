<?php


namespace NOrmGenerator\ClassModelGenerator\Meta;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use Nette\PhpGenerator\ClassType;
use Tracy\Debugger;

class MetaPropertyGenerator extends CorePropertyGenerator {



	public function addProperty($propertyName, $value) {

		$propertyType=$this->getType($value);

		$className=StringManipulator::getClassnameWithoutNamespace($propertyType);
		$classNameLong=StringManipulator::getLongClasnameWithNamespace($propertyType);
		$usePropertyType=$className;
		if($this->substituteUse){
			if(!isset($this->useArray[$className])){
				$this->useArray[$className]=$propertyType;
				if (strlen($propertyType)>0 && is_object($value)){
					$this->phpNamespace->addUse($propertyType);
				}

			}elseif (isset($this->useArray[$className])  && $this->useArray[$className]!== $propertyType){

				$this->useArray[$classNameLong]=$className. ' as '.$classNameLong;
				if (strlen($className)>0 && is_object($value))
					$this->phpNamespace->addUse($className,$classNameLong);
				$usePropertyType=$classNameLong;
			}
		}
		$basicProperty=StringManipulator::underscoreToCamelCase($propertyName);


		$property=$this->class->addProperty($basicProperty);
		if(is_null($value))
			$property->addComment(' NULL VALUE !!!');
		else
			$property->setVisibility(ClassType::VISIBILITY_PRIVATE)
		               ->addComment('@var '.$usePropertyType);


		$this->createSetter($this->class,$basicProperty,$propertyType,$usePropertyType);
		$this->createGetter($this->class,$basicProperty,$propertyType);
// TODO: symbol		$this->createGetterFromMetaTableColoumnsMySqlDriver();
	}



	private function getType($value):string{
		$type=gettype($value);
		if(is_scalar($value)){
			switch ($type){
				case 'integer':
					return 'int';
				case 'double':
					return 'float';
				case 'string':
					return $type;
				default:
					Debugger::barDump($value,$type);
					return $type;

			}

		}else{

			if (is_array($value))
				return 'array';
			elseif (is_null($value))
				return '';// TODO: set not null parameter!!!
			elseif (is_object($value)){
				$className=get_class($value);
				return $className;
			}

			else
				Debugger::barDump($value,$type.'!!!!');
		}

	}

	private function addUse( string $class ){
		$namespace=substr($class,0,strrpos($class,'\\'));
		if($this->phpNamespace->getName()!=$namespace){
			$this->phpNamespace->addUse($class);
		}

	}

	public function addTrait( string $class ) {
		$this->class->addTrait($class);
		$this->addUse($class);
	}

	public function addImplementArray( array $implemets=[] ) {
		foreach ($implemets as $imp){
			$this->class->addImplement($imp);
			$this->addUse($imp);
		}
	}


}