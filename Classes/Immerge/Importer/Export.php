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
 * Export - Exporter For Richey Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Export
{

    private static $model;
    private static $sql_data;
    private static $spreadsheet;
    private static $header;

    public function __construct()
    {
    }




    /**
    * main - The main controller for the exporter. 
    *
    * @return nothing
    */

    public function main()
    {
        $this->buildTheSpreadSheet();
        $this->downloadTheSpreadsheet(static ::$spreadsheet);
        //$this->buildEachRow();
    }




    /**
    * downloadTheSpreadsheet - Downloads the spreadsheet
    *
    * @return nothing
    */

    public function downloadTheSpreadsheet($sheet)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export.xlsx"');
        $writer->save("php://output");
        
    }




    /**
    * buildTheSpreadsheet - Builds the spreadsheet
    *
    * @return spreadsheet
    */

    public function buildTheSpreadSheet()
    {
        static ::$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        static ::$header = $this->createHeaderRow();
        static ::$spreadsheet->getActiveSheet()->fromArray(static ::$header);
        $this->buildEachRow();
        //static ::$spreadsheet->getActiveSheet()->fromArray($this->buildEachRow(), NULL, 'A2');
    }




    /**
    * buildEachRow - Builds each row of the spreadsheet
    *
    * @return array
    */

    public function buildEachRow()
    {
        static ::$model = Models::getInstance();
        $orders = static ::$model->getAllOpenOrders();

        // Start on the second row of the spreadsheet
        $i = 2;

        foreach ($orders as $order_id)
        {
            // Get all of the channel data
            $results = static ::$model->getAllOrderDetails($order_id);
            $results = array_shift($results);

            $new_row = [
                'entry_id' => $order_id,
                'order_date_scanned' => $results['order_date_scanned']
            ];
            
            static ::$spreadsheet->getActiveSheet()->fromArray($new_row, NULL, 'A' . $i);
            $i++;
        }
    }




    /**
    * createHeaderRow - Creates the header row for the spreadsheet
    *
    * @return array
    */

    public function createHeaderRow()
    {
        return [
            'A' => 'title',
            'B' => 'url_title',
            'C' => 'status',
            'D' => 'entry_date',
            'E' => 'entry_id',
            'F' => 'site_id',
            'G' => 'channel_id',
            'H' => 'order_date_scanned',
            'I' => 'order_date_of_pt',
            'J' => 'order_date_shipped',
            'K' => 'order_recover_refurb_ship_date',
            'L' => 'order_adjustment_ship_date',
            'M' => 'order_ship_date_d1',
            'N' => 'order_ship_date_d2',
            'O' => 'order_ship_date_d3',
            'P' => 'order_ship_date_d4',
            'Q' => 'order_date_submitted',
            'R' => 'author_id',
            'S' => 'author_data_username',
            'T' => 'author_data_member_id',
            'U' => 'author_data_screen_name',
            'V' => 'author_data_email',
            'W' => 'author_data_join_date',
            'X' => 'author_data_last_visit',
            'Y' => 'author_data_group_id',
            'Z' => 'author_data_in_authorlist',
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
            mx1.col_id_4 AS order_forefoot_posting_d1_fpd1_additional,
            mx1.col_id_5 AS order_forefoot_posting_d1_fpd1_left,
            mx1.col_id_6 AS order_forefoot_posting_d1_fpd1_right,
            mx2.col_id_7 AS order_rearfoot_posting_d1_rpd1_additional,
            mx2.col_id_8 AS order_rearfoot_posting_d1_rpd1_left,
            mx2.col_id_9 AS order_rearfoot_posting_d1_rpd1_right,
            mx3.col_id_10 AS order_motion_on_vp_d1_mvpd1_left,
            mx3.col_id_11 AS order_motion_on_vp_d1_mvpd1_right,
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
            mx4.col_id_24 AS order_forefoot_posting_d2_fpd2_additional,
            mx4.col_id_25 AS order_forefoot_posting_d2_fpd2_left,
            mx4.col_id_26 AS order_forefoot_posting_d2_fpd2_right,
            mx5.col_id_27 AS order_rearfoot_posting_d2_rpd2_additional,
            mx5.col_id_28 AS order_rearfoot_posting_d2_rpd2_left,
            mx5.col_id_29 AS order_rearfoot_posting_d2_rpd2_right,
            mx6.col_id_30 AS order_motion_on_vp_d2_mvpd2_left,
            mx6.col_id_31 AS order_motion_on_vp_d2_mvpd2_right,
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
            mx7.col_id_44 AS order_forefoot_posting_d3_fpd3_additional,
            mx7.col_id_45 AS order_forefoot_posting_d3_fpd3_left,
            mx7.col_id_46 AS order_forefoot_posting_d3_fpd3_right,
            mx8.col_id_47 AS order_rearfoot_posting_d3_rpd3_additional,
            mx8.col_id_48 AS order_rearfoot_posting_d3_rpd3_left,
            mx8.col_id_49 AS order_rearfoot_posting_d3_rpd3_right,
            mx9.col_id_50 AS order_motion_on_vp_d3_mvpd3_left,
            mx9.col_id_51 AS order_motion_on_vp_d3_mvpd3_right,
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
            mx10.col_id_64 AS order_forefoot_posting_d4_fpd4_additional,
            mx10.col_id_65 AS order_forefoot_posting_d4_fpd4_left,
            mx10.col_id_66 AS order_forefoot_posting_d4_fpd4_right,
            mx11.col_id_67 AS order_rearfoot_posting_d4_rpd4_additional,
            mx11.col_id_68 AS order_rearfoot_posting_d4_rpd4_left,
            mx11.col_id_69 AS order_rearfoot_posting_d4_rpd4_right,
            mx12.col_id_70 AS order_motion_on_vp_d4_mvpd4_left,
            mx12.col_id_71 AS order_motion_on_vp_d4_mvpd4_right,
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
            cd.field_id_411 AS order_assistant_select
        ];
    }

}
