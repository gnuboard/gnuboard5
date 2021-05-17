<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 타 PG 사용시 NHN KCP 네이버페이 사용이 설정되어 있는지 체크, 그렇지 않다면 return;
if( !(function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp')) ){
    return;
}
?>
<script type="text/javascript">
/****************************************************************/
/* m_Completepayment  설명                                      */
/****************************************************************/
/* 인증완료시 재귀 함수                                         */
/* 해당 함수명은 절대 변경하면 안됩니다.                        */
/* 해당 함수의 위치는 payplus.js 보다먼저 선언되어여 합니다.    */
/* Web 방식의 경우 리턴 값이 form 으로 넘어옴                   */
/* EXE 방식의 경우 리턴 값이 json 으로 넘어옴                   */
/****************************************************************/
var kcp_close_eveng_ing = 0;

function m_Completepayment( FormOrJson, closeEvent )
{
    var frm = document.nhnkcp_pay_form;

    /********************************************************************/
    /* FormOrJson은 가맹점 임의 활용 금지                               */
    /* frm 값에 FormOrJson 값이 설정 됨 frm 값으로 활용 하셔야 됩니다.  */
    /* FormOrJson 값을 활용 하시려면 기술지원팀으로 문의바랍니다.       */
    /********************************************************************/
    GetField( frm, FormOrJson );

    if( frm.res_cd.value == "0000" )
    {
        document.getElementById("display_pay_button").style.display = "none" ;
        document.getElementById("display_pay_process").style.display = "" ;

        frm.submit();
    }
    else
    {
        if( ! kcp_close_eveng_ing ){
            
            kcp_close_eveng_ing = 1;
            alert( "[" + frm.res_cd.value + "] " + frm.res_msg.value );

            setTimeout(function(){
                kcp_close_eveng_ing = 0;
            }, 500);

            closeEvent();
        }
    }
}
</script>

<script src="<?php echo $g_conf_js_url; ?>"></script>
<script>
/* Payplus Plug-in 실행 */
function jsf__pay( form )
{
    try
    {
        KCP_Pay_Execute( form );
    }
    catch (e)
    {
        /* IE 에서 결제 정상종료시 throw로 스크립트 종료 */
    }
}
</script>

<form name="nhnkcp_pay_form" id="nhnkcp_pay_form" method="POST" action="<?php echo $order_action_url; ?>" style="display:none">
<?php
    /* ============================================================================== */
    /* =   2. 가맹점 필수 정보 설정                                                 = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수 - 결제에 반드시 필요한 정보입니다.                               = */
    /* = -------------------------------------------------------------------------- = */
    // 요청종류 : 승인(pay)/취소,매입(mod) 요청시 사용
?>
    <input type="hidden" name="req_tx"          value="pay">
    <input type="hidden" name="site_cd"         value="<?php echo $default['de_kcp_mid']; ?>">
    <input type="hidden" name="site_name"       value="<?php echo $g_conf_site_name; ?>">
    <input type="hidden" name="def_site_cd"     value="<?php echo $default['de_kcp_mid']; ?>">
    <input type="hidden" name="od_settle_case"  value="">
    <input type="hidden" name="nhnkcp_pay_case" value="">
<?php
    /*
    할부옵션 : Payplus Plug-in에서 카드결제시 최대로 표시할 할부개월 수를 설정합니다.(0 ~ 18 까지 설정 가능)
    ※ 주의  - 할부 선택은 결제금액이 50,000원 이상일 경우에만 가능, 50000원 미만의 금액은 일시불로만 표기됩니다
               예) value 값을 "5" 로 설정했을 경우 => 카드결제시 결제창에 일시불부터 5개월까지 선택가능
    */
?>
    <input type="hidden" name="pay_method"  value="">
    <input type="hidden" name="ordr_idxx"   value="<?php echo $od_id; ?>">
    <input type="hidden" name="good_name"   value="<?php echo isset($goods) ? get_text($goods) : ''; ?>">
    <input type="hidden" name="good_mny"    value="<?php echo $tot_price; ?>">
    <input type="hidden" name="buyr_name"   value="">
    <input type="hidden" name="buyr_mail"   value="">
    <input type="hidden" name="buyr_tel1"   value="">
    <input type="hidden" name="buyr_tel2"   value="">

    <input type="hidden" name="rcvr_name"     value="">
    <input type="hidden" name="rcvr_tel1"     value="">
    <input type="hidden" name="rcvr_tel2"     value="">
    <input type="hidden" name="rcvr_mail"     value="">
    <input type="hidden" name="rcvr_zipx"     value="">
    <input type="hidden" name="rcvr_add1"     value="">
    <input type="hidden" name="rcvr_add2"     value="">

    <input type="hidden" name="payco_direct"   value="">      <!-- PAYCO 결제창 호출 -->
    <input type="hidden" name="naverpay_direct" value="A" >    <!-- NAVERPAY 결제창 호출 -->
    <?php if(isset($default['de_easy_pay_services']) && in_array('used_nhnkcp_naverpay_point', explode(',', $default['de_easy_pay_services'])) ){     // 네이버페이 포인트 결제 옵션 ?>
    <input type="hidden" name="naverpay_point_direct" value="Y">    <!-- 네이버페이 포인트 결제를 하려면 naverpay_point_direct 를 Y  -->
    <?php } ?>
    <input type="hidden" name="kakaopay_direct" value="A" >    <!-- KAKAOPAY 결제창 호출 -->

    <input type="hidden" name="quotaopt"    value="12">

    <!-- 필수 항목 : 결제 금액/화폐단위 -->
    <input type="hidden" name="currency"    value="WON">

<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   2. 가맹점 필수 정보 설정 END                                             = */
    /* ============================================================================== */
?>

<?php
    /* ============================================================================== */
    /* =   3. Payplus Plugin 필수 정보(변경 불가)                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   결제에 필요한 주문 정보를 입력 및 설정합니다.                            = */
    /* = -------------------------------------------------------------------------- = */
?>
    <!-- PLUGIN 설정 정보입니다(변경 불가) -->
    <input type="hidden" name="module_type"     value="01">
    <!-- 복합 포인트 결제시 넘어오는 포인트사 코드 : OK캐쉬백(SCSK), 베네피아 복지포인트(SCWB) -->
    <input type="hidden" name="epnt_issu"       value="">
<!--
      ※ 필 수
          필수 항목 : Payplus Plugin에서 값을 설정하는 부분으로 반드시 포함되어야 합니다
          값을 설정하지 마십시오
-->
    <input type="hidden" name="res_cd"          value="">
    <input type="hidden" name="res_msg"         value="">
    <input type="hidden" name="tno"             value="">
    <input type="hidden" name="trace_no"        value="">
    <input type="hidden" name="enc_info"        value="">
    <input type="hidden" name="enc_data"        value="">
    <input type="hidden" name="ret_pay_method"  value="">
    <input type="hidden" name="tran_cd"         value="">
    <input type="hidden" name="bank_name"       value="">
    <input type="hidden" name="bank_issu"       value="">
    <input type="hidden" name="use_pay_method"  value="">

    <!--  현금영수증 관련 정보 : Payplus Plugin 에서 설정하는 정보입니다 -->
    <input type="hidden" name="cash_tsdtime"    value="">
    <input type="hidden" name="cash_yn"         value="">
    <input type="hidden" name="cash_authno"     value="">
    <input type="hidden" name="cash_tr_code"    value="">
    <input type="hidden" name="cash_id_info"    value="">

    <!-- 2012년 8월 18일 정자상거래법 개정 관련 설정 부분 -->
    <!-- 제공 기간 설정 0:일회성 1:기간설정(ex 1:2012010120120131)  -->
    <!--
        2012.08.18 부터 개정 시행되는 '전자상거래 등에서의 소비자보호에 관한 법률'에 따른 코드 변경
        이용기간이 제한되는 컨텐츠 상품이나 정기 과금 상품 등에 한하여 '용역의 제공기간'을
        표기/적용하여야 하며 이와 무관한 실물 배송상품 등의 결제에는 해당되지 않습니다.
        0 : 일반결제
        good_expr의 나머지 적용 방식에 대해서는 KCP에서 제공하는 매뉴얼을 참고해 주세요.
    -->
    <input type="hidden" name="good_expr" value="0">

    <!-- 가맹점에서 관리하는 고객 아이디 설정을 해야 합니다.(필수 설정) -->
	<input type="hidden" name="shop_user_id"    value=""/>
	<!-- 복지포인트 결제시 가맹점에 할당되어진 코드 값을 입력해야합니다.(필수 설정) -->
    <input type="hidden" name="pt_memcorp_cd"   value=""/>

    <!-- 에스크로 항목 -->

    <!-- 에스크로 사용 여부 : 반드시 Y 로 세팅 -->
    <input type="hidden" name="escw_used" value="Y">

    <!-- 에스크로 결제처리 모드 : 에스크로: Y, 일반: N, KCP 설정 조건: O -->
    <input type="hidden" name="pay_mod" value="<?php echo ($default['de_escrow_use']?"O":"N"); ?>">

    <!-- 배송 소요일 : 예상 배송 소요일을 입력 -->
    <input type="hidden" name="deli_term" value="03">

    <!-- 장바구니 상품 개수 : 장바구니에 담겨있는 상품의 개수를 입력 -->
    <input type="hidden" name="bask_cntx" value="<?php echo isset($goods_count) ? ((int) $goods_count + 1) : 0; ?>">

    <!-- 장바구니 상품 상세 정보 (자바 스크립트 샘플(create_goodInfo()) 참고) -->
    <input type="hidden" name="good_info" value="">

<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   3. Payplus Plugin 필수 정보 END                                          = */
    /* ============================================================================== */
?>

<?php
    /* ============================================================================== */
    /* =   4. 옵션 정보                                                             = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 옵션 - 결제에 필요한 추가 옵션 정보를 입력 및 설정합니다.             = */
    /* = -------------------------------------------------------------------------- = */

    /* PayPlus에서 보이는 신용카드사 삭제 파라미터 입니다
    ※ 해당 카드를 결제창에서 보이지 않게 하여 고객이 해당 카드로 결제할 수 없도록 합니다. (카드사 코드는 매뉴얼을 참고)
    <input type="hidden" name="not_used_card" value="CCPH:CCSS:CCKE:CCHM:CCSH:CCLO:CCLG:CCJB:CCHN:CCCH"> */

    /* 신용카드 결제시 OK캐쉬백 적립 여부를 묻는 창을 설정하는 파라미터 입니다
         OK캐쉬백 포인트 가맹점의 경우에만 창이 보여집니다
        <input type="hidden" name="save_ocb"        value="Y"> */

    /* 고정 할부 개월 수 선택
           value값을 "7" 로 설정했을 경우 => 카드결제시 결제창에 할부 7개월만 선택가능
    <input type="hidden" name="fix_inst"        value="07"> */

    /*  무이자 옵션
            ※ 설정할부    (가맹점 관리자 페이지에 설정 된 무이자 설정을 따른다)                             - "" 로 설정
            ※ 일반할부    (KCP 이벤트 이외에 설정 된 모든 무이자 설정을 무시한다)                           - "N" 로 설정
            ※ 무이자 할부 (가맹점 관리자 페이지에 설정 된 무이자 이벤트 중 원하는 무이자 설정을 세팅한다)   - "Y" 로 설정
    <input type="hidden" name="kcp_noint"       value=""> */


    /*  무이자 설정
            ※ 주의 1 : 할부는 결제금액이 50,000 원 이상일 경우에만 가능
            ※ 주의 2 : 무이자 설정값은 무이자 옵션이 Y일 경우에만 결제 창에 적용
            예) 전 카드 2,3,6개월 무이자(국민,비씨,엘지,삼성,신한,현대,롯데,외환) : ALL-02:03:04
            BC 2,3,6개월, 국민 3,6개월, 삼성 6,9개월 무이자 : CCBC-02:03:06,CCKM-03:06,CCSS-03:06:04
    <input type="hidden" name="kcp_noint_quota" value="CCBC-02:03:06,CCKM-03:06,CCSS-03:06:09"> */

    /* 사용카드 설정 여부 파라미터 입니다.(통합결제창 노출 유무)
    <input type="hidden" name="used_card_YN"        value="Y">
    /* 사용카드 설정 파라미터 입니다. (해당 카드만 결제창에 보이게 설정하는 파라미터입니다. used_card_YN 값이 Y일때 적용됩니다.
    /<input type="hidden" name="used_card"        value="CCBC:CCKM:CCSS">

    /* 해외카드 구분하는 파라미터 입니다.(해외비자, 해외마스터, 해외JCB로 구분하여 표시)
    <input type="hidden" name="used_card_CCXX"        value="Y">

    /*  가상계좌 은행 선택 파라미터
         ※ 해당 은행을 결제창에서 보이게 합니다.(은행코드는 매뉴얼을 참조) */
?>

<input type="hidden" name="kcp_noint"       value="<?php echo ($default['de_card_noint_use'] ? '' : 'N'); ?>">

<?php
if($default['de_tax_flag_use']) {
    /* KCP는 과세상품과 비과세상품을 동시에 판매하는 업체들의 결제관리에 대한 편의성을 제공해드리고자,
       복합과세 전용 사이트코드를 지원해 드리며 총 금액에 대해 복합과세 처리가 가능하도록 제공하고 있습니다

       복합과세 전용 사이트 코드로 계약하신 가맹점에만 해당이 됩니다

       상품별이 아니라 금액으로 구분하여 요청하셔야 합니다

       총결제 금액은 과세금액 + 부과세 + 비과세금액의 합과 같아야 합니다.
       (good_mny = comm_tax_mny + comm_vat_mny + comm_free_mny) */
?>
    <input type="hidden" name="tax_flag"          value="TG03">     <!-- 변경불가    -->
    <input type="hidden" name="comm_tax_mny"	  value="">         <!-- 과세금액    -->
    <input type="hidden" name="comm_vat_mny"      value="">         <!-- 부가세	    -->
    <input type="hidden" name="comm_free_mny"     value="">        <!-- 비과세 금액 -->
<?php
}
?>
    <input type="hidden" name="wish_vbank_list" value="">
<?php


    /*  가상계좌 입금 기한 설정하는 파라미터 - 발급일 + 3일
    <input type="hidden" name="vcnt_expire_term" value="3"> */


    /*  가상계좌 입금 시간 설정하는 파라미터
         HHMMSS형식으로 입력하시기 바랍니다
         설정을 안하시는경우 기본적으로 23시59분59초가 세팅이 됩니다
         <input type="hidden" name="vcnt_expire_term_time" value="120000"> */


    /* 포인트 결제시 복합 결제(신용카드+포인트) 여부를 결정할 수 있습니다.- N 일경우 복합결제 사용안함
        <input type="hidden" name="complex_pnt_yn" value="N">    */


    /* 문화상품권 결제시 가맹점 고객 아이디 설정을 해야 합니다.(필수 설정)
        <input type="hidden" name="tk_shop_id" value="">    */


    /* 현금영수증 등록 창을 출력 여부를 설정하는 파라미터 입니다
         ※ Y : 현금영수증 등록 창 출력
         ※ N : 현금영수증 등록 창 출력 안함
         ※ 주의 : 현금영수증 사용 시 KCP 상점관리자 페이지에서 현금영수증 사용 동의를 하셔야 합니다 */
?>
    <input type="hidden" name="disp_tax_yn"     value="N">
<?php
    /* 결제창에 가맹점 사이트의 로고를 플러그인 좌측 상단에 출력하는 파라미터 입니다
       업체의 로고가 있는 URL을 정확히 입력하셔야 하며, 최대 150 X 50  미만 크기 지원

    ※ 주의 : 로고 용량이 150 X 50 이상일 경우 site_name 값이 표시됩니다. */
?>
    <input type="hidden" name="site_logo"       value="">
<?php
    /* 결제창 영문 표시 파라미터 입니다. 영문을 기본으로 사용하시려면 Y로 세팅하시기 바랍니다
        2010-06월 현재 신용카드와 가상계좌만 지원됩니다
        <input type="hidden" name="eng_flag"      value="Y"> */
?>

<?php
     /* skin_indx 값은 스킨을 변경할 수 있는 파라미터이며 총 7가지가 지원됩니다.
        변경을 원하시면 1부터 7까지 값을 넣어주시기 바랍니다. */
?>
    <input type="hidden" name="skin_indx"      value="1">

<?php
    /* 상품코드 설정 파라미터 입니다.(상품권을 따로 구분하여 처리할 수 있는 옵션기능입니다.)
    <input type="hidden" name="good_cd"      value=""> */

    /* = -------------------------------------------------------------------------- = */
    /* =   4. 옵션 정보 END                                                         = */
    /* ============================================================================== */
?>
</form>