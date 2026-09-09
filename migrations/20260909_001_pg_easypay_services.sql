-- @description PG별 간편결제 설정과 병용 옵션 저장 공간 확장
-- @if-table-exists {{g5_shop_default_table}}
ALTER TABLE `{{g5_shop_default_table}}`
  MODIFY `de_easy_pay_services` varchar(1024) NOT NULL DEFAULT '';
