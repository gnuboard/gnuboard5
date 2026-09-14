-- @description KVE-2026-2140 주문별 복귀 인증과 영속 승인 상태
-- @if-table-exists {{g5_shop_order_data_table}}
-- @if-table-missing {{g5_shop_order_access_table}}
CREATE TABLE {{g5_shop_order_access_table}} (
  od_id bigint(20) unsigned NOT NULL,
  token_hash char(64) NOT NULL DEFAULT '',
  pg varchar(20) NOT NULL DEFAULT '',
  cart_id bigint(20) unsigned NOT NULL DEFAULT '0',
  status varchar(20) NOT NULL DEFAULT 'pending',
  expires bigint(20) NOT NULL DEFAULT '0',
  updated_at bigint(20) NOT NULL DEFAULT '0',
  state_json mediumtext NOT NULL,
  payment_key varchar(200) NOT NULL DEFAULT '',
  response_json mediumtext NOT NULL,
  PRIMARY KEY (od_id),
  KEY cart_status (cart_id,status),
  KEY state_expiry (status,expires)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
