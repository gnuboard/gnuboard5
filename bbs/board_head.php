<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

// 게시판 관리의 상단 파일 경로
if ($board['bo_include_head']) {
    @include ($board['bo_include_head']); 
}

// 게시판 관리의 상단 이미지 경로
if ($board['bo_image_head']) {
    echo '<img src="'.G4_DATA_PATH.'/file/'.$bo_table.'/'.$board['bo_image_head'].'">';
}

// 게시판 관리의 상단 내용
if ($board['bo_content_head']) {
    echo stripslashes($board['bo_content_head']); 
}
?>