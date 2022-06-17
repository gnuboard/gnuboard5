<?php
$sub_menu = '400300';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();

if ($w == '' || $w == 'u')
    auth_check_menu($auth, $sub_menu, "w");
else if ($w == 'd')
    auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

@mkdir(G5_DATA_PATH."/item", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/item", G5_DIR_PERMISSION);

// input vars 체크
check_input_vars();

$ca_id = isset($_POST['ca_id']) ? preg_replace('/[^0-9a-z]/i', '', $_POST['ca_id']) : '';
$ca_id2 = isset($_POST['ca_id2']) ? preg_replace('/[^0-9a-z]/i', '', $_POST['ca_id2']) : '';
$ca_id3 = isset($_POST['ca_id3']) ? preg_replace('/[^0-9a-z]/i', '', $_POST['ca_id3']) : '';

if ($is_admin != 'super') {     // 최고관리자가 아니면 체크
    if( $w === '' ){
        $sql = "select ca_mb_id from {$g5['g5_shop_category_table']} where ca_id = '$ca_id'";
    } else {
        $sql = "select b.ca_mb_id from {$g5['g5_shop_item_table']} a , {$g5['g5_shop_category_table']} b where (a.ca_id = b.ca_id) and a.it_id = '$it_id'";
    }
    $checks = sql_fetch($sql);

    if( ! (isset($checks['ca_mb_id']) && $checks['ca_mb_id']) || $checks['ca_mb_id'] !== $member['mb_id'] ){
        alert("해당 분류의 관리회원이 아닙니다.");
    }
}

$it_img1 = $it_img2 = $it_img3 = $it_img4 = $it_img5 = $it_img6 = $it_img7 = $it_img8 = $it_img9 = $it_img10 = '';
// 파일정보
if($w == "u") {
    $sql = " select it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
                from {$g5['g5_shop_item_table']}
                where it_id = '$it_id' ";
    $file = sql_fetch($sql);

    $it_img1    = $file['it_img1'];
    $it_img2    = $file['it_img2'];
    $it_img3    = $file['it_img3'];
    $it_img4    = $file['it_img4'];
    $it_img5    = $file['it_img5'];
    $it_img6    = $file['it_img6'];
    $it_img7    = $file['it_img7'];
    $it_img8    = $file['it_img8'];
    $it_img9    = $file['it_img9'];
    $it_img10   = $file['it_img10'];
}

$it_img_dir = G5_DATA_PATH.'/item';

for($i=0;$i<=10;$i++){
    ${'it_img'.$i.'_del'} = ! empty($_POST['it_img'.$i.'_del']) ? 1 : 0;
}

// 파일삭제
if ($it_img1_del) {
    $file_img1 = $it_img_dir.'/'.clean_relative_paths($it_img1);
    @unlink($file_img1);
    delete_item_thumbnail(dirname($file_img1), basename($file_img1));
    $it_img1 = '';
}
if ($it_img2_del) {
    $file_img2 = $it_img_dir.'/'.clean_relative_paths($it_img2);
    @unlink($file_img2);
    delete_item_thumbnail(dirname($file_img2), basename($file_img2));
    $it_img2 = '';
}
if ($it_img3_del) {
    $file_img3 = $it_img_dir.'/'.clean_relative_paths($it_img3);
    @unlink($file_img3);
    delete_item_thumbnail(dirname($file_img3), basename($file_img3));
    $it_img3 = '';
}
if ($it_img4_del) {
    $file_img4 = $it_img_dir.'/'.clean_relative_paths($it_img4);
    @unlink($file_img4);
    delete_item_thumbnail(dirname($file_img4), basename($file_img4));
    $it_img4 = '';
}
if ($it_img5_del) {
    $file_img5 = $it_img_dir.'/'.clean_relative_paths($it_img5);
    @unlink($file_img5);
    delete_item_thumbnail(dirname($file_img5), basename($file_img5));
    $it_img5 = '';
}
if ($it_img6_del) {
    $file_img6 = $it_img_dir.'/'.clean_relative_paths($it_img6);
    @unlink($file_img6);
    delete_item_thumbnail(dirname($file_img6), basename($file_img6));
    $it_img6 = '';
}
if ($it_img7_del) {
    $file_img7 = $it_img_dir.'/'.clean_relative_paths($it_img7);
    @unlink($file_img7);
    delete_item_thumbnail(dirname($file_img7), basename($file_img7));
    $it_img7 = '';
}
if ($it_img8_del) {
    $file_img8 = $it_img_dir.'/'.clean_relative_paths($it_img8);
    @unlink($file_img8);
    delete_item_thumbnail(dirname($file_img8), basename($file_img8));
    $it_img8 = '';
}
if ($it_img9_del) {
    $file_img9 = $it_img_dir.'/'.clean_relative_paths($it_img9);
    @unlink($file_img9);
    delete_item_thumbnail(dirname($file_img9), basename($file_img9));
    $it_img9 = '';
}
if ($it_img10_del) {
    $file_img10 = $it_img_dir.'/'.clean_relative_paths($it_img10);
    @unlink($file_img10);
    delete_item_thumbnail(dirname($file_img10), basename($file_img10));
    $it_img10 = '';
}

// 이미지업로드
if ($_FILES['it_img1']['name']) {
    if($w == 'u' && $it_img1) {
        $file_img1 = $it_img_dir.'/'.clean_relative_paths($it_img1);
        @unlink($file_img1);
        delete_item_thumbnail(dirname($file_img1), basename($file_img1));
    }
    $it_img1 = it_img_upload($_FILES['it_img1']['tmp_name'], $_FILES['it_img1']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img2']['name']) {
    if($w == 'u' && $it_img2) {
        $file_img2 = $it_img_dir.'/'.clean_relative_paths($it_img2);
        @unlink($file_img2);
        delete_item_thumbnail(dirname($file_img2), basename($file_img2));
    }
    $it_img2 = it_img_upload($_FILES['it_img2']['tmp_name'], $_FILES['it_img2']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img3']['name']) {
    if($w == 'u' && $it_img3) {
        $file_img3 = $it_img_dir.'/'.clean_relative_paths($it_img3);
        @unlink($file_img3);
        delete_item_thumbnail(dirname($file_img3), basename($file_img3));
    }
    $it_img3 = it_img_upload($_FILES['it_img3']['tmp_name'], $_FILES['it_img3']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img4']['name']) {
    if($w == 'u' && $it_img4) {
        $file_img4 = $it_img_dir.'/'.clean_relative_paths($it_img4);
        @unlink($file_img4);
        delete_item_thumbnail(dirname($file_img4), basename($file_img4));
    }
    $it_img4 = it_img_upload($_FILES['it_img4']['tmp_name'], $_FILES['it_img4']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img5']['name']) {
    if($w == 'u' && $it_img5) {
        $file_img5 = $it_img_dir.'/'.clean_relative_paths($it_img5);
        @unlink($file_img5);
        delete_item_thumbnail(dirname($file_img5), basename($file_img5));
    }
    $it_img5 = it_img_upload($_FILES['it_img5']['tmp_name'], $_FILES['it_img5']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img6']['name']) {
    if($w == 'u' && $it_img6) {
        $file_img6 = $it_img_dir.'/'.clean_relative_paths($it_img6);
        @unlink($file_img6);
        delete_item_thumbnail(dirname($file_img6), basename($file_img6));
    }
    $it_img6 = it_img_upload($_FILES['it_img6']['tmp_name'], $_FILES['it_img6']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img7']['name']) {
    if($w == 'u' && $it_img7) {
        $file_img7 = $it_img_dir.'/'.clean_relative_paths($it_img7);
        @unlink($file_img7);
        delete_item_thumbnail(dirname($file_img7), basename($file_img7));
    }
    $it_img7 = it_img_upload($_FILES['it_img7']['tmp_name'], $_FILES['it_img7']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img8']['name']) {
    if($w == 'u' && $it_img8) {
        $file_img8 = $it_img_dir.'/'.clean_relative_paths($it_img8);
        @unlink($file_img8);
        delete_item_thumbnail(dirname($file_img8), basename($file_img8));
    }
    $it_img8 = it_img_upload($_FILES['it_img8']['tmp_name'], $_FILES['it_img8']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img9']['name']) {
    if($w == 'u' && $it_img9) {
        $file_img9 = $it_img_dir.'/'.clean_relative_paths($it_img9);
        @unlink($file_img9);
        delete_item_thumbnail(dirname($file_img9), basename($file_img9));
    }
    $it_img9 = it_img_upload($_FILES['it_img9']['tmp_name'], $_FILES['it_img9']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img10']['name']) {
    if($w == 'u' && $it_img10) {
        $file_img10 = $it_img_dir.'/'.clean_relative_paths($it_img10);
        @unlink($file_img10);
        delete_item_thumbnail(dirname($file_img10), basename($file_img10));
    }
    $it_img10 = it_img_upload($_FILES['it_img10']['tmp_name'], $_FILES['it_img10']['name'], $it_img_dir.'/'.$it_id);
}

if ($w == "" || $w == "u")
{
    // 다음 입력을 위해서 옵션값을 쿠키로 한달동안 저장함
    //@setcookie("ck_ca_id",  $ca_id,  time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
    //@setcookie("ck_maker",  stripslashes($it_maker),  time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
    //@setcookie("ck_origin", stripslashes($it_origin), time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
    @set_cookie("ck_ca_id", $ca_id, time() + 86400*31);
    @set_cookie("ck_ca_id2", $ca_id2, time() + 86400*31);
    @set_cookie("ck_ca_id3", $ca_id3, time() + 86400*31);
    @set_cookie("ck_maker", stripslashes($it_maker), time() + 86400*31);
    @set_cookie("ck_origin", stripslashes($it_origin), time() + 86400*31);
}

// 관련상품을 삭제한 뒤에 경고가 노출되어 등록, 수정 없이 관련상품만 삭제될 수 있는 오류 수정 (squared2님,210617)
// 포인트 비율 값 체크
if(($it_point_type == 1 || $it_point_type == 2) && ($it_point < 0  || $it_point > 99))
    alert("포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.");

// 관련상품을 우선 삭제함
sql_query(" delete from {$g5['g5_shop_item_relation_table']} where it_id = '$it_id' ");

// 관련상품의 반대도 삭제
sql_query(" delete from {$g5['g5_shop_item_relation_table']} where it_id2 = '$it_id' ");

// 이벤트상품을 우선 삭제함
sql_query(" delete from {$g5['g5_shop_event_item_table']} where it_id = '$it_id' ");

// 선택옵션
sql_query(" delete from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '$it_id' "); // 기존선택옵션삭제

$option_count = (isset($_POST['opt_id']) && is_array($_POST['opt_id'])) ? count($_POST['opt_id']) : array();
$it_option_subject = '';
$it_supply_subject = '';

if($option_count) {
    // 옵션명
    $opt1_cnt = $opt2_cnt = $opt3_cnt = 0;
    for($i=0; $i<$option_count; $i++) {
        $post_opt_id = isset($_POST['opt_id'][$i]) ? preg_replace(G5_OPTION_ID_FILTER, '', strip_tags($_POST['opt_id'][$i])) : '';

        $opt_val = explode(chr(30), $post_opt_id);
        if(isset($opt_val[0]) && $opt_val[0])
            $opt1_cnt++;
        if(isset($opt_val[1]) && $opt_val[1])
            $opt2_cnt++;
        if(isset($opt_val[2]) && $opt_val[2])
            $opt3_cnt++;
    }

    if($opt1_subject && $opt1_cnt) {
        $it_option_subject = $opt1_subject;
        if($opt2_subject && $opt2_cnt)
            $it_option_subject .= ','.$opt2_subject;
        if($opt3_subject && $opt3_cnt)
            $it_option_subject .= ','.$opt3_subject;
    }
}

// 추가옵션
sql_query(" delete from {$g5['g5_shop_item_option_table']} where io_type = '1' and it_id = '$it_id' "); // 기존추가옵션삭제

$supply_count = (isset($_POST['spl_id']) && is_array($_POST['spl_id'])) ? count($_POST['spl_id']) : array();
if($supply_count) {
    // 추가옵션명
    $arr_spl = array();
    for($i=0; $i<$supply_count; $i++) {
        $post_spl_id = isset($_POST['spl_id'][$i]) ? preg_replace(G5_OPTION_ID_FILTER, '', strip_tags($_POST['spl_id'][$i])) : '';

        $spl_val = explode(chr(30), $post_spl_id);
        if(!in_array($spl_val[0], $arr_spl))
            $arr_spl[] = $spl_val[0];
    }

    $it_supply_subject = implode(',', $arr_spl);
}

// 상품요약정보
$value_array = array();
$count_ii_article = (isset($_POST['ii_article']) && is_array($_POST['ii_article'])) ? count($_POST['ii_article']) : 0;
for($i=0; $i<$count_ii_article; $i++) {
    $key = isset($_POST['ii_article'][$i]) ? strip_tags($_POST['ii_article'][$i], '<br><span><strong><b>') : '';
    $val = isset($_POST['ii_value'][$i]) ? strip_tags($_POST['ii_value'][$i], '<br><span><strong><b>') : '';
    $value_array[$key] = $val;
}
$it_info_value = addslashes(serialize($value_array));

$it_name = isset($_POST['it_name']) ? strip_tags(clean_xss_attributes(trim($_POST['it_name']))) : '';

// KVE-2019-0708
$check_sanitize_keys = array(
'it_order',             // 출력순서
'it_maker',             // 제조사
'it_origin',            // 원산지
'it_brand',             // 브랜드
'it_model',             // 모델
'it_tel_inq',           // 전화문의
'it_use',               // 판매가능
'it_nocoupon',          // 쿠폰적용안함
'ec_mall_pid',          // 네이버쇼핑 상품ID
'it_sell_email',        // 판매자 e-mail
'it_price',             // 판매가격
'it_cust_price',        // 시중가격
'it_point_type',        // 포인트 유형
'it_point',             // 포인트
'it_supply_point',      // 추가옵션상품 포인트
'it_soldout',           // 상품품절
'it_stock_sms',         // 재입고SMS 알림
'it_stock_qty',         // 재고수량
'it_noti_qty',          // 재고 통보수량
'it_buy_min_qty',       // 최소구매수량
'it_notax',             // 상품과세 유형
'it_sc_type',           // 배송비 유형
'it_sc_method',         // 배송비 결제
'it_sc_price',          // 기본배송비
'it_sc_minimum',        // 배송비 상세조건
'it_type1',             // 상품유형(히트)
'it_type2',             // 상품유형(추천)
'it_type3',             // 상품유형(신상품)
'it_type4',             // 상품유형(인기)
'it_type5',             // 상품유형(할인)
);

foreach( $check_sanitize_keys as $key ){
    $$key = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
}

$it_basic = preg_replace('#<script(.*?)>(.*?)<\/script>#is', '', $it_basic);
$it_explan = isset($_POST['it_explan']) ? $_POST['it_explan'] : '';

if ($it_name == "")
    alert("상품명을 입력해 주십시오.");

$sql_common = " ca_id               = '$ca_id',
                ca_id2              = '$ca_id2',
                ca_id3              = '$ca_id3',
                it_skin             = '$it_skin',
                it_mobile_skin      = '$it_mobile_skin',
                it_name             = '$it_name',
                it_maker            = '$it_maker',
                it_origin           = '$it_origin',
                it_brand            = '$it_brand',
                it_model            = '$it_model',
                it_option_subject   = '$it_option_subject',
                it_supply_subject   = '$it_supply_subject',
                it_type1            = '$it_type1',
                it_type2            = '$it_type2',
                it_type3            = '$it_type3',
                it_type4            = '$it_type4',
                it_type5            = '$it_type5',
                it_basic            = '$it_basic',
                it_explan           = '$it_explan',
                it_explan2          = '".strip_tags(trim(clean_xss_attributes($it_explan)))."',
                it_mobile_explan    = '$it_mobile_explan',
                it_cust_price       = '$it_cust_price',
                it_price            = '$it_price',
                it_point            = '$it_point',
                it_point_type       = '$it_point_type',
                it_supply_point     = '$it_supply_point',
                it_notax            = '$it_notax',
                it_sell_email       = '$it_sell_email',
                it_use              = '$it_use',
                it_nocoupon         = '$it_nocoupon',
                it_soldout          = '$it_soldout',
                it_stock_qty        = '$it_stock_qty',
                it_stock_sms        = '$it_stock_sms',
                it_noti_qty         = '$it_noti_qty',
                it_sc_type          = '$it_sc_type',
                it_sc_method        = '$it_sc_method',
                it_sc_price         = '$it_sc_price',
                it_sc_minimum       = '$it_sc_minimum',
                it_sc_qty           = '$it_sc_qty',
                it_buy_min_qty      = '$it_buy_min_qty',
                it_buy_max_qty      = '$it_buy_max_qty',
                it_head_html        = '$it_head_html',
                it_tail_html        = '$it_tail_html',
                it_mobile_head_html = '$it_mobile_head_html',
                it_mobile_tail_html = '$it_mobile_tail_html',
                it_ip               = '{$_SERVER['REMOTE_ADDR']}',
                it_order            = '$it_order',
                it_tel_inq          = '$it_tel_inq',
                it_info_gubun       = '$it_info_gubun',
                it_info_value       = '$it_info_value',
                it_shop_memo        = '$it_shop_memo',
                ec_mall_pid         = '$ec_mall_pid',
                it_img1             = '$it_img1',
                it_img2             = '$it_img2',
                it_img3             = '$it_img3',
                it_img4             = '$it_img4',
                it_img5             = '$it_img5',
                it_img6             = '$it_img6',
                it_img7             = '$it_img7',
                it_img8             = '$it_img8',
                it_img9             = '$it_img9',
                it_img10            = '$it_img10',
                it_1_subj           = '$it_1_subj',
                it_2_subj           = '$it_2_subj',
                it_3_subj           = '$it_3_subj',
                it_4_subj           = '$it_4_subj',
                it_5_subj           = '$it_5_subj',
                it_6_subj           = '$it_6_subj',
                it_7_subj           = '$it_7_subj',
                it_8_subj           = '$it_8_subj',
                it_9_subj           = '$it_9_subj',
                it_10_subj          = '$it_10_subj',
                it_1                = '$it_1',
                it_2                = '$it_2',
                it_3                = '$it_3',
                it_4                = '$it_4',
                it_5                = '$it_5',
                it_6                = '$it_6',
                it_7                = '$it_7',
                it_8                = '$it_8',
                it_9                = '$it_9',
                it_10               = '$it_10'
                ";

if ($w == "")
{
    $it_id = isset($_POST['it_id']) ? $_POST['it_id'] : '';

    if (!trim($it_id)) {
        alert('상품 코드가 없으므로 상품을 추가하실 수 없습니다.');
    }

    $t_it_id = preg_replace("/[A-Za-z0-9\-_]/", "", $it_id);
    if($t_it_id)
        alert('상품 코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.');

    $sql_common .= " , it_time = '".G5_TIME_YMDHIS."' ";
    $sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
    $sql = " insert {$g5['g5_shop_item_table']}
                set it_id = '$it_id',
					$sql_common	";
    sql_query($sql);
}
else if ($w == "u")
{
    $sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
    $sql = " update {$g5['g5_shop_item_table']}
                set $sql_common
              where it_id = '$it_id' ";
    sql_query($sql);
}
/*
else if ($w == "d")
{
    if ($is_admin != 'super')
    {
        $sql = " select it_id from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['it_id'])
            alert("\'{$member['mb_id']}\' 님께서 삭제 할 권한이 없는 상품입니다.");
    }

    itemdelete($it_id);
}
*/

if ($w == "" || $w == "u")
{
    // 관련상품 등록
    $it_id2 = explode(",", $it_list);
    for ($i=0; $i<count($it_id2); $i++)
    {
        if (trim($it_id2[$i]))
        {
            $sql = " insert into {$g5['g5_shop_item_relation_table']}
                        set it_id  = '$it_id',
                            it_id2 = '".sql_real_escape_string($it_id2[$i])."',
                            ir_no = '$i' ";
            sql_query($sql, false);

            // 관련상품의 반대로도 등록
            $sql = " insert into {$g5['g5_shop_item_relation_table']}
                        set it_id  = '".sql_real_escape_string($it_id2[$i])."',
                            it_id2 = '$it_id',
                            ir_no = '$i' ";
            sql_query($sql, false);
        }
    }

    // 이벤트상품 등록
    $ev_id = explode(",", $ev_list);
    for ($i=0; $i<count($ev_id); $i++)
    {
        if (trim($ev_id[$i]))
        {
            $sql = " insert into {$g5['g5_shop_event_item_table']}
                        set ev_id = '".sql_real_escape_string($ev_id[$i])."',
                            it_id = '$it_id' ";
            sql_query($sql, false);
        }
    }
}

// 선택옵션등록
if($option_count) {
    $comma = '';
    $sql = " INSERT INTO {$g5['g5_shop_item_option_table']}
                    ( `io_id`, `io_type`, `it_id`, `io_price`, `io_stock_qty`, `io_noti_qty`, `io_use` )
                VALUES ";
    for($i=0; $i<$option_count; $i++) {
        $sql .= $comma . " ( '".sql_real_escape_string($_POST['opt_id'][$i])."', '0', '$it_id', '".sql_real_escape_string($_POST['opt_price'][$i])."', '".sql_real_escape_string($_POST['opt_stock_qty'][$i])."', '".sql_real_escape_string($_POST['opt_noti_qty'][$i])."', '".sql_real_escape_string($_POST['opt_use'][$i])."' )";
        $comma = ' , ';
    }

    sql_query($sql);
}

// 추가옵션등록
if($supply_count) {
    $comma = '';
    $sql = " INSERT INTO {$g5['g5_shop_item_option_table']}
                    ( `io_id`, `io_type`, `it_id`, `io_price`, `io_stock_qty`, `io_noti_qty`, `io_use` )
                VALUES ";
    for($i=0; $i<$supply_count; $i++) {
        $sql .= $comma . " ( '".sql_real_escape_string($_POST['spl_id'][$i])."', '1', '$it_id', '".sql_real_escape_string($_POST['spl_price'][$i])."', '".sql_real_escape_string($_POST['spl_stock_qty'][$i])."', '".sql_real_escape_string($_POST['spl_noti_qty'][$i])."', '".sql_real_escape_string($_POST['spl_use'][$i])."' )";
        $comma = ' , ';
    }

    sql_query($sql);
}

// 동일 분류내 상품 동일 옵션 적용
$ca_fields = '';
if(is_checked('chk_ca_it_skin'))                $ca_fields .= " , it_skin = '$it_skin' ";
if(is_checked('chk_ca_it_mobile_skin'))         $ca_fields .= " , it_mobile_skin = '$it_mobile_skin' ";
if(is_checked('chk_ca_it_basic'))               $ca_fields .= " , it_basic = '$it_basic' ";
if(is_checked('chk_ca_it_order'))               $ca_fields .= " , it_order = '$it_order' ";
if(is_checked('chk_ca_it_type'))                $ca_fields .= " , it_type1 = '$it_type1', it_type2 = '$it_type2', it_type3 = '$it_type3', it_type4 = '$it_type4', it_type5 = '$it_type5' ";
if(is_checked('chk_ca_it_maker'))               $ca_fields .= " , it_maker = '$it_maker' ";
if(is_checked('chk_ca_it_origin'))              $ca_fields .= " , it_origin = '$it_origin' ";
if(is_checked('chk_ca_it_brand'))               $ca_fields .= " , it_brand = '$it_brand' ";
if(is_checked('chk_ca_it_model'))               $ca_fields .= " , it_model = '$it_model' ";
if(is_checked('chk_ca_it_notax'))               $ca_fields .= " , it_notax = '$it_notax' ";
if(is_checked('chk_ca_it_sell_email'))          $ca_fields .= " , it_sell_email = '$it_sell_email' ";
if(is_checked('chk_ca_it_shop_memo'))           $ca_fields .= " , it_shop_memo = '$it_shop_memo' ";
if(is_checked('chk_ca_it_tel_inq'))             $ca_fields .= " , it_tel_inq = '$it_tel_inq' ";
if(is_checked('chk_ca_it_use'))                 $ca_fields .= " , it_use = '$it_use' ";
if(is_checked('chk_ca_it_nocoupon'))            $ca_fields .= " , it_nocoupon = '$it_nocoupon' ";
if(is_checked('chk_ca_it_soldout'))             $ca_fields .= " , it_soldout = '$it_soldout' ";
if(is_checked('chk_ca_it_info'))                $ca_fields .= " , it_info_gubun = '$it_info_gubun', it_info_value = '$it_info_value' ";
if(is_checked('chk_ca_it_price'))               $ca_fields .= " , it_price = '$it_price' ";
if(is_checked('chk_ca_it_cust_price'))          $ca_fields .= " , it_cust_price = '$it_cust_price' ";
if(is_checked('chk_ca_it_point'))               $ca_fields .= " , it_point = '$it_point' ";
if(is_checked('chk_ca_it_point_type'))          $ca_fields .= " , it_point_type = '$it_point_type' ";
if(is_checked('chk_ca_it_supply_point'))        $ca_fields .= " , it_supply_point = '$it_supply_point' ";
if(is_checked('chk_ca_it_stock_qty'))           $ca_fields .= " , it_stock_qty = '$it_stock_qty' ";
if(is_checked('chk_ca_it_noti_qty'))            $ca_fields .= " , it_noti_qty = '$it_noti_qty' ";
if(is_checked('chk_ca_it_sendcost'))            $ca_fields .= " , it_sc_type = '$it_sc_type', it_sc_method = '$it_sc_method', it_sc_price = '$it_sc_price', it_sc_minimum = '$it_sc_minimum', it_sc_qty = '$it_sc_qty' ";
if(is_checked('chk_ca_it_buy_min_qty'))         $ca_fields .= " , it_buy_min_qty = '$it_buy_min_qty' ";
if(is_checked('chk_ca_it_buy_max_qty'))         $ca_fields .= " , it_buy_max_qty = '$it_buy_max_qty' ";
if(is_checked('chk_ca_it_head_html'))           $ca_fields .= " , it_head_html = '$it_head_html' ";
if(is_checked('chk_ca_it_tail_html'))           $ca_fields .= " , it_tail_html = '$it_tail_html' ";
if(is_checked('chk_ca_it_mobile_head_html'))    $ca_fields .= " , it_mobile_head_html = '$it_mobile_head_html' ";
if(is_checked('chk_ca_it_mobile_tail_html'))    $ca_fields .= " , it_mobile_tail_html = '$it_mobile_tail_html' ";
if(is_checked('chk_ca_1'))                      $ca_fields .= " , it_1_subj = '$it_1_subj', it_1 = '$it_1' ";
if(is_checked('chk_ca_2'))                      $ca_fields .= " , it_2_subj = '$it_2_subj', it_2 = '$it_2' ";
if(is_checked('chk_ca_3'))                      $ca_fields .= " , it_3_subj = '$it_3_subj', it_3 = '$it_3' ";
if(is_checked('chk_ca_4'))                      $ca_fields .= " , it_4_subj = '$it_4_subj', it_4 = '$it_4' ";
if(is_checked('chk_ca_5'))                      $ca_fields .= " , it_5_subj = '$it_5_subj', it_5 = '$it_5' ";
if(is_checked('chk_ca_6'))                      $ca_fields .= " , it_6_subj = '$it_6_subj', it_6 = '$it_6' ";
if(is_checked('chk_ca_7'))                      $ca_fields .= " , it_7_subj = '$it_7_subj', it_7 = '$it_7' ";
if(is_checked('chk_ca_8'))                      $ca_fields .= " , it_8_subj = '$it_8_subj', it_8 = '$it_8' ";
if(is_checked('chk_ca_9'))                      $ca_fields .= " , it_9_subj = '$it_9_subj', it_9 = '$it_9' ";
if(is_checked('chk_ca_10'))                     $ca_fields .= " , it_10_subj = '$it_10_subj', it_10 = '$it_10' ";

if($ca_fields) {
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id = '$ca_id' ");
    if($ca_id2)
        sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id2 = '$ca_id2' ");
    if($ca_id3)
        sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id3 = '$ca_id3' ");
}

// 모든 상품 동일 옵션 적용
$all_fields = '';
if(is_checked('chk_all_it_skin'))                $all_fields .= " , it_skin = '$it_skin' ";
if(is_checked('chk_all_it_mobile_skin'))         $all_fields .= " , it_mobile_skin = '$it_mobile_skin' ";
if(is_checked('chk_all_it_basic'))               $all_fields .= " , it_basic = '$it_basic' ";
if(is_checked('chk_all_it_order'))               $all_fields .= " , it_order = '$it_order' ";
if(is_checked('chk_all_it_type'))                $all_fields .= " , it_type1 = '$it_type1', it_type2 = '$it_type2', it_type3 = '$it_type3', it_type4 = '$it_type4', it_type5 = '$it_type5' ";
if(is_checked('chk_all_it_maker'))               $all_fields .= " , it_maker = '$it_maker' ";
if(is_checked('chk_all_it_origin'))              $all_fields .= " , it_origin = '$it_origin' ";
if(is_checked('chk_all_it_brand'))               $all_fields .= " , it_brand = '$it_brand' ";
if(is_checked('chk_all_it_model'))               $all_fields .= " , it_model = '$it_model' ";
if(is_checked('chk_all_it_notax'))               $all_fields .= " , it_notax = '$it_notax' ";
if(is_checked('chk_all_it_sell_email'))          $all_fields .= " , it_sell_email = '$it_sell_email' ";
if(is_checked('chk_all_it_shop_memo'))           $all_fields .= " , it_shop_memo = '$it_shop_memo' ";
if(is_checked('chk_all_it_tel_inq'))             $all_fields .= " , it_tel_inq = '$it_tel_inq' ";
if(is_checked('chk_all_it_use'))                 $all_fields .= " , it_use = '$it_use' ";
if(is_checked('chk_all_it_nocoupon'))            $all_fields .= " , it_nocoupon = '$it_nocoupon' ";
if(is_checked('chk_all_it_soldout'))             $all_fields .= " , it_soldout = '$it_soldout' ";
if(is_checked('chk_all_it_info'))                $all_fields .= " , it_info_gubun = '$it_info_gubun', it_info_value = '$it_info_value' ";
if(is_checked('chk_all_it_price'))               $all_fields .= " , it_price = '$it_price' ";
if(is_checked('chk_all_it_cust_price'))          $all_fields .= " , it_cust_price = '$it_cust_price' ";
if(is_checked('chk_all_it_point'))               $all_fields .= " , it_point = '$it_point' ";
if(is_checked('chk_all_it_point_type'))          $all_fields .= " , it_point_type = '$it_point_type' ";
if(is_checked('chk_all_it_supply_point'))        $all_fields .= " , it_supply_point = '$it_supply_point' ";
if(is_checked('chk_all_it_stock_qty'))           $all_fields .= " , it_stock_qty = '$it_stock_qty' ";
if(is_checked('chk_all_it_noti_qty'))            $all_fields .= " , it_noti_qty = '$it_noti_qty' ";
if(is_checked('chk_all_it_sendcost'))            $all_fields .= " , it_sc_type = '$it_sc_type', it_sc_method = '$it_sc_method', it_sc_price = '$it_sc_price', it_sc_minimum = '$it_sc_minimum', it_sc_qty = '$it_sc_qty' ";
if(is_checked('chk_all_it_buy_min_qty'))         $all_fields .= " , it_buy_min_qty = '$it_buy_min_qty' ";
if(is_checked('chk_all_it_buy_max_qty'))         $all_fields .= " , it_buy_max_qty = '$it_buy_max_qty' ";
if(is_checked('chk_all_it_head_html'))           $all_fields .= " , it_head_html = '$it_head_html' ";
if(is_checked('chk_all_it_tail_html'))           $all_fields .= " , it_tail_html = '$it_tail_html' ";
if(is_checked('chk_all_it_mobile_head_html'))    $all_fields .= " , it_mobile_head_html = '$it_mobile_head_html' ";
if(is_checked('chk_all_it_mobile_tail_html'))    $all_fields .= " , it_mobile_tail_html = '$it_mobile_tail_html' ";
if(is_checked('chk_all_1'))                      $all_fields .= " , it_1_subj = '$it_1_subj', it_1 = '$it_1' ";
if(is_checked('chk_all_2'))                      $all_fields .= " , it_2_subj = '$it_2_subj', it_2 = '$it_2' ";
if(is_checked('chk_all_3'))                      $all_fields .= " , it_3_subj = '$it_3_subj', it_3 = '$it_3' ";
if(is_checked('chk_all_4'))                      $all_fields .= " , it_4_subj = '$it_4_subj', it_4 = '$it_4' ";
if(is_checked('chk_all_5'))                      $all_fields .= " , it_5_subj = '$it_5_subj', it_5 = '$it_5' ";
if(is_checked('chk_all_6'))                      $all_fields .= " , it_6_subj = '$it_6_subj', it_6 = '$it_6' ";
if(is_checked('chk_all_7'))                      $all_fields .= " , it_7_subj = '$it_7_subj', it_7 = '$it_7' ";
if(is_checked('chk_all_8'))                      $all_fields .= " , it_8_subj = '$it_8_subj', it_8 = '$it_8' ";
if(is_checked('chk_all_9'))                      $all_fields .= " , it_9_subj = '$it_9_subj', it_9 = '$it_9' ";
if(is_checked('chk_all_10'))                     $all_fields .= " , it_10_subj = '$it_10_subj', it_10 = '$it_10' ";

if($all_fields) {
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$all_fields} ");
}

$is_seo_title_edit = $w ? true : false;
if( function_exists('shop_seo_title_update') ) shop_seo_title_update($it_id, $is_seo_title_edit);

run_event('shop_admin_itemformupdate', $it_id, $w);

$qstr = "$qstr&amp;sca=$sca&amp;page=$page";

if ($w == "u") {
    goto_url("./itemform.php?w=u&amp;it_id=$it_id&amp;$qstr");
} else if ($w == "d")  {
    $qstr = "ca_id=$ca_id&amp;sfl=$sfl&amp;sca=$sca&amp;page=$page&amp;stx=".urlencode($stx)."&amp;save_stx=".urlencode($save_stx);
    goto_url("./itemlist.php?$qstr");
}

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
?>
<script>
    if (confirm("계속 입력하시겠습니까?"))
        location.href = "<?php echo "./itemform.php?".str_replace('&amp;', '&', $qstr); ?>";
    else
        location.href = "<?php echo "./itemlist.php?".str_replace('&amp;', '&', $qstr); ?>";
</script>
