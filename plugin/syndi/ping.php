<?php
include_once("./_common.php");

if (!$write)
    die("게시글이 없습니다.");

if ($group['gr_use_access'])
    die("게시판그룹에서 접근사용을 해제하여 주십시오.");

if ($board['bo_read_level'] > 1)
    die("비회원 읽기가 가능한 게시판만 신디케이션을 지원합니다.");

if (strstr($write['wr_option'], 'secret'))
    die("비밀글은 신디케이션을 지원하지 않습니다.");

if (preg_match('#^('.$config['cf_syndi_except'].')$#', $bo_table))
    die("신디케이션에서 제외된 게시판입니다.");

$title        = htmlspecialchars($write['wr_subject']);
$author       = htmlspecialchars($write['wr_name']);
$published    = date('Y-m-d\TH:i:s\+09:00', strtotime($write['wr_datetime']));
$updated      = $published;
$link_href    = G5_BBS_URL . "/board.php?bo_table={$bo_table}";
$id           = $link_href . htmlspecialchars("&wr_id={$wr_id}");
$cf_title     = htmlspecialchars($config['cf_title']);
$link_title   = htmlspecialchars($board['bo_subject']);
$feed_updated = date('Y-m-d\TH:i:s\+09:00', G5_SERVER_TIME);

$find         = array('&amp;', '&nbsp;'); # 찾아서
$replace      = array('&', ' '); # 바꾼다

$content      = str_replace( $find, $replace, $write['wr_content'] );
$summary      = str_replace( $find, $replace, strip_tags($write['wr_content']) );

Header("Content-type: text/xml");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<feed xmlns=\"http://webmastertool.naver.com\">\n";
echo "<id>" . G5_URL . "</id>\n";
echo "<title>naver syndication feed document</title>\n";
echo "<author>\n";
    echo "<name>webmaster</name>\n";
echo "</author>\n";

echo "<updated>{$feed_updated}</updated>\n";

echo "<link rel=\"site\" href=\"" . G5_URL . "\" title=\"{$cf_title}\" />\n";
echo "<entry>\n";
    echo "<id>{$id}</id>\n";
    echo "<title><![CDATA[{$title}]]></title>\n";
    echo "<author>\n";
        echo "<name>{$author}</name>\n";
    echo "</author>\n";
    echo "<updated>{$updated}</updated>\n";
    echo "<published>{$published}</published>\n";
    echo "<link rel=\"via\" href=\"{$link_href}\" title=\"{$link_title}\" />\n";
    echo "<link rel=\"mobile\" href=\"{$id}\" />\n";
    echo "<content type=\"html\"><![CDATA[{$content}]]></content>\n";
    echo "<summary type=\"text\"><![CDATA[{$summary}]]></summary>\n";
    echo "<category term=\"{$bo_table}\" label=\"{$link_title}\" />\n";
echo "</entry>\n";
echo "</feed>";
?>