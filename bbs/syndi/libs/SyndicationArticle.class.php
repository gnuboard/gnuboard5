<?php
/**
 * @class  SyndicationArticle
 * @author sol (ngleader@gmail.com)
 * @brief  Syndication Article 데이타 클래스
 **/

class SyndicationArticle extends SyndicationObject
{
	var $node = array('id','author','category','title','content','published','updated','link_alternative','link_channel','link_channel_alternative');

	/**
	 * @brief URI Tag
	 **/
	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @brief Author name
	 **/
	function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @brief Author email
	 **/
	function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @brief Author homepage or blog
	 **/
	function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @brief category or tag of content
	 **/
	function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @brief Title of content
	 **/
	function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @brief content
	 **/
	function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * @brief Syndication Content Type으로 blog 경우'blog', 일반사이트 경우 'web'
	 **/
	function setType($type='web')
	{
		$this->type = $type;
	}

	/**
	 * @brief Syndication Ping Url
	 **/
	function setLinkSelf($link_self)
	{
		$this->link_self = $link_self;
	}

	/**
	 * @brief Channel Syndication Ping Url
	 **/
	function setLinkChannel($link_channel)
	{
		$this->link_channel = $link_channel;
	}

	/**
	 * @brief Article(게시물) 접근 Url
	 **/
	function setLinkAlternative($link_alternative)
	{
		$this->link_alternative = $link_alternative;
	}

	/**
	 * @brief Channel(게시판) 접근 Url
	 **/
	function setLinkChannelAlternative($link_channel_alternative)
	{
		$this->link_channel_alternative = $link_channel_alternative;
	}

	/**
	 * @brief published time
	 * 'YYYYMMDDHHIISS' type
	 **/
	function setPublished($published)
	{
		$this->published = $published;
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
