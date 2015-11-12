<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE))
    return;

// Browscap 캐시 파일이 있으면 실행
if(defined('G5_VISIT_BROWSCAP_USE') && G5_VISIT_BROWSCAP_USE && is_file(G5_DATA_PATH.'/cache/browscap_cache.php')) {
    include_once(G5_PLUGIN_PATH.'/browscap/Browscap.php');

    $browscap = new phpbrowscap\Browscap(G5_DATA_PATH.'/cache');
    $browscap->doAutoUpdate = false;
    $browscap->cacheFilename = 'browscap_cache.php';

    $info = $browscap->getBrowser($_SERVER['HTTP_USER_AGENT']);

    $vi_browser = $info->Comment;
    $vi_os = $info->Platform;
    $vi_device = $info->Device_Type;
}
?>