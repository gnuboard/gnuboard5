<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 타 PG 사용시 NHN KCP 네이버페이 사용이 설정되어 있는지 체크, 그렇지 않다면 return;
if( !(function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp')) ){
    return;
}

$param_opt_1 = isset($_REQUEST['param_opt_1']) ? clean_xss_tags($_REQUEST['param_opt_1'], 1, 1) : '';
$param_opt_2 = isset($_REQUEST['param_opt_2']) ? clean_xss_tags($_REQUEST['param_opt_2'], 1, 1) : '';
$param_opt_3 = isset($_REQUEST['param_opt_3']) ? clean_xss_tags($_REQUEST['param_opt_3'], 1, 1) : '';
?>

<!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
<script src="<?php echo G5_MSHOP_URL; ?>/kcp/approval_key.js"></script>

<form name="nhnkcp_pay_form" method="POST" action="<?php echo G5_MSHOP_URL; ?>/kcp/order_approval_form.php">
<input type="hidden" name="good_name"     value="<?php echo $goods; ?>">
<input type="hidden" name="good_mny"      value="<?php echo $tot_price ?>" >
<input type="hidden" name="buyr_name"     value="">
<input type="hidden" name="buyr_tel1"     value="">
<input type="hidden" name="buyr_tel2"     value="">
<input type="hidden" name="buyr_mail"     value="">
<input type="hidden" name="settle_method" value="">
<input type="hidden" name="nhnkcp_pay_case" value="">
<input type="hidden" name="payco_direct"   value="">      <!-- PAYCO 결제창 호출 -->
<input type="hidden" name="naverpay_direct" value="A" >    <!-- NAVERPAY 결제창 호출 -->
<?php if(isset($default['de_easy_pay_services']) && in_array('used_nhnkcp_naverpay_point', explode(',', $default['de_easy_pay_services'])) ){     // 네이버페이 포인트 결제 옵션 ?>
<input type="hidden" name="naverpay_point_direct" value="Y">    <!-- 네이버페이 포인트 결제를 하려면 naverpay_point_direct 를 Y  -->
<?php } ?>
<input type="hidden" name="kakaopay_direct" value="A" >    <!-- KAKAOPAY 결제창 호출 -->
<input type="hidden" name="applepay_direct" value="A" >    <!-- APPLEPAY 결제창 호출 -->
<!-- 주문번호 -->
<input type="hidden" name="ordr_idxx" value="<?php echo $od_id; ?>">
<!-- 인증수단(영문 소문자) * 반드시 대소문자 구분 -->
<input type="hidden" name="ActionResult" value="CARD">
<!-- 결제등록 키 -->
<input type="hidden" name="approval_key" id="approval">
<!-- 수취인이름 -->
<input type="hidden" name="rcvr_name" value="">
<!-- 수취인 연락처 -->
<input type="hidden" name="rcvr_tel1" value="">
<!-- 수취인 휴대폰 번호 -->
<input type="hidden" name="rcvr_tel2" value="">
<!-- 수취인 E-MAIL -->
<input type="hidden" name="rcvr_add1" value="">
<!-- 수취인 우편번호 -->
<input type="hidden" name="rcvr_add2" value="">
<!-- 수취인 주소 -->
<input type="hidden" name="rcvr_mail" value="">
<!-- 수취인 상세 주소 -->
<input type="hidden" name="rcvr_zipx" value="">
<!-- 장바구니 상품 개수 -->
<input type="hidden" name="bask_cntx" value="<?php echo (int)$goods_count + 1; ?>">
<!-- 장바구니 정보(상단 스크립트 참조) -->
<input type="hidden" name="good_info" value="<?php echo $good_info; ?>">
<!-- 배송소요기간 -->
<input type="hidden" name="deli_term" value="03">
<!-- 기타 파라메터 추가 부분 - Start - -->
<input type="hidden" name="param_opt_1"  value="<?php echo get_text($param_opt_1); ?>"/>
<input type="hidden" name="param_opt_2"  value="<?php echo get_text($param_opt_2); ?>"/>
<input type="hidden" name="param_opt_3"  value="<?php echo get_text($param_opt_3); ?>"/>
<input type="hidden" name="disp_tax_yn"  value="N">
<!-- 기타 파라메터 추가 부분 - End - -->
<!-- 화면 크기조정 부분 - Start - -->
<input type="hidden" name="tablet_size"  value="<?php echo $tablet_size; ?>"/>
<!-- 화면 크기조정 부분 - End - -->
<!--
    사용 카드 설정
    <input type="hidden" name='used_card'    value="CClg:ccDI">
    /*  무이자 옵션
            ※ 설정할부    (가맹점 관리자 페이지에 설정 된 무이자 설정을 따른다)                             - "" 로 설정
            ※ 일반할부    (KCP 이벤트 이외에 설정 된 모든 무이자 설정을 무시한다)                           - "N" 로 설정
            ※ 무이자 할부 (가맹점 관리자 페이지에 설정 된 무이자 이벤트 중 원하는 무이자 설정을 세팅한다)   - "Y" 로 설정
    <input type="hidden" name="kcp_noint"       value=""/> */

    /*  무이자 설정
            ※ 주의 1 : 할부는 결제금액이 50,000 원 이상일 경우에만 가능
            ※ 주의 2 : 무이자 설정값은 무이자 옵션이 Y일 경우에만 결제 창에 적용
            예) 전 카드 2,3,6개월 무이자(국민,비씨,엘지,삼성,신한,현대,롯데,외환) : ALL-02:03:04
            BC 2,3,6개월, 국민 3,6개월, 삼성 6,9개월 무이자 : CCBC-02:03:06,CCKM-03:06,CCSS-03:06:04
    <input type="hidden" name="kcp_noint_quota" value="CCBC-02:03:06,CCKM-03:06,CCSS-03:06:09"/> */
-->
<input type="hidden" name="kcp_noint"       value="<?php echo ($default['de_card_noint_use'] ? '' : 'N'); ?>">
<?php
if($default['de_tax_flag_use']) {
    /* KCP는 과세상품과 비과세상품을 동시에 판매하는 업체들의 결제관리에 대한 편의성을 제공해드리고자,
       복합과세 전용 사이트코드를 지원해 드리며 총 금액에 대해 복합과세 처리가 가능하도록 제공하고 있습니다

       복합과세 전용 사이트 코드로 계약하신 가맹점에만 해당이 됩니다

       상품별이 아니라 금액으로 구분하여 요청하셔야 합니다

       총결제 금액은 과세금액 + 부과세 + 비과세금액의 합과 같아야 합니다.
       (good_mny = comm_tax_mny + comm_vat_mny + comm_free_mny)

       복합과세는 order_approval_form.php 파일의 의해 적용됨
       아래 필드는 order_approval_form.php 파일로 전송하는 것
    */
?>
<input type="hidden" name="tax_flag"          value="TG03">     <!-- 변경불가    -->
<input type="hidden" name="comm_tax_mny"      value="<?php echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
<input type="hidden" name="comm_vat_mny"      value="<?php echo $comm_vat_mny; ?>">         <!-- 부가세     -->
<input type="hidden" name="comm_free_mny"     value="<?php echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?php
}
?>
</form>