$(function(){
    var hide_menu = false;
    var mouse_event = false;
    var oldX = oldY = 0;

    $(document).mousemove(function(e) {
        if(oldX == 0) {
            oldX = e.pageX;
            oldY = e.pageY;
        }

        if(oldX != e.pageX || oldY != e.pageY) {
            mouse_event = true;
        }
    });

    // 주메뉴
    var $gnb = $(".gnb_1depth > a");
    $gnb.mouseover(function() {
        if(mouse_event) {
            $(".gnb_1depth").removeClass("gnb_1depth_over gnb_1depth_over2 gnb_1depth_on");
            $(this).parent().addClass("gnb_1depth_over gnb_1depth_on");
            menu_rearrange($(this).parent());
            hide_menu = false;
        }
    });

    $gnb.mouseout(function() {
        hide_menu = true;
    });

    $(".gnb_1depth li").mouseover(function() {
        hide_menu = false;
    });

    $(".gnb_1depth li").mouseout(function() {
        hide_menu = true;
    });

    $gnb.focusin(function() {
        $(".gnb_1depth").removeClass("gnb_1depth_over gnb_1depth_over2 gnb_1depth_on");
        $(this).parent().addClass("gnb_1depth_over gnb_1depth_on");
        menu_rearrange($(this).parent());
        hide_menu = false;
    });

    $gnb.focusout(function() {
        hide_menu = true;
    });

    $(".gnb_1depth ul a").focusin(function() {
        $(".gnb_1depth").removeClass("gnb_1depth_over gnb_1depth_over2 gnb_1depth_on");
        var $gnb_li = $(this).closest(".gnb_1depth").addClass("gnb_1depth_over gnb_1depth_on");
        menu_rearrange($(this).closest(".gnb_1depth"));
        hide_menu = false;
    });

    $(".gnb_1depth ul a").focusout(function() {
        hide_menu = true;
    });

    $('#gnb_ul>li').bind('mouseleave',function(){
        submenu_hide();
    });

    $(document).bind('click focusin',function(){
        if(hide_menu) {
            submenu_hide();
        }
    });

    function submenu_hide() {
        $(".gnb_1depth").removeClass("gnb_1depth_over gnb_1depth_over2 gnb_1depth_on");
    }

    // 텍스트 리사이즈 카운트 쿠키있으면 실행
    var resize_act;
    var text_resize_count = parseInt(get_cookie("ck_font_resize_count"));
    if(!isNaN(text_resize_count)) {
        if(text_resize_count > 0)
            resize_act = "increase";
        else if(text_resize_count < 0)
            resize_act = "decrease";

        if(Math.abs(text_resize_count) > 0)
            font_resize2("container", resize_act, Math.abs(text_resize_count));
    }
});

function menu_rearrange(el)
{
    var width = $("#gnb_ul").width();
    var left = w1 = w2 = 0;
    var idx = $(".gnb_1depth").index(el);

    for(i=0; i<=idx; i++) {
        w1 = $(".gnb_1depth:eq("+i+")").outerWidth();
        w2 = $(".gnb_2depth > a:eq("+i+")").outerWidth(true);

        if((left + w2) > width) {
            el.removeClass("gnb_1depth_over").addClass("gnb_1depth_over2");
        }

        left += w1;
    }
}