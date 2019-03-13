<?php
$sub_menu = '400100';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

check_admin_token();

// 대표전화번호 유효성 체크
if(!check_vaild_callback($_POST['de_admin_company_tel']))
    alert('대표전화번호를 올바르게 입력해 주세요.');

// 로그인을 바로 이 주소로 하는 경우 쇼핑몰설정값이 사라지는 현상을 방지
if (!$_POST['de_admin_company_owner']) goto_url("./configform.php");

if ($_POST['logo_img_del'])  @unlink(G5_DATA_PATH."/common/logo_img");
if ($_POST['logo_img_del2'])  @unlink(G5_DATA_PATH."/common/logo_img2");
if ($_POST['mobile_logo_img_del'])  @unlink(G5_DATA_PATH."/common/mobile_logo_img");
if ($_POST['mobile_logo_img_del2'])  @unlink(G5_DATA_PATH."/common/mobile_logo_img2");

if ($_FILES['logo_img']['name']) upload_file($_FILES['logo_img']['tmp_name'], "logo_img", G5_DATA_PATH."/common");
if ($_FILES['logo_img2']['name']) upload_file($_FILES['logo_img2']['tmp_name'], "logo_img2", G5_DATA_PATH."/common");
if ($_FILES['mobile_logo_img']['name']) upload_file($_FILES['mobile_logo_img']['tmp_name'], "mobile_logo_img", G5_DATA_PATH."/common");
if ($_FILES['mobile_logo_img2']['name']) upload_file($_FILES['mobile_logo_img2']['tmp_name'], "mobile_logo_img2", G5_DATA_PATH."/common");

$de_kcp_mid = substr($_POST['de_kcp_mid'],0,3);

// kcp 전자결제를 사용할 때 site key 입력체크
if($_POST['de_pg_service'] == 'kcp' && !$_POST['de_card_test'] && ($_POST['de_iche_use'] || $_POST['de_vbank_use'] || $_POST['de_hp_use'] || $_POST['de_card_use'])) {
    if(trim($_POST['de_kcp_site_key']) == '')
        alert('NHN KCP SITE KEY를 입력해 주십시오.');
}

$de_shop_skin = isset($_POST['de_shop_skin']) ? preg_replace('#\.+(\/|\\\)#', '', $_POST['de_shop_skin']) : 'basic';
$de_shop_mobile_skin = isset($_POST['de_shop_mobile_skin']) ? preg_replace('#\.+(\/|\\\)#', '', $_POST['de_shop_mobile_skin']) : 'basic';

$skins = get_skin_dir('shop');

if(defined('G5_THEME_PATH') && $config['cf_theme']) {
    $dirs = get_skin_dir('shop', G5_THEME_PATH.'/'.G5_SKIN_DIR);
    if(!empty($dirs)) {
        foreach($dirs as $dir) {
            $skins[] = 'theme/'.$dir;
        }
    }
}

$mobile_skins = get_skin_dir('shop', G5_MOBILE_PATH.'/'.G5_SKIN_DIR);

if(defined('G5_THEME_PATH') && $config['cf_theme']) {
    $dirs = get_skin_dir('shop', G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR);
    if(!empty($dirs)) {
        foreach($dirs as $dir) {
            $mobile_skins[] = 'theme/'.$dir;
        }
    }
}

$de_shop_skin = in_array($de_shop_skin, $skins) ? $de_shop_skin : 'basic';
$de_shop_mobile_skin = in_array($de_shop_mobile_skin, $mobile_skins) ? $de_shop_mobile_skin : 'basic';

$check_skin_keys = array('de_type1_list_skin', 'de_type2_list_skin', 'de_type3_list_skin', 'de_type4_list_skin', 'de_type5_list_skin', 'de_mobile_type1_list_skin', 'de_mobile_type2_list_skin', 'de_mobile_type3_list_skin', 'de_mobile_type4_list_skin', 'de_mobile_type5_list_skin', 'de_rel_list_skin', 'de_mobile_rel_list_skin', 'de_search_list_skin', 'de_mobile_search_list_skin', 'de_listtype_list_skin', 'de_mobile_listtype_list_skin');

foreach($check_skin_keys as $key){
    $$key = $_POST[$key] = isset($_POST[$key]) ? preg_replace('#\.+(\/|\\\)#', '', $_POST[$key]) : '';
}

//
// 영카트 default
//
$sql = " update {$g5['g5_shop_default_table']}
            set de_admin_company_owner        = '{$_POST['de_admin_company_owner']}',
                de_admin_company_name         = '{$_POST['de_admin_company_name']}',
                de_admin_company_saupja_no    = '{$_POST['de_admin_company_saupja_no']}',
                de_admin_company_tel          = '{$_POST['de_admin_company_tel']}',
                de_admin_company_fax          = '{$_POST['de_admin_company_fax']}',
                de_admin_tongsin_no           = '{$_POST['de_admin_tongsin_no']}',
                de_admin_company_zip          = '{$_POST['de_admin_company_zip']}',
                de_admin_company_addr         = '{$_POST['de_admin_company_addr']}',
                de_admin_info_name            = '{$_POST['de_admin_info_name']}',
                de_admin_info_email           = '{$_POST['de_admin_info_email']}',
                de_shop_skin                  = '{$de_shop_skin}',
                de_shop_mobile_skin           = '{$de_shop_mobile_skin}',
                de_type1_list_use             = '{$_POST['de_type1_list_use']}',
                de_type1_list_skin            = '{$_POST['de_type1_list_skin']}',
                de_type1_list_mod             = '{$_POST['de_type1_list_mod']}',
                de_type1_list_row             = '{$_POST['de_type1_list_row']}',
                de_type1_img_width            = '{$_POST['de_type1_img_width']}',
                de_type1_img_height           = '{$_POST['de_type1_img_height']}',
                de_type2_list_use             = '{$_POST['de_type2_list_use']}',
                de_type2_list_skin            = '{$_POST['de_type2_list_skin']}',
                de_type2_list_mod             = '{$_POST['de_type2_list_mod']}',
                de_type2_list_row             = '{$_POST['de_type2_list_row']}',
                de_type2_img_width            = '{$_POST['de_type2_img_width']}',
                de_type2_img_height           = '{$_POST['de_type2_img_height']}',
                de_type3_list_use             = '{$_POST['de_type3_list_use']}',
                de_type3_list_skin            = '{$_POST['de_type3_list_skin']}',
                de_type3_list_mod             = '{$_POST['de_type3_list_mod']}',
                de_type3_list_row             = '{$_POST['de_type3_list_row']}',
                de_type3_img_width            = '{$_POST['de_type3_img_width']}',
                de_type3_img_height           = '{$_POST['de_type3_img_height']}',
                de_type4_list_use             = '{$_POST['de_type4_list_use']}',
                de_type4_list_skin            = '{$_POST['de_type4_list_skin']}',
                de_type4_list_mod             = '{$_POST['de_type4_list_mod']}',
                de_type4_list_row             = '{$_POST['de_type4_list_row']}',
                de_type4_img_width            = '{$_POST['de_type4_img_width']}',
                de_type4_img_height           = '{$_POST['de_type4_img_height']}',
                de_type5_list_use             = '{$_POST['de_type5_list_use']}',
                de_type5_list_skin            = '{$_POST['de_type5_list_skin']}',
                de_type5_list_mod             = '{$_POST['de_type5_list_mod']}',
                de_type5_list_row             = '{$_POST['de_type5_list_row']}',
                de_type5_img_width            = '{$_POST['de_type5_img_width']}',
                de_type5_img_height           = '{$_POST['de_type5_img_height']}',
                de_mobile_type1_list_use      = '{$_POST['de_mobile_type1_list_use']}',
                de_mobile_type1_list_skin     = '{$_POST['de_mobile_type1_list_skin']}',
                de_mobile_type1_list_mod      = '{$_POST['de_mobile_type1_list_mod']}',
                de_mobile_type1_list_row      = '{$_POST['de_mobile_type1_list_row']}',
                de_mobile_type1_img_width     = '{$_POST['de_mobile_type1_img_width']}',
                de_mobile_type1_img_height    = '{$_POST['de_mobile_type1_img_height']}',
                de_mobile_type2_list_use      = '{$_POST['de_mobile_type2_list_use']}',
                de_mobile_type2_list_skin     = '{$_POST['de_mobile_type2_list_skin']}',
                de_mobile_type2_list_mod      = '{$_POST['de_mobile_type2_list_mod']}',
                de_mobile_type2_list_row      = '{$_POST['de_mobile_type2_list_row']}',
                de_mobile_type2_img_width     = '{$_POST['de_mobile_type2_img_width']}',
                de_mobile_type2_img_height    = '{$_POST['de_mobile_type2_img_height']}',
                de_mobile_type3_list_use      = '{$_POST['de_mobile_type3_list_use']}',
                de_mobile_type3_list_skin     = '{$_POST['de_mobile_type3_list_skin']}',
                de_mobile_type3_list_mod      = '{$_POST['de_mobile_type3_list_mod']}',
                de_mobile_type3_list_row      = '{$_POST['de_mobile_type3_list_row']}',
                de_mobile_type3_img_width     = '{$_POST['de_mobile_type3_img_width']}',
                de_mobile_type3_img_height    = '{$_POST['de_mobile_type3_img_height']}',
                de_mobile_type4_list_use      = '{$_POST['de_mobile_type4_list_use']}',
                de_mobile_type4_list_skin     = '{$_POST['de_mobile_type4_list_skin']}',
                de_mobile_type4_list_mod      = '{$_POST['de_mobile_type4_list_mod']}',
                de_mobile_type4_list_row      = '{$_POST['de_mobile_type4_list_row']}',
                de_mobile_type4_img_width     = '{$_POST['de_mobile_type4_img_width']}',
                de_mobile_type4_img_height    = '{$_POST['de_mobile_type4_img_height']}',
                de_mobile_type5_list_use      = '{$_POST['de_mobile_type5_list_use']}',
                de_mobile_type5_list_skin     = '{$_POST['de_mobile_type5_list_skin']}',
                de_mobile_type5_list_mod      = '{$_POST['de_mobile_type5_list_mod']}',
                de_mobile_type5_list_row      = '{$_POST['de_mobile_type5_list_row']}',
                de_mobile_type5_img_width     = '{$_POST['de_mobile_type5_img_width']}',
                de_mobile_type5_img_height    = '{$_POST['de_mobile_type5_img_height']}',
                de_rel_list_use               = '{$_POST['de_rel_list_use']}',
                de_rel_list_skin              = '{$_POST['de_rel_list_skin']}',
                de_rel_list_mod               = '{$_POST['de_rel_list_mod']}',
                de_rel_img_width              = '{$_POST['de_rel_img_width']}',
                de_rel_img_height             = '{$_POST['de_rel_img_height']}',
                de_mobile_rel_list_use        = '{$_POST['de_mobile_rel_list_use']}',
                de_mobile_rel_list_skin       = '{$_POST['de_mobile_rel_list_skin']}',
                de_mobile_rel_list_mod        = '{$_POST['de_mobile_rel_list_mod']}',
                de_mobile_rel_img_width       = '{$_POST['de_mobile_rel_img_width']}',
                de_mobile_rel_img_height      = '{$_POST['de_mobile_rel_img_height']}',
                de_search_list_skin           = '{$_POST['de_search_list_skin']}',
                de_search_list_mod            = '{$_POST['de_search_list_mod']}',
                de_search_list_row            = '{$_POST['de_search_list_row']}',
                de_search_img_width           = '{$_POST['de_search_img_width']}',
                de_search_img_height          = '{$_POST['de_search_img_height']}',
                de_mobile_search_list_skin    = '{$_POST['de_mobile_search_list_skin']}',
                de_mobile_search_list_mod     = '{$_POST['de_mobile_search_list_mod']}',
                de_mobile_search_list_row     = '{$_POST['de_mobile_search_list_row']}',
                de_mobile_search_img_width    = '{$_POST['de_mobile_search_img_width']}',
                de_mobile_search_img_height   = '{$_POST['de_mobile_search_img_height']}',
                de_listtype_list_skin         = '{$_POST['de_listtype_list_skin']}',
                de_listtype_list_mod          = '{$_POST['de_listtype_list_mod']}',
                de_listtype_list_row          = '{$_POST['de_listtype_list_row']}',
                de_listtype_img_width         = '{$_POST['de_listtype_img_width']}',
                de_listtype_img_height        = '{$_POST['de_listtype_img_height']}',
                de_mobile_listtype_list_skin  = '{$_POST['de_mobile_listtype_list_skin']}',
                de_mobile_listtype_list_mod   = '{$_POST['de_mobile_listtype_list_mod']}',
                de_mobile_listtype_list_row   = '{$_POST['de_mobile_listtype_list_row']}',
                de_mobile_listtype_img_width  = '{$_POST['de_mobile_listtype_img_width']}',
                de_mobile_listtype_img_height = '{$_POST['de_mobile_listtype_img_height']}',
                de_bank_use                   = '{$_POST['de_bank_use']}',
                de_bank_account               = '{$_POST['de_bank_account']}',
                de_card_test                  = '{$_POST['de_card_test']}',
                de_card_use                   = '{$_POST['de_card_use']}',
                de_easy_pay_use               = '{$_POST['de_easy_pay_use']}',
                de_samsung_pay_use            = '{$_POST['de_samsung_pay_use']}',
                de_inicis_lpay_use            = '{$_POST['de_inicis_lpay_use']}',
                de_inicis_cartpoint_use       = '{$_POST['de_inicis_cartpoint_use']}',
                de_card_noint_use             = '{$_POST['de_card_noint_use']}',
                de_card_point                 = '{$_POST['de_card_point']}',
                de_settle_min_point           = '{$_POST['de_settle_min_point']}',
                de_settle_max_point           = '{$_POST['de_settle_max_point']}',
                de_settle_point_unit          = '{$_POST['de_settle_point_unit']}',
                de_level_sell                 = '{$_POST['de_level_sell']}',
                de_delivery_company           = '{$_POST['de_delivery_company']}',
                de_send_cost_case             = '{$_POST['de_send_cost_case']}',
                de_send_cost_limit            = '{$_POST['de_send_cost_limit']}',
                de_send_cost_list             = '{$_POST['de_send_cost_list']}',
                de_hope_date_use              = '{$_POST['de_hope_date_use']}',
                de_hope_date_after            = '{$_POST['de_hope_date_after']}',
                de_baesong_content            = '{$_POST['de_baesong_content']}',
                de_change_content             = '{$_POST['de_change_content']}',
                de_point_days                 = '{$_POST['de_point_days']}',
                de_simg_width                 = '{$_POST['de_simg_width']}',
                de_simg_height                = '{$_POST['de_simg_height']}',
                de_mimg_width                 = '{$_POST['de_mimg_width']}',
                de_mimg_height                = '{$_POST['de_mimg_height']}',
                de_pg_service                 = '{$_POST['de_pg_service']}',
                de_kcp_mid                    = '{$_POST['de_kcp_mid']}',
                de_kcp_site_key               = '{$_POST['de_kcp_site_key']}',
                de_inicis_mid                 = '{$_POST['de_inicis_mid']}',
                de_inicis_admin_key           = '{$_POST['de_inicis_admin_key']}',
                de_inicis_sign_key            = '{$_POST['de_inicis_sign_key']}',
                de_iche_use                   = '{$_POST['de_iche_use']}',
                de_sms_cont1                  = '{$_POST['de_sms_cont1']}',
                de_sms_cont2                  = '{$_POST['de_sms_cont2']}',
                de_sms_cont3                  = '{$_POST['de_sms_cont3']}',
                de_sms_cont4                  = '{$_POST['de_sms_cont4']}',
                de_sms_cont5                  = '{$_POST['de_sms_cont5']}',
                de_sms_use1                   = '{$_POST['de_sms_use1']}',
                de_sms_use2                   = '{$_POST['de_sms_use2']}',
                de_sms_use3                   = '{$_POST['de_sms_use3']}',
                de_sms_use4                   = '{$_POST['de_sms_use4']}',
                de_sms_use5                   = '{$_POST['de_sms_use5']}',
                de_sms_hp                     = '{$_POST['de_sms_hp']}',
                de_item_use_use               = '{$_POST['de_item_use_use']}',
                de_item_use_write             = '{$_POST['de_item_use_write']}',
                de_code_dup_use               = '{$_POST['de_code_dup_use']}',
                de_cart_keep_term             = '{$_POST['de_cart_keep_term']}',
                de_guest_cart_use             = '{$_POST['de_guest_cart_use']}',
                de_admin_buga_no              = '{$_POST['de_admin_buga_no']}',
                de_vbank_use                  = '{$_POST['de_vbank_use']}',
                de_taxsave_use                = '{$_POST['de_taxsave_use']}',
                de_guest_privacy              = '{$_POST['de_guest_privacy']}',
                de_hp_use                     = '{$_POST['de_hp_use']}',
                de_escrow_use                 = '{$_POST['de_escrow_use']}',
                de_tax_flag_use               = '{$_POST['de_tax_flag_use']}',
                de_kakaopay_mid               = '{$_POST['de_kakaopay_mid']}',
                de_kakaopay_key               = '{$_POST['de_kakaopay_key']}',
                de_kakaopay_enckey            = '{$_POST['de_kakaopay_enckey']}',
                de_kakaopay_hashkey           = '{$_POST['de_kakaopay_hashkey']}',
                de_kakaopay_cancelpwd         = '{$_POST['de_kakaopay_cancelpwd']}',
                de_naverpay_mid               = '{$_POST['de_naverpay_mid']}',
                de_naverpay_cert_key          = '{$_POST['de_naverpay_cert_key']}',
                de_naverpay_button_key        = '{$_POST['de_naverpay_button_key']}',
                de_naverpay_test              = '{$_POST['de_naverpay_test']}',
                de_naverpay_mb_id             = '{$_POST['de_naverpay_mb_id']}',
                de_naverpay_sendcost          = '{$_POST['de_naverpay_sendcost']}',
                de_member_reg_coupon_use      = '{$_POST['de_member_reg_coupon_use']}',
                de_member_reg_coupon_term     = '{$_POST['de_member_reg_coupon_term']}',
                de_member_reg_coupon_price    = '{$_POST['de_member_reg_coupon_price']}',
                de_member_reg_coupon_minimum  = '{$_POST['de_member_reg_coupon_minimum']}'
                ";
sql_query($sql);

// 환경설정 > 포인트 사용
sql_query(" update {$g5['config_table']} set cf_use_point = '{$_POST['cf_use_point']}' ");

// LG, 아이코드 설정
$sql = " update {$g5['config_table']}
            set cf_sms_use              = '{$_POST['cf_sms_use']}',
                cf_sms_type             = '{$_POST['cf_sms_type']}',
                cf_icode_id             = '{$_POST['cf_icode_id']}',
                cf_icode_pw             = '{$_POST['cf_icode_pw']}',
                cf_icode_server_ip      = '{$_POST['cf_icode_server_ip']}',
                cf_icode_server_port    = '{$_POST['cf_icode_server_port']}',
                cf_lg_mid               = '{$_POST['cf_lg_mid']}',
                cf_lg_mert_key          = '{$_POST['cf_lg_mert_key']}' ";
sql_query($sql);

goto_url("./configform.php");
?>
