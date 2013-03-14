<?
include_once("./_common.php");

include_once("$g4[path]/head.sub.php");

if (!$member[mb_id]) {
    $href = "./login.php?$qstr&url=".urlencode("./board.php?bo_table=$bo_table&wr_id=$wr_id");
    echo <<<HEREDOC
    <script language='JavaScript'>
        alert("회원만 가능합니다.");
        top.location.href = "$href";
    </script>
HEREDOC;
    exit;
}

$sql = " select count(*) as cnt from $g4[scrap_table]
          where mb_id = '$member[mb_id]'
            and bo_table = '$bo_table'
            and wr_id = '$wr_id' ";
$row = sql_fetch($sql);
if ($row[cnt]) {
    echo <<<HEREDOC
    <script language="JavaScript">
    //if (confirm('이미 스크랩하신 게시물 입니다.\\n\\n지금 스크랩을 확인하시겠습니까?')) { win_scrap(); }
    alert("이미 스크랩하신 게시물 입니다.");
    </script>
HEREDOC;
    exit;
}

$tmp_row = sql_fetch(" select max(ms_id) as max_ms_id from $g4[scrap_table] ");
$ms_id = $tmp_row[max_ms_id] + 1;

$sql = " insert into $g4[scrap_table] 
                ( ms_id, mb_id, bo_table, wr_id, ms_datetime )
         values ( '$ms_id', '$member[mb_id]', '$bo_table', '$wr_id', '$g4[time_ymdhis]' ) ";
sql_query($sql);

echo <<<HEREDOC
<script language="JavaScript"> 
    //if (confirm("이 게시물을 스크랩 하였습니다.\n\n지금 스크랩을 확인하시겠습니까?")) win_scrap();
    alert("이 게시물을 스크랩 하였습니다.");
</script>
HEREDOC;

include_once("$g4[path]/head.sub.php");
?>
