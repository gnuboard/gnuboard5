<?
if (!defined('_GNUBOARD_')) exit;

$begin_time = get_microtime();
$administrator = 1;
include_once(G4_PATH.'/head.sub.php');

function print_menu1($key, $no)
{
    global $menu;

    $str = print_menu2($key, $no);

    return $str;
}

function print_menu2($key, $no)
{
    global $menu, $auth_menu, $is_admin, $auth, $g4;

    $str = "<noscript>";
    $str .= "<ul>";
    for($i=1; $i<count($menu[$key]); $i++)
    {
        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], 'r')))
            continue;

        // if ($no == 2) $str .= "&nbsp;&nbsp;<img src='{$g4['admin_path']}/img/icon.gif' align=absmiddle> ";
        $str .= '<li class="gnb_2depth"><a href="'.$menu[$key][$i][2].'">'.$menu[$key][$i][1].'</a></li>';

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }
    $str .= "</ul>";
    $str .= "</noscript>";

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

<div id="to_content"><a href="#wrapper">본문 바로가기</a></div>

<header id="hd">
    <div id="hd_wrap">
        <h1><?=$config['cf_title']?></h1>

        <div id="logo"><a href="<?=G4_ADMIN_URL?>"><img src="<?=G4_ADMIN_URL?>/img/logo.jpg" alt="관리자 메인으로"></a></div>

        <div id="home_link">
            <ul>
                <li><a href="<?=G4_ADMIN_URL?>/member_form.php?w=u&amp;mb_id=<?=$member['mb_id']?>">관리자정보수정</a></li>
                <li><a href="<?=G4_URL?>/">홈페이지</a></li>
                <li><a href="<?=G4_BBS_URL?>/logout.php">로그아웃</a></li>
            </ul>
        </div>

        <nav id="gnb">
            <h2>관리자 주메뉴</h2>
            <ul>
            <?
            foreach($amenu as $key=>$value) {
                $href1 = $href2 = '';
                if ($menu['menu'.$key][0][2]) {
                    $href1 = '<a href="'.$menu['menu'.$key][0][2].'">';
                    $href2 = '</a>';
                } else {
                    continue;
                }
                $current_class = "";
                if (isset($sub_menu) && (substr($sub_menu, 0, 2) == substr($menu['menu'.$key][0][0], 0, 2)))
                    $current_class = " gnb_1depth_on";
                echo "<li class=\"gnb_1depth".$current_class."\">";
                echo $href1 . $menu['menu'.$key][0][1] . $href2;
                echo print_menu1('menu'.$key, 1);
                echo "</li>";
                if ($current_class || !$admin_index) $current_class = ""; // 클래스 반복부여 방지
            }

            ?>
            </ul>
        </nav>

        <div id="current_loc">
            <p><?=$member['mb_id']?>님 현재위치</p>
        </div>
    </div>

</header>

<div id="wrapper">

    <ul id="qnb">
        <li><a href="#"></a></li>
        <li><a href="#"></a></li>
        <li><a href="#"></a></li>
    </ul>

    <div id="container">
        <h1><?=$g4['title']?></h1>
