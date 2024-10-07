<?php
/**
 * API Functions
 */

use API\Database\Db;
use API\Service\ConfigService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

define('EXISTS_EXIF_EXTENSION', function_exists('exif_read_data'));

/**
 * API Response JSON
 *
 * @param Response $response
 * @param array|object $data
 * @param int $status HTTP 상태 코드
 * @return Response
 */
function api_response_json(Response $response, $data, int $status = 200)
{
    // api 에서는 json 인코딩시 php 의 백슬래시 추가를 막습니다.
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $response->getBody()->write($json);
    return $response->withStatus($status)->withAddedHeader('Content-Type', 'application/json');
}

/**
 * 리프레시 토큰 테이블 생성
 */
function create_refresh_token_table()
{
    $refresh_token_table_name = $GLOBALS['g5']['member_refresh_token_table'] ?? G5_TABLE_PREFIX . 'member_refresh_token';
    if (!table_exist_check($refresh_token_table_name)) {
        $query = "CREATE TABLE IF NOT EXISTS `$refresh_token_table_name` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NOT NULL,
                    `refresh_token` text NOT NULL,
                    `expires_at` datetime NOT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ix_member_refresh_token_mb_id` (`mb_id`),
                    KEY `ix_member_refresh_token_id` (`id`)
                    ) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        Db::getInstance()->run($query);
    }
}

/**
 * DB 테이블 있는지 확인 함수
 * @param $table_name
 * @return bool
 */
function table_exist_check($table_name)
{
    $row_count = Db::getInstance()->run("SHOW TABLES LIKE '{$table_name}' ")->rowCount();
    return $row_count === 1;
}

/**
 * 그누보드 루트 경로 및 URL 반환 함수
 * @param bool $is_root 이 함수를 호출하는 파일이 최상위 경로인지 여부
 * @param int $depth is_root 가 false 일때 해당 파일에서 최상위 경로까지의 디렉토리 깊이
 * @return array
 */
function g5_root_path($is_root = true, $depth = 1)
{
    $chroot = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], __DIR__));
    //root 경로 슬래시 변경
    if ($is_root) {
        $path = str_replace('\\', '/', $chroot . __DIR__);
    } else {
        $path = str_replace('\\', '/', dirname(__DIR__, $depth));
    }
    // 윈도우 , 리눅스 경로 호환 슬래시로 변경 , // -> / 로 변경
    $server_script_name = preg_replace('/\/+/', '/', str_replace('\\', '/', $_SERVER['SCRIPT_NAME']));
    $server_script_filename = preg_replace('/\/+/', '/', str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));
    // ~ 제거 - 리눅스에서 유저에 ~가 들어가는 경우
    $tilde_remove = preg_replace('/^\/~[^\/]+(.*)$/', '$1', $server_script_name);
    $document_root = str_replace($tilde_remove, '', $server_script_filename);
    $pattern = '/.*?' . preg_quote($document_root, '/') . '/i';
    $url_root = preg_replace($pattern, '', $path);

    $http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';

    //host 경로 정규화
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
    if (isset($_SERVER['HTTP_HOST']) && strpos($host, ':') !== false) {
        $host = preg_replace('/:[0-9]+$/', '', $host);
    }
    $host = preg_replace('/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/', '', $host);

    // 웹서버의 사용자 경로
    $user = str_replace(preg_replace($pattern, '', $server_script_filename), '', $server_script_name);

    $server_port = $_SERVER['SERVER_PORT'];
    $port = ($server_port == 80 || $server_port == 443) ? '' : ':' . $server_port;

    if ($is_root) {
        $gnuboard_root_path = __DIR__;
    } else {
        $gnuboard_root_path = dirname(__DIR__, $depth);
    }

    return [
        'path' => $gnuboard_root_path,
        'url' => "{$http}{$host}{$port}{$user}{$url_root}" // server url
    ];
}

/**
 * Moves the uploaded file to the upload directory and assigns it a unique name
 * to avoid overwriting an existing uploaded file.
 *
 * @param string $directory The directory to which the file is moved
 * @param UploadedFileInterface $uploadedFile The file uploaded file to move
 * @param string|null $basename The basename of the file to use
 *
 * @return string The filename of moved file
 * @throws \Random\RandomException
 */
function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile, ?string $basename = null)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

    // @see http://php.net/manual/en/function.random-bytes.php
    $basename = $basename ?: bin2hex(random_bytes(16) . time());
    $filename = "{$basename}.{$extension}";

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// ========================================
// 회원정보 관련 유효성 검사 함수들
// ========================================

/**
 * 관리자페이지에서 지정한 허용된 이미지 확장자 목록을 가져옵니다.
 * @return array
 */
function get_allow_image_ext()
{
    /**
     * @var array $allow_image_ext_list 이미지 확장자 목록
     */
    static $allow_image_ext_list;
    if ($allow_image_ext_list) {
        return $allow_image_ext_list;
    }

    $config = ConfigService::getConfig();
    //공백을 제거하고 배열로 변환
    $allow_image_ext_list = array_map('trim', explode('|', $config['cf_image_extension']));
    return $allow_image_ext_list;
}

/**
 * 관리자페이지에서 지정한 허용된 동영상 확장자 목록을 가져옵니다.
 * @return array
 */
function get_allow_video_ext()
{
    /**
     * @var array $allow_video_ext_list 동영상 확장자 목록
     */
    static $allow_video_ext_list;
    if ($allow_video_ext_list) {
        return $allow_video_ext_list;
    }
    $config = ConfigService::getConfig();
    //공백을 제거하고 배열로 변환
    $allow_video_ext_list = array_map('trim', explode('|', $config['cf_movie_extension']));
    return $allow_video_ext_list;
}

/**
 * 문자열의 최소 길이를 검증
 *
 * @param string $string 검증할 문자열
 * @param int $min_length
 * @return bool 최소 길이와 같거나 크면 true, 그렇지 않으면 false
 */
function has_min_length(string $string, int $min_length): bool
{
    return strlen($string) >= $min_length;
}

/**
 * 문자열이 유효한 UTF-8 인코딩을 따르는지 여부를 확인
 * @param string $str 검사할 문자열
 * @return bool 유효한 UTF-8 인코딩이면 true, 아니면 false
 */
function is_valid_utf8_string(string $str): bool
{
    return mb_detect_encoding($str, 'UTF-8', true) === 'UTF-8';
}

/**
 * 아이디 형식 검사
 * - register.lib.php 의 valid_mb_id 함수를 참고하여 작성
 * @param string $mb_id
 * @return bool 아이디 형식이면 true, 아니면 false
 */
function is_valid_mb_id(string $mb_id): bool
{
    return !preg_match('/[^0-9a-z_]+/i', $mb_id);
}

/**
 * 닉네임 형식 검사
 * - register.lib.php 의 valid_mb_nick 함수를 참고하여 작성
 * @param string $nick 닉네임
 * @return bool 닉네임 형식이면 true, 아니면 false
 */
function is_valid_mb_nick(string $nick): bool
{
    return check_string($nick, G5_HANGUL + G5_ALPHABETIC + G5_NUMERIC);
}

/**
 * 휴대폰 번호 형식 검사
 * - register.lib.php 의 valid_mb_hp 함수를 참고하여 작성
 * @param string $hp 휴대폰 번호
 * @return bool 휴대폰 번호 형식이면 true, 아니면 false
 */
function is_valid_hp(string $hp): bool
{
    $hp = preg_replace('/[^0-9]/', '', $hp);

    if (!$hp) {
        return false;
    }

    return preg_match('/^01[0-9]{8,9}$/', $hp) === 1;
}

/**
 * 이메일 형식 검사
 * - register.lib.php 의 valid_mb_email 함수를 참고하여 작성
 * @param string $email 이메일 주소
 * @return bool 이메일 형식이면 true, 아니면 false
 */
function is_valid_email(string $email): bool
{
    return (bool)preg_match('/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/', $email);
}

/**
 * 금지된 이메일 도메인인지 검사
 * - register.lib.php 의 prohibit_mb_email 함수를 참고하여 작성
 * @param string $email 이메일 주소
 * @param array $config 환경설정
 * @return bool 금지된 도메인이면 true, 아니면 false
 */
function is_prohibited_email_domain(string $email, array $config): bool
{
    list($id, $domain) = explode('@', $email);
    if (trim($domain) === '') {
        return false;
    }
    $prohibited_domains = explode("\n", trim($config['cf_prohibit_email']));
    $prohibited_domains = array_map('trim', $prohibited_domains);
    $prohibited_domains = array_map('strtolower', $prohibited_domains);
    $email_domain = strtolower($domain);

    return in_array($email_domain, $prohibited_domains);
}

/**
 * 금지된 단어인지 검사
 * - register.lib.php 의 reserve_mb_nick 함수를 참고하여 작성
 * @param string $word 검사할 단어
 * @param array $config 환경설정
 * @return bool 금지된 단어이면 true, 아니면 false
 */
function is_prohibited_word(string $word, array $config): bool
{
    $pattern = '/[\,]?' . preg_quote($word) . '/i';
    return preg_match($pattern, $config['cf_prohibit_id']);
}


// ========================================
// 기타 함수들
// ========================================

/*******************************************************************************
 * 유일한 키를 얻는다.
 *
 * 결과 :
 *
 * 년월일시분초00 ~ 년월일시분초99
 * 년(4) 월(2) 일(2) 시(2) 분(2) 초(2) 100분의1초(2)
 * 총 16자리이며 년도는 2자리로 끊어서 사용해도 됩니다.
 * 예) 2008062611570199 또는 08062611570199 (2100년까지만 유일키)
 *
 * 사용하는 곳 :
 * 1. 게시판 글쓰기시 미리 유일키를 얻어 파일 업로드 필드에 넣는다.
 * 2. 주문번호 생성시에 사용한다.
 * 3. 기타 유일키가 필요한 곳에서 사용한다.
 *******************************************************************************/
// 기존의 get_unique_id() 함수를 사용하지 않고 get_uniqid() 를 사용한다.
function get_uid()
{
    global $g5;
    $unique_table = $g5['uniqid_table'];

    Db::getInstance()->run("LOCK TABLE {$unique_table} WRITE ");

    while (1) {
        // 년월일시분초에 100분의 1초 두자리를 추가함 (1/100 초 앞에 자리가 모자르면 0으로 채움)
        $key = date('YmdHis', time()) . str_pad((int)((float)microtime() * 100), 2, '0', STR_PAD_LEFT);

        $result = Db::getInstance()->insert($unique_table, [
            'uq_id' => $key,
            'uq_ip' => $_SERVER['REMOTE_ADDR']
        ]);

        if ($result) {
            break;
        } // 쿼리가 정상이면 빠진다.

        // insert 하지 못했으면 일정시간 쉰다음 다시 유일키를 만든다.
        usleep(10000); // 100분의 1초를 쉰다
    }

    Db::getInstance()->run('UNLOCK TABLES');
    return $key;
}

/**
 * 최고관리자 여부
 */
function is_super_admin(array $config, string $mb_id)
{
    if (empty($mb_id) || !isset($config['cf_admin']) || empty($config['cf_admin'])) {
        return false;
    }
    return $config['cf_admin'] === $mb_id;
}

/**
 * 입력 값을 정리하고 제한 길이만큼 자름
 */
function sanitize_input(string $input, int $max_length, bool $strip_tags = false): string
{
    $input = substr(trim($input), 0, $max_length);
    if ($strip_tags) {
        $input = trim(strip_tags($input));
    }
    return rtrim($input, '\\');
}

/**
 * config 정보 가져오기
 */
function getConfig()
{
    return ConfigService::getConfig();
}

// hook 함수들

if (!function_exists('add_event')) {
    function add_event($tag, $func, $priority = G5_HOOK_DEFAULT_PRIORITY, $args = 0)
    {
        if ($hook = ContainerHook::getInstance()) {
            $hook->addAction($tag, $func, $priority, $args);
        }
    }
}

if (!function_exists('run_event')) {
    function run_event($tag, $arg = '')
    {
        if ($hook = ContainerHook::getInstance()) {
            $args = array();

            if (is_array($arg)
                &&
                isset($arg[0])
                &&
                is_object($arg[0])
                &&
                1 == count($arg)
            ) {
                $args[] =& $arg[0];
            } else {
                $args[] = $arg;
            }

            $num_args = func_num_args();

            for ($a = 2;$a < $num_args;$a++) {
                $args[] = func_get_arg($a);
            }

            $hook->doAction($tag, $args, false);
        }
    }
}

if (!function_exists('add_replace')) {
    function add_replace($tag, $func, int $priority = G5_HOOK_DEFAULT_PRIORITY, int $args = 1)
    {
        if ($hook = ContainerHook::getInstance()) {
            return $hook->addFilter($tag, $func, $priority, $args);
        }

        return null;
    }
}

if (!function_exists('run_replace')) {
    function run_replace($tag, $arg = '')
    {
        if ($hook = ContainerHook::getInstance()) {
            $args = array();

            if (is_array($arg)
                &&
                isset($arg[0])
                &&
                is_object($arg[0])
                &&
                1 == count($arg)
            ) {
                $args[] =& $arg[0];
            } else {
                $args[] = $arg;
            }

            $num_args = func_num_args();

            for ($a = 2;$a < $num_args;$a++) {
                $args[] = func_get_arg($a);
            }

            return $hook->apply_filters($tag, $args, false);
        }

        return null;
    }
}

if (!function_exists('delete_event')) {
    function delete_event($tag, $func, $priority = G5_HOOK_DEFAULT_PRIORITY)
    {
        if ($hook = ContainerHook::getInstance()) {
            return $hook->remove_action($tag, $func, $priority);
        }

        return null;
    }
}

if (!function_exists('delete_replace')) {
    function delete_replace($tag, $func, $priority = G5_HOOK_DEFAULT_PRIORITY)
    {
        if ($hook = ContainerHook::getInstance()) {
            return $hook->remove_filter($tag, $func, $priority);
        }

        return null;
    }
}

if (!function_exists('get_hook_datas')) {
    function get_hook_datas($type = '', $is_callback = '')
    {
        if ($hook = ContainerHook::getInstance()) {
            return $hook->get_properties($type, $is_callback);
        }

        return null;
    }
}