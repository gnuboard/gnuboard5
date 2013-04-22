<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<? if ($is_admin == 'super') { ?><!-- <div style='float:left; text-align:center;'>RUN TIME : <?=get_microtime()-$begin_time;?><br></div> --><? } ?>

<!-- ie6,7에서 사이드뷰가 게시판 목록에서 아래 사이드뷰에 가려지는 현상 수정 -->
<!--[if lte IE 7]>
<script>
$(function() {
    var $td_name = $(".td_name");
    var count = $td_name.length;

    $td_name.each(function() {
        $(this).css("z-index", count);
        $(this).css("position", "relative");
        count = count - 1;
    });
});
</script>
<![endif]-->

<script>

</script>

</body>
</html>
<?
// HTML 마지막 처리 함수 : 반드시 넣어주시기 바랍니다.
echo html_end();
?>