<?php
include_once('./_common.php');

//**********************************************************************************
// 나이스페이 가상계좌이체 결과 수신 및 DB 처리
//**********************************************************************************

@extract($_GET);
@extract($_POST);
@extract($_SERVER);

$nicepayHome = G5_SHOP_PATH.'/nicepay'; // 나이스페이 홈디렉터리
$nicepayLog  = "true";                  // 로그를 기록하려면 true 로 수정


$pay_method         = $PayMethod;       // 지불수단
$mid                = $MID;             // 상점ID
$mall_user_id       = $MallUserId;      // 회원사 ID
$amt                = $Amt;             // 금액
$name               = $name;            // 구매자명
$goods_name         = $GoodsName;       // 상품명
$tid                = $TID;             // 거래번호
$moid               = $MOID;            // 주문번호
$auth_date          = $AuthDate;        // 입금일시 (yyMMddHHmmss)
$result_code        = $ResultCode;      // 결과코드 ('4110' 경우 입금통보)
$result_msg         = $ResultMsg;       // 결과메시지    
$vbank_num          = $VbankNum;        // 가상계좌번호
$fncd               = $FnCd;            // 가상계좌 은행코드
$vbank_name         = $VbankName;       // 가상계좌 은행명
$vbank_input_name   = $VbankInputName;  // 입금자명
$cancel_date        = $CancelDate;      // 취소일시

// 가상계좌채번시 현금영수증 자동발급신청이 되었을경우 전달 (RcptTID, RcptType, RcptAuthCode)
$rcpt_tid           = $RcptTID;         // 현금영수증 거래번호
$rcpt_type          = $RcptType;        // 현금영수증 구분 (0: 미발행, 1: 소득공제용, 2: 지출증빙용)
$rcpt_auth_code     = $RcptAuthCode;    // 현금영수증 승인번호

// 입금결과 처리
$sql = " select pp_id, od_id from {$g5['g5_shop_personalpay_table']} where pp_id = '$moid' and pp_app_no = '$vbank_num' ";
$row = sql_fetch($sql);

$result = false;
$receipt_time = $auth_date;

if($row['pp_id']) {
    // 개인결제 UPDATE
    $sql = " update {$g5['g5_shop_personalpay_table']}
                set pp_receipt_price    = '$amt',
                    pp_receipt_time     = '$receipt_time'
                where pp_id = '$moid'
                  and pp_app_no = '$vbank_num' ";
    $result = sql_query($sql, false);

    if($result == null || $result == false) {

    }

    if($row['od_id']) {
        // 주문서 UPDATE
        $receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $receipt_time);
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_receipt_price = od_receipt_price + '$amt',
                        od_receipt_time = '$receipt_time',
                        od_shop_memo = concat(od_shop_memo, \"\\n개인결제 ".$row['pp_id']." 로 결제완료 - ".$receipt_time."\")
                  where od_id = '{$row['od_id']}' ";
        $result = sql_query($sql, FALSE);
    }
} else {
    // 주문서 UPDATE
    $sql = " update {$g5['g5_shop_order_table']}
                set od_receipt_price = '$amt',
                    od_receipt_time = '$receipt_time'
              where od_id = '$moid'
                and od_app_no = '$vbank_num' ";
    $result = sql_query($sql, FALSE);
}

if($result) {
    if($row['od_id'])
        $od_id = $row['od_id'];
    else
        $od_id = $moid;

    // 주문정보 체크
    $sql = " select count(od_id) as cnt
                from {$g5['g5_shop_order_table']}
                where od_id = '$od_id'
                  and od_status = '주문' ";
    $row = sql_fetch($sql);

    if($row['cnt'] == 1) {
        // 미수금 정보 업데이트
        $info = get_order_info($od_id);

        $sql = " update {$g5['g5_shop_order_table']}
                    set od_misu = '{$info['od_misu']}' ";
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

if($nicepayLog) {
    $logfile = fopen( $nicepayHome . "/log/result.log", "a+" );

    fwrite($logfile, "***************************************\r\n");
    fwrite($logfile, 'PayMethod : '.$pay_method."\r\n");
    fwrite($logfile, 'MID : '.$mid."\r\n");
    fwrite($logfile, 'MallUserId : '.$mall_user_id."\r\n");
    fwrite($logfile, 'Amt : '.$amt."\r\n");
    fwrite($logfile, 'name : '.$name."\r\n");
    fwrite($logfile, 'GoodsName : '.$GoodsName."\r\n");
    fwrite($logfile, 'TID : '.$TID."\r\n");
    fwrite($logfile, 'MOID : '.$MOID."\r\n");
    fwrite($logfile, 'AuthDate : '.$AuthDate."\r\n");
    fwrite($logfile, 'ResultCode : '.$ResultCode."\r\n");
    fwrite($logfile, 'ResultMsg : '.$ResultMsg."\r\n");
    fwrite($logfile, 'VbankNum : '.$VbankNum."\r\n");
    fwrite($logfile, 'FnCd : '.$FnCd."\r\n");
    fwrite($logfile, 'VbankName : '.$VbankName."\r\n");
    fwrite($logfile, 'VbankInputName : '.$VbankInputName."\r\n");
    fwrite($logfile, 'RcptTID : '.$RcptTID."\r\n");
    fwrite($logfile, 'RcptType : '.$RcptType."\r\n");
    fwrite($logfile, 'RcptAuthCode : '.$RcptAuthCode."\r\n");
    fwrite($logfile, 'CancelDate : '.$CancelDate."\r\n");
    fwrite($logfile, "***************************************\r\n");
    
    fclose( $logfile );
}

// 성공 여부 판별 및 return
if ($result) { 
    echo "OK"; 
} else { 
    echo "FAIL"; 
}

?>