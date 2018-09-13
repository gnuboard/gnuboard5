<?php
if (!defined('_GNUBOARD_')) exit;

function get_config($is_cache=false){
    global $g5;

    static $cache = array();

    $cache = apply_replace('get_config_cache', $cache, $is_cache);

    if( $is_cache && !empty($cache) ){
        return $cache;
    }

    $sql = " select * from {$g5['config_table']} ";
    $cache = apply_replace('get_config', sql_fetch($sql));

    return $cache;
}

function get_content_db($co_id, $is_cache=false){
    global $g5;

    static $cache = array();

    $co_id = preg_replace('/[^a-z0-9_]/i', '', $co_id);
    $key = md5($co_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select * from {$g5['content_table']} where co_id = '$co_id' ";

    $cache[$key] = sql_fetch($sql);

    return $cache[$key];
}

function get_board_names(){
    global $g5;

    static $boards = array();

    if( ! $boards ){
        $sql = " select bo_table from {$g5['board_table']} ";
        $result = sql_query($sql);

        while ($row = sql_fetch_array($result)) {
            $boards[] = $row['bo_table'];
        }
    }

    return $boards;
}

function get_board_db($bo_table, $is_cache=false){
    global $g5;

    static $cache = array();

    $cache = apply_replace('get_board_db_cache', $cache, $bo_table, $is_cache);

    $key = md5($bo_table);

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    if( !($cache[$key] = apply_replace('get_board_db', array(), $bo_table)) ){

        $sql = " select * from {$g5['board_table']} where bo_table = '$bo_table' ";

        $cache[$key] = sql_fetch($sql);

    }

    return $cache[$key];
}

// 게시판 첨부파일 테이블에서 하나의 행을 읽음
function get_board_file_db($bo_table, $wr_id, $fields='*', $add_where='', $is_cache=false)
{
    global $g5;

    static $cache = array();

    $wr_id = (int) $wr_id;
    $key = md5($bo_table.'|'.$wr_id.$fields.$add_where);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select $fields from {$g5['board_file_table']}
                where bo_table = '$bo_table' and wr_id = '$wr_id' $add_where order by bf_no limit 0, 1 ";

    $cache[$key] = sql_fetch($sql);

    return $cache[$key];
}

function get_poll_db($po_id, $is_cache=false){
    global $g5;

    static $cache = array();

    $po_id = (int) $po_id;
    $key = md5($po_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select * from {$g5['poll_table']} where po_id = '{$po_id}' ";

    $cache[$key] = sql_fetch($sql);

    return $cache[$key];
}

function get_point_db($po_id, $is_cache=false){
    global $g5;

    static $cache = array();

    $po_id = (int) $po_id;
    $key = md5($po_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select * from {$g5['point_table']} where po_id = '{$po_id}' ";

    $cache[$key] = sql_fetch($sql);

    return $cache[$key];
}

function get_mail_content_db($ma_id, $is_cache=false){
    global $g5;

    static $cache = array();

    $ma_id = (int) $ma_id;
    $key = md5($ma_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select * from {$g5['mail_table']} where ma_id = '{$ma_id}' ";

    $cache[$key] = sql_fetch($sql);

    return $cache[$key];
}

function get_qacontent_db($qa_id, $is_cache=false){
    global $g5;

    static $cache = array();

    $qa_id = (int) $qa_id;
    $key = md5($qa_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select * from {$g5['qa_content_table']} where qa_id = '{$qa_id}' ";

    $cache[$key] = sql_fetch($sql);

    return $cache[$key];
}

function get_thumbnail_find_cache($bo_table, $wr_id, $wr_key){
    global $g5;

    if( $cache_content = g5_latest_cache_data($bo_table, array(), $wr_id) ){
        if( $wr_key === 'content' ){
            return $cache_content;
        } else if ( $wr_key === 'file' && isset($cache_content['first_file_thumb']) ){
            return $cache_content['first_file_thumb'];
        }
    }

    if( $wr_key === 'content' ){
        $write_table = $g5['write_prefix'].$bo_table;
        return get_write_db($write_table, $wr_id, 'wr_content', true);
    }

    return get_board_file_db($bo_table, $wr_id, 'bf_file, bf_content', "and bf_type between '1' and '3'", true);
}

function get_write_table_name($bo_table){
    global $g5;

    return $g5['write_prefix'].preg_replace('/[^a-z0-9_]/i', '', $bo_table);
}

function get_db_charset($charset){

    $add_charset = $charset;

    if ( 'utf8mb4' === $charset ) {
        $add_charset .= ' COLLATE utf8mb4_unicode_ci';
    }

    return apply_replace('get_db_charset', $add_charset, $charset);
}

function get_db_create_replace($sql_str){

    if( in_array(strtolower(G5_DB_ENGINE), array('innodb', 'myisam')) ){
        $sql_str = preg_replace('/ENGINE=MyISAM/', 'ENGINE='.G5_DB_ENGINE, $sql_str);
    } else {
        $sql_str = preg_replace('/ENGINE=MyISAM/', '', $sql_str);
    }

    if( G5_DB_CHARSET !== 'utf8' ){
        $sql_str = preg_replace('/CHARSET=utf8/', 'CHARACTER SET '.get_db_charset(G5_DB_CHARSET), $sql_str);
    }

    return $sql_str;
}

function get_permission_debug_show(){
    global $member;

    $bool = false;
    if ( defined('G5_DEBUG') && G5_DEBUG ){
        $bool = true;
    }

    return apply_replace('get_permission_debug_show', $bool, $member);
}

function get_check_mod_rewrite(){

    if( function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) )
        $mod_rewrite = 1;
    elseif( isset($_SERVER['IIS_UrlRewriteModule']) )
        $mod_rewrite = 1;
    else
        $mod_rewrite = 0;

    return $mod_rewrite;
}

// 생성되면 안되는 게시판명
function get_bo_table_banned_word(){

    $folders = array();

    foreach(glob(G5_PATH.'/*', GLOB_ONLYDIR) as $dir) {
        $folders[] = basename($dir);
    }

    return apply_replace('get_bo_table_banned_word', $folders);
}

// 읽지 않은 메모 갯수 반환
function get_memo_not_read($mb_id, $add_where='')
{
    global $g5;

    $sql = " SELECT count(*) as cnt FROM {$g5['memo_table']} WHERE me_recv_mb_id = '$mb_id' and me_type= 'recv' and me_read_datetime like '0%' $add_where ";
    $row = sql_fetch($sql, false);

    return $row['cnt'];
}

function get_scrap_totals($mb_id=''){
    global $g5;

    $add_where = $mb_id ? " and mb_id = '$mb_id' " : '';

    $sql = " select count(*) as cnt from {$g5['scrap_table']} where 1=1 $add_where";
    $row = sql_fetch($sql, false);

    return $row['cnt'];
}
?>