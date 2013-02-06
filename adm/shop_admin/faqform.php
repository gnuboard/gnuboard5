<?
$sub_menu = "400710";
define('G4_EDITOR', 1);
include_once("./_common.php");

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
include_once(G4_ADMIN_PATH."/admin.head.php");
?>

<?=subtitle($html_title)?><p>

<form id="frmfaqform" name="frmfaqform" method=post action='./faqformupdate.php' onsubmit="return frmfaqform_check(this);">
<input type="hidden" id="w" name="w"     value='<? echo $w ?>'>
<input type="hidden" id="fm_id" name="fm_id" value='<? echo $fm_id ?>'>
<input type="hidden" id="fa_id" name="fa_id" value='<? echo $fa_id ?>'>
<table>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>

<tr>
    <td> 출력 순서</td>
    <td>
        <input type="text" id="fa_order" id="fa_order" name="fa_order" size=10 maxlength=10 value='<?=$fa[fa_order]?>'>
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
        <?=editor_html('fa_subject', $fa[fa_subject]);?>
    </td>
</tr>
<tr>
    <td> 답변</td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=editor_html('fa_content', $fa[fa_content]);?>
    </td>
</tr>
<tr><td colspan=2 height=1 bgcolor=CCCCCC><td></tr>
</table>

<p>
    <input type="submit" accesskey='s' value='  확  인  '>&nbsp;
    <input type="button" accesskey='l' value='  목  록  ' onclick="document.location.href='./faqlist.php?fm_id=<?=$fm_id?>';">
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
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
