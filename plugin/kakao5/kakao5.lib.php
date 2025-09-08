<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_KAKAO5_PATH.'/_common.php');
require_once(G5_KAKAO5_PATH.'/kakao5_popbill.lib.php');

/*************************************************************************
**
**  알림톡 함수 모음
**
*************************************************************************/
/**
 * 프리셋 코드를 사용하여 알림톡을 전송하는 함수
 */
function send_alimtalk_preset($preset_code, array $recipient, $conditions = [])
{
    global $g5, $sender_hp, $member, $config;

    // 알림톡 사용 설정 확인
    if (empty($config['cf_kakaotalk_use'])) {
        return array('success' => false, 'msg' => '알림톡 사용이 설정되어 있지 않습니다.');
    }
    
    // 프리셋 코드로 프리셋 정보 확인
    $preset_info = get_alimtalk_preset_info($preset_code);
    if (isset($preset_info['error'])) {
        return array('success' => false, 'msg' => $preset_info['error'], 'data' => $preset_info);
    }
    $template_code = $preset_info['template_code']; // 템플릿 코드

    // 수신자 정리 (전화번호 숫자만)
    $receiver_hp = preg_replace('/[^0-9]/', '', $recipient['rcv'] ?? '');
    $receiver_nm = $recipient['rcvnm'] ?? '';

    // 수신자 정보 배열 구성
    $messages = [['rcv' => $receiver_hp, 'rcvnm' => $receiver_nm]];

    // 주문내역에서 mb_id 조회
    if (empty($conditions['mb_id']) && !empty($conditions['od_id'])) {
        $sql = "SELECT mb_id FROM {$g5['g5_shop_order_table']} WHERE od_id = '" . sql_escape_string($conditions['od_id']) . "' LIMIT 1";
        $row = sql_fetch($sql);
        if ($row && !empty($row['mb_id'])) {
            $conditions['mb_id'] = $row['mb_id'];
        } else {
            $conditions['mb_id'] = $member['mb_id'] ?? 'GUEST';
        }
    }

    // 전송요청번호 생성    
    $request_num = generate_alimtalk_request_id($conditions['mb_id'], $preset_code);

    // 전송 내역 초기 저장
    $history_id = save_alimtalk_history($preset_info['preset_id'], $template_code, $preset_info['alt_send'], $request_num, $receiver_nm, $receiver_hp, $conditions['mb_id']);

    // 템플릿 정보 조회    
    $full_template_info = '';
    if($config['cf_kakaotalk_use'] === 'popbill'){ // 팝빌
        $full_template_info = get_popbill_template_info($template_code);
    }

    // 템플릿 정보를 못 불러 올 경우 - 발송 취소
    if (is_array($full_template_info) && isset($full_template_info['error'])) {
        // 탬플릿 정보 조회 실패: 알림톡 전송내역 업데이트
        $messages = "템플릿 정보 조회 실패: ".$full_template_info['error'];
        update_alimtalk_history($history_id, ['ph_log' => $messages]);
        return array('success' => false, 'msg' => $messages, 'data' => $full_template_info);
    }

    // 템플릿 내용 변수 치환
    $content = replace_alimtalk_content_vars($full_template_info->template, $conditions);

    // 버튼 링크 치환
    $buttons = set_alimtalk_button_links($full_template_info->btns, $conditions);

    try {        
        // 알림톡 전송 정보
        $data = [
            'template_code' => $template_code,
            'sender_hp' => $sender_hp,
            'content' => $content,
            'alt_content' => $content,
            'alt_send' => ($preset_info['alt_send'] == '1') ? 'C' : null,
            'messages' => $messages,
            'reserveDT' => null,
            'request_num' => $request_num,
            'buttons' => $buttons,
            'alt_subject' => $preset_info['preset_name']
        ];
        
        $receipt_num = '';
        if ($config['cf_kakaotalk_use'] === 'popbill') { // 팝빌 전송
            $receipt_num = send_popbill_alimtalk($data);
        }
        
        // 전송 결과 처리
        if ((is_array($receipt_num) && isset($receipt_num['error'])) || empty($receipt_num)) {
            // 전송 실패: 알림톡 전송내역 업데이트
            $error_message = is_array($receipt_num) && isset($receipt_num['error']) ? $receipt_num['error'] : '알림톡 전송 결과가 비어 있습니다.';
            $messages = '알림톡 전송 실패하였습니다.\n' . $error_message;
            update_alimtalk_history($history_id, ['ph_log' => $messages, 'ph_state' => 2]);
            return array('success' => false, 'msg' => $messages, 'code' => (is_array($receipt_num) && isset($receipt_num['code']) ? $receipt_num['code'] : null));
        } else {
            // 전송 성공: 알림톡 전송내역 업데이트
            $messages = '알림톡이 정상적으로 전송되었습니다.';
            update_alimtalk_history($history_id, ['ph_receipt_num' => $receipt_num, 'ph_state' => 1, 'ph_log' => $content]);
            return array('success' => true, 'msg' => $messages, 'receipt_num' => $receipt_num);
        }
    } catch (Exception $e) {
        // 전송 오류: 알림톡 전송내역 업데이트
        $messages = '알림톡 전송 중 오류가 발생하였습니다.\n' . $e->getMessage();
        update_alimtalk_history($history_id, ['ph_log' => $messages, 'ph_state' => 2]);
        return array('success' => false, 'msg' => $messages, 'code' => $e->getCode());
    }
}

/**
 * 프리셋 코드로 프리셋 정보 확인
 */
function get_alimtalk_preset_info($preset_code)
{
    global $g5;
    
    if (empty($preset_code)) {
        return array('error' => '프리셋 코드가 입력되지 않았습니다.');
    }
    
    // 프리셋 코드로 프리셋 정보 조회
    $sql = "SELECT * FROM {$g5['kakao5_preset_table']} WHERE kp_preset_code = '" . sql_escape_string($preset_code) . "'";
    $preset = sql_fetch($sql);
                                                                                    
    if (!$preset) {
        return array('error' => '해당 프리셋 코드(' . $preset_code . ')가 존재하지 않습니다.');
    }
    
    // 활성화 상태 확인
    if ($preset['kp_active'] != '1') {
        return array('error' => '프리셋(' . $preset['kp_preset_name'] . ')이 비활성화되어 있습니다.');
    }
    
    // 템플릿 코드 확인
    if (empty($preset['kp_template_name'])) {
        return array('error' => '프리셋(' . $preset['kp_preset_name'] . ')에 템플릿이 설정되지 않았습니다.');
    }
    
    // 모든 조건을 만족하면 프리셋 정보 반환
    return array(
        'success' => true,
        'preset' => $preset,
        'preset_id' => $preset['kp_id'],
        'preset_name' => $preset['kp_preset_name'],
        'preset_code' => $preset['kp_preset_code'],
        'template_code' => $preset['kp_template_name'],
        'alt_send' => $preset['kp_alt_send'],
        'type' => $preset['kp_type']
    );
}

/**
 * 템플릿 내용 변수 치환
 */
function replace_alimtalk_content_vars($content, $conditions = [])
{
    global $g5, $kakao5_preset_variable_list;

    $replacements = [];

    // 1. 템플릿에서 변수 추출
    if (!preg_match_all('/#\{(.*?)\}/', $content, $matches) || empty($matches[1])) {
        return $content;
    }
    $found_vars = array_unique($matches[1]);

    // 2. 변수 정의 맵 캐싱
    static $var_info_map = null;
    if ($var_info_map === null) {
        $var_info_map = [];
        foreach ($kakao5_preset_variable_list as $category) {
            foreach ($category['variables'] as $var) {
                if (preg_match('/#\{(.*?)\}/', $var['name'], $match) && isset($match[1])) {
                    $var_info_map[$match[1]] = $var;
                }
            }
        }
    }

    // 3. 쿼리 맵 구성 및 치환값 우선 결정
    $query_map = [];
    $var_to_query = [];
    foreach ($found_vars as $var_name) {
        $replacement_key = "#{{$var_name}}";

        // 1순위: 변수 정의가 있고, $conditions에 column값이 있으면 바로 치환
        if (isset($var_info_map[$var_name])) {
            $var = $var_info_map[$var_name];
            $column = $var['column'];
            $table = $g5[$var['table'] ?? ''];
            $condition_key = $var['condition_key'] ?? '';

            if (isset($conditions[$column])) {
                $replacements[$replacement_key] = $conditions[$column];
                continue;
            }
            
            // 테이블명에 게시판과 같이 뒤에 붙는 변수가 있을 경우 사용
            $table_placeholder = isset($var['table_placeholder']) ? trim($var['table_placeholder'], '{}') : '';
            if ($table_placeholder && !empty($conditions[$table_placeholder])) {
                $table .= $conditions[$table_placeholder];
            }

            // 2순위: 변수정의에 따라 DB 조회 필요
            $where = '';
            if(!empty($condition_key)) {                
                if (!isset($conditions[$condition_key])) {
                    $replacements[$replacement_key] = '';
                    continue;
                }
                $cond_val = sql_escape_string($conditions[$condition_key]);
                $where = "{$condition_key} = '{$cond_val}'";
            }
            $query_key = "{$table}|{$where}";

            if (!isset($query_map[$query_key])) {
                $query_map[$query_key] = [
                    'table' => $table,
                    'where' => $where,
                    'columns' => [],
                    'is_price' => $var['is_price'] ?? false,
                ];
            }
            $query_map[$query_key]['columns'][$var_name] = $column;
            $var_to_query[$var_name] = $query_key;
            continue;
        }        

        // 4. 조건값이 없으면 조회 불가 → 빈값
        $replacements[$replacement_key] = '';
    }

    // 4. DB 조회 (필요한 경우만)
    $query_results = [];
    foreach ($query_map as $query_key => $info) {
        $table = $info['table'];
        $where = $info['where'];
        $columns = array_unique(array_values($info['columns']));
        $column_sql = implode(',', $columns);

        $sql = "SELECT {$column_sql} FROM {$table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " LIMIT 1";
        $query_results[$query_key] = sql_fetch($sql) ?: [];
    }

    // 5. DB 결과로 치환값 보완
    foreach ($found_vars as $var_name) {
        $replacement_key = "#{{$var_name}}";

        if (isset($replacements[$replacement_key])) continue; // 이미 치환된 값 있음

        if (isset($var_to_query[$var_name])) {
            $query_key = $var_to_query[$var_name];
            $column = $query_map[$query_key]['columns'][$var_name];
            $value = $query_results[$query_key][$column] ?? '';
            // is_price일경우 숫자(정수 또는 실수)라면 number_format 적용
            if (isset($var_info_map[$var_name]['is_price']) && $var_info_map[$var_name]['is_price'] && is_numeric($value) && $value !== '') {
                $value = number_format($value);
            }
            $replacements[$replacement_key] = $value;
        } else {
            $replacements[$replacement_key] = '';
        }
    }

    return strtr($content, $replacements);
}

/**
 * 버튼 링크 치환
 */
function set_alimtalk_button_links($btns, $conditions = [])
{
    // [정의] $kakao5_preset_button_links - extend/kakao5.extend.php
    global $kakao5_preset_button_links;

    $buttons = [];
    if (!empty($btns)) {
        foreach ($btns as $idx => $btn) {
            // 버튼의 u1, u2에 대해 #{...} 플레이스홀더를 찾아 알맞은 URL로 치환
            foreach (['u1', 'u2'] as $field) {
                if (isset($btn->$field)) {
                    if (preg_match('/#\{(.*?)\}/', $btn->$field, $match)) {
                        $placeholder = $match[0];
                        if (isset($kakao5_preset_button_links[$placeholder])) {
                            $url = $kakao5_preset_button_links[$placeholder]['url'];
                            // URL 내 {변수} 치환
                            if (preg_match_all('/\{(.*?)\}/', $url, $url_vars)) {
                                foreach ($url_vars[1] as $var_name) {
                                    // 치환할 값이 없으면 빈 문자열 처리
                                    $replace_val = $conditions[$var_name] ?? '';
        
                                    // URL로 쓰일 수 있으므로 안전하게 인코딩
                                    $url = str_replace('{' . $var_name . '}', urlencode($replace_val), $url);
                                }
                            }
                            $btn->$field = $url;
                        }
                    }
                }
            }
            $buttons[] = (array)$btn;
        }
    }

    return $buttons;
}

/**
 * 전송요청번호 생성 (고유성 보장)
 */
function generate_alimtalk_request_id($mb_id, $preset_code) 
{
    $prefix = substr($preset_code, 0, 1); // 사용자 구분
    $mb_hash = substr(md5($mb_id), 0, 4); // mb_id 해시 4자리 
    $dateTimeStr = date('ymdHis') . sprintf('%03d', (microtime(true) * 1000) % 1000); // 날짜(초) + 마이크로초(밀리초 3자리)
    $requestNum = "{$prefix}{$mb_hash}{$dateTimeStr}";

    return substr($requestNum, 0, 20);
}

/**
 * 알림톡 프리셋 전송 이력 저장
 */
function save_alimtalk_history($preset_id, $template_code, $alt_send, $request_num, $rcvnm, $rcv, $mb_id = '')
{
    global $g5;
    
    $sql = "INSERT INTO {$g5['kakao5_preset_history_table']} 
            (mb_id, kp_id, ph_rcvnm, ph_rcv, ph_template_code, ph_alt_send, ph_request_num, ph_send_datetime, ph_state) 
            VALUES 
            ('" . sql_escape_string($mb_id) . "', 
             '" . (int)$preset_id . "', 
             '" . sql_escape_string($rcvnm) . "',
             '" . sql_escape_string($rcv) . "',
             '" . sql_escape_string($template_code) . "',
             '" . sql_escape_string($alt_send) . "', 
             '" . sql_escape_string($request_num) . "', 
             NOW(), 
             0)";

    $result = sql_query($sql);
    
    if ($result) {
        return sql_insert_id();
    }
    
    return false;
}

/**
 * 전송내역 업데이트
 */
function update_alimtalk_history($history_id, $update_data = [])
{
    global $g5;

    if (!$history_id) {
        return false;
    }

    $set_arr = [];

    // update_data가 들어오면 해당 값들로 업데이트
    if (!empty($update_data) && is_array($update_data)) {
        foreach ($update_data as $key => $val) {
            $set_arr[] = sql_escape_string($key) . " = '" . sql_escape_string($val) . "'";
        }
    }

    // 업데이트할 내용이 없음
    if (empty($set_arr)) {
        return false;
    }

    $sql = "UPDATE {$g5['kakao5_preset_history_table']} 
            SET " . implode(', ', $set_arr) . "
            WHERE ph_id = '" . (int)$history_id . "'";

    return sql_query($sql);
}

/**
 * 알림톡용 상품명 생성 (2개 이상일 경우 '외 N건' 추가)
 */
function get_alimtalk_cart_item_name($od_id)
{
    global $g5;

    $sql = "SELECT it_name FROM {$g5['g5_shop_cart_table']} WHERE od_id = '" . sql_escape_string($od_id) . "'";
    $res = sql_query($sql);

    $names = array();
    while ($row = sql_fetch_array($res)) $names[] = $row['it_name'];    
    if (!$names) return '';
    
    return $names[0] . ($names[1] ? ' 외 ' . (count($names)-1) . '건' : '');
}

/**
 * 관리자 정보로 알림톡 발송
 *
 * @param string $tpl        템플릿 코드 (예: AD-OR01)
 * @param string $type       관리자 유형 (super|group|board)
 * @param array  $conditions 치환 변수 배열
 * @param array  $otherTypes 발송 중복 확인용 관리자 유형 ['super', 'group', 'board']
 * @return array|false       send_alimtalk_preset 반환값 또는 false
 */
function send_admin_alimtalk($tpl, $type = 'super', $conditions = [], $otherTypes = [])
{
    $admin = get_admin($type);

    // 연락처가 없으면 발송하지 않음
    if (empty($admin['mb_hp'])) return false;

    // 다른 관리자 정보가 겹치는 게 있으면 발송 안함
    if(!empty($otherTypes)){
        foreach($otherTypes as $otherType){
            // 자기 자신은 비교하지 않음
            if ($otherType == $type) continue;

            $other = get_admin($otherType, 'mb_id, mb_hp');
            if (empty($other)) continue;

            // 다른 역할과 동일 인물(또는 동일 번호)이라면 발송하지 않음
            $sameId  = !empty($admin['mb_id']) && !empty($other['mb_id']) && $admin['mb_id'] == $other['mb_id'];
            $sameHp  = !empty($other['mb_hp']) && $admin['mb_hp'] == $other['mb_hp'];

            if ($sameId || $sameHp) return false;
        }
    }

    return send_alimtalk_preset(
        $tpl,
        [
            'rcv'   => $admin['mb_hp'],
            'rcvnm' => $admin['mb_name'] ?? ''
        ],
        $conditions
    );
}