<?
if (!defined('_GNUBOARD_')) exit;

/////////////////////////////////////////
//                                     //
//     mics'php - Trackback Sender     //
//                                     //
//     COPYLEFT (c) by micsland.com    //
//                                     //
//     MODIFIED (c) by sir.co.kr       //
//                                     //
/////////////////////////////////////////

// return 값이 있으면 오류, 없으면 정상
function send_trackback($tb_url, $url, $title, $blog_name, $excerpt) 
{
    /*
    // allow_url_fopen = Off 일 경우 트랙백 사용할 수 없었던 오류를 수정
    // allow_url_fopen = On 일 경우에만 사용 가능
    //주소가 유효한지 검사
	$p_fp = fopen($tb_url,"r");
	if($p_fp) 
        @fclose($p_fp);
	else 
        return "트랙백 URL이 존재하지 않습니다.";
    */

	//내용 정리
	$title = strip_tags($title);
	$excerpt = strip_tags($excerpt);

	$tmp_data = "url=".rawurlencode($url)."&title=".rawurlencode($title)."&blog_name=".rawurlencode($blog_name)."&excerpt=".rawurlencode($excerpt);

	//주소 처리
	$uinfo = parse_url($tb_url);
	if($uinfo[query]) $tmp_data .= "&".$uinfo[query];
	if(!$uinfo[port]) $uinfo[port] = "80";

	//최종 전송 자료
	$send_str = "POST ".$uinfo[path]." HTTP/1.1\r\n".
				"Host: ".$uinfo[host]."\r\n".
				"User-Agent: GNUBOARD\r\n".
				"Content-Type: application/x-www-form-urlencoded\r\n".
				"Content-length: ".strlen($tmp_data)."\r\n".
				"Connection: close\r\n\r\n".
				$tmp_data;
    $fp = @fsockopen($uinfo[host],$uinfo[port]);
	if(!$fp) 
        return "트랙백 URL이 존재하지 않습니다.";

	//전송
	//$fp = fsockopen($uinfo[host],$uinfo[port]);
	fputs($fp,$send_str);

	//응답 받음
	while(!feof($fp)) $response .= fgets($fp,128);
	fclose($fp);

	//트랙백 URL인지 확인
	if(!strstr($response,"<response>"))
		return "올바른 트랙백 URL이 아닙니다.";

	//XML 부분만 뽑음
	$response = strchr($response,"<?");
	$response = substr($response,0,strpos($response,"</response>"));

	//에러 검사
	if(strstr($response,"<error>0</error>")) 
        return "";
	else {
		$tb_error_str = strchr($response,"<message>");
		$tb_error_str = substr($tb_error_str,0,strpos($tb_error_str,"</message>"));
		$tb_error_str = str_replace("<message>","",$tb_error_str);
		return "트랙백 전송중 오류가 발생했습니다: $tb_error_str";
	}
}
?>