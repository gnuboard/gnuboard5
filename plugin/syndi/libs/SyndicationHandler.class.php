<?php
/**
 * @class  SyndicationHandler
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication 데이타 출력 핸들러
 **/

class SyndicationHandler
{
	var $param;				// GET value
	var $type;				// 출력 내용 : article, deleted, channel, site
	var $target;			// 출력 대상 : article, channel, site
	var $target_channel_id;	// 출력 대상 channel(게시판) 번호 또는 유닉한 문자열
	var $target_content_id;	// 출력 대상 article(게시물) 번호 또는 유닉한 문자열
	var $register_function = array();

	function &getInstance()
	{
		if(!$GLOBALS['__SyndicationHandler__'])
		{
			$GLOBALS['__SyndicationHandler__'] = new SyndicationHandler();
		}

		return $GLOBALS['__SyndicationHandler__'];
	}

	/**
	 * @brief initialization.
	 **/
	function SyndicationHandler()
	{
	}

	/**
	 * @brief register function
	 * site_info
	 * article_list
	 * deleted_list
	 * channel_list
	 * article_next_page
	 * deleted_next_page
	 * channel_next_page
	 **/

	function checkRegisteredFunctions()
	{
		$ids = array('site_info','article_list','deleted_list','channel_list','article_next_page','deleted_next_page','channel_next_page');

		if(count($this->register_function) != count($ids)) return false;

		foreach($this->register_function as $id => $func)
		{
			if(!in_array($id, $ids) || !is_callable($func))
			{
				return false;
			}
		}

		return true;
	}

	function registerFunction($id, $func)
	{
		$this->register_function[$id] = $func;
	}

	function callFunction($id)
	{
		if(!isset($this->register_function[$id]) || !is_callable($this->register_function[$id])) return false;

		return call_user_func($this->register_function[$id], $this->param);
	}


	/**
	 * @brief set GET Value.
	 **/
	function setArgument($args=null)
	{
		if(!$args) $args = $_GET;

		$obj = new stdClass;
		$obj->id = $args['id'];

		if($args['start-time']) $obj->start_time = SyndicationHandler::getDate($args['start-time']);
		if($args['end-time']) $obj->end_time = SyndicationHandler::getDate($args['end-time']);

		$obj->max_entry = $args['max_entry'];
		if(!$obj->max_entry) $obj->max_entry = 100;

		$obj->page = $args['page'];
		if(!$obj->page) $obj->page = 1;

		$obj->type = $args['type'];

		// for getting all article on mysql 3.X, 4.0.X
		if($args['channel_id']) $obj->channel_id = $args['channel_id'];

		$this->param = $obj;
		$this->parseTag();
	}

	/**
	 * @brief parsing id value(Tag URI)
	 **/
	function parseTag()
	{
		// tag:domain,{YYYY}:site:
		// tag:domain,{YYYY}:channel:{channel_id}
		// tag:domain,{YYYY}:article:{channel_id}-{$article_id}
		if(!preg_match('/^tag:([^,]+),([0-9]+):(site|channel|article)(.*)$/i',$this->param->id,$matches)) return;

		$this->target = $matches[3];
		$id = $matches[4];
		if($id && $id{0}==':') $id = substr($id, 1);

		switch($this->target)
		{
			case 'site':
			break;
			case 'channel':
				if(!$id)
				{
					$this->target = $this->target_channel_id = $this->target_content_id = null;
					return;
				}

				$this->target_channel_id = $id;
			break;
			case 'article':
				if($id && strpos($id,'-')!==false)
				{
					list($this->target_channel_id, $this->target_content_id) = explode('-',$id);
					if(!$this->target_content_id)
					{
						$this->target = $this->target_channel_id = $this->target_content_id = null;
						return;
					}
				}
				else
				{
					$this->target = $this->target_channel_id = $this->target_content_id = null;
					return;
				}
			break;
		}

		if($this->target_channel_id) $this->param->target_channel_id = $this->target_channel_id;
		if($this->target_content_id) $this->param->target_content_id = $this->target_content_id;
	}

	/**
	 * @brief xml for Syndication Server
	 **/
	function getXML()
	{
		if(!$this->checkRegisteredFunctions()) return '';

		switch($this->target)
		{
			// in site
			case 'site':
				// get Site info
				$oSite = $this->callFunction('site_info');
				if(!$oSite) return '';

				$list_xml = '';

				if(in_array($this->param->type,array('article','deleted','channel')))
				{
					$list = $this->callFunction($this->param->type . '_list');
					if(!$list) $list = array();

					$obj = $this->callFunction($this->param->type . '_next_page');
					if($obj) $oSite->setNextPage($obj);

					foreach($list as $oObject)
					{
						$list_xml .= $oObject->wrapEntry($oObject->__toString());
					}

					$xml = $oSite->__toString();
					$xml = $oSite->wrapFeed($xml . $list_xml);
				}
				else
				{
					$xml = $oSite->__toString();
					$xml = $oSite->wrapEntry($xml . $list_xml, true);
				}

			break;

			// in channel
			case 'channel':
				// get Channel info
				$oChannelList = $this->callFunction('channel_list');
				if(!is_array($oChannelList) || count($oChannelList)==0) return '';
				$oChannel = $oChannelList[0];

				$list_xml = '';

				if($this->target_channel_id && in_array($this->param->type,array('article','deleted')))
				{
					$list = $this->callFunction($this->param->type . '_list');

					if(is_array($list) && count($list))
					{
						$obj = $this->callFunction($this->param->type . '_next_page');
						if($obj) $oChannel->setNextPage($obj);

						foreach($list as $oObject)
						{
							$list_xml .= $oObject->wrapEntry($oObject->__toString());
						}
					}

					$xml = $oChannel->__toString();
					$xml = $oChannel->wrapFeed($xml . $list_xml);
				}
				else
				{
					$xml = $oChannel->__toString();
					$xml = $oChannel->wrapEntry($xml . $list_xml, true);
				}

			break;

			// article info
			case 'article':

				if(in_array($this->param->type,array('article','deleted')))
				{
					$list = $this->callFunction($this->param->type . '_list');
				}

				if(!is_array($list) || count($list)==0) return '';

				$oObject = $list[0];
				$xml = $oObject->__toString();

				$xml = $oObject->wrapEntry($xml, true);

			break;
		}
		
		if(!$GLOBALS['syndi_from_encoding']) $GLOBALS['syndi_from_encoding'] = 'utf-8';
		if($xml && strtolower($GLOBALS['syndi_from_encoding']) != 'utf-8' && function_exists('iconv'))
		{
			$xml = iconv($GLOBALS['syndi_from_encoding'], 'utf-8//IGNORE', $xml);
		}

		return $xml;
	}

	/**
	 * @brief Tag URI 
	 **/
	function getTag($type, $channel_id=null, $article_id=null)
	{
		$tag = sprintf('tag:%s,%s:%s' 
				,$GLOBALS['syndi_tag_domain']
				,$GLOBALS['syndi_tag_year']
				,$type);

		if($type=='channel' && $channel_id)
		{
			$tag .= ':' . $channel_id;
		}
		else if($type=='article' && $channel_id && $article_id)
		{
			$tag .= ':' .$channel_id .'-' . $article_id;
		}

		return $tag;
	}

	/**
	 * @brief Timestamp 로 YYYYMMDDHHIISS 변환
	 **/
	function getDate($timestamp)
	{
		$time = strtotime($timestamp);
		if($time == -1) $time = SyndicationHandler::ztime(str_replace(array('-','T',':'),'',$timestamp));

		return date('YmdHis', $time);
	}

	/**
	 * @brief YYYYMMDDHHIISS에서 Timestamp로 변환
	 **/
	function getTimestamp($date)
	{
		$time = mktime(substr($date,8,2),substr($date,10,2),substr($date,12,2),substr($date,4,2),substr($date,6,2),substr($date,0,4));
		$timestamp = date("Y-m-d\\TH:i:s", $time). $GLOBALS['syndi_time_zone'];
		return $timestamp;
	}

	function ztime($str)
	{
		if(!$str) return;
		$hour = (int)substr($str,8,2);
		$min = (int)substr($str,10,2);
		$sec = (int)substr($str,12,2);
		$year = (int)substr($str,0,4);
		$month = (int)substr($str,4,2);
		$day = (int)substr($str,6,2);
		if(strlen($str) <= 8) 
		{
			$gap = 0;
		} 
		else 
		{
			$gap = SyndicationHandler::zgap();
		}

		return mktime($hour, $min, $sec, $month?$month:1, $day?$day:1, $year)+$gap;
	}

	function zgap() 
	{
		$time_zone = $GLOBALS['syndi_time_zone'];
		if($time_zone < 0) $to = -1; else $to = 1;
		$t_hour = substr($time_zone, 1, 2) * $to;
		$t_min = substr($time_zone, 3, 2) * $to;

		$server_time_zone = date("O");
		if($server_time_zone < 0) $so = -1; else $so = 1;
		$c_hour = substr($server_time_zone, 1, 2) * $so;
		$c_min = substr($server_time_zone, 3, 2) * $so;

		$g_min = $t_min - $c_min;
		$g_hour = $t_hour - $c_hour;

		$gap = $g_min*60 + $g_hour*60*60;

		return $gap;
	}

	function error($msg)
	{
		echo $msg;
		exit;
	}
}

?>
