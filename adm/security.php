<?php
$sub_menu = '100600';
require_once './_common.php';

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.', G5_URL);
}

@require_once './safe_check.php';
if (function_exists('social_log_file_delete')) {
    social_log_file_delete();
}

run_event('adm_cache_file_delete_before');

$g5['title'] = '추가 보안';
require_once './admin.head.php';
?>

<div class="local_desc02 local_desc">
    <h1>
        http 를 https로 바꾸기 위해서는 SSL인증서가 필요합니다.
    </h1>
</div>


<p>
HTTPS는 보안을 강화하기 위한 HTTP 프로토콜의 보안 버전으로 HTTPS를 사용하는 것이 HTTP보다 안전한 운영을 도와줍니다.
<ul><br>
    <li>    데이터의 기밀성: HTTPS는 데이터를 암호화하여 중간에 누군가가 데이터를 엿볼 수 없도록 합니다.</li>
    <li>    데이터의 무결성: HTTPS는 데이터가 전송 도중에 변경되지 않았음을 보장하여 데이터의 무결성을 유지합니다.</li>
    <li>    신원 보증: SSL인증서를 사용하여 서버의 신원을 보장하고, 사용자는 신뢰할 수 있는 사이트인지 확인할 수 있습니다.</li>
</p>
</ul>
<p><br><br>
HTTPS 설정을 위해서는 SSL인증서가 필요합니다.<br>
SSL인증서는 다양한 인증기관을 통해 발급받을 수 있습니다.
인증기관을 통해 발급받은 인증서를 서버에 설치하여 적용시키면 
HTTPS보안을 사용할 준비는 거의 다 완료됩니다.
<ul>
<br>
    <li>인증서 발급 요청: 인증서를 발급받기 위해 신뢰할 수 있는 인증기관(Certificate Authority)에 인증서 발급을 요청합니다.</li>
    <li>인증서 설치: 발급받은 인증서를 웹 서버에 설치하여 적용합니다.</li>
    
</ul>
</p>

<p>
        무료로 SSL인증서를 발급해주는 사이트 :<br>
        <ul>
            <li><a href="https://letsencrypt.org">Let's Encrypt</a></li>
            <li><a href="https://zerossl.com">ZeroSSL</a></li>
            <li><a href="https://www.ssl.com">SSL.com</a></li>
        </
</p>

<?php
if (isset($_POST['add_code_button'])) {
    $directory = '/html/extend/';  // 대상 디렉토리 경로
    $htaccess_file = $directory . '.htaccess';

    // 추가할 코드
    $code_to_add = "RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]";

    if (file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, $code_to_add, FILE_APPEND | LOCK_EX);
        echo "코드가 성공적으로 추가되었습니다.";
    } else {
        echo "'.htaccess' 파일을 찾을 수 없습니다.";
    }
}
?>

<form method="post">
    <input type="submit" name="add_code_button" value="https 적용하기" class = "btn_confirm02 button">
</form>




<link rel="stylesheet" href="admin.css">

<?php
require_once './admin.tail.php';
