<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

    /* ============================================================================== */
    /* =   PAGE : 지불 요청 및 결과 처리 PAGE                                       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.02   KCP Inc.   All Rights Reserved.                 = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   환경 설정                                                                = */
    /* = -------------------------------------------------------------------------- = */

    include_once(G5_SHOP_PATH.'/settle_kcp.inc.php');
    require "pp_ax_hub_lib.php";              // library [수정불가]

    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   POST 형식 체크부분                                                       = */
    /* = -------------------------------------------------------------------------- = */
    if ( $_SERVER['REQUEST_METHOD'] != 'POST' )
    {
        echo('잘못된 경로로 접속하였습니다.');
        exit;
    }
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   01. 지불 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
	$req_tx         = isset($_POST['req_tx']) ? $_POST['req_tx'] : ''; // 요청 종류
	$tran_cd        = isset($_POST['tran_cd']) ? preg_replace('/[^0-9A-Za-z_\-\.]/i', '', $_POST['tran_cd']) : ''; // 처리 종류
	/* = -------------------------------------------------------------------------- = */
	$cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
	$ordr_idxx      = isset($_POST['ordr_idxx']) ? preg_replace('/[^0-9A-Za-z_\-\.]/i', '', $_POST['ordr_idxx']) : ''; // 쇼핑몰 주문번호
	$good_name      = isset($_POST['good_name']) ? addslashes($_POST['good_name']) : ''; // 상품명
	$good_mny       = isset($_POST['good_mny']) ? (int) $_POST['good_mny'] : 0; // 결제 총금액
	/* = -------------------------------------------------------------------------- = */
    $res_cd         = "";                         // 응답코드
    $res_msg        = "";                         // 응답메시지
	$res_en_msg     = "";                         // 응답 영문 메세지
    $tno            = isset($_POST['tno']) ? $_POST['tno'] : ''; // KCP 거래 고유 번호
    /* = -------------------------------------------------------------------------- = */
    $buyr_name      = isset($_POST['buyr_name']) ? addslashes($_POST['buyr_name']) : ''; // 주문자명
    $buyr_tel1      = isset($_POST['buyr_tel1']) ? $_POST['buyr_tel1'] : ''; // 주문자 전화번호
    $buyr_tel2      = isset($_POST['buyr_tel2']) ? $_POST['buyr_tel2'] : ''; // 주문자 핸드폰 번호
    $buyr_mail      = isset($_POST['buyr_mail']) ? $_POST['buyr_mail'] : ''; // 주문자 E-mail 주소
    /* = -------------------------------------------------------------------------- = */
    $mod_type       = isset($_POST['mod_type']) ? $_POST['mod_type'] : ''; // 변경TYPE VALUE 승인취소시 필요
    $mod_desc       = isset($_POST['mod_desc']) ? $_POST['mod_desc'] : ''; // 변경사유
    /* = -------------------------------------------------------------------------- = */
    $use_pay_method = isset($_POST['use_pay_method']) ? $_POST['use_pay_method'] : ''; // 결제 방법
    $bSucc          = "";                         // 업체 DB 처리 성공 여부
    /* = -------------------------------------------------------------------------- = */
	$app_time       = "";                         // 승인시간 (모든 결제 수단 공통)
	$amount         = "";                         // KCP 실제 거래 금액
	$total_amount   = 0;                          // 복합결제시 총 거래금액
    $coupon_mny		= "";						  // 쿠폰금액
    /* = -------------------------------------------------------------------------- = */
    $card_cd        = "";                         // 신용카드 코드
    $card_name      = "";                         // 신용카드 명
    $app_no         = "";                         // 신용카드 승인번호
    $noinf          = "";                         // 신용카드 무이자 여부
    $quota          = "";                         // 신용카드 할부개월
	$partcanc_yn    = "";						  // 부분취소 가능유무
	$card_bin_type_01 = "";                       // 카드구분1
	$card_bin_type_01 = "";                       // 카드구분2
    $card_mny		= "";						  // 카드결제금액
    /* = -------------------------------------------------------------------------- = */
	$bank_name      = "";                         // 은행명
	$bank_code      = "";						  // 은행코드
    $bk_mny			= "";						  // 계좌이체결제금액
	/* = -------------------------------------------------------------------------- = */
    $bankname       = "";                         // 입금할 은행명
    $depositor      = "";                         // 입금할 계좌 예금주 성명
    $account        = "";                         // 입금할 계좌 번호
	$va_date		= "";						  // 가상계좌 입금마감시간
    /* = -------------------------------------------------------------------------- = */
	$pnt_issue      = "";                         // 결제 포인트사 코드
	$pt_idno        = "";                         // 결제 및 인증 아이디
	$pnt_amount     = "";                         // 적립금액 or 사용금액
	$pnt_app_time   = "";                         // 승인시간
	$pnt_app_no     = "";                         // 승인번호
    $add_pnt        = "";                         // 발생 포인트
	$use_pnt        = "";                         // 사용가능 포인트
	$rsv_pnt        = "";                         // 총 누적 포인트
    /* = -------------------------------------------------------------------------- = */
	$commid         = "";                         // 통신사 코드
	$mobile_no      = "";                         // 휴대폰 번호
	/* = -------------------------------------------------------------------------- = */
	$shop_user_id   = isset($_POST['shop_user_id']) ? $_POST['shop_user_id'] : ''; // 가맹점 고객 아이디
	$tk_van_code    = "";                         // 발급사 코드
	$tk_app_no      = "";                         // 상품권 승인 번호
	/* = -------------------------------------------------------------------------- = */
    $cash_yn        = isset($_POST['cash_yn']) ? $_POST['cash_yn'] : ''; // 현금영수증 등록 여부
    $cash_authno    = "";                         // 현금 영수증 승인 번호
    $cash_tr_code   = isset($_POST['cash_tr_code']) ? $_POST['cash_tr_code'] : ''; // 현금 영수증 발행 구분
    $cash_id_info   = isset($_POST['cash_id_info']) ? $_POST['cash_id_info'] : ''; // 현금 영수증 등록 번호
    /* ============================================================================== */
    /* =   01-1. 에스크로 지불 요청 정보 설정                                       = */
    /* = -------------------------------------------------------------------------- = */
    $escw_used      = isset($_POST['escw_used']) ? $_POST['escw_used'] : ''; // 에스크로 사용 여부
    $pay_mod        = isset($_POST['pay_mod']) ? $_POST['pay_mod'] : ''; // 에스크로 결제처리 모드
    $deli_term      = isset($_POST['deli_term']) ? $_POST['deli_term'] : ''; // 배송 소요일
    $bask_cntx      = isset($_POST['bask_cntx']) ? $_POST['bask_cntx'] : 0; // 장바구니 상품 개수
    $good_info      = isset($_POST['good_info']) ? $_POST['good_info'] : ''; // 장바구니 상품 상세 정보
    $rcvr_name      = isset($_POST['rcvr_name']) ? addslashes($_POST['rcvr_name']) : ''; // 수취인 이름
    $rcvr_tel1      = isset($_POST['rcvr_tel1']) ? $_POST['rcvr_tel1'] : ''; // 수취인 전화번호
    $rcvr_tel2      = isset($_POST['rcvr_tel2']) ? $_POST['rcvr_tel2'] : ''; // 수취인 휴대폰번호
    $rcvr_mail      = isset($_POST['rcvr_mail']) ? $_POST['rcvr_mail'] : ''; // 수취인 E-Mail
    $rcvr_zipx      = isset($_POST['rcvr_zipx']) ? $_POST['rcvr_zipx'] : ''; // 수취인 우편번호
    $rcvr_add1      = isset($_POST['rcvr_add1']) ? addslashes($_POST['rcvr_add1']) : ''; // 수취인 주소
    $rcvr_add2      = isset($_POST['rcvr_add2']) ? addslashes($_POST['rcvr_add2']) : ''; // 수취인 상세주소
	$escw_yn		= "";						  // 에스크로 여부

    /* ============================================================================== */

/* ============================================================================== */
/* =   02. 인스턴스 생성 및 초기화                                              = */
/* = -------------------------------------------------------------------------- = */
/* =       결제에 필요한 인스턴스를 생성하고 초기화 합니다.                     = */
/* = -------------------------------------------------------------------------- = */
$c_PayPlus = new C_PP_CLI_T;

$c_PayPlus->mf_clear();
/* ------------------------------------------------------------------------------ */
/* =   02. 인스턴스 생성 및 초기화 END											= */
/* ============================================================================== */


/* ============================================================================== */
/* =   03. 처리 요청 정보 설정                                                  = */
/* = -------------------------------------------------------------------------- = */

/* = -------------------------------------------------------------------------- = */
/* =   03-1. 승인 요청                                                          = */
/* = -------------------------------------------------------------------------- = */
if ( $req_tx == "pay" )
{
        /* 1004원은 실제로 업체에서 결제하셔야 될 원 금액을 넣어주셔야 합니다. 결제금액 유효성 검증 */
        $c_PayPlus->mf_set_ordr_data( "ordr_mony",  $good_mny );
        
        $post_enc_data = isset($_POST['enc_data']) ? $_POST['enc_data'] : '';
        $post_enc_info = isset($_POST['enc_info']) ? $_POST['enc_info'] : '';

        $c_PayPlus->mf_set_encx_data( $post_enc_data, $post_enc_info );
}

/* = -------------------------------------------------------------------------- = */
/* =   03-2. 취소/매입 요청                                                     = */
/* = -------------------------------------------------------------------------- = */
else if ( $req_tx == "mod" )
{
    $tran_cd = "00200000";

    $c_PayPlus->mf_set_modx_data( "tno",      $tno      ); // KCP 원거래 거래번호
    $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type ); // 원거래 변경 요청 종류
    $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  ); // 변경 요청자 IP
    $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc ); // 변경 사유
}

/* = -------------------------------------------------------------------------- = */
/* =   03-3. 에스크로 상태변경 요청                                             = */
/* = -------------------------------------------------------------------------- = */
else if ($req_tx = "mod_escrow")
{
    $tran_cd = "00200000";

    $c_PayPlus->mf_set_modx_data( "tno",      $tno      );						// KCP 원거래 거래번호
    $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type );						// 원거래 변경 요청 종류
    $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  );						// 변경 요청자 IP
    $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc );						// 변경 사유

    if ($mod_type == "STE1")													// 상태변경 타입이 [배송요청]인 경우
    {
        $post_deli_numb = isset($_POST['deli_numb']) ? $_POST['deli_numb'] : '';
        $post_deli_corp = isset($_POST['deli_corp']) ? $_POST['deli_corp'] : '';

        $c_PayPlus->mf_set_modx_data( "deli_numb",   $post_deli_numb );   // 운송장 번호
        $c_PayPlus->mf_set_modx_data( "deli_corp",   $post_deli_corp );   // 택배 업체명
    }
    else if ($mod_type == "STE2" || $mod_type == "STE4") // 상태변경 타입이 [즉시취소] 또는 [취소]인 계좌이체, 가상계좌의 경우
    {
        if ($vcnt_yn == "Y")
        {
            $post_refund_account = isset($_POST['refund_account']) ? $_POST['refund_account'] : '';
            $post_refund_nm = isset($_POST['refund_nm']) ? $_POST['refund_nm'] : '';
            $post_bank_code = isset($_POST['bank_code']) ? $_POST['bank_code'] : '';
            $c_PayPlus->mf_set_modx_data( "refund_account",   $post_refund_account );      // 환불수취계좌번호
            $c_PayPlus->mf_set_modx_data( "refund_nm",        $post_refund_nm );      // 환불수취계좌주명
            $c_PayPlus->mf_set_modx_data( "bank_code",        $post_bank_code );      // 환불수취은행코드
        }
    }
}
/* = -------------------------------------------------------------------------- = */
/* =   03-3. 에스크로 상태변경 요청 END                                         = */
/* = -------------------------------------------------------------------------- = */

/* ------------------------------------------------------------------------------ */
/* =   03.  처리 요청 정보 설정 END  											= */
/* ============================================================================== */



/* ============================================================================== */
/* =   04. 실행                                                                 = */
/* = -------------------------------------------------------------------------- = */
if ( $tran_cd != "" )
{
    $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
                          $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                          $cust_ip, $g_conf_log_level , 0, 0, $g_conf_key_dir, $g_conf_log_dir); // 응답 전문 처리

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

    /*
    echo "<script>
    var openwin = window.open( './kcp/proc_win.php', 'proc_win', '' );
    openwin.close();
    </script>";
    */
    alert("$res_cd : $res_msg");
    exit;
}

/* = -------------------------------------------------------------------------- = */
/* =   04. 실행 END                                                             = */
/* ============================================================================== */


/* ============================================================================== */
/* =   05. 승인 결과 값 추출                                                    = */
/* = -------------------------------------------------------------------------- = */
if ( $req_tx == "pay" )
{
    if( $res_cd == "0000" )
    {
        $tno       = $c_PayPlus->mf_get_res_data( "tno"       ); // KCP 거래 고유 번호
        $amount    = $c_PayPlus->mf_get_res_data( "amount"    ); // KCP 실제 거래 금액
        $pnt_issue = $c_PayPlus->mf_get_res_data( "pnt_issue" ); // 결제 포인트사 코드
        $coupon_mny = $c_PayPlus->mf_get_res_data( "coupon_mny" ); // 쿠폰금액

/* = -------------------------------------------------------------------------- = */
/* =   05-1. 신용카드 승인 결과 처리                                            = */
/* = -------------------------------------------------------------------------- = */
        if ( $use_pay_method == "100000000000" )
        {
            $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드사 코드
            $card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드 종류
            $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인 시간
            $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인 번호
            $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부 ( 'Y' : 무이자 )
            $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월 수
            $partcanc_yn = $c_PayPlus->mf_get_res_data( "partcanc_yn" ); // 부분취소 가능유무
            $card_bin_type_01 = $c_PayPlus->mf_get_res_data( "card_bin_type_01" ); // 카드구분1
            $card_bin_type_02 = $c_PayPlus->mf_get_res_data( "card_bin_type_02" ); // 카드구분2
            $card_mny = $c_PayPlus->mf_get_res_data( "card_mny" ); // 카드결제금액
            $od_other_pay_type = $c_PayPlus->mf_get_res_data( "card_other_pay_type" ); // 간편결제유형

            $kcp_pay_method = $c_PayPlus->mf_get_res_data( "pay_method" ); // 카카오페이 결제수단
            // 카드 코드는 PACA, 카카오머니 코드는 PAKM

            if( $kcp_pay_method == "PAKM" ){    // 카카오머니
                $card_mny = $kakaomny_mny = $c_PayPlus->mf_get_res_data( "kakaomny_mny" );
                $app_time = $app_kakaomny_time = $c_PayPlus->mf_get_res_data( "app_kakaomny_time" );
                $od_other_pay_type = 'NHNKCP_KAKAOMONEY';
            }

            /* = -------------------------------------------------------------- = */
            /* =   05-1.1. 복합결제(포인트+신용카드) 승인 결과 처리               = */
            /* = -------------------------------------------------------------- = */
            if ( $pnt_issue == "SCSK" || $pnt_issue == "SCWB" )
            {
                $pt_idno      = $c_PayPlus->mf_get_res_data ( "pt_idno"      ); // 결제 및 인증 아이디
                $pnt_amount   = $c_PayPlus->mf_get_res_data ( "pnt_amount"   ); // 적립금액 or 사용금액
                $pnt_app_time = $c_PayPlus->mf_get_res_data ( "pnt_app_time" ); // 승인시간
                $pnt_app_no   = $c_PayPlus->mf_get_res_data ( "pnt_app_no"   ); // 승인번호
                $add_pnt      = $c_PayPlus->mf_get_res_data ( "add_pnt"      ); // 발생 포인트
                $use_pnt      = $c_PayPlus->mf_get_res_data ( "use_pnt"      ); // 사용가능 포인트
                $rsv_pnt      = $c_PayPlus->mf_get_res_data ( "rsv_pnt"      ); // 총 누적 포인트
                $total_amount = $amount + $pnt_amount;                          // 복합결제시 총 거래금액
            }
        }

/* = -------------------------------------------------------------------------- = */
/* =   05-2. 계좌이체 승인 결과 처리                                            = */
/* = -------------------------------------------------------------------------- = */
        if ( $use_pay_method == "010000000000" )
        {
            $app_time  = $c_PayPlus->mf_get_res_data( "app_time"   );  // 승인 시간
            $bank_name = $c_PayPlus->mf_get_res_data( "bank_name"  );  // 은행명
            $bank_code = $c_PayPlus->mf_get_res_data( "bank_code"  );  // 은행코드
            $bk_mny = $c_PayPlus->mf_get_res_data( "bk_mny" ); // 계좌이체결제금액
        }

/* = -------------------------------------------------------------------------- = */
/* =   05-3. 가상계좌 승인 결과 처리                                            = */
/* = -------------------------------------------------------------------------- = */
        if ( $use_pay_method == "001000000000" )
        {
            $bankname  = $c_PayPlus->mf_get_res_data( "bankname"  ); // 입금할 은행 이름
            $depositor = $c_PayPlus->mf_get_res_data( "depositor" ); // 입금할 계좌 예금주
            $account   = $c_PayPlus->mf_get_res_data( "account"   ); // 입금할 계좌 번호
            $va_date   = $c_PayPlus->mf_get_res_data( "va_date"   ); // 가상계좌 입금마감시간
        }

/* = -------------------------------------------------------------------------- = */
/* =   05-4. 포인트 승인 결과 처리                                               = */
/* = -------------------------------------------------------------------------- = */
        if ( $use_pay_method == "000100000000" )
        {
            $pt_idno      = $c_PayPlus->mf_get_res_data( "pt_idno"      ); // 결제 및 인증 아이디
            $pnt_amount   = $c_PayPlus->mf_get_res_data( "pnt_amount"   ); // 적립금액 or 사용금액
            $pnt_app_time = $c_PayPlus->mf_get_res_data( "pnt_app_time" ); // 승인시간
            $pnt_app_no   = $c_PayPlus->mf_get_res_data( "pnt_app_no"   ); // 승인번호
            $add_pnt      = $c_PayPlus->mf_get_res_data( "add_pnt"      ); // 발생 포인트
            $use_pnt      = $c_PayPlus->mf_get_res_data( "use_pnt"      ); // 사용가능 포인트
            $rsv_pnt      = $c_PayPlus->mf_get_res_data( "rsv_pnt"      ); // 적립 포인트
        }

/* = -------------------------------------------------------------------------- = */
/* =   05-5. 휴대폰 승인 결과 처리                                              = */
/* = -------------------------------------------------------------------------- = */
        if ( $use_pay_method == "000010000000" )
        {
            $app_time  = $c_PayPlus->mf_get_res_data( "hp_app_time"  ); // 승인 시간
            $commid    = $c_PayPlus->mf_get_res_data( "commid"	     ); // 통신사 코드
            $mobile_no = $c_PayPlus->mf_get_res_data( "mobile_no"	 ); // 휴대폰 번호
        }

/* = -------------------------------------------------------------------------- = */
/* =   05-6. 상품권 승인 결과 처리                                              = */
/* = -------------------------------------------------------------------------- = */
        if ( $use_pay_method == "000000001000" )
        {
            $app_time    = $c_PayPlus->mf_get_res_data( "tk_app_time"  ); // 승인 시간
            $tk_van_code = $c_PayPlus->mf_get_res_data( "tk_van_code"  ); // 발급사 코드
            $tk_app_no   = $c_PayPlus->mf_get_res_data( "tk_app_no"    ); // 승인 번호
        }

/* = -------------------------------------------------------------------------- = */
/* =   05-7. 현금영수증 결과 처리                                               = */
/* = -------------------------------------------------------------------------- = */
        $cash_yn      = $c_PayPlus->mf_get_res_data( "cash_yn"  ); // 현금영수증 등록여부
        $cash_authno  = $c_PayPlus->mf_get_res_data( "cash_authno"  ); // 현금 영수증 승인 번호
        $cash_tr_code = $c_PayPlus->mf_get_res_data( "cash_tr_code"  ); // 현금영수증 등록구분

/* = -------------------------------------------------------------------------- = */
/* =   05-8. 에스크로 여부 결과 처리                                            = */
/* = -------------------------------------------------------------------------- = */
        $escw_yn = $c_PayPlus->mf_get_res_data( "escw_yn"  ); // 에스크로 여부
    }
}

/* = -------------------------------------------------------------------------- = */
/* =   05. 승인 결과 처리 END                                                   = */
/* ============================================================================== */;