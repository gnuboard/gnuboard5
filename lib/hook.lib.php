<?php
if (!defined('_GNUBOARD_')) exit;

define('G5_HOOK_DEFAULT_PRIORITY', 8);

if (!function_exists('get_called_class')) {
    function get_called_class() {
        $bt = debug_backtrace();
        $lines = file($bt[1]['file']);
        preg_match(
            '/([a-zA-Z0-9\_]+)::'.$bt[1]['function'].'/',
            $lines[$bt[1]['line']-1],
            $matches
        );
        return $matches[1];
    }
}

include_once(dirname(__FILE__) .'/Hook/hook.class.php');
include_once(dirname(__FILE__) .'/Hook/hook.extends.class.php');

function get_hook_class(){

    if( class_exists('GML_Hook') ){
        return GML_Hook::getInstance();
    }

    return null;
}

function add_event($tag, $func, $priority=G5_HOOK_DEFAULT_PRIORITY, $args=0){

    if( $hook = get_hook_class() ){
        $hook->addAction($tag, $func, $priority, $args);
    }
}

function run_event($tag, $arg = ''){

    if( $hook = get_hook_class() ){

        $args = array();

        if (
            is_array($arg)
            &&
            isset($arg[0])
            &&
            is_object($arg[0])
            &&
            1 == count($arg)
        ) {
          $args[] =& $arg[0];
        } else {
          $args[] = $arg;
        }

        $numArgs = func_num_args();

        for ($a = 2; $a < $numArgs; $a++) {
          $args[] = func_get_arg($a);
        }

        $hook->doAction($tag, $args, false);
    }
}

function add_replace($tag, $func, $priority=G5_HOOK_DEFAULT_PRIORITY, $args=0){

    if( $hook = get_hook_class() ){
        return $hook->addFilter($tag, $func, $priority, $args);
    }

    return null;
}

function run_replace($tag, $arg = ''){

    if( $hook = get_hook_class() ){

        $args = array();

        if (
            is_array($arg)
            &&
            isset($arg[0])
            &&
            is_object($arg[0])
            &&
            1 == count($arg)
        ) {
          $args[] =& $arg[0];
        } else {
          $args[] = $arg;
        }

        $numArgs = func_num_args();

        for ($a = 2; $a < $numArgs; $a++) {
          $args[] = func_get_arg($a);
        }

        return $hook->apply_filters($tag, $args, false);
    }

    return null;
}

function delete_event($tag, $func, $priority=G5_HOOK_DEFAULT_PRIORITY){

    if( $hook = get_hook_class() ){
        return $hook->remove_action($tag, $func, $priority);
    }

    return null;
}

function delete_replace($tag, $func, $priority=G5_HOOK_DEFAULT_PRIORITY){

    if( $hook = get_hook_class() ){
        return $hook->remove_filter($tag, $func, $priority);
    }

    return null;
}

function get_hook_datas($type='', $is_callback=''){
    if( $hook = get_hook_class() ){
        return $hook->get_properties($type, $is_callback);
    }

    return null;
}