<?php
$sub_menu = '400300';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/spreadsheet_upload.lib.php');

// 상품이 많을 경우 대비 설정변경. 처리 시간은 무제한으로 두지 않는다.
@set_time_limit(G5_SPREADSHEET_UPLOAD_TIME_LIMIT);
ini_set('memory_limit', '50M');

auth_check_menu($auth, $sub_menu, "w");

if (function_exists('check_request_origin')) check_request_origin(G5_ADMIN_URL);

// 업로드 파일을 읽기 전에 관리자 토큰(itemexcel.php 폼)을 확인한다.
check_admin_token();

function only_number($n)
{
    return preg_replace('/[^0-9]/', '', (string)$n);
}

// 파일을 골랐지만 업로드에 실패한 경우(크기 초과 등)도 공통 검사가 원인을 알려 준다.
$is_upload_file = (isset($_FILES['excelfile']['name']) && is_string($_FILES['excelfile']['name']) && $_FILES['excelfile']['name'] !== '') ? 1 : 0;

if( ! $is_upload_file){
    alert("엑셀 파일을 업로드해 주세요.");
}

if($is_upload_file) {
    // 확장자와 시그니처가 맞는 xls·xlsx만 형식별 리더로 읽는다. 상품코드~이미지10(A~AN)열만 읽는다.
    $spreadsheet = g5_spreadsheet_upload_open($_FILES['excelfile'], array('max_column' => 'AN'));
    if ($spreadsheet['error'] !== '') {
        alert($spreadsheet['error']);
    }
    $sheet = $spreadsheet['sheet'];

    $num_rows = $spreadsheet['num_rows'];
    $highestColumn = $spreadsheet['max_column'];

    $dup_it_id = array();
    $fail_it_id = array();
    $fail_reasons = array();
    $dup_count = 0;
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;

    for ($i = 3; $i <= $num_rows; $i++) {
        $total_count++;

        $j = 0;

        $rowData = $sheet->rangeToArray('A' . $i . ':' . $highestColumn . $i,
                                            NULL,
                                            TRUE,
                                            FALSE);

        $it_id              = shop_admin_clean_item_id($rowData[0][$j++]);
        // 분류코드는 영문·숫자만, 상품명 등 텍스트는 일반 상품 등록과 같은 정화를 적용한다.
        $ca_id              = shop_admin_clean_category_id($rowData[0][$j++]);
        $ca_id2             = shop_admin_clean_category_id($rowData[0][$j++]);
        $ca_id3             = shop_admin_clean_category_id($rowData[0][$j++]);
        $it_name            = addslashes(shop_admin_clean_item_text('it_name', $rowData[0][$j++]));
        $it_maker           = addslashes(shop_admin_clean_item_text('it_maker', $rowData[0][$j++]));
        $it_origin          = addslashes(shop_admin_clean_item_text('it_origin', $rowData[0][$j++]));
        $it_brand           = addslashes(shop_admin_clean_item_text('it_brand', $rowData[0][$j++]));
        $it_model           = addslashes(shop_admin_clean_item_text('it_model', $rowData[0][$j++]));
        $it_type1           = addslashes((string)$rowData[0][$j++]);
        $it_type2           = addslashes((string)$rowData[0][$j++]);
        $it_type3           = addslashes((string)$rowData[0][$j++]);
        $it_type4           = addslashes((string)$rowData[0][$j++]);
        $it_type5           = addslashes((string)$rowData[0][$j++]);
        $it_basic           = addslashes(shop_admin_clean_item_text('it_basic', $rowData[0][$j++]));
        $it_explan          = addslashes((string)$rowData[0][$j++]);
        $it_mobile_explan   = addslashes((string)$rowData[0][$j++]);
        $it_cust_price      = addslashes(only_number($rowData[0][$j++]));
        $it_price           = addslashes(only_number($rowData[0][$j++]));
        $it_tel_inq         = addslashes((string)$rowData[0][$j++]);
        $it_point           = addslashes(only_number($rowData[0][$j++]));
        $it_point_type      = addslashes(only_number($rowData[0][$j++]));
        $it_sell_email      = addslashes((string)$rowData[0][$j++]);
        $it_use             = addslashes((string)$rowData[0][$j++]);
        $it_stock_qty       = addslashes(only_number($rowData[0][$j++]));
        $it_noti_qty        = addslashes(only_number($rowData[0][$j++]));
        $it_buy_min_qty     = addslashes(only_number($rowData[0][$j++]));
        $it_buy_max_qty     = addslashes(only_number($rowData[0][$j++]));
        $it_notax           = addslashes(only_number($rowData[0][$j++]));
        $it_order           = addslashes(only_number($rowData[0][$j++]));
        $it_img1            = addslashes((string)$rowData[0][$j++]);
        $it_img2            = addslashes((string)$rowData[0][$j++]);
        $it_img3            = addslashes((string)$rowData[0][$j++]);
        $it_img4            = addslashes((string)$rowData[0][$j++]);
        $it_img5            = addslashes((string)$rowData[0][$j++]);
        $it_img6            = addslashes((string)$rowData[0][$j++]);
        $it_img7            = addslashes((string)$rowData[0][$j++]);
        $it_img8            = addslashes((string)$rowData[0][$j++]);
        $it_img9            = addslashes((string)$rowData[0][$j++]);
        $it_img10           = addslashes((string)$rowData[0][$j++]);
        $it_explan2         = strip_tags(trim($it_explan));

        if(!$it_id || !$ca_id || !$it_name) {
            $fail_count++;
            $fail_reasons[] = ($it_id ? $it_id : $i.'행').': 상품코드·기본분류·상품명 누락';
            continue;
        }

        // it_id 중복체크
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        $row2 = sql_fetch($sql2);
        if(isset($row2['cnt']) && $row2['cnt']) {
            $fail_it_id[] = $it_id;
            $dup_it_id[] = $it_id;
            $dup_count++;
            $fail_count++;
            $fail_reasons[] = $it_id.': 상품코드 중복';
            continue;
        }

        // 기본분류체크
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
        $row2 = sql_fetch($sql2);
        if(! (isset($row2['cnt']) && $row2['cnt'])) {
            $fail_it_id[] = $it_id;
            $fail_count++;
            $fail_reasons[] = $it_id.': 기본분류 없음';
            continue;
        }

        // 최고관리자가 아니면 기본분류와 값이 있는 2·3차 분류 모두 본인이 관리 회원인 분류여야 한다.
        if ($is_admin !== 'super') {
            $denied_ca_id = '';
            foreach (array($ca_id, $ca_id2, $ca_id3) as $check_ca_id) {
                if ($check_ca_id !== '' && !shop_admin_category_owner_check($check_ca_id)) {
                    $denied_ca_id = $check_ca_id;
                    break;
                }
            }
            if ($denied_ca_id !== '') {
                $fail_it_id[] = $it_id;
                $fail_count++;
                $fail_reasons[] = $it_id.': 분류 '.$denied_ca_id.'의 관리회원이 아님';
                continue;
            }
        }

        $sql = " INSERT INTO {$g5['g5_shop_item_table']}
                     SET it_id = '$it_id',
                         ca_id = '$ca_id',
                         ca_id2 = '$ca_id2',
                         ca_id3 = '$ca_id3',
                         it_name = '$it_name',
                         it_maker = '$it_maker',
                         it_origin = '$it_origin',
                         it_brand = '$it_brand',
                         it_model = '$it_model',
                         it_type1 = '$it_type1',
                         it_type2 = '$it_type2',
                         it_type3 = '$it_type3',
                         it_type4 = '$it_type4',
                         it_type5 = '$it_type5',
                         it_basic = '$it_basic',
                         it_explan = '$it_explan',
                         it_explan2 = '$it_explan2',
                         it_mobile_explan = '$it_mobile_explan',
                         it_cust_price = '$it_cust_price',
                         it_price = '$it_price',
                         it_point = '$it_point',
                         it_point_type = '$it_point_type',
                         it_stock_qty = '$it_stock_qty',
                         it_noti_qty = '$it_noti_qty',
                         it_buy_min_qty = '$it_buy_min_qty',
                         it_buy_max_qty = '$it_buy_max_qty',
                         it_notax = '$it_notax',
                         it_use = '$it_use',
                         it_time = '".G5_TIME_YMDHIS."',
                         it_ip = '{$_SERVER['REMOTE_ADDR']}',
                         it_order = '$it_order',
                         it_tel_inq = '$it_tel_inq',
                         it_img1 = '$it_img1',
                         it_img2 = '$it_img2',
                         it_img3 = '$it_img3',
                         it_img4 = '$it_img4',
                         it_img5 = '$it_img5',
                         it_img6 = '$it_img6',
                         it_img7 = '$it_img7',
                         it_img8 = '$it_img8',
                         it_img9 = '$it_img9',
                         it_img10 = '$it_img10' ";

        if (!sql_query($sql, false)) {
            $fail_it_id[] = $it_id;
            $fail_count++;
            $fail_reasons[] = $it_id.': 저장 실패(입력값 길이·형식 확인)';
            continue;
        }

        $succ_count++;
    }
}

$g5['title'] = '상품 엑셀일괄등록 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>상품등록을 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>총상품수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt>완료건수</dt>
        <dd><?php echo number_format($succ_count); ?></dd>
        <dt>실패건수</dt>
        <dd><?php echo number_format($fail_count); ?></dd>
        <?php if($fail_count > 0) { ?>
        <dt>실패상품코드</dt>
        <dd><?php echo get_text(implode(', ', $fail_it_id)); ?></dd>
        <dt>실패사유</dt>
        <dd><?php echo implode('<br>', array_map('get_text', $fail_reasons)); ?></dd>
        <?php } ?>
        <?php if($dup_count > 0) { ?>
        <dt>상품코드중복건수</dt>
        <dd><?php echo number_format($dup_count); ?></dd>
        <dt>중복상품코드</dt>
        <dd><?php echo get_text(implode(', ', $dup_it_id)); ?></dd>
        <?php } ?>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');