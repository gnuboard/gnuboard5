<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
    </div>
</div>

<hr>

<nav id="gnb">
    <script>$('#gnb').addClass('gnb_js');</script>
    <h2>홈페이지 메인메뉴</h2>
    <ul>
        <?
        $sql = " select * from {$g4['group_table']} where gr_show_menu = 1 order by gr_order ";
        $result = sql_query($sql);
        for ($gi=0; $row=sql_fetch_array($result); $gi++) { // gi 는 group index 
        ?>
        <li><a href="<?=G4_BBS_URL?>/group.php?gr_id=<?=$row['gr_id']?>"><?=$row['gr_subject']?></a></li>
        <?}?>
    </ul>
</nav>

<hr>

<footer id="ft">
    <h1><?=$config['cf_title']?> 정보</h1>
    <?=popular('basic'); // 인기검색어 ?>
    <?=visit("basic"); // 방문자수 ?>
    <div id="ft_catch"><a href="<?=$g4['url']?>/"><img src="<?=G4_IMG_URL?>/ft_catch.jpg" alt="Sharing All Possibilities"></a></div>
    <div id="ft_copy">
        <p>Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.</p>
    </div>
</footer>

<a href="<?=$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING'].'&amp;':'?').'device=pc';?>">PC 버전으로 보기</a>

<?
include_once(G4_PATH."/tail.sub.php");
?>