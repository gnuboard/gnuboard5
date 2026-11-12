<?php
if (!defined('_GNUBOARD_')) exit;

// 화면 진입 여부나 CSRF 토큰은 게시판 접근 권한을 대신하지 않는다.
function g5_board_access_error($board, $read = false, $admin = null)
{
    global $member, $is_admin, $config, $g5;

    if ($admin === null) $admin = $is_admin;
    if (empty($board['bo_table'])) return '존재하지 않는 게시판입니다.';
    $group = get_group($board['gr_id']);
    if (!empty($group['gr_use_access'])) {
        if (empty($member['mb_id'])) return '회원만 접근할 수 있는 게시판입니다.';
        $group_admin = $admin === 'super' || $admin === 'group' || (!$read && $admin === 'board');
        if (!$group_admin) {
            $gr_id = sql_escape_string($board['gr_id']);
            $mb_id = sql_escape_string($member['mb_id']);
            $row = sql_fetch(" select gr_id from {$g5['group_member_table']} where gr_id = '$gr_id' and mb_id = '$mb_id' ");
            if (empty($row['gr_id'])) return '접근 권한이 없는 게시판입니다.';
        }
    }
    if ($read && $member['mb_level'] < $board['bo_read_level']) return '글을 읽을 권한이 없습니다.';
    if (!empty($board['bo_use_cert']) && !empty($config['cf_cert_use']) && !$admin) {
        if (empty($member['mb_id'])) return '본인확인 후 이용해 주십시오.';
        if (!empty($member['mb_certify']) && strlen((string)($member['mb_dupinfo'] ?? '')) === 64) {
            return '회원정보에서 본인확인 정보를 갱신한 후 이용해 주십시오.';
        }
        if ($board['bo_use_cert'] === 'cert' && empty($member['mb_certify'])) return '본인확인 후 이용해 주십시오.';
        if ($board['bo_use_cert'] === 'adult' && empty($member['mb_adult'])) return '성인인증 후 이용해 주십시오.';
    }
    return '';
}

function g5_require_board_access($board, $read = false)
{
    $error = g5_board_access_error($board, $read);
    if ($error !== '') alert($error);
}

function g5_is_secret_write($write)
{
    return strpos((string)($write['wr_option'] ?? ''), 'secret') !== false;
}

// 이전 ss_secret_{게시판}_{스레드} 값은 인증 근거로 사용하지 않는다.
// 대상·유형·비밀번호·공개 상태·로그인 계정을 묶어 다른 글로 권한이 전파되지 않게 한다.
function g5_secret_auth_key($bo_table, $write)
{
    return 'ss_secret_v2_'.$bo_table.'_'.(int)$write['wr_id'];
}

function g5_secret_auth_signature($write)
{
    global $member;
    $password_hash = $write['wr_password'];
    if ($password_hash === '' && $write['mb_id'] !== '') {
        $author = get_member($write['mb_id']);
        $password_hash = $author['mb_password'] ?? '';
    }
    return hash('sha256', serialize(array(
        (int)$write['wr_id'], (int)$write['wr_is_comment'], (int)$write['wr_num'],
        (int)$write['wr_parent'], $write['wr_reply'], $write['mb_id'],
        $password_hash, $write['wr_option'], $write['wr_datetime'],
        (string)($member['mb_id'] ?? '')
    )));
}

function g5_grant_secret_auth($bo_table, $write)
{
    if (!empty($write['wr_id']) && g5_is_secret_write($write)) {
        set_session(g5_secret_auth_key($bo_table, $write), g5_secret_auth_signature($write));
    }
}

function g5_has_secret_auth($bo_table, $write)
{
    if (empty($write['wr_id']) || !g5_is_secret_write($write)) return false;
    $auth = get_session(g5_secret_auth_key($bo_table, $write));
    return is_string($auth) && hash_equals(g5_secret_auth_signature($write), $auth);
}

function g5_can_read_secret($board, $write)
{
    global $member, $is_admin, $g5;
    if (empty($write['wr_id']) || !empty($write['wr_is_comment'])) return false;
    if (!g5_is_secret_write($write) || $is_admin ||
        (!empty($member['mb_id']) && $write['mb_id'] === $member['mb_id'])) return true;
    if (g5_has_secret_auth($board['bo_table'], $write)) return true;

    if (!empty($write['wr_reply'])) {
        $table = $g5['write_prefix'].$board['bo_table'];
        $num = (int)$write['wr_num'];
        $root = sql_fetch(" select * from $table where wr_num = '$num' and wr_reply = '' and wr_is_comment = 0 ");
        if (!empty($root['wr_id'])) {
            // 회원 원 작성자는 관리자 답변을 읽을 수 있다.
            if (!empty($member['mb_id']) && $root['mb_id'] === $member['mb_id']) return true;
            // 비회원 원글 인증은 같은 비밀번호를 상속한 답글에만 적용한다.
            // 답글 인증에서 원글 또는 다른 답글로 역전파하지 않는다.
            if (($root['wr_password'] !== '' || $root['mb_id'] !== '') && $root['wr_password'] === $write['wr_password'] &&
                g5_has_secret_auth($board['bo_table'], $root)) return true;
        }
    }
    return false;
}

function g5_require_board_read($board, $write)
{
    g5_require_board_access($board, true);
    if (empty($write['wr_id']) || !empty($write['wr_is_comment'])) alert('게시글이 존재하지 않거나 잘못된 대상입니다.');
    if (!g5_can_read_secret($board, $write)) alert('비밀글 인증 후 이용해 주십시오.');
}

function g5_require_board_reply($board, $write)
{
    global $member, $is_admin;
    g5_require_board_read($board, $write);
    if ($member['mb_level'] < $board['bo_reply_level']) alert('글을 답변할 권한이 없습니다.');
    if (g5_is_secret_write($write) && !$is_admin &&
        (empty($write['mb_id']) || $write['mb_id'] !== $member['mb_id'])) {
        alert('비밀글에는 작성자 또는 관리자만 답변할 수 있습니다.');
    }
}

// 보조 조회는 현재 URL이 아니라 대상 게시판의 관리자 권한으로 판정한다.
function g5_board_visible($board, $read = false)
{
    global $member, $config;
    if (empty($board['bo_table'])) return false;
    $group = get_group($board['gr_id']);
    $id = (string)($member['mb_id'] ?? '');
    $admin = '';
    if ($id !== '') {
        if ($id === (string)($config['cf_admin'] ?? '')) $admin = 'super';
        elseif ($id === (string)($group['gr_admin'] ?? '')) $admin = 'group';
        elseif ($id === (string)($board['bo_admin'] ?? '')) $admin = 'board';
    }
    if ((int)($member['mb_level'] ?? 1) < (int)($board['bo_list_level'] ?? 1)) return false;
    return g5_board_access_error($board, $read, $admin) === '';
}

// RSS와 공유 캐시는 로그인 사용자에게만 허용되는 정보를 담지 않는다.
function g5_board_public($board)
{
    global $config;
    $group = get_group($board['gr_id']);
    return !empty($board['bo_table']) && empty($group['gr_use_access'])
        && (int)$board['bo_list_level'] <= 1 && (int)$board['bo_read_level'] <= 1
        && (empty($board['bo_use_cert']) || empty($config['cf_cert_use']));
}

function g5_visible_board_ids($read = false)
{
    global $g5;
    $ids = array();
    $result = sql_query(" select * from {$g5['board_table']} ");
    while ($row = sql_fetch_array($result)) {
        if (g5_board_visible($row, $read)) $ids[] = "'".sql_escape_string($row['bo_table'])."'";
    }
    return $ids ? implode(',', $ids) : "''";
}
