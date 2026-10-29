-- @description KCP 기존 모듈 및 REST API 연동 방식 선택 추가
-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_kcp_payment_api
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_kcp_payment_api` varchar(10) NOT NULL DEFAULT 'legacy';
