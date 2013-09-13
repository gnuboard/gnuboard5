<?php
/**
 * @class  SyndicationObject
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication 데이타 타입의 상위 클래스
 **/

class SyndicationObject
{
	var $id;
	var $title;
	var $content;
	var $summary;
	var $author;
	var $name;
	var $email;
	var $url;
	var $published;
	var $updated;
	var $deleted;
	var $type;
	var $link_self;
	var $link_alternative;
	var $link_rss;
	var $link_channel;
	var $link_channel_alternative;
	var $link_next_in_thread;

	function SyndicationXml()
	{
	}

	function get($node_name)
	{
		$node = array('id','author','name','email','url','title','type','summary','content','published','deleted','updated','link_rss','link_alternative','link_self','link_channel','link_channel_alternative','link_next_in_thread');
		if(!in_array($node_name, $node)) return '';

		if($node_name == 'author')
		{
			$str = "<author>\n";
			$str .= $this->get('name'); 
			$str .= $this->get('email');
			$str .= $this->get('url'); 
			$str .= "</author>\n";

			return $str;
		}

		$value = $this->{$node_name};
		if(!$value) return '';

		if(strpos($node_name,'link_')!==false)
		{
			$type = str_replace('_','-',substr($node_name, strlen('link_')));
			return "<link rel=\"".$type."\" href=\"".htmlspecialchars($value)."\" />\n";
		}

		if(in_array($node_name,array('published','deleted','updated')))
		{
			$value = $this->_getTime($value);
		}

		return sprintf("<%s>%s</%s>\n", $node_name, htmlspecialchars($value) ,$node_name);
	}

	function _getTime($time)
	{
		return SyndicationHandler::getTimestamp($time);
	}

	function wrapFeed($str)
	{
		$return = '<?xml version="1.0" encoding="utf-8"?>';
		$return .= "\n"; 
		$return .= '<feed xmlns="http://www.w3.org/2005/Atom">';
		$return .= "\n"; 
		$return .= $str;
		$return .= "</feed>";

		return $return;
	}

	function wrapEntry($str, $xml_info=false)
	{
		if($xml_info)
		{
			$return = '<?xml version="1.0" encoding="utf-8"?>';
			$return .= "\n"; 
			$return .= '<entry xmlns="http://www.w3.org/2005/Atom">';
			$return .= "\n"; 
		}
		else
		{
			$return .= "<entry>\n";
		}

		$return .= $str;
		$return .= "</entry>\n";

		return $return;
	}
}

?>
