<?
$sub_menu = "400710";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

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
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title)?>

<script src="<?=$g4[cheditor4_path]?>/cheditor.js"></script>
<?=cheditor1('fm_head_html', '100%', '150');?>
<?=cheditor1('fm_tail_html', '100%', '150');?>

<form name=frmfaqmasterform method=post action="./faqmasterformupdate.php" onsubmit="return frmfaqmasterform_check(this);"enctype="MULTIPART/FORM-DATA" style="margin:0px;">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=fm_id value='<? echo $fm_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15% class=tdsl></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=0E87F9></td></tr>
<tr class=ht>
    <td>제목</td>
    <td>
        <input type=text class=ed name=fm_subject size=60 value='<?=get_text($fm[fm_subject]) ?>' required itemname="제목">
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
        <input type=file class=ed name=fm_himg size=40>
        <?
        $himg = "$g4[path]/data/faq/{$fm[fm_id]}_h";
        if (file_exists($himg)) {
            echo "<input type=checkbox name=fm_himg_del value='1'>삭제";
            $himg_str = "<img src='$himg' border=0>";
        }
        ?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=fm_timg size=40>
        <?
        $timg = "$g4[path]/data/faq/{$fm[fm_id]}_t";
        if (file_exists($timg)) {
            echo "<input type=checkbox name=fm_timg_del value='1'>삭제";
            $timg_str = "<img src='$timg' border=0>";
        }
        ?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>

<tr>
    <td>상단 내용</td>
    <td style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('fm_head_html', $fm[fm_head_html]);?></td>
</tr>
<tr>
    <td>하단 내용</td>
    <td style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('fm_tail_html', $fm[fm_tail_html]);?></td>
</tr>
<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./faqmasterlist.php';">
</form>

<script language="javascript">
function frmfaqmasterform_check(f) 
{
    <?=cheditor3('fm_head_html');?>
    <?=cheditor3('fm_tail_html');?>
}

document.frmfaqmasterform.fm_subject.focus();
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
