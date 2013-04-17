<?
$sub_menu = '400740';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$html_title = '배송업체';
$g4['title'] = $html_title;

if ($w == "u") {
    $html_title .= ' 수정';
    $readonly = ' readonly';

    $sql = " select * from {$g4['shop_delivery_table']} where dl_id = '$dl_id' ";
    $dl = sql_fetch($sql);
    if (!$dl['dl_id']) alert('등록된 자료가 없습니다.');
}
else
{
    $html_title .= ' 입력';
    $dl['dl_url'] = "http://";
}

include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="fdeliverycodeform" action="./deliverycodeformupdate.php" method="post">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="dl_id" value="<?=$dl_id?>">

<section class="cbox">
    <h2><?=$html_title?></h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="dl_company">배송업체명</label></th>
        <td>
            <input type="text" name="dl_company" value="<?=stripslashes($dl['dl_company']) ?>" id="dl_company" required class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="dl_url">화물추적 URL</label></th>
        <td>
           <input type="text" class="frm_input" name="dl_url" value="<?=stripslashes($dl['dl_url']) ?>" id="dl_url" size="100">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="dl_tel">고객센터 전화</label></th>
        <td>
            <input type="text" class="frm_input" name="dl_tel" value="<?=stripslashes($dl['dl_tel']) ?>" id="dl_tel">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="dl_order">출력 순서</label></th>
        <td>
            <?=help("셀렉트박스에서 출력할 때 순서를 정합니다.\n\n숫자가 작을수록 상단에 출력합니다.");?>
            <?=order_select("dl_order", $dl['dl_order'])?>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <a href="./deliverycodelist.php">목록</a>
    </div>
</section>

</form>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
