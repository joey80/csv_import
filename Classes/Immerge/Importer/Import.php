<?php

namespace Immerge\Importer;

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/scripts/Classes/' . $className . '.php';
});

require '/var/www/scripts/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Immerge\Importer\Models as Models;



/**
 * Import - Importer For Richie Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Import {

    public function __construct() {}
    private function __clone() {}



    public function main() {

        $model = Models::getInstance();

        // Delete all existing patients from the DB
        $model->deleteOldAppointments();

        // Read through the subfolders inside the cron folder
        $root_path = '/var/www/html/crons/';
        $scanned_folders = array_diff(scandir($root_path), array('..', '.'));

        // Scan the files that are inside each of the subfolders inside of the cron folder
        foreach ($scanned_folders as &$folder) {
            $scanned_file = array_diff(scandir($root_path . '/' . $folder), array('..', '.'));

            // Get the file information
            $theFile = pathinfo($scanned_file[2]);
            $theFile_name = $theFile['basename'];
            $theFile_ext = strtolower($theFile['extension']);

            // Check to make sure its a .csv file and then process it
            if ($theFile_ext === 'csv') {

                // Clean up the temp tables
                $model->deleteTempTables();
                $model->createTempTables();

                echo 'Reading ' . $theFile_name . PHP_EOL;

                $shipping_code = substr($folder, 0,8);
                $data = '';
                $field_ft = 'none';
                $data_initial_count = 0;
                $titles_insertion_count = 0;
                $data_insertion_count = 0;
                
                // Load the .csv file with PHPSpreadsheet
                $input_file = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $spreadsheet = $input_file->load($root_path . $folder . '/' . $theFile_name);
                echo 'import started for ' . $shipping_code . PHP_EOL;

                // Get the author id
                $author_id = $model->titleAuthorFind($shipping_code);
                echo 'The author_id is: ' . $author_id . PHP_EOL;

                // Count how many titles for 'Patients' channel (id 4)
                // Note: This will always be zero?
                $titles_initial_count = $model->titlesTableCount();
                echo 'The total titles are: ' . $titles_initial_count . PHP_EOL;


                // For testing purposes
                // $cellValue = $spreadsheet->getActiveSheet()->getCell('A1')->getValue();
                // echo 'A1 is ' . $cellValue . PHP_EOL;
            }
            
        }

        unset($value);
    }
  
}