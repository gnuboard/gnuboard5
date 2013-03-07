<?
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

<footer id="ft">
    <h1><?=$config['cf_title']?> 정보</h1>
    <?=popular('basic'); // 인기검색어 ?>
    <?=visit("basic"); // 방문자수 ?>
    <div id="ft_catch"><a href="<?=$g4['url']?>/"><img src="<?=G4_IMG_URL?>/ft_catch.jpg" alt="Sharing All Possibilities"></a></div>
    <div id="ft_copy">
        <p>Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.</p>
    </div>
</footer>

<?if(is_mobile()){?>
<a href="<?=$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING'].'&amp;':'?').'device=mobile';?>">모바일 버전으로 보기</a>
<?}?>

<?
include_once(G4_PATH."/tail.sub.php");
?>