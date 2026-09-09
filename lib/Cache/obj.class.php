<?php
if (!defined('_GNUBOARD_')) exit;

Class G5_object_cache {
    /** @deprecated 직접 접근 대신 get/set/exists/delete 메서드를 사용한다. */
    public $writes = array();
    /** @deprecated 직접 접근 대신 get/set/exists/delete 메서드를 사용한다. */
    public $contents = array();
    /** @deprecated 타입별 $etcs[$type][$group][$key] 구조. 메서드 사용 권장. */
    public $etcs = array();

    // 각 타입의 유일한 저장소를 참조로 반환한다. 빈 타입 버킷은 요청 종료까지 유지한다.
    private function &get_type_cache($type) {
        if ($type === 'bbs') {
            return $this->writes;
        }
        if ($type === 'content') {
            return $this->contents;
        }
        if (!isset($this->etcs[$type])) {
            $this->etcs[$type] = array();
        }
        return $this->etcs[$type];
    }

    function get($type, $key, $group = 'default') {
        if (!$this->exists($type, $key, $group)) {
            return false;
        }

        $datas = &$this->get_type_cache($type);
        if (is_object($datas[$group][$key])) {
            return clone $datas[$group][$key];
        }
        return $datas[$group][$key];
    }

    function exists($type, $key, $group = 'default') {
        $datas = &$this->get_type_cache($type);
        return isset($datas[$group]) && (isset($datas[$group][$key]) || array_key_exists($key, $datas[$group]));
    }

    function set($type, $key, $data = array(), $group = 'default') {
        if (is_object($data)) {
            $data = clone $data;
        }

        $datas = &$this->get_type_cache($type);
        $datas[$group][$key] = $data;
    }

    /**
     * 지정한 타입, 그룹, 키의 캐시 데이터만 제거한다.
     * @param string $type
     * @param string $key
     * @param string $group
     * @return bool
     */
    function delete($type, $key, $group = 'default') {
        if (!$this->exists($type, $key, $group)) {
            return false;
        }

        $datas = &$this->get_type_cache($type);
        unset($datas[$group][$key]);
        return true;
    }
}
