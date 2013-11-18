<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/tail.php');
    return;
}

// 하단 파일 경로 지정 : 이 코드는 가능한 삭제하지 마십시오.
if ($config['cf_include_tail']) {
    if (!@include_once($config['cf_include_tail'])) {
        die('기본환경 설정에서 하단 파일 경로가 잘못 설정되어 있습니다.');
    }
    return; // 이 코드의 아래는 실행을 하지 않습니다.
}
?>
    </div>
</div>

<!-- } 콘텐츠 끝 -->

<hr>

<!-- 하단 시작 { -->
<div id="ft">
    <?php echo popular('basic'); // 인기검색어  ?>
    <?php echo visit('basic'); // 방문자수  ?>
    <div id="ft_catch"><img src="<?php echo G5_IMG_URL; ?>/ft_catch.jpg" alt="<?php echo G5_VERSION ?>"></div>
    <div id="ft_copy">
        <p>
            Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
            <a href="#">상단으로</a>
        </p>
    </div>
</div>

<?php
if(G5_USE_MOBILE && !G5_IS_MOBILE) {
    $seq = 0;
    $href = $_SERVER['PHP_SELF'];
    if($_SERVER['QUERY_STRING']) {
        $sep = '?';
        foreach($_GET as $key=>$val) {
            if($key == 'device')
                continue;

            $href .= $sep.$key.'='.strip_tags($val);
            $sep = '&amp;';
            $seq++;
        }
    }
    if($seq)
        $href .= '&amp;device=mobile';
    else
        $href .= '?device=mobile';
?>
<a href="<?php echo $href; ?>" id="device_change">모바일 버전으로 보기</a>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<!-- } 하단 끝 -->

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<?php
include_once(G5_PATH."/tail.sub.php");
?>