-- --------------------------------------------------------

--
-- Table structure for table `g4s_auth`
--

DROP TABLE IF EXISTS `g4s_auth`;
CREATE TABLE IF NOT EXISTS `g4s_auth` (
  `mb_id` varchar(255) NOT NULL default '',
  `au_menu` varchar(20) NOT NULL default '',
  `au_auth` set('r','w','d') NOT NULL default '',
  PRIMARY KEY  (`mb_id`,`au_menu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_board`
--

DROP TABLE IF EXISTS `g4s_board`;
CREATE TABLE IF NOT EXISTS `g4s_board` (
  `bo_table` varchar(20) NOT NULL default '',
  `gr_id` varchar(255) NOT NULL default '',
  `bo_subject` varchar(255) NOT NULL default '',
  `bo_device` enum('both','pc','mobile') NOT NULL DEFAULT 'both',
  `bo_admin` varchar(255) NOT NULL default '',
  `bo_list_level` tinyint(4) NOT NULL default '0',
  `bo_read_level` tinyint(4) NOT NULL default '0',
  `bo_write_level` tinyint(4) NOT NULL default '0',
  `bo_reply_level` tinyint(4) NOT NULL default '0',
  `bo_comment_level` tinyint(4) NOT NULL default '0',
  `bo_upload_level` tinyint(4) NOT NULL default '0',
  `bo_download_level` tinyint(4) NOT NULL default '0',
  `bo_html_level` tinyint(4) NOT NULL default '0',
  `bo_link_level` tinyint(4) NOT NULL default '0',
  `bo_trackback_level` tinyint(4) NOT NULL default '0',
  `bo_count_delete` tinyint(4) NOT NULL default '0',
  `bo_count_modify` tinyint(4) NOT NULL default '0',
  `bo_read_point` int(11) NOT NULL default '0',
  `bo_write_point` int(11) NOT NULL default '0',
  `bo_comment_point` int(11) NOT NULL default '0',
  `bo_download_point` int(11) NOT NULL default '0',
  `bo_use_category` tinyint(4) NOT NULL default '0',
  `bo_category_list` text NOT NULL,
  `bo_disable_tags` text NOT NULL,
  `bo_use_sideview` tinyint(4) NOT NULL default '0',
  `bo_use_file_content` tinyint(4) NOT NULL default '0',
  `bo_use_secret` tinyint(4) NOT NULL default '0',
  `bo_use_dhtml_editor` tinyint(4) NOT NULL default '0',
  `bo_use_rss_view` tinyint(4) NOT NULL default '0',
  `bo_use_comment` tinyint(4) NOT NULL default '0',
  `bo_use_good` tinyint(4) NOT NULL default '0',
  `bo_use_nogood` tinyint(4) NOT NULL default '0',
  `bo_use_name` tinyint(4) NOT NULL default '0',
  `bo_use_signature` tinyint(4) NOT NULL default '0',
  `bo_use_ip_view` tinyint(4) NOT NULL default '0',
  `bo_use_trackback` tinyint(4) NOT NULL default '0',
  `bo_use_list_view` tinyint(4) NOT NULL default '0',
  `bo_use_list_content` tinyint(4) NOT NULL default '0',
  `bo_table_width` int(11) NOT NULL default '0',
  `bo_subject_len` int(11) NOT NULL default '0',
  `bo_page_rows` int(11) NOT NULL default '0',
  `bo_new` int(11) NOT NULL default '0',
  `bo_hot` int(11) NOT NULL default '0',
  `bo_image_width` int(11) NOT NULL default '0',
  `bo_skin` varchar(255) NOT NULL default '',
  `bo_image_head` varchar(255) NOT NULL default '',
  `bo_image_tail` varchar(255) NOT NULL default '',
  `bo_include_head` varchar(255) NOT NULL default '',
  `bo_include_tail` varchar(255) NOT NULL default '',
  `bo_content_head` text NOT NULL,
  `bo_content_tail` text NOT NULL,
  `bo_insert_content` text NOT NULL,
  `bo_gallery_cols` int(11) NOT NULL default '0',
  `bo_upload_size` int(11) NOT NULL default '0',
  `bo_reply_order` tinyint(4) NOT NULL default '0',
  `bo_use_search` tinyint(4) NOT NULL default '0',
  `bo_order_search` int(11) NOT NULL default '0',
  `bo_count_write` int(11) NOT NULL default '0',
  `bo_count_comment` int(11) NOT NULL default '0',
  `bo_write_min` int(11) NOT NULL default '0',
  `bo_write_max` int(11) NOT NULL default '0',
  `bo_comment_min` int(11) NOT NULL default '0',
  `bo_comment_max` int(11) NOT NULL default '0',
  `bo_notice` text NOT NULL,
  `bo_upload_count` tinyint(4) NOT NULL default '0',
  `bo_use_email` tinyint(4) NOT NULL default '0',
  `bo_sort_field` varchar(255) NOT NULL default '',
  `bo_1_subj` varchar(255) NOT NULL default '',
  `bo_2_subj` varchar(255) NOT NULL default '',
  `bo_3_subj` varchar(255) NOT NULL default '',
  `bo_4_subj` varchar(255) NOT NULL default '',
  `bo_5_subj` varchar(255) NOT NULL default '',
  `bo_6_subj` varchar(255) NOT NULL default '',
  `bo_7_subj` varchar(255) NOT NULL default '',
  `bo_8_subj` varchar(255) NOT NULL default '',
  `bo_9_subj` varchar(255) NOT NULL default '',
  `bo_10_subj` varchar(255) NOT NULL default '',
  `bo_1` varchar(255) NOT NULL default '',
  `bo_2` varchar(255) NOT NULL default '',
  `bo_3` varchar(255) NOT NULL default '',
  `bo_4` varchar(255) NOT NULL default '',
  `bo_5` varchar(255) NOT NULL default '',
  `bo_6` varchar(255) NOT NULL default '',
  `bo_7` varchar(255) NOT NULL default '',
  `bo_8` varchar(255) NOT NULL default '',
  `bo_9` varchar(255) NOT NULL default '',
  `bo_10` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bo_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_board_file`
--

DROP TABLE IF EXISTS `g4s_board_file`;
CREATE TABLE IF NOT EXISTS `g4s_board_file` (
  `bo_table` varchar(20) NOT NULL default '',
  `wr_id` int(11) NOT NULL default '0',
  `bf_no` int(11) NOT NULL default '0',
  `bf_source` varchar(255) NOT NULL default '',
  `bf_file` varchar(255) NOT NULL default '',
  `bf_download` int(11) NOT NULL,
  `bf_content` text NOT NULL,
  `bf_filesize` int(11) NOT NULL default '0',
  `bf_width` int(11) NOT NULL default '0',
  `bf_height` smallint(6) NOT NULL default '0',
  `bf_type` tinyint(4) NOT NULL default '0',
  `bf_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`bo_table`,`wr_id`,`bf_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_board_good`
--

DROP TABLE IF EXISTS `g4s_board_good`;
CREATE TABLE IF NOT EXISTS `g4s_board_good` (
  `bg_id` int(11) NOT NULL auto_increment,
  `bo_table` varchar(20) NOT NULL default '',
  `wr_id` int(11) NOT NULL default '0',
  `mb_id` varchar(20) NOT NULL default '',
  `bg_flag` varchar(255) NOT NULL default '',
  `bg_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`bg_id`),
  UNIQUE KEY `fkey1` (`bo_table`,`wr_id`,`mb_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_board_new`
--

DROP TABLE IF EXISTS `g4s_board_new`;
CREATE TABLE IF NOT EXISTS `g4s_board_new` (
  `bn_id` int(11) NOT NULL auto_increment,
  `bo_table` varchar(20) NOT NULL default '',
  `wr_id` int(11) NOT NULL default '0',
  `wr_parent` int(11) NOT NULL default '0',
  `bn_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `mb_id` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`bn_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_config`
--

DROP TABLE IF EXISTS `g4s_config`;
CREATE TABLE IF NOT EXISTS `g4s_config` (
  `cf_title` varchar(255) NOT NULL default '',
  `cf_admin` varchar(255) NOT NULL default '',
  `cf_use_point` tinyint(4) NOT NULL default '0',
  `cf_use_norobot` tinyint(4) NOT NULL default '0',
  `cf_use_copy_log` tinyint(4) NOT NULL default '0',
  `cf_use_email_certify` tinyint(4) NOT NULL default '0',
  `cf_login_point` int(11) NOT NULL default '0',
  `cf_cut_name` tinyint(4) NOT NULL default '0',
  `cf_nick_modify` int(11) NOT NULL default '0',
  `cf_new_skin` varchar(255) NOT NULL default '',
  `cf_login_skin` varchar(255) NOT NULL default '',
  `cf_new_rows` int(11) NOT NULL default '0',
  `cf_search_skin` varchar(255) NOT NULL default '',
  `cf_connect_skin` varchar(255) NOT NULL default '',
  `cf_read_point` int(11) NOT NULL default '0',
  `cf_write_point` int(11) NOT NULL default '0',
  `cf_comment_point` int(11) NOT NULL default '0',
  `cf_download_point` int(11) NOT NULL default '0',
  `cf_search_bgcolor` varchar(255) NOT NULL default '',
  `cf_search_color` varchar(255) NOT NULL default '',
  `cf_write_pages` int(11) NOT NULL default '0',
  `cf_link_target` varchar(255) NOT NULL default '',
  `cf_delay_sec` int(11) NOT NULL default '0',
  `cf_filter` text NOT NULL,
  `cf_possible_ip` text NOT NULL,
  `cf_intercept_ip` text NOT NULL,
  `cf_register_skin` varchar(255) NOT NULL default 'basic',
  `cf_member_skin` varchar(255) NOT NULL default '',
  `cf_use_homepage` tinyint(4) NOT NULL default '0',
  `cf_req_homepage` tinyint(4) NOT NULL default '0',
  `cf_use_tel` tinyint(4) NOT NULL default '0',
  `cf_req_tel` tinyint(4) NOT NULL default '0',
  `cf_use_hp` tinyint(4) NOT NULL default '0',
  `cf_req_hp` tinyint(4) NOT NULL default '0',
  `cf_use_addr` tinyint(4) NOT NULL default '0',
  `cf_req_addr` tinyint(4) NOT NULL default '0',
  `cf_use_signature` tinyint(4) NOT NULL default '0',
  `cf_req_signature` tinyint(4) NOT NULL default '0',
  `cf_use_profile` tinyint(4) NOT NULL default '0',
  `cf_req_profile` tinyint(4) NOT NULL default '0',
  `cf_register_level` tinyint(4) NOT NULL default '0',
  `cf_register_point` int(11) NOT NULL default '0',
  `cf_icon_level` tinyint(4) NOT NULL default '0',
  `cf_use_recommend` tinyint(4) NOT NULL default '0',
  `cf_recommend_point` int(11) NOT NULL default '0',
  `cf_leave_day` int(11) NOT NULL default '0',
  `cf_search_part` int(11) NOT NULL default '0',
  `cf_email_use` tinyint(4) NOT NULL default '0',
  `cf_email_wr_super_admin` tinyint(4) NOT NULL default '0',
  `cf_email_wr_group_admin` tinyint(4) NOT NULL default '0',
  `cf_email_wr_board_admin` tinyint(4) NOT NULL default '0',
  `cf_email_wr_write` tinyint(4) NOT NULL default '0',
  `cf_email_wr_comment_all` tinyint(4) NOT NULL default '0',
  `cf_email_mb_super_admin` tinyint(4) NOT NULL default '0',
  `cf_email_mb_member` tinyint(4) NOT NULL default '0',
  `cf_email_po_super_admin` tinyint(4) NOT NULL default '0',
  `cf_prohibit_id` text NOT NULL,
  `cf_prohibit_email` text NOT NULL,
  `cf_new_del` int(11) NOT NULL default '0',
  `cf_memo_del` int(11) NOT NULL default '0',
  `cf_visit_del` int(11) NOT NULL default '0',
  `cf_popular_del` int(11) NOT NULL default '0',
  `cf_use_jumin` tinyint(4) NOT NULL default '0',
  `cf_use_member_icon` tinyint(4) NOT NULL default '0',
  `cf_member_icon_size` int(11) NOT NULL default '0',
  `cf_member_icon_width` int(11) NOT NULL default '0',
  `cf_member_icon_height` int(11) NOT NULL default '0',
  `cf_login_minutes` int(11) NOT NULL default '0',
  `cf_image_extension` varchar(255) NOT NULL default '',
  `cf_flash_extension` varchar(255) NOT NULL default '',
  `cf_movie_extension` varchar(255) NOT NULL default '',
  `cf_formmail_is_member` tinyint(4) NOT NULL default '0',
  `cf_page_rows` int(11) NOT NULL default '0',
  `cf_visit` varchar(255) NOT NULL default '',
  `cf_max_po_id` int(11) NOT NULL default '0',
  `cf_stipulation` text NOT NULL,
  `cf_privacy` text NOT NULL,
  `cf_open_modify` int(11) NOT NULL default '0',
  `cf_memo_send_point` int(11) NOT NULL default '0',
  `cf_1_subj` varchar(255) NOT NULL default '',
  `cf_2_subj` varchar(255) NOT NULL default '',
  `cf_3_subj` varchar(255) NOT NULL default '',
  `cf_4_subj` varchar(255) NOT NULL default '',
  `cf_5_subj` varchar(255) NOT NULL default '',
  `cf_6_subj` varchar(255) NOT NULL default '',
  `cf_7_subj` varchar(255) NOT NULL default '',
  `cf_8_subj` varchar(255) NOT NULL default '',
  `cf_9_subj` varchar(255) NOT NULL default '',
  `cf_10_subj` varchar(255) NOT NULL default '',
  `cf_1` varchar(255) NOT NULL default '',
  `cf_2` varchar(255) NOT NULL default '',
  `cf_3` varchar(255) NOT NULL default '',
  `cf_4` varchar(255) NOT NULL default '',
  `cf_5` varchar(255) NOT NULL default '',
  `cf_6` varchar(255) NOT NULL default '',
  `cf_7` varchar(255) NOT NULL default '',
  `cf_8` varchar(255) NOT NULL default '',
  `cf_9` varchar(255) NOT NULL default '',
  `cf_10` varchar(255) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_group`
--

DROP TABLE IF EXISTS `g4s_group`;
CREATE TABLE IF NOT EXISTS `g4s_group` (
  `gr_id` varchar(10) NOT NULL default '',
  `gr_subject` varchar(255) NOT NULL default '',
  `gr_admin` varchar(255) NOT NULL default '',
  `gr_use_access` tinyint(4) NOT NULL default '0',
  `gr_1_subj` varchar(255) NOT NULL default '',
  `gr_2_subj` varchar(255) NOT NULL default '',
  `gr_3_subj` varchar(255) NOT NULL default '',
  `gr_4_subj` varchar(255) NOT NULL default '',
  `gr_5_subj` varchar(255) NOT NULL default '',
  `gr_6_subj` varchar(255) NOT NULL default '',
  `gr_7_subj` varchar(255) NOT NULL default '',
  `gr_8_subj` varchar(255) NOT NULL default '',
  `gr_9_subj` varchar(255) NOT NULL default '',
  `gr_10_subj` varchar(255) NOT NULL default '',
  `gr_1` varchar(255) NOT NULL default '',
  `gr_2` varchar(255) NOT NULL default '',
  `gr_3` varchar(255) NOT NULL default '',
  `gr_4` varchar(255) NOT NULL default '',
  `gr_5` varchar(255) NOT NULL default '',
  `gr_6` varchar(255) NOT NULL default '',
  `gr_7` varchar(255) NOT NULL default '',
  `gr_8` varchar(255) NOT NULL default '',
  `gr_9` varchar(255) NOT NULL default '',
  `gr_10` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`gr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_group_member`
--

DROP TABLE IF EXISTS `g4s_group_member`;
CREATE TABLE IF NOT EXISTS `g4s_group_member` (
  `gm_id` int(11) NOT NULL auto_increment,
  `gr_id` varchar(255) NOT NULL default '',
  `mb_id` varchar(255) NOT NULL default '',
  `gm_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`gm_id`),
  KEY `gr_id` (`gr_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_login`
--

DROP TABLE IF EXISTS `g4s_login`;
CREATE TABLE IF NOT EXISTS `g4s_login` (
  `lo_ip` varchar(255) NOT NULL default '',
  `mb_id` varchar(255) NOT NULL default '',
  `lo_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `lo_location` text NOT NULL,
  `lo_url` text NOT NULL,
  PRIMARY KEY  (`lo_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_mail`
--

DROP TABLE IF EXISTS `g4s_mail`;
CREATE TABLE IF NOT EXISTS `g4s_mail` (
  `ma_id` int(11) NOT NULL auto_increment,
  `ma_subject` varchar(255) NOT NULL default '',
  `ma_content` mediumtext NOT NULL,
  `ma_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ma_ip` varchar(255) NOT NULL default '',
  `ma_last_option` text NOT NULL,
  PRIMARY KEY  (`ma_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_member`
--

DROP TABLE IF EXISTS `g4s_member`;
CREATE TABLE IF NOT EXISTS `g4s_member` (
  `mb_no` int(11) NOT NULL auto_increment,
  `mb_id` varchar(255) NOT NULL default '',
  `mb_password` varchar(255) NOT NULL default '',
  `mb_name` varchar(255) NOT NULL default '',
  `mb_nick` varchar(255) NOT NULL default '',
  `mb_nick_date` date NOT NULL default '0000-00-00',
  `mb_email` varchar(255) NOT NULL default '',
  `mb_homepage` varchar(255) NOT NULL default '',
  `mb_password_q` varchar(255) NOT NULL default '',
  `mb_password_a` varchar(255) NOT NULL default '',
  `mb_level` tinyint(4) NOT NULL default '0',
  `mb_jumin` varchar(255) NOT NULL default '',
  `mb_sex` char(1) NOT NULL default '',
  `mb_birth` varchar(255) NOT NULL default '',
  `mb_tel` varchar(255) NOT NULL default '',
  `mb_hp` varchar(255) NOT NULL default '',
  `mb_zip1` char(3) NOT NULL default '',
  `mb_zip2` char(3) NOT NULL default '',
  `mb_addr1` varchar(255) NOT NULL default '',
  `mb_addr2` varchar(255) NOT NULL default '',
  `mb_signature` text NOT NULL,
  `mb_recommend` varchar(255) NOT NULL default '',
  `mb_point` int(11) NOT NULL default '0',
  `mb_today_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `mb_login_ip` varchar(255) NOT NULL default '',
  `mb_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `mb_ip` varchar(255) NOT NULL default '',
  `mb_leave_date` varchar(8) NOT NULL default '',
  `mb_intercept_date` varchar(8) NOT NULL default '',
  `mb_email_certify` datetime NOT NULL default '0000-00-00 00:00:00',
  `mb_memo` text NOT NULL,
  `mb_lost_certify` varchar(255) NOT NULL,
  `mb_mailling` tinyint(4) NOT NULL default '0',
  `mb_sms` tinyint(4) NOT NULL default '0',
  `mb_open` tinyint(4) NOT NULL default '0',
  `mb_open_date` date NOT NULL default '0000-00-00',
  `mb_profile` text NOT NULL,
  `mb_memo_call` varchar(255) NOT NULL default '',
  `mb_1` varchar(255) NOT NULL default '',
  `mb_2` varchar(255) NOT NULL default '',
  `mb_3` varchar(255) NOT NULL default '',
  `mb_4` varchar(255) NOT NULL default '',
  `mb_5` varchar(255) NOT NULL default '',
  `mb_6` varchar(255) NOT NULL default '',
  `mb_7` varchar(255) NOT NULL default '',
  `mb_8` varchar(255) NOT NULL default '',
  `mb_9` varchar(255) NOT NULL default '',
  `mb_10` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`mb_no`),
  UNIQUE KEY `mb_id` (`mb_id`),
  KEY `mb_today_login` (`mb_today_login`),
  KEY `mb_datetime` (`mb_datetime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_memo`
--

DROP TABLE IF EXISTS `g4s_memo`;
CREATE TABLE IF NOT EXISTS `g4s_memo` (
  `me_id` int(11) NOT NULL default '0',
  `me_recv_mb_id` varchar(255) NOT NULL default '',
  `me_send_mb_id` varchar(255) NOT NULL default '',
  `me_send_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `me_read_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `me_memo` text NOT NULL,
  PRIMARY KEY  (`me_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_oneboard`
--

DROP TABLE IF EXISTS `g4s_oneboard`;
CREATE TABLE IF NOT EXISTS `g4s_oneboard` (
  `ob_table` varchar(20) NOT NULL,
  `ob_subject` varchar(255) NOT NULL,
  `ob_admin` varchar(255) NOT NULL,
  `ob_skin` varchar(255) NOT NULL,
  `ob_write_level` tinyint(4) NOT NULL,
  `ob_upload_level` tinyint(4) NOT NULL,
  `ob_use_dhtml_editor` tinyint(4) NOT NULL,
  `ob_use_email` tinyint(4) NOT NULL,
  `ob_table_width` smallint(6) NOT NULL,
  `ob_subject_len` smallint(6) NOT NULL,
  `ob_page_rows` smallint(6) NOT NULL,
  `ob_image_width` smallint(6) NOT NULL,
  `ob_image_head` varchar(255) NOT NULL,
  `ob_image_tail` varchar(255) NOT NULL,
  `ob_include_head` varchar(255) NOT NULL,
  `ob_include_tail` varchar(255) NOT NULL,
  `ob_content_head` text NOT NULL,
  `ob_content_tail` text NOT NULL,
  `ob_insert_content` text NOT NULL,
  `ob_1_subj` varchar(255) NOT NULL,
  `ob_2_subj` varchar(255) NOT NULL,
  `ob_3_subj` varchar(255) NOT NULL,
  `ob_4_subj` varchar(255) NOT NULL,
  `ob_5_subj` varchar(255) NOT NULL,
  `ob_6_subj` varchar(255) NOT NULL,
  `ob_7_subj` varchar(255) NOT NULL,
  `ob_8_subj` varchar(255) NOT NULL,
  `ob_9_subj` varchar(255) NOT NULL,
  `ob_10_subj` varchar(255) NOT NULL,
  `ob_1` varchar(255) NOT NULL,
  `ob_2` varchar(255) NOT NULL,
  `ob_3` varchar(255) NOT NULL,
  `ob_4` varchar(255) NOT NULL,
  `ob_5` varchar(255) NOT NULL,
  `ob_6` varchar(255) NOT NULL,
  `ob_7` varchar(255) NOT NULL,
  `ob_8` varchar(255) NOT NULL,
  `ob_9` varchar(255) NOT NULL,
  `ob_10` varchar(255) NOT NULL,
  PRIMARY KEY  (`ob_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_one_basic`
--

DROP TABLE IF EXISTS `g4s_one_basic`;
CREATE TABLE IF NOT EXISTS `g4s_one_basic` (
  `on_id` int(11) NOT NULL auto_increment,
  `mb_no` int(11) NOT NULL,
  `on_subject` varchar(255) NOT NULL default '',
  `on_question` mediumtext NOT NULL,
  `on_answer` mediumtext NOT NULL,
  `on_qfile` varchar(255) NOT NULL,
  `on_qsource` varchar(255) NOT NULL,
  `on_afile` varchar(255) NOT NULL,
  `on_asource` varchar(255) NOT NULL,
  `on_qdatetime` datetime NOT NULL,
  `on_adatetime` datetime NOT NULL,
  `on_1` text NOT NULL,
  `on_2` text NOT NULL,
  `on_3` text NOT NULL,
  `on_4` text NOT NULL,
  `on_5` text NOT NULL,
  `on_6` text NOT NULL,
  `on_7` text NOT NULL,
  `on_8` text NOT NULL,
  `on_9` text NOT NULL,
  `on_10` text NOT NULL,
  PRIMARY KEY  (`on_id`),
  KEY `mb_no` (`mb_no`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_point`
--

DROP TABLE IF EXISTS `g4s_point`;
CREATE TABLE IF NOT EXISTS `g4s_point` (
  `po_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(20) NOT NULL default '',
  `po_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `po_content` varchar(255) NOT NULL default '',
  `po_point` int(11) NOT NULL default '0',
  `po_rel_table` varchar(20) NOT NULL default '',
  `po_rel_id` varchar(20) NOT NULL default '',
  `po_rel_action` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`po_id`),
  KEY `index1` (`mb_id`,`po_rel_table`,`po_rel_id`,`po_rel_action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_poll`
--

DROP TABLE IF EXISTS `g4s_poll`;
CREATE TABLE IF NOT EXISTS `g4s_poll` (
  `po_id` int(11) NOT NULL auto_increment,
  `po_subject` varchar(255) NOT NULL default '',
  `po_poll1` varchar(255) NOT NULL default '',
  `po_poll2` varchar(255) NOT NULL default '',
  `po_poll3` varchar(255) NOT NULL default '',
  `po_poll4` varchar(255) NOT NULL default '',
  `po_poll5` varchar(255) NOT NULL default '',
  `po_poll6` varchar(255) NOT NULL default '',
  `po_poll7` varchar(255) NOT NULL default '',
  `po_poll8` varchar(255) NOT NULL default '',
  `po_poll9` varchar(255) NOT NULL default '',
  `po_cnt1` int(11) NOT NULL default '0',
  `po_cnt2` int(11) NOT NULL default '0',
  `po_cnt3` int(11) NOT NULL default '0',
  `po_cnt4` int(11) NOT NULL default '0',
  `po_cnt5` int(11) NOT NULL default '0',
  `po_cnt6` int(11) NOT NULL default '0',
  `po_cnt7` int(11) NOT NULL default '0',
  `po_cnt8` int(11) NOT NULL default '0',
  `po_cnt9` int(11) NOT NULL default '0',
  `po_etc` varchar(255) NOT NULL default '',
  `po_level` tinyint(4) NOT NULL default '0',
  `po_point` int(11) NOT NULL default '0',
  `po_date` date NOT NULL default '0000-00-00',
  `po_ips` mediumtext NOT NULL,
  `mb_ids` text NOT NULL,
  PRIMARY KEY  (`po_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_poll_etc`
--

DROP TABLE IF EXISTS `g4s_poll_etc`;
CREATE TABLE IF NOT EXISTS `g4s_poll_etc` (
  `pc_id` int(11) NOT NULL default '0',
  `po_id` int(11) NOT NULL default '0',
  `mb_id` varchar(255) NOT NULL default '',
  `pc_name` varchar(255) NOT NULL default '',
  `pc_idea` varchar(255) NOT NULL default '',
  `pc_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`pc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_popular`
--

DROP TABLE IF EXISTS `g4s_popular`;
CREATE TABLE IF NOT EXISTS `g4s_popular` (
  `pp_id` int(11) NOT NULL auto_increment,
  `pp_word` varchar(50) NOT NULL default '',
  `pp_date` date NOT NULL default '0000-00-00',
  `pp_ip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`pp_id`),
  UNIQUE KEY `index1` (`pp_date`,`pp_word`,`pp_ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_scrap`
--

DROP TABLE IF EXISTS `g4s_scrap`;
CREATE TABLE IF NOT EXISTS `g4s_scrap` (
  `ms_id` int(11) NOT NULL auto_increment,
  `mb_id` varchar(255) NOT NULL default '',
  `bo_table` varchar(20) NOT NULL default '',
  `wr_id` varchar(15) NOT NULL default '',
  `ms_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ms_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_visit`
--

DROP TABLE IF EXISTS `g4s_visit`;
CREATE TABLE IF NOT EXISTS `g4s_visit` (
  `vi_id` int(11) NOT NULL default '0',
  `vi_ip` varchar(255) NOT NULL default '',
  `vi_date` date NOT NULL default '0000-00-00',
  `vi_time` time NOT NULL default '00:00:00',
  `vi_referer` text NOT NULL,
  `vi_agent` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`vi_id`),
  UNIQUE KEY `index1` (`vi_ip`,`vi_date`),
  KEY `index2` (`vi_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_visit_sum`
--

DROP TABLE IF EXISTS `g4s_visit_sum`;
CREATE TABLE IF NOT EXISTS `g4s_visit_sum` (
  `vs_date` date NOT NULL default '0000-00-00',
  `vs_count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vs_date`),
  KEY `index1` (`vs_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_write`
--

DROP TABLE IF EXISTS `g4s_write`;
CREATE TABLE IF NOT EXISTS `g4s_write` (
  `wr_id` int(11) NOT NULL auto_increment,
  `wr_num` int(11) NOT NULL default '0',
  `wr_reply` varchar(10) NOT NULL default '',
  `wr_parent` int(11) NOT NULL default '0',
  `wr_is_comment` tinyint(4) NOT NULL default '0',
  `wr_comment` int(11) NOT NULL default '0',
  `wr_comment_reply` varchar(5) NOT NULL default '',
  `ca_name` varchar(255) NOT NULL default '',
  `wr_option` set('html1','html2','secret','mail') NOT NULL default '',
  `wr_subject` varchar(255) NOT NULL default '',
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL default '0',
  `wr_link2_hit` int(11) NOT NULL default '0',
  `wr_trackback` varchar(255) NOT NULL default '',
  `wr_hit` int(11) NOT NULL default '0',
  `wr_good` int(11) NOT NULL default '0',
  `wr_nogood` int(11) NOT NULL default '0',
  `mb_id` varchar(255) NOT NULL default '',
  `wr_password` varchar(255) NOT NULL default '',
  `wr_name` varchar(255) NOT NULL default '',
  `wr_email` varchar(255) NOT NULL default '',
  `wr_homepage` varchar(255) NOT NULL default '',
  `wr_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `wr_last` varchar(19) NOT NULL default '',
  `wr_ip` varchar(255) NOT NULL default '',
  `wr_1` varchar(255) NOT NULL default '',
  `wr_2` varchar(255) NOT NULL default '',
  `wr_3` varchar(255) NOT NULL default '',
  `wr_4` varchar(255) NOT NULL default '',
  `wr_5` varchar(255) NOT NULL default '',
  `wr_6` varchar(255) NOT NULL default '',
  `wr_7` varchar(255) NOT NULL default '',
  `wr_8` varchar(255) NOT NULL default '',
  `wr_9` varchar(255) NOT NULL default '',
  `wr_10` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`wr_id`),
  KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
  KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g4s_write`
--

DROP TABLE IF EXISTS `g4s_unique`;
CREATE TABLE IF NOT EXISTS `g4s_uniqid` (
  `uq_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`uq_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------