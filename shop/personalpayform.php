<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/personalpayform.php');
    return;
}

$sql = " select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' and pp_use = '1' and pp_price > 0 ";
$pp = sql_fetch($sql);

if(!$pp['pp_id'])
    alert('개인결제 정보가 존재하지 않습니다.');

if($pp['pp_tno'])
    alert('이미 결제하신 개인결제 내역입니다.');

$g5['title'] = $pp['pp_name'].'님 개인결제';
include_once('./_head.php');

$action_url = G5_HTTPS_SHOP_URL.'/personalpayformupdate.php';

require './settle_kcp.inc.php';

// 개인결제 체크를 위한 hash
$hash_data = md5($pp['pp_id'].$pp['pp_price'].$pp['pp_time']);
set_session('ss_personalpay_id', $pp['pp_id']);
set_session('ss_personalpay_hash', $hash_data);
?>

<?php
/* ============================================================================== */
/* =   Javascript source Include                                                = */
/* = -------------------------------------------------------------------------- = */
/* =   ※ 필수                                                                  = */
/* = -------------------------------------------------------------------------- = */
?>
<script src="<?php echo $g_conf_js_url; ?>"></script>
<?php
/* = -------------------------------------------------------------------------- = */
/* =   Javascript source Include END                                            = */
/* ============================================================================== */
?>
<script>
function CheckPayplusInstall()
{
    StartSmartUpdate();

    if(ChkBrowser())
    {
        if(document.Payplus.object != null) {
            document.getElementById("display_setup_message_top").style.display = "none" ;
            document.getElementById("display_setup_message").style.display = "none" ;
            document.getElementById("display_pay_button").style.display = "block" ;
        }
    }
    else
    {
        setTimeout("init_pay_button();",300);
    }
}

/* Payplus Plug-in 실행 */
function  jsf__pay( form )
{
    var RetVal = false;

    /* Payplus Plugin 실행 */
    if ( MakePayMessage( form ) == true )
    {
        openwin = window.open( "./kcp/proc_win.html", "proc_win", "width=449, height=209, top=300, left=300" );
        RetVal = true ;
    }

    else
    {
        /*  res_cd와 res_msg변수에 해당 오류코드와 오류메시지가 설정됩니다.
            ex) 고객이 Payplus Plugin에서 취소 버튼 클릭시 res_cd=3001, res_msg=사용자 취소
            값이 설정됩니다.
        */
        res_cd  = document.fpersonalpayform.res_cd.value ;
        res_msg = document.fpersonalpayform.res_msg.value ;

    }

    return RetVal ;
}

// Payplus Plug-in 설치 안내
function init_pay_button()
{
    if( GetPluginObject() == null ){
        document.getElementById("display_setup_message_top").style.display = "block" ;
        document.getElementById("display_setup_message").style.display = "block" ;
        document.getElementById("display_pay_button").style.display = "none" ;
        document.getElementById("display_setup_message").scrollIntoView();
    }
    else{
        document.getElementById("display_setup_message_top").style.display = "none" ;
        document.getElementById("display_setup_message").style.display = "none" ;
        document.getElementById("display_pay_button").style.display = "block" ;
    }
}

function get_intall_file()
{
    document.location.href = GetInstallFile();
    return false;
}
</script>

    <!-- Payplus Plug-in 설치 안내 -->
    <p id="display_setup_message_top" style="display:block">
        <strong>결제안내</strong>
        <span class="red">결제를 하시려면 상단의 노란색 표시줄을 클릭</span>하시거나, <a href="https://pay.kcp.co.kr/plugin_new/file/KCPPluginSetup.exe" onclick="return get_intall_file();"><span class="bold">[수동설치]</span></a>를 눌러 Payplus Plug-in을 설치하시기 바랍니다.<br>
        [수동설치]를 눌러 설치하신 경우 <span class="red bold">새로고침(F5)키</span>를 눌러 진행하시기 바랍니다.
    </p>

<form name="fpersonalpayform" method="post" action="<?php echo $action_url; ?>" onsubmit="return fpersonalpayform_check(this);" autocomplete="off">
<input type="hidden" name="pp_id" value="<?php echo $pp['pp_id']; ?>">

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
        <input type="hidden" name="site_name"       value="<?php echo $default['de_admin_company_name']; ?>">

    <?php
        /*
        할부옵션 : Payplus Plug-in에서 카드결제시 최대로 표시할 할부개월 수를 설정합니다.(0 ~ 18 까지 설정 가능)
        ※ 주의  - 할부 선택은 결제금액이 50,000원 이상일 경우에만 가능, 50000원 미만의 금액은 일시불로만 표기됩니다
                   예) value 값을 "5" 로 설정했을 경우 => 카드결제시 결제창에 일시불부터 5개월까지 선택가능
        */
    ?>
        <input type="hidden" name="pay_method"  value="">
        <input type="hidden" name="ordr_idxx"   value="<?php echo $pp['pp_id']; ?>">
        <input type="hidden" name="good_name"   value="<?php echo $pp['pp_name'].'님 개인결제'; ?>">
        <input type="hidden" name="good_mny"    value="<?php echo $pp['pp_price']; ?>">
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

    // 에스크로 상품정보
    if($default['de_escrow_use']) {
        $good_info .= "seq=1".chr(31);
        $good_info .= "ordr_numb={$pp_id}_".sprintf("%04d", 1).chr(31);
        $good_info .= "good_name=".addslashes($pp['pp_name'].'님 개인결제').chr(31);
        $good_info .= "good_cntx=1".chr(31);
        $good_info .= "good_amtx=".$pp['pp_price'].chr(31);
    }
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

        <!-- 에스크로 항목 -->

        <!-- 에스크로 사용 여부 : 반드시 Y 로 세팅 -->
        <input type="hidden" name="escw_used" value="Y">

        <!-- 에스크로 결제처리 모드 : 에스크로: Y, 일반: N, KCP 설정 조건: O -->
        <input type="hidden" name="pay_mod" value="<?php echo ($default['de_escrow_use']?"O":"N"); ?>">

        <!-- 배송 소요일 : 예상 배송 소요일을 입력 -->
        <input type="hidden" name="deli_term" value="03">

        <!-- 장바구니 상품 개수 : 장바구니에 담겨있는 상품의 개수를 입력 -->
        <input type="hidden" name="bask_cntx" value="1">

        <!-- 장바구니 상품 상세 정보 (자바 스크립트 샘플(create_goodInfo()) 참고) -->
        <input type="hidden" name="good_info" value="<?php echo $good_info; ?>">

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
        <input type="hidden" name="wish_vbank_list" value="05:03:04:07:11:23:26:32:34:81:71">
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

    <section id="sod_frm_pay">
        <h2>개인결제정보</h2>

        <div class="tbl_frm01 tbl_wrap">
            <table>
            <tbody>
            <tr>
                <th>결제금액</th>
                <td><?php echo display_price($pp['pp_price']); ?></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_name">이름</label></th>
                <td><input type="text" name="pp_name" value="<?php echo $pp['pp_name']; ?>" id="pp_name" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_email">이메일</label></th>
                <td><input type="text" name="pp_email" value="<?php echo $member['mb_email']; ?>" id="pp_email" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_hp">휴대폰</label></th>
                <td><input type="text" name="pp_hp" value="<?php echo $member['mb_hp']; ?>" id="pp_hp" class="frm_input"></td>
            </tr>
            </tbody>
            </table>
        </div>

        <?php
        if (!$default['de_card_point'])
            echo '<p><strong>무통장입금</strong> 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.</p>';

        $multi_settle == 0;
        $checked = '';

        $escrow_title = "";
        if ($default['de_escrow_use']) {
            $escrow_title = "에스크로 ";
        }

        if ($default['de_vbank_use'] || $default['de_bank_use'] || $default['de_bank_use'] || $default['de_bank_use']) {
        echo '<fieldset id="sod_frm_paysel">';
        echo '<legend>결제방법 선택</legend>';
        }

        // 가상계좌 사용
        if ($default['de_vbank_use']) {
            $multi_settle++;
            echo '<input type="radio" id="pp_settle_vbank" name="pp_settle_case" value="가상계좌" '.$checked.'> <label for="pp_settle_vbank">'.$escrow_title.'가상계좌</label>'.PHP_EOL;
            $checked = '';
        }

        // 계좌이체 사용
        if ($default['de_iche_use']) {
            $multi_settle++;
            echo '<input type="radio" id="pp_settle_iche" name="pp_settle_case" value="계좌이체" '.$checked.'> <label for="pp_settle_iche">'.$escrow_title.'계좌이체</label>'.PHP_EOL;
            $checked = '';
        }

        // 휴대폰 사용
        if ($default['de_hp_use']) {
            $multi_settle++;
            echo '<input type="radio" id="pp_settle_hp" name="pp_settle_case" value="휴대폰" '.$checked.'> <label for="pp_settle_hp">휴대폰</label>'.PHP_EOL;
            $checked = '';
        }

        // 신용카드 사용
        if ($default['de_card_use']) {
            $multi_settle++;
            echo '<input type="radio" id="pp_settle_card" name="pp_settle_case" value="신용카드" '.$checked.'> <label for="pp_settle_card">신용카드</label>'.PHP_EOL;
            $checked = '';
        }

        if ($default['de_vbank_use'] || $default['de_bank_use'] || $default['de_bank_use'] || $default['de_bank_use']) {
        echo '</fieldset>';

        }

        if ($multi_settle == 0)
            echo '<p>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</p>';
        ?>
    </section>

    <!-- Payplus Plug-in 설치 안내 시작 { -->
    <p id="display_setup_message" style="display:block">
        <span class="red">결제를 계속 하시려면 상단의 노란색 표시줄을 클릭</span>하시거나 <a href="https://pay.kcp.co.kr/plugin_new/file/KCPPluginSetup.exe" onclick="return get_intall_file();"><b><u>[수동설치]</u></b></a>를 눌러 다운로드 된 Payplus Plug-in을 설치하시기 바랍니다.<br>
        [수동설치]를 눌러 설치하신 경우 <span class="red bold">새로고침(F5)키</span>를 눌러 진행하시기 바랍니다.<br>
        새로고침(F5) 한후에도 계속 설치파일이 다운로드 되거나 결제가 되지 않으면 브라우저를 새로 열어서 주문해 주시기 바랍니다.<br>
        브라우저가 익스플로러가 아닌 경우 Payplus Plug-in 설치에 문제가 있을수 있음을 알려 드립니다.
    </p>
    <!-- } Payplus Plug-in 설치 안내 끝 -->

    <div id="display_pay_button" class="btn_confirm" style="display:none">
        <input type="submit" value="결제하기" class="btn_submit">
        <a href="javascript:history.go(-1);" class="btn_cancel">취소</a>
    </div>

</form>

<?php if ($default['de_escrow_use']) { ?>
<!-- 에스크로 안내 시작 { -->
<section id="sod_frm_escrow">
    <h2>에스크로 안내</h2>
    <form name="escrow_foot" method="post" action="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp">
    <input type="hidden" name="site_cd" value="SR<?php echo $default['de_kcp_mid']; ?>">
    <table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align='center'><img src="<?php echo G5_SHOP_URL; ?>/img/marks_escrow/escrow_foot.gif" width="290" height="92" border="0" usemap="#Map"></td>
    </tr>
    <tr>
        <td style='line-height:150%;'>
            <br>
            <strong>에스크로(escrow) 제도란?</strong>
            <br>상거래 시에, 판매자와 구매자의 사이에 신뢰할 수 있는 중립적인 제삼자(여기서는 <a href='http://kcp.co.kr' target='_blank'>KCP</a>)가 중개하여
            금전 또는 물품을 거래를 하도록 하는 것, 또는 그러한 서비스를 말한다. 거래의 안전성을 확보하기 위해 이용된다.
            (2006.4.1 전자상거래 소비자보호법에 따른 의무 시행)
            <br><br>
            5만원 이상의 현금 거래에만 해당(에스크로 결제를 선택했을 경우에만 해당)되며,
            신용카드로 구매하는 거래, 배송이 필요하지 않은 재화 등을 구매하는 거래(컨텐츠 등),
            5만원 미만의 현금 거래에는 해당되지 않는다.
            <br>
            <br>
        </td>
    </tr>
    </table>
    <map name="Map" id="Map">
    <area shape="rect" coords="5,62,74,83" href="javascript:escrow_foot_check()" alt="가입사실확인">
    </map>
    </form>
</section>

<script>
function escrow_foot_check()
{
    var status  = "width=500 height=450 menubar=no,scrollbars=no,resizable=no,status=no";
    var obj     = window.open('', 'escrow_foot_pop', status);

    document.escrow_foot.method = "post";
    document.escrow_foot.target = "escrow_foot_pop";
    document.escrow_foot.action = "http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp";

    document.escrow_foot.submit();
}
</script>
<!-- } 에스크로 안내 끝 -->
<?php } ?>

<script>
function fpersonalpayform_check(f)
{
    var settle_case = document.getElementsByName("pp_settle_case");
    var settle_check = false;
    var settle_method = "";
    for (i=0; i<settle_case.length; i++)
    {
        if (settle_case[i].checked)
        {
            settle_check = true;
            settle_method = settle_case[i].value;
            break;
        }
    }
    if (!settle_check)
    {
        alert("결제방식을 선택하십시오.");
        return false;
    }

    var tot_price = <?php echo (int)$pp['pp_price']; ?>;

    if (document.getElementById("pp_settle_iche")) {
        if (document.getElementById("pp_settle_iche").checked) {
            if (tot_price < 150) {
                alert("계좌이체는 150원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    if (document.getElementById("pp_settle_card")) {
        if (document.getElementById("pp_settle_card").checked) {
            if (tot_price < 1000) {
                alert("신용카드는 1000원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    if (document.getElementById("pp_settle_hp")) {
        if (document.getElementById("pp_settle_hp").checked) {
            if (tot_price < 350) {
                alert("휴대폰은 350원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    // pay_method 설정
    switch(settle_method)
    {
        case "계좌이체":
            f.pay_method.value = "010000000000";
            break;
        case "가상계좌":
            f.pay_method.value = "001000000000";
            break;
        case "휴대폰":
            f.pay_method.value = "000010000000";
            break;
        case "신용카드":
            f.pay_method.value = "100000000000";
            break;
        default:
            f.pay_method.value = "무통장";
            break;
    }

    // kcp 결제정보설정
    f.buyr_name.value = f.pp_name.value;
    f.buyr_mail.value = f.pp_email.value;
    f.buyr_tel1.value = f.pp_hp.value;
    f.buyr_tel2.value = f.pp_hp.value;
    f.rcvr_name.value = f.pp_name.value;
    f.rcvr_tel1.value = f.pp_hp.value;
    f.rcvr_tel2.value = f.pp_hp.value;
    f.rcvr_mail.value = f.pp_email.value;

    if(jsf__pay( f )) {
        return true;
    } else {
        return false;
    }
}
</script>

<?php
include_once('./_tail.php');
?>

<script>
CheckPayplusInstall();
</script>