<?
include "./_common.php";

    ////////////////////////////////////////////////////////////////////////////////////
    /*
    08.01.30
    무통장을 제외한 결제시 shop/settleresult.php에서 포인트를 차감하게 되는데
    주문시 새로운 창을 여러개 띄우고 결제를 하게 되면 포인트가 - (마이너스)로 
    처리되며, 할인된 금액으로 정상 결제가 됨.
    이런 오류를 방지하고자 아래의 코드를 추가 함
    포인트로 결제하는 내역이 있으면서 회원의 포인트가 - (마이너스) 포인트라면 오류메세지 출력
    */
    $sql = " select od_temp_point from $g4[yc4_order_table] where od_id = '$_POST[ordr_idxx]' ";
    $row = sql_fetch($sql);
    if ($row[od_temp_point] > 0 && $member[mb_point] < 0)
        alert("결제 오류 : 담당자에게 문의하시기 바랍니다.");

    ////////////////////////////////////////////////////////////////////////////////////

    // 주문시 유일한 키
    $on_uid = $_POST[on_uid];


    /* ============================================================================== */
    /* =   PAGE : 지불 요청 및 결과 처리 PAGE                                       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.02   KCP Inc.   All Rights Reserved.                 = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */

    $g_conf_home_dir  = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . '/payplus';
    $g_conf_key_dir   = '';
    $g_conf_log_dir   = '';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
    {
        $g_conf_key_dir   = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . '/payplus/bin/pub.key';
        $g_conf_log_dir   = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . '/payplus/log';
    }

    $g_conf_site_cd = $_POST['site_cd'];

    if (preg_match("/^T000/", $g_conf_site_cd) || $default['de_card_test']) {
        $g_conf_gw_url  = "testpaygw.kcp.co.kr";                    // real url : paygw.kcp.co.kr , test url : testpaygw.kcp.co.kr
    } 
    else {
        $g_conf_gw_url  = "paygw.kcp.co.kr";
        if (!preg_match("/^SR/", $g_conf_site_cd)) {
            alert("SR 로 시작하지 않는 KCP SITE CODE 는 지원하지 않습니다.");
        }
    }

    $g_conf_log_level = "3";           // 변경불가
    $g_conf_gw_port   = "8090";        // 포트번호(변경불가)

    require "pp_ax_hub_lib.php";              // library [수정불가]

    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */
?>

<?
    /* ============================================================================== */
    /* =   01. 지불 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
	$req_tx         = $_POST[ "req_tx"         ]; // 요청 종류
	$tran_cd        = $_POST[ "tran_cd"        ]; // 처리 종류
	/* = -------------------------------------------------------------------------- = */
	$cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
	$ordr_idxx      = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호
	$good_name      = $_POST[ "good_name"      ]; // 상품명
	$good_mny       = $_POST[ "good_mny"       ]; // 결제 총금액
	/* = -------------------------------------------------------------------------- = */
    $res_cd         = "";                         // 응답코드
    $res_msg        = "";                         // 응답메시지
    $tno            = $_POST[ "tno"            ]; // KCP 거래 고유 번호
	$vcnt_yn        = $_POST[ "vcnt_yn"        ]; // 가상계좌 에스크로 사용 유무
    /* = -------------------------------------------------------------------------- = */
    $buyr_name      = $_POST[ "buyr_name"      ]; // 주문자명
    $buyr_tel1      = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호
    $buyr_tel2      = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호
    $buyr_mail      = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소
    /* = -------------------------------------------------------------------------- = */
    $mod_type       = $_POST[ "mod_type"       ]; // 변경TYPE VALUE 승인취소시 필요
    $mod_desc       = $_POST[ "mod_desc"       ]; // 변경사유
    /* = -------------------------------------------------------------------------- = */
    $use_pay_method = $_POST[ "use_pay_method" ]; // 결제 방법
    $bSucc          = "";                         // 업체 DB 처리 성공 여부
    /* = -------------------------------------------------------------------------- = */
	$app_time       = "";                         // 승인시간 (모든 결제 수단 공통)
	$total_amount   = 0;                          // 복합결제시 총 거래금액
    $amount         = "";                         // KCP 실제 거래 금액
    /* = -------------------------------------------------------------------------- = */
    $card_cd        = "";                         // 신용카드 코드
    $card_name      = "";                         // 신용카드 명
    $app_no         = "";                         // 신용카드 승인번호
    $noinf          = "";                         // 신용카드 무이자 여부
    $quota          = "";                         // 신용카드 할부개월
    /* = -------------------------------------------------------------------------- = */
	$bank_name      = "";                         // 은행명
	$bank_code      = "";						  // 은행코드
	/* = -------------------------------------------------------------------------- = */
    $bankname       = "";                         // 입금할 은행명
    $depositor      = "";                         // 입금할 계좌 예금주 성명
    $account        = "";                         // 입금할 계좌 번호
	$va_date		= "";						  // 가상계좌 입금마감시간
    /* = -------------------------------------------------------------------------- = */
	$pnt_issue      = "";					      // 결제 포인트사 코드
	$pt_idno        = "";                         // 결제 및 인증 아이디
	$pnt_amount     = "";                         // 적립금액 or 사용금액
	$pnt_app_time   = "";                         // 승인시간
	$pnt_app_no     = "";                         // 승인번호
    $add_pnt        = "";                         // 발생 포인트
	$use_pnt        = "";                         // 사용가능 포인트
	$rsv_pnt        = "";                         // 총 누적 포인트
    /* = -------------------------------------------------------------------------- = */
	$commid         = "";                         // 통신사 코드
	$mobile_no      = "";                         // 휴대폰 코드
	/* = -------------------------------------------------------------------------- = */
	$tk_shop_id		= $_POST[ "tk_shop_id"     ]; // 가맹점 고객 아이디
	$tk_van_code    = "";                         // 발급사 코드
	$tk_app_no      = "";                         // 상품권 승인 번호
	/* = -------------------------------------------------------------------------- = */
    $cash_yn        = $_POST[ "cash_yn"        ]; // 현금영수증 등록 여부
    $cash_authno    = "";                         // 현금 영수증 승인 번호
    $cash_tr_code   = $_POST[ "cash_tr_code"   ]; // 현금 영수증 발행 구분
    $cash_id_info   = $_POST[ "cash_id_info"   ]; // 현금 영수증 등록 번호
	/* ============================================================================== */
    /* =   01-1. 에스크로 지불 요청 정보 설정                                       = */
    /* = -------------------------------------------------------------------------- = */  
    $escw_used      = $_POST[  "escw_used"     ]; // 에스크로 사용 여부
    $pay_mod        = $_POST[  "pay_mod"       ]; // 에스크로 결제처리 모드
    $deli_term      = $_POST[  "deli_term"     ]; // 배송 소요일
    $bask_cntx      = $_POST[  "bask_cntx"     ]; // 장바구니 상품 개수
    $good_info      = $_POST[  "good_info"     ]; // 장바구니 상품 상세 정보
    $rcvr_name      = $_POST[  "rcvr_name"     ]; // 수취인 이름
    $rcvr_tel1      = $_POST[  "rcvr_tel1"     ]; // 수취인 전화번호
    $rcvr_tel2      = $_POST[  "rcvr_tel2"     ]; // 수취인 휴대폰번호
    $rcvr_mail      = $_POST[  "rcvr_mail"     ]; // 수취인 E-Mail
    $rcvr_zipx      = $_POST[  "rcvr_zipx"     ]; // 수취인 우편번호
    $rcvr_add1      = $_POST[  "rcvr_add1"     ]; // 수취인 주소
    $rcvr_add2      = $_POST[  "rcvr_add2"     ]; // 수취인 상세주소
	$escw_yn		= "";						  // 에스크로 여부
    /* = -------------------------------------------------------------------------- = */
    /* =   01. 지불 요청 정보 설정 END                                              = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화(변경 불가)                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =       결제에 필요한 인스턴스를 생성하고 초기화 합니다.                     = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus = new C_PP_CLI;

    $c_PayPlus->mf_clear();
    /* ------------------------------------------------------------------------------ */
	/* =   02. 인스턴스 생성 및 초기화 END											= */
	/* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 승인 요청 정보 설정                                                = */
    /* = -------------------------------------------------------------------------- = */

	if ( $req_tx == "pay" )
    {
            $c_PayPlus->mf_set_encx_data( $_POST[ "enc_data" ], $_POST[ "enc_info" ] );
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
            $c_PayPlus->mf_set_modx_data( "deli_numb",   $_POST[ "deli_numb" ] );   // 운송장 번호
            $c_PayPlus->mf_set_modx_data( "deli_corp",   $_POST[ "deli_corp" ] );   // 택배 업체명
        }
        else if ($mod_type == "STE2" || $mod_type == "STE4") // 상태변경 타입이 [즉시취소] 또는 [취소]인 계좌이체, 가상계좌의 경우
        {
            if ($vcnt_yn == "Y")
            {
                $c_PayPlus->mf_set_modx_data( "refund_account",   $_POST[ "refund_account" ] );      // 환불수취계좌번호
                $c_PayPlus->mf_set_modx_data( "refund_nm",        $_POST[ "refund_nm"      ] );      // 환불수취계좌주명
                $c_PayPlus->mf_set_modx_data( "bank_code",        $_POST[ "bank_code"      ] );      // 환불수취은행코드
            }
        }
    }
    /* = -------------------------------------------------------------------------- = */
    /* =   03-3. 에스크로 상태변경 요청 END                                         = */
    /* = -------------------------------------------------------------------------- = */

	/* ------------------------------------------------------------------------------ */
	/* =   03.  처리 요청 정보 설정 END  											= */
	/* ============================================================================== */

    // 결제금액을 조작하여 넘어오는 경우에는 pp_cli 실행전에 에러를 출력한다. 그러므로 에러 출력시 결제는 되지 않는다.
    $site_cd   = $_POST['site_cd'];
    $timestamp = $_POST['timestamp'];
    $serverkey = $_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_ADDR']; // 사용자가 알수 없는 고유한 값들
    $hashdata  = $_POST['hashdata']; // 넘어온값
    $hashdata2 = md5($site_cd.$ordr_idxx.$good_mny.$timestamp.$serverkey);
    if ($hashdata !== $hashdata2)
        die("DATA Error!!!");

    /* ============================================================================== */
    /* =   04. 실행                                                                 = */
    /* = -------------------------------------------------------------------------- = */
    if ( $tran_cd != "" )
    {
        $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, "", $tran_cd, "",
                              $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                              $cust_ip, "3" , 0, 0, $g_conf_key_dir, $g_conf_log_dir);

		$res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
		$res_msg = $c_PayPlus->m_res_msg; // 결과 메시지
    }
    else
    {
        $c_PayPlus->m_res_cd  = "9562";
        $c_PayPlus->m_res_msg = "연동 오류|Payplus Plugin이 설치되지 않았거나 tran_cd값이 설정되지 않았습니다.";
    }

    if ($res_cd != '0000')
    {
        if (strtolower($g4[charset]) == "utf-8") 
        {
            $res_msg = iconv("euc-kr", "utf-8", $res_msg);
        }

        echo "<script>
        var openwin = window.open( './proc_win.php', 'proc_win', '' );
        openwin.close();
        </script>";
        alert("$res_cd : $res_msg");
        exit;
    }
    /* = -------------------------------------------------------------------------- = */
    /* =   04. 실행 END                                                             = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   05. 승인 결과 값 추출                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   수정하지 마시기 바랍니다.                                                = */
    /* = -------------------------------------------------------------------------- = */
    if ( $req_tx == "pay" )
    {
        if( $res_cd == "0000" )
        {
            $tno       = $c_PayPlus->mf_get_res_data( "tno"       ); // KCP 거래 고유 번호
            $amount    = $c_PayPlus->mf_get_res_data( "amount"    ); // KCP 실제 거래 금액
			$pnt_issue = $c_PayPlus->mf_get_res_data( "pnt_issue" ); // 결제 포인트사 코드

    /* = -------------------------------------------------------------------------- = */
    /* =   05-1. 신용카드 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "100000000000" )
            {
                $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드사 코드
                $card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드사 명
                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인시간
                $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인번호
                $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부
                $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월 수

                /* = -------------------------------------------------------------- = */
                /* =   05-1.1. 복합결제(포인트+신용카드) 승인 결과 처리             = */
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
    /* =   05-4. 포인트 승인 결과 처리                                              = */
    /* = -------------------------------------------------------------------------- = */
            if ( $use_pay_method == "000100000000" )
            {
				$pt_idno      = $c_PayPlus->mf_get_res_data( "pt_idno"      ); // 결제 및 인증 아이디
                $pnt_amount   = $c_PayPlus->mf_get_res_data( "pnt_amount"   ); // 적립금액 or 사용금액
	            $pnt_app_time = $c_PayPlus->mf_get_res_data( "pnt_app_time" ); // 승인시간
	            $pnt_app_no   = $c_PayPlus->mf_get_res_data( "pnt_app_no"   ); // 승인번호 
	            $add_pnt      = $c_PayPlus->mf_get_res_data( "add_pnt"      ); // 발생 포인트
                $use_pnt      = $c_PayPlus->mf_get_res_data( "use_pnt"      ); // 사용가능 포인트
                $rsv_pnt      = $c_PayPlus->mf_get_res_data( "rsv_pnt"      ); // 총 누적 포인트
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
            $cash_authno  = $c_PayPlus->mf_get_res_data( "cash_authno"  ); // 현금 영수증 승인 번호
        }
	/* = -------------------------------------------------------------------------- = */
    /* =   05-8. 에스크로 여부 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
		$escw_yn = $c_PayPlus->mf_get_res_data( "escw_yn"  ); // 에스크로 여부 
	}

	/* = -------------------------------------------------------------------------- = */
    /* =   05. 승인 결과 처리 END                                                   = */
    /* ============================================================================== */

	/* ============================================================================== */
    /* =   06. 승인 및 실패 결과 DB처리                                             = */
    /* = -------------------------------------------------------------------------- = */
	/* =       결과를 업체 자체적으로 DB처리 작업하시는 부분입니다.                 = */
    /* = -------------------------------------------------------------------------- = */

	if ( $req_tx == "pay" )
    {

	/* = -------------------------------------------------------------------------- = */
    /* =   06-1. 승인 결과 DB 처리(res_cd == "0000")                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =        각 결제수단을 구분하시어 DB 처리를 하시기 바랍니다.                 = */
    /* = -------------------------------------------------------------------------- = */
		if( $res_cd == "0000" )
        {
			// 06-1-1. 신용카드
			if ( $use_pay_method == "100000000000" )
            {
				// 06-1-1-1. 복합결제(신용카드 + 포인트)
				if ( $pnt_issue == "SCSK" || $pnt_issue == "SCWB" )
                {
				}

                $trade_ymd = substr($app_time,0,4)."-".substr($app_time,4,2)."-".substr($app_time,6,2);
                $trade_hms = substr($app_time,8,2).":".substr($app_time,10,2).":".substr($app_time,12,2);

                // 카드내역 INSERT
                $sql = "insert $g4[yc4_card_history_table]
                           set od_id = '$ordr_idxx',
                               on_uid = '$on_uid',
                               cd_mall_id = '$site_cd',
                               cd_amount = '$good_mny',
                               cd_app_no = '$app_no',
                               cd_app_rt = '$res_cd',
                               cd_trade_ymd = '$trade_ymd',
                               cd_trade_hms = '$trade_hms',
                               cd_opt01 = '$buyr_name',
                               cd_time = NOW(),
                               cd_ip = '$cust_ip' ";
                $result = sql_query($sql, TRUE);

                if ($result) 
                {
                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_receipt_card = '$good_mny',
                                    od_card_time = NOW(),
                                    od_escrow1 = '$tno'
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    $result = sql_query($sql, TRUE);
                }
			}
			// 06-1-2. 계좌이체
			if ( $use_pay_method == "010000000000" )
            {
                $trade_ymd = date("Y-m-d", time());
                $trade_hms = date("H:i:s", time());

                // 계좌이체내역 INSERT
                $sql = "insert $g4[yc4_card_history_table]
                           set od_id = '$ordr_idxx',
                               on_uid = '$on_uid',
                               cd_mall_id = '$site_cd',
                               cd_amount = '$good_mny',
                               cd_app_no = '$tno',
                               cd_app_rt = '$res_cd',
                               cd_trade_ymd = '$trade_ymd',
                               cd_trade_hms = '$trade_hms',
                               cd_opt01 = '$buyr_name',
                               cd_time = NOW(),
                               cd_ip = '$cust_ip' ";
                $result = sql_query($sql, TRUE);

                if ($result) 
                {
                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_receipt_bank = '$good_mny',
                                    od_bank_time = NOW(),
                                    od_escrow1 = '$tno'
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    $result = sql_query($sql, TRUE);
                }
			}
			// 06-1-3. 가상계좌
			if ( $use_pay_method == "001000000000" )
            {

                if (strtolower($g4[charset]) == "utf-8") {
                    $bankname = iconv("cp949", "utf8", $bankname);
                }

                $trade_ymd = date("Y-m-d", time());
                $trade_hms = date("H:i:s", time());

                // 가상계좌내역 INSERT
                $sql = "insert $g4[yc4_card_history_table]
                           set od_id = '$ordr_idxx',
                               on_uid = '$on_uid',
                               cd_mall_id = '$site_cd',
                               cd_amount = '0',
                               cd_app_no = '$tno',
                               cd_app_rt = '$res_cd',
                               cd_trade_ymd = '$trade_ymd',
                               cd_trade_hms = '$trade_hms',
                               cd_opt01 = '$buyr_name',
                               cd_time = NOW(),
                               cd_ip = '$cust_ip' ";
                $result = sql_query($sql, TRUE);

                if ($result) 
                {
                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_bank_account = '$bankname $account',
                                    od_receipt_bank = '0',
                                    od_bank_time = '',
                                    od_escrow1 = '$tno'
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    $result = sql_query($sql, TRUE);
                }
			}
			// 06-1-4. 포인트
			if ( $use_pay_method == "000100000000" )
            {
			}
			// 06-1-5. 휴대폰
			if ( $use_pay_method == "000010000000" )
            {

                $trade_ymd = substr($app_time,0,8);
                $trade_hms = substr($app_time,8,6);

                // 휴대폰결제내역 INSERT
                $sql = "insert $g4[yc4_card_history_table]
                           set od_id = '$ordr_idxx',
                               on_uid = '$on_uid',
                               cd_mall_id = '$site_cd',
                               cd_amount = '$good_mny',
                               cd_app_no = '$tno',
                               cd_app_rt = '$res_cd',
                               cd_trade_ymd = '$trade_ymd',
                               cd_trade_hms = '$trade_hms',
                               cd_opt01 = '$buyr_name',
                               cd_opt02 = '$mobile_no $commid',
                               cd_time = NOW(),
                               cd_ip = '$cust_ip' ";
                $result = sql_query($sql, TRUE);

                if ($result) 
                {
                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_receipt_hp = '$good_mny',
                                    od_hp_time = NOW(),
                                    od_escrow1 = '$tno',
                                    od_escrow2 = '$mobile_no $commid'
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    $result = sql_query($sql, TRUE);
                }
			}
			// 06-1-6. 상품권
			 if ( $use_pay_method == "000000001000" )
            {
			}

            if ($result)
            {
                // 포인트 결제를 했다면 실제 포인트 결제한 것으로 수정합니다.
                $sql = " select od_id, on_uid, od_receipt_point, od_temp_point from $g4[yc4_order_table] where on_uid = '$on_uid' ";
                $row = sql_fetch($sql);
                if ($row[od_receipt_point] == 0 && $row[od_temp_point] != 0)
                {
                    sql_query(" update $g4[yc4_order_table] set od_receipt_point = od_temp_point where on_uid = '$on_uid' ");
                    insert_point($member[mb_id], (-1) * $row[od_temp_point], "주문번호:$row[od_id] 결제", "@order", $member[mb_id], "$row[od_id],$row[on_uid]");
                }
            }
		}
	/* = -------------------------------------------------------------------------- = */
    /* =   06.-2 승인 및 실패 결과 DB처리                                             = */
    /* ============================================================================== */
	
		else if ( $req_cd != "0000" )
		{
		}
	}
	/* = -------------------------------------------------------------------------- = */
    /* =   06. 승인 및 실패 결과 DB 처리 END                                        = */
    /* = ========================================================================== = */


	/* = ========================================================================== = */
    /* =   07. 승인 결과 DB 처리 실패시 : 자동취소                                  = */
    /* = -------------------------------------------------------------------------- = */
    /* =      승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해         = */
    /* =      DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로          = */
    /* =      승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                   = */
    /* =                                                                            = */
    /* =      DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"        = */
    /* =      로 설정해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의    = */
    /* =      값을 설정하시면 됩니다.)                                              = */
    /* = -------------------------------------------------------------------------- = */
    
	// 승인 결과 DB 처리 에러시 bSucc값을 false로 설정하여 거래건을 취소 요청
	$bSucc = ""; 

    // 쿼리가 제대로 실행되지 않았다면
    if (!$result) 
    {
        $bSucc = "false";
    }

    if ( $req_tx == "pay" )
    {
		if( $res_cd == "0000" )
        {	
			if ( $bSucc == "false" )
            {
                $c_PayPlus->mf_clear();

                $tran_cd = "00200000";

	/* ============================================================================== */
    /* =   07-1.자동취소시 에스크로 거래인 경우                                     = */
    /* = -------------------------------------------------------------------------- = */
				// 취소시 사용하는 mod_type
                $bSucc_mod_type = "";

                // 에스크로 가상계좌 건의 경우 가상계좌 발급취소(STE5)
                if ( $escw_yn == "Y" && $use_pay_method == "001000000000" )
				{
                    $bSucc_mod_type = "STE5";
				}
                // 에스크로 가상계좌 이외 건은 즉시취소(STE2)
                else if ( $escw_yn == "Y" )
				{
                    $bSucc_mod_type = "STE2";
				}
                // 에스크로 거래 건이 아닌 경우(일반건)(STSC)
                else
				{
                    $bSucc_mod_type = "STSC"; 
				}
	/* = -------------------------------------------------------------------------- = */
	/* =   07-1. 자동취소시 에스크로 거래인 경우 처리 END                           = */
    /* = ========================================================================== = */
                
                $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
                $c_PayPlus->mf_set_modx_data( "mod_type", $bSucc_mod_type              );  // 원거래 변경 요청 종류
                $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
                $c_PayPlus->mf_set_modx_data( "mod_desc", "가맹점 결과 처리 오류 - 가맹점에서 취소 요청" );  // 변경 사유

                $c_PayPlus->mf_do_tx( $tno,  $g_conf_home_dir, $g_conf_site_cd,
                                      "",  $tran_cd,    "",
                                      $g_conf_gw_url,  $g_conf_gw_port,  "payplus_cli_slib",
                                      $ordr_idxx, $cust_ip, "3" ,
                                      0, 0, $g_conf_key_dir, $g_conf_log_dir);

                $res_cd  = $c_PayPlus->m_res_cd;
                $res_msg = $c_PayPlus->m_res_msg;


            }
        }
	}
		// End of [res_cd = "0000"]
	/* = -------------------------------------------------------------------------- = */
    /* =   07. 승인 결과 DB 처리 END                                                = */
    /* = ========================================================================== = */


    /* ============================================================================== */
    /* =   08. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */


    if (strtolower($g4[charset]) == "utf-8") 
    {
        $res_msg = iconv("euc-kr", "utf-8", $res_msg);
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
    <head>
		<title>*** KCP [AX-HUB Version] ***</title>
        <script type="text/javascript">
            function goResult()
            {
                var openwin = window.open( 'proc_win.php', 'proc_win', '' )
                document.pay_info.submit()
                openwin.close()
            }

            // 결제 중 새로고침 방지 샘플 스크립트 (중복결제 방지)
            function noRefresh()
            {
                /* CTRL + N키 막음. */
                if ((event.keyCode == 78) && (event.ctrlKey == true))
                {
                    event.keyCode = 0;
                    return false;
                }
                /* F5 번키 막음. */
                if(event.keyCode == 116)
                {
                    event.keyCode = 0;
                    return false;
                }
            }
            document.onkeydown = noRefresh ;
        </script>
    </head>

    <body onload="goResult()">
    <form name="pay_info" method="post" action="../settleresult.php?on_uid=<?=$on_uid?>">
        <input type="hidden" name="site_cd"           value="<?=$g_conf_site_cd	?>">     <!-- 사이트코드 -->
		<input type="hidden" name="req_tx"            value="<?=$req_tx			?>">     <!-- 요청 구분 -->
        <input type="hidden" name="use_pay_method"    value="<?=$use_pay_method ?>">     <!-- 사용한 결제 수단 -->
        <input type="hidden" name="bSucc"             value="<?=$bSucc			?>">     <!-- 쇼핑몰 DB 처리 성공 여부 -->

        <input type="hidden" name="res_cd"            value="<?=$res_cd			?>">     <!-- 결과 코드 -->
        <input type="hidden" name="res_msg"           value="<?=$res_msg		?>">     <!-- 결과 메세지 -->
        <input type="hidden" name="ordr_idxx"         value="<?=$ordr_idxx		?>">     <!-- 주문번호 -->
        <input type="hidden" name="tno"               value="<?=$tno			?>">     <!-- KCP 거래번호 -->
        <input type="hidden" name="good_mny"          value="<?=$good_mny		?>">     <!-- 결제금액 -->
        <input type="hidden" name="good_name"         value="<?=$good_name		?>">     <!-- 상품명 -->
        <input type="hidden" name="buyr_name"         value="<?=$buyr_name		?>">     <!-- 주문자명 -->
        <input type="hidden" name="buyr_tel1"         value="<?=$buyr_tel1		?>">     <!-- 주문자 전화번호 -->
        <input type="hidden" name="buyr_tel2"         value="<?=$buyr_tel2		?>">     <!-- 주문자 휴대폰번호 -->
        <input type="hidden" name="buyr_mail"         value="<?=$buyr_mail		?>">     <!-- 주문자 E-mail -->

        <input type="hidden" name="app_time"          value="<?=$app_time		?>">     <!-- 승인시간 -->
        <!-- 신용카드 정보 -->
		<input type="hidden" name="card_cd"           value="<?=$card_cd		?>">     <!-- 카드코드 -->
        <input type="hidden" name="card_name"         value="<?=$card_name		?>">     <!-- 카드명 -->
        <input type="hidden" name="app_no"            value="<?=$app_no			?>">     <!-- 승인번호 -->
		<input type="hidden" name="noinf"             value="<?=$noinf			?>">     <!-- 무이자여부 -->
		<input type="hidden" name="quota"             value="<?=$quota			?>">     <!-- 할부개월 -->
        <!-- 계좌이체 정보 -->
        <input type="hidden" name="bank_code"         value="<?=$bank_code		?>">     <!-- 은행코드 -->
		<input type="hidden" name="bank_name"         value="<?=$bank_name		?>">     <!-- 은행명 -->
        <!-- 가상계좌 정보 -->
        <input type="hidden" name="bankname"          value="<?=$bankname		?>">     <!-- 입금할 은행 -->
        <input type="hidden" name="depositor"         value="<?=$depositor		?>">     <!-- 입금할 계좌 예금주 -->
        <input type="hidden" name="account"           value="<?=$account		?>">     <!-- 입금할 계좌 번호 -->
        <input type="hidden" name="va_date"           value="<?=$va_date		?>">     <!-- 가상계좌 입금마감시간 -->
        <!-- 포인트 정보 -->
        <input type="hidden" name="pnt_issue"         value="<?=$pnt_issue		?>">     <!-- 포인트 서비스사 -->
        <input type="hidden" name="pt_idno"		      value="<?=$pt_idno		?>">     <!-- 결제 및 인증 아이디 -->
        <input type="hidden" name="pnt_amount"        value="<?=$pnt_amount		?>">     <!-- 적립금액 or 사용금액 -->
		<input type="hidden" name="pnt_app_time"      value="<?=$pnt_app_time	?>">     <!-- 승인시간 -->
        <input type="hidden" name="pnt_app_no"        value="<?=$pnt_app_no		?>">     <!-- 승인번호 -->
        <input type="hidden" name="add_pnt"           value="<?=$add_pnt		?>">     <!-- 발생 포인트 -->
        <input type="hidden" name="use_pnt"           value="<?=$use_pnt		?>">     <!-- 사용가능 포인트 -->
        <input type="hidden" name="rsv_pnt"           value="<?=$rsv_pnt		?>">     <!-- 총 누적 포인트 -->
    
        <!-- 휴대폰 정보 -->
		<input type="hidden" name="commid"            value="<?=$commid			?>">     <!-- 통신사 코드 -->
		<input type="hidden" name="mobile_no"         value="<?=$mobile_no		?>">     <!-- 휴대폰 번호 -->
        <!-- 상품권 정보 -->
		<input type="hidden" name="tk_van_code"       value="<?=$tk_van_code	?>">     <!-- 발급사 코드 -->
		<input type="hidden" name="tk_app_no"         value="<?=$tk_app_no		?>">     <!-- 승인 번호 -->
        <!-- 현금영수증 정보 -->
        <input type="hidden" name="cash_yn"           value="<?=$cash_yn		?>">     <!-- 현금 영수증 등록 여부 -->
        <input type="hidden" name="cash_authno"       value="<?=$cash_authno	?>">     <!-- 현금 영수증 승인 번호 -->
        <input type="hidden" name="cash_tr_code"      value="<?=$cash_tr_code	?>">     <!-- 현금 영수증 발행 구분 -->
        <input type="hidden" name="cash_id_info"      value="<?=$cash_id_info	?>">     <!-- 현금 영수증 등록 번호 -->

		<!-- 에스크로 정보 -->                                           
        <input type="hidden" name="escw_yn"			  value="<?= $escw_yn		?>">     <!-- 에스크로 유무 -->
        <input type="hidden" name="deli_term"	  	  value="<?= $deli_term		?>">     <!-- 배송 소요일 -->
        <input type="hidden" name="bask_cntx"		  value="<?= $bask_cntx		?>">     <!-- 장바구니 상품 개수 -->
        <input type="hidden" name="good_info"		  value="<?= $good_info		?>">     <!-- 장바구니 상품 상세 정보 -->
        <input type="hidden" name="rcvr_name"		  value="<?= $rcvr_name		?>">     <!-- 수취인 이름 -->
        <input type="hidden" name="rcvr_tel1"		  value="<?= $rcvr_tel1		?>">     <!-- 수취인 전화번호 -->
        <input type="hidden" name="rcvr_tel2"		  value="<?= $rcvr_tel2		?>">     <!-- 수취인 휴대폰번호 -->
        <input type="hidden" name="rcvr_mail"		  value="<?= $rcvr_mail		?>">     <!-- 수취인 E-Mail -->
        <input type="hidden" name="rcvr_zipx"		  value="<?= $rcvr_zipx		?>">     <!-- 수취인 우편번호 -->
        <input type="hidden" name="rcvr_add1"		  value="<?= $rcvr_add1		?>">     <!-- 수취인 주소 -->
        <input type="hidden" name="rcvr_add2"		  value="<?= $rcvr_add2		?>">     <!-- 수취인 상세주소 -->
    </form>
    </body>
</html>
