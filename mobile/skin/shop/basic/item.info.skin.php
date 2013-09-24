<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 관리자가 확인한 사용후기의 갯수를 얻음
$sql = " select count(*) as cnt from `{$g5['g5_shop_item_use_table']}` where it_id = '{$it_id}' and is_confirm = '1' ";
$row = sql_fetch($sql);
$item_use_count = $row['cnt'];

// 상품문의의 갯수를 얻음
$sql = " select count(*) as cnt from `{$g5['g5_shop_item_qa_table']}` where it_id = '{$it_id}' ";
$row = sql_fetch($sql);
$item_qa_count = $row['cnt'];

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from {$g5['g5_shop_item_relation_table']} a
           left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '{$it['it_id']}' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];

if(!function_exists('pg_anchor')) {
    function pg_anchor($anc_id) {
        global $default;
        global $item_use_count, $item_qa_count, $item_relation_count;
?>
        <ul class="sanchor">
            <li><a href="#sit_inf" <?php if ($anc_id == 'inf') echo 'class="sanchor_on"'; ?>>상품정보</a></li>
            <li><a href="#sit_use" <?php if ($anc_id == 'use') echo 'class="sanchor_on"'; ?>>사용후기 <span class="item_use_count"><?php echo $item_use_count; ?></span></a></li>
            <li><a href="#sit_qa" <?php if ($anc_id == 'qa') echo 'class="sanchor_on"'; ?>>상품문의 <span class="item_qa_count"><?php echo $item_qa_count; ?></span></a></li>
            <?php if ($default['de_baesong_content']) { ?><li><a href="#sit_dvr" <?php if ($anc_id == 'dvr') echo 'class="sanchor_on"'; ?>>배송정보</a></li><?php } ?>
            <?php if ($default['de_change_content']) { ?><li><a href="#sit_ex" <?php if ($anc_id == 'ex') echo 'class="sanchor_on"'; ?>>교환정보</a></li><?php } ?>
            <li><a href="#sit_rel" <?php if ($anc_id == 'rel') echo 'class="sanchor_on"'; ?>>관련상품 <span class="item_relation_count"><?php echo $item_relation_count; ?></span></a></li>
        </ul>
    <?php
    }
}
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<section id="sit_inf">
    <h2>상품 정보</h2>
    <?php echo pg_anchor('inf'); ?>

    <?php if ($it['it_basic']) { // 상품 기본설명 ?>
    <div id="sit_inf_basic">
         <?php echo $it['it_basic']; ?>
    </div>
    <?php } ?>

    <?php if ($it['it_explan'] || $it['it_mobile_explan']) { // 상품 상세설명 ?>
    <div id="sit_inf_explan">
        <?php echo ($it['it_mobile_explan'] ? conv_content($it['it_mobile_explan'], 1) : conv_content($it['it_explan'], 1)); ?>
    </div>
    <?php } ?>

    <h3>상품 정보 고시</h3>
    <?php
    if ($it['it_info_value']) {
        $info_data = unserialize($it['it_info_value']);
        $gubun = $it['it_info_gubun'];
        $info_array = $item_info[$gubun]['article'];
    ?>
    <!-- 상품정보고시 -->
    <table id="sit_inf_open">
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <?php
    foreach($info_data as $key=>$val) {
        $ii_title = $info_array[$key][0];
        $ii_value = $val;
    ?>
    <tr valign="top">
        <th scope="row"><?php echo $ii_title; ?></th>
        <td><?php echo $ii_value; ?></th>
    </tr>
    <?php } //foreach?>
    </tbody>
    </table>
    <!-- 상품정보고시 end -->
    <?php } //if?>

</section>
<!-- 상품설명 end -->

<section id="sit_use">
    <h2>사용후기</h2>
    <?php echo pg_anchor('use'); ?>

    <div id="itemuse"><?php include_once('./itemuse.php'); ?></div>
</section>

<section id="sit_qa">
    <h2>상품문의</h2>
    <?php echo pg_anchor('qa'); ?>

    <div id="itemqa"><?php include_once('./itemqa.php'); ?></div>
</section>


<?php if ($default['de_baesong_content']) { // 배송정보 내용이 있다면 ?>
<section id="sit_dvr">
    <h2>배송정보</h2>
    <?php echo pg_anchor('dvr'); ?>

    <?php echo conv_content($default['de_baesong_content'], 1); ?>
</section>
<?php } ?>


<?php if ($default['de_change_content']) { // 교환/반품 내용이 있다면 ?>
<section id="sit_ex">
    <h2>교환/반품</h2>
    <?php echo pg_anchor('ex'); ?>

    <?php echo conv_content($default['de_change_content'], 1); ?>
</section>
<?php } ?>

<section id="sit_rel">
    <h2>관련상품</h2>
    <?php echo pg_anchor('rel'); ?>

    <div class="sct_wrap">
        <?php
        $sql = " select b.* from {$g5['g5_shop_item_relation_table']} a left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id) where a.it_id = '{$it['it_id']}' and b.it_use='1' ";

        $list = new item_list("item.relation.skin.php", $default['de_rel_list_mod'], 1, $default['de_rel_img_width'], $default['de_rel_img_height']);
        $list->set_mobile(true);
        $list->set_query($sql);
        echo $list->run();
        ?>
    </div>
</section>

<script>
$(function(){
    // 상품이미지 크게보기
    $(".popup_item_image").click(function() {
        var url = $(this).attr("href");
        var top = 10;
        var left = 10;
        var opt = 'scrollbars=yes,top='+top+',left='+left;
        popup_window(url, "largeimage", opt);

        return false;
    });
});

var save_use_id = null;
function use_menu(id)
{
    if (save_use_id != null)
        document.getElementById(save_use_id).style.display = "none";
    menu(id);
    save_use_id = id;
}

var save_qa_id = null;
function qa_menu(id)
{
    if (save_qa_id != null)
        document.getElementById(save_qa_id).style.display = "none";
    menu(id);
    save_qa_id = id;
}
</script>

<!--[if lte IE 6]>
<script>
// 이미지 등비율 리사이징
$(window).load(function() {
    view_image_resize();
});

function view_image_resize()
{
    var $img = $("#sit_inf_explan img");
    var img_wrap = $("#sit_inf_explan").width();
    var win_width = $(window).width() - 35;
    var res_width = 0;

    if(img_wrap < win_width)
        res_width = img_wrap;
    else
        res_width = win_width;

    $img.each(function() {
        var img_width = $(this).width();
        var img_height = $(this).height();
        var this_width = $(this).data("width");
        var this_height = $(this).data("height");

        if(this_width == undefined) {
            $(this).data("width", img_width); // 원래 이미지 사이즈
            $(this).data("height", img_height);
            this_width = img_width;
            this_height = img_height;
        }

        if(this_width > res_width) {
            $(this).width(res_width);
            var res_height = Math.round(res_width * $(this).data("height") / $(this).data("width"));
            $(this).height(res_height);
        } else {
            $(this).width(this_width);
            $(this).height(this_height);
        }
    });
}
</script>
<![endif]-->