<?php
$sub_menu = '300700';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$fm_id = isset($_GET['fm_id']) ? (int) $_GET['fm_id'] : 0;
$fa_id = isset($_GET['fa_id']) ? (int) $_GET['fa_id'] : 0;

$sql = " select * from {$g5['faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

$html_title = 'FAQ '.$fm['fm_subject'];

$fa = array('fa_id'=>0, 'fm_id'=>0, 'fa_subject'=>'', 'fa_content'=>'', 'fa_order'=>0);

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g5['faq_table']} where fa_id = '$fa_id' ";
    $fa = sql_fetch($sql);
    if (!$fa['fa_id']) alert("등록된 자료가 없습니다.");
}
else
    $html_title .= ' 항목 입력';

$g5['title'] = $html_title.' 관리';

include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmfaqform" action="./faqformupdate.php" onsubmit="return frmfaqform_check(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="fm_id" value="<?php echo $fm_id; ?>">
<input type="hidden" name="fa_id" value="<?php echo $fa_id; ?>">
<input type="hidden" name="token" value="">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="fa_order">출력순서</label></th>
        <td>
            <?php echo help('숫자가 작을수록 FAQ 페이지에서 먼저 출력됩니다.'); ?>
            <input type="text" name="fa_order" value="<?php echo $fa['fa_order']; ?>" id="fa_order" class="frm_input" maxlength="10" size="10">
            <?php if ($w == 'u') { ?><a href="<?php echo G5_BBS_URL; ?>/faq.php?fm_id=<?php echo $fm_id; ?>" class="btn_frmline">내용보기</a><?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">질문</th>
        <td><?php echo editor_html('fa_subject', get_text(html_purifier($fa['fa_subject']), 0)); ?></td>
    </tr>
    <tr>
        <th scope="row">답변</th>
        <td><?php echo editor_html('fa_content', get_text(html_purifier($fa['fa_content']), 0)); ?></td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./faqlist.php?fm_id=<?php echo $fm_id; ?>" class="btn btn_02">목록</a>
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

    <?php echo get_editor_js('fa_subject'); ?>
    <?php echo get_editor_js('fa_content'); ?>

    return true;
}

// document.getElementById('fa_order').focus(); 포커스 해제
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');