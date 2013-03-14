<?
// 상대 경로
$g4_path = "..";
include_once("$g4_path/common.php");
include_once("$g4[path]/lib/latest.lib.php");

$g4[title] = $group[gr_subject];
include_once("./_head.php");
?>

<!-- 메인화면 최신글 시작 -->
<table width="100%" cellpadding=0 cellspacing=0>
<tr>
    <td valign=top>
    <?
    //  최신글
    $sql = " select bo_table, bo_subject from $g4[board_table] 
              where gr_id = '$gr_id' 
                and bo_list_level <= '$member[mb_level]'
              order by bo_table ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
        // 스킨은 입력하지 않을 경우 관리자 > 환경설정의 최신글 스킨경로를 기본 스킨으로 합니다.

        // 사용방법
        // latest(스킨, 게시판아이디, 출력라인, 글자수);
        echo latest("basic", $row[bo_table], 5, 70);
        echo "<p>";
    }
    ?>
    </td>
</tr>
</table>
<!-- 메인화면 최신글 끝 -->

<?
include_once("./_tail.php");
?>
