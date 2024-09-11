<?php

namespace API\Service;

use API\Database\Db;

class UrlService
{

    /**
     *
     * @param $string
     * @param int $word_limit 단어길이
     * @return string
     */
    public function generateSeoTitle($string, $word_limit = G5_SEO_TITLE_WORD_CUT)
    {
        $separator = '-';

        if ($word_limit != 0) {
            $word_arr = explode(' ', $string);
            $string = implode(' ', array_slice($word_arr, 0, $word_limit));
        }

        $quote_separator = preg_quote($separator, '#');

        $trans = [
            '&.+?;' => '',
            '[^\w\d _-]' => '',
            '\s+' => $separator,
            '(' . $quote_separator . ')+' => $separator
        ];

        $string = strip_tags($string);

        foreach ($trans as $key => $val) {
            $string = preg_replace('#' . $key . '#iu', $val, $string);
        }

        $string = strtolower($string);

        return trim($string, $separator);
    }

    /**
     * exist_seo_url 함수 대체
     * 콘텐츠, 게시글에서 같은 seo 제목을 찾는다.
     * seo 중복방지를 위함.
     * @param string $type bbs, content
     * @param string $seo_title
     * @param string $write_table 테이블 명
     * @param string $pk_id url 에서 게시글의 id 값
     * @return bool
     */
    public function fetchExistSeoUrl($type, $seo_title, $write_table, $pk_id = 0)
    {
        // 하이픈 포함 a-z0-9_-
        $pk_id = preg_replace('/[^a-z0-9_\-]/i', '', $pk_id);
        if ($type === 'bbs') {
            $query = "SELECT CASE WHEN EXISTS (SELECT wr_seo_title FROM `{$write_table}` WHERE wr_seo_title = ? AND wr_id <> ? limit 1 ) 
                THEN 1 ELSE 0 END as wr_seo_title";
            $stmt = Db::getInstance()->run($query, [$seo_title, $pk_id]);
            $row = $stmt->fetch();
            $exists_title = $row['wr_seo_title'] == 1;
        } elseif ($type === 'content') { // 내용관리
            $query = "SELECT CASE WHEN EXISTS (SELECT co_seo_title FROM `{$write_table}` WHERE co_seo_title = ? AND wr_id <> ? limit 1 ) 
                THEN 1 ELSE 0 END as co_seo_title";
            $stmt = Db::getInstance()->run($query, [$seo_title, $pk_id]);
            $row = $stmt->fetch();
            $exists_title = $row['co_seo_title'] == 1;
        } else {
            return run_replace('exist_check_seo_title', $seo_title, $type, $write_table, $pk_id);
        }

        if ($exists_title) {
            return true;
        }

        return false;
    }

    /**
     *
     * seo_title, seo_url 중복을 방지하기 위한 재귀함수
     * @param string $type
     * @param string $title
     * @param string $write_table
     * @param string|int $sql_id
     * @return string
     */
    public function getSeoTtitleRecursive($type, $title, $write_table, $sql_id = 0)
    {
        static $count = 0;
        static $seo_title = '';

        if ($seo_title === '') {
            $seo_title = $this->generateSeoTitle($title);
        }

        $result_seo_title = ($count > 0) ? $seo_title . "-$count" : $seo_title;

        if (!$this->fetchExistSeoUrl($type, $result_seo_title, $write_table, $sql_id)) {
            return $result_seo_title;
        }

        $count++;

        if ($count > 99998) {
            return $result_seo_title;
        }

        return $this->getSeoTtitleRecursive($type, $seo_title, $write_table, $sql_id);
    }
}
