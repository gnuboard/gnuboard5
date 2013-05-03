<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
    </div>
</div>

<hr>

<nav id="gnb">
    <script>$('#gnb').addClass('gnb_js');</script>
    <h2>홈페이지 메인메뉴</h2>
    <ul>
        <?php
        $sql = " select * from {$g4['group_table']} where gr_show_menu = 1 and gr_device <> 'pc' order by gr_order ";
        $result = sql_query($sql);
        for ($gi=0; $row=sql_fetch_array($result); $gi++) { // gi 는 group index
        ?>
        <li><a href="<?php echo G4_BBS_URL ?>/group.php?gr_id=<?php echo $row['gr_id'] ?>"><?php echo $row['gr_subject'] ?></a></li>
        <?php } ?>
        <?php if ($gi == 0) { ?><li class="gnb_empty">생성된 메뉴가 없습니다.</a><?php } ?>
    </ul>
</nav>

<hr>

<?php echo poll('basic'); // 설문조사 ?>

<hr>

<footer id="ft">
    <h1><?php echo $config['cf_title'] ?> 정보</h1>
    <?php echo popular('basic'); // 인기검색어 ?>
    <?php echo visit('basic'); // 방문자수 ?>
    <div id="ft_catch"><a href="<?php echo G4_URL; ?>/"><img src="<?php echo G4_IMG_URL; ?>/ft_catch.jpg" alt="Sharing All Possibilities"></a></div>
    <div id="ft_copy">
        <p>
            Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
            <a href="#">상단으로</a>
        </p>
    </div>
</footer>

<a href="<?php echo $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?'?'.str_replace("&", "&amp;", $_SERVER['QUERY_STRING']).'&amp;':'?').'device=pc'; ?>" id="device_change">PC 버전으로 보기</a>

<?php
include_once(G4_PATH."/tail.sub.php");
?>