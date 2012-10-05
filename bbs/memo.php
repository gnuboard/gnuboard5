<?
include_once("./_common.php");
    
if (!$member[mb_id]) 
    alert_close("회원만 이용하실 수 있습니다.");

$g4[title] = "내 쪽지함";
include_once("$g4[path]/head.sub.php");

// 설정일이 지난 메모 삭제
$sql = " delete from $g4[memo_table]
          where me_recv_mb_id = '$member[mb_id]'
            and me_send_datetime < '".date("Y-m-d H:i:s", $g4[server_time] - (86400 * $config[cf_memo_del]))."' ";
sql_query($sql);

if (!$kind) $kind = "recv";

if ($kind == "recv")
    $unkind = "send";
else if ($kind == "send")
    $unkind = "recv";
else
    alert("\$kind 값을 넘겨주세요.");

$sql = " select count(*) as cnt from $g4[memo_table] where me_{$kind}_mb_id = '$member[mb_id]' ";
$row = sql_fetch($sql);
$total_count = number_format($row[cnt]);

if ($kind == "recv") 
{
    $kind_title = "받은";
    $recv_img = "on";
    $send_img = "off";
} 
else 
{
    $kind_title = "보낸";
    $recv_img = "off";
    $send_img = "on";
}

$list = array();

$sql = " select a.*, b.mb_id, b.mb_nick, b.mb_email, b.mb_homepage 
           from $g4[memo_table] a
           left join $g4[member_table] b on (a.me_{$unkind}_mb_id = b.mb_id)
          where a.me_{$kind}_mb_id = '$member[mb_id]' 
          order by a.me_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i] = $row;

    $mb_id = $row["me_{$unkind}_mb_id"];

    if ($row[mb_nick])
        $mb_nick = $row[mb_nick];
    else
        $mb_nick = "<font color=silver>정보없음</font>";

    $name = get_sideview($row[mb_id], $row[mb_nick], $row[mb_email], $row[mb_homepage]);

    if (substr($row[me_read_datetime],0,1) == '0')
        $read_datetime = '아직 읽지 않음';
    else
        $read_datetime = substr($row[me_read_datetime],2,14);

    $send_datetime = substr($row[me_send_datetime],2,14);

    $list[$i][name] = $name;
    $list[$i][send_datetime] = $send_datetime;
    $list[$i][read_datetime] = $read_datetime;
    $list[$i][view_href] = "./memo_view.php?me_id=$row[me_id]&kind=$kind";
    $list[$i][del_href] = "./memo_delete.php?me_id=$row[me_id]&kind=$kind";
}

echo "<script type='text/javascript' src='$g4[path]/js/sideview.js'></script>";

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/memo.skin.php");

include_once("$g4[path]/tail.sub.php");
?>
