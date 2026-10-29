<?php
if (PHP_SAPI !== 'cli') exit;
// 실제 PC/모바일 승인 페이지 + 모의 HTTP/주문 상태. 운영 DB/외부 PG에 접근하지 않는다.
// php -n -d extension=iconv -d extension=ctype tests/kcp_rest_hub_test.php [mobile] [personal]
if (extension_loaded('curl')) exit("Use php -n -d extension=iconv -d extension=ctype\n");
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(function ($n, $s) { if (error_reporting() & $n) throw new Exception($s); return true; });
$root = dirname(dirname(__FILE__));
$dir = sys_get_temp_dir().'/g5-kcp-hub-'.uniqid(); mkdir($dir, 0700);
define('_GNUBOARD_', true); define('G5_PATH', $root); define('G5_LIB_PATH', $dir);
define('G5_SHOP_PATH', $root.'/shop'); define('G5_MSHOP_PATH', $root.'/mobile/shop');
$default = array('de_kcp_payment_api'=>'rest');
define('G5_KCP_REST_CERT_PATH', $dir.'/cert.pem');
define('G5_KCP_REST_PRIVATE_KEY_PATH', $dir.'/key.pem');
define('G5_KCP_REST_PRIVATE_KEY_PASSWORD', '');
$key = openssl_pkey_new(array('private_key_bits'=>2048));
$csr = openssl_csr_new(array('commonName'=>'KCP hub test'), $key);
$cert = openssl_csr_sign($csr, null, $key, 1);
openssl_x509_export_to_file($cert, G5_KCP_REST_CERT_PATH);
openssl_pkey_export_to_file($key, G5_KCP_REST_PRIVATE_KEY_PATH);
file_put_contents($dir.'/kcp_rest.lib.php', '<?php require_once '.var_export($root.'/lib/kcp_rest.lib.php', true).';');
file_put_contents($dir.'/shop_order_access.lib.php', '<?php // Isolated order-state fixture');
foreach (array('CURLOPT_POST','CURLOPT_POSTFIELDS','CURLOPT_HTTPHEADER','CURLOPT_RETURNTRANSFER','CURLOPT_CONNECTTIMEOUT','CURLOPT_TIMEOUT','CURLOPT_SSL_VERIFYPEER','CURLOPT_SSL_VERIFYHOST','CURLOPT_SSLVERSION','CURL_SSLVERSION_TLSv1_2','CURLOPT_FOLLOWLOCATION','CURLINFO_HTTP_CODE') as $n=>$name) define($name,$n+1);
$calls = 0; $http = array(); $status = 'pending'; $snapshot = '';
function curl_init($url) { return true; }
function curl_setopt_array($ch,$opts) { $GLOBALS['http'] = json_decode($opts[CURLOPT_POSTFIELDS],true); }
function curl_exec($ch) { $GLOBALS['calls']++; return json_encode(array('res_cd'=>'0000','res_msg'=>'정상','tno'=>'25536002322422','amount'=>'1004','pay_method'=>'PACA','card_name'=>'국민카드','app_time'=>'20261029120000','app_no'=>'12345678')); }
function curl_getinfo($ch,$n) { return 200; }
function curl_close($ch) {}
function get_session($key) { return '202610290001'; }
function shop_order_access_load($id,$pg) { return empty($GLOBALS['pp']) ? array() : array('pp_id'=>$id); }
function shop_order_access_fail() { throw new Exception('access denied'); }
function shop_order_state_begin($id,$key,$amount) { return array('status'=>$GLOBALS['status'],'response_json'=>$GLOBALS['snapshot']); }
function shop_order_state_approved($id,$data) { $GLOBALS['snapshot'] = json_encode($data); $GLOBALS['status']='approved'; }
function shop_order_decode_data($data) { return unserialize(base64_decode($data)); }
function shop_order_kcp_result_fields() { return explode(' ', 'res_cd res_msg tno amount card_name app_time app_no'); }
function alert($msg, $url = '') { throw new Exception($msg); }
function iconv_euckr($value) { return iconv('UTF-8','CP949',$value); }
function shop_order_state_cancel($value) { $GLOBALS['status']=$value; }
$default = array('de_kcp_payment_api'=>'rest','de_card_test'=>1,'de_escrow_use'=>0,'de_admin_company_name'=>'test','de_kcp_mid'=>'');
$order_price=1004; $od_settle_case='신용카드'; $pp_settle_case='신용카드'; $pp=array(); $trace_no='';
if (in_array('personal',$argv,true)) {
    $pp=array('pp_price'=>1004,'pp_id'=>'202610290001');
    unset($od_settle_case); // 실제 개인결제 요청에는 일반 주문 결제수단이 없다.
}
$_SERVER['REQUEST_METHOD']='POST';
$_POST=array('req_tx'=>'pay','tran_cd'=>'00100000','ordr_idxx'=>'202610290001','good_mny'=>'1004','enc_data'=>'fixture','enc_info'=>'fixture','use_pay_method'=>'100000000000');
if ($pp) $_POST['pp_id']=$pp['pp_id'];
$page = in_array('mobile',$argv,true) ? G5_MSHOP_PATH.'/kcp/pp_ax_hub.php' : G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
include $page;
if ($calls !== 1 || $status !== 'approved' || $amount !== '1004' || $http['ordr_mony'] !== '1004' || $http['ordr_no'] !== '202610290001' || iconv('CP949','UTF-8',$card_name) !== '국민카드') throw new Exception('approval integration');
unset($c_PayPlus, $req_tx, $ordr_idxx, $use_pay_method, $cust_ip); $amount='';
include $page;
if ($calls !== 1 || $amount !== '1004' || !isset($c_PayPlus) || !($c_PayPlus instanceof C_PP_CLI_T)) throw new Exception('cached approval and rollback client');
$status = 'approving'; $denied=false;
try { include $page; } catch (Exception $e) { $denied=true; }
if (!$denied || $calls !== 1) throw new Exception('uncertain request repeated');
$status = 'pending'; $_POST['use_pay_method']='010000000000'; $denied=false;
try { include $page; } catch (Exception $e) { $denied=true; }
if (!$denied || $calls !== 1) throw new Exception('payment type tampering');
$cancel_msg='DB 저장 실패'; $escw_yn='';
include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
if ($calls !== 2 || $http['mod_type'] !== 'STSC' || $status !== 'cancelled' || empty($http['kcp_sign_data'])) throw new Exception('cached approval rollback');
$res_cd='V000'; $escw_yn='Y'; $use_pay_method='001000000000';
include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
if ($calls !== 3 || $http['mod_type'] !== 'STE5' || $status !== 'cancelled') throw new Exception('virtual account rollback');
foreach (array('cert.pem','key.pem','kcp_rest.lib.php','shop_order_access.lib.php') as $file) unlink($dir.'/'.$file);
rmdir($dir);
echo 'OK hub approval/cache/uncertainty/tampering '.(in_array('mobile',$argv,true)?'mobile':'desktop').($pp?' personal':' order')."\n";
