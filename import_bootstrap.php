<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Import as Import;

/*
|--------------------------------------------------------------------------
| Shipping Importer
|--------------------------------------------------------------------------
|
| This will import entries from a spreadsheet for orders that have updated
| shipping information. Every night someone will FTP upload
| a .csv with new information for certain orders. This will import those updates
| change their status and update any additional information such as tracking
| data.
|
| @author Joey Leger
| @author Immerge 2019
|
*/

$run = new Import();
$run->import_shipping_updates();
