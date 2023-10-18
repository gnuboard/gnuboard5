<?php
if (!defined('_GNUBOARD_')) exit;

Class G5_object_cache {
    public $writes = array();
    public $contents = array();
    public $etcs = array();

    function get($type, $key, $group ='default') {

        switch ($type) {
            case 'bbs':
                $datas = $this->writes;
                break;
            case 'content' :
                $datas = $this->contents;
                break;
            default :
                $datas = $this->etcs;
                break;
        }

        if( $this->exists($type, $key, $group) ){
            if ( is_object($datas[$group][$key]) )
                return clone $datas[$group][$key];
            else
                return $datas[$group][$key];
        }

        return false;
    }

    function exists($type, $key, $group = 'default' ) {

        $return_data = '';

        switch ($type) {
            case 'bbs':
                $datas = $this->writes;
                break;
            case 'content':
                $datas = $this->contents;
                break;
            default :
                $datas = $this->etcs;
                break;
        }

        return isset($datas[$group]) && ( isset($datas[$group][$key]) || array_key_exists($key, $datas[$group]) );
    }

    function set($type, $key, $data=array(), $group='default') {
        if ( is_object( $data ) )
            $data = clone $data;

        switch ($type) {
            case 'bbs':
                $this->writes[$group][$key] = $data;
                break;
            case 'content':
                $this->contents[$group][$key] = $data;
                break;
            default :
                $this->etcs[$group][$key] = $data;
                break;
        }

    }
    /**
     * cache 데이터 제거
     * @param string $type
     * @param string $key
     * @param string $group
     * @return bool
     */
    function delete($type, $key, $group = 'default')
    {
        if (!$this->exists($type, $key, $group)) {
            return false;
        }

        switch ($type) {
            case 'bbs':
                $datas = &$this->writes;
                break;
            case 'content':
                $datas = &$this->contents;
                break;
            default:
                $datas = &$this->etcs;
                break;
        }

        unset($datas[$group][$key]);

        return true;
    }

}   //end Class;