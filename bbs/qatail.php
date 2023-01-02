<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    echo run_replace('qa_mobile_content_tail', conv_content($qaconfig['qa_mobile_content_tail'], 1), $qaconfig);
    // 모바일의 경우 설정을 따르지 않는다.
    include_once('./_tail.php');
} else {
    echo run_replace('qa_content_tail', conv_content($qaconfig['qa_content_tail'], 1), $qaconfig);
    if($qaconfig['qa_include_tail'] && is_include_path_check($qaconfig['qa_include_tail']))
        @include ($qaconfig['qa_include_tail']);
    else
        include ('./_tail.php');
}