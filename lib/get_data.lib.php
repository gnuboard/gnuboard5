<?php
if (!defined('_GNUBOARD_')) exit;

function get_config($is_cache=false){
    global $g5;

    static $cache = array();

    $cache = run_replace('get_config_cache', $cache, $is_cache);

    if( $is_cache && !empty($cache) ){
        return $cache;
    }

    $sql = " select * from {$g5['config_table']} ";
    $cache = run_replace('get_config', sql_fetch($sql));

    return $cache;
}

function get_content_db($co_id, $is_cache=false){
    global $g5, $g5_object;

    static $cache = array();
    
    $type = 'content';

    $co_id = preg_replace('/[^a-z0-9_]/i', '', $co_id);
    $co = $g5_object->get($type, $co_id, $type);

    if( !$co ){

        $cache_file_name = "{$type}-{$co_id}-".g5_cache_secret_key();
        $co = g5_get_cache($cache_file_name, 10800);
        
        if( $co === false ){
            $sql = " select * from {$g5['content_table']} where co_id = '$co_id' ";
            $co = sql_fetch($sql);
            
            g5_set_cache($cache_file_name, $co, 10800);
        }

        $g5_object->set($type, $co_id, $co, $type);
    }

    return $co;
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

    $cache = run_replace('get_board_db_cache', $cache, $bo_table, $is_cache);

    $key = md5($bo_table);

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    if( !($cache[$key] = run_replace('get_board_db', array(), $bo_table)) ){

        $sql = " select * from {$g5['board_table']} where bo_table = '$bo_table' ";

        $cache[$key] = sql_fetch($sql);

    }

    return $cache[$key];
}

// 게시판 테이블에서 하나의 행을 읽음
function get_content_by_field($write_table, $type='bbs', $where_field='', $where_value='', $is_cache=false)
{
    global $g5, $g5_object;

    if( $type === 'content' ){
        $check_array = array('co_id', 'co_html', 'co_subject', 'co_content', 'co_seo_title', 'co_mobile_content', 'co_skin', 'co_mobile_skin', 'co_tag_filter_use', 'co_hit', 'co_include_head', 'co_include_tail');
    } else {
        $check_array = array('wr_id', 'wr_num', 'wr_reply', 'wr_parent', 'wr_is_comment', 'ca_name', 'wr_option', 'wr_subject', 'wr_content', 'wr_seo_title', 'wr_link1', 'wr_link2', 'wr_hit', 'wr_good', 'wr_nogood', 'mb_id', 'wr_name', 'wr_email', 'wr_homepage', 'wr_datetime', 'wr_ip', 'wr_1', 'wr_2', 'wr_3', 'wr_4', 'wr_5', 'wr_6', 'wr_7', 'wr_8', 'wr_9', 'wr_10');
    }

    if( ! in_array($where_field, $check_array) ){
        return '';
    }
    
    $where_value = strip_tags($where_value);
    $key = md5($write_table.'|'.$where_field.'|'.$where_value);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }

    $sql = " select * from {$write_table} where $where_field = '".sql_real_escape_string($where_value)."' ";

    $cache[$key] = sql_fetch($sql);

    if( $type === 'content' ){
        
        $g5_object->set($type, $cache[$key]['co_id'], $cache[$key], 'content');

    } else {
    
        $wr_bo_table = preg_replace('/^'.preg_quote($g5['write_prefix']).'/i', '', $write_table);
        $g5_object->set($type, $cache[$key]['wr_id'], $cache[$key], $wr_bo_table);

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
        return get_write($write_table, $wr_id, true);
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

    return run_replace('get_db_charset', $add_charset, $charset);
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

function get_class_encrypt(){
    static $cache;

    if( $cache && is_object($obj) ){
        return $cache;
    }

    $cache = run_replace('get_class_encrypt', new str_encrypt());

    return $cache;
}

function get_string_encrypt($str){

    $new = get_class_encrypt();

    $encrypt_str = $new->encrypt($str);

    return $encrypt_str;
}

function get_string_decrypt($str){

    $new = get_class_encrypt();

    $decrypt_str = $new->decrypt($str);

    return $decrypt_str;
}

function get_permission_debug_show(){
    global $member;

    $bool = false;
    if ( defined('G5_DEBUG') && G5_DEBUG ){
        $bool = true;
    }

    return run_replace('get_permission_debug_show', $bool, $member);
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

function get_mb_icon_name($mb_id){

    if( $icon_name = run_replace('get_mb_icon_name', '', $mb_id) ){
        return $icon_name;
    }

    return $mb_id;
}

// 생성되면 안되는 게시판명
function get_bo_table_banned_word(){

    $folders = array();

    foreach(glob(G5_PATH.'/*', GLOB_ONLYDIR) as $dir) {
        $folders[] = basename($dir);
    }

    return run_replace('get_bo_table_banned_word', $folders);
}

function get_board_sfl_select_options($sfl){

    global $is_admin;

    $str = '';
    $str .= '<option value="wr_subject" '.get_selected($sfl, 'wr_subject', true).'>제목</option>';
    $str .= '<option value="wr_content" '.get_selected($sfl, 'wr_content').'>내용</option>';
    $str .= '<option value="wr_subject||wr_content" '.get_selected($sfl, 'wr_subject||wr_content').'>제목+내용</option>';
    if ( $is_admin ){
        $str .= '<option value="mb_id,1" '.get_selected($sfl, 'mb_id,1').'>회원아이디</option>';
        $str .= '<option value="mb_id,0" '.get_selected($sfl, 'mb_id,0').'>회원아이디(코)</option>';
    }
    $str .= '<option value="wr_name,1" '.get_selected($sfl, 'wr_name,1').'>글쓴이</option>';
    $str .= '<option value="wr_name,0" '.get_selected($sfl, 'wr_name,0').'>글쓴이(코)</option>';

    return run_replace('get_board_sfl_select_options', $str, $sfl);
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