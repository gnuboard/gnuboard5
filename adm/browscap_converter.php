<?php
ini_set('memory_limit', '-1');
require_once './_common.php';

// clean the output buffer
ob_end_clean();

if (!(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE)) {
    die('사용할 수 없는 기능입니다.');
}

if ($is_admin != 'super') {
    die('최고관리자로 로그인 후 실행해 주세요.');
}

// browscap cache 파일 체크
if (!is_file(G5_DATA_PATH . '/cache/browscap_cache.php')) {
    echo '<p>Browscap 정보가 없습니다. 아래 링크로 이동해 Browscap 정보를 업데이트 하세요.</p>' . PHP_EOL;
    echo '<p><a href="' . G5_ADMIN_URL . '/browscap.php">Browscap 업데이트</a></p>' . PHP_EOL;
    exit;
}

require_once G5_PLUGIN_PATH . '/browscap/Browscap.php';
$browscap = new phpbrowscap\Browscap(G5_DATA_PATH . '/cache');
$browscap->doAutoUpdate = false;
$browscap->cacheFilename = 'browscap_cache.php';

// 데이터 변환
$rows = isset($_GET['rows']) ? preg_replace('#[^0-9]#', '', $_GET['rows']) : 0;
if (!$rows) {
    $rows = 100;
}

$sql_common = " from {$g5['visit_table']} where vi_agent <> '' and ( vi_browser = '' or vi_os = '' or vi_device = '' ) ";
$sql_order  = " order by vi_id desc ";
$sql_limit  = " limit 0, " . strval($rows) . " ";

$sql = " select count(vi_id) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = " select vi_id, vi_agent, vi_browser, vi_os, vi_device
            $sql_common
            $sql_order
            $sql_limit ";
$result = sql_query($sql);

$cnt = 0;
for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $info = $browscap->getBrowser($row['vi_agent']);

    $brow = $row['vi_browser'];
    if (!$brow) {
        $brow = $info->Comment;
    }

    $os = $row['vi_os'];
    if (!$os) {
        $os = $info->Platform;
    }

    $device = $row['vi_device'];
    if (!$device) {
        $device = $info->Device_Type;
    }

    $sql2 = " update {$g5['visit_table']}
                set vi_browser  = '$brow',
                    vi_os       = '$os',
                    vi_device   = '$device'
                where vi_id = '{$row['vi_id']}' ";
    sql_query($sql2);

    $cnt++;
}

if (($total_count - $cnt) == 0 || $total_count == 0) {
    echo '<div class="check_processing"></div><p>변환완료</p>';
} else {
    echo '<p>총 ' . number_format($total_count) . '건 중 ' . number_format($cnt) . '건 변환완료<br><br>접속로그를 추가로 변환하시려면 아래 업데이트 버튼을 클릭해 주세요.</p><button type="button" id="run_update">업데이트</button>';
}
