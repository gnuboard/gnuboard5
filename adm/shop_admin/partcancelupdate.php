<?php
$sub_menu = '400400';
include_once('./_common.php');

if(!$_POST['tno'] || !$_POST['req_tx'] || !$_POST['mod_type'])
    alert_close('올바른 방법으로 이용해 주십시오.');

auth_check($auth[$sub_menu], "w");

$sql = " select od_id, od_settle_case, od_time
            from {$g4['shop_order_table']}
            where od_id = '{$_POST['od_id']}' ";
$od = sql_fetch($sql);

if(!$od['od_id'])
    alert('주문정보가 존재하지 않습니다.');

if($od['od_settle_case'] != '신용카드' && $od['od_settle_case'] != '계좌이체')
    alert('부분취소는 신용카드 또는 실시간 계좌이체 결제건에 대해서만 요청할 수 있습니다.');

if($od['od_settle_case'] == '계좌이체' && substr(0, 10, $od['od_time']) == G4_TIME_YMD)
    alert('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

// 취소사유의 한글깨짐 방지처리
$def_locale = setlocale(LC_CTYPE, 0);
$_POST['mod_desc'] = iconv("utf-8", "euc-kr", $_POST['mod_desc']);
$locale_change = false;
if(preg_match("/utf[\-]?8/i", $def_locale)) {
    setlocale(LC_CTYPE, 'ko_KR.euc-kr');
    $locale_change = true;
}

// 부분취소 실행
if ($default['de_card_test']) {
    if ($default['de_escrow_use'] == 1) {
        // 에스크로결제 테스트
        $default['de_kcp_mid'] = "T0007";
        $default['de_kcp_site_key'] = '2.mDT7R4lUIfHlHq4byhYjf__';
    }
    else {
        // 일반결제 테스트
        $default['de_kcp_mid'] = "T0000";
        $default['de_kcp_site_key'] = '3grptw1.zW0GSo4PQdaGvsF__';
    }
}
else {
    $default['de_kcp_mid'] = "SR".$default['de_kcp_mid'];
}

$g_conf_site_cd = $default['de_kcp_mid'];
$g_conf_home_dir  = G4_SHOP_PATH.'/kcp';
$g_conf_key_dir   = '';
$g_conf_log_dir   = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    $g_conf_key_dir   = G4_SHOP_PATH.'/kcp/bin/pub.key';
    $g_conf_log_dir   = G4_SHOP_PATH.'/kcp/log';
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

$g_conf_log_level = "3";
$g_conf_gw_port   = "8090";

include_once(G4_SHOP_PATH.'/kcp/pp_ax_hub_lib.php');

$tno            = $_POST['tno'];
$req_tx         = $_POST['req_tx'];
$mod_type       = $_POST['mod_type'];
$mod_desc       = $_POST['mod_desc'];
$cust_ip        = getenv("REMOTE_ADDR");

$c_PayPlus = new C_PP_CLI;
$c_PayPlus->mf_clear();

if ( $req_tx == "mod" )
{
    $tran_cd = "00200000";

    $c_PayPlus->mf_set_modx_data( "tno",      $tno      ); // KCP 원거래 거래번호
    $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type ); // 원거래 변경 요청 종류
    $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  ); // 변경 요청자 IP
    $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc ); // 변경 사유

    if ( $mod_type == "RN07" || $mod_type == "STPA" ) // 부분취소의 경우
    {
        $c_PayPlus->mf_set_modx_data( "mod_mny", $_POST[ "mod_mny" ] ); // 취소요청금액
        $c_PayPlus->mf_set_modx_data( "rem_mny", $_POST[ "rem_mny" ] ); // 취소가능잔액
    }
}

if ( $tran_cd != "" )
{
    $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, "", $tran_cd, "",
                          $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                          $cust_ip, "3" , 0, 0, $g_conf_key_dir, $g_conf_log_dir); // 응답 전문 처리

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

/* = -------------------------------------------------------------------------- = */
/* =       부분취소 결과 처리                                                   = */
/* = -------------------------------------------------------------------------- = */
        if ( $mod_type == "RN07" || $mod_type == "STPA" ) // 부분취소의 경우
        {
            $amount  = $c_PayPlus->mf_get_res_data( "amount"       ); // 원 거래금액
            $mod_mny = $c_PayPlus->mf_get_res_data( "panc_mod_mny" ); // 취소요청된 금액
            $rem_mny = $c_PayPlus->mf_get_res_data( "panc_rem_mny" ); // 취소요청후 잔액

            $sql = " update {$g4['shop_order_table']}
                        set od_cancel_card = od_cancel_card + '$mod_mny'
                        where od_id = '{$od['od_id']}' ";
            sql_query($sql);
        }
    } // End of [res_cd = "0000"]

/* = -------------------------------------------------------------------------- = */
/* =       취소 실패 결과 처리                                                  = */
/* = -------------------------------------------------------------------------- = */
    else
    {
    }
}

if($locale_change)
    setlocale(LC_CTYPE, $def_locale);
?>

<script>
alert("부분취소 요청이 정상처리 됐습니다.");
opener.window.location.reload();
window.close();
</script>