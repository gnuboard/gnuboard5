<?php
//==============================================================================
// 쇼핑몰 라이브러리 모음 시작
//==============================================================================

/*
간편 사용법 : 상품유형을 1~5 사이로 지정합니다.
$disp = new item_list(1);
echo $disp->run();


유형+분류별로 노출하는 경우 상세 사용법 : 상품유형을 지정하는 것은 동일합니다.
$disp = new item_list(1);
// 사용할 스킨을 바꿉니다.
$disp->set_list_skin("type_user.skin.php");
// 1단계분류를 20으로 시작되는 분류로 지정합니다.
$disp->set_category("20", 1);
echo $disp->run();


분류별로 노출하는 경우 상세 사용법
// type13.skin.php 스킨으로 3개씩 2줄을 폭 150 사이즈로 분류코드 30 으로 시작되는 상품을 노출합니다.
$disp = new item_list(0, "type13.skin.php", 3, 2, 150, 0, "30");
echo $disp->run();


이벤트로 노출하는 경우 상세 사용법
// type13.skin.php 스킨으로 3개씩 2줄을 폭 150 사이즈로 상품을 노출합니다.
// 스킨의 경로는 스킨 파일의 절대경로를 지정합니다.
$disp = new item_list(0, G5_SHOP_SKIN_PATH.'/list.10.skin.php', 3, 2, 150, 0);
// 이벤트번호를 설정합니다.
$disp->set_event("12345678");
echo $disp->run();

참고) 영카트4의 display_type 함수와 사용방법이 비슷한 class 입니다.
      display_category 나 display_event 로 사용하기 위해서는 $type 값만 넘기지 않으면 됩니다.
*/

class item_list
{
    // 상품유형 : 기본적으로 1~5 까지 사용할수 있으며 0 으로 설정하는 경우 상품유형별로 노출하지 않습니다.
    // 분류나 이벤트로 노출하는 경우 상품유형을 0 으로 설정하면 됩니다.
    protected $type;

    protected $list_skin;
    protected $list_mod;
    protected $list_row;
    protected $img_width;
    protected $img_height;

    // 상품상세보기 경로
    protected $href = "";

    // select 에 사용되는 필드
    protected $fields = "*";

    // 분류코드로만 사용하는 경우 상품유형($type)을 0 으로 설정하면 됩니다.
    protected $ca_id = "";
    protected $ca_id2 = "";
    protected $ca_id3 = "";

    // 노출순서
    protected $order_by = "it_order, it_id desc";

    // 상품의 이벤트번호를 저장합니다.
    protected $event = "";

    // 스킨의 기본 css 를 다른것으로 사용하고자 할 경우에 사용합니다.
    protected $css = "";

    // 상품의 사용여부를 따져 노출합니다. 0 인 경우 모든 상품을 노출합니다.
    protected $use = 1;

    // 모바일에서 노출하고자 할 경우에 true 로 설정합니다.
    protected $is_mobile = false;

    // 기본으로 보여지는 필드들
    protected $view_it_id    = false;       // 상품코드
    protected $view_it_img   = true;        // 상품이미지
    protected $view_it_name  = true;        // 상품명
    protected $view_it_basic = true;        // 기본설명
    protected $view_it_price = true;        // 판매가격
    protected $view_it_cust_price = false;  // 소비자가
    protected $view_it_icon = false;        // 아이콘
    protected $view_sns = false;            // SNS
    protected $view_star = false;           // 별점

    // 몇번째 class 호출인지를 저장합니다.
    protected $count = 0;

    // true 인 경우 페이지를 구한다.
    protected $is_page = false;

    // 페이지 표시를 위하여 총 상품수를 구합니다.
    public $total_count = 0;

    // sql limit 의 시작 레코드
    protected $from_record = 0;

    // 외부에서 쿼리문을 넘겨줄 경우에 담아두는 변수
    protected $query = "";

    // $type        : 상품유형 (기본으로 1~5까지 사용)
    // $list_skin   : 상품리스트를 노출할 스킨을 설정합니다. 스킨위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php
    // $list_mod    : 1줄에 몇개의 상품을 노출할지를 설정합니다.
    // $list_row    : 상품을 몇줄에 노출할지를 설정합니다.
    // $img_width   : 상품이미지의 폭을 설정합니다.
    // $img_height  : 상품이미지의 높이을 설정합니다. 0 으로 설정하는 경우 썸네일 이미지의 높이는 폭에 비례하여 생성합니다.
    //function __construct($type=0, $list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0, $ca_id='') {
    function __construct($list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0) {
        $this->list_skin  = $list_skin;
        $this->list_mod   = $list_mod;
        $this->list_row   = $list_row;
        $this->img_width  = $img_width;
        $this->img_height = $img_height;
        $this->set_href(G5_SHOP_URL.'/item.php?it_id=');
        $this->count++;
    }

    function set_type($type) {
        $this->type = $type;
        if ($type) {
            $this->set_list_skin($this->list_skin);
            $this->set_list_mod($this->list_mod);
            $this->set_list_row($this->list_row);
            $this->set_img_size($this->img_width, $this->img_height);
        }
    }

    // 분류코드로 검색을 하고자 하는 경우 아래와 같이 인수를 넘겨줍니다.
    // 1단계 분류는 (분류코드, 1)
    // 2단계 분류는 (분류코드, 2)
    // 3단계 분류는 (분류코드, 3)
    function set_category($ca_id, $level=1) {
        if ($level == 2) {
            $this->ca_id2 = $ca_id;
        } else if ($level == 3) {
            $this->ca_id3 = $ca_id;
        } else {
            $this->ca_id = $ca_id;
        }
    }

    // 이벤트코드를 인수로 넘기게 되면 해당 이벤트에 속한 상품을 노출합니다.
    function set_event($ev_id) {
        $this->event = $ev_id;
    }

    // 리스트 스킨을 바꾸고자 하는 경우에 사용합니다.
    // 리스트 스킨의 위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php 입니다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_skin($list_skin) {
        global $default;
        if ($this->is_mobile) {
            $this->list_skin = $list_skin ? $list_skin : G5_MSHOP_SKIN_PATH.'/'.preg_replace('/[^A-Za-z0-9 _ .-]/', '', $default['de_mobile_type'.$this->type.'_list_skin']);
        } else {
            $this->list_skin = $list_skin ? $list_skin : G5_SHOP_SKIN_PATH.'/'.preg_replace('/[^A-Za-z0-9 _ .-]/', '', $default['de_type'.$this->type.'_list_skin']);
        }
    }

    // 1줄에 몇개를 노출할지를 사용한다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_mod($list_mod) {
        global $default;
        if ($this->is_mobile) {
            $this->list_mod = $list_mod ? $list_mod : $default['de_mobile_type'.$this->type.'_list_mod'];
        } else {
            $this->list_mod = $list_mod ? $list_mod : $default['de_type'.$this->type.'_list_mod'];
        }
    }

    // 몇줄을 노출할지를 사용한다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_row($list_row) {
        global $default;
        if ($this->is_mobile) {
            $this->list_row = $list_row ? $list_row : $default['de_mobile_type'.$this->type.'_list_row'];
        } else {
            $this->list_row = $list_row ? $list_row : $default['de_type'.$this->type.'_list_row'];
        }
        if (!$this->list_row)
            $this->list_row = 1;
    }

    // 노출이미지(썸네일생성)의 폭, 높이를 설정합니다. 높이를 0 으로 설정하는 경우 쎰네일 비율에 따릅니다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_img_size($img_width, $img_height=0) {
        global $default;
        if ($this->is_mobile) {
            $this->img_width = $img_width ? $img_width : $default['de_mobile_type'.$this->type.'_img_width'];
            $this->img_height = $img_height ? $img_height : $default['de_mobile_type'.$this->type.'_img_height'];
        } else {
            $this->img_width = $img_width ? $img_width : $default['de_type'.$this->type.'_img_width'];
            $this->img_height = $img_height ? $img_height : $default['de_type'.$this->type.'_img_height'];
        }
    }

    // 특정 필드만 select 하는 경우에는 필드명을 , 로 구분하여 "field1, field2, field3, ... fieldn" 으로 인수를 넘겨줍니다.
    function set_fields($str) {
        $this->fields = $str;
    }

    // 특정 필드로 정렬을 하는 경우 필드와 정렬순서를 , 로 구분하여 "field1 desc, field2 asc, ... fieldn desc " 으로 인수를 넘겨줍니다.
    function set_order_by($str) {
        $this->order_by = $str;
    }

    // 사용하는 상품외에 모든 상품을 노출하려면 0 을 인수로 넘겨줍니다.
    function set_use($use) {
        $this->use = $use;
    }

    // 모바일로 사용하려는 경우 true 를 인수로 넘겨줍니다.
    function set_mobile($mobile=true) {
        $this->is_mobile = $mobile;
    }

    // 스킨에서 특정 필드를 노출하거나 하지 않게 할수 있습니다.
    // 가령 소비자가는 처음에 노출되지 않도록 설정되어 있지만 노출을 하려면
    // ("it_cust_price", true) 와 같이 인수를 넘겨줍니다.
    // 이때 인수로 넘겨주는 값은 스킨에 정의된 필드만 가능하다는 것입니다.
    function set_view($field, $view=true) {
        $this->{"view_".$field} = $view;
    }

    // anchor 태그에 하이퍼링크를 다른 주소로 걸거나 아예 링크를 걸지 않을 수 있습니다.
    // 인수를 "" 공백으로 넘기면 링크를 걸지 않습니다.
    function set_href($href) {
        $this->href = $href;
    }

    // ul 태그의 css 를 교체할수 있다. "sct sct_abc" 를 인수로 넘기게 되면
    // 기존의 ul 태그에 걸린 css 는 무시되며 인수로 넘긴 css 가 사용됩니다.
    function set_css($css) {
        $this->css = $css;
    }

    // 페이지를 노출하기 위해 true 로 설정할때 사용합니다.
    function set_is_page($is_page) {
        $this->is_page = $is_page;
    }

    // select ... limit 의 시작값
    function set_from_record($from_record) {
        $this->from_record = $from_record;
    }

    // 외부에서 쿼리문을 넘겨줄 경우에 담아둡니다.
    function set_query($query) {
        $this->query = $query;
    }

    // class 에 설정된 값으로 최종 실행합니다.
    function run() {

        global $g5, $config, $member, $default;
        
        $list = array();

        if ($this->query) {

            $sql = $this->query;
            $result = sql_query($sql);
            $this->total_count = @sql_num_rows($result);

        } else {

            $where = array();
            if ($this->use) {
                $where[] = " it_use = '1' ";
            }

            if ($this->type) {
                $where[] = " it_type{$this->type} = '1' ";
            }

            if ($this->ca_id || $this->ca_id2 || $this->ca_id3) {
                $where_ca_id = array();
                if ($this->ca_id) {
                    $where_ca_id[] = " ca_id like '{$this->ca_id}%' ";
                }
                if ($this->ca_id2) {
                    $where_ca_id[] = " ca_id2 like '{$this->ca_id2}%' ";
                }
                if ($this->ca_id3) {
                    $where_ca_id[] = " ca_id3 like '{$this->ca_id3}%' ";
                }
                $where[] = " ( " . implode(" or ", $where_ca_id) . " ) ";
            }

            if ($this->order_by) {
                $sql_order = " order by {$this->order_by} ";
            }

            if ($this->event) {
                $sql_select = " select {$this->fields} ";
                $sql_common = " from `{$g5['g5_shop_event_item_table']}` a left join `{$g5['g5_shop_item_table']}` b on (a.it_id = b.it_id) ";
                $where[] = " a.ev_id = '{$this->event}' ";
            } else {
                $sql_select = " select {$this->fields} ";
                $sql_common = " from `{$g5['g5_shop_item_table']}` ";
            }
            $sql_where = " where " . implode(" and ", $where);
            $sql_limit = " limit " . $this->from_record . " , " . ($this->list_mod * $this->list_row);

            $sql = $sql_select . $sql_common . $sql_where . $sql_order . $sql_limit;
            $result = sql_query($sql);

            if ($this->is_page) {
                $sql2 = " select count(*) as cnt " . $sql_common . $sql_where;
                $row2 = sql_fetch($sql2);
                $this->total_count = $row2['cnt'];
            }
        }

        if( isset($result) && $result ){
            while ($row=sql_fetch_array($result)) {
                
                if( isset($row['it_seo_title']) && ! $row['it_seo_title'] ){
                    shop_seo_title_update($row['it_id']);
                }
                
                $row['it_basic'] = conv_content($row['it_basic'], 1);
                $list[] = $row;
            }

            if(function_exists('sql_data_seek')){
                sql_data_seek($result, 0);
            }
        }

        $file = $this->list_skin;

        if ($this->list_skin == "") {
            return $this->count."번 item_list() 의 스킨파일이 지정되지 않았습니다.";
        } else if (!file_exists($file)) {
            return $file." 파일을 찾을 수 없습니다.";
        } else {
            ob_start();
            $list_mod = $this->list_mod;
            include($file);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
}

// 장바구니 건수 검사
function get_cart_count($cart_id)
{
    global $g5, $default;

    $sql = " select count(ct_id) as cnt from {$g5['g5_shop_cart_table']} where od_id = '$cart_id' ";
    $row = sql_fetch($sql);
    $cnt = (int)$row['cnt'];
    return $cnt;
}


// 이미지를 얻는다
function get_image($img, $width=0, $height=0, $img_id='')
{
    global $g5, $default;

    $full_img = G5_DATA_PATH.'/item/'.$img;

    if (file_exists($full_img) && $img)
    {
        if (!$width)
        {
            $size = getimagesize($full_img);
            $width = $size[0];
            $height = $size[1];
        }
        $str = '<img src="'.G5_DATA_URL.'/item/'.$img.'" alt="" width="'.$width.'" height="'.$height.'"';

        if($img_id)
            $str .= ' id="'.$img_id.'"';

        $str .= '>';
    }
    else
    {
        $str = '<img src="'.G5_SHOP_URL.'/img/no_image.gif" alt="" ';
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
function get_it_image($it_id, $width, $height=0, $anchor=false, $img_id='', $img_alt='', $is_crop=false)
{
    global $g5;

    if(!$it_id || !$width)
        return '';

    $row = get_shop_item($it_id, true);

    if(!$row['it_id'])
        return '';

    $filename = $thumb = $img = '';
    
    $img_width = 0;
    for($i=1;$i<=10; $i++) {
        $file = G5_DATA_PATH.'/item/'.$row['it_img'.$i];
        if(is_file($file) && $row['it_img'.$i]) {
            $size = @getimagesize($file);
            if(! isset($size[2]) || $size[2] < 1 || $size[2] > 3)
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
        $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', false, $um_value='80/0.5/3');
    }

    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
        $img = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'" alt="'.$img_alt.'"';
    } else {
        $img = '<img src="'.G5_SHOP_URL.'/img/no_image.gif" width="'.$width.'"';
        if($height)
            $img .= ' height="'.$height.'"';
        $img .= ' alt="'.$img_alt.'"';
    }

    if($img_id)
        $img .= ' id="'.$img_id.'"';
    $img .= '>';

    if($anchor)
        $img = $img = '<a href="'.shop_item_url($it_id).'">'.$img.'</a>';

    return run_replace('get_it_image_tag', $img, $thumb, $it_id, $width, $height, $anchor, $img_id, $img_alt, $is_crop);
}

// 상품이미지 썸네일 생성
function get_it_thumbnail($img, $width, $height=0, $id='', $is_crop=false)
{
    $str = '';

    if ( $replace_tag = run_replace('get_it_thumbnail_tag', $str, $img, $width, $height, $id, $is_crop) ){
        return $replace_tag;
    }

    $file = G5_DATA_PATH.'/item/'.$img;
    if(is_file($file))
        $size = @getimagesize($file);

    if (! (isset($size) && is_array($size))) 
        return '';

    if($size[2] < 1 || $size[2] > 3)
        return '';

    $img_width = $size[0];
    $img_height = $size[1];
    $filename = basename($file);
    $filepath = dirname($file);

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', false, $um_value='80/0.5/3');

    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
        $str = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'"';
        if($id)
            $str .= ' id="'.$id.'"';
        $str .= ' alt="">';
    }

    return $str;
}


// 이미지 URL 을 얻는다.
function get_it_imageurl($it_id)
{
    global $g5;

    $row = get_shop_item($it_id, true);
    $filepath = '';

    for($i=1; $i<=10; $i++) {
        $img = $row['it_img'.$i];
        $file = G5_DATA_PATH.'/item/'.$img;
        if(!is_file($file))
            continue;

        $size = @getimagesize($file);
        if($size[2] < 1 || $size[2] > 3)
            continue;

        $filepath = $file;
        break;
    }

    if($filepath)
        $str = str_replace(G5_PATH, G5_URL, $filepath);
    else
        $str = G5_SHOP_URL.'/img/no_image.gif';

    return $str;
}


// 상품의 재고 (창고재고수량 - 주문대기수량)
function get_it_stock_qty($it_id)
{
    global $g5;

    $sql = " select it_stock_qty from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['it_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g5['g5_shop_cart_table']}
              where it_id = '$it_id'
                and io_id = ''
                and ct_stock_use = 0
                and ct_status in ('주문', '입금', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}


// 옵션의 재고 (창고재고수량 - 주문대기수량)
function get_option_stock_qty($it_id, $io_id, $type)
{
    global $g5;

    $sql = " select io_stock_qty
                from {$g5['g5_shop_item_option_table']}
                where it_id = '$it_id' and io_id = '$io_id' and io_type = '$type' and io_use = '1' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['io_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g5['g5_shop_cart_table']}
              where it_id = '$it_id'
                and io_id = '$io_id'
                and io_type = '$type'
                and ct_stock_use = 0
                and ct_status in ('주문', '입금', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}


// 큰 이미지
function get_large_image($img, $it_id, $btn_image=true)
{
    global $g5;

    if (file_exists(G5_DATA_PATH.'/item/'.$img) && $img != '')
    {
        $size   = getimagesize(G5_DATA_PATH.'/item/'.$img);
        $width  = $size[0];
        $height = $size[1];
        $str = '<a href="javascript:popup_large_image(\''.$it_id.'\', \''.$img.'\', '.$width.', '.$height.', \''.G5_SHOP_URL.'\')">';
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


// 상품이미지 업로드
function it_img_upload($srcfile, $filename, $dir)
{
    if($filename == '')
        return '';

    $size = @getimagesize($srcfile);
    if($size[2] < 1 || $size[2] > 3)
        return '';

    //php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
    if (!preg_match('/\.(gif|jpe?g|png)$/i', $filename))
        return '';

    if(!is_dir($dir)) {
        @mkdir($dir, G5_DIR_PERMISSION);
        @chmod($dir, G5_DIR_PERMISSION);
    }

    $pattern = "/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/";

    $filename = preg_replace("/\s+/", "", $filename);
    $filename = preg_replace( $pattern, "", $filename);

    $filename = preg_replace_callback("/[가-힣]+/", '_callback_it_img_upload', $filename);

    $filename = preg_replace( $pattern, "", $filename);
    $prepend = '';

    // 동일한 이름의 파일이 있으면 파일명 변경
    if(is_file($dir.'/'.$filename)) {
        for($i=0; $i<20; $i++) {
            $prepend = str_replace('.', '_', microtime(true)).'_';

            if(is_file($dir.'/'.$prepend.$filename)) {
                usleep(mt_rand(100, 10000));
                continue;
            } else {
                break;
            }
        }
    }

    $filename = $prepend.$filename;

    upload_file($srcfile, $filename, $dir);

    $file = str_replace(G5_DATA_PATH.'/item/', '', $dir.'/'.$filename);

    return $file;
}

function _callback_it_img_upload($matches){
    return isset($matches[0]) ? base64_encode($matches[0]) : '';
}

// 파일을 업로드 함
function upload_file($srcfile, $destfile, $dir)
{
    if ($destfile == "") return false;
    // 업로드 한후 , 퍼미션을 변경함
    @move_uploaded_file($srcfile, $dir.'/'.$destfile);
    @chmod($dir.'/'.$destfile, G5_FILE_PERMISSION);
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
//function display_type($type, $skin_file, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
function display_type($type, $list_skin='', $list_mod='', $list_row='', $img_width='', $img_height='', $ca_id='')
{
    global $member, $g5, $config, $default;

    if (!$default["de_type{$type}_list_use"]) return "";

    $list_skin  = $list_skin  ? $list_skin  : $default["de_type{$type}_list_skin"];
    $list_mod   = $list_mod   ? $list_mod   : $default["de_type{$type}_list_mod"];
    $list_row   = $list_row   ? $list_row   : $default["de_type{$type}_list_row"];
    $img_width  = $img_width  ? $img_width  : $default["de_type{$type}_img_width"];
    $img_height = $img_height ? $img_height : $default["de_type{$type}_img_height"];

    // 상품수
    $items = $list_mod * $list_row;

    // 1.02.00
    // it_order 추가
    $sql = " select * from {$g5['g5_shop_item_table']} where it_use = '1' and it_type{$type} = '1' ";
    if ($ca_id) $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " order by it_order, it_id desc limit $items ";
    $result = sql_query($sql);
    /*
    if (!sql_num_rows($result)) {
        return false;
    }
    */

    //$file = G5_SHOP_PATH.'/'.$skin_file;
    $file = G5_SHOP_SKIN_PATH.'/'.$list_skin;
    if (!file_exists($file)) {
        return G5_SHOP_SKIN_URL.'/'.$list_skin.' 파일을 찾을 수 없습니다.';
    } else {
        $td_width = (int)(100 / $list_mod);
        ob_start();
        include $file;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}


// 모바일 유형별 상품 출력
function mobile_display_type($type, $skin_file, $list_row, $img_width, $img_height, $ca_id="")
{
    global $member, $g5, $config;

    // 상품수
    $items = $list_row;

    // 1.02.00
    // it_order 추가
    $sql = " select * from {$g5['g5_shop_item_table']} where it_use = '1' and it_type{$type} = '1' ";
    if ($ca_id) $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " order by it_order, it_id desc limit $items ";
    $result = sql_query($sql);
    /*
    if (!sql_num_rows($result)) {
        return false;
    }
    */

    $file = G5_MSHOP_PATH.'/'.$skin_file;
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
    global $member, $g5;

    // 상품수
    $items = $list_mod * $list_row;

    $sql = " select * from {$g5['g5_shop_item_table']} where it_use = '1'";
    if ($ca_id)
        $sql .= " and ca_id LIKE '{$ca_id}%' ";
    $sql .= " order by it_order, it_id desc limit $items ";
    $result = sql_query($sql);
    if (!sql_num_rows($result)) {
        return false;
    }

    $file = G5_SHOP_PATH.'/maintype'.$no.'.inc.php';
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
    $star = round($score);
    if ($star > 5) $star = 5;
    else if ($star < 0) $star = 0;

    return $star;
}


// 별 이미지
function get_star_image($it_id)
{
    global $g5;

    $sql = "select (SUM(is_score) / COUNT(*)) as score from {$g5['g5_shop_item_use_table']} where it_id = '$it_id' and is_confirm = 1 ";
    $row = sql_fetch($sql);

    return (int)get_star($row['score']);
}


// 메일 보내는 내용을 HTML 형식으로 만든다.
function email_content($str)
{
    global $g5;

    $s = "";
    $s .= "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset={$g5['charset']}\"><title>메일</title>\n";
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
    #return "$_SERVER[SCRIPT_NAME]?$q1&amp;$q2&amp;page=$page";
    return "{$_SERVER['SCRIPT_NAME']}?$q1&amp;$q2&amp;page=$page";
}

// 세션값을 체크하여 이쪽에서 온것이 아니면 메인으로
function session_check()
{
    global $g5;

    if (!trim(get_session('ss_uniqid')))
        gotourl(G5_SHOP_URL);
}

// 상품 선택옵션
function get_item_options($it_id, $subject, $is_div='', $is_first_option_title='')
{
    global $g5;

    if(!$it_id || !$subject)
        return '';

    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '$it_id' and io_use = '1' order by io_no asc ";
    $result = sql_query($sql);
    if(!sql_num_rows($result))
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
                if(! (isset($options[$k]) && is_array($options[$k])))
                    $options[$k] = array();

                if(isset($opt_id[$k]) && $opt_id[$k] && !in_array($opt_id[$k], $options[$k]))
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

                if($is_div === 'div') {
                    $str .= '<div class="get_item_options">'.PHP_EOL;
                    $str .= '<label for="it_option_'.$seq.'" class="label-title">'.$subj[$i].'</label>'.PHP_EOL;
                } else {
                    $str .= '<tr>'.PHP_EOL;
                    $str .= '<th><label for="it_option_'.$seq.'" class="label-title">'.$subj[$i].'</label></th>'.PHP_EOL;
                }

                $select = '<select id="it_option_'.$seq.'" class="it_option"'.$disabled.'>'.PHP_EOL;

                $first_option_title = $is_first_option_title ? $subj[$i] : '선택';

                $select .= '<option value="">'.$first_option_title.'</option>'.PHP_EOL;
                for($k=0; $k<$opt_count; $k++) {
                    $opt_val = $opt[$k];
                    if(strlen($opt_val)) {
                        $select .= '<option value="'.$opt_val.'">'.$opt_val.'</option>'.PHP_EOL;
                    }
                }
                $select .= '</select>'.PHP_EOL;

                if($is_div === 'div') {
                    $str .= '<span>'.$select.'</span>'.PHP_EOL;
                    $str .= '</div>'.PHP_EOL;
                } else {
                    $str .= '<td>'.$select.'</td>'.PHP_EOL;
                    $str .= '</tr>'.PHP_EOL;
                }
            }
        }
    } else {
        if($is_div === 'div') {
            $str .= '<div class="get_item_options">'.PHP_EOL;
            $str .= '<label for="it_option_1">'.$subj[0].'</label>'.PHP_EOL;
        } else {
            $str .= '<tr>'.PHP_EOL;
            $str .= '<th><label for="it_option_1">'.$subj[0].'</label></th>'.PHP_EOL;
        }

        $select = '<select id="it_option_1" class="it_option">'.PHP_EOL;
        $select .= '<option value="">선택</option>'.PHP_EOL;
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($row['io_price'] >= 0)
                $price = '&nbsp;&nbsp;+ '.number_format($row['io_price']).'원';
            else
                $price = '&nbsp;&nbsp; '.number_format($row['io_price']).'원';

            if($row['io_stock_qty'] < 1)
                $soldout = '&nbsp;&nbsp;[품절]';
            else
                $soldout = '';

            $select .= '<option value="'.$row['io_id'].','.$row['io_price'].','.$row['io_stock_qty'].'">'.$row['io_id'].$price.$soldout.'</option>'.PHP_EOL;
        }
        $select .= '</select>'.PHP_EOL;
        
        if($is_div === 'div') {
            $str .= '<span>'.$select.'</span>'.PHP_EOL;
            $str .= '</div>'.PHP_EOL;
        } else {
            $str .= '<td>'.$select.'</td>'.PHP_EOL;
            $str .= '</tr>'.PHP_EOL;
        }

    }

    return $str;
}

// 상품 추가옵션
function get_item_supply($it_id, $subject, $is_div='', $is_first_option_title='')
{
    global $g5;

    if(!$it_id || !$subject)
        return '';

    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '1' and it_id = '$it_id' and io_use = '1' order by io_no asc ";
    $result = sql_query($sql);
    if(!sql_num_rows($result))
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

        if(strlen($opt_id[1])) {
            if($row['io_price'] >= 0)
                $price = '&nbsp;&nbsp;+ '.number_format($row['io_price']).'원';
            else
                $price = '&nbsp;&nbsp; '.number_format($row['io_price']).'원';
            $io_stock_qty = get_option_stock_qty($it_id, $row['io_id'], $row['io_type']);

            if($io_stock_qty < 1)
                $soldout = '&nbsp;&nbsp;[품절]';
            else
                $soldout = '';

            $options[$opt_id[0]][] = '<option value="'.$opt_id[1].','.$row['io_price'].','.$io_stock_qty.'">'.$opt_id[1].$price.$soldout.'</option>';
        }
    }

    // 옵션항목 만들기
    for($i=0; $i<$subj_count; $i++) {
        $opt = (isset($subj[$i]) && isset($options[$subj[$i]])) ? $options[$subj[$i]] : array();
        $opt_count = count($opt);
        if($opt_count) {
            $seq = $i + 1;
            if($is_div === 'div') {
                $str .= '<div class="get_item_supply">'.PHP_EOL;
                $str .= '<label for="it_supply_'.$seq.'" class="label-title">'.$subj[$i].'</label>'.PHP_EOL;
            } else {
                $str .= '<tr>'.PHP_EOL;
                $str .= '<th><label for="it_supply_'.$seq.'">'.$subj[$i].'</label></th>'.PHP_EOL;
            }
            
            $first_option_title = $is_first_option_title ? $subj[$i] : '선택';

            $select = '<select id="it_supply_'.$seq.'" class="it_supply">'.PHP_EOL;
            $select .= '<option value="">'.$first_option_title.'</option>'.PHP_EOL;
            for($k=0; $k<$opt_count; $k++) {
                $opt_val = $opt[$k];
                if($opt_val) {
                    $select .= $opt_val.PHP_EOL;
                }
            }
            $select .= '</select>'.PHP_EOL;
            
            if($is_div === 'div') {
                $str .= '<span class="td_sit_sel">'.$select.'</span>'.PHP_EOL;
                $str .= '</div>'.PHP_EOL;
            } else {
                $str .= '<td class="td_sit_sel">'.$select.'</td>'.PHP_EOL;
                $str .= '</tr>'.PHP_EOL;
            }
        }
    }

    return $str;
}

function print_item_options($it_id, $cart_id)
{
    global $g5;

    $sql = " select ct_option, ct_qty, io_price
                from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$cart_id' order by io_type asc, ct_id asc ";
    $result = sql_query($sql);

    $str = '';
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0)
            $str .= '<ul>'.PHP_EOL;
        $price_plus = '';
        if($row['io_price'] >= 0)
            $price_plus = '+';
        $str .= '<li>'.get_text($row['ct_option']).' '.$row['ct_qty'].'개 ('.$price_plus.display_price($row['io_price']).')</li>'.PHP_EOL;
    }

    if($i > 0)
        $str .= '</ul>';

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
function display_banner($position, $skin='')
{
    global $g5;

    if (!$position) $position = '왼쪽';
    if (!$skin) $skin = 'boxbanner.skin.php';

    $skin_path = G5_SHOP_SKIN_PATH.'/'.$skin;
    if(G5_IS_MOBILE)
        $skin_path = G5_MSHOP_SKIN_PATH.'/'.$skin;

    if(file_exists($skin_path)) {
        // 접속기기
        $sql_device = " and ( bn_device = 'both' or bn_device = 'pc' ) ";
        if(G5_IS_MOBILE)
            $sql_device = " and ( bn_device = 'both' or bn_device = 'mobile' ) ";

        // 배너 출력
        $sql = " select * from {$g5['g5_shop_banner_table']} where '".G5_TIME_YMDHIS."' between bn_begin_time and bn_end_time $sql_device and bn_position = '$position' order by bn_order, bn_id desc ";
        $result = sql_query($sql);

        include $skin_path;
    } else {
        echo '<p>'.str_replace(G5_PATH.'/', '', $skin_path).'파일이 존재하지 않습니다.</p>';
    }
}


// 1.00.02
// 파일번호, 이벤트번호, 1라인이미지수, 총라인수, 이미지폭, 이미지높이
// 1.02.01 $ca_id 추가
function display_event($no, $event, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
    global $member, $g5;

    // 상품수
    $items = $list_mod * $list_row;

    // 1.02.00
    // b.it_order 추가
    $sql = " select b.* from {$g5['g5_shop_event_item_table']} a, {$g5['g5_shop_item_table']} b where a.it_id = b.it_id and b.it_use = '1' and a.ev_id = '$event' ";
    if ($ca_id) $sql .= " and ca_id = '$ca_id' ";
    $sql .= " order by b.it_order, a.it_id desc limit $items ";
    $result = sql_query($sql);
    if (!sql_num_rows($result)) {
        return false;
    }

    $file = G5_SHOP_PATH.'/maintype'.$no.'.inc.php';
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
function get_goods($cart_id)
{
    global $g5;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from {$g5['g5_shop_cart_table']} a, {$g5['g5_shop_item_table']} b where a.it_id = b.it_id and a.od_id = '$cart_id' order by ct_id limit 1 ");
    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods['it_id'] = $row['it_id'];
    $goods['full_name']= $goods['name'] = addslashes($row['it_name']);
    // 특수문자제거
    $goods['full_name'] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",  $goods['full_name']);

    // 상품건수
    $row = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_cart_table']} where od_id = '$cart_id' ");
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
    global $g5;

    if (!$msg) $msg = '올바른 방법으로 이용해 주십시오.';

    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
    echo "<script>";
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
    global $g5;

    // 주문서 테이블 Lock 걸고
    sql_query(" LOCK TABLES {$g5['g5_shop_order_table']} READ, {$g5['g5_shop_order_table']} WRITE ", FALSE);
    // 주문서 번호를 만든다.
    $date = date("ymd", time());    // 2002년 3월 7일 일경우 020307
    $sql = " select max(od_id) as max_od_id from {$g5['g5_shop_order_table']} where SUBSTRING(od_id, 1, 6) = '$date' ";
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


// cart id 설정
function set_cart_id($direct)
{
    global $g5, $default, $member;

    if ($direct) {
        $tmp_cart_id = get_session('ss_cart_direct');
        if(!$tmp_cart_id) {
            $tmp_cart_id = get_uniqid();
            set_session('ss_cart_direct', $tmp_cart_id);
        }
    } else {
        // 비회원장바구니 cart id 쿠키설정
        if($default['de_guest_cart_use']) {
            $tmp_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', get_cookie('ck_guest_cart_id'));
            if($tmp_cart_id) {
                set_session('ss_cart_id', $tmp_cart_id);
                //set_cookie('ck_guest_cart_id', $tmp_cart_id, ($default['de_cart_keep_term'] * 86400));
            } else {
                $tmp_cart_id = get_uniqid();
                set_session('ss_cart_id', $tmp_cart_id);
                set_cookie('ck_guest_cart_id', $tmp_cart_id, ($default['de_cart_keep_term'] * 86400));
            }
        } else {
            $tmp_cart_id = get_session('ss_cart_id');
            if(!$tmp_cart_id) {
                $tmp_cart_id = get_uniqid();
                set_session('ss_cart_id', $tmp_cart_id);
            }
        }

        // 보관된 회원장바구니 자료 cart id 변경
        if($member['mb_id'] && $tmp_cart_id) {
            $sql = " update {$g5['g5_shop_cart_table']}
                        set od_id = '$tmp_cart_id'
                        where mb_id = '{$member['mb_id']}'
                          and ct_direct = '0'
                          and ct_status = '쇼핑' ";
            sql_query($sql);
        }
    }
}


// 상품 목록 : 관련 상품 출력
function relation_item($it_id, $width, $height, $rows=3)
{
    global $g5;

    $str = '';

    if(!$it_id)
        return $str;

    $sql = " select b.it_id, b.it_name, b.it_price, b.it_tel_inq from {$g5['g5_shop_item_relation_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id2 = b.it_id ) where a.it_id = '$it_id' order by ir_no asc limit 0, $rows ";
    $result = sql_query($sql);

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0) {
            $str .= '<span class="sound_only">관련 상품 시작</span>';
            $str .= '<ul class="sct_rel_ul">';
        }

        $it_name = get_text($row['it_name']); // 상품명
        $it_price = get_price($row); // 상품가격
        if(!$row['it_tel_inq'])
            $it_price = display_price($it_price);

        $img = get_it_image($row['it_id'], $width, $height);

        $str .= '<li class="sct_rel_li"><a href="'.get_pretty_url('shop', $row['it_id']).'" class="sct_rel_a">'.$img.'</a></li>';
    }

    if($i > 0)
        $str .= '</ul><span class="sound_only">관련 상품 끝</span>';

    return $str;
}


// 상품이미지에 유형 아이콘 출력
function item_icon($it)
{
    global $g5;

    $icon = '<span class="sit_icon">';

    if ($it['it_type1'])
        $icon .= '<span class="shop_icon shop_icon_1">히트</span>';

    if ($it['it_type2'])
        $icon .= '<span class="shop_icon shop_icon_2">추천</span>';

    if ($it['it_type3'])
        $icon .= '<span class="shop_icon shop_icon_3">최신</span>';

    if ($it['it_type4'])
        $icon .= '<span class="shop_icon shop_icon_4">인기</span>';

    if ($it['it_type5'])
        $icon .= '<span class="shop_icon shop_icon_5">할인</span>';


    // 쿠폰상품
    $sql = " select count(*) as cnt
                from {$g5['g5_shop_coupon_table']}
                where cp_start <= '".G5_TIME_YMD."'
                  and cp_end >= '".G5_TIME_YMD."'
                  and (
                        ( cp_method = '0' and cp_target = '{$it['it_id']}' )
                        OR
                        ( cp_method = '1' and ( cp_target IN ( '{$it['ca_id']}', '{$it['ca_id2']}', '{$it['ca_id3']}' ) ) )
                      ) ";
    $row = sql_fetch($sql);
    if($row['cnt'])
        $icon .= '<span class="shop_icon shop_icon_coupon">쿠폰</span>';

    $icon .= '</span>';

    return $icon;
}


// sns 공유하기
function get_sns_share_link($sns, $url, $title, $img)
{
    global $config;

    if(!$sns)
        return '';

    $str = '';
    switch($sns) {
        case 'facebook':
            $str = '<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($url).'&amp;p='.urlencode($title).'" class="share-facebook" target="_blank"><img src="'.$img.'" alt="페이스북에 공유"></a>';
            break;
        case 'twitter':
            $str = '<a href="https://twitter.com/share?url='.urlencode($url).'&amp;text='.urlencode($title).'" class="share-twitter" target="_blank"><img src="'.$img.'" alt="트위터에 공유"></a>';
            break;
        case 'kakaotalk':
            if($config['cf_kakao_js_apikey'])
                $str = '<a href="javascript:kakaolink_send(\''.str_replace('+', ' ', urlencode($title)).'\', \''.urlencode($url).'\');" class="share-kakaotalk"><img src="'.$img.'" alt="카카오톡 링크보내기"></a>';
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


// 쿠폰번호 생성함수
function get_coupon_id()
{
    $len = 16;
    $chars = "ABCDEFGHJKLMNPQRSTUVWXYZ123456789";

    srand((double)microtime()*1000000);

    $i = 0;
    $str = '';

    while ($i < $len) {
        $num = rand() % strlen($chars);
        $tmp = substr($chars, $num, 1);
        $str .= $tmp;
        $i++;
    }

    $str = preg_replace("/([0-9A-Z]{4})([0-9A-Z]{4})([0-9A-Z]{4})([0-9A-Z]{4})/", "\\1-\\2-\\3-\\4", $str);

    return $str;
}


// 주문의 금액, 배송비 과세금액 등의 정보를 가져옴
function get_order_info($od_id)
{
    global $g5;

    // 주문정보
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);

    if(!$od['od_id'])
        return false;

    $info = array();

    // 장바구니 주문금액정보
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(cp_price) as coupon,
                    SUM( IF( ct_notax = 0, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as tax_mny,
                    SUM( IF( ct_notax = 1, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as free_mny
                from {$g5['g5_shop_cart_table']}
                where od_id = '$od_id'
                  and ct_status IN ( '주문', '입금', '준비', '배송', '완료' ) ";
    $sum = sql_fetch($sql);

    $cart_price = $sum['price'];
    $cart_coupon = $sum['coupon'];

    // 배송비
    $send_cost = get_sendcost($od_id);

    $od_coupon = $od_send_coupon = 0;

    if($od['mb_id']) {
        // 주문할인 쿠폰
        $sql = " select a.cp_id, a.cp_type, a.cp_price, a.cp_trunc, a.cp_minimum, a.cp_maximum
                    from {$g5['g5_shop_coupon_table']} a right join {$g5['g5_shop_coupon_log_table']} b on ( a.cp_id = b.cp_id )
                    where b.od_id = '$od_id'
                      and b.mb_id = '{$od['mb_id']}'
                      and a.cp_method = '2' ";
        $cp = sql_fetch($sql);

        $tot_od_price = $cart_price - $cart_coupon;

        if(isset($cp['cp_id']) && $cp['cp_id']) {
            $dc = 0;

            if($cp['cp_minimum'] <= $tot_od_price) {
                if($cp['cp_type']) {
                    $dc = floor(($tot_od_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
                } else {
                    $dc = $cp['cp_price'];
                }

                if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                    $dc = $cp['cp_maximum'];

                if($tot_od_price < $dc)
                    $dc = $tot_od_price;

                $tot_od_price -= $dc;
                $od_coupon = $dc;
            }
        }

        // 배송쿠폰 할인
        $sql = " select a.cp_id, a.cp_type, a.cp_price, a.cp_trunc, a.cp_minimum, a.cp_maximum
                    from {$g5['g5_shop_coupon_table']} a right join {$g5['g5_shop_coupon_log_table']} b on ( a.cp_id = b.cp_id )
                    where b.od_id = '$od_id'
                      and b.mb_id = '{$od['mb_id']}'
                      and a.cp_method = '3' ";
        $cp = sql_fetch($sql);

        if(isset($cp['cp_id']) && $cp['cp_id']) {
            $dc = 0;
            if($cp['cp_minimum'] <= $tot_od_price) {
                if($cp['cp_type']) {
                    $dc = floor(($send_cost * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
                } else {
                    $dc = $cp['cp_price'];
                }

                if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                    $dc = $cp['cp_maximum'];

                if($dc > $send_cost)
                    $dc = $send_cost;

                $od_send_coupon = $dc;
            }
        }
    }

    // 과세, 비과세 금액정보
    $tax_mny = $sum['tax_mny'];
    $free_mny = $sum['free_mny'];

    if($od['od_tax_flag']) {
        $tot_tax_mny = ( $tax_mny + $send_cost + $od['od_send_cost2'] )
                       - ( $od_coupon + $od_send_coupon + $od['od_receipt_point'] );
        if($tot_tax_mny < 0) {
            $free_mny += $tot_tax_mny;
            $tot_tax_mny = 0;
        }
    } else {
        $tot_tax_mny = ( $tax_mny + $free_mny + $send_cost + $od['od_send_cost2'] )
                       - ( $od_coupon + $od_send_coupon + $od['od_receipt_point'] );
        $free_mny = 0;
    }

    $od_tax_mny = round($tot_tax_mny / 1.1);
    $od_vat_mny = $tot_tax_mny - $od_tax_mny;
    $od_free_mny = $free_mny;

    // 장바구니 취소금액 정보
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price
                from {$g5['g5_shop_cart_table']}
                where od_id = '$od_id'
                  and ct_status IN ( '취소', '반품', '품절' ) ";
    $sum = sql_fetch($sql);
    $cancel_price = $sum['price'];

    // 미수금액
    $od_misu = ( $cart_price + $send_cost + $od['od_send_cost2'] )
               - ( $cart_coupon + $od_coupon + $od_send_coupon )
               - ( $od['od_receipt_price'] + $od['od_receipt_point'] - $od['od_refund_price'] );

    // 장바구니상품금액
    $od_cart_price = $cart_price + $cancel_price;

    // 결과처리
    $info['od_cart_price']      = $od_cart_price;
    $info['od_send_cost']       = $send_cost;
    $info['od_coupon']          = $od_coupon;
    $info['od_send_coupon']     = $od_send_coupon;
    $info['od_cart_coupon']     = $cart_coupon;
    $info['od_tax_mny']         = $od_tax_mny;
    $info['od_vat_mny']         = $od_vat_mny;
    $info['od_free_mny']        = $od_free_mny;
    $info['od_cancel_price']    = $cancel_price;
    $info['od_misu']            = $od_misu;

    return $info;
}


// 상품포인트
function get_item_point($it, $io_id='', $trunc=10)
{
    global $g5;

    $it_point = 0;

    if($it['it_point_type'] > 0) {
        $it_price = $it['it_price'];

        if($it['it_point_type'] == 2 && $io_id) {
            $sql = " select io_id, io_price
                        from {$g5['g5_shop_item_option_table']}
                        where it_id = '{$it['it_id']}'
                          and io_id = '$io_id'
                          and io_type = '0'
                          and io_use = '1' ";
            $opt = sql_fetch($sql);

            if($opt['io_id'])
                $it_price += $opt['io_price'];
        }

        $it_point = floor(($it_price * ($it['it_point'] / 100) / $trunc)) * $trunc;
    } else {
        $it_point = $it['it_point'];
    }

    return $it_point;
}


// 배송비 구함
function get_sendcost($cart_id, $selected=1)
{
    global $default, $g5;

    $send_cost = 0;
    $total_price = 0;
    $total_send_cost = 0;
    $diff = 0;

    $sql = " select distinct it_id
                from {$g5['g5_shop_cart_table']}
                where od_id = '$cart_id'
                  and ct_send_cost = '0'
                  and ct_status IN ( '쇼핑', '주문', '입금', '준비', '배송', '완료' )
                  and ct_select = '$selected' ";

    $result = sql_query($sql);
    for($i=0; $sc=sql_fetch_array($result); $i++) {
        // 합계
        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                        SUM(ct_qty) as qty
                    from {$g5['g5_shop_cart_table']}
                    where it_id = '{$sc['it_id']}'
                      and od_id = '$cart_id'
                      and ct_status IN ( '쇼핑', '주문', '입금', '준비', '배송', '완료' )
                      and ct_select = '$selected'";
        $sum = sql_fetch($sql);

        $send_cost = get_item_sendcost($sc['it_id'], $sum['price'], $sum['qty'], $cart_id);

        if($send_cost > 0)
            $total_send_cost += $send_cost;

        if($default['de_send_cost_case'] == '차등' && $send_cost == -1) {
            $total_price += $sum['price'];
            $diff++;
        }
    }

    $send_cost = 0;
    if($default['de_send_cost_case'] == '차등' && $total_price >= 0 && $diff > 0) {
        // 금액별차등 : 여러단계의 배송비 적용 가능
        $send_cost_limit = explode(";", $default['de_send_cost_limit']);
        $send_cost_list  = explode(";", $default['de_send_cost_list']);
        $send_cost = 0;
        for ($k=0; $k<count($send_cost_limit); $k++) {
            // 총판매금액이 배송비 상한가 보다 작다면
            if ($total_price < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
                $send_cost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
                break;
            }
        }
    }

    return ($total_send_cost + $send_cost);
}


// 상품별 배송비
function get_item_sendcost($it_id, $price, $qty, $cart_id)
{
    global $g5, $default;

    $sql = " select it_id, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty
                from {$g5['g5_shop_cart_table']}
                where it_id = '$it_id'
                  and od_id = '$cart_id'
                order by ct_id
                limit 1 ";
    $ct = sql_fetch($sql);
    if(!$ct['it_id'])
        return 0;

    if($ct['it_sc_type'] > 1) {
        if($ct['it_sc_type'] == 2) { // 조건부무료
            if($price >= $ct['it_sc_minimum'])
                $sendcost = 0;
            else
                $sendcost = $ct['it_sc_price'];
        } else if($ct['it_sc_type'] == 3) { // 유료배송
            $sendcost = $ct['it_sc_price'];
        } else { // 수량별 부과
            if(!$ct['it_sc_qty'])
                $ct['it_sc_qty'] = 1;

            $q = ceil((int)$qty / (int)$ct['it_sc_qty']);
            $sendcost = (int)$ct['it_sc_price'] * $q;
        }
    } else if($ct['it_sc_type'] == 1) { // 무료배송
        $sendcost = 0;
    } else {
        $sendcost = -1;
    }

    return $sendcost;
}


// 가격비교 사이트 상품 배송비
function get_item_sendcost2($it_id, $price, $qty)
{
    global $g5, $default;

    $sql = " select it_id, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty
                from {$g5['g5_shop_item_table']}
                where it_id = '$it_id' ";
    $it = sql_fetch($sql);
    if(!$it['it_id'])
        return 0;

    $sendcost = 0;

    // 쇼핑몰 기본설정을 사용할 때
    if($it['it_sc_type'] == 0)
    {
        if($default['de_send_cost_case'] == '차등') {
            // 금액별차등 : 여러단계의 배송비 적용 가능
            $send_cost_limit = explode(";", $default['de_send_cost_limit']);
            $send_cost_list  = explode(";", $default['de_send_cost_list']);

            for ($k=0; $k<count($send_cost_limit); $k++) {
                // 총판매금액이 배송비 상한가 보다 작다면
                if ($price < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
                    $sendcost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
                    break;
                }
            }
        }
    }
    else
    {
        if($it['it_sc_type'] > 1) {
            if($it['it_sc_method'] == 1){  // 배송비 결제 설정이 착불인 경우
                $sendcost = -1;
            } else {    // 배송비 결제 설정이 선불 또는 사용자선택인 경우
                if($it['it_sc_type'] == 2) { // 조건부무료
                    if($price >= $it['it_sc_minimum'])
                        $sendcost = 0;
                    else
                        $sendcost = $it['it_sc_price'];
                } else if($it['it_sc_type'] == 3) { // 유료배송
                    $sendcost = $it['it_sc_price'];
                } else { // 수량별 부과
                    if(!$it['it_sc_qty'])
                        $it['it_sc_qty'] = 1;

                    $q = ceil((int)$qty / (int)$it['it_sc_qty']);
                    $sendcost = (int)$it['it_sc_price'] * $q;
                }
            }
        } else if($it['it_sc_type'] == 1) { // 무료배송
            $sendcost = 0;
        }
    }

    return $sendcost;
}


// 쿠폰 사용체크
function is_used_coupon($mb_id, $cp_id)
{
    global $g5;

    $used = false;

    $sql = " select count(*) as cnt from {$g5['g5_shop_coupon_log_table']} where mb_id = '$mb_id' and cp_id = '$cp_id' ";
    $row = sql_fetch($sql);

    if($row['cnt'])
        $used = true;

    return $used;
}

// 품절상품인지 체크
function is_soldout($it_id, $is_cache=false)
{
    global $g5;

    static $cache = array();

    $it_id = preg_replace('/[^a-z0-9_\-]/i', '', $it_id);
    $key = md5($it_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    // 상품정보
    $it = get_shop_item($it_id, $is_cache);

    if($it['it_soldout'] || $it['it_stock_qty'] <= 0)
        return true;

    $count = 0;
    $soldout = false;

    // 상품에 선택옵션 있으면..
    $sql = " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_type = '0' ";
    $row = sql_fetch($sql);

    if($row['cnt']) {
        $sql = " select io_id, io_type, io_stock_qty
                    from {$g5['g5_shop_item_option_table']}
                    where it_id = '$it_id'
                      and io_type = '0'
                      and io_use = '1' ";
        $result = sql_query($sql);

        for($i=0; $row=sql_fetch_array($result); $i++) {
            // 옵션 재고수량
            $stock_qty = get_option_stock_qty($it_id, $row['io_id'], $row['io_type']);

            if($stock_qty <= 0)
                $count++;
        }

        // 모든 선택옵션 품절이면 상품 품절
        if($i == $count)
            $soldout = true;
    } else {
        // 상품 재고수량
        $stock_qty = get_it_stock_qty($it_id);

        if($stock_qty <= 0)
            $soldout = true;
    }
    
    $cache[$key] = $soldout;

    return $soldout;
}

// 상품후기 작성가능한지 체크
function check_itemuse_write($it_id, $mb_id, $close=true)
{
    global $g5, $default, $is_admin;

    if(!$is_admin && $default['de_item_use_write'])
    {
        $sql = " select count(*) as cnt
                    from {$g5['g5_shop_cart_table']}
                    where it_id = '$it_id'
                      and mb_id = '$mb_id'
                      and ct_status = '완료' ";
        $row = sql_fetch($sql);

        if($row['cnt'] == 0)
        {
            if($close)
                alert_close('사용후기는 주문이 완료된 경우에만 작성하실 수 있습니다.');
            else
                alert('사용후기는 주문하신 상품의 상태가 완료인 경우에만 작성하실 수 있습니다.');
        }
    }
}


// 구매 본인인증 체크
function shop_member_cert_check($id, $type)
{
    global $g5, $member;

    $msg = '';

    switch($type)
    {
        case 'item':
            $it = get_shop_item($id, true);

            $seq = '';
            for($i=0; $i<3; $i++) {
                $ca_id = $it['ca_id'.$seq];

                if(!$ca_id)
                    continue;

                $sql = " select ca_cert_use, ca_adult_use from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
                $row = sql_fetch($sql);

                if (($row['ca_cert_use'] || $row['ca_adult_use']) && strlen($member['mb_dupinfo']) == 64 && $member['mb_certify']) { // 본인 인증 된 계정 중에서 di로 저장 되었을 경우에만
                    goto_url(G5_BBS_URL."/member_cert_refresh.php?url=".urlencode(get_pretty_url($bo_table, $wr_id, $qstr)));
                }

                // 본인확인체크
                if($row['ca_cert_use'] && !$member['mb_certify']) {
                    if($member['mb_id'])
                        $msg = '회원정보 수정에서 본인확인 후 이용해 주십시오.';
                    else
                        $msg = '본인확인된 로그인 회원만 이용할 수 있습니다.';

                    break;
                }

                // 성인인증체크
                if($row['ca_adult_use'] && !$member['mb_adult']) {
                    if($member['mb_id'])
                        $msg = '본인확인으로 성인인증된 회원만 이용할 수 있습니다.\\n회원정보 수정에서 본인확인을 해주십시오.';
                    else
                        $msg = '본인확인으로 성인인증된 회원만 이용할 수 있습니다.';

                    break;
                }

                if($i == 0)
                    $seq = 1;
                $seq++;
            }

            break;
        case 'list':
            $sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '$id' ";
            $ca = sql_fetch($sql);

            if (($ca['ca_cert_use'] || $ca['ca_adult_use']) && strlen($member['mb_dupinfo']) == 64 && $member['mb_certify']) { // 본인 인증 된 계정 중에서 di로 저장 되었을 경우에만
                goto_url(G5_BBS_URL."/member_cert_refresh.php?url=".urlencode(get_pretty_url($bo_table, $wr_id, $qstr)));
            
            }

            // 본인확인체크
            if($ca['ca_cert_use'] && !$member['mb_certify']) {
                if($member['mb_id'])
                    $msg = '회원정보 수정에서 본인확인 후 이용해 주십시오.';
                else
                    $msg = '본인확인된 로그인 회원만 이용할 수 있습니다.';
            }

            // 성인인증체크
            if($ca['ca_adult_use'] && !$member['mb_adult']) {
                if($member['mb_id'])
                    $msg = '본인확인으로 성인인증된 회원만 이용할 수 있습니다.\\n회원정보 수정에서 본인확인을 해주십시오.';
                else
                    $msg = '본인확인으로 성인인증된 회원만 이용할 수 있습니다.';
            }

            break;
        default:
            break;
    }

    return $msg;
}


// 배송조회버튼 생성
function get_delivery_inquiry($company, $invoice, $class='')
{
    if(!$company || !$invoice)
        return '';

    $dlcomp = explode(")", str_replace("(", "", G5_DELIVERY_COMPANY));

    for($i=0; $i<count($dlcomp); $i++) {
        if(strstr($dlcomp[$i], $company)) {
            list($com, $url, $tel) = explode("^", $dlcomp[$i]);
            break;
        }
    }

    $str = '';
    if(isset($com) && $com && isset($url) && $url) {
        $str .= '<a href="'.$url.$invoice.'" target="_blank"';
        if($class)
            $str .= ' class="'.$class.'"';
        $str .='>배송조회</a>';
        if($tel)
            $str .= ' (문의전화: '.$tel.')';
    }

    return $str;
}


// 사용후기의 확인된 건수를 상품테이블에 저장합니다.
function update_use_cnt($it_id)
{
    global $g5;
    $row = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_item_use_table']} where it_id = '{$it_id}' and is_confirm = 1 ");
    return sql_query(" update {$g5['g5_shop_item_table']} set it_use_cnt = '{$row['cnt']}' where it_id = '{$it_id}' ");
}


// 사용후기의 선호도(별) 평균을 상품테이블에 저장합니다.
function update_use_avg($it_id)
{
    global $g5;
    $row = sql_fetch(" select count(*) as cnt, sum(is_score) as total from {$g5['g5_shop_item_use_table']} where it_id = '{$it_id}' and is_confirm = 1 ");
    $average = ($row['total'] && $row['cnt']) ? $row['total'] / $row['cnt'] : 0;
    return sql_query(" update {$g5['g5_shop_item_table']} set it_use_avg = '$average' where it_id = '{$it_id}' ");
}

//오늘본상품 데이터
function get_view_today_items($is_cache=false)
{
    global $g5;
    
    $tv_idx = get_session("ss_tv_idx");

    if( !$tv_idx ){
        return array();
    }

    static $cache = array();

    if( $is_cache && !empty($cache) ){
        return $cache;
    }

    for ($i=1;$i<=$tv_idx;$i++){

        $tv_it_idx = $tv_idx - ($i - 1);
        $tv_it_id = get_session("ss_tv[$tv_it_idx]");

        $rowx = get_shop_item($tv_it_id, true);
        if(!$rowx['it_id'])
            continue;
        
        $key = $rowx['it_id'];

        $cache[$key] = $rowx;
    }

    return $cache;
}

//오늘본상품 갯수 출력
function get_view_today_items_count()
{
    $tv_datas = get_view_today_items(true);

    return count($tv_datas);
}

//장바구니 간소 데이터 가져오기
function get_boxcart_datas($is_cache=false)
{
    global $g5;
    
    $cart_id = get_session("ss_cart_id");

    if( !$cart_id ){
        return array();
    }

    static $cache = array();

    if( $is_cache && !empty($cache) ){
        return $cache;
    }

    $sql  = " select * from {$g5['g5_shop_cart_table']} ";
    $sql .= " where od_id = '".$cart_id."' group by it_id ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $key = $row['it_id'];
        $cache[$key] = $row;
    }

    return $cache;
}

//장바구니 간소 데이터 갯수 출력
function get_boxcart_datas_count()
{
    $cart_datas = get_boxcart_datas(true);

    return count($cart_datas);
}

//위시리스트 데이터 가져오기
function get_wishlist_datas($mb_id, $is_cache=false)
{
    global $g5, $member;

    if( !$mb_id ){
        $mb_id = $member['mb_id'];

        if( !$mb_id ) return array();
    }

    static $cache = array();

    if( $is_cache && isset($cache[$mb_id]) ){
        return $cache[$mb_id];
    }

    $cache[$mb_id] = array();
    $sql  = " select a.it_id, b.it_name from {$g5['g5_shop_wish_table']} a, {$g5['g5_shop_item_table']} b ";
    $sql .= " where a.mb_id = '".$mb_id."' and a.it_id  = b.it_id order by a.wi_id desc ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $key = $row['it_id'];
        $cache[$mb_id][$key] = $row;
    }

    return $cache[$mb_id];
}

//위시리스트 데이터 갯수 출력
function get_wishlist_datas_count($mb_id='')
{
    global $member;

    if( !$mb_id ){
        $mb_id = $member['mb_id'];

        if( !$mb_id ) return 0;
    }

    $wishlist_datas = get_wishlist_datas($mb_id, true);

    return is_array($wishlist_datas) ? count($wishlist_datas) : 0;
}

//각 상품에 대한 위시리스트 담은 갯수 출력
function get_wishlist_count_by_item($it_id='')
{
    global $g5;

    if( !$it_id ) return 0;

    $sql = "select count(a.it_id) as num from {$g5['g5_shop_wish_table']} a, {$g5['g5_shop_item_table']} b where a.it_id  = b.it_id and b.it_id = '$it_id'";

    $row = sql_fetch($sql);

    return (int) $row['num'];
}

//주문데이터 또는 개인결제 주문데이터 가져오기
function get_shop_order_data($od_id, $type='item')
{
    global $g5;
    
    $od_id = preg_replace('/[^0-9a-z_-]/i', '', clean_xss_tags($od_id));

    if( $type == 'personal' ){
        $row = sql_fetch("select * from {$g5['g5_shop_personalpay_table']} where pp_id = $od_id ", false);
    } else {
        $row = sql_fetch("select * from {$g5['g5_shop_order_table']} where od_id = $od_id ", false);
    }

    return $row;
}

function is_use_easypay($payname=''){
    global $default;

    $de_easy_pay_service_array = (isset($default['de_easy_pay_services']) && $default['de_easy_pay_services']) ? explode(',', $default['de_easy_pay_services']) : array();

    if($payname === 'global_nhnkcp' && $de_easy_pay_service_array && ('kcp' !== $default['de_pg_service'])){      // NHN_KCP 외 타PG 사용시
        if( in_array('global_nhnkcp_naverpay', $de_easy_pay_service_array) && ($default['de_card_test'] || (!$default['de_card_test'] && $default['de_kcp_mid'] && $default['de_kcp_site_key']) ) ){
            return true;
        }
    }

    return false;
}

function exists_inicis_shop_order($oid, $pp=array(), $od_time='', $od_ip='')
{

    $od_ip = $od_ip ? $od_ip : $_SERVER['REMOTE_ADDR'];

    //개인결제
    if( $pp ) {
        $hash_data = md5($pp['pp_id'].$pp['pp_price'].$pp['pp_time']);
        if( $hash_data == get_session('ss_personalpay_hash') ){
            // 개인결제번호제거
            set_session('ss_personalpay_id', '');
            set_session('ss_personalpay_hash', '');

            $uid = md5($pp['pp_id'].$pp['pp_time'].$od_ip);
            set_session('ss_personalpay_uid', $uid);
            
            goto_url(G5_SHOP_URL.'/personalpayresult.php?pp_id='.$pp['pp_id'].'&amp;uid='.$uid.'&amp;ini_noti=1');
        } else {
            goto_url(G5_SHOP_URL.'/personalpayresult.php?pp_id='.$pp['pp_id'].'&amp;ini_noti=1');
        }
    } else {    //그렇지 않으면
        if (!$od_time){
            $od_time = G5_TIME_YMDHIS;
        }

        if( $oid == get_session('ss_order_id') ){
            // orderview 에서 사용하기 위해 session에 넣고
            $uid = md5($oid.$od_time.$od_ip);
            set_session('ss_orderview_uid', $uid);
            goto_url(G5_SHOP_URL.'/orderinquiryview.php?od_id='.$oid.'&amp;uid='.$uid.'&amp;ini_noti=1');
        } else {
            goto_url(G5_SHOP_URL.'/orderinquiryview.php?od_id='.$oid.'&amp;ini_noti=1');
        }
    }
    return '';
}

//------------------------------------------------------------------------------
// 주문포인트를 적립한다.
// 설정일이 지난 포인트 부여되지 않은 배송완료된 장바구니 자료에 포인트 부여
// 설정일이 0 이면 주문서 완료 설정 시점에서 포인트를 바로 부여합니다.
//------------------------------------------------------------------------------
function save_order_point($ct_status="완료")
{
    global $g5, $default;

    $beforedays = date("Y-m-d H:i:s", ( time() - (86400 * (int)$default['de_point_days']) ) ); // 86400초는 하루
    $sql = " select * from {$g5['g5_shop_cart_table']} where ct_status = '$ct_status' and ct_point_use = '0' and ct_time <= '$beforedays' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 회원 ID 를 얻는다.
        $od_row = sql_fetch("select od_id, mb_id from {$g5['g5_shop_order_table']} where od_id = '{$row['od_id']}' ");
        if ($od_row['mb_id'] && $row['ct_point'] > 0) { // 회원이면서 포인트가 0보다 크다면
            $po_point = $row['ct_point'] * $row['ct_qty'];
            $po_content = "주문번호 {$od_row['od_id']} ({$row['ct_id']}) 배송완료";
            insert_point($od_row['mb_id'], $po_point, $po_content, "@delivery", $od_row['mb_id'], "{$od_row['od_id']},{$row['ct_id']}");
        }
        sql_query("update {$g5['g5_shop_cart_table']} set ct_point_use = '1' where ct_id = '{$row['ct_id']}' ");
    }
}


// 배송업체 리스트 얻기
function get_delivery_company($company)
{
    $option = '<option value="">없음</option>'.PHP_EOL;
    $option .= '<option value="자체배송" '.get_selected($company, '자체배송').'>자체배송</option>'.PHP_EOL;

    $dlcomp = explode(")", str_replace("(", "", G5_DELIVERY_COMPANY));
    for ($i=0; $i<count($dlcomp); $i++) {
        if (trim($dlcomp[$i])=="") continue;
        list($value, $url, $tel) = explode("^", $dlcomp[$i]);
        $option .= '<option value="'.$value.'" '.get_selected($company, $value).'>'.$value.'</option>'.PHP_EOL;
    }

    return $option;
}

// 사용후기 썸네일 생성
function get_itemuse_thumb($contents, $thumb_width, $thumb_height, $is_create=false, $is_crop=true, $crop_mode='center', $is_sharpen=true, $um_value='80/0.5/3'){
    
    global $config;

    $img = $filename = $alt = "";

    $matches = get_editor_image($contents, false);

    for($i=0; $i<count($matches[1]); $i++)
    {
        // 이미지 path 구함
        $p = parse_url($matches[1][$i]);
        if(strpos($p['path'], '/'.G5_DATA_DIR.'/') != 0)
            $data_path = preg_replace('/^\/.*\/'.G5_DATA_DIR.'/', '/'.G5_DATA_DIR, $p['path']);
        else
            $data_path = $p['path'];

        $srcfile = G5_PATH.$data_path;

        if(preg_match("/\.({$config['cf_image_extension']})$/i", $srcfile) && is_file($srcfile)) {
            $size = @getimagesize($srcfile);
            if(empty($size))
                continue;

            $filename = basename($srcfile);
            $filepath = dirname($srcfile);

            preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $matches[0][$i], $malt);
            $alt = isset($malt[1]) ? get_text($malt[1]) : '';

            break;
        }
    }

    if($filename) {
        $thumb = thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, $is_create, $is_crop, $crop_mode, $is_sharpen, $um_value);

        if($thumb) {
            $src = G5_URL.str_replace($filename, $thumb, $data_path);
            $img = '<img src="'.$src.'" width="'.$thumb_width.'" height="'.$thumb_height.'" alt="'.$alt.'">';
        }
    }

    return $img;
}

// 사용후기에서 후기에 이미지가 있으면 썸네일을 리턴하며 후기에 이미지가 없으면 상품이미지를 리턴합니다.
function get_itemuselist_thumbnail($it_id, $contents, $thumb_width, $thumb_height, $is_create=false, $is_crop=true, $crop_mode='center', $is_sharpen=true, $um_value='80/0.5/3')
{
    global $g5, $config;
    $img = $filename = $alt = "";

    if($contents) {
        $img = get_itemuse_thumb($contents, $thumb_width, $thumb_height);
    }

    if(!$img)
        $img = get_it_image($it_id, $thumb_width, $thumb_height);

    return $img;
}

function shop_is_taxsave($od, $is_view_receipt=false){
	global $default, $is_memeber;

	$od_pay_type = '';

	if( $od['od_settle_case'] == '무통장' ){
		$od_pay_type = 'account';
	} else if ( $od['od_settle_case'] == '계좌이체' ) {
		$od_pay_type = 'transfer';
	} else if ( $od['od_settle_case'] == '가상계좌' ) {
		$od_pay_type = 'vbank';
	}
	
	if( $od_pay_type ) {
		if( $default['de_taxsave_use'] && strstr( $default['de_taxsave_types'], $od_pay_type ) ){
			return 1;
		}
		
		// 아직 현금영수증 받기전 상태일때만
		if( $is_view_receipt && ! $od['od_cash'] && in_array($od['od_settle_case'], array('계좌이체', '가상계좌')) && ! strstr( $default['de_taxsave_types'], $od_pay_type ) ){
			return 2;
		}
	}

	return 0;
}

// 장바구니 금액 체크 $is_price_update 가 true 이면 장바구니 가격 업데이트한다. 
function before_check_cart_price($s_cart_id, $is_ct_select_condition=false, $is_price_update=false, $is_item_cache=false){
    global $g5, $default, $config;

    if( !$s_cart_id ){
        return;
    }

    $select_where_add = '';

    if( $is_ct_select_condition ){
        $select_where_add = " and ct_select = '0' ";
    }

    $sql = " select * from `{$g5['g5_shop_cart_table']}` where od_id = '$s_cart_id' {$select_where_add} ";

    $result = sql_query($sql);
    $check_need_update = false;
    
    for ($i=0; $row=sql_fetch_array($result); $i++){
        if( ! $row['it_id'] ) continue;

        $it_id = $row['it_id'];
        $it = get_shop_item($it_id, $is_item_cache);
        
        $update_querys = array();

        if(!$it['it_id'])
            continue;
        
        if( $it['it_price'] !== $row['ct_price'] ){
            // 장바구니 테이블 상품 가격과 상품 테이블의 상품 가격이 다를경우
            $update_querys['ct_price'] = $it['it_price'];
        }

        if( $row['io_id'] ){
            $io_sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '{$it['it_id']}' and io_id = '{$row['io_id']}' ";
            $io_infos = sql_fetch( $io_sql );

            if( $io_infos['io_type'] ){
                $this_io_type = $io_infos['io_type'];
            }
            if( $io_infos['io_id'] && $io_infos['io_price'] !== $row['io_price'] ){
                // 장바구니 테이블 옵션 가격과 상품 옵션테이블의 옵션 가격이 다를경우
                $update_querys['io_price'] = $io_infos['io_price'];
            }
        }

        // 포인트
        $compare_point = 0;
        if($config['cf_use_point']) {

            // DB 에 io_type 이 1이면 상품추가옵션이며, 0이면 상품선택옵션이다
            if($row['io_type'] == 0) {
                $compare_point = get_item_point($it, $row['io_id']);
            } else {
                $compare_point = $it['it_supply_point'];
            }

            if($compare_point < 0)
                $compare_point = 0;
        }
        
        if((int) $row['ct_point'] !== (int) $compare_point){
            // 장바구니 테이블 적립 포인트와 상품 테이블의 적립 포인트가 다를경우
            $update_querys['ct_point'] = $compare_point;
        }

        if( $update_querys ){
            $check_need_update = true;
        }

        // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있고, $is_price_update 가 true 인 경우 장바구니 금액을 업데이트 합니다. 
        if( $is_price_update && $update_querys ){
            $conditions = array();

            foreach ($update_querys as $column => $value) {
                $conditions[] = "`{$column}` = '{$value}'";
            }

            if( $col_querys = implode(',', $conditions) ) {
                $sql_query = "update `{$g5['g5_shop_cart_table']}` set {$col_querys} where it_id = '{$it['it_id']}' and od_id = '$s_cart_id' and ct_id =  '{$row['ct_id']}' ";
                sql_query($sql_query, false);
            }
        }
    }

    // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있다면
    if( $check_need_update ){
        return false;
    }

    return true;
}

// 장바구니 상품삭제
function cart_item_clean()
{
    global $g5, $default;

    // 장바구니 보관일
    $keep_term = $default['de_cart_keep_term'];
    if(!$keep_term)
        $keep_term = 15; // 기본값 15일

    // ct_select_time이 기준시간 이상 경과된 경우 변경
    if(defined('G5_CART_STOCK_LIMIT'))
        $cart_stock_limit = G5_CART_STOCK_LIMIT;
    else
        $cart_stock_limit = 3;

    $stocktime = 0;
    if($cart_stock_limit > 0) {
        if($cart_stock_limit > $keep_term * 24)
            $cart_stock_limit = $keep_term * 24;

        $stocktime = G5_SERVER_TIME - (3600 * $cart_stock_limit);
        $sql = " update {$g5['g5_shop_cart_table']}
                    set ct_select = '0'
                    where ct_select = '1'
                      and ct_status = '쇼핑'
                      and UNIX_TIMESTAMP(ct_select_time) < '$stocktime' ";
        sql_query($sql);
    }

    // 설정 시간이상 경과된 상품 삭제
    $statustime = G5_SERVER_TIME - (86400 * $keep_term);

    $sql = " delete from {$g5['g5_shop_cart_table']}
                where ct_status = '쇼핑'
                  and UNIX_TIMESTAMP(ct_time) < '$statustime' ";
    sql_query($sql);
}

// 임시주문 데이터로 주문 필드 생성
function make_order_field($data, $exclude)
{
    $field = '';

    foreach($data as $key=>$value) {
        if(!empty($exclude) && in_array($key, $exclude))
            continue;

        if(is_array($value)) {
            foreach($value as $k=>$v) {
                $field .= '<input type="hidden" name="'.$key.'['.$k.']" value="'.$v.'">'.PHP_EOL;
            }
        } else {
            $field .= '<input type="hidden" name="'.$key.'" value="'.$value.'">'.PHP_EOL;
        }
    }

    return $field;
}

// 주문요청기록 로그를 남깁니다.
function add_order_post_log($msg='', $code='error'){
    global $g5, $member;
    
    if( empty($_POST) ) return;

    $post_data = base64_encode(serialize($_POST));
    $od_id = get_session('ss_order_id');

    if( $code === 'delete' ){
        sql_query(" delete from {$g5['g5_shop_post_log_table']} where (oid = '$od_id' and mb_id = '{$member['mb_id']}' and ol_code != 'error') OR ol_datetime < '".date('Y-m-d H:i:s', strtotime('-15 day', G5_SERVER_TIME))."' ", false);
        return;
    }

    if ( $code === 'error' ) {
        $result = sql_query("describe `{$g5['g5_shop_post_log_table']}`");
        while ($row = sql_fetch_array($result)){
            if( $row['Field'] === 'ol_msg' && $row['Type'] === 'varchar(255)' ){
                sql_query("ALTER TABLE `{$g5['g5_shop_post_log_table']}` MODIFY ol_msg TEXT NOT NULL;", false);
                sql_query("ALTER TABLE `{$g5['g5_shop_post_log_table']}` DROP PRIMARY KEY;", false);
                sql_query("ALTER TABLE `{$g5['g5_shop_post_log_table']}` ADD `log_id` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`log_id`);", false);
                break;
            }
        }
    }

    $sql = "insert into `{$g5['g5_shop_post_log_table']}`
            set oid = '$od_id',
            mb_id = '{$member['mb_id']}',
            post_data = '$post_data',
            ol_code = '$code',
            ol_msg = '".addslashes($msg)."',
            ol_datetime = '".G5_TIME_YMDHIS."',
            ol_ip = '{$_SERVER['REMOTE_ADDR']}'";

    if( $result = sql_query($sql, false) ){
        sql_query(" delete from {$g5['g5_shop_post_log_table']} where ol_datetime < '".date('Y-m-d H:i:s', strtotime('-15 day', G5_SERVER_TIME))."' ", false);
    } else {
        if(!sql_query(" DESC {$g5['g5_shop_post_log_table']} ", false)) {
            sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_post_log_table']}` (
                          `log_id` int(11) NOT NULL AUTO_INCREMENT,
                          `oid` bigint(20) unsigned NOT NULL,
                          `mb_id` varchar(255) NOT NULL DEFAULT '',
                          `post_data` text NOT NULL,
                          `ol_code` varchar(255) NOT NULL DEFAULT '',
                          `ol_msg` text NOT NULL,
                          `ol_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `ol_ip` varchar(25) NOT NULL DEFAULT '',
                          PRIMARY KEY (`log_id`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ", false);
        }
    }
}

//이니시스의 삼성페이 또는 L.pay 결제 또는 카카오페이 가 활성화 되어 있는지 체크합니다.
function is_inicis_simple_pay(){
    global $default;

    if ( $default['de_samsung_pay_use'] || $default['de_inicis_lpay_use'] || $default['de_inicis_kakaopay_use'] ){
        return true;
    }

    return false;
}

//이니시스의 취소된 주문인지 또는 삼성페이 또는 L.pay 또는 이니시스 카카오페이 결제인지 확인합니다.
function is_inicis_order_pay($type){
    global $default, $g5;

    if( $default['de_pg_service'] === 'inicis' && get_session('P_TID') ){
        $tid = preg_replace('/[^A-Za-z0-9_\-]/', '', get_session('P_TID'));
        $sql = "select P_TID from `{$g5['g5_shop_inicis_log_table']}` where P_TID = '$tid' and P_STATUS = 'cancel' ";

        $row = sql_fetch($sql);

        if(isset($row['P_TID']) && $row['P_TID']){
            alert("이미 취소된 주문입니다.", G5_SHOP_URL);
        }
    }

    if( in_array($type, array('삼성페이', 'lpay', 'inicis_kakaopay') ) ){
        return true;
    }

    return false;
}

function get_item_images_info($it, $size=array(), $image_width, $image_height){
    
    if( !(is_array($it) && $it) ) return array();
    $images = array();

    for($i=1; $i<=10; $i++) {
        if(!$it['it_img'.$i]) continue;
        $file = G5_DATA_PATH.'/item/'.$it['it_img'.$i];
        if( $is_exists = run_replace('is_exists_item_file', is_file($file), $it, $i) ){
            $thumb = get_it_thumbnail($it['it_img'.$i], $image_width, $image_height);
            $attr = (isset($size[0]) && isset($size[1]) && $size[0] && $size[1]) ? 'width="'.$size[0].'" height="'.$size[1].'" ' : '';
            $imageurl = G5_DATA_URL.'/item/'.$it['it_img'.$i];
            $infos = array(
                'thumb'=>$thumb,
                'imageurl'=>$imageurl,
                'imagehtml'=>'<img src="'.$imageurl.'" '.$attr.' alt="'.get_text($it['it_name']).'" id="largeimage_'.$i.'">',
                );
            $images[$i] = run_replace('get_image_by_item', $infos, $it, $i, $size);
        }
    }
    return $images; 
}

//결제방식 이름을 체크하여 치환 대상인 문자열은 따로 리턴합니다.
function check_pay_name_replace($payname, $od=array(), $is_client=0){

    if( $payname === 'lpay' ){
        return 'L.pay';
    } else if($payname === 'inicis_kakaopay'){
        return '카카오페이(KG이니시스)';
    } else if($payname === '신용카드'){
        if(isset($od['od_bank_account']) && $od['od_bank_account'] === '카카오머니'){
            return $payname.'(카카오페이)';
        }
    } else if($payname === '간편결제'){

        $add_str = $is_client ? '('.$payname.')' : '';

        if( isset($od['od_pg']) && $od['od_pg'] === 'lg' ){
            return 'PAYNOW';
        } else if( isset($od['od_pg']) && $od['od_pg'] === 'inicis' ){
            return 'KPAY';
        } else if( isset($od['od_pg']) && $od['od_pg'] === 'kcp' ){
            if( isset($od['od_other_pay_type']) && $od['od_other_pay_type'] === 'OT16' ){
                return '네이버페이_NHNKCP'.$add_str;
            } else if( isset($od['od_other_pay_type']) && ($od['od_other_pay_type'] === 'OT13' || $od['od_other_pay_type'] === 'NHNKCP_KAKAOMONEY') ){
                return '카카오페이_NHNKCP'.$add_str;
            }

            return 'PAYCO'.$add_str;
        }
    }

    return $payname;
}

// 다운로드한 쿠폰인지
function is_coupon_downloaded($mb_id, $cz_id)
{
    global $g5;

    if(!$mb_id)
        return false;

    $sql = " select count(*) as cnt from {$g5['g5_shop_coupon_table']} where mb_id = '$mb_id' and cz_id = '$cz_id' ";
    $row = sql_fetch($sql);

    return ($row['cnt'] > 0);
}

//==============================================================================
// 쇼핑몰 라이브러리 모음 끝
//==============================================================================;