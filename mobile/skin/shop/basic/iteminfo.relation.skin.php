<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<h1 id="win_title">관련상품</h1>

<div class="sct_wrap">
    <?php
    $sql = " select b.* from {$g5['g5_shop_item_relation_table']} a left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id) where a.it_id = '{$it['it_id']}' and b.it_use='1' ";

    $list = new item_list("relation.skin.php", $default['de_rel_list_mod'], 1, $default['de_rel_img_width'], $default['de_rel_img_height']);
    $list->set_mobile(true);
    $list->set_query($sql);
    echo $list->run();
    ?>
</div>

<script>
$(function() {
    $("a.sct_a").on("click", function() {
        window.opener.location.href = this.href;
        self.close();
        return false;
    });
});
</script>