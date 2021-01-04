<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$q = isset($_GET['q']) ? clean_xss_tags($_GET['q'], 1, 1) : '';

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
?>

<header id="hd">
    <?php if ((!$bo_table || $w == 's' ) && defined('_INDEX_')) { ?><h1><?php echo $config['cf_title'] ?></h1><?php } ?>

    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <?php if(defined('_INDEX_')) { // index에서만 실행
        include G5_MOBILE_PATH.'/newwin.inc.php'; // 팝업레이어
    } ?>

    <div id="hd_wr">
        <div id="logo"><a href="<?php echo G5_SHOP_URL; ?>/"><img src="<?php echo G5_DATA_URL; ?>/common/mobile_logo_img" alt="<?php echo $config['cf_title']; ?> 메인"></a></div>
        <div id="hd_btn">
            <button type="button" id="btn_hdcate"><i class="fa fa-bars"></i><span class="sound_only">분류</span></button>
            <button type="button" id="btn_hdsch"><i class="fa fa-search"></i><span class="sound_only">검색열기</span></button>
            <a href="<?php echo G5_SHOP_URL; ?>/mypage.php" id="btn_hduser"><i class="fa fa-user"></i><span class="sound_only">마이페이지</span></a>
            <a href="<?php echo G5_SHOP_URL; ?>/cart.php" id="btn_hdcart"><i class="fa fa-shopping-cart"></i><span class="sound_only">장바구니</span><span class="cart-count"><?php echo get_boxcart_datas_count(); ?></span></a>

        </div>
    </div>

    <form name="frmsearch1" action="<?php echo G5_SHOP_URL; ?>/search.php" onsubmit="return search_submit(this);">
    <aside id="hd_sch">
        <div class="sch_inner">
            <h2>상품 검색</h2>
            <label for="sch_str" class="sound_only">상품명<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="q" value="<?php echo stripslashes(get_text(get_search_string($q))); ?>" id="sch_str" required class="frm_input" placeholder="검색어를 입력해주세요">
            <button type="submit" value="검색" class="sch_submit"><i class="fa fa-search" aria-hidden="true"></i></button>
        </div>
        <button type="button" class="btn_close"><i class="fa fa-times"></i><span class="sound_only">닫기</span></button>

    </aside>
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

    <?php include_once(G5_THEME_MSHOP_PATH.'/category.php'); // 분류 ?>


    <script>
    jQuery(function($){
        $( document ).ready( function() {
            
            function catetory_menu_fn( is_open ){
                var $cagegory = $("#category");

                if( is_open ){
                    $cagegory.show();
                    $("body").addClass("is_hidden");
                } else {
                    $cagegory.hide();
                    $("body").removeClass("is_hidden");
                }
            }

            $(document).on("click", "#btn_hdcate", function(e) {
                // 오픈
                catetory_menu_fn(1);
            }).on("click", ".menu_close", function(e) {
                // 숨김
                catetory_menu_fn(0);
            }).on("click", ".cate_bg", function(e) {
                // 숨김
                catetory_menu_fn(0);
            });

            $("#btn_hdsch").on("click", function() {
                $("#hd_sch").show();
            });

            $("#hd_sch .btn_close").on("click", function() {
                $("#hd_sch").hide();
            });
            
            //타이틀 영역고정
            var jbOffset = $( '#container').offset();
            $( window ).scroll( function() {
                if ( $( document ).scrollTop() > jbOffset.top ) {
                    $( '#container').addClass( 'fixed' );
                }
                else {
                    $( '#container').removeClass( 'fixed' );
                }
            });
        });
    });
   </script>
</header>
<?php
    $container_class = array();
    if( defined('G5_IS_COMMUNITY_PAGE') && G5_IS_COMMUNITY_PAGE ){
        $container_class[] = 'is_community';
    }
?>
<div id="container" class="<?php echo implode(' ', $container_class); ?>">
    <?php if ((!$bo_table || $w == 's' ) && !defined('_INDEX_')) { ?><h1 id="container_title"><a href="javascript:history.back()" class="btn_back"><i class="fa fa-chevron-left" aria-hidden="true"></i><span class="sound_only">뒤로</span></a> <?php echo $g5['title'] ?></h1><?php }