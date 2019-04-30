<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Export as Export;

/*
|--------------------------------------------------------------------------
| Order Exporter
|--------------------------------------------------------------------------
|
| This will first change all orders in the database that has a status of
| "Accepted" to "Accepted-Exported". After that it will create and download
| an Excel file that consists of all of their orders that has a status of
| "Accepted-Exported". This file gets called from the exporter which can
| be found at /exporter
|
| @author Joey Leger
| @author Immerge 2019
|
*/

$run = new Export('Accepted-Exported', true);
$run->main();

?>