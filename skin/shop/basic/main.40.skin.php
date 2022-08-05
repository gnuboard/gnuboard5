<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);

// 관련상품 스킨은 사품을 한줄에 하나만 표시하며 해당 상품에 관련상품이 등록되어 있는 경우 기본으로 7개까지 노출합니다.
add_javascript('<script src="'.G5_JS_URL.'/shop.list.action.js"></script>', 10);
?>

<!-- 상품진열 40 시작 { -->
<?php
$i=0;
foreach((array) $list as $row){

    if( empty($row) ) continue;
    $i++;

    $item_link_href = shop_item_url($row['it_id']);
    $star_score = $row['it_use_avg'] ? (int) get_star($row['it_use_avg']) : '';

    if ($list_mod >= 2) { // 1줄 이미지 : 2개 이상
        if ($i%$list_mod == 0) $sct_last = ' sct_last'; // 줄 마지막
        else if ($i%$list_mod == 1) $sct_last = ' sct_clear'; // 줄 첫번째
        else $sct_last = '';
    } else { // 1줄 이미지 : 1개
        $sct_last = ' sct_clear';
    }

    if ($i == 1) {
        if ($this->css) {
            echo "<ul class=\"{$this->css}\">\n";
        } else {
            echo "<ul class=\"smt smt_10\">\n";
        }
   }

    echo "<li class=\"sct_li{$sct_last}\">\n";

    echo "<div class=\"sct_img\">\n";

    if ($this->href) {
        echo "<a href=\"{$item_link_href}\">\n";
    }

    if ($this->view_it_img) {
        echo get_it_image($row['it_id'], $this->img_width, $this->img_height, '', '', stripslashes($row['it_name']))."\n";
    }

    if ($this->href) {
        echo "</a>\n";
    }

	echo "<div class=\"sct_btn\">
		<button type=\"button\" class=\"btn_cart sct_cart\" data-it_id=\"{$row['it_id']}\"><i class=\"fa fa-shopping-cart\" aria-hidden=\"true\"></i> 장바구니</button>\n";
	echo "</div>\n";
	
	echo "<div class=\"cart-layer\"></div>\n";
    
	if ($this->view_it_icon) {
        // 품절
        if (is_soldout($row['it_id'], true)) {
            echo '<span class="shop_icon_soldout"><span class="soldout_txt">SOLD OUT</span></span>';
        }
    }
    echo "</div>\n";
	
	echo "<div class=\"sct_ct_wrap\">\n";

	// 사용후기 평점표시
	if ($this->view_star && $star_score) {
        echo "<div class=\"sct_star\"><span class=\"sound_only\">고객평점</span><img src=\"".G5_SHOP_URL."/img/s_star".$star_score.".png\" alt=\"별점 ".$star_score."점\" class=\"sit_star\"></div>\n";
    }
	
    if ($this->view_it_id) {
        echo "<div class=\"sct_id\">&lt;".stripslashes($row['it_id'])."&gt;</div>\n";
    }

    if ($this->href) {
        echo "<div class=\"sct_txt\"><a href=\"{$item_link_href}\">\n";
    }

    if ($this->view_it_name) {
        echo stripslashes($row['it_name'])."\n";
    }

    if ($this->href) {
        echo "</a></div>\n";
    }
	
	if ($this->view_it_basic && $row['it_basic']) {
        echo "<div class=\"sct_basic\">".stripslashes($row['it_basic'])."</div>\n";
    }

    if ($this->view_it_cust_price || $this->view_it_price) {

        echo "<div class=\"sct_cost\">\n";
        if ($this->view_it_price) {
            echo display_price(get_price($row), $row['it_tel_inq'])."\n";
        }
        echo "</div>\n";
    }
	
	
	// 위시리스트 + 공유 버튼 시작 {
	echo "<div class=\"sct_op_btn\">\n";
	echo "<button type=\"button\" class=\"btn_wish\" data-it_id=\"{$row['it_id']}\"><span class=\"sound_only\">위시리스트</span><i class=\"fa fa-heart-o\" aria-hidden=\"true\"></i></button>\n";
    if ($this->view_sns) {
	    echo "<button type=\"button\" class=\"btn_share\"><span class=\"sound_only\">공유하기</span><i class=\"fa fa-share-alt\" aria-hidden=\"true\"></i></button>\n";
	}

	echo "<div class=\"sct_sns_wrap\">";
	if ($this->view_sns) {
        $sns_top = $this->img_height + 10;
        $sns_url  = $item_link_href;
        $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
        echo "<div class=\"sct_sns\">";
		echo "<h3>SNS 공유</h3>";
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/facebook.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/twitter.png');
        echo "<button type=\"button\" class=\"sct_sns_cls\"><span class=\"sound_only\">닫기</span><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>";
        echo "</div>\n";
    }
	echo "<div class=\"sct_sns_bg\"></div>";
	echo "</div></div>\n";
	// } 위시리스트 + 공유 버튼 끝

	echo "</div>\n";
	
	if ($this->view_it_icon) {
        echo "<div class=\"sit_icon_li\">".item_icon($row)."</div>\n";
    }
	
    echo "</li>\n";
}

if ($i >= 1) echo "</ul>\n";

if($i == 0) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>
<!-- } 상품진열40 끝 -->

<script>
//SNS 공유
$(function (){
	$(".btn_share").on("click", function() {
		$(this).parent("div").children(".sct_sns_wrap").show();
	});
    $('.sct_sns_bg, .sct_sns_cls').click(function(){
	    $('.sct_sns_wrap').hide();
	});
});			
</script>