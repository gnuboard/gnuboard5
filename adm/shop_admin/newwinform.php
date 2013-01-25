<?
$sub_menu = "400720";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

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
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title)?>

<script src="<?=$g4[cheditor4_path]?>/cheditor.js"></script>
<?=cheditor1('nw_content', '100%', '350');?>

<form name=frmnewwin method=post action="./newwinformupdate.php" onsubmit="return frmnewwin_check(this);" style="margin:0px;">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=nw_id value='<? echo $nw_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%>
<colgroup width=35% bgcolor=#ffffff>
<colgroup width=15%>
<colgroup width=35% bgcolor=#ffffff>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td>시간</td>
    <td colspan=3><input type=text class=ed name=nw_disable_hours size=5 value='<? echo $nw[nw_disable_hours] ?>' required itemname="시간"> 시간 동안 다시 띄우지 않음</td>
</tr>
<tr class=ht>
    <td>시작일시</td>
    <td>
        <input type=text class=ed name=nw_begin_time size=21 maxlength=19 value='<? echo $nw[nw_begin_time] ?>' required itemname="시작일시">
        <input type=checkbox name=nw_begin_chk value="<? echo date("Y-m-d 00:00:00", $g4[server_time]); ?>" onclick="if (this.checked == true) this.form.nw_begin_time.value=this.form.nw_begin_chk.value; else this.form.nw_begin_time.value = this.form.nw_begin_time.defaultValue;">오늘
    <td>종료일시</td>
    <td>
        <input type=text class=ed name=nw_end_time size=21 maxlength=19 value='<? echo $nw[nw_end_time] ?>' required itemname="종료일시">
        <input type=checkbox name=nw_end_chk value="<? echo date("Y-m-d 23:59:59", $g4[server_time]+(60*60*24*7)); ?>" onclick="if (this.checked == true) this.form.nw_end_time.value=this.form.nw_end_chk.value; else this.form.nw_end_time.value = this.form.nw_end_time.defaultValue;">오늘+7일
</tr>
<tr class=ht>
    <td>창위치 왼쪽</td>
    <td><input type=text class=ed name=nw_left size=5 value='<? echo $nw[nw_left] ?>' required itemname="창위치 왼쪽"></td>
    <td>창위치 위</td>
    <td><input type=text class=ed name=nw_top  size=5 value='<? echo $nw[nw_top] ?>' required itemname="창위치 위"></td>
</tr>
<tr class=ht>
    <td>창크기 폭</td>
    <td><input type=text class=ed name=nw_width  size=5 value='<? echo $nw[nw_width] ?>' required itemname="창크기폭"></td>
    <td>창크기 높이</td>
    <td><input type=text class=ed name=nw_height size=5 value='<? echo $nw[nw_height] ?>' required itemname="창크기높이"></td>
</tr>
<tr class=ht>
    <td>창제목</td>
    <td colspan=3><input type=text class=ed name=nw_subject size=80 value='<? echo stripslashes($nw[nw_subject]) ?>' required itemname="제목"></td>
</tr>
<input type=hidden name=nw_content_html value=1>
<tr>
    <td>내용</td>
    <td colspan=3 style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('nw_content', $nw[nw_content]);?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./newwinlist.php';">
</form>

<script language="javascript">
function frmnewwin_check(f) 
{
    errmsg = "";
    errfld = "";
    
    <?=cheditor3('nw_content');?>

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
include_once ("$g4[admin_path]/admin.tail.php");
?>
