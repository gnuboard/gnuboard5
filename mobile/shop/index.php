<?php
include_once('./_common.php');

define("_INDEX_", TRUE);

include_once(G5_MSHOP_PATH.'/_head.php');
?>

<script src="<?php echo G5_JS_URL; ?>/swipe.js"></script>
<script src="<?php echo G5_JS_URL; ?>/shop.mobile.main.js"></script>

<div id="sidx" class="swipe">

    <div id="sidx_slide" class="swipe-wrap">
        <?php if($default['de_mobile_type1_list_use']) { ?>
        <div class="sct_wrap">
            <header>
                <h2>히트상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 히트상품 모음</p>
            </header>
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
            <div class="sct_more"><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=1">더 보기</a></div>
        </div>
        <?php } ?>

        <?php if($default['de_mobile_type2_list_use']) { ?>
        <div class="sct_wrap">
            <header>
                <h2>추천상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 추천상품 모음</p>
            </header>
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
            <div class="sct_more"><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=2">더 보기</a></div>
        </div>
        <?php } ?>

        <?php if($default['de_mobile_type3_list_use']) { ?>
        <div class="sct_wrap">
            <header>
                <h2>최신상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 최신상품 모음</p>
            </header>
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
            <div class="sct_more"><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=3">더 보기</a></div>
        </div>
        <?php } ?>

        <?php if($default['de_mobile_type4_list_use']) { ?>
        <div class="sct_wrap">
            <header>
                <h2>인기상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 인기상품 모음</p>
            </header>
            <?php
            $list = new item_list();
            $list->set_mobile(true);
            $list->set_type(4);
            $list->set_view('it_id', false);
            $list->set_view('it_name', true);
            $list->set_view('it_cust_price', true);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            echo $list->run();
            ?>
            <div class="sct_more"><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=4">더 보기</a></div>
        </div>
        <?php } ?>

        <?php if($default['de_mobile_type5_list_use']) { ?>
        <div class="sct_wrap">
            <header>
                <h2>할인상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 할인상품 모음</p>
            </header>
            <?php
            $list = new item_list();
            $list->set_mobile(true);
            $list->set_type(5);
            $list->set_view('it_id', false);
            $list->set_view('it_name', true);
            $list->set_view('it_cust_price', true);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            echo $list->run();
            ?>
            <div class="sct_more"><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=5">더 보기</a></div>
        </div>
        <?php } ?>

        <?php
        $hsql = " select ev_id, ev_subject, ev_subject_strong from {$g5['g5_shop_event_table']} where ev_use = '1' order by ev_id desc ";
        $hresult = sql_query($hsql);

        if(mysql_num_rows($hresult)) {
        ?>
        <div class="sct_wrap">
            <header>
                <h2>이벤트</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 이벤트 모음</p>
            </header>
            <?php include_once(G5_MSHOP_SKIN_PATH.'/main.event.skin.php'); ?>
        </div>
        <?php
        }
        ?>

    </div>

</div>

<script>
$(function() {
    $("#sidx").swipeSlide({
        slides: ".swipe-wrap > div",
        header: "header h2",
        tabWrap: "slide_tab",
        tabActive: "tab_active",
        tabOffset: 10,
        startSlide: 0,
        auto: 0
    });
});
</script>

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');
?>