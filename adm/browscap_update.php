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

require_once G5_PLUGIN_PATH . '/browscap/Browscap.php';

$browscap = new phpbrowscap\Browscap(G5_DATA_PATH . '/cache');
$browscap->updateMethod = 'cURL';
$browscap->cacheFilename = 'browscap_cache.php';
$browscap->updateCache();

die('');
