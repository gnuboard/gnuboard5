-- @description 장바구니 상품 배송비 정보 필드 추가 (3889e0235)
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_type
ALTER TABLE `{{g5_shop_cart_table}}`
    ADD `it_sc_type` tinyint(4) NOT NULL DEFAULT '0' AFTER `it_name`;
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_method
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_method` tinyint(4) NOT NULL DEFAULT '0' AFTER `it_sc_type`;
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_price
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_price` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_method`;
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_minimum
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_minimum` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_price`;
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_qty
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_qty` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_minimum`;
