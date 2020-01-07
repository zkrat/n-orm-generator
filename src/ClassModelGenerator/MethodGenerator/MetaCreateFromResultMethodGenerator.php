<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use Nette\Database\ResultSet;
use Nette\Database\Row;

use Nette\PhpGenerator\Method;

class MetaCreateFromResultMethodGenerator extends AbstractCreateFromResultMethodGenerator {


	protected function addArrayRowFromObject( Method $method ) {
		$method->addBody('  $arrayRow= (array) $row;');
	}

	protected function getTypeList(): string {
		return ResultSet::class;
	}
	protected function getTypeRow(): string {
		return  Row::class;
	}
}