<?
$sub_menu = "400100";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

// 로그인을 바로 이 주소로 하는 경우 쇼핑몰설정값이 사라지는 현상을 방지
if (!$de_admin_company_owner) goto_url("./configform.php");

if ($logo_img_del)  @unlink("$g4[path]/data/common/logo_img");
if ($main_img_del)  @unlink("$g4[path]/data/common/main_img");

if ($_FILES[logo_img][name]) upload_file($_FILES[logo_img][tmp_name], "logo_img", "$g4[path]/data/common");
if ($_FILES[main_img][name]) upload_file($_FILES[main_img][tmp_name], "main_img", "$g4[path]/data/common");

$de_kcp_mid = substr($_POST['de_kcp_mid'],0,3);

//
// 영카트 default
//
$sql = " update $g4[yc4_default_table]
            set de_admin_company_owner      = '$de_admin_company_owner',
                de_admin_company_name       = '$de_admin_company_name',
                de_admin_company_saupja_no  = '$de_admin_company_saupja_no',
                de_admin_company_tel        = '$de_admin_company_tel',
                de_admin_company_fax        = '$de_admin_company_fax',
                de_admin_tongsin_no         = '$de_admin_tongsin_no',
                de_admin_company_zip        = '$de_admin_company_zip',
                de_admin_company_addr       = '$de_admin_company_addr',
                de_admin_info_name          = '$de_admin_info_name',
                de_admin_info_email         = '$de_admin_info_email',
                de_type1_list_use           = '$de_type1_list_use',
                de_type1_list_skin          = '$de_type1_list_skin',
                de_type1_list_mod           = '$de_type1_list_mod',
                de_type1_list_row           = '$de_type1_list_row',
                de_type1_img_width          = '$de_type1_img_width',
                de_type1_img_height         = '$de_type1_img_height',
                de_type2_list_use           = '$de_type2_list_use',
                de_type2_list_skin          = '$de_type2_list_skin',
                de_type2_list_mod           = '$de_type2_list_mod',
                de_type2_list_row           = '$de_type2_list_row',
                de_type2_img_width          = '$de_type2_img_width',
                de_type2_img_height         = '$de_type2_img_height',
                de_type3_list_use           = '$de_type3_list_use',
                de_type3_list_skin          = '$de_type3_list_skin',
                de_type3_list_mod           = '$de_type3_list_mod',
                de_type3_list_row           = '$de_type3_list_row',
                de_type3_img_width          = '$de_type3_img_width',
                de_type3_img_height         = '$de_type3_img_height',
                de_type4_list_use           = '$de_type4_list_use',
                de_type4_list_skin          = '$de_type4_list_skin',
                de_type4_list_mod           = '$de_type4_list_mod',
                de_type4_list_row           = '$de_type4_list_row',
                de_type4_img_width          = '$de_type4_img_width',
                de_type4_img_height         = '$de_type4_img_height',
                de_type5_list_use           = '$de_type5_list_use',
                de_type5_list_skin          = '$de_type5_list_skin',
                de_type5_list_mod           = '$de_type5_list_mod',
                de_type5_list_row           = '$de_type5_list_row',
                de_type5_img_width          = '$de_type5_img_width',
                de_type5_img_height         = '$de_type5_img_height',
                de_rel_list_mod             = '$de_rel_list_mod',
                de_rel_img_width            = '$de_rel_img_width',
                de_rel_img_height           = '$de_rel_img_height',
                de_bank_use                 = '$de_bank_use',
                de_bank_account             = '$de_bank_account',
                de_card_test                = '$de_card_test',
                de_card_use                 = '$de_card_use',
                de_card_point               = '$de_card_point',
                de_card_pg                  = '$de_card_pg',
                de_card_max_amount          = '$de_card_max_amount',
                de_banktown_mid             = '$de_banktown_mid',
                de_banktown_auth_key        = '$de_banktown_auth_key',
                de_telec_mid                = '$de_telec_mid',
                de_point_settle             = '$de_point_settle',
                de_level_sell               = '$de_level_sell',
                de_send_cost_case           = '$de_send_cost_case',
                de_send_cost_limit          = '$de_send_cost_limit',
                de_send_cost_list           = '$de_send_cost_list',
                de_hope_date_use            = '$de_hope_date_use',
                de_hope_date_after          = '$de_hope_date_after',
                de_baesong_content          = '$de_baesong_content',
                de_change_content           = '$de_change_content',
                de_level_sell               = '$de_level_sell',
                de_point_days               = '$de_point_days',
                de_simg_width               = '$de_simg_width',
                de_simg_height              = '$de_simg_height',
                de_mimg_width               = '$de_mimg_width',
                de_mimg_height              = '$de_mimg_height',
                de_scroll_banner_use        = '$de_scroll_banner_use',
                de_cart_skin                = '$de_cart_skin',
                de_register                 = '$de_register',
                de_inicis_mid               = '$de_inicis_mid',
                de_inicis_passwd            = '$de_inicis_passwd',
                de_dacom_mid                = '$de_dacom_mid',
                de_dacom_test               = '$de_dacom_test',
                de_dacom_mertkey            = '$de_dacom_mertkey',
                de_allthegate_mid           = '$de_allthegate_mid',
                de_kcp_mid                  = '$de_kcp_mid',
                de_iche_use                 = '$de_iche_use',
                de_allat_partner_id         = '$de_allat_partner_id',
                de_allat_prefix             = '$de_allat_prefix',
                de_allat_formkey            = '$de_allat_formkey',
                de_allat_crosskey           = '$de_allat_crosskey',
                de_tgcorp_mxid              = '$de_tgcorp_mxid',
                de_tgcorp_mxotp             = '$de_tgcorp_mxotp',
                de_kspay_id                 = '$de_kspay_id',
                de_sms_cont1                = '$de_sms_cont1',
                de_sms_cont2                = '$de_sms_cont2',
                de_sms_cont3                = '$de_sms_cont3',
                de_sms_cont4                = '$de_sms_cont4',
                de_sms_use1                 = '$de_sms_use1',
                de_sms_use2                 = '$de_sms_use2',
                de_sms_use3                 = '$de_sms_use3',
                de_sms_use4                 = '$de_sms_use4',
                de_xonda_id                 = '$de_xonda_id',
                de_sms_hp                   = '$de_sms_hp',
                de_item_ps_use              = '$de_item_ps_use',
                de_code_dup_use             = '$de_code_dup_use',
                de_point_per                = '$de_point_per',
                de_admin_buga_no            = '$de_admin_buga_no',
                de_different_msg            = '$de_different_msg',
				de_sms_use                  = '$de_sms_use',
				de_icode_id                 = '$de_icode_id',
				de_icode_pw                 = '$de_icode_pw',
				de_icode_server_ip          = '$de_icode_server_ip',
				de_icode_server_port        = '$de_icode_server_port',
                de_vbank_use                = '$de_vbank_use',
                de_kcp_site_key             = '$de_kcp_site_key',
                de_taxsave_use              = '$de_taxsave_use',
                de_guest_privacy            = '$de_guest_privacy',
                de_hp_use                   = '$de_hp_use',
                de_escrow_use               = '$de_escrow_use'
                ";
sql_query($sql);

// 환경설정 > 포인트 사용
sql_query(" update $g4[config_table] set cf_use_point = '$cf_use_point' ");

goto_url("./configform.php");
?>
