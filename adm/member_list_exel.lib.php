<?php
/*************************************************************************
**
**  내보내기 관련 상수 정의
**
*************************************************************************/
define('MEMBER_EXPORT_PAGE_SIZE', 10000);       // 파일당 처리할 회원 수
define('MEMBER_EXPORT_MAX_SIZE', 300000);       // 최대 처리할 회원 수
define('MEMBER_BASE_DIR', "member_list");       // 엑셀 베이스 폴더
define('MEMBER_BASE_DATE', date('YmdHis'));     // 폴더/파일명용 날짜
define('MEMBER_EXPORT_DIR', G5_DATA_PATH . "/" . MEMBER_BASE_DIR . "/" . MEMBER_BASE_DATE); // 엑셀 파일 저장 경로
define('MEMBER_LOG_DIR', G5_DATA_PATH . "/" . MEMBER_BASE_DIR . "/" . "log");               // 로그 파일 저장 경로

/*************************************************************************
**
**  공통 함수 정의
**
*************************************************************************/
/**
 * 검색 옵션 설정
 */
function get_export_config($type = null)
{
    $config = [
        'sfl_list' => [
            'mb_id'=>'아이디',
            'mb_name'=>'이름',
            'mb_nick'=>'닉네임',
            'mb_email'=>'이메일',
            'mb_tel'=>'전화번호',
            'mb_hp'=>'휴대폰번호',
            'mb_addr1'=>'주소'
        ],
        'point_cond_map' => [
            'gte'=>'≥',
            'lte'=>'≤',
            'eq'=>'='
        ],
        'intercept_list' => [
            'exclude'=>'차단회원 제외',
            'only'=>'차단회원만'
        ],
        'ad_range_list' => [
            'all'           => '수신동의 회원 전체',
            'mailling_only' => '이메일 수신동의 회원만',
            'sms_only'      => 'SMS/카카오톡 수신동의 회원만',
            'month_confirm' => date('m월').' 수신동의 확인 대상만',
            'custom_period' => '수신동의 기간 직접 입력'
        ],
    ];

    return $type ? ($config[$type] ?? []) : $config;
}

/**
 * 파라미터 수집 및 유효성 검사
 */
function get_member_export_params() 
{
    // 친구톡 양식 - 엑셀 양식에 포함할 항목
    $fieldArray = array_map('trim', explode(',',  $_GET['fields'] ?? ''));
    $vars = [];
    foreach ($fieldArray as $index => $field) {
        if(!empty($field)){
            $vars['var' . ($index + 1)] = $field;
        }
    }

    $params = [    
        'page'              => 1,
        'formatType'        => (int)($_GET['formatType'] ?? 1),
        'use_stx'           => $_GET['use_stx'] ?? 0,
        'stx_cond'          => clean_xss_tags($_GET['stx_cond'] ?? 'like'),
        'sfl'               => clean_xss_tags($_GET['sfl'] ?? ''),
        'stx'               => clean_xss_tags($_GET['stx'] ?? ''),
        'use_level'         => $_GET['use_level'] ?? 0,
        'level_start'       => (int)($_GET['level_start'] ?? 1),
        'level_end'         => (int)($_GET['level_end'] ?? 10),
        'use_date'          => $_GET['use_date'] ?? 0,
        'date_start'        => clean_xss_tags($_GET['date_start'] ?? ''),
        'date_end'          => clean_xss_tags($_GET['date_end'] ?? ''),
        'use_point'         => $_GET['use_point'] ?? 0,
        'point'             => $_GET['point'] ?? '',
        'point_cond'        => $_GET['point_cond'] ?? 'gte',
        'use_hp_exist'      => $_GET['use_hp_exist'] ?? 0,
        'ad_range_only'     => $_GET['ad_range_only'] ?? 0,
        'ad_range_type'     => clean_xss_tags($_GET['ad_range_type'] ?? 'all'),
        'ad_mailling'       => $_GET['ad_mailling'] ?? 0,
        'ad_sms'            => $_GET['ad_sms'] ?? 0,
        'agree_date_start'  => clean_xss_tags($_GET['agree_date_start'] ?? ''),
        'agree_date_end'    => clean_xss_tags($_GET['agree_date_end'] ?? ''),
        'use_intercept'     => $_GET['use_intercept'] ?? 0,
        'intercept'         => clean_xss_tags($_GET['intercept'] ?? 'exclude'),
        'vars'              => $vars,
    ];
    
    // 레벨 범위 검증
    if ($params['level_start'] > $params['level_end']) {
            [$params['level_start'] , $params['level_end']] = [$params['level_end'], $params['level_start']];
    }
    
    // 가입기간 - 날짜 범위 검증
    if ($params['use_date'] && $params['date_start'] && $params['date_end']) {
        if ($params['date_start'] > $params['date_end']) {
            [$params['date_start'] , $params['date_end']] = [$params['date_end'], $params['date_start']];
        }
    }
    
    // 수신동의기간 - 날짜 범위 검증
    if ($params['ad_range_type'] == 'custom_period' && $params['agree_date_start'] && $params['agree_date_end']) {
        if ($params['agree_date_start'] > $params['agree_date_end']) {
            [$params['agree_date_start'] , $params['agree_date_end']] = [$params['agree_date_end'], $params['agree_date_start']];
        }
    }
    
    return $params;
}

/**
 * 전체 데이터 개수 조회
 */
function member_export_get_total_count($params) 
{
    global $g5;
    
    $where = member_export_build_where($params);
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['member_table']} {$where}";
    
    $result = sql_query($sql);
    if (!$result) {
        throw new Exception("데이터 조회에 실패하였습니다. 다시 시도해주세요.");
    }
    
    $row = sql_fetch_array($result);
    return (int)$row['cnt'];
}

/**
 * WHERE 조건절 생성
 */
function member_export_build_where($params) 
{
    global $config;
    $conditions = [];
    
    // 기본 조건 - 탈퇴하지 않은 사용자
    $conditions[] = "mb_leave_date = ''";
    
    // 검색어 조건 (sql_escape_string 사용으로 보안 강화)
    if (!empty($params['use_stx']) && $params['use_stx'] === '1') {
        $sfl_list = get_export_config('sfl_list');
        $sfl = in_array($params['sfl'], array_keys($sfl_list)) ? $params['sfl'] : '';
        $stx = sql_escape_string($params['stx']);

        if(!empty($sfl) && !empty($stx)){
            if ($params['stx_cond'] === 'like') {
                $conditions[] = "{$sfl} LIKE '%{$stx}%'";
            } else {
                $conditions[] = "{$sfl} = '{$stx}'";
            }
        }
    }
    
    // 권한 조건
    if (!empty($params['use_level']) && $params['use_level'] === '1') {
        $level_start = max(1, (int)$params['level_start']);
        $level_end = min(10, (int)$params['level_end']);

        $conditions[] = "(mb_level BETWEEN {$level_start} AND {$level_end})";
    }
    
    // 가입기간 조건
    if (!empty($params['use_date']) && $params['use_date'] === '1') {
        $date_start = isset($params['date_start']) ? sql_escape_string(trim($params['date_start'])) : '';
        $date_end = isset($params['date_end']) ? sql_escape_string(trim($params['date_end'])) : '';

        if ($date_start && $date_end) {
            $conditions[] = "mb_datetime BETWEEN '{$date_start} 00:00:00' AND '{$date_end} 23:59:59'";
        } elseif ($date_start) {
            $conditions[] = "mb_datetime >= '{$date_start} 00:00:00'";
        } elseif ($date_end) {
            $conditions[] = "mb_datetime <= '{$date_end} 23:59:59'";
        }
    }
    
    // 포인트 조건
    if (!empty($params['use_point']) && $params['use_point'] === '1') {
        $point = $params['point'];
        $point_cond = $params['point_cond'];
    
        if ($point != '') {
            $point = (int)$point; // 정수로 캐스팅

            switch ($point_cond) {
                case 'lte':
                    $conditions[] = "mb_point <= {$point}";
                    break;
                case 'eq':
                    $conditions[] = "mb_point = {$point}";
                    break;
                default:
                    $conditions[] = "mb_point >= {$point}";
                    break;
            }
        }
    }
    
    // 휴대폰 번호 존재 조건
    if (!empty($params['use_hp_exist']) && $params['use_hp_exist'] === '1') {
        $conditions[] = "(mb_hp is not null and mb_hp != '')";
    }
    
    // 정보수신동의 조건
    if (!empty($params['ad_range_only']) && $params['ad_range_only'] === '1') {
        $range = $params['ad_range_type'] ?? '';

        // 공통: 마케팅 목적 수집·이용 동의 + (필요 시) 제3자 동의
        $needs_thirdparty = ($config['cf_sms_use'] !== '' || $config['cf_kakaotalk_use'] !== '');
        $thirdparty_clause = $needs_thirdparty ? " AND mb_thirdparty_agree = 1" : "";        
        $base_marketing = "mb_marketing_agree = 1{$thirdparty_clause}";

        if ($range === 'all') {        
            // 마케팅 동의 + (이메일 OR SMS 동의)
            $conditions[] = "({$base_marketing} AND (mb_mailling = 1 OR mb_sms = 1))";        
        } elseif ($range === 'mailling_only') {        
            // 마케팅 동의 + 이메일 동의
            $conditions[] = "({$base_marketing} AND mb_mailling = 1)";
        } elseif ($range === 'sms_only') {        
            // 마케팅 동의 + SMS/카카오톡 동의
            $conditions[] = "({$base_marketing} AND mb_sms = 1)";
        } elseif ($range === 'month_confirm' || $range === 'custom_period') {
            // 채널 필터 체크
            $useEmail = !empty($params['ad_mailling']);
            $useSms   = !empty($params['ad_sms']);
        
            if ($range === 'month_confirm') {
                // 23개월 전 그 달
                $start = date('Y-m-01 00:00:00', strtotime('-23 months'));
                $end   = date('Y-m-t 23:59:59', strtotime('-23 months'));
                $emailDateCond = "mb_mailling_date BETWEEN '{$start}' AND '{$end}'";
                $smsDateCond   = "mb_sms_date BETWEEN '{$start}' AND '{$end}'";
        
            } else {
                // 수신동의기간 직접 입력 - custom_period
                $date_start = $params['agree_date_start'] ?? '';
                $date_end   = $params['agree_date_end'] ?? '';

                if ($date_start && $date_end) {
                    $emailDateCond = "mb_mailling_date BETWEEN '{$date_start} 00:00:00' AND '{$date_end} 23:59:59'";
                    $smsDateCond   = "mb_sms_date BETWEEN '{$date_start} 00:00:00' AND '{$date_end} 23:59:59'";
                } elseif ($date_start) {
                    $emailDateCond = "mb_mailling_date >= '{$date_start} 00:00:00'";
                    $smsDateCond   = "mb_sms_date >= '{$date_start} 00:00:00'";
                } elseif ($date_end) {
                    $emailDateCond = "mb_mailling_date <= '{$date_end} 23:59:59'";
                    $smsDateCond   = "mb_sms_date <= '{$date_end} 23:59:59'";
                } else {
                    $emailDateCond = "mb_mailling_date <> '0000-00-00 00:00:00'";
                    $smsDateCond   = "mb_sms_date <> '0000-00-00 00:00:00'";
                }
            }
            
            if (!$useEmail && !$useSms) {     
                $conditions[] = "0=1"; // 둘 다 해제 ⇒ 결과 0건
            } else {
                // 조건 조립
                $parts = [];
                if ($useEmail) $parts[] = "(mb_mailling = 1 AND {$emailDateCond})";
                if ($useSms)   $parts[] = "(mb_sms = 1 AND {$smsDateCond})";
            
                $conditions[] = !empty($parts) ? '(' . implode(' OR ', $parts) . ')' : '';
            }
        }
    }
    
    // 차단 회원 조건
    if (!empty($params['use_intercept']) && $params['use_intercept'] === '1') {
        switch ($params['intercept']) {
            case 'exclude':
                $conditions[] = "mb_intercept_date = ''";
                break;
            case 'only':
                $conditions[] = "mb_intercept_date != ''";
                break;
        }
    } 

    return empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
}
