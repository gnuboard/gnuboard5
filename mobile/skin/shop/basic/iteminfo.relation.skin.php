<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 스킨경로
$skin_dir = G5_MSHOP_SKIN_PATH;
$ca_dir_check = true;

if($it['it_mobile_skin']) {
    $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$it['it_mobile_skin'];

    if(is_dir($skin_dir)) {
        $form_skin_file = $skin_dir.'/item.form.skin.php';

        if(is_file($form_skin_file))
            $ca_dir_check = false;
    }
}

if($ca_dir_check) {
    if($ca['ca_mobile_skin_dir']) {
        $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$ca['ca_mobile_skin_dir'];

        if(is_dir($skin_dir)) {
            $form_skin_file = $skin_dir.'/item.form.skin.php';

            if(!is_file($skin_file))
                $skin_dir = G5_MSHOP_SKIN_PATH;
        } else {
            $skin_dir = G5_MSHOP_SKIN_PATH;
        }
    }
}

define('G5_SHOP_CSS_URL', str_replace(G5_PATH, G5_URL, $skin_dir));

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>

<h1 id="win_title">관련상품</h1>

<div class="sct_wrap">
    <?php
    $rel_skin_file = $skin_dir.'/'.$default['de_mobile_rel_list_skin'];
    if(!is_file($rel_skin_file))
        $rel_skin_file = G5_MSHOP_SKIN_PATH.'/'.$default['de_mobile_rel_list_skin'];

    $sql = " select b.* from {$g5['g5_shop_item_relation_table']} a left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id) where a.it_id = '{$it['it_id']}' and b.it_use='1' ";

    $list = new item_list($rel_skin_file, 1, 1, $default['de_mobile_rel_img_width'], $default['de_mobile_rel_img_height']);
    $list->set_mobile(true);
    $list->set_query($sql);
    $list->set_view('sns', true);
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