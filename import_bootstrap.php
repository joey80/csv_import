<?php

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Import as Import;

$run = new Import();
$run->main();
?>