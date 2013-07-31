<?php
include_once('./_common.php');

// 포인트유효기간 필드추가
if(!sql_query(" select cf_point_term from {$g4['config_table']} ", false)) {
    sql_query(" ALTER TABLE `{$g4['config_table']}`
                    ADD `cf_point_term` int(11) NOT NULL DEFAULT '0' AFTER `cf_use_point` ", true);
    sql_query(" ALTER TABLE `{$g4['point_table']}`
                    ADD `po_use_point` int(11) NOT NULL DEFAULT '0' AFTER `po_point`,
                    ADD `po_expired` tinyint(4) NOT NULL DEFAULT '0' AFTER `po_use_point`,
                    ADD `po_expire_date` date NOT NULL DEFAULT '0000-00-00' AFTER `po_expired`,
                    ADD `po_mb_point` int(11) NOT NULL DEFAULT '0' AFTER `po_expire_date`,
                    ADD KEY `index2` (`po_expire_date`) ", true);

    sql_query(" update {$g4['point_table']}
                    set po_expire_date = '9999-12-31'
                    where po_expire_date = '0000-00-00' ");
}

die("테이블 또는 필드 추가 업그레이드가 완료 되었습니다.");
?>