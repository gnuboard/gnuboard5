<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<script>
$.fn.listType = function(type)
{
    var $el = this.find("li.sct_li");
    var count = $el.size();
    if(count < 1)
        return;

    $el.each(function() {
        var st = $(this).attr("style");
        if(st) {
            $(this).data("style", st);
        }
    });

    if(type == "gallery") {
        this.removeClass("sct_40");
        $el.each(function() {
            if($(this).data("style")) {
                $(this).attr("style", $(this).data("style"));
            }
        });
        set_cookie("ck_itemlist<?php echo $ca_id; ?>_type", "gallery", 1, g4_cookie_domain);
    } else {
        this.addClass("sct_40");
        $el.each(function() {
            if($(this).data("style")) {
                $(this).attr("style", "");
            }
        });
        set_cookie("ck_itemlist<?php echo $ca_id; ?>_type", "list", 1, g4_cookie_domain);
    }
}

// 리스트 타입 쿠키가 있을 경우 바로 적용
if(itemlist_type = get_cookie("ck_itemlist<?php echo $ca_id; ?>_type")) {
    $("ul.sct").listType(itemlist_type);
}

$(function() {
    $("button.sct_lst_view").on("click", function() {
        var $el = $("ul.sct").find("li.sct_li");
        var count = $el.size();
        if(count < 1)
            return false;

        $el.each(function() {
            var st = $(this).attr("style");
            if(st) {
                $(this).data("style", st);
            }
        });

        if($(this).hasClass("sct_lst_gallery")) {
            $("ul.sct").removeClass("sct_40");
            $el.each(function() {
                if($(this).data("style")) {
                    $(this).attr("style", $(this).data("style"));
                }
            });
            set_cookie("ck_itemlist<?php echo $ca_id; ?>_type", "gallery", 1, g4_cookie_domain);
        } else {
            $("ul.sct").addClass("sct_40");
            $el.each(function() {
                if($(this).data("style")) {
                    $(this).attr("style", "");
                }
            });
            set_cookie("ck_itemlist<?php echo $ca_id; ?>_type", "list", 1, g4_cookie_domain);
        }
    });
});
</script>