<?
include_once("./_common.php");

$html_title = "$group[gr_subject] > $board[bo_subject] > " . conv_subject($write[wr_subject], 255) . " > 링크";

if (!($bo_table && $wr_id && $no)) 
    alert_close("값이 제대로 넘어오지 않았습니다.");

// SQL Injection 예방
$row = sql_fetch(" select count(*) as cnt from {$g4[write_prefix]}{$bo_table} ", FALSE);
if (!$row[cnt])
    alert_close("존재하는 게시판이 아닙니다.");

if (!$write["wr_link{$no}"])
    alert_close("링크가 없습니다.");

$ss_name = "ss_link_{$bo_table}_{$wr_id}_{$no}";
if (empty($_SESSION[$ss_name])) 
{
    $sql = " update {$g4[write_prefix]}{$bo_table} set wr_link{$no}_hit = wr_link{$no}_hit + 1 where wr_id = '$wr_id' ";
    sql_query($sql);

    set_session($ss_name, true);
}

goto_url(set_http($write["wr_link{$no}"]));
?>