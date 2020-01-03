<?php

namespace NOrmGenerator\ClassModelGenerator\Meta\Exception;


class PropertyGeneratorException extends \Exception{

	const MSG_PROPERTY_IS_ALREADY_ADDED='Property is already added';
	const MSG_PROPERTY_TYPE_IS_NOT_DEFINED='Property type is not defined';

	const PROPERTY_IS_ALREADY_ADDED=101;

	const PROPERTY_TYPE_IS_NOT_DEFINED=102;

}