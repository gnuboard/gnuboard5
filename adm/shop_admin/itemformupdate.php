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

    $str = $comma = $od_id = "";
    $sql = " select b.od_id 
               from $g4[yc4_cart_table] a,
                    $g4[yc4_order_table] b
              where a.on_uid = b.on_uid
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


	// 상품 이미지 삭제
    @unlink("$g4[path]/data/item/$it_id"."_s");
    @unlink("$g4[path]/data/item/$it_id"."_m");
    @unlink("$g4[path]/data/item/$it_id"."_l1");
    @unlink("$g4[path]/data/item/$it_id"."_l2");
    @unlink("$g4[path]/data/item/$it_id"."_l3");
    @unlink("$g4[path]/data/item/$it_id"."_l4");
    @unlink("$g4[path]/data/item/$it_id"."_l5");

    // 상, 하단 이미지 삭제
    @unlink("$g4[path]/data/item/$it_id"."_h");
    @unlink("$g4[path]/data/item/$it_id"."_t");

    // 장바구니 삭제
	$sql = " delete from $g4[yc4_cart_table] where it_id = '$it_id' ";
	sql_query($sql);

    // 이벤트삭제
    $sql = " delete from $g4[yc4_event_item_table] where it_id = '$it_id' ";
	sql_query($sql);

    // 사용후기삭제
    $sql = " delete from $g4[yc4_item_ps_table] where it_id = '$it_id' ";
	sql_query($sql);

    // 상품문의삭제
    $sql = " delete from $g4[yc4_item_qa_table] where it_id = '$it_id' ";
	sql_query($sql);

    // 관련상품삭제
    $sql = " delete from $g4[yc4_item_relation_table] where it_id = '$it_id' or it_id2 = '$it_id' ";
	sql_query($sql);

    // 상품요약정보삭제
    $sql = " delete from $g4[yc4_item_info_table] where it_id = '$it_id' ";
	sql_query($sql);


    //------------------------------------------------------------------------
    // HTML 내용에서 에디터에 올라간 이미지의 경로를 얻어 삭제함
    //------------------------------------------------------------------------
    $sql = " select * from $g4[yc4_item_table] where it_id = '$it_id' ";
    $it = sql_fetch($sql);
    $s = $it[it_explan];

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
    //------------------------------------------------------------------------


    // 상품 삭제
	$sql = " delete from $g4[yc4_item_table] where it_id = '$it_id' ";
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

@mkdir("$g4[path]/data/item", 0707);
@chmod("$g4[path]/data/item", 0707);

if ($it_himg_del)  @unlink("$g4[path]/data/item/{$it_id}_h");
if ($it_timg_del)  @unlink("$g4[path]/data/item/{$it_id}_t");

if ($it_simg_del)  @unlink("$g4[path]/data/item/{$it_id}_s");
if ($it_mimg_del)  @unlink("$g4[path]/data/item/{$it_id}_m");
if ($it_limg1_del) @unlink("$g4[path]/data/item/{$it_id}_l1");
if ($it_limg2_del) @unlink("$g4[path]/data/item/{$it_id}_l2");
if ($it_limg3_del) @unlink("$g4[path]/data/item/{$it_id}_l3");
if ($it_limg4_del) @unlink("$g4[path]/data/item/{$it_id}_l4");
if ($it_limg5_del) @unlink("$g4[path]/data/item/{$it_id}_l5");

// 이미지(대)만 업로드하고 자동생성 체크일 경우 이미지(중,소) 자동생성
if ($createimage && $_FILES[it_limg1][name])
{
    upload_file($_FILES[it_limg1][tmp_name], $it_id."_l1", "$g4[path]/data/item");

    $image = "$g4[path]/data/item/$it_id"."_l1";
    $size = getimagesize($image);
    $src = @imagecreatefromjpeg($image);

    if (!$src)
    {
        echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
        echo "<script>alert('이미지(대)가 JPG 파일이 아닙니다.');</script>";
    }
    else
    {
        // gd 버전에 따라
        if (function_exists("imagecopyresampled")) {
            // 이미지(소) 생성
            $dst = imagecreatetruecolor($default[de_simg_width], $default[de_simg_height]);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $default[de_simg_width], $default[de_simg_height], $size[0], $size[1]);
        } else {
            // 이미지(소) 생성
            $dst = imagecreate($default[de_simg_width], $default[de_simg_height]);
            imagecopyresized($dst, $src, 0, 0, 0, 0, $default[de_simg_width], $default[de_simg_height], $size[0], $size[1]);
        }
        imagejpeg($dst, "$g4[path]/data/item/$it_id"."_s", 90);

        if (function_exists("imagecopyresampled")) {
            // 이미지(중) 생성
            $dst = imagecreatetruecolor($default[de_mimg_width], $default[de_mimg_height]);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $default[de_mimg_width], $default[de_mimg_height], $size[0], $size[1]);
        } else {
            // 이미지(중) 생성
            $dst = imagecreate($default[de_mimg_width], $default[de_mimg_height]);
            imagecopyresized($dst, $src, 0, 0, 0, 0, $default[de_mimg_width], $default[de_mimg_height], $size[0], $size[1]);
        }
        @imagejpeg($dst, "$g4[path]/data/item/$it_id"."_m", 90);
    }
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
sql_query(" delete from $g4[yc4_item_relation_table] where it_id = '$it_id' ");

// 관련상품의 반대도 삭제
sql_query(" delete from $g4[yc4_item_relation_table] where it_id2 = '$it_id' ");

// 이벤트상품을 우선 삭제함
sql_query(" delete from $g4[yc4_event_item_table] where it_id = '$it_id' ");


$sql_common = " ca_id            = '$ca_id',
                ca_id2           = '$ca_id2',
                ca_id3           = '$ca_id3',
                it_name          = '$it_name',
                it_gallery       = '$it_gallery',
                it_maker         = '$it_maker',
                it_origin        = '$it_origin',
                it_opt1_subject  = '$it_opt1_subject',
                it_opt2_subject  = '$it_opt2_subject',
                it_opt3_subject  = '$it_opt3_subject',
                it_opt4_subject  = '$it_opt4_subject',
                it_opt5_subject  = '$it_opt5_subject',
                it_opt6_subject  = '$it_opt6_subject',
                it_opt1          = '$it_opt1',
                it_opt2          = '$it_opt2',
                it_opt3          = '$it_opt3',
                it_opt4          = '$it_opt4',
                it_opt5          = '$it_opt5',
                it_opt6          = '$it_opt6',
                it_type1         = '$it_type1',
                it_type2         = '$it_type2',
                it_type3         = '$it_type3',
                it_type4         = '$it_type4',
                it_type5         = '$it_type5',
                it_basic         = '$it_basic',
                it_explan        = '$it_explan',
                it_explan_html   = '$it_explan_html',
                it_cust_amount   = '$it_cust_amount',
                it_amount        = '$it_amount',
                it_amount2       = '$it_amount2',
                it_amount3       = '$it_amount3',
                it_point         = '$it_point',
                it_sell_email    = '$it_sell_email',
                it_use           = '$it_use',
                it_stock_qty     = '$it_stock_qty',
                it_head_html     = '$it_head_html',
                it_tail_html     = '$it_tail_html',
                it_time          = '$g4[time_ymdhis]',
                it_ip            = '$_SERVER[REMOTE_ADDR]',
                it_order         = '$it_order',
                it_tel_inq       = '$it_tel_inq'
                ";

if ($w == "")
{
    if (!trim($it_id)) {
        alert("상품 코드가 없으므로 상품을 추가하실 수 없습니다.");
    }

    $sql = " insert $g4[yc4_item_table]
                set it_id = '$it_id',
					$sql_common	";
    sql_query($sql);
}
else if ($w == "u")
{
    $sql = " update $g4[yc4_item_table]
                set $sql_common
              where it_id = '$it_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    if ($is_admin != 'super')
    {
        $sql = " select it_id from $g4[yc4_item_table] a, $g4[yc4_category_table] b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '$member[mb_id]' ";
        $row = sql_fetch($sql);
        if (!$row[it_id])
            alert("\'{$member[mb_id]}\' 님께서 삭제 할 권한이 없는 상품입니다.");
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
            $sql = " insert into $g4[yc4_item_relation_table]
                        set it_id  = '$it_id',
                            it_id2 = '$it_id2[$i]' ";
            sql_query($sql, false);

            // 관련상품의 반대로도 등록
            $sql = " insert into $g4[yc4_item_relation_table]
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
            $sql = " insert into $g4[yc4_event_item_table]
                        set ev_id = '$ev_id[$i]',
                            it_id = '$it_id' ";
            sql_query($sql, false);
        }
    }

    if ($_FILES[it_simg][name])  upload_file($_FILES[it_simg][tmp_name],  $it_id . "_s",  "$g4[path]/data/item");
    if ($_FILES[it_mimg][name])  upload_file($_FILES[it_mimg][tmp_name],  $it_id . "_m",  "$g4[path]/data/item");
    if ($_FILES[it_limg1][name]) upload_file($_FILES[it_limg1][tmp_name], $it_id . "_l1", "$g4[path]/data/item");
    if ($_FILES[it_limg2][name]) upload_file($_FILES[it_limg2][tmp_name], $it_id . "_l2", "$g4[path]/data/item");
    if ($_FILES[it_limg3][name]) upload_file($_FILES[it_limg3][tmp_name], $it_id . "_l3", "$g4[path]/data/item");
    if ($_FILES[it_limg4][name]) upload_file($_FILES[it_limg4][tmp_name], $it_id . "_l4", "$g4[path]/data/item");
    if ($_FILES[it_limg5][name]) upload_file($_FILES[it_limg5][tmp_name], $it_id . "_l5", "$g4[path]/data/item");

    if ($_FILES[it_himg][name])  upload_file($_FILES[it_himg][tmp_name], $it_id . "_h", "$g4[path]/data/item");
    if ($_FILES[it_timg][name])  upload_file($_FILES[it_timg][tmp_name], $it_id . "_t", "$g4[path]/data/item");
}

$qstr = "$qstr&sca=$sca&page=$page";

if ($w == "u") {
    goto_url("./itemform.php?w=u&it_id=$it_id&$qstr");
} else if ($w == "d")  {
    // 091123 추가 utf-8
    $qstr = "ca_id=$ca_id&sfl=$sfl&sca=$sca&page=$page&stx=".urlencode($stx)."&save_stx=".urlencode($save_stx);
    goto_url("./itemlist.php?$qstr");
}

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
?>
<script>
    if (confirm("계속 입력하시겠습니까?"))
        //location.href = "<?="./itemform.php?it_id=$it_id&sort1=$sort1&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page"?>";
        location.href = "<?="./itemform.php?it_id=$it_id&$qstr"?>";
    else
        location.href = "<?="./itemlist.php?$qstr"?>";
</script>
