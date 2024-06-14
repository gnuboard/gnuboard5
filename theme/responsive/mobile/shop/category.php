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

<div id="category" class="menu">
    <div class="menu_wr">
        <?php echo outlogin('theme/shop_basic'); // 외부 로그인 ?>

        <div class="content">
            <?php
            $i = 0;
            foreach($mshop_categories as $cate1){
                if( empty($cate1) ) continue;

                $mshop_ca_row1 = $cate1['text'];
                if($i == 0)
                    echo '<ul class="cate">'.PHP_EOL;
            ?>
                <li>
                    <a href="<?php echo $mshop_ca_row1['url']; ?>"><?php echo get_text($mshop_ca_row1['ca_name']); ?></a>
                    <?php
                    if( count($cate1) > 1 )
                        echo '<button class="sub_ct_toggle ct_op">'.get_text($mshop_ca_row1['ca_name']).' 하위분류 열기</button>'.PHP_EOL;

                    $j=0;
                    foreach($cate1 as $key=>$cate2){
                        if( empty($cate2) || $key === 'text' ) continue;
                        
                        $mshop_ca_row2 = $cate2['text'];
                        if($j == 0)
                            echo '<ul class="sub_cate sub_cate1">'.PHP_EOL;
                    ?>
                        <li>
                            <a href="<?php echo $mshop_ca_row2['url']; ?>"><?php echo get_text($mshop_ca_row2['ca_name']); ?></a>
                            <?php
                            $mshop_ca_res3 = sql_query(get_mshop_category($mshop_ca_row2['ca_id'], 6));
                            if( count($cate2) > 1 )
                                echo '<button type="button" class="sub_ct_toggle ct_op">'.get_text($mshop_ca_row2['ca_name']).' 하위분류 열기</button>'.PHP_EOL;
                            
                            $k = 0;
                            foreach($cate2 as $cate3_key=>$cate3){
                                if( empty($cate2) || $cate3_key === 'text' ) continue;
                                
                                $mshop_ca_row3 = $cate3['text'];
                                if($k == 0)
                                    echo '<ul class="sub_cate sub_cate2">'.PHP_EOL;
                            ?>
                                <li>
                                    <a href="<?php echo $mshop_ca_row3['url']; ?>"><?php echo get_text($mshop_ca_row3['ca_name']); ?></a>
                                    <?php
                                    $mshop_ca_res4 = sql_query(get_mshop_category($mshop_ca_row3['ca_id'], 8));
                                    if(sql_num_rows($mshop_ca_res4))
                                        echo '<button type="button" class="sub_ct_toggle ct_op">'.get_text($mshop_ca_row3['ca_name']).' 하위분류 열기</button>'.PHP_EOL;

                                    for($m=0; $mshop_ca_row4=sql_fetch_array($mshop_ca_res4); $m++) {
                                        if($m == 0)
                                            echo '<ul class="sub_cate sub_cate3">'.PHP_EOL;
                                    ?>
                                        <li>
                                            <a href="<?php echo shop_category_url($mshop_ca_row4['ca_id']); ?>"><?php echo get_text($mshop_ca_row4['ca_name']); ?></a>
                                            <?php
                                            $mshop_ca_res5 = sql_query(get_mshop_category($mshop_ca_row4['ca_id'], 10));
                                            if(sql_num_rows($mshop_ca_res5))
                                                echo '<button type="button" class="sub_ct_toggle ct_op">'.get_text($mshop_ca_row4['ca_name']).' 하위분류 열기</button>'.PHP_EOL;

                                            for($n=0; $mshop_ca_row5=sql_fetch_array($mshop_ca_res5); $n++) {
                                                if($n == 0)
                                                    echo '<ul class="sub_cate sub_cate4">'.PHP_EOL;
                                            ?>
                                                <li>
                                                    <a href="<?php echo shop_category_url($mshop_ca_row5['ca_id']); ?>"><?php echo get_text($mshop_ca_row5['ca_name']); ?></a>
                                                </li>
                                            <?php
                                            }

                                            if($n > 0)
                                                echo '</ul>'.PHP_EOL;
                                            ?>
                                        </li>
                                    <?php
                                    }

                                    if($m > 0)
                                        echo '</ul>'.PHP_EOL;
                                    ?>
                                </li>
                            <?php
                            $k++;
                            }

                            if($k > 0)
                                echo '</ul>'.PHP_EOL;
                            ?>
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
                echo '<p>등록된 분류가 없습니다.</p>'.PHP_EOL;
            ?>
        </div>
       
        <?php include(G5_MSHOP_SKIN_PATH.'/boxtodayview.skin.php'); // 오늘 본 상품 ?>

        <ul id="cate_tnb">
            <li><a href="<?php echo G5_SHOP_URL; ?>/couponzone.php"><i class="fa fa-ticket"></i> 쿠폰존</a></li>
            <li><a href="<?php echo G5_SHOP_URL; ?>/itemuselist.php"><i class="fa fa-camera"></i> 사용후기</a></li>
            <li><a href="<?php echo G5_BBS_URL; ?>/faq.php"><i class="fa fa-question"></i>FAQ</a></li>
            <li><a href="<?php echo G5_BBS_URL; ?>/qalist.php"><i class="fa fa-comments"></i>1:1문의</a></li>
            <li><a href="<?php echo G5_SHOP_URL; ?>/personalpay.php"><i class="fa fa-credit-card"></i>개인결제</a></li>
            <li><a href="<?php echo G5_URL; ?>"><i class="fa fa-home"></i>커뮤니티</a></li>
        </ul> 
    </div>
</div>
<script>
jQuery(function ($){

    $("button.sub_ct_toggle").on("click", function() {
        var $this = $(this);
        $sub_ul = $(this).closest("li").children("ul.sub_cate");

        if($sub_ul.length > 0) {
            var txt = $this.text();

            if($sub_ul.is(":visible")) {
                txt = txt.replace(/닫기$/, "열기");
                $this
                    .removeClass("ct_cl")
                    .text(txt);
            } else {
                txt = txt.replace(/열기$/, "닫기");
                $this
                    .addClass("ct_cl")
                    .text(txt);
            }

            $sub_ul.toggle();
        }
    });
});
</script>
