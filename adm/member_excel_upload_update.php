<?php
$sub_menu = '400300';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

function only_number($n) {
    return preg_replace('/[^0-9]/', '', $n);
}

// 엑셀 데이터가 많은 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

auth_check_menu($auth, $sub_menu, "w");

$admin_password = $_POST['admin_password'];

if (empty($admin_password)) {
    alert("관리자 비밀번호를 입력해주세요.");
}

if ($member['mb_level'] != 10) {
    alert("최고관리자만 이용가능합니다.");
}

$is_upload_file = (isset($_FILES['excelfile']['tmp_name']) && $_FILES['excelfile']['tmp_name']) ? 1 : 0;

if (!$is_upload_file){
    alert("엑셀 파일을 업로드해 주세요.");
}

if ($is_upload_file) {
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

    $fail_array = array();
    
    foreach($datas as $key => $var) {

        try {
            $insert_array = array();
            $mb_id = addslashes($var[1]);
            $mb_name = addslashes($var[2]);
            $mb_nick = addslashes($var[3]);
            $mb_nick_time = addslashes($var[4]);
            $mb_level = addslashes($var[5]);
            $status = addslashes($var[6]);
            $mb_point = addslashes($var[7]);
            $mb_email = addslashes($var[8]);
            $mb_homepage = addcslashes($var[9]);
            $mb_hp = addcslashes($var[10]);
            $mb_tel = addcslashes($var[11]);
            $mb_certify_text = addcslashes($var[12]);
            $mb_adult = addcslashes($var[13]);
            $mb_email_certify = addcslashes($var[14]);
            $zip_code = addcslashes($var[15]);
            $mb_addr1 = addcslashes($var[16]);
            $mb_addr2 = addcslashes($var[17]);
            $mb_addr3 = addcslashes($var[18]);
            $mb_addr_jibeon = addcslashes($var[19]);
            $mb_mailling = addcslashes($var[20]);
            $mb_sns = addcslashes($var[21]);
            $mb_open = addcslashes($var[22]);
            $mb_open_date = addcslashes($var[23]);
            $mb_signature = addcslashes($var[24]);
            $mb_profile = addcslashes($var[25]);
            $mb_memo = addcslashes($var[26]);
            $mb_datetime = addcslashes($var[27]);
            $mb_today_login = addcslashes($var[28]);
            $mb_ip = addcslashes($var[29]);
            $mb_leave_date = addcslashes($var[30]);
            $mb_intercept_date = addcslashes($var[31]);
            $mb_recommend = addcslashes($var[32]);
            $mb_1 = addcslashes($var[33]);
            $mb_2 = addcslashes($var[34]);
            $mb_3 = addcslashes($var[35]);
            $mb_4 = addcslashes($var[36]);
            $mb_5 = addcslashes($var[37]);
            $mb_6 = addcslashes($var[38]);
            $mb_7 = addcslashes($var[39]);
            $mb_8 = addcslashes($var[40]);
            $mb_9 = addcslashes($var[41]);
            $mb_10 = addcslashes($var[42]);

            // 아이디 유효성 체크
            if (empty($mb_id)) throw new Exception("아이디 미입력 오류", $var);
            $result = sql_fetch("SELECT count(*) as `cnt` FROM `{$member_table}` WHERE `mb_id` = \"{$var[1]}\"");
            if ($result['cnt'] > 0) throw new Exception("아이디 중복 오류");

            // 이름 유효성 체크
            if (empty($mb_name)) throw new Exception("이름 미입력 오류", $var);

            // 닉네임 유효성 체크
            if (empty_mb_nick($mb_nick) != "") throw new Exception("닉네임 미입력 오류", $var);
            if (valid_mb_nick($mb_nick) != "") throw new Exception("닉네임 유효성 오류(공백없이 한글, 영문, 숫자)", $var);
            if (count_mb_nick($mb_nick) != "") throw new Exception("닉네임 유효성 오류(한글 2글자, 영문 4글자 이상 입력)", $var);
            if (exist_mb_nick($mb_nick, $mb_id) != "") throw new Exception("닉네임 중복 오류", $var);
            if (reserve_mb_nick($mb_nick) != "") throw new Exception("예약어로 등록된 닉네임 등록 오류", $var);
            
            if ($mb_nick_date == "") { 
                $mb_nick_date = date('Y-m-d', time());
            } else {
                if (preg_match('/^\d+$/', $mb_nick_date) == false) throw new Exception("닉네임 등록날짜 오류");
                $mb_nick_date = date('Y-m-d', ($mb_nick_time - 25569) * 86400);
            }

            if (empty($mb_level)) throw new Exception("회원 권한 미입력 오류", $var);
            if (preg_match('/^\d+$/', $mb_level) == false) throw new Exception("회원권한 유효성 오류(숫자만 입력)");
            if ($mb_level > 10 || $mb_level < 1) throw new Exception("회원권한 유효성 오류(회원권한은 1~10)");

            if (empty($mb_point)) $mb_point = 0;
            if (preg_match('/^\d+$/', $mb_point) == false) throw new Exception("포인트 유효성 오류(숫자만 입력)");

            // 이메일 유효성 체크
            if (empty_mb_email($mb_email) != "") throw new Exception("닉네임 미입력 오류", $var);
            if (valid_mb_email($mb_email) != "") throw new Exception("이메일 유효성 오류(E-mail 주소 형식이 아님)");
            if (prohibit_mb_email($mb_email) != "") throw new Exception("금지 메일 도메인 입력 오류");
            if (exist_mb_email($mb_email, $mb_id) != "") throw new Exception("이메일 중복 오류");

            if (!empty($mb_hp)) {
                if (valid_mb_hp($mb_hp) != "") throw new Exception("휴대폰번호 유효성 오류");
                if (exist_mb_hp($mb_hp, $mb_id) != "") throw new Exception("휴대폰번호 중복 오류");
            }

            $mb_certify = "";
            if ($mb_certify_text != "") {
                switch ($mb_certify_text) {
                    case "관리자":
                        $mb_certify = "admin";
                        break;
                    case "아이핀":
                        $mb_certify = "ipin";
                        break;
                    case "휴대폰":
                        $mb_certify = "hp";
                        break;
                    default:
                        $mb_certify = "";
                        break;
                }
            }

        } catch(Exception $e) {

        }
        


        // $sql = " INSERT INTzO {$m}"
        // array_push($total_array, $insert_array);
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