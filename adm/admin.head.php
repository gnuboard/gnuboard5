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

    $str = '<ul>';
    for($i=1; $i<count($menu[$key]); $i++)
    {
        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], 'r')))
            continue;

        // if ($no == 2) $str .= "&nbsp;&nbsp;<img src='{$g4['admin_path']}/img/icon.gif' align=absmiddle> ";
        $str .= '<li class="gnb_2depth"><a href="'.$menu[$key][$i][2].'">'.$menu[$key][$i][1].'</a></li>';

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
    <div id="logo"><a href=""><img src="" alt="관리자 메인으로"></a></div>
    <ul id="home_link">
        <li><a href="<?=$g4['path']?>/">관리자정보수정</a></li>
        <li><a href="<?=$g4['path']?>/">홈페이지</a></li>
        <li><a href="<?=$g4['bbs_path']?>/logout.php">로그아웃</a></li>
    </ul>

    <div id="current_location">
    <?=$member['mb_id']?>님 현재위치
    </div>

    <nav id="gnb">
        <ul>
        <?
        foreach($amenu as $key=>$value)
        {
            $href1 = $href2 = '';
            if ($menu['menu'.$key][0][2])
            {
                $href1 = '<a href="'.$menu['menu'.$key][0][2].'">';
                $href2 = '</a>';
            }
            echo '<li class="gnb_1depth">';
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
