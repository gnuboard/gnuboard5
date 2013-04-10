<?php
/**
 * @class  SyndicationChannel
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication Channel(게시판) 데이타 클래스
 **/

class SyndicationChannel extends SyndicationObject
{
	var $node = array('id','title','type','summary','updated','link_rss','link_alternative','link_self','link_next_in_thread');

	/**
	 * @brief URI Tag
	 **/
	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @brief Title of Channel
	 **/
	function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @brief Type of Channel, web or blog
	 * blog 일경우 setLinkRss()를 등록해야 함
	 **/
	function setType($type='web')
	{
		$this->type = $type;
	}

	/**
	 * @brief Summary of Channel
	 **/
	function setSummary($summary)
	{
		$this->summary = $summary;
	}

	/**
	 * @brief Syndication Ping Url
	 **/
	function setLinkSelf($link_self)
	{
		$this->link_self = $link_self;
	}

	/**
	 * @brief Channel(게시판) 접근 Url
	 **/
	function setLinkAlternative($link_alternative)
	{
		$this->link_alternative = $link_alternative;
	}

	/**
	 * @brief Channel RSS Url (type이 blog면 필수)
	 **/
	function setLinkRss($link_rss)
	{
		$this->link_rss = $link_rss;
	}

	/**
	 * @brief 다음 페이지 Url
	 **/
	function setNextPage($obj)
	{
		if(!$obj) return;

		if($obj['page']>0) $this->link_next_in_thread = $this->link_self . '&page='. $obj['page'];
	}

	/**
	 * @brief update time
	 * 'YYYYMMDDHHIISS' type
	 **/
	function setUpdated($updated)
	{
		$this->updated = $updated;
	}

	function __toString()
	{
		$str = '';
		foreach($this->node as $node){
			$str .= $this->get($node);
		}

		return $str;
	}
}
?>