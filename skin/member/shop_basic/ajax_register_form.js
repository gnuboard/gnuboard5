/*
** 2010.03.12 : jQuery 로 대체하여 앞으로 사용하지 않습니다.
*/

// 회원아이디 검사
function reg_mb_id_check() {
    var url = member_skin_path + "/ajax_mb_id_check.php";
    var para = "reg_mb_id="+encodeURIComponent($F('reg_mb_id'));
    var myAjax = new Ajax.Request(
        url, 
        {
            method: 'post', 
            // 주소창 보안 방지 javascript:void(document.fregisterform.mb_id_enabled.value='000');
            // 동기식 (폼전송시 입력값이 바른지 검사한 후 mb_id_enabled 를 체크하기 때문)
            asynchronous: false,
            parameters: para, 
            onComplete: return_reg_mb_id_check
        });
}

function return_reg_mb_id_check(req) { 
    var msg = $('msg_mb_id');
    var result = req.responseText;
    switch(result) {
        case '110' : msg.update('영문자, 숫자, _ 만 입력하세요.').setStyle({ color: 'red' }); break;
        case '120' : msg.update('최소 3자이상 입력하세요.').setStyle({ color: 'red' }); break;
        case '130' : msg.update('이미 사용중인 아이디 입니다.').setStyle({ color: 'red' }); break;
        case '140' : msg.update('예약어로 사용할 수 없는 아이디 입니다.').setStyle({ color: 'red' }); break;
        case '000' : msg.update('사용하셔도 좋은 아이디 입니다.').setStyle({ color: 'blue' }); break;
        default : alert( '잘못된 접근입니다.\n\n' + result ); break;
    }
    $('mb_id_enabled').value = result;
}

// 별명 검사
function reg_mb_nick_check() {
    var url = member_skin_path + "/ajax_mb_nick_check.php";
    var para = "reg_mb_nick="+encodeURIComponent($F('reg_mb_nick'));
    var myAjax = new Ajax.Request(
        url, 
        {
            method: 'post', 
            // 주소창 보안 방지 javascript:void(document.fregisterform.mb_id_enabled.value='000');
            // 동기식 (폼전송시 입력값이 바른지 검사한 후 mb_id_enabled 를 체크하기 때문)
            asynchronous: false,
            parameters: para, 
            onComplete: return_reg_mb_nick_check
        });
}

function return_reg_mb_nick_check(req) { 
    var msg = $('msg_mb_nick');
    var result = req.responseText;
    switch(result) {
        case '110' : msg.update('별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.').setStyle({ color: 'red' }); break;
        case '120' : msg.update('한글 2글자, 영문 4글자 이상 입력 가능합니다.').setStyle({ color: 'red' }); break;
        case '130' : msg.update('이미 존재하는 별명입니다.').setStyle({ color: 'red' }); break;
        case '000' : msg.update('사용하셔도 좋은 별명 입니다.').setStyle({ color: 'blue' }); break;
        default : alert( '잘못된 접근입니다.\n\n' + result ); break;
    }
    $('mb_nick_enabled').value = result;
}


// E-mail 주소 검사
function reg_mb_email_check() {
    var url = member_skin_path + "/ajax_mb_email_check.php";
    var para = "reg_mb_id="+encodeURIComponent($F('reg_mb_id'));
        para += "&reg_mb_email="+encodeURIComponent($F('reg_mb_email'));
    var myAjax = new Ajax.Request(
        url, 
        {
            method: 'post', 
            // 주소창 보안 방지 javascript:void(document.fregisterform.mb_email_enabled.value='000');
            // 동기식 (폼전송시 입력값이 바른지 검사한 후 mb_email_enabled 를 체크하기 때문)
            asynchronous: false,
            parameters: para, 
            onComplete: return_reg_mb_email_check
        });
}

function return_reg_mb_email_check(req) { 
    var msg = $('msg_mb_email');
    var result = req.responseText;
    switch(result) {
        case '110' : msg.update('E-mail 주소를 입력하십시오.').setStyle({ color: 'red' }); break;
        case '120' : msg.update('E-mail 주소가 형식에 맞지 않습니다.').setStyle({ color: 'red' }); break;
        case '130' : msg.update('이미 존재하는 E-mail 주소입니다.').setStyle({ color: 'red' }); break;
        case '000' : msg.update('사용하셔도 좋은 E-mail 주소입니다.').setStyle({ color: 'blue' }); break;
        default : alert( '잘못된 접근입니다.\n\n' + result ); break;
    }
    $('mb_email_enabled').value = result;
}

// 세션에 저장된 토큰을 얻는다.
function get_token() {
    var url = member_skin_path + "/ajax_get_token.php";
    var para = "reg_mb_id="+encodeURIComponent($F('reg_mb_id'));
        para += "&reg_mb_email="+encodeURIComponent($F('reg_mb_email'));
    var myAjax = new Ajax.Request(
        url, 
        {
            method: 'post', 
            asynchronous: false,
            parameters: para, 
            onComplete: return_get_token
        });
}

function return_get_token(req) {
    var result = req.responseText;
    $('mb_token').value = result;
}