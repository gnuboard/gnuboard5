<?
if (!defined("_GNUBOARD_")) exit;

$begin_time = get_microtime();
$administrator = 1;
include_once("$g4[path]/head.sub.php");

function print_menu1($key, $no)
{
    global $menu;

    $str = print_menu2($key, $no);

    return $str;
}


function print_menu2($key, $no)
{
    global $menu, $auth_menu, $is_admin, $auth, $g4;

    $str = "<ul>";
    for($i=1; $i<count($menu[$key]); $i++)
    {
        if ($is_admin != "super" && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], "r")))
            continue;

        // if ($no == 2) $str .= "&nbsp;&nbsp;<img src='{$g4[admin_path]}/img/icon.gif' align=absmiddle> ";
        $str .= "<li><a href='".$menu[$key][$i][2]."'>".$menu[$key][$i][1]."</a></li>";

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }
    $str .= "</ul>";

    return $str;
}
?>

<script>
if (!g4_is_ie) document.captureEvents(Event.MOUSEMOVE)
document.onmousemove = getMouseXY;
var tempX = 0;
var tempY = 0;
var prevdiv = null;
var timerID = null;

function imageview(id, w, h)
{

    menu(id);

    var el_id = document.getElementById(id);

    //submenu = eval(name+".style");
    submenu = el_id.style;
    submenu.left = tempX - ( w + 11 );
    submenu.top  = tempY - ( h / 2 );

    selectBoxVisible();

    if (el_id.style.display != 'none')
        selectBoxHidden(id);
}

function help(id, left, top)
{
    menu(id);

    var el_id = document.getElementById(id);

    //submenu = eval(name+".style");
    submenu = el_id.style;
    submenu.left = tempX - 50 + left;
    submenu.top  = tempY + 15 + top;

    selectBoxVisible();

    if (el_id.style.display != 'none')
        selectBoxHidden(id);
}

// TEXTAREA 사이즈 변경
function textarea_size(fld, size)
{
	var rows = parseInt(fld.rows);

	rows += parseInt(size);
	if (rows > 0) {
		fld.rows = rows;
	}
}
</script>

<script src='<?=$g4['path']?>/js/common.js'></script>
<script src='<?=$g4['path']?>/js/sideview.js'></script>

<body>

<header>
<a href='<?=$g4['admin_path']?>/'>로고제목</a>
<?=$g4['title']?><?=$member['mb_id']?>님</td>
<a href='<?=$g4['path']?>/'>홈으로</a><a href='<?=$g4['bbs_path']?>/logout.php'>로그아웃</a>
<nav>
<ul>
<?
foreach($amenu as $key=>$value)
{
    $href1 = $href2 = "";
    if ($menu["menu{$key}"][0][2])
    {
        $href1 = "<a href='".$menu["menu{$key}"][0][2]."'>";
        $href2 = "</a>";
    }
    echo "<li id='gnb_".$menu["menu{$key}"][0][3]."'>";
    echo $href1 . $menu["menu{$key}"][0][1] . $href2;
    echo "</li>";
    echo print_menu1("menu{$key}", 1);
}
?>
</ul>

<?
/*
$tmp_menu = "";
if (isset($sub_menu))
    $tmp_menu = substr($sub_menu, 0, 3);
if (isset($menu["menu{$tmp_menu}"][0][1]))
{
    if ($menu["menu{$tmp_menu}"][0][2])
    {
        echo "<a href='".$menu["menu{$tmp_menu}"][0][2];
        echo $menu["menu{$tmp_menu}"][0][1];
        echo "</a> > ";
    }
    else
        echo $menu["menu{$tmp_menu}"][0][1];
}
*/
?>
</nav>
</header>
