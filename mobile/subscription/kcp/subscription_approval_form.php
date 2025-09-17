<?php
include_once('./_common.php');

@header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
@header('Pragma: no-cache'); // HTTP 1.0.
@header('Expires: 0'); // Proxies.

/* ============================================================================== */
/* =   PAGE : 결제 요청 PAGE                                                    = */
/* = -------------------------------------------------------------------------- = */
/* =   이 페이지는 주문 페이지를 통해서 결제자가 결제 요청을 하는 페이지        = */
/* =   입니다. 아래의 ※ 필수, ※ 옵션 부분과 매뉴얼을 참조하셔서 연동을        = */
/* =   진행하여 주시기 바랍니다.                                                = */
/* = -------------------------------------------------------------------------- = */
/* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
/* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
/* = -------------------------------------------------------------------------- = */
/* =   Copyright (c)  2010.05   KCP Inc.   All Rights Reserved.                 = */
/* ============================================================================== */

/* ============================================================================== */
/* =   환경 설정 파일 Include                                                   = */
/* = -------------------------------------------------------------------------- = */
/* =   ※ 필수                                                                  = */
/* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
/* = -------------------------------------------------------------------------- = */

include_once(G5_MSUBSCRIPTION_PATH . '/settle_kcp.inc.php');       // 환경설정 파일 include

/* = -------------------------------------------------------------------------- = */
/* =   환경 설정 파일 Include END                                               = */
/* ============================================================================== */

/* kcp와 통신후 kcp 서버에서 전송되는 결제 요청 정보*/
$req_tx          = isset($_POST["req_tx"]) ? $_POST["req_tx"] : ''; // 요청 종류
$res_cd          = isset($_POST["res_cd"]) ? $_POST["res_cd"] : ''; // 응답 코드
$tran_cd         = isset($_POST["tran_cd"]) ? $_POST["tran_cd"] : ''; // 트랜잭션 코드
$ordr_idxx       = isset($_POST["ordr_idxx"]) ? $_POST["ordr_idxx"] : ''; // 쇼핑몰 주문번호
$good_name       = isset($_POST["good_name"]) ? $_POST["good_name"] : ''; // 상품명
$good_mny        = isset($_POST["good_mny"]) ? $_POST["good_mny"] : ''; // 결제 총금액
$buyr_name       = isset($_POST["buyr_name"]) ? $_POST["buyr_name"] : ''; // 주문자명
$buyr_tel1       = isset($_POST["buyr_tel1"]) ? $_POST["buyr_tel1"] : ''; // 주문자 전화번호
$buyr_tel2       = isset($_POST["buyr_tel2"]) ? $_POST["buyr_tel2"] : ''; // 주문자 핸드폰 번호
$buyr_mail       = isset($_POST["buyr_mail"]) ? $_POST["buyr_mail"] : ''; // 주문자 E-mail 주소
$use_pay_method  = isset($_POST["use_pay_method"]) ? $_POST["use_pay_method"] : ''; // 결제 방법
$enc_info        = isset($_POST["enc_info"]) ? $_POST["enc_info"] : ''; // 암호화 정보
$enc_data        = isset($_POST["enc_data"]) ? $_POST["enc_data"] : ''; // 암호화 데이터
$rcvr_name         = isset($_POST["rcvr_name"]) ? $_POST["rcvr_name"] : ''; // 수취인 이름
$rcvr_tel1         = isset($_POST["rcvr_tel1"]) ? $_POST["rcvr_tel1"] : ''; // 수취인 전화번호
$rcvr_tel2         = isset($_POST["rcvr_tel2"]) ? $_POST["rcvr_tel2"] : ''; // 수취인 휴대폰번호
$rcvr_mail         = isset($_POST["rcvr_mail"]) ? $_POST["rcvr_mail"] : ''; // 수취인 E-Mail
$rcvr_zipx         = isset($_POST["rcvr_zipx"]) ? $_POST["rcvr_zipx"] : ''; // 수취인 우편번호
$rcvr_add1         = isset($_POST["rcvr_add1"]) ? $_POST["rcvr_add1"] : ''; // 수취인 주소
$rcvr_add2         = isset($_POST["rcvr_add2"]) ? $_POST["rcvr_add2"] : ''; // 수취인 상세주소

/* 주문폼에서 전송되는 정보 */
$settle_method   = isset($_POST["settle_method"]) ? $_POST["settle_method"] : ''; // 결제방법
$tablet_size     = isset($_POST["tablet_size"]) ? $_POST["tablet_size"] : '1.0'; // 모바일기기 화면비율

$comm_tax_mny    = isset($_POST["comm_tax_mny"]) ? $_POST["comm_tax_mny"] : ''; // 과세금액
$comm_vat_mny    = isset($_POST["comm_vat_mny"]) ? $_POST["comm_vat_mny"] : ''; // 부가세
$comm_free_mny   = isset($_POST["comm_free_mny"]) ? $_POST["comm_free_mny"] : ''; // 비과세금액

/*
     * 기타 파라메터 추가 부분 - Start -
     */
$param_opt_1     = isset($_POST["param_opt_1"]) ? $_POST["param_opt_1"] : ''; // 기타 파라메터 추가 부분
$param_opt_2     = isset($_POST["param_opt_2"]) ? $_POST["param_opt_2"] : ''; // 기타 파라메터 추가 부분
$param_opt_3     = isset($_POST["param_opt_3"]) ? $_POST["param_opt_3"] : ''; // 기타 파라메터 추가 부분
/*
     * 기타 파라메터 추가 부분 - End -
     */

/* kcp 데이터 캐릭터셋 변환 */
if ($res_cd != '') {
    /*
        $good_name = iconv('euc-kr', 'utf-8', $good_name);
        $buyr_name = iconv('euc-kr', 'utf-8', $buyr_name);
        $rcvr_name = iconv('euc-kr', 'utf-8', $rcvr_name);
        $rcvr_add1 = iconv('euc-kr', 'utf-8', $rcvr_add1);
        $rcvr_add2 = iconv('euc-kr', 'utf-8', $rcvr_add2);
        */
}


switch ($settle_method) {
    case '신용카드':
        $pay_method = 'CARD';
        $ActionResult = 'card';
        break;
    default:
        $pay_method = '';
        $ActionResult = '';
        break;
}

$js_return_url = G5_SUBSCRIPTION_URL . '/orderform.php';
if (get_session('subs_direct')) {
    $js_return_url .= '?sw_direct=1';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">

<head>
    <title>스마트폰 웹 결제창</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Cache-Control" content="No-Cache">
    <meta http-equiv="Pragma" content="No-Cache">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10">
    <meta name="HandheldFriendly" content="true">
    <meta name="format-detection" content="telephone=no">

    <style type="text/css">
        .LINE {
            background-color: #afc3ff
        }

        .HEAD {
            font-family: "굴림", "굴림체";
            font-size: 9pt;
            color: #065491;
            background-color: #eff5ff;
            text-align: left;
            padding: 3px;
        }

        .TEXT {
            font-family: "굴림", "굴림체";
            font-size: 9pt;
            color: #000000;
            background-color: #FFFFFF;
            text-align: left;
            padding: 3px;
        }

        B {
            font-family: "굴림", "굴림체";
            font-size: 13pt;
            color: #065491;
        }

        INPUT {
            font-family: "굴림", "굴림체";
            font-size: 9pt;
        }

        SELECT {
            font-size: 9pt;
        }

        .COMMENT {
            font-family: "굴림", "굴림체";
            font-size: 9pt;
            line-height: 160%
        }
    </style>
    <!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
    <script src="<?php echo G5_MSUBSCRIPTION_URL; ?>/kcp/approval_key.js"></script>


    <script language="javascript">
        /* kcp mobile 결제창 호출 (변경불가)*/
        function call_pay_form() {

            var v_frm = document.pay_form;

            layer_cont_obj = document.getElementById("content");
            layer_receipt_obj = document.getElementById("layer_receipt");

            //layer_cont_obj.style.display = "none";
            //layer_receipt_obj.style.display = "block";

            v_frm.action = PayUrl;

            if (v_frm.Ret_URL.value == "") {
                /* Ret_URL값은 현 페이지의 URL 입니다. */
                alert("연동시 Ret_URL을 반드시 설정하셔야 됩니다.");
                document.location.href = "<?php echo $js_return_url; ?>";
                return false;
            }

            v_frm.submit();
        }


        /* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청*/
        function chk_pay() {
            /*kcp 결제서버에서 가맹점 주문페이지로 폼값을 보내기위한 설정(변경불가)*/
            self.name = "tar_opener";
            var pay_form = document.pay_form;

            if (pay_form.res_cd.value == "3001") {
                alert("사용자가 취소하였습니다.");
                document.location.href = "<?php echo $js_return_url; ?>";
                return false;
            }

            if (pay_form.enc_data.value != "" && pay_form.enc_info.value != "" && pay_form.tran_cd.value != "") {
                document.getElementById("pay_fail").style.display = "none";
                document.getElementById("show_progress").style.display = "block";
                setTimeout(function() {
                    document.forderform.submit();
                }, 300);
            } else {
                kcp_AJAX();
            }
        }
    </script>
</head>

<body onload="chk_pay();">

    <div id="content">

        <?php
        if ($enc_data != '' && $enc_info != '' && $tran_cd != '') {
            // 제외할 필드
            $exclude = array('req_tx', 'res_cd', 'tran_cd', 'ordr_idxx', 'good_mny', 'good_name', 'buyr_name', 'buyr_tel1', 'buyr_tel2', 'buyr_mail', 'enc_info', 'enc_data', 'use_pay_method', 'rcvr_name', 'rcvr_tel1', 'rcvr_tel2', 'rcvr_mail', 'rcvr_zipx', 'rcvr_add1', 'rcvr_add2', 'param_opt_1', 'param_opt_2', 'param_opt_3');

            $sql = " select * from {$g5['g5_subscription_order_data_table']} where od_id = '$ordr_idxx' ";
            $row = sql_fetch($sql);

            $data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();

            $order_action_url = G5_HTTPS_MSUBSCRIPTION_URL . '/orderformupdate.php';

            echo '<form name="forderform" method="post" action="' . $order_action_url . '" autocomplete="off">' . PHP_EOL;

            echo make_order_field($data, $exclude);

            foreach ($_POST as $key => $value) {
                echo '<input type="hidden" name="' . get_text($key) . '" value="' . get_text($value) . '">' . PHP_EOL;
            }

            echo '</form>' . PHP_EOL;
        }
        ?>

        <form name="pay_form" method="POST">

            <input type="hidden" name="ordr_idxx" value="<?php echo get_text($ordr_idxx); ?>"> <!-- 주문번호           -->
            <input type="hidden" name="good_name" value="<?php echo get_text($good_name); ?>"> <!-- 상품명             -->
            <input type="hidden" name="good_mny" value="<?php echo get_text($good_mny); ?>"> <!-- 결제금액    -->
            <input type="hidden" name="buyr_name" value="<?php echo get_text($buyr_name); ?>"> <!-- 주문자명           -->
            <input type="hidden" name="buyr_tel1" value="<?php echo get_text($buyr_tel1); ?>"> <!-- 주문자 전화번호    -->
            <input type="hidden" name="buyr_tel2" value="<?php echo get_text($buyr_tel2); ?>"> <!-- 주문자 휴대폰번호  -->
            <input type="hidden" name="buyr_mail" value="<?php echo get_text($buyr_mail); ?>"> <!-- 주문자 E-mail      -->
            <input type="hidden" name="kcp_group_id" value="<?php echo get_text(get_subs_option('su_kcp_group_id')); ?>">

            <!-- 사이트 코드 -->
            <input type="hidden" name="site_cd" value="<?php echo get_text($g_conf_site_cd); ?>">
            <!-- 결제수단-->
            <input type="hidden" name="pay_method" value="AUTH">
            <!-- 에스크로 사용유무 에스크로 사용 업체(가상계좌만 해당)는 Y로 세팅 해주시기 바랍니다.-->
            <input type="hidden" name="escw_used" value="N">
            <!-- 결제등록 키 -->
            <input type="hidden" name="approval_key" id="approval">
            <input type="hidden" name="ActionResult" value="batch">
            <!-- 반드시 가맹점 주문페이지의 URL을 입력 해주시기 바랍니다. -->
            <input type="hidden" name="Ret_URL" value="<?php echo G5_MSUBSCRIPTION_URL; ?>/kcp/subscription_approval_form.php">

            <input type="hidden" name="req_tx" value="pay"> <!-- 요청 구분          -->
            <input type="hidden" name="shop_name" value="<?php echo get_text($g_conf_site_name); ?>"> <!-- 사이트 이름 -->
            <input type="hidden" name="currency" value="410" /> <!-- 통화 코드 -->
            <input type="hidden" name="eng_flag" value="N" /> <!-- 한 / 영 -->
            <!-- 화면 크기조정 -->
            <input type="hidden" name="tablet_size" value="<?php echo get_text($tablet_size); ?>">


            <!-- 추가 파라미터 -->
            <input type="hidden" name="param_opt_1" value="<?php echo get_text($param_opt_1); ?>">
            <input type="hidden" name="param_opt_2" value="<?php echo get_text($param_opt_2); ?>">
            <input type="hidden" name="param_opt_3" value="<?php echo get_text($param_opt_3); ?>">
            <!-- 결제 정보 등록시 응답 타입 ( 필드가 없거나 값이 '' 일경우 TEXT, 값이 XML 또는 JSON 지원 -->
            <input type="hidden" name="response_type" value="TEXT" />
            <input type="hidden" name="PayUrl" id="PayUrl" value="" />
            <input type="hidden" name="traceNo" id="traceNo" value="" />

            <input type="hidden" name="res_cd" value="<?php echo get_text($res_cd); ?>"> <!-- 결과 코드          -->
            <input type="hidden" name="tran_cd" value="<?php echo get_text($tran_cd); ?>"> <!-- 트랜잭션 코드      -->
            <input type="hidden" name="enc_info" value="<?php echo get_text($enc_info); ?>"> <!-- 암호화 정보        -->
            <input type="hidden" name="enc_data" value="<?php echo get_text($enc_data); ?>"> <!-- 암호화 데이터      -->
            <input type='hidden' name='batch_cardno_return_yn'  value='Y'>

        </form>

        <div id="pay_fail">
            <p>결제가 실패한 경우 아래 돌아가기 버튼을 클릭해주세요.</p>
            <a href="<?php echo $js_return_url; ?>">돌아가기</a>
        </div>
        <div id="show_progress" style="display:none;">
            <span style="display:block; text-align:center;margin-top:120px"><img src="<?php echo G5_MOBILE_URL; ?>/shop/img/loading.gif" alt=""></span>
            <span style="display:block; text-align:center;margin-top:10px; font-size:14px">주문완료 중입니다. 잠시만 기다려 주십시오.</span>
        </div>
    </div>

</body>

</html>