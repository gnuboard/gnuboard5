<?
$sub_menu = "400630";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

auth_check($auth[$sub_menu], "w");

$html_title = "이벤트 ";

if ($w == "u") 
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_event_table] where ev_id = '$ev_id' ";
    $ev = sql_fetch($sql);
    if (!$ev[ev_id])
        alert("등록된 자료가 없습니다.");
} 
else 
{
    $html_title .= " 입력";
    $ev[ev_skin] = 0;
    $ev[ev_use] = 1;

    // 1.03.00
    // 입력일 경우 기본값으로 대체
    $ev[ev_img_width]  = $default[de_simg_width];
    $ev[ev_img_height] = $default[de_simg_height];
    $ev[ev_list_mod] = 4;
    $ev[ev_list_row] = 5;
}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title);?><p>

<script src="<?=$g4[cheditor4_path]?>/cheditor.js"></script>
<?=cheditor1('ev_head_html', '100%', '150');?>
<?=cheditor1('ev_tail_html', '100%', '150');?>

<form name=feventform method=post action="./itemeventformupdate.php" enctype="MULTIPART/FORM-DATA" style="margin:0px;" onsubmit="return feventform_check(this);">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=ev_id value='<? echo $ev_id ?>'>
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<? if ($w == "u") { ?>
<tr class=ht>
    <td>이벤트번호</td>
    <td>
        <? 
        echo $ev_id;
        echo "&nbsp;&nbsp;&nbsp;";
        echo icon("보기", "$g4[shop_path]/event.php?ev_id=$ev[ev_id]"); 
        ?>
</tr>
<? } ?>
</tr>
<tr class=ht>
    <td>출력스킨</td>
    <td>
        <select name=ev_skin>
        <?  echo get_list_skin_options("^list\.skin\.(.*)\.php", $g4[shop_path]); ?>
        </select>

        <? if ($w == 'u') { ?>
        <script>document.all.ev_skin.value='<?=$ev[ev_skin]?>';</script>
        <? } ?>
        <?=help("기본으로 제공하는 스킨은 $cart_dir/list.skin.*.php 입니다.\n\n$cart_dir/list.php&skin=userskin.php 처럼 직접 만든 스킨을 사용할 수도 있습니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>출력이미지 폭</td>
    <td>
        <input type=text name=ev_img_width size=5 value='<? echo $ev[ev_img_width] ?>' class=ed> 픽셀
        <?=help("환경설정 > 이미지(소) 폭, 높이가 기본값으로 설정됩니다.\n\n$cart_dir/event.php에서 출력되는 이미지의 폭과 높이입니다.", 50);?>
    </td>
    <td>출력이미지 높이</td>
    <td><input type=text name=ev_img_height size=5 value='<? echo $ev[ev_img_height] ?>' class=ed> 픽셀</td>
</tr>
<tr class=ht>
    <td>1라인 이미지수</td>
    <td>
        <input type=text name=ev_list_mod size=3 value='<? echo $ev[ev_list_mod] ?>' class=ed> 개
        <?=help("1라인에 설정한 값만큼의 상품을 출력하지만 스킨에 따라 1라인에 하나의 상품만 출력할 수도 있습니다.", 50);?>
    </td>
    <td>총라인수</td>
    <td>
        <input type=text name=ev_list_row size=3 value='<? echo $ev[ev_list_row] ?>' class=ed> 라인
        <?=help("한페이지에 몇라인을 출력할것인지를 설정합니다.\n\n한페이지에서 표시하는 상품수는 (1라인 이미지수 x 총라인수) 입니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>사용</td>
    <td>
        <select name=ev_use>
        <option value='1'>예        
        <option value='0'>아니오
        </select>
        <script>document.all.ev_use.value='<?=$ev[ev_use]?>';</script>
        <?=help("사용하지 않으면 왼쪽의 이벤트 메뉴와 이벤트리스트 페이지에 접근할 수 없습니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>이벤트제목</td>
    <td colspan=3><input type=text class=ed name=ev_subject size=60 value='<? echo htmlspecialchars2($ev[ev_subject]) ?>' required itemname='이벤트 제목'></td>
</tr>
<tr class=ht>
    <td>메뉴이미지</td>
    <td colspan=3>
        <input type=file class=ed name=ev_mimg size=40>
        <?
        $mimg_str = "";
        $mimg = "$g4[path]/data/event/{$ev[ev_id]}_m";
        if (file_exists($mimg)) {
            echo "<input type=checkbox name=ev_mimg_del value='1'>삭제";
            $mimg_str = "<img src='$mimg' border=0>";
        }
        ?>
        <?=help("쇼핑몰 왼쪽 메뉴에 텍스트 메뉴 대신 이미지로 넣을 경우 사용합니다.");?>
    </td>
</tr>
<? if ($mimg_str) { echo "<tr><td></td><td colspan=3>$mimg_str</td></tr>"; } ?>

<tr class=ht>
    <td>상단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=ev_himg size=40>
        <?
        $himg_str = "";
        $himg = "$g4[path]/data/event/{$ev[ev_id]}_h";
        if (file_exists($himg)) {
            echo "<input type=checkbox name=ev_himg_del value='1'>삭제";
            $himg_str = "<img src='$himg' border=0>";
        }
        ?>
        <?=help("이벤트 페이지 상단에 업로드 한 이미지를 출력합니다.");?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=ev_timg size=40>
        <?
        $timg_str = "";
        $timg = "$g4[path]/data/event/{$ev[ev_id]}_t";
        if (file_exists($timg)) {
            echo "<input type=checkbox name=ev_timg_del value='1'>삭제";
            $timg_str = "<img src='$timg' border=0>";
        }
        ?>
        <?=help("이벤트 페이지 하단에 업로드 한 이미지를 출력합니다.");?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>

<tr>
    <td>상단 내용</td>
    <td colspan=3 align=right style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('ev_head_html', $ev[ev_head_html]);?></td>
</tr>
<tr>
    <td>하단 내용</td>
    <td colspan=3 align=right style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('ev_tail_html', $ev[ev_tail_html]);?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./itemevent.php';">
</form>


<script language="javascript">
function feventform_check(f) 
{
    <?=cheditor3('ev_head_html');?>
    <?=cheditor3('ev_tail_html');?>

    return true;
}

document.feventform.ev_subject.focus();
</script>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
