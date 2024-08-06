<?php
/**
 * API Functions
 * TODO: 용도에 맞게 함수들을 별도의 파일로 분리하거나 클래스로 만들어 관리하는 것이 좋을 것 같다.
 */

use API\Database\Db;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

// ========================================
// API Helper Functions
// ========================================

/**
 * API Response JSON
 *
 * @param Response $response
 * @param array|object $data
 * @param int $status
 * @return Response
 */
function api_response_json(Response $response, $data, int $status = 200)
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($json);
    $new_response = $response->withStatus($status);
    return $new_response->withAddedHeader('Content-Type', 'application/json');
}

/**
 * Create a refresh token table
 */
function create_refresh_token_table()
{
    global $g5;

    if (isset($g5['member_refresh_token_table'])) {
        if (!table_exist_check($g5['member_refresh_token_table'])) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$g5['member_refresh_token_table']}` (
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
            Db::getInstance()->run($sql);
        }
    }
}

/**
 * 테이블 체크함수
 * @param $table_name
 * @return bool
 */
function table_exist_check($table_name)
{
    $stmt = Db::getInstance()->run("SHOW TABLES LIKE '{$table_name}' ");
    if ($stmt->rowCount() === 1) {
        return true;
    }
    return false;
}

/**
 * 그누보드 루트 경로 및 URL 반환 함수
 * @return array
 */
function g5_root_path()
{
    $chroot = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], __DIR__));
    $path = str_replace('\\', '/', $chroot . __DIR__);
    
    //root 경로 정규화
    // 윈도우 , 리눅스 경로 호환 슬레시로 변경 , // -> / 로 변경
    $server_script_name = preg_replace('/\/+/', '/', str_replace('\\', '/', $_SERVER['SCRIPT_NAME']));
    $server_script_filename = preg_replace('/\/+/', '/', str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));
    // ~ 제거 - 리눅스에서 유저에 ~가 들어가는 경우
    $tilde_remove = preg_replace('/^\/~[^\/]+(.*)$/', '$1', $server_script_name);
    $document_root = str_replace($tilde_remove, '', $server_script_filename);
    $pattern = '/.*?' . preg_quote($document_root, '/') . '/i';
    $root = preg_replace($pattern, '', $path);
    
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
    
    $gnuboard_root_path = dirname(__DIR__, 1);
    return [
        'path' => $gnuboard_root_path,
        'url' => "{$http}{$host}{$port}{$user}{$root}" // server url
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
 */
function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile, string $basename = null)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

    // see http://php.net/manual/en/function.random-bytes.php
    $basename = $basename ?: bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// ========================================
// 회원정보 관련 유효성 검사 함수들
// ========================================

/**
 * 문자열의 최소 길이를 검증
 * 
 * @param string $string 검증할 문자열
 * @param int $minLength 최소 길이
 * @return string 최소 길이와 같거나 크면 true, 그렇지 않으면 false
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
    return iconv('UTF-8', 'UTF-8//IGNORE', $str) === $str;
}

/**
 * 아이디 형식 검사
 * - register.lib.php 의 valid_mb_id 함수를 참고하여 작성
 * @param string $mb_id
 * @return bool 아이디 형식이면 true, 아니면 false
 */
function is_valid_mb_id(string $mb_id): bool
{
    return (bool)!preg_match("/[^0-9a-z_]+/i", $mb_id);
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
    $hp = preg_replace("/[^0-9]/", "", $hp);

    if (!$hp) {
        return false;
    }

    return preg_match("/^01[0-9]{8,9}$/", $hp) === 1;
}

/**
 * 이메일 형식 검사
 * - register.lib.php 의 valid_mb_email 함수를 참고하여 작성
 * @param string $email 이메일 주소
 * @return bool 이메일 형식이면 true, 아니면 false
 */
function is_valid_email(string $email): bool
{
    return (bool)preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $email);
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
    list($id, $domain) = explode("@", $email);
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
    $pattern = "/[\,]?" . preg_quote($word) . "/i";
    return preg_match($pattern, $config['cf_prohibit_id']);
}

// ========================================
// 메일 발송 관련 함수들
// ========================================

/**
 * 임시비밀번호 메일 발송
 * @param array $config  환경설정
 * @param array $member  회원정보
 * @param string $mb_nonce  인증용 난수
 * @param string $change_password  변경될 비밀번호
 * @return void
 */
function send_reset_password_mail(array $config, array $member, string $mb_nonce, string $change_password)
{
    // 인증 링크 생성
    $href = G5_BBS_URL . '/password_lost_certify.php?mb_no=' . $member['mb_no'] . '&amp;mb_nonce=' . $mb_nonce;

    $subject = "[" . $config['cf_title'] . "] 요청하신 회원정보 찾기 안내 메일입니다.";

    $content = "";
    $content .= '<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">';
    $content .= '<div style="border:1px solid #dedede">';
    $content .= '<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">';
    $content .= '회원정보 찾기 안내';
    $content .= '</h1>';
    $content .= '<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">';
    $content .= '<a href="' . G5_URL . '" target="_blank">' . $config['cf_title'] . '</a>';
    $content .= '</span>';
    $content .= '<p style="margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
    $content .= addslashes($member['mb_name']) . " (" . addslashes($member['mb_nick']) . ")" . " 회원님은 " . G5_TIME_YMDHIS . " 에 회원정보 찾기 요청을 하셨습니다.<br>";
    $content .= '저희 사이트는 관리자라도 회원님의 비밀번호를 알 수 없기 때문에, 비밀번호를 알려드리는 대신 새로운 비밀번호를 생성하여 안내 해드리고 있습니다.<br>';
    $content .= '아래에서 변경될 비밀번호를 확인하신 후, <span style="color:#ff3061"><strong>비밀번호 변경</strong> 링크를 클릭 하십시오.</span><br>';
    $content .= '비밀번호가 변경되었다는 인증 메세지가 출력되면, 홈페이지에서 회원아이디와 변경된 비밀번호를 입력하시고 로그인 하십시오.<br>';
    $content .= '로그인 후에는 정보수정 메뉴에서 새로운 비밀번호로 변경해 주십시오.';
    $content .= '</p>';
    $content .= '<p style="margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
    $content .= '<span style="display:inline-block;width:100px">회원아이디</span> ' . $member['mb_id'] . '<br>';
    $content .= '<span style="display:inline-block;width:100px">변경될 비밀번호</span> <strong style="color:#ff3061">' . $change_password . '</strong>';
    $content .= '</p>';
    $content .= '<a href="' . $href . '" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">비밀번호 변경</a>';
    $content .= '</div>';
    $content .= '</div>';

    mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $member['mb_email'], $subject, $content, 1);
}



/**
 * FIXME: API에 맞게 수정 필요하다.
 */
function send_write_mail(array $config, array $board, int $wr_id, string $w, string $wr_subject, string $wr_content, string $html)
{
    // 관리자의 정보를 얻고
    $super_admin = get_admin('super');
    $group_admin = get_admin('group');
    $board_admin = get_admin('board');

    $wr_subject = get_text(stripslashes($wr_subject));

    $tmp_html = 0;
    if (strstr($html, 'html1'))
        $tmp_html = 1;
    else if (strstr($html, 'html2'))
        $tmp_html = 2;

    $wr_content = conv_content(conv_unescape_nl(stripslashes($wr_content)), $tmp_html);

    $warr = array('' => '입력', 'u' => '수정', 'r' => '답변', 'c' => '코멘트', 'cu' => '코멘트 수정');
    $str = $warr[$w];

    $subject = '[' . $config['cf_title'] . '] ' . $board['bo_subject'] . ' 게시판에 ' . $str . '글이 올라왔습니다.';

    $link_url = get_pretty_url($board['bo_table'], $wr_id);

    include_once(G5_LIB_PATH . '/mailer.lib.php');

    ob_start();
    include_once('./write_update_mail.php');
    $content = ob_get_clean();

    $array_email = array();
    // 게시판관리자에게 보내는 메일
    if ($config['cf_email_wr_board_admin']) $array_email[] = $board_admin['mb_email'];
    // 게시판그룹관리자에게 보내는 메일
    if ($config['cf_email_wr_group_admin']) $array_email[] = $group_admin['mb_email'];
    // 최고관리자에게 보내는 메일
    if ($config['cf_email_wr_super_admin']) $array_email[] = $super_admin['mb_email'];

    // 원글게시자에게 보내는 메일
    if ($config['cf_email_wr_write']) {
        if ($w == '')
            $wr['wr_email'] = $wr_email;

        $array_email[] = $wr['wr_email'];
    }

    // 옵션에 메일받기가 체크되어 있고, 게시자의 메일이 있다면
    if (isset($wr['wr_option']) && isset($wr['wr_email'])) {
        if (strstr($wr['wr_option'], 'mail') && $wr['wr_email'])
            $array_email[] = $wr['wr_email'];
    }

    // 중복된 메일 주소는 제거
    $unique_email = array_unique($array_email);
    $unique_email = run_replace('write_update_mail_list', array_values($unique_email), $board, $wr_id);

    for ($i = 0; $i < count($unique_email); $i++) {
        mailer($wr_name, $wr_email, $unique_email[$i], $subject, $content, 1);
    }
}

// ========================================
// 기타 함수들
// ========================================

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
    return preg_replace("#[\\\]+$#", "", $input);
}

/**
 * config 정보 가져오기
 */
function get_gnuconfig()
{
    $config_table = $GLOBALS['g5']['config_table'];
    return Db::getInstance()->run("SELECT * FROM {$config_table}")->fetch();
}

/**
 * 추천/비추천 단어 반환
 */
function get_good_word(string $good_type)
{
    return $good_type === 'good' ? '추천' : '비추천';
}