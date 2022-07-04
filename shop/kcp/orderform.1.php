<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// kcp 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
?>
<script type="text/javascript">
/****************************************************************/
/* m_Completepayment  설명                                      */
/****************************************************************/
/* 인증완료시 재귀 함수                                         */
/* 해당 함수명은 절대 변경하면 안됩니다.                        */
/* 해당 함수의 위치는 payplus.js 보다먼저 선언되어여 합니다.    */
/* Web 방식의 경우 리턴 값이 form 으로 넘어옴                   */
/* EXE 방식의 경우 리턴 값이 json 으로 넘어옴                   */
/****************************************************************/
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
</script>

<script src="<?php echo $g_conf_js_url; ?>"></script>
<script>
/* Payplus Plug-in 실행 */
function jsf__pay( form )
{
    try
    {
        KCP_Pay_Execute( form );
        $("body").css({
            "position": "fixed",
            "width": "100%",
            "top" : "0"
        });
    }
    catch (e)
    {
        /* IE 에서 결제 정상종료시 throw로 스크립트 종료 */
    }
}
</script>
<?php }