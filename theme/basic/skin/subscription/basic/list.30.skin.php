<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품진열 30 시작 { -->
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
            echo "<ul class=\"sct sct_30\">\n";
        }
    }

    $list_top_pad = 20;
    $list_right_pad = 10;
    $list_bottom_pad = 20;
    $list_left_pad = $this->img_width + 10;
    $list_real_width = 360;
    $list_width = $list_real_width - $list_right_pad - $list_left_pad;
    $list_height = $this->img_height - $list_top_pad - $list_bottom_pad;

    echo "<li class=\"sct_li{$sct_last}\" data-css=\"nocss\" style=\"padding:{$list_top_pad}px {$list_right_pad}px {$list_bottom_pad}px {$list_left_pad}px;width:{$list_width}px;height:{$list_height}px\">\n";

    if ($this->href) {
        echo "<div class=\"sct_img\"><a href=\"{$item_link_href}\" class=\"sct_a\">\n";
    }

    if ($this->view_it_img) {
        echo get_it_image($row['it_id'], $this->img_width, $this->img_height, '', '', stripslashes($row['it_name']))."\n";
    }

    if ($this->href) {
        echo "</a></div>\n";
    }

    if ($this->view_it_icon) {
        echo "<div class=\"sct_icon\">".item_icon($row)."</div>\n";
    }

    if ($this->view_it_id) {
        echo "<div class=\"sct_id\">&lt;".stripslashes($row['it_id'])."&gt;</div>\n";
    }

    if ($this->href) {
        echo "<div class=\"sct_txt\"><a href=\"{$item_link_href}\" class=\"sct_a\">\n";
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

        if ($this->view_it_cust_price && $row['it_cust_price']) {
           echo "<span class=\"sct_dict\">".display_price($row['it_cust_price'])."</span>\n";
        }

        if ($this->view_it_price) {
            echo display_price(get_price($row), $row['it_tel_inq'])."\n";
        }

        echo "</div>\n";

    }

    if ($this->view_sns) {
        $sns_url  = $item_link_href;
        $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
        echo "<div class=\"sct_sns\">";
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_fb_s.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_twt_s.png');
        echo "</div>\n";
    }

    $arrow_left = $this->img_width - 10;
    echo "<div class=\"sct_arw_toleft\" style=\"left:{$arrow_left}px\"></div>";

    echo "</li>\n";
}

if ($i >= 1) echo "</ul>\n";

if($i == 0) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>
<!-- } 상품진열 12 끝 -->