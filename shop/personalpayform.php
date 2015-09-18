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

// 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use']) {
    switch($default['de_pg_service']) {
        case 'lg':
            $g5['body_script'] = ' onload="isActiveXOK();"';
            break;
        case 'inicis':
            $g5['body_script'] = ' onload="javascript:enable_click()"';
            break;
        default:
            $g5['body_script'] = ' onload="CheckPayplusInstall();"';
            break;
    }
}

include_once('./_head.php');

$action_url = G5_HTTPS_SHOP_URL.'/personalpayformupdate.php';

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

require_once('./settle_'.$default['de_pg_service'].'.inc.php');

// 결제대행사별 코드 include (스크립트 등)
require_once('./'.$default['de_pg_service'].'/orderform.1.php');
?>

<form name="forderform" id="forderform" method="post" action="<?php echo $action_url; ?>" onsubmit="return forderform_check(this);" autocomplete="off">
<input type="hidden" name="pp_id" value="<?php echo $pp['pp_id']; ?>">

    <?php
    // 결제대행사별 코드 include (결제대행사 정보 필드)
    require_once('./'.$default['de_pg_service'].'/orderform.2.php');
    ?>

    <section id="sod_frm_pay">
        <h2>개인결제정보</h2>

        <div class="tbl_frm01 tbl_wrap">
            <table>
            <tbody>
            <?php if(trim($pp['pp_content'])) { ?>
            <tr>
                <th>상세내용</th>
                <td><?php echo conv_content($pp['pp_content'], 0); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <th>결제금액</th>
                <td><?php echo display_price($pp['pp_price']); ?></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_name">이름<strong class="sound_only"> 필수</strong></label></th>
                <td><input type="text" name="pp_name" value="<?php echo $pp['pp_name']; ?>" id="pp_name" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_email">이메일<strong class="sound_only"> 필수</strong></label></th>
                <td><input type="text" name="pp_email" value="<?php echo $member['mb_email']; ?>" id="pp_email" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_hp">휴대폰</label></th>
                <td><input type="text" name="pp_hp" value="<?php echo $member['mb_hp']; ?>" id="pp_hp" required class="required frm_input"></td>
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

        if ($multi_settle == 0)
            echo '<p>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</p>';
        ?>
    </section>

    <?php
    // 결제대행사별 코드 include (주문버튼)
    require_once('./'.$default['de_pg_service'].'/orderform.3.php');
    ?>

</form>

<?php
if ($default['de_escrow_use']) {
    // 결제대행사별 코드 include (에스크로 안내)
    require_once('./'.$default['de_pg_service'].'/orderform.4.php');
}
?>

<script>
function forderform_check(f)
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
    <?php if($default['de_pg_service'] == 'kcp') { ?>
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
    <?php } else if($default['de_pg_service'] == 'lg') { ?>
    switch(settle_method)
    {
        case "계좌이체":
            f.LGD_CUSTOM_FIRSTPAY.value = "SC0030";
            f.LGD_CUSTOM_USABLEPAY.value = "SC0030";
            break;
        case "가상계좌":
            f.LGD_CUSTOM_FIRSTPAY.value = "SC0040";
            f.LGD_CUSTOM_USABLEPAY.value = "SC0040";
            break;
        case "휴대폰":
            f.LGD_CUSTOM_FIRSTPAY.value = "SC0060";
            f.LGD_CUSTOM_USABLEPAY.value = "SC0060";
            break;
        case "신용카드":
            f.LGD_CUSTOM_FIRSTPAY.value = "SC0010";
            f.LGD_CUSTOM_USABLEPAY.value = "SC0010";
            break;
        default:
            f.LGD_CUSTOM_FIRSTPAY.value = "무통장";
            break;
    }
    <?php }  else if($default['de_pg_service'] == 'inicis') { ?>
    switch(settle_method)
    {
        case "계좌이체":
            f.gopaymethod.value = "onlydbank";
            break;
        case "가상계좌":
            f.gopaymethod.value = "onlyvbank";
            break;
        case "휴대폰":
            f.gopaymethod.value = "onlyhpp";
            break;
        case "신용카드":
            f.gopaymethod.value = "onlycard";
            break;
        default:
            f.gopaymethod.value = "무통장";
            break;
    }
    <?php } ?>

    // 결제정보설정
    <?php if($default['de_pg_service'] == 'kcp') { ?>
    f.buyr_name.value = f.pp_name.value;
    f.buyr_mail.value = f.pp_email.value;
    f.buyr_tel1.value = f.pp_hp.value;
    f.buyr_tel2.value = f.pp_hp.value;
    f.rcvr_name.value = f.pp_name.value;
    f.rcvr_tel1.value = f.pp_hp.value;
    f.rcvr_tel2.value = f.pp_hp.value;
    f.rcvr_mail.value = f.pp_email.value;

    if(f.pay_method.value != "무통장") {
        if(jsf__pay( f )) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
    <?php } ?>
    <?php if($default['de_pg_service'] == 'lg') { ?>
    f.LGD_BUYER.value = f.pp_name.value;
    f.LGD_BUYEREMAIL.value = f.pp_email.value;
    f.LGD_BUYERPHONE.value = f.pp_hp.value;
    f.LGD_AMOUNT.value = f.good_mny.value;
    f.LGD_TAXFREEAMOUNT.value = 0;

    if(f.LGD_CUSTOM_FIRSTPAY.value != "무통장") {
          Pay_Request("<?php echo $pp_id; ?>", f.LGD_AMOUNT.value, f.LGD_TIMESTAMP.value);
    } else {
        f.submit();
    }
    <?php } ?>
    <?php if($default['de_pg_service'] == 'inicis') { ?>
    f.buyername.value   = f.pp_name.value;
    f.buyeremail.value  = f.pp_email.value;
    f.buyertel.value    = f.pp_hp.value;

    if(f.gopaymethod.value != "무통장") {
        if(!set_encrypt_data(f))
            return false;

        return pay(f);
    } else {
        return true;
    }
    <?php } ?>
}
</script>

<?php
include_once('./_tail.php');

// 결제대행사별 코드 include (스크립트 실행)
require_once('./'.$default['de_pg_service'].'/orderform.5.php');
?>