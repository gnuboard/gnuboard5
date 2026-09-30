<?php
// 실행: php tests/attachment.php (PHP 7 이상, DB 불필요)
namespace AttachmentTest;
if (PHP_SAPI !== 'cli') exit;
\define('_GNUBOARD_', true);
require dirname(__DIR__).'/lib/attachment.lib.php';
// 운영 코드를 별도 namespace에서 실행하여 난수 오류와 충돌을 결정적으로 재현합니다.
eval('namespace AttachmentTest; use \Exception;'.substr(file_get_contents(dirname(__DIR__).'/lib/attachment.lib.php'), 5));

$mode = 'native';
$calls = 0;
$move_fail = false;
function function_exists($name) {
    global $mode;
    if ($name === 'random_bytes') return $mode !== 'openssl' && $mode !== 'weak' && $mode !== 'device' && $mode !== 'unavailable';
    if ($name === 'openssl_random_pseudo_bytes') return $mode === 'openssl' || $mode === 'weak';
    return \function_exists($name);
}
function random_bytes($length) {
    global $mode, $calls;
    $calls++;
    if ($mode === 'throw') throw new \Exception('난수 생성 실패');
    if ($mode === 'short') return 'short';
    if ($mode === 'collision' || ($mode === 'retry' && $calls === 1)) return str_repeat('a', $length);
    return \random_bytes($length);
}
function openssl_random_pseudo_bytes($length, &$strong) {
    global $mode;
    $strong = $mode === 'openssl';
    return str_repeat('b', $length);
}
function is_readable($path) {
    global $mode;
    return $mode !== 'unavailable' && \is_readable($path);
}
function is_uploaded_file($path) { return is_file($path); }
function move_uploaded_file($from, $to) {
    global $move_fail;
    return !$move_fail && rename($from, $to);
}
function check($ok, $message) {
    if (!$ok) throw new \Exception($message);
}
$dir = sys_get_temp_dir().'/g5-attachment-'.bin2hex(\random_bytes(8));
mkdir($dir);
mkdir($dir.'/file');
mkdir($dir.'/file/test');
mkdir($dir.'/qa');
\define('G5_DATA_PATH', $dir);
\define('G5_DIR_PERMISSION', 0700);
\define('G5_FILE_PERMISSION', 0600);
try {
    $_SERVER['REMOTE_ADDR'] = '192.0.2.1';
    $seen = array();
    for ($i=0; $i<100; $i++) {
        $value = \g5_attachment_random_bytes();
        check(strlen($value) === 16 && !isset($seen[bin2hex($value)]), '독립적인 128비트 난수');
        $seen[bin2hex($value)] = true;
    }
    foreach (array('native', 'openssl', 'device') as $mode) {
        check(strlen(g5_attachment_random_bytes()) === 16, '안전한 난수 소스: '.$mode);
    }
    foreach (array('throw', 'short', 'weak', 'unavailable') as $mode) {
        check(g5_attachment_random_bytes() === false, '난수 실패 시 중단: '.$mode);
    }
    $mode = 'native';
    foreach (array('한 글.jpg'=>'.jpg', 'a.php'=>'.php-x', 'a.svg'=>'.svg-x', 'a.php.jpg'=>'.jpg', 'README'=>'') as $original=>$suffix) {
        file_put_contents($dir.'/tmp', 'payload');
        $name = g5_store_attachment($dir.'/tmp', $original, $dir.'/qa');
        check(preg_match('/^[a-f0-9]{32}'.preg_quote($suffix, '/').'$/D', $name) === 1, '확장자와 정규화: '.$original);
        check(file_get_contents($dir.'/qa/'.$name) === 'payload', '파일 내용 보존');
        unlink($dir.'/qa/'.$name);
    }
    $existing = $dir.'/qa/'.bin2hex(str_repeat('a', 16)).'.jpg';
    file_put_contents($existing, '기존 파일');
    foreach (array('collision', 'retry', 'throw') as $mode) {
        $calls = 0;
        file_put_contents($dir.'/tmp', 'new');
        $name = g5_store_attachment($dir.'/tmp', 'a.jpg', $dir.'/qa');
        check(file_get_contents($existing) === '기존 파일', '충돌 시 기존 파일 보존');
        if ($mode === 'retry') {
            check($name !== false && $calls === 2, '충돌 후 새 난수로 재시도');
            unlink($dir.'/qa/'.$name);
        } else {
            check($name === false && is_file($dir.'/tmp'), '실패 시 임시 업로드 보존');
            if ($mode === 'collision') check($calls === 10, '충돌 재시도 제한');
        }
    }
    unlink($existing);
    $mode = 'native';
    $move_fail = true;
    check(g5_store_attachment($dir.'/tmp', 'a.jpg', $dir.'/qa') === false, '이동 실패');
    check(count(glob($dir.'/qa/*')) === 0, '실패 시 예약 파일 정리');
    $move_fail = false;
    check(g5_store_attachment($dir.'/tmp', 'a.jpg', $dir.'/missing') === false, '저장 경로 오류');
    check(\g5_store_attachment($dir.'/tmp', 'a.jpg', $dir.'/qa') === false, '실제 HTTP 업로드가 아닌 파일 거부');

    // 실제 두 업로드 루프를 실행하여 삭제 체크를 함께 보낸 교체 실패를 검증합니다.
    function sql_fetch($sql) { return array('bf_file'=>'legacy.jpg'); }
    function get_file($table, $id) { return array('count'=>1); }
    function get_safe_filename($name) { return $name; }
    function clean_relative_paths($name) { return $name; }
    function run_replace($event, $value) { return $value; }
    function delete_board_thumbnail($table, $name) { global $deleted_thumbnails; $deleted_thumbnails[] = $name; }
    function delete_qa_thumbnail($name) { global $deleted_thumbnails; $deleted_thumbnails[] = $name; }
    $w = 'u'; $bo_table = 'test'; $wr_id = 1; $is_admin = 'super';
    $board = array('bo_upload_count'=>2, 'bo_upload_size'=>1000);
    $qaconfig = array('qa_upload_size'=>1000);
    $config = array('cf_image_extension'=>'jpg', 'cf_flash_extension'=>'swf');
    $g5 = array('board_file_table'=>'files');
    $write = array('qa_file1'=>'legacy.jpg');
    foreach (array('write_update.php'=>0, 'qawrite_update.php'=>1) as $route=>$index) {
        $folder = $index ? $dir.'/qa' : $dir.'/file/test';
        file_put_contents($folder.'/legacy.jpg', '기존 이미지');
        $source = file_get_contents(dirname(__DIR__).'/bbs/'.$route);
        $start = strpos($source, '// 파일개수 체크');
        $end = strpos($source, $index ? "if(\$w == '' || \$w == 'a' || \$w == 'r')" : '// 나중에 테이블에 저장하는 이유', $start);
        foreach (array('throw', 'collision') as $mode) {
            file_put_contents($dir.'/tmp', 'new');
            $collision = $folder.'/'.bin2hex(str_repeat('a', 16)).'.txt';
            file_put_contents($collision, '충돌 파일');
            $_FILES = array('bf_file'=>array('name'=>array($index=>'new.txt'), 'tmp_name'=>array($index=>$dir.'/tmp'), 'size'=>array($index=>3), 'error'=>array($index=>0)));
            $_POST = array('bf_file_del'=>array($index=>1));
            $deleted_thumbnails = array();
            eval('namespace AttachmentTest;'.substr($source, $start, $end-$start));
            check(file_get_contents($folder.'/legacy.jpg') === '기존 이미지', $route.' 교체 실패 시 원본 보존');
            check($upload[$index]['file'] === '' && !$upload[$index]['del_check'], $route.' 기존 DB 참조 보존');
            check(count($deleted_thumbnails) === 0, $route.' 기존 썸네일 보존');
            check($file_upload_msg !== '', $route.' 실패 안내');
            unlink($collision);
        }
        $mode = 'native';
        eval('namespace AttachmentTest;'.substr($source, $start, $end-$start));
        $new_name = $upload[$index]['file'];
        check($new_name !== '' && file_get_contents($folder.'/'.$new_name) === 'new', $route.' 새 파일 저장');
        check(!is_file($folder.'/legacy.jpg') && $deleted_thumbnails === array('legacy.jpg'), $route.' 교체 성공 시 기존 파일과 썸네일 삭제');
        check($upload[$index]['source'] === 'new.txt', $route.' 원본 다운로드 이름 보존');
        // 삭제만 요청한 경우에도 기존 저장 이름을 그대로 사용합니다.
        if ($index) $write['qa_file1'] = $new_name;
        else rename($folder.'/'.$new_name, $folder.'/legacy.jpg');
        $_FILES['bf_file']['name'][$index] = '';
        eval('namespace AttachmentTest;'.substr($source, $start, $end-$start));
        check($upload[$index]['del_check'] && count(glob($folder.'/*')) === 0, $route.' 삭제 전용 요청');
    }
    echo "PASS: 난수 소스, 파일명, 충돌, 저장 실패 및 두 업로드 경로의 교체·삭제\n";
} finally {
    foreach (glob($dir.'/qa/*') as $path) unlink($path);
    foreach (glob($dir.'/file/test/*') as $path) unlink($path);
    if (is_file($dir.'/tmp')) unlink($dir.'/tmp');
    rmdir($dir.'/qa'); rmdir($dir.'/file/test'); rmdir($dir.'/file'); rmdir($dir);
}
