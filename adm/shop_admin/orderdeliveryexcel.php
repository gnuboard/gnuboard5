<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

// 주문정보
$sql = " select *
            from {$g5['g5_shop_order_table']}
            where od_misu = '0'
              and od_status = '준비'
            order by od_id desc ";
$result = sql_query($sql);

if(!@sql_num_rows($result))
    alert_close('배송처리할 주문 내역이 없습니다.');

function column_char($i) { return chr( 65 + $i ); }

if (phpversion() >= '5.2.0') {
    include_once(G5_LIB_PATH.'/PHPExcel.php');
    
    $headers = array('주문번호', '주문자명', '주문자전화1', '주문자전화2', '배송자명', '배송지전화1', '배송지전화2', '배송지주소', '배송회사', '운송장번호');
    $widths  = array(18, 15, 15, 15, 15, 15, 15, 50, 20, 20);
    $header_bgcolor = 'FFABCDEF';
    $last_char = column_char(count($headers) - 1);

    for($i=1; $row=sql_fetch_array($result); $i++) {
        $rows[] = 
                    array(' '.$row['od_id'], 
                          $row['od_name'], 
                          ' '.$row['od_tel'], 
                          ' '.$row['od_hp'], 
                          $row['od_b_name'], 
                          ' '.$row['od_b_tel'], 
                          ' '.$row['od_b_hp'], 
                          print_address($row['od_b_addr1'], $row['od_b_addr2'], $row['od_b_addr3'], $row['od_b_addr_jibeon']),
                          $row['od_delivery_company'],
                          $row['od_invoice']);
    }

    $data = array_merge(array($headers), $rows);

    $excel = new PHPExcel();
    $excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
    $excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
    foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    $excel->getActiveSheet()->fromArray($data,NULL,'A1');

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"deliverylist-".date("ymd", time()).".xls\"");
    header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');
} else {
    /*================================================================================
    php_writeexcel http://www.bettina-attack.de/jonny/view.php/projects/php_writeexcel/
    =================================================================================*/

    include_once(G5_LIB_PATH.'/Excel/php_writeexcel/class.writeexcel_workbook.inc.php');
    include_once(G5_LIB_PATH.'/Excel/php_writeexcel/class.writeexcel_worksheet.inc.php');

    $fname = tempnam(G5_DATA_PATH, "tmp-deliverylist.xls");
    $workbook = new writeexcel_workbook($fname);
    $worksheet = $workbook->addworksheet();

    // Put Excel data
    $data = array('주문번호', '주문자명', '주문자전화1', '주문자전화2', '배송자명', '배송지전화1', '배송지전화2', '배송지주소', '배송회사', '운송장번호');
    $data = array_map('iconv_euckr', $data);

    $col = 0;
    foreach($data as $cell) {
        $worksheet->write(0, $col++, $cell);
    }

    for($i=1; $row=sql_fetch_array($result); $i++) {
        $row = array_map('iconv_euckr', $row);

        $worksheet->write($i, 0, ' '.$row['od_id']);
        $worksheet->write($i, 1, $row['od_name']);
        $worksheet->write($i, 2, ' '.$row['od_tel']);
        $worksheet->write($i, 3, ' '.$row['od_hp']);
        $worksheet->write($i, 4, $row['od_b_name']);
        $worksheet->write($i, 5, ' '.$row['od_b_tel']);
        $worksheet->write($i, 6, ' '.$row['od_b_hp']);
        $worksheet->write($i, 7, print_address($row['od_b_addr1'], $row['od_b_addr2'], $row['od_b_addr3'], $row['od_b_addr_jibeon']));
        $worksheet->write($i, 8, $row['od_delivery_company']);
        $worksheet->write($i, 9, $row['od_invoice']);
    }

    $workbook->close();

    header("Content-Type: application/x-msexcel; name=\"deliverylist-".date("ymd", time()).".xls\"");
    header("Content-Disposition: inline; filename=\"deliverylist-".date("ymd", time()).".xls\"");
    $fh=fopen($fname, "rb");
    fpassthru($fh);
    unlink($fname);
}
?>