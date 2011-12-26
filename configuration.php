<?php
class JConfig
{
    /* The script will search in this path for the file installation/sql/mysql/joomla.sql */
    public $jbase = '/home/elkuku/eclipsespace/indigogit3/joomla-cms-git-1';

    /* Credentials of a database user allowed to create dbs */
    public $host = 'localhost';
    public $user = 'root';
    public $password = '';

    /* The "dummy" database that will be created to generate the XML export. Will be deleted when the script ends */
    public $db = 'aaatestdb';


    /* Path to MySQL bin folder - empty for system default */
    public $mysqlpath = '/opt/lampp/bin';
}
