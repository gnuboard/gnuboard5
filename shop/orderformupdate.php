<?
include_once('./_common.php');

if(get_magic_quotes_gpc())
{
    $_GET  = array_map("stripslashes", $_GET);
    $_POST = array_map("stripslashes", $_POST);
}
$_GET  = array_map("mysql_real_escape_string", $_GET);
$_POST = array_map("mysql_real_escape_string", $_POST);

// 장바구니가 비어있는가?
if (get_session("ss_direct"))
    $tmp_uq_id = get_session("ss_uq_direct");
else
    $tmp_uq_id = get_session("ss_uq_id");

if (get_cart_count($tmp_uq_id) == 0)// 장바구니에 담기
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");

$error = "";
// 장바구니 상품 재고 검사
// 1.03.07 : and a.it_id = b.it_id : where 조건문에 이 부분 추가
$sql = " select a.it_id,
                a.ct_qty,
                b.it_name
           from {$g4['yc4_cart_table']} a,
                {$g4['yc4_item_table']} b
          where a.uq_id = '$tmp_uq_id'
            and a.it_id = b.it_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 상품에 대한 현재고수량
    $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row['ct_qty'] > $it_stock_qty)
        $error .= "{$row['it_name']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
}

if ($error != "")
{
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    alert($error);
}

$i_amount     = (int)$_POST['od_amount'];
$i_send_cost  = (int)$_POST['od_send_cost'];
$i_temp_point = (int)$_POST['od_temp_point'];


// 주문금액이 상이함
$sql = " select SUM(ct_amount * ct_qty) as od_amount from {$g4['yc4_cart_table']} where uq_id = '$tmp_uq_id' ";
$row = sql_fetch($sql);
if ((int)$row['od_amount'] !== $i_amount) {
    die("Error.");
}

// 배송비가 상이함
$tot_sell_amount = $row['od_amount'];
// 배송비 계산
if ($default['de_send_cost_case'] == "없음") {
    $send_cost = 0;
} else {
    // 배송비 상한 : 여러단계의 배송비 적용 가능
    $send_cost_limit = explode(";", $default['de_send_cost_limit']);
    $send_cost_list  = explode(";", $default['de_send_cost_list']);
    $send_cost = 0;
    for ($k=0; $k<count($send_cost_limit); $k++) {
        // 총판매금액이 배송비 상한가 보다 작다면
        if ($tot_sell_amount < $send_cost_limit[$k]) {
            $send_cost = $send_cost_list[$k];
            break;
        }
    }
}
if ((int)$send_cost !== $i_send_cost) {
    die("Error..");
}

// 결제포인트가 상이함
$tot_amount = $tot_sell_amount + $send_cost;
// 회원이면서 포인트사용이면
$temp_point = 0;
if ($is_member && $config['cf_use_point'])
{
    // 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
    if ($member['mb_point'] >= $default['de_point_settle'])
    {
        $temp_point = $tot_amount * ($default['de_point_per'] / 100); // 포인트 결제 % 적용
        $temp_point = (int)((int)($temp_point / 100) * 100); // 100점 단위

        $member_point = (int)((int)($member['mb_point'] / 100) * 100); // 100점 단위
        if ($temp_point > $member_point)
            $temp_point = $member_point;
    }
}

if (($i_temp_point > (int)$temp_point || $i_temp_point < 0) && $config['cf_use_point'])
    die("Error...");

if ($od_temp_point)
{
    if ($member['mb_point'] < $od_temp_point)
        alert("회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.");
}

$i_amount = $i_amount + $i_send_cost - $i_temp_point;

$same_amount_check = false;
if ($od_settle_case == "무통장")
{
    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_bank    = 0;
}
else if ($od_settle_case == "계좌이체")
{
    include "./kcp/pp_ax_hub.php";

    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;

    $od_receipt_bank    = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $od_settle_case;
    $od_deposit_name    = $od_name;
    $bank_name          = iconv("cp949", "utf8", $bank_name);
    $od_bank_account    = $bank_name;
    $same_amount_check  = true;
    $pg_receipt_amount  = $amount;
}
else if ($od_settle_case == "가상계좌")
{
    include "./kcp/pp_ax_hub.php";

    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;
    $od_receipt_point   = 0;

    $od_receipt_amount  = 0;
    $bankname           = iconv("cp949", "utf8", $bankname);
    $depositor          = iconv("cp949", "utf8", $depositor);
    $od_bank_account    = $bankname.' '.$account.' '.$depositor;
    $od_deposit_name    = $od_name;
}
else if ($od_settle_case == "휴대폰")
{
    include "./kcp/pp_ax_hub.php";

    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;

    $od_receipt_hp      = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $commid.' '.$mobile_no;
    $same_amount_check  = true;
    $pg_receipt_amount  = $amount;
}
else if ($od_settle_case == "신용카드")
{
    include "./kcp/pp_ax_hub.php";

    $od_temp_card       = $i_amount;
    $od_temp_point      = $i_temp_point;

    $od_receipt_card    = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $card_name          = iconv("cp949", "utf8", $card_name);
    $od_bank_account    = $card_name;
    $same_amount_check  = true;
    $pg_receipt_amount  = $amount;
}
else
{
    die("od_settle_case Error!!!");
}

// 주문금액과 결제금액이 일치하는지 체크
if($same_amount_check) {
    if((int)$i_amount !== (int)$pg_receipt_amount) {
        include "./kcp/pp_ax_hub_cancel.php"; // 결제취소처리

        die("Receipt Amount Error");
    }
}

if ($is_member)
    $od_pwd = $member['mb_password'];
else
    $od_pwd = sql_password($_POST['od_pwd']);

// 주문번호를 얻는다.
$od_id = get_session('ss_order_uniqid');

// 주문서에 입력
$sql = " insert {$g4['yc4_order_table']}
            set od_id             = '$od_id',
                uq_id             = '$tmp_uq_id',
                mb_id             = '{$member['mb_id']}',
                od_pwd            = '$od_pwd',
                od_name           = '$od_name',
                od_email          = '$od_email',
                od_tel            = '$od_tel',
                od_hp             = '$od_hp',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '$od_addr1',
                od_addr2          = '$od_addr2',
                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
                od_deposit_name   = '$od_deposit_name',
                od_memo           = '$od_memo',
                od_send_cost      = '$od_send_cost',
                od_temp_bank      = '$od_receipt_bank',
                od_temp_card      = '$od_receipt_card',
                od_temp_hp        = '$od_receipt_hp',
                od_temp_point     = '$od_temp_point',
                od_receipt_bank   = '$od_receipt_bank',
                od_receipt_card   = '$od_receipt_card',
                od_receipt_hp     = '$od_receipt_hp',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_shop_memo      = '',
                od_hope_date      = '$od_hope_date',
                od_time           = '".G4_TIME_YMDHIS."',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '$od_settle_case'
                ";
sql_query($sql);

// 장바구니 쇼핑에서 주문으로
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$sql_card_point = "";
//if ($od_receipt_card > 0 && $default[de_card_point] == false) {
if (($od_receipt_card > 0 || $od_receipt_hp > 0) && $default['de_card_point'] == false) {
    $sql_card_point = " , ct_point = '0' ";
}
$sql = "update {$g4['yc4_cart_table']}
           set ct_status = '주문'
               $sql_card_point
         where uq_id = '$tmp_uq_id' ";
sql_query($sql);

// 회원이면서 포인트를 사용했다면 포인트 테이블에 사용을 추가
if ($is_member && $od_receipt_point) {
    insert_point($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");
}

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . "&nbsp;";


include_once('./ordermail1.inc.php');
include_once('./ordermail2.inc.php');

// SMS BEGIN --------------------------------------------------------
// 쇼핑몰 운영자가 수신자가 됨
$receive_number = preg_replace("/[^0-9]/", "", $default['de_sms_hp']); // 수신자번호
$send_number = preg_replace("/[^0-9]/", "", $od_hp); // 발신자번호

$sms_contents = $default['de_sms_cont2'];
$sms_contents = preg_replace("/{이름}/", $od_name, $sms_contents);
$sms_contents = preg_replace("/{보낸분}/", $od_name, $sms_contents);
$sms_contents = preg_replace("/{받는분}/", $od_b_name, $sms_contents);
$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
$sms_contents = preg_replace("/{주문금액}/", number_format($ttotal_amount), $sms_contents);
$sms_contents = preg_replace("/{회원아이디}/", $member['mb_id'], $sms_contents);
$sms_contents = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_contents);

if ($default['de_sms_use2'] && $receive_number)
{
    include_once(G4_LIB_PATH.'/icode.sms.lib.php');
    $SMS = new SMS; // SMS 연결
    $SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
    $SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
    $SMS->Send();
}
// SMS END   --------------------------------------------------------


// order_confirm 에서 사용하기 위해 tmp에 넣고
set_session('ss_temp_uq_id', $tmp_uq_id);

// ss_uq_id 기존자료 세션에서 제거
if (get_session("ss_direct"))
    set_session("ss_uq_direct", "");
else
    set_session("ss_uq_id", "");

goto_url(G4_SHOP_URL.'./orderinquiryview.php?od_id='.$od_id.'&amp;uq_id='.$tmp_uq_id);
?>
