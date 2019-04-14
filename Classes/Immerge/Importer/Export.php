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
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Export - Exporter For Richey Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Export
{

    public static $model;
    public $sql_data;
    public $writer;
    public $order_status;
    public $change;
    public $log;

    // We need the order status and if we need to change
    // the orders from 'Accepted' to 'Accepted-Exported'
    public function __construct($status, $change = NULL)
    {
        $this->order_status = $status;
        static::$model = Models::getInstance();
        $this->writer = WriterFactory::create(Type::CSV);
        $this->log = new Logger('export');
        if ($change != NULL ? $this->change = $change : $this->change = NULL);
    }




    /**
    * main - The main controller for the exporter.
    *        1. If the change parameter was passed in with the constructor, update the accepted orders
    *        2. Open the spreadsheet file to start writing to it
    *        3. Build the spreadsheet
    *        4. Close the spreadsheet
    *
    * @return nothing
    */

    public function main()
    {
        if ($this->change != NULL ? static::$model->updateAcceptedOrder() : NULL);
        $this->openTheSpreadsheet();
        $this->buildTheSpreadSheet();
        $this->writer->close();
    }




    /**
    * openTheSpreadsheet - Opens a new spreadsheet to write to
    *
    * @return nothing
    */

    public function openTheSpreadsheet()
    {

        // Name the file
        if ($this->order_status != NULL ? $name = $this->order_status : $name = 'export');
        $file_name = $name . '_' . date("Y-m-d") . '.csv';

        // Open the file to begin writing
        $this->writer->openToBrowser($file_name);
    }




    /**
    * buildTheSpreadsheet - Builds the spreadsheet
    *
    * @return spreadsheet
    */

    public function buildTheSpreadSheet()
    {
        $header = static::$model->createExportHeaderRow();
        $this->writer->addRow($header);
        $this->buildEachRow();
    }




    /**
    * buildEachRow - Builds each row of the spreadsheet
    *
    * @return array
    */

    public function buildEachRow()
    {
        $orders = static::$model->getAllOrders($this->order_status);

        foreach ($orders as $order_id)
        {
            // Get all of the data
            $results = static::$model->getAllOrderDetails($order_id);
            $titles = static::$model->getChannelTitleDataForExport($order_id);
            $members = static::$model->getMemberDataForExport($titles['author_id']);

            // Get each of the matrix data from the order field group

            // NOTE: This is pretty gross. But due to how maxtrix saves data in the database this
            // is the best implementation without having to perform 12 table joins for one entry_id. - Joey
            $mx1 = static::$model->getMatrixColumns($mx1Data = ['entry_id' => $order_id, 'field_id' => '68']);
            $mx2 = static::$model->getMatrixColumns($mx2Data = ['entry_id' => $order_id, 'field_id' => '69']);
            $mx3 = static::$model->getMatrixColumns($mx3Data = ['entry_id' => $order_id, 'field_id' => '70']);
            $mx4 = static::$model->getMatrixColumns($mx4Data = ['entry_id' => $order_id, 'field_id' => '85']);
            $mx5 = static::$model->getMatrixColumns($mx5Data = ['entry_id' => $order_id, 'field_id' => '86']);
            $mx6 = static::$model->getMatrixColumns($mx6Data = ['entry_id' => $order_id, 'field_id' => '87']);
            $mx7 = static::$model->getMatrixColumns($mx7Data = ['entry_id' => $order_id, 'field_id' => '102']);
            $mx8 = static::$model->getMatrixColumns($mx8Data = ['entry_id' => $order_id, 'field_id' => '103']);
            $mx9 = static::$model->getMatrixColumns($mx9Data = ['entry_id' => $order_id, 'field_id' => '104']);
            $mx10 = static::$model->getMatrixColumns($mx10Data = ['entry_id' => $order_id, 'field_id' => '119']);
            $mx11 = static::$model->getMatrixColumns($mx11Data = ['entry_id' => $order_id, 'field_id' => '120']);
            $mx12 = static::$model->getMatrixColumns($mx12Data = ['entry_id' => $order_id, 'field_id' => '121']);

            $new_row = [
                'title' => $titles['title'],
                'url_title' => $titles['url_title'],
                'status' => $titles['status'],
                'entry_date' => $titles['entry_date'],
                'entry_id' => $order_id,
                'site_id' => $titles['site_id'],
                'channel_id' => $titles['channel_id'],
                'order_date_scanned' => $results['order_date_scanned'],
                'order_date_of_pt' => $results['order_date_of_pt'],
                'order_date_shipped' => $results['order_date_shipped'],
                'order_recover_refurb_ship_date' => $results['order_recover_refurb_ship_date'],
                'order_adjustment_ship_date' => $results['order_adjustment_ship_date'],
                'order_ship_date_d1' => $results['order_ship_date_d1'],
                'order_ship_date_d2' => $results['order_ship_date_d2'],
                'order_ship_date_d3' => $results['order_ship_date_d3'],
                'order_ship_date_d4' => $results['order_ship_date_d4'],
                'order_date_submitted' => $results['order_date_submitted'],
                'author_id' => $titles['author_id'],
                'author_data_username' => $members['author_data_username'],
                'author_data_member_id' => $members['author_data_member_id'],
                'author_data_screen_name' => $members['author_data_screen_name'],
                'author_data_email' => $members['author_data_email'],
                'author_data_join_date' => $members['author_data_join_date'],
                'author_data_last_visit' => $members['author_data_last_visit'],
                'author_data_group_id' => $members['author_data_group_id'],
                'author_data_in_authorlist' => $members['author_data_in_authorlist'],
                'order_practice_name' => $results['order_practice_name'],
                'order_practice_address' => $results['order_practice_address'],
                'order_practice_phone' => $results['order_practice_phone'],
                'order_practice_contact_email' => $results['order_practice_contact_email'],
                'order_practice_shipping_code' => $results['order_practice_shipping_code'],
                'order_practice_billing_code' => $results['order_practice_billing_code'],
                'order_doctor' => $results['order_doctor'],
                'order_special_doctor_notes' => $results['order_special_doctor_notes'],
                'order_staff' => $results['order_staff'],
                'order_patient_first_name' => $results['order_patient_first_name'],
                'order_patient_last_name' => $results['order_patient_last_name'],
                'order_patient_id' => $results['order_patient_id'],
                'order_three_day_rush' => $results['order_three_day_rush'],
                'order_shipping_speed' => $results['order_shipping_speed'],
                'order_ship_to' => $results['order_ship_to'],
                'order_patient_shipping_street' => $results['order_patient_shipping_street'],
                'order_patient_shipping_city' => $results['order_patient_shipping_city'],
                'order_patient_shipping_state' => $results['order_patient_shipping_state'],
                'order_patient_shipping_zip' => $results['order_patient_shipping_zip'],
                'order_patient_email' => $results['order_patient_email'],
                'order_patient_phone' => $results['order_patient_phone'],
                'order_patient_weight' => $results['order_patient_weight'],
                'order_patient_age' => $results['order_patient_age'],
                'order_patient_gender' => $results['order_patient_gender'],
                'order_patient_shoe_size' => $results['order_patient_shoe_size'],
                'order_patient_shoe_width' => $results['order_patient_shoe_width'],
                'order_diagnosis' => $results['order_diagnosis'],
                'order_see_pictures_in_dropbox' => $results['order_see_pictures_in_dropbox'],
                'order_pair_one_foot' => $results['order_pair_one_foot'],
                'order_additional_items' => $results['order_additional_items'],
                'order_type_of_order' => $results['order_type_of_order'],
                'order_recover_refurb_po' => $results['order_recover_refurb_po'],
                'order_recover_refurb_work' => $results['order_recover_refurb_work'],
                'order_recover_refurb_notes' => $results['order_recover_refurb_notes'],
                'order_recover_refurb_tracking' => $results['order_recover_refurb_tracking'],
                'order_recover_refurb_elog' => $results['order_recover_refurb_elog'],
                'order_recover_refurb_ship_comp' => $results['order_recover_refurb_ship_comp'],
                'order_original_elog' => $results['order_original_elog'],
                'order_primary_problem' => $results['order_primary_problem'],
                'order_adjustment_elog' => $results['order_adjustment_elog'],
                'order_adjustment_tracking' => $results['order_adjustment_tracking'],
                'order_adjustment_notes' => $results['order_adjustment_notes'],
                'order_adjustment_ship_comp' => $results['order_adjustment_ship_comp'],
                'order_new_device_po' => $results['order_new_device_po'],
                'order_new_device_quantity' => $results['order_new_device_quantity'],
                'order_pt_coverage_frequency' => $results['order_pt_coverage_frequency'],
                'order_source_of_foot_impressions' => $results['order_source_of_foot_impressions'],
                'order_weight_bearing_arch_type' => $results['order_weight_bearing_arch_type'],
                'order_scan_attachements' => $results['order_scan_attachements'],
                'order_images_video' => $results['order_images_video'],
                'order_device_type_d1' => $results['order_device_type_d1'],
                'order_top_cover_d1' => $results['order_top_cover_d1'],
                'order_ppt_layer_d1' => $results['order_ppt_layer_d1'],
                'order_top_cover_length_d1' => $results['order_top_cover_length_d1'],
                'order_shell_thickness_d1' => $results['order_shell_thickness_d1'],
                'order_shell_grind_width_d1' => $results['order_shell_grind_width_d1'],
                'order_shell_arch_fill_d1' => $results['order_shell_arch_fill_d1'],
                'order_heel_cup_in_shell_d1' => $results['order_heel_cup_in_shell_d1'],
                'order_forefoot_posting_d1_fpd1_additional' => $mx1['col_id_4'],
                'order_forefoot_posting_d1_fpd1_left' => $mx1['col_id_5'],
                'order_forefoot_posting_d1_fpd1_right' => $mx1['col_id_6'],
                'order_rearfoot_posting_d1_rpd1_additional' => $mx2['col_id_7'],
                'order_rearfoot_posting_d1_rpd1_left' => $mx2['col_id_8'],
                'order_rearfoot_posting_d1_rpd1_right' => $mx2['col_id_9'],
                'order_motion_on_vp_d1_mvpd1_left' => $mx3['col_id_10'],
                'order_motion_on_vp_d1_mvpd1_right' => $mx3['col_id_11'],
                'order_met_pad_bar_d1' => $results['order_met_pad_bar_d1'],
                'order_met_unloads_in_ppt_d1_1' => $results['order_met_unloads_in_ppt_d1_1'],
                'order_met_unloads_in_ppt_d1_2' => $results['order_met_unloads_in_ppt_d1_2'],
                'order_met_unloads_in_ppt_d1_3' => $results['order_met_unloads_in_ppt_d1_3'],
                'order_met_unloads_in_ppt_d1_4' => $results['order_met_unloads_in_ppt_d1_4'],
                'order_met_unloads_in_ppt_d1_5' => $results['order_met_unloads_in_ppt_d1_5'],
                'order_heel_horseshoe_unload_d1' => $results['order_heel_horseshoe_unload_d1'],
                'order_navicular_unload_d1' => $results['order_navicular_unload_d1'],
                'order_base_of_fifth_unload_d1' => $results['order_base_of_fifth_unload_d1'],
                'order_plantar_fascial_groove_d1' => $results['order_plantar_fascial_groove_d1'],
                'order_cuboid_unload_d1' => $results['order_cuboid_unload_d1'],
                'order_future_unload_1_d1' => $results['order_future_unload_1_d1'],
                'order_mortons_extension_d1' => $results['order_mortons_extension_d1'],
                'order_reverse_mortons_d1' => $results['order_reverse_mortons_d1'],
                'order_kw_unload_in_crepe_d1' => $results['order_kw_unload_in_crepe_d1'],
                'order_kw_unload_in_ppt_d1' => $results['order_kw_unload_in_ppt_d1'],
                'order_medial_flange_d1' => $results['order_medial_flange_d1'],
                'order_lateral_flange_d1' => $results['order_lateral_flange_d1'],
                'order_kirby_skive_2mm_d1' => $results['order_kirby_skive_2mm_d1'],
                'order_kirby_skive_4mm_d1' => $results['order_kirby_skive_4mm_d1'],
                'order_1st_met_cut_out_shell_d1' => $results['order_1st_met_cut_out_shell_d1'],
                'order_heel_lift_amt_in_notes_d1' => $results['order_heel_lift_amt_in_notes_d1'],
                'order_future_accommodation_1_d1' => $results['order_future_accommodation_1_d1'],
                'order_future_accommodation_2_d1' => $results['order_future_accommodation_2_d1'],
                'order_transmet_filler_d1' => $results['order_transmet_filler_d1'],
                'order_hallux_filler_d1' => $results['order_hallux_filler_d1'],
                'order_lesser_toe_fillers_d1' => $results['order_lesser_toe_fillers_d1'],
                'order_whole_foot_wrymark_d1' => $results['order_whole_foot_wrymark_d1'],
                'order_gait_plate_toe_out_d1' => $results['order_gait_plate_toe_out_d1'],
                'order_gait_plate_toe_in_d1' => $results['order_gait_plate_toe_in_d1'],
                'order_add_ppt_schaphoid_pad_d1' => $results['order_add_ppt_schaphoid_pad_d1'],
                'order_central_heel_reliefs_d1' => $results['order_central_heel_reliefs_d1'],
                'order_met_ridge_6mm_ppt_d1' => $results['order_met_ridge_6mm_ppt_d1'],
                'order_fill_under_arch_ppt_d1' => $results['order_fill_under_arch_ppt_d1'],
                'order_heel_pad_3mm_ppt_d1' => $results['order_heel_pad_3mm_ppt_d1'],
                'order_heel_pad_1_5mm_ppt_d1' => $results['order_heel_pad_1_5mm_ppt_d1'],
                'order_denton_extend_lateral_d1' => $results['order_denton_extend_lateral_d1'],
                'order_zero_deg_forefoot_post_d1' => $results['order_zero_deg_forefoot_post_d1'],
                'order_runners_wedge_medial_d1' => $results['order_runners_wedge_medial_d1'],
                'order_runners_wedge_lateral_d1' => $results['order_runners_wedge_lateral_d1'],
                'order_future_4_d1' => $results['order_future_4_d1'],
                'order_future_5_d1' => $results['order_future_5_d1'],
                'order_future_6_d1' => $results['order_future_6_d1'],
                'order_future_7_d1' => $results['order_future_7_d1'],
                'order_future_8_d1' => $results['order_future_8_d1'],
                'order_future_9_d1' => $results['order_future_9_d1'],
                'order_future_10_d1' => $results['order_future_10_d1'],
                'order_future_11_d1' => $results['order_future_11_d1'],
                'order_future_12_d1' => $results['order_future_12_d1'],
                'order_practitioner_def_a_d1' => $results['order_practitioner_def_a_d1'],
                'order_practitioner_def_b_d1' => $results['order_practitioner_def_b_d1'],
                'order_other_notes_d1' => $results['order_other_notes_d1'],
                'order_elog_d1' => $results['order_elog_d1'],
                'order_tracking_d1' => $results['order_tracking_d1'],
                'order_ship_comp_d1' => $results['order_ship_comp_d1'],
                'order_device_type_d2' => $results['order_device_type_d2'],
                'order_top_cover_d2' => $results['order_top_cover_d2'],
                'order_ppt_layer_d2' => $results['order_ppt_layer_d2'],
                'order_top_cover_length_d2' => $results['order_top_cover_length_d2'],
                'order_shell_thickness_d2' => $results['order_shell_thickness_d2'],
                'order_shell_grind_width_d2' => $results['order_shell_grind_width_d2'],
                'order_shell_arch_fill_d2' => $results['order_shell_arch_fill_d2'],
                'order_heel_cup_in_shell_d2' => $results['order_heel_cup_in_shell_d2'],
                'order_forefoot_posting_d2_fpd2_additional' => $mx4['col_id_24'],
                'order_forefoot_posting_d2_fpd2_left' => $mx4['col_id_25'],
                'order_forefoot_posting_d2_fpd2_right' => $mx4['col_id_26'],
                'order_rearfoot_posting_d2_rpd2_additional' => $mx5['col_id_27'],
                'order_rearfoot_posting_d2_rpd2_left' => $mx5['col_id_28'],
                'order_rearfoot_posting_d2_rpd2_right' => $mx5['col_id_29'],
                'order_motion_on_vp_d2_mvpd2_left' => $mx6['col_id_30'],
                'order_motion_on_vp_d2_mvpd2_right' => $mx6['col_id_31'],
                'order_met_pad_bar_d2' => $results['order_met_pad_bar_d2'],
                'order_met_unloads_in_ppt_d2_1' => $results['order_met_unloads_in_ppt_d2_1'],
                'order_met_unloads_in_ppt_d2_2' => $results['order_met_unloads_in_ppt_d2_2'],
                'order_met_unloads_in_ppt_d2_3' => $results['order_met_unloads_in_ppt_d2_3'],
                'order_met_unloads_in_ppt_d2_4' => $results['order_met_unloads_in_ppt_d2_4'],
                'order_met_unloads_in_ppt_d2_5' => $results['order_met_unloads_in_ppt_d2_5'],
                'order_heel_horseshoe_unload_d2' => $results['order_heel_horseshoe_unload_d2'],
                'order_navicular_unload_d2' => $results['order_navicular_unload_d2'],
                'order_base_of_fifth_unload_d2' => $results['order_base_of_fifth_unload_d2'],
                'order_plantar_fascial_groove_d2' => $results['order_plantar_fascial_groove_d2'],
                'order_cuboid_unload_d2' => $results['order_cuboid_unload_d2'],
                'order_future_unload_1_d2' => $results['order_future_unload_1_d2'],
                'order_mortons_extension_d2' => $results['order_mortons_extension_d2'],
                'order_reverse_mortons_d2' => $results['order_reverse_mortons_d2'],
                'order_kw_unload_in_crepe_d2' => $results['order_kw_unload_in_crepe_d2'],
                'order_kw_unload_in_ppt_d2' => $results['order_kw_unload_in_ppt_d2'],
                'order_medial_flange_d2' => $results['order_medial_flange_d2'],
                'order_lateral_flange_d2' => $results['order_lateral_flange_d2'],
                'order_kirby_skive_2mm_d2' => $results['order_kirby_skive_2mm_d2'],
                'order_kirby_skive_4mm_d2' => $results['order_kirby_skive_4mm_d2'],
                'order_1st_met_cut_out_shell_d2' => $results['order_1st_met_cut_out_shell_d2'],
                'order_heel_lift_amt_in_notes_d2' => $results['order_heel_lift_amt_in_notes_d2'],
                'order_future_accommodation_1_d2' => $results['order_future_accommodation_1_d2'],
                'order_future_accommodation_2_d2' => $results['order_future_accommodation_2_d2'],
                'order_transmet_filler_d2' => $results['order_transmet_filler_d2'],
                'order_hallux_filler_d2' => $results['order_hallux_filler_d2'],
                'order_lesser_toe_fillers_d2' => $results['order_lesser_toe_fillers_d2'],
                'order_whole_foot_wrymark_d2' => $results['order_whole_foot_wrymark_d2'],
                'order_gait_plate_toe_out_d2' => $results['order_gait_plate_toe_out_d2'],
                'order_gait_plate_to_toe_in_d2' => $results['order_gait_plate_to_toe_in_d2'],
                'order_add_ppt_schaphoid_pad_d2' => $results['order_add_ppt_schaphoid_pad_d2'],
                'order_central_heel_reliefs_d2' => $results['order_central_heel_reliefs_d2'],
                'order_met_ridge_6mm_ppt_d2' => $results['order_met_ridge_6mm_ppt_d2'],
                'order_fill_under_arch_ppt_d2' => $results['order_fill_under_arch_ppt_d2'],
                'order_heel_pad_3mm_ppt_d2' => $results['order_heel_pad_3mm_ppt_d2'],
                'order_heel_pad_1_5mm_ppt_d2' => $results['order_heel_pad_1_5mm_ppt_d2'],
                'order_denton_extend_lateral_d2' => $results['order_denton_extend_lateral_d2'],
                'order_zero_deg_forefoot_post_d2' => $results['order_zero_deg_forefoot_post_d2'],
                'order_runners_wedge_medial_d2' => $results['order_runners_wedge_medial_d2'],
                'order_runners_wedge_lateral_d2' => $results['order_runners_wedge_lateral_d2'],
                'order_future_4_d2' => $results['order_future_4_d2'],
                'order_future_5_d2' => $results['order_future_5_d2'],
                'order_future_6_d2' => $results['order_future_6_d2'],
                'order_future_7_d2' => $results['order_future_7_d2'],
                'order_future_8_d2' => $results['order_future_8_d2'],
                'order_future_9_d2' => $results['order_future_9_d2'],
                'order_future_10_d2' => $results['order_future_10_d2'],
                'order_future_11_d2' => $results['order_future_11_d2'],
                'order_future_12_d2' => $results['order_future_12_d2'],
                'order_practitioner_def_a_d2' => $results['order_practitioner_def_a_d2'],
                'order_practitioner_def_b_d2' => $results['order_practitioner_def_b_d2'],
                'order_other_notes_d2' => $results['order_other_notes_d2'],
                'order_elog_d2' => $results['order_elog_d2'],
                'order_tracking_d2' => $results['order_tracking_d2'],
                'order_ship_comp_d2' => $results['order_ship_comp_d2'],
                'order_device_type_d3' => $results['order_device_type_d3'],
                'order_top_cover_d3' => $results['order_top_cover_d3'],
                'order_ppt_layer_d3' => $results['order_ppt_layer_d3'],
                'order_top_cover_length_d3' => $results['order_top_cover_length_d3'],
                'order_shell_thickness_d3' => $results['order_shell_thickness_d3'],
                'order_shell_grind_width_d3' => $results['order_shell_grind_width_d3'],
                'order_shell_arch_fill_d3' => $results['order_shell_arch_fill_d3'],
                'order_heel_cup_in_shell_d3' => $results['order_heel_cup_in_shell_d3'],
                'order_forefoot_posting_d3_fpd3_additional' => $mx7['col_id_44'],
                'order_forefoot_posting_d3_fpd3_left' => $mx7['col_id_45'],
                'order_forefoot_posting_d3_fpd3_right' => $mx7['col_id_46'],
                'order_rearfoot_posting_d3_rpd3_additional' => $mx8['col_id_48'],
                'order_rearfoot_posting_d3_rpd3_left' => $mx8['col_id_49'],
                'order_rearfoot_posting_d3_rpd3_right' => $mx8['col_id_50'],
                'order_motion_on_vp_d3_mvpd3_left' => $mx9['col_id_50'],
                'order_motion_on_vp_d3_mvpd3_right' => $mx9['col_id_51'],
                'order_met_pad_bar_d3' => $results['order_met_pad_bar_d3'],
                'order_met_unloads_in_ppt_d3_1' => $results['order_met_unloads_in_ppt_d3_1'],
                'order_met_unloads_in_ppt_d3_2' => $results['order_met_unloads_in_ppt_d3_2'],
                'order_met_unloads_in_ppt_d3_3' => $results['order_met_unloads_in_ppt_d3_3'],
                'order_met_unloads_in_ppt_d3_4' => $results['order_met_unloads_in_ppt_d3_4'],
                'order_met_unloads_in_ppt_d3_5' => $results['order_met_unloads_in_ppt_d3_5'],
                'order_heel_horseshoe_unload_d3' => $results['order_heel_horseshoe_unload_d3'],
                'order_navicular_unload_d3' => $results['order_navicular_unload_d3'],
                'order_base_of_fifth_unload_d3' => $results['order_base_of_fifth_unload_d3'],
                'order_plantar_fascial_groove_d3' => $results['order_plantar_fascial_groove_d3'],
                'order_cuboid_unload_d3' => $results['order_cuboid_unload_d3'],
                'order_future_unload_d3' => $results['order_future_unload_d3'],
                'order_mortons_extension_d3' => $results['order_mortons_extension_d3'],
                'order_reverse_mortons_d3' => $results['order_reverse_mortons_d3'],
                'order_kw_unload_in_crepe_d3' => $results['order_kw_unload_in_crepe_d3'],
                'order_kw_unload_in_ppt_d3' => $results['order_kw_unload_in_ppt_d3'],
                'order_medial_flange_d3' => $results['order_medial_flange_d3'],
                'order_lateral_flange_d3' => $results['order_lateral_flange_d3'],
                'order_kirby_skive_2mm_d3' => $results['order_kirby_skive_2mm_d3'],
                'order_kirby_skive_4mm_d3' => $results['order_kirby_skive_4mm_d3'],
                'order_1st_met_cut_out_shell_d3' => $results['order_1st_met_cut_out_shell_d3'],
                'order_heel_lift_amt_in_notes_d3' => $results['order_heel_lift_amt_in_notes_d3'],
                'order_future_accommodation_1_d3' => $results['order_future_accommodation_1_d3'],
                'order_future_accommodation_2_d3' => $results['order_future_accommodation_2_d3'],
                'order_transmet_filler_d3' => $results['order_transmet_filler_d3'],
                'order_hallux_filler_d3' => $results['order_hallux_filler_d3'],
                'order_lesser_toe_fillers_d3' => $results['order_lesser_toe_fillers_d3'],
                'order_whole_foot_wrymark_d3' => $results['order_whole_foot_wrymark_d3'],
                'order_gait_plate_toe_out_d3' => $results['order_gait_plate_toe_out_d3'],
                'order_gait_plate_toe_in_d3' => $results['order_gait_plate_toe_in_d3'],
                'order_add_ppt_schaphoid_pad_d3' => $results['order_add_ppt_schaphoid_pad_d3'],
                'order_central_heel_reliefs_d3' => $results['order_central_heel_reliefs_d3'],
                'order_met_ridge_6mm_ppt_d3' => $results['order_met_ridge_6mm_ppt_d3'],
                'order_fill_under_arch_ppt_d3' => $results['order_fill_under_arch_ppt_d3'],
                'order_heel_pad_3mm_ppt_d3' => $results['order_heel_pad_3mm_ppt_d3'],
                'order_heel_pad_1_5mm_ppt_d3' => $results['order_heel_pad_1_5mm_ppt_d3'],
                'order_denton_extend_lateral_d3' => $results['order_denton_extend_lateral_d3'],
                'order_zero_deg_forefoot_post_d3' => $results['order_zero_deg_forefoot_post_d3'],
                'order_runners_wedge_medial_d3' => $results['order_runners_wedge_medial_d3'],
                'order_runners_wedge_lateral_d3' => $results['order_runners_wedge_lateral_d3'],
                'order_future_4_d3' => $results['order_future_4_d3'],
                'order_future_5_d3' => $results['order_future_5_d3'],
                'order_future_6_d3' => $results['order_future_6_d3'],
                'order_future_7_d3' => $results['order_future_7_d3'],
                'order_future_8_d3' => $results['order_future_8_d3'],
                'order_future_9_d3' => $results['order_future_9_d3'],
                'order_future_10_d3' => $results['order_future_10_d3'],
                'order_future_11_d3' => $results['order_future_11_d3'],
                'order_future_12_d3' => $results['order_future_12_d3'],
                'order_practitioner_def_a_d3' => $results['order_practitioner_def_a_d3'],
                'order_practitioner_def_b_d3' => $results['order_practitioner_def_b_d3'],
                'order_other_notes_d3' => $results['order_other_notes_d3'],
                'order_elog_d3' => $results['order_elog_d3'],
                'order_tracking_d3' => $results['order_tracking_d3'],
                'order_ship_comp_d3' => $results['order_ship_comp_d3'],
                'order_device_type_d4' => $results['order_device_type_d4'],
                'order_top_cover_d4' => $results['order_top_cover_d4'],
                'order_ppt_layer_d4' => $results['order_ppt_layer_d4'],
                'order_top_cover_length_d4' => $results['order_top_cover_length_d4'],
                'order_shell_thickness_d4' => $results['order_shell_thickness_d4'],
                'order_shell_grind_width_d4' => $results['order_shell_grind_width_d4'],
                'order_shell_arch_fill_d4' => $results['order_shell_arch_fill_d4'],
                'order_heel_cup_in_shell_d4' => $results['order_heel_cup_in_shell_d4'],
                'order_forefoot_posting_d4_fpd4_additional' => $mx10['col_id_64'],
                'order_forefoot_posting_d4_fpd4_left' => $mx10['col_id_65'],
                'order_forefoot_posting_d4_fpd4_right' => $mx10['col_id_66'],
                'order_rearfoot_posting_d4_rpd4_additional' => $mx11['col_id_68'],
                'order_rearfoot_posting_d4_rpd4_left' => $mx11['col_id_69'],
                'order_rearfoot_posting_d4_rpd4_right' => $mx11['col_id_70'],
                'order_motion_on_vp_d4_mvpd4_left' => $mx12['col_id_70'],
                'order_motion_on_vp_d4_mvpd4_right' => $mx12['col_id_71'],
                'order_met_pad_bar_d4' => $results['order_met_pad_bar_d4'],
                'order_met_unloads_in_ppt_d4_1' => $results['order_met_unloads_in_ppt_d4_1'],
                'order_met_unloads_in_ppt_d4_2' => $results['order_met_unloads_in_ppt_d4_2'],
                'order_met_unloads_in_ppt_d4_3' => $results['order_met_unloads_in_ppt_d4_3'],
                'order_met_unloads_in_ppt_d4_4' => $results['order_met_unloads_in_ppt_d4_4'],
                'order_met_unloads_in_ppt_d4_5' => $results['order_met_unloads_in_ppt_d4_5'],
                'order_heel_horseshoe_unload_d4' => $results['order_heel_horseshoe_unload_d4'],
                'order_navicular_unload_d4' => $results['order_navicular_unload_d4'],
                'order_base_of_fifth_unload_d4' => $results['order_base_of_fifth_unload_d4'],
                'order_plantar_fascial_groove_d4' => $results['order_plantar_fascial_groove_d4'],
                'order_cuboid_unload_d4' => $results['order_cuboid_unload_d4'],
                'order_future_unload_1_d4' => $results['order_future_unload_1_d4'],
                'order_mortons_extension_d4' => $results['order_mortons_extension_d4'],
                'order_reverse_mortons_d4' => $results['order_reverse_mortons_d4'],
                'order_kw_unload_in_crepe_d4' => $results['order_kw_unload_in_crepe_d4'],
                'order_kw_unload_in_ppt_d4' => $results['order_kw_unload_in_ppt_d4'],
                'order_medial_flange_d4' => $results['order_medial_flange_d4'],
                'order_lateral_flange_d4' => $results['order_lateral_flange_d4'],
                'order_kirby_skive_2mm_d4' => $results['order_kirby_skive_2mm_d4'],
                'order_kirby_skive_4mm_d4' => $results['order_kirby_skive_4mm_d4'],
                'order_1st_met_cut_out_shell_d4' => $results['order_1st_met_cut_out_shell_d4'],
                'order_heel_lift_amt_in_notes_d4' => $results['order_heel_lift_amt_in_notes_d4'],
                'order_future_accommodation_1_d4' => $results['order_future_accommodation_1_d4'],
                'order_future_accommodation_2_d4' => $results['order_future_accommodation_2_d4'],
                'order_transmet_filler_d4' => $results['order_transmet_filler_d4'],
                'order_hallux_filler_d4' => $results['order_hallux_filler_d4'],
                'order_lesser_toe_fillers_d4' => $results['order_lesser_toe_fillers_d4'],
                'order_whole_foot_wrymark_d4' => $results['order_whole_foot_wrymark_d4'],
                'order_gait_plate_toe_out_d4' => $results['order_gait_plate_toe_out_d4'],
                'order_gait_plate_toe_in_d4' => $results['order_gait_plate_toe_in_d4'],
                'order_add_ppt_schaphoid_pad_d4' => $results['order_add_ppt_schaphoid_pad_d4'],
                'order_central_heel_reliefs_d4' => $results['order_central_heel_reliefs_d4'],
                'order_met_ridge_6mm_ppt_d4' => $results['order_met_ridge_6mm_ppt_d4'],
                'order_fill_under_arch_ppt_d4' => $results['order_fill_under_arch_ppt_d4'],
                'order_heel_pad_3mm_ppt_d4' => $results['order_heel_pad_3mm_ppt_d4'],
                'order_heel_pad_1_5mm_ppt_d4' => $results['order_heel_pad_1_5mm_ppt_d4'],
                'order_denton_extend_lateral_d4' => $results['order_denton_extend_lateral_d4'],
                'order_zero_deg_forefoot_post_d4' => $results['order_zero_deg_forefoot_post_d4'],
                'order_runners_wedge_medial_d4' => $results['order_runners_wedge_medial_d4'],
                'order_runners_wedge_lateral_d4' => $results['order_runners_wedge_lateral_d4'],
                'order_future_4_d4' => $results['order_future_4_d4'],
                'order_future_5_d4' => $results['order_future_5_d4'],
                'order_future_6_d4' => $results['order_future_6_d4'],
                'order_future_7_d4' => $results['order_future_7_d4'],
                'order_future_8_d4' => $results['order_future_8_d4'],
                'order_future_9_d4' => $results['order_future_9_d4'],
                'order_future_10_d4' => $results['order_future_10_d4'],
                'order_future_11_d4' => $results['order_future_11_d4'],
                'order_future_12_d4' => $results['order_future_12_d4'],
                'order_practitioner_defined_a_d4' => $results['order_practitioner_defined_a_d4'],
                'order_practitioner_defined_b_d4' => $results['order_practitioner_defined_b_d4'],
                'order_other_notes_d4' => $results['order_other_notes_d4'],
                'order_elog_d4' => $results['order_elog_d4'],
                'order_tracking_d4' => $results['order_tracking_d4'],
                'order_ship_comp_d4' => $results['order_ship_comp_d4'],
                'order_confirm_problem_for_adjust' => $results['order_confirm_problem_for_adjust'],
                'order_confirm_type_of_work' => $results['order_confirm_type_of_work'],
                'order_internal_doctor_notes' => $results['order_internal_doctor_notes'],
                'order_previous_elog' => $results['order_previous_elog'],
                'order_mirror_plaster' => $results['order_mirror_plaster'],
                'order_miscellaneous_charges' => $results['order_miscellaneous_charges'],
                'order_discount_rush' => $results['order_discount_rush'],
                'order_discount_device' => $results['order_discount_device'],
                'order_device_must_ship_by' => $results['order_device_must_ship_by'],
                'order_markings_notes' => $results['order_markings_notes'],
                'order_save_ship_back_casts' => $results['order_save_ship_back_casts'],
                'order_scanning_notes' => $results['order_scanning_notes'],
                'order_correcting_notes' => $results['order_correcting_notes'],
                'order_pulling_notes' => $results['order_pulling_notes'],
                'order_finishing_notes_1' => $results['order_finishing_notes_1'],
                'order_finishing_notes_2' => $results['order_finishing_notes_2'],
                'order_other_shipping_notes' => $results['order_other_shipping_notes'],
                'order_billing_notes' => $results['order_billing_notes'],
                'order_elog' => $results['order_elog'],
                'order_date_accepted' => $results['order_date_accepted'],
                'order_date_shipped' => $results['order_date_shipped'],
                'order_practice_select' => $results['order_practice_select'],
                'order_doctor_select' => $results['order_doctor_select'],
                'order_assistant_select' => $results['order_assistant_select']
            ];
            
            // Add the data to a new row in the spreadsheet
            $this->writer->addRow($new_row);
        }

        $this->log->write('completed');
    }

}
