<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
리스트의 상품이 순차적으로 위에서 내려옴
*/
?>

<link rel="stylesheet" href="<?php echo G4_SHOP_SKIN_URL; ?>/style.css">

<!-- 상품진열 50 시작 { -->
<?php
for ($i=1; $row=sql_fetch_array($result); $i++) {
    $sct_last = '';
    if($i>1 && $i%$this->list_mod == 0)
        $sct_last = ' sct_last'; // 줄 마지막

    if ($i == 1) {
        if ($this->css) {
            echo "<ul id=\"smt_{$this->type}\" class=\"{$this->css}\">\n";
        } else {
            echo "<ul id=\"smt_{$this->type}\" class=\"sct smt_50\">\n";
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
$.fn.itemDrop = function(option)
{
    var $smt = this.find("li.sct_li");
    var $smt_a = $smt.find("a");
    var count = $smt.size();
    var height = $smt.height();
    var c_idx = o_idx = 0;
    var fx = null;
    var delay = 0;

    // 기본 설정값
    var settings = $.extend({
        interval: 6000,
        duration: 800,
        delay: 300
    }, option);

    // 초기실행
    if(count > 0) {
        $smt.eq(0).find("div.sct_div").each(function() {
            $(this).delay(delay).animate(
                { top: "+="+height+"px" }, settings.duration
            );

            delay += settings.delay;
        });
    }

    if(count > 1)
        fx = setInterval(item_drop, settings.interval);

    $smt.hover(
        function() {
            if(fx != null)
                clearInterval(fx);
        },
        function() {
            if(fx != null)
                clearInterval(fx);

            if(count > 1)
                fx = setInterval(item_drop, settings.interval);
        }
    );

    $smt_a.on("focusin", function() {
        if(fx != null)
            clearInterval(fx);
    });

    $smt_a.on("focusout", function() {
        if(fx != null)
            clearInterval(fx);

        if(count > 1)
            fx = setInterval(item_drop, settings.interval);
    });

    function item_drop() {
        $smt.each(function(index) {
            if($(this).is(":visible")) {
                o_idx = index;
                return false;
            }
        });

        delay = 0;

        $smt.eq(o_idx).css("display", "none");
        $smt.eq(o_idx).find("div.sct_div").css("top", "-"+height+"px");

        c_idx = (o_idx + 1) % count;

        $smt.eq(c_idx).css("display", "block");
        $smt.eq(c_idx).find("div.sct_div").each(function() {
            $(this).delay(delay).animate(
                { top: "+="+height+"px" }, settings.duration
            );

            delay += settings.delay;
        });

        o_idx = c_idx;
    }
}
$(function() {
    $("#smt_<?php echo $this->type; ?>").itemDrop();
    // 기본 설정값을 변경하려면 아래처럼 사용
    //$("#smt_<?php echo $this->type; ?>").itemDrop({ interval: 6000, duration: 800, delay: 300 });
});
</script>
<!-- } 상품진열 50 끝 -->