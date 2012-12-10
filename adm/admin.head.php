<?
if (!defined("_GNUBOARD_")) exit;

$begin_time = get_microtime();
$administrator = 1;
include_once($g4['path'].'/head.sub.php');

function print_menu1($key, $no)
{
    global $menu;

    $str = print_menu2($key, $no);

    return $str;
}

function print_menu2($key, $no)
{
    global $menu, $auth_menu, $is_admin, $auth, $g4;

    $str = '<ul class="gnb_2depth">';
    for($i=1; $i<count($menu[$key]); $i++)
    {
        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], 'r')))
            continue;

        // if ($no == 2) $str .= "&nbsp;&nbsp;<img src='{$g4['admin_path']}/img/icon.gif' align=absmiddle> ";
        $str .= '<li id="gnb_'.$menu[$key][$i][3].'"><a href="'.$menu[$key][$i][2].'">'.$menu[$key][$i][1].'</a></li>';

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }
    $str .= '</ul>';

    return $str;
}
?>

<script>
var tempX = 0;
var tempY = 0;

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

<script src="<?=$g4['path']?>/js/common.js"></script>
<script src="<?=$g4['path']?>/js/sideview.js"></script>

<header>

    <div><a href="#wrapper" id="skip_to_main">본문 바로가기</a></div>
    <p><strong>사이트제목</strong> <?=$member['mb_id']?>님</p>
    <section>
    <ul>
        <li><a href="<?=$g4['admin_path']?>/">관리자메인</a></li>
        <li><a href="<?=$g4['path']?>/">홈페이지</a></li>
        <li><a href="<?=$g4['bbs_path']?>/logout.php">로그아웃</a></li>
    </ul>
    </section>

    <div id="current_location">
    현재위치
    <?
    $tmp_menu = '';
    if (isset($sub_menu))
        $tmp_menu = substr($sub_menu, 0, 3);
    if (isset($menu['menu'.$tmp_menu][0][1]))
    {
        if ($menu['menu'.$tmp_menu][0][2])
        {
            echo '<a href="'.$menu['menu'.$tmp_menu][0][2];
            echo $menu['menu'.$tmp_menu][0][1];
            echo '</a> > ';
        }
        else
            echo $menu['menu'.$tmp_menu][0][1];
    }
    ?>
    </div>

    <nav>
    <ul id="gnb_parents">
    <?
    foreach($amenu as $key=>$value)
    {
        $href1 = $href2 = '';
        if ($menu['menu'.$key][0][2])
        {
            $href1 = '<a href="'.$menu['menu'.$key][0][2].'">';
            $href2 = '</a>';
        }
        echo '<li id="gnb_'.$menu['menu'.$key][0][3].'">';
        echo $href1 . $menu['menu'.$key][0][1] . $href2;
        echo print_menu1('menu'.$key, 1);
        echo '</li>';
    }

    ?>
    </ul>
    </nav>

    <h1><span></span><?=$g4['title']?></h1>

</header>

<div id="wrapper">
