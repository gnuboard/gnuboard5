<?php
$sub_menu = '400100';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "r");

if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

$userinfo = array('payment'=>'');
if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
    $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
}

$g5['title'] = '쇼핑몰설정';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_scf_info">사업자정보</a></li>
<li><a href="#anc_scf_skin">스킨설정</a></li>
<li><a href="#anc_scf_index">쇼핑몰 초기화면</a></li>
<li><a href="#anc_mscf_index">모바일 초기화면</a></li>
<li><a href="#anc_scf_payment">결제설정</a></li>
<li><a href="#anc_scf_delivery">배송설정</a></li>
<li><a href="#anc_scf_etc">기타설정</a></li>
<li><a href="#anc_scf_sms">SMS설정</a></li>
</ul>';

// 무이자 할부 사용설정 필드 추가
if(!isset($default['de_card_noint_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_card_noint_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_card_use` ", true);
}

// 모바일 관련상품 설정 필드추가
if(!isset($default['de_mobile_rel_list_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_mobile_rel_list_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_rel_img_height`,
                    ADD `de_mobile_rel_list_skin` varchar(255) NOT NULL DEFAULT '' AFTER `de_mobile_rel_list_use`,
                    ADD `de_mobile_rel_img_width` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_rel_list_skin`,
                    ADD `de_mobile_rel_img_height` int(11) NOT NULL DEFAULT ' 0' AFTER `de_mobile_rel_img_width`", true);
}

// 신규회원 쿠폰 설정 필드 추가
if(!isset($default['de_member_reg_coupon_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_member_reg_coupon_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_tax_flag_use`,
                    ADD `de_member_reg_coupon_term` int(11) NOT NULL DEFAULT '0' AFTER `de_member_reg_coupon_use`,
                    ADD `de_member_reg_coupon_price` int(11) NOT NULL DEFAULT '0' AFTER `de_member_reg_coupon_term` ", true);
}

// 신규회원 쿠폰 주문 최소금액 필드추가
if(!isset($default['de_member_reg_coupon_minimum'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_member_reg_coupon_minimum` int(11) NOT NULL DEFAULT '0' AFTER `de_member_reg_coupon_price` ", true);
}

// lg 결제관련 필드 추가
if(!isset($default['de_pg_service'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_pg_service` varchar(255) NOT NULL DEFAULT '' AFTER `de_sms_hp` ", true);
}


// inicis 필드 추가
if(!isset($default['de_inicis_mid'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_inicis_mid` varchar(255) NOT NULL DEFAULT '' AFTER `de_kcp_site_key` ", true);
}

// 모바일 초기화면 이미지 줄 수 필드 추가
if(!isset($default['de_mobile_type1_list_row'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_mobile_type1_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type1_list_mod`,
                    ADD `de_mobile_type2_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type2_list_mod`,
                    ADD `de_mobile_type3_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type3_list_mod`,
                    ADD `de_mobile_type4_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type4_list_mod`,
                    ADD `de_mobile_type5_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type5_list_mod` ", true);
}

// 모바일 관련상품 이미지 줄 수 필드 추가
if(!isset($default['de_mobile_rel_list_mod'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_mobile_rel_list_mod` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_rel_list_skin` ", true);
}

// 모바일 검색상품 이미지 줄 수 필드 추가
if(!isset($default['de_mobile_search_list_row'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_mobile_search_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_search_list_mod` ", true);
}

// PG 간펼결제 사용여부 필드 추가
if(!isset($default['de_easy_pay_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_easy_pay_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_iche_use` ", true);
}

// 이니시스 삼성페이 사용여부 필드 추가
if(!isset($default['de_samsung_pay_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_samsung_pay_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_easy_pay_use` ", true);
}

// 이니시스
if(!isset($default['de_inicis_cartpoint_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_inicis_cartpoint_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_samsung_pay_use` ", true);
}

// 이니시스 lpay 사용여부 필드 추가
if(!isset($default['de_inicis_lpay_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_inicis_lpay_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_samsung_pay_use` ", true);
}

// 이니시스 kakaopay 사용여부 필드 추가
if(!isset($default['de_inicis_kakaopay_use'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_inicis_kakaopay_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_inicis_lpay_use` ", true);
}

// 카카오페이 필드 추가
if(!isset($default['de_kakaopay_mid'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_kakaopay_mid` varchar(255) NOT NULL DEFAULT '' AFTER `de_tax_flag_use`,
                    ADD `de_kakaopay_key` varchar(255) NOT NULL DEFAULT '' AFTER `de_kakaopay_mid`,
                    ADD `de_kakaopay_enckey` varchar(255) NOT NULL DEFAULT '' AFTER `de_kakaopay_key`,
                    ADD `de_kakaopay_hashkey` varchar(255) NOT NULL DEFAULT '' AFTER `de_kakaopay_enckey`,
                    ADD `de_kakaopay_cancelpwd` varchar(255) NOT NULL DEFAULT '' AFTER `de_kakaopay_hashkey` ", true);
}

// 이니시스 웹결제 사인키 필드 추가
if(!isset($default['de_inicis_sign_key'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_inicis_sign_key` varchar(255) NOT NULL DEFAULT '' ", true);
}

// 네이버페이 필드추가
if(!isset($default['de_naverpay_mid'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_naverpay_mid` varchar(255) NOT NULL DEFAULT '' AFTER `de_kakaopay_cancelpwd`,
                    ADD `de_naverpay_cert_key` varchar(255) NOT NULL DEFAULT '' AFTER `de_naverpay_mid`,
                    ADD `de_naverpay_button_key` varchar(255) NOT NULL DEFAULT '' AFTER `de_naverpay_cert_key`,
                    ADD `de_naverpay_test` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_naverpay_button_key`,
                    ADD `de_naverpay_mb_id` varchar(255) NOT NULL DEFAULT '' AFTER `de_naverpay_test`,
                    ADD `de_naverpay_sendcost` varchar(255) NOT NULL DEFAULT '' AFTER `de_naverpay_mb_id`", true);
}

// 유형별상품리스트 설정필드 추가
if(!isset($default['de_listtype_list_skin'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_listtype_list_skin` varchar(255) NOT NULL DEFAULT '' AFTER `de_mobile_search_img_height`,
                    ADD `de_listtype_list_mod` int(11) NOT NULL DEFAULT '0' AFTER `de_listtype_list_skin`,
                    ADD `de_listtype_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_listtype_list_mod`,
                    ADD `de_listtype_img_width` int(11) NOT NULL DEFAULT '0' AFTER `de_listtype_list_row`,
                    ADD `de_listtype_img_height` int(11) NOT NULL DEFAULT '0' AFTER `de_listtype_img_width`,
                    ADD `de_mobile_listtype_list_skin` varchar(255) NOT NULL DEFAULT '' AFTER `de_listtype_img_height`,
                    ADD `de_mobile_listtype_list_mod` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_listtype_list_skin`,
                    ADD `de_mobile_listtype_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_listtype_list_mod`,
                    ADD `de_mobile_listtype_img_width` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_listtype_list_row`,
                    ADD `de_mobile_listtype_img_height` int(11) NOT NULL DEFAULT '0' AFTER `de_mobile_listtype_img_width` ", true);
}

// 임시저장 테이블이 없을 경우 생성
if(!sql_query(" DESC {$g5['g5_shop_post_log_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_post_log_table']}` (
                  `log_id` int(11) NOT NULL AUTO_INCREMENT,   
                  `oid` bigint(20) unsigned NOT NULL,
                  `mb_id` varchar(255) NOT NULL DEFAULT '',
                  `post_data` text NOT NULL,
                  `ol_code` varchar(255) NOT NULL DEFAULT '',
                  `ol_msg` text NOT NULL,
                  `ol_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `ol_ip` varchar(25) NOT NULL DEFAULT '',
                  PRIMARY KEY (`log_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ", false);
}


// 현금영수증 발급 조건 추가
if(!isset($default['de_taxsave_types'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_taxsave_types` set('account','vbank','transfer') NOT NULL DEFAULT 'account' AFTER `de_taxsave_use` ", true);
}

// 아이코드 토큰키 추가
if( ! isset($config['cf_icode_token_key']) ){
    $sql = "ALTER TABLE `{$g5['config_table']}` 
            ADD COLUMN `cf_icode_token_key` VARCHAR(100) NOT NULL DEFAULT '' AFTER `cf_icode_server_port`; ";
    sql_query($sql, false);
}

// PG 간편결제 추가 ( NHN_KCP 네이버페이, 카카오페이 )
if( ! isset($default['de_easy_pay_services']) ){
    $sql = "ALTER TABLE `{$g5['g5_shop_default_table']}` 
            ADD COLUMN `de_easy_pay_services` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_easy_pay_use`; ";
    sql_query($sql, false);
}

// KG 이니시스 iniapi_key 추가
if( ! isset($default['de_inicis_iniapi_key']) ){
    $sql = "ALTER TABLE `{$g5['g5_shop_default_table']}` 
            ADD COLUMN `de_inicis_iniapi_key` VARCHAR(30) NOT NULL DEFAULT '' AFTER `de_inicis_sign_key`,
            ADD COLUMN `de_inicis_iniapi_iv` VARCHAR(30) NOT NULL DEFAULT '' AFTER `de_inicis_iniapi_key`; ";
    sql_query($sql, false);
}

// NICEPAY mid, key 추가
if (! isset($default['de_nicepay_mid'])) {
    $sql = "ALTER TABLE `{$g5['g5_shop_default_table']}` 
            ADD COLUMN `de_nicepay_mid` VARCHAR(20) NOT NULL DEFAULT '' AFTER `de_inicis_cartpoint_use`,
            ADD COLUMN `de_nicepay_key` VARCHAR(150) NOT NULL DEFAULT '' AFTER `de_nicepay_mid`; ";
    sql_query($sql, false);
}

if( function_exists('pg_setting_check') ){
    pg_setting_check(true);
}

if(!$default['de_kakaopay_cancelpwd']){
    $default['de_kakaopay_cancelpwd'] = '1111';
}
?>

<form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="token" value="">
<section id="anc_scf_info">
    <h2 class="h2_frm">사업자정보</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            사업자정보는 tail.php 와 content.php 에서 표시합니다.<br>
            대표전화번호는 SMS 발송번호로 사용되므로 사전등록된 발신번호와 일치해야 합니다.
        </p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>사업자정보 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_admin_company_name">회사명</label></th>
            <td>
                <input type="text" name="de_admin_company_name" value="<?php echo get_sanitize_input($default['de_admin_company_name']); ?>" id="de_admin_company_name" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_company_saupja_no">사업자등록번호</label></th>
            <td>
                <input type="text" name="de_admin_company_saupja_no"  value="<?php echo get_sanitize_input($default['de_admin_company_saupja_no']); ?>" id="de_admin_company_saupja_no" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_company_owner">대표자명</label></th>
            <td colspan="3">
                <input type="text" name="de_admin_company_owner" value="<?php echo get_sanitize_input($default['de_admin_company_owner']); ?>" id="de_admin_company_owner" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_company_tel">대표전화번호</label></th>
            <td>
                <input type="text" name="de_admin_company_tel" value="<?php echo get_sanitize_input($default['de_admin_company_tel']); ?>" id="de_admin_company_tel" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_company_fax">팩스번호</label></th>
            <td>
                <input type="text" name="de_admin_company_fax" value="<?php echo get_sanitize_input($default['de_admin_company_fax']); ?>" id="de_admin_company_fax" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_tongsin_no">통신판매업 신고번호</label></th>
            <td>
                <input type="text" name="de_admin_tongsin_no" value="<?php echo get_sanitize_input($default['de_admin_tongsin_no']); ?>" id="de_admin_tongsin_no" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_buga_no">부가통신 사업자번호</label></th>
            <td>
                <input type="text" name="de_admin_buga_no" value="<?php echo get_sanitize_input($default['de_admin_buga_no']); ?>" id="de_admin_buga_no" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_company_zip">사업장우편번호</label></th>
            <td>
                <input type="text" name="de_admin_company_zip" value="<?php echo get_sanitize_input($default['de_admin_company_zip']); ?>" id="de_admin_company_zip" class="frm_input" size="10">
            </td>
            <th scope="row"><label for="de_admin_company_addr">사업장주소</label></th>
            <td>
                <input type="text" name="de_admin_company_addr" value="<?php echo get_sanitize_input($default['de_admin_company_addr']); ?>" id="de_admin_company_addr" class="frm_input" size="30">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_admin_info_name">정보관리책임자명</label></th>
            <td>
                <input type="text" name="de_admin_info_name" value="<?php echo get_sanitize_input($default['de_admin_info_name']); ?>" id="de_admin_info_name" class="frm_input" size="30">
            </td>
            <th scope="row"><label for="de_admin_info_email">정보책임자 e-mail</label></th>
            <td>
                <input type="text" name="de_admin_info_email" value="<?php echo get_sanitize_input($default['de_admin_info_email']); ?>" id="de_admin_info_email" class="frm_input" size="30">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_scf_skin">
    <h2 class="h2_frm">스킨설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>상품 분류리스트, 상품상세보기 등 에서 사용할 스킨을 설정합니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>스킨설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_shop_skin">PC용 스킨</label></th>
            <td>
                <?php echo get_skin_select('shop', 'de_shop_skin', 'de_shop_skin', $default['de_shop_skin'], 'required'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_shop_mobile_skin">모바일용 스킨</label></th>
            <td>
                <?php echo get_mobile_skin_select('shop', 'de_shop_mobile_skin', 'de_shop_mobile_skin', $default['de_shop_mobile_skin'], 'required'); ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<button type="button" class="get_shop_skin">테마 스킨설정 가져오기</button>

<section id="anc_scf_index">
    <h2 class="h2_frm">쇼핑몰 초기화면</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            상품관리에서 선택한 상품의 타입대로 쇼핑몰 초기화면에 출력합니다. (상품 타입 히트/추천/최신/인기/할인)<br>
            각 타입별로 선택된 상품이 없으면 쇼핑몰 초기화면에 출력하지 않습니다.
        </p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>쇼핑몰 초기화면 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">히트상품출력</th>
            <td>
                <label for="de_type1_list_use">출력</label>
                <input type="checkbox" name="de_type1_list_use" value="1" id="de_type1_list_use" <?php echo $default['de_type1_list_use']?"checked":""; ?>>
                <label for="de_type1_list_skin">스킨</label>
                <select name="de_type1_list_skin" id="de_type1_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_type1_list_skin']); ?>
                </select>
                <label for="de_type1_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_type1_list_mod" value="<?php echo get_sanitize_input($default['de_type1_list_mod']); ?>" id="de_type1_list_mod" class="frm_input" size="3">
                <label for="de_type1_list_row">출력할 줄 수</label>
                <input type="text" name="de_type1_list_row" value="<?php echo get_sanitize_input($default['de_type1_list_row']); ?>" id="de_type1_list_row" class="frm_input" size="3">
                <label for="de_type1_img_width">이미지 폭</label>
                <input type="text" name="de_type1_img_width" value="<?php echo get_sanitize_input($default['de_type1_img_width']); ?>" id="de_type1_img_width" class="frm_input" size="3">
                <label for="de_type1_img_height">이미지 높이</label>
                <input type="text" name="de_type1_img_height" value="<?php echo get_sanitize_input($default['de_type1_img_height']); ?>" id="de_type1_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">추천상품출력</th>
            <td>
                <label for="de_type2_list_use">출력</label>
                <input type="checkbox" name="de_type2_list_use" value="1" id="de_type2_list_use" <?php echo $default['de_type2_list_use']?"checked":""; ?>>
                <label for="de_type2_list_skin">스킨</label>
                <select name="de_type2_list_skin" id="de_type2_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_type2_list_skin']); ?>
                </select>
                <label for="de_type2_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_type2_list_mod" value="<?php echo get_sanitize_input($default['de_type2_list_mod']); ?>" id="de_type2_list_mod" class="frm_input" size="3">
                <label for="de_type2_list_row">출력할 줄 수</label>
                <input type="text" name="de_type2_list_row" value="<?php echo get_sanitize_input($default['de_type2_list_row']); ?>" id="de_type2_list_row" class="frm_input" size="3">
                <label for="de_type2_img_width">이미지 폭</label>
                <input type="text" name="de_type2_img_width" value="<?php echo get_sanitize_input($default['de_type2_img_width']); ?>" id="de_type2_img_width" class="frm_input" size="3">
                <label for="de_type2_img_height">이미지 높이</label>
                <input type="text" name="de_type2_img_height" value="<?php echo get_sanitize_input($default['de_type2_img_height']); ?>" id="de_type2_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">최신상품출력</th>
            <td>
                <label for="de_type3_list_use">출력</label>
                <input type="checkbox" name="de_type3_list_use" value="1" id="de_type3_list_use" <?php echo $default['de_type3_list_use']?"checked":""; ?>>
                <label for="de_type3_list_skin">스킨</label>
                <select name="de_type3_list_skin" id="de_type3_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_type3_list_skin']); ?>
                </select>
                <label for="de_type3_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_type3_list_mod" value="<?php echo get_sanitize_input($default['de_type3_list_mod']); ?>" id="de_type3_list_mod" class="frm_input" size="3">
                <label for="de_type3_list_row">출력할 줄 수</label>
                <input type="text" name="de_type3_list_row" value="<?php echo get_sanitize_input($default['de_type3_list_row']); ?>" id="de_type3_list_row" class="frm_input" size="3">
                <label for="de_type3_img_width">이미지 폭</label>
                <input type="text" name="de_type3_img_width" value="<?php echo get_sanitize_input($default['de_type3_img_width']); ?>" id="de_type3_img_width" class="frm_input" size="3">
                <label for="de_type3_img_height">이미지 높이</label>
                <input type="text" name="de_type3_img_height" value="<?php echo get_sanitize_input($default['de_type3_img_height']); ?>" id="de_type3_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">인기상품출력</th>
            <td>
                <label for="de_type4_list_use">출력</label>
                <input type="checkbox" name="de_type4_list_use" value="1" id="de_type4_list_use" <?php echo $default['de_type4_list_use']?"checked":""; ?>>
                <label for="de_type4_list_skin">스킨</label>
                <select name="de_type4_list_skin" id="de_type4_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_type4_list_skin']); ?>
                </select>
                <label for="de_type4_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_type4_list_mod" value="<?php echo get_sanitize_input($default['de_type4_list_mod']); ?>" id="de_type4_list_mod" class="frm_input" size="3">
                <label for="de_type4_list_row">출력할 줄 수</label>
                <input type="text" name="de_type4_list_row" value="<?php echo get_sanitize_input($default['de_type4_list_row']); ?>" id="de_type4_list_row" class="frm_input" size="3">
                <label for="de_type4_img_width">이미지 폭</label>
                <input type="text" name="de_type4_img_width" value="<?php echo get_sanitize_input($default['de_type4_img_width']); ?>" id="de_type4_img_width" class="frm_input" size="3">
                <label for="de_type4_img_height">이미지 높이</label>
                <input type="text" name="de_type4_img_height" value="<?php echo get_sanitize_input($default['de_type4_img_height']); ?>" id="de_type4_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">할인상품출력</th>
            <td>
                <label for="de_type5_list_use">출력</label>
                <input type="checkbox" name="de_type5_list_use" value="1" id="de_type5_list_use" <?php echo $default['de_type5_list_use']?"checked":""; ?>>
                <label for="de_type5_list_skin">스킨</label>
                <select name="de_type5_list_skin" id="de_type5_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_type5_list_skin']); ?>
                </select>
                <label for="de_type5_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_type5_list_mod" value="<?php echo get_sanitize_input($default['de_type5_list_mod']); ?>" id="de_type5_list_mod" class="frm_input" size="3">
                <label for="de_type5_list_row">출력할 줄 수</label>
                <input type="text" name="de_type5_list_row" value="<?php echo get_sanitize_input($default['de_type5_list_row']); ?>" id="de_type5_list_row" class="frm_input" size="3">
                <label for="de_type5_img_width">이미지 폭</label>
                <input type="text" name="de_type5_img_width" value="<?php echo get_sanitize_input($default['de_type5_img_width']); ?>" id="de_type5_img_width" class="frm_input" size="3">
                <label for="de_type5_img_height">이미지 높이</label>
                <input type="text" name="de_type5_img_height" value="<?php echo get_sanitize_input($default['de_type5_img_height']); ?>" id="de_type5_img_height" class="frm_input" size="3">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<button type="button" class="shop_pc_index">테마설정 가져오기</button>

<section id="anc_mscf_index">
    <h2 class="h2_frm">모바일 쇼핑몰 초기화면 설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            상품관리에서 선택한 상품의 타입대로 쇼핑몰 초기화면에 출력합니다. (상품 타입 히트/추천/최신/인기/할인)<br>
            각 타입별로 선택된 상품이 없으면 쇼핑몰 초기화면에 출력하지 않습니다.
        </p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>모바일 쇼핑몰 초기화면 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">히트상품출력</th>
            <td>
                <label for="de_mobile_type1_list_use">출력</label>
                <input type="checkbox" name="de_mobile_type1_list_use" value="1" id="de_mobile_type1_list_use" <?php echo $default['de_mobile_type1_list_use']?"checked":""; ?>>
                <label for="de_mobile_type1_list_skin">스킨</label>
                <select name="de_mobile_type1_list_skin" id="de_mobile_type1_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_type1_list_skin']); ?>
                </select>
                <label for="de_mobile_type1_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_type1_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_type1_list_mod']); ?>" id="de_mobile_type1_list_mod" class="frm_input" size="3">
                 <label for="de_mobile_type1_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_type1_list_row" value="<?php echo get_sanitize_input($default['de_mobile_type1_list_row']); ?>" id="de_mobile_type1_list_row" class="frm_input" size="3">
                <label for="de_mobile_type1_img_width">이미지 폭</label>
                <input type="text" name="de_mobile_type1_img_width" value="<?php echo get_sanitize_input($default['de_mobile_type1_img_width']); ?>" id="de_mobile_type1_img_width" class="frm_input" size="3">
                <label for="de_mobile_type1_img_height">이미지 높이</label>
                <input type="text" name="de_mobile_type1_img_height" value="<?php echo get_sanitize_input($default['de_mobile_type1_img_height']); ?>" id="de_mobile_type1_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">추천상품출력</th>
            <td>
                <label for="de_mobile_type2_list_use">출력</label> <input type="checkbox" name="de_mobile_type2_list_use" value="1" id="de_mobile_type2_list_use" <?php echo $default['de_mobile_type2_list_use']?"checked":""; ?>>
                <label for="de_mobile_type2_list_skin">스킨 </label>
                <select name="de_mobile_type2_list_skin" id="de_mobile_type2_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_type2_list_skin']); ?>
                </select>
                <label for="de_mobile_type2_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_type2_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_type2_list_mod']); ?>" id="de_mobile_type2_list_mod" class="frm_input" size="3">
                 <label for="de_mobile_type2_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_type2_list_row" value="<?php echo get_sanitize_input($default['de_mobile_type2_list_row']); ?>" id="de_mobile_type2_list_row" class="frm_input" size="3">
                <label for="de_mobile_type2_img_width">이미지 폭</label>
                <input type="text" name="de_mobile_type2_img_width" value="<?php echo get_sanitize_input($default['de_mobile_type2_img_width']); ?>" id="de_mobile_type2_img_width" class="frm_input" size="3">
                <label for="de_mobile_type2_img_height">이미지 높이</label>
                <input type="text" name="de_mobile_type2_img_height" value="<?php echo get_sanitize_input($default['de_mobile_type2_img_height']); ?>" id="de_mobile_type2_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">최신상품출력</th>
            <td>
                <label for="de_mobile_type3_list_use">출력</label>
                <input type="checkbox" name="de_mobile_type3_list_use" value="1" id="de_mobile_type3_list_use" <?php echo $default['de_mobile_type3_list_use']?"checked":""; ?>>
                <label for="de_mobile_type3_list_skin">스킨</label>
                <select name="de_mobile_type3_list_skin" id="de_mobile_type3_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_type3_list_skin']); ?>
                </select>
                <label for="de_mobile_type3_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_type3_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_type3_list_mod']); ?>" id="de_mobile_type3_list_mod" class="frm_input" size="3">
                 <label for="de_mobile_type3_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_type3_list_row" value="<?php echo get_sanitize_input($default['de_mobile_type3_list_row']); ?>" id="de_mobile_type3_list_row" class="frm_input" size="3">
                <label for="de_mobile_type3_img_width">이미지 폭</label>
                <input type="text" name="de_mobile_type3_img_width" value="<?php echo get_sanitize_input($default['de_mobile_type3_img_width']); ?>" id="de_mobile_type3_img_width" class="frm_input" size="3">
                <label for="de_mobile_type3_img_height">이미지 높이</label>
                <input type="text" name="de_mobile_type3_img_height" value="<?php echo get_sanitize_input($default['de_mobile_type3_img_height']); ?>" id="de_mobile_type3_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">인기상품출력</th>
            <td>
                <label for="de_mobile_type4_list_use">출력</label>
                <input type="checkbox" name="de_mobile_type4_list_use" value="1" id="de_mobile_type4_list_use" <?php echo $default['de_mobile_type4_list_use']?"checked":""; ?>>
                <label for="de_mobile_type4_list_skin">스킨</label>
                <select name="de_mobile_type4_list_skin" id="de_mobile_type4_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_type4_list_skin']); ?>
                </select>
                <label for="de_mobile_type4_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_type4_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_type4_list_mod']); ?>" id="de_mobile_type4_list_mod" class="frm_input" size="3">
                 <label for="de_mobile_type4_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_type4_list_row" value="<?php echo get_sanitize_input($default['de_mobile_type4_list_row']); ?>" id="de_mobile_type4_list_row" class="frm_input" size="3">
                <label for="de_mobile_type4_img_width">이미지 폭</label>
                <input type="text" name="de_mobile_type4_img_width" value="<?php echo get_sanitize_input($default['de_mobile_type4_img_width']); ?>" id="de_mobile_type4_img_width" class="frm_input" size="3">
                <label for="de_mobile_type4_img_height">이미지 높이</label>
                <input type="text" name="de_mobile_type4_img_height" value="<?php echo get_sanitize_input($default['de_mobile_type4_img_height']); ?>" id="de_mobile_type4_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">할인상품출력</th>
            <td>
                <label for="de_mobile_type5_list_use">출력</label>
                <input type="checkbox" name="de_mobile_type5_list_use" value="1" id="de_mobile_type5_list_use" <?php echo $default['de_mobile_type5_list_use']?"checked":""; ?>>
                <label for="de_mobile_type5_list_skin">스킨</label>
                <select id="de_mobile_type5_list_skin" name="de_mobile_type5_list_skin">
                    <?php echo get_list_skin_options("^main.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_type5_list_skin']); ?>
                </select>
                <label for="de_mobile_type5_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_type5_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_type5_list_mod']); ?>" id="de_mobile_type5_list_mod" class="frm_input" size="3">
                 <label for="de_mobile_type5_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_type5_list_row" value="<?php echo get_sanitize_input($default['de_mobile_type5_list_row']); ?>" id="de_mobile_type5_list_row" class="frm_input" size="3">
                <label for="de_mobile_type5_img_width">이미지 폭</label>
                <input type="text" name="de_mobile_type5_img_width" value="<?php echo get_sanitize_input($default['de_mobile_type5_img_width']); ?>" id="de_mobile_type5_img_width" class="frm_input" size="3">
                <label for="de_mobile_type5_img_height">이미지 높이</label>
                <input type="text" name="de_mobile_type5_img_height" value="<?php echo get_sanitize_input($default['de_mobile_type5_img_height']); ?>" id="de_mobile_type5_img_height" class="frm_input" size="3">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<button type="button" class="shop_mobile_index">테마설정 가져오기</button>

<section id ="anc_scf_payment">
    <h2 class="h2_frm">결제설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>결제설정 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_bank_use">무통장입금사용</label></th>
            <td>
                <?php echo help("주문시 무통장으로 입금을 가능하게 할것인지를 설정합니다.\n사용할 경우 은행계좌번호를 반드시 입력하여 주십시오.", 50); ?>
                <select id="de_bank_use" name="de_bank_use">
                    <option value="0" <?php echo get_selected($default['de_bank_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_bank_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_bank_account">은행계좌번호</label></th>
            <td>
                <textarea name="de_bank_account" id="de_bank_account"><?php echo html_purifier($default['de_bank_account']); ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_iche_use">계좌이체 결제사용</label></th>
            <td>
            <?php echo help("주문시 실시간 계좌이체를 가능하게 할것인지를 설정합니다.", 50); ?>
                <select id="de_iche_use" name="de_iche_use">
                    <option value="0" <?php echo get_selected($default['de_iche_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_iche_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_vbank_use">가상계좌 결제사용</label></th>
            <td>
                <?php echo help("주문별로 유일하게 생성되는 일회용 계좌번호입니다. 주문자가 가상계좌에 입금시 상점에 실시간으로 통보가 되므로 업무처리가 빨라집니다.", 50); ?>
                <select name="de_vbank_use" id="de_vbank_use">
                    <option value="0" <?php echo get_selected($default['de_vbank_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_vbank_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr id="kcp_vbank_url" class="pg_vbank_url">
            <th scope="row">NHN KCP 가상계좌<br>입금통보 URL</th>
            <td>
                <?php echo help("NHN KCP 가상계좌 사용시 다음 주소를 <strong><a href=\"http://admin.kcp.co.kr\" target=\"_blank\">NHN KCP 관리자</a> &gt; 상점정보관리 &gt; 정보변경 &gt; 공통URL 정보 &gt; 공통URL 변경후</strong>에 넣으셔야 상점에 자동으로 입금 통보됩니다."); ?>
                <?php echo G5_SHOP_URL; ?>/settle_kcp_common.php</td>
        </tr>
        <tr id="inicis_vbank_url" class="pg_vbank_url">
            <th scope="row">KG이니시스 가상계좌 입금통보 URL</th>
            <td>
                <?php echo help("KG이니시스 가상계좌 사용시 다음 주소를 <strong><a href=\"https://iniweb.inicis.com/\" target=\"_blank\">KG이니시스 관리자</a> &gt; 거래내역 &gt; 가상계좌 &gt; 입금통보방식선택 &gt; URL 수신 설정</strong>에 넣으셔야 상점에 자동으로 입금 통보됩니다."); ?>
                <?php echo G5_SHOP_URL; ?>/settle_inicis_common.php</td>
        </tr>
        <tr id="nicepay_vbank_url" class="pg_vbank_url">
            <th scope="row">NICEPAY 가상계좌 입금통보 URL</th>
            <td>
                <?php echo help("NICEPAY 가상계좌 사용시 다음 주소를 <strong><a href=\"https://npg.nicepay.co.kr/\" target=\"_blank\">NICEPAY 관리자</a> &gt; 가맹점관리자페이지 설정 (메인화면 → 가맹점정보 클릭)</strong>에 넣으셔야 상점에 자동으로 입금 통보됩니다."); ?>
                <?php echo G5_SHOP_URL; ?>/settle_nicepay_common.php</td>
        </tr>
        <tr>
            <th scope="row"><label for="de_hp_use">휴대폰결제사용</label></th>
            <td>
                <?php echo help("주문시 휴대폰 결제를 가능하게 할것인지를 설정합니다.", 50); ?>
                <select id="de_hp_use" name="de_hp_use">
                    <option value="0" <?php echo get_selected($default['de_hp_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_hp_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_card_use">신용카드결제사용</label></th>
            <td>
                <?php echo help("주문시 신용카드 결제를 가능하게 할것인지를 설정합니다.", 50); ?>
                <select id="de_card_use" name="de_card_use">
                    <option value="0" <?php echo get_selected($default['de_card_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_card_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_card_noint_use">신용카드 무이자할부사용<br>( KCP 만 해당 )</label></th>
            <td>
                <?php echo help("주문시 신용카드 무이자할부를 가능하게 할것인지를 설정합니다.<br>사용으로 설정하시면 KCP PG사 가맹점 관리자 페이지에서 설정하신 무이자할부 설정이 적용됩니다.<br>사용안함으로 설정하시면 KCP PG사 무이자 이벤트 카드를 제외한 모든 카드의 무이자 설정이 적용되지 않습니다.", 50); ?>
                <select id="de_card_noint_use" name="de_card_noint_use">
                    <option value="0" <?php echo get_selected($default['de_card_noint_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_card_noint_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_easy_pay_use">PG사 간편결제 버튼 사용</label></th>
            <td>
                <?php echo help("주문서 작성 페이지에 PG사 간편결제(PAYCO, PAYNOW, KPAY) 버튼의 별도 사용 여부를 설정합니다.", 50); ?>
                <select id="de_easy_pay_use" name="de_easy_pay_use">
                    <option value="0" <?php echo get_selected($default['de_easy_pay_use'], 0); ?>>노출안함</option>
                    <option value="1" <?php echo get_selected($default['de_easy_pay_use'], 1); ?>>노출함</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_taxsave_use">현금영수증<br>발급사용</label></th>
            <td>
                <?php echo help("관리자는 설정에 관계없이 <a href=\"".G5_ADMIN_URL."/shop_admin/orderlist.php\">주문내역</a> &gt; 보기에서 발급이 가능합니다.\n현금영수증 발급 취소는 PG사에서 지원하는 현금영수증 취소 기능을 사용하시기 바랍니다.", 50); ?>
                <select id="de_taxsave_use" name="de_taxsave_use">
                    <option value="0" <?php echo get_selected($default['de_taxsave_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_taxsave_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <?php
        $account_checked = $vbank_checked = $transfer_checked = '';

        if (strstr($default['de_taxsave_types'], 'account')) {
            $account_checked = 'checked="checked"';
        }
        if (strstr($default['de_taxsave_types'], 'vbank')) {
            $vbank_checked = 'checked="checked"';
        }
        if (strstr($default['de_taxsave_types'], 'transfer')) {
            $transfer_checked = 'checked="checked"';
        }
        ?>
        <tr id="de_taxsave_types" class="de_taxsave_types">
            <th scope="row">현금영수증<br>적용수단</th>
            <td>
                <?php echo help("현금영수증 발급 사용일 경우 해당됩니다.<br>현금 영수증 발급은 무통장입금, 가상계좌, 실시간계좌에만 적용됩니다.<br>아래 체크된 수단에 한해서 회원이 직접 주문 보기 페이지에서 현금영수증을 발급 받을수 있습니다.<br>!!! 만약 PG사 상점관리자에서 가상계좌 또는 실시간계좌이체가 자동으로 현금영수증이 발급되는 경우이면, 아래 가상계좌와 실시간계좌이체 체크박스를 해제하여 사용해 주세요.( 중복으로 발급되는 것을 막기 위함입니다. )", 50); ?>
                <input type="checkbox" id="de_taxsave_types_account" name="de_taxsave_types_account" value="account" <?php echo $account_checked; ?> > <label for="de_taxsave_types_account" disabled>무통장입금</label><br>
                <input type="checkbox" id="de_taxsave_types_vbank" name="de_taxsave_types_vbank" value="vbank" <?php echo $vbank_checked; ?> > <label for="de_taxsave_types_vbank">가상계좌</label><br>
                <input type="checkbox" id="de_taxsave_types_transfer" name="de_taxsave_types_transfer" value="transfer" <?php echo $transfer_checked; ?> > <label for="de_taxsave_types_transfer">실시간계좌이체</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_use_point">포인트 사용</label></th>
            <td>
                <?php echo help("<a href=\"".G5_ADMIN_URL."/config_form.php#frm_board\" target=\"_blank\">환경설정 &gt; 기본환경설정</a>과 동일한 설정입니다."); ?>
                <input type="checkbox" name="cf_use_point" value="1" id="cf_use_point"<?php echo $config['cf_use_point']?' checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_settle_min_point">결제 최소포인트</label></th>
            <td>
                <?php echo help("회원의 포인트가 설정값 이상일 경우만 주문시 결제에 사용할 수 있습니다.\n포인트 사용을 하지 않는 경우에는 의미가 없습니다."); ?>
                <input type="text" name="de_settle_min_point" value="<?php echo get_sanitize_input($default['de_settle_min_point']); ?>" id="de_settle_min_point" class="frm_input" size="10"> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_settle_max_point">최대 결제포인트</label></th>
            <td>
                <?php echo help("주문 결제시 최대로 사용할 수 있는 포인트를 설정합니다.\n포인트 사용을 하지 않는 경우에는 의미가 없습니다."); ?>
                <input type="text" name="de_settle_max_point" value="<?php echo get_sanitize_input($default['de_settle_max_point']); ?>" id="de_settle_max_point" class="frm_input" size="10"> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_settle_point_unit">결제 포인트단위</label></th>
            <td>
                <?php echo help("주문 결제시 사용되는 포인트의 절사 단위를 설정합니다."); ?>
                <select id="de_settle_point_unit" name="de_settle_point_unit">
                    <option value="100" <?php echo get_selected($default['de_settle_point_unit'], 100); ?>>100</option>
                    <option value="10"  <?php echo get_selected($default['de_settle_point_unit'],  10); ?>>10</option>
                    <option value="1"   <?php echo get_selected($default['de_settle_point_unit'],   1); ?>>1</option>
                </select> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_card_point">포인트부여</label></th>
            <td>
                <?php echo help("신용카드, 계좌이체, 휴대폰 결제시 포인트를 부여할지를 설정합니다. (기본값은 '아니오')"); ?>
                <select id="de_card_point" name="de_card_point">
                    <option value="0" <?php echo get_selected($default['de_card_point'], 0); ?>>아니오</option>
                    <option value="1" <?php echo get_selected($default['de_card_point'], 1); ?>>예</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_point_days">주문완료 포인트</label></th>
            <td>
                <?php echo help("주문자가 회원일 경우에만 주문완료시 포인트를 지급합니다. 주문취소, 반품 등을 고려하여 포인트를 지급할 적당한 기간을 입력하십시오. (기본값은 7일)\n0일로 설정하는 경우에는 주문완료와 동시에 포인트를 지급합니다."); ?>
                주문 완료 <input type="text" name="de_point_days" value="<?php echo get_sanitize_input($default['de_point_days']); ?>" id="de_point_days" class="frm_input" size="2"> 일 이후에 포인트를 지급
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_pg_service">결제대행사</label></th>
            <td>
                <input type="hidden" name="de_pg_service" id="de_pg_service" value="<?php echo $default['de_pg_service']; ?>" >
                <?php echo help('쇼핑몰에서 사용할 결제대행사를 선택합니다.'); ?>
                <ul class="de_pg_tab">
                    <li class="<?php if($default['de_pg_service'] == 'kcp') echo 'tab-current'; ?>"><a href="#kcp_info_anchor" data-value="kcp" title="NHN KCP 선택하기" >NHN KCP</a></li>
                    <li class="<?php if($default['de_pg_service'] == 'lg') echo 'tab-current'; ?>"><a href="#lg_info_anchor" data-value="lg" title="토스페이먼츠 선택하기">토스페이먼츠</a></li>
                    <li class="<?php if($default['de_pg_service'] == 'inicis') echo 'tab-current'; ?>"><a href="#inicis_info_anchor" data-value="inicis" title="KG이니시스 선택하기">KG이니시스</a></li>
                    <li class="<?php if($default['de_pg_service'] == 'nicepay') echo 'tab-current'; ?>"><a href="#nicepay_info_anchor" data-value="nicepay" title="NICEPAY 선택하기">NICEPAY</a></li>
                </ul>
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld" id="kcp_info_anchor">
            <th scope="row">
                <label for="de_kcp_mid">KCP SITE CODE</label><br>
                <a href="http://sir.kr/main/service/p_pg.php" target="_blank" id="scf_kcpreg" class="kcp_btn">NHN KCP 신청하기</a>
            </th>
            <td>
                <?php echo help("NHN KCP 에서 받은 SR 로 시작하는 영대문자, 숫자 혼용 총 5자리 중 SR 을 제외한 나머지 3자리 SITE CODE 를 입력하세요.\n만약, 사이트코드가 SR로 시작하지 않는다면 NHN KCP에 사이트코드 변경 요청을 하십시오. 예) SR9A3"); ?>
                <span class="sitecode">SR</span> <input type="text" name="de_kcp_mid" value="<?php echo get_sanitize_input($default['de_kcp_mid']); ?>" id="de_kcp_mid" class="frm_input code_input" size="2" maxlength="3"> 영대문자, 숫자 혼용 3자리
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="de_kcp_site_key">NHN KCP SITE KEY</label></th>
            <td>
                <?php echo help("25자리 영대소문자와 숫자 - 그리고 _ 로 이루어 집니다. SITE KEY 발급 NHN KCP 전화: 1544-8660\n예) 1Q9YRV83gz6TukH8PjH0xFf__"); ?>
                <input type="text" name="de_kcp_site_key" value="<?php echo get_sanitize_input($default['de_kcp_site_key']); ?>" id="de_kcp_site_key" class="frm_input" size="36" maxlength="25">
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="de_kcp_easy_pays">NHN KCP 간편결제</label></th>
            <td>
                <?php echo help("체크시 NHN KCP 간편결제들을 활성화 합니다.\nNHN_KCP > 네이버페이, 카카오페이는 테스트결제가 되지 않습니다.\n애플페이는 IOS 기기에 모바일결제만 가능합니다."); ?>
                <input type="checkbox" id="de_easy_nhnkcp_payco" name="de_easy_pays[]" value="nhnkcp_payco" <?php if(stripos($default['de_easy_pay_services'], 'nhnkcp_payco') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nhnkcp_payco" disabled>PAYCO (페이코)</label><br>
                <input type="checkbox" id="de_easy_nhnkcp_naverpay" name="de_easy_pays[]" value="nhnkcp_naverpay" <?php if(stripos($default['de_easy_pay_services'], 'nhnkcp_naverpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nhnkcp_naverpay">NAVERPAY (네이버페이)</label><br>
                <input type="checkbox" id="de_easy_nhnkcp_kakaopay" name="de_easy_pays[]" value="nhnkcp_kakaopay" <?php if(stripos($default['de_easy_pay_services'], 'nhnkcp_kakaopay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nhnkcp_kakaopay">KAKAOPAY (카카오페이)</label><br>
                <input type="checkbox" id="de_easy_nhnkcp_applepay" name="de_easy_pays[]" value="nhnkcp_applepay" <?php if(stripos($default['de_easy_pay_services'], 'nhnkcp_applepay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nhnkcp_applepay">APPLEPAY (애플페이)</label>
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="de_global_nhnkcp_naverpay">NHN KCP 네이버페이 사용</label></th>
            <td>
                <?php echo help("체크시 타 PG (토스페이먼츠, KG 이니시스) 사용중일때도 NHN_KCP 를 통한 네이버페이 간편결제를 사용할수 있습니다.\n실결제시 반드시 결제대행사 NHN_KCP 항목에 KCP SITE CODE와 NHN KCP SITE KEY를 입력해야 합니다."); ?>
                <input type="checkbox" id="de_global_nhnkcp_naverpay" name="de_easy_pays[]" value="global_nhnkcp_naverpay" <?php if(stripos($default['de_easy_pay_services'], 'global_nhnkcp_naverpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_global_nhnkcp_naverpay">NAVERPAY (네이버페이)</label><br>
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="used_nhnkcp_naverpay_point">NHN KCP 네이버페이<br>포인트결제 사용</label></th>
            <td>
                <?php echo help("체크시 NHN_KCP 를 통한 네이버페이 결제시 네이버페이 포인트결제가 활성화 됩니다.\n체크를 했는데도 [DR02] 실결제시 가맹점 설정정보가 올바르지 않습니다 라고 메시지가 뜬다면, 체크를 해제하고 NHN_KCP 에 위에서 설정한 KCP SITE CODE 로 네이버페이 포인트 결제가 가능한지 문의해 주세요."); ?>
                <input type="checkbox" id="used_nhnkcp_naverpay_point" name="de_easy_pays[]" value="used_nhnkcp_naverpay_point" <?php if(stripos($default['de_easy_pay_services'], 'used_nhnkcp_naverpay_point') !== false){ echo 'checked="checked"'; } ?> > <label for="used_nhnkcp_naverpay_point">NAVERPAY POINT (네이버페이 포인트 사용)</label><br>
            </td>
        </tr>
        <tr class="pg_info_fld lg_info_fld" id="lg_info_anchor">
            <th scope="row">
                <label for="cf_lg_mid">토스페이먼츠 상점아이디</label><br>
                <a href="http://sir.kr/main/service/lg_pg.php" target="_blank" id="scf_lgreg" class="lg_btn">토스페이먼츠 신청하기</a>
            </th>
            <td>
                <?php echo help("토스페이먼츠에서 받은 si_ 로 시작하는 상점 ID를 입력하세요.\n만약, 상점 ID가 si_로 시작하지 않는다면 토스페이먼츠에 사이트코드 변경 요청을 하십시오. 예) si_lguplus\n<a href=\"".G5_ADMIN_URL."/config_form.php#anc_cf_cert\">기본환경설정 &gt; 본인확인</a> 설정의 토스페이먼츠 상점아이디와 동일합니다."); ?>
                <span class="sitecode">si_</span> <input type="text" name="cf_lg_mid" value="<?php echo get_sanitize_input($config['cf_lg_mid']); ?>" id="cf_lg_mid" class="frm_input code_input" size="10" maxlength="20"> 영문자, 숫자 혼용
            </td>
        </tr>
        <tr class="pg_info_fld lg_info_fld">
            <th scope="row"><label for="cf_lg_mert_key">토스페이먼츠 MERT KEY</label></th>
            <td>
                <?php echo help("토스페이먼츠 상점MertKey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실 수 있습니다.\n예) 95160cce09854ef44d2edb2bfb05f9f3\n<a href=\"".G5_ADMIN_URL."/config_form.php#anc_cf_cert\">기본환경설정 &gt; 본인확인</a> 설정의 토스페이먼츠 MERT KEY와 동일합니다."); ?>
                <input type="text" name="cf_lg_mert_key" value="<?php echo get_sanitize_input($config['cf_lg_mert_key']); ?>" id="cf_lg_mert_key" class="frm_input " size="36" maxlength="50">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld" id="inicis_info_anchor">
            <th scope="row">
                <label for="de_inicis_mid">KG이니시스 상점아이디</label><br>
                <a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_kgreg" class="kg_btn">KG이니시스 신청하기</a>
            </th>
            <td>
                <?php echo help("KG이니시스로 부터 발급 받으신 상점아이디(MID) 10자리 중 SIR 을 제외한 나머지 7자리를 입력 합니다.\n만약, 상점아이디가 SIR로 시작하지 않는다면 계약담당자에게 변경 요청을 해주시기 바랍니다. (Tel. 02-3430-5858) 예) SIRpaytest"); ?>
                <span class="sitecode">SIR</span> <input type="text" name="de_inicis_mid" value="<?php echo $default['de_inicis_mid']; ?>" id="de_inicis_mid" class="frm_input code_input" size="10" maxlength="10"> 영문소문자(숫자포함 가능)
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="de_inicis_sign_key">KG이니시스 웹결제 사인키</label></th>
            <td>
                <?php echo help("KG이니시스에서 발급받은 웹결제 사인키를 입력합니다.\n<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보의 웹결제 signkey생성 조회 버튼 클릭, 팝업창에서 생성 버튼 클릭 후 해당 값을 입력합니다."); ?>
                <input type="text" name="de_inicis_sign_key" value="<?php echo get_sanitize_input($default['de_inicis_sign_key']); ?>" id="de_inicis_sign_key" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="de_inicis_iniapi_key">KG이니시스 INIAPI KEY</label></th>
            <td>
                <?php echo help("<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보 > INIAPI key 생성 조회 하여 KEY를 여기에 입력합니다.\n이 항목은 영카트 주문에서 kg이니시스 PG 결제 취소, 부분취소, 에스크로 배송등록, 현금영수증 발급에 필요합니다."); ?>
                <input type="text" name="de_inicis_iniapi_key" value="<?php echo get_sanitize_input($default['de_inicis_iniapi_key']); ?>" id="de_inicis_iniapi_key" class="frm_input" size="30" maxlength="30">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="de_inicis_iniapi_iv">KG이니시스 INIAPI IV</label></th>
            <td>
                <?php echo help("<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보 > INIAPI IV 생성 조회 하여 KEY를 여기에 입력합니다.\n이 항목은 영카트 주문에서 kg이니시스 현금영수증 발급에 필요합니다."); ?>
                <input type="text" name="de_inicis_iniapi_iv" value="<?php echo get_sanitize_input($default['de_inicis_iniapi_iv']); ?>" id="de_inicis_iniapi_iv" class="frm_input" size="30" maxlength="30">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row">
                <label for="de_samsung_pay_use">KG이니시스 삼성페이 사용</label>
                <a href="http://sir.kr/main/service/samsungpay.php" target="_blank" class="kg_btn">삼성페이 서비스신청하기</a>
            </th>
            <td>
                <?php echo help("KG이니시스와 별도로 <strong>삼성페이 사용 계약을 하신 경우</strong>에만 체크해주세요. (모바일 주문서 결제수단에 삼성페이가 노출됩니다.) <br >실결제시 반드시 결제대행사 KG이니시스 항목에 상점 아이디와 웹결제 사인키를 입력해 주세요.", 50); ?>
                <input type="checkbox" name="de_samsung_pay_use" value="1" id="de_samsung_pay_use"<?php echo $default['de_samsung_pay_use']?' checked':''; ?>> <label for="de_samsung_pay_use">사용</label>
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row">
                <label for="de_inicis_lpay_use">KG이니시스 L.pay 사용</label>
            </th>
            <td>
                <?php echo help("체크시 KG이니시스 L.pay를 사용합니다. <br >실결제시 반드시 결제대행사 KG이니시스 항목의 상점 정보( 아이디, 웹결제 사인키 )를 입력해 주세요.", 50); ?>
                <input type="checkbox" name="de_inicis_lpay_use" value="1" id="de_inicis_lpay_use"<?php echo $default['de_inicis_lpay_use']?' checked':''; ?>> <label for="de_inicis_lpay_use">사용</label>
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row">
                <label for="de_inicis_kakaopay_use">KG이니시스 카카오페이 사용</label>
            </th>
            <td>
                <?php echo help("체크시 KG이니시스 결제의 카카오페이를 사용합니다. 주문서 결제수단에 카카오페이가 노출됩니다. <br>실결제시 반드시 결제대행사 KG이니시스 항목의 상점 정보( 아이디, 웹결제 사인키 )를 입력해 주세요.", 50); ?>
                <input type="checkbox" name="de_inicis_kakaopay_use" value="1" id="de_inicis_kakaopay_use"<?php echo $default['de_inicis_kakaopay_use']?' checked':''; ?>> <label for="de_inicis_kakaopay_use">사용</label>
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row">
                <label for="de_inicis_cartpoint_use">KG이니시스 신용카드 포인트 결제</label>
            </th>
            <td>
                <?php echo help("신용카드 포인트 결제에 대해 이니시스와 계약을 맺은 상점에서만 적용하는 옵션입니다.<br>체크시 pc 결제에서는 신용카드 포인트 사용 여부에 대한 팝업창에 사용 버튼과 사용안함 버튼이 표기되어 결제하는 고객의 선택여부에 따라 신용카드 포인트 결제가 가능합니다.<br >모바일에서는 신용카드 포인트 사용이 가능합니다.", 50); ?>
                <input type="checkbox" name="de_inicis_cartpoint_use" value="1" id="de_inicis_cartpoint_use"<?php echo $default['de_inicis_cartpoint_use']?' checked':''; ?>> <label for="de_inicis_cartpoint_use">사용</label>
            </td>
        </tr>
        <tr class="kakao_info_fld">
            <th scope="row">
                <label for="de_kakaopay_mid">카카오페이 상점아이디<br>( KG이니시스 )</label>
                <a href="http://sir.kr/main/service/kakaopay.php?kk=yc5" target="_blank" class="kakao_btn">카카오페이 서비스신청하기</a>
            </th>
            <td>
                <?php echo help("KG이니시스로 부터 카카오페이 간편결제만 사용용도로 발급 받으신 상점아이디(MID) 10자리 중 SIRK 을 제외한 나머지 6자리를 입력 합니다."); ?>
                <span class="sitecode">SIRK</span> <input type="text" name="de_kakaopay_mid" value="<?php echo get_sanitize_input($default['de_kakaopay_mid']); ?>" id="de_kakaopay_mid" class="frm_input code_input" size="10" maxlength="7">
            </td>
        </tr>
        <tr class="kakao_info_fld">
            <th scope="row"><label for="de_kakaopay_key">카카오페이 상점키<br>( KG이니시스 )</label></th>
            <td>
                <?php echo help("SIRK****** 아이디로 KG이니시스에서 발급받은 웹결제 사인키를 입력합니다.\nKG이니시스 상점관리자 > 상점정보 > 계약정보 > 부가정보의 웹결제 signkey생성 조회 버튼 클릭, 팝업창에서 생성 버튼 클릭 후 해당 값을 입력합니다."); ?>
                <input type="text" name="de_kakaopay_key" value="<?php echo get_sanitize_input($default['de_kakaopay_key']); ?>" id="de_kakaopay_key" class="frm_input" size="100">
            </td>
        </tr>
        <tr class="kakao_info_fld">
            <th scope="row"><label for="de_kakaopay_cancelpwd">카카오페이 키패스워드<br>( KG이니시스 )</label></th>
            <td>
                <?php echo help("SIRK****** 아이디로 KG이니시스에서 발급받은 4자리 상점 키패스워드를 입력합니다.\nKG이니시스 상점관리자 패스워드와 관련이 없습니다.\n키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오"); ?>
                <input type="text" name="de_kakaopay_cancelpwd" value="<?php echo get_sanitize_input($default['de_kakaopay_cancelpwd']); ?>" id="de_kakaopay_cancelpwd" class="frm_input" size="20">
            </td>
        </tr>
        <tr class="kakao_info_fld">
            <th scope="row">
                <label for="de_kakaopay_enckey">KG이니시스<br>카카오페이 사용</label>
            </th>
            <td>
                <?php echo help("체크시 카카오페이 (KG 이니시스)를 사용합니다. <br >KG 이니시스의 SIRK****** 아이디를 받은 상점만 해당됩니다.", 50); ?>
                <input type="checkbox" name="de_kakaopay_enckey" value="1" id="de_kakaopay_enckey"<?php echo $default['de_kakaopay_enckey']?' checked':''; ?>> <label for="de_kakaopay_enckey">사용</label>
            </td>
        </tr>
        <tr class="kakao_info_fld" style="display:none">
            <th scope="row"><label for="de_kakaopay_hashkey">카카오페이 상점 HashKey</label></th>
            <td>
                <?php echo help("카카오페이로 부터 발급 받으신 상점 인증 전용 HashKey를 입력합니다."); ?>
                <input type="text" name="de_kakaopay_hashkey" value="<?php echo get_sanitize_input($default['de_kakaopay_hashkey']); ?>" id="de_kakaopay_hashkey" class="frm_input" size="20">
            </td>
        </tr>

        <tr class="pg_info_fld nicepay_info_fld" id="nicepay_info_anchor">
            <th scope="row"><label for="de_nicepay_mid">NICEPAY MID</label><br><a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_nicepay_reg" class="nicepay_btn">NICEPAY 신청하기</a></th>
            <td>
                <span class="frm_info">NICEPAY로 부터 발급 받으신 상점MID를 SR 을 제외한 나머지 자리를 입력 합니다.<br>NICEPAY 상점관리자 > 가맹점정보 > KEY관리에서 확인 할수 있습니다.<br>만약, 상점아이디가 SR로 시작하지 않는다면 계약담당자에게 변경 요청을 해주시기 바랍니다. 예) SRpaytestm</span>
                <span class="sitecode">SR</span>
                <input type="text" name="de_nicepay_mid" value="<?php echo get_sanitize_input($default['de_nicepay_mid']); ?>" id="de_nicepay_mid" class="frm_input" size="12" maxlength="12">
                영문소문자(숫자포함 가능)
            </td>
        </tr>
        <tr class="pg_info_fld nicepay_info_fld">
            <th scope="row"><label for="de_nicepay_key">NICEPAY KEY</label></th>
            <td>
                <input type="text" name="de_nicepay_key" value="<?php echo get_sanitize_input($default['de_nicepay_key']); ?>" id="de_nicepay_key" class="frm_input" size="100" maxlength="100">
            </td>
        </tr>

        <tr class="pg_info_fld nicepay_info_fld">
            <th scope="row"><label for="de_nicepay_easy_pays">NICEPAY 간편결제</label></th>
            <td>
                <?php echo help("체크시 NICEPAY 간편결제들을 활성화 합니다.\nNICEPAY > 간편결제는 테스트결제가 되지 않습니다. 실결제에만 정상작동 합니다.\n애플페이는 IOS 기기에 모바일결제만 가능합니다."); ?>
                <input type="checkbox" id="de_easy_nicepay_samsungpay" name="de_easy_pays[]" value="nicepay_samsungpay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_samsungpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_samsungpay" disabled>삼성페이</label><br>
                <input type="checkbox" id="de_easy_nicepay_naverpay" name="de_easy_pays[]" value="nicepay_naverpay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_naverpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_naverpay">NAVERPAY (네이버페이)</label><br>
                <input type="checkbox" id="de_easy_nicepay_kakaopay" name="de_easy_pays[]" value="nicepay_kakaopay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_kakaopay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_kakaopay">KAKAOPAY (카카오페이)</label><br>
                <input type="checkbox" id="de_easy_nicepay_applepay" name="de_easy_pays[]" value="nicepay_applepay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_applepay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_applepay">APPLEPAY (애플페이)</label><br>
                <input type="checkbox" id="de_easy_nicepay_paycopay" name="de_easy_pays[]" value="nicepay_paycopay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_paycopay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_paycopay">페이코</label><br>
                <input type="checkbox" id="de_easy_nicepay_skpay" name="de_easy_pays[]" value="nicepay_skpay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_skpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_skpay">SK페이</label><br>
                <input type="checkbox" id="de_easy_nicepay_ssgpay" name="de_easy_pays[]" value="nicepay_ssgpay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_ssgpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_ssgpay">SSG페이</label><br>
                <input type="checkbox" id="de_easy_nicepay_lpay" name="de_easy_pays[]" value="nicepay_lpay" <?php if(stripos($default['de_easy_pay_services'], 'nicepay_lpay') !== false){ echo 'checked="checked"'; } ?> > <label for="de_easy_nicepay_lpay">LPAY</label>
            </td>
        </tr>

        <?php if (defined('G5_SHOP_DIRECT_NAVERPAY') && G5_SHOP_DIRECT_NAVERPAY) { ?>
        <tr class="naver_info_fld">
            <th scope="row">
                <label for="de_naverpay_mid">네이버페이 가맹점 아이디</label>
                <a href="http://sir.kr/main/service/naverpay.php" target="_blank" class="naver_btn">네이버페이 서비스신청하기</a>
            </th>
            <td>
                <?php echo help("네이버페이 가맹점 아이디를 입력합니다."); ?>
                <input type="text" name="de_naverpay_mid" value="<?php echo get_sanitize_input($default['de_naverpay_mid']); ?>" id="de_naverpay_mid" class="frm_input" size="20" maxlength="50">
             </td>
        </tr>
        <tr class="naver_info_fld">
            <th scope="row">
                <label for="de_naverpay_cert_key">네이버페이 가맹점 인증키</label>
            </th>
            <td>
                <?php echo help("네이버페이 가맹점 인증키를 입력합니다."); ?>
                <input type="text" name="de_naverpay_cert_key" value="<?php echo get_sanitize_input($default['de_naverpay_cert_key']); ?>" id="de_naverpay_cert_key" class="frm_input" size="50" maxlength="100">
             </td>
        </tr>
        <tr class="naver_info_fld">
            <th scope="row">
                <label for="de_naverpay_button_key">네이버페이 버튼 인증키</label>
            </th>
            <td>
                <?php echo help("네이버페이 버튼 인증키를 입력합니다."); ?>
                <input type="text" name="de_naverpay_button_key" value="<?php echo get_sanitize_input($default['de_naverpay_button_key']); ?>" id="de_naverpay_button_key" class="frm_input" size="50" maxlength="100">
             </td>
        </tr>
        <tr class="naver_info_fld">
            <th scope="row"><label for="de_naverpay_test">네이버페이 결제테스트</label></th>
            <td>
                <?php echo help("네이버페이 결제테스트 여부를 설정합니다. 검수 과정 중에는 <strong>예</strong>로 설정해야 하며 최종 승인 후 <strong>아니오</strong>로 설정합니다."); ?>
                <select id="de_naverpay_test" name="de_naverpay_test">
                    <option value="1" <?php echo get_selected($default['de_naverpay_test'], 1); ?>>예</option>
                    <option value="0" <?php echo get_selected($default['de_naverpay_test'], 0); ?>>아니오</option>
                </select>
            </td>
        </tr>
        <tr class="naver_info_fld">
            <th scope="row">
                <label for="de_naverpay_mb_id">네이버페이 결제테스트 아이디</label>
            </th>
            <td>
                <?php echo help("네이버페이 결제테스트를 위한 테스트 회원 아이디를 입력합니다. 네이버페이 검수 과정에서 필요합니다."); ?>
                <input type="text" name="de_naverpay_mb_id" value="<?php echo get_sanitize_input($default['de_naverpay_mb_id']); ?>" id="de_naverpay_mb_id" class="frm_input" size="20" maxlength="20">
             </td>
        </tr>
        <tr class="naver_info_fld">
            <th scope="row">네이버페이 상품정보 XML URL</th>
            <td>
                <?php echo help("네이버페이에 상품정보를 XML 데이터로 제공하는 페이지입니다. 검수과정에서 아래의 URL 정보를 제공해야 합니다."); ?>
                <?php echo G5_SHOP_URL; ?>/naverpay/naverpay_item.php
             </td>
        </tr>
        <tr class="naver_info_fld">
            <th scope="row">
                <label for="de_naverpay_sendcost">네이버페이 추가배송비 안내</label>
            </th>
            <td>
                <?php echo help("네이버페이를 통한 결제 때 구매자에게 보여질 추가배송비 내용을 입력합니다.<br>예) 제주도 3,000원 추가, 제주도 외 도서·산간 지역 5,000원 추가"); ?>
                <input type="text" name="de_naverpay_sendcost" value="<?php echo get_sanitize_input($default['de_naverpay_sendcost']); ?>" id="de_naverpay_sendcost" class="frm_input" size="70">
             </td>
        </tr>
        <?php } // defined('G5_SHOP_DIRECT_NAVERPAY') ?>
        <tr>
            <th scope="row">에스크로 사용</th>
            <td>
                <?php echo help("에스크로 결제를 사용하시려면, 반드시 결제대행사 상점 관리자 페이지에서 에스크로 서비스를 신청하신 후 사용하셔야 합니다.\n에스크로 사용시 배송과의 연동은 되지 않으며 에스크로 결제만 지원됩니다."); ?>
                    <input type="radio" name="de_escrow_use" value="0" <?php echo $default['de_escrow_use']==0?"checked":""; ?> id="de_escrow_use1">
                    <label for="de_escrow_use1">일반결제 사용</label>
                    <input type="radio" name="de_escrow_use" value="1" <?php echo $default['de_escrow_use']==1?"checked":""; ?> id="de_escrow_use2">
                    <label for="de_escrow_use2"> 에스크로결제 사용</label>
            </td>
        </tr>
        <tr>
            <th scope="row">결제 테스트</th>
            <td>
                <?php echo help("PG사의 결제 테스트를 하실 경우에 체크하세요. 결제단위 최소 1,000원"); ?>
                <input type="radio" name="de_card_test" value="0" <?php echo $default['de_card_test']==0?"checked":""; ?> id="de_card_test1">
                <label for="de_card_test1">실결제 </label>
                <input type="radio" name="de_card_test" value="1" <?php echo $default['de_card_test']==1?"checked":""; ?> id="de_card_test2">
                <label for="de_card_test2">테스트결제</label>
                <div class="scf_cardtest kcp_cardtest">
                    <a href="http://admin.kcp.co.kr/" target="_blank" class="btn_frmline">실결제 관리자</a>
                    <a href="http://testadmin8.kcp.co.kr/" target="_blank" class="btn_frmline">테스트 관리자</a>
                </div>
                <div class="scf_cardtest lg_cardtest">
                    <a href="https://pgweb.uplus.co.kr/" target="_blank" class="btn_frmline">실결제 관리자</a>
                    <a href="https://pgweb.uplus.co.kr/tmert" target="_blank" class="btn_frmline">테스트 관리자</a>
                </div>
                <div class="scf_cardtest inicis_cardtest">
                    <a href="https://iniweb.inicis.com/" target="_blank" class="btn_frmline">상점 관리자</a>
                </div>
                <div id="scf_cardtest_tip">
                    <strong>일반결제 사용시 테스트 결제</strong>
                    <dl>
                        <dt>신용카드</dt><dd>1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다.<br>(BC, 현대, 롯데, 삼성카드)</dd>
                        <dt>계좌이체</dt><dd>150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.</dd>
                        <dt>가상계좌</dt><dd>1원 이상, 모든 은행이 테스트 되는 것은 아니며 "해당 은행 계좌 없음" 자주 발생함.<br>(광주은행, 하나은행)</dd>
                        <dt>휴대폰</dt><dd>1004원, 실결제가 되며 다음날 새벽에 일괄 취소됨</dd>
                    </dl>
                    <strong>에스크로 사용시 테스트 결제</strong><br>
                    <dl>
                        <dt>신용카드</dt><dd>1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다.<br>(BC, 현대, 롯데, 삼성카드)</dd>
                        <dt>계좌이체</dt><dd>150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.</dd>
                        <dt>가상계좌</dt><dd>1원 이상, 입금통보는 제대로 되지 않음.</dd>
                        <dt>휴대폰</dt><dd>테스트 지원되지 않음.</dd>
                    </dl>
                    <ul id="kcp_cardtest_tip" class="scf_cardtest_tip_adm scf_cardtest_tip_adm_hide">
                        <li>테스트결제의 <a href="http://testadmin8.kcp.co.kr/assist/login.LoginAction.do" target="_blank">상점관리자</a> 로그인 정보는 NHN KCP로 문의하시기 바랍니다. (기술지원 1544-8661)</li>
                        <li><b>일반결제</b>의 테스트 사이트코드는 <b>T0000</b> 이며, <b>에스크로 결제</b>의 테스트 사이트코드는 <b>T0007</b> 입니다.</li>
                    </ul>
                    <ul id="lg_cardtest_tip" class="scf_cardtest_tip_adm scf_cardtest_tip_adm_hide">
                        <li>테스트결제의 <a href="http://pgweb.dacom.net:7085/" target="_blank">상점관리자</a> 로그인 정보는 토스페이먼츠 상점아이디 첫 글자에 t를 추가해서 로그인하시기 바랍니다. 예) tsi_lguplus</li>
                    </ul>
                    <ul id="inicis_cardtest_tip" class="scf_cardtest_tip_adm scf_cardtest_tip_adm_hide">
                        <li><b>일반결제</b>의 테스트 사이트 mid는 <b>INIpayTest</b> 이며, <b>에스크로 결제</b>의 테스트 사이트 mid는 <b>iniescrow0</b> 입니다.</li>
                    </ul>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_tax_flag_use">복합과세 결제</label></th>
            <td>
                 <?php echo help("복합과세(과세, 비과세) 결제를 사용하려면 체크하십시오.\n복합과세 결제를 사용하기 전 PG사에 별도로 결제 신청을 해주셔야 합니다. 사용시 PG사로 문의하여 주시기 바랍니다."); ?>
                <input type="checkbox" name="de_tax_flag_use" value="1" id="de_tax_flag_use"<?php echo $default['de_tax_flag_use']?' checked':''; ?>> 사용
            </td>
        </tr>
        </tbody>
        </table>
        <script>
        $('#scf_cardtest_tip').addClass('scf_cardtest_tip');
        $('<button type="button" class="scf_cardtest_btn btn_frmline">테스트결제 팁 더보기</button>').appendTo('.scf_cardtest');

        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $(".<?php echo $default['de_pg_service']; ?>_cardtest").removeClass("scf_cardtest_hide");
        $("#<?php echo $default['de_pg_service']; ?>_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
        </script>
    </div>
</section>


<section id="anc_scf_delivery">
    <h2 >배송설정</h2>
     <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>배송설정 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="de_delivery_company">배송업체</label></th>
            <td>
                <?php echo help("이용 중이거나 이용하실 배송업체를 선택하세요."); ?>
                <select name="de_delivery_company" id="de_delivery_company">
                    <?php echo get_delivery_company($default['de_delivery_company']); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_send_cost_case">배송비유형</label></th>
            <td>
                <?php echo help("<strong>금액별차등</strong>으로 설정한 경우, 주문총액이 배송비상한가 미만일 경우 배송비를 받습니다.\n<strong>무료배송</strong>으로 설정한 경우, 배송비상한가 및 배송비를 무시하며 착불의 경우도 무료배송으로 설정합니다.\n<strong>상품별로 배송비 설정을 한 경우 상품별 배송비 설정이 우선</strong> 적용됩니다.\n예를 들어 무료배송으로 설정했을 때 특정 상품에 배송비가 설정되어 있으면 주문시 배송비가 부과됩니다."); ?>
                <select name="de_send_cost_case" id="de_send_cost_case">
                    <option value="차등" <?php echo get_selected($default['de_send_cost_case'], "차등"); ?>>금액별차등</option>
                    <option value="무료" <?php echo get_selected($default['de_send_cost_case'], "무료"); ?>>무료배송</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_send_cost_limit">배송비상한가</label></th>
            <td>
                <?php echo help("배송비유형이 '금액별차등'일 경우에만 해당되며 배송비상한가를 여러개 두고자 하는 경우는 <b>;</b> 로 구분합니다.\n\n예를 들어 20000원 미만일 경우 4000원, 30000원 미만일 경우 3000원 으로 사용할 경우에는 배송비상한가를 20000;30000 으로 입력하고 배송비를 4000;3000 으로 입력합니다."); ?>
                <input type="text" name="de_send_cost_limit" value="<?php echo get_sanitize_input($default['de_send_cost_limit']); ?>" size="40" class="frm_input" id="de_send_cost_limit"> 원
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_send_cost_list">배송비</label></th>
            <td>
                <input type="text" name="de_send_cost_list" value="<?php echo get_sanitize_input($default['de_send_cost_list']); ?>" size="40" class="frm_input" id="de_send_cost_list"> 원
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_hope_date_use">희망배송일사용</label></th>
            <td>
                <?php echo help("'예'로 설정한 경우 주문서에서 희망배송일을 입력 받습니다."); ?>
                <select name="de_hope_date_use" id="de_hope_date_use">
                    <option value="0" <?php echo get_selected($default['de_hope_date_use'], 0); ?>>사용안함</option>
                    <option value="1" <?php echo get_selected($default['de_hope_date_use'], 1); ?>>사용</option>
                </select>
            </td>
        </tr>
        <tr>
             <th scope="row"><label for="de_hope_date_after">희망배송일지정</label></th>
            <td>
                <?php echo help("오늘을 포함하여 설정한 날 이후부터 일주일 동안을 달력 형식으로 노출하여 선택할수 있도록 합니다."); ?>
                <input type="text" name="de_hope_date_after" value="<?php echo get_sanitize_input($default['de_hope_date_after']); ?>" id="de_hope_date_after" class="frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row">배송정보</th>
            <td><?php echo editor_html('de_baesong_content', get_text(html_purifier($default['de_baesong_content']), 0)); ?></td>
        </tr>
        <tr>
            <th scope="row">교환/반품</th>
            <td><?php echo editor_html('de_change_content', get_text(html_purifier($default['de_change_content']), 0)); ?></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_scf_etc">
    <h2 class="h2_frm">기타 설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기타 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">관련상품출력</th>
            <td>
                <?php echo help("관련상품의 경우 등록된 상품은 모두 출력하므로 '출력할 줄 수'는 설정하지 않습니다. 이미지높이를 0으로 설정하면 상품이미지를 이미지폭에 비례하여 생성합니다."); ?>
                <label for="de_rel_list_skin">스킨</label>
                <select name="de_rel_list_skin" id="de_rel_list_skin">
                    <?php echo get_list_skin_options("^relation.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_rel_list_skin']); ?>
                </select>
                <label for="de_rel_img_width">이미지폭</label>
                <input type="text" name="de_rel_img_width" value="<?php echo get_sanitize_input($default['de_rel_img_width']); ?>" id="de_rel_img_width" class="frm_input" size="3">
                <label for="de_rel_img_height">이미지높이</label>
                <input type="text" name="de_rel_img_height" value="<?php echo get_sanitize_input($default['de_rel_img_height']); ?>" id="de_rel_img_height" class="frm_input" size="3">
                <label for="de_rel_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_rel_list_mod" value="<?php echo get_sanitize_input($default['de_rel_list_mod']); ?>" id="de_rel_list_mod" class="frm_input" size="3">
                <label for="de_rel_list_use">출력</label>
                <input type="checkbox" name="de_rel_list_use" value="1" id="de_rel_list_use" <?php echo $default['de_rel_list_use']?"checked":""; ?>>
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 관련상품출력</th>
            <td>
                <?php echo help("관련상품의 경우 등록된 상품은 모두 출력하므로 '출력할 줄 수'는 설정하지 않습니다. 이미지높이를 0으로 설정하면 상품이미지를 이미지폭에 비례하여 생성합니다."); ?>
                <label for="de_mobile_rel_list_skin">스킨</label>
                <select name="de_mobile_rel_list_skin" id="de_mobile_rel_list_skin">
                    <?php echo get_list_skin_options("^relation.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_rel_list_skin']); ?>
                </select>
                <label for="de_mobile_rel_img_width">이미지폭</label>
                <input type="text" name="de_mobile_rel_img_width" value="<?php echo get_sanitize_input($default['de_mobile_rel_img_width']); ?>" id="de_mobile_rel_img_width" class="frm_input" size="3">
                <label for="de_mobile_rel_img_height">이미지높이</label>
                <input type="text" name="de_mobile_rel_img_height" value="<?php echo get_sanitize_input($default['de_mobile_rel_img_height']); ?>" id="de_mobile_rel_img_height" class="frm_input" size="3">
                <label for="de_mobile_rel_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_rel_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_rel_list_mod']); ?>" id="de_mobile_rel_list_mod" class="frm_input" size="3">
                <label for="de_mobile_rel_list_use">출력</label>
                <input type="checkbox" name="de_mobile_rel_list_use" value="1" id="de_mobile_rel_list_use" <?php echo $default['de_mobile_rel_list_use']?"checked":""; ?>>
            </td>
        </tr>
        <tr>
            <th scope="row">검색상품출력</th>
            <td>
                <label for="de_search_list_skin">스킨</label>
                <select name="de_search_list_skin" id="de_search_list_skin">
                    <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_search_list_skin']); ?>
                </select>
                <label for="de_search_img_width">이미지폭</label>
                <input type="text" name="de_search_img_width" value="<?php echo get_sanitize_input($default['de_search_img_width']); ?>" id="de_search_img_width" class="frm_input" size="3">
                <label for="de_search_img_height">이미지높이</label>
                <input type="text" name="de_search_img_height" value="<?php echo get_sanitize_input($default['de_search_img_height']); ?>" id="de_search_img_height" class="frm_input" size="3">
                <label for="de_search_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_search_list_mod" value="<?php echo get_sanitize_input($default['de_search_list_mod']); ?>" id="de_search_list_mod" class="frm_input" size="3">
                <label for="de_search_list_row">출력할 줄 수</label>
                <input type="text" name="de_search_list_row" value="<?php echo get_sanitize_input($default['de_search_list_row']); ?>" id="de_search_list_row" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 검색상품출력</th>
            <td>
                <label for="de_mobile_search_list_skin">스킨</label>
                <select name="de_mobile_search_list_skin" id="de_mobile_search_list_skin">
                    <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_search_list_skin']); ?>
                </select>
                <label for="de_mobile_search_img_width">이미지폭</label>
                <input type="text" name="de_mobile_search_img_width" value="<?php echo get_sanitize_input($default['de_mobile_search_img_width']); ?>" id="de_mobile_search_img_width" class="frm_input" size="3">
                <label for="de_mobile_search_img_height">이미지높이</label>
                <input type="text" name="de_mobile_search_img_height" value="<?php echo get_sanitize_input($default['de_mobile_search_img_height']); ?>" id="de_mobile_search_img_height" class="frm_input" size="3">
                <label for="de_mobile_search_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_search_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_search_list_mod']); ?>" id="de_mobile_search_list_mod" class="frm_input" size="3">
                <label for="de_mobile_search_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_search_list_row" value="<?php echo get_sanitize_input($default['de_mobile_search_list_row']); ?>" id="de_mobile_search_list_row" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">유형별 상품리스트</th>
            <td>
                <label for="de_listtype_list_skin">스킨</label>
                <select name="de_listtype_list_skin" id="de_listtype_list_skin">
                    <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", G5_SHOP_SKIN_PATH, $default['de_listtype_list_skin']); ?>
                </select>
                <label for="de_listtype_img_width">이미지폭</label>
                <input type="text" name="de_listtype_img_width" value="<?php echo get_sanitize_input($default['de_listtype_img_width']); ?>" id="de_listtype_img_width" class="frm_input" size="3">
                <label for="de_listtype_img_height">이미지높이</label>
                <input type="text" name="de_listtype_img_height" value="<?php echo get_sanitize_input($default['de_listtype_img_height']); ?>" id="de_listtype_img_height" class="frm_input" size="3">
                <label for="de_listtype_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_listtype_list_mod" value="<?php echo get_sanitize_input($default['de_listtype_list_mod']); ?>" id="de_listtype_list_mod" class="frm_input" size="3">
                <label for="de_listtype_list_row">출력할 줄 수</label>
                <input type="text" name="de_listtype_list_row" value="<?php echo get_sanitize_input($default['de_listtype_list_row']); ?>" id="de_listtype_list_row" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 유형별 상품리스트</th>
            <td>
                <label for="de_mobile_listtype_list_skin">스킨</label>
                <select name="de_mobile_listtype_list_skin" id="de_mobile_listtype_list_skin">
                    <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", G5_MSHOP_SKIN_PATH, $default['de_mobile_listtype_list_skin']); ?>
                </select>
                <label for="de_mobile_listtype_img_width">이미지폭</label>
                <input type="text" name="de_mobile_listtype_img_width" value="<?php echo get_sanitize_input($default['de_mobile_listtype_img_width']); ?>" id="de_mobile_listtype_img_width" class="frm_input" size="3">
                <label for="de_mobile_listtype_img_height">이미지높이</label>
                <input type="text" name="de_mobile_listtype_img_height" value="<?php echo get_sanitize_input($default['de_mobile_listtype_img_height']); ?>" id="de_mobile_listtype_img_height" class="frm_input" size="3">
                <label for="de_mobile_listtype_list_mod">1줄당 이미지 수</label>
                <input type="text" name="de_mobile_listtype_list_mod" value="<?php echo get_sanitize_input($default['de_mobile_listtype_list_mod']); ?>" id="de_mobile_listtype_list_mod" class="frm_input" size="3">
                <label for="de_mobile_listtype_list_row">출력할 줄 수</label>
                <input type="text" name="de_mobile_listtype_list_row" value="<?php echo get_sanitize_input($default['de_mobile_listtype_list_row']); ?>" id="de_mobile_listtype_list_row" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">이미지(소)</th>
            <td>
                <?php echo help("분류리스트에서 보여지는 사이즈를 설정하시면 됩니다. 분류관리의 출력 이미지폭, 높이의 기본값으로 사용됩니다. 높이를 0 으로 설정하시면 폭에 비례하여 높이를 썸네일로 생성합니다."); ?>
                <label for="de_simg_width"><span class="sound_only">이미지(소) </span>폭</label>
                <input type="text" name="de_simg_width" value="<?php echo get_sanitize_input($default['de_simg_width']); ?>" id="de_simg_width" class="frm_input" size="5"> 픽셀
                /
                <label for="de_simg_height"><span class="sound_only">이미지(소) </span>높이</label>
                <input type="text" name="de_simg_height" value="<?php echo get_sanitize_input($default['de_simg_height']); ?>" id="de_simg_height" class="frm_input" size="5"> 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row">이미지(중)</th>
            <td>
                <?php echo help("상품상세보기에서 보여지는 상품이미지의 사이즈를 픽셀로 설정합니다. 높이를 0 으로 설정하시면 폭에 비례하여 높이를 썸네일로 생성합니다."); ?>
                <label for="de_mimg_width"><span class="sound_only">이미지(중) </span>폭</label>
                <input type="text" name="de_mimg_width" value="<?php echo get_sanitize_input($default['de_mimg_width']); ?>" id="de_mimg_width" class="frm_input" size="5"> 픽셀
                /
                <label for="de_mimg_height"><span class="sound_only">이미지(중) </span>높이</label>
                <input type="text" name="de_mimg_height" value="<?php echo get_sanitize_input($default['de_mimg_height']); ?>" id="de_mimg_height" class="frm_input" size="5"> 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row">상단로고이미지</th>
            <td>
                <?php echo help("쇼핑몰 상단로고를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
                <input type="file" name="logo_img" id="logo_img">
                <?php
                $logo_img = G5_DATA_PATH."/common/logo_img";
                if (file_exists($logo_img))
                {
                    $size = getimagesize($logo_img);
                ?>
                <input type="checkbox" name="logo_img_del" value="1" id="logo_img_del">
                <label for="logo_img_del"><span class="sound_only">상단로고이미지</span> 삭제</label>
                <span class="scf_img_logoimg"></span>
                <div id="logoimg" class="banner_or_img">
                    <img src="<?php echo G5_DATA_URL; ?>/common/logo_img" alt="">
                    <button type="button" class="sit_wimg_close">닫기</button>
                </div>
                <script>
                $('<button type="button" id="cf_logoimg_view" class="btn_frmline scf_img_view">상단로고이미지 확인</button>').appendTo('.scf_img_logoimg');
                </script>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">하단로고이미지</th>
            <td>
                <?php echo help("쇼핑몰 하단로고를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
                <input type="file" name="logo_img2" id="logo_img2">
                <?php
                $logo_img2 = G5_DATA_PATH."/common/logo_img2";
                if (file_exists($logo_img2))
                {
                    $size = getimagesize($logo_img2);
                ?>
                <input type="checkbox" name="logo_img_del2" value="1" id="logo_img_del2">
                <label for="logo_img_del2"><span class="sound_only">하단로고이미지</span> 삭제</label>
                <span class="scf_img_logoimg2"></span>
                <div id="logoimg2" class="banner_or_img">
                    <img src="<?php echo G5_DATA_URL; ?>/common/logo_img2" alt="">
                    <button type="button" class="sit_wimg_close">닫기</button>
                </div>
                <script>
                $('<button type="button" id="cf_logoimg2_view" class="btn_frmline scf_img_view">하단로고이미지 확인</button>').appendTo('.scf_img_logoimg2');
                </script>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 상단로고이미지</th>
            <td>
                <?php echo help("모바일 쇼핑몰 상단로고를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
                <input type="file" name="mobile_logo_img" id="mobile_logo_img">
                <?php
                $mobile_logo_img = G5_DATA_PATH."/common/mobile_logo_img";
                if (file_exists($mobile_logo_img))
                {
                    $size = getimagesize($mobile_logo_img);
                ?>
                <input type="checkbox" name="mobile_logo_img_del" value="1" id="mobile_logo_img_del">
                <label for="mobile_logo_img_del"><span class="sound_only">모바일 상단로고이미지</span> 삭제</label>
                <span class="scf_img_mobilelogoimg"></span>
                <div id="mobilelogoimg" class="banner_or_img">
                    <img src="<?php echo G5_DATA_URL; ?>/common/mobile_logo_img" alt="">
                    <button type="button" class="sit_wimg_close">닫기</button>
                </div>
                <script>
                $('<button type="button" id="cf_mobilelogoimg_view" class="btn_frmline scf_img_view">모바일 상단로고이미지 확인</button>').appendTo('.scf_img_mobilelogoimg');
                </script>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 하단로고이미지</th>
            <td>
                <?php echo help("모바일 쇼핑몰 하단로고를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
                <input type="file" name="mobile_logo_img2" id="mobile_logo_img2">
                <?php
                $mobile_logo_img2 = G5_DATA_PATH."/common/mobile_logo_img2";
                if (file_exists($mobile_logo_img2))
                {
                    $size = getimagesize($mobile_logo_img2);
                ?>
                <input type="checkbox" name="mobile_logo_img_del2" value="1" id="mobile_logo_img_del2">
                <label for="mobile_logo_img_del2"><span class="sound_only">모바일 하단로고이미지</span> 삭제</label>
                <span class="scf_img_mobilelogoimg2"></span>
                <div id="mobilelogoimg2" class="banner_or_img">
                    <img src="<?php echo G5_DATA_URL; ?>/common/mobile_logo_img2" alt="">
                    <button type="button" class="sit_wimg_close">닫기</button>
                </div>
                <script>
                $('<button type="button" id="cf_mobilelogoimg2_view" class="btn_frmline scf_img_view">모바일 하단로고이미지 확인</button>').appendTo('.scf_img_mobilelogoimg2');
                </script>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_item_use_write">사용후기 작성</label></th>
            <td>
                 <?php echo help("주문상태에 따른 사용후기 작성여부를 설정합니다.", 50); ?>
                <select name="de_item_use_write" id="de_item_use_write">
                    <option value="0" <?php echo get_selected($default['de_item_use_write'], 0); ?>>주문상태와 무관하게 작성가능</option>
                    <option value="1" <?php echo get_selected($default['de_item_use_write'], 1); ?>>주문상태가 완료인 경우에만 작성가능</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_item_use_use">사용후기</label></th>
            <td>
                 <?php echo help("사용후기가 올라오면, 즉시 출력 혹은 관리자 승인 후 출력 여부를 설정합니다.", 50); ?>
                <select name="de_item_use_use" id="de_item_use_use">
                    <option value="0" <?php echo get_selected($default['de_item_use_use'], 0); ?>>즉시 출력</option>
                    <option value="1" <?php echo get_selected($default['de_item_use_use'], 1); ?>>관리자 승인 후 출력</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_level_sell">상품구입 권한</label></th>
            <td>
                <?php echo help("권한을 1로 설정하면 누구나 구입할 수 있습니다. 특정회원만 구입할 수 있도록 하려면 해당 권한으로 설정하십시오."); ?>
                <?php echo get_member_level_select('de_level_sell', 1, 10, $default['de_level_sell']); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_cart_keep_term">장바구니 보관기간</label></th>
            <td>
                 <?php echo help("장바구니 상품의 보관 기간을 설정하십시오."); ?>
                <input type="text" name="de_cart_keep_term" value="<?php echo get_sanitize_input($default['de_cart_keep_term']); ?>" id="de_cart_keep_term" class="frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_guest_cart_use">비회원 장바구니</label></th>
            <td>
                 <?php echo help("비회원 장바구니 기능을 사용하려면 체크하십시오."); ?>
                <input type="checkbox" name="de_guest_cart_use" value="1" id="de_guest_cart_use"<?php echo $default['de_guest_cart_use']?' checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row">신규회원 쿠폰발행</th>
            <td>
                 <?php echo help("신규회원에게 주문금액 할인 쿠폰을 발행하시려면 아래를 설정하십시오."); ?>
                <label for="de_member_reg_coupon_use">쿠폰발행</label>
                <input type="checkbox" name="de_member_reg_coupon_use" value="1" id="de_member_reg_coupon_use"<?php echo $default['de_member_reg_coupon_use']?' checked':''; ?>>
                <label for="de_member_reg_coupon_price">쿠폰할인금액</label>
                <input type="text" name="de_member_reg_coupon_price" value="<?php echo get_sanitize_input($default['de_member_reg_coupon_price']); ?>" id="de_member_reg_coupon_price" class="frm_input" size="10"> 원
                <label for="de_member_reg_coupon_minimum">주문최소금액</label>
                <input type="text" name="de_member_reg_coupon_minimum" value="<?php echo get_sanitize_input($default['de_member_reg_coupon_minimum']); ?>" id="de_member_reg_coupon_minimum" class="frm_input" size="10"> 원이상
                <label for="de_member_reg_coupon_term">쿠폰유효기간</label>
                <input type="text" name="de_member_reg_coupon_term" value="<?php echo get_sanitize_input($default['de_member_reg_coupon_term']); ?>" id="de_member_reg_coupon_term" class="frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row">비회원에 대한<br/>개인정보수집 내용</th>
            <td><?php echo editor_html('de_guest_privacy', get_text(html_purifier($default['de_guest_privacy']), 0)); ?></td>
        </tr>
        <tr>
            <th scope="row">MYSQL USER</th>
            <td><?php echo G5_MYSQL_USER; ?></td>
        </tr>
        <tr>
            <th scope="row">MYSQL DB</th>
            <td><?php echo G5_MYSQL_DB; ?></td>
        </tr>
        <tr>
            <th scope="row">서버 IP</th>
            <td><?php echo ($_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:$_SERVER['LOCAL_ADDR']); ?></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<button type="button" class="shop_etc">테마설정 가져오기</button>

<?php if (file_exists($logo_img) || file_exists($logo_img2) || file_exists($mobile_logo_img) || file_exists($mobile_logo_img2)) { ?>
<script>
$(".banner_or_img").addClass("scf_img");
$(function() {
    $(".scf_img_view").bind("click", function() {
        var sit_wimg_id = $(this).attr("id").split("_");
        var $img_display = $("#"+sit_wimg_id[1]);

        $img_display.toggle();

        if($img_display.is(":visible")) {
            $(this).text($(this).text().replace("확인", "닫기"));
        } else {
            $(this).text($(this).text().replace("닫기", "확인"));
        }

        if(sit_wimg_id[1].search("mainimg") > -1) {
            var $img = $("#"+sit_wimg_id[1]).children("img");
            var width = $img.width();
            var height = $img.height();
            if(width > 700) {
                var img_width = 700;
                var img_height = Math.round((img_width * height) / width);

                $img.width(img_width).height(img_height);
            }
        }
    });
    $(".sit_wimg_close").bind("click", function() {
        var $img_display = $(this).parents(".banner_or_img");
        var id = $img_display.attr("id");
        $img_display.toggle();
        var $button = $("#cf_"+id+"_view");
        $button.text($button.text().replace("닫기", "확인"));
    });
});
</script>
<?php } ?>

<script>
function byte_check(el_cont, el_byte)
{
    var cont = document.getElementById(el_cont);
    var bytes = document.getElementById(el_byte);
    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';
    var limit_num = (jQuery("#cf_sms_type").val() == "LMS") ? 1500 : 80;

    if( $("input[name='cf_icode_token_key']").length && $("input[name='cf_icode_token_key']").val() && jQuery("#cf_sms_type").val() == "LMS" ){
        limit_num = 2000;
    }

    for (i=0; i<cont.value.length; i++) {
        ch = cont.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    //byte.value = cnt + ' / 80 bytes';
    bytes.innerHTML = cnt + ' / ' + limit_num +' bytes';

    if (cnt > limit_num) {
        exceed = cnt - limit_num;
        alert('메시지 내용은 ' + limit_num +' 바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = cont.value;
        for (i=0; i<tmp.length; i++) {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > limit_num) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        cont.value = tmp;
        //byte.value = xcnt + ' / 80 bytes';
        bytes.innerHTML = xcnt + ' / ' + limit_num +' bytes';
        return;
    }
}
</script>

<section id="anc_scf_sms" >
    <h2 class="h2_frm">SMS 설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>SMS 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_sms_use">SMS 사용</label></th>
            <td>
                <?php echo help("SMS  서비스 회사를 선택하십시오. 서비스 회사를 선택하지 않으면, SMS 발송 기능이 동작하지 않습니다.<br>아이코드는 무료 문자메세지 발송 테스트 환경을 지원합니다.<br><a href=\"".G5_ADMIN_URL."/config_form.php#anc_cf_sms\">기본환경설정 &gt; SMS</a> 설정과 동일합니다."); ?>
                <select id="cf_sms_use" name="cf_sms_use">
                    <option value="" <?php echo get_selected($config['cf_sms_use'], ''); ?>>사용안함</option>
                    <option value="icode" <?php echo get_selected($config['cf_sms_use'], 'icode'); ?>>아이코드</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_sms_type">SMS 전송유형</label></th>
            <td>
                <?php echo help("전송유형을 SMS로 선택하시면 최대 80바이트까지 전송하실 수 있으며<br>LMS로 선택하시면 90바이트 이하는 SMS로, 그 이상은 1500바이트까지 LMS로 전송됩니다.<br>요금은 건당 SMS는 16원, LMS는 48원입니다."); ?>
                <select id="cf_sms_type" name="cf_sms_type">
                    <option value="" <?php echo get_selected($config['cf_sms_type'], ''); ?>>SMS</option>
                    <option value="LMS" <?php echo get_selected($config['cf_sms_type'], 'LMS'); ?>>LMS</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="de_sms_hp">관리자 휴대폰번호</label></th>
            <td>
                <?php echo help("주문서작성시 쇼핑몰관리자가 문자메세지를 받아볼 번호를 숫자만으로 입력하세요. 예) 0101234567"); ?>
                <input type="text" name="de_sms_hp" value="<?php echo get_sanitize_input($default['de_sms_hp']); ?>" id="de_sms_hp" class="frm_input" size="20">
            </td>
        </tr>
        <tr class="icode_old_version">
            <th scope="row"><label for="cf_icode_id">아이코드 회원아이디<br>(구버전)</label></th>
            <td>
                <?php echo help("아이코드에서 사용하시는 회원아이디를 입력합니다."); ?>
                <input type="text" name="cf_icode_id" value="<?php echo get_sanitize_input($config['cf_icode_id']); ?>" id="cf_icode_id" class="frm_input" size="20">
            </td>
        </tr>
        <tr class="icode_old_version">
            <th scope="row"><label for="cf_icode_pw">아이코드 비밀번호<br>(구버전)</label></th>
            <td>
                <?php echo help("아이코드에서 사용하시는 비밀번호를 입력합니다."); ?>
                <input type="password" name="cf_icode_pw" value="<?php echo get_sanitize_input($config['cf_icode_pw']); ?>" class="frm_input" id="cf_icode_pw">
            </td>
        </tr>
        <tr class="icode_old_version <?php if(!(isset($userinfo['payment']) && $userinfo['payment'])){ echo 'cf_tr_hide'; } ?>">
            <th scope="row">요금제<br>(구버전)</th>
            <td>
                <input type="hidden" name="cf_icode_server_ip" value="<?php echo get_sanitize_input($config['cf_icode_server_ip']); ?>">
                <?php
                    if ($userinfo['payment'] == 'A') {
                       echo '충전제';
                        echo '<input type="hidden" name="cf_icode_server_port" value="7295">';
                    } else if ($userinfo['payment'] == 'C') {
                        echo '정액제';
                        echo '<input type="hidden" name="cf_icode_server_port" value="7296">';
                    } else {
                        echo '가입해주세요.';
                        echo '<input type="hidden" name="cf_icode_server_port" value="7295">';
                    }
                ?>
            </td>
        </tr>
        <?php if ($userinfo['payment'] == 'A') { ?>
        <tr class="icode_old_version">
            <th scope="row">충전 잔액</th>
            <td>
                <?php echo number_format($userinfo['coin']); ?> 원.
                <a href="http://www.icodekorea.com/smsbiz/credit_card_amt.php?icode_id=<?php echo $config['cf_icode_id']; ?>&amp;icode_passwd=<?php echo $config['cf_icode_pw']; ?>" target="_blank" class="btn_frmline" onclick="window.open(this.href,'icode_payment', 'scrollbars=1,resizable=1'); return false;">충전하기</a>
            </td>
        </tr>
        <?php } ?>
        <tr class="icode_json_version">
            <th scope="row"><label for="cf_icode_token_key">아이코드 토큰키<br>(JSON버전)</label></th>
            <td>
                <?php echo help("아이코드 JSON 버전의 경우 아이코드 토큰키를 입력시 실행됩니다.<br>SMS 전송유형을 LMS로 설정시 90바이트 이내는 SMS, 90 ~ 2000 바이트는 LMS 그 이상은 절삭 되어 LMS로 발송됩니다."); ?>
                <input type="text" name="cf_icode_token_key" value="<?php echo get_sanitize_input($config['cf_icode_token_key']); ?>" id="cf_icode_token_key" class="frm_input" size="40">
                <?php echo help("아이코드 사이트 -> 토큰키관리 메뉴에서 생성한 토큰키를 입력합니다."); ?>
                <br>
                서버아이피 : <?php echo $_SERVER['SERVER_ADDR']; ?>
            </td>
        </tr>
        <tr>
            <th scope="row">아이코드 SMS 신청<br>회원가입</th>
            <td>
                <?php echo help("아래 링크에서 회원가입 하시면 문자 건당 16원에 제공 받을 수 있습니다."); ?>
                <a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank" class="btn_frmline">아이코드 회원가입</a>
            </td>
        </tr>
         </tbody>
        </table>
    </div>

    <section id="scf_sms_pre">
        <h3>사전에 정의된 SMS프리셋</h3>
        <div class="local_desc01 local_desc">
            <dl>
                <dt>회원가입시</dt>
                <dd>{이름} {회원아이디} {회사명}</dd>
                <dt>주문서작성</dt>
                <dd>{이름} {보낸분} {받는분} {주문번호} {주문금액} {회사명}</dd>
                <dt>입금확인시</dt>
                <dd>{이름} {입금액} {주문번호} {회사명}</dd>
                <dt>상품배송시</dt>
                <dd>{이름} {택배회사} {운송장번호} {주문번호} {회사명}</dd>
            </dl>
           <p><?php echo help('주의! 80 bytes 까지만 전송됩니다. (영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 임)'); ?></p>
        </div>

        <div id="scf_sms">
            <?php
            $scf_sms_title = array (1=>"회원가입시 고객님께 발송", "주문시 고객님께 발송", "주문시 관리자에게 발송", "입금확인시 고객님께 발송", "상품배송시 고객님께 발송");
            for ($i=1; $i<=5; $i++) {
            ?>
            <section class="scf_sms_box">
                <h4><?php echo $scf_sms_title[$i]; ?></h4>
                <input type="checkbox" name="de_sms_use<?php echo $i; ?>" value="1" id="de_sms_use<?php echo $i; ?>" <?php echo ($default["de_sms_use".$i] ? " checked" : ""); ?>>
                <label for="de_sms_use<?php echo $i; ?>"><span class="sound_only"><?php echo $scf_sms_title[$i]; ?></span>사용</label>
                <div class="scf_sms_img">
                    <textarea id="de_sms_cont<?php echo $i; ?>" name="de_sms_cont<?php echo $i; ?>" ONKEYUP="byte_check('de_sms_cont<?php echo $i; ?>', 'byte<?php echo $i; ?>');"><?php echo html_purifier($default['de_sms_cont'.$i]); ?></textarea>
                </div>
                <span id="byte<?php echo $i; ?>" class="scf_sms_cnt">0 / 80 바이트</span>
            </section>

            <script>
            byte_check('de_sms_cont<?php echo $i; ?>', 'byte<?php echo $i; ?>');
            </script>
            <?php } ?>
        </div>
    </section>

</section>


<div class="btn_fixed_top">
    <a href=" <?php echo G5_SHOP_URL; ?>" class="btn btn_02">쇼핑몰</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
function fconfig_check(f)
{
    <?php echo get_editor_js('de_baesong_content'); ?>
    <?php echo get_editor_js('de_change_content'); ?>
    <?php echo get_editor_js('de_guest_privacy'); ?>
    
    var msg = "",
        pg_msg = "";

    if( f.de_pg_service.value == "kcp" ){
        if( f.de_kcp_mid.value && f.de_kcp_site_key.value && parseInt(f.de_card_test.value) > 0 ){
            pg_msg = "NHN KCP";
        }
    } else if ( f.de_pg_service.value == "lg" ) {
        if( f.cf_lg_mid.value && f.cf_lg_mert_key.value && parseInt(f.de_card_test.value) > 0 ){
            pg_msg = "토스페이먼츠";
        }
    } else if ( f.de_pg_service.value == "inicis" ) {
        if( f.de_inicis_mid.value && f.de_inicis_sign_key.value && parseInt(f.de_card_test.value) > 0 ){
            pg_msg = "KG이니시스";
        }
    } else if ( f.de_pg_service.value == "nicepay" ) {
        if( f.de_nicepay_mid.value && f.de_nicepay_key.value && parseInt(f.de_card_test.value) > 0 ){
            pg_msg = "NICEPAY";
        }
    }

    if( pg_msg ){
        msg += "(주의!) "+pg_msg+" 결제의 결제 설정이 현재 테스트결제 로 되어 있습니다.\n쇼핑몰 운영중이면 반드시 실결제로 설정하여 운영하셔야 합니다.\n실결제로 변경하려면 결제설정 탭 -> 결제 테스트에서 실결제를 선택해 주세요.\n정말로 테스트결제로 설정하시겠습니까?";
    }

    if( msg ){
        if (confirm(msg)){
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

$(function() {

    $(document).ready(function () {
        
        $("#de_global_nhnkcp_naverpay").on("click", function(e){
            if ( $(this).prop('checked') ) {
                $("#de_easy_nhnkcp_naverpay").prop('checked', true);
            }
        });

        function hash_goto_scroll(hash){
            var $elem = hash ? $("#"+hash) : $('#' + window.location.hash.replace('#', ''));
            if($elem.length) {

                var admin_head_height = $("#hd_top").height() + $("#container_title").height() + 30;

                $('html, body').animate({
                    scrollTop: ($elem.offset().top - admin_head_height) + 'px'
                }, 500, 'swing');
            }
        }

        hash_goto_scroll();
        
        $(document).on("click", ".pg_test_conf_link", function(e){
            e.preventDefault();

            var str_hash = this.href.split("#")[1];

            if( str_hash ){
                hash_goto_scroll(str_hash);
            }
        });
    });

    //$(".pg_info_fld").hide();
    $(".pg_vbank_url").hide();
    <?php if($default['de_pg_service']) { ?>
    //$(".<?php echo $default['de_pg_service']; ?>_info_fld").show();
    $("#<?php echo $default['de_pg_service']; ?>_vbank_url").show();
    <?php } else { ?>
    $(".kcp_info_fld").show();
    $("#kcp_vbank_url").show();
    <?php } ?>
    $(document).on("click", ".de_pg_tab a", function(e){

        var pg = $(this).attr("data-value"),
            class_name = "tab-current";

        $("#de_pg_service").val(pg);
        $(this).parent("li").addClass(class_name).siblings().removeClass(class_name);

        //$(".pg_info_fld:visible").hide();
        $(".pg_vbank_url:visible").hide();
        //$("."+pg+"_info_fld").show();
        $("#"+pg+"_vbank_url").show();
        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
    });

    $("#de_pg_service").on("change", function() {
        var pg = $(this).val();
        $(".pg_info_fld:visible").hide();
        $(".pg_vbank_url:visible").hide();
        $("."+pg+"_info_fld").show();
        $("#"+pg+"_vbank_url").show();
        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
    });

    $(".scf_cardtest_btn").bind("click", function() {
        var $cf_cardtest_tip = $("#scf_cardtest_tip");
        var $cf_cardtest_btn = $(".scf_cardtest_btn");

        $cf_cardtest_tip.toggle();

        if($cf_cardtest_tip.is(":visible")) {
            $cf_cardtest_btn.text("테스트결제 팁 닫기");
        } else {
            $cf_cardtest_btn.text("테스트결제 팁 더보기");
        }
    });

    $(".get_shop_skin").on("click", function() {
        if(!confirm("현재 테마의 쇼핑몰 스킨 설정을 적용하시겠습니까?"))
            return false;

        $.ajax({
            type: "POST",
            url: "../theme_config_load.php",
            cache: false,
            async: false,
            data: { type: "shop_skin" },
            dataType: "json",
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                    return false;
                }

                var field = Array('de_shop_skin', 'de_shop_mobile_skin');
                var count = field.length;
                var key;

                for(i=0; i<count; i++) {
                    key = field[i];

                    if(data[key] != undefined && data[key] != "")
                        $("select[name="+key+"]").val(data[key]);
                }
            }
        });
    });

    $(".shop_pc_index, .shop_mobile_index, .shop_etc").on("click", function() {
        if(!confirm("현재 테마의 스킨, 이미지 사이즈 등의 설정을 적용하시겠습니까?"))
            return false;

        var type = $(this).attr("class");
        var $el;

        $.ajax({
            type: "POST",
            url: "../theme_config_load.php",
            cache: false,
            async: false,
            data: { type: type },
            dataType: "json",
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                    return false;
                }

                $.each(data, function(key, val) {
                    if(key == "error")
                        return true;

                    $el = $("#"+key);

                    if($el[0].type == "checkbox") {
                        $el.attr("checked", parseInt(val) ? true : false);
                        return true;
                    }
                    $el.val(val);
                });
            }
        });
    });

    $(document).on("change", "#de_taxsave_use", function(e){
        var $val = $(this).val();
        
        if( parseInt($val) > 0 ){
            $("#de_taxsave_types").show();
        } else {
            $("#de_taxsave_types").hide();
        }
    });
    
    // 현금영수증 발급수단 중 무통장입금은 무조건 체크처리
    document.getElementById("de_taxsave_types_account").checked = true;
    document.getElementById("de_taxsave_types_account").disabled = true;
});
</script>

<?php
// 결제모듈 실행권한 체크
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use']) {
    // kcp의 경우 pp_cli 체크
    if($default['de_pg_service'] == 'kcp') {
        if(!extension_loaded('openssl')) {
            echo '<script>'.PHP_EOL;
            echo 'alert("PHP openssl 확장모듈이 설치되어 있지 않습니다.\n모바일 쇼핑몰 결제 때 사용되오니 openssl 확장 모듈을 설치하여 주십시오.");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }

        if(!extension_loaded('soap') || !class_exists('SOAPClient')) {
            echo '<script>'.PHP_EOL;
            echo 'alert("PHP SOAP 확장모듈이 설치되어 있지 않습니다.\n모바일 쇼핑몰 결제 때 사용되오니 SOAP 확장 모듈을 설치하여 주십시오.");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }

        $is_linux = true;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            $is_linux = false;

        $exe = '/kcp/bin/';
        if($is_linux) {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $exe .= 'pp_cli';
            else
                $exe .= 'pp_cli_x64';
        } else {
            $exe .= 'pp_cli_exe.exe';
        }

        echo module_exec_check(G5_SHOP_PATH.$exe, 'pp_cli');

        // shop/kcp/log 디렉토리 체크 후 있으면 경고
        if(is_dir(G5_SHOP_PATH.'/kcp/log') && is_writable(G5_SHOP_PATH.'/kcp/log')) {
            echo '<script>'.PHP_EOL;
            echo 'alert("웹접근 가능 경로에 log 디렉토리가 있습니다.\nlog 디렉토리를 웹에서 접근 불가능한 경로로 변경해 주십시오.\n\nlog 디렉토리 경로 변경은 SIR FAQ를 참고해 주세요.")'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }
    }

    // LG의 경우 log 디렉토리 체크
    if($default['de_pg_service'] == 'lg') {
        $log_path = G5_LGXPAY_PATH.'/lgdacom/log';

        try {
            if( ! is_dir($log_path) && is_writable(G5_LGXPAY_PATH.'/lgdacom/') ){
                @mkdir($log_path, G5_DIR_PERMISSION);
                @chmod($log_path, G5_DIR_PERMISSION);
            }
        } catch(Exception $e) {
        }

        if(!is_dir($log_path)) {

            if( is_writable(G5_LGXPAY_PATH.'/lgdacom/') ){
                // 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
                @mkdir($log_path, G5_DIR_PERMISSION);
                @chmod($log_path, G5_DIR_PERMISSION);
            }

            if(!is_dir($log_path)){
                echo '<script>'.PHP_EOL;
                echo 'alert("'.str_replace(G5_PATH.'/', '', G5_LGXPAY_PATH).'/lgdacom 폴더 안에 log 폴더를 생성하신 후 쓰기권한을 부여해 주십시오.\n> mkdir log\n> chmod 707 log");'.PHP_EOL;
                echo '</script>'.PHP_EOL;
            }
        }

        if(is_writable($log_path)) {
            if( function_exists('check_log_folder') ){
                check_log_folder($log_path);
            }
        } else {
            echo '<script>'.PHP_EOL;
            echo 'alert("'.str_replace(G5_PATH.'/', '',$log_path).' 폴더에 쓰기권한을 부여해 주십시오.\n> chmod 707 log");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }
    }

    // 이니시스의 경우 log 디렉토리 체크
    if($default['de_pg_service'] == 'inicis') {
        if (!function_exists('xml_set_element_handler')) {
            echo '<script>'.PHP_EOL;
            echo 'alert("XML 관련 함수를 사용할 수 없습니다.\n서버 관리자에게 문의해 주십시오.");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }

        if (!function_exists('openssl_get_publickey')) {
            echo '<script>'.PHP_EOL;
            echo 'alert("OPENSSL 관련 함수를 사용할 수 없습니다.\n서버 관리자에게 문의해 주십시오.");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }

        if (!function_exists('socket_create')) {
            echo '<script>'.PHP_EOL;
            echo 'alert("SOCKET 관련 함수를 사용할 수 없습니다.\n서버 관리자에게 문의해 주십시오.");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        }

        $log_path = G5_SHOP_PATH.'/inicis/log';
        
        try {
            if( ! is_dir($log_path) && is_writable(G5_SHOP_PATH.'/inicis/') ){
                @mkdir($log_path, G5_DIR_PERMISSION);
                @chmod($log_path, G5_DIR_PERMISSION);
            }
        } catch(Exception $e) {
        }

        if( function_exists('check_log_folder') && is_writable($log_path) ){
            check_log_folder($log_path);
        }
    }

    // 카카오페이의 경우 log 디렉토리 체크
    if($default['de_kakaopay_mid'] && $default['de_kakaopay_key'] && $default['de_kakaopay_enckey'] && $default['de_kakaopay_hashkey'] && $default['de_kakaopay_cancelpwd']) {
        $log_path = G5_SHOP_PATH.'/kakaopay/log';

        if(!is_dir($log_path)) {
            echo '<script>'.PHP_EOL;
            echo 'alert("'.str_replace(G5_PATH.'/', '', G5_SHOP_PATH).'/kakaopay 폴더 안에 log 폴더를 생성하신 후 쓰기권한을 부여해 주십시오.\n> mkdir log\n> chmod 707 log");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        } else {
            if(!is_writable($log_path)) {
                echo '<script>'.PHP_EOL;
                echo 'alert("'.str_replace(G5_PATH.'/', '',$log_path).' 폴더에 쓰기권한을 부여해 주십시오.\n> chmod 707 log");'.PHP_EOL;
                echo '</script>'.PHP_EOL;
            } else {
                if( function_exists('check_log_folder') && is_writable($log_path) ){
                    check_log_folder($log_path);
                }
            }
        }
    }
}

include_once (G5_ADMIN_PATH.'/admin.tail.php');