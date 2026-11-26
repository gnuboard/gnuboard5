<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$install_library_required_missing = false;
$install_library_checks = array(
    array(
        'name' => 'MySQL 확장',
        'required' => true,
        'ok' => (function_exists('mysqli_connect') && G5_MYSQLI_USE) || function_exists('mysql_connect'),
        'message' => 'MySQL DB 연결에 필요합니다.'
    ),
    array(
        'name' => 'JSON',
        'required' => true,
        'ok' => function_exists('json_encode') && function_exists('json_decode'),
        'message' => '설치, 관리자 화면, 외부 연동 응답 처리에 필요합니다.'
    ),
    array(
        'name' => 'iconv',
        'required' => true,
        'ok' => function_exists('iconv'),
        'message' => '회원가입, 문자 발송 등의 문자열 인코딩 변환에 필요합니다.'
    ),
    array(
        'name' => 'GD',
        'required' => true,
        'ok' => extension_loaded('gd') && function_exists('gd_info'),
        'message' => '자동등록방지 문자(기본 캡차)와 썸네일에 필요합니다.'
    ),
    array(
        'name' => 'mbstring',
        'required' => false,
        'ok' => function_exists('mb_strlen'),
        'message' => '다국어 문자열 처리, 메일, 문자 발송에 권장됩니다.'
    ),
    array(
        'name' => 'OpenSSL',
        'required' => false,
        'ok' => extension_loaded('openssl'),
        'message' => '보안 통신, 소셜 로그인, KCP REST API, 현금영수증 사용 시 권장됩니다.'
    ),
    array(
        'name' => 'cURL',
        'required' => false,
        'ok' => function_exists('curl_init'),
        'message' => '결제(PG) 연동, 소셜 로그인, reCAPTCHA 등 외부 API 사용 시 권장됩니다.'
    )
);

foreach ($install_library_checks as $check) {
    if ($check['required'] && !$check['ok']) {
        $install_library_required_missing = true;
        break;
    }
}
?>
<div class="ins_inner">
    <h2>서버 환경 점검</h2>
    <ul>
<?php for ($i=0; $i<count($install_library_checks); $i++) { ?>
        <li>
            <strong><?php echo $install_library_checks[$i]['ok'] ? '확인' : ($install_library_checks[$i]['required'] ? '필수 누락' : '권장 누락'); ?></strong>
            - <?php echo $install_library_checks[$i]['name']; ?>
            (<?php echo $install_library_checks[$i]['required'] ? '필수' : '권장'; ?>)
            : <?php echo $install_library_checks[$i]['message']; ?>
        </li>
<?php } ?>
    </ul>
<?php if ($install_library_required_missing) { ?>
    <p>필수 서버 환경이 준비되지 않아 설치를 진행할 수 없습니다. 누락된 PHP 확장을 설치한 뒤 새로고침해 주십시오.</p>
<?php } else { ?>
    <p>필수 서버 환경을 확인했습니다. 권장 항목은 설치 후 일부 기능 사용에 영향을 줄 수 있습니다.</p>
<?php } ?>
</div>
