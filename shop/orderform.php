<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/orderform.php');
    return;
}

set_session("ss_direct", $sw_direct);
// 장바구니가 비어있는가?
if ($sw_direct) {
    $tmp_uq_id = get_session("ss_uq_direct");
}
else {
    $tmp_uq_id = get_session("ss_uq_id");
}

if (get_cart_count($tmp_uq_id) == 0)
    alert('장바구니가 비어 있습니다.', './cart.php');

// 포인트 결제 대기 필드 추가
//sql_query(" ALTER TABLE `$g4[shop_order_table]` ADD `od_temp_point` INT NOT NULL AFTER `od_temp_card` ", false);

$g4['title'] = '주문서 작성';

include_once('./_head.php');

// 새로운 주문번호 생성
$od_id = get_uniqid();
set_session('ss_order_uniqid', $od_id);
?>

<div id="sod_frm">

    <?php
    $s_page = 'orderform.php';
    $s_uq_id = $tmp_uq_id;

    echo '<p>주문하실 상품을 확인하세요.</p>';
    include_once('./cartsub.inc.php');

    if (file_exists('./settle_'.$default['de_card_pg'].'.inc.php')) {
        include './settle_'.$default['de_card_pg'].'.inc.php';
    }

    $good_mny = (int)$tot_sell_amount + (int)$send_cost;

    $order_action_url = G4_HTTPS_SHOP_URL.'/orderformupdate.php';
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
    /* 플러그인 설치(확인) */
    StartSmartUpdate();

    /*  해당 스크립트는 타브라우져에서 적용이 되지 않습니다.
    if( document.Payplus.object == null )
    {
        openwin = window.open( "chk_plugin.html", "chk_plugin", "width=420, height=100, top=300, left=300" );
    }
    */

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
            res_cd  = document.forderform.res_cd.value ;
            res_msg = document.forderform.res_msg.value ;

        }

        return RetVal ;
    }

    // Payplus Plug-in 설치 안내
    function init_pay_button()
    {
        /*
        if( document.Payplus.object == null )
            document.getElementById("display_setup_message").style.display = "block" ;
        else
            document.getElementById("display_pay_button").style.display = "block" ;
        */
        // 체크 방법이 변경
        if( GetPluginObject() == null ){
            document.getElementById("display_setup_message").style.display = "block" ;
        }
        else{
            document.getElementById("display_pay_button").style.display = "block" ;
        }
    }

    /*
     * 인터넷 익스플로러와 파이어폭스(사파리, 크롬.. 등등)는 javascript 파싱법이 틀리기 때문에 object 가 인식 전에 실행 되는 문제
     * 기존에는 onload 부분에 추가를 했지만 setTimeout 부분에 추가
     * setTimeout 에 2번째 변수 0은 딜레이 시간 0은 딜래이 없음을 의미
     * - 김민수 - 20101018 -
     */
    setTimeout("init_pay_button();",300);
    </script>

    <form name="forderform" method="post" action="<?php echo $order_action_url; ?>" onsubmit="return forderform_check(this);" autocomplete="off">
    <input type="hidden" name="od_amount"    value="<?php echo $tot_sell_amount; ?>">
    <input type="hidden" name="od_send_cost" value="<?php echo $send_cost; ?>">

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
        <input type="hidden" name="site_key"        value="<?php echo $default['de_kcp_site_key']; ?>">
        <input type="hidden" name="site_name"       value="<?php echo $default['de_admin_company_name']; ?>">

    <?php
        /*
        할부옵션 : Payplus Plug-in에서 카드결제시 최대로 표시할 할부개월 수를 설정합니다.(0 ~ 18 까지 설정 가능)
        ※ 주의  - 할부 선택은 결제금액이 50,000원 이상일 경우에만 가능, 50000원 미만의 금액은 일시불로만 표기됩니다
                   예) value 값을 "5" 로 설정했을 경우 => 카드결제시 결제창에 일시불부터 5개월까지 선택가능
        */
    ?>
        <input type="hidden" name="pay_method"  value="">
        <input type="hidden" name="ordr_idxx"   value="<?php echo $od_id; ?>">
        <input type="hidden" name="good_name"   value="<?php echo $goods; ?>">
        <input type="hidden" name="good_mny"    value="<?php echo $good_mny; ?>">
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
        <input type="hidden" name="bask_cntx" value="<?php echo (int)$goods_count + 1; ?>">

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

    <section id="sod_frm_orderer">
        <h2>주문하시는 분</h2>

        <table class="frm_tbl">
        <tbody>
        <tr>
            <th scope="row"><label for="od_name">이름</label></th>
            <td><input type="text" name="od_name" value="<?php echo $member['mb_name']; ?>" id="od_name" required class="frm_input required" maxlength="20"></td>
        </tr>

        <?php if (!$is_member) { // 비회원이면 ?>
        <tr>
            <th scope="row"><label for="od_pwd">비밀번호</label></th>
            <td>
                <input type="password" name="od_pwd" id="od_pwd" maxlength="20">
                영,숫자 3~20자 (주문서 조회시 필요)
            </td>
        </tr>
        <?php } ?>

        <tr>
            <th scope="row"><label for="od_tel">전화번호</label></th>
            <td><input type="text" name="od_tel" value="<?php echo $member['mb_tel']; ?>" id="od_tel" required class="frm_input required" maxlength="20"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_hp">핸드폰</label></th>
            <td><input type="text" name="od_hp" value="<?php echo $member['mb_hp']; ?>" id="od_hp" class="frm_input" maxlength="20"></td>
        </tr>
        <?php $zip_href = G4_BBS_URL.'/zip.php?frm_name=forderform&amp;frm_zip1=od_zip1&amp;frm_zip2=od_zip2&amp;frm_addr1=od_addr1&amp;frm_addr2=od_addr2'; ?>
        <tr>
            <th scope="row">주소</th>
            <td>
                <label for="od_zip1" class="sound_only">우편번호 앞자리<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_zip1" value="<?php echo $member['mb_zip1'] ?>" id="od_zip1" required class="frm_input required" size="2" maxlength="3">
                -
                <label for="od_zip2" class="sound_only">우편번호 뒷자리<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_zip2" value="<?php echo $member['mb_zip2'] ?>" id="od_zip2" required class="frm_input required" size="2" maxlength="3">
                <span id="od_win_zip" style="display:block"></span>
                <label for="od_addr1" class="sound_only">주소<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_addr1" value="<?php echo $member['mb_addr1'] ?>" id="od_addr1" required class="frm_input frm_address required" size="50">
                <label for="od_addr2" class="sound_only">상세주소<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_addr2" value="<?php echo $member['mb_addr2'] ?>" id="od_addr2" required class="frm_input frm_address required" size="50">
                <script>
                // 우편번호 자바스크립트 비활성화 대응을 위한 코드
                $('<a href="<?php echo $zip_href; ?>" class="btn_frmline win_zip_find" target="_blank">우편번호 검색</a><br>').appendTo('#od_win_zip');
                $("#od_win_zip").css("display", "inline");
                $("#od_zip1, #od_zip2, #od_addr1").attr('readonly', 'readonly');
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_email">E-mail</label></th>
            <td><input type="text" name="od_email" value="<?php echo $member['mb_email']; ?>" id="od_email" required class="frm_input required" size="35" maxlength="100"></td>
        </tr>

        <?php if ($default['de_hope_date_use']) { // 배송희망일 사용 ?>
        <tr>
            <th scope="row"><label for="od_hope_date">희망배송일</label></th>
            <td>
                <select name="od_hope_date" id="od_hope_date">
                <option value="">선택하십시오.</option>
                <?php
                for ($i=0; $i<7; $i++) {
                    $sdate = date("Y-m-d", time()+86400*($default['de_hope_date_after']+$i));
                    echo '<option value="'.$sdate.'">'.$sdate.' ('.get_yoil($sdate).')</option>'.PHP_EOL;
                }
                ?>
                </select>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </section>

    <section id="sod_frm_taker">
        <h2>받으시는 분</h2>

        <div id="sod_frm_same">
            <input type="checkbox" name="same" id="same" onclick="javascript:gumae2baesong(document.forderform);">
            <label for="same">주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크하십시오.</label>
        </div>

        <table class="frm_tbl">
        <tbody>
        <tr>
            <th scope="row"><label for="od_b_name">이름</label></th>
            <td><input type="text" name="od_b_name" id="od_b_name" required class="frm_input required" maxlength="20"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_b_tel">전화번호</label></th>
            <td><input type="text" name="od_b_tel" id="od_b_tel" required class="frm_input required" maxlength="20"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_b_hp">핸드폰</label></th>
            <td><input type="text" name="od_b_hp" id="od_b_hp" class="frm_input" maxlength="20"></td>
        </tr>
        <?php $zip_href = G4_BBS_URL.'/zip.php?frm_name=forderform&amp;frm_zip1=od_b_zip1&amp;frm_zip2=od_b_zip2&amp;frm_addr1=od_b_addr1&amp;frm_addr2=od_b_addr2'; ?>
        <tr>
            <th scope="row">주소</th>
            <td>
                <label for="od_b_zip1" class="sound_only">우편번호 앞자리<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_b_zip1" id="od_b_zip1" required class="frm_input required" size="2" maxlength="3">
                -
                <label for="od_b_zip2" class="sound_only">우편번호 뒷자리<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_b_zip2" id="od_b_zip2" required class="frm_input required" size="2" maxlength="3">
                <span id="od_winb_zip" style="display:block"></span>
                <label for="od_b_addr1" class="sound_only">주소<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_b_addr1" id="od_b_addr1" required class="frm_input frm_address required" size="50">
                <label for="od_b_addr2" class="sound_only">상세주소<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="od_b_addr2" id="od_b_addr2" required class="frm_input frm_address required" size="50">
                <script>
                // 우편번호 자바스크립트 비활성화 대응을 위한 코드
                $('<a href="<?php echo $zip_href; ?>" class="btn_frmline win_zip_find" target="_blank">우편번호 검색</a><br>').appendTo('#od_winb_zip');
                $("#od_winb_zip").css("display", "inline");
                $("#od_b_zip1, #od_b_zip2, #od_b_addr1").attr('readonly', 'readonly');
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_memo">전하실말씀</label></th>
            <td><textarea name="od_memo" id="od_memo"></textarea></td>
        </tr>
        </tbody>
        </table>
    </section>

    <section id="sod_frm_pay">
        <h2>결제정보 입력</h2>

        <?php
        $multi_settle == 0;
        $checked = '';

        $escrow_title = "";
        if ($default['de_escrow_use']) {
            $escrow_title = "에스크로 ";
        }

        if ($default['de_bank_use'] || $default['de_vbank_use'] || $default['de_bank_use'] || $default['de_bank_use'] || $default['de_bank_use']) {
        echo '<fieldset id="sod_frm_paysel">';
        echo '<legend>결제방법 선택</legend>';
        }

        // 무통장입금 사용
        if ($default['de_bank_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_bank" name="od_settle_case" value="무통장" '.$checked.'> <label for="od_settle_bank">무통장입금</label>'.PHP_EOL;
            $checked = '';
        }

        // 가상계좌 사용
        if ($default['de_vbank_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_vbank" name=od_settle_case value="가상계좌" '.$checked.'> <label for="od_settle_vbank">'.$escrow_title.'가상계좌</label>'.PHP_EOL;
            $checked = '';
        }

        // 계좌이체 사용
        if ($default['de_iche_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_iche" name=od_settle_case value="계좌이체" '.$checked.'> <label for="od_settle_iche">'.$escrow_title.'계좌이체</label>'.PHP_EOL;
            $checked = '';
        }

        // 휴대폰 사용
        if ($default['de_hp_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_hp" name=od_settle_case value="휴대폰" '.$checked.'> <label for="od_settle_hp">휴대폰</label>'.PHP_EOL;
            $checked = '';
        }

        // 신용카드 사용
        if ($default['de_card_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_card" name=od_settle_case value="신용카드" '.$checked.'> <label for="od_settle_card">신용카드</label>'.PHP_EOL;
            $checked = '';
        }

        if ($default['de_bank_use']) {
            // 은행계좌를 배열로 만든후
            $str = explode("\n", trim($default['de_bank_account']));
            if (count($str) <= 1)
            {
                $bank_account = '<input type="hidden" name="od_bank_account" value="'.$str[0].'">'.$str[0].PHP_EOL;
            }
            else
            {
                $bank_account = '<select name="od_bank_account" id="od_bank_account">'.PHP_EOL;
                $bank_account .= '<option value="">선택하십시오.</option>';
                for ($i=0; $i<count($str); $i++)
                {
                    //$str[$i] = str_replace("\r", "", $str[$i]);
                    $str[$i] = trim($str[$i]);
                    $bank_account .= '<option value="'.$str[$i].'">'.$str[$i].'</option>'.PHP_EOL;
                }
                $bank_account .= '</select>'.PHP_EOL;
            }
            echo '<div id="settle_bank" style="display:none">';
            echo '<label for="od_bank_account" class="sound_only">입금할 계좌</label>';
            echo $bank_account;
            echo '<label for="od_deposit_name" class="sound_only">입금자명</label>';
            echo '<input type="text" name="od_deposit_name" id="od_deposit_name" class="frm_input" size="10" maxlength="20">';
            echo '</div>';
        }

        if ($default['de_bank_use'] || $default['de_vbank_use'] || $default['de_bank_use'] || $default['de_bank_use'] || $default['de_bank_use']) {
        echo '</fieldset>';

        }

        // 회원이면서 포인트사용이면
        $temp_point = 0;
        if ($is_member && $config['cf_use_point'])
        {
            // 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
            if ($member['mb_point'] >= $default['de_point_settle'])
            {
                $temp_point = $tot_amount * ($default['de_point_per'] / 100); // 포인트 결제 % 적용
                $temp_point = (int)((int)($temp_point / 100) * 100); // 100점 단위

                $member_point = (int)((int)($member['mb_point'] / 100) * 100); // 100점 단위
                if ($temp_point > $member_point)
                    $temp_point = $member_point;

                echo '<div>결제포인트 : <input type="text" id="od_temp_point" name="od_temp_point" value="0" size="10">점 (100점 단위로 입력하세요.)</div>';
                echo '<div>회원님의 보유포인트('.display_point($member['mb_point']).')중 <strong>'.display_point($temp_point).'</strong>(주문금액 '.$default['de_point_per'].'%) 내에서 결제가 가능합니다.</div>';
                $multi_settle++;
            }
        }
        ?>

        <?php
        if (!$default['de_card_point'])
            echo '<p><strong>무통장입금</strong> 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.</p>';

        if ($multi_settle == 0)
            echo '<p>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</p>';
        ?>
    </section>

    <!-- Payplus Plug-in 설치 안내 -->
    <p id="display_setup_message" style="display:none">
        <span class="red">결제를 계속 하시려면 상단의 노란색 표시줄을 클릭</span>하시거나<br>
        <a href="http://pay.kcp.co.kr/plugin/file_vista/PayplusWizard.exe"><span class="bold">[수동설치]</span></a>를 눌러 Payplus Plug-in을 설치하시기 바랍니다.<br>
        [수동설치]를 눌러 설치하신 경우 <span class="red bold">새로고침(F5)키</span>를 눌러 진행하시기 바랍니다.
    </p>

    <div id="display_pay_button" class="btn_confirm" style="display:none">
        <input type="submit" value="주문하기" class="btn_submit">
        <a href="javascript:history.go(-1);" class="btn01">취소</a>
    </div>
    </form>

    <?php if ($default['de_escrow_use']) { ?>
    <section id="sod_frm_escrow">
        <h2>에스크로 안내</h2>
        <form name="escrow_foot" method="post" action="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp">
        <input type="hidden" name="site_cd" value="SR<?php echo $default['de_kcp_mid']; ?>">
        <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align='center'><img src="<?php echo G4_SHOP_URL; ?>/img/marks_escrow/escrow_foot.gif" width="290" height="92" border="0" usemap="#Map"></td>
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
    <?php } ?>

    <!-- <?php if ($default[de_card_use] || $default[de_iche_use]) { echo "결제대행사 : $default[de_card_pg]"; } ?> -->

</div>

<script>
function forderform_check(f)
{
    errmsg = "";
    errfld = "";
    var deffld = "";

    check_field(f.od_name, "주문하시는 분 이름을 입력하십시오.");
    if (typeof(f.od_pwd) != 'undefined')
    {
        clear_field(f.od_pwd);
        if( (f.od_pwd.value.length<3) || (f.od_pwd.value.search(/([^A-Za-z0-9]+)/)!=-1) )
            error_field(f.od_pwd, "회원이 아니신 경우 주문서 조회시 필요한 비밀번호를 3자리 이상 입력해 주십시오.");
    }
    check_field(f.od_tel, "주문하시는 분 전화번호를 입력하십시오.");
    check_field(f.od_addr1, "우편번호 찾기를 이용하여 주문하시는 분 주소를 입력하십시오.");
    check_field(f.od_addr2, " 주문하시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_zip1, "");
    check_field(f.od_zip2, "");

    clear_field(f.od_email);
    if(f.od_email.value=='' || f.od_email.value.search(/(\S+)@(\S+)\.(\S+)/) == -1)
        error_field(f.od_email, "E-mail을 바르게 입력해 주십시오.");

    if (typeof(f.od_hope_date) != "undefined")
    {
        clear_field(f.od_hope_date);
        if (!f.od_hope_date.value)
            error_field(f.od_hope_date, "희망배송일을 선택하여 주십시오.");
    }

    check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");
    check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
    check_field(f.od_b_addr1, "우편번호 찾기를 이용하여 받으시는 분 주소를 입력하십시오.");
    check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_b_zip1, "");
    check_field(f.od_b_zip2, "");

    var od_settle_bank = document.getElementById("od_settle_bank");
    if (od_settle_bank) {
        if (od_settle_bank.checked) {
            check_field(f.od_bank_account, "계좌번호를 선택하세요.");
            check_field(f.od_deposit_name, "입금자명을 입력하세요.");
        }
    }

    // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
    f.od_send_cost.value = parseInt(f.od_send_cost.value);

    if (errmsg)
    {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    var settle_case = document.getElementsByName("od_settle_case");
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

    var tot_amount = <?php echo (int)$tot_amount; ?>;
    var max_point  = <?php echo (int)$temp_point; ?>;

    var temp_point = 0;
    if (typeof(f.od_temp_point) != "undefined") {
        if (f.od_temp_point.value)
        {
            temp_point = parseInt(f.od_temp_point.value);

            if (temp_point < 0) {
                alert("포인트를 0 이상 입력하세요.");
                f.od_temp_point.select();
                return false;
            }

            if (temp_point > tot_amount) {
                alert("주문금액 보다 많이 포인트결제할 수 없습니다.");
                f.od_temp_point.select();
                return false;
            }

            if (temp_point > <?php echo (int)$member['mb_point']; ?>) {
                alert("회원님의 포인트보다 많이 결제할 수 없습니다.");
                f.od_temp_point.select();
                return false;
            }

            if (temp_point > max_point) {
                alert(max_point + "점 이상 결제할 수 없습니다.");
                f.od_temp_point.select();
                return false;
            }

            if (parseInt(parseInt(temp_point / 100) * 100) != temp_point) {
                alert("포인트를 100점 단위로 입력하세요.");
                f.od_temp_point.select();
                return false;
            }
        }
    }

    if (document.getElementById("od_settle_iche")) {
        if (document.getElementById("od_settle_iche").checked) {
            if (tot_amount - temp_point < 150) {
                alert("계좌이체는 150원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    if (document.getElementById("od_settle_card")) {
        if (document.getElementById("od_settle_card").checked) {
            if (tot_amount - temp_point < 1000) {
                alert("신용카드는 1000원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    if (document.getElementById("od_settle_hp")) {
        if (document.getElementById("od_settle_hp").checked) {
            if (tot_amount - temp_point < 350) {
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
    f.buyr_name.value = f.od_name.value;
    f.buyr_mail.value = f.od_email.value;
    f.buyr_tel1.value = f.od_tel.value;
    f.buyr_tel2.value = f.od_hp.value;
    f.rcvr_name.value = f.od_b_name.value;
    f.rcvr_tel1.value = f.od_b_tel.value;
    f.rcvr_tel2.value = f.od_b_hp.value;
    f.rcvr_mail.value = f.od_email.value;
    f.rcvr_zipx.value = f.od_b_zip1.value + f.od_b_zip2.value;
    f.rcvr_add1.value = f.od_b_addr1.value;
    f.rcvr_add2.value = f.od_b_addr2.value;

    if(f.pay_method.value != "무통장") {
        if(jsf__pay( f )) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

// 구매자 정보와 동일합니다.
function gumae2baesong(f)
{
    f.od_b_name.value = f.od_name.value;
    f.od_b_tel.value  = f.od_tel.value;
    f.od_b_hp.value   = f.od_hp.value;
    f.od_b_zip1.value = f.od_zip1.value;
    f.od_b_zip2.value = f.od_zip2.value;
    f.od_b_addr1.value = f.od_addr1.value;
    f.od_b_addr2.value = f.od_addr2.value;
}

$(function() {
    $("#od_settle_bank").bind("click", function() {
        $("[name=od_deposit_name]").val( $("[name=od_b_name]").val() );
        $("#settle_bank").show();
    });

    $("#od_settle_iche,#od_settle_card,#od_settle_vbank").bind("click", function() {
        $("#settle_bank").hide();
    });
});
</script>

<?php
include_once('./_tail.php');
?>