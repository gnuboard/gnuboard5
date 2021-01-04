<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'kcp') return;

include_once(G5_SHOP_PATH.'/settle_kcp.inc.php');
include_once(G5_SHOP_PATH.'/kcp/pp_ax_hub_lib.php');

// locale ko_KR.euc-kr 로 설정
setlocale(LC_CTYPE, 'ko_KR.euc-kr');

$req_tx         = 'mod_escrow';
$mod_type       = 'STE1';
$mod_desc       = '에스크로 배송시작 등록';
$cust_ip        = getenv('REMOTE_ADDR');
$ordr_idxx      = isset($ordr_idxx) ? preg_replace('/[^a-z0-9_\-]/i', '', $ordr_idxx) : '';

$c_PayPlus = new C_PP_CLI_T;
$c_PayPlus->mf_clear();

$tran_cd = "00200000";

// 에스크로 상태변경
$c_PayPlus->mf_set_modx_data( "tno",        $escrow_tno  );
$c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type        );
$c_PayPlus->mf_set_modx_data( "mod_ip",     $cust_ip         );
$c_PayPlus->mf_set_modx_data( "mod_desc",   $mod_desc        );

$c_PayPlus->mf_set_modx_data( "deli_numb",  $escrow_numb );
$c_PayPlus->mf_set_modx_data( "deli_corp",  $escrow_corp );

$c_PayPlus->mf_do_tx( '', $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
                      $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                      $cust_ip, "3" , 0, 0, $g_conf_key_dir, $g_conf_log_dir); // 응답 전문 처리

$res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
$res_msg = $c_PayPlus->m_res_msg; // 결과 메시지

// locale 설정 초기화
setlocale(LC_CTYPE, '');