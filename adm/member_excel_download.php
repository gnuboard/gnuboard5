<?php
$sub_menu = "200100";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, 'r');

if(! function_exists('column_char')) {
    function column_char($i) {
        if($i >= 26) {
            $front = floor(($i/26)-1);
            $char = column_char($front);
            $char .= column_char($i%26);

            return $char;
        }
        return chr( 65 + $i );
    }
}

$sql = "SELECT * FROM `{$g5['member_table']}`";
$result = sql_query($sql);

include_once(G5_LIB_PATH.'/PHPExcel.php');

$headers = array('번호', '아이디', '이름', '닉네임', '닉네임 변경일', '회원권한', '상태', '포인트', 'E-mail', '홈페이지', '휴대폰번호', '전화번호', '본인확인', '성인인증', '메일인증', '우편번호', '기본주소', '상세주소', '참고항목', '지번', '메일 수신', 'SMS 수신', '정보 공개', '정보 공개일', '서명', '자기 소개', '메모', '회원가입일', '최근접속일', 'IP', '탈퇴일자', '접근차단일', '추천인', '여분필드1', '여분필드2', '여분필드3', '여분필드4', '여분필드5', '여분필드6', '여분필드7', '여분필드8', '여분필드9', '여분필드10');
$widths = array(10, 10, 10, 10, 20, 10, 10, 10, 50, 10, 15, 15, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 30, 20, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10);

$header_bgcolor = 'FFFFFF00';
$last_char = column_char(count($headers) - 1);

$message = "필수! 3번째 행부터 입력해주세요.\n번호 : 빈칸으로 두세요.(권장)\n주소 : 우편번호, 기본주소, 상세주소 3개 항목을 모두 입력하셔야 등록됩니다.\n지번 : 등록한 주소지가 지번 주소인 경우 : Y 입력 / 등록하는 주소가 도로명인 경우 비워두거나\n빨간색으로 표시된 항목은 필수입력 항목입니다.\n예제 (아이디=test 인 행) 지우고 사용해 주세요.";

$rows = array();
while($row = sql_fetch_array($result)) {
    $array = array();

    // 엑셀 샅태값 표시
    if(empty($row['mb_leave_date']) && empty($row['mb_intercept_date'])) {
        $status = "정상";
    } else if(!empty($row['mb_leave_date'])) {
        $status = "탈퇴";
    } else if(!empty($row['mb_intercept_date'])) {
        $status = "차단";
    } else {
        // 둘다 빈값이 아닌경우??
        $status = "기타";
    }

    switch($row['mb_certify']) {
        case 'hp':
            $certify = "휴대폰";
            break;
        case 'ipin':
            $certify = "아이핀";
            break;
        case 'admin':
            $certify = "관리자";
            break;
        default:
            $certify = "N";
            break;
    }

    // 우편번호 값
    $zip_code = $row['mb_zip1'].$row['mb_zip2'];

    $array = array( $row['mb_no'],
                    $row['mb_id'],
                    $row['mb_name'],
                    $row['mb_nick'],
                    $row['mb_nick_date'],
                    $row['mb_level'],
                    $status,
                    $row['mb_point'],
                    $row['mb_email'],
                    $row['mb_homepage'],
                    $row['mb_hp'],
                    $row['mb_tel'],
                    $certify,
                    $row['mb_adult'] == '1' ? 'Y' : 'N',
                    $row['mb_email_certify'] == '1' ? 'Y' : 'N',
                    $zip_code,
                    $row['mb_addr1'],
                    $row['mb_addr2'],
                    $row['mb_addr3'],
                    $row['mb_addr_jibeon'] == 'J' ? 'Y' : 'N',
                    $row['mb_mailling'] == '1' ? 'Y' : 'N',
                    $row['mb_sms'] == '1' ? 'Y' : 'N',
                    $row['mb_open'] == '1' ? 'Y' : 'N',
                    $row['mb_open_date'],
                    $row['mb_signature'],
                    $row['mb_profile'],
                    $row['mb_memo'],
                    $row['mb_datetime'],
                    $row['mb_today_login'],
                    $row['mb_ip'],
                    $row['mb_leave_date'],
                    $row['mb_intercept_date'],
                    $row['mb_recommend'],
                    $row['mb_1'],
                    $row['mb_2'],
                    $row['mb_3'],
                    $row['mb_4'],
                    $row['mb_5'],
                    $row['mb_6'],
                    $row['mb_7'],
                    $row['mb_8'],
                    $row['mb_9'],
                    $row['mb_10']);
    $rows[] = $array;
}

// $datas = array_merge(array($message), array($headers), $rows);

$excel = new PHPExcel();
$excel->getActiveSheet()->getDefaultStyle()->getFont()->setName('맑은 고딕');
$excel->setActiveSheetIndex(0)->getStyle("A")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
$excel->setActiveSheetIndex(0)->getStyle("A1:${last_char}1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->getActiveSheet()->getRowDimension(1)->setRowHeight(109.5);

foreach($widths as $key => $var) {
    $excel->getActiveSheet()->getColumnDimension(column_char($key))->setWidth($var);
}

$red_font_cells = array(2, 3, 4, 6, 9, 22, 23, 28);
foreach($red_font_cells as $var) {
    $excel->getActiveSheet()->getStyle(column_char($var-1)."2")->getFont()->getColor()->setARGB('FFFF0000');
}

$excel->getActiveSheet()->mergeCells("A1:${last_char}1");

$excel->getActiveSheet()->setCellValue("A1", $message);

foreach($headers as $key => $var) {
    $excel->getActiveSheet()->setCellValue(column_char($key)."2", $var);
}

foreach($rows as $key => $var) {
    foreach($var as $key2 => $var2) {
        // $excel->getActiveSheet()->setCellValue(column_char($key2).($key+3), $var2);
        if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $var2)) {
            $excel->getActiveSheet()->getStyle(column_char($key2).($key+3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
        } else if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})$/", $var2)) {
            $excel->getActiveSheet()->getStyle(column_char($key2).($key+3))->getNumberFormat()->setFormatCode("yyyy-mm-dd h:mm");
        }
        $excel->getActiveSheet()->setCellValue(column_char($key2).($key+3), $var2);
        
    }
}

// print_r2($excel->getActiveSheet());
// exit;


// $excel->getActiveSheet()->fromArray($datas, null, 'A1');

// print_r2($excel);

// $excel->getActiveSheet()->getStyle('E3')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
// $excel->getActiveSheet()->getStyle('X3')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
// $excel->getActiveSheet()->getStyle('AB3')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME2);
// $excel->getActiveSheet()->getStyle('AC3')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME2);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"memberlist-".date("ymd", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>