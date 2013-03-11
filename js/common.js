// 전역 변수
var errmsg = "";
var errfld = null;

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

// php chr() 대응
function chr(code)
{
    return String.fromCharCode(code);
}

var win_password_lost = function(href) {
    window.open(href, "win_password_lost", "left=50, top=50, width=617, height=330, scrollbars=1");
}

$(document).ready(function(){
    $("#login_password_lost, #ol_password_lost").click(function(){
        win_password_lost(this.href);
        return false;
    });
});

/**
 * 포인트 창
 **/
var win_point = function(href) {
    var new_win = window.open(href, 'win_point', 'left=100,top=100,width=600, height=600, scrollbars=1');
    new_win.focus();
}

/**
 * 쪽지 창
 **/
var win_memo = function(href) {
    var new_win = window.open(href, 'win_memo', 'left=100,top=100,width=620,height=500,scrollbars=1');
    new_win.focus();
}

/**
 * 메일 창
 **/
var win_email = function(href) {
    var new_win = window.open(href, 'win_email', 'left=100,top=100,width=600,height=580,scrollbars=0');
    new_win.focus();
}

/**
 * 자기소개 창
 **/
var win_profile = function(href) {
    var new_win = window.open(href, 'win_profile', 'left=100,top=100,width=620,height=510,scrollbars=1');
    new_win.focus();
}

/**
 * 스크랩 창
 **/
var win_scrap = function(href) {
    var new_win = window.open(href, 'win_scrap', 'left=100,top=100,width=600,height=600,scrollbars=1');
    new_win.focus();
}

/**
 * 홈페이지 창
 **/
var win_homepage = function(href) {
    var new_win = window.open(href, 'win_homepage', '');
    new_win.focus();
}

/**
 * 우편번호 창
 **/
var win_zip = function(href) {
    var new_win = window.open(href, 'win_zip', 'width=616, height=460, scrollbars=1');
    new_win.focus();
}

/**
 * 새로운 패스워드 분실 창 : 101123
 **/
win_password_lost = function(href)
{
    var new_win = window.open(href, 'win_password_lost', 'width=617, height=330, scrollbars=1');
    new_win.focus();
}

/**
 * 설문조사 결과
 **/
var win_poll = function(href) {
    var new_win = window.open(href, 'win_poll', 'width=616, height=500, scrollbars=1');
    new_win.focus();
}

/**
 * 텍스트 리사이즈
**/
var default_font_size_saved = false;
function font_resize(id, act)
{
    var $elements = $("#"+id+" *").not("select").not("option");
    $elements.removeClass("applied");
    var count = parseInt(get_cookie("ck_font_resize_count"));
    if(isNaN(count))
        count = 0;

    // 엘리먼트의 기본 폰트사이즈 저장
    if(!default_font_size_saved) {
        save_default_font_size($elements);
    }

    // 크롬의 최소 폰트사이즈 버그로 작게는 한단계만 가능
    if(act == "decrease" && count == -1)
        return;

    $elements.each(function() {
        if($(this).hasClass("no_text_resize"))
            return true;

        if($(this).data("fs")) {
            set_font_size($(this), act)
        }
    });

    // 텍스트 리사이즈 회수 쿠키에 기록
    if(act == "increase")
        count++;
    else
        count--;

    set_cookie("ck_font_resize_count", count, 1, g4_cookie_domain);
}


/**
 * 텍스트 기본사이즈
**/
function font_default(id)
{
    var act;
    var count = parseInt(get_cookie("ck_font_resize_count"));
    if(isNaN(count))
        count = 0;

    if(count > 0) {
        act = "decrease";
    } else {
        act = "increase";
        // 작게 후 기본 크기가 되지 않는 문제해결을 위해 추가
        set_cookie("ck_font_resize_count", 0, 1, g4_cookie_domain);
    }

    for(i=0; i<Math.abs(count); i++) {
        font_resize(id, act);
    }

    // font resize 카운트 초기화
    set_cookie("ck_font_resize_count", 0, 1, g4_cookie_domain);
}


/**
 * font_resize 함수를 반복 할 때 사용
**/
function font_resize2(id, act, loop)
{
    // font resize 카운트 초기화
    set_cookie("ck_font_resize_count", 0, 1, g4_cookie_domain);

    for(i=0; i<loop; i++) {
        font_resize(id, act);
    }
}


/**
 * font size 적용
**/
function set_font_size($el, act)
{
    if($el.hasClass("applied"))
        return true;

    var x = 0;
    var fs = $el.data("fs");
    var unit = fs.replace(/[0-9\.]/g, "");
    var fsize = parseFloat(fs.replace(/[^0-9\.]/g, ""));
    var nfsize;

    if(!fsize)
        return true;

    if(unit == "em")
        x = 1;

    if(act == "increase") {
        nfsize = (fsize * 1.2);
    } else {
        nfsize = (fsize / 1.2);
    }

    nfsize = nfsize.toFixed(x);

    $el.css("font-size", nfsize+unit).addClass("applied");
    $el.data("fs", nfsize+unit);
}


/**
 * 기본 font size .data()에 저장
**/
function save_default_font_size($el)
{
    $el.each(function() {
        // 텍스트노드 있는지 체크
        var text = $(this).contents().filter(function() {
            return this.nodeType == 3;
        }).text().replace(/\s*/, "");

        if(text.length) {
            $(this).data("fs", $(this).css("font-size"));
        }
    });

    default_font_size_saved = true;
}


$(function(){
    $('.win_point').click(function() {
        win_point(this.href);
        return false;
    });

    $('.win_memo').click(function() {
        win_memo(this.href);
        return false;
    });

    $('.win_email').click(function() {
        win_email(this.ref);
        return false;
    });

    $('.win_scrap').click(function() {
        win_scrap(this.href);
        return false;
    });

    $('.win_profile').click(function() {
        win_profile(this.ref);
        return false;
    });

    $('.win_homepage').click(function() {
        win_homepage(this.ref);
        return false;
    });

    $('.win_zip_find').click(function() {
        win_zip(this.href);
        return false;
    });

    $('.win_password_lost').click(function() {
        win_password_lost(this.href);
        return false;
    });

    /*
    $('.win_poll').click(function() {
        win_poll(this.href);
        return false;
    });
    */

    // 사이드뷰
    var sv_hide = false;
    $('.sv_member, .sv_guest').click(function() {
        $('.sv').removeClass('sv_on');
        $(this).closest('.sv_wrap').find('.sv').addClass('sv_on');
    });

    $('.sv, .sv_wrap').hover(
        function() {
            sv_hide = false;
        },
        function() {
            sv_hide = true;
        }
    );

    $('.sv_member, .sv_guest').focusin(function() {
        sv_hide = false;
        $('.sv').removeClass('sv_on');
        $(this).closest('.sv_wrap').find('.sv').addClass('sv_on');
    });

    $('.sv a').focusin(function() {
        sv_hide = false;
    });

    $('.sv a').focusout(function() {
        sv_hide = true;
    });

    $(document).click(function() {
        if(sv_hide) {
            $('.sv').removeClass('sv_on');
        }
    });

    $(document).focusin(function() {
        if(sv_hide) {
            $('.sv').removeClass('sv_on');
        }
    });
});