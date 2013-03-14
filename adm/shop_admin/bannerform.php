<?
$sub_menu = "400730";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "배너";
if ($w=="u")
{
    $html_title .= " 수정";
    $sql = " select * from $g4[yc4_banner_table] where bn_id = '$bn_id' ";
    $bn = sql_fetch($sql);
}
else
{
    $html_title .= " 입력";
    $bn[bn_url]        = "http://";
    $bn[bn_begin_time] = date("Y-m-d 00:00:00", time());
    $bn[bn_end_time]   = date("Y-m-d 00:00:00", time()+(60*60*24*31));
}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title)?>

<form name=fbanner method=post action='./bannerformupdate.php' enctype='multipart/form-data' style="margin:0px;">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=bn_id value='<? echo $bn_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=0E87F9></td></tr>
<tr class=ht>
    <td>&nbsp;이미지</td>
    <td>
        <input type=file name=bn_bimg size=40 class=ed>
        <?
        $bimg_str = "";
        $bimg = "$g4[path]/data/banner/{$bn[bn_id]}";
        if (file_exists($bimg) && $bn[bn_id]) {
            echo "<input type=checkbox name=bn_bimg_del value='1'>삭제";
            $bimg_str = "<img src='$bimg' border=0>";
            //$size = getimagesize($bimg);
            //echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('bimg', $size[0], $size[1]);\"><input type=checkbox name=bn_bimg_del value='1'>삭제";
            //echo "<div id='bimg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$bimg' border=1></div>";
        }
        ?>
    </td>
</tr>
<? if ($bimg_str) { echo "<tr><td></td><td>$bimg_str</td></tr>"; } ?>

<tr class=ht>
    <td>&nbsp;이미지 설명</td>
    <td>
        <input type=text name=bn_alt size=80 value='<? echo $bn[bn_alt] ?>' class=ed>
        <?=help("img 태그의 alt, title 에 해당되는 내용입니다.\n배너에 마우스를 오버하면 이미지의 설명이 나옵니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;링크</td>
    <td>
        <input type=text name=bn_url size=80 value='<? echo $bn[bn_url] ?>' class=ed>
        <?=help("배너클릭시 이동하는 주소입니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;출력위치</td>
    <td>
        <select name=bn_position>
        <option value="왼쪽">왼쪽
        <option value="메인">메인
        </select>
        <?=help("왼쪽 : 쇼핑몰화면 왼쪽에 출력합니다.\n메인 : 쇼핑몰 메인화면(index.php)에만 출력합니다.", 50);?>
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;테두리</td>
    <td>
        <select name=bn_border>
        <option value="0">아니오
        <option value="1">예
        </select>
        <?=help("배너이미지에 테두리를 넣을지를 설정합니다.", 50);?>
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;새창</td>
    <td>
        <select name=bn_new_win>
        <option value="0">아니오
        <option value="1">예
        </select>
        <?=help("배너클릭시 새창을 띄울지를 설정합니다.", 50);?>
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;시작일시</td>
    <td>
        <input type=text name=bn_begin_time size=21 maxlength=19 value='<? echo $bn[bn_begin_time] ?>' class=ed>
        <input type=checkbox name=bn_begin_chk value="<? echo date("Y-m-d 00:00:00", time()); ?>" onclick="if (this.checked == true) this.form.bn_begin_time.value=this.form.bn_begin_chk.value; else this.form.bn_begin_time.value = this.form.bn_begin_time.defaultValue;">오늘
        <?=help("현재시간이 시작일시와 종료일시 기간안에 있어야 배너가 출력됩니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;종료일시</td>
    <td>
        <input type=text name=bn_end_time size=21 maxlength=19 value='<? echo $bn[bn_end_time] ?>' class=ed>
        <input type=checkbox name=bn_end_chk value="<? echo date("Y-m-d 23:59:59", time()+60*60*24*31); ?>" onclick="if (this.checked == true) this.form.bn_end_time.value=this.form.bn_end_chk.value; else this.form.bn_end_time.value = this.form.bn_end_time.defaultValue;">오늘+31일
    </td>
</tr>
<tr class=ht>
    <td>&nbsp;출력 순서</td>
    <td>
        <?=order_select("bn_order", $bn[bn_order])?>
        <?=help("배너를 출력할 때 순서를 정합니다.\n\n숫자가 작을수록 상단에 출력합니다.");?>
    </td>
</tr>
<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./bannerlist.php';">&nbsp;
</form>



<script language="JavaScript">
if (document.fbanner.w.value == 'u')
{
    document.fbanner.bn_position.value = '<?=$bn[bn_position]?>';
    document.fbanner.bn_border.value   = '<?=$bn[bn_border]?>';
    document.fbanner.bn_new_win.value  = '<?=$bn[bn_new_win]?>';
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
