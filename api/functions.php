<?php

use API\Auth\JwtTokenManager;
use API\EnvironmentConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

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
        if (!sql_query(" DESCRIBE {$g5['member_refresh_token_table']} ", false)) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$g5['member_refresh_token_table']}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NOT NULL,
                    `refresh_token` text NOT NULL,
                    `expires_at` datetime NOT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `refresh_token` (`refresh_token`) USING HASH,
                    KEY `ix_member_refresh_token_mb_id` (`mb_id`),
                    KEY `ix_member_refresh_token_id` (`id`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
            sql_query($sql);
        }
    }
}


/**
 * Create JWT token
 */
function create_token(string $type, array $add_claim = array())
{
    $env_config = new EnvironmentConfig();
    $token_info = new JwtTokenManager($env_config, $type);

    $payload = [
        'iss' => $env_config->auth_issuer,
        'aud' => $env_config->auth_audience,
        'iat' => time(),
        'nbf' => time(),
        'exp' => time() + (60 * $token_info->expire_minutes()),
    ];
    $payload = array_merge($payload, $add_claim);
    return JWT::encode($payload, $token_info->secret_key(), $token_info->algorithm);
}

/**
 * Decode JWT token
 */
function decode_token(string $type, string $token, stdClass $headers = null)
{
    $token_info = new JwtTokenManager(new EnvironmentConfig(), $type);

    /**
     * You can add a leeway to account for when there is a clock skew times between
     * the signing and verifying servers. It is recommended that this leeway should
     * not be bigger than a few minutes.
     *
     * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
     */
    // JWT::$leeway = 60; // $leeway in seconds
    return JWT::decode($token, new Key($token_info->secret_key(), $token_info->algorithm), $headers);
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
    $content = ob_get_contents();
    ob_end_clean();

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
