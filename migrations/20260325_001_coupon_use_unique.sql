-- @description 쿠폰 중복 사용 방지 복합 고유 인덱스 추가 (5054e2463)
-- @if-table-exists {{g5_shop_coupon_log_table}}
ALTER TABLE `{{g5_shop_coupon_log_table}}` MODIFY `cp_id` varchar(100) NOT NULL DEFAULT '', MODIFY `mb_id` varchar(100) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_coupon_log_table}}
-- @if-index-missing {{g5_shop_coupon_log_table}} idx_coupon_use
ALTER TABLE `{{g5_shop_coupon_log_table}}` ADD UNIQUE KEY `idx_coupon_use` (`cp_id`, `mb_id`);
