<?
$sub_menu = "300100";
include_once("./_common.php");

check_demo();

if ($is_admin != "super")
    alert("게시판 삭제는 최고관리자만 가능합니다.");

auth_check($auth[$sub_menu], "d");

check_token();

// _BOARD_DELETE_ 상수를 선언해야 board_delete.inc.php 가 정상 작동함
define("_BOARD_DELETE_", TRUE);

for ($i=0; $i<count($chk); $i++) 
{
    // 실제 번호를 넘김
    $k = $chk[$i];

    // include 전에 $bo_table 값을 반드시 넘겨야 함
    $tmp_bo_table = mysql_real_escape_string(trim($_POST['board_table'][$k]));
    include ("./board_delete.inc.php");
}

goto_url("./board_list.php?$qstr");
?>
