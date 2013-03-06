<?
define('_INDEX_', true);
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MOBILE_PATH.'/index.php');
    return;
}

// 초기화면 파일 경로 지정 : 이 코드는 가능한 삭제하지 마십시오.
if ($config['cf_include_index']) {
    if (!@include_once($config['cf_include_index'])) {
        die('기본환경 설정에서 초기화면 파일 경로가 잘못 설정되어 있습니다.');
    }
    return; // 이 코드의 아래는 실행을 하지 않습니다.
}

include_once('./_head.php');
?>

<!-- 메인화면 최신글 시작 -->
<?
//  최신글
$sql = " select bo_table from {$g4['board_table']} order by gr_id, bo_table ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $lt_style = "";
    if ($i%2==1) $lt_style = "margin-left:20px";
    else $lt_style = "";
?>
    <div style="float:left;<?=$lt_style?>">
        <?
        // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
        // 스킨은 입력하지 않을 경우 관리자 > 환경설정의 최신글 스킨경로를 기본 스킨으로 합니다.

        // 사용방법
        // latest(스킨, 게시판아이디, 출력라인, 글자수);
        echo latest("basic", $row['bo_table'], 5, 25);
        ?>
    </div>
<?
}
?>
<!-- 메인화면 최신글 끝 -->

<?
include_once('./_tail.php');
?>
