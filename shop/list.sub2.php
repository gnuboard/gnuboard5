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

    $("button.sct_lst_view span").removeClass("sct_lst_on").html("");

    if(type == "gallery") {
        this.removeClass("sct_40");
        $el.each(function() {
            if($(this).data("style")) {
                $(this).attr("style", $(this).data("style"));
            }
        });

        $("button.sct_lst_gallery span").addClass("sct_lst_on").html("<b class=\"sound_only\">활성</b>");
    } else {
        this.addClass("sct_40");
        $el.each(function() {
            if($(this).data("style")) {
                $(this).attr("style", "");
            }
        });

        $("button.sct_lst_list span").addClass("sct_lst_on").html("<b class=\"sound_only\">활성</b>");
    }

    set_cookie("ck_itemlist<?php echo $ca_id; ?>_type", type, 1, g4_cookie_domain);
}

// 리스트 타입 쿠키가 있을 경우 바로 적용
if(itemlist_type = get_cookie("ck_itemlist<?php echo $ca_id; ?>_type")) {
    $("ul.sct").listType(itemlist_type);
}

$(function() {
    $("button.sct_lst_view").on("click", function() {
        if($(this).hasClass("sct_lst_gallery")) {
            $("ul.sct").listType("gallery");
        } else {
            $("ul.sct").listType("list");
        }
    });
});
</script>