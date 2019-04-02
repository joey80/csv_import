<?php
namespace Immerge\Importer;

use Immerge\Importer\Database as Database;

spl_autoload_register(function ($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

/**
 * Models
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Models
{

    private $db;
    private static $instance;
    private function __construct()
    {
    }
    private function __clone()
    {
    }




    /**
     * Get the singleton instance
     * @param none
     * @return Auth
     */

    public static function getInstance()
    {

        if (static ::$instance === null)
        {
            static ::$instance = new Models();
        }
        return static ::$instance;
    }




    /**
     * deleteOldAppointments - Clears out the patients entries in the channel_data and channel_title tables
     *
     * @return nothing
     */

    public function deleteOldAppointments()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('DELETE d, t FROM exp_channel_data d INNER JOIN exp_channel_titles t ON d.entry_id = t.entry_id WHERE t.channel_id = 4');
            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * deleteTempTables - Deletes all temp tables that were created during the import
     *
     * @return nothing
     */

    public function deleteTempTables()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('DROP TABLE IF EXISTS temp_csv, temp_channel_titles, temp_channel_data');
            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * createTempTables - Creates all of the temp tables that are needed for the import
     *
     * @return nothing
     */

    public function createTempTables()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('CREATE TABLE temp_csv(
									patient_number 		VARCHAR(20) NOT NULL PRIMARY KEY,
									patient_lastname	VARCHAR(50),
									patient_firstname	VARCHAR(50),
									patient_street		VARCHAR(100),
									patient_address2	VARCHAR(100),
									patient_city		VARCHAR(100),
									patient_state		VARCHAR(2),
									patient_zip			VARCHAR(5),
									patient_phone		VARCHAR(20),
									patient_phone2		VARCHAR(20),
									patient_phone3		VARCHAR(20),
									patient_email		VARCHAR(100)
								)
								ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
								CREATE TABLE temp_channel_titles LIKE exp_channel_titles;
								INSERT temp_channel_titles SELECT * FROM exp_channel_titles WHERE channel_id = 4;
								CREATE TABLE temp_channel_data LIKE exp_channel_data;
								INSERT temp_channel_data SELECT * FROM exp_channel_data WHERE channel_id = 4');
            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * titleAuthorFind - Find the practice given the shipping_code
     *
     * @param array $data - An array with the shipping_code
     * @return $result - The author_id which is from field_id_8
     */

    public function titleAuthorFind($data)
    {

        try
        {

            $db = Database::getInstance();

            echo 'looking up ' . $data . PHP_EOL;
            $stmt = $db->prepare('SELECT field_id_8 from exp_channel_data WHERE field_id_17 = :shipping AND channel_id = 3');
            $stmt->execute(array(
                ':shipping' => $data
            ));
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * titlesTableCount - Count how many patient entries are in the exp_channel_titles table
     *
     * @return $result - The total count
     */

    public function titlesTableCount()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT COUNT(*) FROM exp_channel_titles WHERE channel_id = 4');
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * dataTableCount - Count how many patient entries are in the exp_channel_data table
     *
     * @return $result - The total count
     */

    public function dataTableCount()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT COUNT(*) FROM exp_channel_data WHERE channel_id = 4');
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * tempTitlesCount - Count how many patient entries are in the exp_channel_data table
     *
     * @return $result - The total count
     */

    public function tempTitlesCount()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT COUNT(*) FROM temp_channel_titles');
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * tempDataCount - Count how many patient entries are in the temp_channel_data table
     *
     * @return $result - The total count
     */

    public function tempDataCount()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT COUNT(*) FROM temp_channel_data');
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * csvInsertSql - Inserts temp data into the temp_csv table
     *
     * @param array $data - An array with the patient information
     * @return nothing
     */

    public function csvInsertSql($data)
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('INSERT INTO temp_csv (patient_number, patient_lastname, patient_firstname, patient_street,
														patient_address2, patient_city, patient_state, patient_zip, patient_phone,
														patient_phone2, patient_phone3, patient_email)

														VALUES (:patient_number, :patient_lastname, :patient_firstname, :patient_street,
																:patient_address2, :patient_city, :patient_state, :patient_zip,
																:patient_phone, :patient_phone2, :patient_phone3, :patient_email)');

            $stmt->bindParam(':patient_number', $data['patient_number']);
            $stmt->bindParam(':patient_lastname', $data['patient_lastname']);
            $stmt->bindParam(':patient_firstname', $data['patient_firstname']);
            $stmt->bindParam(':patient_street', $data['patient_street']);
            $stmt->bindParam(':patient_address2', $data['patient_address2']);
            $stmt->bindParam(':patient_city', $data['patient_city']);
            $stmt->bindParam(':patient_state', $data['patient_state']);
            $stmt->bindParam(':patient_zip', $data['patient_zip']);
            $stmt->bindParam(':patient_phone', $data['patient_phone']);
            $stmt->bindParam(':patient_phone2', $data['patient_phone2']);
            $stmt->bindParam(':patient_phone3', $data['patient_phone3']);
            $stmt->bindParam(':patient_email', $data['patient_email']);

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * titlesInsertNewSql - Inserts data into the exp_channel_titles table
     *
     * @param array $data - An array with the patient information
     * @return nothing
     */

    public function titlesInsertNewSql($data)
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('INSERT INTO exp_channel_titles (site_id, channel_id, author_id, title, url_title, status,
																versioning_enabled, allow_comments, entry_date, year, month, day)

																VALUES (:site_id, :channel_id, :author_id, :title, :url_title, :status,
																		:versioning_enabled, :allow_comments, :entry_date, :year, :month, :day)');

            $stmt->bindParam(':site_id', $data['site_id']);
            $stmt->bindParam(':channel_id', $data['channel_id']);
            $stmt->bindParam(':author_id', $data['author_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':url_title', $data['url_title']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':versioning_enabled', $data['versioning_enabled']);
            $stmt->bindParam(':allow_comments', $data['allow_comments']);
            $stmt->bindParam(':entry_date', $data['entry_date']);
            $stmt->bindParam(':year', $data['year']);
            $stmt->bindParam(':month', $data['month']);
            $stmt->bindParam(':day', $data['day']);

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * dataInsertNewSql - Inserts data into the exp_channel_titles table
     *
     * @param array $data - An array with the patient information
     * @return nothing
     */

    public function dataInsertNewSql($data)
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('INSERT INTO exp_channel_data (entry_id, site_id, channel_id, field_id_8, field_id_162, field_ft_162, field_id_163,
																field_ft_163, field_id_164, field_ft_164, field_id_165, field_ft_165,
																field_id_166, field_ft_166, field_id_167, field_ft_167, field_id_168,
																field_ft_168, field_id_169, field_ft_169, field_id_170, field_ft_170,
																field_id_171, field_ft_171, field_id_172, field_ft_172, field_id_173, field_ft_173)

																VALUES (:entry_id, :site_id, :channel_id, :field_id_8, :field_id_162, :field_ft_162, :field_id_163,
																:field_ft_163, :field_id_164, :field_ft_164, :field_id_165, :field_ft_165,
																:field_id_166, :field_ft_166, :field_id_167, :field_ft_167, :field_id_168,
																:field_ft_168, :field_id_169, :field_ft_169, :field_id_170, :field_ft_170,
																:field_id_171, :field_ft_171, :field_id_172, :field_ft_172, :field_id_173, :field_ft_173)');

            $stmt->bindParam(':entry_id', $data['entry_id']);
            $stmt->bindParam(':site_id', $data['site_id']);
            $stmt->bindParam(':channel_id', $data['channel_id']);
            $stmt->bindParam(':field_id_8', $data['field_id_8']);
            $stmt->bindParam(':field_id_162', $data['field_id_162']);
            $stmt->bindParam(':field_ft_162', $data['field_ft_162']);
            $stmt->bindParam(':field_id_163', $data['field_id_163']);
            $stmt->bindParam(':field_ft_163', $data['field_ft_163']);
            $stmt->bindParam(':field_id_164', $data['field_id_164']);
            $stmt->bindParam(':field_ft_164', $data['field_ft_164']);
            $stmt->bindParam(':field_id_165', $data['field_id_165']);
            $stmt->bindParam(':field_ft_165', $data['field_ft_165']);
            $stmt->bindParam(':field_id_166', $data['field_id_166']);
            $stmt->bindParam(':field_ft_166', $data['field_ft_166']);
            $stmt->bindParam(':field_id_167', $data['field_id_167']);
            $stmt->bindParam(':field_ft_167', $data['field_ft_167']);
            $stmt->bindParam(':field_id_168', $data['field_id_168']);
            $stmt->bindParam(':field_ft_168', $data['field_ft_168']);
            $stmt->bindParam(':field_id_169', $data['field_id_169']);
            $stmt->bindParam(':field_ft_169', $data['field_ft_169']);
            $stmt->bindParam(':field_id_170', $data['field_id_170']);
            $stmt->bindParam(':field_ft_170', $data['field_ft_170']);
            $stmt->bindParam(':field_id_171', $data['field_id_171']);
            $stmt->bindParam(':field_ft_171', $data['field_ft_171']);
            $stmt->bindParam(':field_id_172', $data['field_id_172']);
            $stmt->bindParam(':field_ft_172', $data['field_ft_172']);
            $stmt->bindParam(':field_id_173', $data['field_id_173']);
            $stmt->bindParam(':field_ft_173', $data['field_ft_173']);

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * getLastEntry - Gets the last entry from the exp_channel_titles. This is needed for making
     *                entries in the exp_channel_data table
     *
     * @return $result - The entry_id
     */

    public function getLastEntry()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT @last_id := MAX(entry_id) FROM exp_channel_titles; SELECT entry_id FROM exp_channel_titles WHERE entry_id = @last_id;');
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * titlesTableCountSql - Count how many patient entries are in the exp_channel_data table
     *
     * @return $result - The total count
     */

    public function titlesTableCountSql()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT COUNT(*) FROM exp_channel_titles WHERE channel_id = 4');
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false)
            {
                return $result;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
     * channelsUpdateSql - Updates the exp_channel number of entries for the patient's channel
     *
     * @return nothing
     */

    public function channelsUpdateSql()
    {

        try
        {

            $db = Database::getInstance();

            $stmt = $db->prepare('UPDATE `exp_channels`
									SET `total_entries` = (SELECT COUNT(*) FROM exp_channel_data WHERE channel_id = 4)
									WHERE `channel_id` = 4');

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }

}
