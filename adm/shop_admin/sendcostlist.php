<?php
$sub_menu = "400750";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$sql_common = " from {$g4['yc4_sendcost_table']} ";
$sql_order = " order by sc_no ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $no = $row[cnt];


$sql = " select *
            $sql_common
            $sql_order ";
$result = sql_query($sql);

$g4['title'] = "추가배송비관리";
include_once ($g4['admin_path']."/admin.head.php");
?>

<style type="text/css">
<!--
form { display: inline; }
-->
</style>

<form id="fsendcost" method="post" action="./sendcostupdate.php">
<table cellpadding="0" cellspacing="0" width="100%">
<tr class="ht">
    <td width="150">지역명</td>
    <td><input type="text" name="sc_name" class="ed" size="20" /></td>
</tr>
<tr class="ht">
    <td>우편번호범위</td>
    <td><input type="text" name="sc_zip1" class="ed" size="10" maxlength="7" /> ~ <input type="text" name="sc_zip2" class="ed" size="10" maxlength="7" /> <button type="button" id="findzip">우편번호찾기</button></td>
</tr>
<tr class="ht">
    <td>추가배송료</td>
    <td><input type="text" name="sc_amount" class="ed" size="10" />원</td>
</tr>
<tr class="ht">
    <td colspan="2"><input type="submit" class="btn1" value=" 확 인 " /></td>
</tr>
</table>
</form>
<p></p>

<table cellpadding="0" cellspacing="0" width="100%">
<colgroup width="100">
<colgroup width="">
<colgroup width="300">
<colgroup width="100">
<colgroup width="80">
<tr><td colspan="5" height="2" bgcolor="#0E87F9"></td></tr>
<tr align="center" class="ht">
    <td>번호</td>
    <td>지역명</td>
    <td>우편번호범위</td>
    <td>배송료</td>
    <td>삭제</td>
</tr>
<tr><td colspan="5" height="1" bgcolor="#CCCCCC"></td></tr>
<?
for($i=0; $row=sql_fetch_array($result); $i++) {
    $s_del = icon("삭제", "javascript:del('./sendcostdelete.php?sc_no={$row['sc_no']}');");

    if ($i)
        echo '<tr><td colspan="5" height="1" bgcolor="F1F1F1"></td></tr>';

    $list = $i%2;

    $zip1 = preg_replace("/([0-9]{3})([0-9]{3})/", "\\1-\\2", $row['sc_zip1']);
    $zip2 = preg_replace("/([0-9]{3})([0-9]{3})/", "\\1-\\2", $row['sc_zip2']);
    echo "
    <tr class=\"list$list center ht\">
        <td align=\"center\">$no</td>
        <td align=\"center\">". stripslashes($row['sc_name']) . "</td>
        <td align=\"center\">".$zip1." ~ ".$zip2."</td>
        <td align=\"center\">".number_format($row['sc_amount'])."원</td>
        <td align=\"center\">$s_del</td>
    </tr>";

    $no--;
}

if ($i == 0)
    echo '<tr><td colspan="5" align="center" height="100" bgcolor="#ffffff"><span class="point">자료가 한건도 없습니다.</span></td></tr>'."\n";
?>
<tr><td colspan="5" height="1" bgcolor="#CCCCCC"></td></tr>
</table>

<script>
$(function() {
    $("#findzip").click(function() {
        window.open("./sendcostzipcode.php", "zipcode", "width=400, height=350, left=100, top=50, scrollbars=yes");
    });

    $("#fsendcost").submit(function() {
        var patt = /[^0-9]/g;
        var name = $.trim($("input[name=sc_name]").val());
        var zip1 = $.trim($("input[name=sc_zip1]").val()).replace(patt, "");
        var zip2 = $.trim($("input[name=sc_zip2]").val()).replace(patt, "");
        var amount = $.trim($("input[name=sc_amount]").val());

        if(!name) {
            alert("지역명을 입력해 주세요.");
            return false;
        }
        if(!zip1 || !zip2) {
            alert("우편번호 범위를 입력해 주세요.");
            return false;
        }
        if(parseInt(zip1) >= parseInt(zip2)) {
            alert("우편번호 범위가 올바른지 확인해 주세요.");
            return false;
        }
        if(!amount) {
            alert("추가배송료를 입력해 주세요.");
            return false;
        }

        return true;
    });
});
</script>

<?php
include_once ($g4['admin_path']."/admin.tail.php");
?>