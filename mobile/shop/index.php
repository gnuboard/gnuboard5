<?php
include_once("./_common.php");

define("_INDEX_", TRUE);

include_once(G4_MSHOP_PATH.'/shop.head.php');
?>

<div id="sidx">

    <section class="sct_wrap">
        <header>
            <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=3">최신상품</a></h2>
            <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 최신상품 모음</p>
        </header>
        <?php 
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(1);
        echo $list->run();
        ?>
    </section>

    <section class="sct_wrap">
        <header>
            <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=1">히트상품</a></h2>
            <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 히트상품 모음</p>
        </header>
        <?php 
        $list = new item_list();
        $list->set_mobile(true);
        $list->set_type(2);
        echo $list->run();
        ?>
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
        echo $list->run();
        ?>
    </section>

</div>

<?php
include_once(G4_MSHOP_PATH.'/shop.tail.php');
?>