<?php
if (!defined('_GNUBOARD_')) exit;

class naverpay_register
{
    public $options;
    public $keys;
    public $send_cost;
    public $total_price;

    function __construct($options, $send_cost)
    {
        $this->options = $options;
        $this->send_cost = $send_cost;
    }

    function get_sendcost()
    {
        global $g5, $default;

        $options = $this->options;
        $send_cost = $this->send_cost;
        $keys = $this->keys;

        $data = array();

        if($send_cost == 1)
            return array('type' => 'ONDELIVERY', 'cost' => 0);

        $cost = 0;
        $cnt  = 0;
        $diff = 0;
        $total_price = 0;
        $diff_cost = 0;

        foreach($keys as $it_id) {
            $it = get_shop_item($it_id, true);
            if(!$it['it_id'])
                continue;

            if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1) { // 착불
                $cnt++;
                continue;
            }

            $qty = 0;
            $price = 0;
            $opts = $options[$it_id];
            $uprice = get_price($it);

            foreach($opts as $opt) {
                if($opt['type'])
                    $price += ((int)$opt['price'] * (int)$opt['qty']);
                else
                    $price += (((int)$uprice + (int)$opt['price']) * (int)$opt['qty']);

                $qty += $opt['qty'];
            }

            if($it['it_sc_type'] > 1) {
                if($it['it_sc_type'] == 2) { // 조건부무료
                    if($price >= $it['it_sc_minimum'])
                        $cost += 0;
                    else
                        $cost += $it['it_sc_price'];
                } else if($it['it_sc_type'] == 3) { // 유료배송
                    $cost += $it['it_sc_price'];
                } else { // 수량별 부과
                    if(!$it['it_sc_qty'])
                        $it['it_sc_qty'] = 1;

                    $q = ceil((int)$qty / (int)$it['it_sc_qty']);
                    $cost += (int)$it['it_sc_price'] * $q;
                }
            } else if($it['it_sc_type'] == 1) { // 무료배송
                $cost += 0;
            } else {
                if($default['de_send_cost_case'] == '차등') {
                    $total_price += $price;
                    $diff++;
                } else {
                    $cost += 0;
                }
            }
        }

        if($default['de_send_cost_case'] == '차등' && $total_price >= 0 && $diff > 0) {
            // 금액별차등 : 여러단계의 배송비 적용 가능
            $send_cost_limit = explode(";", $default['de_send_cost_limit']);
            $send_cost_list  = explode(";", $default['de_send_cost_list']);

            for ($k=0; $k<count($send_cost_limit); $k++) {
                // 총판매금액이 배송비 상한가 보다 작다면
                if ($total_price < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
                    $diff_cost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
                    break;
                }
            }
        }

        $cost += $diff_cost;

        // 모두 착불상품이면
        if(count($keys) == $cnt && $cnt > 0)
            return array('type' => 'ONDELIVERY', 'cost' => 0);

        if($cost > 0)
            $data = array('type' => 'PAYED', 'cost' => $cost);
        else
            $data = array('type' => 'FREE', 'cost' => 0);

        return $data;
    }

    function query()
    {
        global $g5, $default;

        $keys = array();
        $opts = array();

        $item     = '';
        $query    = '';
        $total    = 0;
        $shipping = '';

        $keys = array_unique(array_keys($this->options));
        $this->keys = $keys;

        foreach($keys as $it_id) {
            $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' and it_use = '1' and it_soldout = '0' and it_tel_inq = '0' ";
            $it = sql_fetch($sql);
            if(!$it['it_id'])
                continue;

            $opts = $this->options[$it_id];

            if(empty($opts) || !is_array($opts))
                continue;

            $it_name = $it['it_name'];
            $uprice  = get_price($it);
            $tprice  = 0;

            foreach($opts as $opt) {
                if($opt['type'])
                    $tprice = ((int)$opt['price'] * (int)$opt['qty']);
                else
                    $tprice = (((int)$uprice + (int)$opt['price']) * (int)$opt['qty']);

                $item .= '&ITEM_ID='.urlencode($it_id);
                if($it['ec_mall_pid'])
                    $item .= '&EC_MALL_PID='.urlencode($it['ec_mall_pid']);
                $item .= '&ITEM_NAME='.urlencode($it_name);
                $item .= '&ITEM_COUNT='.$opt['qty'];
                $item .= '&ITEM_OPTION='.urlencode($opt['option']);
                $item .= '&ITEM_TPRICE='.$tprice;
                $item .= '&ITEM_UPRICE='.$uprice;

                $total += $tprice;
            }

        }

        $sendcost = $this->get_sendcost();

        if($sendcost['cost'] > 0)
            $total += $sendcost['cost'];

        $this->total_price = $total;

        $shipping .= '&SHIPPING_TYPE='.$sendcost['type'];
        $shipping .= '&SHIPPING_PRICE='.$sendcost['cost'];
        if(defined('SHIPPING_ADDITIONAL_PRICE') && SHIPPING_ADDITIONAL_PRICE)
            $shipping .= '&SHIPPING_ADDITIONAL_PRICE='.urlencode(SHIPPING_ADDITIONAL_PRICE);

        if($item) {
            $na_co_val = isset($_COOKIE['NA_CO']) ? urlencode($_COOKIE['NA_CO']) : '';
            $query .= 'SHOP_ID='.urlencode($default['de_naverpay_mid']);
            $query .= '&CERTI_KEY='.urlencode($default['de_naverpay_cert_key']);
            $query .= $shipping;
            $query .= '&BACK_URL='.urlencode(NAVERPAY_BACK_URL);
            $query .= '&NAVER_INFLOW_CODE='.$na_co_val;
            $query .= $item;
            $query .= '&TOTAL_PRICE='.$total;
        }

        return $query;
    }
}

function get_naverpay_item_image_url($it_id)
{
    global $g5;

    $row = get_shop_item($it_id, true);

    if(!$row['it_id'])
        return '';

    $url = '';

    for($i=1;$i<=10; $i++) {
        $file = G5_DATA_PATH.'/item/'.$row['it_img'.$i];
        if(is_file($file) && $row['it_img'.$i]) {
            $size = @getimagesize($file);
            if($size[2] < 1 || $size[2] > 3)
                continue;

            $url = str_replace(G5_PATH, G5_URL, $file);

            if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ){
                $url = preg_replace('#^https:#', '', $url);
                
                $port_str = ':'.$_SERVER['SERVER_PORT'];
                
                if( strpos($url, $port_str) !== false ){
                    $url = str_replace($port_str, '', $url);
                }
            }
            
            //TLS(SSL/HTTPS) 프로토콜 사용 시 네이버페이/네이버 쇼핑 서버가 해당 경로로 접근하여 데이터를 취득할 수 없으므로, 반드시 http 를 사용해야 함
            $url = (preg_match('#^http:#', $url) ? '' : 'http:').$url;

            break;
        }
    }

    return $url;
}

function get_naverpay_item_stock($it_id)
{
    global $g5;

    $it = get_shop_item($it_id, true);
    if(!$it['it_id'] || !$it['it_use'] || $it['it_soldout'])
        return 0;

    // 옵션체크
    $sql = " select count(io_no) as cnt, sum(io_stock_qty) as qty from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_type = '0' and io_use = '1' ";
    $row = sql_fetch($sql);

    if($row['cnt'] > 0)
        return $row['qty'];
    else
        return $it['it_stock_qty'];
}

function get_naverpay_item_option($it_id, $subject)
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

    $option = '';

    if($subj_count > 1) {
        $options = array();

        // 옵션항목 배열에 저장
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $osl_id = explode(chr(30), $row['io_id']);

            for($k=0; $k<$subj_count; $k++) {
                if(!is_array($options[$k]))
                    $options[$k] = array();

                if($osl_id[$k] && !in_array($osl_id[$k], $options[$k]))
                    $options[$k][] = $osl_id[$k];
            }
        }

        // 옵션선택목록 만들기
        for($i=0; $i<$subj_count; $i++) {
            $opt = $options[$i];
            $osl_count = count($opt);
            if($osl_count) {
                $option .= '<option name="'.get_text($subj[$i]).'">'.PHP_EOL;
                for($k=0; $k<$osl_count; $k++) {
                    $osl_val = $opt[$k];
                    if(strlen($osl_val)) {
                        $option .= '<select><![CDATA['.$osl_val.']]></select>'.PHP_EOL;
                    }
                }
                $option .= '</option>'.PHP_EOL;
            }
        }
    } else {
        $option .= '<option name="'.get_text($subj[0]).'">'.PHP_EOL;
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $option .= '<select><![CDATA['.$row['io_id'].']]></select>'.PHP_EOL;
        }
        $option .= '</option>'.PHP_EOL;
    }

    return '<options>'.$option.'</options>';
}

function get_naverpay_return_info($mb_id)
{
    global $default;

    $data = '';
    $address1 = trim($default['de_admin_company_addr']);
    $address2 = ' ';

    $data .= '<returnInfo>';
    $data .= '<zipcode><![CDATA['.$default['de_admin_company_zip'].']]></zipcode>';
    $data .= '<address1><![CDATA['.$address1.']]></address1>';
    $data .= '<address2><![CDATA['.$address2.']]></address2>';
    $data .= '<sellername><![CDATA['.$default['de_admin_company_name'].']]></sellername>';
    $data .= '<contact1><![CDATA['.$default['de_admin_company_tel'].']]></contact1>';
    $data .= '</returnInfo>';

    return $data;
}

function return_error2json($str, $fld='error')
{
    $data = array();
    $data[$fld] = trim($str);

    die(json_encode($data));
}