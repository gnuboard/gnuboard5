<?php
include_once('./_common.php');

$sql = " select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' and pp_use = '1' and pp_price > 0 ";
$pp = sql_fetch($sql);

if(!$pp['pp_id'])
    alert('개인결제 정보가 존재하지 않습니다.');

if($pp['pp_tno'])
    alert('이미 결제하신 개인결제 내역입니다.');

$g5['title'] = $pp['pp_name'].'님 개인결제';
include_once(G5_MSHOP_PATH.'/_head.php');

$action_url = G5_HTTPS_MSHOP_URL.'/personalpayformupdate.php';

require './settle_kcp.inc.php';

// 결제등록 요청시 사용할 입금마감일
$ipgm_date = date("Ymd", (G5_SERVER_TIME + 86400 * 5));
$tablet_size = "1.0"; // 화면 사이즈 조정 - 기기화면에 맞게 수정(갤럭시탭,아이패드 - 1.85, 스마트폰 - 1.0)

// 개인결제 체크를 위한 hash
$hash_data = md5($pp['pp_id'].$pp['pp_price'].$pp['pp_time']);
set_session('ss_personalpay_id', $pp['pp_id']);
set_session('ss_personalpay_hash', $hash_data);

// 에스크로 상품정보
if($default['de_escrow_use']) {
    $good_info .= "seq=1".chr(31);
    $good_info .= "ordr_numb={$pp_id}_".sprintf("%04d", 1).chr(31);
    $good_info .= "good_name=".addslashes($pp['pp_name'].'님 개인결제').chr(31);
    $good_info .= "good_cntx=1".chr(31);
    $good_info .= "good_amtx=".$pp['pp_price'].chr(31);
}
?>

<div id="sod_approval_frm">
    <!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
    <script src="<?php echo G5_MSHOP_URL; ?>/kcp/approval_key.js"></script>

    <form name="sm_form" method="POST" action="<?php echo G5_MSHOP_URL; ?>/kcp/personalpay_approval_form.php">
    <input type="hidden" name="good_name"     value="<?php echo $pp['pp_name'].'님 개인결제'; ?>">
    <input type="hidden" name="good_mny"      value="<?php echo $pp['pp_price']; ?>" >
    <input type="hidden" name="buyr_name"     value="">
    <input type="hidden" name="buyr_tel1"     value="">
    <input type="hidden" name="buyr_tel2"     value="">
    <input type="hidden" name="buyr_mail"     value="">
    <input type="hidden" name="ipgm_date"     value="<?php echo $ipgm_date; ?>">
    <input type="hidden" name="settle_method" value="">
    <!-- 주문번호 -->
    <input type="hidden" name="ordr_idxx" value="<?php echo $pp['pp_id']; ?>">
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
    <input type="hidden" name="bask_cntx" value="1">
    <!-- 장바구니 정보(상단 스크립트 참조) -->
    <input type="hidden" name="good_info" value="<?php echo $good_info; ?>">
    <!-- 배송소요기간 -->
    <input type="hidden" name="deli_term" value="03">
    <!-- 기타 파라메터 추가 부분 - Start - -->
    <input type="hidden" name="param_opt_1"  value="<?php echo $param_opt_1; ?>"/>
    <input type="hidden" name="param_opt_2"  value="<?php echo $param_opt_2; ?>"/>
    <input type="hidden" name="param_opt_3"  value="<?php echo $param_opt_3; ?>"/>
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
    </form>
</div>

<div id="sod_frm">
    <form name="fpersonalpayform" method="post" action="<?php echo $action_url; ?>" autocomplete="off">
    <input type="hidden" name="pp_id" value="<?php echo $pp['pp_id']; ?>">
    <section id="sod_frm_orderer">
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
                <td><input type="email" name="pp_email" value="<?php echo $member['mb_email']; ?>" id="pp_email" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_hp">휴대폰</label></th>
                <td><input type="text" name="pp_hp" value="<?php echo $member['mb_hp']; ?>" id="pp_hp" class="frm_input"></td>
            </tr>
            </tbody>
            </table>
        </div>

        <?php
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
    <input type="hidden" name="param_opt_1"    value="">
    <input type="hidden" name="param_opt_2"    value="">
    <input type="hidden" name="param_opt_3"    value="">

    <div id="display_pay_button" class="btn_confirm">
        <span id="show_req_btn"><input type="button" name="submitChecked" onClick="kcp_approval();" value="결제등록요청"class="btn_submit"></span>
        <span id="show_pay_btn" style="display:none;"><input type="button" onClick="fpersonalpayform_check();" value="결제하기" class="btn_submit"></span>
        <a href="javascript:history.go(-1);" class="btn_cancel">취소</a>
    </div>

    <div id="show_progress" style="display:none;">
        <img src="<?php echo G5_MOBILE_URL; ?>/shop/img/loading.gif" alt="">
        <span>결제진행 중입니다. 잠시만 기다려 주십시오.</span>
    </div>
    </form>

    <?php if ($default['de_escrow_use']) { ?>
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
    <?php } ?>

    <!-- <?php if ($default['de_card_use'] || $default['de_iche_use']) { echo "결제대행사 : KCP"; } ?> -->
</div>

<script>
/* 결제방법에 따른 처리 후 결제등록요청 실행 */
var settle_method = "";

function kcp_approval()
{
    var f = document.sm_form;
    var pf = document.fpersonalpayform;

    // 필드체크
    if(!payfield_check(pf))
        return false;

    // 금액체크
    if(!payment_check(pf))
        return false;

    f.buyr_name.value = pf.pp_name.value;
    f.buyr_mail.value = pf.pp_email.value;
    f.buyr_tel1.value = pf.pp_hp.value;
    f.buyr_tel2.value = pf.pp_hp.value;
    f.rcvr_name.value = pf.pp_name.value;
    f.rcvr_tel1.value = pf.pp_hp.value;
    f.rcvr_tel2.value = pf.pp_hp.value;
    f.rcvr_mail.value = pf.pp_email.value;
    f.settle_method.value = settle_method;

    var new_win = window.open("about:blank", "tar_opener", "scrollbars=yes,resizable=yes");
    f.target = "tar_opener";

    f.submit();
}

function fpersonalpayform_check()
{
    var f = document.fpersonalpayform;

    // 필드체크
    if(!payfield_check(f))
        return false;

    // 금액체크
    if(!payment_check(f))
        return false;

    if(f.res_cd.value != "0000") {
        alert("결제등록요청 후 결제해 주십시오.");
        return false;
    }

    document.getElementById("display_pay_button").style.display = "none";
    document.getElementById("show_progress").style.display = "block";

    setTimeout(function() {
        f.submit();
    }, 300);
}

// 결제폼 필드체크
function payfield_check(f)
{
    var settle_case = document.getElementsByName("pp_settle_case");
    var settle_check = false;
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

    return true;
}

// 결제체크
function payment_check(f)
{
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

    return true;
}
</script>

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');
?>