<?
$tv_idx = get_session("ss_tv_idx");

$tv_div['top'] = 0;
$tv_div['img_width'] = 70;
$tv_div['img_height'] = 70;
$tv_div['img_length'] = 4; // 보여지는 최대 이미지수
?>

<div id='divTodayHidden' style="position:relative; top:<?=$tv_div['top']?>;display:none;"><a href='javascript:todayview_visible();'><img src='<?=G4_SHOP_URL?>/img/todayview.gif' border=0></a></div>
<div id='divToday' style="position:relative; top:0;">
<table cellpadding=0 cellspacing=0 border="0" bgcolor="#FFFFFF">
<tr><td background='<?=G4_SHOP_URL?>/img/todayview01.gif' height=31 align=right><span id='todayviewcount'></span>&nbsp;&nbsp;</td></tr>
<?
// 오늘 본 상품이 있다면
if ($tv_idx)
{
    // 오늘 본 상품갯수가 보여지는 최대 이미지 수 보다 크다면 위로 화살표를 보임
    if ($tv_idx > $tv_div['img_length'])
        echo "<tr><td><img src='".G4_SHOP_URL."/img/todayview02.gif' border='0' onclick='javascript:todayview_up();' style='cursor:pointer;'></td></tr>";

    // 오늘 본 상품 이미지 출력
    echo "<tr><td background='".G4_SHOP_URL."/img/todayview03.gif'><table width=100% cellpadding=2>";
    for ($i=1; $i<=$tv_div['img_length']; $i++)
    {
        echo "<tr><td align=center>";
        echo "<span id='todayview_{$i}'></span>";
        echo "</td></tr>";
    }
    echo "</table></td></tr>";

    // 오늘 본 상품갯수가 보여지는 최대 이미지 수 보다 크다면 아래로 화살표를 보임
    if ($tv_idx > $tv_div['img_length'])
        echo "<tr><td><img src='".G4_SHOP_URL."/img/todayview05.gif' border='0' onclick='javascript:todayview_dn();' style='cursor:pointer;'></td></tr>";
}
else
{
    echo "<tr><td><img src='".G4_SHOP_URL."/img/todayview04.gif'></td></tr>";
}
?>
<tr><td><a href='javascript:todayview_hidden();'><img src='<?=G4_SHOP_URL?>/img/todayview06.gif' border=0></a></td></tr>
</table>
</div>

<!-- 오늘 본 상품 -->
<script language="JavaScript">
var goods_link = new Array();
<?
echo "var goods_max = ".(int)$tv_idx.";\n";
echo "var goods_length = ".(int)$tv_div['img_length'].";\n";
echo "var goods_current = goods_max;\n";
echo "\n";

for ($i=1; $i<=$tv_idx; $i++)
{
    $tv_it_id = get_session("ss_tv[$i]");
    $rowx = sql_fetch(" select it_name from $g4[yc4_item_table] where it_id = '$tv_it_id' ");
    $it_name = get_text(addslashes($rowx['it_name']));
    $img = get_it_image($tv_it_id."_s", $tv_div['img_width'], $tv_div['img_height'], $tv_it_id);
    $img = preg_replace("/\<a /", "<a title='$it_name' ", $img);
    echo "goods_link[$i] = \"{$img}<br/><span class=small>".cut_str($it_name,10,"")."</span>\";\n";
}
?>

var divSave = null;

function todayview_visible()
{
    set_cookie('ck_tvhidden', '', 1);

    document.getElementById('divToday').innerHTML = divSave;
}

function todayview_hidden()
{
    divSave = document.getElementById('divToday').innerHTML;

    set_cookie('ck_tvhidden', '1', 1);

    document.getElementById('divToday').innerHTML = document.getElementById('divTodayHidden').innerHTML;
}

function todayview_move(current)
{
    k = 0;
    for (i=goods_current; i>0 ; i--)
    {
        k++;
        if (k > goods_length)
            break;
        document.getElementById('todayview_'+k).innerHTML = goods_link[i];
    }
}

function todayview_up()
{
    if (goods_current + 1 > goods_max)
        alert("오늘 본 마지막 상품입니다.");
    else
        todayview_move(goods_current++);
}

function todayview_dn()
{
    if (goods_current - goods_length == 0)
        alert("오늘 본 처음 상품입니다.");
    else
        todayview_move(goods_current--);
}

<?
$k=0;
for ($i=$tv_idx; $i>0; $i--)
{
    $k++;
    if ($k > $tv_div['img_length'])
        break;

    $tv_it_id = get_session("ss_tv[$i]");
    echo "document.getElementById('todayview_{$k}').innerHTML = goods_link[$i];\n";
}

if ($tv_idx)
{
    echo "if (document.getElementById('todayviewcount')) document.getElementById('todayviewcount').innerHTML = '$tv_idx';\n";
}
?>
</script>

<script language=javascript>
function CheckUIElements()
{
    var yMenuFrom, yMenuTo, yButtonFrom, yButtonTo, yOffset, timeoutNextCheck;

    yMenuFrom   = parseInt (document.getElementById('divToday').style.top, 10);
    if ( g4_is_gecko )
        yMenuTo = top.pageYOffset + <?=$tv_div[top]?>;
    else if ( g4_is_ie )
        yMenuTo = document.body.scrollTop + parseInt("<?=$tv_div['top']?>");

    timeoutNextCheck = 500;

    if ( Math.abs (yButtonFrom - (yMenuTo + 152)) < 6 && yButtonTo < yButtonFrom )
     {
        setTimeout ("CheckUIElements()", timeoutNextCheck);
        return;
    }

    if ( yMenuFrom != yMenuTo )
    {
        yOffset = Math.ceil( Math.abs( yMenuTo - yMenuFrom ) / 10 );
        if ( yMenuTo < yMenuFrom )
            yOffset = -yOffset;

        document.getElementById('divToday').style.top = parseInt(document.getElementById('divToday').style.top) + yOffset;

        timeoutNextCheck = 10;
    }

    setTimeout ("CheckUIElements()", timeoutNextCheck);
}

function OnLoad() {
    var y;

    if ( top.frames.length )
        document.getElementById('divToday').style.display = '';

    CheckUIElements();
    return true;
}

OnLoad();

<?
if ($_COOKIE['ck_tvhidden'])
    echo "todayview_hidden();";
?>
//-->
</script>
