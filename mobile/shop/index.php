<?php
include_once('./_common.php');

define("_INDEX_", TRUE);

include_once(G5_MSHOP_PATH.'/_head.php');
?>

<script src="<?php echo G5_JS_URL; ?>/swipe.js"></script>
<script src="<?php echo G5_JS_URL; ?>/shop.mobile.main.js"></script>

<?php echo display_banner('메인', 'mainbanner.10.skin.php'); ?>
<?php echo display_banner('왼쪽', 'boxbanner.skin.php'); ?>

    <?php if($default['de_mobile_type1_list_use']) { ?>
    <div class="sct_wrap">
            <h2><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=1">히트상품</a></h2>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(1);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', true);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();
        ?>
    </div>
    <?php } ?>



    <?php if($default['de_mobile_type2_list_use']) { ?>
    <div class="sct_wrap">
        <h2><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=2">추천상품</a></h2>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(2);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', true);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();
        ?>
    </div>
    <?php } ?>


    <?php if($default['de_mobile_type3_list_use']) { ?>
    <div class="sct_wrap">
        <h2><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=3">최신상품</a></h2>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(3);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', true);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();
        ?>
    </div>
    <?php } ?>

    <?php if($default['de_mobile_type4_list_use']) { ?>
    <div class="sct_wrap">
        <h2><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=4">인기상품</a></h2>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(4);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', false);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', false);
        $list->set_view('sns', false);
        echo $list->run();
        ?>
    </div>
    <?php } ?>

    <?php if($default['de_mobile_type5_list_use']) { ?>
    <div class="sct_wrap">
        <h2><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=5">할인상품</a></h2>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(5);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', false);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', false);
        $list->set_view('sns', false);
        echo $list->run();
        ?>
    </div>
    <?php } ?>




    <?php include_once(G5_MSHOP_SKIN_PATH.'/main.event.skin.php'); // 이벤트 ?>

    <!-- 커뮤니티 최신글 시작 { -->
    <section id="sidx_lat">
        <?php echo latest('shop_basic', 'notice', 3, 30); ?>
    </section>

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');