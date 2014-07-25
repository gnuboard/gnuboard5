<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    echo stripslashes($qaconfig['qa_mobile_content_tail']);
    // 모바일의 경우 설정을 따르지 않는다.
    include_once('./_tail.php');
} else {
    echo stripslashes($qaconfig['qa_mobile_content_tail']);
    if($qaconfig['qa_include_tail'])
        @include ($qaconfig['qa_include_tail']);
    else
        include ('./_tail.php');
}
?>