<?php

namespace API\Service\Social;

/**
 * API 에서 사용을 위해 기본 세션 스토리지를 대체합니다.
 */
class StatelessStorage implements \Hybridauth\Storage\StorageInterface {
    private $data = [];

    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    public function delete($key) {
        unset($this->data[$key]);
    }

    public function deleteMatch($key) {
        foreach ($this->data as $k => $v) {
            if (strpos($k, $key) === 0) {
                unset($this->data[$k]);
            }
        }
    }

    public function clear() {
        $this->data = [];
    }
}