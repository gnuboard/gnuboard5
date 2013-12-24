<?php
include_once('./_common.php');

header('Content-Type: text/html; charset=UTF-8');
header('Pragma: no-cache');

if(version_compare(PHP_VERSION, '5.3.0') >= 0)
{
    date_default_timezone_set(@date_default_timezone_get());
}

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

$syndi_path = dirname(__FILE__);

include $syndi_path . '/config/site.config.php';

$sql = " select bo_table from " . $g5['board_table'] . " b, ". $g5['group_table'] . " g where b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id order by b.gr_id, b.bo_table limit 1 ";
$channel = sql_fetch($sql);

if (!$channel) die("게시판이 존재하지 않습니다. 게시판 생성후 실행하시기 바랍니다.");
$sql = " select wr_id from {$g5['write_prefix']}{$channel['bo_table']} where wr_is_comment = 0 order by wr_num, wr_reply desc limit 1 ";
$article = sql_fetch($sql);
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>네이버 신디케이션 핑</title>
<style>
body {background:#f5f6fa;color:#000}
h1 {padding:20px 0 0;text-align:center}
ul {margin:30px;padding:0;border:1px solid #aaa;border:1px solid #aaa;list-style:none;zoom:1}
ul:after {display:block;visibility:hidden;clear:both;content:""}
li {float:left;width:50%;border-bottom:1px solid #e9e9e9}
a {display:block;padding:1em;height:2em;background:#fff;color:#000;font-weight:bold;text-decoration:none}
a:focus, a:hover {background:#333;color:#fff;text-decoration:none}
.left_line {border-left:1px solid #e9e9e9}
.no_bottom_line {border-bottom:0 !important}
.bg {background:#f7f7f7}
</style>
</head>

<body>
<h1>네이버 신디케이션 핑 (Naver Syndication PING)</h1>

<ul>
    <li><a href="http://developer.naver.com/wiki/pages/SyndicationAPI">Syndication API</a></li>
    <li><a href="http://syndication.openapi.naver.com/status/?site=<?php echo $syndi_tag_domain; ?>" class="left_line">Naver Syndication 연결확인</a></li>
    <li><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:site&amp;type=site" class="bg">사이트 정보</a></li>
    <li><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:channel:<?php echo $channel['bo_table']; ?>&type=channel" class="bg left_line">특정 채널 정보</a></li>
    <li><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:site&type=channel" class="bg">채널 목록</a></li>
    <li><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:site&type=article" class="left_line">사이트의 모든 문서 목록</a></li>
    <li><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:channel:<?php echo $channel['bo_table']; ?>&type=article">특정 채널의 문서 목록</a></li>
    <li><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:article:<?php echo $channel['bo_table']; ?>-<?php echo $article['wr_id']; ?>&type=article" class="left_line">특정 문서 정보</a></li>
    <li class="no_bottom_line"><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:site&amp;type=deleted" class="bg">사이트의 모든 삭제문서 목록</a></li>
    <li class="no_bottom_line"><a href="<?php echo G5_SYNDI_URL; ?>/syndi_echo.php?id=tag:<?php echo $syndi_tag_domain.','.$syndi_tag_year; ?>:channel:<?php echo $channel['bo_table']; ?>&type=deleted" class="bg left_line">특정 채널의 삭제 문서 목록</a></li>
</ul>

</body>
</html>
