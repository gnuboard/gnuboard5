// 본인확인 인증창 호출
function certify_win_open(type, url)
{
    if(type == 'kcb-ipin')
    {
        var popupWindow = window.open( url, "kcbPop", "left=200, top=100, status=0, width=450, height=550" );
        popupWindow.focus();
    }
    else if(type == 'kcb-hp')
    {
        var popupWindow = window.open( url, "auth_popup", "left=200, top=100, width=430, height=590, scrollbar=yes" );
        popupWindow.focus();
    }
    else if(type == 'kcp-hp')
    {
        if($("input[name=veri_up_hash]").size() < 1)
                $("input[name=cert_no]").after('<input type="hidden" name="veri_up_hash" value="">');

        if( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 )
        {
            var $frm = $(event.target.form);
            if($("#kcp_cert").size() < 1) {
                $frm.wrap('<div id="cert_info"></div>');

                $("#cert_info").append('<form name="form_temp" method="post">')
                               .after('<iframe id="kcp_cert" name="kcp_cert" width="100%" height="700" frameborder="0" scrolling="no" style="display:none"></iframe>');
            }

            var temp_form = document.form_temp;
            temp_form.target = "kcp_cert";
            temp_form.action = url;

            document.getElementById( "cert_info" ).style.display = "none";
            document.getElementById( "kcp_cert"  ).style.display = "";

            temp_form.submit();
        }
        else
        {
            var return_gubun;
            var width  = 410;
            var height = 500;

            var leftpos = screen.width  / 2 - ( width  / 2 );
            var toppos  = screen.height / 2 - ( height / 2 );

            var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
            var position = ",left=" + leftpos + ", top="    + toppos;
            var AUTH_POP = window.open(url,'auth_popup', winopts + position);
        }
    }
    else if(type == 'lg-hp')
    {
        var popupWindow = window.open( url, "auth_popup", "left=200, top=100, width=400, height=400, scrollbar=yes" );
        popupWindow.focus();
    }
}

// 인증체크
function cert_confirm()
{
    var type;
    var val = document.fregisterform.cert_type.value

    switch(val) {
        case "ipin":
            type = "아이핀";
            break;
        case "hp":
            type = "휴대폰";
            break;
        default:
            return true;
    }

    if(confirm("이미 "+type+"으로 본인확인을 완료하셨습니다.\n\n이전 인증을 취소하고 다시 인증하시겠습니까?"))
        return true;
    else
        return false;
}