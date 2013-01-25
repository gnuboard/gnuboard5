<?php
$sub_menu = "400800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "쿠폰";

if($w == 'u') {
    $html_title .= "수정";

    $sql = " select * from {$g4['yc4_coupon_table']} where cp_no = '$cp_no' ";
    $write = sql_fetch($sql);

    if(!$write['cp_id']) {
        alert("쿠폰 정보가 없습니다.");
    }
} else {
    $html_title .= "등록";
}

$g4['title'] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<style type="text/css">
<!--
#cp_trunc_dsp { display: none; }
#cp_minimum_dsp { display: none; }
#cp_maximum_dsp { display: none; }
#ca_id_dsp { display: none; }
-->
</style>

<form id="fcoupon" method="post" action="./couponformupdate.php" style="margin:0px;">
<input type="hidden" name="cp_no" value="<? echo $cp_no; ?>" />
<input type="hidden" name="w"    value="<? echo $w; ?>" />
<input type="hidden" name="sst"  value="<? echo $sst ?>" />
<input type="hidden" name="sod"  value="<? echo $sod; ?>" />
<input type="hidden" name="sfl"  value="<? echo $sfl; ?>" />
<input type="hidden" name="stx"  value="<? echo $stx; ?>" />
<input type="hidden" name="page" value="<? echo $page; ?>" />
<table cellpadding="0" cellspacing="0" width="100%">
<colgroup width="15%"></colgroup>
<colgroup width="85%" bgcolor="#ffffff"></colgroup>
<tr><td colspan="2" height="2" bgcolor="#0E87F9"></td></tr>
<? if($w == 'u') { ?>
<tr class="ht">
    <td>쿠폰번호</td>
    <td><? echo $write['cp_id']; ?></td>
</tr>
<? } ?>
<tr class="ht">
    <td>쿠폰명</td>
    <td><input type="text" name="cp_subject" size="60" class="ed" value="<? echo $write['cp_subject']; ?>" /></td>
</tr>
<tr class="ht">
    <td>쿠폰종류</td>
    <td><input type="radio" name="cp_type" value="0" <? if(!$write['cp_type'] || $w == '') echo "checked=\"checked\""; ?> /> 상품할인&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_type" value="1" <? if($write['cp_type'] == 1) echo "checked=\"checked\""; ?> /> 결제금액할인&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_type" value="2" <? if($write['cp_type'] == 2) echo "checked=\"checked\""; ?> /> 배송비할인</td>
</tr>
<tr class="ht">
    <td>사용대상</td>
    <td><input type="radio" name="cp_target" value="0" <? if(!$write['cp_target'] || $w == '') echo "checked=\"checked\""; ?> /> 상품&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_target" value="1" <? if($write['cp_target'] == 1) echo "checked=\"checked\""; ?> /> 카테고리&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_target" value="2" <? if($write['cp_target'] == 2) echo "checked=\"checked\""; ?> /> 전체상품&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_target" value="3" <? if($write['cp_target'] == 3) echo "checked=\"checked\""; ?> disabled="disabled" /> 주문서</td>
</tr>
<tr class="ht">
    <td>할인방식</td>
    <td><input type="radio" name="cp_method" value="0" <? if(!$write['cp_method'] || $w == '') echo "checked=\"checked\""; ?> /> 정액할인&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_method" value="1" <? if($write['cp_method']) echo "checked=\"checked\""; ?> /> 정율(%)할인</td>
</tr>
<tr class="ht">
    <td><span id="cp_amount_label">할인금액</span></td>
    <td><input type="text" name="cp_amount" size="10" class="ed" value="<? echo $write['cp_amount']; ?>" /><span id="cp_amount_unit">원</span></td>
</tr>
<tr id="cp_trunc_dsp" class="ht">
    <td>절사금액</td>
    <td>
        <select name="cp_trunc">
            <option value="1">1원단위</option>
            <option value="10">10원단위</option>
            <option value="100">100원단위</option>
        </select>
    </td>
</tr>
<tr id="cp_maximum_dsp" class="ht">
    <td>최대할인금액</td>
    <td><input type="text" name="cp_maximum" size="10" class="ed" value="<? echo $write['cp_maximum']; ?>" />원&nbsp;&nbsp;* 0이면 제한없음</td>
</tr>
<tr id="cp_minimum_dsp" class="ht">
    <td>최소주문금액</td>
    <td><input type="text" name="cp_minimum" size="10" class="ed" value="<? echo $write['cp_minimum']; ?>" />원&nbsp;&nbsp;* 0이면 제한없음</td>
</tr>
<tr class="ht">
    <td>사용기한</td>
    <td><input type="text" name="cp_start" size="20" class="ed" value="<? echo $write['cp_start']; ?>" />&nbsp;~&nbsp;<input type="text" name="cp_end" size="20" class="ed" value="<? echo $write['cp_end']; ?>" />&nbsp;&nbsp;* 입력예: <? echo date("Y-m-d", (time() + 86400 * 7)); ?></td>
</tr>
<tr id="it_id_dsp" class="ht">
    <td>적용상품</td>
    <td><input type="text" name="it_id" size="50" class="ed" value="<? echo $write['it_id']; ?>" />&nbsp;<button type="button" id="item_search">상품찾기</button></td>
</tr>
<tr id="ca_id_dsp" class="ht">
    <td>적용카테고리</td>
    <td><input type="text" name="ca_id" size="50" class="ed" value="<? echo $write['ca_id']; ?>" />&nbsp;&nbsp;<input type="checkbox" name="allcategory" value="1" />전체카테고리&nbsp;<button type="button" id="category_search">카테고리찾기</button></td>
</tr>
<tr class="ht">
    <td>적용회원</td>
    <td><input type="text" name="mb_id" size="50" class="ed" value="<? echo $write['mb_id']; ?>" />&nbsp;&nbsp;<input type="checkbox" name="allmember" value="1" />전체회원&nbsp;<button type="button" id="member_search">회원찾기</button></td>
</tr>
<tr class="ht">
    <td>쿠폰사용</td>
    <td><input type="radio" name="cp_use" value="1" <? if($write['cp_use'] || $w == '') echo "checked=\"checked\""; ?> /> 사용함&nbsp;&nbsp;&nbsp;<input type="radio" name="cp_use" value="0" <? if(!$write['cp_use'] && $w == 'u') echo "checked=\"checked\""; ?> /> 사용안함</td>
</tr>
<tr><td colspan="2" height="1" bgcolor="#CCCCCC"><td></tr>
</table>

<p align="center">
    <input type="submit" class="btn1" accesskey="s" value="  확  인  ">&nbsp;
    <input type="button" class="btn1" accesskey="l" value="  목  록  " onclick="document.location.href='./couponlist.php?page=<?=$page?>';">
</p>
</form>

<script>
$(function() {
    <?php if($w == 'u') { ?>
    $("select[name=cp_trunc]").val("<? echo $write['cp_trunc']; ?>");
    if("<?php echo $write['cp_method']; ?>" == "1") {
        $("#cp_amount_label").text("할인비율");
        $("#cp_amount_unit").text("%");
        $("#cp_trunc_dsp").show();
        $("#cp_maximum_dsp").show();
    }
    var u_cp_type = "<?php echo $write['cp_type']; ?>";
    if(u_cp_type == "0") { // 상품할인
        $("#cp_minimum_dsp").hide();
        $("input[name=cp_target]").not("[value=3]").attr("disabled", false);
        $("input[name=cp_target]").filter("[value=3]").attr("disabled", true);
        $("input[name=cp_method]").not("[value=0]").attr("disabled", false);
        $("#it_id_dsp").show();
        $("#ca_id_dsp").hide();
    } else if(u_cp_type == "2") { // 배송비할인
        $("#cp_minimum_dsp").show();
        $("input[name=cp_target]").not("[value=3]").attr("disabled", true);
        $("input[name=cp_target]").filter("[value=3]").attr("disabled", false);
        $("input[name=cp_method]").not("[value=0]").attr("disabled", true);
        $("#cp_amount_label").text("할인금액");
        $("#cp_amount_unit").text("원");
        $("#cp_trunc_dsp").hide();
        $("#cp_maximum_dsp").hide();
        $("#it_id_dsp").hide();
        $("#ca_id_dsp").hide();
    } else {
        $("#cp_minimum_dsp").show();
        $("input[name=cp_target]").not("[value=3]").attr("disabled", true);
        $("input[name=cp_target]").filter("[value=3]").attr("disabled", false);
        $("input[name=cp_method]").not("[value=0]").attr("disabled", false);
        $("#it_id_dsp").hide();
        $("#ca_id_dsp").hide();
    }
    var u_cp_target = "<?php echo $write['cp_target']; ?>";
    if(u_cp_target == "2") { // 전체상품
        $("#it_id_dsp").hide();
        $("#ca_id_dsp").hide();
    } else if(u_cp_target == "1") { // 카테고리
        $("#it_id_dsp").hide();
        $("#ca_id_dsp").show();
    } else if(u_cp_target == "0") {
        $("#it_id_dsp").show();
        $("#ca_id_dsp").hide();
    }
    <?php } ?>
    $("input[name=cp_type]").click(function() {
        var val = $(this).val();
        if(val == "0") { // 상품할인
            $("#cp_minimum_dsp").hide();
            $("input[name=cp_target]").not("[value=3]").attr("disabled", false);
            $("input[name=cp_target]").filter("[value=0]").attr("checked", true);
            $("input[name=cp_target]").filter("[value=3]").attr("disabled", true);
            $("input[name=cp_method]").not("[value=0]").attr("disabled", false);
            $("#it_id_dsp").show();
            $("#ca_id_dsp").hide();
        } else if(val == "2") { // 배송비할인
            $("#cp_minimum_dsp").show();
            $("input[name=cp_target]").not("[value=3]").attr("disabled", true);
            $("input[name=cp_target]").filter("[value=3]").attr("checked", true).attr("disabled", false);
            $("input[name=cp_method]").filter("[value=0]").attr("checked", true);
            $("input[name=cp_method]").not("[value=0]").attr("disabled", true);
            $("#cp_amount_label").text("할인금액");
            $("#cp_amount_unit").text("원");
            $("#cp_trunc_dsp").hide();
            $("#cp_maximum_dsp").hide();
            $("#it_id_dsp").hide();
            $("#ca_id_dsp").hide();
        } else {
            $("#cp_minimum_dsp").show();
            $("input[name=cp_target]").not("[value=3]").attr("disabled", true);
            $("input[name=cp_target]").filter("[value=3]").attr("checked", true).attr("disabled", false);
            $("input[name=cp_method]").not("[value=0]").attr("disabled", false);
            $("#it_id_dsp").hide();
            $("#ca_id_dsp").hide();
        }
    });

    $("input[name=cp_method]").click(function() {
        var val = $(this).val();
        if(val == "1") { // 정율할인
            $("#cp_amount_label").text("할인비율");
            $("#cp_amount_unit").text("%");
            $("#cp_trunc_dsp").show();
            $("#cp_maximum_dsp").show();
        } else {
            $("#cp_amount_label").text("할인금액");
            $("#cp_amount_unit").text("원");
            $("#cp_trunc_dsp").hide();
            $("#cp_maximum_dsp").hide();
        }
    });

    $("input[name=cp_target]").click(function() {
        var val = $(this).val();
        if(val == "2") { // 전체상품
            $("#it_id_dsp").hide();
            $("#ca_id_dsp").hide();
        } else if(val == "1") { // 카테고리
            $("#it_id_dsp").hide();
            $("#ca_id_dsp").show();
        } else if(val == "0") {
            $("#it_id_dsp").show();
            $("#ca_id_dsp").hide();
        }
    });

    $("input[name=allcategory]").click(function() {
        if($(this).is(":checked")) {
            $("input[name=ca_id]").val("전체카테고리");
        } else {
            $("input[name=ca_id]").val("");
        }
    });

    $("input[name=allmember]").click(function() {
        if($(this).is(":checked")) {
            $("input[name=mb_id]").val("전체회원");
        } else {
            $("input[name=mb_id]").val("");
        }
    });

    $("form#fcoupon").submit(function() {
        var cp_subject = $.trim($("input[name=cp_subject]").val());
        var cp_type = $("input[name=cp_type]:checked").val();
        var cp_target = $("input[name=cp_target]:checked").val();
        var cp_method = $("input[name=cp_method]:checked").val();
        var cp_amount = $.trim($("input[name=cp_amount]").val()).replace(/[^0-9]/, "");
        var cp_start = $.trim($("input[name=cp_start]").val());
        var cp_end = $.trim($("input[name=cp_end]").val());
        var it_id = $.trim($("input[name=it_id]").val());
        var ca_id = $.trim($("input[name=ca_id]").val());
        var mb_id = $.trim($("input[name=mb_id]").val());

        if(cp_subject == "") {
            alert("쿠폰명을 입력해 주세요.");
            return false;
        }
        if(cp_amount == "") {
            if(cp_method == "1") {
                alert("할인비율을 입력해 주세요.");
            } else {
                alert("할인금액을 입력해 주세요.");
            }

            return false;
        } else {
            amount = parseInt(cp_amount);

            if(cp_method == "1") {
                if(amount < 1 || amount > 99) {
                    alert("할인비율을 1과 99 사이로 입력해 주세요.");
                    return false;
                }
            } else {
                if(amount < 1) {
                    alert("할인금액을 1원이상 입력해 주세요.");
                    return false;
                }
            }
        }
        if(cp_start == "") {
            alert("사용시작일을 입력해 주세요.");
            return false;
        } else {
            var patt = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
            if(!patt.test(cp_start)) {
                var d = new Date();
                alert("사용시작일을 "+d.getFullYear()+"-"+leadzeros((d.getMonth()+1), 2)+"-"+leadzeros(d.getDate(), 2)+"형식으로 입력해 주세요.");
                return false;
            }
        }
        if(cp_end == "") {
            alert("사용종료일을 입력해 주세요.");
            return false;
        } else {
            var patt = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
            if(!patt.test(cp_end)) {
                var d = new Date();
                alert("사용종료일을 "+d.getFullYear()+"-"+leadzeros((d.getMonth()+1), 2)+"-"+leadzeros(d.getDate(), 2)+"형식으로 입력해 주세요.");
                return false;
            }
        }
        if(cp_target == "0" && it_id == "") {
            alert("적용상품을 입력해 주세요.");
            return false;
        }
        if(cp_target == "1" && ca_id == "") {
            alert("적용카테고리를 입력해 주세요.");
            return false;
        }
        if(mb_id == "") {
            alert("회원을 선택해 주세요.");
            return false;
        }

        return true;
    });

    // 상품찾기창
    $("#item_search").click(function() {
        window.open("./coupon_item.php?w=<? echo $w; ?>", "itemsearch", "width=400, height=350, left=100, top=50, scrollbars=yes");
    });

    // 카테고리찾기창
    $("#category_search").click(function() {
        window.open("./coupon_category.php?w=<? echo $w; ?>", "categorysearch", "width=400, height=350, left=100, top=50, scrollbars=yes");
    });

    // 회원찾기창
    $("#member_search").click(function() {
        window.open("./coupon_member.php?w=<? echo $w; ?>", "membersearch", "width=400, height=350, left=100, top=50, scrollbars=yes");
    });
});

function leadzeros(n, digits) {
    var zero = "";
    n = n.toString();

    if (n.length < digits) {
    for (i = 0; i < digits - n.length; i++)
      zero += "0";
    }
    return zero + n;
}
</script>

<?
include_once ($g4['admin_path'].'/admin.tail.php');
?>