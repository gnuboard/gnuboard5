<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}
?>
<script src="<?php echo $g_conf_js_url; ?>"></script>
<script type="text/javascript">
function m_Completepayment( FormOrJson, closeEvent ) 
{
    var frm = document.forderform;

    /********************************************************************/
    /* FormOrJson은 가맹점 임의 활용 금지                               */
    /* frm 값에 FormOrJson 값이 설정 됨 frm 값으로 활용 하셔야 됩니다.  */
    /* FormOrJson 값을 활용 하시려면 기술지원팀으로 문의바랍니다.       */
    /********************************************************************/
    GetField( frm, FormOrJson );

    $("body").css({
        "position": "",
        "width": "",
        "top" : ""
    });

    if( frm.res_cd.value == "0000" )
    {
        document.getElementById("display_pay_button").style.display = "none" ;
        document.getElementById("display_pay_process").style.display = "" ;

        frm.submit();
    }
    else
    {
        alert( "[" + frm.res_cd.value + "] " + frm.res_msg.value );

        closeEvent();
    }
}
/* 배치키 발급창 실행 */
function jsf__pay( form ) {

    try {
        KCP_Pay_Execute( form ); 
        $("body").css({
            "position": "fixed",
            "width": "100%",
            "top" : "0"
        });
    }
    catch (e)
    {
        /* 브라우저에서 결제 정상종료시 throw로 스크립트 종료 */ 
    }
}     
</script>