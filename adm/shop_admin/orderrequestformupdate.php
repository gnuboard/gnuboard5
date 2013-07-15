<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$sql = " select * from {$g4['shop_request_table']} where rq_id = '$rq_id' ";
$rq = sql_fetch($sql);
if(!$rq['rq_id'])
    die('등록된 자료가 없습니다.');

// 주문정보
$sql = " select * from {$g4['shop_order_table']} where od_id = '{$rq['od_id']}' ";
$od = sql_fetch($sql);

if(!$od['od_id'])
    die('주문정보가 존재하지 않습니다.');

if(!trim($rq_content))
    die('처리내용을 입력해 주십시오.');

// 상품의 상태변경
$ct_status = '';
if($rq['rq_type'] == 0)
    $ct_status = '취소';
else if($rq['rq_type'] == 2)
    $ct_status = '반품';

if($rq_status == 1 && $ct_status != '') {
    $item = explode(',', $rq['ct_id']);
    for($i=0; $i<count($item); $i++) {
        $sql = " update {$g4['shop_cart_table']}
                    set ct_status = '$ct_status'
                    where uq_id = '{$od['uq_id']}'
                      and ct_id = '{$item[$i]}' ";
        sql_query($sql);
    }
}

// 환불금액입력(입금 금액이 있을 때만)
$rq_amount1 = preg_replace('/[^0-9]/', '', $rq_amount1);
if($od['od_receipt_amount'] > 0 && $rq_amount1 > 0) {
    $sql = " update {$g4['shop_order_table']}
                set od_refund_amount = '$rq_amount1'
                where od_id = '{$od['od_id']}' ";
    sql_query($sql);
}

// 처리내용입력
$sql = " insert into `{$g4['shop_request_table']}`
              ( rq_type, rq_parent, od_id, ct_id, mb_id, rq_content, rq_status, rq_item, dl_company, rq_invoice, rq_amount1, rq_amount2, rq_amount3, rq_account, rq_ip, rq_time )
            values
              ( '{$rq['rq_type']}', '$rq_id', '{$od['od_id']}', '{$rq['ct_id']}', '{$member['mb_id']}', '$rq_content', '$rq_status', '$rq_item', '$dl_company', '$rq_invoice', '$rq_amount1', '$rq_amount2', '$rq_amount3', '$rq_account', '$REMOTE_ADDR', '".G4_TIME_YMDHIS."' ) ";
sql_query($sql);

// 부분취소처리(결제금액이 있을 때만)
if(($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == '계좌이체') && $rq_status == 1 && $od['od_receipt_amount'] > 0 && $od['od_tno'])
{
    $rq_amount2 = preg_replace('/[^0-9]/', '', $rq_amount2);
    $rq_amount3 = preg_replace('/[^0-9]/', '', $rq_amount3);

    switch($rq['rq_type']) {
        case 0:
            $type = '취소';
            break;
        case 1:
            $type = '교환';
            break;
        case 2:
            $type = '반품';
            break;
        default:
            $type = '';
            break;
    }

    if($od['od_settle_case'] == '계좌이체' && substr(0, 10, $od['od_time']) >= G4_TIME_YMD)
        die('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

    // 취소사유의 한글깨짐 방지처리
    $def_locale = setlocale(LC_CTYPE, 0);
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

    $g_conf_site_cd   = $default['de_kcp_mid'];
    $g_conf_site_key  = $default['de_kcp_site_key'];
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

    include G4_SHOP_PATH.'/kcp/pp_cli_hub_lib.php';

    $tno            = $od['od_tno'];
    $req_tx         = 'mod';
    $mod_desc       = '고객 '.$type.'요청으로 인한 부분취소';
    $cust_ip        = getenv('REMOTE_ADDR');
    $rem_mny        = $od['od_receipt_amount'] - $od['od_cancel_card'];;
    $mod_mny        = $rq_amount2;
    $tax_mny        = $rq_amount2;
    $mod_free_mny   = $rq_amount3;
    $mod_type       = 'RN07';
    if($od['od_settle_case'] == '계좌이체')
        $mod_type   = 'STPA';

    if($default['de_tax_flag_use']) {
        $mod_mny = strval($tax_mny + $mod_free_mny);
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
        $c_PayPlus->mf_set_modx_data( "rem_mny"      , $rem_mny              );  // 취소 가능 잔액
        $c_PayPlus->mf_set_modx_data( "mod_mny"      , $mod_mny              );  // 취소 요청 금액

        if($default['de_tax_flag_use'])
        {
            $mod_tax_mny = round((int)$tax_mny / 1.1);
            $mod_vat_mny = (int)$tax_mny - $mod_tax_mny;

            $c_PayPlus->mf_set_modx_data( "tax_flag"     , "TG03"				 );  // 복합과세 구분
            $c_PayPlus->mf_set_modx_data( "mod_tax_mny"  , strval($mod_tax_mny)  );	 // 공급가 부분 취소 요청 금액
            $c_PayPlus->mf_set_modx_data( "mod_vat_mny"  , strval($mod_vat_mny)	 );  // 부과세 부분 취소 요청 금액
            $c_PayPlus->mf_set_modx_data( "mod_free_mny" , $mod_free_mny		 );  // 비관세 부분 취소 요청 금액
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

        die("$res_cd : $res_msg");
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

            $sql = " update {$g4['shop_order_table']}
                        set od_cancel_card = od_cancel_card + '$mod_mny'
                        where od_id = '{$od['od_id']}' ";
            sql_query($sql);
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
}
?>