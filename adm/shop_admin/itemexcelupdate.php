<?php
$sub_menu = '400300';
include_once('./_common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

auth_check($auth[$sub_menu], "w");

function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
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

    $dup_it_id = array();
    $fail_it_id = array();
    $dup_count = 0;
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;

    for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++) {
        $total_count++;

        $j = 1;

        $it_id              = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $ca_id              = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $ca_id2             = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $ca_id3             = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_name            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_maker           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_origin          = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_brand           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_model           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type1           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type2           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type3           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type4           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type5           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_basic           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_explan          = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_mobile_explan   = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_cust_price      = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_price           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_tel_inq         = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_point           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_point_type      = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_sell_email      = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_use             = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_stock_qty       = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_noti_qty        = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_buy_min_qty     = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_buy_max_qty     = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_notax           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_order           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_img1            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img2            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img3            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img4            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img5            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img6            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img7            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img8            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img9            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img10           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_explan2         = strip_tags(trim($it_explan));

        if(!$it_id || !$ca_id || !$it_name) {
            $fail_count++;
            continue;
        }

        // it_id 중복체크
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        $row2 = sql_fetch($sql2);
        if($row2['cnt']) {
            $fail_it_id[] = $it_id;
            $dup_it_id[] = $it_id;
            $dup_count++;
            $fail_count++;
            continue;
        }

        // 기본분류체크
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
        $row2 = sql_fetch($sql2);
        if(!$row2['cnt']) {
            $fail_it_id[] = $it_id;
            $fail_count++;
            continue;
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
        sql_query($sql);

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
        <dd><?php echo implode(', ', $fail_it_id); ?></dd>
        <?php } ?>
        <?php if($dup_count > 0) { ?>
        <dt>상품코드중복건수</dt>
        <dd><?php echo number_format($dup_count); ?></dd>
        <dt>중복상품코드</dt>
        <dd><?php echo implode(', ', $dup_it_id); ?></dd>
        <?php } ?>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>