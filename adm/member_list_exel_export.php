<?php
$sub_menu = "200400";
require_once './_common.php';
require_once './member_list_exel.lib.php'; // 회원관리파일 공통 라이브러리 (상수, 검색 옵션 설정, SQL WHERE 등)
include_once(G5_LIB_PATH.'/PHPExcel.php');

check_demo();
auth_check_menu($auth, $sub_menu, 'w');

ini_set('memory_limit', '-1');
session_write_close(); // 세션 종료 및 잠금 해제 (백그라운드 작업을 위해 필요)

// 파라미터 수집 및 유효성 검사
$params = get_member_export_params();
if (!$params || !is_array($params)) {
    member_export_send_progress("error", "데이터가 올바르게 전달되지 않아 작업에 실패하였습니다.");
    member_export_write_log([], ['success' => false, 'error' => '데이터가 올바르게 전달되지 않아 작업에 실패하였습니다.']);
    exit;
}

// 기존 생성된 엑셀 파일 삭제 - LOG 및 오늘 날짜 폴더 제외
$resultExcelDelete = member_export_delete();

// 서버 전송 이벤트(SSE)를 위한 헤더 설정
member_export_set_sse_headers();

// 모드 확인 
$mode = $_GET['mode'] ?? '';
if ($mode !== 'start') {
    member_export_send_progress("error", "잘못된 요청 입니다.");
    member_export_write_log($params, ['success' => false, 'error' => '잘못된 요청 입니다.']);
    exit;
}

/**
 * 회원 내보내기 처리 실행 (예외 처리 포함)
 */
try {
    main_member_export($params);
}
catch (Exception $e)
{
    // 에러 로그 저장 및 SSE 에러 전송
    error_log("[Member Export Error] " . $e->getMessage());
    member_export_send_progress("error", $e->getMessage());
    member_export_write_log($params, ['success' => false, 'error' => $e->getMessage()]);
}

/**
 * 메인 내보내기 프로세스
 */
function main_member_export($params) 
{
    $total = member_export_get_total_count($params);

    if($total > MEMBER_EXPORT_MAX_SIZE){
        throw new Exception("엑셀 다운로드 가능 범위(최대 " . number_format(MEMBER_EXPORT_MAX_SIZE) . "건)를 초과했습니다.<br>조건을 추가로 설정하신 후 다시 시도해 주세요.");
    }

    if($total <= 0){
        throw new Exception("조회된 데이터가 없어 엑셀 파일을 생성할 수 없습니다.<br>조건을 추가로 설정하신 후 다시 시도해 주세요.");
    }

    $fileName = 'member_'.MEMBER_BASE_DATE;
    $fileList = [];
    $zipFileName = '';
    
    if ($total > MEMBER_EXPORT_PAGE_SIZE) {
        // 대용량 데이터 - 분할 처리
        $pages = (int)ceil($total / MEMBER_EXPORT_PAGE_SIZE);
        member_export_send_progress("progress", "", 2, $total, 0, $pages, 0);
        
        for ($i = 1; $i <= $pages; $i++) {
            $params['page'] = $i;
            
            member_export_send_progress("progress", "", 2, $total, ($pages == $i ? $total : $i * MEMBER_EXPORT_PAGE_SIZE), $pages, $i);            
            try {
                $data = member_export_get_data($params);
                $fileList[] = member_export_create_excel($data, $fileName, $i, $params['formatType']);                
            } catch (Exception $e) {
                throw new Exception("총 {$pages}개 중 {$i}번째 파일을 생성하지 못했습니다<br>" . $e->getMessage());
            }
        }
        
        // 압축 파일 생성
        if (count($fileList) > 1) {
            member_export_send_progress("zipping", "", 2, $total, $total, $pages, $i);                
            $zipResult = member_export_create_zip($fileList, $fileName); // 압축 파일 생성

            if($zipResult['error']){
                member_export_write_log($params, ['success' => false, 'error' => $zipResult['error']]);
                member_export_send_progress("zippingError", $zipResult['error']);
            }
            
            if ($zipResult && $zipResult['result']) {
                member_export_delete($fileList); // 압축 후 엑셀 파일 제거
                $zipFileName = $zipResult['zipFile'];
            }
        }
        
    } else {
        // 소용량 데이터 - 단일 파일
        member_export_send_progress("progress", "", 1, $total, 0);                
        $data = member_export_get_data($params);
        member_export_send_progress("progress", "", 1, $total, $total/2);                
        $fileList[] = member_export_create_excel($data, $fileName, 0, $params['formatType']);
        member_export_send_progress("progress", "", 1, $total, $total);                
    }
    
    member_export_write_log($params, ['success' => true, 'total' => $total, 'files' => $fileList, 'zip' => $zipFileName ?? null]);
    member_export_send_progress("done", "", 2, $total, $total, $pages, $pages, $fileList, $zipFileName);                
}

/**
 * 진행률 전송
 */
function member_export_send_progress($status, $message = "", $downloadType = 1, $total = 1, $current = 1, $totalChunks = 1, $currentChunk = 1, $files = [], $zipFile = '') 
{
    // 연결 상태 확인
    if (connection_aborted()) return;
    
    $data = [
        'status' => $status,
        'message' => $message,
        'downloadType' => $downloadType,
        'total' => $total,
        'current' => $current,
        'totalChunks' => $totalChunks,
        'currentChunk' => $currentChunk,
        'files' => $files,
        'zipFile' => $zipFile,
        'filePath' =>  G5_DATA_URL . "/" . MEMBER_BASE_DIR . "/" . MEMBER_BASE_DATE,
    ];
    
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 더 안정적인 플러시
    if (ob_get_level()) ob_end_flush();
    flush();
}

/**
 * 엑셀 내보내기 설정
 */
function member_export_get_config($type) 
{
    $configs = [
        1 => [
            'title'   => ["회원관리파일(일반)"],
            'headers' => ['아이디', '이름', '닉네임', '휴대폰번호', '전화번호', '이메일', '주소', '회원권한', '포인트', '가입일', '차단', 
                            '광고성 이메일 수신동의', '광고성 이메일 동의일자', '광고성 SMS/카카오톡 수신동의', '광고성 SMS/카카오톡 동의일자', 
                            '마케팅목적의개인정보수집및이용동의', '마케팅목적의개인정보수집및이용동의일자', '개인정보제3자제공동의', '개인정보제3자제공동의일자'],
            'fields'  => ['mb_id', 'mb_name', 'mb_nick', 'mb_hp', 'mb_tel', 'mb_email', 'mb_addr1', 'mb_level', 'mb_point', 'mb_datetime', 'mb_intercept_date',
                            'mb_mailling','mb_mailling_date', 'mb_sms','mb_sms_date', 'mb_marketing_agree', 
                            'mb_marketing_date', 'mb_thirdparty_agree', 'mb_thirdparty_date'],
            'widths'  => [20, 20, 20, 20, 20, 30, 30, 10, 15, 25, 10, 20, 25, 20, 25, 20, 25, 20, 25],
        ],
        2 => [
            'title'   => ["회원관리파일(팝빌)"],
            'headers' => ['휴대폰번호', '이름', '변수1', '변수2', '변수3'],
            'fields'  => ['mb_hp', 'mb_name'],
            'widths'  => [20, 15, 30, 30, 30],
        ],
    ];
    
    return isset($configs[$type]) ? $configs[$type] : $configs[1];
}

/**
 * SSE 헤더 설정
 */
function member_export_set_sse_headers() 
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
    
    if (ob_get_level()) ob_end_flush();
    ob_implicit_flush(true);
}

/**
 * 엑셀 컬럼 문자 반환
 */
function member_export_column_char($i) 
{
    return chr(65 + $i);
}

/**
 * 회원 데이터 조회
 */
function member_export_get_data($params) 
{
    global $g5;

    $config = member_export_get_config($params['formatType']);
    $fields = $config['fields'];

    // 팝빌 타입인 경우 var 추가
    if ($params['formatType'] == 2 && !empty($params['vars'])) {
        $fields = array_merge($fields, array_values($params['vars']));
    }

    $fields = array_unique($fields);

    // SQL 변환 맵 (가공이 필요한 필드만 정의)
    $sqlTransformMap = [
        'mb_datetime' => "IF(mb_datetime = '0000-00-00 00:00:00', '', mb_datetime) AS mb_datetime",
        'mb_intercept_date' => "IF(mb_intercept_date != '', '차단됨', '정상') AS mb_intercept_date",
        'mb_sms' => "IF(mb_sms = '1', '동의', '미동의') AS mb_sms",
        'mb_sms_date' => "IF(mb_sms != '1' OR mb_sms_date = '0000-00-00 00:00:00', '', mb_sms_date) AS mb_sms_date",
        'mb_mailling' => "IF(mb_mailling = '1', '동의', '미동의') AS mb_mailling",
        'mb_mailling_date' => "IF(mb_mailling != '1' OR mb_mailling_date = '0000-00-00 00:00:00', '', mb_mailling_date) AS mb_mailling_date",
        'mb_marketing_agree' => "IF(mb_marketing_agree = '1', '동의', '미동의') AS mb_marketing_agree",
        'mb_marketing_date' => "IF(mb_marketing_agree != '1' OR mb_marketing_date = '0000-00-00 00:00:00', '', mb_marketing_date) AS mb_marketing_date",
        'mb_thirdparty_agree' => "IF(mb_thirdparty_agree = '1', '동의', '미동의') AS mb_thirdparty_agree",
        'mb_thirdparty_date' => "IF(mb_thirdparty_agree != '1' OR mb_thirdparty_date = '0000-00-00 00:00:00', '', mb_thirdparty_date) AS mb_thirdparty_date",
    ];

    // SQL 필드 생성
    $sqlFields = [];
    foreach ($fields as $field) {
        $sqlFields[] = $sqlTransformMap[$field] ?? $field;
    }
    $field_list = implode(', ', $sqlFields);

    $where = member_export_build_where($params);

    $page = (int)($params['page'] ?? 1);
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * MEMBER_EXPORT_PAGE_SIZE;

    $sql = "SELECT {$field_list} FROM {$g5['member_table']} {$where} ORDER BY mb_no DESC LIMIT {$offset}, " . MEMBER_EXPORT_PAGE_SIZE;
    
    $result = sql_query($sql);
    if (!$result) {
        throw new Exception("데이터 조회에 실패하였습니다");
    }

    $excelData = [$config['title'], $config['headers']];

    while ($row = sql_fetch_array($result)) {
        $rowData = [];
        foreach ($fields as $field) {
            $rowData[] = $row[$field] ?? '';
        }
        $excelData[] = $rowData;
    }

    return $excelData;
}

/**
 * 엑셀 파일 생성
 */
function member_export_create_excel($data, $fileName, $index = 0, $type = 1) 
{
    $config = member_export_get_config($type);
    
    if (!class_exists('PHPExcel')) {
        error_log('[Member Export Error] PHPExcel 라이브러리를 찾을 수 없습니다.');
        throw new Exception('파일 생성 중 내부 오류가 발생했습니다: PHPExcel 라이브러리를 찾을 수 없습니다.');
    }

    // 현재 설정값 백업
    $currentCache = PHPExcel_Settings::getCacheStorageMethod();
    
    // 캐싱 모드 설정 (엑셀 생성 전용)
    $cacheMethods = [
        PHPExcel_CachedObjectStorageFactory::cache_to_discISAM,
        PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized
    ];

    foreach ($cacheMethods as $method) {
        if (PHPExcel_Settings::setCacheStorageMethod($method)) {
            break;
        }
    }

    try {
        $excel = new PHPExcel();
        $sheet = $excel->setActiveSheetIndex(0);
        
        // 헤더 스타일 적용
        $last_char = member_export_column_char(count($config['headers']) - 1);
        $sheet->getStyle("A2:{$last_char}2")->applyFromArray([
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['rgb' => 'D9E1F2'], // 연파랑 배경
            ],
        ]);
        
        // 셀 정렬 및 줄바꿈 설정
        $sheet->getStyle("A:{$last_char}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        
        // 컬럼 너비 설정
        foreach ($config['widths'] as $i => $width) {
            $sheet->getColumnDimension(member_export_column_char($i))->setWidth($width);
        }
        
        // 데이터 입력
        $sheet->fromArray($data, NULL, 'A1');

        // 디렉토리 확인
        member_export_ensure_directory(MEMBER_EXPORT_DIR);
        
        // 파일명 생성
        $subname = $index == 0 ? 'all' : sprintf("%02d", $index);
        $filename = $fileName . "_" . $subname . ".xlsx";
        $filePath = MEMBER_EXPORT_DIR . "/" . $filename;

        // 파일 저장
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->setPreCalculateFormulas(false);
        $writer->save($filePath);

        unset($excel, $sheet, $writer); // 생성 완료 후 메모리 해제        
    } 
    catch (Exception $e) 
    {
        throw new Exception("엑셀 파일 생성에 실패하였습니다: " . $e->getMessage());
    } 
    finally 
    {
        // 캐싱 모드 원래 상태로 복원
        if ($currentCache) {
            PHPExcel_Settings::setCacheStorageMethod($currentCache);
        }
    }
    
    return $filename;
}

/**
 * 압축 파일 생성
 */
function member_export_create_zip($files, $zipFileName) 
{    
    if (!class_exists('ZipArchive')) {
        error_log('[Member Export Error]  ZipArchive 클래스를 사용할 수 없습니다.');
        return ['error' => '파일을 압축하는 중 문제가 발생했습니다. 개별 파일로 제공됩니다.<br>: ZipArchive 클래스를 사용할 수 없습니다.'];
    }
    
    member_export_ensure_directory(MEMBER_EXPORT_DIR);
    $destinationZipPath = rtrim(MEMBER_EXPORT_DIR, "/") . "/" . $zipFileName . ".zip";
    
    $zip = new ZipArchive();
    if ($zip->open($destinationZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return ['error' => "파일을 압축하는 중 문제가 발생했습니다. 개별 파일로 제공됩니다."];
    }
    
    foreach ($files as $file) {
        $filePath = MEMBER_EXPORT_DIR . "/" . $file;
        if (file_exists($filePath)) {
            $zip->addFile($filePath, basename($filePath));
        }
    }
    
    $result = $zip->close();
    
    return [
        'result' => $result,
        'zipFile' => $zipFileName . ".zip",
        'zipPath' => $destinationZipPath,
    ];
}

/**
 * 디렉토리 생성 및 확인
 */
function member_export_ensure_directory($dir) 
{
    if (!is_dir($dir)) {
        if (!@mkdir($dir, G5_DIR_PERMISSION, true)) {
            throw new Exception("디렉토리 생성 실패");
        }
        @chmod($dir, G5_DIR_PERMISSION);
    }
    
    if (!is_writable($dir)) {
        throw new Exception("디렉토리 쓰기 권한 없음");
    }
}

/**
 * 파일 삭제 - 값이 있으면 해당 파일만 삭제, 없으면 디렉토리 내 모든 파일 삭제
 * - 알집 생성 완료 시 엑셀 파일 제거
 * - 작업 전 오늘 날짜 폴더 및 log 폴더를 제외한 나머지 파일 모두 제거
 */
function member_export_delete($fileList = []) 
{    
    $cnt = 0;

    // 파일 리스트가 있는 경우 -> 해당 파일만 삭제
    if (!empty($fileList)) {
        foreach ($fileList as $file) {
            $filePath = rtrim(MEMBER_EXPORT_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath) && is_file($filePath) && @unlink($filePath)) {
                $cnt++;
            }
        }
    } 
    // 파일 리스트가 없는 경우 -> 디렉토리 내 모든 파일 삭제
    else {
        $files = glob(rtrim(G5_DATA_PATH . "/" . MEMBER_BASE_DIR, '/') . '/*');

        function deleteFolder($dir) {
            foreach (glob($dir . '/{.,}*', GLOB_BRACE) as $item) {
                if (in_array(basename($item), ['.', '..'])) continue;
                is_dir($item) ? deleteFolder($item) : unlink($item);
            }
            rmdir($dir);
        }

        foreach ($files as $file) {
            $name = basename($file);
        
            // log 폴더와 오늘 날짜로 시작하는 폴더는 제외
            if ($name === 'log' || preg_match('/^' . date('Ymd') . '\d{6}$/', $name)) continue;

            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'log' && @unlink($file)) {
                $cnt++;
            } elseif (is_dir($file)) {
                deleteFolder($file); // 재귀 폴더 삭제 함수 사용
                $cnt++;
            }
        }
    }

    return $cnt;
}

/**
 * 로그 작성
 */
function member_export_write_log($params, $result = [])
{
    global $member;

    $maxSize = 1024 * 1024 * 2; // 2MB
    $maxFiles = 10; // 최대 로그 파일 수 (필요시 조정)
    $username = $member['mb_id'] ?? 'guest';
    $datetime = date("Y-m-d H:i:s");

    if (!is_dir(MEMBER_LOG_DIR)) {
        @mkdir(MEMBER_LOG_DIR, G5_DIR_PERMISSION, true);
        @chmod(MEMBER_LOG_DIR, G5_DIR_PERMISSION);
    }

    $logFiles = glob(MEMBER_LOG_DIR . "/export_log_*.log") ?: [];

    // 최신 파일 기준 정렬 (최신 → 오래된)
    usort($logFiles, fn($a, $b) => filemtime($b) - filemtime($a));
    
    $latestLogFile = $logFiles[0] ?? null;

    // 용량 기준으로 새 파일 생성
    if (!$latestLogFile || filesize($latestLogFile) >= $maxSize) {
        $latestLogFile = MEMBER_LOG_DIR . "/export_log_" . date("YmdHi") . ".log";
        file_put_contents($latestLogFile, '');
        array_unshift($logFiles, $latestLogFile);
    }

    // 최대 파일 수 초과 시 오래된 파일 제거
    if (count($logFiles) > $maxFiles) {
        $filesToDelete = array_slice($logFiles, $maxFiles);
        foreach ($filesToDelete as $file) {
            @unlink($file);
        }
    }

    $formatType = (isset($params['formatType']) && $params['formatType'] == 2) ? '팝빌' : '일반';
    $success = isset($result['success']) && $result['success'] === true;
    $status = $success ? '성공' : '실패';

    // 조건 정리
    $condition = [];    
    
    // 검색 조건
    if ($params['use_stx'] == 1 && !empty($params['stx'])) {
        $sfl_list = get_export_config('sfl_list');

        $label = $sfl_list[$params['sfl']] ?? '';
        $condition[] = "검색({$params['stx_cond']}) : {$label} - {$params['stx']}";
    }
    
    // 레벨 조건
    if ($params['use_level'] == 1 && ($params['level_start'] || $params['level_end'])) {
        $condition[] = "레벨: {$params['level_start']}~{$params['level_end']}";
    }
    
    // 가입일 조건
    if ($params['use_date'] == 1 && ($params['date_start'] || $params['date_end'])) {
        $condition[] = "가입일: {$params['date_start']}~{$params['date_end']}";
    }
    
    // 포인트 조건
    if ($params['use_point'] == 1 && $params['point'] !== '') {
        $point_cond_map = get_export_config('point_cond_map');
        $symbol = $point_cond_map[$params['point_cond']] ?? '≥';
        $condition[] = "포인트 {$symbol} {$params['point']}";
    }
    
    // 휴대폰 여부
    if ($params['use_hp_exist'] == 1) {
        $condition[] = "휴대폰번호 있는 경우만";
    }
    
    // 광고 수신 동의
    if ($params['ad_range_only'] == 1) {
        $ad_range_list = get_export_config('ad_range_list');
        $label = $ad_range_list[$params['ad_range_type']] ?? '';
        $condition[] = "수신동의: 예 ({$label})";

        if ($params['ad_range_type'] == "custom_period" && ($params['agree_date_start'] || $params['agree_date_end'])) {
            $condition[] = "수신동의일: {$params['agree_date_start']}~{$params['agree_date_end']}";
        }

        if (in_array($params['ad_range_type'], ["month_confirm", "custom_period"])){
            $channels = array_filter([
                !empty($params['ad_mailling']) && (int)$params['ad_mailling'] === 1 ? '이메일' : null,
                !empty($params['ad_sms']) && (int)$params['ad_sms'] === 1 ? 'SMS/카카오톡' : null,
            ]);
        
            if ($channels) {
                $condition[] = '수신채널: ' . implode(', ', $channels);
            }
        }
    }
    
    // 차단회원 처리
    if ($params['use_intercept'] == 1) {
        $intercept_list = get_export_config('intercept_list');
        $label = $intercept_list[$params['intercept']] ?? '';
        if ($label) $condition[] = $label;
    }

    $conditionStr = !empty($condition) ? implode(', ', $condition) : '없음';
    $line1 = "[{$datetime}] [{$status}] 관리자: {$username} | 형식: {$formatType}";

    // 성공일 경우 추가 정보
    if ($success) {
        $total = $result['total'] ?? 0;
        $fileCount = isset($result['zip']) ? 1 : count($result['files'] ?? []);
        $line1 .= " | 총 {$total}건 | 파일: {$fileCount}개";
    }

    $logEntry = $line1 . PHP_EOL;
    $logEntry .= "조건: {$conditionStr}" . PHP_EOL;

    if (!$success && !empty($result['error'])) {
        $logEntry .= "오류 메시지: {$result['error']}" . PHP_EOL;
    }

    $logEntry .= PHP_EOL;

    // 파일에 기록
    if (@file_put_contents($latestLogFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
        error_log("[Member Export Error] 로그 파일 기록 실패: {$latestLogFile}");
    }
}