<?php
include_once('./_common.php');

set_session("ss_direct", $sw_direct);
// 장바구니가 비어있는가?
if ($sw_direct) {
    $tmp_uq_id = get_session("ss_uq_direct");
}
else {
    $tmp_uq_id = get_session("ss_uq_id");
}

if (get_cart_count($tmp_uq_id) == 0)
    alert('장바구니가 비어 있습니다.', G4_SHOP_URL.'/cart.php');

// 포인트 결제 대기 필드 추가
//sql_query(" ALTER TABLE `$g4[shop_order_table]` ADD `od_temp_point` INT NOT NULL AFTER `od_temp_card` ", false);

$g4['title'] = '주문서 작성';

include_once(G4_MSHOP_PATH.'/_head.php');

// 새로운 주문번호 생성
$od_id = get_uniqid();
set_session('ss_order_uniqid', $od_id);

// 결제등록 요청시 사용할 입금마감일
$ipgm_date = date("Ymd", (G4_SERVER_TIME + 86400 * 5));
$tablet_size = "1.0"; // 화면 사이즈 조정 - 기기화면에 맞게 수정(갤럭시탭,아이패드 - 1.85, 스마트폰 - 1.0)
?>

<div id="sod_frm">

    <?php
    $s_page = 'orderform.php';
    $s_uq_id = $tmp_uq_id;

    echo '<p>주문하실 상품을 확인하세요.</p>';
    include_once(G4_MSHOP_PATH.'/cartsub.inc.php');

    if (file_exists(G4_MSHOP_PATH.'/settle_'.$default['de_card_pg'].'.inc.php')) {
        include G4_MSHOP_PATH.'/settle_'.$default['de_card_pg'].'.inc.php';
    }

    $good_mny = (int)$tot_sell_amount + (int)$send_cost;

    $order_action_url = G4_HTTPS_MSHOP_URL.'/orderformupdate.php';
    ?>

    <!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
    <script src="<?php echo G4_MSHOP_URL; ?>/kcp/approval_key.js"></script>

    <script language="javascript">
        /* 결제방법에 따른 처리 후 결제등록요청 실행 */
        function kcp_approval()
        {
            var f = document.sm_form;
            var pf = document.forderform;

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

            f.buyr_name.value = pf.od_name.value;
            f.buyr_mail.value = pf.od_email.value;
            f.buyr_tel1.value = pf.od_tel.value;
            f.buyr_tel2.value = pf.od_hp.value;
            f.rcvr_name.value = pf.od_b_name.value;
            f.rcvr_tel1.value = pf.od_b_tel.value;
            f.rcvr_tel2.value = pf.od_b_hp.value;
            f.rcvr_mail.value = pf.od_email.value;
            f.rcvr_zipx.value = pf.od_b_zip1.value + pf.od_b_zip2.value;
            f.rcvr_add1.value = pf.od_b_addr1.value;
            f.rcvr_add2.value = pf.od_b_addr2.value;
            f.settle_method.value = settle_method;

            var new_win = window.open("about:blank", "tar_opener", "scrollbars=yes,resizable=yes");
            f.target = "tar_opener";

            f.submit();
        }
    </script>

    <form name="sm_form" method="POST" action="<?php echo G4_MSHOP_URL; ?>/kcp/order_approval_form.php">
    <input type="hidden" name="good_name"     value="<?php echo $goods; ?>">
    <input type="hidden" name="good_mny"      value="<?php echo $good_mny; ?>" >
    <input type="hidden" name="buyr_name"     value="">
    <input type="hidden" name="buyr_tel1"     value="">
    <input type="hidden" name="buyr_tel2"     value="">
    <input type="hidden" name="buyr_mail"     value="">
    <input type="hidden" name="ipgm_date"     value="<?php echo $ipgm_date; ?>">
    <input type="hidden" name="settle_method" value="">
    <!-- 주문번호 -->
    <input type="hidden" name="ordr_idxx" value="<?php echo $od_id; ?>">
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
    <input type="hidden" name="param_opt_1"	 value="<?=$param_opt_1?>"/>
    <input type="hidden" name="param_opt_2"	 value="<?=$param_opt_2?>"/>
    <input type="hidden" name="param_opt_3"	 value="<?=$param_opt_3?>"/>
    <!-- 기타 파라메터 추가 부분 - End - -->
    <!-- 화면 크기조정 부분 - Start - -->
    <input type="text" name="tablet_size"	 value="<?=$tablet_size?>"/>
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
    </form>

    <form name="forderform" method="post" action="<?php echo $order_action_url; ?>" onsubmit="return forderform_check(this);" autocomplete="off">
    <input type="hidden" name="od_amount"    value="<?php echo $tot_sell_amount; ?>">
    <input type="hidden" name="od_send_cost" value="<?php echo $send_cost; ?>">

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
            echo '<input type="radio" id="od_settle_vbank" name="od_settle_case" value="가상계좌" '.$checked.'> <label for="od_settle_vbank">'.$escrow_title.'가상계좌</label>'.PHP_EOL;
            $checked = '';
        }

        // 계좌이체 사용
        if ($default['de_iche_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_iche" name="od_settle_case" value="계좌이체" '.$checked.'> <label for="od_settle_iche">'.$escrow_title.'계좌이체</label>'.PHP_EOL;
            $checked = '';
        }

        // 휴대폰 사용
        if ($default['de_hp_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_hp" name="od_settle_case" value="휴대폰" '.$checked.'> <label for="od_settle_hp">휴대폰</label>'.PHP_EOL;
            $checked = '';
        }

        // 신용카드 사용
        if ($default['de_card_use']) {
            $multi_settle++;
            echo '<input type="radio" id="od_settle_card" name="od_settle_case" value="신용카드" '.$checked.'> <label for="od_settle_card">신용카드</label>'.PHP_EOL;
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

    <input type="hidden" name="req_tx"         value="">      <!-- 요청 구분          -->
    <input type="hidden" name="res_cd"         value="">      <!-- 결과 코드          -->
    <input type="hidden" name="tran_cd"        value="">      <!-- 트랜잭션 코드      -->
    <input type="hidden" name="ordr_idxx"      value="">      <!-- 주문번호           -->
    <input type="hidden" name="good_mny"       value="">      <!-- 결제금액    -->
    <input type="hidden" name="good_name"      value="">      <!-- 상품명             -->
    <input type="hidden" name="buyr_name"      value="">      <!-- 주문자명           -->
    <input type="hidden" name="buyr_tel1"      value="">      <!-- 주문자 전화번호    -->
    <input type="hidden" name="buyr_tel2"      value="">      <!-- 주문자 휴대폰번호  -->
    <input type="hidden" name="buyr_mail"      value="">      <!-- 주문자 E-mail      -->
    <input type="hidden" name="enc_info"       value="">      <!-- 암호화 정보        -->
    <input type="hidden" name="enc_data"       value="">      <!-- 암호화 데이터      -->
    <input type="hidden" name="use_pay_method" value="">      <!-- 요청된 결제 수단   -->
    <input type="hidden" name="rcvr_name"      value="">      <!-- 수취인 이름        -->
    <input type="hidden" name="rcvr_tel1"      value="">      <!-- 수취인 전화번호    -->
    <input type="hidden" name="rcvr_tel2"      value="">      <!-- 수취인 휴대폰번호  -->
    <input type="hidden" name="rcvr_mail"      value="">      <!-- 수취인 E-Mail      -->
    <input type="hidden" name="rcvr_zipx"      value="">      <!-- 수취인 우편번호    -->
    <input type="hidden" name="rcvr_add1"      value="">      <!-- 수취인 주소        -->
    <input type="hidden" name="rcvr_add2"      value="">      <!-- 수취인 상세 주소   -->
	<input type="hidden" name="param_opt_1"	   value="">
	<input type="hidden" name="param_opt_2"	   value="">
	<input type="hidden" name="param_opt_3"	   value="">

    <p id="show_progress" style="display:none;">반드시 주문하기 버튼을 클릭 하셔야만 결제가 진행됩니다.</p>

    <div id="display_pay_button" class="btn_confirm">
        <span id="show_req_btn"><input type="button" name="submitChecked" onClick="kcp_approval();" value="결제등록요청" /></span>
        <span id="show_pay_btn" style="display:none;"><input type="submit" value="주문하기" class="btn_submit"></span>
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

    if(settle_method != "무통장" && f.res_cd.value != "0000") {
        alert("결제등록요청 후 주문해 주십시오.");
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

    return true;
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
        $("#show_req_btn").css("display", "none");
        $("#show_pay_btn").css("display", "inline");
    });

    $("#od_settle_iche,#od_settle_card,#od_settle_vbank,#od_settle_hp").bind("click", function() {
        $("#settle_bank").hide();
        $("#show_req_btn").css("display", "inline");
        $("#show_pay_btn").css("display", "none");
    });
});
</script>

<?php
include_once(G4_MSHOP_PATH.'/_tail.php');
?>