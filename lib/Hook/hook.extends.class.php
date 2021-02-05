<?php
if (!defined('_GNUBOARD_')) exit;

Class GML_Hook extends Hook {
    
    protected $filters = array('count' => 0);
    
    protected $callback_filters = array();
    
    protected static $current_filter = false;

    protected function runAction($action, $args)
    {
        $function = $action['function'];
        $argsNumber = $action['arguments'];

        $class = (is_array($function) && isset($function[0])) ? $function[0] : false;
        $method = (is_array($function) && isset($function[1])) ? $function[1] : false;

        $args = $this->getArguments($argsNumber, $args);

        if (! ($class && $method) && function_exists($function)) {
            return call_user_func_array($function, $args);
        } elseif ($obj = call_user_func(array($class, $this->singleton))) {
            if ($obj !== false) {
                return call_user_func_array(array($obj, $method), $args);
            }
        } elseif (class_exists($class)) {
            $instance = new $class;

            return call_user_func_array(array($instance, $method), $args);
        }
    }

    protected function getFilters($tag, $remove)
    {
        if (isset($this->callback_filters[$tag])) {
            $filters = $this->callback_filters[$tag];
            if ($remove) {
                unset($this->callback_filters[$tag]);
            }
        }

        return (isset($filters)) ? $filters : array();
    }

    public static function get_properties($type, $is_callback=false){

        $that = self::getInstance(self::$id);

        if( $type === 'event' ){
            return $is_callback ? $that->callbacks : $that->actions;
        }

        return $is_callback ? $that->callback_filters : $that->filters;
    }

    public static function addFilter($tag, $func, $priority = 8, $args = 0)
    {
        $that = self::getInstance(self::$id);

        $that->callback_filters[$tag][$priority][] = array(
            'function' => $func,
            'arguments' => $args,
        );

        return true;
    }

    public static function apply_filters($tag, $args = array(), $remove = true)
    {
        $that = self::getInstance(self::$id);

        self::$current_filter = $tag;

        $that->filters['count']++;

        if (! array_key_exists($tag, $that->filters)) {
            $that->filters[$tag] = 0;
        }

        $that->filters[$tag]++;
        $filters = $that->getFilters($tag, $remove);
        ksort($filters);

        $value = $args[0];

        foreach ($filters as $priority) {
            foreach ($priority as $filter) {
                if( isset($args[0]) ){
                    $args[0] = $value;
                }
                $replace = $that->runAction($filter, $args);

                if( ! is_null($replace) ) {
                    $value = $replace;
                }
            }
        }

        self::$current_filter = false;

        return $value;
    }

    protected function getArguments($argsNumber, $arguments)
    {
        if ($argsNumber == 1 && is_string($arguments)) {
            return array($arguments);
        } elseif ($argsNumber === count($arguments)) {
            return $arguments;
        }

        $args = array();

        for ($i = 0; $i < $argsNumber; $i++) {
            if (is_array($arguments) && array_key_exists($i, $arguments)) {
                $args[] = $arguments[$i];
            }
        }

        return $args;
    }

    public static function remove_filter($tag, $func, $priority)
    {
        $that = self::getInstance(self::$id);

        $is_remove = false;

        if (isset($that->callback_filters[$tag]) && isset($that->callback_filters[$tag][$priority]) ) {
            
            foreach((array) $that->callback_filters[$tag][$priority] as $key=>$value){
                if(isset($value['function']) && $value['function'] === $func) {
                    unset($that->callback_filters[$tag][$priority][$key]);
                    $is_remove = true;
                }
            }
        }

        return $is_remove;
    }

    public static function remove_action($tag, $func, $priority)
    {
        $that = self::getInstance(self::$id);

        $is_remove = false;

        if (isset($that->callbacks[$tag]) && isset($that->callbacks[$tag][$priority]) ) {
            
            foreach((array) $that->callbacks[$tag][$priority] as $key=>$value){
                if(isset($value['function']) && $value['function'] === $func) {
                    unset($that->callbacks[$tag][$priority][$key]);
                    $is_remove = true;
                }
            }
        }

        return $is_remove;
    }
}

// end  Hook Class;