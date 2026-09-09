-- @description 미완료 주문의 장바구니·회원 필드 추가 (f1cd96dc3)
-- @if-table-exists {{g5_shop_order_data_table}}
-- @if-column-missing {{g5_shop_order_data_table}} cart_id
ALTER TABLE `{{g5_shop_order_data_table}}`
    ADD `cart_id` bigint(20) unsigned NOT NULL AFTER `od_id`;
-- @if-table-exists {{g5_shop_order_data_table}}
-- @if-column-missing {{g5_shop_order_data_table}} mb_id
ALTER TABLE `{{g5_shop_order_data_table}}` ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `cart_id`;
