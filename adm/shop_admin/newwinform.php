<?
$sub_menu = "400720";
define('G4_EDITOR', 1);
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "새창";
if ($w == "u")
{
    $html_title .= " 수정";
    $sql = " select * from $g4[yc4_new_win_table] where nw_id = '$nw_id' ";
    $nw = sql_fetch($sql);
    if (!$nw[nw_id]) alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= " 입력";
    $nw[nw_disable_hours] = 24;
    $nw[nw_left]   = 10;
    $nw[nw_top]    = 10;
    $nw[nw_width]  = 450;
    $nw[nw_height] = 500;
    $nw[nw_content_html] = 2;
}

$g4[title] = $html_title;
include_once(G4_ADMIN_PATH."/admin.head.php");
?>

<?=subtitle($html_title)?>

<form id="frmnewwin" name="frmnewwin" method=post action="./newwinformupdate.php" onsubmit="return frmnewwin_check(this);">
<input type="hidden" id="w" name="w"     value='<? echo $w ?>'>
<input type="hidden" id="nw_id" name="nw_id" value='<? echo $nw_id ?>'>
<table>
<colgroup width=15%>
<colgroup width=35% bgcolor=#ffffff>
<colgroup width=15%>
<colgroup width=35% bgcolor=#ffffff>

<tr>
    <td>시간</td>
    <td colspan=3><input type="text" id="nw_disable_hours" name="nw_disable_hours" size=5 value='<? echo $nw[nw_disable_hours] ?>' required itemid="시간" name="시간"> 시간 동안 다시 띄우지 않음</td>
</tr>
<tr>
    <td>시작일시</td>
    <td>
        <input type="text" id="nw_begin_time" name="nw_begin_time" size=21 maxlength=19 value='<? echo $nw[nw_begin_time] ?>' required itemid="시작일시" name="시작일시">
        <input type="checkbox" id="nw_begin_chk" name="nw_begin_chk" value="<? echo date("Y-m-d 00:00:00", $g4[server_time]); ?>" onclick="if (this.checked == true) this.form.nw_begin_time.value=this.form.nw_begin_chk.value; else this.form.nw_begin_time.value = this.form.nw_begin_time.defaultValue;">오늘
    <td>종료일시</td>
    <td>
        <input type="text" id="nw_end_time" name="nw_end_time" size=21 maxlength=19 value='<? echo $nw[nw_end_time] ?>' required itemid="종료일시" name="종료일시">
        <input type="checkbox" id="nw_end_chk" name="nw_end_chk" value="<? echo date("Y-m-d 23:59:59", $g4[server_time]+(60*60*24*7)); ?>" onclick="if (this.checked == true) this.form.nw_end_time.value=this.form.nw_end_chk.value; else this.form.nw_end_time.value = this.form.nw_end_time.defaultValue;">오늘+7일
</tr>
<tr>
    <td>창위치 왼쪽</td>
    <td><input type="text" id="nw_left" name="nw_left" size=5 value='<? echo $nw[nw_left] ?>' required itemid="창위치 왼쪽" name="창위치 왼쪽"></td>
    <td>창위치 위</td>
    <td><input type="text" id="nw_top" name="nw_top"  size=5 value='<? echo $nw[nw_top] ?>' required itemid="창위치 위" name="창위치 위"></td>
</tr>
<tr>
    <td>창크기 폭</td>
    <td><input type="text" id="nw_width" name="nw_width"  size=5 value='<? echo $nw[nw_width] ?>' required itemid="창크기폭" name="창크기폭"></td>
    <td>창크기 높이</td>
    <td><input type="text" id="nw_height" name="nw_height" size=5 value='<? echo $nw[nw_height] ?>' required itemid="창크기높이" name="창크기높이"></td>
</tr>
<tr>
    <td>창제목</td>
    <td colspan=3><input type="text" id="nw_subject" name="nw_subject" size=80 value='<? echo stripslashes($nw[nw_subject]) ?>' required itemid="제목" name="제목"></td>
</tr>
<input type="hidden" id="nw_content_html" name="nw_content_html" value=1>
<tr>
    <td>내용</td>
    <td colspan=3 style='padding-top:5px; padding-bottom:5px;'><?=editor_html('nw_content', $nw[nw_content]);?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=CCCCCC></td></tr>
</table>

<p>
    <input type="submit" accesskey='s' value='  확  인  '>&nbsp;
    <input type="button" accesskey='l' value='  목  록  ' onclick="document.location.href='./newwinlist.php';">
</form>

<script>
function frmnewwin_check(f)
{
    errmsg = "";
    errfld = "";

    <?=get_editor_js('nw_content');?>

    check_field(f.nw_subject, "제목을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }
    return true;
}

document.frmnewwin.nw_subject.focus();
</script>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
