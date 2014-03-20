<?php
$sub_menu = "900900";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if ($bg_no != 'all' && $bg_no < 1)
    alert_just('다운로드 할 휴대폰번호 그룹을 선택해주세요.');

if ($bg_no == 'all')  $sql_bg = ""; else $sql_bg = "and bg_no='$bg_no'";

if ($no_hp) $sql_hp = ""; else  $sql_hp = "and bk_hp<>''";

$sql = "select count(*) as cnt from {$g5['sms5_book_table']} where 1 $sql_bg $sql_hp order by bk_name";
$total = sql_fetch($sql);

if (!$total['cnt']) alert_just('데이터가 없습니다.');

$qry = sql_query("select * from {$g5['sms5_book_table']} where 1 $sql_bg $sql_hp order by bk_name");

/*================================================================================
php_writeexcel http://www.bettina-attack.de/jonny/view.php/projects/php_writeexcel/
=================================================================================*/

include_once(G5_LIB_PATH.'/Excel/php_writeexcel/class.writeexcel_workbook.inc.php');
include_once(G5_LIB_PATH.'/Excel/php_writeexcel/class.writeexcel_worksheet.inc.php');

$fname = tempnam(G5_DATA_PATH, "tmp.xls");
$workbook = new writeexcel_workbook($fname);
$worksheet = $workbook->addworksheet();

$num2_format =& $workbook->addformat(array(num_format => '\0#'));

// Put Excel data
$data = array('이름', '전화번호');
$data = array_map('iconv_euckr', $data);

$col = 0;
foreach($data as $cell) {
    $worksheet->write(0, $col++, $cell);
}

for($i=1; $res=sql_fetch_array($qry); $i++)
{
    $res = array_map('iconv_euckr', $res);

    $hp = get_hp($res['bk_hp'], $hyphen);

    if ($no_hp && $res['bk_hp'] != '' && !$hp) continue;

    $worksheet->write($i, 0, $res['bk_name']);
    $worksheet->write($i, 1, $hp, $num2_format);
}

$workbook->close();

$filename = "휴대폰번호목록-".date("ymd", time()).".xls";
if( is_ie() ) $filename = utf2euc($filename);

header("Content-Type: application/x-msexcel; name=".$filename);
header("Content-Disposition: inline; filename=".$filename);
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

exit;
?>