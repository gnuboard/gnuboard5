<?php
if (!defined('_GNUBOARD_')) exit;

$begin_time = get_microtime();

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

    $str .= "<ul class=\"gnb_2dul\">";
    for($i=1; $i<count($menu[$key]); $i++)
    {
        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], 'r')))
            continue;

        if ($menu[$key][$i][4] == 1 && $gnb_grp_style == false) $gnb_grp_div = 'gnb_grp_div';
        else if ($menu[$key][$i][4] != 1 && $gnb_grp_style == true) $gnb_grp_div = 'gnb_grp_div';
        else $gnb_grp_div = '';

        if ($menu[$key][$i][4] == 1) $gnb_grp_style = 'gnb_grp_style';
        else $gnb_grp_style = '';

        $str .= '<li class="gnb_2dli"><a href="'.$menu[$key][$i][2].'" class="gnb_2da '.$gnb_grp_style.' '.$gnb_grp_div.'">'.$menu[$key][$i][1].'</a></li>';

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }
    $str .= "</ul>";

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
</script>

<div id="to_content"><a href="#container">본문 바로가기</a></div>

<header id="hd">
    <div id="hd_wrap">
        <h1><?php echo $config['cf_title'] ?></h1>
        <div id="logo"><a href="<?php echo G4_ADMIN_URL ?>"><img src="<?php echo G4_ADMIN_URL ?>/img/logo.jpg" alt="<?php echo $config['cf_title'] ?> 관리자 처음으로"></a></div>
        <div id="mb_nb">
            <ul>
                <li>
                    <a href="<?php echo G4_ADMIN_URL ?>/member_form.php?w=u&amp;mb_id=<?php echo $member['mb_id'] ?>">
                        <img src="<?php echo G4_ADMIN_URL ?>/img/snb_modify.jpg" alt="" width="28" height="28">
                        관리자 정보수정
                    </a>
                </li>
                <li>
                    <a href="<?php echo G4_URL ?>/">
                        <img src="<?php echo G4_ADMIN_URL ?>/img/snb_home.jpg" alt="" width="28" height="28">
                        홈페이지 메인
                    </a>
                </li>
                <?php if(defined('G4_USE_SHOP')) { ?>
                <li>
                    <a href="<?php echo G4_SHOP_URL ?>/">
                        <img src="<?php echo G4_ADMIN_URL ?>/img/snb_home.jpg" alt="" width="28" height="28">
                        쇼핑몰 메인
                    </a>
                </li>
                <?php } ?>
                <li>
                    <a href="<?php echo G4_BBS_URL ?>/logout.php">
                        <img src="<?php echo G4_ADMIN_URL ?>/img/snb_logout.jpg" alt="로그아웃" width="28" height="28">
                        로그아웃
                    </a>
                </li>
            </ul>
        </div>

        <nav id="gnb">
            <h2>관리자 주메뉴</h2>
            <script>$('#gnb').addClass('gnb_js');</script>
            <?php
            $gnb_str = "<ul id=\"gnb_1dul\">";
            foreach($amenu as $key=>$value) {
                $href1 = $href2 = '';
                if ($menu['menu'.$key][0][2]) {
                    $href1 = '<a href="'.$menu['menu'.$key][0][2].'" class="gnb_1da">';
                    $href2 = '</a>';
                } else {
                    continue;
                }
                $current_class = "";
                if (isset($sub_menu) && (substr($sub_menu, 0, 2) == substr($menu['menu'.$key][0][0], 0, 2)))
                    $current_class = " gnb_1dli_air";
                $gnb_str .= '<li class="gnb_1dli'.$current_class.'">'.PHP_EOL;
                $gnb_str .=  $href1 . $menu['menu'.$key][0][1] . $href2;
                $gnb_str .=  print_menu1('menu'.$key, 1);
                $gnb_str .=  "</li>";
                if ($current_class) $current_class = ""; // 클래스 반복부여 방지
            }
            $gnb_str .= "</ul>";
            echo $gnb_str;
            ?>
        </nav>
    </div>

</header>

<div id="wrapper">

    <ul id="qnb">
        <?php if(defined('G4_USE_SHOP')) { ?>
        <li>
            <a href="<?=G4_ADMIN_URL?>/shop_admin/orderlist.php">
                <img src="<?=G4_ADMIN_URL?>/shop_admin/img/qnb_sodr.jpg" alt="" width="40" height="40">
                주문관리
            </a>
        </li>
        <li>
            <a href="<?=G4_ADMIN_URL?>/shop_admin/itemlist.php">
                <img src="<?=G4_ADMIN_URL?>/shop_admin/img/qnb_sit.jpg" alt="" width="40" height="40">
                상품관리
            </a>
        </li>
        <li>
            <a href="<?=G4_ADMIN_URL?>/shop_admin/itemqalist.php">
                <img src="<?=G4_ADMIN_URL?>/shop_admin/img/qnb_sqna.jpg" alt="" width="40" height="40">
                상품문의
            </a>
        </li>
        <li>
            <a href="<?=G4_ADMIN_URL?>/shop_admin/itempslist.php">
                <img src="<?=G4_ADMIN_URL?>/shop_admin/img/qnb_sps.jpg" alt="" width="40" height="40">
                사용후기
            </a>
        </li>
        <?php } ?>
        <li>
            <a href="<?php echo G4_ADMIN_URL ?>/member_list.php">
                <img src="<?php echo G4_ADMIN_URL ?>/img/qnb_mb.jpg" alt="" width="40" height="40">
                회원
            </a>
        </li>
        <li>
            <a href="<?php echo G4_ADMIN_URL ?>/board_list.php">
                <img src="<?php echo G4_ADMIN_URL ?>/img/qnb_board.jpg" alt="" width="40" height="40">
                게시판
            </a>
        </li>
        <li>
            <a href="<?php echo G4_ADMIN_URL ?>/visit_list.php">
                <img src="<?php echo G4_ADMIN_URL ?>/img/qnb_log.jpg" alt="" width="40" height="40">
                접속자
            </a>
        </li>
    </ul>

    <div id="container">
        <div id="text_size">
            <button class="no_text_resize" onclick="font_resize('container', 'decrease');">작게</button>
            <button class="no_text_resize" onclick="font_default('container');">기본</button>
            <button class="no_text_resize" onclick="font_resize('container', 'increase');">크게</button>
        </div>
        <h1><?php echo $g4['title'] ?></h1>
