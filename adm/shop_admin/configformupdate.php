<?php
$sub_menu = '400100';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

// 로그인을 바로 이 주소로 하는 경우 쇼핑몰설정값이 사라지는 현상을 방지
if (!$de_admin_company_owner) goto_url("./configform.php");

if ($logo_img_del)  @unlink(G5_DATA_PATH."/common/logo_img");
if ($logo_img_del2)  @unlink(G5_DATA_PATH."/common/logo_img2");
if ($mobile_logo_img_del)  @unlink(G5_DATA_PATH."/common/mobile_logo_img");
if ($mobile_logo_img_del2)  @unlink(G5_DATA_PATH."/common/mobile_logo_img2");

if ($_FILES['logo_img']['name']) upload_file($_FILES['logo_img']['tmp_name'], "logo_img", G5_DATA_PATH."/common");
if ($_FILES['logo_img2']['name']) upload_file($_FILES['logo_img2']['tmp_name'], "logo_img2", G5_DATA_PATH."/common");
if ($_FILES['mobile_logo_img']['name']) upload_file($_FILES['mobile_logo_img']['tmp_name'], "mobile_logo_img", G5_DATA_PATH."/common");
if ($_FILES['mobile_logo_img2']['name']) upload_file($_FILES['mobile_logo_img2']['tmp_name'], "mobile_logo_img2", G5_DATA_PATH."/common");

$de_kcp_mid = substr($_POST['de_kcp_mid'],0,3);

// kcp 전자결제를 사용할 때 site key 입력체크
if($de_pg_service == 'kcp' && ($de_iche_use || $de_vbank_use || $de_hp_use || $de_card_use)) {
    if(trim($de_kcp_site_key) == '')
        alert('KCP SITE KEY를 입력해 주십시오.');
}

//
// 영카트 default
//
$sql = " update {$g5['g5_shop_default_table']}
            set de_admin_company_owner        = '$de_admin_company_owner',
                de_admin_company_name         = '$de_admin_company_name',
                de_admin_company_saupja_no    = '$de_admin_company_saupja_no',
                de_admin_company_tel          = '$de_admin_company_tel',
                de_admin_company_fax          = '$de_admin_company_fax',
                de_admin_tongsin_no           = '$de_admin_tongsin_no',
                de_admin_company_zip          = '$de_admin_company_zip',
                de_admin_company_addr         = '$de_admin_company_addr',
                de_admin_info_name            = '$de_admin_info_name',
                de_admin_info_email           = '$de_admin_info_email',
                de_include_index              = '$de_include_index',
                de_include_head               = '$de_include_head',
                de_include_tail               = '$de_include_tail',
                de_root_index_use             = '$de_root_index_use',
                de_shop_layout_use            = '$de_shop_layout_use',
                de_shop_skin                  = '$de_shop_skin',
                de_shop_mobile_skin           = '$de_shop_mobile_skin',
                de_type1_list_use             = '$de_type1_list_use',
                de_type1_list_skin            = '$de_type1_list_skin',
                de_type1_list_mod             = '$de_type1_list_mod',
                de_type1_list_row             = '$de_type1_list_row',
                de_type1_img_width            = '$de_type1_img_width',
                de_type1_img_height           = '$de_type1_img_height',
                de_type2_list_use             = '$de_type2_list_use',
                de_type2_list_skin            = '$de_type2_list_skin',
                de_type2_list_mod             = '$de_type2_list_mod',
                de_type2_list_row             = '$de_type2_list_row',
                de_type2_img_width            = '$de_type2_img_width',
                de_type2_img_height           = '$de_type2_img_height',
                de_type3_list_use             = '$de_type3_list_use',
                de_type3_list_skin            = '$de_type3_list_skin',
                de_type3_list_mod             = '$de_type3_list_mod',
                de_type3_list_row             = '$de_type3_list_row',
                de_type3_img_width            = '$de_type3_img_width',
                de_type3_img_height           = '$de_type3_img_height',
                de_type4_list_use             = '$de_type4_list_use',
                de_type4_list_skin            = '$de_type4_list_skin',
                de_type4_list_mod             = '$de_type4_list_mod',
                de_type4_list_row             = '$de_type4_list_row',
                de_type4_img_width            = '$de_type4_img_width',
                de_type4_img_height           = '$de_type4_img_height',
                de_type5_list_use             = '$de_type5_list_use',
                de_type5_list_skin            = '$de_type5_list_skin',
                de_type5_list_mod             = '$de_type5_list_mod',
                de_type5_list_row             = '$de_type5_list_row',
                de_type5_img_width            = '$de_type5_img_width',
                de_type5_img_height           = '$de_type5_img_height',
                de_mobile_type1_list_use      = '$de_mobile_type1_list_use',
                de_mobile_type1_list_skin     = '$de_mobile_type1_list_skin',
                de_mobile_type1_list_mod      = '$de_mobile_type1_list_mod',
                de_mobile_type1_img_width     = '$de_mobile_type1_img_width',
                de_mobile_type1_img_height    = '$de_mobile_type1_img_height',
                de_mobile_type2_list_use      = '$de_mobile_type2_list_use',
                de_mobile_type2_list_skin     = '$de_mobile_type2_list_skin',
                de_mobile_type2_list_mod      = '$de_mobile_type2_list_mod',
                de_mobile_type2_img_width     = '$de_mobile_type2_img_width',
                de_mobile_type2_img_height    = '$de_mobile_type2_img_height',
                de_mobile_type3_list_use      = '$de_mobile_type3_list_use',
                de_mobile_type3_list_skin     = '$de_mobile_type3_list_skin',
                de_mobile_type3_list_mod      = '$de_mobile_type3_list_mod',
                de_mobile_type3_img_width     = '$de_mobile_type3_img_width',
                de_mobile_type3_img_height    = '$de_mobile_type3_img_height',
                de_mobile_type4_list_use      = '$de_mobile_type4_list_use',
                de_mobile_type4_list_skin     = '$de_mobile_type4_list_skin',
                de_mobile_type4_list_mod      = '$de_mobile_type4_list_mod',
                de_mobile_type4_img_width     = '$de_mobile_type4_img_width',
                de_mobile_type4_img_height    = '$de_mobile_type4_img_height',
                de_mobile_type5_list_use      = '$de_mobile_type5_list_use',
                de_mobile_type5_list_skin     = '$de_mobile_type5_list_skin',
                de_mobile_type5_list_mod      = '$de_mobile_type5_list_mod',
                de_mobile_type5_img_width     = '$de_mobile_type5_img_width',
                de_mobile_type5_img_height    = '$de_mobile_type5_img_height',
                de_rel_list_use               = '$de_rel_list_use',
                de_rel_list_skin              = '$de_rel_list_skin',
                de_rel_list_mod               = '$de_rel_list_mod',
                de_rel_img_width              = '$de_rel_img_width',
                de_rel_img_height             = '$de_rel_img_height',
                de_mobile_rel_list_use        = '$de_mobile_rel_list_use',
                de_mobile_rel_list_skin       = '$de_mobile_rel_list_skin',
                de_mobile_rel_img_width       = '$de_mobile_rel_img_width',
                de_mobile_rel_img_height      = '$de_mobile_rel_img_height',
                de_search_list_skin           = '$de_search_list_skin',
                de_search_list_mod            = '$de_search_list_mod',
                de_search_list_row            = '$de_search_list_row',
                de_search_img_width           = '$de_search_img_width',
                de_search_img_height          = '$de_search_img_height',
                de_mobile_search_list_skin    = '$de_mobile_search_list_skin',
                de_mobile_search_list_mod     = '$de_mobile_search_list_mod',
                de_mobile_search_img_width    = '$de_mobile_search_img_width',
                de_mobile_search_img_height   = '$de_mobile_search_img_height',
                de_bank_use                   = '$de_bank_use',
                de_bank_account               = '$de_bank_account',
                de_card_test                  = '$de_card_test',
                de_card_use                   = '$de_card_use',
                de_card_noint_use             = '$de_card_noint_use',
                de_card_point                 = '$de_card_point',
                de_settle_min_point           = '$de_settle_min_point',
                de_settle_max_point           = '$de_settle_max_point',
                de_settle_point_unit          = '$de_settle_point_unit',
                de_level_sell                 = '$de_level_sell',
                de_delivery_company           = '$de_delivery_company',
                de_send_cost_case             = '$de_send_cost_case',
                de_send_cost_limit            = '$de_send_cost_limit',
                de_send_cost_list             = '$de_send_cost_list',
                de_hope_date_use              = '$de_hope_date_use',
                de_hope_date_after            = '$de_hope_date_after',
                de_baesong_content            = '$de_baesong_content',
                de_change_content             = '$de_change_content',
                de_level_sell                 = '$de_level_sell',
                de_point_days                 = '$de_point_days',
                de_simg_width                 = '$de_simg_width',
                de_simg_height                = '$de_simg_height',
                de_mimg_width                 = '$de_mimg_width',
                de_mimg_height                = '$de_mimg_height',
                de_pg_service                 = '$de_pg_service',
                de_kcp_mid                    = '$de_kcp_mid',
                de_kcp_site_key               = '$de_kcp_site_key',
                de_iche_use                   = '$de_iche_use',
                de_sms_cont1                  = '$de_sms_cont1',
                de_sms_cont2                  = '$de_sms_cont2',
                de_sms_cont3                  = '$de_sms_cont3',
                de_sms_cont4                  = '$de_sms_cont4',
                de_sms_cont5                  = '$de_sms_cont5',
                de_sms_use1                   = '$de_sms_use1',
                de_sms_use2                   = '$de_sms_use2',
                de_sms_use3                   = '$de_sms_use3',
                de_sms_use4                   = '$de_sms_use4',
                de_sms_use5                   = '$de_sms_use5',
                de_sms_hp                     = '$de_sms_hp',
                de_item_use_use               = '$de_item_use_use',
                de_item_use_write             = '$de_item_use_write',
                de_code_dup_use               = '$de_code_dup_use',
                de_cart_keep_term             = '$de_cart_keep_term',
                de_guest_cart_use             = '$de_guest_cart_use',
                de_admin_buga_no              = '$de_admin_buga_no',
                de_vbank_use                  = '$de_vbank_use',
                de_taxsave_use                = '$de_taxsave_use',
                de_guest_privacy              = '$de_guest_privacy',
                de_hp_use                     = '$de_hp_use',
                de_escrow_use                 = '$de_escrow_use',
                de_tax_flag_use               = '$de_tax_flag_use',
                de_member_reg_coupon_use      = '$de_member_reg_coupon_use',
                de_member_reg_coupon_term     = '$de_member_reg_coupon_term',
                de_member_reg_coupon_price    = '$de_member_reg_coupon_price',
                de_member_reg_coupon_minimum  = '$de_member_reg_coupon_minimum'
                ";
sql_query($sql);

// 환경설정 > 포인트 사용
sql_query(" update {$g5['config_table']} set cf_use_point = '$cf_use_point' ");

// LG, 아이코드 설정
$sql = " update {$g5['config_table']}
            set cf_sms_use              = '$cf_sms_use',
                cf_icode_id             = '$cf_icode_id',
                cf_icode_pw             = '$cf_icode_pw',
                cf_icode_server_ip      = '$cf_icode_server_ip',
                cf_icode_server_port    = '$cf_icode_server_port',
                cf_lg_mid               = '$cf_lg_mid',
                cf_lg_mert_key          = '$cf_lg_mert_key' ";
sql_query($sql);

goto_url("./configform.php");
?>
