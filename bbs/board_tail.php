<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 게시판 관리의 하단 내용
if ($board[bo_content_tail]) 
    echo stripslashes($board[bo_content_tail]); 

// 게시판 관리의 하단 이미지 경로
if ($board[bo_image_tail]) 
    echo "<img src='$g4[path]/data/file/$bo_table/$board[bo_image_tail]' border='0'>";

// 게시판 관리의 하단 파일 경로
if ($board[bo_include_tail]) 
    @include ($board[bo_include_tail]); 
?>