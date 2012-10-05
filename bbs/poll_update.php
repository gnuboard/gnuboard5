<?
include_once("./_common.php");

$po = sql_fetch(" select * from $g4[poll_table] where po_id = '$_POST[po_id]' ");
if (!$po[po_id]) 
    alert_close("po_id 값이 제대로 넘어오지 않았습니다.");

if ($member[mb_level] < $po[po_level]) 
    alert_close("권한 $po[po_level] 이상 회원만 투표에 참여하실 수 있습니다.");

// 쿠키에 저장된 투표번호가 없다면
if (get_cookie("ck_po_id") != $po[po_id]) 
{
    // 투표했던 ip들 중에서 찾아본다
    $search_ip = false;
    $ips = explode("\n", trim($po[po_ips]));
    for ($i=0; $i<count($ips); $i++) 
    {
        if ($_SERVER[REMOTE_ADDR] == trim($ips[$i])) 
        {
            $search_ip = true;
            break;
        }
    }

    // 투표했던 회원아이디들 중에서 찾아본다
    $search_mb_id = false;
    if ($is_member)
    {
        $ids = explode("\n", trim($po[mb_ids]));
        for ($i=0; $i<count($ids); $i++) 
        {
            if ($member[mb_id] == trim($ids[$i])) 
            {
                $search_mb_id = true;
                break;
            }
        }
    }

    // 없다면 선택한 투표항목을 1증가 시키고 ip, id를 저장
    if (!($search_ip || $search_mb_id)) 
    {
        $po_ips = $po[po_ips] . $_SERVER[REMOTE_ADDR] . "\n";
        $mb_ids = $po[mb_ids];
        if ($member[mb_id])
            $mb_ids .= $member[mb_id] . "\n";
        sql_query(" update $g4[poll_table] set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1, po_ips = '$po_ips', mb_ids = '$mb_ids' where po_id = '$po_id' ");
    }

    if (!$search_mb_id)
        insert_point($member[mb_id], $po[po_point], $po[po_id] . ". " . cut_str($po[po_subject],20) . " 투표 참여 ", "@poll", $po[po_id], "투표");
}

set_cookie("ck_po_id", $po[po_id], 86400 * 15); // 투표 쿠키 보름간 저장

goto_url("./poll_result.php?po_id=$po_id&skin_dir=$skin_dir");
?>
