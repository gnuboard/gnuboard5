<?php
/**
 * @class  SyndicationDeleted
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication Delete(Article) 데이타 클래스
 **/

class SyndicationDeleted extends SyndicationObject
{
	var $node = array('id','title','deleted','updated','link_alternative');

	/**
	 * @brief URI Tag
	 **/
	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @brief Title of content
	 **/
	function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @brief update time
	 * 'YYYYMMDDHHIISS' type
	 **/
	function setUpdated($updated)
	{
		$this->updated = $updated;
	}

	/**
	 * @brief deleted time
	 * 'YYYYMMDDHHIISS' type
	 **/
	function setDeleted($deleted)
	{
		$this->deleted = $deleted;
	}

	/**
	 * @brief Article(게시물) 접근 Url
	 **/
	function setLinkAlternative($link_alternative)
	{
		$this->link_alternative = $link_alternative;
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
