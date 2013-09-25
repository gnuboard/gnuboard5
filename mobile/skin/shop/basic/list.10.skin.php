<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<!-- 상품진열 10 시작 { -->
<?php
for ($i=0; $row=sql_fetch_array($result); $i++) {
    if ($i == 0) {
        if ($this->css) {
            echo "<ul class=\"{$this->css}\">\n";
        } else {
            echo "<ul class=\"sct sct_10\">\n";
        }
    }

    echo "<li class=\"sct_li\">\n";

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
        $it_name = $row['it_name'];
        if ($this->is_mobile && $row['it_mobile_name'])  {
            $it_name = $row['it_mobile_name'];
        }
        echo "<b>".stripslashes($it_name)."</b>\n";
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
        echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_fb.png');
        echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_twt.png');
        echo get_sns_share_link('googleplus', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_goo.png');
        echo "</div>\n";
    }

    if ($this->href) {
        echo "</a>\n";
    }

    echo "</li>\n";
}

if ($i > 0) echo "</ul>\n";

if($i == 0) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>
<!-- } 상품진열 10 끝 -->

<script>
$(function() {
    set_list_margin();
});

$(window).resize(function() {
    set_list_margin();
});

function set_list_margin()
{
    var li_margin = 0;
    if($("li.sct_li:first").data("margin-right") == undefined) {
        li_margin = parseInt($("li.sct_li:first").css("margin-right"));
        $("li.sct_li:first").data("margin-right", li_margin);
    }
    else
        li_margin = $("li.sct_li:first").data("margin-right");

    $("li.sct_li").css("margin-left", 0).css("margin-right", li_margin);

    var ul_width = parseInt($("ul.sct").width());
    var li_width = parseInt($("li.sct_li:first").outerWidth(true));
    var li_count = parseInt((ul_width + li_margin) / li_width);

    if(li_count == 0)
        return;

    var space = parseInt(ul_width % li_width);

    if((space + li_margin) < li_width) {
        var new_margin = parseInt((space + li_margin) / (li_count * 2));

        if(new_margin > li_margin)
            $("li.sct_li").css("margin-left", new_margin+"px").css("margin-right", new_margin);
    }

    $("li.sct_li:nth-child("+li_count+"n)").css("margin-right", 0);
}
</script>