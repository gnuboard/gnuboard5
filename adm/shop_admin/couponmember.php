<?php
$sub_menu = '400650';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$html_title = '회원검색';

$g4['title'] = $html_title;
include_once(G4_PATH.'/head.sub.php');

if($_GET['mb_name']) {
    $sql = " select mb_id, mb_name from {$g4['member_table']} where mb_leave_date = '' and mb_intercept_date ='' and mb_name like '%$mb_name%' ";
    $result = sql_query($sql);
}
?>

<div id="sch_member_frm">
<form name="fmember" method="get">
<div>
    <label for="mb_name">회원이름</label>
    <input type="text" name="mb_name" id="mb_name" class="frm_input required" required size="20">
</div>
<?php if($_GET['mb_name']) { ?>
<table>
<tr>
    <th>회원이름</th>
    <th>회원아이디</th>
    <th>선택</th>
</tr>
<?php
for($i=0; $row=sql_fetch_array($result); $i++) {
?>
<tr>
    <td><?php echo $row['mb_name']; ?></td>
    <td><?php echo $row['mb_id']; ?></td>
    <td><button type="button" onclick="sel_member_id('<?php echo $row['mb_id']; ?>');">선택</button>
</tr>
<?php
}

if($i ==0)
    echo '<tr><td colspan="3">검색된 자료가 없습니다.</td></tr>';
?>
</table>
<?php } ?>
<div>
    <input type="submit" value="검색">
    <button type="button" onclick="window.close();">닫기</button>
</div>
</form>
</div>

<script>
function sel_member_id(id)
{
    var f = window.opener.document.fcouponform;
    f.mb_id.value = id;

    window.close();
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>