<?php
/**
 * @class  SyndicationPing
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication Ping Request 클래스
 **/

class SyndicationPing
{
	// Ping Server
	var $ping_host = 'syndication.openapi.naver.com';
	var $client;
	var $type = 'article';
	var $id;
	var $start_time;
	var $end_time;
	var $max_entry;
	var $page;

	function setId($id)
	{
		$this->id = $id;
	}

	function setType($type)
	{
		$this->type = $type;	
	}

	function setStartTime($start_time)
	{
		if($start_time)
		{
			$this->start_time = $this->_convertTime($start_time);
		}
	}

	function setEndTime($end_time)
	{
		if($end_time)
		{
			$this->end_time = $this->_convertTime($end_time);
		}
	}

	function _convertTime($time)
	{
		return  str_replace('+','%2b',$time);
	}

	function setMaxEntry($max_entry)
	{
		if($max_entry > 0 && $max_entry <= 10000)
		{
			$this->max_entry = $max_entry;
		}
	}

	function setPage($page)
	{
		if($page > 0 && $page <= 10000)
		{
			$this->page = $page;
		}
	}

	function getBody()
	{
		$str = $GLOBALS['syndi_echo_url'];
		$str .= '?id=' . $this->id;
		$str .= '&type=' . $this->type;
		if($this->start_time && $this->end_time)
		{
			$str .= '&start_time=' . $this->start_time;
			$str .= '&end_time=' . $this->end_time;
		}
		if($this->max_entry) $str .= '&max_entry=' . $this->max_entry;
		if($this->page) $str .= '&page=' . $this->page;

		return 'link='.urlencode($str);
	}

	function request()
	{
		$body = $this->getBody();
		if(!$body) return false;

		$header = "POST /ping/ HTTP/1.0\r\n".
				"User-Agent: request\r\n".
				"Host: " . $this->ping_host . "\r\n".
				"Content-Type: application/x-www-form-urlencoded\r\n".
				"Content-Length: ". strlen($body) ."\r\n".
				"\r\n".
				$body;

		$fp = @fsockopen($this->ping_host, '80', $errno, $errstr, 5); 
		if(!$fp) return false;

		fputs($fp, $header);
		fclose($fp);

		return true;
	}
}
?>
