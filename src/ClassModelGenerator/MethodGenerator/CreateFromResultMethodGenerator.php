<?php


namespace NOrmGenerator\ClassModelGenerator\MethodGenerator;


use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;

class CreateFromResultMethodGenerator extends AbstractCreateFromResultMethodGenerator {


	protected function addArrayRowFromObject( Method $method ) {
//				$method->addBody('  if (!$row instanceof ActiveRow)');
//				$method->addBody('    continue;');
		$method->addBody('  $arrayRow=  $row->toArray();');
	}

	protected function getTypeList():string {
		return Selection::class;
	}

	protected function getTypeRow(): string {
		return  ActiveRow::class;
	}
}