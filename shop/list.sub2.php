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

    // class 있다면 저장
    var cl = this.attr("class");
    if(cl && !this.data("class")) {
        this.data("class", cl);
    }

    // 각 element의 inline 스타일 저장
    $el.each(function() {
        var st = $(this).attr("style");
        if(st && !$(this).data("style")) {
            $(this).data("style", st);
        }
    });

    // 버튼의 class on class 제거
    $("button.sct_lst_view span").removeClass("sct_lst_on").html("");

    if(type == "gallery") {
        this.removeClass("sct sct_40");
        if(this.data("class")) {
            this.attr("class", this.data("class"));
        }

        $el.each(function() {
            if($(this).data("style")) {
                $(this).attr("style", $(this).data("style"));
            }
        });

        $("button.sct_lst_gallery span").addClass("sct_lst_on").html("<b class=\"sound_only\"> 선택됨</b>");
    } else {
        if(this.data("class")) {
            this.removeAttr("class");
        }
        this.addClass("sct sct_40");

        $el.each(function() {
            if($(this).data("style")) {
                $(this).removeAttr("style");
            }
        });

        $("button.sct_lst_list span").addClass("sct_lst_on").html("<b class=\"sound_only\"> 선택됨</b>");
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