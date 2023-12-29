<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

// 그누보드5.4.5.5 버전과 영카트5.4.5.5.1 버전이 통합됨에 따라 그누보드 버전만 표시
// $print_version = defined('G5_YOUNGCART_VER') ? 'YoungCart Version '.G5_YOUNGCART_VER : 'Version '.G5_GNUBOARD_VER;
$print_version = ($is_admin == 'super') ? 'Version ' . G5_GNUBOARD_VER : '';
?>

<noscript>
    <p>
        귀하께서 사용하시는 브라우저는 현재 <strong>자바스크립트를 사용하지 않음</strong>으로 설정되어 있습니다.<br>
        <strong>자바스크립트를 사용하지 않음</strong>으로 설정하신 경우는 수정이나 삭제시 별도의 경고창이 나오지 않으므로 이점 주의하시기 바랍니다.
    </p>
</noscript>

</div>
<footer id="ft">
    <p>
        Copyright &copy; <?php echo $_SERVER['HTTP_HOST']; ?>. All rights reserved. <?php echo $print_version; ?><br>
        <button type="button" class="scroll_top"><span class="top_img"></span><span class="top_txt">TOP</span></button>
        <label id="darkmode_btn">
        <input type="checkbox" id="dark-mode-toggle" <?php echo is_darkmode() ? 'checked' : '';?>>
        <svg xmlns="http://www.w3.org/2000/svg" class="visible dark" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
        <svg xmlns="http://www.w3.org/2000/svg" class="visible bright" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
        </label>
    </p>
</footer>
</div>

</div>

<script>
$(function() {
    $(".scroll_top").click(function() {
        $("body,html").animate({
            scrollTop: 0
        }, 400);
    });
    $('#darkmode_btn').on("click", function(e) {
        if ($("#dark-mode-toggle").is(':checked')) {
            set_darkmode('dark');
        } else {
            set_darkmode('light');
        }
    });
});
</script>

<!-- <p>실행시간 : <?php echo get_microtime() - $begin_time; ?> -->

<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_JS_URL ?>/jquery.anchorScroll.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script>
    $(function() {

        var admin_head_height = $("#hd_top").height() + $("#container_title").height() + 5;

        $("a[href^='#']").anchorScroll({
            scrollSpeed: 0, // scroll speed
            offsetTop: admin_head_height, // offset for fixed top bars (defaults to 0)
            onScroll: function() {
                // callback on scroll start
            },
            scrollEnd: function() {
                // callback on scroll end
            }
        });

        var hide_menu = false;
        var mouse_event = false;
        var oldX = oldY = 0;

        $(document).mousemove(function(e) {
            if (oldX == 0) {
                oldX = e.pageX;
                oldY = e.pageY;
            }

            if (oldX != e.pageX || oldY != e.pageY) {
                mouse_event = true;
            }
        });

        // 주메뉴
        var $gnb = $(".gnb_1dli > a");
        $gnb.mouseover(function() {
            if (mouse_event) {
                $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
                $(this).parent().addClass("gnb_1dli_over gnb_1dli_on");
                menu_rearrange($(this).parent());
                hide_menu = false;
            }
        });

        $gnb.mouseout(function() {
            hide_menu = true;
        });

        $(".gnb_2dli").mouseover(function() {
            hide_menu = false;
        });

        $(".gnb_2dli").mouseout(function() {
            hide_menu = true;
        });

        $gnb.focusin(function() {
            $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
            $(this).parent().addClass("gnb_1dli_over gnb_1dli_on");
            menu_rearrange($(this).parent());
            hide_menu = false;
        });

        $gnb.focusout(function() {
            hide_menu = true;
        });

        $(".gnb_2da").focusin(function() {
            $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
            var $gnb_li = $(this).closest(".gnb_1dli").addClass("gnb_1dli_over gnb_1dli_on");
            menu_rearrange($(this).closest(".gnb_1dli"));
            hide_menu = false;
        });

        $(".gnb_2da").focusout(function() {
            hide_menu = true;
        });

        $('#gnb_1dul>li').bind('mouseleave', function() {
            submenu_hide();
        });

        $(document).bind('click focusin', function() {
            if (hide_menu) {
                submenu_hide();
            }
        });

        // 폰트 리사이즈 쿠키있으면 실행
        var font_resize_act = get_cookie("ck_font_resize_act");
        if (font_resize_act != "") {
            font_resize("container", font_resize_act);
        }
    });

    function submenu_hide() {
        $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
    }

    function menu_rearrange(el) {
        var width = $("#gnb_1dul").width();
        var left = w1 = w2 = 0;
        var idx = $(".gnb_1dli").index(el);

        for (i = 0; i <= idx; i++) {
            w1 = $(".gnb_1dli:eq(" + i + ")").outerWidth();
            w2 = $(".gnb_2dli > a:eq(" + i + ")").outerWidth(true);

            if ((left + w2) > width) {
                el.removeClass("gnb_1dli_over").addClass("gnb_1dli_over2");
            }

            left += w1;
        }
    }
</script>

<?php
require_once G5_PATH . '/tail.sub.php';
