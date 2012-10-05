<?
if (!defined("_GNUBOARD_")) exit;

$begin_time = get_microtime();

include_once("$g4[path]/head.sub.php");

function print_menu1($key, $no)
{
    global $menu;

    $str = "<table width=130 cellpadding=1 cellspacing=0 id='menu_{$key}' style='position:absolute; display:none; z-index:1;' onpropertychange=\"selectBoxHidden('menu_{$key}')\"><colgroup><colgroup><colgroup width=10><tr><td rowspan=2 colspan=2 bgcolor=#EFCA95><table width=127 cellpadding=0 cellspacing=0 bgcolor=#FEF8F0><colgroup style='padding-left:10px'>";
    $str .= print_menu2($key, $no);
    $str .= "</table></td><td></td></tr><tr><td bgcolor=#DDDAD5 height=40></td></tr><tr><td width=4></td><td height=3 width=127 bgcolor=#DDDAD5></td><td bgcolor=#DDDAD5></td></tr></table>\n";

    return $str;
}


function print_menu2($key, $no)
{
    global $menu, $auth_menu, $is_admin, $auth, $g4;

    $str = "";
    for($i=1; $i<count($menu[$key]); $i++)
    {
        if ($is_admin != "super" && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], "r")))
            continue;

        if ($menu[$key][$i][0] == "-")
            $str .= "<tr><td class=bg_line{$no}></td></tr>";
        else
        {
            $span1 = $span2 = "";
            if (isset($menu[$key][$i][3]))
            {
                $span1 = "<span style='{$menu[$key][$i][3]}'>";
                $span2 = "</span>";
            }
            $str .= "<tr><td class=bg_menu{$no}>";
            if ($no == 2)
                $str .= "&nbsp;&nbsp;<img src='{$g4[admin_path]}/img/icon.gif' align=absmiddle> ";
            $str .= "<a href='{$menu[$key][$i][2]}' style='color:#555500;'>{$span1}{$menu[$key][$i][1]}{$span2}</a></td></tr>";

            $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
        }
    }

    return $str;
}
?>

<script type="text/javascript">
if (!g4_is_ie) document.captureEvents(Event.MOUSEMOVE)
document.onmousemove = getMouseXY;
var tempX = 0;
var tempY = 0;
var prevdiv = null;
var timerID = null;

function getMouseXY(e) 
{
    if (g4_is_ie) { // grab the x-y pos.s if browser is IE
        tempX = event.clientX + document.body.scrollLeft;
        tempY = event.clientY + document.body.scrollTop;
    } else {  // grab the x-y pos.s if browser is NS
        tempX = e.pageX;
        tempY = e.pageY;
    }  

    if (tempX < 0) {tempX = 0;}
    if (tempY < 0) {tempY = 0;}  

    return true;
}

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

<script type="text/javascript" src="<?=$g4['path']?>/js/common.js"></script>
<script type="text/javascript" src="<?=$g4['path']?>/js/sideview.js"></script>
<script type="text/javascript">
var save_layer = null;
function layer_view(link_id, menu_id, opt, x, y)
{
    var link = document.getElementById(link_id);
    var menu = document.getElementById(menu_id);

    //for (i in link) { document.write(i + '<br/>'); } return;

    if (save_layer != null)
    {
        save_layer.style.display = "none";
        selectBoxVisible();
    }

    if (link_id == '')
        return;

    if (opt == 'hide')
    {
        menu.style.display = 'none';
        selectBoxVisible();
    }
    else
    {
        x = parseInt(x);
        y = parseInt(y);
        menu.style.left = get_left_pos(link) + x;
        menu.style.top  = get_top_pos(link) + link.offsetHeight + y;
        menu.style.display = 'block';
    }

    save_layer = menu;
}
</script>

<link rel="stylesheet" href="<?=$g4['admin_path']?>/admin.style.css" type="text/css">
<style>
.bg_menu1 { height:22px; 
            padding-left:15px; 
            padding-right:15px; } 
.bg_line1 { height:1px; background-color:#EFCA95; } 

.bg_menu2 { height:22px; 
            padding-left:25px; } 
.bg_line2 { background-image:url('<?=$g4['admin_path']?>/img/dot.gif'); height:3px; } 
.dot {color:#D6D0C8;border-style:dotted;}

#csshelp1 { border:0px; background:#FFFFFF; padding:6px; }
#csshelp2 { border:2px solid #BDBEC6; padding:0px; }
#csshelp3 { background:#F9F9F9; padding:6px; width:200px; color:#222222; line-height:120%; text-align:left; }
</style>

<body leftmargin=0 topmargin=0>
<a name='gnuboard4_admin_head'></a>
<table width=1004 cellpadding=0 cellspacing=0 border=0>
<colgroup width=180>
<colgroup>
<tr bgcolor=#E3DCD2 height=70>
    <td colspan=2 onmouseover="layer_view('','','','','')"><a href='<?=$g4['admin_path']?>/'><img src='<?=$g4['admin_path']?>/img/logo.gif' border=0></a></td>
    <td>
        <?
        foreach($amenu as $key=>$value)
        {
            $href1 = $href2 = "";
            if ($menu["menu{$key}"][0][2])
            {
                $href1 = "<a href='".$menu["menu{$key}"][0][2]."'>";
                $href2 = "</a>";
            }
            echo "{$href1}<img src='$g4[admin_path]/img/menu{$key}.gif' border=0 id='id_menu{$key}' onmouseover=\"layer_view('id_menu{$key}', 'menu_menu{$key}', 'view', -2, 5);\">{$href2}&nbsp; ";
            echo print_menu1("menu{$key}", 1);
        }
        ?>
    </td>
</tr>
<tr><td colspan=3 bgcolor=#C3BBB1 height=1></td></tr>
<tr><td colspan=3 bgcolor=#E5E5E5 height=2></td></tr>
<tr onmouseover="layer_view('','','','','')">
    <td><a href='<?=$g4['path']?>/'><img src='<?=$g4['admin_path']?>/img/home.gif' border=0></a><a href='<?=$g4['bbs_path']?>/logout.php'><img src='<?=$g4['admin_path']?>/img/logout.gif' border=0></a></td>
    <td rowspan=2 width=1 bgcolor=#DBDBDB></td>
    <td bgcolor=#F8F8F8 align=right>
        <img src='<?=$g4['admin_path']?>/img/navi_icon.gif' align=absmiddle> 
        &nbsp;<a href='<?=$g4['admin_path']?>/'>Admin</a> > 
        <? 
        $tmp_menu = "";
        if (isset($sub_menu))
            $tmp_menu = substr($sub_menu, 0, 3);
        if (isset($menu["menu{$tmp_menu}"][0][1]))
        {
            if ($menu["menu{$tmp_menu}"][0][2])
            {
                echo "<a href='".$menu["menu{$tmp_menu}"][0][2]."'>";
                echo $menu["menu{$tmp_menu}"][0][1];
                echo "</a> > ";
            }
            else
                echo $menu["menu{$tmp_menu}"][0][1]." > ";
        }
        ?>
        <?=$g4['title']?> <span class=small>: <?=$member['mb_id']?>님</span>&nbsp;&nbsp;</td>
</tr>
<tr onmouseover="layer_view('','','','','')">
    <td valign=top>
        <table width=180 cellpadding=0 cellspacing=0>
        <?
        echo "<tr><td><img src='$g4[admin_path]/img/title_menu{$tmp_menu}.gif'></td></tr>";
        echo print_menu2("menu{$tmp_menu}", 2);
        ?>
        </table><br>
    </td>
    <td valign=top style='padding:10px;'>
