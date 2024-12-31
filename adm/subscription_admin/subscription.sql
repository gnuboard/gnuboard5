-- --------------------------------------------------------

--
-- Table structure for table `g5_subscription_config`
--

-- DROP TABLE IF EXISTS `g5_subscription_config`;
CREATE TABLE IF NOT EXISTS `g5_subscription_config` (
  `su_id` int(11) NOT NULL auto_increment,
  `su_pg_service` varchar(80) NOT NULL DEFAULT '',
  `su_card_test` int(11) NOT NULL DEFAULT '0',
  `su_kcp_mid` varchar(80) NOT NULL DEFAULT '',
  `su_kcp_group_id` varchar(80) NOT NULL DEFAULT '',
  `su_kcp_cert_info` text NOT NULL,
  `su_inicis_mid` varchar(80) NOT NULL DEFAULT '',
  `su_inicis_iniapi_key` varchar(30) NOT NULL DEFAULT '',
  `su_inicis_iniapi_iv` varchar(30) NOT NULL DEFAULT '',
  `su_inicis_sign_key` varchar(80) NOT NULL DEFAULT '',
  `su_tosspayments_mid` varchar(30) NOT NULL DEFAULT '',
  `su_tosspayments_api_clientkey` varchar(80) NOT NULL DEFAULT '',
  `su_tosspayments_api_secretkey` varchar(80) NOT NULL DEFAULT '',
  `su_nice_clientid` varchar(80) NOT NULL DEFAULT '',
  `su_nice_secretkey` varchar(80) NOT NULL DEFAULT '',
  `su_cron_updatetime` datetime DEFAULT NULL,
  `su_cron_execute_hour` tinyint(2) NOT NULL DEFAULT '0',
  `su_opt_settings` text NOT NULL,
  `su_use_settings` text NOT NULL,
  `su_hope_date_use` tinyint(2) NOT NULL DEFAULT '0',
  `su_hope_date_after` int(10) NOT NULL DEFAULT '0',
  `su_output_display_type` tinyint(2) NOT NULL DEFAULT '0',
  `su_auto_payment_lead_days` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`su_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_subscription_cart`
--

-- DROP TABLE IF EXISTS `g5_subscription_cart`;
CREATE TABLE IF NOT EXISTS `g5_subscription_cart` (
  `ct_id` int(11) NOT NULL AUTO_INCREMENT,
  `od_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `it_id` varchar(20) NOT NULL DEFAULT '',
  `it_name` varchar(255) NOT NULL DEFAULT '',
  `it_sc_type` tinyint(4) NOT NULL DEFAULT '0',
  `it_sc_method` tinyint(4) NOT NULL DEFAULT '0',
  `it_sc_price` int(11) NOT NULL DEFAULT '0',
  `it_sc_minimum` int(11) NOT NULL DEFAULT '0',
  `it_sc_qty` int(11) NOT NULL DEFAULT '0',
  `ct_status` varchar(255) NOT NULL DEFAULT '',
  `ct_history` text NOT NULL,
  `ct_price` int(11) NOT NULL DEFAULT '0',
  `ct_point` int(11) NOT NULL DEFAULT '0',
  `cp_price` int(11) NOT NULL DEFAULT '0',
  `ct_point_use` tinyint(4) NOT NULL DEFAULT '0',
  `ct_stock_use` tinyint(4) NOT NULL DEFAULT '0',
  `ct_option` varchar(255) NOT NULL DEFAULT '',
  `ct_qty` int(11) NOT NULL DEFAULT '0',
  `ct_notax` tinyint(4) NOT NULL DEFAULT '0',
  `io_id` varchar(255) NOT NULL DEFAULT '',
  `io_type` tinyint(4) NOT NULL DEFAULT '0',
  `io_price` int(11) NOT NULL DEFAULT '0',
  `ct_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ct_ip` varchar(25) NOT NULL DEFAULT '',
  `ct_send_cost` tinyint(4) NOT NULL DEFAULT '0',
  `ct_direct` tinyint(4) NOT NULL DEFAULT '0',
  `ct_select` tinyint(4) NOT NULL DEFAULT '0',
  `ct_select_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ct_subscription_number` tinyint(4) NOT NULL DEFAULT '0',
  `ct_firstshipment_date` DATETIME DEFAULT NULL,
  `ct_date_format` CHAR(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`ct_id`),
  KEY `od_id` (`od_id`),
  KEY `it_id` (`it_id`),
  KEY `ct_status` (`ct_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_subscription_cart`
--

-- DROP TABLE IF EXISTS `g5_subscription_pay_basket`;
CREATE TABLE IF NOT EXISTS `g5_subscription_pay_basket` (
  `pb_id` int(11) NOT NULL AUTO_INCREMENT,
  `od_id` bigint(20) unsigned NOT NULL,
  `pay_id` int(11) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `it_id` varchar(20) NOT NULL DEFAULT '',
  `it_name` varchar(255) NOT NULL DEFAULT '',
  `it_sc_type` tinyint(4) NOT NULL DEFAULT '0',
  `it_sc_method` tinyint(4) NOT NULL DEFAULT '0',
  `it_sc_price` int(11) NOT NULL DEFAULT '0',
  `it_sc_minimum` int(11) NOT NULL DEFAULT '0',
  `it_sc_qty` int(11) NOT NULL DEFAULT '0',
  `pb_status` varchar(255) NOT NULL DEFAULT '',
  `pb_history` text NOT NULL,
  `pb_price` int(11) NOT NULL DEFAULT '0',
  `pb_point` int(11) NOT NULL DEFAULT '0',
  `cp_price` int(11) NOT NULL DEFAULT '0',
  `pb_point_use` tinyint(4) NOT NULL DEFAULT '0',
  `pb_stock_use` tinyint(4) NOT NULL DEFAULT '0',
  `pb_option` varchar(255) NOT NULL DEFAULT '',
  `pb_qty` int(11) NOT NULL DEFAULT '0',
  `pb_notax` tinyint(4) NOT NULL DEFAULT '0',
  `io_id` varchar(255) NOT NULL DEFAULT '',
  `io_type` tinyint(4) NOT NULL DEFAULT '0',
  `io_price` int(11) NOT NULL DEFAULT '0',
  `pb_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pb_ip` varchar(25) NOT NULL DEFAULT '',
  `pb_send_cost` tinyint(4) NOT NULL DEFAULT '0',
  `pb_direct` tinyint(4) NOT NULL DEFAULT '0',
  `pb_select` tinyint(4) NOT NULL DEFAULT '0',
  `pb_select_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pb_subscription_number` tinyint(4) NOT NULL DEFAULT '0',
  `pb_firstshipment_date` DATETIME DEFAULT NULL,
  `pb_date_format` CHAR(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`pb_id`),
  KEY `od_id` (`od_id`),
  KEY `it_id` (`it_id`),
  KEY `pb_status` (`pb_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
--
-- Table structure for table `g5_subscription_item_use`
--

-- DROP TABLE IF EXISTS `g5_subscription_item_use`;
CREATE TABLE IF NOT EXISTS `g5_subscription_item_use` (
  `is_id` int(11) NOT NULL AUTO_INCREMENT,
  `it_id` varchar(20) NOT NULL DEFAULT '0',
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `is_name` varchar(255) NOT NULL DEFAULT '',
  `is_password` varchar(255) NOT NULL DEFAULT '',
  `is_score` tinyint(4) NOT NULL DEFAULT '0',
  `is_subject` varchar(255) NOT NULL DEFAULT '',
  `is_content` text NOT NULL,
  `is_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_ip` varchar(25) NOT NULL DEFAULT '',
  `is_confirm` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`is_id`),
  KEY `index1` (`it_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_subscription_item_use`
--

-- DROP TABLE IF EXISTS `g5_subscription_pay`;

CREATE TABLE IF NOT EXISTS `g5_subscription_pay` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `od_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `subscription_id` char(50) NOT NULL DEFAULT '',
  `py_name` varchar(20) NOT NULL DEFAULT '',
  `py_email` varchar(100) NOT NULL DEFAULT '',

  `py_b_name` varchar(20) NOT NULL DEFAULT '',
  `py_b_tel` varchar(20) NOT NULL DEFAULT '',
  `py_b_hp` varchar(20) NOT NULL DEFAULT '',
  `py_b_zip1` char(3) NOT NULL DEFAULT '',
  `py_b_zip2` char(3) NOT NULL DEFAULT '',
  `py_b_addr1` varchar(100) NOT NULL DEFAULT '',
  `py_b_addr2` varchar(100) NOT NULL DEFAULT '',
  `py_b_addr3` varchar(255) NOT NULL DEFAULT '',
  `py_b_addr_jibeon` varchar(255) NOT NULL DEFAULT '',
  
  `py_memo` text NOT NULL,
  `py_cart_count` int(11) NOT NULL DEFAULT '0',
  `py_cart_price` int(11) NOT NULL DEFAULT '0',
  `py_cart_coupon` int(11) NOT NULL DEFAULT '0',
  `py_send_cost` int(11) NOT NULL DEFAULT '0',
  `py_send_cost2` int(11) NOT NULL DEFAULT '0',
  `py_send_coupon` int(11) NOT NULL DEFAULT '0',  
  `py_receipt_price` int(11) NOT NULL DEFAULT '0',
  `py_cancel_price` int(11) NOT NULL DEFAULT '0',
  `py_receipt_point` int(11) NOT NULL DEFAULT '0',
  `py_refund_price` int(11) NOT NULL DEFAULT '0',
  `py_bank_account` varchar(255) NOT NULL DEFAULT '',
  `py_receipt_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `py_receipt_url` varchar(255) NOT NULL DEFAULT '',
  `py_coupon` int(11) NOT NULL DEFAULT '0',
  `py_misu` int(11) NOT NULL DEFAULT '0',
  `py_subscription_memo` text NOT NULL,
  `py_mod_history` text NOT NULL,
  `py_status` varchar(255) NOT NULL DEFAULT '',
  
  `py_round_no` int(10) NOT NULL DEFAULT '1',
  `py_hope_date` date NOT NULL DEFAULT '0000-00-00',
  
  `py_settle_case` varchar(255) NOT NULL DEFAULT '',
  `py_other_pay_type` varchar(100) NOT NULL DEFAULT '',
  `py_test` tinyint(4) NOT NULL DEFAULT '0',
  `py_mobile` tinyint(4) NOT NULL DEFAULT '0',
  `py_pg` varchar(255) NOT NULL DEFAULT '',
  `py_tno` varchar(255) NOT NULL DEFAULT '',
  `py_app_no` varchar(20) NOT NULL DEFAULT '',
  `py_escrow` tinyint(4) NOT NULL DEFAULT '0',
  `py_casseqno` varchar(255) NOT NULL DEFAULT '',
  `py_tax_flag` tinyint(4) NOT NULL DEFAULT '0',
  `py_tax_mny` int(11) NOT NULL DEFAULT '0',
  `py_vat_mny` int(11) NOT NULL DEFAULT '0',
  `py_free_mny` int(11) NOT NULL DEFAULT '0',
  `py_delivery_company` varchar(255) NOT NULL DEFAULT '0',
  `py_invoice` varchar(255) NOT NULL DEFAULT '',
  `py_invoice_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  
  `py_cash` tinyint(4) NOT NULL,
  `py_cash_no` varchar(255) NOT NULL,
  `py_cash_info` text NOT NULL,
  
  `py_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',  
  PRIMARY KEY (`id`),
  KEY `index2` (`mb_id`),
  KEY `index3` (`subscription_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_shop_order`
--

-- DROP TABLE IF EXISTS `g5_subscription_order`;
CREATE TABLE IF NOT EXISTS `g5_subscription_order_history` (
  `hs_id` bigint(20) NOT NULL auto_increment,
  `hs_parent` bigint(20) NOT NULL DEFAULT '0',
  `hs_type` varchar(20) NOT NULL DEFAULT '',
  `hs_category` varchar(100) NOT NULL DEFAULT '',
  `od_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `hs_content` text NOT NULL,
  `hs_time` datetime DEFAULT NULL,
  PRIMARY KEY (`hs_id`),
  KEY `mb_id` (`mb_id`),
  KEY `hs_type` (`hs_type`),
  KEY `hs_category` (`hs_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_subscription_order`
--

-- DROP TABLE IF EXISTS `g5_subscription_order`;
CREATE TABLE IF NOT EXISTS `g5_subscription_order` (
  `od_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `ci_id` int(11) NOT NULL DEFAULT '0',
  `od_name` varchar(20) NOT NULL DEFAULT '',
  `od_email` varchar(100) NOT NULL DEFAULT '',
  `od_tel` varchar(20) NOT NULL DEFAULT '',
  `od_hp` varchar(20) NOT NULL DEFAULT '',
  `od_zip1` char(3) NOT NULL DEFAULT '',
  `od_zip2` char(3) NOT NULL DEFAULT '',
  `od_addr1` varchar(100) NOT NULL DEFAULT '',
  `od_addr2` varchar(100) NOT NULL DEFAULT '',
  `od_addr3` varchar(255) NOT NULL DEFAULT '',
  `od_addr_jibeon` varchar(255) NOT NULL DEFAULT '',
  `od_deposit_name` varchar(20) NOT NULL DEFAULT '',
  `od_b_name` varchar(20) NOT NULL DEFAULT '',
  `od_b_tel` varchar(20) NOT NULL DEFAULT '',
  `od_b_hp` varchar(20) NOT NULL DEFAULT '',
  `od_b_zip1` char(3) NOT NULL DEFAULT '',
  `od_b_zip2` char(3) NOT NULL DEFAULT '',
  `od_b_addr1` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr2` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr3` varchar(255) NOT NULL DEFAULT '',
  `od_b_addr_jibeon` varchar(255) NOT NULL DEFAULT '',
  `od_memo` text NOT NULL,
  `next_delivery_date` datetime DEFAULT NULL,
  `next_billing_date` datetime DEFAULT NULL,
  `od_enable_status` tinyint(3) NOT NULL DEFAULT '1',
  `last_billed_date` datetime DEFAULT NULL,
  `od_cart_count` int(11) NOT NULL DEFAULT '0',
  `od_cart_price` int(11) NOT NULL DEFAULT '0',
  `od_cart_coupon` int(11) NOT NULL DEFAULT '0',
  `od_send_cost` int(11) NOT NULL DEFAULT '0',
  `od_send_cost2` int(11) NOT NULL DEFAULT '0',
  `od_send_coupon` int(11) NOT NULL DEFAULT '0',  
  `od_receipt_price` int(11) NOT NULL DEFAULT '0',
  `od_receipt_point` int(11) NOT NULL DEFAULT '0',
  `od_card_name` varchar(100) NOT NULL DEFAULT '',
  `od_receipt_time` datetime DEFAULT NULL,
  `od_coupon` int(11) NOT NULL DEFAULT '0',
  `od_subscription_memo` text NOT NULL,
  `od_mod_history` text NOT NULL,
  `od_hope_date` datetime DEFAULT NULL,  
  `od_settle_case` varchar(255) NOT NULL DEFAULT '',
  `od_other_pay_type` varchar(100) NOT NULL DEFAULT '',
  `od_test` tinyint(4) NOT NULL DEFAULT '0',
  `od_mobile` tinyint(4) NOT NULL DEFAULT '0',
  `od_pg` varchar(255) NOT NULL DEFAULT '',
  `od_tno` varchar(255) NOT NULL DEFAULT '',
  `od_tax_flag` tinyint(4) NOT NULL DEFAULT '0',
  `od_tax_mny` int(11) NOT NULL DEFAULT '0',
  `od_vat_mny` int(11) NOT NULL DEFAULT '0',
  `od_free_mny` int(11) NOT NULL DEFAULT '0',
  `od_ip` varchar(25) NOT NULL DEFAULT '',
  `card_mask_number` varchar(50) NOT NULL DEFAULT '',
  `card_billkey` varchar(100) NOT NULL DEFAULT '',
  `od_pays_total` int(10) NOT NULL DEFAULT '0',
  `od_subscription_date_format` CHAR(4) NOT NULL DEFAULT '',
  `od_subscription_selected_data` text NOT NULL,
  `od_subscription_selected_number` text NOT NULL,
  `od_subscription_number` tinyint(4) NOT NULL DEFAULT '0',
  `od_firstshipment_date` datetime DEFAULT NULL,
  `od_time` datetime DEFAULT NULL,
  PRIMARY KEY (`od_id`),
  KEY `index2` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_subscription_mb_cardinfo`
--

-- DROP TABLE IF EXISTS `g5_subscription_mb_cardinfo`;
CREATE TABLE IF NOT EXISTS `g5_subscription_mb_cardinfo` (
  `ci_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(100) NOT NULL DEFAULT '',  
  `pg_service` varchar(20) NOT NULL DEFAULT '',
  `pg_id` varchar(50) NOT NULL DEFAULT '',
  `pg_apikey` varchar(150) NOT NULL DEFAULT '',
  `first_ordernumber` varchar(20) NOT NULL DEFAULT '',
  `card_mask_number` varchar(50) NOT NULL DEFAULT '',
  `card_billkey` varchar(100) NOT NULL DEFAULT '',
  `od_card_name` varchar(100) NOT NULL DEFAULT '',
  `od_id` bigint(20) unsigned NOT NULL,
  `od_test` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ci_id`),
  KEY `index2` (`mb_id`),
  UNIQUE KEY `unique_pg_service_apikey_billkey` (`pg_service`, `pg_apikey`, `card_billkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_shop_order_data`
--

-- DROP TABLE IF EXISTS `g5_subscription_order_data`;
CREATE TABLE IF NOT EXISTS `g5_subscription_order_data` (
  `od_id` bigint(20) unsigned NOT NULL,
  `cart_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `dt_pg` varchar(255) NOT NULL DEFAULT '',
  `dt_data` text NOT NULL,
  `dt_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `od_id` (`od_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_unique`
--

-- DROP TABLE IF EXISTS `g5_subscription_uniqid`;
CREATE TABLE IF NOT EXISTS `g5_subscription_uniqid` (
  `uq_id` bigint(20) unsigned NOT NULL,
  `uq_ip` varchar(255) NOT NULL,
  PRIMARY KEY (`uq_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------