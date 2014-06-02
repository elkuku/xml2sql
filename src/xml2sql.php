#!/usr/bin/env php
<?php

use Application\Application;

'cli' == PHP_SAPI || die("\nThis script must be run from the command line interface.\n\n");

/**
 * Turn on strict error reporting during development
 */
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL | E_STRICT);

define('JPATH_ROOT', realpath(__DIR__ . '/..'));

// Load the autoloader
$path = realpath(JPATH_ROOT . '/vendor/autoload.php');

if (!$path)
{
	echo 'ERROR: Composer not properly set up! Run "composer install" or see README.md for more details.' . PHP_EOL;

	exit(1);
}

/* @type Composer\Autoload\ClassLoader $loader */
$loader = include $path;

// Add the namespace for our application to the autoloader.
$loader->add('Application', __DIR__);

try
{
	(new Application)->execute();
}
catch (\Exception $e)
{
	echo "\n\nERROR: " . $e->getMessage() . "\n\n";

	echo $e->getTraceAsString();

	exit($e->getCode() ? : 255);
}
