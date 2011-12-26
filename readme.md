## XML2SQL

Converts XML dumps (currently only from MySQL dumps) to SQL queries in different formats.

* http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_xml

Using the ```--createall``` option the script will do "allInOne (step)":

1. Create a dummy database.
2. Read a joomla.sql install file for MySQL and "install" it to the created database.
3. Create a XML dump from the database. This will be the base for the following SQL install files.
4. Create install.sql files

-- Supported formats: MySQL, SQLite, Postgres, ...

### Other options
* ```-i <filename>``` Input file
* ```-o <filename>``` Output file
* ```--format <format>``` SQL output format (mysql, sqlite, postgres)

* ```--create``` Create the XML dump from a Joomla! SQL file.