<?php
namespace Immerge\Importer;

use Immerge\Importer\Database as Database;
use PDO;

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

    public static $db;
    public static $instance;

    public function __construct()
    {
        static::$db = Database::getInstance();
    }




    /**
    * Get the singleton instance
    * @param none
    * @return Auth
    */

    public static function getInstance()
    {
        if (static::$instance === null)
        {
            static::$instance = new Models();
        }
        return static::$instance;
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

            $stmt = static::$db->prepare('DELETE d, t FROM exp_channel_data d INNER JOIN exp_channel_titles t ON d.entry_id = t.entry_id WHERE t.channel_id = 4');
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

            $stmt = static::$db->prepare('DROP TABLE IF EXISTS temp_csv, temp_channel_titles, temp_channel_data');
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

            $stmt = static::$db->prepare('CREATE TABLE temp_csv(
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

            $stmt = static::$db->prepare('SELECT field_id_8 from exp_channel_data WHERE field_id_17 = :shipping AND channel_id = 3');
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

            $stmt = static::$db->prepare('SELECT COUNT(*) FROM exp_channel_titles WHERE channel_id = 4');
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

            $stmt = static::$db->prepare('SELECT COUNT(*) FROM exp_channel_data WHERE channel_id = 4');
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

            $stmt = static::$db->prepare('SELECT COUNT(*) FROM temp_channel_titles');
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

            $stmt = static::$db->prepare('SELECT COUNT(*) FROM temp_channel_data');
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

            $stmt = static::$db->prepare('INSERT INTO temp_csv (patient_number, patient_lastname, patient_firstname, patient_street,
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

            $stmt = static::$db->prepare('INSERT INTO exp_channel_titles (site_id, channel_id, author_id, title, url_title, status,
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

            $stmt = static::$db->prepare('INSERT INTO exp_channel_data (entry_id, site_id, channel_id, field_id_8, field_id_162, field_ft_162, field_id_163,
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

            $stmt = static::$db->prepare('SELECT @last_id := MAX(entry_id) FROM exp_channel_titles; SELECT entry_id FROM exp_channel_titles WHERE entry_id = @last_id;');
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

            $stmt = static::$db->prepare('SELECT COUNT(*) FROM exp_channel_titles WHERE channel_id = 4');
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
    * channelsUpdateSql - Updates the exp_channel table's number of entries for the patient's channel
    *
    * @return nothing
    */

    public function channelsUpdateSql()
    {

        try
        {

            $stmt = static::$db->prepare('UPDATE exp_channels
									SET total_entries = (SELECT COUNT(*) FROM exp_channel_data WHERE channel_id = 4)
									WHERE channel_id = 4');

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
    * getAllOrders - Returns all of the orders that matches the status passed in
    *
    * @return $result - An array of all entry_id's from orders
    */

    public function getAllOrders($data)
    {

        try
        {

            $stmt = static::$db->prepare('SELECT cd.entry_id FROM exp_channel_data cd 
                                    INNER JOIN exp_channel_titles AS ct ON ct.entry_id = cd.entry_id
                                    WHERE ct.status = :order_status AND cd.channel_id = 2');
            $stmt->execute(array(
                ':order_status' => $data
            ));
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
    * getAllOrderDetails - Returns all order data from a specified entry_id
    *
    * @return $result - An array of order data
    */

    public function getAllOrderDetails($data)
    {

        try
        {

            $stmt = static::$db->prepare('SELECT
                                    cd.field_id_58 AS order_date_scanned,
                                    cd.field_id_53 AS order_date_of_pt,
                                    cd.field_id_161 AS order_date_shipped,
                                    cd.field_id_186 AS order_recover_refurb_ship_date,
                                    cd.field_id_188 AS order_adjustment_ship_date,
                                    cd.field_id_189 AS order_ship_date_d1,
                                    cd.field_id_190 AS order_ship_date_d2,
                                    cd.field_id_191 AS order_ship_date_d3,
                                    cd.field_id_192 AS order_ship_date_d4,
                                    cd.field_id_407 AS order_date_submitted,
                                    cd.field_id_3 AS order_practice_name,
                                    cd.field_id_4 AS order_practice_address,
                                    cd.field_id_5 AS order_practice_phone,
                                    cd.field_id_6 AS order_practice_contact_email,
                                    cd.field_id_7 AS order_practice_shipping_code,
                                    cd.field_id_138 AS order_practice_billing_code,
                                    cd.field_id_20 AS order_doctor,
                                    cd.field_id_21 AS order_special_doctor_notes,
                                    cd.field_id_22 AS order_staff,
                                    cd.field_id_25 AS order_patient_first_name,
                                    cd.field_id_24 AS order_patient_last_name,
                                    cd.field_id_26 AS order_patient_id,
                                    cd.field_id_27 AS order_three_day_rush,
                                    cd.field_id_28 AS order_shipping_speed,
                                    cd.field_id_29 AS order_ship_to,
                                    cd.field_id_30 AS order_patient_shipping_street,
                                    cd.field_id_31 AS order_patient_shipping_city,
                                    cd.field_id_32 AS order_patient_shipping_state,
                                    cd.field_id_33 AS order_patient_shipping_zip,
                                    cd.field_id_34 AS order_patient_email,
                                    cd.field_id_35 AS order_patient_phone,
                                    cd.field_id_36 AS order_patient_weight,
                                    cd.field_id_37 AS order_patient_age,
                                    cd.field_id_38 AS order_patient_gender,
                                    cd.field_id_39 AS order_patient_shoe_size,
                                    cd.field_id_40 AS order_patient_shoe_width,
                                    cd.field_id_41 AS order_diagnosis,
                                    cd.field_id_408 AS order_see_pictures_in_dropbox,
                                    cd.field_id_42 AS order_pair_one_foot,
                                    cd.field_id_43 AS order_additional_items,
                                    cd.field_id_44 AS order_type_of_order,
                                    cd.field_id_45 AS order_recover_refurb_po,
                                    cd.field_id_46 AS order_recover_refurb_work,
                                    cd.field_id_47 AS order_recover_refurb_notes,
                                    cd.field_id_175 AS order_recover_refurb_tracking,
                                    cd.field_id_174 AS order_recover_refurb_elog,
                                    cd.field_id_193 AS order_recover_refurb_ship_comp,
                                    cd.field_id_48 AS order_original_elog,
                                    cd.field_id_49 AS order_primary_problem,
                                    cd.field_id_176 AS order_adjustment_elog,
                                    cd.field_id_177 AS order_adjustment_tracking,
                                    cd.field_id_50 AS order_adjustment_notes,
                                    cd.field_id_194 AS order_adjustment_ship_comp,
                                    cd.field_id_51 AS order_new_device_po,
                                    cd.field_id_52 AS order_new_device_quantity,
                                    cd.field_id_54 AS order_pt_coverage_frequency,
                                    cd.field_id_55 AS order_source_of_foot_impressions,
                                    cd.field_id_56 AS order_weight_bearing_arch_type,
                                    cd.field_id_57 AS order_scan_attachements,
                                    cd.field_id_59 AS order_images_video,
                                    cd.field_id_60 AS order_device_type_d1,
                                    cd.field_id_62 AS order_top_cover_d1,
                                    cd.field_id_61 AS order_ppt_layer_d1,
                                    cd.field_id_63 AS order_top_cover_length_d1,
                                    cd.field_id_64 AS order_shell_thickness_d1,
                                    cd.field_id_65 AS order_shell_grind_width_d1,
                                    cd.field_id_66 AS order_shell_arch_fill_d1,
                                    cd.field_id_67 AS order_heel_cup_in_shell_d1,
                                    cd.field_id_71 AS order_met_pad_bar_d1,
                                    cd.field_id_199 AS order_met_unloads_in_ppt_d1_1,
                                    cd.field_id_200 AS order_met_unloads_in_ppt_d1_2,
                                    cd.field_id_201 AS order_met_unloads_in_ppt_d1_3,
                                    cd.field_id_202 AS order_met_unloads_in_ppt_d1_4,
                                    cd.field_id_203 AS order_met_unloads_in_ppt_d1_5,
                                    cd.field_id_204 AS order_heel_horseshoe_unload_d1,
                                    cd.field_id_205 AS order_navicular_unload_d1,
                                    cd.field_id_206 AS order_base_of_fifth_unload_d1,
                                    cd.field_id_207 AS order_plantar_fascial_groove_d1,
                                    cd.field_id_208 AS order_cuboid_unload_d1,
                                    cd.field_id_209 AS order_future_unload_1_d1,
                                    cd.field_id_210 AS order_mortons_extension_d1,
                                    cd.field_id_211 AS order_reverse_mortons_d1,
                                    cd.field_id_212 AS order_kw_unload_in_crepe_d1,
                                    cd.field_id_213 AS order_kw_unload_in_ppt_d1,
                                    cd.field_id_214 AS order_medial_flange_d1,
                                    cd.field_id_215 AS order_lateral_flange_d1,
                                    cd.field_id_216 AS order_kirby_skive_2mm_d1,
                                    cd.field_id_217 AS order_kirby_skive_4mm_d1,
                                    cd.field_id_218 AS order_1st_met_cut_out_shell_d1,
                                    cd.field_id_219 AS order_heel_lift_amt_in_notes_d1,
                                    cd.field_id_220 AS order_future_accommodation_1_d1,
                                    cd.field_id_221 AS order_future_accommodation_2_d1,
                                    cd.field_id_222 AS order_transmet_filler_d1,
                                    cd.field_id_223 AS order_hallux_filler_d1,
                                    cd.field_id_224 AS order_lesser_toe_fillers_d1,
                                    cd.field_id_236 AS order_whole_foot_wrymark_d1,
                                    cd.field_id_226 AS order_gait_plate_toe_out_d1,
                                    cd.field_id_227 AS order_gait_plate_toe_in_d1,
                                    cd.field_id_228 AS order_add_ppt_schaphoid_pad_d1,
                                    cd.field_id_229 AS order_central_heel_reliefs_d1,
                                    cd.field_id_230 AS order_met_ridge_6mm_ppt_d1,
                                    cd.field_id_231 AS order_fill_under_arch_ppt_d1,
                                    cd.field_id_232 AS order_heel_pad_3mm_ppt_d1,
                                    cd.field_id_233 AS order_heel_pad_1_5mm_ppt_d1,
                                    cd.field_id_234 AS order_denton_extend_lateral_d1,
                                    cd.field_id_235 AS order_zero_deg_forefoot_post_d1,
                                    cd.field_id_225 AS order_runners_wedge_medial_d1,
                                    cd.field_id_237 AS order_runners_wedge_lateral_d1,
                                    cd.field_id_238 AS order_future_4_d1,
                                    cd.field_id_239 AS order_future_5_d1,
                                    cd.field_id_240 AS order_future_6_d1,
                                    cd.field_id_241 AS order_future_7_d1,
                                    cd.field_id_242 AS order_future_8_d1,
                                    cd.field_id_243 AS order_future_9_d1,
                                    cd.field_id_244 AS order_future_10_d1,
                                    cd.field_id_245 AS order_future_11_d1,
                                    cd.field_id_246 AS order_future_12_d1,
                                    cd.field_id_247 AS order_practitioner_def_a_d1,
                                    cd.field_id_248 AS order_practitioner_def_b_d1,
                                    cd.field_id_76 AS order_other_notes_d1,
                                    cd.field_id_178 AS order_elog_d1,
                                    cd.field_id_179 AS order_tracking_d1,
                                    cd.field_id_195 AS order_ship_comp_d1,
                                    cd.field_id_77 AS order_device_type_d2,
                                    cd.field_id_79 AS order_top_cover_d2,
                                    cd.field_id_78 AS order_ppt_layer_d2,
                                    cd.field_id_80 AS order_top_cover_length_d2,
                                    cd.field_id_81 AS order_shell_thickness_d2,
                                    cd.field_id_82 AS order_shell_grind_width_d2,
                                    cd.field_id_83 AS order_shell_arch_fill_d2,
                                    cd.field_id_84 AS order_heel_cup_in_shell_d2,
                                    cd.field_id_88 AS order_met_pad_bar_d2,
                                    cd.field_id_249 AS order_met_unloads_in_ppt_d2_1,
                                    cd.field_id_250 AS order_met_unloads_in_ppt_d2_2,
                                    cd.field_id_251 AS order_met_unloads_in_ppt_d2_3,
                                    cd.field_id_252 AS order_met_unloads_in_ppt_d2_4,
                                    cd.field_id_253 AS order_met_unloads_in_ppt_d2_5,
                                    cd.field_id_254 AS order_heel_horseshoe_unload_d2,
                                    cd.field_id_255 AS order_navicular_unload_d2,
                                    cd.field_id_256 AS order_base_of_fifth_unload_d2,
                                    cd.field_id_257 AS order_plantar_fascial_groove_d2,
                                    cd.field_id_258 AS order_cuboid_unload_d2,
                                    cd.field_id_259 AS order_future_unload_1_d2,
                                    cd.field_id_260 AS order_mortons_extension_d2,
                                    cd.field_id_261 AS order_reverse_mortons_d2,
                                    cd.field_id_262 AS order_kw_unload_in_crepe_d2,
                                    cd.field_id_263 AS order_kw_unload_in_ppt_d2,
                                    cd.field_id_264 AS order_medial_flange_d2,
                                    cd.field_id_265 AS order_lateral_flange_d2,
                                    cd.field_id_266 AS order_kirby_skive_2mm_d2,
                                    cd.field_id_267 AS order_kirby_skive_4mm_d2,
                                    cd.field_id_268 AS order_1st_met_cut_out_shell_d2,
                                    cd.field_id_269 AS order_heel_lift_amt_in_notes_d2,
                                    cd.field_id_270 AS order_future_accommodation_1_d2,
                                    cd.field_id_271 AS order_future_accommodation_2_d2,
                                    cd.field_id_272 AS order_transmet_filler_d2,
                                    cd.field_id_273 AS order_hallux_filler_d2,
                                    cd.field_id_274 AS order_lesser_toe_fillers_d2,
                                    cd.field_id_286 AS order_whole_foot_wrymark_d2,
                                    cd.field_id_276 AS order_gait_plate_toe_out_d2,
                                    cd.field_id_277 AS order_gait_plate_to_toe_in_d2,
                                    cd.field_id_278 AS order_add_ppt_schaphoid_pad_d2,
                                    cd.field_id_279 AS order_central_heel_reliefs_d2,
                                    cd.field_id_280 AS order_met_ridge_6mm_ppt_d2,
                                    cd.field_id_281 AS order_fill_under_arch_ppt_d2,
                                    cd.field_id_282 AS order_heel_pad_3mm_ppt_d2,
                                    cd.field_id_283 AS order_heel_pad_1_5mm_ppt_d2,
                                    cd.field_id_284 AS order_denton_extend_lateral_d2,
                                    cd.field_id_285 AS order_zero_deg_forefoot_post_d2,
                                    cd.field_id_275 AS order_runners_wedge_medial_d2,
                                    cd.field_id_287 AS order_runners_wedge_lateral_d2,
                                    cd.field_id_288 AS order_future_4_d2,
                                    cd.field_id_289 AS order_future_5_d2,
                                    cd.field_id_290 AS order_future_6_d2,
                                    cd.field_id_291 AS order_future_7_d2,
                                    cd.field_id_292 AS order_future_8_d2,
                                    cd.field_id_293 AS order_future_9_d2,
                                    cd.field_id_294 AS order_future_10_d2,
                                    cd.field_id_295 AS order_future_11_d2,
                                    cd.field_id_296 AS order_future_12_d2,
                                    cd.field_id_297 AS order_practitioner_def_a_d2,
                                    cd.field_id_298 AS order_practitioner_def_b_d2,
                                    cd.field_id_93 AS order_other_notes_d2,
                                    cd.field_id_180 AS order_elog_d2,
                                    cd.field_id_181 AS order_tracking_d2,
                                    cd.field_id_196 AS order_ship_comp_d2,
                                    cd.field_id_94 AS order_device_type_d3,
                                    cd.field_id_96 AS order_top_cover_d3,
                                    cd.field_id_95 AS order_ppt_layer_d3,
                                    cd.field_id_97 AS order_top_cover_length_d3,
                                    cd.field_id_98 AS order_shell_thickness_d3,
                                    cd.field_id_99 AS order_shell_grind_width_d3,
                                    cd.field_id_100 AS order_shell_arch_fill_d3,
                                    cd.field_id_101 AS order_heel_cup_in_shell_d3,
                                    cd.field_id_105 AS order_met_pad_bar_d3,
                                    cd.field_id_299 AS order_met_unloads_in_ppt_d3_1,
                                    cd.field_id_300 AS order_met_unloads_in_ppt_d3_2,
                                    cd.field_id_301 AS order_met_unloads_in_ppt_d3_3,
                                    cd.field_id_302 AS order_met_unloads_in_ppt_d3_4,
                                    cd.field_id_303 AS order_met_unloads_in_ppt_d3_5,
                                    cd.field_id_304 AS order_heel_horseshoe_unload_d3,
                                    cd.field_id_305 AS order_navicular_unload_d3,
                                    cd.field_id_306 AS order_base_of_fifth_unload_d3,
                                    cd.field_id_307 AS order_plantar_fascial_groove_d3,
                                    cd.field_id_308 AS order_cuboid_unload_d3,
                                    cd.field_id_309 AS order_future_unload_d3,
                                    cd.field_id_310 AS order_mortons_extension_d3,
                                    cd.field_id_311 AS order_reverse_mortons_d3,
                                    cd.field_id_312 AS order_kw_unload_in_crepe_d3,
                                    cd.field_id_313 AS order_kw_unload_in_ppt_d3,
                                    cd.field_id_314 AS order_medial_flange_d3,
                                    cd.field_id_315 AS order_lateral_flange_d3,
                                    cd.field_id_316 AS order_kirby_skive_2mm_d3,
                                    cd.field_id_317 AS order_kirby_skive_4mm_d3,
                                    cd.field_id_318 AS order_1st_met_cut_out_shell_d3,
                                    cd.field_id_319 AS order_heel_lift_amt_in_notes_d3,
                                    cd.field_id_320 AS order_future_accommodation_1_d3,
                                    cd.field_id_321 AS order_future_accommodation_2_d3,
                                    cd.field_id_322 AS order_transmet_filler_d3,
                                    cd.field_id_323 AS order_hallux_filler_d3,
                                    cd.field_id_324 AS order_lesser_toe_fillers_d3,
                                    cd.field_id_336 AS order_whole_foot_wrymark_d3,
                                    cd.field_id_326 AS order_gait_plate_toe_out_d3,
                                    cd.field_id_327 AS order_gait_plate_toe_in_d3,
                                    cd.field_id_328 AS order_add_ppt_schaphoid_pad_d3,
                                    cd.field_id_329 AS order_central_heel_reliefs_d3,
                                    cd.field_id_330 AS order_met_ridge_6mm_ppt_d3,
                                    cd.field_id_331 AS order_fill_under_arch_ppt_d3,
                                    cd.field_id_332 AS order_heel_pad_3mm_ppt_d3,
                                    cd.field_id_333 AS order_heel_pad_1_5mm_ppt_d3,
                                    cd.field_id_334 AS order_denton_extend_lateral_d3,
                                    cd.field_id_335 AS order_zero_deg_forefoot_post_d3,
                                    cd.field_id_325 AS order_runners_wedge_medial_d3,
                                    cd.field_id_337 AS order_runners_wedge_lateral_d3,
                                    cd.field_id_338 AS order_future_4_d3,
                                    cd.field_id_339 AS order_future_5_d3,
                                    cd.field_id_340 AS order_future_6_d3,
                                    cd.field_id_341 AS order_future_7_d3,
                                    cd.field_id_342 AS order_future_8_d3,
                                    cd.field_id_343 AS order_future_9_d3,
                                    cd.field_id_344 AS order_future_10_d3,
                                    cd.field_id_345 AS order_future_11_d3,
                                    cd.field_id_346 AS order_future_12_d3,
                                    cd.field_id_347 AS order_practitioner_def_a_d3,
                                    cd.field_id_348 AS order_practitioner_def_b_d3,
                                    cd.field_id_110 AS order_other_notes_d3,
                                    cd.field_id_182 AS order_elog_d3,
                                    cd.field_id_183 AS order_tracking_d3,
                                    cd.field_id_197 AS order_ship_comp_d3,
                                    cd.field_id_111 AS order_device_type_d4,
                                    cd.field_id_113 AS order_top_cover_d4,
                                    cd.field_id_112 AS order_ppt_layer_d4,
                                    cd.field_id_114 AS order_top_cover_length_d4,
                                    cd.field_id_115 AS order_shell_thickness_d4,
                                    cd.field_id_116 AS order_shell_grind_width_d4,
                                    cd.field_id_117 AS order_shell_arch_fill_d4,
                                    cd.field_id_118 AS order_heel_cup_in_shell_d4,
                                    cd.field_id_122 AS order_met_pad_bar_d4,
                                    cd.field_id_349 AS order_met_unloads_in_ppt_d4_1,
                                    cd.field_id_350 AS order_met_unloads_in_ppt_d4_2,
                                    cd.field_id_351 AS order_met_unloads_in_ppt_d4_3,
                                    cd.field_id_352 AS order_met_unloads_in_ppt_d4_4,
                                    cd.field_id_353 AS order_met_unloads_in_ppt_d4_5,
                                    cd.field_id_354 AS order_heel_horseshoe_unload_d4,
                                    cd.field_id_355 AS order_navicular_unload_d4,
                                    cd.field_id_356 AS order_base_of_fifth_unload_d4,
                                    cd.field_id_357 AS order_plantar_fascial_groove_d4,
                                    cd.field_id_358 AS order_cuboid_unload_d4,
                                    cd.field_id_359 AS order_future_unload_1_d4,
                                    cd.field_id_360 AS order_mortons_extension_d4,
                                    cd.field_id_361 AS order_reverse_mortons_d4,
                                    cd.field_id_362 AS order_kw_unload_in_crepe_d4,
                                    cd.field_id_363 AS order_kw_unload_in_ppt_d4,
                                    cd.field_id_364 AS order_medial_flange_d4,
                                    cd.field_id_365 AS order_lateral_flange_d4,
                                    cd.field_id_366 AS order_kirby_skive_2mm_d4,
                                    cd.field_id_367 AS order_kirby_skive_4mm_d4,
                                    cd.field_id_368 AS order_1st_met_cut_out_shell_d4,
                                    cd.field_id_369 AS order_heel_lift_amt_in_notes_d4,
                                    cd.field_id_370 AS order_future_accommodation_1_d4,
                                    cd.field_id_371 AS order_future_accommodation_2_d4,
                                    cd.field_id_372 AS order_transmet_filler_d4,
                                    cd.field_id_373 AS order_hallux_filler_d4,
                                    cd.field_id_374 AS order_lesser_toe_fillers_d4,
                                    cd.field_id_386 AS order_whole_foot_wrymark_d4,
                                    cd.field_id_376 AS order_gait_plate_toe_out_d4,
                                    cd.field_id_377 AS order_gait_plate_toe_in_d4,
                                    cd.field_id_378 AS order_add_ppt_schaphoid_pad_d4,
                                    cd.field_id_379 AS order_central_heel_reliefs_d4,
                                    cd.field_id_380 AS order_met_ridge_6mm_ppt_d4,
                                    cd.field_id_381 AS order_fill_under_arch_ppt_d4,
                                    cd.field_id_382 AS order_heel_pad_3mm_ppt_d4,
                                    cd.field_id_383 AS order_heel_pad_1_5mm_ppt_d4,
                                    cd.field_id_384 AS order_denton_extend_lateral_d4,
                                    cd.field_id_385 AS order_zero_deg_forefoot_post_d4,
                                    cd.field_id_375 AS order_runners_wedge_medial_d4,
                                    cd.field_id_387 AS order_runners_wedge_lateral_d4,
                                    cd.field_id_388 AS order_future_4_d4,
                                    cd.field_id_389 AS order_future_5_d4,
                                    cd.field_id_390 AS order_future_6_d4,
                                    cd.field_id_391 AS order_future_7_d4,
                                    cd.field_id_392 AS order_future_8_d4,
                                    cd.field_id_393 AS order_future_9_d4,
                                    cd.field_id_394 AS order_future_10_d4,
                                    cd.field_id_395 AS order_future_11_d4,
                                    cd.field_id_396 AS order_future_12_d4,
                                    cd.field_id_397 AS order_practitioner_defined_a_d4,
                                    cd.field_id_398 AS order_practitioner_defined_b_d4,
                                    cd.field_id_127 AS order_other_notes_d4,
                                    cd.field_id_184 AS order_elog_d4,
                                    cd.field_id_185 AS order_tracking_d4,
                                    cd.field_id_198 AS order_ship_comp_d4,
                                    cd.field_id_141 AS order_confirm_problem_for_adjust,
                                    cd.field_id_142 AS order_confirm_type_of_work,
                                    cd.field_id_143 AS order_internal_doctor_notes,
                                    cd.field_id_144 AS order_previous_elog,
                                    cd.field_id_145 AS order_mirror_plaster,
                                    cd.field_id_146 AS order_miscellaneous_charges,
                                    cd.field_id_147 AS order_discount_rush,
                                    cd.field_id_148 AS order_discount_device,
                                    cd.field_id_149 AS order_device_must_ship_by,
                                    cd.field_id_150 AS order_markings_notes,
                                    cd.field_id_151 AS order_save_ship_back_casts,
                                    cd.field_id_152 AS order_scanning_notes,
                                    cd.field_id_153 AS order_correcting_notes,
                                    cd.field_id_154 AS order_pulling_notes,
                                    cd.field_id_155 AS order_finishing_notes_1,
                                    cd.field_id_156 AS order_finishing_notes_2,
                                    cd.field_id_157 AS order_other_shipping_notes,
                                    cd.field_id_158 AS order_billing_notes,
                                    cd.field_id_159 AS order_elog,
                                    cd.field_id_417 AS order_date_accepted,
                                    cd.field_id_161 AS order_date_shipped,
                                    cd.field_id_409 AS order_practice_select,
                                    cd.field_id_410 AS order_doctor_select,
                                    cd.field_id_411 AS order_assistant_select,
                                    cd.field_id_407 AS order_date_submitted
                                    FROM exp_channel_data cd
                                    WHERE cd.entry_id = :entry_id AND cd.channel_id = 2');

            $stmt->execute(array(
                ':entry_id' => $data
            ));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result !== false)
            {
                return array_shift($result);
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }



    /**
    * getMatrixColumns - Returns all of the matrix columns of a certain field type and entry id
    *
    * @return $result - An array of matrix columns
    */

    public function getMatrixColumns($data)
    {

        try
        {

            $stmt = static::$db->prepare('SELECT * FROM exp_matrix_data WHERE entry_id = :entry_id AND field_id = :field_id');
            $stmt->execute(array(
                ':entry_id' => $data['entry_id'],
                ':field_id' => $data['field_id']
            ));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result !== false)
            {
                return array_shift($result);
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }



    /**
    * getChannelTitleDataForExport - Returns channel title data for the exporter
    *
    * @return $result - An array of channel title data
    */

    public function getChannelTitleDataForExport($data)
    {

        try
        {

            $stmt = static::$db->prepare('SELECT
                                                title,
                                                url_title,
                                                status,
                                                entry_date,
                                                site_id,
                                                channel_id,
                                                author_id
                                            FROM exp_channel_titles WHERE entry_id = :entry_id');
            $stmt->execute(array(
                ':entry_id' => $data
            ));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result !== false)
            {
                return array_shift($result);
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
    * getMemberDataForExport - Returns the member information needed for the exporter
    *
    * @return $result - An array of membership data
    */

    public function getMemberDataForExport($data)
    {

        try
        {

            $stmt = static::$db->prepare('SELECT
                                                username AS author_data_username,
                                                member_id AS author_data_member_id,
                                                screen_name AS author_data_screen_name,
                                                email AS author_data_email,
                                                join_date AS author_data_join_date,
                                                last_visit AS author_data_last_visit,
                                                group_id AS author_data_group_id,
                                                in_authorlist AS author_data_in_authorlist
                                            FROM exp_members WHERE member_id = :author_id');
            $stmt->execute(array(
                ':author_id' => $data
            ));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result !== false)
            {
                return array_shift($result);
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
    * updateAcceptedOrder - Updates all orders that have a status of 'Accepted' to 'Accepted-Exported'
    *
    * @return nothing
    */
    public function updateAcceptedOrders()
    {

        try
        {
            $stmt = static::$db->prepare('UPDATE exp_channel_titles
									SET status =  "Accepted-Exported"
									WHERE channel_id = 2 AND status = "Accepted"');
            $stmt->execute();
        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }



    
    /**
    * updateShippingOrderData - Updates orders that have updated shipping information
    *                        such as new tracking details
    *
    * @return nothing
    */

    public function updateShippingOrderData($data)
    {
        
        try
        {

            $stmt = static::$db->prepare('UPDATE exp_channel_data
									SET field_id_175 = :order_recover_refurb_tracking,
                                        field_id_186 = :order_recover_refurb_ship_date,
                                        field_id_193 = :order_recover_refurb_ship_comp,
                                        field_id_177 = :order_adjustment_tracking,
                                        field_id_188 = :order_adjustment_ship_date,
                                        field_id_194 = :order_adjustment_ship_comp,
                                        field_id_179 = :order_tracking_d1,
                                        field_id_189 = :order_ship_date_d1,
                                        field_id_195 = :order_ship_comp_d1,
                                        field_id_181 = :order_tracking_d2,
                                        field_id_190 = :order_ship_date_d2,
                                        field_id_196 = :order_ship_comp_d2,
                                        field_id_183 = :order_tracking_d3,
                                        field_id_191 = :order_ship_date_d3,
                                        field_id_197 = :order_ship_comp_d3,
                                        field_id_185 = :order_tracking_d4,
                                        field_id_192 = :order_ship_date_d4,
                                        field_id_198 = :order_ship_comp_d4
									WHERE entry_id = :entry_id');

            $stmt->bindParam(':entry_id', $data['entry_id']);
            $stmt->bindParam(':order_recover_refurb_tracking', $data['order_recover_refurb_tracking'], is_null($data['order_recover_refurb_tracking'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_recover_refurb_ship_date', $data['order_recover_refurb_ship_date'], is_null($data['order_recover_refurb_ship_date'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_recover_refurb_ship_comp', $data['order_recover_refurb_ship_comp'], is_null($data['order_recover_refurb_ship_comp'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_adjustment_tracking', $data['order_adjustment_tracking'], is_null($data['order_adjustment_tracking'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_adjustment_ship_date', $data['order_adjustment_ship_date'], is_null($data['order_adjustment_ship_date'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_adjustment_ship_comp', $data['order_adjustment_ship_comp'], is_null($data['order_adjustment_ship_comp'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_tracking_d1', $data['order_tracking_d1'], is_null($data['order_tracking_d1'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_date_d1', $data['order_ship_date_d1'], is_null($data['order_ship_date_d1'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_comp_d1', $data['order_ship_comp_d1'], is_null($data['order_ship_comp_d1'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_tracking_d2', $data['order_tracking_d2'], is_null($data['order_tracking_d2'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_date_d2', $data['order_ship_date_d2'], is_null($data['order_ship_date_d2'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_comp_d2', $data['order_ship_comp_d2'], is_null($data['order_ship_comp_d2'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_tracking_d3', $data['order_tracking_d3'], is_null($data['order_tracking_d3'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_date_d3', $data['order_ship_date_d3'], is_null($data['order_ship_date_d3'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_comp_d3', $data['order_ship_comp_d3'], is_null($data['order_ship_comp_d3'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_tracking_d4', $data['order_tracking_d4'], is_null($data['order_tracking_d4'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_date_d4', $data['order_ship_date_d4'], is_null($data['order_ship_date_d4'] ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':order_ship_comp_d4', $data['order_ship_comp_d4'], is_null($data['order_ship_comp_d4'] ? PDO::PARAM_NULL : PDO::PARAM_STR));

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            return error_log($exception->getMessage());
        }
    }




    /**
    * updateShippingOrderTitles - Updates orders that have updated shipping information
    *                             such as new tracking details
    *
    * @return nothing
    */

    public function updateShippingOrderTitles($data)
    {

        try
        {

            $stmt = static::$db->prepare('UPDATE exp_channel_titles
									SET
                                        status = :status,
                                        title = :title
									WHERE entry_id = :entry_id');

            $stmt->bindParam(':entry_id', $data['entry_id']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':title', $data['title']);

            $stmt->execute();

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
    * getClosedOrdersOlderThanSixMonths - Deletes all orders that are older than six months
    *
    * @return $results - An array of membership data
    */

    public function getClosedOrdersOlderThanSixMonths()
    {

        try
        {

            $stmt = static::$db->prepare("SELECT entry_id FROM exp_channel_titles WHERE channel_id = 2 AND status = 'closed' AND entry_date <= UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH)");
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($results !== false)
            {
                return $results;
            }

        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
    * deleteClosedOrdersOlderThanSixMonths - Deletes all orders that are older than six months
    *
    * @return nothing
    */

    public function deleteClosedOrdersOlderThanSixMonths($entry_id)
    {

        try
        {
            $stmt = static::$db->prepare('DELETE d, t FROM exp_channel_data d INNER JOIN exp_channel_titles t ON d.entry_id = t.entry_id WHERE d.entry_id = :entry_id');
            $stmt->bindParam(':entry_id', $entry_id);
            $stmt->execute();
        }
        catch(PDOException $exception)
        {
            error_log($exception->getMessage());
        }
    }




    /**
    * createExportHeaderRow - Creates the header row for the export spreadsheet
    *
    * @return array
    */

    public function createExportHeaderRow()
    {
        return [
            'title',
            'url_title',
            'status',
            'entry_date',
            'entry_id',
            'site_id',
            'channel_id',
            'order_date_scanned',
            'order_date_of_pt',
            'order_date_shipped',
            'order_recover_refurb_ship_date',
            'order_adjustment_ship_date',
            'order_ship_date_d1',
            'order_ship_date_d2',
            'order_ship_date_d3',
            'order_ship_date_d4',
            'order_date_submitted',
            'channel_title',
            'author_id',
            'member_id',
            'last_visit',
            'channel_name',
            'author_data_username',
            'author_data_member_id',
            'author_data_screen_name',
            'author_data_email',
            'author_data_join_date',
            'author_data_last_visit',
            'author_data_group_id',
            'author_data_in_authorlist',
            'order_practice_name',
            'order_practice_address',
            'order_practice_phone',
            'order_practice_contact_email',
            'order_practice_shipping_code',
            'order_practice_billing_code',
            'order_doctor',
            'order_special_doctor_notes',
            'order_staff',
            'order_patient_first_name',
            'order_patient_last_name',
            'order_patient_id',
            'order_three_day_rush',
            'order_shipping_speed',
            'order_ship_to',
            'order_patient_shipping_street',
            'order_patient_shipping_city',
            'order_patient_shipping_state',
            'order_patient_shipping_zip',
            'order_patient_email',
            'order_patient_phone',
            'order_patient_weight',
            'order_patient_age',
            'order_patient_gender',
            'order_patient_shoe_size',
            'order_patient_shoe_width',
            'order_diagnosis',
            'order_see_pictures_in_dropbox',
            'order_pair_one_foot',
            'order_additional_items',
            'order_type_of_order',
            'order_recover_refurb_po',
            'order_recover_refurb_work',
            'order_recover_refurb_notes',
            'order_recover_refurb_tracking',
            'order_recover_refurb_elog',
            'order_recover_refurb_ship_date',
            'order_recover_refurb_ship_comp',
            'order_original_elog',
            'order_primary_problem',
            'order_adjustment_elog',
            'order_adjustment_tracking',
            'order_adjustment_notes',
            'order_adjustment_ship_date',
            'order_adjustment_ship_comp',
            'order_new_device_po',
            'order_new_device_quantity',
            'order_date_of_pt',
            'order_pt_coverage_frequency',
            'order_source_of_foot_impressions',
            'order_weight_bearing_arch_type',
            'order_scan_attachements',
            'order_date_scanned',
            'order_images_video',
            'order_device_type_d1',
            'order_top_cover_d1',
            'order_ppt_layer_d1',
            'order_top_cover_length_d1',
            'order_shell_thickness_d1',
            'order_shell_grind_width_d1',
            'order_shell_arch_fill_d1',
            'order_heel_cup_in_shell_d1',
            'order_forefoot_posting_d1_fpd1_additional',
            'order_forefoot_posting_d1_fpd1_left',
            'order_forefoot_posting_d1_fpd1_right',
            'order_rearfoot_posting_d1_rpd1_additional',
            'order_rearfoot_posting_d1_rpd1_left',
            'order_rearfoot_posting_d1_rpd1_right',
            'order_motion_on_vp_d1_mvpd1_left',
            'order_motion_on_vp_d1_mvpd1_right',
            'order_met_pad_bar_d1',
            'order_met_unloads_in_ppt_d1_1',
            'order_met_unloads_in_ppt_d1_2',
            'order_met_unloads_in_ppt_d1_3',
            'order_met_unloads_in_ppt_d1_4',
            'order_met_unloads_in_ppt_d1_5',
            'order_heel_horseshoe_unload_d1',
            'order_navicular_unload_d1',
            'order_base_of_fifth_unload_d1',
            'order_plantar_fascial_groove_d1',
            'order_cuboid_unload_d1',
            'order_future_unload_1_d1',
            'order_mortons_extension_d1',
            'order_reverse_mortons_d1',
            'order_kw_unload_in_crepe_d1',
            'order_kw_unload_in_ppt_d1',
            'order_medial_flange_d1',
            'order_lateral_flange_d1',
            'order_kirby_skive_2mm_d1',
            'order_kirby_skive_4mm_d1',
            'order_1st_met_cut_out_shell_d1',
            'order_heel_lift_amt_in_notes_d1',
            'order_future_accommodation_1_d1',
            'order_future_accommodation_2_d1',
            'order_transmet_filler_d1',
            'order_hallux_filler_d1',
            'order_lesser_toe_fillers_d1',
            'order_whole_foot_wrymark_d1',
            'order_gait_plate_toe_out_d1',
            'order_gait_plate_toe_in_d1',
            'order_add_ppt_schaphoid_pad_d1',
            'order_central_heel_reliefs_d1',
            'order_met_ridge_6mm_ppt_d1',
            'order_fill_under_arch_ppt_d1',
            'order_heel_pad_3mm_ppt_d1',
            'order_heel_pad_1_5mm_ppt_d1',
            'order_denton_extend_lateral_d1',
            'order_zero_deg_forefoot_post_d1',
            'order_runners_wedge_medial_d1',
            'order_runners_wedge_lateral_d1',
            'order_future_4_d1',
            'order_future_5_d1',
            'order_future_6_d1',
            'order_future_7_d1',
            'order_future_8_d1',
            'order_future_9_d1',
            'order_future_10_d1',
            'order_future_11_d1',
            'order_future_12_d1',
            'order_practitioner_def_a_d1',
            'order_practitioner_def_b_d1',
            'order_other_notes_d1',
            'order_elog_d1',
            'order_tracking_d1',
            'order_ship_date_d1',
            'order_ship_comp_d1',
            'order_device_type_d2',
            'order_top_cover_d2',
            'order_ppt_layer_d2',
            'order_top_cover_length_d2',
            'order_shell_thickness_d2',
            'order_shell_grind_width_d2',
            'order_shell_arch_fill_d2',
            'order_heel_cup_in_shell_d2',
            'order_forefoot_posting_d2_fpd2_additional',
            'order_forefoot_posting_d2_fpd2_left',
            'order_forefoot_posting_d2_fpd2_right',
            'order_rearfoot_posting_d2_rpd2_additional',
            'order_rearfoot_posting_d2_rpd2_left',
            'order_rearfoot_posting_d2_rpd2_right',
            'order_motion_on_vp_d2_mvpd2_left',
            'order_motion_on_vp_d2_mvpd2_right',
            'order_met_pad_bar_d2',
            'order_met_unloads_in_ppt_d2_1',
            'order_met_unloads_in_ppt_d2_2',
            'order_met_unloads_in_ppt_d2_3',
            'order_met_unloads_in_ppt_d2_4',
            'order_met_unloads_in_ppt_d2_5',
            'order_heel_horseshoe_unload_d2',
            'order_navicular_unload_d2',
            'order_base_of_fifth_unload_d2',
            'order_plantar_fascial_groove_d2',
            'order_cuboid_unload_d2',
            'order_future_unload_1_d2',
            'order_mortons_extension_d2',
            'order_reverse_mortons_d2',
            'order_kw_unload_in_crepe_d2',
            'order_kw_unload_in_ppt_d2',
            'order_medial_flange_d2',
            'order_lateral_flange_d2',
            'order_kirby_skive_2mm_d2',
            'order_kirby_skive_4mm_d2',
            'order_1st_met_cut_out_shell_d2',
            'order_heel_lift_amt_in_notes_d2',
            'order_future_accommodation_1_d2',
            'order_future_accommodation_2_d2',
            'order_transmet_filler_d2',
            'order_hallux_filler_d2',
            'order_lesser_toe_fillers_d2',
            'order_whole_foot_wrymark_d2',
            'order_gait_plate_toe_out_d2',
            'order_gait_plate_to_toe_in_d2',
            'order_add_ppt_schaphoid_pad_d2',
            'order_central_heel_reliefs_d2',
            'order_met_ridge_6mm_ppt_d2',
            'order_fill_under_arch_ppt_d2',
            'order_heel_pad_3mm_ppt_d2',
            'order_heel_pad_1_5mm_ppt_d2',
            'order_denton_extend_lateral_d2',
            'order_zero_deg_forefoot_post_d2',
            'order_runners_wedge_medial_d2',
            'order_runners_wedge_lateral_d2',
            'order_future_4_d2',
            'order_future_5_d2',
            'order_future_6_d2',
            'order_future_7_d2',
            'order_future_8_d2',
            'order_future_9_d2',
            'order_future_10_d2',
            'order_future_11_d2',
            'order_future_12_d2',
            'order_practitioner_def_a_d2',
            'order_practitioner_def_b_d2',
            'order_other_notes_d2',
            'order_elog_d2',
            'order_tracking_d2',
            'order_ship_date_d2',
            'order_ship_comp_d2',
            'order_device_type_d3',
            'order_top_cover_d3',
            'order_ppt_layer_d3',
            'order_top_cover_length_d3',
            'order_shell_thickness_d3',
            'order_shell_grind_width_d3',
            'order_shell_arch_fill_d3',
            'order_heel_cup_in_shell_d3',
            'order_forefoot_posting_d3_fpd3_additional',
            'order_forefoot_posting_d3_fpd3_left',
            'order_forefoot_posting_d3_fpd3_right',
            'order_rearfoot_posting_d3_rpd3_additional',
            'order_rearfoot_posting_d3_rpd3_left',
            'order_rearfoot_posting_d3_rpd3_right',
            'order_motion_on_vp_d3_mvpd3_left',
            'order_motion_on_vp_d3_mvpd3_right',
            'order_met_pad_bar_d3',
            'order_met_unloads_in_ppt_d3_1',
            'order_met_unloads_in_ppt_d3_2',
            'order_met_unloads_in_ppt_d3_3',
            'order_met_unloads_in_ppt_d3_4',
            'order_met_unloads_in_ppt_d3_5',
            'order_heel_horseshoe_unload_d3',
            'order_navicular_unload_d3',
            'order_base_of_fifth_unload_d3',
            'order_plantar_fascial_groove_d3',
            'order_cuboid_unload_d3',
            'order_future_unload_d3',
            'order_mortons_extension_d3',
            'order_reverse_mortons_d3',
            'order_kw_unload_in_crepe_d3',
            'order_kw_unload_in_ppt_d3',
            'order_medial_flange_d3',
            'order_lateral_flange_d3',
            'order_kirby_skive_2mm_d3',
            'order_kirby_skive_4mm_d3',
            'order_1st_met_cut_out_shell_d3',
            'order_heel_lift_amt_in_notes_d3',
            'order_future_accommodation_1_d3',
            'order_future_accommodation_2_d3',
            'order_transmet_filler_d3',
            'order_hallux_filler_d3',
            'order_lesser_toe_fillers_d3',
            'order_whole_foot_wrymark_d3',
            'order_gait_plate_toe_out_d3',
            'order_gait_plate_toe_in_d3',
            'order_add_ppt_schaphoid_pad_d3',
            'order_central_heel_reliefs_d3',
            'order_met_ridge_6mm_ppt_d3',
            'order_fill_under_arch_ppt_d3',
            'order_heel_pad_3mm_ppt_d3',
            'order_heel_pad_1_5mm_ppt_d3',
            'order_denton_extend_lateral_d3',
            'order_zero_deg_forefoot_post_d3',
            'order_runners_wedge_medial_d3',
            'order_runners_wedge_lateral_d3',
            'order_future_4_d3',
            'order_future_5_d3',
            'order_future_6_d3',
            'order_future_7_d3',
            'order_future_8_d3',
            'order_future_9_d3',
            'order_future_10_d3',
            'order_future_11_d3',
            'order_future_12_d3',
            'order_practitioner_def_a_d3',
            'order_practitioner_def_b_d3',
            'order_other_notes_d3',
            'order_elog_d3',
            'order_tracking_d3',
            'order_ship_date_d3',
            'order_ship_comp_d3',
            'order_device_type_d4',
            'order_top_cover_d4',
            'order_ppt_layer_d4',
            'order_top_cover_length_d4',
            'order_shell_thickness_d4',
            'order_shell_grind_width_d4',
            'order_shell_arch_fill_d4',
            'order_heel_cup_in_shell_d4',
            'order_forefoot_posting_d4_fpd4_additional',
            'order_forefoot_posting_d4_fpd4_left',
            'order_forefoot_posting_d4_fpd4_right',
            'order_rearfoot_posting_d4_rpd4_additional',
            'order_rearfoot_posting_d4_rpd4_left',
            'order_rearfoot_posting_d4_rpd4_right',
            'order_motion_on_vp_d4_mvpd4_left',
            'order_motion_on_vp_d4_mvpd4_right',
            'order_met_pad_bar_d4',
            'order_met_unloads_in_ppt_d4_1',
            'order_met_unloads_in_ppt_d4_2',
            'order_met_unloads_in_ppt_d4_3',
            'order_met_unloads_in_ppt_d4_4',
            'order_met_unloads_in_ppt_d4_5',
            'order_heel_horseshoe_unload_d4',
            'order_navicular_unload_d4',
            'order_base_of_fifth_unload_d4',
            'order_plantar_fascial_groove_d4',
            'order_cuboid_unload_d4',
            'order_future_unload_1_d4',
            'order_mortons_extension_d4',
            'order_reverse_mortons_d4',
            'order_kw_unload_in_crepe_d4',
            'order_kw_unload_in_ppt_d4',
            'order_medial_flange_d4',
            'order_lateral_flange_d4',
            'order_kirby_skive_2mm_d4',
            'order_kirby_skive_4mm_d4',
            'order_1st_met_cut_out_shell_d4',
            'order_heel_lift_amt_in_notes_d4',
            'order_future_accommodation_1_d4',
            'order_future_accommodation_2_d4',
            'order_transmet_filler_d4',
            'order_hallux_filler_d4',
            'order_lesser_toe_fillers_d4',
            'order_whole_foot_wrymark_d4',
            'order_gait_plate_toe_out_d4',
            'order_gait_plate_toe_in_d4',
            'order_add_ppt_schaphoid_pad_d4',
            'order_central_heel_reliefs_d4',
            'order_met_ridge_6mm_ppt_d4',
            'order_fill_under_arch_ppt_d4',
            'order_heel_pad_3mm_ppt_d4',
            'order_heel_pad_1_5mm_ppt_d4',
            'order_denton_extend_lateral_d4',
            'order_zero_deg_forefoot_post_d4',
            'order_runners_wedge_medial_d4',
            'order_runners_wedge_lateral_d4',
            'order_future_4_d4',
            'order_future_5_d4',
            'order_future_6_d4',
            'order_future_7_d4',
            'order_future_8_d4',
            'order_future_9_d4',
            'order_future_10_d4',
            'order_future_11_d4',
            'order_future_12_d4',
            'order_practitioner_defined_a_d4',
            'order_practitioner_defined_b_d4',
            'order_other_notes_d4',
            'order_elog_d4',
            'order_tracking_d4',
            'order_ship_date_d4',
            'order_ship_comp_d4',
            'order_confirm_problem_for_adjust',
            'order_confirm_type_of_work',
            'order_internal_doctor_notes',
            'order_previous_elog',
            'order_mirror_plaster',
            'order_miscellaneous_charges',
            'order_discount_rush',
            'order_discount_device',
            'order_device_must_ship_by',
            'order_markings_notes',
            'order_save_ship_back_casts',
            'order_scanning_notes',
            'order_correcting_notes',
            'order_pulling_notes',
            'order_finishing_notes_1',
            'order_finishing_notes_2',
            'order_other_shipping_notes',
            'order_billing_notes',
            'order_elog',
            'order_date_accepted',
            'order_date_submitted',
            'order_date_shipped',
            'order_practice_select',
            'order_doctor_select',
            'order_assistant_select'
        ];
    }

}
