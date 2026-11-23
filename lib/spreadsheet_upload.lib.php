<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/*
 * 관리자 엑셀(xls·xlsx) 업로드 공통 검사 (KVE-2026-3262 ~ KVE-2026-3267)
 *
 * - 업로드 오류·크기를 먼저 확인하고, 원래 파일명 확장자와 파일 시그니처가 함께 맞을 때만
 *   해당 리더를 명시해 읽는다. PHPExcel_IOFactory::load()·identify()의 형식 자동 판별은 쓰지 않는다.
 *     OLE(D0 CF 11 E0 A1 B1 1A E1) + .xls  -> Excel5
 *     ZIP(PK 03 04)                + .xlsx -> Excel2007
 * - 데이터만 읽고(setReadDataOnly), 첫 시트만, 처리기가 쓰는 열과 최대 행까지만 셀을 만든다.
 * - 라이브러리 예외는 잡아서 오류 문구로 돌려준다. 호출하는 처리기가 alert()로 끝낸다.
 *
 * 아래 상한은 config.php 등에서 먼저 define하면 바꿀 수 있다.
 */
if (!defined('G5_SPREADSHEET_UPLOAD_MAX_SIZE')) {
    define('G5_SPREADSHEET_UPLOAD_MAX_SIZE', 10485760);         // 업로드 파일 크기(바이트), 10MiB
}
if (!defined('G5_SPREADSHEET_UPLOAD_MAX_ROWS')) {
    define('G5_SPREADSHEET_UPLOAD_MAX_ROWS', 10000);            // 읽을 최대 행 번호(제목 행 포함)
}
if (!defined('G5_SPREADSHEET_UPLOAD_MAX_UNZIP_SIZE')) {
    define('G5_SPREADSHEET_UPLOAD_MAX_UNZIP_SIZE', 52428800);   // xlsx 압축 해제 합계(바이트), 50MiB
}
if (!defined('G5_SPREADSHEET_UPLOAD_MAX_ZIP_ENTRIES')) {
    define('G5_SPREADSHEET_UPLOAD_MAX_ZIP_ENTRIES', 1000);      // xlsx 안의 파일 수
}
if (!defined('G5_SPREADSHEET_UPLOAD_TIME_LIMIT')) {
    define('G5_SPREADSHEET_UPLOAD_TIME_LIMIT', 300);            // 상품 일괄등록 처리 시간(초)
}
if (!defined('G5_SPREADSHEET_UPLOAD_MEMORY_LIMIT')) {
    // memory_limit이 무제한(-1)일 때만 적용한다. 영카트는 extend/shop.extend.php가 읽는
    // lib/thumbnail.lib.php에서 memory_limit을 -1로 바꾸므로 배송·SMS 처리기는 무제한이었다.
    define('G5_SPREADSHEET_UPLOAD_MEMORY_LIMIT', '256M');
}

include_once(dirname(__FILE__).'/PHPExcel/IOFactory.php');

/**
 * 처리기가 쓰는 열과 최대 행까지만 셀을 만드는 읽기 필터.
 * 최대 행을 넘는 셀이 있으면 overflow를 표시해 호출부가 파일 전체를 거부한다(잘린 채 처리하지 않음).
 */
class G5_Spreadsheet_Read_Filter implements PHPExcel_Reader_IReadFilter
{
    public $max_row;
    public $max_column_index;
    public $overflow = false;

    public function __construct($max_row, $max_column_index)
    {
        $this->max_row = (int) $max_row;
        $this->max_column_index = (int) $max_column_index;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if ((int) $row > $this->max_row) {
            $this->overflow = true;
            return false;
        }
        return PHPExcel_Cell::columnIndexFromString($column) <= $this->max_column_index;
    }
}

function g5_spreadsheet_size_text($bytes)
{
    $bytes = (int) $bytes;
    if ($bytes >= 1048576 && $bytes % 1048576 === 0) return ($bytes / 1048576).'MB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1).'MB';
    return max(1, (int) ceil($bytes / 1024)).'KB';
}

function g5_spreadsheet_result($error, $data = array())
{
    $data['error'] = (string) $error;
    return $data;
}

/**
 * 업로드 항목($_FILES['이름'])을 검사한다.
 *
 * @param array $upload  $_FILES의 한 항목
 * @param array $allowed 허용 확장자(소문자, 점 없음). 기본 xls, xlsx. csv는 시그니처 검사 없이 크기만 본다.
 * @param int   $max_size 최대 크기(바이트). 기본 G5_SPREADSHEET_UPLOAD_MAX_SIZE
 * @return array error(빈 문자열이면 통과), path, name, type(xls|xlsx|csv)
 */
function g5_spreadsheet_upload_check($upload, $allowed = null, $max_size = null)
{
    if ($max_size === null) $max_size = G5_SPREADSHEET_UPLOAD_MAX_SIZE;

    if (!is_array($upload) || !isset($upload['tmp_name']) || !isset($upload['name'])
        || !is_string($upload['tmp_name']) || !is_string($upload['name'])) {
        return g5_spreadsheet_result('파일을 선택해 주세요.');
    }
    $error = isset($upload['error']) ? (int) $upload['error'] : UPLOAD_ERR_OK;
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return g5_spreadsheet_result('파일 크기가 너무 큽니다. (최대 '.g5_spreadsheet_size_text($max_size).')');
    }
    if ($error === UPLOAD_ERR_NO_FILE || $upload['tmp_name'] === '') {
        return g5_spreadsheet_result('파일을 선택해 주세요.');
    }
    if ($error !== UPLOAD_ERR_OK) {
        return g5_spreadsheet_result('파일 업로드에 실패했습니다. 다시 시도해 주세요.');
    }
    if (!is_uploaded_file($upload['tmp_name'])) {
        return g5_spreadsheet_result('올바른 업로드 파일이 아닙니다.');
    }

    return g5_spreadsheet_file_check($upload['tmp_name'], $upload['name'], $allowed, $max_size);
}

/**
 * 파일 크기와 "확장자 + 시그니처" 조합을 확인한다. 내용은 파싱하지 않는다.
 */
function g5_spreadsheet_file_check($path, $name, $allowed = null, $max_size = null)
{
    if ($allowed === null) $allowed = array('xls', 'xlsx');
    if ($max_size === null) $max_size = G5_SPREADSHEET_UPLOAD_MAX_SIZE;

    $pos = strrpos((string) $name, '.');
    $ext = ($pos === false) ? '' : strtolower(substr($name, $pos + 1));
    if (!in_array($ext, $allowed, true)) {
        return g5_spreadsheet_result(strtoupper(implode(', ', $allowed)).' 파일만 업로드할 수 있습니다.');
    }

    $size = is_file($path) ? @filesize($path) : false;
    if ($size === false) {
        return g5_spreadsheet_result('업로드한 파일을 읽을 수 없습니다.');
    }
    if ($size <= 0) {
        return g5_spreadsheet_result('빈 파일입니다.');
    }
    if ($size > $max_size) {
        return g5_spreadsheet_result('파일 크기가 너무 큽니다. (최대 '.g5_spreadsheet_size_text($max_size).')');
    }

    $info = array('path' => $path, 'name' => $name, 'type' => $ext, 'size' => $size);
    if ($ext === 'csv') {
        return g5_spreadsheet_result('', $info);
    }

    $head = (string) @file_get_contents($path, false, null, 0, 8);
    $is_ole = ($head === pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1));
    $is_zip = (substr($head, 0, 4) === "PK\x03\x04");
    if (($ext === 'xls' && $is_ole) || ($ext === 'xlsx' && $is_zip)) {
        return g5_spreadsheet_result('', $info);
    }

    return g5_spreadsheet_result('파일 내용이 확장자('.$ext.')와 맞지 않습니다. Excel에서 xls 또는 xlsx 형식으로 저장한 파일을 올려 주세요.');
}

/**
 * xlsx(ZIP) 안의 파일 수와 압축 해제 크기 합계를 파싱 전에 확인한다.
 * PHP ZipArchive::getFromName()은 중앙 디렉터리의 크기만큼만 읽으므로 이 합계가 해제 메모리의 상한이 된다.
 */
function g5_spreadsheet_zip_check($path)
{
    if (!class_exists('ZipArchive')) {
        return 'xlsx 파일을 읽으려면 PHP zip 확장이 필요합니다. xls 형식으로 저장해 올려 주세요.';
    }
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return '엑셀 파일을 읽을 수 없습니다. 파일이 손상되었거나 지원하지 않는 형식입니다.';
    }
    $count = (int) $zip->numFiles;
    $error = '';
    if ($count < 1 || $count > G5_SPREADSHEET_UPLOAD_MAX_ZIP_ENTRIES) {
        $error = '엑셀 파일 구성이 올바르지 않습니다.';
    } else {
        $total = 0;
        for ($i = 0; $i < $count; $i++) {
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['size'])) {
                $error = '엑셀 파일 구성이 올바르지 않습니다.';
                break;
            }
            $total += $stat['size'];
            if ($total > G5_SPREADSHEET_UPLOAD_MAX_UNZIP_SIZE) {
                $error = '엑셀 파일의 내용이 너무 큽니다.';
                break;
            }
        }
    }
    $zip->close();
    return $error;
}

/**
 * 검사를 통과한 파일을 형식에 맞는 리더로 읽는다.
 *
 * @param string $path
 * @param string $type    xls 또는 xlsx (g5_spreadsheet_file_check()의 type)
 * @param array  $options max_rows: 최대 행 번호(기본 G5_SPREADSHEET_UPLOAD_MAX_ROWS), max_column: 마지막 열(기본 Z)
 * @return array error, excel(PHPExcel), sheet(첫 시트), num_rows(데이터가 있는 마지막 행), max_column
 */
function g5_spreadsheet_load($path, $type, $options = array())
{
    $max_rows = isset($options['max_rows']) ? (int) $options['max_rows'] : G5_SPREADSHEET_UPLOAD_MAX_ROWS;
    $max_column = isset($options['max_column']) ? strtoupper((string) $options['max_column']) : 'Z';
    $unreadable = '엑셀 파일을 읽을 수 없습니다. 파일이 손상되었거나 지원하지 않는 형식입니다.';
    $too_many_rows = '엑셀 파일의 행이 너무 많습니다. (최대 '.number_format($max_rows).'행) 파일을 나누어 올려 주세요.';

    if (!preg_match('/^[A-Z]{1,3}$/', $max_column) || $max_rows < 1) {
        return g5_spreadsheet_result($unreadable);
    }

    $memory_limit = trim((string) ini_get('memory_limit'));
    if (($memory_limit === '-1' || $memory_limit === '') && G5_SPREADSHEET_UPLOAD_MEMORY_LIMIT) {
        @ini_set('memory_limit', G5_SPREADSHEET_UPLOAD_MEMORY_LIMIT);
    }

    try {
        if ($type === 'xlsx') {
            $zip_error = g5_spreadsheet_zip_check($path);
            if ($zip_error) return g5_spreadsheet_result($zip_error);
            $reader = PHPExcel_IOFactory::createReader('Excel2007');
        } elseif ($type === 'xls') {
            $reader = PHPExcel_IOFactory::createReader('Excel5');
        } else {
            return g5_spreadsheet_result($unreadable);
        }

        if (!$reader->canRead($path)) {
            return g5_spreadsheet_result($unreadable);
        }
        $reader->setReadDataOnly(true);
        $names = $reader->listWorksheetNames($path);
        if (!is_array($names) || !count($names)) {
            return g5_spreadsheet_result($unreadable);
        }
        $reader->setLoadSheetsOnly(array(reset($names)));
        $filter = new G5_Spreadsheet_Read_Filter($max_rows, PHPExcel_Cell::columnIndexFromString($max_column));
        $reader->setReadFilter($filter);

        $excel = $reader->load($path);
        if ($filter->overflow) {
            $excel->disconnectWorksheets();
            return g5_spreadsheet_result($too_many_rows);
        }
        $sheet = $excel->getSheet(0);
        // 행 서식(collapsed 등)으로 늘어난 getHighestRow()가 아니라 실제 셀이 있는 마지막 행까지만 반복한다.
        $num_rows = (int) $sheet->getHighestDataRow();
        if ($num_rows > $max_rows) {
            $excel->disconnectWorksheets();
            return g5_spreadsheet_result($too_many_rows);
        }
    } catch (Exception $e) {
        return g5_spreadsheet_result($unreadable);
    } catch (Throwable $e) {
        return g5_spreadsheet_result($unreadable);
    }

    return g5_spreadsheet_result('', array('excel' => $excel, 'sheet' => $sheet, 'num_rows' => $num_rows, 'max_column' => $max_column));
}

/**
 * 업로드 검사와 읽기를 한 번에 한다. xls·xlsx만 받는다.
 */
function g5_spreadsheet_upload_open($upload, $options = array())
{
    $checked = g5_spreadsheet_upload_check($upload, array('xls', 'xlsx'));
    if ($checked['error'] !== '') return $checked;
    return g5_spreadsheet_load($checked['path'], $checked['type'], $options);
}
