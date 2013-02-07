<?
$sub_menu = "400300";
include_once("./_common.php");

if ($w == "u" || $w == "d")
    check_demo();

if ($w == '' || $w == 'u')
    auth_check($auth[$sub_menu], "w");
else if ($w == 'd')
    auth_check($auth[$sub_menu], "d");

// 상품삭제
// 메세지출력후 주문개별내역페이지로 이동
function itemdelete($it_id)
{
    global $g4, $is_admin;

    /*
    $str = $comma = $od_id = "";
    $sql = " select b.od_id
               from $g4[shop_cart_table] a,
                    $g4[shop_order_table] b
              where a.uq_id = b.uq_id
                and a.it_id = '$it_id'
                and a.ct_status != '쇼핑' ";
    $result = sql_query($sql);
    $i=0;
    while ($row = sql_fetch_array($result))
    {
        if (!$od_id)
            $od_id = $row[od_id];

        $i++;
        if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma$row[od_id]";
        $comma = " , ";
    }
    if ($str)
    {
        alert("이 상품과 관련된 주문이 총 {$i} 건 존재하므로 주문서를 삭제한 후 상품을 삭제하여 주십시오.\\n\\n$str", "./orderstatuslist.php?sort1=od_id&sel_field=od_id&search=$od_id");
    }
    */


	// 상품 이미지 삭제
    $files = scan_dir(G4_DATA_PATH.'/item/'.$it_id);
    for($i=0; $i<count($files); $i++) {
        @unlink(G4_DATA_PATH.'/item/'.$it_id.'/'.$files[$i]);
    }
    @rmdir(G4_DATA_PATH.'/item/'.$it_id);

    // 장바구니 삭제
	$sql = " delete from `{$g4['shop_cart_table']}` where it_id = '$it_id' ";
	sql_query($sql);

    // 이벤트삭제
    $sql = " delete from `{$g4['shop_event_item_table']}` where it_id = '$it_id' ";
	sql_query($sql);

    // 사용후기삭제
    $sql = " delete from `{$g4['shop_item_ps_table']}` where it_id = '$it_id' ";
	sql_query($sql);

    // 상품문의삭제
    $sql = " delete from `{$g4['shop_item_qa_table']}` where it_id = '$it_id' ";
	sql_query($sql);

    // 관련상품삭제
    $sql = " delete from `{$g4['shop_item_relation_table']}` where it_id = '$it_id' or it_id2 = '$it_id' ";
	sql_query($sql);

    // 선택옵션정보삭제
    $sql = " delete from `{$g4['shop_option_table']}` where it_id = '$it_id' ";
    sql_query($sql);

    // 추가옵션정보삭제
    $sql = " delete from `{$g4['shop_supplement_table']}` where it_id = '$it_id' ";
    sql_query($sql);

    // 상품요약정보삭제
    $sql = " delete from `{$g4['shop_item_info_table']}` where it_id = '$it_id' ";
    sql_query($sql);


    //------------------------------------------------------------------------
    // HTML 내용에서 에디터에 올라간 이미지의 경로를 얻어 삭제함
    //------------------------------------------------------------------------
    $sql = " select * from {$g4['shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);
    $s = $it['it_explan'];

    // img 태그의 src 중 data/editor 가 포함된 것만 추출
    preg_match_all("/<img[^>]*src=[\'\"]?([^>\'\"]+data\/editor[^>\'\"]+)[\'\"]?[^>]*>/", $s, $matchs);

    // 파일의 경로를 얻어 삭제
    for($i=0; $i<count($matchs[1]); $i++) {
        $imgurl = parse_url($matchs[1][$i]);
        $imgfile = $_SERVER['DOCUMENT_ROOT'].$imgurl['path'];
        if(file_exists($imgfile))
            @unlink($imgfile);
    }

    /*
    $img_file = Array();
    while($s) {
        $pos = strpos($s, "/data/cheditor");
        $s = substr($s, $pos, strlen($s));
        $pos = strpos($s, '"');

        // 결과값
        $file_path = substr($s, 0, $pos);
        if (!$file_path) break;

        $img_file[] = $file_path;

        $s = substr($s, $pos, strlen($s));
    }

    for($i=0;$i<count($img_file);$i++) {
        $f = $g4[path].$img_file[$i];
        if (file_exists($f))
            @unlink($f);
    }
    */
    //------------------------------------------------------------------------


    // 상품 삭제
	$sql = " delete from {$g4['shop_item_table']} where it_id = '$it_id' ";
	sql_query($sql);
}

// 디렉토리내 파일 리스트 배열로 얻기
function scan_dir($path)
{
    if(!is_dir($path))
        return;

    $dir = opendir($path);
    while (false !== ($filename = readdir($dir))) {
        if($filename == "." || $filename == "..")
            continue;

        $files[] = $filename;
    }

    return $files;
}

if($w == "" && !trim($it_id)) {
    alert("상품 코드가 없으므로 상품을 추가하실 수 없습니다.");
}

@mkdir(G4_DATA_PATH.'/item', 0707);
@chmod(G4_DATA_PATH.'/item', 0707);

// 파일정보
if($w == "u") {
    $sql = " select it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
                from {$g4['shop_item_table']}
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

$it_img_dir = G4_DATA_PATH.'/item/'.$it_id;

// 파일삭제
if ($it_img1_del) {
    @unlink("$it_img_dir/$it_img1");
    $it_img1 = "";
}
if ($it_img2_del) {
    @unlink("$it_img_dir/$it_img2");
    $it_img2 = "";
}
if ($it_img3_del) {
    @unlink("$it_img_dir/$it_img3");
    $it_img3 = "";
}
if ($it_img4_del) {
    @unlink("$it_img_dir/$it_img4");
    $it_img4 = "";
}
if ($it_img5_del) {
    @unlink("$it_img_dir/$it_img5");
    $it_img5 = "";
}
if ($it_img6_del) {
    @unlink("$it_img_dir/$it_img6");
    $it_img6 = "";
}
if ($it_img7_del) {
    @unlink("$it_img_dir/$it_img7");
    $it_img7 = "";
}
if ($it_img8_del) {
    @unlink("$it_img_dir/$it_img8");
    $it_img8 = "";
}
if ($it_img9_del) {
    @unlink("$it_img_dir/$it_img9");
    $it_img9 = "";
}
if ($it_img10_del) {
    @unlink("$it_img_dir/$it_img10");
    $it_img10 = "";
}

// 이미지업로드
if ($_FILES['it_img1']['name'])
    $it_img1 = it_img_upload($_FILES['it_img1']['tmp_name'], $_FILES['it_img1']['name'], $it_img_dir);
if ($_FILES['it_img2']['name'])
    $it_img2 = it_img_upload($_FILES['it_img2']['tmp_name'], $_FILES['it_img2']['name'], $it_img_dir);
if ($_FILES['it_img3']['name'])
    $it_img3 = it_img_upload($_FILES['it_img3']['tmp_name'], $_FILES['it_img3']['name'], $it_img_dir);
if ($_FILES['it_img4']['name'])
    $it_img4 = it_img_upload($_FILES['it_img4']['tmp_name'], $_FILES['it_img4']['name'], $it_img_dir);
if ($_FILES['it_img5']['name'])
    $it_img5 = it_img_upload($_FILES['it_img5']['tmp_name'], $_FILES['it_img5']['name'], $it_img_dir);
if ($_FILES['it_img6']['name'])
    $it_img6 = it_img_upload($_FILES['it_img6']['tmp_name'], $_FILES['it_img6']['name'], $it_img_dir);
if ($_FILES['it_img7']['name'])
    $it_img7 = it_img_upload($_FILES['it_img7']['tmp_name'], $_FILES['it_img7']['name'], $it_img_dir);
if ($_FILES['it_img8']['name'])
    $it_img8 = it_img_upload($_FILES['it_img8']['tmp_name'], $_FILES['it_img8']['name'], $it_img_dir);
if ($_FILES['it_img9']['name'])
    $it_img9 = it_img_upload($_FILES['it_img9']['tmp_name'], $_FILES['it_img9']['name'], $it_img_dir);
if ($_FILES['it_img10']['name'])
    $it_img10 = it_img_upload($_FILES['it_img10']['tmp_name'], $_FILES['it_img10']['name'], $it_img_dir);

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


// 관련상품을 우선 삭제함
sql_query(" delete from {$g4['shop_item_relation_table']} where it_id = '$it_id' ");

// 관련상품의 반대도 삭제
sql_query(" delete from {$g4['shop_item_relation_table']} where it_id2 = '$it_id' ");

// 이벤트상품을 우선 삭제함
sql_query(" delete from {$g4['shop_event_item_table']} where it_id = '$it_id' ");

// 개별배송비처리
if($default['de_send_cost_case'] == "개별배송") {
    if($it_sc_type == 3) { // 수량별
        $itcount = (int)preg_replace("/[^0-9]/", "", $_POST['it_count']);
        if(!$itcount) {
            alert('반복수량을 입력해 주세요.');
        }

        $condition = $itcount;
    } else if($it_sc_type == 1) { // 조건부무료
        $itminimum = (int)preg_replace("/[^0-9]/", "", $_POST['it_minimum']);
        if(!$itminimum) {
            alert('구매합계 금액을 입력해 주세요.');
        }

        $condition = $itminimum;
    } else {
        $condition = 0;
    }

    $it_sc_basic = preg_replace("/[^0-9]/", "", $it_sc_basic);
    if($it_sc_type && !$it_sc_basic) {
        alert('기본배송비를 입력해 주세요.');
    }
}

$sql_common = " ca_id               = '$ca_id',
                ca_id2              = '$ca_id2',
                ca_id3              = '$ca_id3',
                it_name             = '$it_name',
                it_gallery          = '$it_gallery',
                it_maker            = '$it_maker',
                it_origin           = '$it_origin',
                it_brand            = '$it_brand',
                it_model            = '$it_model',
                it_option_use       = '$it_option_use',
                it_opt1_subject     = '$it_opt1_subject',
                it_opt2_subject     = '$it_opt2_subject',
                it_opt3_subject     = '$it_opt3_subject',
                it_opt4_subject     = '$it_opt4_subject',
                it_opt5_subject     = '$it_opt5_subject',
                it_opt6_subject     = '$it_opt6_subject',
                it_opt1             = '$it_opt1',
                it_opt2             = '$it_opt2',
                it_opt3             = '$it_opt3',
                it_opt4             = '$it_opt4',
                it_opt5             = '$it_opt5',
                it_opt6             = '$it_opt6',
                it_supplement_use   = '$it_supplement_use',
                it_type1            = '$it_type1',
                it_type2            = '$it_type2',
                it_type3            = '$it_type3',
                it_type4            = '$it_type4',
                it_type5            = '$it_type5',
                it_basic            = '$it_basic',
                it_explan           = '$it_explan',
                it_explan_html      = '$it_explan_html',
                it_notax            = '$it_notax',
                it_cust_amount      = '$it_cust_amount',
                it_amount           = '$it_amount',
                it_amount2          = '$it_amount2',
                it_amount3          = '$it_amount3',
                it_point            = '$it_point',
                it_sell_email       = '$it_sell_email',
                it_use              = '$it_use',
                it_stock_qty        = '$it_stock_qty',
                it_nocoupon         = '$it_nocoupon',
                it_sc_type          = '$it_sc_type',
                it_sc_method        = '$it_sc_method',
                it_sc_basic         = '$it_sc_basic',
                it_sc_condition     = '$condition',
                it_head_html        = '$it_head_html',
                it_tail_html        = '$it_tail_html',
                it_time             = '{$g4['time_ymdhis']}',
                it_ip               = '{$_SERVER['REMOTE_ADDR']}',
                it_order            = '$it_order',
                it_tel_inq          = '$it_tel_inq',
                it_img1             = '$it_img1',
                it_img2             = '$it_img2',
                it_img3             = '$it_img3',
                it_img4             = '$it_img4',
                it_img5             = '$it_img5',
                it_img6             = '$it_img6',
                it_img7             = '$it_img7',
                it_img8             = '$it_img8',
                it_img9             = '$it_img9',
                it_img10            = '$it_img10'
                ";

if ($w == "")
{
    $sql = " insert $g4[shop_item_table]
                set it_id = '$it_id',
					$sql_common	";
    sql_query($sql);

    // 상품등록시 등록된 선택옵션의 it_id 가 변경됐을 경우 처리
    if($it_option_use) {
        $op_item_code = get_session('ss_op_item_code');

        if($it_id != $op_item_code) {
            $sql = " update {$g4['shop_option_table']} set it_id = '$it_id' where it_id = '$op_item_code' ";
            sql_query($sql);
        }
    }

    // 상품등록시 등록된 추가옵션의 it_id 가 변경됐을 경우 처리
    if($it_supplement_use) {
        $sp_item_code = get_session('ss_sp_item_code');

        if($it_id != $sp_item_code) {
            $sql = " update {$g4['shop_supplement_table']} set it_id = '$it_id' where it_id = '$sp_item_code' ";
            sql_query($sql);
        }
    }

    // 상품등록시 등록된 상품요약정보의 it_id가 변경됐을 경우 처리
    if(get_session('ss_ii_item_code')) {
        $ii_item_code = get_session('ss_ii_item_code');

        if($it_id != $ii_item_code) {
            $sql = " update {$g4['shop_item_info_table']} set it_id = '$it_id' where it_id = '$ii_item_code' ";
            sql_query($sql);
        }
    }

    unset($_SESSION['ss_op_item_code']);
    unset($_SESSION['ss_sp_item_code']);
    unset($_SESSION['ss_ii_item_code']);
}
else if ($w == "u")
{
    $sql = " update $g4[shop_item_table]
                set $sql_common
              where it_id = '$it_id' ";
    sql_query($sql);

    // 선택옵션정보 삭제
    if(!$it_option_use) {
        $sql = " delete from {$g4['shop_option_table']} where it_id = '$it_id' ";
        sql_query($sql);
    }

    // 추가옵션정보 삭제
    if(!$it_supplement_use) {
        $sql = " delete from {$g4['shop_supplement_table']} where it_id = '$it_id' ";
        sql_query($sql);
    }
}
else if ($w == "d")
{
    if ($is_admin != 'super')
    {
        $sql = " select it_id from $g4[shop_item_table] a, $g4[shop_category_table] b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '$member[mb_id]' ";
        $row = sql_fetch($sql);
        if (!$row[it_id])
            alert("\'{$member['mb_id']}\' 님께서 삭제 할 권한이 없는 상품입니다.");
    }

    itemdelete($it_id);
}

if ($w == "" || $w == "u")
{
    // 관련상품 등록
    $it_id2 = explode(",", $it_list);
    for ($i=0; $i<count($it_id2); $i++)
    {
        if (trim($it_id2[$i]))
        {
            $sql = " insert into {$g4['shop_item_relation_table']}
                        set it_id  = '$it_id',
                            it_id2 = '$it_id2[$i]' ";
            sql_query($sql, false);

            // 관련상품의 반대로도 등록
            $sql = " insert into {$g4['shop_item_relation_table']}
                        set it_id  = '$it_id2[$i]',
                            it_id2 = '$it_id' ";
            sql_query($sql, false);
        }
    }

    // 이벤트상품 등록
    $ev_id = explode(",", $ev_list);
    for ($i=0; $i<count($ev_id); $i++)
    {
        if (trim($ev_id[$i]))
        {
            $sql = " insert into {$g4['shop_event_item_table']}
                        set ev_id = '$ev_id[$i]',
                            it_id = '$it_id' ";
            sql_query($sql, false);
        }
    }
}

// 선택, 추가 옵션 테이블을 체크해 상품정보가 없는 것은 삭제
include_once('./item_option_check.php');

// 상품요약정보 테이블을 체크해 상품정보가 없는 것은 삭제
include_once('./item_info_check.php');

$qstr = "$qstr&sca=$sca&page=$page";

if ($w == "u") {
    goto_url("./itemform.php?w=u&it_id=$it_id&$qstr");
} else if ($w == "d")  {
    // 091123 추가 utf-8
    $qstr = "ca_id=$ca_id&sfl=$sfl&sca=$sca&page=$page&stx=".urlencode($stx)."&save_stx=".urlencode($save_stx);
    goto_url("./itemlist.php?$qstr");
}

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
?>
<script>
    if (confirm("계속 입력하시겠습니까?"))
        //location.href = "<?="./itemform.php?it_id=$it_id&sort1=$sort1&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page"?>";
        location.href = "<?="./itemform.php?it_id=$it_id&$qstr"?>";
    else
        location.href = "<?="./itemlist.php?$qstr"?>";
</script>
