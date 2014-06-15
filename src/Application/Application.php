<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 02.06.14
 * Time: 10:52
 */

namespace Application;

use Application\Exporter\MySQLi as MySQLiExporter;
use Application\Format\Formatter;

use Joomla\Application\AbstractCliApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

/**
 * CLI application for installing the tracker application
 *
 * @since  1.0
 */
class Application extends AbstractCliApplication
{
	/**
	 * @var Formatter
	 */
	private $formatter = null;

	protected $dbOptions;

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the AbstractApplication::__construct method.
	 * This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	protected function initialise()
	{
		$path = realpath(JPATH_ROOT . '/config/configuration.json');

		if (!$path)
		{
			throw new \RuntimeException('No configuration found.');
		}

		$config = new Registry(file_get_contents($path));

		$this->setConfiguration($config);
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
			$this->out('|-------------------------|');
			$this->out('|          XML2SQL        |');
			$this->out('|                         |');
			$this->out('|   2011 by NiK (elkuku)  |');
			$this->out('|-------------------------|');
			$this->out();

			$this->dbOptions = array(
				'driver' => $this->get('dbtype', 'mysqli'),
				'host' => $this->get('host'),
				'user' => $this->get('user'),
				'password' => $this->get('password'),
				'database' => $this->get('db'),
				'prefix' => $this->get('dbprefix'),
				'select' => false,
			);

			$sampleData = ($this->input->get('sampledata')) ? '.sampledata' : '';

			if($this->input->get('create'))
			{
				$this->create();
			}
			elseif($this->input->get('createall'))
			{
				$this->create();

				$input = JPATH_ROOT . '/build/xml2sql-created.xml';

				foreach (new \DirectoryIterator(JPATH_ROOT . '/src/Application/Format') as $fInfo)
				{
					if($fInfo->isDot() || 'Formatter.php' == $fInfo->getBasename())
					{
						continue;
					}

					$format = $fInfo->getBasename('.php');

					$output = JPATH_ROOT . '/build/xml2sql-created.'.$format.$sampleData.'.sql';

					$this->createSql($input, $output, $format);
				}//foreach
			}
			else
			{
				$input = $this->input->get('i', '', 'string');
				$output = $this->input->get('o', '', 'string');
				$format = $this->input->get('format');

				$this->createSql($input, $output, $format);
			}

			$this->out();
			$this->out('Finished =;)');
			$this->out();
	}

	private function createSql($input, $output, $format)
	{
		if( ! $input || ! $output || ! $format)
		{
			throw new \Exception('Missing values. Usage: -i <inputfile> -o <outputfile> --format <format>', 1);
		}

		$this->out();
		$this->out('Format: '.$format);
		$this->out('Input:  '.$input);
		$this->out('Output: '.$output);
		$this->out();

		$className = '\\Application\\Format\\' . $format;

		if( ! class_exists($className))
		{
			throw new \Exception(sprintf('Required class "%1$s" not found', $className), 1);
		}

		$this->formatter = new $className(['prefix' => $this->input->get('prefix', 'xxxxx_')]);

		$xml = simplexml_load_file($input);

		if( ! $xml)
		{
			throw new \Exception('Invalid xml file: '.$input, 1);
		}

		$sql = '';
		$cnt = 0;

		if($this->input->get('sampledata'))
		{
			// To install the sample data, first we empty all the tables
			foreach ($xml->database->table_structure as $tableStructure)
			{
				$sql .= $this->formatter->formatTruncate($tableStructure);
				$cnt ++;
			}//foreach

			$this->out(sprintf('Processed %d truncate table queries.', $cnt));
		}
		else
		{
			//-- Process "CREATE TABLE" stanetments
			foreach ($xml->database->table_structure as $create)
			{
				$sql .= $this->formatter->formatCreate($create);
				$cnt ++;
			}//foreach

			$this->out(sprintf('Processed %d create queries.', $cnt));
		}

		$cnt = 0;

		//-- Process "INSERT" statements
		foreach ($xml->database->table_data as $insert)
		{
			$sql .= $this->formatter->formatInsert($insert);
			$cnt ++;
		}//foreach

		$this->out(sprintf('Processed %d insert queries.', $cnt));

		if( ! File::write($output, $sql))
		{
			throw new \Exception('Unable to generate the output file at: '.$output, 1);
		}

		$this->out(sprintf('Output file created at: %s', $output));
	}

	private function create()
	{
		$jBase = $this->get('jbase');

		$this->out('JBase:'.$jBase);

		if( ! is_dir($jBase))
		{
			throw new \Exception('Invalid $jbase set in configuration.php');
		}

		if( ! is_dir($jBase.'/installation'))
		{
			throw new \Exception('No installation dir found in specified $jbase dir set in configuration.php');
		}

		$this->out('Create db...', false);

		$db = DatabaseDriver::getInstance($this->dbOptions);

		$db->setQuery('DROP DATABASE IF EXISTS '.$this->get('db'))->execute();

		$db->setQuery('CREATE DATABASE '.$this->get('db'))->execute();

		$db->setQuery('USE '.$this->get('db'))->execute();

		$this->out('ok');

		$this->out('Fill db...', false);

		$sql = file_get_contents($jBase . '/installation/sql/mysql/joomla.sql');

		if($this->input->get('sampledata'))
		{
			$this->out('with sample data...', false);
			$sql .= file_get_contents($jBase . '/installation/sql/mysql/sample_data.sql');
		}

		$queries = $db->splitSql($sql);

		$this->out(sprintf('Found %d queries...', count($queries)), false);

		$i = 0;

		foreach ($queries as $i => $query)
		{
			if($i && $i / 10 == floor($i / 10))
				$this->out($i.'...', false);

			$query = trim($query);

			if( ! $query)
				continue;

			$db->setQuery($query)->execute();
		}

		$this->out(($i + 1).'...', false);

		$this->out('ok');

		$this->out('dump db to XML...', false);

		$this->dbOptions['select'] = true;

		$tables = $db->getTableList();

		$exporter = new MySQLiExporter;

		$contents = (string)$exporter->setDbo($db)->from($tables)->withData()->asXml();

		if( ! File::write(JPATH_ROOT . '/build/xml2sql-created.xml', $contents))
		{
			throw new \Exception('Can not write output file to: '.JPATH_ROOT . '/xml2sql-created.xml');
		}

		$this->out('ok');

		$this->out('delete db...', false);

		$db->setQuery('DROP DATABASE '.$this->get('db'))->execute();

		$this->out('ok');

		return $this;
	}
}
