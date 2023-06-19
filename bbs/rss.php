<?php
include_once('./_common.php');

// 특수문자 변환
function specialchars_replace($str, $len=0) {
    if ($len) {
        $str = substr($str, 0, $len);
    }

    $str = str_replace(array("&", "<", ">"), array("&amp;", "&lt;", "&gt;"), $str);

    /*
    $str = preg_replace("/&/", "&amp;", $str);
    $str = preg_replace("/</", "&lt;", $str);
    $str = preg_replace("/>/", "&gt;", $str);
    */

    return $str;
}

$sql = " select gr_id, bo_subject, bo_page_rows, bo_read_level, bo_use_rss_view from {$g5['board_table']} where bo_table = '$bo_table' ";
$row = sql_fetch($sql);
$subj2 = specialchars_replace($row['bo_subject'], 255);
$lines = $row['bo_page_rows'];

// 비회원 읽기가 가능한 게시판만 RSS 지원
if ($row['bo_read_level'] >= 2) {
    echo '비회원 읽기가 가능한 게시판만 RSS 지원합니다.';
    exit;
}

// RSS 사용 체크
if (!$row['bo_use_rss_view']) {
    echo 'RSS 보기가 금지되어 있습니다.';
    exit;
}

header('Content-type: text/xml');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

$sql = " select gr_subject from {$g5['group_table']} where gr_id = '{$row['gr_id']}' ";
$row = sql_fetch($sql);
$subj1 = specialchars_replace($row['gr_subject'], 255);

echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title><?php echo specialchars_replace($config['cf_title'].' &gt; '.$subj1.' &gt; '.$subj2); ?></title>
<link><?php echo specialchars_replace(get_pretty_url($bo_table)); ?></link>
<language>ko</language>
<?php
$sql = " select wr_id, wr_subject, wr_content, wr_name, wr_datetime, wr_option
            from {$g5['write_prefix']}$bo_table
            where wr_is_comment = 0
            and wr_option not like '%secret%'
            order by wr_num, wr_reply limit 0, $lines ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $file = '';

    if (strstr($row['wr_option'], 'html'))
        $html = 1;
    else
        $html = 0;

if ($i === 0) {
    echo '<description>'. specialchars_replace($subj2). ' ('. $row['wr_datetime'] .')</description>'.PHP_EOL;
}
?>

<item>
<title><?php echo specialchars_replace($row['wr_subject']); ?></title>
<link><?php echo specialchars_replace(get_pretty_url($bo_table, $row['wr_id'])); ?></link>
<description><![CDATA[<?php echo $file ?><?php echo conv_content($row['wr_content'], $html) ?>]]></description>
<dc:creator><?php echo specialchars_replace($row['wr_name']) ?></dc:creator>
<?php
$date = $row['wr_datetime'];
// rss 리더 스킨으로 호출하면 날짜가 제대로 표시되지 않음
$date = substr($date,0,10) . "T" . substr($date,11,8) . "+09:00";
//$date = date('r', strtotime($date));  // 구글 서치 콘솔에서 오류가 난다
?>
<dc:date><?php echo $date ?></dc:date>
</item>

<?php
}

echo '</channel>'."\n";
echo '</rss>'."\n";