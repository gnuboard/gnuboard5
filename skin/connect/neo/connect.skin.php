<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<section id="current_connect">
    <h2>현재접속자</h2>
    <div><a href="<?=$g4['bbs_path']?>/current_connect.php"><?=$row['total_cnt']?>명 중 회원 <?=$row['mb_cnt']?>명</a></div>
</section>
