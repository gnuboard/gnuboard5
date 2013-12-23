<?php
/**
 * @file plugin/syndi/func/site.func.php
 * @author sol (ngleader@gmail.com)
 * @brief syndication client functions for gnuboard5
 * 
 * Syndi_getSiteInfo	: 사이트 정보
 * Syndi_getChannelList	: 사이트의 게시판
 * Syndi_getArticleList	: 게시물
 * Syndi_getDeletedList	: 삭제 게시물
 * Syndi_getChannelNextPage	: Syndi_getChannelList 의 페이징시 다음 페이지
 * Syndi_getArticleNextPage	: Syndi_getArticleList 의 페이징시 다음 페이지
 * Syndi_getDeletedNextPage	: Syndi_getDeletedList 의 페이징시 다음 페이지
 */


// include gnuboard config & lib
//include G5_PATH.'/config.php';
//include G5_LIB_PATH.'/common.lib.php';
//include G5_DATA_PATH.'/dbconfig.php';

$connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD);
if(!$connect_db) return;
$GLOBALS['connect_db'] = $connect_db;

sql_select_db(G5_MYSQL_DB, $connect_db);


/**
 * @brief 사이트 정보
 **/
function Syndi_getSiteInfo($args)
{
	$title = $GLOBALS['syndi_homepage_title'];
	$tag = SyndicationHandler::getTag('site');

	$oSite = new SyndicationSite;
	$oSite->setId($tag);
	$oSite->setTitle($title);
	$oSite->setUpdated(date('YmdHis'));

	// 홈페이지 주소
	$link_alternative = sprintf('%s',  G5_URL);
	$oSite->setLinkAlternative($link_alternative);

	$link_self = sprintf('%s?id=%s&type=%s',$GLOBALS['syndi_echo_url'],$tag,$args->type);
	$oSite->setLinkSelf($link_self);

	return $oSite;
}

/**
 * @brief Channel(게시판) 목록 
 **/
function Syndi_getChannelList($args)
{
	global $g5;

	$where = '';
	if($args->target_channel_id) $where .= " and b.bo_table='". mysql_real_escape_string($args->target_channel_id) . "'";

	$sql = "select b.bo_table,b.bo_subject from " . $g5['board_table'] . " b, ". $g5['group_table'] . " g where b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id ". $where;
	$sql .= " order by b.gr_id,b.bo_table";

	if($args->type=='channel')
	{
		$sql .= sprintf(" limit %s,%s", ($args->page-1)*$args->max_entry, $args->max_entry);
	}

	$result = sql_query($sql);

	$channel_list = array();
	while($row = sql_fetch_array($result))
	{
		$row['bo_subject'] = $row['bo_subject']?$row['bo_subject']:$row['bo_table'];

		$tag = SyndicationHandler::getTag('channel',$row['bo_table']);
		$oChannel = new SyndicationChannel;
		$oChannel->setId($tag);
		$oChannel->setTitle($row['bo_subject']);
		$oChannel->setType('web');
		$oChannel->setUpdated(date('YmdHis'));

		$link_self = sprintf('%s?id=%s&type=%s',$GLOBALS['syndi_echo_url'],$tag,$args->type);
		$oChannel->setLinkSelf($link_self);

		// 게시판 웹주소
		$link_alternative = sprintf('%s/bbs/board.php?bo_table=%s', G5_URL, $row['bo_table']);
		$oChannel->setLinkAlternative($link_alternative);

		$channel_list[] = $oChannel;
	}

	sql_free_result($result);

	return $channel_list;
}


/**
 * @brief 모든 게시판의 게시물을 가져올때, 다음 게시판을 가져옴
 **/
function _Syndi_getNextChannelId($channel_id=null)
{
	global $g5;

	if(!$channel_id)
	{
		$sql = "select b.bo_table from " . $g5['board_table'] . " b, ". $g5['group_table'] . " g where b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id";
		$sql .= " order by b.bo_table limit 1";

		$row = sql_fetch($sql);

		return $row['bo_table'];
	}

	$channel_id = mysql_real_escape_string($channel_id);

	$sql = "select b.bo_table from " . $g5['board_table'] . " b, ". $g5['group_table'] . " g where b.bo_table>'$channel_id' and b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id";
	$sql .= " order by b.bo_table limit 1";

	$result = sql_query($sql);
	if(mysql_num_rows($result)==0) return false;

	$row = sql_fetch_array($result);
	sql_free_result($result);

	return $row['bo_table'];
}


/**
 * @brief 게시물 목록 
 **/
function Syndi_getArticleList($args)
{
	global $g5;

	/*
	$args->target_content_id	//게시물 번호
	$args->target_channel_id	//게시판 번호
	$args->start_time			//기간
	$args->end_time
	$args->max_entry			//출력 목록당 개수
	$args->page					//페이지 번호
	$args->channel_id			//모든 글 출력시 해당 게시판
	*/

	// all channel articles mysql 3.X, 4.0.X 에서는 sub query가 되지 않는다.
	if(!$args->target_channel_id)
	{
		// $args->channel_id 가 없을 경우 첫번째 게시판을 가져온다.
		if(!$args->channel_id) $args->channel_id = _Syndi_getNextChannelId();
		$args->target_channel_id = $args->channel_id;
		$article_list = Syndi_getArticleList($args);
		unset($args->target_channel_id);

		if(count($article_list)>0) return $article_list;

		return array();
	}

	return _Syndi_getArticleList($args);
}


/**
 * @brief 게시물 목록 
 **/
function _Syndi_getArticleList($args)
{
	global $g5;

	/*
	$args->target_content_id	//게시물 번호
	$args->target_channel_id	//게시판 번호
	$args->start_time			//기간
	$args->end_time
	$args->max_entry			//출력 목록당 개수
	$args->page					//페이지 번호
	*/

	$sql = "select count(*) as cnt from " . $g5['board_table'] . " b, ". $g5['group_table'] . " g where b.bo_table='" . mysql_real_escape_string($args->target_channel_id). "' and b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id";
	$row = sql_fetch_array(sql_query($sql));
	if($row['cnt']<1) return array();

	$content_table = $t_board.'_'. $args->target_channel_id;
	$category_table = $t_category.'_'. $args->target_channel_id;

	// get article list
	$where = " and wr_is_comment=0 ";
	if($args->target_content_id) $where .= ' and wr_id='. mysql_real_escape_string($args->target_content_id);
	if($args->start_time) $where .= ' and wr_datetime >= '. _getTime($args->start_time);
	if($args->end_time) $where .= ' and wr_datetime <= '. _getTime($args->end_time);

	$sql = "select wr_id, ca_name, wr_subject, wr_content, mb_id, wr_name, wr_homepage, wr_email, wr_datetime, wr_last from " . $g5['write_prefix'] . $args->target_channel_id  . " where 1=1" . $where;
	$sql .= " order by wr_id desc ";
	$sql .= sprintf(" limit %s,%s", ($args->page-1)*$args->max_entry, $args->max_entry);

	$result = sql_query($sql);
	$article_list = array();
	while($row = sql_fetch_array($result))
	{
		$oArticle = new SyndicationArticle;
		$tag = SyndicationHandler::getTag('article', $args->target_channel_id, $row['wr_id']);
		$oArticle->setId($tag);
		$oArticle->setTitle($row['wr_subject']);
		$oArticle->setContent($row['wr_content']);
		$oArticle->setType('web');
		$oArticle->setCategory($row['ca_name']);
		$oArticle->setName($row['wr_name']);
		$oArticle->setEmail($row['wr_email']);
		$oArticle->setUrl($row['wr_homepage']);
		$oArticle->setPublished(date('YmdHis',_getTime($row['wr_datetime'])));
		if($row['wr_last']) $oArticle->setUpdated(date('YmdHis',_getTime($row['wr_last'])));
		
		// 게시판 웹주소
		$link_channel_alternative = sprintf('%s/bbs/board.php?bo_table=%s',G5_URL,$args->target_channel_id);

		// 게시물 웹주소
		$link_alternative = $link_channel_alternative . '&wr_id=' . $row['wr_id'];

		$oArticle->setLinkChannel($tag);
		$oArticle->setLinkAlternative($link_alternative);
		$oArticle->setLinkChannelAlternative($link_channel_alternative);

		// add list
		$article_list[] = $oArticle;
	}

	sql_free_result($result);

	return $article_list;
}



/**
 * @brief 삭제 게시물 목록 
 * 삭제된 게시물에 대해 logging이 필요
 **/
function Syndi_getDeletedList($args)
{
    global $g5;
	$table = $g5['syndi_log_table'];

	// get delete article list
	$where = '';
	if($args->target_content_id) $where .= " and content_id='" . mysql_real_escape_string($args->target_content_id) . "'";
	if($args->target_channel_id) $where .= " and bbs_id='" . mysql_real_escape_string($args->target_channel_id) . "'";
	if($args->start_time) $where .= ' and delete_date >= '. $args->start_time;
	if($args->end_time) $where .= ' and delete_date <= '. $args->end_time;

	$sql = "select content_id, bbs_id, title, link_alternative, delete_date from $table where 1=1" . $where;
	$sql .= " order by delete_date desc ";	
	$sql .= sprintf(" limit %s,%s", ($args->page-1)*$args->max_entry, $args->max_entry);
	$result = sql_query($sql);

	$deleted_list = array();
	while($row = sql_fetch_array($result))
	{
		$oDeleted = new SyndicationDeleted;
		$tag = SyndicationHandler::getTag('article', $row['bbs_id'], $row['content_id']);
		$oDeleted->setId($tag);
		$oDeleted->setTitle($row['title']);
		$oDeleted->setUpdated($row['delete_date']);
		$oDeleted->setDeleted($row['delete_date']);

		if(substr($row['link_alternative'],0,2)=='./')
		{
			$row['link_alternative'] = G5_URL . substr($row['link_alternative'],1);
		}
		$oDeleted->setLinkAlternative($row['link_alternative']);

		$deleted_list[] = $oDeleted;
	}

	sql_free_result($result);

	return $deleted_list;

}


/**
 * @brief Channel 목록 출력시 다음 페이지 번호 
 * return array('page'=>다음 페이지);
 **/
function Syndi_getChannelNextPage($args)
{
	global $g5;

	$where = '';
	if($args->target_channel_id) $where .= " and bo_table='". mysql_real_escape_string($args->target_channel_id) . "'";

	$count_sql = "select count(*) as cnt from " . $g5['board_table'] . "  where bo_read_level=1 and bo_list_level=1 " .$where;
	$result = sql_query($count_sql);
	$row = sql_fetch_array($result);
	sql_free_result($result);

	$total_count = $row['cnt'];
	$total_page = ceil($total_count / $args->max_entry);

	if($args->page >= $total_page)
	{
		return false;
	}
	else
	{
		return array('page'=>$args->page+1);
	}
}



/**
 * @brief 게시물 목록 출력시 다음 페이지
 * return array('page'=>다음 페이지, 'channel_id'=>다음 게시판)
 **/
function Syndi_getArticleNextPage($args)
{
	global $g5;

	// 사이트 모든 글
	if(!$args->target_channel_id)
	{
		// channel_id 라는 변수로 게시판id를 받는다 (mysql 3.x,4.0.x을 위해)
		if(!$args->channel_id)
		{
			$args->channel_id = _Syndi_getNextChannelId();
			$args->all_channel = true;
		}

		$args->target_channel_id = $args->channel_id;
		$obj = _Syndi_getArticleNextPage($args);

		unset($args->target_channel_id,	$args->all_channel);

		return $obj;
	}
	else
	{
		return _Syndi_getArticleNextPage($args);
	}
}

function _Syndi_getArticleNextPage($args)
{
	global $g5;

	$sql = "select count(*) as cnt from " . $g5['board_table'] . " b, ". $g5['group_table'] . " g where b.bo_table='" . mysql_real_escape_string($args->target_channel_id). "' and b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id";
	
	if($row['cnt']==0) return false;

	// get article list
	$where = " and wr_is_comment=0 ";
	if($args->target_content_id) $where .= ' and wr_id='. mysql_real_escape_string($args->target_content_id);
	if($args->start_time) $where .= ' and wr_datetime >= '. _getTime($args->start_time);
	if($args->end_time) $where .= ' and wr_datetime <= '. _getTime($args->end_time);

	$count_sql = "select count(*) as cnt from " . $g5['write_prefix'] . $args->target_channel_id  . " where 1=1 " .$where;
	$result = sql_query($count_sql);

	$row = sql_fetch_array($result);
	sql_free_result($result);

	$total_count = $row['cnt'];
	$total_page = ceil($total_count / $args->max_entry);

	if($args->page >= $total_page)
	{
		if($args->all_channel)
		{
			$next_channel_id = _Syndi_getNextChannelId($args->target_channel_id);
			if(!$next_channel_id) return false;
			return array('page'=>1, 'channel_id'=>$next_channel_id);
		}
		else
		{
			return false;
		}
	}
	else
	{
		return array('page'=>$args->page+1);
	}
}


/**
 * @brief 게시물 삭제 목록 출력시 다음 페이지 번호 
 **/
function Syndi_getDeletedNextPage($args)
{
    global $g5;
	$table = $g5['syndi_log_table'];

	// get delete article list
	$where = '';
	if($args->target_content_id) $where .= " and no='" . mysql_real_escape_string($args->target_content_id) . "'";
	if($args->target_channel_id) $where .= " and bbs_id='" . mysql_real_escape_string($args->target_channel_id) . "'";
	if($args->start_time) $where .= ' and delete_date >= '. $args->start_time;
	if($args->end_time) $where .= ' and delete_date <= '. $args->end_time;

	$count_sql = "select count(*) as cnt from $table where 1=1" .$where;
	$result = sql_query($count_sql);
	$row = sql_fetch_array($result);
	sql_free_result($result);
	
	$total_count = $row['cnt'];
	$total_page = ceil($total_count / $args->max_entry);

	if($args->page >= $total_page)
	{
		return array('page'=>0);
	}
	else
	{
		return array('page'=>$args->page+1);
	}
}


function _getTime($date)
{
	return strtotime($date);
}

$oSyndicationHandler = &SyndicationHandler::getInstance();
$oSyndicationHandler->registerFunction('site_info','Syndi_getSiteInfo');
$oSyndicationHandler->registerFunction('channel_list','Syndi_getChannelList');
$oSyndicationHandler->registerFunction('channel_next_page','Syndi_getChannelNextPage');
$oSyndicationHandler->registerFunction('article_list','Syndi_getArticleList');
$oSyndicationHandler->registerFunction('article_next_page','Syndi_getArticleNextPage');
$oSyndicationHandler->registerFunction('deleted_list','Syndi_getDeletedList');
$oSyndicationHandler->registerFunction('deleted_next_page','Syndi_getDeletedNextPage');

?>
