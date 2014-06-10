<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/* ============================================================================== */
/* =   Javascript source Include                                                = */
/* = -------------------------------------------------------------------------- = */
/* =   ※ 필수                                                                  = */
/* = -------------------------------------------------------------------------- = */

// kcp 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use']) {
?>
<script src="<?php echo $g_conf_js_url; ?>"></script>
<?php
/* = -------------------------------------------------------------------------- = */
/* =   Javascript source Include END                                            = */
/* ============================================================================== */
?>
<script>
function CheckPayplusInstall()
{
    if(ChkBrowser())
    {
        if(document.Payplus.object != null) {
            document.getElementById("display_setup_message_top").style.display = "none" ;
            document.getElementById("display_setup_message").style.display = "none" ;
            document.getElementById("display_pay_button").style.display = "" ;
        }
    }
    else
    {
        setTimeout("init_pay_button();",300);
    }
}

/* Payplus Plug-in 실행 */
function  jsf__pay( form )
{
    var RetVal = false;

    /* Payplus Plugin 실행 */
    if ( MakePayMessage( form ) == true )
    {
        //openwin = window.open( "./kcp/proc_win.html", "proc_win", "width=449, height=209, top=300, left=300" );
        document.getElementById("display_pay_button").style.display = "none" ;
        document.getElementById("display_pay_process").style.display = "" ;
        RetVal = true ;
    }

    else
    {
        /*  res_cd와 res_msg변수에 해당 오류코드와 오류메시지가 설정됩니다.
            ex) 고객이 Payplus Plugin에서 취소 버튼 클릭시 res_cd=3001, res_msg=사용자 취소
            값이 설정됩니다.
        */
        res_cd  = document.forderform.res_cd.value ;
        res_msg = document.forderform.res_msg.value ;

    }

    return RetVal ;
}

// Payplus Plug-in 설치 안내

function init_pay_button()
{
    if (navigator.userAgent.indexOf('MSIE') > 0)
    {
        try
        {
            if( document.Payplus.object == null )
            {
                document.getElementById("display_setup_message_top").style.display = "" ;
                document.getElementById("display_setup_message").style.display = "" ;
                document.getElementById("display_pay_button").style.display = "none" ;
                document.getElementById("display_setup_message").scrollIntoView();
            }
            else{
                document.getElementById("display_setup_message_top").style.display = "none" ;
                document.getElementById("display_setup_message").style.display = "none" ;
                document.getElementById("display_pay_button").style.display = "" ;
            }
        }
        catch (e)
        {
            document.getElementById("display_setup_message_top").style.display = "" ;
            document.getElementById("display_setup_message").style.display = "" ;
            document.getElementById("display_pay_button").style.display = "none" ;
            document.getElementById("display_setup_message").scrollIntoView();
        }
    }
    else
    {
        try
        {
            if( Payplus == null )
            {
                document.getElementById("display_setup_message_top").style.display = "" ;
                document.getElementById("display_setup_message").style.display = "" ;
                document.getElementById("display_pay_button").style.display = "none" ;
                document.getElementById("display_setup_message").scrollIntoView();
            }
            else{
                document.getElementById("display_setup_message_top").style.display = "none" ;
                document.getElementById("display_setup_message").style.display = "none" ;
                document.getElementById("display_pay_button").style.display = "" ;
            }
        }
        catch (e)
        {
            document.getElementById("display_setup_message_top").style.display = "" ;
            document.getElementById("display_setup_message").style.display = "" ;
            document.getElementById("display_pay_button").style.display = "none" ;
            document.getElementById("display_setup_message").scrollIntoView();
        }
    }
}

function get_intall_file()
{
    document.location.href = GetInstallFile();
    return false;
}
</script>

<!-- Payplus Plug-in 설치 안내 -->
<p id="display_setup_message_top" class="display_setup_message" style="display:block">
    <strong>결제안내</strong>
    <span class="red">결제를 하시려면 상단의 노란색 표시줄을 클릭</span>하시거나, <a href="https://pay.kcp.co.kr/plugin_new/file/KCPPluginSetup.exe" onclick="return get_intall_file();"><span class="bold">[수동설치]</span></a>를 눌러 Payplus Plug-in을 설치하시기 바랍니다.<br>
    [수동설치]를 눌러 설치하신 경우 <span class="red bold">새로고침(F5)키</span>를 눌러 진행하시기 바랍니다.
</p>

<?php } ?>