<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 게시판 관리의 상단 내용
if (G5_IS_MOBILE) {
    // 모바일의 경우 설정을 따르지 않는다.
    include_once('./_head.php');
    echo stripslashes($board['bo_mobile_content_head']);
} else {
    @include ($board['bo_include_head']);
    echo stripslashes($board['bo_content_head']);
}
?>