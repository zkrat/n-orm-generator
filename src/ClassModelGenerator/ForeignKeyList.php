<?php


namespace NOrmGenerator\ClassModelGenerator;


use Model\DbDriver\MetaTableColumnsForeignKeysMySqlDriver;
use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriver;
use Model\DbDriver\MetaTableMySqlDriverList;

class ForeignKeyList {

	private $forienKeyArray=[];
	private $referenceArray=[];

	/***
	 * @var MetaTableMySqlDriverList
	 */
	private $metaTableMySqlDriverList;

	/**
	 * @var MetaDriverGenerator
	 */
	private $metaDriverGenerator;

	/**
	 * @var string
	 */
	private $datebase;


	public function __construct(MetaTableMySqlDriverList $metaTableMySqlDriverList,MetaDriverGenerator $metaDriverGenerator,string $datebase) {
		$this->metaTableMySqlDriverList=$metaTableMySqlDriverList;
		$this->datebase=$datebase;
		$this->metaDriverGenerator=$metaDriverGenerator;

		foreach ($metaTableMySqlDriverList as $metaTableMySqlDriver){
			$metaTableColumnsForenKeyMySqlDriverList=$metaDriverGenerator->getMetaTableColumnsForeignKeyMySqlDriverList($datebase,$metaTableMySqlDriver->getName());

			if($metaTableColumnsForenKeyMySqlDriverList->count()>0){
				foreach ($metaTableColumnsForenKeyMySqlDriverList as $metaTableColumnsForenKeyMySqlDriver){
					/**
					 * @var MetaTableColumnsForeignKeysMySqlDriver $metaTableColumnsForenKeyMySqlDriver
					 */
					$this->forienKeyArray[$metaTableColumnsForenKeyMySqlDriver->getTableName()][$metaTableColumnsForenKeyMySqlDriver->getColumnName()]=$metaTableColumnsForenKeyMySqlDriver;
					$this->referenceArray[$metaTableColumnsForenKeyMySqlDriver->getReferencedTableName()][$metaTableColumnsForenKeyMySqlDriver->getReferencedColumnName()]=$metaTableColumnsForenKeyMySqlDriver;
				}
			}
		}
	}

	/**
	 * @return MetaTableMySqlDriverList
	 */
	public function getMetaTableMySqlDriverList(): MetaTableMySqlDriverList {
		return $this->metaTableMySqlDriverList;
	}

	/**
	 * @return MetaDriverGenerator
	 */
	public function getMetaDriverGenerator(): MetaDriverGenerator {
		return $this->metaDriverGenerator;
	}

	/**
	 * @return string
	 */
	public function getDatebase(): string {
		return $this->datebase;
	}

	public function hasTableForeignKey( string $tableName ):bool {
		return  isset($this->forienKeyArray[$tableName]);
	}

	public function hasTableRreference( string $tableName ) {
		return  isset($this->referenceArray[$tableName]);
	}

	/**
	 * @param string $tableName
	 *
	 * @return MetaTableColumnsForeinKeysMySqlDriver[]
	 */
	public function getTableForeignKeyArray( string $tableName ):array{
		if($this->hasTableForeignKey($tableName) && is_array($this->forienKeyArray[$tableName])){
			return $this->forienKeyArray[$tableName];
		}else{
			return [];
		}
	}

	public function getTableReferenceArray( string $tableName ):array {
		if($this->hasTableRreference($tableName) && is_array($this->referenceArray[$tableName])){
			return  $this->referenceArray[$tableName];
		}else{
			return [];
		}
	}


	}