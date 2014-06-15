<?php

namespace Application\Format;

use Joomla\Registry\Registry;

abstract class Formatter
{
    /**
     * @var Registry
     */
    protected $options;

    protected $quoteString = '';

	protected $reserved = [];

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = new Registry($options);
    }

    /**
     * Format a SQL CREATE TABLE statement
     *
     * @param \SimpleXMLElement $tableStructure The CREATE TABLE block
     *
     * @return string Formatted SQL statement
     */
    abstract public function formatCreate(\SimpleXMLElement $tableStructure);

    /**
     * Format a SQL INSERT statement
     *
     * @param \SimpleXMLElement $tableStructure The INSERT block
     *
     * @return string Formatted SQL statement
     */
    abstract public function formatInsert(\SimpleXMLElement $tableStructure);

	/**
	 * Format a SQL TRUNCATE TABLE statement
	 *
	 * @param \SimpleXMLElement $tableStructure The TRUNCATE block
	 *
	 * @return string Formatted SQL statement
	 */
	abstract public function formatTruncate(\SimpleXMLElement $tableStructure);

    /**
     * Quote a string.
     *
     * @param string $string The string to quote
     *
     * @return string
     */
    protected function quote($string)
    {
        return $this->quoteString.$string.$this->quoteString;
    }
}
