<?php
$sub_menu = '600100';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "r");

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$sql = " select * from `{$g5['g5_subscription_config_table']}` limit 1";
$g5_subscriptions_options = $config['g5_subscriptions_options'] = sql_fetch($sql);

if (! isset($g5_subscriptions_options)) {
    sql_query(
        " ALTER TABLE `{$g5['g5_subscription_config_table']}`
                    ADD `su_cron_updatetime` datetime DEFAULT NULL,
                    ADD `su_cron_execute_hour` tinyint(2) NOT NULL DEFAULT '0'",
        true
    );
}

$g5['title'] = '정기결제설정';
include_once (G5_ADMIN_PATH.'/admin.head.php');

?>
<div>
<form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="token" value="">
<section>
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
            <th scope="row"><label>마지막 크론실행시간</label></th>
            <td>
                <?php echo get_subs_option('su_cron_updatetime'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="su_cron_execute_hour">매일 크론 실행 hour</label></th>
            <td>
                <?php echo help('매일 실행되는 크론 실행 시간을 지정합니다. 지정된 시간에만 정기결제를 실행합니다.'); ?>
                
                <select name="su_cron_execute_hour" id="su_cron_execute_hour">
                    <?php for($i=0;$i<24;++$i) { ?>
                    <option value="<?php echo $i; ?>" <?php echo get_selected(get_subs_option('su_cron_execute_hour'), $i) ?>><?php echo $i.' ~ '.$i + 1; ?> 시</option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="su_pg_service">결제대행사</label></th>
            <td>
                <input type="hidden" name="su_pg_service" id="su_pg_service" value="<?php echo get_subs_option('su_pg_service'); ?>" >
                <?php echo help('정기결제에서 사용할 결제대행사를 선택합니다.'); ?>
                <ul class="de_pg_tab">
                    <li class="<?php if(get_subs_option('su_pg_service') == 'kcp') echo 'tab-current'; ?>"><a href="#kcp_info_anchor" data-value="kcp" title="NHN KCP 선택하기" >NHN KCP</a></li>
                    <li class="<?php if(get_subs_option('su_pg_service') == 'inicis') echo 'tab-current'; ?>"><a href="#inicis_info_anchor" data-value="inicis" title="KG이니시스 선택하기">KG이니시스</a></li>
                    <li class="<?php if(get_subs_option('su_pg_service') == 'nicepay') echo 'tab-current'; ?>"><a href="#nicepay_info_anchor" data-value="nicepay" title="NICEPAY 선택하기">NICEPAY</a></li>
                </ul>
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld" id="kcp_info_anchor">
            <th scope="row">
                <label for="su_kcp_mid">KCP SITE CODE</label><br>
                <a href="http://sir.kr/main/service/p_pg.php" target="_blank" id="scf_kcpreg" class="kcp_btn">NHN KCP 신청하기</a>
            </th>
            <td>
                <?php echo help("NHN KCP 에서 받은 SR 로 시작하는 영대문자, 숫자 혼용 총 5자리 중 SR 을 제외한 나머지 3자리 SITE CODE 를 입력하세요.\n만약, 사이트코드가 SR로 시작하지 않는다면 NHN KCP에 사이트코드 변경 요청을 하십시오. 예) SR9A3"); ?>
                <span class="sitecode">SR</span> <input type="text" name="su_kcp_mid" value="<?php echo get_sanitize_input(get_subs_option('su_kcp_mid')); ?>" id="su_kcp_mid" class="frm_input code_input" size="2" maxlength="3"> 영대문자, 숫자 혼용 3자리
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="su_kcp_group_id">NHN KCP 그룹아이디</label></th>
            <td>
                <input type="text" name="su_kcp_group_id" value="<?php echo get_sanitize_input(get_subs_option('su_kcp_group_id')); ?>" id="su_kcp_group_id" class="frm_input" size="36" maxlength="25">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld" id="inicis_info_anchor">
            <th scope="row">
                <label for="su_inicis_mid">KG이니시스 상점아이디</label><br>
                <a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_kgreg" class="kg_btn">KG이니시스 신청하기</a>
            </th>
            <td>
                <?php echo help("KG이니시스로 부터 발급 받으신 상점아이디(MID) 10자리 중 SIR 을 제외한 나머지 7자리를 입력 합니다.\n만약, 상점아이디가 SIR로 시작하지 않는다면 계약담당자에게 변경 요청을 해주시기 바랍니다. (Tel. 02-3430-5858) 예) SIRpaytest"); ?>
                <span class="sitecode">SIR</span> <input type="text" name="su_inicis_mid" value="<?php echo get_subs_option('su_inicis_mid'); ?>" id="su_inicis_mid" class="frm_input code_input" size="10" maxlength="10"> 영문소문자(숫자포함 가능)
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="su_inicis_sign_key">KG이니시스 웹결제 사인키</label></th>
            <td>
                <?php echo help("KG이니시스에서 발급받은 웹결제 사인키를 입력합니다.\n<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보의 웹결제 signkey생성 조회 버튼 클릭, 팝업창에서 생성 버튼 클릭 후 해당 값을 입력합니다."); ?>
                <input type="text" name="su_inicis_sign_key" value="<?php echo get_sanitize_input(get_subs_option('su_inicis_sign_key')); ?>" id="su_inicis_sign_key" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr class="pg_info_fld nicepay_info_fld" id="nicepay_info_anchor">
            <th scope="row"><label for="su_nice_clientid">나이스페이 clientId</label>
            <br>
            <a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_nicepay_reg" class="nicepay_btn">NICEPAY 신청하기</a>
            </th>
            <td>
                <input type="text" name="su_nice_clientid" value="<?php echo get_sanitize_input(get_subs_option('su_nice_clientid')); ?>" id="su_nice_clientid" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr class="pg_info_fld nicepay_info_fld">
            <th scope="row"><label for="su_nice_secretkey">나이스페이 secretKey</label></th>
            <td>
                <input type="text" name="su_nice_secretkey" value="<?php echo get_sanitize_input(get_subs_option('su_nice_secretkey')); ?>" id="su_nice_secretkey" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr>
            <th scope="row">정기결제 테스트</th>
            <td>
                <?php echo help("PG사의 정기결제 테스트를 하실 경우에 체크하세요. 결제단위 최소 1,000원"); ?>
                <input type="radio" name="su_card_test" value="0" <?php echo (get_subs_option('su_card_test') == 0) ? "checked" : ""; ?> id="su_card_test1">
                <label for="su_card_test1">실결제 </label>
                <input type="radio" name="su_card_test" value="1" <?php echo (get_subs_option('su_card_test') == 1) ? "checked" : ""; ?> id="su_card_test2">
                <label for="su_card_test2">테스트결제</label>
                <div class="scf_cardtest kcp_cardtest">
                    <a href="http://admin.kcp.co.kr/" target="_blank" class="btn_frmline">실결제 관리자</a>
                    <a href="http://testadmin8.kcp.co.kr/" target="_blank" class="btn_frmline">테스트 관리자</a>
                </div>
                <div class="scf_cardtest inicis_cardtest">
                    <a href="https://iniweb.inicis.com/" target="_blank" class="btn_frmline">상점 관리자</a>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>
</div>
<script>
jQuery(function($) {
    
    $(document).on("click", ".de_pg_tab a", function(e){

        var pg = $(this).attr("data-value"),
            class_name = "tab-current";
        
        $("#su_pg_service").val(pg);
        $(this).parent("li").addClass(class_name).siblings().removeClass(class_name);

        $(".pg_vbank_url:visible").hide();
        $("#"+pg+"_vbank_url").show();
        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
        
    });
    
    <?php if (get_subs_option('su_pg_service')) { ?>
    $("#<?php echo get_subs_option('su_pg_service'); ?>_vbank_url").show();
    <?php } else { ?>
    $(".kcp_info_fld").show();
    $("#kcp_vbank_url").show();
    <?php } ?>
        
    $("#su_pg_service").on("change", function() {
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
});
</script>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');