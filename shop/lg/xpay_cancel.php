<?php
//최종결제요청 결과 성공 DB처리 실패시 Rollback 처리
$isDBOK = false; //DB처리 실패시 false로 변경해 주세요.
if( !$isDBOK ) {
    if( isset($xpay) && method_exists($xpay, 'Rollback')) {
        $xpay->Rollback($cancel_msg . " [TID:" . $xpay->Response("LGD_TID",0) . ",MID:" . $xpay->Response("LGD_MID",0) . ",OID:" . $xpay->Response("LGD_OID",0) . "]");
    }

    /*
    echo "TX Rollback Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Rollback Response_msg = " . $xpay->Response_Msg() . "<p>";

    if( "0000" == $xpay->Response_Code() ) {
        echo "자동취소가 정상적으로 완료 되었습니다.<br>";
    }else{
        echo "자동취소가 정상적으로 처리되지 않았습니다.<br>";
    }
    */
}