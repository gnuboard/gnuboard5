<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE))
    return;

// Browscap 캐시 파일이 있으면 실행
if(defined('G5_VISIT_BROWSCAP_USE') && G5_VISIT_BROWSCAP_USE && is_file(G5_DATA_PATH.'/cache/browscap_cache.php')) {
    include_once(G5_PLUGIN_PATH.'/browscap/Browscap.php');

    $browscap = new Browscap(G5_DATA_PATH.'/cache');
    $browscap->doAutoUpdate = false;
    // 방문 요청에서는 원격 업데이트를 하지 않는다. 캐시가 없거나 이전 형식이면 Browscap 정보 없이 기록한다(KVE-2026-3255).
    $browscap->updateMethod = false;
    $browscap->cacheFilename = 'browscap_cache.php';

    try {
        $info = $browscap->getBrowser(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

        $vi_browser = isset($info->Comment) ? sql_real_escape_string($info->Comment) : '';
        $vi_os = isset($info->Platform) ? sql_real_escape_string($info->Platform) : '';
        $vi_device = isset($info->Device_Type) ? sql_real_escape_string($info->Device_Type) : '';
    } catch (Exception $e) {
        $vi_browser = $vi_os = $vi_device = '';
    }
}
