<?php
namespace src\Bdd;

use src\Config\Config;

class ConnexionPDO
{
    private static $_db = null;

    public static function open_db($host, $dbName, $login, $password)
    {
        if (self::$_db !== null)
            throw new Exception('Une base de donnée est déjà ouverte.');

            self::$_db = new \PDO("mysql:dbname=$dbName;host=$host;charset=utf8", $login, $password);
            self::$_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
    }

    public static function get_db()
    {
        if (self::$_db === null)
        self::open_db(Config::getDbHost(), Config::getDbName(), Config::getDbLogin(), Config::getDbPassword());

        return self::$_db;
    }
}
