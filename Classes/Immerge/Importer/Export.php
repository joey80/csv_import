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
use DateTime;

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
    public $the_date;
    public $total_row_count;

    // We need the order status and if we need to change
    // the orders from 'Accepted' to 'Accepted-Exported'
    public function __construct($status, $change = null)
    {

        $this->order_status = $status;
        static::$model = Models::getInstance();
        $this->writer = WriterFactory::create(Type::CSV);
        $this->log = new Logger(str_replace('-', '_', strtolower($this->order_status)));
        $this->the_date = (new DateTime('America/New_York'))->format('m-d-Y H:i:s');
        if ($change != null ? $this->change = $change : $this->change = null);
    }




    /**
    * main - The main controller for the exporter.
    *        1. If the change parameter was passed in with the constructor, update the accepted orders
    *        2. Open the spreadsheet file to start writing to it
    *        3. Build the spreadsheet
    *        4. Close the spreadsheet
    *        5. Save the results of the export to the settings file
    *
    * @return nothing
    */

    public function main()
    {

        if ($this->change != null ? static::$model->updateAcceptedOrder() : null);
        $this->openTheSpreadsheet();
        $this->buildTheSpreadSheet();
        $this->writer->close();
        
        $this->log->write(' ');
        $this->log->write('The ' . $this->order_status . ' Export Has Completed');
        $this->log->write('################################################');
        $this->saveTheSettings($this->total_row_count);
    }




    /**
    * openTheSpreadsheet - Opens a new spreadsheet to write to
    *
    * @return nothing
    */

    public function openTheSpreadsheet()
    {

        // Name the file
        if ($this->order_status != null ? $name = $this->order_status : $name = 'export');
        $file_name = $name . '_' . $this->the_date . '.csv';

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
        $this->log->write('Starting The ' . $this->order_status . ' Export ' . $this->the_date);
        $this->log->write(' ');

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
        $this->total_row_count = 0;

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
                'A - title' => $titles['title'],
                'B - url_title' => $titles['url_title'],
                'C - status' => $titles['status'],
                'D - entry_date' => $titles['entry_date'],
                'E - entry_id' => $order_id,
                'F - site_id' => $titles['site_id'],
                'G - channel_id' => $titles['channel_id'],
                'H - order_date_scanned' => $results['order_date_scanned'],
                'I - order_date_of_pt' => $results['order_date_of_pt'],
                'J - order_date_shipped' => $results['order_date_shipped'],
                'K - order_recover_refurb_ship_date' => $results['order_recover_refurb_ship_date'],
                'L - order_adjustment_ship_date' => $results['order_adjustment_ship_date'],
                'M - order_ship_date_d1' => $results['order_ship_date_d1'],
                'N - order_ship_date_d2' => $results['order_ship_date_d2'],
                'O - order_ship_date_d3' => $results['order_ship_date_d3'],
                'P - order_ship_date_d4' => $results['order_ship_date_d4'],
                'Q - order_date_submitted' => $results['order_date_submitted'],
                'R - channel_title' => 'Orders',
                'S - author_id' => $titles['author_id'],
                'T - member_id' => $members['author_data_member_id'],
                'U - last_visit' => $members['author_data_last_visit'],
                'V - channel_name' => 'Orders',
                'W - author_data_username' => $members['author_data_username'],
                'X - author_data_member_id' => $members['author_data_member_id'],
                'Y - author_data_screen_name' => $members['author_data_screen_name'],
                'Z - author_data_email' => $members['author_data_email'],
                'AA - author_data_join_date' => $members['author_data_join_date'],
                'AB - author_data_last_visit' => $members['author_data_last_visit'],
                'AC - author_data_group_id' => $members['author_data_group_id'],
                'AD - author_data_in_authorlist' => $members['author_data_in_authorlist'],
                'AE - order_practice_name' => $results['order_practice_name'],
                'AF - order_practice_address' => $results['order_practice_address'],
                'AG - order_practice_phone' => $results['order_practice_phone'],
                'AH - order_practice_contact_email' => $results['order_practice_contact_email'],
                'AI - order_practice_shipping_code' => $results['order_practice_shipping_code'],
                'AJ - order_practice_billing_code' => $results['order_practice_billing_code'],
                'AK - order_doctor' => $results['order_doctor'],
                'AL - order_special_doctor_notes' => $results['order_special_doctor_notes'],
                'AM - order_staff' => $results['order_staff'],
                'AN - order_patient_first_name' => $results['order_patient_first_name'],
                'AO - order_patient_last_name' => $results['order_patient_last_name'],
                'AP - order_patient_id' => $results['order_patient_id'],
                'AQ - order_three_day_rush' => $results['order_three_day_rush'],
                'AR - order_shipping_speed' => $results['order_shipping_speed'],
                'AS - order_ship_to' => $results['order_ship_to'],
                'AT - order_patient_shipping_street' => $results['order_patient_shipping_street'],
                'AU - order_patient_shipping_city' => $results['order_patient_shipping_city'],
                'AV - order_patient_shipping_state' => $results['order_patient_shipping_state'],
                'AW - order_patient_shipping_zip' => $results['order_patient_shipping_zip'],
                'AX - order_patient_email' => $results['order_patient_email'],
                'AY - order_patient_phone' => $results['order_patient_phone'],
                'AZ - order_patient_weight' => $results['order_patient_weight'],
                'BA - order_patient_age' => $results['order_patient_age'],
                'BB - order_patient_gender' => $results['order_patient_gender'],
                'BC - order_patient_shoe_size' => $results['order_patient_shoe_size'],
                'BD - order_patient_shoe_width' => $results['order_patient_shoe_width'],
                'BE - order_diagnosis' => $results['order_diagnosis'],
                'BF - order_see_pictures_in_dropbox' => $results['order_see_pictures_in_dropbox'],
                'BG - order_pair_one_foot' => $results['order_pair_one_foot'],
                'BH - order_additional_items' => $results['order_additional_items'],
                'BI - order_type_of_order' => $results['order_type_of_order'],
                'BJ - order_recover_refurb_po' => $results['order_recover_refurb_po'],
                'BK - order_recover_refurb_work' => $results['order_recover_refurb_work'],
                'BL - order_recover_refurb_notes' => $results['order_recover_refurb_notes'],
                'BM - order_recover_refurb_tracking' => $results['order_recover_refurb_tracking'],
                'BN - order_recover_refurb_elog' => $results['order_recover_refurb_elog'],
                'BO - order_recover_refurb_ship_date' => $results['order_recover_refurb_ship_date'],
                'BP - order_recover_refurb_ship_comp' => $results['order_recover_refurb_ship_comp'],
                'BQ - order_original_elog' => $results['order_original_elog'],
                'BR - order_primary_problem' => $results['order_primary_problem'],
                'BS - order_adjustment_elog' => $results['order_adjustment_elog'],
                'BT - order_adjustment_tracking' => $results['order_adjustment_tracking'],
                'BU - order_adjustment_notes' => $results['order_adjustment_notes'],
                'BV - order_adjustment_ship_date' => $results['order_adjustment_ship_date'],
                'BW - order_adjustment_ship_comp' => $results['order_adjustment_ship_comp'],
                'BX - order_new_device_po' => $results['order_new_device_po'],
                'BY - order_new_device_quantity' => $results['order_new_device_quantity'],
                'BZ - order_date_of_pt' => $results['order_date_of_pt'],
                'CA - order_pt_coverage_frequency' => $results['order_pt_coverage_frequency'],
                'CB - order_source_of_foot_impressions' => $results['order_source_of_foot_impressions'],
                'CC - order_weight_bearing_arch_type' => $results['order_weight_bearing_arch_type'],
                'CD - order_scan_attachements' => $results['order_scan_attachements'],
                'CE - order_date_scanned' => $results['order_date_scanned'],
                'CF - order_images_video' => $results['order_images_video'],
                'CG - order_device_type_d1' => $results['order_device_type_d1'],
                'CH - order_top_cover_d1' => $results['order_top_cover_d1'],
                'CI - order_ppt_layer_d1' => $results['order_ppt_layer_d1'],
                'CJ - order_top_cover_length_d1' => $results['order_top_cover_length_d1'],
                'CK - order_shell_thickness_d1' => $results['order_shell_thickness_d1'],
                'CL - order_shell_grind_width_d1' => $results['order_shell_grind_width_d1'],
                'CM - order_shell_arch_fill_d1' => $results['order_shell_arch_fill_d1'],
                'CN - order_heel_cup_in_shell_d1' => $results['order_heel_cup_in_shell_d1'],
                'CO - order_forefoot_posting_d1_fpd1_additional' => $mx1['col_id_4'],
                'CP - order_forefoot_posting_d1_fpd1_left' => $mx1['col_id_5'],
                'CQ - order_forefoot_posting_d1_fpd1_right' => $mx1['col_id_6'],
                'CR - order_rearfoot_posting_d1_rpd1_additional' => $mx2['col_id_7'],
                'CS - order_rearfoot_posting_d1_rpd1_left' => $mx2['col_id_8'],
                'CT - order_rearfoot_posting_d1_rpd1_right' => $mx2['col_id_9'],
                'CU - order_motion_on_vp_d1_mvpd1_left' => $mx3['col_id_10'],
                'CV - order_motion_on_vp_d1_mvpd1_right' => $mx3['col_id_11'],
                'CW - order_met_pad_bar_d1' => $results['order_met_pad_bar_d1'],
                'CX - order_met_unloads_in_ppt_d1_1' => $results['order_met_unloads_in_ppt_d1_1'],
                'CY - order_met_unloads_in_ppt_d1_2' => $results['order_met_unloads_in_ppt_d1_2'],
                'CZ - order_met_unloads_in_ppt_d1_3' => $results['order_met_unloads_in_ppt_d1_3'],
                'DA - order_met_unloads_in_ppt_d1_4' => $results['order_met_unloads_in_ppt_d1_4'],
                'DB - order_met_unloads_in_ppt_d1_5' => $results['order_met_unloads_in_ppt_d1_5'],
                'DC - order_heel_horseshoe_unload_d1' => $results['order_heel_horseshoe_unload_d1'],
                'DD - order_navicular_unload_d1' => $results['order_navicular_unload_d1'],
                'DE - order_base_of_fifth_unload_d1' => $results['order_base_of_fifth_unload_d1'],
                'DF - order_plantar_fascial_groove_d1' => $results['order_plantar_fascial_groove_d1'],
                'DG - order_cuboid_unload_d1' => $results['order_cuboid_unload_d1'],
                'DH - order_future_unload_1_d1' => $results['order_future_unload_1_d1'],
                'DI - order_mortons_extension_d1' => $results['order_mortons_extension_d1'],
                'DJ - order_reverse_mortons_d1' => $results['order_reverse_mortons_d1'],
                'DK - order_kw_unload_in_crepe_d1' => $results['order_kw_unload_in_crepe_d1'],
                'DL - order_kw_unload_in_ppt_d1' => $results['order_kw_unload_in_ppt_d1'],
                'DM - order_medial_flange_d1' => $results['order_medial_flange_d1'],
                'DN - order_lateral_flange_d1' => $results['order_lateral_flange_d1'],
                'DO - order_kirby_skive_2mm_d1' => $results['order_kirby_skive_2mm_d1'],
                'DP - order_kirby_skive_4mm_d1' => $results['order_kirby_skive_4mm_d1'],
                'DQ - order_1st_met_cut_out_shell_d1' => $results['order_1st_met_cut_out_shell_d1'],
                'DR - order_heel_lift_amt_in_notes_d1' => $results['order_heel_lift_amt_in_notes_d1'],
                'DS - order_future_accommodation_1_d1' => $results['order_future_accommodation_1_d1'],
                'DT - order_future_accommodation_2_d1' => $results['order_future_accommodation_2_d1'],
                'DU - order_transmet_filler_d1' => $results['order_transmet_filler_d1'],
                'DV - order_hallux_filler_d1' => $results['order_hallux_filler_d1'],
                'DW - order_lesser_toe_fillers_d1' => $results['order_lesser_toe_fillers_d1'],
                'DX - order_whole_foot_wrymark_d1' => $results['order_whole_foot_wrymark_d1'],
                'DY - order_gait_plate_toe_out_d1' => $results['order_gait_plate_toe_out_d1'],
                'DZ - order_gait_plate_toe_in_d1' => $results['order_gait_plate_toe_in_d1'],
                'EA - order_add_ppt_schaphoid_pad_d1' => $results['order_add_ppt_schaphoid_pad_d1'],
                'EB - order_central_heel_reliefs_d1' => $results['order_central_heel_reliefs_d1'],
                'EC - order_met_ridge_6mm_ppt_d1' => $results['order_met_ridge_6mm_ppt_d1'],
                'ED - order_fill_under_arch_ppt_d1' => $results['order_fill_under_arch_ppt_d1'],
                'EE - order_heel_pad_3mm_ppt_d1' => $results['order_heel_pad_3mm_ppt_d1'],
                'EF - order_heel_pad_1_5mm_ppt_d1' => $results['order_heel_pad_1_5mm_ppt_d1'],
                'EG - order_denton_extend_lateral_d1' => $results['order_denton_extend_lateral_d1'],
                'EH - order_zero_deg_forefoot_post_d1' => $results['order_zero_deg_forefoot_post_d1'],
                'EI - order_runners_wedge_medial_d1' => $results['order_runners_wedge_medial_d1'],
                'EJ - order_runners_wedge_lateral_d1' => $results['order_runners_wedge_lateral_d1'],
                'EK - order_future_4_d1' => $results['order_future_4_d1'],
                'EL - order_future_5_d1' => $results['order_future_5_d1'],
                'EM - order_future_6_d1' => $results['order_future_6_d1'],
                'EN - order_future_7_d1' => $results['order_future_7_d1'],
                'EO - order_future_8_d1' => $results['order_future_8_d1'],
                'EP - order_future_9_d1' => $results['order_future_9_d1'],
                'EQ - order_future_10_d1' => $results['order_future_10_d1'],
                'ER - order_future_11_d1' => $results['order_future_11_d1'],
                'ES - order_future_12_d1' => $results['order_future_12_d1'],
                'ET - order_practitioner_def_a_d1' => $results['order_practitioner_def_a_d1'],
                'EU - order_practitioner_def_b_d1' => $results['order_practitioner_def_b_d1'],
                'EV - order_other_notes_d1' => $results['order_other_notes_d1'],
                'EW - order_elog_d1' => $results['order_elog_d1'],
                'EX - order_tracking_d1' => $results['order_tracking_d1'],
                'EY - order_ship_date_d1' => $results['order_ship_date_d1'],
                'EZ - order_ship_comp_d1' => $results['order_ship_comp_d1'],
                'FA - order_device_type_d2' => $results['order_device_type_d2'],
                'FB - order_top_cover_d2' => $results['order_top_cover_d2'],
                'FC - order_ppt_layer_d2' => $results['order_ppt_layer_d2'],
                'FD - order_top_cover_length_d2' => $results['order_top_cover_length_d2'],
                'FE - order_shell_thickness_d2' => $results['order_shell_thickness_d2'],
                'FF - order_shell_grind_width_d2' => $results['order_shell_grind_width_d2'],
                'FG - order_shell_arch_fill_d2' => $results['order_shell_arch_fill_d2'],
                'FH - order_heel_cup_in_shell_d2' => $results['order_heel_cup_in_shell_d2'],
                'FI - order_forefoot_posting_d2_fpd2_additional' => $mx4['col_id_24'],
                'FJ - order_forefoot_posting_d2_fpd2_left' => $mx4['col_id_25'],
                'FK - order_forefoot_posting_d2_fpd2_right' => $mx4['col_id_26'],
                'FL - order_rearfoot_posting_d2_rpd2_additional' => $mx5['col_id_27'],
                'FM - order_rearfoot_posting_d2_rpd2_left' => $mx5['col_id_28'],
                'FN - order_rearfoot_posting_d2_rpd2_right' => $mx5['col_id_29'],
                'FO - order_motion_on_vp_d2_mvpd2_left' => $mx6['col_id_30'],
                'FP - order_motion_on_vp_d2_mvpd2_right' => $mx6['col_id_31'],
                'FQ - order_met_pad_bar_d2' => $results['order_met_pad_bar_d2'],
                'FR - order_met_unloads_in_ppt_d2_1' => $results['order_met_unloads_in_ppt_d2_1'],
                'FS - order_met_unloads_in_ppt_d2_2' => $results['order_met_unloads_in_ppt_d2_2'],
                'FT - order_met_unloads_in_ppt_d2_3' => $results['order_met_unloads_in_ppt_d2_3'],
                'FU - order_met_unloads_in_ppt_d2_4' => $results['order_met_unloads_in_ppt_d2_4'],
                'FV - order_met_unloads_in_ppt_d2_5' => $results['order_met_unloads_in_ppt_d2_5'],
                'FW - order_heel_horseshoe_unload_d2' => $results['order_heel_horseshoe_unload_d2'],
                'FX - order_navicular_unload_d2' => $results['order_navicular_unload_d2'],
                'FY - order_base_of_fifth_unload_d2' => $results['order_base_of_fifth_unload_d2'],
                'FZ - order_plantar_fascial_groove_d2' => $results['order_plantar_fascial_groove_d2'],
                'GA - order_cuboid_unload_d2' => $results['order_cuboid_unload_d2'],
                'GB - order_future_unload_1_d2' => $results['order_future_unload_1_d2'],
                'GC - order_mortons_extension_d2' => $results['order_mortons_extension_d2'],
                'GD - order_reverse_mortons_d2' => $results['order_reverse_mortons_d2'],
                'GE - order_kw_unload_in_crepe_d2' => $results['order_kw_unload_in_crepe_d2'],
                'GF - order_kw_unload_in_ppt_d2' => $results['order_kw_unload_in_ppt_d2'],
                'GG - order_medial_flange_d2' => $results['order_medial_flange_d2'],
                'GH - order_lateral_flange_d2' => $results['order_lateral_flange_d2'],
                'GI - order_kirby_skive_2mm_d2' => $results['order_kirby_skive_2mm_d2'],
                'GJ - order_kirby_skive_4mm_d2' => $results['order_kirby_skive_4mm_d2'],
                'GK - order_1st_met_cut_out_shell_d2' => $results['order_1st_met_cut_out_shell_d2'],
                'GL - order_heel_lift_amt_in_notes_d2' => $results['order_heel_lift_amt_in_notes_d2'],
                'GM - order_future_accommodation_1_d2' => $results['order_future_accommodation_1_d2'],
                'GN - order_future_accommodation_2_d2' => $results['order_future_accommodation_2_d2'],
                'GO - order_transmet_filler_d2' => $results['order_transmet_filler_d2'],
                'GP - order_hallux_filler_d2' => $results['order_hallux_filler_d2'],
                'GQ - order_lesser_toe_fillers_d2' => $results['order_lesser_toe_fillers_d2'],
                'GR - order_whole_foot_wrymark_d2' => $results['order_whole_foot_wrymark_d2'],
                'GS - order_gait_plate_toe_out_d2' => $results['order_gait_plate_toe_out_d2'],
                'GT - order_gait_plate_to_toe_in_d2' => $results['order_gait_plate_to_toe_in_d2'],
                'GU - order_add_ppt_schaphoid_pad_d2' => $results['order_add_ppt_schaphoid_pad_d2'],
                'GV - order_central_heel_reliefs_d2' => $results['order_central_heel_reliefs_d2'],
                'GW - order_met_ridge_6mm_ppt_d2' => $results['order_met_ridge_6mm_ppt_d2'],
                'GX - order_fill_under_arch_ppt_d2' => $results['order_fill_under_arch_ppt_d2'],
                'GY - order_heel_pad_3mm_ppt_d2' => $results['order_heel_pad_3mm_ppt_d2'],
                'GZ - order_heel_pad_1_5mm_ppt_d2' => $results['order_heel_pad_1_5mm_ppt_d2'],
                'HA - order_denton_extend_lateral_d2' => $results['order_denton_extend_lateral_d2'],
                'HB - order_zero_deg_forefoot_post_d2' => $results['order_zero_deg_forefoot_post_d2'],
                'HC - order_runners_wedge_medial_d2' => $results['order_runners_wedge_medial_d2'],
                'HD - order_runners_wedge_lateral_d2' => $results['order_runners_wedge_lateral_d2'],
                'HE - order_future_4_d2' => $results['order_future_4_d2'],
                'HF - order_future_5_d2' => $results['order_future_5_d2'],
                'HG - order_future_6_d2' => $results['order_future_6_d2'],
                'HH - order_future_7_d2' => $results['order_future_7_d2'],
                'HI - order_future_8_d2' => $results['order_future_8_d2'],
                'HJ - order_future_9_d2' => $results['order_future_9_d2'],
                'HK - order_future_10_d2' => $results['order_future_10_d2'],
                'HL - order_future_11_d2' => $results['order_future_11_d2'],
                'HM - order_future_12_d2' => $results['order_future_12_d2'],
                'HN - order_practitioner_def_a_d2' => $results['order_practitioner_def_a_d2'],
                'HO - order_practitioner_def_b_d2' => $results['order_practitioner_def_b_d2'],
                'HP - order_other_notes_d2' => $results['order_other_notes_d2'],
                'HQ - order_elog_d2' => $results['order_elog_d2'],
                'HR - order_tracking_d2' => $results['order_tracking_d2'],
                'HS - order_ship_date_d2' => $results['order_ship_date_d2'],
                'HT - order_ship_comp_d2' => $results['order_ship_comp_d2'],
                'HU - order_device_type_d3' => $results['order_device_type_d3'],
                'HV - order_top_cover_d3' => $results['order_top_cover_d3'],
                'HW - order_ppt_layer_d3' => $results['order_ppt_layer_d3'],
                'HX - order_top_cover_length_d3' => $results['order_top_cover_length_d3'],
                'HY - order_shell_thickness_d3' => $results['order_shell_thickness_d3'],
                'HZ - order_shell_grind_width_d3' => $results['order_shell_grind_width_d3'],
                'IA - order_shell_arch_fill_d3' => $results['order_shell_arch_fill_d3'],
                'IB - order_heel_cup_in_shell_d3' => $results['order_heel_cup_in_shell_d3'],
                'IC - order_forefoot_posting_d3_fpd3_additional' => $mx7['col_id_44'],
                'ID - order_forefoot_posting_d3_fpd3_left' => $mx7['col_id_45'],
                'IE - order_forefoot_posting_d3_fpd3_right' => $mx7['col_id_46'],
                'IF - order_rearfoot_posting_d3_rpd3_additional' => $mx8['col_id_48'],
                'IG - order_rearfoot_posting_d3_rpd3_left' => $mx8['col_id_49'],
                'IH - order_rearfoot_posting_d3_rpd3_right' => $mx8['col_id_50'],
                'II - order_motion_on_vp_d3_mvpd3_left' => $mx9['col_id_50'],
                'IJ - order_motion_on_vp_d3_mvpd3_right' => $mx9['col_id_51'],
                'IK - order_met_pad_bar_d3' => $results['order_met_pad_bar_d3'],
                'IL - order_met_unloads_in_ppt_d3_1' => $results['order_met_unloads_in_ppt_d3_1'],
                'IM - order_met_unloads_in_ppt_d3_2' => $results['order_met_unloads_in_ppt_d3_2'],
                'IN - order_met_unloads_in_ppt_d3_3' => $results['order_met_unloads_in_ppt_d3_3'],
                'IO - order_met_unloads_in_ppt_d3_4' => $results['order_met_unloads_in_ppt_d3_4'],
                'IP - order_met_unloads_in_ppt_d3_5' => $results['order_met_unloads_in_ppt_d3_5'],
                'IQ - order_heel_horseshoe_unload_d3' => $results['order_heel_horseshoe_unload_d3'],
                'IR - order_navicular_unload_d3' => $results['order_navicular_unload_d3'],
                'IS - order_base_of_fifth_unload_d3' => $results['order_base_of_fifth_unload_d3'],
                'IT - order_plantar_fascial_groove_d3' => $results['order_plantar_fascial_groove_d3'],
                'IU - order_cuboid_unload_d3' => $results['order_cuboid_unload_d3'],
                'IV - order_future_unload_d3' => $results['order_future_unload_d3'],
                'IW - order_mortons_extension_d3' => $results['order_mortons_extension_d3'],
                'IX - order_reverse_mortons_d3' => $results['order_reverse_mortons_d3'],
                'IY - order_kw_unload_in_crepe_d3' => $results['order_kw_unload_in_crepe_d3'],
                'IZ - order_kw_unload_in_ppt_d3' => $results['order_kw_unload_in_ppt_d3'],
                'JA - order_medial_flange_d3' => $results['order_medial_flange_d3'],
                'JB - order_lateral_flange_d3' => $results['order_lateral_flange_d3'],
                'JC - order_kirby_skive_2mm_d3' => $results['order_kirby_skive_2mm_d3'],
                'JD - order_kirby_skive_4mm_d3' => $results['order_kirby_skive_4mm_d3'],
                'JE - order_1st_met_cut_out_shell_d3' => $results['order_1st_met_cut_out_shell_d3'],
                'JF - order_heel_lift_amt_in_notes_d3' => $results['order_heel_lift_amt_in_notes_d3'],
                'JG - order_future_accommodation_1_d3' => $results['order_future_accommodation_1_d3'],
                'JH - order_future_accommodation_2_d3' => $results['order_future_accommodation_2_d3'],
                'JI - order_transmet_filler_d3' => $results['order_transmet_filler_d3'],
                'JJ - order_hallux_filler_d3' => $results['order_hallux_filler_d3'],
                'JK - order_lesser_toe_fillers_d3' => $results['order_lesser_toe_fillers_d3'],
                'JL - order_whole_foot_wrymark_d3' => $results['order_whole_foot_wrymark_d3'],
                'JM - order_gait_plate_toe_out_d3' => $results['order_gait_plate_toe_out_d3'],
                'JN - order_gait_plate_toe_in_d3' => $results['order_gait_plate_toe_in_d3'],
                'JO - order_add_ppt_schaphoid_pad_d3' => $results['order_add_ppt_schaphoid_pad_d3'],
                'JP - order_central_heel_reliefs_d3' => $results['order_central_heel_reliefs_d3'],
                'JQ - order_met_ridge_6mm_ppt_d3' => $results['order_met_ridge_6mm_ppt_d3'],
                'JR - order_fill_under_arch_ppt_d3' => $results['order_fill_under_arch_ppt_d3'],
                'JS - order_heel_pad_3mm_ppt_d3' => $results['order_heel_pad_3mm_ppt_d3'],
                'JT - order_heel_pad_1_5mm_ppt_d3' => $results['order_heel_pad_1_5mm_ppt_d3'],
                'JU - order_denton_extend_lateral_d3' => $results['order_denton_extend_lateral_d3'],
                'JV - order_zero_deg_forefoot_post_d3' => $results['order_zero_deg_forefoot_post_d3'],
                'JW - order_runners_wedge_medial_d3' => $results['order_runners_wedge_medial_d3'],
                'JX - order_runners_wedge_lateral_d3' => $results['order_runners_wedge_lateral_d3'],
                'JY - order_future_4_d3' => $results['order_future_4_d3'],
                'JZ - order_future_5_d3' => $results['order_future_5_d3'],
                'KA - order_future_6_d3' => $results['order_future_6_d3'],
                'KB - order_future_7_d3' => $results['order_future_7_d3'],
                'KC - order_future_8_d3' => $results['order_future_8_d3'],
                'KD - order_future_9_d3' => $results['order_future_9_d3'],
                'KE - order_future_10_d3' => $results['order_future_10_d3'],
                'KF - order_future_11_d3' => $results['order_future_11_d3'],
                'KG - order_future_12_d3' => $results['order_future_12_d3'],
                'KH - order_practitioner_def_a_d3' => $results['order_practitioner_def_a_d3'],
                'KI - order_practitioner_def_b_d3' => $results['order_practitioner_def_b_d3'],
                'KJ - order_other_notes_d3' => $results['order_other_notes_d3'],
                'KK - order_elog_d3' => $results['order_elog_d3'],
                'KL - order_tracking_d3' => $results['order_tracking_d3'],
                'KM - order_ship_date_d3' => $results['order_ship_date_d3'],
                'KN - order_ship_comp_d3' => $results['order_ship_comp_d3'],
                'KO - order_device_type_d4' => $results['order_device_type_d4'],
                'KP - order_top_cover_d4' => $results['order_top_cover_d4'],
                'KQ - order_ppt_layer_d4' => $results['order_ppt_layer_d4'],
                'KR - order_top_cover_length_d4' => $results['order_top_cover_length_d4'],
                'KS - order_shell_thickness_d4' => $results['order_shell_thickness_d4'],
                'KT - order_shell_grind_width_d4' => $results['order_shell_grind_width_d4'],
                'KU - order_shell_arch_fill_d4' => $results['order_shell_arch_fill_d4'],
                'KV - order_heel_cup_in_shell_d4' => $results['order_heel_cup_in_shell_d4'],
                'KW - order_forefoot_posting_d4_fpd4_additional' => $mx10['col_id_64'],
                'KX - order_forefoot_posting_d4_fpd4_left' => $mx10['col_id_65'],
                'KY - order_forefoot_posting_d4_fpd4_right' => $mx10['col_id_66'],
                'KZ - order_rearfoot_posting_d4_rpd4_additional' => $mx11['col_id_68'],
                'LA - order_rearfoot_posting_d4_rpd4_left' => $mx11['col_id_69'],
                'LB - order_rearfoot_posting_d4_rpd4_right' => $mx11['col_id_70'],
                'LC - order_motion_on_vp_d4_mvpd4_left' => $mx12['col_id_70'],
                'LD - order_motion_on_vp_d4_mvpd4_right' => $mx12['col_id_71'],
                'LE - order_met_pad_bar_d4' => $results['order_met_pad_bar_d4'],
                'LF - order_met_unloads_in_ppt_d4_1' => $results['order_met_unloads_in_ppt_d4_1'],
                'LG - order_met_unloads_in_ppt_d4_2' => $results['order_met_unloads_in_ppt_d4_2'],
                'LH - order_met_unloads_in_ppt_d4_3' => $results['order_met_unloads_in_ppt_d4_3'],
                'LI - order_met_unloads_in_ppt_d4_4' => $results['order_met_unloads_in_ppt_d4_4'],
                'LJ - order_met_unloads_in_ppt_d4_5' => $results['order_met_unloads_in_ppt_d4_5'],
                'LK - order_heel_horseshoe_unload_d4' => $results['order_heel_horseshoe_unload_d4'],
                'LL - order_navicular_unload_d4' => $results['order_navicular_unload_d4'],
                'LM - order_base_of_fifth_unload_d4' => $results['order_base_of_fifth_unload_d4'],
                'LN - order_plantar_fascial_groove_d4' => $results['order_plantar_fascial_groove_d4'],
                'LO - order_cuboid_unload_d4' => $results['order_cuboid_unload_d4'],
                'LP - order_future_unload_1_d4' => $results['order_future_unload_1_d4'],
                'LQ - order_mortons_extension_d4' => $results['order_mortons_extension_d4'],
                'LR - order_reverse_mortons_d4' => $results['order_reverse_mortons_d4'],
                'LS - order_kw_unload_in_crepe_d4' => $results['order_kw_unload_in_crepe_d4'],
                'LT - order_kw_unload_in_ppt_d4' => $results['order_kw_unload_in_ppt_d4'],
                'LU - order_medial_flange_d4' => $results['order_medial_flange_d4'],
                'LV - order_lateral_flange_d4' => $results['order_lateral_flange_d4'],
                'LW - order_kirby_skive_2mm_d4' => $results['order_kirby_skive_2mm_d4'],
                'LX - order_kirby_skive_4mm_d4' => $results['order_kirby_skive_4mm_d4'],
                'LY - order_1st_met_cut_out_shell_d4' => $results['order_1st_met_cut_out_shell_d4'],
                'LZ - order_heel_lift_amt_in_notes_d4' => $results['order_heel_lift_amt_in_notes_d4'],
                'MA - order_future_accommodation_1_d4' => $results['order_future_accommodation_1_d4'],
                'MB - order_future_accommodation_2_d4' => $results['order_future_accommodation_2_d4'],
                'MC - order_transmet_filler_d4' => $results['order_transmet_filler_d4'],
                'MD - order_hallux_filler_d4' => $results['order_hallux_filler_d4'],
                'ME - order_lesser_toe_fillers_d4' => $results['order_lesser_toe_fillers_d4'],
                'MF - order_whole_foot_wrymark_d4' => $results['order_whole_foot_wrymark_d4'],
                'MG - order_gait_plate_toe_out_d4' => $results['order_gait_plate_toe_out_d4'],
                'MH - order_gait_plate_toe_in_d4' => $results['order_gait_plate_toe_in_d4'],
                'MI - order_add_ppt_schaphoid_pad_d4' => $results['order_add_ppt_schaphoid_pad_d4'],
                'MJ - order_central_heel_reliefs_d4' => $results['order_central_heel_reliefs_d4'],
                'MK - order_met_ridge_6mm_ppt_d4' => $results['order_met_ridge_6mm_ppt_d4'],
                'ML - order_fill_under_arch_ppt_d4' => $results['order_fill_under_arch_ppt_d4'],
                'MM - order_heel_pad_3mm_ppt_d4' => $results['order_heel_pad_3mm_ppt_d4'],
                'MN - order_heel_pad_1_5mm_ppt_d4' => $results['order_heel_pad_1_5mm_ppt_d4'],
                'MO - order_denton_extend_lateral_d4' => $results['order_denton_extend_lateral_d4'],
                'MP - order_zero_deg_forefoot_post_d4' => $results['order_zero_deg_forefoot_post_d4'],
                'MQ - order_runners_wedge_medial_d4' => $results['order_runners_wedge_medial_d4'],
                'MR - order_runners_wedge_lateral_d4' => $results['order_runners_wedge_lateral_d4'],
                'MS - order_future_4_d4' => $results['order_future_4_d4'],
                'MT - order_future_5_d4' => $results['order_future_5_d4'],
                'MU - order_future_6_d4' => $results['order_future_6_d4'],
                'MV - order_future_7_d4' => $results['order_future_7_d4'],
                'MW - order_future_8_d4' => $results['order_future_8_d4'],
                'MX - order_future_9_d4' => $results['order_future_9_d4'],
                'MY - order_future_10_d4' => $results['order_future_10_d4'],
                'MZ - order_future_11_d4' => $results['order_future_11_d4'],
                'NA - order_future_12_d4' => $results['order_future_12_d4'],
                'NB - order_practitioner_defined_a_d4' => $results['order_practitioner_defined_a_d4'],
                'NC - order_practitioner_defined_b_d4' => $results['order_practitioner_defined_b_d4'],
                'ND - order_other_notes_d4' => $results['order_other_notes_d4'],
                'NE - order_elog_d4' => $results['order_elog_d4'],
                'NF - order_tracking_d4' => $results['order_tracking_d4'],
                'NG - order_ship_date_d4' => $results['order_ship_date_d4'],
                'NH - order_ship_comp_d4' => $results['order_ship_comp_d4'],
                'NI - order_confirm_problem_for_adjust' => $results['order_confirm_problem_for_adjust'],
                'NJ - order_confirm_type_of_work' => $results['order_confirm_type_of_work'],
                'NK - order_internal_doctor_notes' => $results['order_internal_doctor_notes'],
                'NL - order_previous_elog' => $results['order_previous_elog'],
                'NM - order_mirror_plaster' => $results['order_mirror_plaster'],
                'NN - order_miscellaneous_charges' => $results['order_miscellaneous_charges'],
                'NO - order_discount_rush' => $results['order_discount_rush'],
                'NP - order_discount_device' => $results['order_discount_device'],
                'NQ - order_device_must_ship_by' => $results['order_device_must_ship_by'],
                'NR - order_markings_notes' => $results['order_markings_notes'],
                'NS - order_save_ship_back_casts' => $results['order_save_ship_back_casts'],
                'NT - order_scanning_notes' => $results['order_scanning_notes'],
                'NU - order_correcting_notes' => $results['order_correcting_notes'],
                'NV - order_pulling_notes' => $results['order_pulling_notes'],
                'NW - order_finishing_notes_1' => $results['order_finishing_notes_1'],
                'NX - order_finishing_notes_2' => $results['order_finishing_notes_2'],
                'NY - order_other_shipping_notes' => $results['order_other_shipping_notes'],
                'NZ - order_billing_notes' => $results['order_billing_notes'],
                'OA - order_elog' => $results['order_elog'],
                'OB - order_date_accepted' => $results['order_date_accepted'],
                'OC - order_date_submitted' => $results['order_date_submitted'],
                'OD - order_date_shipped' => $results['order_date_shipped'],
                'OE - order_practice_select' => $results['order_practice_select'],
                'OF - order_doctor_select' => $results['order_doctor_select'],
                'OG - order_assistant_select' => $results['order_assistant_select']
            ];
            
            // Add the data to a new row in the spreadsheet
            $this->writer->addRow($new_row);
            $this->total_row_count++;
        }
    }




    /**
    * saveTheSettings - Saves the settings of the export to the settings file
    *
    * @return spreadsheet
    */

    public function saveTheSettings($rows)
    {
        $settings = array(
            'report name' => $this->order_status . ' Orders',
            'status' => 'completed',
            'date' => $this->the_date,
            'rows exported' => $rows
        );

        $this->log->saveToJSON($settings, strtolower($this->order_status));
    }

}
