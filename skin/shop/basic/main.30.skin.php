<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
상품리스트가 일정 시간마다 좌로 롤링되는 스킨
롤링되기 위해서는 상품이 2줄 이상이어야 함
*/
?>

<link rel="stylesheet" href="<?php echo G4_SHOP_SKIN_URL; ?>/style.css">

<!-- 상품유형 30 시작 { -->
<?php
$itemtype = $this->type;

for ($i=1; $row=sql_fetch_array($result); $i++) {
    if ($i == 1) {
        if ($this->css) {
            echo "<ul id=\"smt_{$itemtype}\" class=\"{$this->css}\">\n";
        } else {
            echo "<ul id=\"smt_{$itemtype}\" class=\"sct smt_30\">\n";
        }
        echo "<li class=\"sct_li sct_li_first\">\n";
    }

    if ($i > 1 && $i%$this->list_mod == 1) {
        echo "</li>\n";
        echo "<li class=\"sct_li\">\n";
    }

    echo "<div class=\"sct_div\">";

    if ($this->href) {
        echo "<a href=\"{$this->href}{$row['it_id']}\" class=\"sct_a\">\n";
    }

    if ($this->view_it_img) {
        echo "<span class=\"sct_img\">".get_it_image($row['it_id'], $this->img_width, $this->img_height)."</span>\n";
    }

    if ($this->view_it_id) {
        echo "<b>".stripslashes($row['it_id'])."</b>\n";
    }

    if ($this->view_it_name) {
        echo "<b>".stripslashes($row['it_name'])."</b>\n";
    }

    if ($this->view_it_cust_price) {
        echo "<span class=\"sct_cost\">".display_price($row['it_cust_price'])."</span>\n";
    }

    if ($this->view_it_price) {
        echo "<span class=\"sct_cost\">".display_price(get_price($row), $row['it_tel_inq'])."</span>\n";
    }

    if ($this->view_it_icon) {
        echo "<span class=\"sct_icon\">".item_icon($row)."</span>\n";
    }

    if ($this->view_sns) {
        echo "<div class=\"sct_sns\">";
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G4_SHOP_URL.'/img/sns_fb.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G4_SHOP_URL.'/img/sns_twt.png');
        echo get_sns_share_link('googleplus', $sns_url, $sns_title, G4_SHOP_URL.'/img/sns_goo.png');
        echo "</div>\n";
    }

    if ($this->href) {
        echo "</a>\n";
    }

    echo "</div>\n";
}

if ($i > 1) {
    echo "</li>\n";
    echo "</ul>\n";
}

if($i == 1) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>

<script>
$(function() {
    var $smt<?php echo $itemtype; ?> = $("#smt_<?php echo $itemtype; ?> li.sct_li");
    var $smt<?php echo $itemtype; ?>_a = $("#smt_<?php echo $itemtype; ?> li.sct_li a");
    var smt<?php echo $itemtype; ?>_width = $smt<?php echo $itemtype; ?>.width();
    var smt<?php echo $itemtype; ?>_count = $smt<?php echo $itemtype; ?>.size();
    var smt<?php echo $itemtype; ?>_c_idx = smt<?php echo $itemtype; ?>_o_idx = 0;
    var smt<?php echo $itemtype; ?>_time = 7000;
    var smt<?php echo $itemtype; ?>_a_time = 1500;
    var smt<?php echo $itemtype; ?>_interval = null;

    if(smt<?php echo $itemtype; ?>_count > 1)
        smt<?php echo $itemtype; ?>_interval = setInterval(left_rolling, smt<?php echo $itemtype; ?>_time);

    $smt<?php echo $itemtype; ?>.hover(
        function() {
            if(smt<?php echo $itemtype; ?>_interval != null)
                clearInterval(smt<?php echo $itemtype; ?>_interval);
        },
        function() {
            if(smt<?php echo $itemtype; ?>_interval != null)
                clearInterval(smt<?php echo $itemtype; ?>_interval);

            if(smt<?php echo $itemtype; ?>_count > 1)
                smt<?php echo $itemtype; ?>_interval = setInterval(left_rolling, smt<?php echo $itemtype; ?>_time);
        }
    );

    $smt<?php echo $itemtype; ?>_a.on("focusin", function() {
        if(smt<?php echo $itemtype; ?>_interval != null)
            clearInterval(smt<?php echo $itemtype; ?>_interval);
    });

    $smt<?php echo $itemtype; ?>_a.on("focusout", function() {
        if(smt<?php echo $itemtype; ?>_interval != null)
            clearInterval(smt<?php echo $itemtype; ?>_interval);

        if(smt<?php echo $itemtype; ?>_count > 1)
            smt<?php echo $itemtype; ?>_interval = setInterval(left_rolling, smt<?php echo $itemtype; ?>_time);
    });

    function left_rolling() {
        $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_o_idx).animate(
            { left: "-="+smt<?php echo $itemtype; ?>_width+"px" }, smt<?php echo $itemtype; ?>_a_time
        );

        smt<?php echo $itemtype; ?>_c_idx = (smt<?php echo $itemtype; ?>_o_idx + 1) % smt<?php echo $itemtype; ?>_count;

        $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_c_idx).css("display", "block").animate(
            { left: "-="+smt<?php echo $itemtype; ?>_width+"px" }, smt<?php echo $itemtype; ?>_a_time,
            function() {
                $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_o_idx).css("display", "none").css("left", smt<?php echo $itemtype; ?>_width+"px");
                smt<?php echo $itemtype; ?>_o_idx = smt<?php echo $itemtype; ?>_c_idx;
            }
        );
    }
});
</script>
<!-- } 상품진열 30 끝 -->