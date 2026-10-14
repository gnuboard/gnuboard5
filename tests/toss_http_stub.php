<?php
// 격리 설치본의 PG 전송 경계만 대체한다. 운영 코드에서 이 파일을 include하지 않는다.
if (!defined('_GNUBOARD_') || strpos(G5_MYSQL_DB,'issue48_') !== 0 ||
    !is_file(G5_DATA_PATH.'/.order-state-test')) exit;
class TossPayments {
    public $headers=array(); public $responseData=array(); public $paymentData=array(); public $cancelData=array();
    public $cardCode=array('61'=>'TEST CARD');public $bankCode=array();public $easyPayCode=array();
    public function __construct($a,$b,$c) {}
    public function setPaymentData($data) { $this->paymentData=$data; }
    public function setPaymentHeader() { $this->headers=array('Content-Type: application/json'); }
    public function approvePayment() {
        $p=$this->paymentData;
        $this->responseData=array('orderId'=>$p['orderId'],'paymentKey'=>$p['paymentKey'],'totalAmount'=>$p['amount'],
            'status'=>'DONE','method'=>'카드','useEscrow'=>false,'approvedAt'=>date('c'),
            'card'=>array('approveNo'=>'12345678','issuerCode'=>'61'),'cashReceipt'=>null);
        $id=sql_escape_string($p['orderId']);$data=sql_escape_string(json_encode($this->responseData));
        sql_query("insert into g5_order_test_pg set od_id='$id',approvals=1,queries=0,data='$data' on duplicate key update approvals=approvals+1,data='$data'");
        return true;
    }
    public function getPaymentByOrderId($id) {
        $id=sql_escape_string($id);sql_query("update g5_order_test_pg set queries=queries+1 where od_id='$id'");
        $row=sql_fetch("select data from g5_order_test_pg where od_id='$id'");$this->responseData=$row?json_decode($row['data'],true):array();return (bool)$row;
    }
    public function setCancelData($data) { $this->cancelData=$data; }
    public function cancelPayment() {
        $this->responseData['status']='CANCELED';$id=sql_escape_string($this->responseData['orderId']);
        $data=sql_escape_string(json_encode($this->responseData));sql_query("update g5_order_test_pg set data='$data' where od_id='$id'");return true;
    }
}
