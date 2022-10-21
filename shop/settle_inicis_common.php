<?php
include_once('./_common.php');

//**********************************************************************************
//이니시스가 전달하는 가상계좌이체의 결과를 수신하여 DB 처리 하는 부분 입니다.
//필요한 파라메터에 대한 DB 작업을 수행하십시오.
//**********************************************************************************

//**********************************************************************************
//  이부분에 로그파일 경로를 수정해주세요.

$INIpayHome = G5_SHOP_PATH.'/inicis'; // 이니페이 홈디렉터리
$INIpayLog  = false;                  // 로그를 기록하려면 true 로 수정

//**********************************************************************************

$PG_IP = get_real_client_ip();

if( $PG_IP == "203.238.37.3" || $PG_IP == "203.238.37.15" || $PG_IP == "203.238.37.16" || $PG_IP == "203.238.37.25" || $PG_IP == "183.109.71.153" || $PG_IP == "39.115.212.9" )  //PG에서 보냈는지 IP로 체크
{
        $msg_id = $msg_id;             //메세지 타입
        $no_tid = $no_tid;             //거래번호
        $no_oid = $no_oid;             //상점 주문번호
        $id_merchant = $id_merchant;   //상점 아이디
        $cd_bank = $cd_bank;           //거래 발생 기관 코드
        $cd_deal = $cd_deal;           //취급 기관 코드
        $dt_trans = $dt_trans;         //거래 일자
        $tm_trans = $tm_trans;         //거래 시간
        $no_msgseq = $no_msgseq;       //전문 일련 번호
        $cd_joinorg = $cd_joinorg;     //제휴 기관 코드

        $dt_transbase = $dt_transbase; //거래 기준 일자
        $no_transeq = $no_transeq;     //거래 일련 번호
        $type_msg = $type_msg;         //거래 구분 코드
        $cl_close = $cl_close;         //마감 구분코드
        $cl_kor = $cl_kor;             //한글 구분 코드
        $no_msgmanage = $no_msgmanage; //전문 관리 번호
        $no_vacct = $no_vacct;         //가상계좌번호
        $amt_input = $amt_input;       //입금금액
        $amt_check = $amt_check;       //미결제 타점권 금액
        $nm_inputbank = $nm_inputbank; //입금 금융기관명
        $nm_input = $nm_input;         //입금 의뢰인
        $dt_inputstd = $dt_inputstd;   //입금 기준 일자
        $dt_calculstd = $dt_calculstd; //정산 기준 일자
        $flg_close = $flg_close;       //마감 전화

        //가상계좌채번시 현금영수증 자동발급신청시에만 전달
        $dt_cshr      = $dt_cshr;       //현금영수증 발급일자
        $tm_cshr      = $tm_cshr;       //현금영수증 발급시간
        $no_cshr_appl = $no_cshr_appl;  //현금영수증 발급번호
        $no_cshr_tid  = $no_cshr_tid;   //현금영수증 발급TID

        // 입금결과 처리
        $sql = " select pp_id, od_id from {$g5['g5_shop_personalpay_table']} where pp_id = '$no_oid' and pp_app_no = '$no_vacct' ";
        $row = sql_fetch($sql);

        $result = false;
        $receipt_time = $dt_trans.$tm_trans;

        if($row['pp_id']) {
            // 개인결제 UPDATE
            $sql = " update {$g5['g5_shop_personalpay_table']}
                        set pp_receipt_price    = '$amt_input',
                            pp_receipt_time     = '$receipt_time'
                        where pp_id = '$no_oid'
                          and pp_app_no = '$no_vacct' ";
            $result = sql_query($sql, false);

            if($row['od_id']) {
                // 주문서 UPDATE
                $receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $receipt_time);
                $sql = " update {$g5['g5_shop_order_table']}
                            set od_receipt_price = od_receipt_price + '$amt_input',
                                od_receipt_time = '$receipt_time',
                                od_shop_memo = concat(od_shop_memo, \"\\n개인결제 ".$row['pp_id']." 로 결제완료 - ".$receipt_time."\")
                          where od_id = '{$row['od_id']}' ";
                $result = sql_query($sql, FALSE);
            }
        } else {
            // 주문서 UPDATE
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_receipt_price = '$amt_input',
                            od_receipt_time = '$receipt_time'
                      where od_id = '$no_oid'
                        and od_app_no = '$no_vacct' ";
            $result = sql_query($sql, FALSE);
        }

        if($result) {
            if($row['od_id'])
                $od_id = $row['od_id'];
            else
                $od_id = $no_oid;

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

        if($INIpayLog) {
            $logfile = fopen( $INIpayHome . "/log/result.log", "a+" );

            fwrite( $logfile,"************************************************");
            fwrite( $logfile,"ID_MERCHANT : ".$id_merchant."\r\n");
            fwrite( $logfile,"NO_TID : ".$no_tid."\r\n");
            fwrite( $logfile,"NO_OID : ".$no_oid."\r\n");
            fwrite( $logfile,"NO_VACCT : ".$no_vacct."\r\n");
            fwrite( $logfile,"AMT_INPUT : ".$amt_input."\r\n");
            fwrite( $logfile,"NM_INPUTBANK : ".$nm_inputbank."\r\n");
            fwrite( $logfile,"NM_INPUT : ".$nm_input."\r\n");
            fwrite( $logfile,"************************************************");

            fwrite( $logfile,"전체 결과값"."\r\n");
            fwrite( $logfile, $msg_id."\r\n");
            fwrite( $logfile, $no_tid."\r\n");
            fwrite( $logfile, $no_oid."\r\n");
            fwrite( $logfile, $id_merchant."\r\n");
            fwrite( $logfile, $cd_bank."\r\n");
            fwrite( $logfile, $dt_trans."\r\n");
            fwrite( $logfile, $tm_trans."\r\n");
            fwrite( $logfile, $no_msgseq."\r\n");
            fwrite( $logfile, $type_msg."\r\n");
            fwrite( $logfile, $cl_close."\r\n");
            fwrite( $logfile, $cl_kor."\r\n");
            fwrite( $logfile, $no_msgmanage."\r\n");
            fwrite( $logfile, $no_vacct."\r\n");
            fwrite( $logfile, $amt_input."\r\n");
            fwrite( $logfile, $amt_check."\r\n");
            fwrite( $logfile, $nm_inputbank."\r\n");
            fwrite( $logfile, $nm_input."\r\n");
            fwrite( $logfile, $dt_inputstd."\r\n");
            fwrite( $logfile, $dt_calculstd."\r\n");
            fwrite( $logfile, $flg_close."\r\n");
            fwrite( $logfile, "\r\n");

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
        echo "DB Error";
    }

//*************************************************************************************

}