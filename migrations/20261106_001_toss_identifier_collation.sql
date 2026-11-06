-- @description 결제 식별자 대소문자 구분 보장
-- @if-column-exists {{g5_shop_order_table}} od_tno
ALTER TABLE {{g5_shop_order_table}} MODIFY od_tno varchar(255) BINARY NOT NULL DEFAULT '';
-- @if-column-exists {{g5_shop_personalpay_table}} pp_tno
ALTER TABLE {{g5_shop_personalpay_table}} MODIFY pp_tno varchar(255) BINARY NOT NULL DEFAULT '';
-- @if-column-exists {{g5_shop_order_access_table}} payment_key
ALTER TABLE {{g5_shop_order_access_table}} MODIFY payment_key varchar(200) BINARY NOT NULL DEFAULT '';
