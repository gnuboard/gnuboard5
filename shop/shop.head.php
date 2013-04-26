<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
define('_SHOP_', true);

include_once(G4_PATH.'/head.sub.php');
include_once(G4_LIB_PATH.'/outlogin.lib.php');
include_once(G4_LIB_PATH.'/poll.lib.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once(G4_LIB_PATH.'/connect.lib.php');
include_once(G4_LIB_PATH.'/popular.lib.php');
?>

<header id="hd">
    <h1><?php echo $config['cf_title'] ?></h1>

    <div id="to_content"><a href="#s_container">내용 바로가기</a></div>

    <aside id="hd_nb">
        <ul>
            <li><a href=""><img src="<?php echo G4_URL; ?>/img/shop/hd_nb_help.gif" alt="고객센터"></a></li>
            <li><a href=""><img src="<?php echo G4_URL; ?>/img/shop/hd_nb_cart.gif" alt="장바구니"></a></li>
            <li><a href=""><img src="<?php echo G4_URL; ?>/img/shop/hd_nb_wish.gif" alt="위시리스트"></a></li>
            <li id="hd_nb_last"><a href=""><img src="<?php echo G4_URL; ?>/img/shop/hd_nb_deli.gif" alt="주문/배송조회"></a></li>
        </ul>
    </aside>

    <div id="logo"><a href="<?php echo G4_SHOP_URL; ?>/"><img src="<?php echo G4_DATA_URL; ?>/common/logo_img" alt="쇼핑몰 처음으로"></a></div>

    <aside id="hd_aside">

        <div>

            <section id="sch_all">
                <h2>쇼핑몰 검색</h2>
                <form name="frmsearch1" onsubmit="return search_submit(this);">
                <input type="hidden" name="sfl" value="wr_subject||wr_content">
                <input type="hidden" name="sop" value="and">
                <input type="hidden" name="stx" value="">

                <span>
                    <label for="sch_all_flag" class="sound_only">검색대상</label>
                    <select name="search_flag" id="sch_all_flag">
                        <option value="상품" <?php echo get_selected($search_flag, '상품'); ?>>상품</option>
                        <option value="게시판" <?php echo get_selected($search_flag, '게시판'); ?>>게시판</option>
                    </select>
                </span>

                <label for="sch_all_str" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="search_str" value="<?php echo stripslashes(get_text($search_str)); ?>" id="sch_all_str">
                <input type="submit" value="검색" id="sch_all_submit">

                </form>
                <script>
                function search_submit(f) {
                    if (f.search_flag.value == '상품') {
                        f.action = '<?=G4_SHOP_URL?>/search.php';
                    } else {
                        f.stx.value = f.search_str.value;
                        f.action = '<?=G4_BBS_URL?>/search.php';
                    }
                }
                </script>
            </section>

            <ul>
                <?php if ($is_member) { ?>
                <li><a href="<?php echo G4_BBS_URL; ?>/logout.php">로그아웃</a></li>
                <li><a href="<?php echo G4_BBS_URL; ?>/member_confirm.php?url=register_form.php">정보수정</a></li>
                <?php } else { ?>
                <li><a href="<?php echo G4_BBS_URL; ?>/login.php?url=<?php echo $urlencode; ?>">로그인</a></li>
                <li><a href="<?php echo G4_BBS_URL; ?>/register.php">회원가입</a></li>
                <?php } ?>
                <li><a href="<?php echo G4_SHOP_URL; ?>/mypage.php">마이페이지</a></li>
                <li><a href="<?php echo G4_SHOP_URL; ?>/faq.php">FAQ</a></li>
                <li><a href="<?php echo G4_SHOP_URL; ?>/itemuselist.php">사용후기</a></li>
            </ul>

        </div>

    </aside>

</header>

<div id="wrapper">

        <!-- 새창 -->
        <?php if(defined('_INDEX_')) { // index에서만 실행 ?>
        <div style="position:relative;z-index:100"><?php include G4_SHOP_PATH.'/newwin.inc.php'; // 새창띄우기 ?></div>
        <?php } ?>

        <!-- 오늘본 상품 {-->
        <div>
            <?php include(G4_SHOP_PATH.'/boxtodayview.inc.php'); ?>
        </div>
        <!-- 오늘본 상품 }-->


        <?php echo outlogin("shop_outlogin"); // 외부 로그인 ?>
        <br>

        <!-- 상품분류 -->
        <?php include_once(G4_SHOP_PATH.'/boxcategory.inc.php'); ?>


        <!-- 이벤트 -->
        <?php include_once(G4_SHOP_PATH.'/boxevent.inc.php'); ?>

        <!-- 커뮤니티 -->
        <?php include_once(G4_SHOP_PATH.'/boxcommunity.inc.php'); ?>

        <!-- 장바구니 -->
        <?php // include_once(G4_SHOP_PATH.'/boxcart.inc.php'); ?>

        <!-- 보관함 -->
        <?php // include_once(G4_SHOP_PATH.'/boxwish.inc.php'); ?>

        <!-- 왼쪽 배너 -->
        <?php echo display_banner('왼쪽'); ?><br>
