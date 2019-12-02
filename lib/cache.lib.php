<?php
if (!defined('_GNUBOARD_')) exit;

include_once(dirname(__FILE__) .'/Cache/obj.class.php');
include_once(dirname(__FILE__) .'/Cache/FileCache.class.php');

function get_cachemanage_instance(){
    static $instance = null;
    
    if( ! (defined('G5_USE_CACHE') && G5_USE_CACHE) ) return $instance;

    if( $instance === null ){
        $options = array(
            '_cache_path'=> G5_DATA_PATH.'/cache',
            'file_extension'=>'.php',
            );
        $instance = new FileCache($options);
    }

    return $instance;
}

function g5_cache_secret_key(){
    static $str = '';

    if( $str ) return $str;

    $str = substr(md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['DOCUMENT_ROOT']), 0, 6);

    return $str;
}

function g5_latest_cache_data($bo_table, $cache_list=array(), $find_wr_id=0){
    static $cache = array();

    if( $bo_table && $cache_list && ! isset($cache[$bo_table]) ){
        foreach( (array) $cache_list as $wr ){
            if( empty($wr) || ! isset($wr['wr_id']) ) continue;
            $cache[$bo_table][$wr['wr_id']] = $wr;
        }
    }
    
    if( $find_wr_id && isset($cache[$bo_table][$find_wr_id]) ){
        return $cache[$bo_table][$find_wr_id];
    }
}

function g5_set_cache($key, $save_data, $ttl = null){

    if( $cache = get_cachemanage_instance() ){
        $cache->save($key, $save_data, $ttl);
    }
}

function g5_get_cache($key){

    if( $cache = get_cachemanage_instance() ){
        return $cache->get($key);
    }
    
    return false;
}

function g5_delete_cache($key){
    if( $cache = get_cachemanage_instance() ){
        return $cache->delete($key);
    }
    
    return false;
}

function g5_delete_all_cache(){

    $board_tables = get_board_names();

    foreach( $board_tables as $board_table ){
        delete_cache_latest($board_table);
    }

    run_event('adm_cache_delete', $board_tables);

}

function g5_delete_cache_by_prefix($key){
    $files = glob(G5_DATA_PATH.'/cache/'.$key.'*');

    foreach( (array) $files as $filename){
        if(empty($filename)) continue;

        unlink($filename);
    }

    return ($files) ? true : false;
}
?>