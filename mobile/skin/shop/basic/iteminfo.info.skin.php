<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/iteminfo.lib.php');
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<section id="sit_inf">
    <h2>상품 정보</h2>
    <?php echo pg_anchor($info); ?>

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