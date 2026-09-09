-- @description 과거 스키마 변경 시 누락된 기존 데이터 보정
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-table-exists {{g5_shop_item_table}}
UPDATE `{{g5_shop_cart_table}}` c
INNER JOIN `{{g5_shop_item_table}}` i ON i.it_id = c.it_id
SET c.it_sc_type = i.it_sc_type,
    c.it_sc_method = i.it_sc_method,
    c.it_sc_price = i.it_sc_price,
    c.it_sc_minimum = i.it_sc_minimum,
    c.it_sc_qty = i.it_sc_qty
WHERE c.it_sc_type = 0
  AND c.it_sc_method = 0
  AND c.it_sc_price = 0
  AND c.it_sc_minimum = 0
  AND c.it_sc_qty = 0;

UPDATE `{{config_table}}`
SET cf_member_img_size = 50000,
    cf_member_img_width = 60,
    cf_member_img_height = 60
WHERE cf_member_img_size = 0
  AND cf_member_img_width = 0
  AND cf_member_img_height = 0;

-- @foreach-content-missing-seo
UPDATE `{{content_table}}`
SET co_seo_title = '{{seo_title}}'
WHERE co_id = '{{content_id}}';
