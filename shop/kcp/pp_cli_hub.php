<?php
include_once './_common.php';
include_once G5_LIB_PATH.'/etc.lib.php';

if($tx == 'personalpay')
    $sql = " select count(*) as cnt from {$g5['g5_shop_personalpay_table']} where pp_id = '{$_POST['ordr_idxx']}' and pp_cash = 1 ";
else
    $sql = " select count(*) as cnt from {$g5['g5_shop_order_table']} where od_id = '{$_POST['ordr_idxx']}' and od_cash = 1 ";

$row = sql_fetch($sql);
if ($row['cnt']) {
    alert('이미 등록된 현금영수증 입니다.');
}

// locale ko_KR.euc-kr 로 설정
setlocale(LC_CTYPE, 'ko_KR.euc-kr');

    //write_log("$g5[path]/data/log/cash.log", $_POST);

    /* ============================================================================== */
    /* =   PAGE : 등록/변경 처리 PAGE                                               = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2007   KCP Inc.   All Rights Reserverd.                   = */
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* = 라이브러리 및 사이트 정보 include                                          = */
    /* = -------------------------------------------------------------------------- = */
    include_once(G5_SHOP_PATH.'/settle_kcp.inc.php');
    require_once(G5_SHOP_PATH.'/kcp/pp_cli_hub_lib.php');

    /* ============================================================================== */
    /* =   01. KCP 지불 서버 정보 설정                                              = */
    /* = -------------------------------------------------------------------------- = */
    if ($default['de_card_test']) {
        $g_conf_pa_url    = "testpaygw.kcp.co.kr"; // ※ 테스트: testpaygw.kcp.co.kr, 리얼: paygw.kcp.co.kr
        $g_conf_pa_port   = "8090";                // ※ 테스트: 8090,                리얼: 8090
    }
    else {
        $g_conf_pa_url    = "paygw.kcp.co.kr";
        $g_conf_pa_port   = "8090";
    }

    $g_conf_tx_mode   = 0;
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 쇼핑몰 지불 정보 설정                                                = */
    /* = -------------------------------------------------------------------------- = */
    // ※ V6 가맹점의 경우
    $g_conf_user_type = "PGNW";  // 변경 불가
    //$g_conf_site_id   = $default[de_kcp_mid]; // 리얼 반영시 KCP에 발급된 site_cd 사용 ex) T0000
    $g_conf_site_id   = strlen($default['de_kcp_mid']) == 3 ? "SR".$default['de_kcp_mid'] : $default['de_kcp_mid']; // 리얼 반영시 KCP에 발급된 site_cd 사용 ex) T0000
    $g_conf_site_key = $default['de_kcp_site_key'];
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   01. 요청 정보 설정                                                       = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx     = isset($_POST["req_tx"]) ? $_POST["req_tx"] : '';                             // 요청 종류
    $trad_time  = isset($_POST["trad_time"]) ? $_POST["trad_time"] : '';                             // 원거래 시각
    /* = -------------------------------------------------------------------------- = */
    $ordr_idxx  = isset($_POST["ordr_idxx"]) ? preg_replace('/[^0-9A-Za-z_\-\.]/i', '', $_POST[ "ordr_idxx"  ]) : '';                             // 주문 번호
    $buyr_name  = isset($_POST["buyr_name"]) ? $_POST["buyr_name"] : '';                             // 주문자 이름
    $buyr_tel1  = isset($_POST["buyr_tel1"]) ? $_POST["buyr_tel1"] : '';                             // 주문자 전화번호
    $buyr_mail  = isset($_POST["buyr_mail"]) ? $_POST["buyr_mail"] : '';                             // 주문자 E-Mail
    $good_name  = isset($_POST["good_name"]) ? $_POST["good_name"] : '';                             // 상품 정보
    $comment    = isset($_POST["comment"]) ? $_POST["comment"] : '';                             // 비고
    /* = -------------------------------------------------------------------------- = */
    $corp_type     = isset($_POST["corp_type"]) ? $_POST["corp_type"] : '';                      // 사업장 구분
    $corp_tax_type = isset($_POST["corp_tax_type"]) ? $_POST["corp_tax_type"] : '';                      // 과세/면세 구분
    $corp_tax_no   = isset($_POST["corp_tax_no"]) ? $_POST["corp_tax_no"] : '';                      // 발행 사업자 번호
    $corp_nm       = isset($_POST["corp_nm"]) ? $_POST["corp_nm"] : '';                      // 상호
    $corp_owner_nm = isset($_POST["corp_owner_nm"]) ? $_POST["corp_owner_nm"] : '';                      // 대표자명
    $corp_addr     = isset($_POST["corp_addr"]) ? $_POST["corp_addr"] : '';                      // 사업장 주소
    $corp_telno    = isset($_POST["corp_telno"]) ? $_POST["corp_telno"] : '';                      // 사업장 대표 연락처
    /* = -------------------------------------------------------------------------- = */
    $tr_code    = isset($_POST["tr_code"]) ? $_POST["tr_code"] : '';                             // 발행용도
    $id_info    = isset($_POST["id_info"]) ? $_POST["id_info"] : '';                             // 신분확인 ID
    $amt_tot    = isset($_POST["amt_tot"]) ? $_POST["amt_tot"] : '';                             // 거래금액 총 합
    $amt_sup    = isset($_POST["amt_sup"]) ? $_POST["amt_sup"] : '';                             // 공급가액
    $amt_svc    = isset($_POST["amt_svc"]) ? $_POST["amt_svc"] : '';                             // 봉사료
    $amt_tax    = isset($_POST["amt_tax"]) ? $_POST["amt_tax"] : '';                             // 부가가치세
    /* = -------------------------------------------------------------------------- = */
    $mod_type   = isset($_POST["mod_type"]) ? $_POST["mod_type"] : '';                             // 변경 타입
    $mod_value  = isset($_POST["mod_value"]) ? $_POST["mod_value"] : '';                             // 변경 요청 거래번호
    $mod_gubn   = isset($_POST["mod_gubn"]) ? $_POST["mod_gubn"] : '';                             // 변경 요청 거래번호 구분
    $mod_mny    = isset($_POST["mod_mny"]) ? $_POST["mod_mny"] : '';                             // 변경 요청 금액
    $rem_mny    = isset($_POST["rem_mny"]) ? $_POST["rem_mny"] : '';                             // 변경처리 이전 금액
    /* = -------------------------------------------------------------------------- = */
    $cust_ip    = getenv( "REMOTE_ADDR" );                            // 요청 IP
    /* ============================================================================== */

    $buyr_name = iconv("utf-8", "cp949", $buyr_name);
    $good_name = iconv("utf-8", "cp949", $good_name);
    $tx_cd = '';

    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화                                              = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus  = new C_PAYPLUS_CLI_T;
    $c_PayPlus->mf_clear();
    /* ============================================================================== */

    $rcpt_data_set = $corp_data_set = null;
    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정, 실행                                            = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 승인 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        // 업체 환경 정보
        if ( $req_tx == "pay" )
        {
            $tx_cd = "07010000"; // 현금영수증 등록 요청

            // 현금영수증 정보
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "user_type",      $g_conf_user_type );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "trad_time",      $trad_time        );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "tr_code",        $tr_code          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "id_info",        $id_info          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_tot",        $amt_tot          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_sup",        $amt_sup          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_svc",        $amt_svc          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "amt_tax",        $amt_tax          );
            $rcpt_data_set .= $c_PayPlus->mf_set_data_us( "pay_type",       "PAXX"            ); // 선 결제 서비스 구분(PABK - 계좌이체, PAVC - 가상계좌, PAXX - 기타)
            //$rcpt_data_set .= $c_PayPlus->mf_set_data_us( "pay_trade_no",   $pay_trade_no ); // 결제 거래번호(PABK, PAVC일 경우 필수)
            //$rcpt_data_set .= $c_PayPlus->mf_set_data_us( "pay_tx_id",      $pay_tx_id    ); // 가상계좌 입금통보 TX_ID(PAVC일 경우 필수)

            // 주문 정보
            $c_PayPlus->mf_set_ordr_data( "ordr_idxx",  $ordr_idxx );
            $c_PayPlus->mf_set_ordr_data( "good_name",  $good_name );
            $c_PayPlus->mf_set_ordr_data( "buyr_name",  $buyr_name );
            $c_PayPlus->mf_set_ordr_data( "buyr_tel1",  $buyr_tel1 );
            $c_PayPlus->mf_set_ordr_data( "buyr_mail",  $buyr_mail );
            $c_PayPlus->mf_set_ordr_data( "comment",    $comment   );

            // 가맹점 정보
            $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_type",       $corp_type     );

            if ( $corp_type == "1" ) // 입점몰인 경우 판매상점 DATA 전문 생성
            {
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_tax_type",   $corp_tax_type );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_tax_no",     $corp_tax_no   );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_sell_tax_no",$corp_tax_no   );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_nm",         $corp_nm       );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_owner_nm",   $corp_owner_nm );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_addr",       $corp_addr     );
                $corp_data_set .= $c_PayPlus->mf_set_data_us( "corp_telno",      $corp_telno    );
            }

            $c_PayPlus->mf_set_ordr_data( "rcpt_data", $rcpt_data_set );
            $c_PayPlus->mf_set_ordr_data( "corp_data", $corp_data_set );
        }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-2. 취소 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            if ( $mod_type == "STSQ" )
            {
                $tx_cd = "07030000"; // 조회 요청
            }
            else
            {
                $tx_cd = "07020000"; // 취소 요청
            }

            $c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type   );      // 원거래 변경 요청 종류
            $c_PayPlus->mf_set_modx_data( "mod_value",  $mod_value  );
            $c_PayPlus->mf_set_modx_data( "mod_gubn",   $mod_gubn   );
            $c_PayPlus->mf_set_modx_data( "trad_time",  $trad_time  );

            if ( $mod_type == "STPC" ) // 부분취소
            {
                $c_PayPlus->mf_set_modx_data( "mod_mny",  $mod_mny  );
                $c_PayPlus->mf_set_modx_data( "rem_mny",  $rem_mny  );
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03-3. 실행                                                               = */
    /* ------------------------------------------------------------------------------ */
        if ( strlen($tx_cd) > 0 )
        {
            $c_PayPlus->mf_do_tx( "",                $g_conf_home_dir, $g_conf_site_id,
                                  $g_conf_site_key,  $tx_cd,           "",
                                  $g_conf_pa_url,    $g_conf_pa_port,  "payplus_cli_slib",
                                  $ordr_idxx,        $cust_ip,         $g_conf_log_level,
                                  "",                $g_conf_tx_mode,  $g_conf_key_dir, $g_conf_log_dir );
        }
        else
        {
            $c_PayPlus->m_res_cd  = "9562";
            $c_PayPlus->m_res_msg = "연동 오류";
        }
        $res_cd  = $c_PayPlus->m_res_cd;                      // 결과 코드
        $res_msg = $c_PayPlus->m_res_msg;                     // 결과 메시지
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. 승인 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        if ( $req_tx == "pay" )
        {
            if ( $res_cd == "0000" )
            {
                $cash_no    = $c_PayPlus->mf_get_res_data( "cash_no"    );       // 현금영수증 거래번호
                $receipt_no = $c_PayPlus->mf_get_res_data( "receipt_no" );       // 현금영수증 승인번호
                $app_time   = $c_PayPlus->mf_get_res_data( "app_time"   );       // 승인시간(YYYYMMDDhhmmss)
                $reg_stat   = $c_PayPlus->mf_get_res_data( "reg_stat"   );       // 등록 상태 코드
                $reg_desc   = $c_PayPlus->mf_get_res_data( "reg_desc"   );       // 등록 상태 설명

    /* = -------------------------------------------------------------------------- = */
    /* =   04-1. 승인 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
    /* =         승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해      = */
    /* =         DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로       = */
    /* =         승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                = */
    /* =         DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"     = */
    /* =         로 세팅해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의 = */
    /* =         값을 세팅하시면 됩니다.)                                           = */
    /* = -------------------------------------------------------------------------- = */
                $bSucc = "";             // DB 작업 실패일 경우 "false" 로 세팅

                // 결과값 serialize
                $cash = array();
                $cash['receipt_no'] = $receipt_no;
                $cash['app_time']   = $app_time;
                $cash['reg_stat']   = $reg_stat;
                $cash['reg_desc']   = iconv("cp949", "utf-8", $reg_desc);
                $cash['tr_code']    = $tr_code;
                $cash['id_info']    = $id_info;
                $cash_info = serialize($cash);

                if($tx == 'personalpay') {
                    $sql = " update {$g5['g5_shop_personalpay_table']}
                                set pp_cash = '1',
                                    pp_cash_no = '$cash_no',
                                    pp_cash_info = '$cash_info'
                              where pp_id = '$ordr_idxx' ";
                } else {
                    $sql = " update {$g5['g5_shop_order_table']}
                                set od_cash = '1',
                                    od_cash_no = '$cash_no',
                                    od_cash_info = '$cash_info'
                              where od_id = '$ordr_idxx' ";
                }

                $result = sql_query($sql, false);
                if (!$result) $bSucc = "false";

    /* = -------------------------------------------------------------------------- = */
    /* =   04-2. DB 작업 실패일 경우 자동 승인 취소                                 = */
    /* = -------------------------------------------------------------------------- = */
                if ( $bSucc == "false" )
                {
                    $c_PayPlus->mf_clear();

                    $tx_cd = "07020000"; // 취소 요청

                    $c_PayPlus->mf_set_modx_data( "mod_type",  "STSC"     );                    // 원거래 변경 요청 종류
                    $c_PayPlus->mf_set_modx_data( "mod_value", $cash_no   );
                    $c_PayPlus->mf_set_modx_data( "mod_gubn",  "MG01"     );
                    $c_PayPlus->mf_set_modx_data( "trad_time", $trad_time );

                    $c_PayPlus->mf_do_tx( "",                $g_conf_home_dir, $g_conf_site_id,
                                          $g_conf_site_key,  $tx_cd,           "",
                                          $g_conf_pa_url,    $g_conf_pa_port,  "payplus_cli_slib",
                                          $ordr_idxx,        $cust_ip,         $g_conf_log_level,
                                          "",                $g_conf_tx_mode );

                    $res_cd  = $c_PayPlus->m_res_cd;
                    $res_msg = $c_PayPlus->m_res_msg;
                }

            }    // End of [res_cd = "0000"]

    /* = -------------------------------------------------------------------------- = */
    /* =   04-3. 등록 실패를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
            else
            {
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   05. 변경 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            if ( $res_cd == "0000" )
            {
                $cash_no    = $c_PayPlus->mf_get_res_data( "cash_no"    );       // 현금영수증 거래번호
                $receipt_no = $c_PayPlus->mf_get_res_data( "receipt_no" );       // 현금영수증 승인번호
                $app_time   = $c_PayPlus->mf_get_res_data( "app_time"   );       // 승인시간(YYYYMMDDhhmmss)
                $reg_stat   = $c_PayPlus->mf_get_res_data( "reg_stat"   );       // 등록 상태 코드
                $reg_desc   = $c_PayPlus->mf_get_res_data( "reg_desc"   );       // 등록 상태 설명
            }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-1. 변경 실패를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
            else
            {
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   06. 인스턴스 CleanUp                                                     = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus->mf_clear();
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   07. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */
?>

    <html>
    <head>
    <script language = 'javascript'>
        function goResult()
        {
            document.pay_info.submit();
        }
    </script>
    </head>
    <body onload="goResult();">
    <form name="pay_info" method="post" action="./pp_cli_result.php">
        <input type="hidden" name="req_tx"            value="<?php echo $req_tx; ?>">            <!-- 요청 구분 -->
        <input type="hidden" name="bSucc"             value="<?php echo $bSucc; ?>">             <!-- 쇼핑몰 DB 처리 성공 여부 -->

        <input type="hidden" name="res_cd"            value="<?php echo $res_cd; ?>">            <!-- 결과 코드 -->
        <input type="hidden" name="res_msg"           value="<?php echo iconv("cp949", "utf-8", $res_msg); ?>"> <!-- 결과 메세지 -->
        <input type="hidden" name="ordr_idxx"         value="<?php echo $ordr_idxx; ?>">         <!-- 주문번호 -->
        <input type="hidden" name="good_name"         value="<?php echo $good_name; ?>">         <!-- 상품명 -->
        <input type="hidden" name="buyr_name"         value="<?php echo $buyr_name; ?>">         <!-- 주문자명 -->
        <input type="hidden" name="buyr_tel1"         value="<?php echo $buyr_tel1; ?>">         <!-- 주문자 전화번호 -->
        <input type="hidden" name="buyr_mail"         value="<?php echo $buyr_mail; ?>">         <!-- 주문자 E-mail -->
        <input type="hidden" name="comment"           value="<?php echo $comment; ?>">           <!-- 비고 -->

        <input type="hidden" name="corp_type"         value="<?php echo $corp_type; ?>">         <!-- 사업장 구분 -->
        <input type="hidden" name="corp_tax_type"     value="<?php echo $corp_tax_type; ?>">     <!-- 과세/면세 구분 -->
        <input type="hidden" name="corp_tax_no"       value="<?php echo $corp_tax_no; ?>">       <!-- 발행 사업자 번호 -->
        <input type="hidden" name="corp_nm"           value="<?php echo $corp_nm; ?>">           <!-- 상호 -->
        <input type="hidden" name="corp_owner_nm"     value="<?php echo $corp_owner_nm; ?>">     <!-- 대표자명 -->
        <input type="hidden" name="corp_addr"         value="<?php echo $corp_addr; ?>">         <!-- 사업장주소 -->
        <input type="hidden" name="corp_telno"        value="<?php echo $corp_telno; ?>">        <!-- 사업장 대표 연락처 -->

        <input type="hidden" name="tr_code"           value="<?php echo $tr_code; ?>">           <!-- 발행용도 -->
        <input type="hidden" name="id_info"           value="<?php echo $id_info; ?>">           <!-- 신분확인 ID -->
        <input type="hidden" name="amt_tot"           value="<?php echo $amt_tot; ?>">           <!-- 거래금액 총 합 -->
        <input type="hidden" name="amt_sub"           value="<?php echo $amt_sup; ?>">           <!-- 공급가액 -->
        <input type="hidden" name="amt_svc"           value="<?php echo $amt_svc; ?>">           <!-- 봉사료 -->
        <input type="hidden" name="amt_tax"           value="<?php echo $amt_tax; ?>">           <!-- 부가가치세 -->
        <input type="hidden" name="pay_type"          value="<?php echo $pay_type; ?>">          <!-- 결제 서비스 구분 -->
        <input type="hidden" name="pay_trade_no"      value="<?php echo $pay_trade_no; ?>">      <!-- 결제 거래번호 -->

        <input type="hidden" name="mod_type"          value="<?php echo $mod_type; ?>">          <!-- 변경 타입 -->
        <input type="hidden" name="mod_value"         value="<?php echo $mod_value; ?>">         <!-- 변경 요청 거래번호 -->
        <input type="hidden" name="mod_gubn"          value="<?php echo $mod_gubn; ?>">          <!-- 변경 요청 거래번호 구분 -->
        <input type="hidden" name="mod_mny"           value="<?php echo $mod_mny; ?>">           <!-- 변경 요청 금액 -->
        <input type="hidden" name="rem_mny"           value="<?php echo $rem_mny; ?>">           <!-- 변경처리 이전 금액 -->

        <input type="hidden" name="cash_no"           value="<?php echo $cash_no; ?>">           <!-- 현금영수증 거래번호 -->
        <input type="hidden" name="receipt_no"        value="<?php echo $receipt_no; ?>">        <!-- 현금영수증 승인번호 -->
        <input type="hidden" name="app_time"          value="<?php echo $app_time; ?>">          <!-- 승인시간(YYYYMMDDhhmmss) -->
        <input type="hidden" name="reg_stat"          value="<?php echo $reg_stat; ?>">          <!-- 등록 상태 코드 -->
        <input type="hidden" name="reg_desc"          value="<?php echo iconv("cp949", "utf-8", $reg_desc); ?>"> <!-- 등록 상태 설명 -->

    </form>
    </body>
    </html>

<?php
// locale 설정 초기화
setlocale(LC_CTYPE, '');