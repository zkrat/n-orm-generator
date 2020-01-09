<?php


namespace NOrmGenerator\ClassModelGenerator\Meta;


interface IMetaTableColumnsForeinKeys {


	public function getColumnName();


	public function getTableName();


	public function getReferencedColumnName();


	public function getReferencedTableName();

}