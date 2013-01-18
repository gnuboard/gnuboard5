<?
include_once('./_common.php');

$g4['title'] = '로그인';
include_once('./_head.php');

$p = parse_url($url);
if ((isset($p['scheme']) && $p['scheme']) || 
    (isset($p['host']) && $p['host'])) {
    alert('url에 도메인을 지정할 수 없습니다.');
}

// 이미 로그인 중이라면
if ($is_member) {
    if ($url)
        goto_url($url);
    else
        goto_url($g4['path']);
}

if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER['REQUEST_URI']);

if ($g4['https_url']) {
    $login_url = $_GET['url'];
    if ($login_url) {
        if (preg_match("/^\.\.\//", $url)) {
            $login_url = urlencode($g4['url']."/".preg_replace("/^\.\.\//", "", $login_url));
        } else {
            $purl = parse_url($g4['url']);
            if ($purl['path']) {
                $path = urlencode($purl['path']);
                $urlencode = preg_replace("/".$path."/", "", $urlencode);
            }
            $login_url = $g4[url].$urlencode;
        }
    } else {
        $login_url = $g4[url];
    }
} else {
    $login_url = $urlencode;
}

if ($g4['https_url'])
    $login_action_url = "{$g4['https_url']}/$g4[bbs]/login_check.php";
else
    $login_action_url = "{$g4['bbs_url']}/login_check.php";

include_once($member_skin_path.'/login.skin.php');

include_once('./_tail.php');
?>
