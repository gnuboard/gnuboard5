-- @description 상품별 배송완료 시각 추가 (기존 완료 상품은 종전 포인트 지급 기준 유지)
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} ct_complete_time
ALTER TABLE `{{g5_shop_cart_table}}` ADD `ct_complete_time` datetime DEFAULT NULL AFTER `ct_select_time`;
