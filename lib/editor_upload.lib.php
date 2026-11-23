<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 에디터(SmartEditor2·CHEditor5) 이미지 업로드 제한 (KVE-2026-3251)
// - 비회원 업로드 허용 여부, 파일당 최대 크기
// - 세션·회원(비회원은 세션·IP) 단위로 일정 시간 동안의 업로드 파일 수와 누적 바이트
// 설정값은 extend/editor_upload.extend.php에서 바꾼다. 아래는 그 파일이 없을 때의 기본값이다.
// 게시물에 쓰이지 않은 업로드 파일의 정리는 이 제한의 범위가 아니다(별도 과제).

if (!defined('G5_EDITOR_UPLOAD_GUEST')) define('G5_EDITOR_UPLOAD_GUEST', false);
if (!defined('G5_EDITOR_UPLOAD_FILE_MAX_SIZE')) define('G5_EDITOR_UPLOAD_FILE_MAX_SIZE', 20 * 1024 * 1024);
if (!defined('G5_EDITOR_UPLOAD_WINDOW')) define('G5_EDITOR_UPLOAD_WINDOW', 3600);
if (!defined('G5_EDITOR_UPLOAD_MAX_FILES')) define('G5_EDITOR_UPLOAD_MAX_FILES', 100);
if (!defined('G5_EDITOR_UPLOAD_MAX_BYTES')) define('G5_EDITOR_UPLOAD_MAX_BYTES', 200 * 1024 * 1024);
if (!defined('G5_EDITOR_UPLOAD_ADMIN_EXEMPT')) define('G5_EDITOR_UPLOAD_ADMIN_EXEMPT', true);

// 비회원 업로드를 허용하지 않으면 오류 문구를 돌려준다. 허용되면 ''.
function g5_editor_upload_access_error()
{
    global $is_member;

    if (!$is_member && !G5_EDITOR_UPLOAD_GUEST) {
        return '로그인한 회원만 에디터에 이미지를 올릴 수 있습니다.';
    }

    return '';
}

// 파일당 최대 바이트. 0 이하이면 제한하지 않는다.
function g5_editor_upload_max_file_size()
{
    return max(0, (int)G5_EDITOR_UPLOAD_FILE_MAX_SIZE);
}

// 파일 크기가 파일당 상한을 넘으면 오류 문구를 돌려준다.
function g5_editor_upload_size_error($bytes)
{
    $max = g5_editor_upload_max_file_size();
    if ($max > 0 && (int)$bytes > $max) {
        return '파일 크기가 너무 큽니다. (최대 '.round($max / 1048576, 1).'MB)';
    }

    return '';
}

// 누적 제한을 적용할 식별자. 회원은 회원 ID와 세션, 비회원은 세션과 IP 기준이다.
function g5_editor_upload_limit_keys()
{
    global $is_member, $member;

    if (session_id() == '') {
        @session_start();
    }

    $keys = array();
    if (session_id() != '') {
        $keys[] = 'session:'.session_id();
    }
    if ($is_member && isset($member['mb_id']) && $member['mb_id'] !== '') {
        $keys[] = 'member:'.$member['mb_id'];
    } else {
        $keys[] = 'ip:'.(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    }

    return $keys;
}

function g5_editor_upload_limit_dir()
{
    return G5_DATA_PATH.'/cache/editor_upload_limit';
}

function g5_editor_upload_limit_file($key)
{
    $secret = defined('G5_TOKEN_ENCRYPTION_KEY') && G5_TOKEN_ENCRYPTION_KEY ? G5_TOKEN_ENCRYPTION_KEY : G5_MYSQL_PASSWORD;
    return g5_editor_upload_limit_dir().'/'.md5($secret."\0".$key).'.php';
}

function g5_editor_upload_limit_read($path, $now)
{
    $entries = array();
    if (is_file($path)) {
        $raw = @file_get_contents($path);
        $pos = is_string($raw) ? strpos($raw, "\n") : false;
        $data = $pos === false ? false : @unserialize(substr($raw, $pos + 1));
        if (is_array($data)) {
            foreach ($data as $entry) {
                if (is_array($entry) && isset($entry[0], $entry[1], $entry[2]) && (int)$entry[0] > $now - G5_EDITOR_UPLOAD_WINDOW) {
                    $entries[] = array((int)$entry[0], max(0, (int)$entry[1]), (string)$entry[2]);
                }
            }
        }
    }

    return $entries;
}

function g5_editor_upload_limit_write($path, $entries)
{
    $content = "<?php exit; ?>\n".serialize($entries);
    if (@file_put_contents($path, $content) !== strlen($content)) {
        return false;
    }
    if (defined('G5_FILE_PERMISSION')) {
        @chmod($path, G5_FILE_PERMISSION);
    }

    return true;
}

// 기록 파일을 읽고 쓰는 동안 잡는 전역 잠금. 잠금 실패 시 false.
function g5_editor_upload_limit_lock()
{
    $dir = g5_editor_upload_limit_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, G5_DIR_PERMISSION);
        @chmod($dir, G5_DIR_PERMISSION);
    }
    $lock = @fopen($dir.'/lock', 'a+');
    if (!$lock) {
        return false;
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        return false;
    }

    return $lock;
}

function g5_editor_upload_limit_unlock($lock)
{
    @flock($lock, LOCK_UN);
    @fclose($lock);
}

// 업로드 한 건을 예약한다. 한도를 넘거나 저장소를 쓸 수 없으면 오류 문구를, 아니면 ''를 돌려준다.
// 저장에 실패한 업로드는 g5_editor_upload_release()로 예약을 되돌린다.
function g5_editor_upload_reserve($bytes, &$reservation)
{
    global $is_admin;

    $reservation = null;
    $bytes = max(0, (int)$bytes);

    $error = g5_editor_upload_size_error($bytes);
    if ($error) {
        return $error;
    }

    if (G5_EDITOR_UPLOAD_ADMIN_EXEMPT && $is_admin === 'super') {
        return '';
    }

    $max_files = (int)G5_EDITOR_UPLOAD_MAX_FILES;
    $max_bytes = (int)G5_EDITOR_UPLOAD_MAX_BYTES;
    if ($max_files <= 0 && $max_bytes <= 0) {
        return '';
    }

    $lock = g5_editor_upload_limit_lock();
    if (!$lock) {
        error_log('[g5 editor upload] limit storage is not writable: '.g5_editor_upload_limit_dir());
        return '업로드 제한 정보를 저장할 수 없어 업로드할 수 없습니다.';
    }

    $now = time();
    $id = md5(uniqid(mt_rand(), true));
    $paths = array();
    $error = '';
    foreach (g5_editor_upload_limit_keys() as $key) {
        $path = g5_editor_upload_limit_file($key);
        $entries = g5_editor_upload_limit_read($path, $now);
        $sum = 0;
        foreach ($entries as $entry) {
            $sum += $entry[1];
        }
        if ($max_files > 0 && count($entries) + 1 > $max_files) {
            $error = '업로드할 수 있는 파일 수를 초과했습니다. 잠시 후 다시 시도해 주세요.';
            break;
        }
        if ($max_bytes > 0 && $sum + $bytes > $max_bytes) {
            $error = '업로드할 수 있는 용량을 초과했습니다. 잠시 후 다시 시도해 주세요.';
            break;
        }
        $entries[] = array($now, $bytes, $id);
        $paths[$path] = $entries;
    }

    if (!$error) {
        foreach ($paths as $path => $entries) {
            if (!g5_editor_upload_limit_write($path, $entries)) {
                $error = '업로드 제한 정보를 저장할 수 없어 업로드할 수 없습니다.';
                break;
            }
        }
    }

    if ($error && $paths) {
        // 일부만 기록된 예약을 되돌린다.
        foreach (array_keys($paths) as $path) {
            g5_editor_upload_limit_remove($path, $id, $now);
        }
    } elseif (!$error) {
        $reservation = array('id' => $id, 'paths' => array_keys($paths));
        if (mt_rand(1, 100) === 1) {
            g5_editor_upload_limit_gc($now);
        }
    }

    g5_editor_upload_limit_unlock($lock);

    return $error;
}

function g5_editor_upload_limit_remove($path, $id, $now)
{
    $entries = g5_editor_upload_limit_read($path, $now);
    $kept = array();
    foreach ($entries as $entry) {
        if ($entry[2] !== $id) {
            $kept[] = $entry;
        }
    }
    if (count($kept) !== count($entries)) {
        g5_editor_upload_limit_write($path, $kept);
    }
}

// 저장에 실패한 업로드의 예약을 되돌린다.
function g5_editor_upload_release($reservation)
{
    if (!is_array($reservation) || empty($reservation['id']) || empty($reservation['paths'])) {
        return;
    }

    $lock = g5_editor_upload_limit_lock();
    if (!$lock) {
        return;
    }
    $now = time();
    foreach ($reservation['paths'] as $path) {
        if (strpos($path, g5_editor_upload_limit_dir().'/') === 0) {
            g5_editor_upload_limit_remove($path, $reservation['id'], $now);
        }
    }
    g5_editor_upload_limit_unlock($lock);
}

// 기간이 지난 기록 파일을 지운다(잠금 안에서 호출).
function g5_editor_upload_limit_gc($now)
{
    $files = glob(g5_editor_upload_limit_dir().'/*.php');
    if (!$files) {
        return;
    }
    foreach ($files as $file) {
        if (@filemtime($file) < $now - G5_EDITOR_UPLOAD_WINDOW) {
            @unlink($file);
        }
    }
}
