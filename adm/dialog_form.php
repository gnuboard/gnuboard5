<?
$sub_menu = "300300";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

auth_check($auth[$sub_menu], "w");

$html_title = "다이얼로그";
if ($w == "u") 
{
    $html_title .= " 수정";
    $sql = " select * from $g4[dialog_table] where di_id = '$di_id' ";
    $di = sql_fetch($sql);
    if (!$di[di_id]) alert("등록된 자료가 없습니다.");
} 
else 
{
    $html_title .= " 입력";
    $di[di_disable_hours] = 24;
    $di[di_speeds] = 0;
    $di[di_width] = 0;
    $di[di_height] = 0;
    $di[di_draggable] = true;
    $di[di_escape] = true;
}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title)?>

<script src="<?=$g4[cheditor4_path]?>/cheditor.js"></script>
<?=cheditor1('di_content', '100%', '350');?>

<form name=fdialog method=post action="#" onsubmit="return fdialog_check(this);" style="margin:0px;">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=di_id value='<? echo $di_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%>
<colgroup width=35% bgcolor=#ffffff>
<colgroup width=15%>
<colgroup width=35% bgcolor=#ffffff>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td>테마</td>
    <td colspan=3>
        <select name=di_ui_theme> 
        <option value='base'>기본</option>
        <option value='black-tie'>black-tie</option>
        <option value='blitzer'>blitzer</option>
        <option value='cupertino'>cupertino</option>
        <option value='dark-hive'>dark-hive</option>
        <option value='dot-luv'>dot-luv</option>
        <option value='eggplant'>eggplant</option>
        <option value='excite-bike'>excite-bike</option>
        <option value='flick'>flick</option>
        <option value='hot-sneaks'>hot-sneaks</option>
        <option value='humanity'>humanity</option>
        <option value='le-frog'>le-frog</option>
        <option value='mint-choc'>mint-choc</option>
        <option value='overcast'>overcast</option>
        <option value='pepper-grinder'>pepper-grinder</option>
        <option value='redmond'>redmond</option>
        <option value='smoothness'>smoothness</option>
        <option value='south-street'>south-street</option>
        <option value='start'>start</option>
        <option value='sunny'>sunny</option>
        <option value='swanky-purse'>swanky-purse</option>
        <option value='trontastic'>trontastic</option>
        <option value='ui-darkness'>ui-darkness</option>
        <option value='ui-lightness'>ui-lightness</option>
        <option value='vader'>vader</option>
        </select> 
        <script> document.fdialog.di_ui_theme.value = "<?=$di[di_ui_theme]?>"; </script>
        <a href="http://jqueryui.com/themeroller/" target="_blank">http://jqueryui.com/themeroller/</a> 의 Gallery 참고
    </td>
</tr>
<tr class=ht>
    <td>시간</td>
    <td colspan=3>
        <input type=text name=di_disable_hours class=ed value="<?=$di[di_disable_hours]?>" size=5> 동안 창을 다시 띄우지 않음
    </td>
</tr>
<tr class=ht>
    <td>시작일시</td>
    <td>
        <input type=text class=ed name=di_begin_time size=21 maxlength=19 value='<? echo $di[di_begin_time] ?>' required itemname="시작일시">
        <input type=checkbox name=di_begin_chk value="<? echo date("Y-m-d 00:00:00", $g4[server_time]); ?>" onclick="if (this.checked == true) this.form.di_begin_time.value=this.form.di_begin_chk.value; else this.form.di_begin_time.value = this.form.di_begin_time.defaultValue;">오늘
    <td>종료일시</td>
    <td>
        <input type=text class=ed name=di_end_time size=21 maxlength=19 value='<? echo $di[di_end_time] ?>' required itemname="종료일시">
        <input type=checkbox name=di_end_chk value="<? echo date("Y-m-d 23:59:59", $g4[server_time]+(60*60*24*7)); ?>" onclick="if (this.checked == true) this.form.di_end_time.value=this.form.di_end_chk.value; else this.form.di_end_time.value = this.form.di_end_time.defaultValue;">오늘+7일
</tr>
<tr class=ht>
    <td>출력 스피드</td>
    <td colspan=3>
        <input type=text class=ed name=di_speeds  size=5 value='<?=$di[di_speeds]?>'>
        <?=help("0 이 가장 빠르고 숫자가 높으면 창의 출력 및 닫기 속도가 느려짐");?>
        예) 1000
    </td>
</tr>
<tr class=ht>
    <td>창위치</td>
    <td>
        <select name=di_position_sel>
        <option value="">직접입력</option>
        <option value="['top']">top</option>
        <option value="['left']">left</option>
        <option value="['center']">center</option>
        <option value="['right']">right</option>
        <option value="['bottom']">bottom</option>
        </select>
        <input type=text name=di_position class=ed value="<?=$di[di_position]?>">
        <?=help("입력이 없으면 중앙에 출력합니다.<br><br>왼쪽, 상단을 [100,50] 과 같이 입력하거나, ['right','top'] 과 같이 입력할  수 있습니다.");?>
    </td>
    <td>창 드래그</td>
    <td>
        <input type=checkbox name=di_draggable value='1' <?=($di[di_draggable]?"checked":"");?>> 가능 
        <?=help("창을 드래그 할 수 있음");?>
    </td>
</tr>
<tr class=ht>
    <td>창크기 폭</td>
    <td>
        <input type=text class=ed name=di_width  size=5 value='<? echo $di[di_width] ?>' required itemname="창크기폭"> px
        <?=help("0 으로 설정하면 폭을 자동으로 맞춥니다.");?>
    </td>
    <td>창크기 높이</td>
    <td>
        <input type=text class=ed name=di_height size=5 value='<? echo $di[di_height] ?>' required itemname="창크기높이"> px
        <?=help("0 으로 설정하면 높이를 자동으로 맞춥니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>modal</td>
    <td>
        <input type=checkbox name=di_modal value='1' <?=($di[di_modal]?"checked":"");?>> 사용 
        <?=help("창을 닫기전에는 부모창을 선택할 수 없음");?>
    </td>
    <td>사이즈 조절 가능</td>
    <td>
        <input type=checkbox name=di_resizable value='1' <?=($di[di_resizable]?"checked":"");?>> 사용 
        <?=help("창의 리사이즈가 가능하게 할때 사용합니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>show</td>
    <td>
        <!-- http://docs.jquery.com/UI/Effects/ -->
        <select name=di_show>
        <option value=''>기본</option>
        <option value='blind'>blind</option>
        <option value='clip'>clip</option>
        <option value='drop'>drop</option>
        <option value='explode'>explode</option>
        <option value='fade'>fade</option>
        <option value='fold'>fold</option>
        <option value='puff'>puff</option>
        <option value='slide'>slide</option>
        <option value='scale'>scale</option>
        <option value='bounce'>bounce</option>
        <option value='highlight'>highlight</option>
        <option value='pulsate'>pulsate</option>
        <option value='shake'>shake</option>
        <option value='size'>size</option>
        <option value='transfer'>transfer</option>
        </select>
        <script> document.fdialog.di_show.value = "<?=$di[di_show]?>"; </script>
        <?=help("창이 보여지는 효과");?>
    </td>
    <td>hide</td>
    <td>
        <select name=di_hide>
        <option value=''>기본</option>
        <option value='blind'>blind</option>
        <option value='clip'>clip</option>
        <option value='drop'>drop</option>
        <option value='explode'>explode</option>
        <option value='fade'>fade</option>
        <option value='fold'>fold</option>
        <option value='puff'>puff</option>
        <option value='slide'>slide</option>
        <option value='scale'>scale</option>
        <option value='bounce'>bounce</option>
        <option value='highlight'>highlight</option>
        <option value='pulsate'>pulsate</option>
        </select>
        <script> document.fdialog.di_hide.value = "<?=$di[di_hide]?>"; </script>
        <?=help("창이 가려지는 효과");?>
    </td>
</tr>
<tr class=ht>
    <td>ESC</td>
    <td>
        <input type=checkbox name=di_escape value='1' <?=($di[di_escape]?"checked":"");?>> 사용 
        <?=help("ESC 키를 누르면 창이 닫힙니다.");?>
    </td>
    <td>zIndex</td>
    <td>
        <input type=text name=di_zindex class=ed value="<?=$di[di_zindex]?>" size=10> 
        <?=help("창이 출력되는 순서. 숫자가 높을수록 우선 출력됨.");?>
    </td>
</tr>
<tr class=ht>
    <td>창제목</td>
    <td colspan=3><input type=text class=ed name=di_subject style="width:99%;" value='<? echo stripslashes($di[di_subject]) ?>' required itemname="제목"></td>
</tr>
<tr>
    <td>내용</td>
    <td colspan=3 style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('di_content', $di[di_content]);?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./dialog_list.php';">
</form>

<script type="text/javascript">
function fdialog_check(f) 
{
    <?=cheditor3('di_content');?>

    f.action = "./dialog_form_update.php";
    return true;
}

document.fdialog.di_subject.focus();

$(function() {
    $("[name=di_position_sel]").bind("change", function() {
        if ($(this).val()) {
            $("[name='di_position']").val( $(this).val() );
        }
    });
});
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
