-- --------------------------------------------------------

--
-- Table structure for table `shop_banner`
--

DROP TABLE IF EXISTS `shop_banner`;
CREATE TABLE IF NOT EXISTS `shop_banner` (
  `bn_id` int(11) NOT NULL AUTO_INCREMENT,
  `bn_alt` varchar(255) NOT NULL DEFAULT '',
  `bn_url` varchar(255) NOT NULL DEFAULT '',
  `bn_position` varchar(255) NOT NULL DEFAULT '',
  `bn_border` tinyint(4) NOT NULL DEFAULT '0',
  `bn_new_win` tinyint(4) NOT NULL DEFAULT '0',
  `bn_begin_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bn_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bn_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bn_hit` int(11) NOT NULL DEFAULT '0',
  `bn_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_card_history`
--

DROP TABLE IF EXISTS `shop_card_history`;
CREATE TABLE IF NOT EXISTS `shop_card_history` (
  `cd_id` int(11) NOT NULL AUTO_INCREMENT,
  `od_id` bigint(20) unsigned NOT NULL,
  `uq_id` bigint(20) unsigned NOT NULL,
  `cd_mall_id` varchar(20) NOT NULL DEFAULT '',
  `cd_amount` int(11) NOT NULL DEFAULT '0',
  `cd_app_no` varchar(20) NOT NULL DEFAULT '',
  `cd_app_rt` varchar(8) NOT NULL DEFAULT '',
  `cd_trade_ymd` date NOT NULL DEFAULT '0000-00-00',
  `cd_trade_hms` time NOT NULL DEFAULT '00:00:00',
  `cd_quota` char(2) NOT NULL DEFAULT '',
  `cd_opt01` varchar(255) NOT NULL DEFAULT '',
  `cd_opt02` varchar(255) NOT NULL DEFAULT '',
  `cd_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cd_ip` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`cd_id`),
  KEY `od_id` (`od_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_cart`
--

DROP TABLE IF EXISTS `shop_cart`;
CREATE TABLE IF NOT EXISTS `shop_cart` (
  `ct_id` int(11) NOT NULL AUTO_INCREMENT,
  `uq_id` bigint(20) unsigned NOT NULL,
  `it_id` varchar(10) NOT NULL DEFAULT '0',
  `it_opt1` varchar(255) NOT NULL DEFAULT '',
  `it_opt2` varchar(255) NOT NULL DEFAULT '',
  `it_opt3` varchar(255) NOT NULL DEFAULT '',
  `it_opt4` varchar(255) NOT NULL DEFAULT '',
  `it_opt5` varchar(255) NOT NULL DEFAULT '',
  `it_opt6` varchar(255) NOT NULL DEFAULT '',
  `ct_status` enum('쇼핑','주문','준비','배송','완료','취소','반품','품절') NOT NULL DEFAULT '쇼핑',
  `ct_history` text NOT NULL,
  `ct_amount` int(11) NOT NULL DEFAULT '0',
  `ct_point` int(11) NOT NULL DEFAULT '0',
  `ct_point_use` tinyint(4) NOT NULL DEFAULT '0',
  `ct_stock_use` tinyint(4) NOT NULL DEFAULT '0',
  `ct_qty` int(11) NOT NULL DEFAULT '0',
  `ct_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ct_ip` varchar(25) NOT NULL DEFAULT '',
  `ct_send_cost` varchar(255) NOT NULL,
  `ct_direct` tinyint(4) NOT NULL,
  PRIMARY KEY (`ct_id`),
  KEY `uq_id` (`uq_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_category`
--

DROP TABLE IF EXISTS `shop_category`;
CREATE TABLE IF NOT EXISTS `shop_category` (
  `ca_id` varchar(10) NOT NULL DEFAULT '0',
  `ca_name` varchar(255) NOT NULL DEFAULT '',
  `ca_skin` varchar(255) NOT NULL DEFAULT '',
  `ca_opt1_subject` varchar(255) NOT NULL DEFAULT '',
  `ca_opt2_subject` varchar(255) NOT NULL DEFAULT '',
  `ca_opt3_subject` varchar(255) NOT NULL DEFAULT '',
  `ca_opt4_subject` varchar(255) NOT NULL DEFAULT '',
  `ca_opt5_subject` varchar(255) NOT NULL DEFAULT '',
  `ca_opt6_subject` varchar(255) NOT NULL DEFAULT '',
  `ca_img_width` int(11) NOT NULL DEFAULT '0',
  `ca_img_height` int(11) NOT NULL DEFAULT '0',
  `ca_sell_email` varchar(255) NOT NULL DEFAULT '',
  `ca_use` tinyint(4) NOT NULL DEFAULT '0',
  `ca_stock_qty` int(11) NOT NULL DEFAULT '0',
  `ca_explan_html` tinyint(4) NOT NULL DEFAULT '0',
  `ca_head_html` text NOT NULL,
  `ca_tail_html` text NOT NULL,
  `ca_list_mod` int(11) NOT NULL DEFAULT '0',
  `ca_list_row` int(11) NOT NULL DEFAULT '0',
  `ca_include_head` varchar(255) NOT NULL DEFAULT '',
  `ca_include_tail` varchar(255) NOT NULL DEFAULT '',
  `ca_mb_id` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ca_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_content`
--

DROP TABLE IF EXISTS `shop_content`;
CREATE TABLE IF NOT EXISTS `shop_content` (
  `co_id` varchar(20) NOT NULL DEFAULT '',
  `co_html` tinyint(4) NOT NULL DEFAULT '0',
  `co_subject` varchar(255) NOT NULL DEFAULT '',
  `co_content` longtext NOT NULL,
  `co_hit` int(11) NOT NULL DEFAULT '0',
  `co_include_head` varchar(255) NOT NULL,
  `co_include_tail` varchar(255) NOT NULL,
  PRIMARY KEY (`co_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_default`
--

DROP TABLE IF EXISTS `shop_default`;
CREATE TABLE IF NOT EXISTS `shop_default` (
  `de_admin_company_owner` varchar(255) NOT NULL DEFAULT '',
  `de_admin_company_name` varchar(255) NOT NULL DEFAULT '',
  `de_admin_company_saupja_no` varchar(255) NOT NULL DEFAULT '',
  `de_admin_company_tel` varchar(255) NOT NULL DEFAULT '',
  `de_admin_company_fax` varchar(255) NOT NULL DEFAULT '',
  `de_admin_tongsin_no` varchar(255) NOT NULL DEFAULT '',
  `de_admin_company_zip` varchar(255) NOT NULL DEFAULT '',
  `de_admin_company_addr` varchar(255) NOT NULL DEFAULT '',
  `de_admin_info_name` varchar(255) NOT NULL DEFAULT '',
  `de_admin_info_email` varchar(255) NOT NULL DEFAULT '',
  `de_type1_list_use` int(11) NOT NULL DEFAULT '0',
  `de_type1_list_skin` varchar(255) NOT NULL DEFAULT '',
  `de_type1_list_mod` int(11) NOT NULL DEFAULT '0',
  `de_type1_list_row` int(11) NOT NULL DEFAULT '0',
  `de_type1_img_width` int(11) NOT NULL DEFAULT '0',
  `de_type1_img_height` int(11) NOT NULL DEFAULT '0',
  `de_type2_list_use` int(11) NOT NULL DEFAULT '0',
  `de_type2_list_skin` varchar(255) NOT NULL DEFAULT '',
  `de_type2_list_mod` int(11) NOT NULL DEFAULT '0',
  `de_type2_list_row` int(11) NOT NULL DEFAULT '0',
  `de_type2_img_width` int(11) NOT NULL DEFAULT '0',
  `de_type2_img_height` int(11) NOT NULL DEFAULT '0',
  `de_type3_list_use` int(11) NOT NULL DEFAULT '0',
  `de_type3_list_skin` varchar(255) NOT NULL DEFAULT '',
  `de_type3_list_mod` int(11) NOT NULL DEFAULT '0',
  `de_type3_list_row` int(11) NOT NULL DEFAULT '0',
  `de_type3_img_width` int(11) NOT NULL DEFAULT '0',
  `de_type3_img_height` int(11) NOT NULL DEFAULT '0',
  `de_type4_list_use` int(11) NOT NULL DEFAULT '0',
  `de_type4_list_skin` varchar(255) NOT NULL DEFAULT '',
  `de_type4_list_mod` int(11) NOT NULL DEFAULT '0',
  `de_type4_list_row` int(11) NOT NULL DEFAULT '0',
  `de_type4_img_width` int(11) NOT NULL DEFAULT '0',
  `de_type4_img_height` int(11) NOT NULL DEFAULT '0',
  `de_type5_list_use` int(11) NOT NULL DEFAULT '0',
  `de_type5_list_skin` varchar(255) NOT NULL DEFAULT '',
  `de_type5_list_mod` int(11) NOT NULL DEFAULT '0',
  `de_type5_list_row` int(11) NOT NULL DEFAULT '0',
  `de_type5_img_width` int(11) NOT NULL DEFAULT '0',
  `de_type5_img_height` int(11) NOT NULL DEFAULT '0',
  `de_rel_list_mod` int(11) NOT NULL DEFAULT '0',
  `de_rel_img_width` int(11) NOT NULL DEFAULT '0',
  `de_rel_img_height` int(11) NOT NULL DEFAULT '0',
  `de_bank_use` int(11) NOT NULL DEFAULT '0',
  `de_bank_account` text NOT NULL,
  `de_card_test` int(11) NOT NULL DEFAULT '0',
  `de_card_use` int(11) NOT NULL DEFAULT '0',
  `de_card_point` int(11) NOT NULL DEFAULT '0',
  `de_card_pg` varchar(255) NOT NULL DEFAULT '',
  `de_card_max_amount` int(11) NOT NULL DEFAULT '0',
  `de_banktown_mid` varchar(255) NOT NULL DEFAULT '',
  `de_banktown_auth_key` varchar(255) NOT NULL DEFAULT '',
  `de_telec_mid` varchar(255) NOT NULL DEFAULT '',
  `de_point_settle` int(11) NOT NULL DEFAULT '0',
  `de_level_sell` int(11) NOT NULL DEFAULT '0',
  `de_send_cost_case` varchar(255) NOT NULL DEFAULT '',
  `de_send_cost_limit` varchar(255) NOT NULL DEFAULT '',
  `de_send_cost_list` varchar(255) NOT NULL DEFAULT '',
  `de_hope_date_use` int(11) NOT NULL DEFAULT '0',
  `de_hope_date_after` int(11) NOT NULL DEFAULT '0',
  `de_baesong_content` text NOT NULL,
  `de_change_content` text NOT NULL,
  `de_point_days` int(11) NOT NULL DEFAULT '0',
  `de_simg_width` int(11) NOT NULL DEFAULT '0',
  `de_simg_height` int(11) NOT NULL DEFAULT '0',
  `de_mimg_width` int(11) NOT NULL DEFAULT '0',
  `de_mimg_height` int(11) NOT NULL DEFAULT '0',
  `de_scroll_banner_use` tinyint(4) NOT NULL DEFAULT '0',
  `de_cart_skin` varchar(255) NOT NULL DEFAULT '',
  `de_register` varchar(255) NOT NULL DEFAULT '',
  `de_sms_cont1` varchar(255) NOT NULL DEFAULT '',
  `de_sms_cont2` varchar(255) NOT NULL DEFAULT '',
  `de_sms_cont3` varchar(255) NOT NULL DEFAULT '',
  `de_sms_cont4` varchar(255) NOT NULL DEFAULT '',
  `de_sms_use1` tinyint(4) NOT NULL DEFAULT '0',
  `de_sms_use2` tinyint(4) NOT NULL DEFAULT '0',
  `de_sms_use3` tinyint(4) NOT NULL DEFAULT '0',
  `de_sms_use4` tinyint(4) NOT NULL DEFAULT '0',
  `de_sms_hp` varchar(255) NOT NULL DEFAULT '',
  `de_kcp_mid` varchar(255) NOT NULL DEFAULT '',
  `de_iche_use` tinyint(4) NOT NULL DEFAULT '0',
  `de_item_ps_use` tinyint(4) NOT NULL DEFAULT '0',
  `de_code_dup_use` tinyint(4) NOT NULL DEFAULT '0',
  `de_point_per` tinyint(4) NOT NULL DEFAULT '0',
  `de_admin_buga_no` varchar(255) NOT NULL DEFAULT '',
  `de_different_msg` tinyint(4) NOT NULL DEFAULT '0',
  `de_sms_use` varchar(255) NOT NULL DEFAULT '',
  `de_icode_id` varchar(255) NOT NULL DEFAULT '',
  `de_icode_pw` varchar(255) NOT NULL DEFAULT '',
  `de_icode_server_ip` varchar(255) NOT NULL DEFAULT '',
  `de_icode_server_port` varchar(255) NOT NULL DEFAULT '',
  `de_kcp_site_key` varchar(255) NOT NULL DEFAULT '',
  `de_vbank_use` varchar(255) NOT NULL DEFAULT '',
  `de_taxsave_use` tinyint(4) NOT NULL,
  `de_guest_privacy` text NOT NULL,
  `de_hp_use` tinyint(4) NOT NULL DEFAULT '0',
  `de_escrow_use` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_delivery`
--

DROP TABLE IF EXISTS `shop_delivery`;
CREATE TABLE IF NOT EXISTS `shop_delivery` (
  `dl_id` int(11) NOT NULL AUTO_INCREMENT,
  `dl_company` varchar(255) NOT NULL DEFAULT '',
  `dl_url` varchar(255) NOT NULL DEFAULT '',
  `dl_tel` varchar(255) NOT NULL DEFAULT '',
  `dl_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_event`
--

DROP TABLE IF EXISTS `shop_event`;
CREATE TABLE IF NOT EXISTS `shop_event` (
  `ev_id` int(11) NOT NULL AUTO_INCREMENT,
  `it_group` int(11) NOT NULL DEFAULT '0',
  `ev_skin` varchar(255) NOT NULL DEFAULT '',
  `ev_img_width` int(11) NOT NULL DEFAULT '0',
  `ev_img_height` int(11) NOT NULL DEFAULT '0',
  `ev_list_mod` int(11) NOT NULL DEFAULT '0',
  `ev_list_row` int(11) NOT NULL DEFAULT '0',
  `ev_subject` varchar(255) NOT NULL DEFAULT '',
  `ev_head_html` text NOT NULL,
  `ev_tail_html` text NOT NULL,
  `ev_use` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ev_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_event_item`
--

DROP TABLE IF EXISTS `shop_event_item`;
CREATE TABLE IF NOT EXISTS `shop_event_item` (
  `ev_id` int(11) NOT NULL DEFAULT '0',
  `it_id` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`ev_id`,`it_id`),
  KEY `it_id` (`it_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_faq`
--

DROP TABLE IF EXISTS `shop_faq`;
CREATE TABLE IF NOT EXISTS `shop_faq` (
  `fa_id` int(11) NOT NULL AUTO_INCREMENT,
  `fm_id` int(11) NOT NULL DEFAULT '0',
  `fa_subject` text NOT NULL,
  `fa_content` text NOT NULL,
  `fa_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fa_id`),
  KEY `fm_id` (`fm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_faq_master`
--

DROP TABLE IF EXISTS `shop_faq_master`;
CREATE TABLE IF NOT EXISTS `shop_faq_master` (
  `fm_id` int(11) NOT NULL AUTO_INCREMENT,
  `fm_subject` varchar(255) NOT NULL DEFAULT '',
  `fm_head_html` text NOT NULL,
  `fm_tail_html` text NOT NULL,
  `fm_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fm_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_item`
--

DROP TABLE IF EXISTS `shop_item`;
CREATE TABLE IF NOT EXISTS `shop_item` (
  `it_id` varchar(10) NOT NULL DEFAULT '',
  `ca_id` varchar(10) NOT NULL DEFAULT '0',
  `ca_id2` varchar(255) NOT NULL DEFAULT '',
  `ca_id3` varchar(255) NOT NULL DEFAULT '',
  `it_name` varchar(255) NOT NULL DEFAULT '',
  `it_gallery` tinyint(4) NOT NULL DEFAULT '0',
  `it_maker` varchar(255) NOT NULL DEFAULT '',
  `it_origin` varchar(255) NOT NULL DEFAULT '',
  `it_opt1_subject` varchar(255) NOT NULL DEFAULT '',
  `it_opt2_subject` varchar(255) NOT NULL DEFAULT '',
  `it_opt3_subject` varchar(255) NOT NULL DEFAULT '',
  `it_opt4_subject` varchar(255) NOT NULL DEFAULT '',
  `it_opt5_subject` varchar(255) NOT NULL DEFAULT '',
  `it_opt6_subject` varchar(255) NOT NULL DEFAULT '',
  `it_opt1` text NOT NULL,
  `it_opt2` text NOT NULL,
  `it_opt3` text NOT NULL,
  `it_opt4` text NOT NULL,
  `it_opt5` text NOT NULL,
  `it_opt6` text NOT NULL,
  `it_type1` tinyint(4) NOT NULL DEFAULT '0',
  `it_type2` tinyint(4) NOT NULL DEFAULT '0',
  `it_type3` tinyint(4) NOT NULL DEFAULT '0',
  `it_type4` tinyint(4) NOT NULL DEFAULT '0',
  `it_type5` tinyint(4) NOT NULL DEFAULT '0',
  `it_basic` text NOT NULL,
  `it_explan` mediumtext NOT NULL,
  `it_explan_html` tinyint(4) NOT NULL DEFAULT '0',
  `it_cust_amount` int(11) NOT NULL DEFAULT '0',
  `it_amount` int(11) NOT NULL DEFAULT '0',
  `it_amount2` int(11) NOT NULL DEFAULT '0',
  `it_amount3` int(11) NOT NULL DEFAULT '0',
  `it_point` int(11) NOT NULL DEFAULT '0',
  `it_sell_email` varchar(255) NOT NULL DEFAULT '',
  `it_use` tinyint(4) NOT NULL DEFAULT '0',
  `it_stock_qty` int(11) NOT NULL DEFAULT '0',
  `it_head_html` text NOT NULL,
  `it_tail_html` text NOT NULL,
  `it_hit` int(11) NOT NULL DEFAULT '0',
  `it_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `it_ip` varchar(25) NOT NULL DEFAULT '',
  `it_order` int(11) NOT NULL DEFAULT '0',
  `it_tel_inq` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`it_id`),
  KEY `ca_id` (`ca_id`),
  KEY `it_name` (`it_name`),
  KEY `it_order` (`it_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_item_info`
--

DROP TABLE IF EXISTS `shop_item_info`;
CREATE TABLE IF NOT EXISTS `shop_item_info` (
  `ii_id` int(11) NOT NULL AUTO_INCREMENT,
  `it_id` varchar(10) NOT NULL,
  `ii_gubun` varchar(50) NOT NULL,
  `ii_article` varchar(50) NOT NULL,
  `ii_title` varchar(255) NOT NULL,
  `ii_value` varchar(255) NOT NULL,
  PRIMARY KEY (`ii_id`),
  UNIQUE KEY `it_id` (`it_id`,`ii_gubun`,`ii_article`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_item_ps`
--

DROP TABLE IF EXISTS `shop_item_ps`;
CREATE TABLE IF NOT EXISTS `shop_item_ps` (
  `is_id` int(11) NOT NULL AUTO_INCREMENT,
  `it_id` varchar(10) NOT NULL DEFAULT '0',
  `mb_id` varchar(20) NOT NULL DEFAULT '',
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
-- Table structure for table `shop_item_qa`
--

DROP TABLE IF EXISTS `shop_item_qa`;
CREATE TABLE IF NOT EXISTS `shop_item_qa` (
  `iq_id` int(11) NOT NULL AUTO_INCREMENT,
  `it_id` varchar(10) NOT NULL DEFAULT '',
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `iq_name` varchar(255) NOT NULL DEFAULT '',
  `iq_password` varchar(255) NOT NULL DEFAULT '',
  `iq_subject` varchar(255) NOT NULL DEFAULT '',
  `iq_question` text NOT NULL,
  `iq_answer` text NOT NULL,
  `iq_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `iq_ip` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`iq_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_item_relation`
--

DROP TABLE IF EXISTS `shop_item_relation`;
CREATE TABLE IF NOT EXISTS `shop_item_relation` (
  `it_id` varchar(10) NOT NULL DEFAULT '',
  `it_id2` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`it_id`,`it_id2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_new_win`
--

DROP TABLE IF EXISTS `shop_new_win`;
CREATE TABLE IF NOT EXISTS `shop_new_win` (
  `nw_id` int(11) NOT NULL AUTO_INCREMENT,
  `nw_begin_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nw_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nw_disable_hours` int(11) NOT NULL DEFAULT '0',
  `nw_left` int(11) NOT NULL DEFAULT '0',
  `nw_top` int(11) NOT NULL DEFAULT '0',
  `nw_height` int(11) NOT NULL DEFAULT '0',
  `nw_width` int(11) NOT NULL DEFAULT '0',
  `nw_subject` text NOT NULL,
  `nw_content` text NOT NULL,
  `nw_content_html` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nw_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_onlinecalc`
--

DROP TABLE IF EXISTS `shop_onlinecalc`;
CREATE TABLE IF NOT EXISTS `shop_onlinecalc` (
  `oc_id` int(11) NOT NULL AUTO_INCREMENT,
  `oc_subject` varchar(255) NOT NULL DEFAULT '',
  `oc_category` text NOT NULL,
  `oc_head_html` text NOT NULL,
  `oc_tail_html` text NOT NULL,
  PRIMARY KEY (`oc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_order`
--

DROP TABLE IF EXISTS `shop_order`;
CREATE TABLE IF NOT EXISTS `shop_order` (
  `od_id` bigint(20) unsigned NOT NULL,
  `uq_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `od_pwd` varchar(255) NOT NULL DEFAULT '',
  `od_name` varchar(20) NOT NULL DEFAULT '',
  `od_email` varchar(100) NOT NULL DEFAULT '',
  `od_tel` varchar(20) NOT NULL DEFAULT '',
  `od_hp` varchar(20) NOT NULL DEFAULT '',
  `od_zip1` char(3) NOT NULL DEFAULT '',
  `od_zip2` char(3) NOT NULL DEFAULT '',
  `od_addr1` varchar(100) NOT NULL DEFAULT '',
  `od_addr2` varchar(100) NOT NULL DEFAULT '',
  `od_deposit_name` varchar(20) NOT NULL DEFAULT '',
  `od_b_name` varchar(20) NOT NULL DEFAULT '',
  `od_b_tel` varchar(20) NOT NULL DEFAULT '',
  `od_b_hp` varchar(20) NOT NULL DEFAULT '',
  `od_b_zip1` char(3) NOT NULL DEFAULT '',
  `od_b_zip2` char(3) NOT NULL DEFAULT '',
  `od_b_addr1` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr2` varchar(100) NOT NULL DEFAULT '',
  `od_memo` text NOT NULL,
  `od_send_cost` int(11) NOT NULL DEFAULT '0',
  `od_temp_bank` int(11) NOT NULL DEFAULT '0',
  `od_temp_card` int(11) NOT NULL DEFAULT '0',
  `od_temp_hp` int(11) NOT NULL,
  `od_temp_point` int(11) NOT NULL DEFAULT '0',
  `od_receipt_bank` int(11) NOT NULL DEFAULT '0',
  `od_receipt_card` int(11) NOT NULL DEFAULT '0',
  `od_receipt_hp` int(11) NOT NULL,
  `od_receipt_point` int(11) NOT NULL DEFAULT '0',
  `od_bank_account` varchar(255) NOT NULL DEFAULT '',
  `od_bank_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `od_card_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `od_hp_time` datetime NOT NULL,
  `od_cancel_card` int(11) NOT NULL DEFAULT '0',
  `od_dc_amount` int(11) NOT NULL DEFAULT '0',
  `od_refund_amount` int(11) NOT NULL DEFAULT '0',
  `od_shop_memo` text NOT NULL,
  `dl_id` int(11) NOT NULL DEFAULT '0',
  `od_invoice` varchar(255) NOT NULL DEFAULT '',
  `od_invoice_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `od_hope_date` date NOT NULL DEFAULT '0000-00-00',  
  `od_settle_case` varchar(255) NOT NULL DEFAULT '',
  `od_escrow1` varchar(255) NOT NULL DEFAULT '',
  `od_escrow2` varchar(255) NOT NULL DEFAULT '',
  `od_escrow3` varchar(255) NOT NULL DEFAULT '',
  `od_cash_no` varchar(255) NOT NULL,
  `od_cash_receipt_no` varchar(255) NOT NULL,
  `od_cash_app_time` varchar(255) NOT NULL,
  `od_cash_reg_stat` varchar(255) NOT NULL,
  `od_cash_reg_desc` varchar(255) NOT NULL,
  `od_cash_tr_code` varchar(255) NOT NULL,
  `od_cash_id_info` varchar(255) NOT NULL,
  `od_cash` tinyint(4) NOT NULL,
  `od_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `od_ip` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`od_id`),
  UNIQUE KEY `uq_id` (`uq_id`),
  KEY `index2` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shop_wish`
--

DROP TABLE IF EXISTS `shop_wish`;
CREATE TABLE IF NOT EXISTS `shop_wish` (
  `wi_id` int(11) NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `it_id` varchar(10) NOT NULL DEFAULT '0',
  `wi_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wi_ip` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`wi_id`),
  KEY `index1` (`mb_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;