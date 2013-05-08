<?php
include_once('./_common.php');

$url = $_REQUEST['url'];

$title = str_replace('\"', '"',$_REQUEST['title']);
$title_url = $title.' : '.$url;
//$title = strcut_utf8($title, 140);

switch($_REQUEST['sns']) {
    case 'facebook' :
        header("Location:http://www.facebook.com/sharer/sharer.php?s=100&amp;p[url]=".$url."&amp;p[title]=".$title);
        break;
    case 'twitter' :
        header("Location:http://twitter.com/home?status=".$title_url);
        break;
    case 'me2day' :
        header("Location:http://me2day.net/posts/new?new_post[body]=".$title_url);
        break;
    case 'yozm' :
        header("Location:http://yozm.daum.net/api/popup/prePost?sourceid=41&amp;prefix=".$title_url);
        break;
    case 'google' :
        header("Location:https://plus.google.com/share?url=".$url);
        break;
    default :
        echo 'Error';
        break;
}
?>