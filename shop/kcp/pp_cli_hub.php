<?php
include_once './_common.php';
require_once G5_LIB_PATH.'/kcp_cash.lib.php';

try {
    $request = kcp_cash_request($_POST, $_SERVER['REQUEST_METHOD']);
    $result = kcp_cash_process($request, 'kcp_cash_gateway');
} catch (KcpCashRequestException $e) {
    alert($e->getMessage());
    exit;
} catch (Exception $e) {
    error_log('KCP cash request internal failure');
    alert('현금영수증 처리 상태를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
    exit;
} catch (Throwable $e) {
    error_log('KCP cash request internal failure');
    alert('현금영수증 처리 상태를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
    exit;
}

// 결과값을 클라이언트 POST로 왕복시키지 않는다.
set_session('ss_kcp_cash_result', array('result'=>$result, 'expires'=>time()+600));
goto_url(G5_SHOP_URL.'/kcp/pp_cli_result.php');
