<?
include_once('./_common.php');

$g4['title'] = '로그인';
include_once('./_head.sub.php');

$p = parse_url($url);
if ((isset($p['scheme']) && $p['scheme']) || (isset($p['host']) && $p['host'])) {
    //print_r2($p);
    if ($p['host'] != $_SERVER['HTTP_HOST'])
        alert('url에 타 도메인을 지정할 수 없습니다.');
}

// 이미 로그인 중이라면
if ($is_member) {
    if ($url)
        goto_url($url);
    else
        goto_url(G4_URL);
}

if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER['REQUEST_URI']);

if (G4_HTTPS_DOMAIN) {
    $login_url = $_GET['url'];
    if ($login_url) {
        if (preg_match("/^\.\.\//", $url)) {
            $login_url = urlencode(G4_URL."/".preg_replace("/^\.\.\//", "", $login_url));
        } else {
            $purl = parse_url(G4_URL);
            if ($purl['path']) {
                $path = urlencode($purl['path']);
                $urlencode = preg_replace("/".$path."/", "", $urlencode);
            }
            $login_url = $urlencode;
        }
    } else {
        $login_url = G4_URL;
    }
} else {
    $login_url = $urlencode;
}

$login_action_url = G4_HTTPS_BBS_URL."/login_check.php";

// 로그인 스킨이 없는 경우 관리자 페이지 접속이 안되는 것을 막기 위하여 기본 스킨으로 대체
$login_file = $member_skin_path.'/login.skin.php';
if (!file_exists($login_file))
    $member_skin_path   = G4_SKIN_PATH.'/member/basic';

include_once($member_skin_path.'/login.skin.php');

include_once('./_tail.sub.php');
?>
