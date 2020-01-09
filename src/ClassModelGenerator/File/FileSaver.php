<?php


namespace NOrmGenerator\ClassModelGenerator\File;


use Model\Entity\BinariesRow;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\FileSystem;
use NOrmGenerator\ClassModelGenerator\Logger\ILogger;
use NOrmGenerator\ClassModelGenerator\Logger\TextLogger;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariableConfiguration;
use NOrmGenerator\TracyAddon\BarReport;
use Tracy\Debugger;

class FileSaver {

	/**
	 * @var MetaVariableConfiguration
	 */
	private $metaVariableConfiguration;

	/**
	 * @var PhpNamespace
	 */
	private $phpClassModel;

	/**
	 * @var string
	 */
	private $className;

	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * FileSaver constructor.
	 *
	 * @param MetaVariableConfiguration $metaVariableConfiguration
	 * @param PhpNamespace $phpClassModel
	 * @param string $className
	 * @param ILogger|null $logger
	 */
	public function __construct( MetaVariableConfiguration $metaVariableConfiguration, PhpNamespace $phpClassModel, string $className ,ILogger $logger=null) {
		$this->metaVariableConfiguration = $metaVariableConfiguration;
		$this->phpClassModel             = $phpClassModel;
		$this->className                 = $className;
		$this->logger= $logger instanceof ILogger ? $logger : new TextLogger();
	}

	/**
	 * @param MetaVariableConfiguration $metaVariableConfiguration
	 * @param PhpNamespace $phpClassModel
	 * @param string $className
	 * @param ILogger|null $logger
	 *
	 * @return FileSaver
	 */
	public static function create( MetaVariableConfiguration $metaVariableConfiguration, PhpNamespace $phpClassModel, string $className ,ILogger $logger=null):FileSaver {
		return new static($metaVariableConfiguration, $phpClassModel, $className ,$logger);

	}

	public function saveFile(){
		$filePath=$this->getFilePath();
		$output=$this->getClassOutput();
		if (!file_exists($filePath) || $this->metaVariableConfiguration->isOverwrite()){

			FileSystem::createDir($this->metaVariableConfiguration->getDir());
			file_put_contents($filePath,$output);
			$this->logger->message($filePath);
			$fullClassname=$this->phpClassModel->getName().'\\'.$this->className;
			if(!class_exists($fullClassname)){
				require_once ($filePath);
			}



		}else{
			Debugger::barDump($filePath,'already generated');
		}
	}

	private function getFilePath(){
		$dir=$this->metaVariableConfiguration->getDir();
		$fileName=$this->className.'.php';
		if (FALSE && $this->className=='MetaTableColumnsForeinKeysMySqlDriver'){
			try{
				throw new \Exception();
			}catch (\Exception $e){
				Debugger::log($e);
			}
		}
		BarReport::addStaticFile($fileName);
		return $dir.DIRECTORY_SEPARATOR.$fileName;
	}



	public function getClassOutput():string{

		$filePath=$this->getFilePath();
		$this->checkClassMembers();

		$output='<?php'.PHP_EOL.PHP_EOL;
		$output.=(string) $this->phpClassModel;
		return  $output;



	}


	private function checkClassMembers() {
		if(file_exists($this->getFilePath()) && class_exists($this->phpClassModel->getName().'\\'.$this->className)){
			$sourceClass=ClassType::from($this->phpClassModel->getName().'\\'.$this->className);

			foreach ($this->phpClassModel->getClasses() as $destinationClassType){
				/**
				 * @var ClassType $destinationClassType
				 */
				if($sourceClass->getName()==$destinationClassType->getName()){

					$this->checkDestinationMemers($sourceClass->getMethods(),$destinationClassType);
					$this->checkDestinationMemers($sourceClass->getProperties(),$destinationClassType);
					$this->checkDestinationMemers($sourceClass->getConstants(),$destinationClassType);

					foreach ($sourceClass->getTraits() as $traitName){
						if(!in_array($traitName,$destinationClassType->getTraits()))
							$destinationClassType->addTrait($traitName);
					}

				}
			}
		}
	}


	private function checkDestinationMemers($array,ClassType $destinationClassType){
		return null;
		foreach ($array as $memberName => $member){
			/**
			 * @var Method $member
			 */
			if(!$destinationClassType->hasProperty($memberName)){
				if (($member instanceof  Method && is_string($member->getBody()) && strlen($member->getBody())>0))
					$destinationClassType->addMember($member);
				else
					dump('unknown member:',$member);
			}
		}
	}

}