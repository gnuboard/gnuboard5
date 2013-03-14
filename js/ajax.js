// request 객체 생성
var req = null;
function create_request() {
    var request = null;
    try {
        request = new XMLHttpRequest();
    } catch (trymicrosoft) {
        try {
            request = new ActiveXObject("Msxml12.XMLHTTP");
        } catch (othermicrosoft) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                request = null;
            }
        }
    }
    if (request == null)
        alert("Error creating request object!");
    else
        return request;
}

// 트랙백을 사용한다면 토큰을 실시간으로 생성
var trackback_url = "";
function trackback_send_server(url) {
    req = create_request();
    trackback_url = url;
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            if (req.status == 200) {
                var token = req.responseText;
                prompt("아래 주소를 복사하세요. 이 주소는 스팸을 막기 위하여 한번만 사용 가능합니다.", trackback_url+"/"+token);
                trackback_url = "";
            }
        }
    }
    req.open("POST", g4_path+'/'+g4_bbs+'/'+'tb_token.php', true);
    //req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
    req.send(null);
}
