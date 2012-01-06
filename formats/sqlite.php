<?php
/**
 * Format XML database dumps to SQLite format.
 *
 * @link http://www.sqlite.org/datatype3.html
 */
class Xml2SqlFormatSQLite extends Xml2SqlFormatter
{
    protected $quoteString = '';

    /**
     * (non-PHPdoc)
     * @see Xml2SqlFormatter::formatCreate()
     */
    public function formatCreate(SimpleXMLElement $create)
    {
        $tableName = (string)$create->attributes()->name;

        $tableName = str_replace($this->options->get('prefix'), '#__', $tableName);

        $fields = array();

        $primaryKeySet = false;

        $affinityTypes = array(
            'INTEGER' => array('int'),
            'TEXT' => array('char', 'text', 'clob'),
            'NONE' => array('blob'),
            'REAL' => array('real', 'floa', 'doub'),
        );

        foreach ($create->field as $field)
        {
            $attribs = $field->attributes();

            $as = array();

            $as[] = (string)$attribs->Field;

            $type = (string)$attribs->Type;

            $type = str_replace(' unsigned', '', $type);

            $affinity = '';

            if( ! $affinity)
            {
                foreach ($affinityTypes as $aType => $cTypes)
                {
                    if($affinity)
                    continue;

                    foreach ($cTypes as $cType)
                    {
                        if(false !== strpos($type, $cType))
                        {
                            $affinity = $aType;

                            continue 2;
                        }
                    }//foreach
                }//foreach
            }

            if( ! $affinity)
            {
                $affinity = 'NUMERIC';
            }

            $as[] = $affinity;

            if('PRI' == (string) $attribs->Key
            && ! $primaryKeySet)
            {
                $as[] = 'PRIMARY KEY';
                $primaryKeySet = true;
            }

            if(0)//@todo - we ditch NOT NULL for now,as SQLite is very strict about it :(
            {
                if('NO' == (string) $attribs->Null
                && 'auto_increment' != (string)$attribs->Extra)
                $as[] = 'NOT NULL';
            }

            $default = (string) $attribs->Default;

            if('' != $default)
            $as[] = "DEFAULT '$default'";

            if('auto_increment' == (string)$attribs->Extra)
            $as[] = 'AUTOINCREMENT';

            $fields[] = implode(' ', $as);
        }//foreach

        $s = array();

        $s[] = '';
        $s[] = '-- Table structure for table '.$tableName;
        $s[] = '';
        $s[] = 'CREATE TABLE IF NOT EXISTS '.$tableName.' (';
        $s[] = implode(",\n", $fields);
        $s[] = ');';

        return implode("\n", $s);
    }//function

    /**
     * (non-PHPdoc)
     * @see Xml2SqlFormatter::formatInsert()
     */
    public function formatInsert(SimpleXMLElement $insert)
    {
        if( ! isset($insert->row->field))
        return '';

        $tableName = (string)$insert->attributes()->name;

        $tableName = str_replace($this->options->get('prefix'), '#__', $tableName);

        $keys = array();
        $values = array();

        foreach ($insert->row->field as $field)
        {
            $keys[] = (string) $field->attributes()->name;
        }//foreach

        $s = array();

        $s[] = '';
        $s[] = '-- Table data for table '.$tableName;
        $s[] = '';
        $s[] = 'INSERT INTO '.$tableName;

        $started = false;

        foreach ($insert->row as $row)
        {
            $vs = array();

            $i = 0;

            foreach ($row->field as $field)
            {
                // ''escape'' single quotes by prefixing them with another single quote
                $f = str_replace("'", "''", (string) $field);

                $vs[] =($started) ? "'".$f."'" : "'".$f."' AS ".$keys[$i++];
            }//foreach

            if( ! $started)
            {
                $s[] = '      SELECT '.implode(', ', $vs);
            }
            else
            {
                $s[] = 'UNION SELECT '.implode(', ', $vs);
            }

            $started = true;
        }//foreach

        $s[] = ';';

        return implode("\n", $s);
    }//function

	/**
	 * (non-PHPdoc)
	 * @see Xml2SqlFormatter::formatTruncate()
	 */
	public function formatTruncate(SimpleXMLElement $tableStructure)
	{
		$tableName = str_replace($this->options->get('prefix'), '#__', (string)$tableStructure->attributes()->name);

		return 'DELETE FROM '.$tableName.";\n";
	}

}//class
