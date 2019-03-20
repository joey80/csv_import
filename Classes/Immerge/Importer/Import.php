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
 * Import - Importer For Richey Lab
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

                $shipping_code = $folder;
                                
                // Load the .csv file with PHPSpreadsheet
                $input_file = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $spreadsheet = $input_file->load($root_path . $folder . '/' . $theFile_name);
                echo 'import started for ' . $shipping_code . PHP_EOL;

                // Get the author id
                $author_id = $model->titleAuthorFind($shipping_code);

                // Check to make sure that both table counts match
                $titles_initial_count = $model->titlesTableCount();
                $data_initial_count = $model->dataTableCount();
                $temp_titles_count = $model->tempTitlesCount();
                $temp_data_count = $model->tempDataCount();

                if (($titles_initial_count === $temp_titles_count) && ($data_initial_count === $temp_data_count)) {
                    echo 'Both tables backed up' . PHP_EOL;
                } else {
                    echo 'Import has failed on backup table creation' . PHP_EOL;
                    return;
                }

                // Get the total row count from the .csv
                $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();

                // Ignore empty .csv's - (All of them should have at least one header row)
                if ($highestRow > 1) {

                    // Keep track of how many insertions we've done
                    $titles_insertion_count = 0;
                    $data_insertion_count = 0;

                    // Iterate through each row of the .csv - Starting with the second row to exclude the header
                    for ($i = 2; $i <= $highestRow; $i++) {

                        // Get each row from the .csv and return it as an array - We need columns A through L
                        $data_array = $spreadsheet->getActiveSheet()->rangeToArray(
                            'A' . $i . ':L' . $i, NULL, TRUE, TRUE, TRUE
                        );

                        // Build an array with the values and clean up the capitalization
                        $sql_data = [
                            'patient_number'    => $data_array[$i]['A'],
                            'patient_lastname'  => ucfirst(strtolower($data_array[$i]['B'])),
                            'patient_firstname' => ucfirst(strtolower($data_array[$i]['C'])),
                            'patient_street'    => ucfirst(strtolower($data_array[$i]['D'])),
                            'patient_address2'  => ucfirst(strtolower($data_array[$i]['E'])),
                            'patient_city'      => ucfirst(strtolower($data_array[$i]['F'])),
                            'patient_state'     => strtoupper($data_array[$i]['G']),
                            'patient_zip'       => $data_array[$i]['H'],
                            'patient_phone'     => $data_array[$i]['I'],
                            'patient_phone2'    => $data_array[$i]['J'],
                            'patient_phone3'    => $data_array[$i]['K'],
                            'patient_email'     => strtolower($data_array[$i]['L']),
                        ];
                        
                        // Insert this row from the .csv into temp_csv
                        $model->csvInsertSql($sql_data);

                        // Get the date and then build a new array
                        $date = date("Y-m-d H:i:s");
                        $entry_date = strtotime($date);

                        $sql_data2 = [
                            'site_id'            => 1,
                            'channel_id'         => 4,
                            'author_id'          => $author_id,
                            'title'              => $sql_data['patient_firstname'] . ' ' . $sql_data['patient_lastname'],
                            'url_title'          => strtolower($sql_data['patient_firstname'] . '-' . $sql_data['patient_lastname']),
                            'status'             => 'open',
                            'versioning_enabled' => 'y',
                            'allow_comments'     => 'n',
                            'entry_date'         => $entry_date,
                            'year'               => date("Y"),
                            'month'              => date("m"),
                            'day'                => date("j")
                        ];

                        // Insert the new record into exp_channel_titles and update the counter
                        $model->titlesInsertNewSql($sql_data2);
                        $titles_insertion_count++;

                        // Get the auto-encremented entry_id from exp_channel_titles
                        // We need this in order to make the same entry_id into exp_channel_data
                        $last_entry = $model->getLastEntry();

                        // Build new array for insert query
                        $sql_data3 = [
                            'entry_id'     => $last_entry,
                            'site_id'      => 1,
                            'channel_id'   => 4,
                            'field_id_162' => $sql_data['patient_number'],
                            'field_ft_162' => 'none',
                            'field_id_163' => $sql_data['patient_lastname'],
                            'field_ft_163' => 'none',
                            'field_id_164' => $sql_data['patient_firstname'],
                            'field_ft_164' => 'none',
                            'field_id_165' => $sql_data['patient_street'],
                            'field_ft_165' => 'none',
                            'field_id_166' => $sql_data['patient_address2'],
                            'field_ft_166' => 'none',
                            'field_id_167' => $sql_data['patient_city'],
                            'field_ft_167' => 'none',
                            'field_id_168' => $sql_data['patient_state'],
                            'field_ft_168' => 'none',
                            'field_id_169' => $sql_data['patient_zip'],
                            'field_ft_169' => 'none',
                            'field_id_170' => $sql_data['patient_phone'],
                            'field_ft_170' => 'none',
                            'field_id_171' => $sql_data['patient_phone2'],
                            'field_ft_171' => 'none',
                            'field_id_172' => $sql_data['patient_phone3'],
                            'field_ft_172' => 'none',
                            'field_id_173' => $sql_data['patient_email'],
                            'field_ft_173' => 'none',
                        ];

                        // Insert the new record into exp_channel_data and update the counter
                        $model->dataInsertNewSql($sql_data3);
                        $data_insertion_count++;

                        // Check to make sure that the count matches what was inserted
                        $titles_final_count = $model->titlesTableCount();
                        $data_final_count = $model->dataTableCount();

                        if (!$titles_final_count === $titles_insertion_count) {
                            echo 'Import has failed on new channel titles record insertion' . PHP_EOL;
                        }

                        if (!$data_final_count === $data_insertion_count) {
                            echo 'Import has failed on new channel data record insertion' . PHP_EOL;
                        }
                    }
                
                } else {

                    // Move on if the .csv file is empty
                    echo 'Skipping this one because its blank' . PHP_EOL;
                }
            }

            // End of the current .csv
            echo 'import successful for ' . $shipping_code . PHP_EOL;
            echo '---------------------------------' . PHP_EOL;
        }
        
        // Unset the value from the foreach loop
        unset($value);

        // Update the total entries for the Patients channel
        $model->channelsUpdateSql();

        // Clean up the temp tables
        $model->deleteTempTables();
        echo 'Final cleanup of the temp tables' . PHP_EOL;
    }
  
}