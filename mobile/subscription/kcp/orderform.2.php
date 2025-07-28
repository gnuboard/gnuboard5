<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}

$tablet_size     = "1.0"; // 화면 사이즈 고정
?>
<input type="hidden" name="ordr_idxx"   value="<?php echo $od_id; ?>">
<input type="hidden" name="good_name"   value="">
<input type="hidden" name="good_mny"    value="">
<input type="hidden" name="buyr_name"   value="">
<input type="hidden" name="buyr_mail"   value="">
<input type="hidden" name="buyr_tel1"   value="">
<input type="hidden" name="buyr_tel2"   value="">
<input type="hidden" name="kcp_group_id"   value="<?php echo get_subs_option('su_kcp_group_id'); ?>">

<!-- 공통정보 -->
<input type="hidden" name="req_tx"          value="pay">                           <!-- 요청 구분 -->
<input type="hidden" name="shop_name"       value="<?php echo $g_conf_site_name; ?>">       <!-- 사이트 이름 --> 
<!-- 가맹점 정보 설정-->
<input type="hidden" name="site_cd"         value="<?php echo get_subs_option('su_kcp_mid'); ?>" />
<input type="hidden" name="currency"        value="410"/>                          <!-- 통화 코드 -->
<input type="hidden" name="eng_flag"        value="N"/>                            <!-- 한 / 영 --> 

<!-- 결제등록 키 -->
<input type="hidden" name="approval_key"    id="approval">
<!-- 인증시 필요한 파라미터(변경불가)-->
<input type="hidden" name="escw_used"       value="N">
<input type="hidden" name="pay_method"      value="AUTH">
<input type="hidden" name="ActionResult"    value="batch">

<!-- 리턴 URL (kcp와 통신후 결제를 요청할 수 있는 암호화 데이터를 전송 받을 가맹점의 주문페이지 URL) -->
<input type="hidden" name="Ret_URL"         value="<?=$url?>">
<!-- 화면 크기조정 -->
<input type="hidden" name="tablet_size"     value="<?php echo $tablet_size?>">

<!-- 추가 파라미터 ( 가맹점에서 별도의 값전달시 param_opt 를 사용하여 값 전달 ) -->
<input type="hidden" name="param_opt_1"     value="">
<input type="hidden" name="param_opt_2"     value="">
<input type="hidden" name="param_opt_3"     value="">

<!-- 결제 정보 등록시 응답 타입 ( 필드가 없거나 값이 '' 일경우 TEXT, 값이 XML 또는 JSON 지원 -->
<input type="hidden" name="response_type"  value="TEXT"/>
<input type="hidden" name="PayUrl"   id="PayUrl"   value=""/>
<input type="hidden" name="traceNo"  id="traceNo"  value=""/>

<div id="display_pay_button" class="btn_confirm">
    <span id="show_req_btn"><input type="button" name="submitChecked" onClick="pay_approval();" value="결제등록요청" class="btn_submit"></span>
    <span id="show_pay_btn" style="display:none;"><input type="button" onClick="forderform_check(this.form);" value="주문하기" class="btn_submit"></span>
    <a href="javascript:history.go(-1);" class="btn01">취소</a>
</div>
<div id="display_pay_process" style="display:none">
    <img src="<?php echo G5_URL; ?>/shop/img/loading.gif" alt="">
    <span>주문완료 중입니다. 잠시만 기다려 주십시오.</span>
</div>