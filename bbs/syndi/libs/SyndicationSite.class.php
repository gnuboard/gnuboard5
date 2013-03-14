<?php
/**
 * @class  SyndicationSite
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication Site 정보 클래스
 **/

class SyndicationSite extends SyndicationObject
{
	var $node = array('id','title','link_self','link_alternative','link_next_in_thread','updated');

	/**
	 * @brief URI Tag
	 **/
	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @brief Title of Site
	 **/
	function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @brief Syndication Ping Url
	 **/
	function setLinkSelf($link_self)
	{
		$this->link_self = $link_self;
	}

	/**
	 * @brief 접근 Url
	 **/
	function setLinkAlternative($link_alternative)
	{
		$this->link_alternative = $link_alternative;
	}
	
	/**
	 * @brief 다음 페이지 Url
	 **/
	function setNextPage($obj)
	{
		if(!$obj) return;

		if($obj['page']>0) $this->link_next_in_thread = $this->link_self . '&page='. $obj['page'];
		if($obj['channel_id']) $this->link_next_in_thread .= '&channel_id='. $obj['channel_id'];
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