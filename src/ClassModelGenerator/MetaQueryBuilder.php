<?php


namespace NOrmGenerator\ClassModelGenerator;


use NOrmGenerator\ClassModelGenerator\Exception\MetaQueryBuilderException;
use Nette\Database\Context;

class MetaQueryBuilder {


	public static function createMetaSqlQuery(Context $context):IMetaSqlQuery{
		$className=get_class($context->getConnection()->getSupplementalDriver());
		switch ($className){
			case 'Nette\Database\Drivers\MySqlDriver':
				return new MySqlMetaQuery();
			default:
				$msg=sprintf(MetaQueryBuilderException::MSG_META_QUERY_BUILDER_NOT_IMPLEMENNTED,$className);
				throw new MetaQueryBuilderException($msg,MetaQueryBuilderException::META_QUERY_BUILDER_NOT_IMPLEMENNTED);
		}



	}


}