<?php
abstract class Xml2SqlFormatter
{
    /**
     * @var string table prefix
     */
    protected $prefix = '';

    public function __construct(array $options = array())
    {
        $options = new JRegistry($options);

        $this->prefix = $options->get('prefix');
    }//function

    abstract public function formatCreate(SimpleXMLElement $create, array $options = array());

	abstract public function formatInsert(SimpleXMLElement $insert, array $options = array());

}//class
