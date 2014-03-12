<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$qa_skin_path = (G5_IS_MOBILE ? G5_MOBILE_PATH : G5_PATH).'/'.G5_SKIN_DIR.'/qa/'.$qaconfig['qa_skin'];
$qa_skin_url = (G5_IS_MOBILE ? G5_MOBILE_URL : G5_URL).'/'.G5_SKIN_DIR.'/qa/'.$qaconfig['qa_skin'];

if (G5_IS_MOBILE) {
    // 모바일의 경우 설정을 따르지 않는다.
    include_once('./_head.php');
    echo stripslashes($qaconfig['qa_mobile_content_head']);
} else {
    if($qaconfig['qa_include_head'])
        @include ($qaconfig['qa_include_head']);
    else
        include ('./_head.php');
    echo stripslashes($qaconfig['qa_content_head']);
}
?>