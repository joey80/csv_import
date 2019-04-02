<?php
namespace Immerge\Importer;

spl_autoload_register(function ($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

require '/var/www/html/scripts/vendor/autoload.php';
use Immerge\Importer\Models as Models;

/**
 * Export - Exporter For Richey Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Export
{

    private static $model;
    private static $sql_data;

    public function __construct()
    {
    }
    private function __clone()
    {
    }







    /**
     * main - The main controller for the Importer. It reads through .csv's that are nested
     *        inside folders that reside in the cron folder. It goes through them one by one
     *        in a loop, reads each line and then adds that data to the database.
     *
     * @return nothing
     */

    public function main()
    {

        echo 'Hello from the exporter!';
    }

}
