<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G4_PATH.'/head.sub.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once(G4_LIB_PATH.'/connect.lib.php');
include_once(G4_LIB_PATH.'/popular.lib.php');
?>

<header id="hd">
    <h1><?php echo $config['cf_title'] ?></h1>

    <div id="to_content"><a href="#container">본문 바로가기</a></div>

    <div id="logo"><a href="<?php echo G4_MSHOP_URL; ?>/"><img src="<?php echo G4_DATA_URL; ?>/common/logo_img" alt="쇼핑몰 처음으로"></a></div>

    <aside id="hd_ct">
        <h2>쇼핑몰 카테고리</h2>
        
    </aside>

    <aside id="hd_aside">
        <h2>편의메뉴</h2>
        <div>
            <section id="sch_all">
                <h3>쇼핑몰 검색</h3>
                <form name="frmsearch1" action="<?php echo G4_MSHOP_URL; ?>/search.php">

                <label for="sch_all_str" class="sound_only">상품명<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="search_str" value="<?php echo stripslashes(get_text($search_str)); ?>" id="sch_all_str" required>
                <input type="submit" value="검색" id="sch_all_submit">

                </form>
            </section>

            <section id="hd_aside_mb">
                <h3>회원메뉴</h3>
                <ul>
                    <?php if ($is_member) { ?>
                    <li><a href="<?php echo G4_BBS_URL; ?>/logout.php">로그아웃</a></li>
                    <?php } else { ?>
                    <li><a href="<?php echo G4_BBS_URL; ?>/login.php?url=<?php echo $urlencode; ?>">로그인</a></li>
                    <?php } ?>
                    <li><a href="<?php echo G4_MSHOP_URL; ?>/mypage.php">마이페이지</a></li>
                    <li><a href="<?php echo G4_MSHOP_URL; ?>/cart.php">장바구니</a></li>
                </ul>
            </section>
        </div>
    </aside>

</header>

<div id="wrapper">

    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined('_INDEX_')) { ?><h1 id="wrapper_title"><?php echo $g4['title'] ?></h1><?php } ?>
        <div id="text_size">
            <button class="no_text_resize" onclick="font_resize('container', 'decrease');">작게</button>
            <button class="no_text_resize" onclick="font_default('container');">기본</button>
            <button class="no_text_resize" onclick="font_resize('container', 'increase');">크게</button>
        </div>