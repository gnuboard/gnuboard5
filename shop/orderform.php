<?
include_once("./_common.php");

set_session("ss_direct", $sw_direct);
// 장바구니가 비어있는가?
if ($sw_direct) {
    $tmp_on_uid = get_session("ss_on_direct");
}
else {
    $tmp_on_uid = get_session("ss_on_uid");
}

if (get_cart_count($tmp_on_uid) == 0) 
    alert("장바구니가 비어 있습니다.", "./cart.php");

// 포인트 결제 대기 필드 추가
//sql_query(" ALTER TABLE `$g4[yc4_order_table]` ADD `od_temp_point` INT NOT NULL AFTER `od_temp_card` ", false);

$g4[title] = "주문서 작성";

include_once("./_head.php");
?>

<img src="<?=$g4[shop_img_path]?>/top_orderform.gif" border="0"><p>

<?
$s_page = 'orderform.php';
$s_on_uid = $tmp_on_uid;
include_once("./cartsub.inc.php");
?>

<form name=forderform method=post action="#" onsubmit="return forderform_check(this);" autocomplete=off>
<input type=hidden name=od_amount    value='<?=$tot_sell_amount?>'>
<input type=hidden name=od_send_cost value='<?=$send_cost?>'>

<!-- 주문하시는 분 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#F3F2FF align=center><img src='<?=$g4[shop_img_path]?>/t_data01.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=100>
        <colgroup width=''>
        <tr>
            <td>이름</td>
            <td><input type=text id=od_name name=od_name value='<?=$member[mb_name]?>' maxlength=20 class=ed></td>
        </tr>

        <? if (!$is_member) { // 비회원이면 ?>
        <tr>
            <td>비밀번호</td>
            <td><input type=password name=od_pwd class=ed maxlength=20>
                영,숫자 3~20자 (주문서 조회시 필요)</td>
        </tr>
        <? } ?>

        <tr>
            <td>전화번호</td>
            <td><input type=text name=od_tel value='<?=$member[mb_tel]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type=text name=od_hp value='<?=$member[mb_hp]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <td rowspan=2>주 소</td>
            <td>
                <input type=text name=od_zip1 size=3 maxlength=3 value='<?=$member[mb_zip1]?>' class=ed readonly>
                -
                <input type=text name=od_zip2 size=3 maxlength=3 value='<?=$member[mb_zip2]?>' class=ed readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2');"><img 
                    src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
            </td>
        </tr>
        <tr>
            <td>
                <input type=text name=od_addr1 size=35 maxlength=50 value='<?=$member[mb_addr1]?>' class=ed readonly>
                <input type=text name=od_addr2 size=20 maxlength=50 value='<?=$member[mb_addr2]?>' class=ed> (상세주소)
            </td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td><input type=text name=od_email size=35 maxlength=100 value='<?=$member[mb_email]?>' class=ed></td>
        </tr>

        <? if ($default[de_hope_date_use]) { // 배송희망일 사용 ?>
        <tr>
            <td>희망배송일</td>
            <td><select name=od_hope_date>
                <option value=''>선택하십시오.
                <? 
                for ($i=0; $i<7; $i++) {
                    $sdate = date("Y-m-d", time()+86400*($default[de_hope_date_after]+$i));
                    echo "<option value='$sdate'>$sdate (".get_yoil($sdate).")\n";
                }
                ?>
                </select></td>
        </tr>
        <? } ?>
        </table>
    </td>
</tr>
</table>

<!-- 받으시는 분 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#F3F2FF align=center><img src='<?=$g4[shop_img_path]?>/t_data03.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=100>
        <colgroup width=''>
        <tr height=30>
            <td colspan=2>
                <input type=checkbox id=same name=same onclick="javascript:gumae2baesong(document.forderform);"> 
                <label for='same'><b>주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크하세요.</b></label></td></tr>
        <tr>
        <tr>
            <td>이름</td>
            <td><input type=text name=od_b_name class=ed maxlength=20></td>
        </tr>
        <tr>
            <td>전화번호</td>
            <td><input type=text name=od_b_tel class=ed
                maxlength=20></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type=text name=od_b_hp class=ed
                maxlength=20></td>
        </tr>
        <tr>
            <td rowspan=2>주 소</td>
            <td>
                <input type=text name=od_b_zip1 size=3 maxlength=3 class=ed readonly>
                -
                <input type=text name=od_b_zip2 size=3 maxlength=3 class=ed readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2');"><img 
                    src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <input type=text name=od_b_addr1 size=35 maxlength=50 class=ed readonly>
                <input type=text name=od_b_addr2 size=20 maxlength=50 class=ed> (상세주소)
            </td>
        </tr>
        <tr>
            <td>전하실말씀</td>
            <td><textarea name=od_memo rows=4 cols=60 class=ed></textarea></td>
        </tr>
        </table>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#FFEFFD align=center><img src='<?=$g4[shop_img_path]?>/t_data04.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <tr>
            <td height=50>
                <?
                $multi_settle == 0;
                $checked = "";

                $escrow_title = "";
                if ($default[de_escrow_use]) {
                    $escrow_title = "에스크로 ";
                }

                // 무통장입금 사용
                if ($default[de_bank_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_bank name='od_settle_case' value='무통장' $checked><label for='od_settle_bank'>무통장입금</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 가상계좌 사용
                if ($default[de_vbank_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_vbank name=od_settle_case value='가상계좌' $checked><label for='od_settle_vbank'>{$escrow_title} 가상계좌</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 계좌이체 사용
                if ($default[de_iche_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_iche name=od_settle_case value='계좌이체' $checked><label for='od_settle_iche'>{$escrow_title} 계좌이체</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 휴대폰 사용
                if ($default[de_hp_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_hp name=od_settle_case value='휴대폰' $checked><label for='od_settle_hp'>휴대폰</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 신용카드 사용
                if ($default[de_card_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_card name=od_settle_case value='신용카드' $checked><label for='od_settle_card'>신용카드</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 회원이면서 포인트사용이면
                $temp_point = 0;
                if ($is_member && $config[cf_use_point]) 
                {
                    // 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
                    if ($member[mb_point] >= $default[de_point_settle])
                    {
                        $temp_point = $tot_amount * ($default[de_point_per] / 100); // 포인트 결제 % 적용
                        $temp_point = (int)((int)($temp_point / 100) * 100); // 100점 단위

                        $member_point = (int)((int)($member[mb_point] / 100) * 100); // 100점 단위
                        if ($temp_point > $member_point) 
                            $temp_point = $member_point;

                        echo "<div style='margin-top:20px;'>결제포인트 : <input type=text id=od_temp_point name=od_temp_point value='0' size=10 class=ed>점 (100점 단위로 입력하세요.)</div>";
                        echo "<div style='margin-top:10px;'>회원님의 보유포인트(".display_point($member[mb_point]).")중 <strong>".display_point($temp_point)."</strong>(주문금액  {$default[de_point_per]}%) 내에서 결제가 가능합니다.</div>";
                        $multi_settle++;
                    }
                }

                if ($multi_settle == 0)
                    echo "<br><span class=point>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</span>";

                if (!$default[de_card_point])
                    echo "<br><br>· '무통장입금' 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.";
                ?>
            </td>
        </tr>
        </table>

        <?
        if ($default[de_bank_use]) {
            // 은행계좌를 배열로 만든후
            $str = explode("\n", trim($default[de_bank_account]));
            if (count($str) <= 1)
            {
                $bank_account = "<input type=hidden name='od_bank_account' value='$str[0]'>$str[0]\n";
            }
            else 
            {
                $bank_account = "\n<select name=od_bank_account>\n";
                $bank_account .= "<option value=''>--------------- 선택하십시오 ---------------\n";
                for ($i=0; $i<count($str); $i++)
                {
                    //$str[$i] = str_replace("\r", "", $str[$i]);
                    $str[$i] = trim($str[$i]);
                    $bank_account .= "<option value='$str[$i]'>$str[$i] \n";
                }
                $bank_account .= "</select> ";
            }
        ?>
        <div id="settle_bank" style="display:none;">
        <table width=100%>
        <tr>
            <td>계좌번호</td>
            <td><?=$bank_account?></td>
        </tr>
        <tr>
            <td>입금자명</td>
            <td><input type=text name=od_deposit_name class=ed size=10 maxlength=20></td>
        </tr>
        </table>
        </div>
        <? } ?>

    </td>
</tr>
</table>

<p align=center>
    <input type="image" src="<?=$g4[shop_img_path]?>/btn_next2.gif" border=0 alt="다음">&nbsp;
    <a href='javascript:history.go(-1);'><img src="<?=$g4[shop_img_path]?>/btn_back1.gif" alt="뒤로" border=0></a>
</form>

<!-- <? if ($default[de_card_use] || $default[de_iche_use]) { echo "결제대행사 : $default[de_card_pg]"; } ?> -->

<? if ($default[de_escrow_use]) { ?>
<script type="text/javascript">
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

<form name="escrow_foot" method="post" action="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp">
<input type="hidden" name="site_cd" value="SR<?=$default['de_kcp_mid']?>">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
    <td align='center'><img src="<?=$g4[shop_path]?>/img/marks_escrow/escrow_foot.gif" width="290" height="92" border="0" usemap="#Map"></td>
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
<area shape="rect" coords="5,62,74,83" href="javascript:escrow_foot_check()" alt="가입사실확인"  onfocus="this.blur()"/>
</map>
</form>
<? } ?>
 
<script type='text/javascript'>
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
    for (i=0; i<settle_case.length; i++)
    {
        if (settle_case[i].checked)
        {
            settle_check = true;
            break;
        }
    }
    if (!settle_check)
    {
        alert("결제방식을 선택하십시오.");
        return false;
    }

    var tot_amount = <?=(int)$tot_amount?>;
    var max_point  = <?=(int)$temp_point?>;

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

            if (temp_point > <?=(int)$member[mb_point]?>) {
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

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[shop]/orderformupdate.php';";
    else
        echo "f.action = './orderformupdate.php';";
    ?>


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
        $('[name=od_deposit_name]').val( $('[name=od_b_name]').val() );
        $("#settle_bank").show();
    });

    $("#od_settle_iche,#od_settle_card,#od_settle_vbank").bind("click", function() {
        $("#settle_bank").hide();
    });
});
</script>

<?
include_once("./_tail.php");
?>