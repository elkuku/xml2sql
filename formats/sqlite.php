<?php
class Xml2SqlFormatSQLite extends Xml2SqlFormatter
{
    public function formatCreate(SimpleXMLElement $create, array $options = array())
    {
        $options = new JRegistry($options);

        $tableName = (string)$create->attributes()->name;

        $tableName = str_replace($this->prefix, '#__', $tableName);

        $fields = array();

        $primaryKeySet = false;

        foreach ($create->field as $field)
        {
            $attribs = $field->attributes();

            $as = array();

            $as[] = $attribs->Field;

            $type = $attribs->Type;
            $type = str_replace(' unsigned', '', $type);

            if(0 === strpos($type, 'int'))
            $type = 'INTEGER';

            $as[] = $type;

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

    public function formatInsert(SimpleXMLElement $insert, array $options = array())
    {
        if( ! isset($insert->row->field))
        return '';

        $options = new JRegistry($options);

        $tableName = (string)$insert->attributes()->name;

        $tableName = str_replace($this->prefix, '#__', $tableName);

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
//             $values[] = '('.implode(', ', $vs).')';
        }//foreach

//         $s[] = ' ('.implode(', ', $keys).')';
//         $s[] = 'VALUES';
//         $s[] = implode(",\n", $values);
        $s[] = ';';

        return implode("\n", $s);
    }//function

}//class
