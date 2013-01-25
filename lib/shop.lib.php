<?
if (!defined('_GNUBOARD_')) exit;

/*******************************************************************************
    유일한 키를 얻는다.

    결과 :

        년월일시분초00 ~ 년월일시분초99
        년(4) 월(2) 일(2) 시(2) 분(2) 초(2) 100분의1초(2)
        총 16자리이며 년도는 2자리로 끊어서 사용해도 됩니다.
        예) 2008062611570199 또는 08062611570199 (2100년까지만 유일키)

    사용하는 곳 :
    1. 게시판 글쓰기시 미리 유일키를 얻어 파일 업로드 필드에 넣는다.
    2. 주문번호 생성시에 사용한다.
    3. 기타 유일키가 필요한 곳에서 사용한다.
*******************************************************************************/
// 기존의 get_unique_id() 함수를 사용하지 않고 get_uniqid() 를 사용한다.
function get_uniqid()
{
    global $g4;

    sql_query(" LOCK TABLE {$g4['yc4_uniqid_table']} WRITE ");
    while (1) {
        // 년월일시분초에 100분의 1초 두자리를 추가함 (1/100 초 앞에 자리가 모자르면 0으로 채움)
        $key = date('YmdHis', time()) . str_pad((int)(microtime()*100), 2, "0", STR_PAD_LEFT);

        $result = sql_query(" insert into {$g4['yc4_uniqid_table']} values ('$key') ", false);
        if ($result) break; // 쿼리가 정상이면 빠진다.

        // insert 하지 못했으면 일정시간 쉰다음 다시 유일키를 만든다.
        usleep(10000); // 100분의 1초를 쉰다
    }
    sql_query(" UNLOCK TABLES ");

    return $key;
}

// CHARSET 변경 : euc-kr -> utf-8
function iconv_utf8($str)
{
    return iconv('euc-kr', 'utf-8', $str);
}

// CHARSET 변경 : utf-8 -> euc-kr
function iconv_euckr($str)
{
    return iconv('utf-8', 'euc-kr', $str);
}

// 상위 분류코드 반환
function parent_ca_id($ca_id)
{
    return substr($ca_id, 0, strlen($ca_id) - 2);
}

// 상위 분류순서 반환
function parent_ca_order($ca_id)
{
    global $g4;
    $upper_ca_id = parent_ca_id($ca_id);
    if ($upper_ca_id) {
        $row = sql_fetch(" select ca_order from {$g4['yc4_category_table']} where ca_id = '$upper_ca_id' ");
        $ca_order = $row['ca_order'];
    } else {
        $ca_order = "";
    }
    return $ca_order;
}

// array_map() 대체
function array_add_callback($func, $array)
{
    if(!$func) {
        return;
    }

    if(is_array($array)) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $array[$key] = array_add_callback($func, $value);
            } else {
                $array[$key] = call_user_func($func, $value);
            }
        }
    } else {
        $array = call_user_func($func, $array);
    }

    return $array;
}
?>
<?
//==============================================================================
// 쇼핑몰 함수 모음 시작
//==============================================================================
// 장바구니 건수 검사
function get_cart_count($uq_id, $sw=0, $mb_id='')
{
    global $g4;

    if($mb_id)
        $sql_where = " ( uq_id = '$uq_id' or mb_id = '$mb_id' ) ";
    else
        $sql_where = " uq_id = '$uq_id' ";

    $sql = " select count(distinct it_id) as cnt from {$g4['yc4_cart_table']} where $sql_where  and ct_direct = '$sw' and ct_status = '쇼핑' ";
    $row = sql_fetch($sql);
    $cnt = (int)$row['cnt'];
    return $cnt;
}

// 이미지를 얻는다
function get_image($img, $width=0, $height=0)
{
    global $g4, $default;

    $full_img = "$g4[path]/data/item/$img";

    if (file_exists($full_img) && $img)
    {
        if (!$width)
        {
            $size = getimagesize($full_img);
            $width = $size[0];
            $height = $size[1];
        }
        $str = "<img id='$img' src='$g4[url]/data/item/$img' width='$width' height='$height' border='0'>";
    }
    else
    {
        $str = "<img id='$img' src='$g4[shop_img_url]/no_image.gif' border='0' ";
        if ($width)
            $str .= "width='$width' height='$height'";
        else
            $str .= "width='$default[de_mimg_width]' height='$default[de_mimg_height]'";
        $str .= ">";
    }


    return $str;
}

// 상품 이미지를 얻는다
function get_it_image($img, $width=0, $height=0, $id="")
{
    global $g4;

    $str = get_image($img, $width, $height);
    if ($id) {
        $str = "<a href='$g4[shop_url]/item.php?it_id=$id'>$str</a>";
    }
    return $str;
}

// 상품의 재고 (창고재고수량 - 주문대기수량)
function get_it_stock_qty($it_id)
{
    global $g4;

    $sql = " select it_stock_qty from $g4[yc4_item_table] where it_id = '$it_id' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['it_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g4['yc4_cart_table']}
              where it_id = '$it_id'
                and is_option = '0'
                and ct_stock_use = 0
                and ct_status in ('주문', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}

// 옵션별 재고
// $is_option : 1-> 선택옵션, 2->추가옵션, 0->옵션없는 상품
function get_option_stock_qty($it_id, $opt_id, $is_option)
{
    global $g4;

    if($is_option == 1) {
        $sql = " select opt_qty from {$g4['yc4_option_table']} where it_id = '$it_id' and opt_id = '$opt_id' and opt_use = '1' ";
        $row = sql_fetch($sql);
        $jaego = (int)$row['opt_qty'];
    } else {
        $sql = " select sp_qty from {$g4['yc4_supplement_table']} where it_id = '$it_id' and sp_id = '$opt_id' and sp_use = '1' ";
        $row = sql_fetch($sql);
        $jaego = (int)$row['sp_qty'];
    }

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
                from {$g4['yc4_cart_table']}
                where it_id = '$it_id'
                  and opt_id = '$opt_id'
                  and ct_stock_use = '0'
                  and is_option = '$is_option'
                  and ct_status in ('주문', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}

// 큰 이미지
function get_large_image($img, $it_id, $btn_image=true)
{
    global $g4;

    if (file_exists("$g4[path]/data/item/$img") && $img != "")
    {
        $size   = getimagesize("$g4[path]/data/item/$img");
        $width  = $size[0];
        $height = $size[1];
        $str = "<a href=\"javascript:popup_large_image('$it_id', '$img', $width, $height, '$g4[shop_path]')\">";
        if ($btn_image)
            $str .= "<img src='$g4[shop_img_path]/btn_zoom.gif' border='0'></a>";
    }
    else
        $str = "";
    return $str;
}

// 금액 표시
function display_amount($amount, $tel_inq=false)
{
    if ($tel_inq)
        $amount = "전화문의";
    else
        $amount = number_format($amount, 0) . "원";

    return $amount;
}

// 금액표시
// $it : 상품 배열
function get_amount($it)
{
    global $member;

    if ($it['it_tel_inq']) return '전화문의';

    if ($member[mb_level] > 2) // 특별회원
        $amount = $it[it_amount3];

    if ($member[mb_level] == 2 || $amount == 0) // 회원가격
        $amount = $it[it_amount2];

    if ($member[mb_level] == 1 || $amount == 0) // 비회원가격
        $amount = $it[it_amount];

    return (int)$amount;
}


// 포인트 표시
function display_point($point)
{
    return number_format($point, 0) . "점";
}

// 포인트를 구한다
function get_point($amount, $point)
{
    return (int)($amount * $point / 100);
}

// HTML 특수문자 변환 htmlspecialchars
function htmlspecialchars2($str)
{
    $trans = array("\"" => "&#034;", "'" => "&#039;", "<"=>"&#060;", ">"=>"&#062;");
    $str = strtr($str, $trans);
    return $str;
}

// 파일을 업로드 함
function upload_file($srcfile, $destfile, $dir)
{
    if ($destfile == "") return false;
    // 업로드 한후 , 퍼미션을 변경함
    @move_uploaded_file($srcfile, "$dir/$destfile");
    @chmod("$dir/$destfile", 0606);
    return true;
}

// 유일키를 생성
function get_unique_id($len=32)
{
    global $g4;

    $result = @mysql_query(" LOCK TABLES $g4[yc4_on_uid_table] WRITE, $g4[yc4_cart_table] READ, $g4[yc4_order_table] READ ");
    if (!$result) {
        $sql = " CREATE TABLE `$g4[yc4_on_uid_table]` (
                    `on_id` int(11) NOT NULL auto_increment,
                    `on_uid` varchar(32) NOT NULL default '',
                    `on_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
                    `session_id` varchar(32) NOT NULL default '',
                    PRIMARY KEY  (`on_id`),
                    UNIQUE KEY `on_uid` (`on_uid`) ) ";
        sql_query($sql, false);
    }

    // 이틀전 자료는 모두 삭제함
    $ytime = date("Y-m-d", $g4['server_time'] - 86400 * 1);
    $sql = " delete from $g4[yc4_on_uid_table] where on_datetime < '$ytime' ";
    sql_query($sql);

    $unique = false;

    do {
        sql_query(" INSERT INTO $g4[yc4_on_uid_table] set on_uid = NOW(), on_datetime = NOW(), session_id = '".session_id()."' ", false);
        $id = @mysql_insert_id();
        $uid = md5($id);
        sql_query(" UPDATE $g4[yc4_on_uid_table] set on_uid = '$uid' where on_id = '$id' ");

        // 장바구니에도 겹치는게 있을 수 있으므로 ...
        $sql = "select COUNT(*) as cnt from $g4[yc4_cart_table] where on_uid = '$uid' ";
        $row = sql_fetch($sql);
        if (!$row[cnt]) {
            // 주문서에도 겹치는게 있을 수 있으므로 ...
            $sql = "select COUNT(*) as cnt from $g4[yc4_order_table] where on_uid = '$uid' ";
            $row = sql_fetch($sql);
            if (!$row[cnt])
                $unique = true;
        }
    } while (!$unique); // $unique 가 거짓인동안 실행

    @mysql_query(" UNLOCK TABLES ");

    return $uid;
}

// 주문서 번호를 얻는다.
function get_new_od_id()
{
    global $g4;

    // 주문서 테이블 Lock 걸고
    sql_query(" LOCK TABLES $g4[yc4_order_table] READ, $g4[yc4_order_table] WRITE ", FALSE);
    // 주문서 번호를 만든다.
    $date = date("ymd", time());    // 2002년 3월 7일 일경우 020307
    $sql = " select max(od_id) as max_od_id from $g4[yc4_order_table] where SUBSTRING(od_id, 1, 6) = '$date' ";
    $row = sql_fetch($sql);
    $od_id = $row[max_od_id];
    if ($od_id == 0)
        $od_id = 1;
    else
    {
        $od_id = (int)substr($od_id, -4);
        $od_id++;
    }
    $od_id = $date . substr("0000" . $od_id, -4);
    // 주문서 테이블 Lock 풀고
    sql_query(" UNLOCK TABLES ", FALSE);

    return $od_id;
}

function message($subject, $content, $align="left", $width="450")
{
    $str = "
        <table width=$width cellpadding=4 align=center>
            <tr><td class=line height=1></td></tr>
            <tr>
                <td align=center>$subject</td>
            </tr>
            <tr><td class=line height=1></td></tr>
            <tr>
                <td>
                    <table width=100% cellpadding=8 cellspacing=0>
                        <tr>
                            <td class=leading align=$align>$content</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td class=line height=1></td></tr>
        </table>
        <br>
        ";
    return $str;
}

// 시간이 비어 있는지 검사
function is_null_time($datetime)
{
    // 공란 0 : - 제거
    //$datetime = ereg_replace("[ 0:-]", "", $datetime); // 이 함수는 PHP 5.3.0 에서 배제되고 PHP 6.0 부터 사라집니다.
    $datetime = preg_replace("/[ 0:-]/", "", $datetime);
    if ($datetime == "")
        return true;
    else
        return false;
}

// 출력유형, 스킨파일, 1라인이미지수, 총라인수, 이미지폭, 이미지높이
// 1.02.01 $ca_id 추가
function display_type($type, $skin_file, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
    global $member, $g4;

    // 상품의 갯수
    $items = $list_mod * $list_row;

    // 1.02.00
    // it_order 추가
    $sql = " select *
               from $g4[yc4_item_table]
              where it_use = '1'
                and it_type{$type} = '1' ";
    if ($ca_id) $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " order by it_order, it_id desc
              limit $items ";
    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = "$g4[shop_path]/$skin_file";
    if (!file_exists($file)) {
        echo "<span class=point>{$file} 파일을 찾을 수 없습니다.</span>";
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

// 분류별 출력
// 스킨파일번호, 1라인이미지수, 총라인수, 이미지폭, 이미지높이 , 분류번호
function display_category($no, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
    global $member, $g4;

    // 상품의 갯수
    $items = $list_mod * $list_row;

    $sql = " select * from $g4[yc4_item_table] where it_use = '1'";
    if ($ca_id)
        $sql .= " and ca_id LIKE '{$ca_id}%' ";
    $sql .= " order by it_order, it_id desc limit $items ";
    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = "$g4[shop_path]/maintype{$no}.inc.php";
    if (!file_exists($file)) {
        echo "<span class=point>{$file} 파일을 찾을 수 없습니다.</span>";
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

// 별
function get_star($score)
{
    if ($score > 8) $star = "5";
    else if ($score > 6) $star = "4";
    else if ($score > 4) $star = "3";
    else if ($score > 2) $star = "2";
    else if ($score > 0) $star = "1";
    else $star = "5";

    return $star;
}

// 별 이미지
function get_star_image($it_id)
{
    global $g4;

    $sql = "select (SUM(is_score) / COUNT(*)) as score from $g4[yc4_item_ps_table] where it_id = '$it_id' ";
    $row = sql_fetch($sql);

    return (int)get_star($row[score]);
}

// 메일 보내는 내용을 HTML 형식으로 만든다.
function email_content($str)
{
    global $g4;

    $s = "";
    $s .= "<html><head><meta http-equiv='content-type' content='text/html; charset={$g4['charset']}'><title>메일</title>\n";
    $s .= "<body>\n";
    $s .= $str;
    $s .= "</body>\n";
    $s .= "</html>";

    return $s;
}

// 타임스탬프 형식으로 넘어와야 한다.
// 시작시간, 종료시간
function gap_time($begin_time, $end_time)
{
    $gap = $end_time - $begin_time;
    $time[days]    = (int)($gap / 86400);
    $time[hours]   = (int)(($gap - ($time[days] * 86400)) / 3600);
    $time[minutes] = (int)(($gap - ($time[days] * 86400 + $time[hours] * 3600)) / 60);
    $time[seconds] = (int)($gap - ($time[days] * 86400 + $time[hours] * 3600 + $time[minutes] * 60));
    return $time;
}


// 공란없이 이어지는 문자 자르기 (wayboard 참고 (way.co.kr))
function continue_cut_str($str, $len=80)
{
    /*
    $pattern = "[^ \n<>]{".$len."}";
    return eregi_replace($pattern, "\\0\n", $str);
    */
    $pattern = "/[^ \n<>]{".$len."}/";
    return preg_replace($pattern, "\\0\n", $str);
}

// 제목별로 컬럼 정렬하는 QUERY STRING
// $type 이 1이면 반대
function title_sort($col, $type=0)
{
    global $sort1, $sort2;
    global $_SERVER;
    global $page;
    global $doc;

    $q1 = "sort1=$col";
    if ($type) {
        $q2 = "sort2=desc";
        if ($sort1 == $col) {
            if ($sort2 == "desc") {
                $q2 = "sort2=asc";
            }
        }
    } else {
        $q2 = "sort2=asc";
        if ($sort1 == $col) {
            if ($sort2 == "asc") {
                $q2 = "sort2=desc";
            }
        }
    }
    #return "$_SERVER[PHP_SELF]?$q1&$q2&page=$page";
    return "$_SERVER[PHP_SELF]?$q1&$q2&page=$page";
}


// 세션값을 체크하여 이쪽에서 온것이 아니면 메인으로
function session_check()
{
    global $g4;

    if (!trim(get_session('ss_on_uid')))
        gotourl("$g4[path]/");
}

// 상품 옵션
function get_item_options($subject, $option, $index)
{
    $subject = trim($subject);
    $option  = trim($option);

    if (!$subject || !$option) return "";

    $str = "";

    $arr = explode("\n", $option);
    // 옵션이 하나일 경우
    if (count($arr) == 1)
    {
        $str = $option;
    }
    else
    {
        $str = "<select name=it_opt{$index} onchange='amount_change()'>\n";
        for ($k=0; $k<count($arr); $k++)
        {
            $arr[$k] = str_replace("\r", "", $arr[$k]);
            $opt = explode(";", trim($arr[$k]));
            $str .= "<option value='$arr[$k]'>{$opt[0]}";
            // 옵션에 금액이 있다면
            if ($opt[1] != 0)
            {
                $str .= " (";
                // - 금액이 아니라면 모두 + 금액으로
                //if (!ereg("[-]", $opt[1]))
                if (!preg_match("/[-]/", $opt[1]))
                    $str .= "+";
                $str .= display_amount($opt[1]) . ")";
            }
            $str .= "</option>\n";
        }
        $str .= "</select>\n<input type=hidden name=it_opt{$index}_subject value='$subject'>\n";
    }

    return $str;
}

// 선택옵션
function conv_item_options($subject, $option, $index, $disabled='')
{
    $subject = trim($subject);
    $option = trim($option);

    if(!$subject || !$option) {
        return false;
    }

    if($disabled) {
        $disabled = ' disabled="disabled"';
    }

    $str = '<select name="item-option-'.$index.'"'.$disabled.'>'."\n";
    $str .= '<option value="">'.$subject.'선택</option>'."\n";
    $option_item = explode(',', $option);
    $option_count = count($option_item);

    for($i = 0; $i < $option_count; $i++) {
        $str .= '<option value="'.$option_item[$i].'">'.$option_item[$i].'</option>'."\n";
    }

    $str .= '</select>';

    return $str;
}

// 추가옵션명
function get_supplement_subject($it_id)
{
    global $g4;

    // 추가옵션정보
    $sql = " select sp_id from {$g4['yc4_supplement_table']} where it_id = '$it_id' and sp_use = '1' order by sp_no asc ";
    $result = sql_query($sql);

    $count = mysql_num_rows($result);
    if(!$count) {
        return false;
    }

    // 추가옵션명
    $subject = array();
    for($i = 0; $row = sql_fetch_array($result); $i++) {
        $str = explode('|*|', $row['sp_id']);

        if(!in_array($str[0], $subject)) {
            array_push($subject, $str[0]);
        }
    }

    return $subject;
}

// 추가옵션항목
function get_supplement_option($it_id, $sp_id, $index)
{
    global $g4;

    // 추가옵션정보
    $sql = " select sp_id, sp_amount, sp_qty from {$g4['yc4_supplement_table']} where it_id = '$it_id' and sp_use = '1' and sp_id like '$sp_id%' order by sp_no asc ";
    $result = sql_query($sql);

    $count = mysql_num_rows($result);
    if(!$count) {
        return '';
    }

    $str = '<select name="item-supplement-'.$index.'">'."\n";
    $str .= '<option value="">선택</option>'."\n";
    for($i = 0; $row = sql_fetch_array($result); $i++) {
        $opt = str_replace($sp_id.'|*|', '', $row['sp_id']);
        if($opt) {
            if($row['sp_amount']) {
                $info = ' (+'.number_format($row['sp_amount']).'원)';
            }
            $str .= '<option value="'.$opt.'">'.$opt.$info.'</option>'."\n";
        }
    }
    $str .= '</select>';

    return $str;
}

// 장바구니 옵션 출력
function print_cart_options($uq_id, $it_id, $sw='')
{
    global $g4;

    if(!$uq_id || !$it_id) {
        return '';
    }

    $str = '';
    $br = '';

    $sql = " select ct_option, ct_qty, it_amount, ct_amount
                from {$g4['yc4_cart_table']}
                where uq_id = '$uq_id'
                  and it_id = '$it_id' ";

    if($sw == "0" || $sw == "1") {
        $sql .= " and ct_direct = '$sw' ";
    }

    $sql .= " order by ct_id, is_option ";

    $result = sql_query($sql);

    for($i=0; $row = sql_fetch_array($result); $i++) {
        if($row['ct_option']) {
            $str .= $br . $row['ct_option'];

            if($row['it_amount']) {
                $str .= ' / ' . number_format($row['it_amount']) . '원';
            }

            if($row['ct_amount']) {
                $str .= ' (+' . number_format($row['ct_amount']) . '원)';
            }

            $str .= ' / ' . number_format($row['ct_qty']) . '개';

            $br = '<br />';
        }
    }

    return $str;
}

// 인수는 $it_id, $it_opt1, ..., $it_opt6 까지 넘어옴
function print_item_options()
{
    global $g4;

    $it_id = func_get_arg(0);
    $sql = " select it_opt1_subject,
                    it_opt2_subject,
                    it_opt3_subject,
                    it_opt4_subject,
                    it_opt5_subject,
                    it_opt6_subject
               from $g4[yc4_item_table]
              where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    $it_name = $str_split = "";
    for ($i=1; $i<=6; $i++)
    {
        $it_opt = trim(func_get_arg($i));
        // 상품옵션에서 0은 제외되는 현상을 수정
        if ($it_opt==null) continue;

        $it_name .= $str_split;
        $it_opt_subject = $it["it_opt{$i}_subject"];
        $opt = explode( ";", $it_opt );
        $it_name .= "&nbsp; $it_opt_subject = $opt[0]";

        if ($opt[1] != 0)
        {
            $it_name .= " (";
            //if (ereg("[+]", $opt[1]) == true)
            if (preg_match("/[+]/", $opt[1]) == true)
                $it_name .= "+";
            $it_name .= display_amount($opt[1]) . ")";
        }
        $str_split = "<br>";
    }

    return $it_name;
}

function it_name_icon($it, $it_name="", $url=1)
{
    global $g4;

    $str = "";
    if ($it_name)
        $str = $it_name;
    else
        $str = stripslashes($it[it_name]);

    if ($url)
        $str = "<a href='$g4[shop_path]/item.php?it_id=$it[it_id]'>$str</a>";

    if ($it[it_type1]) $str .= " <img src='$g4[shop_img_path]/icon_type1.gif' border='0' align='absmiddle' />";
    if ($it[it_type2]) $str .= " <img src='$g4[shop_img_path]/icon_type2.gif' border='0' align='absmiddle' />";
    if ($it[it_type3]) $str .= " <img src='$g4[shop_img_path]/icon_type3.gif' border='0' align='absmiddle' />";
    if ($it[it_type4]) $str .= " <img src='$g4[shop_img_path]/icon_type4.gif' border='0' align='absmiddle' />";
    if ($it[it_type5]) $str .= " <img src='$g4[shop_img_path]/icon_type5.gif' border='0' align='absmiddle' />";

    // 품절
    $stock = get_it_stock_qty($it[it_id]);
    if ($stock <= 0)
        $str .= " <img src='$g4[shop_img_path]/icon_pumjul.gif' border='0' align='absmiddle' /> ";

    return $str;
}

// 일자형식변환
function date_conv($date, $case=1)
{
    if ($case == 1) { // 년-월-일 로 만들어줌
        $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);
    } else if ($case == 2) { // 년월일 로 만들어줌
        $date = preg_replace("/-/", "", $date);
    }

    return $date;
}

// 배너출력
function display_banner($position, $num="")
{
    global $g4;

    if (!$position) $position = "왼쪽";

    include "$g4[shop_path]/boxbanner{$num}.inc.php";
}

// 1.00.02
// 파일번호, 이벤트번호, 1라인이미지수, 총라인수, 이미지폭, 이미지높이
// 1.02.01 $ca_id 추가
function display_event($no, $event, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
    global $member, $g4;

    // 상품의 갯수
    $items = $list_mod * $list_row;

    // 1.02.00
    // b.it_order 추가
    $sql = " select b.*
               from $g4[yc4_event_item_table] a,
                    $g4[yc4_item_table] b
              where a.it_id = b.it_id
                and b.it_use = '1'
                and a.ev_id = '$event' ";
    if ($ca_id) $sql .= " and ca_id = '$ca_id' ";
    $sql .= " order by b.it_order, a.it_id desc
              limit $items ";
    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = "$g4[shop_path]/maintype{$no}.inc.php";
    if (!file_exists($file)) {
        echo "<span class=point>{$file} 파일을 찾을 수 없습니다.</span>";
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

function get_yn($val, $case='')
{
    switch ($case) {
        case '1' : $result = ($val > 0) ? 'Y' : 'N'; break;
        default :  $result = ($val > 0) ? '예' : '아니오';
    }
    return $result;
}

// 상품명과 건수를 반환
function get_goods($on_uid)
{
    global $g4;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from $g4[yc4_cart_table] a, $g4[yc4_item_table] b where a.it_id = b.it_id and a.on_uid = '$on_uid' order by ct_id limit 1 ");
    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods[it_id] = $row[it_id];
    $goods[full_name]= $goods[name] = addslashes($row[it_name]);
    // 특수문자제거
    $goods[full_name] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",  $goods[full_name]);

    // 상품건수
    $row = sql_fetch(" select count(*) as cnt from $g4[yc4_cart_table] where on_uid = '$on_uid' ");
    $cnt = $row[cnt] - 1;
    if ($cnt)
        $goods[full_name] .= " 외 {$cnt}건";
    $goods[count] = $row[cnt];

    return $goods;
}


// 패턴의 내용대로 해당 디렉토리에서 정렬하여 <select> 태그에 적용할 수 있게 반환
function get_list_skin_options($pattern, $dirname="./")
{
    $str = "";

    unset($arr);
    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if (preg_match("/$pattern/", $file, $matches)) {
            $arr[] = $matches[0];
        }
    }
    closedir($handle);

    sort($arr);
    foreach($arr as $key=>$value) {
        $str .= "<option value='$arr[$key]'>$arr[$key]</option>\n";
    }

    return $str;
}


// 일자 시간을 검사한다.
function check_datetime($datetime)
{
    if ($datetime == "0000-00-00 00:00:00")
        return true;

    $year   = substr($datetime, 0, 4);
    $month  = substr($datetime, 5, 2);
    $day    = substr($datetime, 8, 2);
    $hour   = substr($datetime, 11, 2);
    $minute = substr($datetime, 14, 2);
    $second = substr($datetime, 17, 2);

    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

    $tmp_datetime = date("Y-m-d H:i:s", $timestamp);
    if ($datetime == $tmp_datetime)
        return true;
    else
        return false;
}

// 경고메세지를 경고창으로
function alert_opener($msg='', $url='')
{
    global $g4;

    if (!$msg) $msg = '올바른 방법으로 이용해 주십시오.';

    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
    echo "<script type='text/javascript'>";
    echo "alert('$msg');";
    echo "opener.location.href='$url';";
    echo "self.close();";
    echo "</script>";
    exit;
}


function subtitle($title, $more="")
{
    global $g4;

    $s = "<table width=100% cellpadding=0 cellspacing=0><tr><td width=80% align=left><table border='0' cellpadding='0' cellspacing='1'><tr><td height='24'><img src='$g4[admin_path]/img/icon_title.gif' width=20 height=9> <font color='#525252'><b>$title</b></font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table><table width=100% cellpadding=0 cellspacing=0><tr><td height=1></td></tr></table></td><td width=20% align=right>";
    if ($more)
        $s .= "<a href='$more'><img src='$g4[admin_path]/img/icon_more.gif' width='43' height='11' border=0 align=absmiddle></a>";
    $s .= "</td></tr></table>\n";

    return $s;
}
//==============================================================================
// 쇼핑몰 함수 모음 끝
//==============================================================================
?>