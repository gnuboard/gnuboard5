<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
    </div>
</div>

<hr>

<?php echo poll('basic'); // 설문조사 ?>

<hr>

<div id="ft">
    <?php echo popular('basic'); // 인기검색어 ?>
    <?php echo visit('basic'); // 방문자수 ?>
    <div id="ft_copy">
        <div id="ft_company">
            <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=company">회사소개</a>
            <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=privacy">개인정보취급방침</a>
            <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=provision">서비스이용약관</a>
        </div>
        Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
        <a href="#">상단으로</a>
    </div>
</div>

<?php
if(G5_USE_MOBILE && G5_IS_MOBILE) {
    $seq = 0;
    $href = $_SERVER['PHP_SELF'];
    if($_SERVER['QUERY_STRING']) {
        $sep = '?';
        foreach($_GET as $key=>$val) {
            if($key == 'device')
                continue;

            $href .= $sep.$key.'='.$val;
            $sep = '&amp;';
            $seq++;
        }
    }
    if($seq)
        $href .= '&amp;device=pc';
    else
        $href .= '?device=pc';
?>
<a href="<?php echo $href; ?>" id="device_change">PC 버전으로 보기</a>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}

include_once(G5_PATH."/tail.sub.php");
?>