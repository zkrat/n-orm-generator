<?php


namespace NOrmGenerator\ClassModelGenerator\Logger;


class TextLogger implements ILogger {

	public function message(string $msg, $priority = null ) {
		echo $msg.PHP_EOL;
	}
}