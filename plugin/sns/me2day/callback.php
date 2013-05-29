<?php
include_once("./_common.php");

$user_id  = $_GET['user_id'];
$user_key = $_GET['user_key'];
$auth_key = "12345678" . md5("12345678" . $user_key);

$_SESSION['me2day']['user_id']  = $user_id;
$_SESSION['me2day']['user_key'] = $user_key;

$g4['title'] = '미투데이 콜백';
include_once(G4_PATH.'/head.sub.php');

$result = json_decode(file_get_contents("http://me2day.net/api/noop.json?uid={$user_id}&ukey={$auth_key}&akey=".$config['cf_me2day_key']));
if ($result->code == 0) {

    $user     = json_decode(file_get_contents("http://me2day.net/api/get_person/{$user_id}.json"));
    $sns_name = $user->nickname;
    $sns_user = $user->id;

    set_cookie('ck_sns_name', $sns_name, 86400);
    set_session('ss_me2day_user', $sns_user);

    $g4_sns_url = G4_SNS_URL;

    echo <<<EOT
    <script>
    $(function() {
        document.write("<strong>미투데이에 승인이 되었습니다.</strong>");

        var opener = window.opener;
        opener.$("#wr_name").val("{$sns_name}");
        opener.$("#me2day_icon").attr("src", "{$g4_sns_url}/icon/me2day.png");
        opener.$("#me2day_checked").attr("disabled", false);
        opener.$("#me2day_checked").attr("checked", true);
        window.close();
    });
    </script>
EOT;

} else {

    echo <<<EOT
    <script>
    $(function() {
        alert("미투데이에 승인이 되지 않았습니다.");
        window.close();
    });
    </script>
EOT;

}

include_once(G4_PATH.'/tail.sub.php');
?>
