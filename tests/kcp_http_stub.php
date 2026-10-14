<?php
if (!defined('_GNUBOARD_') || strpos(G5_MYSQL_DB,'issue48_') !== 0 || !is_file(G5_DATA_PATH.'/.order-state-test')) exit;
class C_PP_CLI_T {
    public $m_res_cd='0000';public $m_res_msg='OK';private $data=array();private $out=array();
    public function mf_clear() {}
    public function mf_set_ordr_data($k,$v) { $this->data[$k]=$v; }
    public function mf_set_encx_data($a,$b) {}
    public function mf_do_tx(...$args) {
        $id=$this->data['ordr_no'];
        $this->out=array('tno'=>'KCPTEST'.$id,'amount'=>$this->data['ordr_mony'],'card_cd'=>'CCBC','card_name'=>'TEST CARD','app_time'=>date('YmdHis'),'app_no'=>'12345678','escw_yn'=>'N');
        $data=sql_escape_string(json_encode($this->out));
        sql_query("insert into g5_order_test_pg set od_id='$id',approvals=1,queries=0,data='$data' on duplicate key update approvals=approvals+1,data='$data'");
    }
    public function mf_get_res_data($key) { return isset($this->out[$key])?$this->out[$key]:''; }
}
