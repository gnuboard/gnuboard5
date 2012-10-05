<?
if (!defined("_GNUBOARD_")) exit;

// 메일 보내기 (파일 여러개 첨부 가능)
// type : text=0, html=1, text+html=2
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="") 
{
    global $config;
    global $g4;

    // 메일발송 사용을 하지 않는다면
    if (!$config[cf_email_use]) return;

    $fname   = "=?$g4[charset]?B?" . base64_encode($fname) . "?=";
    $subject = "=?$g4[charset]?B?" . base64_encode($subject) . "?=";
    //$g4[charset] = ($g4[charset] != "") ? "charset=$g4[charset]" : "";

    $header  = "Return-Path: <$fmail>\n";
    $header .= "From: $fname <$fmail>\n";
    $header .= "Reply-To: <$fmail>\n";
    if ($cc)  $header .= "Cc: $cc\n";
    if ($bcc) $header .= "Bcc: $bcc\n";
    $header .= "MIME-Version: 1.0\n";
    //$header .= "X-Mailer: SIR Mailer 0.91 (sir.co.kr) : $_SERVER[SERVER_ADDR] : $_SERVER[REMOTE_ADDR] : $g4[url] : $_SERVER[PHP_SELF] : $_SERVER[HTTP_REFERER] \n";
    // UTF-8 관련 수정
    $header .= "X-Mailer: SIR Mailer 0.92 (sir.co.kr) : $_SERVER[SERVER_ADDR] : $_SERVER[REMOTE_ADDR] : $g4[url] : $_SERVER[PHP_SELF] : $_SERVER[HTTP_REFERER] \n";

    if ($file != "") {
        $boundary = uniqid("http://sir.co.kr/");

        $header .= "Content-type: MULTIPART/MIXED; BOUNDARY=\"$boundary\"\n\n";
        $header .= "--$boundary\n";
    }

    if ($type) {
        $header .= "Content-Type: TEXT/HTML; charset=$g4[charset]\n";
        if ($type == 2)
            $content = nl2br($content);
    } else {
        $header .= "Content-Type: TEXT/PLAIN; charset=$g4[charset]\n";
        $content = stripslashes($content);
    }
    $header .= "Content-Transfer-Encoding: BASE64\n\n";
    $header .= chunk_split(base64_encode($content)) . "\n";

    if ($file != "") {
        foreach ($file as $f) {
            $header .= "\n--$boundary\n";
            $header .= "Content-Type: APPLICATION/OCTET-STREAM; name=\"$f[name]\"\n";
            $header .= "Content-Transfer-Encoding: BASE64\n";
            $header .= "Content-Disposition: inline; filename=\"$f[name]\"\n";

            $header .= "\n";
            $header .= chunk_split(base64_encode($f[data]));
            $header .= "\n";
        }
        $header .= "--$boundary--\n";
    }
    @mail($to, $subject, "", $header);
}

// 파일 첨부시
/*
$fp = fopen(__FILE__, "r");
$file[] = array(
    "name"=>basename(__FILE__),
    "data"=>fread($fp, filesize(__FILE__)));
fclose($fp);
*/

// 파일을 첨부함
function attach_file($filename, $file)
{
    $fp = fopen($file, "r");
    $tmpfile = array(
        "name" => $filename,
        "data" => fread($fp, filesize($file)));
    fclose($fp);
    return $tmpfile;
}

// 메일 유효성 검사
// core PHP Programming 책 참고
// hanmail.net , hotmail.com , kebi.com 등이 정상적이지 않음으로 사용 불가
function verify_email($address, &$error)
{
    global $g4;

    $WAIT_SECOND = 3; // ?초 기다림

    list($user, $domain) = explode("@", $address);

    // 도메인에 메일 교환기가 존재하는지 검사
    if (checkdnsrr($domain, "MX")) {
        // 메일 교환기 레코드들을 얻는다
        if (!getmxrr($domain, $mxhost, $mxweight)) {
            $error = "메일 교환기를 회수할 수 없음";
            return false;
        }
    } else {
        // 메일 교환기가 없으면, 도메인 자체가 편지를 받는 것으로 간주
        $mxhost[] = $domain;
        $mxweight[] = 1;
    }

    // 메일 교환기 호스트의 배열을 만든다.
    for ($i=0; $i<count($mxhost); $i++)
        $weighted_host[($mxweight[$i])] = $mxhost[$i];
    ksort($weighted_host);

    // 각 호스트를 검사
    foreach($weighted_host as $host) {
        // 호스트의 SMTP 포트에 연결
        if (!($fp = @fsockopen($host, 25))) continue;

        // 220 메세지들은 건너뜀
        // 3초가 지나도 응답이 없으면 포기
        socket_set_blocking($fp, false);
        $stoptime = $g4[server_time] + $WAIT_SECOND;
        $gotresponse = false;

        while (true) {
            // 메일서버로부터 한줄 얻음
            $line = fgets($fp, 1024);

            if (substr($line, 0, 3) == "220") {
                // 타이머를 초기화
                $stoptime = $g4[server_time] + $WAIT_SECOND;
                $gotresponse = true;
            } else if ($line == "" && $gotresponse)
                break;
            else if ($g4[server_time] > $stoptime)
                break;
        }

        // 이 호스트는 응답이 없음. 다음 호스트로 넘어간다
        if (!$gotresponse) continue;

        socket_set_blocking($fp, true);

        // SMTP 서버와의 대화를 시작
        fputs($fp, "HELO $_SERVER[SERVER_NAME]\r\n");
        echo "HELO $_SERVER[SERVER_NAME]\r\n";
        fgets($fp, 1024);

        // From을 설정
        fputs($fp, "MAIL FROM: <info@$domain>\r\n");
        echo "MAIL FROM: <info@$domain>\r\n";
        fgets($fp, 1024);

        // 주소를 시도
        fputs($fp, "RCPT TO: <$address>\r\n");
        echo "RCPT TO: <$address>\r\n";
        $line = fgets($fp, 1024);

        // 연결을 닫음
        fputs($fp, "QUIT\r\n");
        fclose($fp);

        if (substr($line, 0, 3) != "250") {
            // SMTP 서버가 이 주소를 인식하지 못하므로 잘못된 주소임
            $error = $line;
            return false;
        } else
            // 주소를 인식했음
            return true;

    }
    
    $error = "메일 교환기에 도달하지 못하였습니다.";
    return false;
}
?>