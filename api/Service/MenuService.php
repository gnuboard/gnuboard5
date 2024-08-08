<?php

namespace API\Service;

use API\Database\Db;

class MenuService
{

    /**
     * DB 메뉴테이블에서 데이터를 가져옵니다.
     * @param bool $is_mobile
     * @return array
     */
    private function fetchMenu($is_mobile = false)
    {
        $menu_table = $GLOBALS['g5']['menu_table'];
        $query = "SELECT * FROM $menu_table WHERE me_use = 1";
        if ($is_mobile) {
            $query .= " AND me_mobile_use = 1";
        }
        return Db::getInstance()->run($query)->fetchAll();
    }


    /**
     * 메뉴 데이터를 정렬합니다.
     * @param array $data
     * @return array
     */
    public function sortMenu(array $data)
    {
        $parents = array_filter($data, fn($item) => strlen($item['me_code']) === 2);
        $sub_menu_list = array_filter($data, fn($item) => strlen($item['me_code']) === 4);

        usort($parents, fn($a, $b) => $a['me_order'] <=> $b['me_order']);
        usort($sub_menu_list, fn($a, $b) => $a['me_order'] <=> $b['me_order']);

        $sub_menu_data = [];
        foreach ($sub_menu_list as $sub_menu) {
            $parent_code = substr($sub_menu['me_code'], 0, 2);
            $sub_menu_data[$parent_code][] = $sub_menu;
        }

        foreach ($parents as &$parent) {
            $parent_code = $parent['me_code'];
            $parent['sub'] = $sub_menu_data[$parent_code] ?? [];
        }

        return $parents;
    }

    /**
     * 메뉴 정보를 가져옵니다.
     * @param $is_mobile
     * @return array
     *
     * @todo cache 적용
     */
    public function getMenu($is_mobile = false): array
    {
        $menu_data = $this->fetchMenu($is_mobile);
        if (!$menu_data) {
            return [];
        }
        return $this->sortMenu($menu_data);
    }


}