<?php
ini_set('memory_limit', '-1');

$sub_menu = "100510";
require_once './_common.php';

// clean the output buffer
ob_end_clean();

if (!(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE)) {
    die('사용할 수 없는 기능입니다.');
}

if ($is_admin != 'super') {
    die('최고관리자만 접근 가능합니다.');
}

// 업데이트는 POST와 관리자 토큰으로만 실행한다(KVE-2026-3255).
if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST'
    || !isset($_POST['token']) || !is_string($_POST['token']) || $_POST['token'] === ''
    || !isset($_REQUEST['token']) || $_REQUEST['token'] !== $_POST['token']) {
    die('올바른 방법으로 이용해 주십시오.');
}
check_admin_token();

require_once G5_PLUGIN_PATH . '/browscap/Browscap.php';

$browscap = new Browscap(G5_DATA_PATH . '/cache');
$browscap->updateMethod = 'cURL';
$browscap->cacheFilename = 'browscap_cache.php';

// 사설 미러·검증 환경용 설정. 설정 파일(extend 등)에서 상수로만 지정할 수 있고 요청 값으로는 바꿀 수 없다.
// 지정해도 https 주소와 인증서·호스트 이름 검증은 그대로 적용된다.
if (defined('G5_BROWSCAP_REMOTE_INI_URL') && G5_BROWSCAP_REMOTE_INI_URL) {
    $browscap->remoteIniUrl = G5_BROWSCAP_REMOTE_INI_URL;
}
if (defined('G5_BROWSCAP_REMOTE_VER_URL') && G5_BROWSCAP_REMOTE_VER_URL) {
    $browscap->remoteVerUrl = G5_BROWSCAP_REMOTE_VER_URL;
}
if (defined('G5_BROWSCAP_CA_FILE') && G5_BROWSCAP_CA_FILE) {
    $browscap->caFile = G5_BROWSCAP_CA_FILE;
}

try {
    $browscap->updateCache();
} catch (Exception $e) {
    die("Browscap 정보를 업데이트하지 못했습니다. 기존 정보는 그대로 유지됩니다.\n" . $e->getMessage());
}

die('');
