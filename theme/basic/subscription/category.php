<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function get_mshop_category($ca_id, $len)
{
    global $g5;

    $sql = " select ca_id, ca_name from {$g5['g5_shop_category_table']}
                where ca_use = '1' ";
    if($ca_id)
        $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " and length(ca_id) = '$len' order by ca_order, ca_id ";

    return $sql;
}

$mshop_categories = get_shop_category_array(true);
?>
<div id="category">
	<h2>전체메뉴</h2>
    <?php
    $i = 0;
    foreach($mshop_categories as $cate1){
        if( empty($cate1) ) continue;

        $mshop_ca_row1 = $cate1['text'];
        if($i == 0)
            echo '<ul class="cate">'.PHP_EOL;
    ?>
        <li class="cate_li_1">
            <a href="<?php echo $mshop_ca_row1['url']; ?>" class="cate_li_1_a"><?php echo get_text($mshop_ca_row1['ca_name']); ?></a>
            <?php
            $j=0;
            foreach($cate1 as $key=>$cate2){
                if( empty($cate2) || $key === 'text' ) continue;
                
                $mshop_ca_row2 = $cate2['text'];
                if($j == 0)
                    echo '<ul class="sub_cate sub_cate1">'.PHP_EOL;
            ?>
                <li class="cate_li_2">
                    <a href="<?php echo $mshop_ca_row2['url']; ?>"><?php echo get_text($mshop_ca_row2['ca_name']); ?></a>
                </li>
            <?php
            $j++;
            }

            if($j > 0)
                echo '</ul>'.PHP_EOL;
            ?>
        </li>
    <?php
    $i++;
    }   // end for

    if($i > 0)
        echo '</ul>'.PHP_EOL;
    else
        echo '<p class="no-cate">등록된 분류가 없습니다.</p>'.PHP_EOL;
    ?>
    <button type="button" class="close_btn"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">카테고리 닫기</span></button>
</div>
<div id="category_all_bg"></div>
<script>
$(function (){
    var $category = $("#category");

    $("#menu_open").on("click", function() {
        $category.css("display","block");
        $("#category_all_bg").css("display","block");
    });

    $("#category .close_btn, #category_all_bg").on("click", function(){
        $category.css("display","none");
		$("#category_all_bg").css("display","none");
    });
});
$(document).mouseup(function (e){
	var container = $("#category");
	if( container.has(e.target).length === 0)
	container.hide();
});
</script>
