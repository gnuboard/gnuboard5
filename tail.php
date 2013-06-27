<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G4_IS_MOBILE) {
    include_once(G4_MOBILE_PATH.'/tail.php');
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

<hr>

<div id="ft">
    <?php echo popular('basic'); // 인기검색어  ?>
    <?php echo visit('basic'); // 방문자수  ?>
    <div id="ft_catch"><img src="<?php echo G4_IMG_URL; ?>/ft_catch.jpg" alt="gnuboard4 second edition"></div>
    <div id="ft_copy">
        <p>
            Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
            <a href="#">상단으로</a>
        </p>
    </div>
</div>

<?php if(!G4_IS_MOBILE) { ?>
<a href="<?php echo $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?'?'.str_replace("&", "&amp;", $_SERVER['QUERY_STRING']).'&amp;':'?').'device=mobile'; ?>" id="device_change">모바일 버전으로 보기</a>
<?php } ?>

<?php
include_once(G4_PATH."/tail.sub.php");
?>