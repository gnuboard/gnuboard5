<?php
if (!defined('_GNUBOARD_')) exit;

// 승인 응답 중 검증에 필요한 카드 정보만 보존한다. 인증 토큰과 카드번호는 저장하지 않는다.
function shop_pg_approval_card_fields($pg)
{
    $fields = array(
        'kcp' => array('card_cd', 'card_name', 'quota', 'noinf'),
        'kcp_rest' => array('card_cd', 'card_name', 'quota', 'noinf'),
        'inicis_pc' => array('CARD_Code', 'CARD_Quota', 'CARD_Interest'),
        'inicis_mobile' => array('P_FN_CD1', 'P_CARD_ISSUER_CODE', 'P_RMESG2', 'P_CARD_INTEREST'),
        'inicis_pro' => array('P_FN_CD1', 'P_CARD_ISSUER_CODE', 'P_CARD_ISSUER_NAME', 'P_CARD_QUOTA', 'P_CARD_INTEREST'),
        'lg' => array('LGD_FINANCECODE', 'LGD_FINANCENAME', 'LGD_CARDINSTALLMONTH', 'LGD_CARDNOINTYN'),
        'nicepay' => array('CardCode', 'CardName', 'CardQuota', 'CardInterest', 'CardCl'),
        'toss' => array('issuerCode', 'acquirerCode', 'installmentPlanMonths', 'isInterestFree', 'interestPayer')
    );
    return isset($fields[$pg]) ? $fields[$pg] : array();
}

function shop_pg_approval_card_data($pg, $response)
{
    if (!is_array($response)) return array();
    if ($pg === 'toss') $response = isset($response['card']) && is_array($response['card']) ? $response['card'] : array();
    $card = array();
    foreach (shop_pg_approval_card_fields($pg) as $field) {
        if (array_key_exists($field, $response) && is_scalar($response[$field])) {
            $value = is_bool($response[$field]) ? $response[$field] : substr((string) $response[$field], 0, 100);
            // 구형 PG의 한글 인코딩 오류가 할부/무이자 응답 전체를 누락시키지 않게 한다.
            if (json_encode($value) !== false) $card[$field] = $value;
        }
    }
    return $card;
}

function shop_pg_approval_log($pg, $oid, $tid, $amount, $code, $response)
{
    if (!shop_pg_approval_card_fields($pg) || !is_scalar($oid) || !is_scalar($tid) ||
        !preg_match('/\A[A-Za-z0-9_-]{1,64}\z/', (string) $oid) || (string) $tid === '') return false;
    $table = G5_SHOP_TABLE_PREFIX.'pg_approval_log';
    $card = json_encode(shop_pg_approval_card_data($pg, $response));
    if ($card === false) return false;
    $device = defined('G5_IS_MOBILE') && G5_IS_MOBILE ? 'MOBILE' : 'PC';
    $values = array($pg, (string) $oid, substr((string) $tid, 0, 200), $device, (string)(int)$amount, substr((string)$code,0,20), $card);
    foreach ($values as $i => $value) $values[$i] = "'".sql_escape_string($value)."'";
    $result = sql_query("INSERT INTO `$table` (pg,oid,tid,device,amount,result_code,card_info,created_at) VALUES (".implode(',', $values).",'".G5_TIME_YMDHIS."')", false);
    if (!$result) error_log('PG approval log write failed: '.$pg.' oid='.(string)$oid.'; check DB upgrade');
    return (bool) $result;
}

function shop_pg_approval_admin_rows($oid)
{
    $table = G5_SHOP_TABLE_PREFIX.'pg_approval_log';
    $result = sql_query("SELECT pg,device,amount,result_code,card_info,created_at FROM `$table` WHERE oid='".sql_escape_string($oid)."' ORDER BY id DESC LIMIT 20", false);
    if (!$result) return array();
    $rows = array();
    while ($row = sql_fetch_array($result)) $rows[] = $row;
    return $rows;
}
