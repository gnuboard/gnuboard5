<?
include_once("./_common.php");

$po = sql_fetch(" select * from $g4[poll_table] where po_id = '$po_id' ");
if (!$po[po_id]) 
    alert_close('설문조사 정보가 없습니다.');

$g4[title] = "설문조사 결과";

$po_subject = $po[po_subject];

$max = 1;
$total_po_cnt = 0;
for ($i=1; $i<=9; $i++) 
{
    $poll = $po["po_poll{$i}"];
    if ($poll == "") { break; }
    $total_po_cnt += $po["po_cnt{$i}"];

    if ($po["po_cnt{$i}"] > $max)
        $max = $po["po_cnt{$i}"];
}
$nf_total_po_cnt = number_format($total_po_cnt);

$list = array();

for ($i=1; $i<=9; $i++) 
{
    $poll = $po["po_poll" . $i];
    if ($poll == "") { break; }

    $list[$i][content] = $poll;
    $list[$i][cnt] = $po["po_cnt" . $i];
    if ($total_po_cnt > 0) 
        $list[$i][rate] = ($list[$i][cnt] / $total_po_cnt) * 100;

    $bar = (int)($list[$i][cnt] / $max * 100);
    
    $list[$i][bar] = $bar;
    $list[$i][num] = $i;
}

$list2 = array();

// 기타의견 리스트
$sql = " select a.*, b.mb_open
           from $g4[poll_etc_table] a
           left join $g4[member_table] b on (a.mb_id = b.mb_id)
          where po_id = '$po_id' order by pc_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    $list2[$i][name] = get_sideview($row[mb_id], cut_str($row[pc_name],10), '', '', $row[mb_open]);
    $list2[$i][idea] = get_text(cut_str($row[pc_idea], 255));
    $list2[$i][datetime] = $row[pc_datetime];

    $list2[$i][del] = "";
    if ($is_admin == "super" || ($row[mb_id] == $member[mb_id] && $row[mb_id])) 
        $list2[$i][del] = "<a href=\"javascript:del('./poll_etc_update.php?w=d&pc_id=$row[pc_id]&po_id=$po_id');\">";
}

// 기타의견 입력
$is_etc = false;
if ($po[po_etc]) 
{
    $is_etc = true;
    $po_etc = $po[po_etc];
    if ($member[mb_id]) 
        $name = "<b>$member[mb_nick]</b> <input type='hidden' name='pc_name' value='$member[mb_nick]'>";
    else 
        $name = "<input type='text' name='pc_name' size=10 class=input required itemname='이름'>";
}

$list3 = array();

// 다른투표
$sql = " select po_id, po_subject, po_date from $g4[poll_table] order by po_id desc ";
$result = sql_query($sql);
for ($i=0; $row2=sql_fetch_array($result); $i++) 
{
    $list3[$i][po_id]   = $row2[po_id];
    $list3[$i][date]    = substr($row2[po_date],2,8);
    $list3[$i][subject] = cut_str($row2[po_subject],60,"…");
}

include_once("$g4[path]/head.sub.php");

echo "<script type='text/javascript' src='$g4[path]/js/sideview.js'></script>";

if (!$skin_dir) $skin_dir = "basic";
$poll_skin_path = "$g4[path]/skin/poll/$skin_dir";
if (!file_exists("$poll_skin_path/poll_result.skin.php")) die("skin error");
include_once ("$poll_skin_path/poll_result.skin.php");

include_once("$g4[path]/tail.sub.php");
?>
