<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);

/*
상품리스트가 일정 시간마다 좌로 롤링되는 스킨
롤링되기 위해서는 상품이 2줄 이상이어야 함
*/
?>

<!-- 이전 재생 정지 다음 버튼 시작 { -->
<ul id="btn_smt_<?php echo $this->type; ?>" class="sctrl">
    <li><button type="button" class="sctrl_play">효과재생<span></span></button></li>
    <li><button type="button" class="sctrl_stop">효과정지<span></span></button></li>
</ul>
<!-- } 이전 재생 정지 다음 버튼 끝 -->

<!-- 상품유형 30 시작 { -->
<?php
for ($i=1; $row=sql_fetch_array($result); $i++) {
    if ($i == 1) {
        if ($this->css) {
            echo "<div id=\"smt_{$this->type}\" class=\"{$this->css}\">\n";
        } else {
            echo "<div id=\"smt_{$this->type}\" class=\"smt_30\">\n";
        }
        echo "<ul class=\"sct_ul sct_ul_first\">\n";
    }

    if ($i>1 && $i%$this->list_mod == 1) {
        echo "</ul>";
        echo "<ul class=\"sct_ul\">";
    }

    echo "<li class=\"sct_li\" style=\"width:{$this->img_width}px\">\n";

    if ($this->href) {
        echo "<div class=\"sct_img\"><a href=\"{$this->href}{$row['it_id']}\" class=\"sct_a\">\n";
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
        echo "<div class=\"sct_txt\"><a href=\"{$this->href}{$row['it_id']}\" class=\"sct_a\">\n";
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
            echo "<strike>".display_price($row['it_cust_price'])."</strike>\n";
        }

        if ($this->view_it_price) {
            echo display_price(get_price($row), $row['it_tel_inq'])."\n";
        }

        echo "</div>\n";

    }

    if ($this->view_sns) {
        $sns_top = $this->img_height + 10;
        $sns_url  = G5_SHOP_URL.'/item.php?it_id='.$row['it_id'];
        $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
        echo "<div class=\"sct_sns\" style=\"top:{$sns_top}px\">";
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_fb_s.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_twt_s.png');
        echo get_sns_share_link('googleplus', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_goo_s.png');
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
(function($) {
    var intervals = {};

    var methods = {
        init: function(option)
        {
            if(this.length < 1)
                return false;

            var $smt = this.find("ul.sct_ul");
            var $smt_a = $smt.find("a");
            var width = $smt.eq(0).width();
            var height = 0;
            var count = $smt.size();
            var c_idx = smt_o_idx = 0;
            var fx = null;
            var el_id = this[0].id;

            $smt.each(function() {
                var h = $(this).outerHeight(true);
                if(h > height)
                    height = h;
            });

            this.height(height);

            // 기본 설정값
            var settings = $.extend({
                interval: 6000,
                duration: 1500
            }, option);

            if(count < 2)
                return;

            set_interval();

            $smt.hover(
                function() {
                    clear_interval();
                },
                function() {
                    set_interval();
                }
            );

            $smt_a.on("focusin", function() {
                clear_interval();
            });

            $smt_a.on("focusout", function() {
                set_interval();
            });

            function left_rolling() {
                $smt.each(function(index) {
                    if($(this).is(":visible")) {
                        o_idx = index;
                        return false;
                    }
                });

                $smt.eq(o_idx).animate(
                    { left: "-="+width+"px" }, settings.duration,
                    function() {
                        $(this).css("display", "none").css("left", width+"px");
                    }
                );

                c_idx = (o_idx + 1) % count;

                $smt.eq(c_idx).css("display", "block").animate(
                    { left: "-="+width+"px" }, settings.duration,
                    function() {
                        o_idx = c_idx;
                    }
                );
            }

            function set_interval() {
                if(count > 1) {
                    clear_interval();

                    if($("#btn_"+el_id).find("button.sctrl_stop").data("stop") == true)
                        return;

                    intervals[el_id] = setInterval(left_rolling, settings.interval);

                    // control 버튼 class
                    $("#btn_"+el_id).find("button span").removeClass("sctrl_on").html("")
                        .end().find("button.sctrl_play span").addClass("sctrl_on").html("<b class=\"sound_only\">선택됨</b>");
                }
            }

            function clear_interval() {
                if(intervals[el_id]) {
                    clearInterval(intervals[el_id]);

                    // control 버튼 class
                    $("#btn_"+el_id).find("button span").removeClass("sctrl_on").html("")
                        .end().find("button.sctrl_stop span").addClass("sctrl_on").html("<b class=\"sound_only\">선택됨</b>");
                }
            }
        },
        stop: function()
        {
            var el_id = this[0].id;
            if(intervals[el_id])
                clearInterval(intervals[el_id]);
        }
    };

    $.fn.leftRolling = function(option) {
        if (methods[option])
            return methods[option].apply(this, Array.prototype.slice.call(arguments, 1));
        else
            return methods.init.apply(this, arguments);
    }
}(jQuery));

$(function() {
    $("#smt_<?php echo $this->type; ?>").leftRolling();
    // 기본 설정값을 변경하려면 아래처럼 사용
    //$("#smt_<?php echo $this->type; ?>").leftRolling({ interval: 6000, duration: 1500 });

    // 애니메이션 play
    $("#btn_smt_<?php echo $this->type; ?> button.sctrl_play").on("click", function() {
        $("#btn_smt_<?php echo $this->type; ?> button.sctrl_stop").data("stop", false);

        var id = $(this).closest(".sctrl").attr("id").replace("btn_", "");
        $("#"+id).leftRolling();
        //$("#"+id).leftRolling({ interval: 6000, duration: 1500 });
    });

    // 애니메이션 stop
    $("#btn_smt_<?php echo $this->type; ?> button.sctrl_stop").on("click", function() {
        if($(this).parent().siblings().find(".sctrl_on").size() > 0) {
            $(this).parent().siblings().find("span").removeClass("sctrl_on").html("");
            $(this).children().addClass("sctrl_on").html("<b class=\"sound_only\">선택됨</b>");
            var id = $(this).closest(".sctrl").attr("id").replace("btn_", "");
            $("#"+id).leftRolling("stop");

            $(this).data("stop", true);
        }
    });
});
</script>
<!-- } 상품진열 30 끝 -->