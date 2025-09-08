<?php
// 팝빌 설정값 가져오기
$userID = isset($config['cf_popbill_userid']) && $config['cf_popbill_userid'] !== '' ? $config['cf_popbill_userid'] : '';
$linkID = isset($config['cf_popbill_link_id']) && $config['cf_popbill_link_id'] !== '' ? $config['cf_popbill_link_id'] : '';
$secretKey = isset($config['cf_popbill_secretkey']) && $config['cf_popbill_secretkey'] !== '' ? $config['cf_popbill_secretkey'] : '';
$corpnum = isset($config['cf_kakaotalk_corpnum']) && $config['cf_kakaotalk_corpnum'] !== '' ? preg_replace('/[^0-9]/', '', $config['cf_kakaotalk_corpnum']) : '';
$sender_hp = isset($config['cf_kakaotalk_sender_hp']) && $config['cf_kakaotalk_sender_hp'] !== '' ? preg_replace('/[^0-9]/', '', $config['cf_kakaotalk_sender_hp']) : '';