<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

require_once(G5_MSHOP_PATH.'/settle_'.$default['de_pg_service'].'.inc.php');

$tablet_size = "1.0"; // 화면 사이즈 조정 - 기기화면에 맞게 수정(갤럭시탭,아이패드 - 1.85, 스마트폰 - 1.0)
?>

<div id="sod_approval_frm">
    <?php
    // 결제대행사별 코드 include (결제등록 필드)
    require_once(G5_MSHOP_PATH.'/'.$default['de_pg_service'].'/orderform.1.php');
    ?>
</div>

<div id="m_pv_sod_frm">
    <form name="forderform" method="post" action="<?php echo $order_action_url; ?>" autocomplete="off">
    <input type="hidden" name="pp_id" value="<?php echo $pp['pp_id']; ?>">
    <section id="m_sod_frm_orderer">
        <h2>개인결제정보</h2>

        <div class="odf_tbl">
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
                <th scope="row"><label for="pp_name">이름</label></th>
                <td><input type="text" name="pp_name" value="<?php echo get_text($pp['pp_name']); ?>" id="pp_name" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_email">이메일</label></th>
                <td><input type="email" name="pp_email" value="<?php echo $member['mb_email']; ?>" id="pp_email" required class="required frm_input"></td>
            </tr>
            <tr>
                <th scope="row"><label for="pp_hp">휴대폰</label></th>
                <td><input type="text" name="pp_hp" value="<?php echo get_text($member['mb_hp']); ?>" id="pp_hp" class="frm_input"></td>
            </tr>
            </tbody>
            </table>
        </div>

        <?php
        $multi_settle = 0;
        $checked = '';

        $escrow_title = "";
        if ($default['de_escrow_use']) {
            $escrow_title = "에스크로 ";
        }

        if ($default['de_vbank_use'] || $default['de_iche_use'] || $default['de_card_use'] || $default['de_hp_use']) {
        echo '<fieldset id="sod_frm_paysel">';
        echo '<legend>결제방법 선택</legend>';
		echo '<ul class="pay_way chk_box">';
        }

        // 가상계좌 사용
        if ($default['de_vbank_use']) {
            $multi_settle++;
            echo '<li><input type="radio" id="pp_settle_vbank" name="pp_settle_case" value="가상계좌" '.$checked.'> <label for="pp_settle_vbank"><span></span>'.$escrow_title.'가상계좌</label></li>'.PHP_EOL;
            $checked = '';
        }

        // 계좌이체 사용
        if ($default['de_iche_use']) {
            $multi_settle++;
            echo '<li><input type="radio" id="pp_settle_iche" name="pp_settle_case" value="계좌이체" '.$checked.'> <label for="pp_settle_iche"><span></span>'.$escrow_title.'계좌이체</label></li>'.PHP_EOL;
            $checked = '';
        }

        // 휴대폰 사용
        if ($default['de_hp_use']) {
            $multi_settle++;
            echo '<li><input type="radio" id="pp_settle_hp" name="pp_settle_case" value="휴대폰" '.$checked.'> <label for="pp_settle_hp"><span></span>휴대폰</label></li>'.PHP_EOL;
            $checked = '';
        }

        // 신용카드 사용
        if ($default['de_card_use']) {
            $multi_settle++;
            echo '<li><input type="radio" id="pp_settle_card" name="pp_settle_case" value="신용카드" '.$checked.'> <label for="pp_settle_card"><span></span>신용카드</label></li>'.PHP_EOL;
            $checked = '';
        }

        if ($default['de_vbank_use'] || $default['de_iche_use'] || $default['de_card_use'] || $default['de_hp_use']) {
        echo '</ul>';
        echo '</fieldset>';

        }
        ?>

        <?php
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
    <?php } else if($default['de_pg_service'] == 'inicis') { ?>
    var paymethod = "";
    var width = 330;
    var height = 480;
    var xpos = (screen.width - width) / 2;
    var ypos = (screen.width - height) / 2;
    var position = "top=" + ypos + ",left=" + xpos;
    var features = position + ", width=320, height=440";
    switch(settle_method) {
        case "계좌이체":
            paymethod = "bank";
            break;
        case "가상계좌":
            paymethod = "vbank";
            break;
        case "휴대폰":
            paymethod = "mobile";
            break;
        case "신용카드":
            paymethod = "wcard";
            break;
    }
    f.P_AMT.value = f.good_mny.value;
    f.P_UNAME.value = pf.pp_name.value;
    f.P_MOBILE.value = pf.pp_hp.value;
    f.P_EMAIL.value = pf.pp_email.value;
    <?php if($default['de_tax_flag_use']) { ?>
    f.P_TAX.value = pf.comm_vat_mny.value;
    f.P_TAXFREE = pf.comm_free_mny.value;
    <?php } ?>
    f.P_RETURN_URL.value = "<?php echo $return_url.$pp_id; ?>";
    f.action = "https://mobile.inicis.com/smart/" + paymethod + "/";
    <?php } ?>

    //var new_win = window.open("about:blank", "tar_opener", "scrollbars=yes,resizable=yes");
    //f.target = "tar_opener";

    // 주문 정보 임시저장
    var order_data = $(pf).serialize();
    var save_result = "";
    $.ajax({
        type: "POST",
        data: order_data,
        url: g5_url+"/shop/ajax.orderdatasave.php",
        cache: false,
        async: false,
        success: function(data) {
            save_result = data;
        }
    });

    if(save_result) {
        alert(save_result);
        return false;
    }

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