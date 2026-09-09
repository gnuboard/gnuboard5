-- @description 장바구니 주문폼 선택 시각 필드 추가 (de2b73436)
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} ct_select_time
ALTER TABLE `{{g5_shop_cart_table}}` ADD `ct_select_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `ct_select`;
