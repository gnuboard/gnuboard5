<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if(defined('G5_THEME_PATH')) {
    require_once(G5_THEME_SHOP_PATH.'/shop.head.php');
    return;
}

include_once(G5_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
?>


<!-- 상단 시작 { -->
<div id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <?php if(defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
     } ?>
    <div id="tnb">
        <h3>회원메뉴</h3>
        <ul>
            <?php if(G5_COMMUNITY_USE) { ?>
            <li class="tnb_left tnb_shop"><a href="<?php echo G5_SHOP_URL; ?>/"><i class="fa fa-shopping-bag" aria-hidden="true"></i> 쇼핑몰</a></li>
            <li class="tnb_left tnb_community"><a href="<?php echo G5_URL; ?>/"><i class="fa fa-home" aria-hidden="true"></i> 커뮤니티</a></li>
            <?php } ?>
            <li class="tnb_cart"><a href="<?php echo G5_SHOP_URL; ?>/cart.php"><i class="fa fa-shopping-cart" aria-hidden="true"></i> 장바구니</a></li>            
            <li><a href="<?php echo G5_SHOP_URL; ?>/mypage.php">마이페이지</a></li>
            <?php if ($is_member) { ?>

            <li><a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL; ?>/logout.php?url=shop">로그아웃</a></li>
            <?php if ($is_admin) {  ?>
            <li class="tnb_admin"><a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/"><b>관리자</b></a></li>
            <?php }  ?>
            <?php } else { ?>
            <li><a href="<?php echo G5_BBS_URL; ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo $urlencode; ?>"><b>로그인</b></a></li>
            <?php } ?>
        </ul>
    </div>
    <div id="hd_wrapper">
        <div id="logo"><a href="<?php echo G5_SHOP_URL; ?>/"><img src="<?php echo G5_DATA_URL; ?>/common/logo_img" alt="<?php echo $config['cf_title']; ?>"></a></div>

        <div id="hd_sch">
            <h3>쇼핑몰 검색</h3>
            <form name="frmsearch1" action="<?php echo G5_SHOP_URL; ?>/search.php" onsubmit="return search_submit(this);">

            <label for="sch_str" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="q" value="<?php echo stripslashes(get_text(get_search_string($q))); ?>" id="sch_str" required>
            <button type="submit" id="sch_submit"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>

            </form>
            <script>
            function search_submit(f) {
                if (f.q.value.length < 2) {
                    alert("검색어는 두글자 이상 입력하십시오.");
                    f.q.select();
                    f.q.focus();
                    return false;
                }
                return true;
            }
            </script>
        </div>

        <!-- 쇼핑몰 배너 시작 { -->
        <?php echo display_banner('왼쪽'); ?>
        <!-- } 쇼핑몰 배너 끝 -->
    </div>
    <div id="hd_menu">
        <ul>
            <li><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=1">히트상품</a></li>
            <li><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=2">추천상품</a></li>
            <li><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=3">최신상품</a></li>
            <li><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=4">인기상품</a></li>
            <li><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=5">할인상품</a></li>
            <li class="hd_menu_right"><a href="<?php echo G5_BBS_URL; ?>/faq.php">FAQ</a></li>
            <li class="hd_menu_right"><a href="<?php echo G5_BBS_URL; ?>/qalist.php">1:1문의</a></li>
            <li class="hd_menu_right"><a href="<?php echo G5_SHOP_URL; ?>/personalpay.php">개인결제</a></li>
            <li class="hd_menu_right"><a href="<?php echo G5_SHOP_URL; ?>/itemuselist.php">사용후기</a></li>
            <li class="hd_menu_right"><a href="<?php echo G5_SHOP_URL; ?>/couponzone.php">쿠폰존</a></li>

        </ul>
    </div>
</div>

<div id="side_menu">
    <button type="button" id="btn_sidemenu" class="btn_sidemenu_cl"><i class="fa fa-outdent" aria-hidden="true"></i><span class="sound_only">사이드메뉴버튼</span></button>
    <div class="side_menu_wr">
        <?php echo outlogin('shop_basic'); // 아웃로그인 ?>
        <div class="side_menu_shop">
            <button type="button" class="btn_side_shop">오늘본상품<span class="count"><?php echo get_view_today_items_count(); ?></span></button>
            <?php include(G5_SHOP_SKIN_PATH.'/boxtodayview.skin.php'); // 오늘 본 상품 ?>
            <button type="button" class="btn_side_shop">장바구니<span class="count"><?php echo get_boxcart_datas_count(); ?></span></button>
            <?php include_once(G5_SHOP_SKIN_PATH.'/boxcart.skin.php'); // 장바구니 ?>
            <button type="button" class="btn_side_shop">위시리스트<span class="count"><?php echo get_wishlist_datas_count(); ?></span></button>
            <?php include_once(G5_SHOP_SKIN_PATH.'/boxwish.skin.php'); // 위시리스트 ?>
        </div>
        <?php include_once(G5_SHOP_SKIN_PATH.'/boxcommunity.skin.php'); // 커뮤니티 ?>

    </div>
</div>


<script>
$(function (){

    $(".btn_sidemenu_cl").on("click", function() {
        $(".side_menu_wr").toggle();
        $(".fa-outdent").toggleClass("fa-indent")
    });

    $(".btn_side_shop").on("click", function() {
        $(this).next(".op_area").slideToggle(300).siblings(".op_area").slideUp();
    });
});
</script>


<div id="wrapper">

    <div id="aside">
        <?php include_once(G5_SHOP_SKIN_PATH.'/boxcategory.skin.php'); // 상품분류 ?>
        <?php include_once(G5_SHOP_PATH.'/category.php'); // 분류 ?>
        <?php if($default['de_type4_list_use']) { ?>
        <!-- 인기상품 시작 { -->
        <section class="sale_prd">
            <h2><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=4">인기상품</a></h2>
            <?php
            $list = new item_list();
            $list->set_type(4);
            $list->set_view('it_id', false);
            $list->set_view('it_name', true);
            $list->set_view('it_basic', false);
            $list->set_view('it_cust_price', false);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', false);
            $list->set_view('sns', false);
            echo $list->run();
            ?>
        </section>
        <!-- } 인기상품 끝 -->
        <?php } ?>

        <!-- 커뮤니티 최신글 시작 { -->
        <section id="sidx_lat">
            <h2>커뮤니티 최신글</h2>
            <?php echo latest('shop_basic', 'notice', 5, 30); ?>
        </section>
        <!-- } 커뮤니티 최신글 끝 -->

        <?php echo poll('shop_basic'); // 설문조사 ?>

        <?php echo visit('shop_basic'); // 접속자 ?>
    </div>
<!-- } 상단 끝 -->

    <!-- 콘텐츠 시작 { -->
    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined('_INDEX_')) { ?><div id="wrapper_title"><?php echo $g5['title'] ?></div><?php } ?>
        <!-- 글자크기 조정 display:none 되어 있음 시작 { -->
        <div id="text_size">
            <button class="no_text_resize" onclick="font_resize('container', 'decrease');">작게</button>
            <button class="no_text_resize" onclick="font_default('container');">기본</button>
            <button class="no_text_resize" onclick="font_resize('container', 'increase');">크게</button>
        </div>
        <!-- } 글자크기 조정 display:none 되어 있음 끝 -->