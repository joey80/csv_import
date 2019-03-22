<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Import as Import;


/**
 * Patient Importer For Richey Lab
 * 
 * Description: This will import a listing of patients for RL. This is currently set up
 *              as a cron job that runs nightly. It first scrubs the database of the
 *              previous day's patients and then will import new ones. The client will
 *              make use of this through the frontend of the Richey Lab site. When a new
 *              order is being created there is a drop down select field at the top of the
 *              "Patient Information" section. It will say, "Select a Patient ...". This
 *              drop down will be populated with the appropriate patients for that practice
 *              that were from this import.
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

$run = new Import();
$run->main();

?>