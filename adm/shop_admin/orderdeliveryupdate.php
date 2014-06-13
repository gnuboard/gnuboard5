<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once('./admin.shop.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/icode.sms.lib.php');

auth_check($auth[$sub_menu], "w");

define("_ORDERMAIL_", true);

$sms_count = 0;
if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'])
{
    $SMS = new SMS;
	$SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
}

if($_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once(G5_LIB_PATH.'/Excel/reader.php');

    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('UTF-8');

    /***
    * if you want you can change 'iconv' to mb_convert_encoding:
    * $data->setUTFEncoder('mb');
    *
    **/

    /***
    * By default rows & cols indeces start with 1
    * For change initial index use:
    * $data->setRowColOffset(0);
    *
    **/



    /***
    *  Some function for formatting output.
    * $data->setDefaultFormat('%.2f');
    * setDefaultFormat - set format for columns with unknown formatting
    *
    * $data->setColumnFormat(4, '%.3f');
    * setColumnFormat - set format for column (apply only to number fields)
    *
    **/

    $data->read($file);

    /*


     $data->sheets[0]['numRows'] - count rows
     $data->sheets[0]['numCols'] - count columns
     $data->sheets[0]['cells'][$i][$j] - data from $i-row $j-column

     $data->sheets[0]['cellsInfo'][$i][$j] - extended info about cell

        $data->sheets[0]['cellsInfo'][$i][$j]['type'] = "date" | "number" | "unknown"
            if 'type' == "unknown" - use 'raw' value, because  cell contain value with format '0.00';
        $data->sheets[0]['cellsInfo'][$i][$j]['raw'] = value if cell without format
        $data->sheets[0]['cellsInfo'][$i][$j]['colspan']
        $data->sheets[0]['cellsInfo'][$i][$j]['rowspan']
    */

    error_reporting(E_ALL ^ E_NOTICE);

    $fail_od_id = array();
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;

    // $i 사용시 ordermail.inc.php의 $i 때문에 무한루프에 빠짐
    for ($k = 2; $k <= $data->sheets[0]['numRows']; $k++) {
        $total_count++;

        $od_id               = addslashes(trim($data->sheets[0]['cells'][$k][1]));
        $od_delivery_company = addslashes($data->sheets[0]['cells'][$k][9]);
        $od_invoice          = addslashes($data->sheets[0]['cells'][$k][10]);

        if(!$od_id || !$od_delivery_company || !$od_invoice) {
            $fail_count++;
            $fail_od_id[] = $od_id;
            continue;
        }

        // 주문정보
        $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
        if (!$od) {
            $fail_count++;
            $fail_od_id[] = $od_id;
            continue;
        }

        if($od['od_status'] != '준비') {
            $fail_count++;
            $fail_od_id[] = $od_id;
            continue;
        }

        $delivery['invoice'] = $od_invoice;
        $delivery['invoice_time'] = G5_TIME_YMDHIS;
        $delivery['delivery_company'] = $od_delivery_company;

        // 주문정보 업데이트
        order_update_delivery($od_id, $od['mb_id'], '배송', $delivery);
        change_status($od_id, '준비', '배송');

        $succ_count++;

        // SMS
        if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'] && $default['de_sms_use5']) {
            $sms_contents = conv_sms_contents($od_id, $default['de_sms_cont5']);
            if($sms_contents) {
                $receive_number = preg_replace("/[^0-9]/", "", $od['od_hp']);	// 수신자번호
                $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

                if($receive_number && $send_number) {
                    $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], $sms_contents, "");
                    $sms_count++;
                }
            }
        }

        // 메일
        if($config['cf_email_use'] && $_POST['od_send_mail'])
            include './ordermail.inc.php';

        // 에스크로 배송
        if($_POST['send_escrow'] && $od['od_tno'] && $od['od_escrow']) {
            $escrow_tno  = $od['od_tno'];
            $escrow_numb = $od_invoice;
            $escrow_corp = $od_delivery_company;

            include(G5_SHOP_PATH.'/'.$od['od_pg'].'/escrow.register.php');
        }
    }
}

// SMS
if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'] && $sms_count)
{
    $SMS->Send();
}

$g5['title'] = '엑셀 배송일괄처리 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>배송일괄처리를 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>총배송건수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt class="result_done">완료건수</dt>
        <dd class="result_done"><?php echo number_format($succ_count); ?></dd>
        <dt class="result_fail">실패건수</dt>
        <dd class="result_fail"><?php echo number_format($fail_count); ?></dd>
        <?php if($fail_count > 0) { ?>
        <dt>실패주문코드</dt>
        <dd><?php echo implode(', ', $fail_od_id); ?></dd>
        <?php } ?>
    </dl>

    <div class="btn_confirm01 btn_confirm">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>