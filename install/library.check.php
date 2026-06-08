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
        'message' => '설치 전 DB 점검 응답 처리에 필요합니다.'
    ),
    array(
        'name' => 'iconv 또는 mbstring',
        'required' => true,
        'ok' => function_exists('iconv') || function_exists('mb_convert_encoding'),
        'message' => '문자열 인코딩 처리에 필요합니다.'
    ),
    array(
        'name' => 'GD',
        'required' => false,
        'ok' => extension_loaded('gd') && function_exists('gd_info'),
        'message' => '자동등록방지 문자와 썸네일 기능에 필요합니다.'
    ),
    array(
        'name' => 'OpenSSL',
        'required' => false,
        'ok' => extension_loaded('openssl'),
        'message' => '암호화, 외부 연동, 보안 통신 기능 사용 시 권장됩니다.'
    ),
    array(
        'name' => 'cURL',
        'required' => false,
        'ok' => function_exists('curl_init'),
        'message' => '외부 API, 결제, 소셜 연동 사용 시 권장됩니다.'
    ),
    array(
        'name' => 'Fileinfo',
        'required' => false,
        'ok' => function_exists('finfo_open'),
        'message' => '업로드 파일의 MIME 타입 확인에 권장됩니다.'
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
