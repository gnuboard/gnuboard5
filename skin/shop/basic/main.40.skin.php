<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
상품리스트가 일정 시간마다 바뀜
롤링되기 위해서는 상품이 2줄 이상이어야 함
*/
?>

<link rel="stylesheet" href="<?php echo G4_SHOP_SKIN_URL; ?>/style.css">

<!-- 이전 재생 정지 다음 버튼 시작 { -->
<ul class="sctrl">
    <li><button type="button" class="sctrl_prev">이전<span></span></button></li>
    <li><button type="button" class="sctrl_play">효과재생<span></span></button></li>
    <li><button type="button" class="sctrl_stop">효과정지<span></span></button></li>
    <li><button type="button" class="sctrl_next">다음<span></span></button></li>
</ul>
<!-- } 이전 재생 정지 다음 버튼 끝 -->

<!-- 상품진열 40 시작 { -->
<?php
for ($i=1; $row=sql_fetch_array($result); $i++) {
    $sct_last = '';
    if($i>1 && $i%$this->list_mod == 0)
        $sct_last = ' sct_last'; // 줄 마지막

    if ($i == 1) {
        if ($this->css) {
            echo "<div id=\"smt_{$this->type}\" class=\"{$this->css}\">\n";
        } else {
            echo "<div id=\"smt_{$this->type}\" class=\"sct smt_40\">\n";
        }
        echo "<ul class=\"sct_ul sct_ul_first\">\n";
    }

    if ($i>1 && $i%$this->list_mod == 1) {
        echo "</ul>\n";
        echo "<ul class=\"sct_ul\">\n";
    }

    echo "<li class=\"sct_li{$sct_last}\">";

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

    if ($this->href) {
        echo "</a>\n";
    }

    if ($this->view_sns) {
        echo "<div class=\"sct_sns\">";
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G4_SHOP_URL.'/img/sns_fb.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G4_SHOP_URL.'/img/sns_twt.png');
        echo get_sns_share_link('googleplus', $sns_url, $sns_title, G4_SHOP_URL.'/img/sns_goo.png');
        echo "</div>\n";
    }

    echo "</li>\n";
}

if ($i > 1) {
    echo "</ul>\n";
    echo "</div>\n";
}

if($i == 1) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>

<script>
$.fn.itemlistShow = function(option)
{
    var $smt = this.find("ul.sct_ul");
    var $smt_a = $smt.find("a.sct_a");
    var count = $smt.size();
    var c_idx = o_idx = 0;
    var fx = null;

    // 기본 설정값
    var settings = $.extend({
        interval: 3000
    }, option);

    if(count < 2)
        return;

    fx = setInterval(itemlist_show, settings.interval);

    $smt.hover(
        function() {
            if(fx != null)
                clearInterval(fx);
        },
        function() {
            if(fx != null)
                clearInterval(fx);

            if(count > 1)
                fx = setInterval(itemlist_show, settings.interval);
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
            fx = setInterval(itemlist_show, settings.interval);
    });

    function itemlist_show() {
        $smt.each(function(index) {
            if($(this).is(":visible")) {
                o_idx = index;
                return false;
            }
        });

        $smt.eq(o_idx).css("display", "none");
        c_idx = (o_idx + 1) % count;
        $smt.eq(c_idx).css("display", "block");
        o_idx = c_idx;
    }
}

$(function() {
    $("#smt_<?php echo $this->type; ?>").itemlistShow();
    // 기본 설정값을 변경하려면 아래처럼 사용
    //$("#smt_<?php echo $this->type; ?>").itemlistShow({ interval: 3000 });
});
</script>
<!-- } 상품진열 40 끝 -->