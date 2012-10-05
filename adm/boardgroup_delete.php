<?
$sub_menu = "300200";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "d");

$gr_id = mysql_real_escape_string(trim($_POST['gr_id']));
$row = sql_fetch(" select count(*) as cnt from $g4[board_table] where gr_id = '$gr_id' ");
if ($row[cnt])
    alert("이 그룹에 속한 게시판이 존재하여 게시판 그룹을 삭제할 수 없습니다.\\n\\n이 그룹에 속한 게시판을 먼저 삭제하여 주십시오.", "./board_list.php?sfl=gr_id&stx=$gr_id");


/*
// _BOARD_DELETE_ 상수를 선언해야 board_delete.inc.php 가 정상 작동함
define("_BOARD_DELETE_", TRUE);

$sql = " select * from $g4[board_table] where gr_id = '$gr_id' ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $tmp_bo_table = $row[bo_table];

    include ('./board_delete.inc.php');
}
*/

// 그룹 삭제
sql_query(" delete from $g4[group_table] where gr_id = '$gr_id' ");

// 그룹접근 회원 삭제
sql_query(" delete from $g4[group_member_table] where gr_id = '$gr_id' ");

goto_url("boardgroup_list.php?$qstr");
?>
