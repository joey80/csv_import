<?php

namespace Immerge\Importer;

use PDO;

/**
 * Database class
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Database
{

    private static $_db;
    private function __construct()
    {}
    private function __clone()
    {}



      
    /**
     * Get the instance of the PDO connection
     *
     * @return DB  PDO connection
     */

    public static function getInstance()
    {
        include_once '/var/www/html/scripts/config.php';

        if (static::$_db === null) {
            $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8';
            static::$_db = new PDO($dsn, $user, $pass);

            // Raise exceptions when a database exception occurs
            static::$_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return static::$_db;
    }

}
