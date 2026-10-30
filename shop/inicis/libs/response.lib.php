<?php
if (!defined('_GNUBOARD_')) exit;

// 알 수 없는 구조화 필드는 무시하고, 사용하는 필수 필드는 별도로 검증한다.
function inicis_response_fields($data, $required = array())
{
    if (!is_array($data))
        throw new Exception('KG이니시스 응답 형식이 올바르지 않습니다.');

    $fields = array();
    foreach ($data as $key => $value) {
        if (is_string($value) || is_int($value) || is_float($value))
            $fields[$key] = (string) $value;
    }
    foreach ($required as $key) {
        if (!isset($fields[$key]) || trim($fields[$key]) === '')
            throw new Exception('KG이니시스 응답의 필수 항목이 누락되었거나 형식이 올바르지 않습니다.');
    }
    return $fields;
}
