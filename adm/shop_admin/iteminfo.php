<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');

$it_id = trim($_POST['it_id']);
$gubun = $_POST['gubun'] ? $_POST['gubun'] : 'wear';
if($it['it_id'])
    $it_id = $it['it_id'];
else {
    $sql = " select it_id, it_info_gubun, it_info_value from {$g4['shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);
}

if(!$gubun && $it['it_info_gubun'])
    $gubun = $it['it_info_gubun'];
?>

<table class="frm_tbl">
<colgroup>
    <col class="grid_3">
    <col>
</colgroup>
<tbody>
<?php
if($it['it_info_value'])
    $info_value = unserialize($it['it_info_value']);
$article = $item_info[$gubun]['article'];
if ($article) {
    foreach($article as $key=>$value) {
        $el_name    = $key;
        $el_title   = $value[0];
        $el_example = $value[1];
        $el_value = '상품페이지 참고';

        if($gubun == $it['it_info_gubun'] && $info_value[$key])
            $el_value = $info_value[$key];
?>

<tr>
    <th scope="row"><label for="ii_article_<?php echo $el_name; ?>"><?php echo $el_title; ?></label></th>
    <td>
        <input type="hidden" name="ii_article[]" value="<?php echo $el_name; ?>">
        <?php if ($el_example != "") echo help($el_example); ?>
        <input type="text" name="ii_value[]" value="<?php echo $el_value; ?>" id="ii_article_<?php echo $el_name; ?>" required class="frm_input required" />
    </td>
</tr>
<?php
    }
}
?>
</tbody>
</table>