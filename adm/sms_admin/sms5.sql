## 마이에스큐엘 dump 10.11
##
##
## ######################################################
## Server version	5.0.92-log







##
## Table structure for table `{$g5['sms5_book_table']}`
##

DROP TABLE IF EXISTS `{$g5['sms5_book_table']}`;


CREATE TABLE `{$g5['sms5_book_table']}` (
  `bk_no` int(11) NOT NULL auto_increment,
  `bg_no` int(11) NOT NULL default '0',
  `mb_no` int(11) NOT NULL default '0',
  `mb_id` varchar(20) NOT NULL default '',
  `bk_name` varchar(255) NOT NULL default '',
  `bk_hp` varchar(255) NOT NULL default '',
  `bk_receipt` tinyint(4) NOT NULL default '0',
  `bk_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `bk_memo` text NOT NULL,
  PRIMARY KEY  (`bk_no`),
  KEY `bk_name` (`bk_name`),
  KEY `bk_hp` (`bk_hp`),
  KEY `mb_no` (`mb_no`),
  KEY `bg_no` (`bg_no`,`bk_no`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_book_table']}_group`
##

DROP TABLE IF EXISTS `{$g5['sms5_book_table']}_group`;


CREATE TABLE `{$g5['sms5_book_table']}_group` (
  `bg_no` int(11) NOT NULL auto_increment,
  `bg_name` varchar(255) NOT NULL default '',
  `bg_count` int(11) NOT NULL default '0',
  `bg_member` int(11) NOT NULL default '0',
  `bg_nomember` int(11) NOT NULL default '0',
  `bg_receipt` int(11) NOT NULL default '0',
  `bg_reject` int(11) NOT NULL default '0',
  PRIMARY KEY  (`bg_no`),
  KEY `bg_name` (`bg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_config_table']}`
##

DROP TABLE IF EXISTS `{$g5['sms5_config_table']}`;


CREATE TABLE `{$g5['sms5_config_table']}` (
  `cf_phone` varchar(255) NOT NULL default '',
  `cf_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `cf_member` tinyint(4) NOT NULL default '1',
  `cf_level` tinyint(4) NOT NULL default '2',
  `cf_point` int(11) NOT NULL default '0',
  `cf_day_count` int(11) NOT NULL default '0',
  `cf_skin` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_form_table']}`
##

DROP TABLE IF EXISTS `{$g5['sms5_form_table']}`;


CREATE TABLE `{$g5['sms5_form_table']}` (
  `fo_no` int(11) NOT NULL auto_increment,
  `fg_no` tinyint(4) NOT NULL default '0',
  `fg_member` char(1) NOT NULL default '0',
  `fo_name` varchar(255) NOT NULL default '',
  `fo_content` text NOT NULL,
  `fo_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`fo_no`),
  KEY `fg_no` (`fg_no`,`fo_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_form_table']}_group`
##

DROP TABLE IF EXISTS `{$g5['sms5_form_table']}_group`;


CREATE TABLE `{$g5['sms5_form_table']}_group` (
  `fg_no` int(11) NOT NULL auto_increment,
  `fg_name` varchar(255) NOT NULL default '',
  `fg_count` int(11) NOT NULL default '0',
  `fg_member` tinyint(4) NOT NULL,
  PRIMARY KEY  (`fg_no`),
  KEY `fg_name` (`fg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_history_table']}`
##

DROP TABLE IF EXISTS `{$g5['sms5_history_table']}`;


CREATE TABLE `{$g5['sms5_history_table']}` (
  `hs_no` int(11) NOT NULL auto_increment,
  `wr_no` int(11) NOT NULL default '0',
  `wr_renum` int(11) NOT NULL default '0',
  `bg_no` int(11) NOT NULL default '0',
  `mb_no` int(11) NOT NULL default '0',
  `mb_id` varchar(20) NOT NULL default '',
  `bk_no` int(11) NOT NULL default '0',
  `hs_name` varchar(30) NOT NULL default '',
  `hs_hp` varchar(255) NOT NULL default '',
  `hs_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `hs_flag` tinyint(4) NOT NULL default '0',
  `hs_code` varchar(255) NOT NULL default '',
  `hs_memo` varchar(255) NOT NULL default '',
  `hs_log` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`hs_no`),
  KEY `wr_no` (`wr_no`),
  KEY `mb_no` (`mb_no`),
  KEY `bk_no` (`bk_no`),
  KEY `hs_hp` (`hs_hp`),
  KEY `hs_code` (`hs_code`),
  KEY `bg_no` (`bg_no`),
  KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_write_table']}`
##

DROP TABLE IF EXISTS `{$g5['sms5_write_table']}`;


CREATE TABLE `{$g5['sms5_write_table']}` (
  `wr_no` int(11) NOT NULL default '1',
  `wr_renum` int(11) NOT NULL default '0',
  `wr_reply` varchar(255) NOT NULL default '',
  `wr_message` varchar(255) NOT NULL default '',
  `wr_booking` datetime NOT NULL default '0000-00-00 00:00:00',
  `wr_total` int(11) NOT NULL default '0',
  `wr_re_total` int(11) NOT NULL default '0',
  `wr_success` int(11) NOT NULL default '0',
  `wr_failure` int(11) NOT NULL default '0',
  `wr_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `wr_memo` text NOT NULL,
  KEY `wr_no` (`wr_no`,`wr_renum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##
## Table structure for table `{$g5['sms5_member_history_table']}`
##

DROP TABLE IF EXISTS `{$g5['sms5_member_history_table']}`;


CREATE TABLE `{$g5['sms5_member_history_table']}` (
  `mh_no` int(11) NOT NULL auto_increment,
  `mb_id` varchar(30) NOT NULL,
  `mh_reply` varchar(30) NOT NULL,
  `mh_hp` varchar(30) NOT NULL,
  `mh_datetime` datetime NOT NULL,
  `mh_booking` datetime NOT NULL,
  `mh_log` varchar(255) NOT NULL,
  `mh_ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`mh_no`),
  KEY `mb_id` (`mb_id`,`mh_datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

