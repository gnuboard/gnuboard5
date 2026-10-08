-- @description 비밀번호 찾기 복수 한도의 원자적 확보를 위한 InnoDB 전환
-- @if-table-exists {{abuse_rate_table}}
ALTER TABLE `{{abuse_rate_table}}` ENGINE=InnoDB;
