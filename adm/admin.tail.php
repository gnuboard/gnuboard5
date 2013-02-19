<?
if (!defined('_GNUBOARD_')) exit;
?>

        <noscript>
            <p>
                귀하께서 사용하시는 브라우저는 현재 <strong>자바스크립트를 사용하지 않음</strong>으로 설정되어 있습니다.<br>
                <strong>자바스크립트를 사용하지 않음</strong>으로 설정하신 경우는 수정이나 삭제시 별도의 경고창이 나오지 않으므로 이점 주의하시기 바랍니다.
            </p>
        </noscript>

    </div>
</div>

<footer id="ft">
    <p>Copyright &copy; 소유하신 도메인. All rights reserved.</p>
</footer>

<!-- <p>실행시간 : <?=get_microtime() - $begin_time;?> -->

<script src="<?=G4_ADMIN_URL?>/admin.js"></script>
<script>
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
    var $gnb = $('.gnb_1depth > a');
    $gnb.mouseover(function() {
        if(mouse_event) {
            $('.gnb_1depth').removeClass('gnb_1depth_over gnb_1depth_on');
            $(this).parent().addClass('gnb_1depth_over gnb_1depth_on');
            hide_menu = false;
        }
    });

    $gnb.mouseout(function() {
        hide_menu = true;
    });

    $('.gnb_1depth li').mouseover(function() {
        hide_menu = false;
    });

    $('.gnb_1depth li').mouseout(function() {
        hide_menu = true;
    });

    $gnb.focusin(function() {
        $('.gnb_1depth').removeClass('gnb_1depth_over gnb_1depth_on');
        $(this).parent().addClass('gnb_1depth_over gnb_1depth_on');
        hide_menu = false;
    });

    $gnb.focusout(function() {
        hide_menu = true;
    });

    $('.gnb_1depth ul a').focusin(function() {
        $('.gnb_1depth').removeClass('gnb_1depth_over gnb_1depth_on');
        var $gnb_li = $(this).closest('.gnb_1depth').addClass('gnb_1depth_over gnb_1depth_on');
        hide_menu = false;
    });

    $('.gnb_1depth ul a').focusout(function() {
        hide_menu = true;
    });

    $(document).click(function() {
        if(hide_menu) {
            $('.gnb_1depth').removeClass('gnb_1depth_over gnb_1depth_on');
        }
    });

    $(document).focusin(function() {
        if(hide_menu) {
            $('.gnb_1depth').removeClass('gnb_1depth_over gnb_1depth_on');
        }
    });
});

</script>

<?
include_once(G4_PATH.'/tail.sub.php');
?>