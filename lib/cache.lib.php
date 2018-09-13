<?php
if (!defined('_GNUBOARD_')) exit;

function g5_delete_all_cache(){

    $board_tables = get_board_names();

    foreach( $board_tables as $board_table ){
        delete_cache_latest($board_table);
    }

    start_event('adm_cache_delete', $board_tables);

}

?>