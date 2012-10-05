var reg_mb_id_check = function() {
    $.ajax({
        type: 'POST',
        url: member_skin_path+'/ajax_mb_id_check.php',
        data: {
            'reg_mb_id': encodeURIComponent($('#reg_mb_id').val())
        },
        cache: false,
        async: false,
        success: function(result) {
            var msg = $('#msg_mb_id');
            switch(result) {
                case '110' : msg.html('영문자, 숫자, _ 만 입력하세요.').css('color', 'red'); break;
                case '120' : msg.html('최소 3자이상 입력하세요.').css('color', 'red'); break;
                case '130' : msg.html('이미 사용중인 아이디 입니다.').css('color', 'red'); break;
                case '140' : msg.html('예약어로 사용할 수 없는 아이디 입니다.').css('color', 'red'); break;
                case '000' : msg.html('사용하셔도 좋은 아이디 입니다.').css('color', 'blue'); break;
                default : alert( '잘못된 접근입니다.\n\n' + result ); break;
            }
            $('#mb_id_enabled').val(result);
        }
    });
}

var reg_mb_nick_check = function() {
    $.ajax({
        type: 'POST',
        url: member_skin_path+'/ajax_mb_nick_check.php',
        data: {
            'reg_mb_nick': ($('#reg_mb_nick').val())
        },
        cache: false,
        async: false,
        success: function(result) {
            var msg = $('#msg_mb_nick');
            switch(result) {
                case '110' : msg.html('별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.').css('color', 'red'); break;
                case '120' : msg.html('한글 2글자, 영문 4글자 이상 입력 가능합니다.').css('color', 'red'); break;
                case '130' : msg.html('이미 존재하는 별명입니다.').css('color', 'red'); break;
                case '000' : msg.html('사용하셔도 좋은 별명 입니다.').css('color', 'blue'); break;
                default : alert( '잘못된 접근입니다.\n\n' + result ); break;
            }
            $('#mb_nick_enabled').val(result);
        }
    });
}

var reg_mb_email_check = function() {
    $.ajax({
        type: 'POST',
        url: member_skin_path+'/ajax_mb_email_check.php',
        data: {
            'reg_mb_id': encodeURIComponent($('#reg_mb_id').val()),
            'reg_mb_email': $('#reg_mb_email').val()
        },
        cache: false,
        async: false,
        success: function(result) {
            var msg = $('#msg_mb_email');
            switch(result) {
                case '110' : msg.html('E-mail 주소를 입력하십시오.').css('color', 'red'); break;
                case '120' : msg.html('E-mail 주소가 형식에 맞지 않습니다.').css('color', 'red'); break;
                case '130' : msg.html('이미 존재하는 E-mail 주소입니다.').css('color', 'red'); break;
                case '000' : msg.html('사용하셔도 좋은 E-mail 주소입니다.').css('color', 'blue'); break;
                default : alert( '잘못된 접근입니다.\n\n' + result ); break;
            }
            $('#mb_email_enabled').val(result);
        }
    });
}