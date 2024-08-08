<?php

namespace API\Service;

use API\Database\Db;

class ContentService
{

    /**
     * 콘텐츠 조회
     * @param int $co_id
     * @return array|null
     * @todo cache 추가
     */
    public static function getContent($co_id)
    {
        $content_table = $GLOBALS['g5']['content_table'];
        $query = "SELECT * FROM  $content_table WHERE co_id = :co_id";
        $content = Db::getInstance()->run($query, ['co_id' => $co_id])->fetch();
        if (!isset($content['co_id'])) {
            return null;
        }

        return $content;
    }


    /**
     * 콘텐츠 리스트 조회
     * @param int $page
     * @param int $per_page
     * @todo cache 추가
     */
    public static function getContentList($page, $per_page)
    {
        $content_table = $GLOBALS['g5']['content_table'];
        $query = "SELECT * FROM  $content_table LIMIT :per_page OFFSET :offset";
        $offset = ($page - 1) * $per_page;

        return Db::getInstance()->run($query, ['per_page' => $per_page, 'offset' => $offset])->fetchAll();
    }
}