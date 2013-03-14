<?
include_once("./_common.php");

if (!$member[mb_id]) 
    alert("회원만 이용하실 수 있습니다.");

$me_id = (int)$me_id;

if ($kind == "recv") 
{
    $t = "받은";
    $unkind = "send";

    $sql = " update $g4[memo_table]
                set me_read_datetime = '$g4[time_ymdhis]' 
              where me_id = '$me_id' 
                and me_recv_mb_id = '$member[mb_id]' 
                and me_read_datetime = '0000-00-00 00:00:00' ";
    sql_query($sql);
} 
else if ($kind == "send") 
{
    $t = "보낸";
    $unkind = "recv";
}
else 
{
    alert("\$kind 값을 넘겨주세요.");
}

$g4[title] = "{$t} 쪽지 보기";
include_once("$g4[path]/head.sub.php");

$sql = " select * from $g4[memo_table]
          where me_id = '$me_id'
            and me_{$kind}_mb_id = '$member[mb_id]' ";
$memo = sql_fetch($sql);

// 이전 쪽지
$sql = " select * from $g4[memo_table]
          where me_id > '$me_id'
            and me_{$kind}_mb_id = '$member[mb_id]' 
          order by me_id asc
          limit 1 ";
$prev = sql_fetch($sql);
if ($prev[me_id])
    $prev_link = "./memo_view.php?kind=$kind&me_id=$prev[me_id]";
else
    $prev_link = "javascript:alert('쪽지의 처음입니다.');";


// 다음 쪽지
$sql = " select * from $g4[memo_table]
          where me_id < '$me_id'
            and me_{$kind}_mb_id = '$member[mb_id]' 
          order by me_id desc
          limit 1 ";
$next = sql_fetch($sql);
if ($next[me_id])
    $next_link = "./memo_view.php?kind=$kind&me_id=$next[me_id]";
else
    $next_link = "javascript:alert('쪽지의 마지막입니다.');";

$mb = get_member($memo["me_{$unkind}_mb_id"]);

echo "<script type='text/javascript' src='$g4[path]/js/sideview.js'></script>";

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/memo_view.skin.php");

include_once("$g4[path]/tail.sub.php");
?>
