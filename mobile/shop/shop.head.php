<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G4_PATH.'/head.sub.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once(G4_LIB_PATH.'/connect.lib.php');
include_once(G4_LIB_PATH.'/popular.lib.php');
?>

<header id="header">
    <?php if ((!$bo_table || $w == 's' ) && defined('_INDEX_')) { ?><h1><?php echo $config['cf_title'] ?></h1><?php } ?>

    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <a href="<?php echo G4_SHOP_URL; ?>/"><img src="<?php echo G4_DATA_URL; ?>/common/mobile_logo_img" alt="<?php echo $config['cf_title']; ?> 메인"></a>
    <a href="<?php echo G4_SHOP_URL; ?>/category.php" target="_blank" id="hd_ct">전체분류</a>
    <button type="button" id="hd_sch_open">검색<span class="sound_only"> 열기</span></button>

    <form name="frmsearch1" action="<?php echo G4_SHOP_URL; ?>/search.php">
    <aside id="hd_sch">
        <h2>상품 검색</h2>
        <label for="sch_str" class="sound_only">상품명<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="search_str" value="<?php echo stripslashes(get_text($search_str)); ?>" id="sch_str" required class="frm_input">
        <input type="submit" value="검색" class="btn_submit">
        <button type="button" class="pop_close"><span class="sound_only">검색 </span>닫기</button>
    </aside>
    <script>
        $(function (){
            var $hd_sch = $("#hd_sch");
            $("#hd_sch_open").click(function(){
                $hd_sch.css("display","block");
            });
            $("#hd_sch .pop_close").click(function(){
                $hd_sch.css("display","none");
            });
        });
    </script>
    </form>

    <ul id="hd_mb">
        <?php if ($is_member) { ?>
        <li><a href="<?php echo G4_BBS_URL; ?>/logout.php?url=shop">로그아웃</a></li>
        <?php } else { ?>
        <li><a href="<?php echo G4_BBS_URL; ?>/login.php?url=<?php echo $urlencode; ?>">로그인</a></li>
        <?php } ?>
        <li><a href="<?php echo G4_SHOP_URL; ?>/mypage.php">마이페이지</a></li>
        <li><a href="<?php echo G4_SHOP_URL; ?>/cart.php">장바구니</a></li>
    </ul>

</header>

<div id="container">
    <?php if ((!$bo_table || $w == 's' ) && !defined('_INDEX_')) { ?><h1 id="container_title"><?php echo $g4['title'] ?></h1><?php } ?>