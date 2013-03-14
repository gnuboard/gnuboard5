if (typeof(COMMON_JS) == 'undefined') { // 한번만 실행
    var COMMON_JS = true;

    // 전역 변수
    var errmsg = "";
    var errfld;

    // 필드 검사
    function check_field(fld, msg) 
    {
        if ((fld.value = trim(fld.value)) == "") 			   
            error_field(fld, msg);
        else
            clear_field(fld);
        return;
    }

    // 필드 오류 표시
    function error_field(fld, msg) 
    {
        if (msg != "")
            errmsg += msg + "\n";
        if (!errfld) errfld = fld;
        fld.style.background = "#BDDEF7";
    }

    // 필드를 깨끗하게
    function clear_field(fld) 
    {
        fld.style.background = "#FFFFFF";
    }

    function trim(s)
    {
        var t = "";
        var from_pos = to_pos = 0;

        for (i=0; i<s.length; i++)
        {
            if (s.charAt(i) == ' ')
                continue;
            else 
            {
                from_pos = i;
                break;
            }
        }

        for (i=s.length; i>=0; i--)
        {
            if (s.charAt(i-1) == ' ')
                continue;
            else 
            {
                to_pos = i;
                break;
            }
        }	

        t = s.substring(from_pos, to_pos);
        //				alert(from_pos + ',' + to_pos + ',' + t+'.');
        return t;
    }

    // 자바스크립트로 PHP의 number_format 흉내를 냄
    // 숫자에 , 를 출력
    function number_format(data) 
    {
        
        var tmp = '';
        var number = '';
        var cutlen = 3;
        var comma = ',';
        var i;
       
        len = data.length;
        mod = (len % cutlen);
        k = cutlen - mod;
        for (i=0; i<data.length; i++) 
        {
            number = number + data.charAt(i);
            
            if (i < data.length - 1) 
            {
                k++;
                if ((k % cutlen) == 0) 
                {
                    number = number + comma;
                    k = 0;
                }
            }
        }

        return number;
    }

    // 새 창
    function popup_window(url, winname, opt)
    {
        window.open(url, winname, opt);
    }


    // 폼메일 창
    function popup_formmail(url)
    {
        opt = 'scrollbars=yes,width=417,height=385,top=10,left=20';
        popup_window(url, "wformmail", opt);
    }

    // , 를 없앤다.
    function no_comma(data)
    {
        var tmp = '';
        var comma = ',';
        var i;

        for (i=0; i<data.length; i++)
        {
            if (data.charAt(i) != comma)
                tmp += data.charAt(i);
        }
        return tmp;
    }

    // 삭제 검사 확인
    function del(href) 
    {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
            if (g4_charset.toUpperCase() == 'EUC-KR') 
                document.location.href = href;
            else
                document.location.href = encodeURI(href);
        }
    }

    // 쿠키 입력
    function set_cookie(name, value, expirehours, domain) 
    {
        var today = new Date();
        today.setTime(today.getTime() + (60*60*1000*expirehours));
        document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + today.toGMTString() + ";";
        if (domain) {
            document.cookie += "domain=" + domain + ";";
        }
    }

    // 쿠키 얻음
    function get_cookie(name) 
    {
        var find_sw = false;
        var start, end;
        var i = 0;

        for (i=0; i<= document.cookie.length; i++)
        {
            start = i;
            end = start + name.length;

            if(document.cookie.substring(start, end) == name) 
            {
                find_sw = true
                break
            }
        }

        if (find_sw == true) 
        {
            start = end + 1;
            end = document.cookie.indexOf(";", start);

            if(end < start)
                end = document.cookie.length;

            return document.cookie.substring(start, end);
        }
        return "";
    }

    // 쿠키 지움
    function delete_cookie(name) 
    {
        var today = new Date();

        today.setTime(today.getTime() - 1);
        var value = get_cookie(name);
        if(value != "")
            document.cookie = name + "=" + value + "; path=/; expires=" + today.toGMTString();
    }

    // 이미지의 크기에 따라 새창의 크기가 변경됩니다.
    // zzzz님께서 알려주셨습니다. 2005/04/12
    function image_window(img)
    {
        var w = img.tmp_width; 
        var h = img.tmp_height; 
        var winl = (screen.width-w)/2; 
        var wint = (screen.height-h)/3; 

        if (w >= screen.width) { 
            winl = 0; 
            h = (parseInt)(w * (h / w)); 
        } 

        if (h >= screen.height) { 
            wint = 0; 
            w = (parseInt)(h * (w / h)); 
        } 

        var js_url = "<script type='text/javascript'> \n"; 
            js_url += "<!-- \n"; 
            js_url += "var ie=document.all; \n"; 
            js_url += "var nn6=document.getElementById&&!document.all; \n"; 
            js_url += "var isdrag=false; \n"; 
            js_url += "var x,y; \n"; 
            js_url += "var dobj; \n"; 
            js_url += "function movemouse(e) \n"; 
            js_url += "{ \n"; 
            js_url += "  if (isdrag) \n"; 
            js_url += "  { \n"; 
            js_url += "    dobj.style.left = nn6 ? tx + e.clientX - x : tx + event.clientX - x; \n"; 
            js_url += "    dobj.style.top  = nn6 ? ty + e.clientY - y : ty + event.clientY - y; \n"; 
            js_url += "    return false; \n"; 
            js_url += "  } \n"; 
            js_url += "} \n"; 
            js_url += "function selectmouse(e) \n"; 
            js_url += "{ \n"; 
            js_url += "  var fobj      = nn6 ? e.target : event.srcElement; \n"; 
            js_url += "  var topelement = nn6 ? 'HTML' : 'BODY'; \n"; 
            js_url += "  while (fobj.tagName != topelement && fobj.className != 'dragme') \n"; 
            js_url += "  { \n"; 
            js_url += "    fobj = nn6 ? fobj.parentNode : fobj.parentElement; \n"; 
            js_url += "  } \n"; 
            js_url += "  if (fobj.className=='dragme') \n"; 
            js_url += "  { \n"; 
            js_url += "    isdrag = true; \n"; 
            js_url += "    dobj = fobj; \n"; 
            js_url += "    tx = parseInt(dobj.style.left+0); \n"; 
            js_url += "    ty = parseInt(dobj.style.top+0); \n"; 
            js_url += "    x = nn6 ? e.clientX : event.clientX; \n"; 
            js_url += "    y = nn6 ? e.clientY : event.clientY; \n"; 
            js_url += "    document.onmousemove=movemouse; \n"; 
            js_url += "    return false; \n"; 
            js_url += "  } \n"; 
            js_url += "} \n"; 
            js_url += "document.onmousedown=selectmouse; \n"; 
            js_url += "document.onmouseup=new Function('isdrag=false'); \n"; 
            js_url += "//--> \n"; 
            js_url += "</"+"script> \n"; 

        var settings;

        if (g4_is_gecko) {
            settings  ='width='+(w+10)+','; 
            settings +='height='+(h+10)+','; 
        } else {
            settings  ='width='+w+','; 
            settings +='height='+h+','; 
        }
        settings +='top='+wint+','; 
        settings +='left='+winl+','; 
        settings +='scrollbars=no,'; 
        settings +='resizable=yes,'; 
        settings +='status=no'; 


        win=window.open("","image_window",settings); 
        win.document.open(); 
        win.document.write ("<html><head> \n<meta http-equiv='imagetoolbar' CONTENT='no'> \n<meta http-equiv='content-type' content='text/html; charset="+g4_charset+"'>\n"); 
        var size = "이미지 사이즈 : "+w+" x "+h;
        win.document.write ("<title>"+size+"</title> \n"); 
        if(w >= screen.width || h >= screen.height) { 
            win.document.write (js_url); 
            var click = "ondblclick='window.close();' style='cursor:move' title=' "+size+" \n\n 이미지 사이즈가 화면보다 큽니다. \n 왼쪽 버튼을 클릭한 후 마우스를 움직여서 보세요. \n\n 더블 클릭하면 닫혀요. '"; 
        } 
        else 
            var click = "onclick='window.close();' style='cursor:pointer' title=' "+size+" \n\n 클릭하면 닫혀요. '"; 
        win.document.write ("<style>.dragme{position:relative;}</style> \n"); 
        win.document.write ("</head> \n\n"); 
        win.document.write ("<body leftmargin=0 topmargin=0 bgcolor=#dddddd style='cursor:arrow;'> \n"); 
        win.document.write ("<table width=100% height=100% cellpadding=0 cellspacing=0><tr><td align=center valign=middle><img src='"+img.src+"' width='"+w+"' height='"+h+"' border=0 class='dragme' "+click+"></td></tr></table>");
        win.document.write ("</body></html>"); 
        win.document.close(); 

        if(parseInt(navigator.appVersion) >= 4){win.window.focus();} 
    }

    // a 태그에서 onclick 이벤트를 사용하지 않기 위해
    function win_open(url, name, option)
    {
        var popup = window.open(url, name, option);
        popup.focus();
    }

    // 우편번호 창
    function win_zip(frm_name, frm_zip1, frm_zip2, frm_addr1, frm_addr2)
    {
        url = g4_path + "/" + g4_bbs + "/zip.php?frm_name="+frm_name+"&frm_zip1="+frm_zip1+"&frm_zip2="+frm_zip2+"&frm_addr1="+frm_addr1+"&frm_addr2="+frm_addr2;
        win_open(url, "winZip", "left=50,top=50,width=616,height=460,scrollbars=1");
    }

    // 쪽지 창
    function win_memo(url)
    {
        if (!url)
            url = g4_path + "/" + g4_bbs + "/memo.php";
        win_open(url, "winMemo", "left=50,top=50,width=620,height=460,scrollbars=1");
    }

    // 포인트 창
    function win_point(url)
    {
        win_open(g4_path + "/" + g4_bbs + "/point.php", "winPoint", "left=20, top=20, width=616, height=635, scrollbars=1");
    }

    // 스크랩 창
    function win_scrap(url)
    {
        if (!url)
            url = g4_path + "/" + g4_bbs + "/scrap.php";
        win_open(url, "scrap", "left=20, top=20, width=616, height=500, scrollbars=1");
    }

    // 새로운 패스워드 분실 창 : 100902
    function win_password_lost()
    {
        win_open(g4_path + "/" + g4_bbs + "/password_lost.php", 'winPasswordLost', 'left=50, top=50, width=617, height=330, scrollbars=1');
    }

    // 패스워드 분실 창
    function win_password_forget()
    {
        win_open(g4_path + "/" + g4_bbs + "/password_forget.php", 'winPasswordForget', 'left=50, top=50, width=616, height=500, scrollbars=1');
    }

    // 코멘트 창
    function win_comment(url)
    {
        win_open(url, "winComment", "left=50, top=50, width=800, height=600, scrollbars=1");
    }

    // 폼메일 창
    function win_formmail(mb_id, name, email)
    {
		if (g4_charset.toLowerCase() == 'euc-kr')
	        win_open(g4_path+"/" + g4_bbs + "/formmail.php?mb_id="+mb_id+"&name="+name+"&email="+email, "winFormmail", "left=50, top=50, width=600, height=500, scrollbars=0");
		else
	        win_open(g4_path+"/" + g4_bbs + "/formmail.php?mb_id="+mb_id+"&name="+encodeURIComponent(name)+"&email="+email, "winFormmail", "left=50, top=50, width=600, height=480, scrollbars=0");
    }

    // 달력 창
    function win_calendar(fld, cur_date, delimiter, opt)
    {
        if (!opt)
            opt = "left=50, top=50, width=240, height=230, scrollbars=0,status=0,resizable=0";
        win_open(g4_path+"/" + g4_bbs + "/calendar.php?fld="+fld+"&cur_date="+cur_date+"&delimiter="+delimiter, "winCalendar", opt);
    }

    // 설문조사 창
    function win_poll(url)
    {
        if (!url)
            url = "";
        win_open(url, "winPoll", "left=50, top=50, width=616, height=500, scrollbars=1");
    }

    // 자기소개 창
    function win_profile(mb_id)
    {
        win_open(g4_path+"/" + g4_bbs + "/profile.php?mb_id="+mb_id, 'winProfile', 'left=50,top=50,width=620,height=510,scrollbars=1');
    }

    var last_id = null;
    function menu(id)
    {
        if (id != last_id)
        {
            if (last_id != null)
                document.getElementById(last_id).style.display = "none";
            document.getElementById(id).style.display = "block";
            last_id = id;
        }
        else
        {
            document.getElementById(id).style.display = "none";
            last_id = null;
        }
    }

    function textarea_decrease(id, row)
    {
        if (document.getElementById(id).rows - row > 0)
            document.getElementById(id).rows -= row;
    }

    function textarea_original(id, row)
    {
        document.getElementById(id).rows = row;
    }

    function textarea_increase(id, row)
    {
        document.getElementById(id).rows += row;
    }

    // 글숫자 검사
    function check_byte(content, target)
    {
        var i = 0;
        var cnt = 0;
        var ch = '';
        var cont = document.getElementById(content).value;

        for (i=0; i<cont.length; i++) {
            ch = cont.charAt(i);
            if (escape(ch).length > 4) {
                cnt += 2;
            } else {
                cnt += 1;
            }
        }
        // 숫자를 출력
        document.getElementById(target).innerHTML = cnt;

        return cnt;
    }

    // 브라우저에서 오브젝트의 왼쪽 좌표
    function get_left_pos(obj)
    {
        var parentObj = null;
        var clientObj = obj;
        //var left = obj.offsetLeft + document.body.clientLeft;
        var left = obj.offsetLeft;

        while((parentObj=clientObj.offsetParent) != null)
        {
            left = left + parentObj.offsetLeft;
            clientObj = parentObj;
        }

        return left;
    }

    // 브라우저에서 오브젝트의 상단 좌표
    function get_top_pos(obj)
    {
        var parentObj = null;
        var clientObj = obj;
        //var top = obj.offsetTop + document.body.clientTop;
        var top = obj.offsetTop;

        while((parentObj=clientObj.offsetParent) != null)
        {
            top = top + parentObj.offsetTop;
            clientObj = parentObj;
        }

        return top;
    }

    function flash_movie(src, ids, width, height, wmode)
    {
        var wh = "";
        if (parseInt(width) && parseInt(height)) 
            wh = " width='"+width+"' height='"+height+"' ";
        return "<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' "+wh+" id="+ids+"><param name=wmode value="+wmode+"><param name=movie value="+src+"><param name=quality value=high><embed src="+src+" quality=high wmode="+wmode+" type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?p1_prod_version=shockwaveflash' "+wh+"></embed></object>";
    }

    function obj_movie(src, ids, width, height, autostart)
    {
        var wh = "";
        if (parseInt(width) && parseInt(height)) 
            wh = " width='"+width+"' height='"+height+"' ";
        if (!autostart) autostart = false;
        return "<embed src='"+src+"' "+wh+" autostart='"+autostart+"'></embed>";
    }

    function doc_write(cont)
    {
        document.write(cont);
    }
}
