<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_kcp.inc.php');

auth_check($auth[$sub_menu], "w");

$tax_mny = preg_replace('/[^0-9]/', '', $_POST['mod_tax_mny']);
$free_mny = preg_replace('/[^0-9]/', '', $_POST['mod_free_mny']);

if(!$tax_mny && !$free_mny)
    alert('과세 취소금액 또는 비과세 취소금액을 입력해 주십시오.');

if(!trim($mod_memo))
    alert('요청사유를 입력해 주십시오.');

// 주문정보
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

if(!$od['od_id'])
    alert_close('주문정보가 존재하지 않습니다.');

if($od['od_settle_case'] == '계좌이체' && substr($od['od_receipt_time'], 0, 10) >= G5_TIME_YMD)
    alert_close('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

// 금액비교
// 과세, 비과세 취소가능 금액계산
$sql = " select SUM( IF( ct_notax = 0, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as tax_mny,
                SUM( IF( ct_notax = 1, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as free_mny
            from {$g5['g5_shop_cart_table']}
            where od_id = '$od_id'
              and ct_status IN ( '취소', '반품', '품절' ) ";
$sum = sql_fetch($sql);

if($tax_mny && $tax_mny > $sum['tax_mny'])
    alert('과세 취소금액을 '.number_format($sum['tax_mny']).'원 이하로 입력해 주십시오.');

if($free_mny && $free_mny > $sum['free_mny'])
    alert('비과세 취소금액을 '.number_format($sum['free_mny']).'원 이하로 입력해 주십시오.');

// locale ko_KR.euc-kr 로 설정
setlocale(LC_CTYPE, 'ko_KR.euc-kr');

// 부분취소 실행
$g_conf_site_cd   = $default['de_kcp_mid'];
$g_conf_site_key  = $default['de_kcp_site_key'];
$g_conf_home_dir  = G5_SHOP_PATH.'/kcp';
$g_conf_key_dir   = '';
$g_conf_log_dir   = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    $g_conf_key_dir   = G5_SHOP_PATH.'/kcp/bin/pub.key';
    $g_conf_log_dir   = G5_SHOP_PATH.'/kcp/log';
}

if (preg_match("/^T000/", $g_conf_site_cd) || $default['de_card_test']) {
    $g_conf_gw_url  = "testpaygw.kcp.co.kr";
}
else {
    $g_conf_gw_url  = "paygw.kcp.co.kr";
    if (!preg_match("/^SR/", $g_conf_site_cd)) {
        alert("SR 로 시작하지 않는 KCP SITE CODE 는 지원하지 않습니다.");
    }
}

include G5_SHOP_PATH.'/kcp/pp_cli_hub_lib.php';

$tno            = $od['od_tno'];
$req_tx         = 'mod';
$mod_desc       = $cancel_memo;
$cust_ip        = getenv('REMOTE_ADDR');
$rem_mny        = (int)$od['od_receipt_price'] - (int)$od['od_refund_price'];;
$mod_mny        = (int)$tax_mny;
$mod_free_mny   = (int)$free_mny;
$mod_type       = 'RN07';
if($od['od_settle_case'] == '계좌이체')
    $mod_type   = 'STPA';

if($od['od_tax_flag']) {
    $mod_mny = $tax_mny + $free_mny;
}

$c_PayPlus  = new C_PAYPLUS_CLI;
$c_PayPlus->mf_clear();

if ( $req_tx == "mod" )
{
    $tran_cd = "00200000";

    $c_PayPlus->mf_set_modx_data( "tno"          , $tno                  );  // KCP 원거래 거래번호
    $c_PayPlus->mf_set_modx_data( "mod_type"     , $mod_type			 );  // 원거래 변경 요청 종류
    $c_PayPlus->mf_set_modx_data( "mod_ip"       , $cust_ip				 );  // 변경 요청자 IP
    $c_PayPlus->mf_set_modx_data( "mod_desc"     , $mod_desc			 );  // 변경 사유
    $c_PayPlus->mf_set_modx_data( "rem_mny"      , strval($rem_mny)      );  // 취소 가능 잔액
    $c_PayPlus->mf_set_modx_data( "mod_mny"      , strval($mod_mny)      );  // 취소 요청 금액

    if($od['od_tax_flag'])
    {
        $mod_tax_mny = round((int)$tax_mny / 1.1);
        $mod_vat_mny = (int)$tax_mny - $mod_tax_mny;

        $c_PayPlus->mf_set_modx_data( "tax_flag"     , "TG03"				 );  // 복합과세 구분
        $c_PayPlus->mf_set_modx_data( "mod_tax_mny"  , strval($mod_tax_mny)  );	 // 공급가 부분 취소 요청 금액
        $c_PayPlus->mf_set_modx_data( "mod_vat_mny"  , strval($mod_vat_mny)	 );  // 부과세 부분 취소 요청 금액
        $c_PayPlus->mf_set_modx_data( "mod_free_mny" , strval($mod_free_mny) );  // 비관세 부분 취소 요청 금액
    }
}

if ( $tran_cd != "" )
{
    $c_PayPlus->mf_do_tx( "",                $g_conf_home_dir, $g_conf_site_cd,
                          $g_conf_site_key,  $tran_cd,         "",
                          $g_conf_gw_url,    $g_conf_gw_port,  "payplus_cli_slib",
                          $ordr_idxx,        $cust_ip,         $g_conf_log_level,
                          "",                0 );

    $res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
    $res_msg = $c_PayPlus->m_res_msg; // 결과 메시지
    /* $res_en_msg = $c_PayPlus->mf_get_res_data( "res_en_msg" );  // 결과 영문 메세지 */
}
else
{
    $c_PayPlus->m_res_cd  = "9562";
    $c_PayPlus->m_res_msg = "연동 오류|Payplus Plugin이 설치되지 않았거나 tran_cd값이 설정되지 않았습니다.";
}

if ($res_cd != '0000')
{
    $res_msg = iconv("euc-kr", "utf-8", $res_msg);

    alert("$res_cd : $res_msg");
}

/* ============================================================================== */
/* =       취소 결과 처리                                                       = */
/* = -------------------------------------------------------------------------- = */
if ( $req_tx == "mod" )
{
    if ( $res_cd == "0000" )
    {
        $tno = $c_PayPlus->mf_get_res_data( "tno" );  // KCP 거래 고유 번호
        $amount  = $c_PayPlus->mf_get_res_data( "amount"       ); // 원 거래금액
        $mod_mny = $c_PayPlus->mf_get_res_data( "panc_mod_mny" ); // 취소요청된 금액
        $rem_mny = $c_PayPlus->mf_get_res_data( "panc_rem_mny" ); // 취소요청후 잔액

        // 환불금액기록
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_refund_price = od_refund_price + '$mod_mny',
                        od_shop_memo = concat(od_shop_memo, \"$mod_memo\")
                    where od_id = '{$od['od_id']}'
                      and od_tno = '$tno' ";
        sql_query($sql);

        // 미수금 등의 정보 업데이트
        $info = get_order_info($od_id);

        $sql = " update {$g5['g5_shop_order_table']}
                    set od_misu     = '{$info['od_misu']}',
                        od_tax_mny  = '{$info['od_tax_mny']}',
                        od_vat_mny  = '{$info['od_vat_mny']}',
                        od_free_mny = '{$info['od_free_mny']}'
                    where od_id = '$od_id' ";
        sql_query($sql);
    } // End of [res_cd = "0000"]

/* = -------------------------------------------------------------------------- = */
/* =       취소 실패 결과 처리                                                  = */
/* = -------------------------------------------------------------------------- = */
    else
    {
    }
}

// locale 설정 초기화
setlocale(LC_CTYPE, '');

include_once(G5_PATH.'/head.sub.php');
?>

<script>
alert("<?php echo $od['od_settle_case']; ?> 부분취소 처리됐습니다.");
opener.document.location.reload();
self.close();
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>