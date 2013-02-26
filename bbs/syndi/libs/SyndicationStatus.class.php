<?php
/**
 * @class  SyndicationStatus
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication 상태 정보 클래스
 **/

class SyndicationStatus
{
	// Status Server
	var $status_host = 'syndication.openapi.naver.com';
	var $site;

	function setSite($site)
	{
		$this->site = $site;
	}

	function request()
	{
		if(!$this->site) return false;

		$header = "GET /status/?site=".$this->site." HTTP/1.0\r\n".
				"Host: " . $this->status_host . "\r\n\r\n";

		$fp = @fsockopen($this->status_host, '80', $errno, $errstr); 
		if(!$fp) return false;

		$output = '';

		fputs($fp, $header);
		while(!feof($fp)){
			$output .= fgets($fp, 1024);
		}
		fclose($fp);

		$output = substr($output, strpos($output, "\r\n\r\n")+4); 
		return $this->_parse($output);
	}

	function _parse($data)
	{
		preg_match_all('@\<([a-z_0-9=\" ]+)\>([^\<]+)\</@', $data, $matches);
		if(!$matches[2]) return false;

		$output = array('article'=>array());

		for($i=0,$c=count($matches[0]);$i<$c;$i++){
			if(strpos($matches[1][$i], 'date="')!==false){
				$date = substr($matches[1][$i],14,8);
				$output['article'][$date] = $matches[2][$i];
			}else{
				$output[$matches[1][$i]] = $matches[2][$i];
			}
		}
		
		return $output;
	}
}

/*
$oStatus = new SyndicationStatus;
$oStatus->setSite('domain.com');
$output = $oStatus->request();

$output data fields
error	: 0이 아닌 경우 에러 
message	: 에러 메세지
site_url	: site url
site_name	: site name
first_update	: Syndication 서버에 처음 등록된 시간
last_update		: Syndication 서버에 최근 갱신 시간
status			: site 상태 
visit_ok_count	: ping 연속 성공 횟수
visit_fail_count	: ping 실패 횟수
*/
?>
