<?php
namespace Immerge\Importer;

spl_autoload_register(function ($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

require '/var/www/html/scripts/vendor/autoload.php';
use Immerge\Importer\Models as Models;

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

    public function __construct()
    {
        static::$model = Models::getInstance();
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
     * main - The main controller for the Importer. It reads through .csv's that are nested
     *        inside folders that reside in the cron folder. It goes through them one by one
     *        in a loop, reads each line and then adds that data to the database.
     *
     * @return nothing
     */

    public function main()
    {

        // Delete all existing patients from the DB
        static::$model->deleteOldAppointments();

        // Read through the subfolders inside the cron folder
        $root_path = '/var/www/html/crons/';
        $scanned_folders = array_diff(scandir($root_path) , array('..', '.'));

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

                echo 'Reading ' . $theFile_name . PHP_EOL;

                $shipping_code = $folder;

                // Load the .csv file with PHPSpreadsheet
                $input_file = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $spreadsheet = $input_file->load($root_path . $folder . '/' . $theFile_name);
                echo 'import started for ' . $shipping_code . PHP_EOL;

                // Get the author id
                $author_id = static::$model->titleAuthorFind($shipping_code);

                // Check to make sure that both table counts match
                $titles_initial_count = static::$model->titlesTableCount();
                $data_initial_count = static::$model->dataTableCount();
                $temp_titles_count = static::$model->tempTitlesCount();
                $temp_data_count = static::$model->tempDataCount();

                if (($titles_initial_count === $temp_titles_count) && ($data_initial_count === $temp_data_count))
                {
                    echo 'Both tables backed up' . PHP_EOL;
                }
                else
                {
                    echo 'Import has failed on backup table creation' . PHP_EOL;
                    return;
                }

                // Get the total row count from the .csv
                $highestRow = $spreadsheet->getActiveSheet()
                    ->getHighestRow();

                // Ignore empty .csv's - (All of them should have at least one header row)
                if ($highestRow > 1)
                {

                    $empty_file = false;

                    // Keep track of how many insertions we've done
                    $titles_insertion_count = 0;
                    $data_insertion_count = 0;

                    // Iterate through each row of the .csv - Starting with the second row to exclude the header
                    // NOTE: spreadsheet rows start with 1
                    for ($i = 2; $i <= $highestRow; $i++)
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
                            echo 'Import has failed on new channel titles record insertion' . PHP_EOL;
                        }

                        if (!$data_final_count === $data_insertion_count)
                        {
                            echo 'Import has failed on new channel data record insertion' . PHP_EOL;
                        }
                    }

                }
                else
                {

                    // Move on if the .csv file is empty
                    echo 'Skipping this one because its blank' . PHP_EOL;
                    $empty_file = true;
                }
            }

            // End of the current .csv
            if (!$empty_file)
            {
                echo 'import successful for ' . $shipping_code . PHP_EOL;
            }

            echo '---------------------------------' . PHP_EOL;
        }

        // Update the total entries for the Patients channel
        static::$model->channelsUpdateSql();

        // Clean up the temp tables
        static::$model->deleteTempTables();
        echo 'Final cleanup of the temp tables' . PHP_EOL;
    }

}
