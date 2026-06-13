<?php
if (!defined('_GNUBOARD_')) exit;

if (!class_exists('C_KCP_API_V2')) {
    class C_KCP_API_V2
    {
        private $site_cd;
        private $enc_key;
        private $reg_url;
        private $dec_url;
        private $last_error = '';

        public function __construct($site_cd, $enc_key, $reg_url, $dec_url)
        {
            $this->site_cd = $site_cd;
            $this->enc_key = $enc_key;
            $this->reg_url = $reg_url;
            $this->dec_url = $dec_url;
        }

        public function trade_reg($ordr_idxx, $ret_url, $web_siteid = '', $opt1 = '', $opt2 = '', $opt3 = '')
        {
            $payload = array(
                'site_cd'     => $this->site_cd,
                'ordr_idxx'   => $ordr_idxx,
                'Ret_URL'     => $ret_url,
                'web_siteid'  => $web_siteid,
                'param_opt_1' => $opt1,
                'param_opt_2' => $opt2,
                'param_opt_3' => $opt3,
            );

            $req_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($req_json === false) {
                return array(
                    'res_cd'       => 'JSON_ERROR',
                    'res_msg'      => 'KCP 거래등록 요청 데이터를 생성할 수 없습니다.',
                    'call_url'     => '',
                    'reg_cert_key' => '',
                );
            }

            $enc = Crypto_KCP_V2::encryptJson($req_json, $this->enc_key, $this->site_cd);
            if (empty($enc['encData']) || empty($enc['rv'])) {
                return array(
                    'res_cd'       => 'ENCRYPT_ERROR',
                    'res_msg'      => 'KCP 거래등록 요청 데이터를 암호화할 수 없습니다.',
                    'call_url'     => '',
                    'reg_cert_key' => '',
                );
            }

            $headers = array(
                'Content-Type: application/json',
                'site_cd: '.$this->site_cd,
                'rv: '.$enc['rv'],
            );

            $body = $this->http_post($this->reg_url, $enc['encData'], $headers);
            if ($body === false || $body === '') {
                return array(
                    'res_cd'       => 'HTTP_ERROR',
                    'res_msg'      => $this->last_error ? $this->last_error : 'KCP 거래등록 API 응답이 없습니다.',
                    'call_url'     => '',
                    'reg_cert_key' => '',
                );
            }

            $res = json_decode($body, true);
            if (!is_array($res)) {
                return array(
                    'res_cd'       => 'JSON_ERROR',
                    'res_msg'      => 'KCP 거래등록 API 응답을 해석할 수 없습니다.',
                    'call_url'     => '',
                    'reg_cert_key' => '',
                );
            }

            return array(
                'res_cd'       => isset($res['res_cd']) ? $res['res_cd'] : '',
                'res_msg'      => isset($res['res_msg']) ? $res['res_msg'] : '',
                'call_url'     => isset($res['call_url']) ? $res['call_url'] : '',
                'reg_cert_key' => isset($res['reg_cert_key']) ? $res['reg_cert_key'] : '',
            );
        }

        public function get_cert_data($reg_cert_key, $ordr_idxx)
        {
            $req_json = json_encode(array(
                'reg_cert_key' => $reg_cert_key,
                'ordr_idxx'    => $ordr_idxx,
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($req_json === false) {
                return array('res_cd' => 'JSON_ERROR', 'res_msg' => 'KCP 본인확인 결과조회 요청 데이터를 생성할 수 없습니다.');
            }

            $headers = array(
                'Content-Type: application/json',
                'site_cd: '.$this->site_cd,
            );

            $body = $this->http_post($this->dec_url, $req_json, $headers);
            if ($body === false || $body === '') {
                return array(
                    'res_cd'  => 'HTTP_ERROR',
                    'res_msg' => $this->last_error ? $this->last_error : 'KCP 본인확인 결과조회 API 응답이 없습니다.',
                );
            }

            $res = json_decode($body, true);
            if (!is_array($res)) {
                return array('res_cd' => 'JSON_ERROR', 'res_msg' => 'KCP 본인확인 결과조회 API 응답을 해석할 수 없습니다.');
            }

            $res_cd  = isset($res['res_cd']) ? $res['res_cd'] : '';
            $res_msg = isset($res['res_msg']) ? $res['res_msg'] : '';

            if ($res_cd !== '0000') {
                return array('res_cd' => $res_cd, 'res_msg' => $res_msg);
            }

            $enc_cert_data = isset($res['enc_cert_data']) ? $res['enc_cert_data'] : '';
            $rv            = isset($res['rv']) ? $res['rv'] : '';

            $plain = Crypto_KCP_V2::decryptJson($enc_cert_data, $rv, $this->enc_key, $this->site_cd);
            if ($plain === false || $plain === '') {
                return array('res_cd' => 'DECRYPT_ERROR', 'res_msg' => 'KCP 본인확인 결과 데이터를 복호화할 수 없습니다.');
            }

            $dec   = json_decode($plain, true);
            if (!is_array($dec)) {
                return array('res_cd' => 'JSON_ERROR', 'res_msg' => 'KCP 본인확인 복호화 데이터를 해석할 수 없습니다.');
            }

            return array(
                'res_cd'     => $res_cd,
                'res_msg'    => $res_msg,
                'phone_no'   => isset($dec['phone_no'])   ? $dec['phone_no']   : '',
                'user_name'  => isset($dec['user_name'])  ? $dec['user_name']  : '',
                'birth_day'  => isset($dec['birth_day'])  ? $dec['birth_day']  : '',
                'comm_id'    => isset($dec['comm_id'])    ? $dec['comm_id']    : '',
                'sex_code'   => isset($dec['sex_code'])   ? $dec['sex_code']   : '',
                'local_code' => isset($dec['local_code']) ? $dec['local_code'] : '',
                'ci'         => isset($dec['CI'])         ? $dec['CI']         : (isset($dec['ci']) ? $dec['ci'] : ''),
                'di'         => isset($dec['DI'])         ? $dec['DI']         : (isset($dec['di']) ? $dec['di'] : ''),
                'ci_url'     => isset($dec['CI_URL'])     ? urldecode($dec['CI_URL']) : '',
                'di_url'     => isset($dec['DI_URL'])     ? urldecode($dec['DI_URL']) : '',
            );
        }

        private function http_post($url, $body, $headers)
        {
            $this->last_error = '';

            $ch = curl_init($url);
            if (!$ch) {
                $this->last_error = 'cURL 초기화에 실패했습니다.';
                return false;
            }

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $resp = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($resp === false) {
                $this->last_error = 'KCP API 통신 실패: '.curl_error($ch);
            } else if ($http_code && ($http_code < 200 || $http_code >= 300)) {
                $this->last_error = 'KCP API HTTP 오류: '.$http_code;
            }
            curl_close($ch);

            if ($this->last_error) {
                return false;
            }

            return $resp;
        }
    }
}
