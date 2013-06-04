<?php
$sub_menu = '400650';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

if($_GET['sch_target'] == 1) {
    $html_title = '분류검색';
    $t_name = '분류명';
    $t_id = '분류코드';
} else {
    $html_title = '상품검색';
    $t_name = '상품명';
    $t_id = '상품코드';
}

$g4['title'] = $html_title;
include_once(G4_PATH.'/head.sub.php');

if($_GET['sch_word']) {
    if($_GET['sch_target'] == 1) {
        $sql = " select ca_id as t_id, ca_name as t_name from {$g4['shop_category_table']} where ca_use = '1' and ca_name like '%$sch_word%' ";
    } else {
        $sql = " select it_id as t_id, it_name as t_name from {$g4['shop_item_table']} where it_use = '1' and it_name like '%$sch_word%' ";
    }

    $result = sql_query($sql);
}
?>

<div id="sch_target_frm">
<form name="ftarget" method="get">
<input type="hidden" name="sch_target" value="<?php echo $_GET['sch_target']; ?>">
<div>
    <label for="sch_word"><?php echo $t_name; ?></label>
    <input type="text" name="sch_word" id="sch_word" class="frm_input required" required size="20">
</div>
<?php if($_GET['sch_word']) { ?>
<table>
<tr>
    <th><?php echo $t_name; ?></th>
    <th><?php echo $t_id; ?></th>
    <th>선택</th>
</tr>
<?php
for($i=0; $row=sql_fetch_array($result); $i++) {
?>
<tr>
    <td><?php echo $row['t_name']; ?></td>
    <td><?php echo $row['t_id']; ?></td>
    <td><button type="button" onclick="sel_target_id('<?php echo $row['t_id']; ?>');">선택</button>
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
function sel_target_id(id)
{
    var f = window.opener.document.fcouponform;
    f.cp_target.value = id;

    window.close();
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>