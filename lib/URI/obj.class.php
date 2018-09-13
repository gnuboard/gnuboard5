<?php
if (!defined('_GNUBOARD_')) exit;

Class G5_object_store {
    public $writes = array();
    
	function get($type, $key, $group ='default') {

        switch ($type) {
            case 'bbs':
                if( $this->exists('bbs', $key, $group) ){
                    if ( is_object($this->writes[$group][$key]) )
                        return clone $this->writes[$group][$key];
                    else
                        return $this->writes[$group][$key];
                }
                return false;
                break;
        }

	}

	function exists($type, $key, $group = 'default' ) {
        
        $return_data = '';

        switch ($type) {
            case 'bbs':
                $datas = $this->writes;
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
        }

	}

    function delete($key, $group='default') {
        switch ($type) {
            case 'bbs':
                if ( ! $this->exists('bbs', $key, $group) )
                    return false;

                unset( $this->writes[$group][$key] );
                return true;
                break;
        }
    }

}   //end Class

?>