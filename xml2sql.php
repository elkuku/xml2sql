#!/usr/bin/php
<?php
define('_JEXEC', 1);

/**
 * Turn on strict error reporting during development
 */
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL | E_STRICT);

/**
 * Bootstrap the Joomla! Platform.
 */
require $_SERVER['JOOMLA_PLATFORM_PATH'].'/libraries/import.php';
// require '/home/elkuku/eclipsespace/indigogit3/joomla-platform-testing/libraries/import.php';

define('JPATH_BASE', dirname(__FILE__));
define('JPATH_SITE', JPATH_BASE);

jimport('joomla.application.cli');
jimport('joomla.database');
jimport('joomla.database.table');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

require 'formatter.php';

JError::$legacy = false;

/**
 * Ian's PullTester
 */
class XML2SQL extends JCli
{
	/**
	 * @var Xml2SqlFormatter
	 */
	private $formatter = null;

	/**
	 * Execute the application.
	 *
	 * @return  void
	 */
	public function execute()
	{
		$input = $this->input->get('i', '', 'string');
		$output = $this->input->get('o', '', 'string');
		$format = $this->input->get('format');

		$this->out('|-------------------------|');
		$this->out('|          XML2SQL        |');
		$this->out('|                         |');
		$this->out('|   2011 by NiK (elkuku)  |');
		$this->out('|-------------------------|');
		$this->out();

		$this->out('Input:  '.$input);
		$this->out('Output: '.$output);
		$this->out();

		if( ! $input || ! $output || ! $format)
		throw new Exception('Missing values. Use -i <inputfile> -o <outputfile> --format <format>', 1);

		$path = JPATH_BASE.'/formats/'.$format.'.php';

		if( ! file_exists($path))
		throw new Exception('Format file not found: '.$path, 1);

		require $path;

		$className = 'Xml2Sql'.ucfirst($format);

		if( ! class_exists($className))
		throw new Exception(sprintf('Required class "%2$s" not found in file %2$s', $className, $path), 1);

		$options = array(
			'prefix' => 'zfyg0_',//@todo..
		);

		$this->formatter = new $className($options);

		$xml = simplexml_load_file($input);

		if( ! $xml)
		throw new Exception('Invalid xml file: '.$input, 1);

		$sql = '';

		foreach ($xml->database->table_structure as $create)
		{
			$sql .= $this->formatter->formatCreate($create);
		}//foreach

		foreach ($xml->database->table_data as $insert)
		{
			$sql .= $this->formatter->formatInsert($insert);
		}//foreach

		if( ! JFile::write($output, $sql))
		throw new Exception('Unable to generate the output file at: '.$output, 1);

		$this->out('Finished =;)');
		$this->out();
	}//function

}//class

try
{
	// Execute the application.
	JCli::getInstance('XML2SQL')->execute();

	exit(0);
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");

	exit($e->getCode());
}//try
