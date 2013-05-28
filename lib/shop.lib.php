<?php
//==============================================================================
// 쇼핑몰 함수 모음 시작
//==============================================================================
// 장바구니 건수 검사
function get_cart_count($uq_id)
{
    global $g4;

    $sql = " select count(ct_id) as cnt from {$g4['shop_cart_table']} where uq_id = '$uq_id' ";
    $row = sql_fetch($sql);
    $cnt = (int)$row[cnt];
    return $cnt;
}

// 이미지를 얻는다
function get_image($img, $width=0, $height=0, $img_id='')
{
    global $g4, $default;

    $full_img = G4_DATA_PATH.'/item/'.$img;

    if (file_exists($full_img) && $img)
    {
        if (!$width)
        {
            $size = getimagesize($full_img);
            $width = $size[0];
            $height = $size[1];
        }
        $str = '<img src="'.G4_DATA_URL.'/item/'.$img.'" alt="" width="'.$width.'" height="'.$height.'"';

        if($img_id)
            $str .= ' id="'.$img_id.'"';

        $str .= '>';
    }
    else
    {
        $str = '<img src="'.G4_SHOP_URL.'/img/no_image.gif" alt="" ';
        if ($width)
            $str .= 'width="'.$width.'" height="'.$height.'"';
        else
            $str .= 'width="'.$default['de_mimg_width'].'" height="'.$default['de_mimg_height'].'"';

        if($img_id)
            $str .= ' id="'.$img_id.'"'.
        $str .= '>';
    }

    return $str;
}

// 상품 이미지를 얻는다
function get_it_image($it_id, $width, $height=0, $anchor=false, $img_id='')
{
    global $g4;

    if(!$it_id || !$width)
        return '';

    $sql = " select it_id, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
                from {$g4['shop_item_table']} where it_id = '$it_id' ";
    $row = sql_fetch($sql);

    if(!$row['it_id'])
        return '';

    for($i=1;$i<=10; $i++) {
        $file = G4_DATA_PATH.'/item/'.$row['it_img'.$i];
        if(is_file($file) && $row['it_img'.$i]) {
            $size = @getimagesize($file);
            if($size[2] < 1 || $size[2] > 3)
                continue;

            $filename = basename($file);
            $filepath = dirname($file);
            $img_width = $size[0];
            $img_height = $size[1];

            break;
        }
    }

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    if($filename) {
        //thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create, $is_crop=false, $crop_mode='center', $is_sharpen=true, $um_value='80/0.5/3')
        $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, false, 'center', true, $um_value='80/0.5/3');
    }

    if($thumb) {
        $file_url = str_replace(G4_PATH, G4_URL, $filepath.'/'.$thumb);
        $img = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'" alt=""';
    } else {
        $img = '<img src="'.G4_SHOP_URL.'/img/no_image.gif" width="'.$width.'"';
        if($height)
            $img .= ' height="'.$height.'"';
        $img .= ' alt=""';
    }

    if($img_id)
        $img .= ' id="'.$img_id.'"';
    $img .= '>';

    if($anchor)
        $img = '<a href="'.G4_SHOP_URL.'/item.php?it_id='.$it_id.'">'.$img.'</a>';

    return $img;
}

function get_it_thumbnail($img, $width, $height=0, $id='')
{
    $str = '';

    $file = G4_DATA_PATH.'/item/'.$img;
    if(is_file($file))
        $size = @getimagesize($file);

    if($size[2] < 1 || $size[2] > 3)
        return '';

    $img_width = $size[0];
    $img_height = $size[1];
    $filename = basename($file);
    $filepath = dirname($file);

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, false, 'center', true, $um_value='80/0.5/3');

    if($thumb) {
        $file_url = str_replace(G4_PATH, G4_URL, $filepath.'/'.$thumb);
        $str = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'"';
        if($id)
            $str .= ' id="'.$id.'"';
        $str .= ' alt="">';
    }

    return $str;
}

// 상품의 재고 (창고재고수량 - 주문대기수량)
function get_it_stock_qty($it_id)
{
    global $g4;

    $sql = " select it_stock_qty from {$g4['shop_item_table']} where it_id = '$it_id' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['it_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g4['shop_cart_table']}
              where it_id = '$it_id'
                and io_id = ''
                and ct_stock_use = 0
                and ct_status in ('주문', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}

// 옵션의 재고 (창고재고수량 - 주문대기수량)
function get_option_stock_qty($it_id, $io_id, $type)
{
    global $g4;

    $sql = " select io_stock_qty
                from {$g4['shop_item_option_table']}
                where it_id = '$it_id' and io_id = '$io_id' and io_type = '$type' and io_use = '1' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['io_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g4['shop_cart_table']}
              where it_id = '$it_id'
                and io_id = '$io_id'
                and io_type = '$type'
                and ct_stock_use = 0
                and ct_status in ('주문', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}

// 큰 이미지
function get_large_image($img, $it_id, $btn_image=true)
{
    global $g4;

    if (file_exists(G4_DATA_PATH.'/item/'.$img) && $img != '')
    {
        $size   = getimagesize(G4_DATA_PATH.'/item/'.$img);
        $width  = $size[0];
        $height = $size[1];
        $str = '<a href="javascript:popup_large_image(\''.$it_id.'\', \''.$img.'\', '.$width.', '.$height.', \''.G4_SHOP_URL.'\')">';
        if ($btn_image)
            $str .= '큰이미지</a>';
    }
    else
        $str = '';
    return $str;
}

// 금액 표시
function display_price($price, $tel_inq=false)
{
    if ($tel_inq)
        $price = '전화문의';
    else
        $price = number_format($price, 0).'원';

    return $price;
}

// 금액표시
// $it : 상품 배열
function get_price($it)
{
    global $member;

    if ($it['it_tel_inq']) return '전화문의';

    $price = $it['it_price'];

    return (int)$price;
}


// 포인트 표시
function display_point($point)
{
    return number_format($point, 0).'점';
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

// 상품이미지 업로드
function it_img_upload($srcfile, $filename, $dir)
{
    if($filename == '')
        return '';

    $size = @getimagesize($srcfile);
    if($size[2] < 1 || $size[2] > 3)
        return '';

    if(!is_dir($dir)) {
        @mkdir($dir, 0707);
        @chmod($dir, 0707);
    }

    $filename = preg_replace("/\s+/", "", $filename);
    $filename = preg_replace("/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/", "", $filename);

    $filename = preg_replace_callback(
                          "/[가-힣]+/",
                          create_function('$matches', 'return base64_encode($matches[0]);'),
                          $filename);

    upload_file($srcfile, $filename, $dir);

    $file = str_replace(G4_DATA_PATH.'/item/', '', $dir.'/'.$filename);

    return $file;
}

// 파일을 업로드 함
function upload_file($srcfile, $destfile, $dir)
{
    if ($destfile == "") return false;
    // 업로드 한후 , 퍼미션을 변경함
    @move_uploaded_file($srcfile, $dir.'/'.$destfile);
    @chmod($dir.'/'.$destfile, 0606);
    return true;
}

function message($subject, $content, $align="left", $width="450")
{
    $str = "
        <table width=\"$width\" cellpadding=\"4\" align=\"center\">
            <tr><td class=\"line\" height=\"1\"></td></tr>
            <tr>
                <td align=\"center\">$subject</td>
            </tr>
            <tr><td class=\"line\" height=\"1\"></td></tr>
            <tr>
                <td>
                    <table width=\"100%\" cellpadding=\"8\" cellspacing=\"0\">
                        <tr>
                            <td class=\"leading\" align=\"$align\">$content</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td class=\"line\" height=\"1\"></td></tr>
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
    global $member, $g4, $config;

    // 상품의 갯수
    $items = $list_mod * $list_row;

    // 1.02.00
    // it_order 추가
    $sql = " select *
               from {$g4['shop_item_table']}
              where it_use = '1'
                and it_type{$type} = '1' ";
    if ($ca_id) $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " order by it_order, it_id desc
              limit $items ";
    $result = sql_query($sql);
    /*
    if (!mysql_num_rows($result)) {
        return false;
    }
    */

    $file = G4_SHOP_PATH.'/'.$skin_file;
    if (!file_exists($file)) {
        echo $file.' 파일을 찾을 수 없습니다.';
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

// 모바일 유형별 상품 출력
function mobile_display_type($type, $skin_file, $list_row, $img_width, $img_height, $ca_id="")
{
    global $member, $g4, $config;

    // 상품의 갯수
    $items = $list_row;

    // 1.02.00
    // it_order 추가
    $sql = " select *
               from {$g4['shop_item_table']}
              where it_use = '1'
                and it_type{$type} = '1' ";
    if ($ca_id) $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " order by it_order, it_id desc
              limit $items ";
    $result = sql_query($sql);
    /*
    if (!mysql_num_rows($result)) {
        return false;
    }
    */

    $file = G4_MSHOP_PATH.'/'.$skin_file;
    if (!file_exists($file)) {
        echo $file.' 파일을 찾을 수 없습니다.';
    } else {
        //$td_width = (int)(100 / $list_mod);
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

    $sql = " select * from {$g4['shop_item_table']} where it_use = '1'";
    if ($ca_id)
        $sql .= " and ca_id LIKE '{$ca_id}%' ";
    $sql .= " order by it_order, it_id desc limit $items ";
    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = G4_SHOP_PATH.'/maintype'.$no.'.inc.php';
    if (!file_exists($file)) {
        echo $file.' 파일을 찾을 수 없습니다.';
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

// 별
function get_star($score)
{
    if ($score > 8) $star = 5;
    else if ($score > 6) $star = 4;
    else if ($score > 4) $star = 3;
    else if ($score > 2) $star = 2;
    else if ($score > 0) $star = 1;
    else $star = 5;

    return $star;
}

// 별 이미지
function get_star_image($it_id)
{
    global $g4;

    $sql = "select (SUM(is_score) / COUNT(*)) as score from {$g4['shop_item_ps_table']} where it_id = '$it_id' ";
    $row = sql_fetch($sql);

    return (int)get_star($row['score']);
}

// 메일 보내는 내용을 HTML 형식으로 만든다.
function email_content($str)
{
    global $g4;

    $s = "";
    $s .= "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset={$g4['charset']}\"><title>메일</title>\n";
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
    $time['days']    = (int)($gap / 86400);
    $time['hours']   = (int)(($gap - ($time['days'] * 86400)) / 3600);
    $time['minutes'] = (int)(($gap - ($time['days'] * 86400 + $time['hours'] * 3600)) / 60);
    $time['seconds'] = (int)($gap - ($time['days'] * 86400 + $time['hours'] * 3600 + $time['minutes'] * 60));
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

    $q1 = 'sort1='.$col;
    if ($type) {
        $q2 = 'sort2=desc';
        if ($sort1 == $col) {
            if ($sort2 == 'desc') {
                $q2 = 'sort2=asc';
            }
        }
    } else {
        $q2 = 'sort2=asc';
        if ($sort1 == $col) {
            if ($sort2 == 'asc') {
                $q2 = 'sort2=desc';
            }
        }
    }
    #return "$_SERVER[PHP_SELF]?$q1&amp;$q2&amp;page=$page";
    return "{$_SERVER['PHP_SELF']}?$q1&amp;$q2&amp;page=$page";
}


// 세션값을 체크하여 이쪽에서 온것이 아니면 메인으로
function session_check()
{
    global $g4;

    if (!trim(get_session('ss_uniqid')))
        gotourl(G4_SHOP_URL);
}

// 상품 선택옵션
function get_item_options($it_id, $subject)
{
    global $g4;

    if(!$it_id || !$subject)
        return '';

    $sql = " select * from {$g4['shop_item_option_table']} where io_type = '0' and it_id = '$it_id' and io_use = '1' order by io_no asc ";
    $result = sql_query($sql);
    if(!mysql_num_rows($result))
        return '';

    $str = '';
    $subj = explode(',', $subject);
    $subj_count = count($subj);

    if($subj_count > 1) {
        $options = array();

        // 옵션항목 배열에 저장
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $opt_id = explode(chr(30), $row['io_id']);

            for($k=0; $k<$subj_count; $k++) {
                if(!is_array($options[$k]))
                    $options[$k] = array();

                if($opt_id[$k] && !in_array($opt_id[$k], $options[$k]))
                    $options[$k][] = $opt_id[$k];
            }
        }

        // 옵션선택목록 만들기
        for($i=0; $i<$subj_count; $i++) {
            $opt = $options[$i];
            $opt_count = count($opt);
            $disabled = '';
            if($opt_count) {
                $seq = $i + 1;
                if($i > 0)
                    $disabled = ' disabled="disabled"';
                $str .= '<tr>'.PHP_EOL;
                $str .= '<th><label for="it_option_'.$seq.'">'.$subj[$i].'</label></th>'.PHP_EOL;

                $select = '<select name="it_option[]" id="it_option_'.$seq.'"'.$disabled.'>'.PHP_EOL;
                $select .= '<option value="">선택</option>'.PHP_EOL;
                for($k=0; $k<$opt_count; $k++) {
                    $opt_val = $opt[$k];
                    if($opt_val) {
                        $select .= '<option value="'.$opt_val.'">'.$opt_val.'</option>'.PHP_EOL;
                    }
                }
                $select .= '</select>'.PHP_EOL;

                $str .= '<td>'.$select.'</td>'.PHP_EOL;
                $str .= '</tr>'.PHP_EOL;
            }
        }
    } else {
        $str .= '<tr>'.PHP_EOL;
        $str .= '<th><label for="it_option_1">'.$subj[0].'</label></th>'.PHP_EOL;

        $select = '<select name="it_option[]" id="it_option_1">'.PHP_EOL;
        $select .= '<option value="">선택</option>'.PHP_EOL;
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($row['io_price'] >= 0)
                $price = '&nbsp;&nbsp;+ '.number_format($row['io_price']).'원';
            else
                $price = '&nbsp;&nbsp; '.number_format($row['io_price']).'원';

            if(!$row['io_stock_qty'])
                $soldout = '&nbsp;&nbsp;[품절]';
            else
                $soldout = '';

            $select .= '<option value="'.$row['io_id'].','.$row['io_price'].','.$row['io_stock_qty'].'">'.$row['io_id'].$price.$soldout.'</option>'.PHP_EOL;
        }
        $select .= '</select>'.PHP_EOL;

        $str .= '<td>'.$select.'</td>'.PHP_EOL;
        $str .= '</tr>'.PHP_EOL;
    }

    return $str;
}

// 상품 추가옵션
function get_item_supply($it_id, $subject)
{
    global $g4;

    if(!$it_id || !$subject)
        return '';

    $sql = " select * from {$g4['shop_item_option_table']} where io_type = '1' and it_id = '$it_id' and io_use = '1' order by io_no asc ";
    $result = sql_query($sql);
    if(!mysql_num_rows($result))
        return '';

    $str = '';

    $subj = explode(',', $subject);
    $subj_count = count($subj);
    $options = array();

    // 옵션항목 배열에 저장
    for($i=0; $row=sql_fetch_array($result); $i++) {
        $opt_id = explode(chr(30), $row['io_id']);

        if($opt_id[0] && !array_key_exists($opt_id[0], $options))
            $options[$opt_id[0]] = array();

        if($opt_id[1]) {
            if($row['io_price'] >= 0)
                $price = '&nbsp;&nbsp;+ '.number_format($row['io_price']).'원';
            else
                $price = '&nbsp;&nbsp; '.number_format($row['io_price']).'원';

            $options[$opt_id[0]][] = '<option value="'.$opt_id[1].','.$row['io_price'].','.$row['io_stock_qty'].'">'.$opt_id[1].$price.'</option>';
        }
    }

    // 옵션항목 만들기
    for($i=0; $i<$subj_count; $i++) {
        $opt = $options[$subj[$i]];
        $opt_count = count($opt);
        if($opt_count) {
            $seq = $i + 1;
            $str .= '<tr>'.PHP_EOL;
            $str .= '<th><label for="it_supply_'.$seq.'">'.$subj[$i].'</label></th>'.PHP_EOL;

            $select = '<select name="it_supply[]" id="it_supply_'.$seq.'">'.PHP_EOL;
            $select .= '<option value="">선택</option>'.PHP_EOL;
            for($k=0; $k<$opt_count; $k++) {
                $opt_val = $opt[$k];
                if($opt_val) {
                    $select .= $opt_val.PHP_EOL;
                }
            }
            $select .= '</select>'.PHP_EOL;
            $select .= '<button type="button" id="sit_sel_submit_'.$i.'" class="btn_frmline sit_sel_submit">추가</button>'.PHP_EOL;

            $str .= '<td class="td_sit_sel">'.$select.'</td>'.PHP_EOL;
            $str .= '</tr>'.PHP_EOL;
        }
    }

    return $str;
}

function print_item_options($it_id, $uq_id)
{
    global $g4;

    $sql = " select ct_option, ct_qty
                from {$g4['shop_cart_table']}
                where it_id = '$it_id' and uq_id = '$uq_id'
                order by io_type asc, ct_num asc, ct_id asc ";
    $result = sql_query($sql);

    $str = '';
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0)
            $str .= '<ul>'.PHP_EOL;
        $str .= '<li>'.$row['ct_option'].'&nbsp;&nbsp;'.$row['ct_qty'].'개</li>'.PHP_EOL;
    }

    if($i > 0)
        $str .= '</ul>';

    return $str;
}

function it_name_icon($it, $it_name="", $url=1)
{
    global $g4;

    $str = '';
    if ($it_name)
        $str = $it_name;
    // 제목이 없으면 아이콘만 나오도록 주석처리 - 지운아빠 2013-05-03
    //else
    //    $str = stripslashes($it['it_name']);

    if ($url)
        $str = '<a href="'.G4_SHOP_URL.'/item.php?it_id='.$it['it_id'].'">'.$str.'</a>';

    $str .= '<span class="sit_icon">';
    // 품절
    $stock = get_it_stock_qty($it['it_id']);
    if ($stock <= 0)
        $str .= ' <img src="'.G4_SHOP.'/img/icon_soldout2.gif" alt="품절"> ';
    if ($it['it_type1']) $str .= '<img src="'.G4_URL.'/img/shop/icon_hit2.gif" alt="히트">';
    if ($it['it_type2']) $str .= '<img src="'.G4_URL.'/img/shop/icon_rec2.gif" alt="추천">';
    if ($it['it_type3']) $str .= '<img src="'.G4_URL.'/img/shop/icon_new2.gif" alt="신상품">';
    if ($it['it_type4']) $str .= '<img src="'.G4_URL.'/img/shop/icon_best2.gif" alt="인기">';
    if ($it['it_type5']) $str .= '<img src="'.G4_URL.'/img/shop/icon_discount2.gif" alt="할인">';

    $str .= '</span>';

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

    if (!$position) $position = '왼쪽';

    include G4_SHOP_PATH.'/boxbanner'.$num.'.inc.php';
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
               from {$g4['shop_event_item_table']} a,
                    {$g4['shop_item_table']} b
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

    $file = G4_SHOP_PATH.'/maintype'.$no.'.inc.php';
    if (!file_exists($file)) {
        echo $file.' 파일을 찾을 수 없습니다.';
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
function get_goods($uq_id)
{
    global $g4;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from {$g4['shop_cart_table']} a, {$g4['shop_item_table']} b where a.it_id = b.it_id and a.uq_id = '$uq_id' order by ct_id limit 1 ");
    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods['it_id'] = $row['it_id'];
    $goods['full_name']= $goods['name'] = addslashes($row['it_name']);
    // 특수문자제거
    $goods['full_name'] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",  $goods['full_name']);

    // 상품건수
    $row = sql_fetch(" select count(*) as cnt from {$g4['shop_cart_table']} where uq_id = '$uq_id' ");
    $cnt = $row['cnt'] - 1;
    if ($cnt)
        $goods['full_name'] .= ' 외 '.$cnt.'건';
    $goods['count'] = $row['cnt'];

    return $goods;
}


// 패턴의 내용대로 해당 디렉토리에서 정렬하여 <select> 태그에 적용할 수 있게 반환
function get_list_skin_options($pattern, $dirname='./', $sval='')
{
    $str = '<option value="">선택</option>'.PHP_EOL;

    unset($arr);
    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if (preg_match("/$pattern/", $file, $matches)) {
            $arr[] = $matches[0];
        }
    }
    closedir($handle);

    sort($arr);
    foreach($arr as $value) {
        if($value == $sval)
            $selected = ' selected="selected"';
        else
            $selected = '';

        $str .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>'.PHP_EOL;
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

    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset={$g4['charset']}\">";
    echo "<script type='text/javascript'>";
    echo "alert(\"$msg\");";
    echo "opener.location.href=\"$url\";";
    echo "self.close();";
    echo "</script>";
    exit;
}

// option 리스트에 selected 추가
function conv_selected_option($options, $value)
{
    if(!$options)
        return '';

    $options = str_replace('value="'.$value.'"', 'value="'.$value.'" selected', $options);

    return $options;
}

// 주문서 번호를 얻는다.
function get_new_od_id()
{
    global $g4;

    // 주문서 테이블 Lock 걸고
    sql_query(" LOCK TABLES {$g4['shop_order_table']} READ, {$g4['shop_order_table']} WRITE ", FALSE);
    // 주문서 번호를 만든다.
    $date = date("ymd", time());    // 2002년 3월 7일 일경우 020307
    $sql = " select max(od_id) as max_od_id from {$g4['shop_order_table']} where SUBSTRING(od_id, 1, 6) = '$date' ";
    $row = sql_fetch($sql);
    $od_id = $row['max_od_id'];
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

// 상품 목록 : 관련 상품 출력
function display_relation_item($it_id, $width, $height, $rows=3)
{
    global $g4;

    $str = '';

    if(!$it_id)
        return $str;

    $sql = " select b.it_id, b.it_name, b.it_price, b.it_tel_inq, b.it_gallery
                from {$g4['shop_item_relation_table']} a left join {$g4['shop_item_table']} b on ( a.it_id2 = b.it_id )
                where a.it_id = '$it_id'
                order by ir_no asc
                limit 0, $rows ";
    $result = sql_query($sql);

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0) {
            $str .= '<span class="sound_only">관련 상품 시작</span>';
            $str .= '<ul class="sct_rel_ul">';
        }

        $it_name = get_text($row['it_name']); // 상품명
        $it_price = ''; // 상품가격
        if(!$row['it_gallery']) {
            $it_price = get_price($row);
            if(!$row['it_tel_inq'])
                $it_price = display_price($it_price);
        }

        $img = get_it_image($row['it_id'], $width, $height);

        $str .= '<li class="sct_rel_li"><a href="'.G4_SHOP_URL.'/item.php?it_id='.$row['it_id'].'" class="sct_rel_a">'.$img.'</a></li>';
    }

    if($i > 0)
        $str .= '</ul><span class="sound_only">관련 상품 끝</span>';

    return $str;
}

// 상품이미지에 유형 아이콘 출력
function display_item_icon($it)
{
    $icon = '';

    if($it['it_gallery'] || $it['it_type1'] || $it['it_type2'] || $it['it_type3'] || $it['it_type4'] || $it['it_type5']) {
        if($it['it_gallery']) // sold out
            $icon .= '<img src="'.G4_URL.'/img/shop/icon_soldout.gif" alt="품절" class="sct_icon_so">';

        if($it['it_type3'])
            $icon .= '<img src="'.G4_URL.'/img/shop/icon_new.gif" alt="신상품" class="sct_icon_new">';

        if($it['it_type1'])
            $icon .= '<img src="'.G4_URL.'/img/shop/icon_hit.gif" alt="히트" class="sct_icon_hit">';

        if($it['it_type2'])
            $icon .= '<img src="'.G4_URL.'/img/shop/icon_rec.gif" alt="추천" class="sct_icon_rec">';

        if($it['it_type4'])
            $icon .= '<img src="'.G4_URL.'/img/shop/icon_best.gif" alt="인기" class="sct_icon_best">';

        if($it['it_type5'])
            $icon .= '<img src="'.G4_URL.'/img/shop/icon_discount.gif" alt="할인" class="sct_icon_discount">';
    }

    return $icon;
}

// sns 공유하기
function get_sns_share_link($sns, $url, $title, $img)
{
    if(!$sns)
        return '';

    switch($sns) {
        case 'facebook':
            $str = '<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($url).'&amp;t='.urlencode($title).'" class="share-facebook" target="_blank"><img src="'.$img.'" alt="페이스북에 공유"></a>';
            break;
        case 'twitter':
            $str = '<a href="https://twitter.com/share?url='.urlencode($url).'&amp;text='.urlencode($title).'" class="share-twitter" target="_blank"><img src="'.$img.'" alt="트위터에 공유"></a>';
            break;
        case 'googleplus':
            $str = '<a href="https://plus.google.com/share?url='.urlencode($url).'" class="share-googleplus" target="_blank"><img src="'.$img.'" alt="구글플러스에 공유"></a>';
            break;
    }

    return $str;
}

// 상품이미지 썸네일 삭제
function delete_item_thumbnail($dir, $file)
{
    if(!$dir || !$file)
        return;

    $filename = preg_replace("/\.[^\.]+$/i", "", $file); // 확장자제거

    $files = glob($dir.'/thumb-'.$filename.'*');

    if(is_array($files)) {
        foreach($files as $thumb_file) {
            @unlink($thumb_file);
        }
    }
}
//==============================================================================
// 쇼핑몰 함수 모음 끝
//==============================================================================
?>