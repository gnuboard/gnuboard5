<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}
?>
<script src="<?php echo $g_conf_js_url; ?>"></script>
<script type="text/javascript">
function m_Completepayment( frm_mpi, closeEvent ) 
{
    var frm = document.forderform;

    if (frm_mpi.res_cd.value == "0000") {
        GetField(frm, frm_mpi); 
        
        frm.submit(); 

        closeEvent();
    } else {
        closeEvent();

        setTimeout("alert( \"[" + frm_mpi.res_cd.value + "]" + frm_mpi.res_msg.value + "\");", 1000);
    }
}
/* 배치키 발급창 실행 */
function jsf__pay( form ) {

    try {
        KCP_Pay_Execute_Web( form ); 
    }
    catch (e)
    {
        /* 브라우저에서 결제 정상종료시 throw로 스크립트 종료 */ 
    }
}     
</script>