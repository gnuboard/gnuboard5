






















CREATE TABLE `__TABLE_NAME__` (
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
) DEFAULT CHARSET=utf8;












