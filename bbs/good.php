<?
include_once("./_common.php");

@include_once("$board_skin_path/good.head.skin.php");

echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";

if (!$is_member) 
{
    $href = "./login.php?$qstr&url=".urlencode("./board.php?bo_table=$bo_table&wr_id=$wr_id");

    echo "<script type='text/javascript'>alert('회원만 가능합니다.'); top.location.href = '$href';</script>";
    exit;
}

if (!($bo_table && $wr_id)) 
    alert_close("값이 제대로 넘어오지 않았습니다.");

$ss_name = "ss_view_{$bo_table}_{$wr_id}";
if (!get_session($ss_name))
    alert_close("해당 게시물에서만 추천 또는 비추천 하실 수 있습니다.");

$row = sql_fetch(" select count(*) as cnt from {$g4[write_prefix]}{$bo_table} ", FALSE);
if (!$row[cnt])
    alert_close("존재하는 게시판이 아닙니다.");

if ($good == "good" || $good == "nogood") 
{
    if($write[mb_id] == $member[mb_id])
        alert_close("자신의 글에는 추천 또는 비추천 하실 수 없습니다.");

    if (!$board[bo_use_good] && $good == "good")
        alert_close("이 게시판은 추천 기능을 사용하지 않습니다.");

    if (!$board[bo_use_nogood] && $good == "nogood")
        alert_close("이 게시판은 비추천 기능을 사용하지 않습니다.");

    $sql = " select bg_flag from $g4[board_good_table]
              where bo_table = '$bo_table'
                and wr_id = '$wr_id' 
                and mb_id = '$member[mb_id]' 
                and bg_flag in ('good', 'nogood') ";
    $row = sql_fetch($sql);
    if ($row[bg_flag])
    {
        if ($row[bg_flag] == "good")
            $status = "추천";
        else 
            $status = "비추천";
        
        echo "<script type='text/javascript'>alert('이미 \'$status\' 하신 글 입니다.');</script>";
    }
    else
    {
        // 추천(찬성), 비추천(반대) 카운트 증가
        sql_query(" update {$g4[write_prefix]}{$bo_table} set wr_{$good} = wr_{$good} + 1 where wr_id = '$wr_id' ");
        // 내역 생성
        sql_query(" insert $g4[board_good_table] set bo_table = '$bo_table', wr_id = '$wr_id', mb_id = '$member[mb_id]', bg_flag = '$good', bg_datetime = '$g4[time_ymdhis]' ");

        if ($good == "good") 
            $status = "추천";
        else 
            $status = "비추천";

        echo "<script type='text/javascript'> alert('이 글을 \'$status\' 하셨습니다.');</script>";
    }
}

@include_once("$board_skin_path/good.tail.skin.php");
?>
<script type="text/javascript"> window.close(); </script>