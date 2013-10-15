<?php
include_once("./_common.php");

define("_INDEX_", TRUE);

include_once(G5_MSHOP_PATH.'/shop.head.php');
?>

<script src="<?php echo G5_JS_URL; ?>/jquery.event.move.js"></script>
<script src="<?php echo G5_JS_URL; ?>/jquery.event.swipe.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery.slideview.js"></script>

<div id="sidx">

    <div id="sidx_slide">
        <section class="sct_wrap">
            <header>
                <h2>최신상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 최신상품 모음</p>
            </header>
            <?php
            $list = new item_list();
            $list->set_mobile(true);
            $list->set_type(1);
            $list->set_view('it_id', false);
            $list->set_view('it_name', true);
            $list->set_view('it_cust_price', false);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            echo $list->run();
            ?>
            <div><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=3">더 보기</a></div>
        </section>

        <section class="sct_wrap">
            <header>
                <h2>히트상품</h2>
                <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 히트상품 모음</p>
            </header>
            <?php
            $list = new item_list();
            $list->set_mobile(true);
            $list->set_type(2);
            $list->set_view('it_id', false);
            $list->set_view('it_name', true);
            $list->set_view('it_cust_price', false);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            echo $list->run();
            ?>
            <div><a href="<?php echo G5_SHOP_URL; ?>/listtype.php?type=1">더 보기</a></div>
        </section>

        <section class="sct_wrap">
        <header>
            <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=2">추천상품</a></h2>
            <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 추천상품 모음</p>
        </header>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(3);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', false);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();
        ?>
    </section>

    <section class="sct_wrap">
        <header>
            <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=4">인기상품</a></h2>
            <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 인기상품 모음</p>
        </header>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(4);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', false);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();
        ?>
    </section>

    <section class="sct_wrap">
        <header>
            <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=5">할인상품</a></h2>
            <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 할인상품 모음</p>
        </header>
        <?php
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(5);
        $list->set_view('it_id', false);
        $list->set_view('it_name', true);
        $list->set_view('it_cust_price', false);
        $list->set_view('it_price', true);
        $list->set_view('it_icon', true);
        $list->set_view('sns', true);
        echo $list->run();
        ?>
    </section>

    </div>

</div>

<script>
$(function() {
    $("#sidx").slideSwipe(
        {
            el_class: "sidx",
            selector: "section.sct_wrap",
            slide_tab: "slide_tab",
            slide_class: "sidx_slide",
            active_class: "slide_active",
            tab_active: "tab_active",
            duration: 300
        }
    );
});
</script>

<?php
include_once(G5_MSHOP_PATH.'/shop.tail.php');
?>