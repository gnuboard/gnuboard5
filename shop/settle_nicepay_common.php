<?php
include_once('./_common.php');

if (function_exists('add_log')) add_log($_POST, false, 'vv');

$NICEPAY_log_path = G5_DATA_PATH.'/log'; // 나이스페이 가상계좌 로그저장 경로
$NICEPAY_payLog  = false;                  // 로그를 기록하려면 true 로 수정

//**********************************************************************************

$pg_allow_ips = array(
    '121.133.126.10',
    '121.133.126.11',
    '211.33.136.39'
);

// PG 사에서 보냈는지 IP로 체크
if (in_array($_SERVER['REMOTE_ADDR'], $pg_allow_ips)) {

    $PayMethod      = isset($_POST['PayMethod']) ? clean_xss_tags($_POST['PayMethod']) : '';           //지불수단
    $M_ID           = isset($_POST['MID']) ? clean_xss_tags($_POST['MID']) : '';                 //상점ID
    $MallUserID     = isset($_POST['MallUserID']) ? clean_xss_tags($_POST['MallUserID']) : '';          //회원사 ID
    $Amt            = isset($_POST['Amt']) ? (int) $_POST['Amt'] : 0;                 //금액
    $name           = isset($_POST['name']) ? clean_xss_tags($_POST['name']) : '';                //구매자명
    $GoodsName      = isset($_POST['GoodsName']) ? clean_xss_tags($_POST['GoodsName']) : '';           //상품명
    $TID            = isset($_POST['TID']) ? clean_xss_tags($_POST['TID']) : '';                 //거래번호
    $MOID           = isset($_POST['MOID']) ? clean_xss_tags($_POST['MOID']) : '';                //주문번호
    $AuthDate       = isset($_POST['AuthDate']) ? clean_xss_tags($_POST['AuthDate']) : '';            //입금일시 (yyMMddHHmmss) 12 자리
    $ResultCode     = isset($_POST['ResultCode']) ? clean_xss_tags($_POST['ResultCode']) : '';          //결과코드 ('4110' 경우 입금통보)
    $ResultMsg      = isset($_POST['ResultMsg']) ? clean_xss_tags($_POST['ResultMsg']) : '';           //결과메시지
    $VbankNum       = isset($_POST['VbankNum']) ? clean_xss_tags($_POST['VbankNum']) : '';            //가상계좌번호
    $FnCd           = isset($_POST['FnCd']) ? clean_xss_tags($_POST['FnCd']) : '';                //가상계좌 은행코드
    $VbankName      = isset($_POST['VbankName']) ? clean_xss_tags($_POST['VbankName']) : '';           //가상계좌 은행명
    $VbankInputName = isset($_POST['VbankInputName']) ? clean_xss_tags($_POST['VbankInputName']) : '';      //입금자 명
    $CancelDate     = isset($_POST['CancelDate']) ? clean_xss_tags($_POST['CancelDate']) : '';          //취소일시
    
    //가상계좌채번시 현금영수증 자동발급신청이 되었을경우 전달되며 
    //RcptTID 에 값이 있는경우만 발급처리 됨
    $RcptTID        = isset($_POST['RcptTID']) ? clean_xss_tags($_POST['RcptTID']) : '';             //현금영수증 거래번호
    $RcptType       = isset($_POST['RcptType']) ? clean_xss_tags($_POST['RcptType']) : '';            //현금 영수증 구분(0:미발행, 1:소득공제용, 2:지출증빙용)
    $RcptAuthCode   = isset($_POST['RcptAuthCode']) ? clean_xss_tags($_POST['RcptAuthCode']) : '';        //현금영수증 승인번호
    
    // 입금통보 코드가 4110 성공이면
    if ($ResultCode === '4110') {
        // 입금결과 처리
        $sql = " select pp_id, od_id from {$g5['g5_shop_personalpay_table']} where pp_id = '$MOID' and pp_app_no = '$VbankNum' ";
        $row = sql_fetch($sql);

        $result = false;
        $receipt_time = preg_replace("/([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $AuthDate);

        if($row['pp_id']) {
            // 개인결제 UPDATE
            $sql = " update {$g5['g5_shop_personalpay_table']}
                        set pp_receipt_price    = '$Amt',
                            pp_receipt_time     = '$receipt_time'
                        where pp_id = '$MOID'
                            and pp_app_no = '$VbankNum' ";
            $result = sql_query($sql, false);

            if($row['od_id']) {
                // 주문서 UPDATE
                $sql = " update {$g5['g5_shop_order_table']}
                            set od_receipt_price = od_receipt_price + '$Amt',
                                od_receipt_time = '$receipt_time',
                                od_shop_memo = concat(od_shop_memo, \"\\n개인결제 ".$row['pp_id']." 로 결제완료 - ".$receipt_time."\")
                            where od_id = '{$row['od_id']}' ";
                $result = sql_query($sql, FALSE);
            }
        } else {
            // 주문서 UPDATE
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_receipt_price = '$Amt',
                            od_receipt_time = '$receipt_time'
                        where od_id = '$MOID'
                        and od_app_no = '$VbankNum' ";
            $result = sql_query($sql, FALSE);
        }

        if($result) {
            if ($row['od_id'])
                $od_id = $row['od_id'];
            else
                $od_id = $MOID;

            // 주문정보 체크
            $sql = " select count(od_id) as cnt
                        from {$g5['g5_shop_order_table']}
                        where od_id = '$od_id'
                            and od_status = '주문' ";
            $row = sql_fetch($sql);

            if($row['cnt'] == 1) {
                // 미수금 정보 업데이트
                $info = get_order_info($od_id);
                
                $add_update_sql = '';

                // 현금영수증 발급시 1 또는 2 이면
                if ($RcptType) {
                    $add_update_sql = "
                    , od_cash           = '1',
                    od_cash_no        = '".$RcptAuthCode."',
                    od_cash_info      = '".serialize(array('TID'=>$RcptTID, 'ApplNum'=>$RcptAuthCode, 'AuthDate'=>$AuthDate))."'
                    ";
                }

                $sql = " update {$g5['g5_shop_order_table']}
                            set od_misu = '{$info['od_misu']}' $add_update_sql ";
                if($info['od_misu'] == 0)
                    $sql .= " , od_status = '입금' ";
                $sql .= " where od_id = '$od_id' ";
                sql_query($sql, FALSE);

                // 장바구니 상태변경
                if($info['od_misu'] == 0) {
                    $sql = " update {$g5['g5_shop_cart_table']}
                                set ct_status = '입금'
                                where od_id = '$od_id' ";
                    sql_query($sql, FALSE);
                }

            }
        }

        if($NICEPAY_payLog) {
            $logfile = fopen( $NICEPAY_log_path . "/nice_vacct_noti_result.log", "a+" );

            fwrite( $logfile,"************************************************\r\n");
            fwrite( $logfile,"PayMethod     : ".$PayMethod."\r\n");
            fwrite( $logfile,"MID           : ".$MID."\r\n");
            fwrite( $logfile,"MallUserID    : ".$MallUserID."\r\n");
            fwrite( $logfile,"Amt           : ".$Amt."\r\n");
            fwrite( $logfile,"name          : ".$name."\r\n");
            fwrite( $logfile,"GoodsName     : ".$GoodsName."\r\n");
            fwrite( $logfile,"TID           : ".$TID."\r\n");
            fwrite( $logfile,"MOID          : ".$MOID."\r\n");
            fwrite( $logfile,"AuthDate      : ".$AuthDate."\r\n");
            fwrite( $logfile,"ResultCode    : ".$ResultCode."\r\n");
            fwrite( $logfile,"ResultMsg     : ".$ResultMsg."\r\n");
            fwrite( $logfile,"VbankNum      : ".$VbankNum."\r\n");
            fwrite( $logfile,"FnCd          : ".$FnCd."\r\n");
            fwrite( $logfile,"VbankName     : ".$VbankName."\r\n");
            fwrite( $logfile,"VbankInputName : ".$VbankInputName."\r\n");
            fwrite( $logfile,"RcptTID       : ".$RcptTID."\r\n");
            fwrite( $logfile,"RcptType      : ".$RcptType."\r\n");
            fwrite( $logfile,"RcptAuthCode  : ".$RcptAuthCode."\r\n");
            fwrite( $logfile,"CancelDate    : ".$CancelDate."\r\n");
            fwrite( $logfile,"************************************************\r\n");

            fclose( $logfile );
        }


        //************************************************************************************

        //위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "OK"를 이니시스로
        //리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
        //(주의) OK를 리턴하지 않으시면 이니시스 지불 서버는 "OK"를 수신할때까지 계속 재전송을 시도합니다
        //기타 다른 형태의 PRINT( echo )는 하지 않으시기 바랍니다

        if ($result)
        {
            echo "OK";                        // 절대로 지우지마세요
        }
        else
        {
            echo "FAIL";                        // 절대로 지우지마세요
        }

        //*************************************************************************************

    }


}