<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

$iv = get_inicis_iniapi_iv();
if (strlen($iv) !== 16){
    alert('쇼핑몰설정 < KG이니시스 INIAPI IV 값을 16자리로 설정 후 이용해 주세요.');
}

/*
 *
 * 현금결제(실시간 은행계좌이체, 무통장입금)에 대한 현금결제 영수증 발행 요청한다.
 *
 *
 * http://www.inicis.com
 * http://support.inicis.com
 */

$companynumber = isset($_REQUEST['companynumber']) ? clean_xss_tags($_REQUEST['companynumber'], 1, 1) : '';
$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';
$tx    = isset($_REQUEST['tx']) ? clean_xss_tags($_REQUEST['tx'], 1, 1) : '';

if($tx == 'personalpay') {
    $od = sql_fetch(" select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">개인결제 내역이 존재하지 않습니다.</p>');

    // IDOR 방지: 본인 개인결제거나 정당한 세션 uid 보유 시에만 허용
    if (function_exists('is_shop_order_owner') && !is_shop_order_owner($od, 'personalpay')) {
        alert('해당 개인결제 정보에 접근 권한이 없습니다.', G5_SHOP_URL);
    }

    if($od['pp_cash'] == 1)
        alert('이미 등록된 현금영수증 입니다.');

    $buyername = $od['pp_name'];
    $goodname  = $od['pp_name'].'님 개인결제';
    $amt_tot   = (int)$od['pp_receipt_price'];
    $amt_sup   = (int)round(($amt_tot * 10) / 11);
    $amt_svc   = 0;
    $amt_tax   = (int)($amt_tot - $amt_sup);
} else {
    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">주문서가 존재하지 않습니다.</p>');

    // IDOR 방지: 본인 주문이거나 정당한 세션 uid 보유 시에만 허용
    if (function_exists('is_shop_order_owner') && !is_shop_order_owner($od, 'order')) {
        alert('해당 주문 정보에 접근 권한이 없습니다.', G5_SHOP_URL);
    }

    if($od['od_cash'] == 1)
        alert('이미 등록된 현금영수증 입니다.');

    $buyername = $od['od_name'];
    $goods     = get_goods($od['od_id']);
    $goodname  = $goods['full_name'];
    $amt_tot   = (int)$od['od_tax_mny'] + (int)$od['od_vat_mny'] + (int)$od['od_free_mny'];
    $amt_sup   = (int)$od['od_tax_mny'] + (int)$od['od_free_mny'];
    $amt_tax   = (int)$od['od_vat_mny'];
    $amt_svc   = 0;
}


$reg_num  = $id_info;
$useopt   = $tr_code;
$currency = 'WON';

//step1. 요청을 위한 파라미터 설정
// 가맹점관리자 > 상점정보 > 계약정보 > 부가정보 > INIAPI key 생성조회, IV 도 조회 가능
$key = get_inicis_iniapi_key();
$iv = get_inicis_iniapi_iv();
$type          = "Issue";// 고정
$paymethod     = "Receipt";// 고정
$timestamp     = date("YmdHis");
$clientIp      = $_SERVER['SERVER_ADDR'];// 가맹점 요청 서버IP (추후 거래 확인 등에 사용됨)	
$mid           = $default['de_inicis_mid'];
$goodName      = $goodname;                     // 상품명
$crPrice       = $amt_tot;// 총 현금결제 금액
$supPrice      = $amt_sup;// 공급가액
$tax           = $amt_tax;// 부가세
$srcvPrice     = $amt_svc;// 봉사료
$buyerName     = $buyername;// 구매자 성명
$buyerEmail    = $buyeremail;// 구매자 이메일 주소
$buyerTel      = $buyertel;// 구매자 전화번호
$useOpt        = $useopt;// 현금영수증 발행용도 ("1" - 소비자 소득공제용, "2" - 사업자 지출증빙용)
$regNum        = $reg_num;// 현금결제자 주민등록번호

// AES 암호화 (regNum)
if (function_exists('openssl_encrypt')) {
    $enregNum = base64_encode(openssl_encrypt($regNum, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
} else if (function_exists('mcrypt_encrypt')) {
    $padSize = 16 - (strlen($regNum) % 16);
    $value = $regNum.str_repeat(chr($padSize), $padSize);
    $enregNum = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_CBC, $iv));
} else {
    alert('openssl_encrypt 함수가 없어서 실행할수 없습니다.');
}

// SHA512 Hash 암호화
// INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid + crPrice + supPrice + srcvPrice + enregNum
$hashData = hash("sha512",  (string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$crPrice.(string)$supPrice.(string)$srcvPrice.(string)$enregNum);

//step2. key=value 로 post 요청

$data = array(
    'type' => $type,
    'paymethod' => $paymethod,
    'timestamp' => $timestamp,
    'clientIp' => $clientIp,
    'mid' => $mid,
    'goodName' => $goodName,
    'crPrice' => $crPrice,
    'supPrice' => $supPrice,
    'tax' => $tax,
    'srcvPrice' => $srcvPrice,
    'buyerName' => $buyerName,
    'buyerEmail' => $buyerEmail,
    'buyerTel' => $buyerTel,
    'regNum' => $enregNum,
    'useOpt' => $useOpt,
    'compayNumber' => $companynumber,
    'hashData'=> $hashData
);

$url = "https://iniapi.inicis.com/api/v1/receipt";

// 인증서·호스트명을 검증해 요청합니다. 전송 실패·HTTP 오류·JSON 오류면 주문 상태를 바꾸지 않습니다.
$response = g5_tls_http_post($url, http_build_query($data), array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
$ini_result = ($response !== false) ? json_decode($response, true) : null;
if (!is_array($ini_result) || !isset($ini_result['resultCode']) || !is_scalar($ini_result['resultCode'])) {
    alert('KG이니시스 현금영수증 서버와 통신하지 못했습니다. 잠시 후 다시 시도해 주십시오.');
}

//step3. 요청 결과
$result_code = (string) $ini_result['resultCode'];
$result_msg  = (isset($ini_result['resultMsg']) && is_scalar($ini_result['resultMsg'])) ? cut_str(strip_tags((string) $ini_result['resultMsg']), 200, '') : '';
$cash_tid = $cash_no = $cash_date = $cash_time = $cash_use_opt = '';

if ($result_code === '00') {
    // 응답 필드 형식을 확인합니다. 하나라도 어긋나면 발급 정보를 저장하지 않습니다.
    $cash_fields = array(
        'tid'        => '/\A[A-Za-z0-9]{1,64}\z/',
        'authNo'     => '/\A[A-Za-z0-9]{1,20}\z/',
        'authDate'   => '/\A[0-9]{8}\z/',
        'authTime'   => '/\A[0-9]{6}\z/',
        'authUseOpt' => '/\A[A-Za-z0-9]{0,2}\z/',
    );
    $cash_values = array();
    foreach ($cash_fields as $field => $pattern) {
        $value = (isset($ini_result[$field]) && is_scalar($ini_result[$field])) ? (string) $ini_result[$field] : '';
        if (!preg_match($pattern, $value)) {
            alert('KG이니시스 현금영수증 응답 형식이 올바르지 않아 발급 정보를 저장하지 않았습니다. 관리자에게 문의해 주십시오.');
        }
        $cash_values[$field] = $value;
    }
    if (!checkdate((int) substr($cash_values['authDate'], 4, 2), (int) substr($cash_values['authDate'], 6, 2), (int) substr($cash_values['authDate'], 0, 4))
        || (int) substr($cash_values['authTime'], 0, 2) > 23 || (int) substr($cash_values['authTime'], 2, 2) > 59 || (int) substr($cash_values['authTime'], 4, 2) > 59) {
        alert('KG이니시스 현금영수증 응답 형식이 올바르지 않아 발급 정보를 저장하지 않았습니다. 관리자에게 문의해 주십시오.');
    }
    // 응답에 금액·MID가 있으면 요청 값과 대조합니다.
    if ((isset($ini_result['crPrice']) && (string) $ini_result['crPrice'] !== (string) $crPrice)
        || (isset($ini_result['mid']) && (string) $ini_result['mid'] !== (string) $mid)) {
        alert('KG이니시스 현금영수증 응답의 금액 또는 상점 정보가 요청과 달라 발급 정보를 저장하지 않았습니다. 관리자에게 문의해 주십시오.');
    }

    // DB 반영
    $cash_tid     = $cash_values['tid'];
    $cash_no      = $cash_values['authNo'];       // 현금영수증 승인번호
    $cash_date    = $cash_values['authDate'];
    $cash_time    = $cash_values['authTime'];
    $cash_use_opt = $cash_values['authUseOpt'];

    $cash = array();
    $cash['TID']       = $cash_tid;
    $cash['ApplNum']   = $cash_no;
    $cash['ApplDate']  = $cash_date;
    $cash['ApplTime']  = $cash_time;
    $cash['CSHR_Type'] = $cash_use_opt;
    $cash_info = sql_real_escape_string(serialize($cash));
    $sql_cash_no = sql_real_escape_string($cash_no);

    if($tx == 'personalpay') {
        $sql = " update {$g5['g5_shop_personalpay_table']}
                    set pp_cash = '1',
                        pp_cash_no = '$sql_cash_no',
                        pp_cash_info = '$cash_info'
                  where pp_id = '$od_id' ";
    } else {
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_cash = '1',
                        od_cash_no = '$sql_cash_no',
                        od_cash_info = '$cash_info'
                  where od_id = '$od_id' ";
    }

    $result = sql_query($sql, false);
    if (!$result) {
        // 발급은 되었으나 주문에 기록하지 못했습니다. 성공 화면을 보여주지 않고 대조에 필요한 거래번호를 안내합니다.
        alert('현금영수증은 발급되었으나 주문 정보에 저장하지 못했습니다. 관리자에게 문의해 주십시오.\n거래번호: '.$cash_tid);
    }
}

$cash_appl_time = ($cash_date !== '') ? preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $cash_date.$cash_time) : '';

$g5['title'] = '현금영수증 발급';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
function showreceipt() // 현금 영수증 출력
{
    var showreceiptUrl = "https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/Cash_mCmReceipt.jsp?noTid=" + encodeURIComponent(<?php echo get_js_safe_string($cash_tid); ?>) + "&clpaymethod=22";
    window.open(showreceiptUrl,"showreceipt","width=380,height=540, scrollbars=no,resizable=no");
}
</script>

<div id="lg_req_tx" class="new_win">
    <h1 id="win_title">현금영수증 - KG이니시스</h1>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo get_text($result_code); ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo get_text($result_msg); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo get_text($cash_tid); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo get_text($cash_no); ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo get_text($cash_appl_time); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:showreceipt();">영수증 확인</button>
                <p>영수증 확인은 실 등록의 경우에만 가능합니다.</p>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        </tbody>
        </table>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');