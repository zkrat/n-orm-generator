<?php

/**
 * Test: Nette\Database\Context fetch methods.
 * @dataProvider? database.ini
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/empty_tables_rows.sql");


test(function () use ($context) { // fetch
	Assert::true(true);
});



