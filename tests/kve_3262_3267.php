<?php
// KVE-2026-3262~3267 관리자 엑셀 업로드(PHPExcel) 회귀 검사. DB와 네트워크를 쓰지 않는다.
// 실행: php8.4 tests/kve_3262_3267.php [검사할 코드 루트, 기본은 이 저장소]
//       php8.4 tests/kve_3262_3267.php --legacy [루트]   수정 전 처리기 방식(IOFactory::load + getHighestRow 반복)을 측정만 한다.
//       php8.4 tests/kve_3262_3267.php --emit 디렉터리       표본 파일만 만든다(tests/kve_3262_3267_http.py가 사용).
// 악성 표본은 임시 디렉터리에 만들고, 각 표본은 자식 PHP 프로세스(memory_limit 128M, 벽시계 20초)에서 읽어
// 시간과 메모리를 잰다. 수정 전 코드를 루트로 주면 자식이 메모리 한도·시간 제한에 걸리고 검사가 실패한다.

const CHILD_MEMORY = '128M';
const CHILD_WALL = 20.0;      // 자식 프로세스 강제 종료(초)
const FAST_MS = 2000;         // 거부·처리에 허용하는 시간
const SMALL_MEM = 33554432;   // 거부·처리에 허용하는 메모리 증가(32MiB)

if (PHP_SAPI !== 'cli') exit(1);

// ------------------------------------------------------------------ 자식 프로세스
if (isset($argv[1]) && $argv[1] === '--child') {
    $in = json_decode(stream_get_contents(STDIN), true);
    foreach ($in['defines'] ?? array() as $k => $v) define($k, $v);
    define('_GNUBOARD_', true);
    $root = $in['root'];
    require $root.'/lib/PHPExcel/IOFactory.php';
    $helper = $root.'/lib/spreadsheet_upload.lib.php';
    $out = array('status' => 'ok');
    class_exists('PHPExcel_Cell');   // 클래스 선언 시점의 PHP 8.4 시그니처 경고는 세지 않는다.
    $deprecations = 0;
    set_error_handler(function ($no, $str) use (&$deprecations) {
        if (($no === E_DEPRECATED || $no === E_USER_DEPRECATED) && stripos($str, 'Implicit conversion') !== false) $deprecations++;
        return false;
    });
    $base = memory_get_usage();
    $t = microtime(true);
    try {
        switch ($in['case']) {
            case 'helper':
                if (!is_file($helper)) { $out['status'] = 'missing'; break; }
                require $helper;
                $c = g5_spreadsheet_file_check($in['file'], $in['name'], $in['allowed'] ?? null, $in['max_size'] ?? null);
                if ($c['error'] === '') $c = g5_spreadsheet_load($c['path'], $c['type'], $in['options'] ?? array());
                if ($c['error'] !== '') { $out['status'] = 'rejected'; $out['message'] = $c['error']; break; }
                $out['status'] = 'loaded';
                $out['num_rows'] = $c['num_rows'];
                $out['highest_row'] = $c['sheet']->getHighestRow();
                $last = min($c['num_rows'], 6);
                $out['cells'] = $c['sheet']->rangeToArray('A1:'.$c['max_column'].$last, null, true, false);
                break;
            case 'check':
                if (!is_file($helper)) { $out['status'] = 'missing'; break; }
                require $helper;
                $c = g5_spreadsheet_file_check($in['file'], $in['name'], $in['allowed'] ?? null, $in['max_size'] ?? null);
                if ($c['error'] !== '') { $out['status'] = 'rejected'; $out['message'] = $c['error']; break; }
                $out['type'] = $c['type'];
                break;
            case 'memlimit':
                if (!is_file($helper)) { $out['status'] = 'missing'; break; }
                ini_set('memory_limit', $in['limit']);
                require $helper;
                $c = g5_spreadsheet_load($in['file'], 'xlsx', array('max_column' => 'B'));
                $out['loaded'] = $c['error'] === '';
                $out['after'] = ini_get('memory_limit');
                break;
            case 'reader':
                $r = PHPExcel_IOFactory::createReader($in['reader']);
                if (isset($in['rdo'])) $r->setReadDataOnly($in['rdo']);
                if (!$r->canRead($in['file'])) { $out['status'] = 'cannot_read'; break; }
                $x = $r->load($in['file']);
                $s = $x->getSheet(0);
                $out['highest_row'] = $s->getHighestRow();
                $out['highest_column'] = $s->getHighestColumn();
                $out['data_row'] = $s->getHighestDataRow();
                if (!empty($in['dump'])) $out['cells'] = $s->rangeToArray('A1:'.$in['dump'], null, true, false);
                break;
            case 'scan':
                $r = PHPExcel_IOFactory::createReader('Excel2007');
                $r->securityScan(file_get_contents($in['file']));
                break;
            case 'legacy':   // 수정 전 처리기와 같은 순서
                $x = PHPExcel_IOFactory::load($in['file']);
                $s = $x->getSheet(0);
                $hr = $s->getHighestRow();
                $hc = $s->getHighestColumn();
                $out['highest_row'] = $hr;
                $out['highest_column'] = $hc;
                for ($i = 1; $i <= $hr; $i++) $s->rangeToArray('A'.$i.':'.$hc.$i, null, true, false);
                break;
            case 'columns':
                $bad = 0;
                for ($i = 0; $i < 18278; $i++) {
                    $n = $i; $s = '';
                    do { $s = chr(65 + $n % 26).$s; $n = intdiv($n, 26) - 1; } while ($n >= 0);
                    if (PHPExcel_Cell::stringFromColumnIndex($i) !== $s || PHPExcel_Cell::columnIndexFromString($s) !== $i + 1) $bad++;
                }
                $out['bad'] = $bad;
                try { PHPExcel_Cell::stringFromColumnIndex(18278); $out['beyond'] = 'accepted'; }
                catch (Exception $e) { $out['beyond'] = 'exception'; }
                $out['deprecations'] = $deprecations;
                break;
            case 'dimensions':
                $x = new PHPExcel();
                $s = $x->getActiveSheet();
                $s->getRowDimension(1048576);
                $s->getColumnDimension('XFD');
                $out['legal'] = 'ok';
                try { $s->getRowDimension(1048577); $out['row'] = 'accepted'; } catch (Exception $e) { $out['row'] = 'exception'; }
                try { $s->getColumnDimension('XFE'); $out['column'] = 'accepted'; } catch (Exception $e) { $out['column'] = 'exception'; }
                $out['highest_row'] = $s->getHighestRow();
                break;
            case 'libxml':
                $o = PHPExcel_Settings::getLibXmlLoaderOptions();
                $out['default'] = $o;
                PHPExcel_Settings::setLibXmlLoaderOptions(0);
                $out['zero'] = PHPExcel_Settings::getLibXmlLoaderOptions();
                PHPExcel_Settings::setLibXmlLoaderOptions(LIBXML_DTDLOAD | LIBXML_NOENT | LIBXML_DTDATTR);
                $out['forced'] = PHPExcel_Settings::getLibXmlLoaderOptions();
                $out['danger'] = LIBXML_DTDLOAD | LIBXML_NOENT | LIBXML_DTDATTR | LIBXML_DTDVALID;
                break;
        }
    } catch (Throwable $e) {
        $out['status'] = 'exception';
        $out['message'] = get_class($e).': '.$e->getMessage();
    }
    $out['ms'] = (int) round((microtime(true) - $t) * 1000);
    $out['mem'] = memory_get_peak_usage() - $base;
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    exit(0);
}

// ------------------------------------------------------------------ 부모 프로세스
$legacy = false;
$emit = null;
$args = array_slice($argv, 1);
if ($args && $args[0] === '--legacy') { $legacy = true; array_shift($args); }
if ($args && $args[0] === '--emit') { array_shift($args); $emit = array_shift($args); }
$root = realpath($emit === null && $args ? $args[0] : dirname(__DIR__));
if (!$root || !is_file($root.'/lib/PHPExcel/IOFactory.php')) { fwrite(STDERR, "코드 루트가 올바르지 않습니다.\n"); exit(2); }
if (!class_exists('ZipArchive') || !function_exists('gzopen')) { fwrite(STDERR, "zip, zlib 확장이 필요합니다.\n"); exit(2); }

if ($emit !== null) {
    if (!is_dir($emit) || glob($emit.'/*')) { fwrite(STDERR, "--emit 에는 비어 있는 디렉터리를 지정하십시오.\n"); exit(2); }
    $tmp = realpath($emit);
} else {
    $tmp = sys_get_temp_dir().'/g5kve3262-'.bin2hex(random_bytes(4));
    mkdir($tmp, 0700);
    register_shutdown_function(function () use ($tmp) {
        foreach (glob($tmp.'/*') as $f) @unlink($f);
        @rmdir($tmp);
    });
}

function child(array $spec)
{
    global $root;
    $spec['root'] = $root;
    $cmd = array(PHP_BINARY, '-n', '-d', 'memory_limit='.CHILD_MEMORY, '-d', 'display_errors=stderr', '-d', 'log_errors=0');
    // -n 으로 시스템 ini를 빼면 공유 확장이 빠질 수 있으므로 필요한 확장이 내장인지 확인 후 사용한다.
    static $use_n = null;
    if ($use_n === null) {
        $probe = shell_exec(escapeshellarg(PHP_BINARY).' -n -r '.escapeshellarg('echo (int)(class_exists("ZipArchive") && function_exists("gzopen") && function_exists("simplexml_load_string") && function_exists("mb_convert_encoding") && class_exists("XMLReader"));'));
        $use_n = trim((string) $probe) === '1';
    }
    if (!$use_n) array_splice($cmd, 1, 1);
    $cmd[] = __FILE__;
    $cmd[] = '--child';
    $p = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
    fwrite($pipes[0], json_encode($spec));
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $stdout = $stderr = '';
    $start = microtime(true);
    $timeout = false;
    while (true) {
        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
        $st = proc_get_status($p);
        if (!$st['running']) break;
        if (microtime(true) - $start > CHILD_WALL) { $timeout = true; proc_terminate($p, 9); break; }
        usleep(10000);
    }
    $stdout .= stream_get_contents($pipes[1]);
    $stderr .= stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    proc_close($p);
    $wall = (int) round((microtime(true) - $start) * 1000);
    if ($timeout) return array('status' => 'timeout', 'ms' => $wall, 'mem' => 0);
    $r = json_decode($stdout, true);
    if (!is_array($r)) {
        $msg = trim($stderr.' '.$stdout);
        return array('status' => stripos($msg, 'Allowed memory size') !== false ? 'memory_exhausted' : 'fatal', 'message' => substr($msg, 0, 300), 'ms' => $wall, 'mem' => 0);
    }
    return $r;
}

$passed = 0; $failed = 0;
function check($ok, $label, $detail = null)
{
    global $passed, $failed;
    if ($ok) { $passed++; echo "PASS $label\n"; return; }
    $failed++;
    echo "FAIL $label".($detail !== null ? ' -- '.substr(is_string($detail) ? $detail : json_encode($detail, JSON_UNESCAPED_UNICODE), 0, 400) : '')."\n";
}
function bounded($r, $mem = SMALL_MEM) { return isset($r['ms'], $r['mem']) && $r['ms'] < FAST_MS && $r['mem'] < $mem && !in_array($r['status'], array('timeout', 'memory_exhausted', 'fatal'), true); }
function rejected($r) { return $r['status'] === 'rejected' && bounded($r); }
function brief($r) { unset($r['cells']); return $r; }

// ---------- 표본 만들기 ----------
require $root.'/lib/PHPExcel.php';
function make_zip($path, array $entries)
{
    $z = new ZipArchive();
    if ($z->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new Exception('zip '.$path);
    foreach ($entries as $name => $data) $z->addFromString($name, $data);
    $z->close();
}
function zip_entries($path)
{
    $z = new ZipArchive(); $z->open($path); $e = array();
    for ($i = 0; $i < $z->numFiles; $i++) { $n = $z->getNameIndex($i); $e[$n] = $z->getFromIndex($i); }
    $z->close();
    return $e;
}
// PHPExcel 쓰기 도구로 정상 xlsx 골격을 만든 뒤 시트·공유 문자열만 바꿔 넣는다.
$x = new PHPExcel();
$x->getActiveSheet()->fromArray(array(array('홍길동', '010-1234-5678'), array('김철수', '01098765432', 'extra')), null, 'A1');
$x->getActiveSheet()->setCellValueExplicit('C1', 12345, PHPExcel_Cell_DataType::TYPE_NUMERIC);
$x->getActiveSheet()->setCellValueExplicit('B2', '01098765432', PHPExcel_Cell_DataType::TYPE_STRING);
PHPExcel_IOFactory::createWriter($x, 'Excel2007')->save($tmp.'/valid.xlsx');
$base = zip_entries($tmp.'/valid.xlsx');
$NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
function xlsx_with($path, $changes)
{
    global $base;
    make_zip($path, array_merge($base, $changes));
}
function sheet_xml($inner) { global $NS; return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".'<worksheet xmlns="'.$NS.'">'.$inner.'</worksheet>'; }
$one_cell = '<row r="1"><c r="A1" t="inlineStr"><is><t>a</t></is></c></row>';

// KVE-2026-3262: ISO-2022 전환 시퀀스로 감춘 DOCTYPE과 내부 엔티티 확장
$esc = "\x1b(B";
$dt = '<'.$esc.'!'.implode($esc, str_split('DOCTYPE'));
$ents = '<!ENTITY a "aaaaaaaaaa">';
$prev = 'a';
foreach (range('b', 'h') as $n) { $ents .= '<!ENTITY '.$n.' "'.str_repeat('&'.$prev.';', 10).'">'; $prev = $n; }
$iso = '<?xml version="1.0" encoding="ISO-2022-JP"?>'."\n".$dt.' sst ['.$ents.']>'."\n".'<sst xmlns="'.$NS.'" count="1" uniqueCount="1"><si><t>&h;</t></si></sst>';
xlsx_with($tmp.'/iso2022.xlsx', array('xl/sharedStrings.xml' => $iso));
file_put_contents($tmp.'/iso2022.xml', $iso);
file_put_contents($tmp.'/latin1.xml', '<?xml version="1.0" encoding="ISO-8859-1"?><sst xmlns="'.$NS.'"/>');
$utf16_doctype = "\xFF\xFE".mb_convert_encoding('<?xml version="1.0" encoding="UTF-16"?><!DOCTYPE sst [<!ENTITY a "a">]><sst xmlns="'.$NS.'"><si><t>&a;</t></si></sst>', 'UTF-16LE', 'UTF-8');
file_put_contents($tmp.'/utf16-doctype.xml', $utf16_doctype);
$utf16_clean = "\xFF\xFE".mb_convert_encoding('<?xml version="1.0" encoding="UTF-16"?><sst xmlns="'.$NS.'" count="2" uniqueCount="2"><si><t>유니코드</t></si><si><t>010-2222-3333</t></si></sst>', 'UTF-16LE', 'UTF-8');
file_put_contents($tmp.'/utf16-clean.xml', $utf16_clean);
xlsx_with($tmp.'/utf16-clean.xlsx', array('xl/sharedStrings.xml' => $utf16_clean,
    'xl/worksheets/sheet1.xml' => sheet_xml('<sheetData><row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row></sheetData>')));
file_put_contents($tmp.'/utf32.xml', mb_convert_encoding('<?xml version="1.0" encoding="UTF-32"?><sst/>', 'UTF-32BE', 'UTF-8'));
file_put_contents($tmp.'/utf8.xml', $base['xl/sharedStrings.xml']);

// KVE-2026-3263: 공백만 담은 Gnumeric 문서를 gzip(약 200MiB → 수백 KiB)
$gz = gzopen($tmp.'/bomb.gnumeric', 'wb9');
gzwrite($gz, '<?xml version="1.0" encoding="UTF-8"?><gnm:Workbook xmlns:gnm="http://www.gnumeric.org/v10.dtd">');
$chunk = str_repeat(' ', 1048576);
for ($i = 0; $i < 200; $i++) gzwrite($gz, $chunk);
gzwrite($gz, '</gnm:Workbook>');
gzclose($gz);
unset($chunk);
copy($tmp.'/bomb.gnumeric', $tmp.'/gnumeric.xlsx');
copy($tmp.'/bomb.gnumeric', $tmp.'/gnumeric.xls');

// KVE-2026-3264: ODS 행·열 반복
function ods($path, $table)
{
    $o = 'urn:oasis:names:tc:opendocument:xmlns:';
    $ns = 'xmlns:office="'.$o.'office:1.0" xmlns:table="'.$o.'table:1.0" xmlns:text="'.$o.'text:1.0"';
    make_zip($path, array(
        'mimetype' => 'application/vnd.oasis.opendocument.spreadsheet',
        'META-INF/manifest.xml' => '<?xml version="1.0" encoding="UTF-8"?><manifest:manifest xmlns:manifest="'.$o.'manifest:1.0"><manifest:file-entry manifest:full-path="/" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/></manifest:manifest>',
        'meta.xml' => '<?xml version="1.0" encoding="UTF-8"?><office:document-meta '.$ns.' office:version="1.2"><office:meta/></office:document-meta>',
        'content.xml' => '<?xml version="1.0" encoding="UTF-8"?><office:document-content '.$ns.' office:version="1.2"><office:body><office:spreadsheet><table:table table:name="Sheet1">'.$table.'</table:table></office:spreadsheet></office:body></office:document-content>',
    ));
}
$cell = '<table:table-cell office:value-type="string"><text:p>x</text:p></table:table-cell>';
ods($tmp.'/rows.ods', '<table:table-row table:number-rows-repeated="1000000">'.$cell.'</table:table-row>');
ods($tmp.'/cols.ods', '<table:table-row table:number-rows-repeated="40"><table:table-cell table:number-columns-repeated="16000" office:value-type="string"><text:p>x</text:p></table:table-cell></table:table-row>');
ods($tmp.'/ods-ok.ods', '<table:table-row>'.$cell.$cell.'</table:table-row><table:table-row table:number-rows-repeated="1048575"><table:table-cell table:number-columns-repeated="1024"/></table:table-row>');
copy($tmp.'/rows.ods', $tmp.'/ods.xlsx');

// KVE-2026-3265: OLE 섹터 순환. 섹터 0 = SSAT, 섹터 1 = SAT
function ole($path, $sat, $sbdStart, $rootStart, $numBbd = 1, $sectors = 2)
{
    $h = pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1).str_repeat("\0", 16).pack('vvvv', 0x3e, 3, 0xfffe, 9).pack('v', 6).str_repeat("\0", 10);
    $h .= pack('V', $numBbd).pack('V', $rootStart).pack('V', 0).pack('V', 4096).pack('V', $sbdStart).pack('V', 1).pack('V', 0xfffffffe).pack('V', 0);
    $h .= pack('V', 1);                           // MSAT[0] = 섹터 1(SAT)
    $h .= str_repeat(pack('V', 0xffffffff), 108);
    $body = str_repeat("\0", 512);              // 섹터 0
    $s = ''; foreach ($sat as $v) $s .= pack('V', $v & 0xffffffff);
    $body .= str_pad($s, 512, pack('V', 0xffffffff));
    for ($i = 2; $i < $sectors; $i++) $body .= str_repeat("\0", 512);
    file_put_contents($path, $h.$body);
}
ole($tmp.'/ssat-loop.xls', array(0, -3), 0, 0);          // SSAT 0 → 0
ole($tmp.'/dir-loop.xls', array(0, -3), -2, 0);          // 디렉터리 0 → 0
ole($tmp.'/sat-count.xls', array(-2, -3), -2, 0, 0x7fffffff); // SAT 섹터 수 과장
ole($tmp.'/chain-range.xls', array(5000, -3), -2, 0);    // 파일 밖 섹터

// KVE-2026-3266: 큰 행 번호(collapsed·outlineLevel은 readDataOnly에서도 처리됨)
xlsx_with($tmp.'/huge-row.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<sheetData>'.$one_cell.'<row r="1000000000" collapsed="1" outlineLevel="1"/></sheetData>')));
xlsx_with($tmp.'/huge-cell.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<sheetData>'.$one_cell.'<row r="9999999"><c r="A9999999"><v>1</v></c></row></sheetData>')));
xlsx_with($tmp.'/last-row.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<sheetData>'.$one_cell.'<row r="1048576" collapsed="1" outlineLevel="1"/></sheetData>')));
xlsx_with($tmp.'/many-rows.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<sheetData>'.$one_cell.'<row r="20000"><c r="A20000"><v>1</v></c></row></sheetData>')));

// KVE-2026-3267: 열 범위
xlsx_with($tmp.'/huge-cols.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<cols><col min="1" max="100000000" width="10" customWidth="1"/></cols><sheetData>'.$one_cell.'</sheetData>')));
xlsx_with($tmp.'/neg-cols.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<cols><col min="-1000000" max="3" width="10" customWidth="1"/></cols><sheetData>'.$one_cell.'</sheetData>')));
xlsx_with($tmp.'/ok-cols.xlsx', array('xl/worksheets/sheet1.xml' => sheet_xml('<cols><col min="1" max="16384" width="10" customWidth="1"/></cols><sheetData>'.$one_cell.'</sheetData>')));

// 정상 파일: 상품 템플릿 + 상품 2행, 배송 xls
$template = $root.'/lib/Excel/itemexcel.xls';
$r5 = PHPExcel_IOFactory::createReader('Excel5');
$item = $r5->load($template);
$item_rows = array(
    array('KVE3262A', '10', '', '', '테스트 상품 가', '제조사', '', '', '', '1', '0', '0', '0', '0', '기본', '<p>설명</p>', '', '15000', '12000', '0', '100', '0', '', '1', '50', '5', '1', '10', '0', '0', 'a/1.jpg', '', '', '', '', '', '', '', '', 'a/10.jpg'),
    array('1700000000123', '10', '', '', '숫자 코드 상품', '', '', '', '', '0', '0', '0', '0', '0', '', '', '', '0', '9900', '0', '0', '0', '', '1', '1', '0', '0', '0', '0', '0', '', '', '', '', '', '', '', '', '', ''),
);
foreach ($item_rows as $i => $row) foreach ($row as $c => $v) $item->getSheet(0)->setCellValueExplicitByColumnAndRow($c, 3 + $i, $v, PHPExcel_Cell_DataType::TYPE_STRING);
$item->getSheet(0)->setCellValueExplicitByColumnAndRow(17, 4, 25000, PHPExcel_Cell_DataType::TYPE_NUMERIC);   // 숫자 셀
PHPExcel_IOFactory::createWriter($item, 'Excel5')->save($tmp.'/item.xls');
$d = new PHPExcel();
$d->getActiveSheet()->fromArray(array(array('주문번호', '주문자', '연락처', '휴대폰', '받는분', '받는분연락처', '받는분휴대폰', '주소', '배송회사', '운송장번호'),
    array('2026111912345678', '홍길동', '', '', '', '', '', '', 'CJ대한통운', '123456789012')), null, 'A1');
PHPExcel_IOFactory::createWriter($d, 'Excel5')->save($tmp.'/delivery.xls');
xlsx_with($tmp.'/pad.xlsx', array('xl/media/pad.bin' => str_repeat("\0", 2 * 1048576)));

// 형식 위장
copy($tmp.'/valid.xlsx', $tmp.'/zip-as.xls');
copy($tmp.'/item.xls', $tmp.'/ole-as.xlsx');
file_put_contents($tmp.'/html.xls', '<html><body><table><tr><td>홍길동</td><td>010-1234-5678</td></tr></table></body></html>');
file_put_contents($tmp.'/csv.xlsx', "홍길동,010-1234-5678\n");
file_put_contents($tmp.'/spreadsheetml.xls', '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="S"><Table><Row ss:Index="1000000000"><Cell><Data ss:Type="String">x</Data></Cell></Row></Table></Worksheet></Workbook>');
file_put_contents($tmp.'/empty.xls', '');
file_put_contents($tmp.'/sms.csv', "이름,휴대폰\n홍길동,010-1234-5678\n");

if ($emit !== null) { echo "EMITTED ", count(glob($tmp.'/*')), "\n"; exit(0); }

// ---------- --legacy: 수정 전 처리기 방식 측정 ----------
if ($legacy) {
    foreach (array('iso2022.xlsx', 'gnumeric.xlsx', 'ods.xlsx', 'ssat-loop.xls', 'dir-loop.xls', 'sat-count.xls', 'huge-row.xlsx', 'huge-cols.xlsx', 'valid.xlsx') as $f) {
        $r = child(array('case' => 'legacy', 'file' => $tmp.'/'.$f));
        echo 'LEGACY ', $f, ' ', json_encode(brief($r), JSON_UNESCAPED_UNICODE), "\n";
    }
    exit(0);
}

// ---------- 라이브러리 수준 ----------
$r = child(array('case' => 'scan', 'file' => $tmp.'/iso2022.xml'));
check($r['status'] === 'exception' && bounded($r), '3262 securityScan: ISO-2022 전환 시퀀스로 감춘 DOCTYPE 거부', brief($r));
$r = child(array('case' => 'scan', 'file' => $tmp.'/latin1.xml'));
check($r['status'] === 'exception', '3262 securityScan: UTF-8·UTF-16 외 선언 인코딩 거부', $r);
$r = child(array('case' => 'scan', 'file' => $tmp.'/utf16-doctype.xml'));
check($r['status'] === 'exception', '3262 securityScan: UTF-16(NUL 삽입) DOCTYPE 거부', $r);
$r = child(array('case' => 'scan', 'file' => $tmp.'/utf32.xml'));
check($r['status'] === 'exception', '3262 securityScan: UTF-32 문서 거부', $r);
$r = child(array('case' => 'scan', 'file' => $tmp.'/utf16-clean.xml'));
check($r['status'] === 'ok', '3262 securityScan: DOCTYPE 없는 UTF-16 문서 허용', $r);
$r = child(array('case' => 'scan', 'file' => $tmp.'/utf8.xml'));
check($r['status'] === 'ok', '3262 securityScan: 정상 UTF-8 공유 문자열 허용', $r);
$r = child(array('case' => 'libxml'));
check(isset($r['default']) && $r['default'] && !($r['default'] & $r['danger']) && $r['zero'] && !($r['zero'] & $r['danger']) && !($r['forced'] & $r['danger']),
    '3262 libxml 기본 옵션에 DTD 로드·엔티티 치환이 없고 0·위험 옵션 지정도 무력화', $r);
$r = child(array('case' => 'reader', 'reader' => 'Excel2007', 'file' => $tmp.'/iso2022.xlsx'));
check($r['status'] === 'exception' && bounded($r), '3262 Excel2007 리더가 ISO-2022 xlsx를 빠르게 거부', brief($r));

$r = child(array('case' => 'reader', 'reader' => 'Gnumeric', 'file' => $tmp.'/bomb.gnumeric'));
check($r['status'] === 'exception' && bounded($r, 50331648), '3263 Gnumeric 리더 gzip 해제 상한(32MiB)에서 예외', brief($r));

$r = child(array('case' => 'reader', 'reader' => 'OOCalc', 'file' => $tmp.'/rows.ods'));
check($r['status'] === 'exception' && bounded($r), '3264 OOCalc 리더 행 반복 100만 거부', brief($r));
$r = child(array('case' => 'reader', 'reader' => 'OOCalc', 'file' => $tmp.'/cols.ods'));
check($r['status'] === 'exception' && bounded($r), '3264 OOCalc 리더 행×열 반복 셀 예산 초과 거부', brief($r));
$r = child(array('case' => 'reader', 'reader' => 'OOCalc', 'rdo' => true, 'file' => $tmp.'/ods-ok.ods', 'dump' => 'B1'));
check($r['status'] === 'ok' && bounded($r) && ($r['cells'] ?? null) === array(array('x', 'x')), '3264 OOCalc 정상 끝 행 반복(1048576행까지 빈 행) 허용', brief($r) + array('cells' => $r['cells'] ?? null));

foreach (array('ssat-loop.xls' => 'SSAT 자기 순환', 'dir-loop.xls' => '디렉터리 스트림 자기 순환', 'sat-count.xls' => 'SAT 섹터 수 과장', 'chain-range.xls' => '파일 밖 섹터 참조') as $f => $label) {
    $r = child(array('case' => 'reader', 'reader' => 'Excel5', 'file' => $tmp.'/'.$f));
    check($r['status'] === 'cannot_read' && bounded($r), '3265 Excel5 canRead: '.$label.' 거부', brief($r));
}

$r = child(array('case' => 'reader', 'reader' => 'Excel2007', 'rdo' => true, 'file' => $tmp.'/huge-row.xlsx'));
check($r['status'] === 'exception' && bounded($r), '3266 Excel2007 행 번호 10억(collapsed·outlineLevel, readDataOnly) 거부', brief($r));
$r = child(array('case' => 'reader', 'reader' => 'Excel2007', 'rdo' => true, 'file' => $tmp.'/huge-cell.xlsx'));
check($r['status'] === 'exception' && bounded($r), '3266 Excel2007 셀 좌표 행 9999999 거부', brief($r));
$r = child(array('case' => 'dimensions'));
check(($r['row'] ?? '') === 'exception' && ($r['column'] ?? '') === 'exception' && ($r['legal'] ?? '') === 'ok' && $r['highest_row'] == 1048576,
    '3266 3267 Worksheet 행·열 차원은 1048576행·XFD열까지만 생성', $r);

$r = child(array('case' => 'reader', 'reader' => 'Excel2007', 'rdo' => false, 'file' => $tmp.'/huge-cols.xlsx'));
check($r['status'] === 'exception' && bounded($r), '3267 Excel2007 열 범위 max=1억 거부', brief($r));
$r = child(array('case' => 'reader', 'reader' => 'Excel2007', 'rdo' => false, 'file' => $tmp.'/neg-cols.xlsx'));
check($r['status'] === 'exception' && bounded($r), '3267 Excel2007 음수 min 열 범위 거부', brief($r));
$r = child(array('case' => 'reader', 'reader' => 'Excel2007', 'rdo' => false, 'file' => $tmp.'/ok-cols.xlsx'));
check($r['status'] === 'ok' && bounded($r), '3267 Excel2007 정상 전체 열 범위(1~16384) 허용', brief($r));
$r = child(array('case' => 'columns'));
check(($r['bad'] ?? -1) === 0 && ($r['beyond'] ?? '') === 'exception' && ($r['deprecations'] ?? -1) === 0,
    '3267 열 번호↔문자 변환 0~18277 일치, 실수→chr 경고 없음, 18278 이상 거부', $r);

// ---------- 공통 업로드 검사(헬퍼) ----------
function helper($file, $name, $options = array(), $extra = array())
{
    global $tmp;
    return child(array_merge(array('case' => 'helper', 'file' => $tmp.'/'.$file, 'name' => $name, 'options' => $options), $extra));
}
$malicious = array(
    array('iso2022.xlsx', 'items.xlsx', '3262 ISO-2022 DOCTYPE xlsx'),
    array('gnumeric.xlsx', 'items.xlsx', '3263 gzip Gnumeric(.xlsx 이름)'),
    array('gnumeric.xls', 'items.xls', '3263 gzip Gnumeric(.xls 이름)'),
    array('ods.xlsx', 'items.xlsx', '3264 ODS 행 반복(.xlsx 이름)'),
    array('ssat-loop.xls', 'items.xls', '3265 OLE SSAT 순환 xls'),
    array('dir-loop.xls', 'items.xls', '3265 OLE 디렉터리 순환 xls'),
    array('sat-count.xls', 'items.xls', '3265 OLE SAT 섹터 수 과장 xls'),
    array('huge-row.xlsx', 'items.xlsx', '3266 행 번호 10억 xlsx'),
    array('huge-cell.xlsx', 'items.xlsx', '3266 셀 행 9999999 xlsx'),
    array('many-rows.xlsx', 'items.xlsx', '3266 최대 행(10000) 초과 xlsx'),
    array('zip-as.xls', 'items.xls', '형식 위장: ZIP 내용 + .xls'),
    array('ole-as.xlsx', 'items.xlsx', '형식 위장: OLE 내용 + .xlsx'),
    array('html.xls', 'items.xls', '형식 위장: HTML 표 + .xls'),
    array('csv.xlsx', 'items.xlsx', '형식 위장: CSV + .xlsx'),
    array('spreadsheetml.xls', 'items.xls', '형식 위장: SpreadsheetML(XML) + .xls'),
    array('rows.ods', 'items.ods', '확장자 .ods'),
    array('bomb.gnumeric', 'items.gnumeric', '확장자 .gnumeric'),
    array('valid.xlsx', 'items.xlsm', '확장자 .xlsm'),
    array('empty.xls', 'items.xls', '빈 파일'),
);
foreach ($malicious as $m) {
    $r = helper($m[0], $m[1], array('max_column' => 'AN'));
    check(rejected($r), '헬퍼 파싱 전·중 거부: '.$m[2], brief($r));
}
$r = helper('huge-cols.xlsx', 'items.xlsx', array('max_column' => 'B'));
check($r['status'] === 'loaded' && bounded($r) && $r['num_rows'] === 1, '3267 헬퍼: 열 범위 1억은 데이터 전용 읽기로 무시되어 빠르게 처리', brief($r));
$r = helper('neg-cols.xlsx', 'items.xlsx', array('max_column' => 'B'));
check(in_array($r['status'], array('loaded', 'rejected'), true) && bounded($r), '3267 헬퍼: 음수 열 범위도 빠르게 끝남', brief($r));
$r = helper('last-row.xlsx', 'items.xlsx', array('max_column' => 'B'));
check($r['status'] === 'loaded' && bounded($r) && $r['num_rows'] === 1, '3266 헬퍼: 행 서식만 있는 1048576행은 반복 범위에서 제외(데이터 행 1)', brief($r));
$r = helper('valid.xlsx', 'big.xlsx', array(), array('max_size' => 1024));
check(rejected($r) && strpos($r['message'], '크기') !== false, '크기 상한 초과 거부', $r);
$r = child(array('case' => 'helper', 'file' => $tmp.'/pad.xlsx', 'name' => 'pad.xlsx', 'defines' => array('G5_SPREADSHEET_UPLOAD_MAX_UNZIP_SIZE' => 1048576)));
check(rejected($r), 'xlsx 압축 해제 합계 상한(설정값) 초과 거부', $r);
$r = child(array('case' => 'helper', 'file' => $tmp.'/valid.xlsx', 'name' => 'v.xlsx', 'defines' => array('G5_SPREADSHEET_UPLOAD_MAX_ZIP_ENTRIES' => 3)));
check(rejected($r), 'xlsx 파일 수 상한(설정값) 초과 거부', $r);
$r = child(array('case' => 'helper', 'file' => $tmp.'/many-rows.xlsx', 'name' => 'v.xlsx', 'options' => array('max_column' => 'B'), 'defines' => array('G5_SPREADSHEET_UPLOAD_MAX_ROWS' => 30000)));
check($r['status'] === 'loaded' && $r['num_rows'] === 20000, '최대 행 상한은 설정값으로 늘릴 수 있음', brief($r));

$r = child(array('case' => 'memlimit', 'file' => $tmp.'/valid.xlsx', 'limit' => '-1'));
check(($r['loaded'] ?? false) && ($r['after'] ?? '') === '256M', 'memory_limit 무제한(-1)이면 읽기 전에 256M으로 제한', $r);
$r = child(array('case' => 'memlimit', 'file' => $tmp.'/valid.xlsx', 'limit' => '100M'));
check(($r['loaded'] ?? false) && ($r['after'] ?? '') === '100M', '정한 memory_limit은 바꾸지 않음', $r);

// 정상 흐름
$r = helper('valid.xlsx', '휴대폰번호.xlsx', array('max_column' => 'B'));
check($r['status'] === 'loaded' && bounded($r) && $r['num_rows'] === 2 && $r['cells'] === array(array('홍길동', '010-1234-5678'), array('김철수', '01098765432')),
    '정상 xlsx(SMS A~B열) 읽기, C열은 읽지 않음', brief($r) + array('cells' => $r['cells'] ?? null));
$r = helper('utf16-clean.xlsx', 'u.xlsx', array('max_column' => 'B'));
check($r['status'] === 'loaded' && ($r['cells'][0] ?? null) === array('유니코드', '010-2222-3333'), 'UTF-16 공유 문자열 xlsx 읽기', brief($r) + array('cells' => $r['cells'] ?? null));
$r = helper('item.xls', '상품.xls', array('max_column' => 'AN'));
$expect = $item_rows;
$expect[1][17] = 25000;
check($r['status'] === 'loaded' && bounded($r) && $r['num_rows'] === 4 && count($r['cells'][2] ?? array()) === 40
    && array_map('strval', $r['cells'][2]) === $expect[0] && array_map('strval', $r['cells'][3]) === array_map('strval', $expect[1]),
    '상품 템플릿 기반 xls(A~AN열, 3~4행) 읽기', brief($r) + array('row3' => $r['cells'][2] ?? null));
$r = child(array('case' => 'helper', 'file' => $template, 'name' => 'itemexcel.xls', 'options' => array('max_column' => 'AN')));
$o = child(array('case' => 'reader', 'reader' => 'Excel5', 'rdo' => false, 'file' => $template, 'dump' => 'AN2'));
check($r['status'] === 'loaded' && $r['num_rows'] === 2 && ($r['cells'][1][1] ?? '') === '기본분류' && ($r['cells'][1][39] ?? '') === '이미지10'
    && $r['cells'] === ($o['cells'] ?? null), '배포 템플릿 itemexcel.xls: 데이터 전용 읽기 결과가 서식 포함 읽기와 같음', brief($r));
$r = helper('delivery.xls', 'deliverylist-261119.xls', array('max_column' => 'J'));
$o = child(array('case' => 'reader', 'reader' => 'Excel5', 'rdo' => false, 'file' => $tmp.'/delivery.xls', 'dump' => 'J2'));
check($r['status'] === 'loaded' && strval($r['cells'][1][0] ?? '') === '2026111912345678' && ($r['cells'][1][8] ?? '') === 'CJ대한통운' && strval($r['cells'][1][9] ?? '') === '123456789012'
    && $r['cells'] == ($o['cells'] ?? null), '배송 일괄처리 xls(A·I·J열) 읽기, 서식 포함 읽기와 같은 값', brief($r) + array('cells' => $r['cells'] ?? null, 'formatted' => $o['cells'] ?? null));
$r = child(array('case' => 'check', 'file' => $tmp.'/sms.csv', 'name' => 'book.csv', 'allowed' => array('xls', 'xlsx', 'csv')));
check($r['status'] === 'ok' && ($r['type'] ?? '') === 'csv', 'SMS CSV는 크기·확장자 검사만 통과하면 기존 경로로 처리', $r);
$r = child(array('case' => 'check', 'file' => $tmp.'/sms.csv', 'name' => 'book.csv', 'allowed' => array('xls', 'xlsx', 'csv'), 'max_size' => 8));
check($r['status'] === 'rejected', 'SMS CSV에도 크기 상한 적용', $r);
$r = child(array('case' => 'check', 'file' => $tmp.'/sms.csv', 'name' => 'book.csv'));
check($r['status'] === 'rejected', '상품·배송(기본 허용 xls·xlsx)은 CSV 거부', $r);

// ---------- 처리기·화면 ----------
$src = function ($p) use ($root) { return is_file($root.'/'.$p) ? file_get_contents($root.'/'.$p) : ''; };
$handlers = array('adm/shop_admin/itemexcelupdate.php' => 'AN', 'adm/shop_admin/orderdeliveryupdate.php' => 'J', 'adm/sms_admin/num_book_file_upload.php' => 'B');
foreach ($handlers as $p => $col) {
    $s = $src($p);
    check($s !== '' && !preg_match('/PHPExcel_IOFactory::(load|identify|createReaderForFile)\s*\(/', $s) && strpos($s, "spreadsheet_upload.lib.php") !== false
        && strpos($s, "'max_column' => '$col'") !== false && strpos($s, "\$spreadsheet['num_rows']") !== false && strpos($s, 'getHighestRow()') === false,
        "처리기 $p: 자동 판별 없이 공통 검사 사용, 읽는 열 $col, 데이터 행까지만 반복");
}
$s = $src('adm/shop_admin/itemexcelupdate.php');
check(!preg_match('/set_time_limit\s*\(\s*0\s*\)/', $s) && strpos($s, 'set_time_limit(G5_SPREADSHEET_UPLOAD_TIME_LIMIT)') !== false, '상품 일괄등록 처리 시간 무제한 해제');
$s = $src('adm/sms_admin/num_book_file_upload.php');
check(strpos($s, "array('xls', 'xlsx', 'csv')") !== false && strpos($s, "case '.csv' :") !== false && preg_match("/case '\\.csv' :\s+\\\$data = file\(\\\$file\);/", $s), 'SMS CSV는 기존 file() 경로 유지(크기 검사만 추가)');
check(strpos($src('adm/shop_admin/itemexcel.php'), '(*.xlsx)') !== false && strpos($src('adm/shop_admin/orderdelivery.php'), '(*.xlsx)') !== false
    && strpos($src('adm/sms_admin/num_book_file.php'), 'xlsx 불가') === false && strpos($src('adm/sms_admin/num_book_file.php'), 'XLSX') !== false, '화면 안내가 xls·xlsx 허용으로 바뀜');

echo "TOTAL ", $passed + $failed, " PASS ", $passed, " FAIL ", $failed, "\n";
exit($failed ? 1 : 0);
