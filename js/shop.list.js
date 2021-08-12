$.fn.listType = function(type)
{
    var $el = this.find("li.sct_li");
    var count = $el.length;
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
    $("button.sct_lst_view").removeClass("sct_lst_on").find("span").removeClass("sct_lst_on").find("b").remove();

    if(type == "gallery") {
        this.removeClass("sct sct_40");
        if(this.data("class")) {
            this.attr("class", this.data("class"));
        }

        $el.each(function() {
            $(this).removeAttr("style");

            if($(this).data("style")) {
                $(this).attr("style", $(this).data("style"));
            }
        });

        $("button.sct_lst_gallery").addClass("sct_lst_on").find("span").addClass("sct_lst_on").html("<b class=\"sound_only\"> 선택됨</b>");
    } else {
        if(this.data("class")) {
            this.removeAttr("class");
        }
        this.addClass("sct sct_40");

        var list_top_pad = 20;
        var list_right_pad = 20;
        var list_bottom_pad = 20;
        var list_real_width = this.outerWidth();
        var list_left_pad, list_width, list_height;
        var img_width, img_height;

        $el.each(function() {
            $(this).removeAttr("style");

            img_width = $(this).find(".sct_img img").width();
            img_height = $(this).find(".sct_img img").height();

            list_left_pad = img_width + 20;
            list_width = list_real_width - list_right_pad - list_left_pad;
            list_height = img_height +2;
            
            if( $(this).attr("data-css") !== "nocss" ){
                $(this).css({
                    paddingTop : list_top_pad+"px",
                    paddingRight: list_right_pad+"px",
                    paddingBottom: list_bottom_pad+"px",
                    paddingLeft: list_left_pad+"px",
                    //width: list_width+"px",
                    height: list_height+"px"
                });
            }
        });

		$("button.sct_lst_list").addClass("sct_lst_on").find("span").addClass("sct_lst_on").html("<b class=\"sound_only\"> 선택됨</b>");
    }

    set_cookie("ck_itemlist"+itemlist_ca_id+"_type", type, 1, g5_cookie_domain);
}

$(function() {
    // 리스트 타입 쿠키가 있을 경우 바로 적용
    if(itemlist_type = get_cookie("ck_itemlist"+itemlist_ca_id+"_type")) {
        $("ul.sct").listType(itemlist_type);
    }

    $("button.sct_lst_view").on("click", function() {
        if($(this).hasClass("sct_lst_gallery")) {
            $("ul.sct").listType("gallery");
        } else {
            $("ul.sct").listType("list");
        }
    });
});