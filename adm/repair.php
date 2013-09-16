<?
$sub_menu = "100700";
include_once("./_common.php");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", $g5[path]);

$g5[title] = "테이블 복구 및 최적화";
include_once("./admin.head.php");
echo "'완료' 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.<br>";
echo "<span id='ct'></span>";
include_once("./admin.tail.php");
flush();


// 설정일이 지난 접속자로그 삭제
$tmp_before_date = date("Y-m-d", $g5[server_time] - ($config[cf_visit_del] * 86400));
$sql = " delete from $g5[visit_table] where vi_date < '$tmp_before_date' ";
sql_query($sql);
sql_query(" OPTIMIZE TABLE `$g5[visit_table]`, `$g5[visit_sum_table]` ");

// 설정일이 지난 인기검색어 삭제
$tmp_before_date = date("Y-m-d", $g5[server_time] - ($config[cf_popular_del] * 86400));
$sql = " delete from $g5[popular_table] where pp_date < '$tmp_before_date' ";
sql_query($sql);
sql_query(" OPTIMIZE TABLE `$g5[popular_table]` ");

// 설정일이 지난 최근게시물 삭제
$sql = " delete from $g5[board_new_table] where (TO_DAYS('$g5[time_ymdhis]') - TO_DAYS(bn_datetime)) > '$config[cf_new_del]' ";
sql_query($sql);
sql_query(" OPTIMIZE TABLE `$g5[board_new_table]` ");

// 설정일이 지난 쪽지 삭제
$sql = " delete from $g5[memo_table] where (TO_DAYS('$g5[time_ymdhis]') - TO_DAYS(me_send_datetime)) > '$config[cf_memo_del]' ";
sql_query($sql);
sql_query(" OPTIMIZE TABLE `$g5[memo_table]` ");

// 탈퇴회원 자동 삭제
$sql = " select mb_id from $g5[member_table] where (TO_DAYS('$g5[time_ymdhis]') - TO_DAYS(mb_leave_date)) > '$config[cf_leave_day]' ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) 
{
    // 회원자료 삭제
    member_delete($row[mb_id]);
}


$sql = "SHOW TABLE STATUS FROM ".$mysql_db;
$result = sql_query($sql);
while($row = sql_fetch_array($result))
{
    $str = '';

    $tbl = $row['Name'];

    $sql1 = " SELECT COUNT(*) FROM `$tbl` ";
    $result1 = @mysql_query($sql1);
    if (!$result1)
    {
        // 테이블 복구
        $sql2 = " REPAIR TABLE `$tbl` ";
        sql_query($sql2);
        $str .= $sql2 . "<br/>";
    }

    if($row['Data_free'] == 0) continue;

    // 테이블 최적화
    $sql3 = " OPTIMIZE TABLE `$tbl` ";
    sql_query($sql3);
    $str .= $sql3 . "<br/>";

    echo "<script>document.getElementById('ct').innerHTML += '$str';</script>\n";

    flush();
    /*
    for($i = 0; $i < 40 - strlen($tbl); $i ++) echo " ";
        echo "\t";
    for($i = 0; $i < 9 - strlen($row['Data_free']); $i ++) echo " ";
        echo $row['Data_free']." OPTIMIZED\n";
    */
}
echo "<script>document.getElementById('ct').innerHTML += '<br><br>테이블 복구 및 최적화 완료.<br><br>프로그램의 실행을 끝마치셔도 좋습니다.';</script>\n";
?>