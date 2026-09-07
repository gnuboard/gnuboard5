-- @description 테스트 주문 구분 필드 추가 (4c9ac8b6b)
-- @if-table-exists {{g5_shop_order_table}}
-- @if-column-missing {{g5_shop_order_table}} od_test
ALTER TABLE `{{g5_shop_order_table}}` ADD `od_test` tinyint(4) NOT NULL DEFAULT '0' AFTER `od_settle_case`;
