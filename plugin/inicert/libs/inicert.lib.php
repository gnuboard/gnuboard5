<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// KG이니시스 간편인증 결과 조회 주소로 허용하는 호스트(https, 포트 없음, 정확히 일치)
function inicert_allowed_hosts()
{
    return array('kssa.inicis.com', 'fcsa.inicis.com');
}

// 현재 설정의 간편인증 MID
function inicert_config_mid()
{
    global $config;

    if (isset($config['cf_cert_use']) && (int) $config['cf_cert_use'] === 2) {
        return 'SRA'.$config['cf_cert_kg_mid'];
    }
    return 'SRAiasTest';
}

// 인증 시작 시 가맹점 거래번호·용도·MID·시각을 세션에 저장합니다.
function inicert_save_request($m_tx_id, $purpose, $mid)
{
    set_session('ss_inicert_request', array(
        'mTxId' => (string) $m_tx_id,
        'purpose' => (string) $purpose,
        'mid' => (string) $mid,
        'time' => G5_SERVER_TIME,
    ));
}

// 결과 처리 시 저장된 요청을 꺼내고 바로 지웁니다(1회용). 없거나 용도가 다르거나 만료되면 false.
function inicert_consume_request($purpose, $ttl = 600)
{
    $saved = get_session('ss_inicert_request');
    set_session('ss_inicert_request', '');

    if (!is_array($saved) || !isset($saved['mTxId'], $saved['purpose'], $saved['mid'], $saved['time'])) return false;
    if (!is_string($saved['mTxId']) || $saved['mTxId'] === '' || $saved['purpose'] !== (string) $purpose) return false;
    $age = G5_SERVER_TIME - (int) $saved['time'];
    if ($age < 0 || $age > $ttl) return false;
    if ($saved['mid'] !== inicert_config_mid()) return false;

    return $saved;
}

// 브라우저가 보낸 결과 값과 저장된 요청을 대조합니다. 통과하면 조회에 쓸 값 배열, 아니면 오류 문구를 반환합니다.
function inicert_check_return($saved, $post)
{
    $tx_id = (isset($post['txId']) && is_string($post['txId'])) ? $post['txId'] : '';
    if (!preg_match('/\A[A-Za-z0-9_\-]{16,100}\z/', $tx_id)) return '잘못된 요청입니다.';

    // txId에 들어 있는 MID가 현재 설정 MID와 같아야 합니다.
    if (substr($tx_id, 6, strlen($saved['mid'])) !== $saved['mid']) return '인증 요청 상점 정보가 일치하지 않습니다.';

    if (isset($post['mTxId']) && (!is_string($post['mTxId']) || $post['mTxId'] !== $saved['mTxId'])) return '인증 요청 정보가 일치하지 않습니다.';

    $url = isset($post['authRequestUrl']) ? is_inicis_url_return($post['authRequestUrl'], inicert_allowed_hosts()) : '';
    if (!$url) return '잘못된 요청입니다.';

    $token = (isset($post['token']) && is_string($post['token'])) ? $post['token'] : '';
    if ($token !== '' && !preg_match('/\A[A-Za-z0-9+\/=_\-]{1,128}\z/', $token)) return '잘못된 요청입니다.';

    return array('txId' => $tx_id, 'mid' => $saved['mid'], 'url' => $url, 'token' => $token);
}

// 인증서를 검증해 결과를 조회하고 응답 형식을 확인합니다. 통과하면 응답 배열, 아니면 오류 문구를 반환합니다.
function inicert_fetch_result($req, $saved)
{
    $response = g5_tls_http_post($req['url'], json_encode(array('mid' => $req['mid'], 'txId' => $req['txId'])),
        array('Accept: application/json', 'Content-Type: application/json'));
    $res = ($response !== false) ? json_decode($response, true) : null;
    if (!is_array($res) || !isset($res['resultCode']) || !is_string($res['resultCode'])) {
        return '본인인증 결과를 확인하지 못했습니다. 잠시 후 다시 시도해 주십시오.';
    }
    if ($res['resultCode'] !== '0000') {
        $msg = (isset($res['resultMsg']) && is_string($res['resultMsg'])) ? cut_str(strip_tags(urldecode($res['resultMsg'])), 100, '') : '';
        return '코드 : '.preg_replace('/[^A-Za-z0-9]/', '', substr($res['resultCode'], 0, 10)).'  '.$msg;
    }
    foreach (array('userName', 'userPhone', 'userBirthday', 'userCi') as $field) {
        if (!isset($res[$field]) || !is_string($res[$field]) || $res[$field] === '') return '정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.';
    }
    if (isset($res['txId']) && $res['txId'] !== $req['txId']) return '정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.';
    if (isset($res['mTxId']) && $res['mTxId'] !== $saved['mTxId']) return '정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.';
    return $res;
}

// 복호화한 개인정보 형식을 확인합니다.
function inicert_valid_identity($user_name, $phone_no, $birth_day, $ci)
{
    if (!is_string($user_name) || $user_name === '' || strlen($user_name) > 90 || preg_match('/[\x00-\x1f\x7f<>]/', $user_name)) return false;
    if (!is_string($phone_no) || !preg_match('/\A01[0-9]{8,9}\z/', str_replace('-', '', $phone_no))) return false;
    if (!is_string($birth_day) || !preg_match('/\A([0-9]{4})([0-9]{2})([0-9]{2})\z/', $birth_day, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) return false;
    if (!is_string($ci) || $ci === '' || strlen($ci) > 255) return false;
    return true;
}
