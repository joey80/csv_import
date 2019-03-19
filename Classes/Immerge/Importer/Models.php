<?php

namespace Immerge\Importer;

spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/scripts/Classes/' . $className . '.php';
});

use Immerge\Importer\Database as Database;

/**
* Models
*
* @author Joey Leger
* @author Immerge 2019
*/

class Models {

	private $db;
	private static $instance;
	private function __construct() {}
    private function __clone() {}

    /**
   	* Get the singleton instance
	* @param none
	* @return Auth
	*/
	public static function getInstance() {

		if (static::$instance === NULL) {
			static::$instance = new Models();
	    }
		return static::$instance;
	}



	public function deleteOldAppointments() {

		try {

			echo 'Deleting old appointments' . PHP_EOL;
			$db = Database::getInstance();

            $stmt = $db->prepare('DELETE d, t FROM exp_channel_data d INNER JOIN exp_channel_titles t ON d.entry_id = t.entry_id WHERE t.channel_id = 4');
			$stmt->execute();

		} catch(PDOException $exception) {
			error_log($exception->getMessage());
		}
	}



	public function deleteTempTables() {

		try {

			echo 'Deleting temp tables' . PHP_EOL;
			$db = Database::getInstance();

            $stmt = $db->prepare('DROP TABLE IF EXISTS temp_csv, temp_channel_titles, temp_channel_data');
			$stmt->execute();

		} catch(PDOException $exception) {
			error_log($exception->getMessage());
		}
	}



	public function createTempTables() {

		try {

			echo 'Creating temp tables' . PHP_EOL;
			$db = Database::getInstance();

            $stmt = $db->prepare('CREATE TABLE temp_csv(
									patient_number 		VARCHAR(10) NOT NULL PRIMARY KEY,
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

		} catch(PDOException $exception) {
			error_log($exception->getMessage());
		}
	}


	
	public function titleAuthorFind($data) {

		try {

			echo 'Looking up title author ID' . PHP_EOL;
			$db = Database::getInstance();

            $stmt = $db->prepare('SELECT field_id_8 from exp_channel_data WHERE field_id_17 = :shipping AND channel_id = 3');
			$stmt->execute(array(':shipping' => $data));
			$result = $stmt->fetchColumn();
			//$result = $stmt->fetch(PDO::FETCH_OBJ);

			if($result !== false) {
				return $result;
			}
		} catch(PDOException $exception) {
			error_log($exception->getMessage());
		}
	}
 


	public function titlesTableCount() {

		try {

			echo 'Counting channel titles for ID 4' . PHP_EOL;
			$db = Database::getInstance();

            $stmt = $db->prepare('SELECT COUNT(*) FROM exp_channel_titles WHERE channel_id = 4');
			$stmt->execute();
			$result = $stmt->fetchColumn();
			//$result = $stmt->fetch(PDO::FETCH_OBJ);

			if($result !== false) {
				return $result;
			}
		} catch(PDOException $exception) {
			error_log($exception->getMessage());
		}
	}
	
}


?>