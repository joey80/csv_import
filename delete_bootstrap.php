<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Richey as Richey;

/*
|--------------------------------------------------------------------------
| Order Delete
|--------------------------------------------------------------------------
|
| This will delete all order from the database that has a status of closed
| and is older than six months. This will run nightly as a cron and it can
| also be ran from the importer/exporter page.
|
| @author Joey Leger
| @author Immerge 2019
|
*/

$run = new Richey();
$run->delete();

?>