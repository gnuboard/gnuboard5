<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PHPMAILER_PATH.'/class.phpmailer.php');

// 메일 보내기 (파일 여러개 첨부 가능)
// type : text=0, html=1, text+html=2
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    global $config;
    global $g5;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    if ($type != 1)
        $content = nl2br($content);

    $mail = new PHPMailer(); // defaults to using php "mail()"
    if (defined('G5_SMTP') && G5_SMTP) {
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = G5_SMTP; // SMTP server
    }
    $mail->From = $fmail;
    $mail->FromName = $fname;
    $mail->Subject = $subject;
    $mail->AltBody = ""; // optional, comment out and test
    $mail->MsgHTML($content);
    $mail->AddAddress($to);
    if ($cc)
        $mail->AddCC($cc);
    if ($bcc)
        $mail->AddBCC($bcc);
    //print_r2($file); exit;
    if ($file != "") {
        foreach ($file as $f) {
            $mail->AddAttachment($f['path'], $f['name']);
        }
    }
    return $mail->Send();
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = G5_DATA_PATH.'/tmp/'.str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    /*
    $fp = fopen($tmp_name, "r");
    $tmpfile = array(
        "name" => $filename,
        "tmp_name" => $tmp_name,
        "data" => fread($fp, filesize($tmp_name)));
    fclose($fp);
    */
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}

/*
// 메일 보내기 (파일 여러개 첨부 가능)
// type : text=0, html=1, text+html=2
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    global $config;
    global $g5;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    $boundary = uniqid(time());

    $header = "Message-ID: <".generate_mail_id(preg_replace("/@.+$/i","",$to)).">\r\n".
              "From:=?utf-8?B?".base64_encode($fname)."?=<$fmail>\r\n";
    if ($cc)  $header .= "Cc: $cc\n";
    if ($bcc) $header .= "Bcc: $bcc\n";
    $header .= "MIME-Version: 1.0\n";
    $header .= "X-Mailer: SIR Mailer 0.94 : {$_SERVER['SERVER_ADDR']} : {$_SERVER['REMOTE_ADDR']} : ".G5_URL." : {$_SERVER['SCRIPT_NAME']} : {$_SERVER['HTTP_REFERER']} \n";
    $header .= "Date: ".date ("D, j M Y H:i:s T",time())."\r\n".
               "To: $to\r\n".
               "Subject: =?utf-8?B?".base64_encode($subject)."?=\r\n";

    if ($file == "") {
        $header .= "Content-Type: MULTIPART/ALTERNATIVE;\n".
                   "              BOUNDARY=\"$boundary\"\n\n";
    } else {
        $header .= "Content-Type: MULTIPART/MIXED;\n".
                   "              BOUNDARY=\"$boundary\"\n\n";
    }

    if ($type == 2)
        $content = nl2br($content);

    $strip_content  = stripslashes(trim($content));
    $encode_content = chunk_split(base64_encode($strip_content));

    $body = "";
    $body .= "\n--$boundary\n";
    $body .= "Content-Type: TEXT/PLAIN; charset=utf-8\n";
    $body .= "Content-Transfer-Encoding: BASE64\n\n";
    $body .= $encode_content;
    $body .= "\n--$boundary\n";

    if ($type) {
        $body .= "Content-Type: TEXT/HTML; charset=utf-8\n";
        $body .= "Content-Transfer-Encoding: BASE64\n\n";
        $body .= $encode_content;
        $body .= "\n--$boundary\n";
    }

    if ($file != "") {
        foreach ($file as $f) {
            $body .= "n--$boundary\n";
            $body .= "Content-Type: APPLICATION/OCTET-STREAM; name=$fname\n";
            $body .= "Content-Transfer-Encoding: BASE64\n";
            $body .= "Content-Disposition: inline; filename=$fname\n";

            $body .= "\n";
            $body .= chunk_split(base64_encode($f['data']));
            $body .= "\n";
        }
        $body .= "--$boundary--\n";
    }

    $mails['to'] = $to;
    $mails['from'] = $fmail;
    $mails['text'] = $header.$body;

    if (defined(G5_SMTP)) {
        ini_set('SMTP', G5_SMTP);
        @mail($to, $subject, $body, $header, "-f $fmail");
    } else {
        new maildaemon($mails);
    }
}

// 파일 첨부시
$fp = fopen(__FILE__, "r");
$file[] = array(
    "name"=>basename(__FILE__),
    "data"=>fread($fp, filesize(__FILE__)));
fclose($fp);

// 메일 유효성 검사
// core PHP Programming 책 참고
// hanmail.net , hotmail.com , kebi.com 등이 정상적이지 않음으로 사용 불가
function verify_email($address, &$error)
{
    global $g5;

    $WAIT_SECOND = 3; // ?초 기다림

    list($user, $domain) = explode("@", $address);

    // 도메인에 메일 교환기가 존재하는지 검사
    if (checkdnsrr($domain, "MX")) {
        // 메일 교환기 레코드들을 얻는다
        if (!getmxrr($domain, $mxhost, $mxweight)) {
            $error = '메일 교환기를 회수할 수 없음';
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
        $stoptime = G5_SERVER_TIME + $WAIT_SECOND;
        $gotresponse = false;

        while (true) {
            // 메일서버로부터 한줄 얻음
            $line = fgets($fp, 1024);

            if (substr($line, 0, 3) == '220') {
                // 타이머를 초기화
                $stoptime = G5_SERVER_TIME + $WAIT_SECOND;
                $gotresponse = true;
            } else if ($line == '' && $gotresponse)
                break;
            else if (G5_SERVER_TIME > $stoptime)
                break;
        }

        // 이 호스트는 응답이 없음. 다음 호스트로 넘어간다
        if (!$gotresponse) continue;

        socket_set_blocking($fp, true);

        // SMTP 서버와의 대화를 시작
        fputs($fp, "HELO {$_SERVER['SERVER_NAME']}\r\n");
        echo "HELO {$_SERVER['SERVER_NAME']}\r\n";
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

        if (substr($line, 0, 3) != '250') {
            // SMTP 서버가 이 주소를 인식하지 못하므로 잘못된 주소임
            $error = $line;
            return false;
        } else
            // 주소를 인식했음
            return true;

    }

    $error = '메일 교환기에 도달하지 못하였습니다.';
    return false;
}


# jsboard 의 메일보내기 class를 추가합니다. 130808
# http://kldp.net/projects/jsboard/

# mail 보내기 함수 2001.11.30 김정균
# $Id: include/sendmail.php,v 1.4 2009/11/19 05:29:51 oops Exp $

# 서버상의 smtp daemon 에 의존하지 않고 직접 발송하는 smtp class
#
# 특정 배열로 class 에 전달을 하여 메일을 발송한다. 배열은 아래을 참조한다.
#
# debug -> debug 를 할지 안할지를 결정한다.
# ofhtml -> 웹상에서 사용할지 쉘상에서 사용할지를 결정한다.
# from -> 메일을 발송하는 사람의 메일주소
# to -> 메일을 받을 사람의 메일 주소
# text -> 헤더 내용을 포함한 메일 본문
#
class maildaemon {
  var $failed = 0;

  function __construct($v) {
    $this->debug = $v['debug'];
    $this->ofhtml = $v['ofhtml'];
    if($_SERVER['SERVER_NAME']) $this->helo = $_SERVER['SERVER_NAME'];
    if(!$this->helo || preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i",$this->helo))
      $this->helo = "JSBoardMessage";

    $this->from = $v['from'];
    $this->to   = $v['to'];
    $this->body = $v['text']."\r\n.";

    //die($v['text']);
    $this->newline = $this->ofhtml ? "<br>\n" : "\n";

    $this->mx = $this->getMX($this->to);

    if($this->debug) {
      echo "DEBUG: ".$this->mx." start".$this->newline;
      echo "################################################################".$this->newline;
    }
    $this->sockets("open");
    $this->send("HELO ".$this->helo);
    $this->send("MAIL FROM: <".$this->from.">");
    $this->send("RCPT TO: <".$this->to.">");
    $this->send("data");
    $this->send($this->body);
    $this->send("quit");
    $this->sockets("close");
  }

  function getMX($email) {
    $dev = explode("@",$email);
    $account = $dev[0];
    $host = $dev[1];

    if(checkdnsrr($host,"MX") && getmxrr($host,$mx,$weight)) {
      $idx = 0;
      for($i=0;$i<sizeof($mx);$i++) {
        $dest = $dest ? $dest : $weight[$i];
        if($dest > $weight[$i]) {
          $dest = $weight[$i];
          $idx = $i;
        }
      }
    } else return $host;
    return $mx[$idx];
  }

  # 디버그 함수
  #  $t -> 1 (debug of socket open,close)
  #        0 (regular smtp message)
  #  $p -> 1 (print detail debug)
  #
  # return 1 -> success
  # return 0 -> failed
  #
  function debug($str,$t=0,$p=0) {
    if($t) {
      if(!$str) $this->failed = 1;
      if($this->sock) $returnmsg = trim(fgets($this->sock,1024));
    } else {
      if(!preg_match("/^(220|221|250|251|354)$/",substr(trim($str),0,3)))
        $this->failed = 1;
    }

    # DEBUG mode -> 모든 메세지 출력
    if($p) {
      if($t) {
        $str = "Conncet ".$this->mx;
        $str .= $this->failed ? " Failed" : " Success";
        $str .= $this->newline."DEBUG: $returnmsg";
      }
      echo "DEBUG: $str".$this->newline;
    }

    # DEBUG 모드가 아닐때, 에러 메세지 출력
    if(!$p && $this->failed) {
      if($this->ofhtml) echo "<SCRIPT>\nalert('$str')\n</SCRIPT>\n";
      else "ERROR: $str\n";
    }
  }

  function sockets($option=0) {
    switch($option) {
      case "open" :
        $this->sock = @fsockopen($this->mx,25,$this->errno,$this->errstr,30);
        $this->debug($this->sock,1,$this->debug);
        break;
      default :
        if($this->sock) fclose($this->sock);
        break;
    }
  }

  function send($str,$chk=0) {
    if(!$this->failed) {
      if($this->debug) {
        if(preg_match("/\r\n/",trim($str)))
          $str_debug = trim(str_replace("\r\n","\r\n       ",$str));
        else $str_debug = $str;
      }
      fputs($this->sock,"$str\r\n");
      $recv = trim(fgets($this->sock,1024));
      $recvchk = $recv;
      $this->debug($recv,0,$this->debug);

      if(preg_match("/Mail From:/i",$str) && preg_match("/exist|require|error/i",$recvchk) && !$chk) {
        $this->failed = 0;
        $this->send("MAIL FROM: <".$this->to.">",1);
      }
    }
  }
}


function generate_mail_id($uid) {
  $id = date("YmdHis",time());
  mt_srand((float) microtime() * 1000000);
  $randval = mt_rand();
  $id .= $randval."@$uid";
  return $id;
}


function mail_header($to,$from,$title,$mta=0) {
  global $langs,$boundary;

  # mail header 를 작성
  $boundary = get_boundary_msg();
  $header = "Message-ID: <".generate_mail_id(preg_replace("/@.+$/i","",$to)).">\r\n".
            "From:=?utf-8?B?".base64_encode('보내는사람')."?=<$from>\r\n".
            "MIME-Version: 1.0\r\n";

  if(!$mta) $header .= "Date: ".date ("D, j M Y H:i:s T",time())."\r\n".
                       "To: $to\r\n".
                       "Subject: $title\r\n";

  $header .= "Content-Type: multipart/alternative;\r\n".
             "              boundary=\"$boundary\"\r\n\r\n";

  return $header;
}


function get_boundary_msg() {
  $uniqchr = uniqid("");
  $one = strtoupper($uniqchr[0]);
  $two = strtoupper(substr($uniqchr,0,8));
  $three = strtoupper(substr(strrev($uniqchr),0,8));
  return "----=_NextPart_000_000${one}_${two}.${three}";
}
*/
?>