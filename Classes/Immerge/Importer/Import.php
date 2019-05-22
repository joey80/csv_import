<?php
namespace Immerge\Importer;

spl_autoload_register(function ($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

require '/var/www/html/scripts/vendor/autoload.php';
use Immerge\Importer\Models as Models;
use Immerge\Importer\Logger as Logger;
use DateTime;

/**
 * Import - Importer For Richey Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Import
{

    public static $model;
    public $sql_data;
    public $the_date;

    public function __construct()
    {
        static::$model = Models::getInstance();
        $this->the_date = (new DateTime('America/New_York'))->format('m-d-Y H:i:s');
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
        // Log the start of the importer
        $patients_log = new Logger('daily_patient_import');
        $import_title = 'Daily Patient';
        $patients_log->write('Starting The ' . $import_title . ' Import Date: ' . $this->the_date);
        $patients_log->write('################################################');
        $patients_log->write(' ');

        // Delete all existing patients from the DB
        static::$model->deleteOldAppointments();

        // Read through the subfolders inside the cron folder
        $root_path = '/var/www/html/crons/';
        $scanned_folders = array_diff(scandir($root_path) , array('..', '.'));
        $total_row_count = 0;

        // Scan the files that are inside each of the subfolders inside of the cron folder
        foreach ($scanned_folders as $folder)
        {
            $scanned_file = array_diff(scandir($root_path . '/' . $folder) , array('..', '.'));

            // Get the file information
            $theFile = pathinfo($scanned_file[2]);
            $theFile_name = $theFile['basename'];
            $theFile_ext = strtolower($theFile['extension']);

            // Check to make sure its a .csv file and then process it
            if ($theFile_ext === 'csv')
            {

                // Clean up the temp tables
                static::$model->deleteTempTables();
                static::$model->createTempTables();

                $patients_log->write('Reading ' . $theFile_name);

                $shipping_code = $folder;

                // Load the .csv file with PHPSpreadsheet
                $input_file = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $spreadsheet = $input_file->load($root_path . $folder . '/' . $theFile_name);
                $patients_log->write('import started for ' . $shipping_code);

                // Get the author id
                $author_id = static::$model->titleAuthorFind($shipping_code);

                // Check to make sure that both table counts match
                $titles_initial_count = static::$model->titlesTableCount();
                $data_initial_count = static::$model->dataTableCount();
                $temp_titles_count = static::$model->tempTitlesCount();
                $temp_data_count = static::$model->tempDataCount();

                if (($titles_initial_count === $temp_titles_count) && ($data_initial_count === $temp_data_count))
                {
                    $patients_log->write('Both tables backed up');
                }
                else
                {
                    $patients_log->write('Import has failed on backup table creation');
                    return;
                }

                // Get the total row count from the .csv
                $highest_row = $spreadsheet->getActiveSheet()->getHighestRow();
                $total_row_count = $total_row_count + $highest_row;

                // Ignore empty .csv's - (All of them should have at least one header row)
                if ($highest_row > 1)
                {

                    $empty_file = false;

                    // Keep track of how many insertions we've done
                    $titles_insertion_count = 0;
                    $data_insertion_count = 0;

                    // Iterate through each row of the .csv - Starting with the second row to exclude the header
                    // NOTE: spreadsheet rows start with 1
                    for ($i = 2; $i <= $highest_row; $i++)
                    {

                        // Get each row from the .csv and return it as an array - We need columns A through L
                        $data_array = $spreadsheet->getActiveSheet()
                            ->rangeToArray('A' . $i . ':L' . $i, null, true, true, true);

                        // Insert into the temp_csv table
                        $this->temp_csv_insert($data_array, $i);

                        // Get the date and then start second insert
                        $date = date("Y-m-d H:i:s");
                        $entry_date = strtotime($date);

                        // Insert the new record into exp_channel_titles and update the counter
                        $this->channel_titles_insert($author_id, $entry_date);
                        $titles_insertion_count++;

                        // Get the auto-encremented entry_id from exp_channel_titles
                        // We need this in order to make the same entry_id into exp_channel_data
                        $last_entry = static::$model->getLastEntry();

                        // Insert the new record into exp_channel_data and update the counter
                        $this->channel_data_insert($last_entry, $author_id);
                        $data_insertion_count++;

                        // Check to make sure that the count matches what was inserted
                        $titles_final_count = static::$model->titlesTableCount();
                        $data_final_count = static::$model->dataTableCount();

                        if (!$titles_final_count === $titles_insertion_count)
                        {
                            $patients_log->write('Import has failed on new channel titles record insertion');
                        }

                        if (!$data_final_count === $data_insertion_count)
                        {
                            $patients_log->write('Import has failed on new channel data record insertion');
                        }
                    }

                }
                else
                {

                    // Move on if the .csv file is empty
                    $patients_log->write('Skipping this one because its blank');
                    $empty_file = true;
                }
            }

            // End of the current .csv
            if (!$empty_file)
            {
                $patients_log->write('import successful for ' . $shipping_code);
            }

            $patients_log->write('---------------------------------');
        }

        // End of the main method

        // Update the total entries for the Patients channel
        static::$model->channelsUpdateSql();

        // Clean up the temp tables
        static::$model->deleteTempTables();
        $patients_log->write('Final cleanup of the temp tables');

        // Log the end of the import
        $patients_log->write(' ');
        $patients_log->write('The Patient Importer Has Completed');
        $patients_log->write('################################################');
        $this->saveTheSettings($patients_log, $import_title, $total_row_count);
    }




    /**
     * temp_csv_insert - Inserts each row of the .csv into the temp_csv table
     *
     * @param array $array - An array of data from a single row of the .csv
     * @param int $i - The current iteration of the loop
     * @return nothing
     */

    public function temp_csv_insert($array, $i)
    {

        // Build an array with the values and clean up the capitalization and lengths
        $this->sql_data = [
            'patient_number' => $array[$i]['A'],
            'patient_lastname' => ucfirst(strtolower($array[$i]['B'])),
            'patient_firstname' => ucfirst(strtolower($array[$i]['C'])),
            'patient_street' => ucfirst(strtolower($array[$i]['D'])),
            'patient_address2' => ucfirst(strtolower($array[$i]['E'])),
            'patient_city' => ucfirst(strtolower($array[$i]['F'])),
            'patient_state' => strtoupper($array[$i]['G']),
            'patient_zip' => substr($array[$i]['H'], 0,4),
            'patient_phone' => substr($array[$i]['I'], 0,9),
            'patient_phone2' => substr($array[$i]['J'], 0,9),
            'patient_phone3' => substr($array[$i]['K'], 0,9),
            'patient_email' => strtolower($array[$i]['L'])
        ];

        // Insert this row from the .csv into temp_csv
        static::$model->csvInsertSql($this->sql_data);
    }




    /**
     * channel_titles_insert - Inserts a new entry with data from each row
     *                         of the .csv into the exp_channel_titles table
     *
     * @param int $author_id - The author_id is based off of the Practice's shipping code
     * @param int $entry_date - The current date in a 10 digit Unix timestamp
     * @return nothing
     */

    public function channel_titles_insert($author_id, $entry_date)
    {

        $sql_data2 = [
            'site_id' => 1,
            'channel_id' => 4,
            'author_id' => $author_id,
            'title' => $this->sql_data['patient_firstname'] . ' ' . $this->sql_data['patient_lastname'],
            'url_title' => strtolower($this->sql_data['patient_firstname'] . '-' . $this->sql_data['patient_lastname']),
            'status' => 'open',
            'status_id' => 1,
            'versioning_enabled' => 'y',
            'allow_comments' => 'n',
            'entry_date' => $entry_date,
            'year' => date("Y"),
            'month' => date("m"),
            'day' => date("j")
        ];

        // Insert the new record into exp_channel_titles and update the counter
        static::$model->titlesInsertNewSql($sql_data2);
    }




    /**
     * channel_data_insert - Inserts a new entry with data from each row
     *                         of the .csv into the exp_channel_data table
     *
     * @param int $last_entry - This is the entry_id from the prior channel_title insert.
     *                          We need this number to be the same for this table
     * @return nothing
     */

    public function channel_data_insert($last_entry, $author_id)
    {

        // Build new array for insert query
        $sql_data3 = [
            'entry_id' => $last_entry,
            'site_id' => 1,
            'channel_id' => 4,
            'field_id_8' => $author_id,
            'field_id_162' => $this->sql_data['patient_number'],
            'field_ft_162' => 'none',
            'field_id_163' => $this->sql_data['patient_lastname'],
            'field_ft_163' => 'none',
            'field_id_164' => $this->sql_data['patient_firstname'],
            'field_ft_164' => 'none',
            'field_id_165' => $this->sql_data['patient_street'],
            'field_ft_165' => 'none',
            'field_id_166' => $this->sql_data['patient_address2'],
            'field_ft_166' => 'none',
            'field_id_167' => $this->sql_data['patient_city'],
            'field_ft_167' => 'none',
            'field_id_168' => $this->sql_data['patient_state'],
            'field_ft_168' => 'none',
            'field_id_169' => $this->sql_data['patient_zip'],
            'field_ft_169' => 'none',
            'field_id_170' => $this->sql_data['patient_phone'],
            'field_ft_170' => 'none',
            'field_id_171' => $this->sql_data['patient_phone2'],
            'field_ft_171' => 'none',
            'field_id_172' => $this->sql_data['patient_phone3'],
            'field_ft_172' => 'none',
            'field_id_173' => $this->sql_data['patient_email'],
            'field_ft_173' => 'none'
        ];

        // Insert the new record into exp_channel_data and update the counter
        static::$model->dataInsertNewSql($sql_data3);
    }




    /**
    * saveTheSettings - Saves the settings of the export to the settings file
    *
    * @return spreadsheet
    */

    public function saveTheSettings($log, $title, $rows)
    {
        $full_title = $title . ' Import';

        $settings = array(
            'report name' => $full_title,
            'status' => 'completed',
            'date' => $this->the_date,
            'rows imported' => $rows
        );

        $log->saveToJSON($settings, str_replace(' ', '-', strtolower($full_title)));
    }




    /**
     * import_shipping_updates - Reads a .csv with all of new shipping updates
     *                           for each order and updates the database
     *
     * @param int $author_id - 
     * @param int $entry_date - 
     * @return nothing
     */

    public function import_shipping_updates()
    {

        $shipping_log = new Logger('shipping_update_import');
        $import_title = 'Shipping Update';
        $root_path = '/var/www/html/shipping/';
        $scanned_file = array_diff(scandir($root_path), array('..', '.'));

        // Get the file information
        $theFile = pathinfo($scanned_file[2]);
        $theFile_name = $theFile['basename'];
        $theFile_ext = strtolower($theFile['extension']);

        $shipping_log->write('Starting The ' . $import_title . ' Import Date: ' . $this->the_date);
        $shipping_log->write('Reading ' . $theFile_name);
        $shipping_log->write(' ');

        // Load the .csv file with PHPSpreadsheet
        $input_file = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $spreadsheet = $input_file->load($root_path . $theFile_name);

        // Get the total row count from the .csv
        $highest_row = $spreadsheet->getActiveSheet()
        ->getHighestRow();

        // Ignore empty .csv's - (All of them should have at least one header row)
        if ($highest_row > 1)
        {
            // Iterate through each row of the .csv - Starting with the second row to exclude the header
            // NOTE: spreadsheet rows start with 1
            for ($i = 2; $i <= $highest_row; $i++)
            {
                // Get each row from the .csv and return it as an array - We need columns A through U
                $data_array = $spreadsheet->getActiveSheet()
                ->rangeToArray('A' . $i . ':U' . $i, null, true, true, true);
                
                $build_array = [
                    'entry_id' => $data_array[$i]['A'],
                    'title' => ucfirst(strtolower($data_array[$i]['B'])),
                    'status' => ucfirst(strtolower($data_array[$i]['C'])),
                    'order_recover_refurb_tracking' => strtoupper($data_array[$i]['D']),
                    'order_recover_refurb_ship_date' => strtotime($data_array[$i]['E']),
                    'order_recover_refurb_ship_comp' => strtoupper($data_array[$i]['F']),
                    'order_adjustment_tracking' => strtoupper($data_array[$i]['G']),
                    'order_adjustment_ship_date' => strtotime($data_array[$i]['H']),
                    'order_adjustment_ship_comp' => strtoupper($data_array[$i]['I']),
                    'order_tracking_d1' => strtoupper($data_array[$i]['J']),
                    'order_ship_date_d1' => strtotime($data_array[$i]['K']),
                    'order_ship_comp_d1' => strtoupper($data_array[$i]['L']),
                    'order_tracking_d2' => strtoupper($data_array[$i]['M']),
                    'order_ship_date_d2' => strtotime($data_array[$i]['N']),
                    'order_ship_comp_d2' => strtoupper($data_array[$i]['O']),
                    'order_tracking_d3' => strtoupper($data_array[$i]['P']),
                    'order_ship_date_d3' => strtotime($data_array[$i]['Q']),
                    'order_ship_comp_d3' => strtoupper($data_array[$i]['R']),
                    'order_tracking_d4' => strtoupper($data_array[$i]['S']),
                    'order_ship_date_d4' => strtotime($data_array[$i]['T']),
                    'order_ship_comp_d4' => strtoupper($data_array[$i]['U'])
                ];
        
                // Update the orders in exp_channel_data
                static::$model->updateShippingOrderData($build_array);
        
                // Update the orders in exp_channel_titles
                static::$model->updateShippingOrderTitles($build_array);

                $i++;

                // Log this entry
                $shipping_log->write('Importing ' . $build_array['title']);
                $shipping_log->write('- entry_id: ' . $build_array['entry_id']);
                $shipping_log->write('- status: ' . $build_array['status']);
                $shipping_log->write('---------------------------------');
            }
        }

        $shipping_log->write(' ');
        $shipping_log->write('The Update Shipping Importer Has Completed');
        $shipping_log->write('################################################');
        $this->saveTheSettings($shipping_log, $import_title, $highest_row);
    }

}
