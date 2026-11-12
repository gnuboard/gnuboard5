<?php
if (!defined('_GNUBOARD_')) exit;

// 권한 필터를 거친 행을 기존 sql_* 결과 API로 스킨에 전달한다.
class G5_sql_rows
{
    private $rows;
    private $offset = 0;

    public function __construct($rows) { $this->rows = array_values($rows); }
    public function fetch() { return $this->rows[$this->offset++] ?? null; }
    public function count() { return count($this->rows); }
    public function seek($offset)
    {
        if ($offset < 0 || $offset >= count($this->rows)) return false;
        $this->offset = (int)$offset;
        return true;
    }
    public function free() { $this->rows = array(); $this->offset = 0; }
}
