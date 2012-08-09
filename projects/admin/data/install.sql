-- phpMyAdmin SQL Dump
-- version 3.4.3
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2011 年 12 月 02 日 17:33
-- 服务器版本: 5.5.11
-- PHP 版本: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- 表的结构 `admin_config`
--

DROP TABLE IF EXISTS `{{table_prefix}}admin_config`;
CREATE TABLE `{{table_prefix}}admin_config` (
  `type` varchar(32) NOT NULL COMMENT '分类类型',
  `key_md5` varchar(32) NOT NULL COMMENT 'key md5',
  `key_name` varchar(128) NOT NULL COMMENT '关键字',
  `value` blob NOT NULL COMMENT '值',
  PRIMARY KEY (`type`,`key_md5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置表';

-- --------------------------------------------------------

--
-- 表的结构 `admin_log`
--

DROP TABLE IF EXISTS `{{table_prefix}}admin_log`;
CREATE TABLE `{{table_prefix}}admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) DEFAULT NULL,
  `uri` varchar(1000) DEFAULT NULL,
  `referer` varchar(1000) DEFAULT NULL,
  `post` text,
  `admin_id` int(10) unsigned NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `adminId_type` (`admin_id`,`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='管理员操作日志表' AUTO_INCREMENT=1 ;


--
-- 表的结构 `admin_login_error_log`
--

DROP TABLE IF EXISTS `{{table_prefix}}admin_login_error_log`;
CREATE TABLE `{{table_prefix}}admin_login_error_log` (
  `ip` varchar(100) NOT NULL COMMENT 'IP',
  `timeline` int(10) NOT NULL COMMENT '时间',
  `error_num` mediumint(6) NOT NULL COMMENT '错误数',
  `last_error_msg` varchar(255) NOT NULL COMMENT '最后错误信息',
  `last_post_username` varchar(255) NOT NULL COMMENT '最后提交的用户名',
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='管理员登录错误信息日志';

-- --------------------------------------------------------

--
-- 表的结构 `admin_member`
--

DROP TABLE IF EXISTS `{{table_prefix}}admin_member`;
CREATE TABLE `{{table_prefix}}admin_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL DEFAULT ' ',
  `rand_code` varchar(16) NOT NULL COMMENT '密码随机码',
  `project` varchar(100) NOT NULL,
  `is_super_admin` tinyint(1) unsigned NOT NULL COMMENT '是否超管',
  `shielded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `perm_setting` text NOT NULL,
  `login_num` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_login_ip` varchar(100) NOT NULL COMMENT '最后登录IP',
  `last_login_session_id` varchar(64) NOT NULL COMMENT '最后登录SESSION_ID',
  `notepad` text NOT NULL COMMENT '首页便签',
  `setting` text NOT NULL COMMENT '设置',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='管理员表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `admin_member`
--

INSERT INTO `{{table_prefix}}admin_member` (`id`, `username`, `nickname`, `password`, `rand_code`, `project`, `is_super_admin`, `shielded`, `perm_setting`, `login_num`, `last_login_time`, `last_login_ip`, `last_login_session_id`, `notepad`, `setting`) VALUES
(1, 'admin', '超管', '80af4947e1bb88ca8e31448aec657445', 'J@Y(i$h\\]F]?Kf?C', 'admin', 1, 0, 'N;', 0, 0, '127.0.0.1', '', '', 'a:1:{s:15:"only_self_login";s:1:"0";}');

-- --------------------------------------------------------

--
-- 表的结构 `admin_member_group`
--

DROP TABLE IF EXISTS `{{table_prefix}}admin_member_group`;
CREATE TABLE `{{table_prefix}}admin_member_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `group_desc` text,
  `project` varchar(100) NOT NULL,
  `sort` smallint(4) unsigned NOT NULL,
  `perm_setting` text NOT NULL,
  `setting` text,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `project` (`project`),
  KEY `project_sort` (`project`,`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='管理组表' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- 表的结构 `admin_member_group_ids`
--

DROP TABLE IF EXISTS `{{table_prefix}}admin_member_group_ids`;
CREATE TABLE `{{table_prefix}}admin_member_group_ids` (
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员ID',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '管理组',
  `view_users` tinyint(1) unsigned NOT NULL COMMENT '查看用户',
  `edit_users` tinyint(1) unsigned NOT NULL COMMENT '修改用户',
  `edit_users_password` tinyint(1) unsigned NOT NULL COMMENT '修改用户密码',
  `add_user` tinyint(1) unsigned NOT NULL COMMENT '添加用户',
  `del_user` tinyint(1) unsigned NOT NULL COMMENT '删除用户',
  `remove_user` tinyint(1) unsigned NOT NULL COMMENT '移除用户',
  `shield_user` tinyint(1) unsigned NOT NULL COMMENT '屏蔽用户',
  `liftshield_user` tinyint(1) unsigned NOT NULL COMMENT '解除屏蔽用户',
  `edit_group` tinyint(1) unsigned NOT NULL COMMENT '修改组设置',
  PRIMARY KEY (`admin_id`,`group_id`),
  KEY `group_id` (`group_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员所在组';
