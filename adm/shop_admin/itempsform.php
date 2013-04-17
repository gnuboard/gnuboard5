<?
$sub_menu = '400650';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$sql = " select *
           from {$g4['shop_item_ps_table']} a
           left join {$g4['member_table']} b on (a.mb_id = b.mb_id)
           left join {$g4['shop_item_table']} c on (a.it_id = c.it_id)
          where is_id = '$is_id' ";
$is = sql_fetch($sql);
if (!$is['is_id'])
    alert('등록된 자료가 없습니다.');

$name = get_sideview($is['mb_id'], get_text($is['is_name']), $is['mb_email'], $is['mb_homepage']);

$g4['title'] = '사용후기';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$qstr = 'page='.$page.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2;
?>

<form name="fitemps" method="post" onsubmit="return fitemps_submit(this);">
<input type="hidden" name="w" value="<? echo $w ?>">
<input type="hidden" name="is_id" value="<? echo $is_id ?>">
<input type="hidden" name="page" value="<? echo $page ?>">
<input type="hidden" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" name="sort2" value="<? echo $sort2 ?>">
<input type="hidden" name="is_confirm" value="<? echo $is['is_confirm']?>">

<section class="cbox">
    <h2>사용후기 수정</h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">상품명</th>
        <td><a href="<?=G4_SHOP_URL?>/item.php?it_id=<?=$is['it_id']?>"><?=$is['it_name']?></a></td>
    </tr>
    <tr>
        <th scope="row">이름</th>
        <td><?=$name?></td>
    </tr>
    <tr>
        <th scope="row">점수</th>
        <td><?=stripslashes($is['is_score']) ?> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="is_subject">제목</label></th>
        <td><input type="text" name="is_subject" required class="required frm_input" id="is_subject" size="100"
        value='<?=conv_subject($is['is_subject'], 120)?>'></td>
    </tr>
    <tr>
        <th scope="row">내용</th>
        <td><?=editor_html('is_content', $is['is_content']);?></td>
    </tr>
    <tr>
        <th scope="row">확인</th>
        <td><input type="submit" name="btn_confirm" value="확인하였습니다" class="btn_frmline"></td>
    </tr>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./itempslist.php?<?=$qstr?>">목록</a>
</div>
</form>

<script>
function fitemps_submit(f)
{
    <? echo get_editor_js('is_content'); ?>

    f.action="./itempsformupdate.php";
    return true;
}
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
