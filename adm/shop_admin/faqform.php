<?
$sub_menu = "400710";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

auth_check($auth[$sub_menu], "w");

$html_title = "FAQ 상세";

$sql = " select * from $g4[yc4_faq_master_table] where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

if ($w == "u") 
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_faq_table] where fa_id = '$fa_id' ";
    $fa = sql_fetch($sql);
    if (!$fa[fa_id]) alert("등록된 자료가 없습니다.");

    $fa[fa_subject] = htmlspecialchars2($fa[fa_subject]);
    $fa[fa_content] = htmlspecialchars2($fa[fa_content]);
} 
else
    $html_title .= " 입력";

$html_title .= " : $fm[fm_subject]";

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title)?><p>

<script src="<?=$g4[cheditor4_path]?>/cheditor.js"></script>
<?=cheditor1('fa_subject', '100%', '150');?>
<?=cheditor1('fa_content', '100%', '300');?>

<form name=frmfaqform method=post action='./faqformupdate.php' onsubmit="return frmfaqform_check(this);" style="margin:0px;">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=fm_id value='<? echo $fm_id ?>'>
<input type=hidden name=fa_id value='<? echo $fa_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td> 출력 순서</td>
    <td>
        <input type=text id=fa_order name=fa_order size=10 maxlength=10 value='<?=$fa[fa_order]?>' class=ed>
        <?=help('숫자가 작을수록 FAQ 페이지의 상단에 출력합니다.', 60, -50)?>
    </td>
</tr>
<tr>
    <td> 질문        
		<? if ($w == 'u') {
            echo icon("보기", "$g4[shop_path]/faq.php?fm_id=$fm_id");
            }
        ?>
    </td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=cheditor2('fa_subject', $fa[fa_subject]);?>
    </td>
</tr>
<tr>
    <td> 답변</td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=cheditor2('fa_content', $fa[fa_content]);?>
    </td>
</tr>
<tr><td colspan=2 height=1 bgcolor=CCCCCC><td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./faqlist.php?fm_id=<?=$fm_id?>';">
</form>

<script language="javascript">
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

    <?=cheditor3('fa_subject');?>
    <?=cheditor3('fa_content');?>

    return true;
}

document.getElementById('fa_order').focus();
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
