-- @description 장바구니 상품·상태 조회 인덱스 추가 (344b1983a)
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-index-missing {{g5_shop_cart_table}} it_id
ALTER TABLE `{{g5_shop_cart_table}}` ADD INDEX `it_id` (`it_id`);
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-index-missing {{g5_shop_cart_table}} ct_status
ALTER TABLE `{{g5_shop_cart_table}}` ADD INDEX `ct_status` (`ct_status`);
