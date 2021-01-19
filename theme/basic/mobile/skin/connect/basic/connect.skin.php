<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// 회원수는 $row['mb_cnt'];

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$connect_skin_url.'/style.css">', 0);
?>
<?php echo $row['total_cnt'];