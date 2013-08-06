<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
리스트의 상품이 순차적으로 위에서 내려옴
*/
?>

<link rel="stylesheet" href="<?php echo G4_SHOP_SKIN_URL; ?>/style.css">

<!-- 상품진열 50 시작 { -->
<?php
$itemtype = $this->type;

for ($i=1; $row=sql_fetch_array($result); $i++) {
    if ($this->list_mod >= 2) { // 1줄 이미지 : 2개 이상
        if ($i%$this->list_mod == 0) $sct_last = ' sct_last'; // 줄 마지막
        else if ($i%$this->list_mod == 1) $sct_last = ' sct_clear'; // 줄 첫번째
        else $sct_last = '';
    } else { // 1줄 이미지 : 1개
        $sct_last = ' sct_clear';
    }

    if ($i == 1) {
        if ($this->css) {
            echo "<ul id=\"smt_{$itemtype}\" class=\"{$this->css}\">\n";
        } else {
            echo "<ul id=\"smt_{$itemtype}\" class=\"sct smt_50\">\n";
        }
        echo "<li class=\"sct_li sct_li_first\">\n";
    }

    if ($i > 1 && $i%$this->list_mod == 1) {
        echo "</li>\n";
        echo "<li class=\"sct_li\">\n";
    }

    echo "<div class=\"sct_div{$sct_last}\">";

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
    var smt<?php echo $itemtype; ?>_count = $smt<?php echo $itemtype; ?>.size();
    var $smt<?php echo $itemtype; ?>_height = $smt<?php echo $itemtype; ?>.height();
    var smt<?php echo $itemtype; ?>_c_idx = smt<?php echo $itemtype; ?>_o_idx = 0;
    var smt<?php echo $itemtype; ?>_time = 6000;
    var smt<?php echo $itemtype; ?>_a_time = 800;
    var smt<?php echo $itemtype; ?>_delay = 300;
    var smt<?php echo $itemtype; ?>_interval = null;

    // 초기실행
    if(smt<?php echo $itemtype; ?>_count > 0)
        item_drop();

    if(smt<?php echo $itemtype; ?>_count > 1)
        smt<?php echo $itemtype; ?>_interval = setInterval(item_drop, smt<?php echo $itemtype; ?>_time);

    $smt<?php echo $itemtype; ?>.hover(
        function() {
            if(smt<?php echo $itemtype; ?>_interval != null)
                clearInterval(smt<?php echo $itemtype; ?>_interval);
        },
        function() {
            if(smt<?php echo $itemtype; ?>_interval != null)
                clearInterval(smt<?php echo $itemtype; ?>_interval);

            if(smt<?php echo $itemtype; ?>_count > 1)
                smt<?php echo $itemtype; ?>_interval = setInterval(item_drop, smt<?php echo $itemtype; ?>_time);
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
            smt<?php echo $itemtype; ?>_interval = setInterval(item_drop, smt<?php echo $itemtype; ?>_time);
    });

    function item_drop() {
        var delay = 0;
        $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_o_idx).css("display", "none");
        $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_o_idx).find("div").css("top", "-"+$smt<?php echo $itemtype; ?>_height+"px");

        smt<?php echo $itemtype; ?>_c_idx = (smt<?php echo $itemtype; ?>_o_idx + 1) % smt<?php echo $itemtype; ?>_count;

        $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_c_idx).css("display", "block");
        $smt<?php echo $itemtype; ?>.eq(smt<?php echo $itemtype; ?>_c_idx).find("div").each(function() {
            $(this).delay(delay).animate(
                { top: "+="+$smt<?php echo $itemtype; ?>_height+"px" }, smt<?php echo $itemtype; ?>_a_time
            );

            delay += smt<?php echo $itemtype; ?>_delay;
        });
        smt<?php echo $itemtype; ?>_o_idx = smt<?php echo $itemtype; ?>_c_idx;
    }
});
</script>
<!-- } 상품진열 50 끝 -->