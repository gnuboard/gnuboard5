<?php
$sub_menu = "900900";
include_once("./_common.php");

$bg_no = isset($_REQUEST['bg_no']) ? clean_xss_tags($_REQUEST['bg_no'], 1, 1) : '';
$no_hp = isset($_REQUEST['no_hp']) ? clean_xss_tags($_REQUEST['no_hp'], 1, 1) : '';

auth_check_menu($auth, $sub_menu, "r");

if ($bg_no != 'all' && $bg_no < 1)
    alert_just('다운로드 할 휴대폰번호 그룹을 선택해주세요.');

if ($bg_no == 'all')  $sql_bg = ""; else $sql_bg = "and bg_no='$bg_no'";

if ($no_hp) $sql_hp = ""; else  $sql_hp = "and bk_hp<>''";

$sql = "select count(*) as cnt from {$g5['sms5_book_table']} where 1 $sql_bg $sql_hp order by bk_name";
$total = sql_fetch($sql);

if (!$total['cnt']) alert_just('데이터가 없습니다.');

$qry = sql_query("select * from {$g5['sms5_book_table']} where 1 $sql_bg $sql_hp order by bk_name");

if(! function_exists('column_char')) {
    function column_char($i) {
        return chr( 65 + $i );
    }
}

include_once(G5_LIB_PATH.'/PHPExcel.php');
$excel = new PHPExcel();

$headers = array('이름', '전화번호');
$widths  = array(18, 25);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);
$rows = array();

$col = 0;

for($i=1; $res=sql_fetch_array($qry); $i++)
{
    //$res = array_map('iconv_euckr', $res);

    $hp = get_hp($res['bk_hp'], $hyphen);

    if ($no_hp && $res['bk_hp'] != '' && !$hp) continue;

    $rows[] = array($res['bk_name'], ' '.$hp);
}

$data = array_merge(array($headers), $rows);

$excel->setActiveSheetIndex(0)->getStyle( "A1:{$last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

$filename = "휴대폰번호목록-".date("ymd", time()).".xls";
if( is_ie() ) $filename = utf2euc($filename);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=".$filename);
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

exit;