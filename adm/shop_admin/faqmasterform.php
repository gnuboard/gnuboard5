<?
$sub_menu = "400710";
define('G4_EDITOR', 1);
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "FAQ";
if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_faq_master_table] where fm_id = '$fm_id' ";
    $fm = sql_fetch($sql);
    if (!$fm[fm_id]) alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= " 입력";
}

$g4[title] = $html_title;
include_once(G4_ADMIN_PATH."/admin.head.php");
?>

<?=subtitle($html_title)?>

<form id="frmfaqmasterform" name="frmfaqmasterform" method=post action="./faqmasterformupdate.php" onsubmit="return frmfaqmasterform_check(this);"enctype="MULTIPART/FORM-DATA" style="margin:0px;">
<input type="hidden" id="w" name="w"     value='<? echo $w ?>'>
<input type="hidden" id="fm_id" name="fm_id" value='<? echo $fm_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15% class=tdsl></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=0E87F9></td></tr>
<tr class=ht>
    <td>제목</td>
    <td>
        <input type="text" class=ed id="fm_subject" name="fm_subject" size=60 value='<?=get_text($fm[fm_subject]) ?>' required itemid="제목" name="제목">
        <?
        if ($w == 'u')
        {
            echo icon("보기", "$g4[shop_path]/faq.php?fm_id=$fm_id");
            echo " <a href='./faqlist.php?fm_id=$fm_id'>상세보기</a>";
        }
        ?>
    </td>
</tr>
<tr class=ht>
    <td>상단이미지</td>
    <td colspan=3>
        <input type="file" class=ed id="fm_himg" name="fm_himg" size=40>
        <?
        $himg = "$g4[path]/data/faq/{$fm[fm_id]}_h";
        if (file_exists($himg)) {
            echo "<input type="checkbox" id="fm_himg_del" name="fm_himg_del" value='1'>삭제";
            $himg_str = "<img src='$himg' border=0>";
        }
        ?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td colspan=3>
        <input type="file" class=ed id="fm_timg" name="fm_timg" size=40>
        <?
        $timg = "$g4[path]/data/faq/{$fm[fm_id]}_t";
        if (file_exists($timg)) {
            echo "<input type="checkbox" id="fm_timg_del" name="fm_timg_del" value='1'>삭제";
            $timg_str = "<img src='$timg' border=0>";
        }
        ?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>

<tr>
    <td>상단 내용</td>
    <td style='padding-top:5px; padding-bottom:5px;'><?=editor_html('fm_head_html', $fm[fm_head_html]);?></td>
</tr>
<tr>
    <td>하단 내용</td>
    <td style='padding-top:5px; padding-bottom:5px;'><?=editor_html('fm_tail_html', $fm[fm_tail_html]);?></td>
</tr>
<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
</table>

<p align=center>
    <input type="submit" class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type="button" class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./faqmasterlist.php';">
</form>

<script language="javascript">
function frmfaqmasterform_check(f)
{
    <?=get_editor_js('fm_head_html');?>
    <?=get_editor_js('fm_tail_html');?>
}

document.frmfaqmasterform.fm_subject.focus();
</script>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
