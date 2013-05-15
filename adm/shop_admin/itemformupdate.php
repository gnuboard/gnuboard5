<?php
$sub_menu = '400300';
include_once('./_common.php');

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

    $sql = " select it_explan, it_mobile_explan, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
                from {$g4['shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    /*
    $str = $comma = $od_id = "";
    $sql = " select b.od_id
               from {$g4['shop_cart_table']} a,
                    {$g4['shop_order_table']} b
              where a.uq_id = b.uq_id
                and a.it_id = '$it_id'
                and a.ct_status != '쇼핑' ";
    $result = sql_query($sql);
    $i=0;
    while ($row = sql_fetch_array($result))
    {
        if (!$od_id)
            $od_id = $row['od_id'];

        $i++;
        if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma{$row['od_id']}";
        $comma = " , ";
    }
    if ($str)
    {
        alert("이 상품과 관련된 주문이 총 {$i} 건 존재하므로 주문서를 삭제한 후 상품을 삭제하여 주십시오.\\n\\n$str", "./orderstatuslist.php?sort1=od_id&amp;sel_field=od_id&amp;search=$od_id");
    }
    */

    // 상품 이미지 삭제
    for($i=1; $i<=10; $i++) {
        $file = G4_DATA_PATH.'/item/'.$it['it_img'.$i];
        if(is_file($file) && $it['it_img'.$i]) {
            @unlink($file);
            delete_item_thumbnail(dirname($file), basename($file));
        }
    }

    // 상, 하단 이미지 삭제
    @unlink(G4_DATA_PATH."/item/$it_id"."_h");
    @unlink(G4_DATA_PATH."/item/$it_id"."_t");

    // 장바구니 삭제
	$sql = " delete from {$g4['shop_cart_table']} where it_id = '$it_id' ";
	sql_query($sql);

    // 이벤트삭제
    $sql = " delete from {$g4['shop_event_item_table']} where it_id = '$it_id' ";
	sql_query($sql);

    // 사용후기삭제
    $sql = " delete from {$g4['shop_item_ps_table']} where it_id = '$it_id' ";
	sql_query($sql);

    // 상품문의삭제
    $sql = " delete from {$g4['shop_item_qa_table']} where it_id = '$it_id' ";
	sql_query($sql);

    // 관련상품삭제
    $sql = " delete from {$g4['shop_item_relation_table']} where it_id = '$it_id' or it_id2 = '$it_id' ";
	sql_query($sql);


    //------------------------------------------------------------------------
    // HTML 내용에서 에디터에 올라간 이미지의 경로를 얻어 삭제함
    //------------------------------------------------------------------------
    $imgs = get_editor_image($it['it_explan']);

    for($i=0;$i<count($imgs[1]);$i++) {
        $p = parse_url($imgs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $data_path = $p['path'];

        $destfile = G4_PATH.$data_path;

        if(is_file($destfile))
            @unlink($destfile);
    }

    $imgs = get_editor_image($it['it_mobile_explan']);

    for($i=0;$i<count($imgs[1]);$i++) {
        $p = parse_url($imgs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $data_path = $p['path'];

        $destfile = G4_PATH.$data_path;

        if(is_file($destfile))
            @unlink($destfile);
    }
    //------------------------------------------------------------------------


    // 상품 삭제
	$sql = " delete from {$g4['shop_item_table']} where it_id = '$it_id' ";
	sql_query($sql);
}


//------------------------------------------------------------------------------
// 금액 오류 검사
$line1 = true;
$cnt = 0;
if ($w == "" || $w == "u")
{
    for ($i=1; $i<=6; $i++)
    {
        $it_opt = $_POST["it_opt{$i}"];
        unset($opt);
        $opt = explode("\n", $it_opt);
        for ($k=0; $k<count($opt); $k++)
        {
            // 첫라인에는 금액옵션을 줄 수 없음
            if ($k == 0)
            {
                // 첫라인에 '셑'과 같은 문자를 입력할 수 없음
                // if (preg_match("/;/", $opt[$k])) {
                if (!preg_match("/&/", $opt[$k]) && preg_match("/;/", $opt[$k]))
                {
                    $line1 = false;
                    break;
                }
            }

            // 옵션금액에 + 또는 - 부호가 없다면 오류
            unset($exp);
            $exp = explode(";", $opt[$k]);
            if ($exp[1] > 0)
            {
                if (!preg_match("/^([+|-])/", $exp[1])) {
                    $cnt++;
                    break;
                }
            }
        }
    }
}

if (!$line1) {
    alert("옵션의 첫라인에는 금액을 입력할 수 없습니다.");
}

if ($cnt > 0) {
    alert("옵션의 금액 입력 오류입니다.\\n\\n추가되는 금액은 + 부호를\\n\\n할인되는 금액은 - 부호를 붙여 주십시오.");
}
//------------------------------------------------------------------------------

@mkdir(G4_DATA_PATH."/item", 0707);
@chmod(G4_DATA_PATH."/item", 0707);

if ($it_himg_del)  @unlink(G4_DATA_PATH."/item/{$it_id}_h");
if ($it_timg_del)  @unlink(G4_DATA_PATH."/item/{$it_id}_t");

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

$it_img_dir = G4_DATA_PATH.'/item';

// 파일삭제
if ($it_img1_del) {
    $file_img1 = $it_img_dir.'/'.$it_img1;
    @unlink($file_img1);
    delete_item_thumbnail(dirname($file_img1), basename($file_img1));
    $it_img1 = '';
}
if ($it_img2_del) {
    $file_img2 = $it_img_dir.'/'.$it_img2;
    @unlink($file_img2);
    delete_item_thumbnail(dirname($file_img2), basename($file_img2));
    $it_img2 = '';
}
if ($it_img3_del) {
    $file_img3 = $it_img_dir.'/'.$it_img3;
    @unlink($file_img3);
    delete_item_thumbnail(dirname($file_img3), basename($file_img3));
    $it_img3 = '';
}
if ($it_img4_del) {
    $file_img4 = $it_img_dir.'/'.$it_img4;
    @unlink($file_img4);
    delete_item_thumbnail(dirname($file_img4), basename($file_img4));
    $it_img4 = '';
}
if ($it_img5_del) {
    $file_img5 = $it_img_dir.'/'.$it_img5;
    @unlink($file_img5);
    delete_item_thumbnail(dirname($file_img5), basename($file_img5));
    $it_img5 = '';
}
if ($it_img6_del) {
    $file_img6 = $it_img_dir.'/'.$it_img6;
    @unlink($file_img6);
    delete_item_thumbnail(dirname($file_img6), basename($file_img6));
    $it_img6 = '';
}
if ($it_img7_del) {
    $file_img7 = $it_img_dir.'/'.$it_img7;
    @unlink($file_img7);
    delete_item_thumbnail(dirname($file_img7), basename($file_img7));
    $it_img7 = '';
}
if ($it_img8_del) {
    $file_img8 = $it_img_dir.'/'.$it_img8;
    @unlink($file_img8);
    delete_item_thumbnail(dirname($file_img8), basename($file_img8));
    $it_img8 = '';
}
if ($it_img9_del) {
    $file_img9 = $it_img_dir.'/'.$it_img9;
    @unlink($file_img9);
    delete_item_thumbnail(dirname($file_img9), basename($file_img9));
    $it_img9 = '';
}
if ($it_img10_del) {
    $file_img10 = $it_img_dir.'/'.$it_img10;
    @unlink($file_img10);
    delete_item_thumbnail(dirname($file_img10), basename($file_img10));
    $it_img10 = '';
}

// 이미지업로드
if ($_FILES['it_img1']['name']) {
    if($w == 'u' && $it_img1) {
        $file_img1 = $it_img_dir.'/'.$it_img1;
        @unlink($file_img1);
        delete_item_thumbnail(dirname($file_img1), basename($file_img1));
    }
    $it_img1 = it_img_upload($_FILES['it_img1']['tmp_name'], $_FILES['it_img1']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img2']['name']) {
    if($w == 'u' && $it_img2) {
        $file_img2 = $it_img_dir.'/'.$it_img2;
        @unlink($file_img2);
        delete_item_thumbnail(dirname($file_img2), basename($file_img2));
    }
    $it_img2 = it_img_upload($_FILES['it_img2']['tmp_name'], $_FILES['it_img2']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img3']['name']) {
    if($w == 'u' && $it_img3) {
        $file_img3 = $it_img_dir.'/'.$it_img3;
        @unlink($file_img3);
        delete_item_thumbnail(dirname($file_img3), basename($file_img3));
    }
    $it_img3 = it_img_upload($_FILES['it_img3']['tmp_name'], $_FILES['it_img3']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img4']['name']) {
    if($w == 'u' && $it_img4) {
        $file_img4 = $it_img_dir.'/'.$it_img4;
        @unlink($file_img4);
        delete_item_thumbnail(dirname($file_img4), basename($file_img4));
    }
    $it_img4 = it_img_upload($_FILES['it_img4']['tmp_name'], $_FILES['it_img4']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img5']['name']) {
    if($w == 'u' && $it_img5) {
        $file_img5 = $it_img_dir.'/'.$it_img5;
        @unlink($file_img5);
        delete_item_thumbnail(dirname($file_img5), basename($file_img5));
    }
    $it_img5 = it_img_upload($_FILES['it_img5']['tmp_name'], $_FILES['it_img5']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img6']['name']) {
    if($w == 'u' && $it_img6) {
        $file_img6 = $it_img_dir.'/'.$it_img6;
        @unlink($file_img6);
        delete_item_thumbnail(dirname($file_img6), basename($file_img6));
    }
    $it_img6 = it_img_upload($_FILES['it_img6']['tmp_name'], $_FILES['it_img6']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img7']['name']) {
    if($w == 'u' && $it_img7) {
        $file_img7 = $it_img_dir.'/'.$it_img7;
        @unlink($file_img7);
        delete_item_thumbnail(dirname($file_img7), basename($file_img7));
    }
    $it_img7 = it_img_upload($_FILES['it_img7']['tmp_name'], $_FILES['it_img7']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img8']['name']) {
    if($w == 'u' && $it_img8) {
        $file_img8 = $it_img_dir.'/'.$it_img8;
        @unlink($file_img8);
        delete_item_thumbnail(dirname($file_img8), basename($file_img8));
    }
    $it_img8 = it_img_upload($_FILES['it_img8']['tmp_name'], $_FILES['it_img8']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img9']['name']) {
    if($w == 'u' && $it_img9) {
        $file_img9 = $it_img_dir.'/'.$it_img9;
        @unlink($file_img9);
        delete_item_thumbnail(dirname($file_img9), basename($file_img9));
    }
    $it_img9 = it_img_upload($_FILES['it_img9']['tmp_name'], $_FILES['it_img9']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img10']['name']) {
    if($w == 'u' && $it_img10) {
        $file_img10 = $it_img_dir.'/'.$it_img10;
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


// 관련상품을 우선 삭제함
sql_query(" delete from {$g4['shop_item_relation_table']} where it_id = '$it_id' ");

// 관련상품의 반대도 삭제
sql_query(" delete from {$g4['shop_item_relation_table']} where it_id2 = '$it_id' ");

// 이벤트상품을 우선 삭제함
sql_query(" delete from {$g4['shop_event_item_table']} where it_id = '$it_id' ");

// 상품요약정보
$value_array = array();
for($i=0; $i<count($_POST['ii_article']); $i++) {
    $key = $_POST['ii_article'][$i];
    $val = $_POST['ii_value'][$i];
    $value_array[$key] = $val;
}
$it_info_value = serialize($value_array);


$sql_common = " ca_id               = '$ca_id',
                ca_id2              = '$ca_id2',
                ca_id3              = '$ca_id3',
                it_name             = '$it_name',
                it_gallery          = '$it_gallery',
                it_maker            = '$it_maker',
                it_origin           = '$it_origin',
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
                it_type1            = '$it_type1',
                it_type2            = '$it_type2',
                it_type3            = '$it_type3',
                it_type4            = '$it_type4',
                it_type5            = '$it_type5',
                it_basic            = '$it_basic',
                it_explan           = '$it_explan',
                it_explan_html      = '$it_explan_html',
                it_mobile_explan    = '$it_mobile_explan',
                it_cust_price       = '$it_cust_price',
                it_price            = '$it_price',
                it_point            = '$it_point',
                it_sell_email       = '$it_sell_email',
                it_use              = '$it_use',
                it_stock_qty        = '$it_stock_qty',
                it_head_html        = '$it_head_html',
                it_tail_html        = '$it_tail_html',
                it_mobile_head_html = '$it_mobile_head_html',
                it_mobile_tail_html = '$it_mobile_tail_html',
                it_time             = '".G4_TIME_YMDHIS."',
                it_ip               = '{$_SERVER['REMOTE_ADDR']}',
                it_order            = '$it_order',
                it_tel_inq          = '$it_tel_inq',
                it_info_gubun       = '$it_info_gubun',
                it_info_value       = '$it_info_value',
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
    $it_id = $_POST['it_id'];

    if (!trim($it_id)) {
        alert('상품 코드가 없으므로 상품을 추가하실 수 없습니다.');
    }

    $t_it_id = preg_replace("/[A-Za-z0-9\-_]/", "", $it_id);
    if($t_it_id)
        alert('상품 코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.');

    $sql = " insert {$g4['shop_item_table']}
                set it_id = '$it_id',
					$sql_common	";
    sql_query($sql);
}
else if ($w == "u")
{
    $sql = " update {$g4['shop_item_table']}
                set $sql_common
              where it_id = '$it_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    if ($is_admin != 'super')
    {
        $sql = " select it_id from {$g4['shop_item_table']} a, {$g4['shop_category_table']} b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['it_id'])
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
        //location.href = "<?="./itemform.php?it_id=$it_id&amp;sort1=$sort1&amp;sort2=$sort2&amp;sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page"?>";
        location.href = "<?php echo "./itemform.php?$qstr"; ?>";
    else
        location.href = "<?php echo "./itemlist.php?$qstr"; ?>";
</script>
