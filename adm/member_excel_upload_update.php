<?php
$sub_menu = '400300';
include_once('./_common.php');

function format_check($data, $format_code = '') {
    if($format_code == '') return false;

    return false;
}

function upload_error($code = '', $prefix = '') {
    switch($code) {
        case 'datetime':
            $message = $prefix . " 날짜 입력오류";
            break;
        default:
            return;
    }

    return $message;
}

try {
    // 엑셀 데이터가 많은 경우 대비 설정변경
    set_time_limit ( 0 );
    ini_set('memory_limit', '50M');

    auth_check_menu($auth, $sub_menu, "w");

    function only_number($n)
    {
        return preg_replace('/[^0-9]/', '', $n);
    }

    $is_upload_file = (isset($_FILES['excelfile']['tmp_name']) && $_FILES['excelfile']['tmp_name']) ? 1 : 0;

    if( ! $is_upload_file){
        alert("엑셀 파일을 업로드해 주세요.");
    }

    if($is_upload_file) {
        $file = $_FILES['excelfile']['tmp_name'];

        include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

        $objPHPExcel = PHPExcel_IOFactory::load($file);
        $sheet = $objPHPExcel->getSheet(0);

        $num_rows = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $range_str = "A"."3".":".$highestColumn.$num_rows;
        $datas = $sheet->rangeToArray($range_str, null, true, false);

        $succ_count = 0;
        $fail_count = 0;
        $total_count = count($datas);

        $total_array = array();
        
        foreach($datas as $key => $var) {
            $insert_array = array();
            $insert_array['row'] = $key + 3;
            $insert_array['mb_id'] = $var[1];
            $insert_array['mb_name'] = $var[2];
            $insert_array['mb_nick'] = $var[3];

            if( preg_match('/^\d+$/', $var[4]) == false) {
                $insert_array['error_message'] = upload_error("datetime", '닉네임');
            } else {
                $insert_array['mb_nick_date'] = date('Y-m-d', ($var[4] - 25569) * 86400);
            }

            $insert_array['mb_level'] = $var[5];
            $insert_array['status'] = $var[6];
            $insert_array['mb_point'] = $var[7];
            $insert_array['mb_email'] = $var[8];
            $insert_array['mb_homepage'] = $var[9];
            $insert_array['mb_hp'] = $var[10];
            $insert_array['mb_tel'] = $var[11];
            $insert_array['mb_certify'] = $var[12];
            $insert_array['mb_adult'] = $var[13];
            $insert_array['mb_email_certify'] = $var[14];
            $insert_array['zip_code'] = $var[15];
            $insert_array['mb_addr1'] = $var[16];
            $insert_array['mb_addr2'] = $var[17];
            $insert_array['mb_addr3'] = $var[18];
            $insert_array['mb_addr_jibeon'] = $var[19];
            $insert_array['mb_mailling'] = $var[20];
            $insert_array['mb_sns'] = $var[21];
            $insert_array['mb_open'] = $var[22];
            $insert_array['mb_open_date'] = $var[23];
            $insert_array['mb_signature'] = $var[24];
            $insert_array['mb_profile'] = $var[25];
            $insert_array['mb_memo'] = $var[26];
            $insert_array['mb_datetime'] = $var[27];
            $insert_array['mb_today_login'] = $var[28];
            $insert_array['mb_ip'] = $var[29];
            $insert_array['mb_leave_date'] = $var[30];
            $insert_array['mb_intercept_date'] = $var[31];
            $insert_array['mb_recommend'] = $var[32];
            $insert_array['mb_1'] = $var[33];
            $insert_array['mb_2'] = $var[34];
            $insert_array['mb_3'] = $var[35];
            $insert_array['mb_4'] = $var[36];
            $insert_array['mb_5'] = $var[37];
            $insert_array['mb_6'] = $var[38];
            $insert_array['mb_7'] = $var[39];
            $insert_array['mb_8'] = $var[40];
            $insert_array['mb_9'] = $var[41];
            $insert_array['mb_10'] = $var[42];

            if(isset($insert_array['error_message']) == false) {
                $insert_array['error_message'] = '';
            }

            array_push($total_array, $insert_array);
        }

        print_r2($total_array);
        exit;


        // for ($i = 3; $i <= $num_rows; $i++) {
        //     $total_count++;

        //     $j = 0;

        //     $rowData = $sheet->rangeToArray('A' . $i . ':' . $highestColumn . $i,
        //                                         NULL,
        //                                         TRUE,
        //                                         FALSE);

        //     $it_id              = (string) $rowData[0][$j++];
        //     $it_id              = preg_match('/[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)/', $it_id) ? addslashes(sprintf("%.0f", $it_id)) : preg_replace('/[^0-9a-z_\-]/i', '', $it_id);
        //     $ca_id              = addslashes($rowData[0][$j++]);
        //     $ca_id2             = addslashes($rowData[0][$j++]);
        //     $ca_id3             = addslashes($rowData[0][$j++]);
        //     $it_name            = addslashes($rowData[0][$j++]);
        //     $it_maker           = addslashes($rowData[0][$j++]);
        //     $it_origin          = addslashes($rowData[0][$j++]);
        //     $it_brand           = addslashes($rowData[0][$j++]);
        //     $it_model           = addslashes($rowData[0][$j++]);
        //     $it_type1           = addslashes($rowData[0][$j++]);
        //     $it_type2           = addslashes($rowData[0][$j++]);
        //     $it_type3           = addslashes($rowData[0][$j++]);
        //     $it_type4           = addslashes($rowData[0][$j++]);
        //     $it_type5           = addslashes($rowData[0][$j++]);
        //     $it_basic           = addslashes($rowData[0][$j++]);
        //     $it_explan          = addslashes($rowData[0][$j++]);
        //     $it_mobile_explan   = addslashes($rowData[0][$j++]);
        //     $it_cust_price      = addslashes(only_number($rowData[0][$j++]));
        //     $it_price           = addslashes(only_number($rowData[0][$j++]));
        //     $it_tel_inq         = addslashes($rowData[0][$j++]);
        //     $it_point           = addslashes(only_number($rowData[0][$j++]));
        //     $it_point_type      = addslashes(only_number($rowData[0][$j++]));
        //     $it_sell_email      = addslashes($rowData[0][$j++]);
        //     $it_use             = addslashes($rowData[0][$j++]);
        //     $it_stock_qty       = addslashes(only_number($rowData[0][$j++]));
        //     $it_noti_qty        = addslashes(only_number($rowData[0][$j++]));
        //     $it_buy_min_qty     = addslashes(only_number($rowData[0][$j++]));
        //     $it_buy_max_qty     = addslashes(only_number($rowData[0][$j++]));
        //     $it_notax           = addslashes(only_number($rowData[0][$j++]));
        //     $it_order           = addslashes(only_number($rowData[0][$j++]));
        //     $it_img1            = addslashes($rowData[0][$j++]);
        //     $it_img2            = addslashes($rowData[0][$j++]);
        //     $it_img3            = addslashes($rowData[0][$j++]);
        //     $it_img4            = addslashes($rowData[0][$j++]);
        //     $it_img5            = addslashes($rowData[0][$j++]);
        //     $it_img6            = addslashes($rowData[0][$j++]);
        //     $it_img7            = addslashes($rowData[0][$j++]);
        //     $it_img8            = addslashes($rowData[0][$j++]);
        //     $it_img9            = addslashes($rowData[0][$j++]);
        //     $it_img10           = addslashes($rowData[0][$j++]);
        //     $it_explan2         = strip_tags(trim($it_explan));

        //     if(!$it_id || !$ca_id || !$it_name) {
        //         $fail_count++;
        //         continue;
        //     }

        //     // it_id 중복체크
        //     $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        //     $row2 = sql_fetch($sql2);
        //     if(isset($row2['cnt']) && $row2['cnt']) {
        //         $fail_it_id[] = $it_id;
        //         $dup_it_id[] = $it_id;
        //         $dup_count++;
        //         $fail_count++;
        //         continue;
        //     }

        //     // 기본분류체크
        //     $sql2 = " select count(*) as cnt from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
        //     $row2 = sql_fetch($sql2);
        //     if(! (isset($row2['cnt']) && $row2['cnt'])) {
        //         $fail_it_id[] = $it_id;
        //         $fail_count++;
        //         continue;
        //     }

        //     $sql = " INSERT INTO {$g5['g5_shop_item_table']}
        //                  SET it_id = '$it_id',
        //                      ca_id = '$ca_id',
        //                      ca_id2 = '$ca_id2',
        //                      ca_id3 = '$ca_id3',
        //                      it_name = '$it_name',
        //                      it_maker = '$it_maker',
        //                      it_origin = '$it_origin',
        //                      it_brand = '$it_brand',
        //                      it_model = '$it_model',
        //                      it_type1 = '$it_type1',
        //                      it_type2 = '$it_type2',
        //                      it_type3 = '$it_type3',
        //                      it_type4 = '$it_type4',
        //                      it_type5 = '$it_type5',
        //                      it_basic = '$it_basic',
        //                      it_explan = '$it_explan',
        //                      it_explan2 = '$it_explan2',
        //                      it_mobile_explan = '$it_mobile_explan',
        //                      it_cust_price = '$it_cust_price',
        //                      it_price = '$it_price',
        //                      it_point = '$it_point',
        //                      it_point_type = '$it_point_type',
        //                      it_stock_qty = '$it_stock_qty',
        //                      it_noti_qty = '$it_noti_qty',
        //                      it_buy_min_qty = '$it_buy_min_qty',
        //                      it_buy_max_qty = '$it_buy_max_qty',
        //                      it_notax = '$it_notax',
        //                      it_use = '$it_use',
        //                      it_time = '".G5_TIME_YMDHIS."',
        //                      it_ip = '{$_SERVER['REMOTE_ADDR']}',
        //                      it_order = '$it_order',
        //                      it_tel_inq = '$it_tel_inq',
        //                      it_img1 = '$it_img1',
        //                      it_img2 = '$it_img2',
        //                      it_img3 = '$it_img3',
        //                      it_img4 = '$it_img4',
        //                      it_img5 = '$it_img5',
        //                      it_img6 = '$it_img6',
        //                      it_img7 = '$it_img7',
        //                      it_img8 = '$it_img8',
        //                      it_img9 = '$it_img9',
        //                      it_img10 = '$it_img10' ";

        //     sql_query($sql);

        //     $succ_count++;
        // }
    }
} catch (Exception $e) {
    alert("처리실패");
    exit;
}
$g5['title'] = '엑셀 회원 데이터 업로드 결과';
include_once ('./admin.head.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <dl id="excelfile_result">
        <dt>총 데이터수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt>등록 성공</dt>
        <dd><?php echo number_format($succ_count); ?></dd>
        <dt>등록 실패</dt>
        <dd><?php echo number_format($fail_count); ?></dd>
        <?php if($fail_count > 0) { ?>
        <dt>등록 실패 사유</dt>
        <dd><?php echo implode(', ', $fail_it_id); ?></dd>
        <?php } ?>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>

<?php
include_once ('./admin.tail.php');