<?php
if (!defined('_GNUBOARD_')) exit;

// 설정하지 않은 상점은 기존 pp_cli 방식을 유지한다.
function kcp_rest_enabled()
{
    global $default;
    return isset($default['de_kcp_payment_api']) && $default['de_kcp_payment_api'] === 'rest';
}

function kcp_rest_site_code()
{
    global $default;
    if (defined('G5_KCP_REST_SITE_CODE')) return G5_KCP_REST_SITE_CODE;
    if (!empty($default['de_card_test'])) return !empty($default['de_escrow_use']) ? 'T0007' : 'T0000';
    return strlen($default['de_kcp_mid']) === 5 ? $default['de_kcp_mid'] : 'SR'.$default['de_kcp_mid'];
}

function kcp_rest_config()
{
    global $default;
    return array(
        'test' => !empty($default['de_card_test']),
        'cert' => defined('G5_KCP_REST_CERT_PATH') ? G5_KCP_REST_CERT_PATH : '',
        'key' => defined('G5_KCP_REST_PRIVATE_KEY_PATH') ? G5_KCP_REST_PRIVATE_KEY_PATH : '',
        'password' => defined('G5_KCP_REST_PRIVATE_KEY_PASSWORD') ? G5_KCP_REST_PRIVATE_KEY_PASSWORD : ''
    );
}

class G5_KCP_REST_Client
{
    var $config;

    function __construct($config) { $this->config = $config; }

    function endpoint($operation)
    {
        if ($operation === 'register') return $this->config['test'] ?
            'https://testsmpay.kcp.co.kr/trade/register.do' : 'https://smpay.kcp.co.kr/trade/register.do';
        if ($operation === 'cash_issue') return ($this->config['test'] ? 'https://stg-spl.kcp.co.kr' : 'https://spl.kcp.co.kr').'/gw/hub/v1/payment';
        if (!in_array($operation, array('payment', 'cancel'), true)) throw new Exception('Unsupported KCP operation');
        return ($this->config['test'] ? 'https://stg-spl.kcp.co.kr' : 'https://spl.kcp.co.kr').
            ($operation === 'payment' ? '/gw/enc/v1/payment' : '/gw/mod/v1/cancel');
    }

    function secret_file($path)
    {
        // URL wrapper 및 웹 루트 안의 인증서/개인키는 허용하지 않는다.
        if (!is_string($path) || $path === '' || strpos($path, '://') !== false) throw new Exception('KCP credential path');
        $real = realpath($path);
        if ($real === false || !is_file($real) || !is_readable($real) || filesize($real) > 65536) throw new Exception('KCP credential file');
        $roots = array(defined('G5_PATH') ? G5_PATH : '', isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
        foreach ($roots as $root) {
            $root = $root === '' ? false : realpath($root);
            if ($root !== false && strpos(strtolower(str_replace('\\', '/', $real)), rtrim(strtolower(str_replace('\\', '/', $root)), '/').'/') === 0) throw new Exception('KCP credential must be outside web root');
        }
        $data = file_get_contents($real);
        if ($data === false || $data === '') throw new Exception('KCP credential read');
        return $data;
    }

    function credentials($site)
    {
        if (!preg_match('/\A[A-Z0-9]{5}\z/D', $site) || !function_exists('openssl_x509_read')) throw new Exception('KCP configuration');
        $cert = $this->secret_file($this->config['cert']);
        if (!@openssl_x509_read($cert)) throw new Exception('KCP certificate');
        return array('site_cd'=>$site, 'kcp_cert_info'=>str_replace(array("\r", "\n"), '', trim($cert)));
    }

    function sign($site, $tno, $type)
    {
        $key = $this->private_key();
        if (!openssl_sign($site.'^'.$tno.'^'.$type, $signature, $key, OPENSSL_ALGO_SHA256)) throw new Exception('KCP signature');
        return base64_encode($signature);
    }

    function private_key()
    {
        $key = @openssl_pkey_get_private($this->secret_file($this->config['key']), $this->config['password']);
        if (!$key || !openssl_x509_check_private_key($this->secret_file($this->config['cert']), $key)) throw new Exception('KCP private key');
        $details = openssl_pkey_get_details($key);
        if ($details['type'] !== OPENSSL_KEYTYPE_RSA) throw new Exception('KCP RSA key required');
        return $key;
    }

    function post($operation, $data)
    {
        if (!function_exists('curl_init') || !defined('CURL_SSLVERSION_TLSv1_2')) throw new Exception('KCP requires cURL TLS 1.2');
        $body = json_encode($data);
        if ($body === false) throw new Exception('KCP JSON request');
        $ch = curl_init($this->endpoint($operation));
        curl_setopt_array($ch, array(CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$body,
            CURLOPT_HTTPHEADER=>array('Content-Type: application/json; charset=utf-8'),
            CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_TIMEOUT=>45,
            CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2,
            CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1_2, CURLOPT_FOLLOWLOCATION=>false));
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // 테스트 계측은 허용된 메타데이터만 전달한다. 전문/인증정보는 전달하지 않는다.
        if (!empty($this->config['test']) && function_exists('g5_kcp_rest_test_trace')) {
            $decoded = is_string($raw) ? json_decode($raw, true) : null;
            $trace = array('time'=>gmdate('c'), 'operation'=>$operation,
                'endpoint'=>$this->endpoint($operation), 'http_status'=>(int)$status);
            foreach (array('ordr_no','ordr_mony','tno','mod_type','mod_mny') as $field) {
                if (isset($data[$field]) && is_scalar($data[$field]) && preg_match('/\A[A-Z0-9]{1,20}\z/D', (string)$data[$field])) $trace[$field] = (string)$data[$field];
            }
            foreach (array('res_cd','Code','tno','amount') as $field) {
                if (isset($decoded[$field]) && is_scalar($decoded[$field]) && preg_match('/\A[A-Z0-9]{1,20}\z/D', (string)$decoded[$field])) $trace['response_'.$field] = (string)$decoded[$field];
            }
            g5_kcp_rest_test_trace($trace);
        }
        // 타임아웃은 승인 실패로 단정하지 않는다. 자동 재요청/pp_cli 대체 금지.
        if ($raw === false || $status !== 200 || strlen($raw) > 1048576) throw new Exception('KCP response uncertain');
        return $this->decode($operation, $raw);
    }

    function decode($operation, $raw)
    {
        $result = json_decode($raw, true);
        $field = $operation === 'register' ? 'Code' : 'res_cd';
        if (!is_array($result) || !isset($result[$field]) || !is_string($result[$field]) ||
            !preg_match('/\A[A-Z0-9]{4}\z/D', $result[$field])) throw new Exception('KCP invalid response');
        foreach ($result as $value) {
            if (!is_scalar($value) && $value !== null) throw new Exception('KCP invalid response value');
        }
        return $result;
    }

    function payment($site, $order, $amount, $type, $tran, $enc_data, $enc_info)
    {
        if (!preg_match('/\A[0-9]{1,20}\z/D', (string)$order) || !preg_match('/\A[0-9]{1,12}\z/D', (string)$amount) || $amount <= 0 ||
            !in_array($type, array('PACA','PABK','PAVC','PAMC'), true) || !preg_match('/\A[0-9]{8}\z/D', $tran) ||
            !is_string($enc_data) || $enc_data === '' || !is_string($enc_info) || $enc_info === '') throw new Exception('KCP approval input');
        $data = $this->credentials($site);
        // 승인 후 DB 오류가 나면 즉시 취소할 수 있도록 개인키도 사전에 점검한다.
        $this->private_key();
        $data += array('ordr_no'=>(string)$order, 'ordr_mony'=>(string)$amount, 'pay_type'=>$type,
            'tran_cd'=>$tran, 'enc_data'=>$enc_data, 'enc_info'=>$enc_info);
        $result = $this->post('payment', $data);
        if ($result['res_cd'] === '0000' || $result['res_cd'] === 'V000') {
            $methods = $type === 'PACA' ? array('PACA','PAKM','PANP','PATO','PASG','PASM') : array($type);
            if (!isset($result['tno'], $result['amount'], $result['pay_method']) ||
                !preg_match('/\A[0-9]{14}\z/D', (string)$result['tno']) ||
                !preg_match('/\A[0-9]{1,12}\z/D', (string)$result['amount']) || (string)$result['amount'] !== (string)$amount ||
                !in_array($result['pay_method'], $methods, true) || ($result['res_cd'] === 'V000' && $type !== 'PAVC')) throw new Exception('KCP approval mismatch');
        }
        return $result;
    }

    function cancel($site, $data)
    {
        if (!isset($data['tno'], $data['mod_type']) || !preg_match('/\A[0-9]{14}\z/D', $data['tno'])) throw new Exception('KCP cancel input');
        if (in_array($data['mod_type'], array('RN07','STPA'), true)) $data['mod_type'] = 'STPC';
        if (!in_array($data['mod_type'], array('STSC','STPC','STE1','STE2','STE3','STE4','STE5','STE9'), true)) throw new Exception('KCP cancel type');
        if ($data['mod_type'] === 'STPC') {
            foreach (array('mod_mny','rem_mny') as $field) {
                if (!isset($data[$field]) || !preg_match('/\A[0-9]{1,12}\z/D', (string)$data[$field]) || $data[$field] <= 0) throw new Exception('KCP cancel amount');
            }
            if ($data['mod_mny'] > $data['rem_mny']) throw new Exception('KCP cancel balance');
            if (isset($data['tax_flag']) && $data['tax_flag'] === 'TG03') {
                $sum = 0;
                foreach (array('mod_tax_mny','mod_vat_mny','mod_free_mny') as $field) {
                    if (!isset($data[$field]) || !preg_match('/\A[0-9]{1,12}\z/D', (string)$data[$field])) throw new Exception('KCP cancel tax');
                    $sum += $data[$field];
                }
                if ($sum != $data['mod_mny']) throw new Exception('KCP cancel tax total');
            }
        }
        $data = array_merge($data, $this->credentials($site));
        $data['kcp_sign_data'] = $this->sign($site, $data['tno'], $data['mod_type']);
        $result = $this->post('cancel', $data);
        if ($result['res_cd'] === '0000') {
            if (!isset($result['tno']) || $result['tno'] !== $data['tno']) throw new Exception('KCP cancel mismatch');
            if ($data['mod_type'] === 'STPC') {
                if (!isset($result['mod_mny'], $result['rem_mny']) ||
                    !preg_match('/\A[0-9]{1,12}\z/D', (string)$result['mod_mny']) ||
                    !preg_match('/\A[0-9]{1,12}\z/D', (string)$result['rem_mny']) ||
                    (string)$result['mod_mny'] !== (string)$data['mod_mny'] ||
                    $result['rem_mny'] != $data['rem_mny'] - $data['mod_mny']) throw new Exception('KCP cancel result amount');
                $result['panc_mod_mny'] = $result['mod_mny'];
                $result['panc_rem_mny'] = $result['rem_mny'];
            }
        }
        return $result;
    }

    function cash_issue($site, $input)
    {
        foreach (array('amt_tot','amt_sup','amt_tax') as $field) {
            if (!isset($input[$field]) || !preg_match('/\A[0-9]{1,12}\z/D', (string)$input[$field])) throw new Exception('KCP cash amount');
        }
        if ($input['amt_tot'] <= 0 || $input['amt_sup'] + $input['amt_tax'] != $input['amt_tot'] || !empty($input['amt_svc'])) throw new Exception('KCP cash total');
        if (!isset($input['tr_code'], $input['id_info'], $input['trad_time'], $input['ordr_idxx']) ||
            !in_array($input['tr_code'], array('0','1'), true) ||
            !preg_match('/\A[0-9]{10,19}\z/D', $input['id_info']) ||
            !preg_match('/\A[0-9]{14}\z/D', $input['trad_time']) ||
            !preg_match('/\A[0-9A-Za-z_.-]{1,20}\z/D', $input['ordr_idxx'])) throw new Exception('KCP cash input');
        $data = $this->credentials($site);
        // 저장 실패 시 발급을 취소할 수 있는지 먼저 확인한다.
        $this->private_key();
        $data += array('pay_method'=>'CASH', 'user_type'=>'PGNW', 'corp_type'=>'0');
        foreach (array('trad_time','tr_code','id_info','amt_tot','amt_sup','amt_tax','ordr_idxx','good_name','buyr_name','buyr_mail') as $field) {
            $data[$field] = isset($input[$field]) ? (string)$input[$field] : '';
        }
        return $this->cash_result($this->post('cash_issue', $data));
    }

    function cash_cancel($site, $cash_no)
    {
        if (!is_string($cash_no) || !preg_match('/\A[0-9]{14}\z/D', $cash_no)) throw new Exception('KCP cash cancel input');
        $data = $this->credentials($site);
        $data += array('mod_type'=>'STSC', 'mod_gubn'=>'MG01', 'mod_value'=>$cash_no,
            'kcp_sign_data'=>$this->sign($site, $cash_no, 'STSC'));
        $result = $this->cash_result($this->post('cancel', $data));
        if ($result['res_cd'] === '0000' && $result['cash_no'] !== $cash_no) throw new Exception('KCP cash cancel mismatch');
        return $result;
    }

    function cash_result($result)
    {
        if ($result['res_cd'] === '0000') {
            foreach (array('cash_no'=>14, 'receipt_no'=>9, 'app_time'=>14) as $field=>$length) {
                if (!isset($result[$field]) || !preg_match('/\A[0-9]{'.$length.'}\z/D', (string)$result[$field])) throw new Exception('KCP cash response');
            }
        }
        return $result;
    }

    function register($data)
    {
        $result = $this->post('register', $data);
        if ($result['Code'] === '0000') {
            $host = $this->config['test'] ? 'testsmpay.kcp.co.kr' : 'smpay.kcp.co.kr';
            if (empty($result['approvalKey']) || empty($result['PayUrl']) ||
                !preg_match('~\Ahttps://'.preg_quote($host, '~').'/[^\s,]*\z~D', $result['PayUrl']) ||
                preg_match('/[\r\n,]/', $result['approvalKey'])) throw new Exception('KCP registration response');
        }
        return $result;
    }
}

// pp_cli 호출부의 결과 인코딩 계약(CP949)을 유지한다. API 전문은 UTF-8이다.
function kcp_rest_legacy_result($result)
{
    if (isset($result['easypoint_mny'])) $result['kakaomny_mny'] = $result['easypoint_mny'];
    if (isset($result['app_easypoint_time'])) {
        $result['app_kakaomny_time'] = $result['app_easypoint_time'];
        if (empty($result['app_time'])) $result['app_time'] = $result['app_easypoint_time'];
    }
    foreach ($result as $name => $value) {
        $result[$name] = iconv('UTF-8', 'CP949//TRANSLIT', (string)$value);
    }
    return $result;
}

function kcp_rest_fields($raw)
{
    $data = array();
    foreach (explode(chr(31), $raw) as $field) {
        $pair = explode('=', $field, 2);
        if (count($pair) === 2) $data[$pair[0]] = $pair[1];
    }
    return $data;
}

function kcp_rest_bridge($legacy, $site, $tran, $order)
{
    global $order_price, $pp, $od_settle_case, $pp_settle_case;
    try {
        $client = new G5_KCP_REST_Client(kcp_rest_config());
        if ($tran !== '00200000' && empty($legacy->m_encx_data)) throw new Exception('KCP unsupported transaction');
        if ($tran === '00200000') {
            $data = kcp_rest_fields($legacy->m_modx_data);
            foreach ($data as $name => $value) {
                if (!preg_match('//u', $value)) $data[$name] = iconv('CP949', 'UTF-8', $value);
            }
            $result = $client->cancel($site, $data);
        } else {
            $amount = isset($pp['pp_price']) ? (int)$pp['pp_price'] : (int)$order_price;
            $methods = array('신용카드'=>'PACA','간편결제'=>'PACA','계좌이체'=>'PABK','가상계좌'=>'PAVC','휴대폰'=>'PAMC');
            $settle = isset($pp['pp_price']) ? $pp_settle_case : $od_settle_case;
            if (!isset($methods[$settle]) || (string)get_session('ss_order_id') !== (string)$order) throw new Exception('KCP order context');
            $result = $client->payment($site, $order, $amount, $methods[$settle], $tran, $legacy->m_encx_data, $legacy->m_encx_info);
        }
    } catch (Exception $e) {
        // 인증서, 암호화 전문, 개인정보, cURL 상세 오류를 출력/기록하지 않는다.
        $result = array('res_cd'=>'R999', 'res_msg'=>'KCP REST 처리 결과를 확인할 수 없습니다. 관리자에게 결제 내역 확인을 요청해 주십시오.');
    }
    $legacy->m_res_data = kcp_rest_legacy_result($result);
    $legacy->m_res_cd = $legacy->m_res_data['res_cd'];
    $legacy->m_res_msg = isset($legacy->m_res_data['res_msg']) ? $legacy->m_res_data['res_msg'] : '';
}
