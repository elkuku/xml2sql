<?php
abstract class Xml2SqlFormatter
{
    /**
     * @var JRegistry
     */
    protected $options;

    protected $quoteString = '';

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = new JRegistry($options);
    }//function

    /**
     * Format a SQL CREATE TABLE statement
     *
     * @param SimpleXMLElement $create The CREATE TABLE block
     *
     * @return string Formatted SQL statement
     */
    abstract public function formatCreate(SimpleXMLElement $tableStructure);

    /**
     * Format a SQL INSERT statement
     *
     * @param SimpleXMLElement $insert The INSERT block
     *
     * @return string Formatted SQL statement
     */
    abstract public function formatInsert(SimpleXMLElement $tableData);

	/**
	 * Format a SQL TRUNCATE TABLE statement
	 *
	 * @param SimpleXMLElement $insert The INSERT block
	 *
	 * @return string Formatted SQL statement
	 */
	abstract public function formatTruncate(SimpleXMLElement $tableStructure);

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
}//class
