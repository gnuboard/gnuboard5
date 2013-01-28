<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
    </div>
</div>

<hr>

<footer id="ft">
    <h1><?=$config['cf_title']?> 정보</h1>
    <?=popular('neo'); // 인기검색어 ?>
    <?=visit("neo"); // 방문자수 ?>
    <div id="ft_catch"><img src="<?=G4_URL?>/img/ft_catch.jpg" alt="Sharing All Possibilities" id="ft_catch_img"><img src="<?=G4_URL?>/img/ft_html5_badge.jpg" alt="HTML5 Markup" id="ft_html5badge"></div>
    <div id="ft_copy">
        <p>Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.</p>
    </div>
</footer>

<?
if (is_mobile()) {
    echo "<a href=\"{$g4['url']}/?mobile\">모바일 버전으로</a>";
}
?>

<?
include_once(G4_PATH."/tail.sub.php");
?>