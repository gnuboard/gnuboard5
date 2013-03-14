## 마이에스큐엘 dump 10.13  Distrib 5.1.66, for redhat-linux-gnu (i386)
##
## Host: 1.226.84.20    Database: yc4kcp
## ######################################################
## Server version	5.0.96-log












##
## Not dumping tablespaces as no INFORMATION_SCHEMA.FILES table on this server
##

##
## Table structure for table `$g4[yc4_banner_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_banner_table]`;


CREATE TABLE `$g4[yc4_banner_table]` (
  `bn_id` int(11) NOT NULL auto_increment,
  `bn_alt` varchar(255) NOT NULL default '',
  `bn_url` varchar(255) NOT NULL default '',
  `bn_position` varchar(255) NOT NULL default '',
  `bn_border` tinyint(4) NOT NULL default '0',
  `bn_new_win` tinyint(4) NOT NULL default '0',
  `bn_begin_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `bn_end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `bn_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `bn_hit` int(11) NOT NULL default '0',
  `bn_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`bn_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_card_history_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_card_history_table]`;


CREATE TABLE `$g4[yc4_card_history_table]` (
  `cd_id` int(11) NOT NULL auto_increment,
  `od_id` varchar(10) NOT NULL default '',
  `on_uid` varchar(32) NOT NULL default '',
  `cd_mall_id` varchar(20) NOT NULL default '',
  `cd_amount` int(11) NOT NULL default '0',
  `cd_app_no` varchar(20) NOT NULL default '',
  `cd_app_rt` varchar(8) NOT NULL default '',
  `cd_trade_ymd` date NOT NULL default '0000-00-00',
  `cd_trade_hms` time NOT NULL default '00:00:00',
  `cd_quota` char(2) NOT NULL default '',
  `cd_opt01` varchar(255) NOT NULL default '',
  `cd_opt02` varchar(255) NOT NULL default '',
  `cd_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cd_ip` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`cd_id`),
  KEY `od_id` (`od_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_cart_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_cart_table]`;


CREATE TABLE `$g4[yc4_cart_table]` (
  `ct_id` int(11) NOT NULL auto_increment,
  `on_uid` varchar(32) NOT NULL default '',
  `it_id` varchar(10) NOT NULL default '0',
  `it_opt1` varchar(255) NOT NULL default '',
  `it_opt2` varchar(255) NOT NULL default '',
  `it_opt3` varchar(255) NOT NULL default '',
  `it_opt4` varchar(255) NOT NULL default '',
  `it_opt5` varchar(255) NOT NULL default '',
  `it_opt6` varchar(255) NOT NULL default '',
  `ct_status` enum('쇼핑','주문','준비','배송','완료','취소','반품','품절') NOT NULL default '쇼핑',
  `ct_history` text NOT NULL,
  `ct_amount` int(11) NOT NULL default '0',
  `ct_point` int(11) NOT NULL default '0',
  `ct_point_use` tinyint(4) NOT NULL default '0',
  `ct_stock_use` tinyint(4) NOT NULL default '0',
  `ct_qty` int(11) NOT NULL default '0',
  `ct_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ct_ip` varchar(25) NOT NULL default '',
  `ct_send_cost` varchar(255) NOT NULL,
  `ct_direct` tinyint(4) NOT NULL,
  PRIMARY KEY  (`ct_id`),
  KEY `on_uid` (`on_uid`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_category_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_category_table]`;


CREATE TABLE `$g4[yc4_category_table]` (
  `ca_id` varchar(10) NOT NULL default '0',
  `ca_name` varchar(255) NOT NULL default '',
  `ca_skin` varchar(255) NOT NULL default '',
  `ca_opt1_subject` varchar(255) NOT NULL default '',
  `ca_opt2_subject` varchar(255) NOT NULL default '',
  `ca_opt3_subject` varchar(255) NOT NULL default '',
  `ca_opt4_subject` varchar(255) NOT NULL default '',
  `ca_opt5_subject` varchar(255) NOT NULL default '',
  `ca_opt6_subject` varchar(255) NOT NULL default '',
  `ca_img_width` int(11) NOT NULL default '0',
  `ca_img_height` int(11) NOT NULL default '0',
  `ca_sell_email` varchar(255) NOT NULL default '',
  `ca_use` tinyint(4) NOT NULL default '0',
  `ca_stock_qty` int(11) NOT NULL default '0',
  `ca_explan_html` tinyint(4) NOT NULL default '0',
  `ca_head_html` text NOT NULL,
  `ca_tail_html` text NOT NULL,
  `ca_list_mod` int(11) NOT NULL default '0',
  `ca_list_row` int(11) NOT NULL default '0',
  `ca_include_head` varchar(255) NOT NULL default '',
  `ca_include_tail` varchar(255) NOT NULL default '',
  `ca_mb_id` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ca_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_content_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_content_table]`;


CREATE TABLE `$g4[yc4_content_table]` (
  `co_id` varchar(20) NOT NULL default '',
  `co_html` tinyint(4) NOT NULL default '0',
  `co_subject` varchar(255) NOT NULL default '',
  `co_content` longtext NOT NULL,
  `co_hit` int(11) NOT NULL default '0',
  `co_include_head` varchar(255) NOT NULL,
  `co_include_tail` varchar(255) NOT NULL,
  PRIMARY KEY  (`co_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_default_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_default_table]`;


CREATE TABLE `$g4[yc4_default_table]` (
  `de_admin_company_owner` varchar(255) NOT NULL default '',
  `de_admin_company_name` varchar(255) NOT NULL default '',
  `de_admin_company_saupja_no` varchar(255) NOT NULL default '',
  `de_admin_company_tel` varchar(255) NOT NULL default '',
  `de_admin_company_fax` varchar(255) NOT NULL default '',
  `de_admin_tongsin_no` varchar(255) NOT NULL default '',
  `de_admin_company_zip` varchar(255) NOT NULL default '',
  `de_admin_company_addr` varchar(255) NOT NULL default '',
  `de_admin_info_name` varchar(255) NOT NULL default '',
  `de_admin_info_email` varchar(255) NOT NULL default '',
  `de_type1_list_use` int(11) NOT NULL default '0',
  `de_type1_list_skin` varchar(255) NOT NULL default '',
  `de_type1_list_mod` int(11) NOT NULL default '0',
  `de_type1_list_row` int(11) NOT NULL default '0',
  `de_type1_img_width` int(11) NOT NULL default '0',
  `de_type1_img_height` int(11) NOT NULL default '0',
  `de_type2_list_use` int(11) NOT NULL default '0',
  `de_type2_list_skin` varchar(255) NOT NULL default '',
  `de_type2_list_mod` int(11) NOT NULL default '0',
  `de_type2_list_row` int(11) NOT NULL default '0',
  `de_type2_img_width` int(11) NOT NULL default '0',
  `de_type2_img_height` int(11) NOT NULL default '0',
  `de_type3_list_use` int(11) NOT NULL default '0',
  `de_type3_list_skin` varchar(255) NOT NULL default '',
  `de_type3_list_mod` int(11) NOT NULL default '0',
  `de_type3_list_row` int(11) NOT NULL default '0',
  `de_type3_img_width` int(11) NOT NULL default '0',
  `de_type3_img_height` int(11) NOT NULL default '0',
  `de_type4_list_use` int(11) NOT NULL default '0',
  `de_type4_list_skin` varchar(255) NOT NULL default '',
  `de_type4_list_mod` int(11) NOT NULL default '0',
  `de_type4_list_row` int(11) NOT NULL default '0',
  `de_type4_img_width` int(11) NOT NULL default '0',
  `de_type4_img_height` int(11) NOT NULL default '0',
  `de_type5_list_use` int(11) NOT NULL default '0',
  `de_type5_list_skin` varchar(255) NOT NULL default '',
  `de_type5_list_mod` int(11) NOT NULL default '0',
  `de_type5_list_row` int(11) NOT NULL default '0',
  `de_type5_img_width` int(11) NOT NULL default '0',
  `de_type5_img_height` int(11) NOT NULL default '0',
  `de_rel_list_mod` int(11) NOT NULL default '0',
  `de_rel_img_width` int(11) NOT NULL default '0',
  `de_rel_img_height` int(11) NOT NULL default '0',
  `de_bank_use` int(11) NOT NULL default '0',
  `de_bank_account` text NOT NULL,
  `de_card_test` int(11) NOT NULL default '0',
  `de_card_use` int(11) NOT NULL default '0',
  `de_card_point` int(11) NOT NULL default '0',
  `de_card_pg` varchar(255) NOT NULL default '',
  `de_card_max_amount` int(11) NOT NULL default '0',
  `de_banktown_mid` varchar(255) NOT NULL default '',
  `de_banktown_auth_key` varchar(255) NOT NULL default '',
  `de_telec_mid` varchar(255) NOT NULL default '',
  `de_point_settle` int(11) NOT NULL default '0',
  `de_level_sell` int(11) NOT NULL default '0',
  `de_send_cost_case` varchar(255) NOT NULL default '',
  `de_send_cost_limit` varchar(255) NOT NULL default '',
  `de_send_cost_list` varchar(255) NOT NULL default '',
  `de_hope_date_use` int(11) NOT NULL default '0',
  `de_hope_date_after` int(11) NOT NULL default '0',
  `de_baesong_content` text NOT NULL,
  `de_change_content` text NOT NULL,
  `de_point_days` int(11) NOT NULL default '0',
  `de_simg_width` int(11) NOT NULL default '0',
  `de_simg_height` int(11) NOT NULL default '0',
  `de_mimg_width` int(11) NOT NULL default '0',
  `de_mimg_height` int(11) NOT NULL default '0',
  `de_scroll_banner_use` tinyint(4) NOT NULL default '0',
  `de_cart_skin` varchar(255) NOT NULL default '',
  `de_register` varchar(255) NOT NULL default '',
  `de_sms_cont1` varchar(255) NOT NULL default '',
  `de_sms_cont2` varchar(255) NOT NULL default '',
  `de_sms_cont3` varchar(255) NOT NULL default '',
  `de_sms_cont4` varchar(255) NOT NULL default '',
  `de_sms_use1` tinyint(4) NOT NULL default '0',
  `de_sms_use2` tinyint(4) NOT NULL default '0',
  `de_sms_use3` tinyint(4) NOT NULL default '0',
  `de_sms_use4` tinyint(4) NOT NULL default '0',
  `de_xonda_id` varchar(255) NOT NULL default '',
  `de_sms_hp` varchar(255) NOT NULL default '',
  `de_inicis_mid` varchar(255) NOT NULL default '',
  `de_inicis_passwd` varchar(255) NOT NULL default '',
  `de_dacom_mid` varchar(255) NOT NULL default '',
  `de_dacom_test` tinyint(4) NOT NULL default '0',
  `de_dacom_mertkey` varchar(255) NOT NULL default '0',
  `de_allthegate_mid` varchar(255) NOT NULL default '',
  `de_kcp_mid` varchar(255) NOT NULL default '',
  `de_iche_use` tinyint(4) NOT NULL default '0',
  `de_allat_partner_id` varchar(255) NOT NULL default '',
  `de_allat_prefix` varchar(255) NOT NULL default '',
  `de_allat_formkey` varchar(255) NOT NULL default '',
  `de_allat_crosskey` varchar(255) NOT NULL default '',
  `de_tgcorp_mxid` varchar(255) NOT NULL default '',
  `de_tgcorp_mxotp` varchar(255) NOT NULL default '',
  `de_kspay_id` varchar(255) NOT NULL default '',
  `de_item_ps_use` tinyint(4) NOT NULL default '0',
  `de_code_dup_use` tinyint(4) NOT NULL default '0',
  `de_point_per` tinyint(4) NOT NULL default '0',
  `de_admin_buga_no` varchar(255) NOT NULL default '',
  `de_different_msg` tinyint(4) NOT NULL default '0',
  `de_sms_use` varchar(255) NOT NULL default '',
  `de_icode_id` varchar(255) NOT NULL default '',
  `de_icode_pw` varchar(255) NOT NULL default '',
  `de_icode_server_ip` varchar(255) NOT NULL default '',
  `de_icode_server_port` varchar(255) NOT NULL default '',
  `de_kcp_site_key` varchar(255) NOT NULL default '',
  `de_vbank_use` varchar(255) NOT NULL default '',
  `de_taxsave_use` tinyint(4) NOT NULL,
  `de_guest_privacy` text NOT NULL,
  `de_hp_use` tinyint(4) NOT NULL default '0',
  `de_xonda_smskey` varchar(255) NOT NULL,
  `de_escrow_use` tinyint(4) NOT NULL default '0'
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_delivery_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_delivery_table]`;


CREATE TABLE `$g4[yc4_delivery_table]` (
  `dl_id` int(11) NOT NULL auto_increment,
  `dl_company` varchar(255) NOT NULL default '',
  `dl_url` varchar(255) NOT NULL default '',
  `dl_tel` varchar(255) NOT NULL default '',
  `dl_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`dl_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_event_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_event_table]`;


CREATE TABLE `$g4[yc4_event_table]` (
  `ev_id` int(11) NOT NULL auto_increment,
  `it_group` int(11) NOT NULL default '0',
  `ev_skin` varchar(255) NOT NULL default '',
  `ev_img_width` int(11) NOT NULL default '0',
  `ev_img_height` int(11) NOT NULL default '0',
  `ev_list_mod` int(11) NOT NULL default '0',
  `ev_list_row` int(11) NOT NULL default '0',
  `ev_subject` varchar(255) NOT NULL default '',
  `ev_head_html` text NOT NULL,
  `ev_tail_html` text NOT NULL,
  `ev_use` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ev_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_event_table]_item`
##

DROP TABLE IF EXISTS `$g4[yc4_event_table]_item`;


CREATE TABLE `$g4[yc4_event_table]_item` (
  `ev_id` int(11) NOT NULL default '0',
  `it_id` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`ev_id`,`it_id`),
  KEY `it_id` (`it_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_faq_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_faq_table]`;


CREATE TABLE `$g4[yc4_faq_table]` (
  `fa_id` int(11) NOT NULL auto_increment,
  `fm_id` int(11) NOT NULL default '0',
  `fa_subject` text NOT NULL,
  `fa_content` text NOT NULL,
  `fa_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`fa_id`),
  KEY `fm_id` (`fm_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_faq_table]_master`
##

DROP TABLE IF EXISTS `$g4[yc4_faq_table]_master`;


CREATE TABLE `$g4[yc4_faq_table]_master` (
  `fm_id` int(11) NOT NULL auto_increment,
  `fm_subject` varchar(255) NOT NULL default '',
  `fm_head_html` text NOT NULL,
  `fm_tail_html` text NOT NULL,
  `fm_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`fm_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_item_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_item_table]`;


CREATE TABLE `$g4[yc4_item_table]` (
  `it_id` varchar(10) NOT NULL default '',
  `ca_id` varchar(10) NOT NULL default '0',
  `ca_id2` varchar(255) NOT NULL default '',
  `ca_id3` varchar(255) NOT NULL default '',
  `it_name` varchar(255) NOT NULL default '',
  `it_gallery` tinyint(4) NOT NULL default '0',
  `it_maker` varchar(255) NOT NULL default '',
  `it_origin` varchar(255) NOT NULL default '',
  `it_opt1_subject` varchar(255) NOT NULL default '',
  `it_opt2_subject` varchar(255) NOT NULL default '',
  `it_opt3_subject` varchar(255) NOT NULL default '',
  `it_opt4_subject` varchar(255) NOT NULL default '',
  `it_opt5_subject` varchar(255) NOT NULL default '',
  `it_opt6_subject` varchar(255) NOT NULL default '',
  `it_opt1` text NOT NULL,
  `it_opt2` text NOT NULL,
  `it_opt3` text NOT NULL,
  `it_opt4` text NOT NULL,
  `it_opt5` text NOT NULL,
  `it_opt6` text NOT NULL,
  `it_type1` tinyint(4) NOT NULL default '0',
  `it_type2` tinyint(4) NOT NULL default '0',
  `it_type3` tinyint(4) NOT NULL default '0',
  `it_type4` tinyint(4) NOT NULL default '0',
  `it_type5` tinyint(4) NOT NULL default '0',
  `it_basic` text NOT NULL,
  `it_explan` mediumtext NOT NULL,
  `it_explan_html` tinyint(4) NOT NULL default '0',
  `it_cust_amount` int(11) NOT NULL default '0',
  `it_amount` int(11) NOT NULL default '0',
  `it_amount2` int(11) NOT NULL default '0',
  `it_amount3` int(11) NOT NULL default '0',
  `it_point` int(11) NOT NULL default '0',
  `it_sell_email` varchar(255) NOT NULL default '',
  `it_use` tinyint(4) NOT NULL default '0',
  `it_stock_qty` int(11) NOT NULL default '0',
  `it_head_html` text NOT NULL,
  `it_tail_html` text NOT NULL,
  `it_hit` int(11) NOT NULL default '0',
  `it_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `it_ip` varchar(25) NOT NULL default '',
  `it_order` int(11) NOT NULL default '0',
  `it_tel_inq` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`it_id`),
  KEY `ca_id` (`ca_id`),
  KEY `it_name` (`it_name`),
  KEY `it_order` (`it_order`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_item_table]_ps`
##

DROP TABLE IF EXISTS `$g4[yc4_item_table]_ps`;


CREATE TABLE `$g4[yc4_item_table]_ps` (
  `is_id` int(11) NOT NULL auto_increment,
  `it_id` varchar(10) NOT NULL default '0',
  `mb_id` varchar(20) NOT NULL default '',
  `is_name` varchar(255) NOT NULL default '',
  `is_password` varchar(255) NOT NULL default '',
  `is_score` tinyint(4) NOT NULL default '0',
  `is_subject` varchar(255) NOT NULL default '',
  `is_content` text NOT NULL,
  `is_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_ip` varchar(25) NOT NULL default '',
  `is_confirm` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`is_id`),
  KEY `index1` (`it_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_item_table]_qa`
##

DROP TABLE IF EXISTS `$g4[yc4_item_table]_qa`;


CREATE TABLE `$g4[yc4_item_table]_qa` (
  `iq_id` int(11) NOT NULL auto_increment,
  `it_id` varchar(10) NOT NULL default '',
  `mb_id` varchar(20) NOT NULL default '',
  `iq_name` varchar(255) NOT NULL default '',
  `iq_password` varchar(255) NOT NULL default '',
  `iq_subject` varchar(255) NOT NULL default '',
  `iq_question` text NOT NULL,
  `iq_answer` text NOT NULL,
  `iq_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `iq_ip` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`iq_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_item_table]_relation`
##

DROP TABLE IF EXISTS `$g4[yc4_item_table]_relation`;


CREATE TABLE `$g4[yc4_item_table]_relation` (
  `it_id` varchar(10) NOT NULL default '',
  `it_id2` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`it_id`,`it_id2`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_new_win_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_new_win_table]`;


CREATE TABLE `$g4[yc4_new_win_table]` (
  `nw_id` int(11) NOT NULL auto_increment,
  `nw_begin_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `nw_end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `nw_disable_hours` int(11) NOT NULL default '0',
  `nw_left` int(11) NOT NULL default '0',
  `nw_top` int(11) NOT NULL default '0',
  `nw_height` int(11) NOT NULL default '0',
  `nw_width` int(11) NOT NULL default '0',
  `nw_subject` text NOT NULL,
  `nw_content` text NOT NULL,
  `nw_content_html` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`nw_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_onlinecalc_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_onlinecalc_table]`;


CREATE TABLE `$g4[yc4_onlinecalc_table]` (
  `oc_id` int(11) NOT NULL auto_increment,
  `oc_subject` varchar(255) NOT NULL default '',
  `oc_category` text NOT NULL,
  `oc_head_html` text NOT NULL,
  `oc_tail_html` text NOT NULL,
  PRIMARY KEY  (`oc_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_order_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_order_table]`;


CREATE TABLE `$g4[yc4_order_table]` (
  `od_id` varchar(10) NOT NULL default '',
  `on_uid` varchar(32) NOT NULL default '',
  `mb_id` varchar(20) NOT NULL default '',
  `od_pwd` varchar(255) NOT NULL default '',
  `od_name` varchar(20) NOT NULL default '',
  `od_email` varchar(100) NOT NULL default '',
  `od_tel` varchar(20) NOT NULL default '',
  `od_hp` varchar(20) NOT NULL default '',
  `od_zip1` char(3) NOT NULL default '',
  `od_zip2` char(3) NOT NULL default '',
  `od_addr1` varchar(100) NOT NULL default '',
  `od_addr2` varchar(100) NOT NULL default '',
  `od_deposit_name` varchar(20) NOT NULL default '',
  `od_b_name` varchar(20) NOT NULL default '',
  `od_b_tel` varchar(20) NOT NULL default '',
  `od_b_hp` varchar(20) NOT NULL default '',
  `od_b_zip1` char(3) NOT NULL default '',
  `od_b_zip2` char(3) NOT NULL default '',
  `od_b_addr1` varchar(100) NOT NULL default '',
  `od_b_addr2` varchar(100) NOT NULL default '',
  `od_memo` text NOT NULL,
  `od_send_cost` int(11) NOT NULL default '0',
  `od_temp_bank` int(11) NOT NULL default '0',
  `od_temp_card` int(11) NOT NULL default '0',
  `od_temp_hp` int(11) NOT NULL,
  `od_temp_point` int(11) NOT NULL default '0',
  `od_receipt_bank` int(11) NOT NULL default '0',
  `od_receipt_card` int(11) NOT NULL default '0',
  `od_receipt_hp` int(11) NOT NULL,
  `od_receipt_point` int(11) NOT NULL default '0',
  `od_bank_account` varchar(255) NOT NULL default '',
  `od_bank_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `od_card_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `od_hp_time` datetime NOT NULL,
  `od_cancel_card` int(11) NOT NULL default '0',
  `od_dc_amount` int(11) NOT NULL default '0',
  `od_refund_amount` int(11) NOT NULL default '0',
  `od_shop_memo` text NOT NULL,
  `dl_id` int(11) NOT NULL default '0',
  `od_invoice` varchar(255) NOT NULL default '',
  `od_invoice_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `od_hope_date` date NOT NULL default '0000-00-00',
  `od_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `od_ip` varchar(25) NOT NULL default '',
  `od_settle_case` varchar(255) NOT NULL default '',
  `od_escrow1` varchar(255) NOT NULL default '',
  `od_escrow2` varchar(255) NOT NULL default '',
  `od_escrow3` varchar(255) NOT NULL default '',
  `od_cash_no` varchar(255) NOT NULL,
  `od_cash_receipt_no` varchar(255) NOT NULL,
  `od_cash_app_time` varchar(255) NOT NULL,
  `od_cash_reg_stat` varchar(255) NOT NULL,
  `od_cash_reg_desc` varchar(255) NOT NULL,
  `od_cash_tr_code` varchar(255) NOT NULL,
  `od_cash_id_info` varchar(255) NOT NULL,
  `od_cash` tinyint(4) NOT NULL,
  `od_cash_allthegate_gubun_cd` varchar(255) NOT NULL,
  `od_cash_allthegate_confirm_no` varchar(255) NOT NULL,
  `od_cash_allthegate_adm_no` varchar(255) NOT NULL,
  `od_cash_tgcorp_mxissueno` varchar(255) NOT NULL,
  `od_cash_inicis_noappl` varchar(255) NOT NULL,
  `od_cash_inicis_pgauthdate` varchar(255) NOT NULL,
  `od_cash_inicis_pgauthtime` varchar(255) NOT NULL,
  `od_cash_inicis_tid` varchar(255) NOT NULL,
  `od_cash_inicis_ruseopt` varchar(255) NOT NULL,
  `od_cash_receiptnumber` varchar(255) NOT NULL,
  `od_cash_kspay_revatransactionno` varchar(255) NOT NULL,
  PRIMARY KEY  (`od_id`),
  UNIQUE KEY `index1` (`on_uid`),
  KEY `index2` (`mb_id`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_on_uid_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_on_uid_table]`;


CREATE TABLE `$g4[yc4_on_uid_table]` (
  `on_id` int(11) NOT NULL auto_increment,
  `on_uid` varchar(32) NOT NULL default '',
  `on_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `session_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`on_id`),
  UNIQUE KEY `on_uid` (`on_uid`)
) DEFAULT CHARSET=utf8;


##
## Table structure for table `$g4[yc4_wish_table]`
##

DROP TABLE IF EXISTS `$g4[yc4_wish_table]`;


CREATE TABLE `$g4[yc4_wish_table]` (
  `wi_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(20) NOT NULL default '',
  `it_id` varchar(10) NOT NULL default '0',
  `wi_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `wi_ip` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`wi_id`),
  KEY `index1` (`mb_id`)
) DEFAULT CHARSET=utf8;











## Dump completed on 2013-02-26 16:04:46
