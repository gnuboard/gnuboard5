-- @description 관리 권한 메뉴 ID 길이를 50자로 확장 (28bfcea9c)
-- @if-table-exists {{auth_table}}
ALTER TABLE `{{auth_table}}` CHANGE `au_menu` `au_menu` varchar(50) NOT NULL;
