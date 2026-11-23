<?php
include_once('./_common.php');

require_once(G5_SHOP_PATH.'/settle_lg.inc.php');

require_once(G5_LIB_PATH.'/shop_cash.lib.php');

// KVE-2026-3247: POST·출처·주문에 묶인 일회용 토큰·식별번호 형식·발급 조건($_POST만 사용)을 확인하고,
// PG 호출 전에 주문을 조건부로 선점(정확히 1행)한 뒤 발급한다. 발급되지 않았으면 안내 후 종료한다.
$cash_result = shop_cash_handle('lg', 'shop_cash_gateway_lg');
$cash_view   = $cash_result['display'];
$LGD_OID     = $cash_view['oid'];
$LGD_PAYTYPE = $cash_view['pay_type'];
$od_casseqno = $cash_view['casseqno'];

$g5['title'] = '';
include_once(G5_PATH.'/head.sub.php');

if($default['de_card_test']) {
    echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_TEST_JS.'"></script>'.PHP_EOL;
} else {
    echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_REAL_JS.'"></script>'.PHP_EOL;
}

switch($LGD_PAYTYPE) {
    case 'SC0030':
        $trade_type = 'BANK';
        break;
    case 'SC0040':
        $trade_type = 'CAS';
        break;
    default:
        $trade_type = 'CR';
        break;
}
?>

<div id="lg_req_tx" class="new_win">
    <h1 id="win_title">현금영수증 - 토스페이먼츠 eCredit</h1>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo get_text($cash_view['code']); ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo get_text($cash_view['msg']); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo get_text($cash_view['tid']); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo get_text($cash_view['cash_no']); ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", get_text($cash_view['respdate'])); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:showCashReceipts(<?php echo shop_cash_html(get_js_safe_string($LGD_MID)); ?>,<?php echo shop_cash_html(get_js_safe_string($LGD_OID)); ?>,<?php echo shop_cash_html(get_js_safe_string($od_casseqno)); ?>,<?php echo shop_cash_html(get_js_safe_string($trade_type)); ?>,<?php echo shop_cash_html(get_js_safe_string($CST_PLATFORM)); ?>);">영수증 확인</button>
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