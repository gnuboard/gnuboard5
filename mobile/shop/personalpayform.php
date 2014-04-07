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

require_once(G5_MSHOP_PATH.'/settle_'.$default['de_pg_service'].'.inc.php');

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

// 주문폼과 공통 사용을 위해 추가
$od_id = $pp_id;
$tot_price = $pp['pp_price'];
$goods = $pp['pp_name'].'님 개인결제';
?>

<div id="sod_approval_frm">
    <?php
    // 결제대행사별 코드 include (결제등록 필드)
    require_once(G5_MSHOP_PATH.'/'.$default['de_pg_service'].'/orderform.1.php');
    ?>
</div>

<div id="sod_frm">
    <form name="forderform" method="post" action="<?php echo $action_url; ?>" autocomplete="off">
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

        if ($default['de_vbank_use'] || $default['de_iche_use'] || $default['de_card_use'] || $default['de_hp_use']) {
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

        if ($default['de_vbank_use'] || $default['de_iche_use'] || $default['de_card_use'] || $default['de_hp_use']) {
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

    <?php
    // 결제대행사별 코드 include (결제대행사 정보 필드 및 주분버튼)
    require_once(G5_MSHOP_PATH.'/'.$default['de_pg_service'].'/orderform.2.php');
    ?>

    <div id="show_progress" style="display:none;">
        <img src="<?php echo G5_MOBILE_URL; ?>/shop/img/loading.gif" alt="">
        <span>결제진행 중입니다. 잠시만 기다려 주십시오.</span>
    </div>
    </form>

    <?php
    if ($default['de_escrow_use']) {
        // 결제대행사별 코드 include (에스크로 안내)
        require_once(G5_MSHOP_PATH.'/'.$default['de_pg_service'].'/orderform.3.php');
    }
    ?>
</div>

<script>
/* 결제방법에 따른 처리 후 결제등록요청 실행 */
var settle_method = "";

function pay_approval()
{
    var f = document.sm_form;
    var pf = document.forderform;

    // 필드체크
    if(!payfield_check(pf))
        return false;

    // 금액체크
    if(!payment_check(pf))
        return false;

    <?php if($default['de_pg_service'] == 'kcp') { ?>
    f.buyr_name.value = pf.pp_name.value;
    f.buyr_mail.value = pf.pp_email.value;
    f.buyr_tel1.value = pf.pp_hp.value;
    f.buyr_tel2.value = pf.pp_hp.value;
    f.rcvr_name.value = pf.pp_name.value;
    f.rcvr_tel1.value = pf.pp_hp.value;
    f.rcvr_tel2.value = pf.pp_hp.value;
    f.rcvr_mail.value = pf.pp_email.value;
    f.settle_method.value = settle_method;
    <?php } else if($default['de_pg_service'] == 'lg') { ?>
    var pay_method = "";
    switch(settle_method) {
        case "계좌이체":
            pay_method = "SC0030";
            break;
        case "가상계좌":
            pay_method = "SC0040";
            break;
        case "휴대폰":
            pay_method = "SC0060";
            break;
        case "신용카드":
            pay_method = "SC0010";
            break;
    }
    f.LGD_CUSTOM_FIRSTPAY.value = pay_method;
    f.LGD_BUYER.value = pf.pp_name.value;
    f.LGD_BUYEREMAIL.value = pf.pp_email.value;
    f.LGD_BUYERPHONE.value = pf.pp_hp.value;
    f.LGD_AMOUNT.value = f.good_mny.value;
    <?php if($default['de_tax_flag_use']) { ?>
    f.LGD_TAXFREEAMOUNT.value = pf.comm_free_mny.value;
    <?php } ?>
    <?php } ?>

    var new_win = window.open("about:blank", "tar_opener", "scrollbars=yes,resizable=yes");
    f.target = "tar_opener";

    f.submit();
}

function forderform_check()
{
    var f = document.forderform;

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