<?php

namespace API\Service;

use API\Database\Db;

class FaqService
{
    /**
     * FAQ 분류목록 조회
     * @param $page
     * @param $per_page
     * @return array|false
     */
    public function fetchFaqCategory($page, $per_page)
    {
        $faq_master_table = $GLOBALS['g5']['faq_master_table'];
        $query = "SELECT * FROM $faq_master_table ORDER BY fm_order DESC LIMIT :offset, :limit";

        $offset = ($page - 1) * $per_page;
        $result = Db::getInstance()->run($query, ['offset' => $offset, 'limit' => $per_page])->fetchAll();
        if (empty($result)) {
            return false;
        }

        return $result;
    }


    /**
     * html 빠진 FAQ 분류목록 조회
     * @param $page
     * @param $per_page
     * @return array|false
     */
    public function fetchFaqCategoryNotHtml($page, $per_page)
    {
        $faq_master_table = $GLOBALS['g5']['faq_master_table'];
        $query = "SELECT fm_id, fm_subject, fm_order
            FROM `$faq_master_table`
            ORDER BY fm_order
            DESC LIMIT :offset, :limit";

        $offset = ($page - 1) * $per_page;
        $result = Db::getInstance()->run($query, ['offset' => $offset, 'limit' => $per_page])->fetchAll();
        if (empty($result)) {
            return false;
        }

        return $result;
    }

    /**
     * 카테고리에 해당되는 FAQ 조회
     * @param $faq_ca_id
     * @return array|false
     */
    public function fetchFaqById($faq_ca_id)
    {
        $faq_table = $GLOBALS['g5']['faq_table'];
        $query = "SELECT * FROM `$faq_table` WHERE fm_id = :faq_ca_id ORDER BY fa_order DESC";
        $result = Db::getInstance()->run($query, [
            'faq_ca_id' => $faq_ca_id,
        ])->fetch();
        if (empty($result)) {
            return false;
        }

        return $result;
    }

    /**
     * FAQ 검색
     * @param int $faq_ca_id
     * @param string $stx
     * @return array|false
     */
    public function searchFaq($faq_ca_id, $stx)
    {
        $faq_table = $GLOBALS['g5']['faq_table'];
        $query = "SELECT * FROM `$faq_table` WHERE fm_id = :fm_id AND (fa_subject LIKE :search_subject OR fa_content LIKE :search_content)";
        return Db::getInstance()->run($query, [
            'fm_id' => $faq_ca_id,
            'search_subject' => "%$stx%",
            'search_content' => "%$stx%"
        ])->fetchAll();
    }

    /**
     * FAQ 조회
     * @param int $faq_ca_id 카테고리 ID
     * @param string $stx 검색어
     * @return array|false
     */
    public function getFaq($faq_ca_id, $stx)
    {
        if ($stx) {
            return $this->searchFaq($faq_ca_id, $stx);
        }

        return $this->fetchFaqById($faq_ca_id);
    }
}