<?php
namespace NOrmGenerator\Console;


use NOrmGenerator\ClassModelGenerator\DbAccessMySqlGenerator;
use NOrmGenerator\ClassModelGenerator\MetaDriverGenerator;
use Nette\Database\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


final class OrmCreateCommand extends Command
{

	/**
	 * @var MetaDriverGenerator
	 */
	public $metaDriverGenerator;

	/**
	 * @var DbAccessMySqlGenerator
	 */
	public $dbAccessMySqlGenerator;


	/** @var string */
	protected static $defaultName = 'orm:create';

	public function __construct($name=null,array $config,Context $context ) {
		parent::__construct($name);

		$this->metaDriverGenerator    = new MetaDriverGenerator($config,$context);
		$this->dbAccessMySqlGenerator = new DbAccessMySqlGenerator($config,$context);
	}


	protected function gen(){

		$this->metaDriverGenerator->generateMetaDrivers();

		$datebase=$this->metaDriverGenerator->getDatabaseName();

		$forienKeyList =$this->metaDriverGenerator->getForeinKeyList($datebase);

		$this->dbAccessMySqlGenerator->generateFromForienKeyList($forienKeyList);
	}

	protected function configure(): void
	{
		$this->setName(static::$defaultName);
		$this->setDescription('orm:create database');
	}


	protected function execute(InputInterface $input, OutputInterface $output): void
	{

		$style = new SymfonyStyle($input, $output);
		if($input->hasArgument('database')){
			$database = $input->getArgument('database');
			$style->note(sprintf('Received value %s from input argument "database"', $database));
		}



		$this->gen();


		$style->success('Command successful');
	}



}

