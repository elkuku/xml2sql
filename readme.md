## XML2SQL

Converts XML dumps to SQL queries in different formats.

* http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_xml

### Installation

1. Use [Composer](https://getcomposer.org/) to install dependencies.
2. Copy the file `config/configuration.dist.json` to `config/configuration.json` and adjust the values to meet your system.

### Usage

Execute `bin/xml2sql` from the command line.

### Options

* ```-i <filename>``` Input file
* ```-o <filename>``` Output file
* ```--format <format>``` SQL output format (mysql, sqlite, postgres)

#### Joomla! specific options

* ```--create``` Create the XML dump from a Joomla! SQL file.
* ```--sampledata``` Sample data will be included in the SQL file.

#### Joomla! Create all

Using the ```--createall``` option the script will do "allInOne (step)":

1. Create a dummy database.
2. Read a joomla.sql install file for MySQL and "install" it to the created database.
3. Create a XML dump from the database. This will be the base for the following SQL install files.
4. Create install.sql files in all available formats.

### Supported formats

* MySQL
* SQLite
* PostgreSQL
* `@todo`
