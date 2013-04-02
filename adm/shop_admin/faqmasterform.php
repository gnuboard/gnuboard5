<?
$sub_menu = '400710';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$html_title = 'FAQ';
if ($w == "u")
{
    $html_title .= ' 수정';
    $readonly = ' readonly';

    $sql = " select * from {$g4['yc4_faq_master_table']} where fm_id = '$fm_id' ";
    $fm = sql_fetch($sql);
    if (!$fm['fm_id']) alert('등록된 자료가 없습니다.');
}
else
{
    $html_title .= ' 입력';
}

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmfaqmasterform" action="./faqmasterformupdate.php" onsubmit="return frmfaqmasterform_check(this);" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="w" value="<? echo $w ?>">
<input type="hidden" name="fm_id" value="<? echo $fm_id ?>">

<section class="cbox">
    <h2>FAQ입력</h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="fm_subject">제목</label></th>
        <td>
            <input type="text" value="<?=get_text($fm['fm_subject']) ?>" name="fm_subject" id="fm_subject" required class="frm_input requried"  size="60">
            <?
            if ($w == 'u')
            {
                echo icon("보기", G4_SHOP_URL."/faq.php?fm_id=$fm_id");
                echo " <a href='./faqlist.php?fm_id=$fm_id'>상세보기</a>";
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">상단이미지</th>
        <td>
            <input type="file" name="fm_himg">
            <?
            $himg = G4_DATA_PATH."/faq/{$fm['fm_id']}_h";
            if (file_exists($himg)) {
                echo "<input type=checkbox name=fm_himg_del value='1'>삭제";
                $himg_str = "<img src='".G4_DATA_URL."/faq/{$fm['fm_id']}_h' border=0>";
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단이미지</th>
        <td>
            <input type="file" name="fm_timg">
            <?
            $timg = G4_DATA_PATH."/faq/{$fm['fm_id']}_t";
            if (file_exists($timg)) {
                echo "<input type=checkbox name=fm_timg_del value='1'>삭제";
                $timg_str = "<img src='".G4_DATA_URL."/faq/{$fm['fm_id']}_t' border=0>";
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">상단 내용</th>
        <td>
            <?=editor_html('fm_head_html', $fm['fm_head_html']);?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단 내용</th>
        <td>
            <?=editor_html('fm_tail_html', $fm['fm_tail_html']);?>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./faqmasterlist.php">목록</a>
</div>
</form>

<script>
function frmfaqmasterform_check(f)
{
    <?=get_editor_js('fm_head_html');?>
    <?=get_editor_js('fm_tail_html');?>
}

// document.frmfaqmasterform.fm_subject.focus(); 김혜련 2013-04-02 포커스해제
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
