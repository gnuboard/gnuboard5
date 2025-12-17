<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}
?>
<input type="hidden" name="ordr_idxx"   value="<?php echo sanitize_input($od_id); ?>">
<input type="hidden" name="buyr_name"   value="">
<!-- 가맹점 정보 설정-->
<input type="hidden" name="site_cd"         value="<?php echo get_subs_option('su_kcp_mid'); ?>" />
<input type="hidden" name="site_name"       value="<?php echo sanitize_input($g_conf_site_name); ?>" />
<input type="hidden" name="kcpgroup_id"   value="<?php echo get_subs_option('su_kcp_group_id'); ?>">
<!-- 상품제공기간 설정 -->
<?php
// 해당 값을 설정하지 않는 경우, 결제창에서 [자동결제]로 노출됩니다.
// ex) "good_expr" : "2:1m" -> [1개월 자동결제]로 표기
// 기본값 <input type="hidden" name="good_expr"       value="2:1m"/>
?>
<input type="hidden" name="good_expr"       value=""/>
<!-- 결제 수단 : 고정값 -->
<input type="hidden" name="pay_method"     value="AUTH:CARD" />
<!-- 인증 방식 : 고정값 -->
<input type="hidden" name="card_cert_type" value="BATCH" />
<!-- 배치키 발급시 주민번호 입력을 결제창 안에서 진행 -->
<input type='hidden' name='batch_soc'      value="Y"/>
<!-- 
    ※필수 항목
    인증 완료 후 값을 설정하는 부분으로 반드시 포함되어야 합니다. 값을 설정하지 마십시오.
-->
<input type="hidden" name="module_type"     value="01"/>
<input type="hidden" name="res_cd"          value=""/>
<input type="hidden" name="res_msg"         value=""/>
<input type="hidden" name="enc_info"        value=""/>
<input type="hidden" name="enc_data"        value=""/>
<input type="hidden" name="tran_cd"         value=""/>

<!-- 주민번호 S / 사업자번호 C 픽스 여부 -->
<!-- <input type='hidden' name='batch_soc_choice'        value='' /> -->

<!-- 배치키 발급시 카드번호 리턴 여부 설정 -->
<!-- Y : 1234-4567-****-8910 형식, L : 8910 형식(카드번호 끝 4자리) -->
<input type="hidden" name="batch_cardno_return_yn" value="Y" >
<!-- batch_cardno_return_yn 설정시 결제창에서 리턴 -->
<input type="hidden" name="card_mask_no"		     value="">

<!-- 배치키 발급 제한 카드구분 값 설정 변수 -->
<!-- <input type='hidden' name='kcp_restricted_card'		     value=''> -->