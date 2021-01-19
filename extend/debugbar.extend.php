<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_event('tail_sub', 'show_debug_bar');

function show_debug_bar() {

    global $g5, $g5_debug, $is_admin;
    
    if( ! get_permission_debug_show() ) return;

    if ( !($is_admin === 'super' && !is_mobile() ) ){
        return;
    }

    $memory_usage = function_exists( 'memory_get_peak_usage' ) ? memory_get_peak_usage() : memory_get_usage();
    $php_run_time = (isset($g5_debug['php']) && isset($g5_debug['php']['begin_time'])) ? ( get_microtime() - $g5_debug['php']['begin_time'] ) : 0;

    include_once( G5_PLUGIN_PATH.'/debugbar/debugbar.php' );
}