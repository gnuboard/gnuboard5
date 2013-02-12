<?
include_once("./_common.php");

$ca_name   = stripslashes($_POST['ca_name']);
$ca_number = (int)$_POST['ca_number'];
$start = 2 * $ca_number - 1;
$end = 2 * $ca_number;

$sql = " SELECT MAX(SUBSTRING(ca_id,$start,$end)) AS ca_id FROM {$g4['shop_category_table']} ";
$row = sql_fetch($sql);
if ($row['ca_id']) {
    $ca_id = base_convert($row['ca_id'], 36, 10);
    $ca_id += 36;
    $ca_id = base_convert($ca_id, 10, 36);
} else  {
    $ca_id = "10";
}

$sql = " INSERT INTO {$g4['shop_category_table']} SET ca_id = '$ca_id', ca_name = '$ca_name' ";
sql_query($sql);

die("{\"ca_id\":\"$ca_id\", \"ca_name\":\"$ca_name\"}");

exit;
if ($is_guest) {
    die("{\"error\":\"회원님만 신고 가능합니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.\"}");
}

$reason = get_text(strip_tags($_POST['reason']));

$sql = " select sg_reason from `$g4[singo_table]` where `bo_table` = '$bo_table' and `wr_id` = '$wr_id' and (`mb_id` = '$member[mb_id]' or `sg_ip` = '{$_SERVER['REMOTE_ADDR']}') ";
$row = sql_fetch($sql);
if ($row) {
    //die("{\"error\":\"이미 (".get_text(strip_tags($row[sg_reason])).") 사유로 '신고' 하셨습니다.\"}");
    die("{\"error\":\"이미 이 게시물을 '신고' 하셨습니다.\"}");
}

if ($write['mb_id'] && $write['mb_id'] == $member['mb_id']) {
    die("{\"error\":\"자신의 게시물은 '신고' 할 수 없습니다.\"}");
}

$gap = gap_days($write['wr_datetime']);
if ($gap > 7) {
    die("{\"error\":\"일주일 이내에 등록된 게시물에만 '신고' 할 수 있습니다.\"}");
}

// 글쓴이 회원정보 (신고가 되는 상대 회원정보)
$target = get_member($write['mb_id'], 'mb_id,mb_level,mb_jisu_rank');

if ($target['mb_level'] > $member['mb_level']) {
    die("{\"error\":\"자신보다 권한이 높은 회원의 게시물은 신고할 수 없습니다.\"}");
}

if ($target['mb_jisu_rank'] <= $member['mb_jisu_rank']) {
    die("{\"error\":\"자신보다 활동지수 순위가 높거나 같은 회원의 게시물은 신고할 수 없습니다.\"}");
}

/*
// 회원가입후 몇일째인지? + 1 은 당일을 포함한다는 뜻
$sql = " select (TO_DAYS('".G4_TIME_YMDHIS."') - TO_DAYS('$target[mb_datetime]') + 1) as days ";
$row = sql_fetch($sql);
$mb_reg_after = $row[days];
if ($mb_reg_after >= 365) {
    die("{\"error\":\"회원가입후 1년이 지난 회원님의 글은 신고할 수 없습니다.\"}");
}
*/

// '싫어요'도 하나 더 드세요.
$sql = " insert into $g4[board_good_table] ( bo_table, wr_id, mb_id, bg_flag, bg_datetime, bg_ip, tar_mb_id ) values ( '$bo_table', '$wr_id', '$member[mb_id]', 'nogood', '".G4_TIME_YMDHIS."', '$_SERVER[REMOTE_ADDR]', '$write[mb_id]' ) ";
sql_query($sql);

// 신고 테이블에 레코드를 추가한다.
$sql = " insert into `$g4[singo_table]` set bo_table = '$bo_table', wr_id = '$wr_id', wr_parent = '{$write['wr_parent']}', mb_id = '{$target['mb_id']}', sg_mb_id = '{$member['mb_id']}', sg_reason = '$reason', sg_datetime = '".G4_TIME_YMDHIS."', sg_ip = '$_SERVER[REMOTE_ADDR]' ";
sql_query($sql);

// 신고가 되면 일정 시간이 지난후 부터 글쓰기가 가능함
$time = date("Y-m-d H:i:s", $g4[server_time] + (3600 * 3));
// 상대 회원의 글쓰기등을 금지하기 위하여 상대 회원의 신고수를 누적하고 신고 시간을 업데이트 한다.
$sql = " UPDATE `{$g4['member_table']}` SET `mb_singo_count` = `mb_singo_count` + 1, `mb_singo_time` = '$time' WHERE `mb_id` = '{$target['mb_id']}' ";
sql_query($sql);

// 게시글에 신고수를 누적한다.
$sql = " update `$write_table` set `wr_singo` = `wr_singo` + 1 where `wr_id` = '$wr_id' ";
sql_query($sql);

// 게시글의 신고수를 얻는다.
$sql = " select `wr_singo` as cnt from `$write_table` where `wr_id` = '$wr_id' ";
$row = sql_fetch($sql);

$message = "신고 하셨습니다.";

die("{\"error\":\"\", \"message\":\"$message\", \"count\":\"$row[cnt]\"}");
?>