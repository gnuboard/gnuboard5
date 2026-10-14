<?php
// 명시적인 테스트 모드 확인 + 격리 DB만 사용. 운영 설정 파일을 로드하지 않는다.
if (PHP_SAPI !== 'cli' || ($argv[2] ?? '') !== '--confirm-kcp-test-mode') exit(1);
define('KCP_CASH_TEST_BOOTSTRAP', true);
require __DIR__.'/kcp_cash_test.php';
define('G5_SHOP_PATH', dirname(__DIR__).'/shop');
$default['de_card_test']=1;$default['de_escrow_use']=0;
setup_cash('InnoDB');
$id=date('YmdHis').mt_rand(100,999);
$request=fixture_cash('order',$id);
$default['de_card_test']=1;$default['de_escrow_use']=0;
$approved=array();$cancelled=false;
try {
    $result=invoke_cash($request,function($operation,$data) use (&$approved) {
        $response=kcp_cash_gateway($operation,$data);
        echo 'KCP '.$operation.' result='.(string)($response['res_cd'] ?? 'missing')."\n";
        if (($response['res_cd'] ?? '')==='0000' && !empty($response['cash_no'])) $approved=array('data'=>$data,'response'=>$response);
        return $response;
    });
    if ($result['state']!=='issued') throw new RuntimeException('KCP test issuance not confirmed; state='.$result['state']);
    $row=sql_fetch("SELECT * FROM cash_order WHERE od_id='$id'");
    if ((int)$row['od_cash']!==1 || $row['od_cash_no']!==$approved['response']['cash_no']) throw new RuntimeException('local receipt mismatch');
    echo "PASS KCP test issuance and isolated DB persistence\n";
} finally {
    if ($approved) {
        $data=$approved['data'];$data['cash_no']=$approved['response']['cash_no'];
        $response=kcp_cash_gateway('cancel',$data);
        $cancelled=($response['res_cd'] ?? '')==='0000';
        echo 'KCP cleanup result='.(string)($response['res_cd'] ?? 'missing')."\n";
        if (!$cancelled) {
            file_put_contents(dirname($argv[1]).'/kcp-test-cleanup-required.json',json_encode($approved));
            throw new RuntimeException('KCP TEST cleanup required; secure record retained');
        }
        echo "PASS KCP test transaction fully cancelled\n";
    }
}
if (!$cancelled) exit(1);
