<?php
class Xml2SqlSQLite extends Xml2SqlFormatter
{
	public function formatCreate(SimpleXMLElement $create, array $options = array())
	{
		$options = new JRegistry($options);

		$tableName = (string)$create->attributes()->name;

		$tableName = str_replace($this->prefix, '#__', $tableName);

		$s = array();

		$s[] = '';
		$s[] = '-- Table structure for table '.$tableName;
		$s[] = '';

		$s[] = 'CREATE TABLE IF NOT EXISTS '.$tableName.' (';

		$fields = array();

		foreach ($create->field as $field)
		{
			$attribs = $field->attributes();

			$as = array();

			$as[] = $attribs->Field;

			$type = $attribs->Type;
			$type = str_replace(' unsigned', '', $type);

			$as[] = $type;

			if('PRI' == (string) $attribs->Key)
			$as[] = 'PRIMARY KEY';

			if('NO' == (string) $attribs->Null
			&& 'auto_increment' != (string)$attribs->Extra)
			$as[] = 'NOT NULL';

			$default = (string) $attribs->Default;

			if('' != $default)
			$as[] = "DEFAULT '$default'";

			if('auto_increment' == (string)$attribs->Extra)
			$as[] = 'AUTOINCREMENT';

			$fields[] = implode(' ', $as);
		}//foreach

		$s[] = implode(",\n", $fields);

		$s[] = ');';

		$s[] = '';

		return implode("\n", $s);
	}//function

	public function formatInsert(SimpleXMLElement $insert, array $options = array())
	{
		if( ! isset($insert->row->field))
		return '';

		$options = new JRegistry($options);

		$tableName = (string)$insert->attributes()->name;

		$tableName = str_replace($this->prefix, '#__', $tableName);

		$s = array();

		$s[] = '';
		$s[] = '-- Table data for table '.$tableName;
		$s[] = '';

		$keys = array();

		foreach ($insert->row->field as $field)
		{
			$keys[] = (string) $field->attributes()->name;
		}

		$s[] = 'INSERT INTO '.$tableName.' ('.implode(', ', $keys).')';

		$fields = array();

		$values = array();

		foreach ($insert->row as $row)
		{
			$vs = array();
			foreach ($row->field as $field)
			{
				$vs[] = "'".(string) $field."'";
			}//foreach

			$values[] = '('.implode(', ', $vs).')';
		}//foreach

		$s[] = 'VALUES';

		$s[] = implode(",\n", $values);

		$s[] = ';';

		return implode("\n", $s);
	}//function

}//class
