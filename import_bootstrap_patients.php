<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Import as Import;

/*
|--------------------------------------------------------------------------
| Patient Importer For Richey Lab
|--------------------------------------------------------------------------
|
| This will import a listing of patients for Richey Lab. This is currently
| set up as a cron job that runs nightly. First, it will scrub the database
| of the previous day's patients and then will import new ones. The client has
| FTP access and will upload new .csv files every day. These files will be inside
| a folder that is named after the practice ID or 'shipping code'. Each of these
| folders reside in the 'crons' folder inside the root HTML folder.
| 
| The client will make use of this through the frontend of the Richey Lab site.
| When you create a new order you will notice a drop down select field at the top
| of the "Patient Information" section. It will say, "Select a Patient ...". This
| drop down will be populated with the appropriate patients for that practice
| that were from this import.
|
| @author Joey Leger
| @author Immerge 2019
|
*/

$run = new Import();
$run->main();
