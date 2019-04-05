<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Export as Export;

/*
|--------------------------------------------------------------------------
| Order Exporter For Richey Lab
|--------------------------------------------------------------------------
|
| This will create and download an Excel file that consists of all of their orders
| that has a status of 'Accepted'. This file gets called from the exporter which can
| be found at /exporter. The columns are set up in a specific order at the request of
| Richey Lab.
|
| @author Joey Leger
| @author Immerge 2019
|
*/

$run = new Export('Accepted');
$run->main();

?>