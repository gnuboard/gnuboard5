<?
$sub_menu = '400710';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$html_title = 'FAQ 상세';

$sql = " select * from {$g4['yc4_faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g4['yc4_faq_table']} where fa_id = '$fa_id' ";
    $fa = sql_fetch($sql);
    if (!$fa['fa_id']) alert("등록된 자료가 없습니다.");

    $fa['fa_subject'] = htmlspecialchars2($fa['fa_subject']);
    $fa['fa_content'] = htmlspecialchars2($fa['fa_content']);
}
else
    $html_title .= ' 입력';

$html_title .= ' : '.$fm['fm_subject'];

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmfaqform" action="./faqformupdate.php" onsubmit="return frmfaqform_check(this);" method="post">
<input type="hidden" name="w" value="<? echo $w ?>">
<input type="hidden" name="fm_id" value="<? echo $fm_id ?>">
<input type="hidden" name="fa_id" value="<? echo $fa_id ?>">

<section class="cbox">
    <h2>FAQ상세입력 수정</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_13">
    </colgroup>
    <tbody>
    <tr>
        <th scope="col"><label for="fa_order">출력순서</label></th>
        <td >
            <input type="text" name="fa_order" value="<?=$fa['fa_order']?>" id="fa_order" class="frm_input" maxlength="10" size="10">
            <?=help('숫자가 작을수록 FAQ 페이지의 상단에 출력합니다.', 60, -50)?>
        </td>
    </tr>
        <tr>
        <th scope="col">질문
            <? if ($w == 'u') {
                echo icon("보기", G4_SHOP_URL."/faq.php?fm_id=$fm_id");
                }
            ?>
        </th>
        <td >
            <?=editor_html('fa_subject', $fa['fa_subject']);?>
        </td>
    </tr>
        <tr>
        <th scope="col">답변</th>
        <td ><?=editor_html('fa_content', $fa['fa_content']);?></td>
    </tr>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./faqlist.php?fm_id=<?=$fm_id?>">목록</a>
</div>
</form>

<script>
function frmfaqform_check(f)
{
    errmsg = "";
    errfld = "";

    //check_field(f.fa_subject, "제목을 입력하세요.");
    //check_field(f.fa_content, "내용을 입력하세요.");

    if (errmsg != "")
    {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    <?=get_editor_js('fa_subject');?>
    <?=get_editor_js('fa_content');?>

    return true;
}

document.getElementById('fa_order').focus();
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
