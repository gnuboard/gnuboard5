<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '상품 복사';
include_once(G4_PATH.'/head.sub.php');
?>

<div class="cbox">
    <h1>상품 복사</h1>

    <table class="frm_tbl">
    <tbody>
    <tr>
        <th scope="row"><label for="new_it_id">상품코드</label></th>
        <td><input type="text" value="<?php echo time(); ?>" id="new_it_id" class="frm_input"></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="button" value="복사하기" class="btn_submit" onclick="_copy('item_copy_update.php?it_id=<?php echo $it_id; ?>&amp;ca_id=<?php echo $ca_id; ?>');">
        <button type="button" onclick="self.close();">창닫기</button>
    </div>

</div>
</form>

<script>
function _copy(link)
{
    var new_it_id = document.getElementById('new_it_id').value;
    opener.parent.location.href = encodeURI(link+'&amp;new_it_id='+new_it_id);
    self.close();
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>