/*
SQLyog Enterprise - MySQL GUI v8.12 
MySQL - 5.0.41-community-nt : Database - web_myqee
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`myqee_web` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `myqee_web`;

/*Table structure for table `mycms_[acquisition]` */

DROP TABLE IF EXISTS `mycms_[acquisition]`;

CREATE TABLE `mycms_[acquisition]` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `method` varchar(4) default 'GET',
  `isuse` tinyint(1) default '1',
  `charset` varchar(10) default NULL,
  `classid` int(11) default NULL,
  `classname` varchar(200) default NULL,
  `modelid` int(11) default NULL,
  `modelname` varchar(200) default NULL,
  `dbname` varchar(200) default NULL,
  `post` text,
  `other_post` text,
  `islogin` tinyint(1) default NULL,
  `loginactionurl` varchar(500) default NULL,
  `loginpost` text,
  `loginimageurl` varchar(200) default NULL,
  `node` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[acquisition]` */

/*Table structure for table `mycms_[acquisition_data]` */

DROP TABLE IF EXISTS `mycms_[acquisition_data]`;

CREATE TABLE `mycms_[acquisition_data]` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(4000) default NULL,
  `info_id` varchar(255) default NULL,
  `info_content` longtext,
  `info_url` varchar(1000) default NULL COMMENT '信息地址',
  `info_url_md5` varchar(32) default NULL COMMENT '信息地址md5',
  `is_todb` tinyint(1) default NULL COMMENT '是否入库，0=否，1=是，2=失败',
  `mydb_id` int(11) default NULL COMMENT '入库后对应的ID',
  `urlread_time` int(10) default NULL COMMENT '操作时的时间',
  `acqu_id` int(10) default NULL,
  `node_id` varchar(200) default NULL,
  `dbname` varchar(100) default NULL,
  `class_id` int(10) default NULL,
  `model_id` int(10) default NULL,
  `dotime` varchar(25) default NULL,
  PRIMARY KEY  (`id`),
  KEY `acqu_id` (`acqu_id`),
  KEY `node_id` (`node_id`),
  KEY `info_url_md5` (`info_url_md5`),
  KEY `is_todb` (`is_todb`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[acquisition_data]` */

/*Table structure for table `mycms_[admin]` */

DROP TABLE IF EXISTS `mycms_[admin]`;

CREATE TABLE `mycms_[admin]` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `lastlogintime` int(10) default NULL,
  `lastloginip` varchar(30) default NULL,
  `countlogin` int(6) default NULL,
  `groupid` int(6) default NULL,
  `groupname` varchar(200) default NULL,
  `competence` text,
  `auto_classset` smallint(1) default '1',
  `auto_defaultsite` smallint(1) default '1',
  `defaultsite` int(10) default '0',
  `siteset` text,
  `auto_siteset` smallint(1) default '1',
  `classset` text,
  `dbset` text,
  `auto_dbset` smallint(1) default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[admin]` */

insert  into `mycms_[admin]`(`id`,`username`,`password`,`lastlogintime`,`lastloginip`,`countlogin`,`groupid`,`groupname`,`competence`,`auto_classset`,`auto_defaultsite`,`defaultsite`,`siteset`,`auto_siteset`,`classset`,`dbset`,`auto_dbset`) values (1,'admin','e10adc3949ba59abbe56e057f20f883e',1258293857,'127.0.0.1',2585,1,'超级管理员','a:9:{s:5:\"index\";a:13:{s:13:\"indexshowpath\";i:1;s:5:\"cache\";i:1;s:6:\"config\";i:1;s:10:\"config_bak\";i:1;s:6:\"runsql\";i:1;s:7:\"phpinfo\";i:1;s:9:\"adminmenu\";i:1;s:14:\"adminmenu_edit\";i:1;s:13:\"adminmenu_add\";i:1;s:9:\"site_list\";i:1;s:8:\"site_add\";i:1;s:9:\"site_edit\";i:1;s:8:\"site_del\";i:1;}s:5:\"class\";a:13:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:10:\"edit_paixu\";i:1;s:3:\"del\";i:1;s:10:\"navigation\";i:1;s:3:\"set\";i:1;s:12:\"dbchangesite\";i:1;s:12:\"special_list\";i:1;s:11:\"special_add\";i:1;s:11:\"special_del\";i:1;s:15:\"special_delinfo\";i:1;s:18:\"special_manageinfo\";i:1;}s:6:\"member\";a:4:{s:4:\"list\";i:1;s:5:\"group\";i:1;s:12:\"field_config\";i:1;s:11:\"data_update\";i:1;}s:4:\"info\";a:47:{s:4:\"list\";i:1;s:18:\"list_showclasstree\";i:1;s:15:\"list_showdbtree\";i:1;s:4:\"edit\";i:1;s:4:\"view\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:4:\"move\";i:1;s:8:\"setvalue\";i:1;s:10:\"block_list\";i:1;s:9:\"block_add\";i:1;s:10:\"block_edit\";i:1;s:7:\"comment\";i:1;s:13:\"comment_check\";i:1;s:11:\"comment_del\";i:1;s:10:\"uploadlist\";i:1;s:9:\"uploaddel\";i:1;s:10:\"uploadedit\";i:1;s:10:\"uploadfile\";i:1;s:14:\"uploadexplorer\";i:1;s:10:\"custompage\";i:1;s:13:\"custompageadd\";i:1;s:14:\"custompageedit\";i:1;s:13:\"custompagedel\";i:1;s:10:\"customlist\";i:1;s:13:\"customlistadd\";i:1;s:14:\"customlistedit\";i:1;s:13:\"customlistdel\";i:1;s:11:\"mydata_list\";i:1;s:10:\"mydata_add\";i:1;s:11:\"mydata_copy\";i:1;s:11:\"mydata_edit\";i:1;s:12:\"mydata_order\";i:1;s:10:\"mydata_del\";i:1;s:12:\"mydata_input\";i:1;s:13:\"mydata_output\";i:1;s:17:\"mydata_renewfiles\";i:1;s:12:\"mylink_lists\";i:1;s:12:\"mylink_order\";i:1;s:10:\"mylink_add\";i:1;s:11:\"mylink_edit\";i:1;s:10:\"mylink_del\";i:1;s:18:\"mylink_child_links\";i:1;s:10:\"save_links\";i:1;s:17:\"mylink_renewfiles\";i:1;s:13:\"mylink_output\";i:1;s:12:\"mylink_input\";i:1;}s:5:\"model\";a:20:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:9:\"editfield\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:6:\"dblist\";i:1;s:5:\"dbadd\";i:1;s:6:\"dbedit\";i:1;s:5:\"dbdel\";i:1;s:11:\"dbfieldlist\";i:1;s:10:\"dbfieldadd\";i:1;s:11:\"dbfieldedit\";i:1;s:10:\"dbfielddel\";i:1;s:7:\"dborder\";i:1;s:8:\"dboutput\";i:1;s:7:\"dbinput\";i:1;s:12:\"dbrenewfiles\";i:1;}s:8:\"template\";a:12:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:9:\"grouplist\";i:1;s:8:\"groupadd\";i:1;s:9:\"groupedit\";i:1;s:8:\"groupdel\";i:1;s:7:\"systemp\";i:1;}s:4:\"task\";a:19:{s:4:\"list\";i:1;s:8:\"task_add\";i:1;s:9:\"task_edit\";i:1;s:8:\"task_del\";i:1;s:10:\"task_input\";i:1;s:11:\"task_output\";i:1;s:15:\"task_renewfiles\";i:1;s:12:\"dohtml_index\";i:1;s:16:\"dohtml_siteindex\";i:1;s:17:\"dohtml_custompage\";i:1;s:17:\"dohtml_customlist\";i:1;s:12:\"dohtml_class\";i:1;s:11:\"dohtml_info\";i:1;s:16:\"acquisition_list\";i:1;s:15:\"acquisition_add\";i:1;s:16:\"acquisition_edit\";i:1;s:15:\"acquisition_del\";i:1;s:20:\"acquisition_datatodb\";i:1;s:15:\"acquisition_run\";i:1;}s:5:\"admin\";a:10:{s:4:\"list\";i:1;s:16:\"changepassword_1\";i:1;s:16:\"changepassword_2\";i:1;s:16:\"changecompetence\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:10:\"group_list\";i:1;s:10:\"group_edit\";i:1;s:9:\"group_add\";i:1;s:9:\"group_del\";i:1;}s:7:\"plugins\";a:6:{s:4:\"list\";i:1;s:5:\"setup\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:15:\"createsetupfile\";i:1;s:9:\"uninstall\";i:1;}}',1,1,0,'-ALL-',1,'-ALL-','-ALL-',1),(2,'editor','5aee9dbd2a188839105073571bee1b1f',1243839625,'192.168.1.171',11,0,'默认','a:3:{s:5:\"class\";a:1:{s:3:\"del\";i:1;}s:4:\"info\";a:31:{s:4:\"list\";i:1;s:18:\"list_showclasstree\";i:1;s:15:\"list_showdbtree\";i:1;s:4:\"edit\";i:1;s:4:\"view\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:4:\"move\";i:1;s:8:\"setvalue\";i:1;s:7:\"comment\";i:1;s:13:\"comment_check\";i:1;s:11:\"comment_del\";i:1;s:10:\"uploadlist\";i:1;s:9:\"uploaddel\";i:1;s:10:\"uploadedit\";i:1;s:10:\"uploadfile\";i:1;s:14:\"uploadexplorer\";i:1;s:10:\"custompage\";i:1;s:13:\"custompageadd\";i:1;s:14:\"custompageedit\";i:1;s:13:\"custompagedel\";i:1;s:12:\"mylink_lists\";i:1;s:12:\"mylink_order\";i:1;s:10:\"mylink_add\";i:1;s:11:\"mylink_edit\";i:1;s:10:\"mylink_del\";i:1;s:18:\"mylink_child_links\";i:1;s:10:\"save_links\";i:1;s:17:\"mylink_renewfiles\";i:1;s:13:\"mylink_output\";i:1;s:12:\"mylink_input\";i:1;}s:5:\"admin\";a:2:{s:16:\"changecompetence\";i:1;s:3:\"del\";i:1;}}',1,1,-1,NULL,1,NULL,NULL,1);

/*Table structure for table `mycms_[admin_group]` */

DROP TABLE IF EXISTS `mycms_[admin_group]`;

CREATE TABLE `mycms_[admin_group]` (
  `id` int(11) NOT NULL auto_increment,
  `groupname` varchar(200) default NULL,
  `competence` text,
  `defaultsite` int(10) default '0',
  `site` text,
  `class` text,
  `db` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[admin_group]` */

insert  into `mycms_[admin_group]`(`id`,`groupname`,`competence`,`defaultsite`,`site`,`class`,`db`) values (1,'超级管理员','a:9:{s:5:\"index\";a:13:{s:13:\"indexshowpath\";i:1;s:5:\"cache\";i:1;s:6:\"config\";i:1;s:10:\"config_bak\";i:1;s:6:\"runsql\";i:1;s:7:\"phpinfo\";i:1;s:9:\"adminmenu\";i:1;s:14:\"adminmenu_edit\";i:1;s:13:\"adminmenu_add\";i:1;s:9:\"site_list\";i:1;s:8:\"site_add\";i:1;s:9:\"site_edit\";i:1;s:8:\"site_del\";i:1;}s:5:\"class\";a:13:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:10:\"edit_paixu\";i:1;s:3:\"del\";i:1;s:10:\"navigation\";i:1;s:3:\"set\";i:1;s:12:\"dbchangesite\";i:1;s:12:\"special_list\";i:1;s:11:\"special_add\";i:1;s:11:\"special_del\";i:1;s:15:\"special_delinfo\";i:1;s:18:\"special_manageinfo\";i:1;}s:6:\"member\";a:4:{s:4:\"list\";i:1;s:5:\"group\";i:1;s:12:\"field_config\";i:1;s:11:\"data_update\";i:1;}s:4:\"info\";a:47:{s:4:\"list\";i:1;s:18:\"list_showclasstree\";i:1;s:15:\"list_showdbtree\";i:1;s:4:\"edit\";i:1;s:4:\"view\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:4:\"move\";i:1;s:8:\"setvalue\";i:1;s:10:\"block_list\";i:1;s:9:\"block_add\";i:1;s:10:\"block_edit\";i:1;s:7:\"comment\";i:1;s:13:\"comment_check\";i:1;s:11:\"comment_del\";i:1;s:10:\"uploadlist\";i:1;s:9:\"uploaddel\";i:1;s:10:\"uploadedit\";i:1;s:10:\"uploadfile\";i:1;s:14:\"uploadexplorer\";i:1;s:10:\"custompage\";i:1;s:13:\"custompageadd\";i:1;s:14:\"custompageedit\";i:1;s:13:\"custompagedel\";i:1;s:10:\"customlist\";i:1;s:13:\"customlistadd\";i:1;s:14:\"customlistedit\";i:1;s:13:\"customlistdel\";i:1;s:11:\"mydata_list\";i:1;s:10:\"mydata_add\";i:1;s:11:\"mydata_copy\";i:1;s:11:\"mydata_edit\";i:1;s:12:\"mydata_order\";i:1;s:10:\"mydata_del\";i:1;s:12:\"mydata_input\";i:1;s:13:\"mydata_output\";i:1;s:17:\"mydata_renewfiles\";i:1;s:12:\"mylink_lists\";i:1;s:12:\"mylink_order\";i:1;s:10:\"mylink_add\";i:1;s:11:\"mylink_edit\";i:1;s:10:\"mylink_del\";i:1;s:18:\"mylink_child_links\";i:1;s:10:\"save_links\";i:1;s:17:\"mylink_renewfiles\";i:1;s:13:\"mylink_output\";i:1;s:12:\"mylink_input\";i:1;}s:5:\"model\";a:20:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:9:\"editfield\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:6:\"dblist\";i:1;s:5:\"dbadd\";i:1;s:6:\"dbedit\";i:1;s:5:\"dbdel\";i:1;s:11:\"dbfieldlist\";i:1;s:10:\"dbfieldadd\";i:1;s:11:\"dbfieldedit\";i:1;s:10:\"dbfielddel\";i:1;s:7:\"dborder\";i:1;s:8:\"dboutput\";i:1;s:7:\"dbinput\";i:1;s:12:\"dbrenewfiles\";i:1;}s:8:\"template\";a:12:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:9:\"grouplist\";i:1;s:8:\"groupadd\";i:1;s:9:\"groupedit\";i:1;s:8:\"groupdel\";i:1;s:7:\"systemp\";i:1;}s:4:\"task\";a:19:{s:4:\"list\";i:1;s:8:\"task_add\";i:1;s:9:\"task_edit\";i:1;s:8:\"task_del\";i:1;s:10:\"task_input\";i:1;s:11:\"task_output\";i:1;s:15:\"task_renewfiles\";i:1;s:12:\"dohtml_index\";i:1;s:16:\"dohtml_siteindex\";i:1;s:17:\"dohtml_custompage\";i:1;s:17:\"dohtml_customlist\";i:1;s:12:\"dohtml_class\";i:1;s:11:\"dohtml_info\";i:1;s:16:\"acquisition_list\";i:1;s:15:\"acquisition_add\";i:1;s:16:\"acquisition_edit\";i:1;s:15:\"acquisition_del\";i:1;s:20:\"acquisition_datatodb\";i:1;s:15:\"acquisition_run\";i:1;}s:5:\"admin\";a:10:{s:4:\"list\";i:1;s:16:\"changepassword_1\";i:1;s:16:\"changepassword_2\";i:1;s:16:\"changecompetence\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:10:\"group_list\";i:1;s:10:\"group_edit\";i:1;s:9:\"group_add\";i:1;s:9:\"group_del\";i:1;}s:7:\"plugins\";a:6:{s:4:\"list\";i:1;s:5:\"setup\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:15:\"createsetupfile\";i:1;s:9:\"uninstall\";i:1;}}',0,'-ALL-','-ALL-','-ALL-'),(3,'编辑','a:9:{s:7:\"special\";a:1:{s:3:\"sms\";i:1;}s:5:\"index\";a:5:{s:13:\"indexshowpath\";i:1;s:6:\"config\";i:1;s:10:\"config_bak\";i:1;s:7:\"phpinfo\";i:1;s:9:\"adminmenu\";i:1;}s:5:\"class\";a:7:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:10:\"edit_paixu\";i:1;s:3:\"del\";i:1;s:10:\"navigation\";i:1;s:3:\"set\";i:1;}s:4:\"info\";a:30:{s:4:\"list\";i:1;s:18:\"list_showclasstree\";i:1;s:15:\"list_showdbtree\";i:1;s:4:\"edit\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:4:\"move\";i:1;s:8:\"setvalue\";i:1;s:7:\"comment\";i:1;s:13:\"comment_check\";i:1;s:11:\"comment_del\";i:1;s:10:\"uploadlist\";i:1;s:9:\"uploaddel\";i:1;s:10:\"uploadedit\";i:1;s:10:\"uploadfile\";i:1;s:14:\"uploadexplorer\";i:1;s:10:\"custompage\";i:1;s:13:\"custompageadd\";i:1;s:14:\"custompageedit\";i:1;s:13:\"custompagedel\";i:1;s:12:\"mylink_lists\";i:1;s:12:\"mylink_order\";i:1;s:10:\"mylink_add\";i:1;s:11:\"mylink_edit\";i:1;s:10:\"mylink_del\";i:1;s:18:\"mylink_child_links\";i:1;s:10:\"save_links\";i:1;s:17:\"mylink_renewfiles\";i:1;s:13:\"mylink_output\";i:1;s:12:\"mylink_input\";i:1;}s:5:\"model\";a:20:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:9:\"editfield\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:6:\"dblist\";i:1;s:5:\"dbadd\";i:1;s:6:\"dbedit\";i:1;s:5:\"dbdel\";i:1;s:11:\"dbfieldlist\";i:1;s:10:\"dbfieldadd\";i:1;s:11:\"dbfieldedit\";i:1;s:10:\"dbfielddel\";i:1;s:7:\"dborder\";i:1;s:8:\"dboutput\";i:1;s:7:\"dbinput\";i:1;s:12:\"dbrenewfiles\";i:1;}s:8:\"template\";a:12:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:9:\"grouplist\";i:1;s:8:\"groupadd\";i:1;s:9:\"groupedit\";i:1;s:8:\"groupdel\";i:1;s:7:\"systemp\";i:1;}s:4:\"task\";a:24:{s:4:\"list\";i:1;s:8:\"task_add\";i:1;s:9:\"task_edit\";i:1;s:8:\"task_del\";i:1;s:10:\"task_input\";i:1;s:11:\"task_output\";i:1;s:15:\"task_renewfiles\";i:1;s:12:\"dohtml_index\";i:1;s:12:\"dohtml_class\";i:1;s:11:\"dohtml_info\";i:1;s:15:\"competence_list\";i:1;s:14:\"competence_add\";i:1;s:15:\"competence_edit\";i:1;s:14:\"competence_del\";i:1;s:11:\"mydata_list\";i:1;s:10:\"mydata_add\";i:1;s:11:\"mydata_copy\";i:1;s:11:\"mydata_edit\";i:1;s:12:\"mydata_order\";i:1;s:10:\"mydata_del\";i:1;s:12:\"mydata_input\";i:1;s:13:\"mydata_output\";i:1;s:17:\"mydata_renewfiles\";i:1;s:12:\"urlread_list\";i:1;}s:5:\"admin\";a:10:{s:4:\"list\";i:1;s:16:\"changepassword_1\";i:1;s:16:\"changepassword_2\";i:1;s:16:\"changecompetence\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:10:\"group_list\";i:1;s:10:\"group_edit\";i:1;s:9:\"group_add\";i:1;s:9:\"group_del\";i:1;}s:4:\"plus\";a:4:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;}}',0,NULL,NULL,NULL),(4,'SMS','a:9:{s:7:\"special\";a:1:{s:3:\"sms\";i:1;}s:5:\"index\";a:5:{s:13:\"indexshowpath\";i:1;s:6:\"config\";i:1;s:10:\"config_bak\";i:1;s:7:\"phpinfo\";i:1;s:9:\"adminmenu\";i:1;}s:5:\"class\";a:7:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:10:\"edit_paixu\";i:1;s:3:\"del\";i:1;s:10:\"navigation\";i:1;s:3:\"set\";i:1;}s:4:\"info\";a:30:{s:4:\"list\";i:1;s:18:\"list_showclasstree\";i:1;s:15:\"list_showdbtree\";i:1;s:4:\"edit\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:4:\"move\";i:1;s:8:\"setvalue\";i:1;s:7:\"comment\";i:1;s:13:\"comment_check\";i:1;s:11:\"comment_del\";i:1;s:10:\"uploadlist\";i:1;s:9:\"uploaddel\";i:1;s:10:\"uploadedit\";i:1;s:10:\"uploadfile\";i:1;s:14:\"uploadexplorer\";i:1;s:10:\"custompage\";i:1;s:13:\"custompageadd\";i:1;s:14:\"custompageedit\";i:1;s:13:\"custompagedel\";i:1;s:12:\"mylink_lists\";i:1;s:12:\"mylink_order\";i:1;s:10:\"mylink_add\";i:1;s:11:\"mylink_edit\";i:1;s:10:\"mylink_del\";i:1;s:18:\"mylink_child_links\";i:1;s:10:\"save_links\";i:1;s:17:\"mylink_renewfiles\";i:1;s:13:\"mylink_output\";i:1;s:12:\"mylink_input\";i:1;}s:5:\"model\";a:20:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:9:\"editfield\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:6:\"dblist\";i:1;s:5:\"dbadd\";i:1;s:6:\"dbedit\";i:1;s:5:\"dbdel\";i:1;s:11:\"dbfieldlist\";i:1;s:10:\"dbfieldadd\";i:1;s:11:\"dbfieldedit\";i:1;s:10:\"dbfielddel\";i:1;s:7:\"dborder\";i:1;s:8:\"dboutput\";i:1;s:7:\"dbinput\";i:1;s:12:\"dbrenewfiles\";i:1;}s:8:\"template\";a:12:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;s:6:\"output\";i:1;s:5:\"input\";i:1;s:10:\"renewfiles\";i:1;s:9:\"grouplist\";i:1;s:8:\"groupadd\";i:1;s:9:\"groupedit\";i:1;s:8:\"groupdel\";i:1;s:7:\"systemp\";i:1;}s:4:\"task\";a:24:{s:4:\"list\";i:1;s:8:\"task_add\";i:1;s:9:\"task_edit\";i:1;s:8:\"task_del\";i:1;s:10:\"task_input\";i:1;s:11:\"task_output\";i:1;s:15:\"task_renewfiles\";i:1;s:12:\"dohtml_index\";i:1;s:12:\"dohtml_class\";i:1;s:11:\"dohtml_info\";i:1;s:15:\"competence_list\";i:1;s:14:\"competence_add\";i:1;s:15:\"competence_edit\";i:1;s:14:\"competence_del\";i:1;s:11:\"mydata_list\";i:1;s:10:\"mydata_add\";i:1;s:11:\"mydata_copy\";i:1;s:11:\"mydata_edit\";i:1;s:12:\"mydata_order\";i:1;s:10:\"mydata_del\";i:1;s:12:\"mydata_input\";i:1;s:13:\"mydata_output\";i:1;s:17:\"mydata_renewfiles\";i:1;s:12:\"urlread_list\";i:1;}s:5:\"admin\";a:10:{s:4:\"list\";i:1;s:16:\"changepassword_1\";i:1;s:16:\"changepassword_2\";i:1;s:16:\"changecompetence\";i:1;s:3:\"add\";i:1;s:3:\"del\";i:1;s:10:\"group_list\";i:1;s:10:\"group_edit\";i:1;s:9:\"group_add\";i:1;s:9:\"group_del\";i:1;}s:4:\"plus\";a:4:{s:4:\"list\";i:1;s:3:\"add\";i:1;s:4:\"edit\";i:1;s:3:\"del\";i:1;}}',0,NULL,NULL,'sms_info');

/*Table structure for table `mycms_[block]` */

DROP TABLE IF EXISTS `mycms_[block]`;

CREATE TABLE `mycms_[block]` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `type` varchar(100) default NULL,
  `no` int(10) default NULL,
  `isuse` smallint(1) default '1',
  `myorder` int(10) default NULL,
  `show_type` smallint(1) default NULL,
  `content` text,
  `varname` varchar(200) default NULL COMMENT '变量名',
  `len` int(10) default '10',
  `tpl_id` int(11) default '0',
  `tpl_engie` varchar(200) default NULL,
  `template` text,
  `mydata_id` int(11) default '0',
  `cache_time` int(10) default '0',
  `advfield` text,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `no` (`no`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[block]` */

insert  into `mycms_[block]`(`id`,`title`,`type`,`no`,`isuse`,`myorder`,`show_type`,`content`,`varname`,`len`,`tpl_id`,`tpl_engie`,`template`,`mydata_id`,`cache_time`,`advfield`) values (2,'首页下载','index',1,1,0,2,'a:1:{i:0;a:3:{s:1:\"v\";s:17:\"麦琪CMS 1.0 RC1\";s:4:\"time\";s:10:\"2009-10-19\";s:4:\"down\";a:6:{i:0;a:2:{s:7:\"address\";s:6:\"/nmew/\";s:5:\"title\";s:12:\"本地下载\";}i:1;a:2:{s:7:\"address\";s:3:\"#jj\";s:5:\"title\";s:12:\"了解详情\";}i:2;a:2:{s:7:\"address\";s:1:\"#\";s:5:\"title\";s:12:\"开发手册\";}i:3;a:2:{s:7:\"address\";s:1:\"3\";s:5:\"title\";s:15:\"站长站下载\";}i:4;a:2:{s:7:\"address\";s:0:\"\";s:5:\"title\";s:12:\"下载中心\";}i:5;a:2:{s:7:\"address\";s:0:\"\";s:5:\"title\";s:12:\"站长工具\";}}}}','data',0,0,'','<span>{$data.0.time}</span>\r\n<h2>{$data.0.v}</h2>\r\n<div class=\"address\">\r\n<!--{$i=0}-->\r\n<!--{$c=count($data.0.down)}-->\r\n<!--{loop $data.0.down as $item}-->\r\n<!--{$i++}-->\r\n<a href=\"{$item.address}\">{$item.title}</a>\r\n<!--{if $i==3}--><br/><!--{/if}-->\r\n<!--{if $i!=3&&$i!=$c}--> | <!--{/if}-->\r\n<!--{/loop}-->\r\n</div>',0,0,'a:5:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:1:\"2\";s:3:\"num\";s:1:\"1\";s:9:\"editwidth\";N;s:5:\"isadd\";i:0;s:5:\"isdel\";i:0;s:7:\"isorder\";i:0;}s:1:\"v\";a:8:{s:4:\"flag\";s:1:\"v\";s:4:\"name\";s:12:\"版本名称\";s:4:\"type\";s:5:\"input\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";s:2:\"30\";s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:4:\"time\";a:8:{s:4:\"flag\";s:4:\"time\";s:4:\"name\";s:12:\"更新时间\";s:4:\"type\";s:4:\"date\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:4:\"test\";a:8:{s:4:\"flag\";s:4:\"test\";s:4:\"name\";s:6:\"测试\";s:4:\"type\";s:8:\"htmlarea\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:4:\"down\";a:4:{s:2:\"_g\";a:8:{s:4:\"flag\";s:4:\"down\";s:4:\"name\";s:12:\"下载地址\";s:4:\"type\";s:1:\"0\";s:3:\"num\";s:1:\"6\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}s:7:\"address\";a:8:{s:4:\"flag\";s:7:\"address\";s:4:\"name\";s:6:\"地址\";s:4:\"type\";s:5:\"input\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";s:2:\"25\";s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:5:\"title\";a:8:{s:4:\"flag\";s:5:\"title\";s:4:\"name\";s:6:\"显示\";s:4:\"type\";s:5:\"input\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";s:2:\"16\";s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:3:\"sss\";a:8:{s:4:\"flag\";s:3:\"sss\";s:4:\"name\";s:9:\"撒旦法\";s:4:\"type\";s:5:\"input\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}}}'),(3,'首页今日推荐','index',2,1,0,1,'a:1:{i:0;a:6:{s:5:\"title\";s:52:\"[我是传奇] <strong>21916天全民日志</strong>\";s:3:\"URL\";s:6:\"/news/\";s:5:\"image\";s:16:\"/images/logo.gif\";s:6:\"target\";s:6:\"_blank\";s:4:\"time\";s:0:\"\";s:11:\"description\";s:69:\"天全民日志天全民日志天全民日志天全天全民日日志\";}}','data',0,0,'','<!--{loop $data as $item}-->\r\n<div style=\"height:105px\">\r\n<a href=\"{$item.URL}\" target=\"{$item.target}\">{$item.title}</a>\r\n<div style=\"padding:10px 0;width:90px;float:left;height:60px;\"><a href=\"{$item.URL}\" target=\"{$item.target}\"><img src=\"{$item.image}\" style=\"width:90px;height:60px;border:1px solid #ccc;\" /></a></div>\r\n<div style=\"padding:10px 0;float:right;width:125px;height:60px;overflow:hidden;line-height:1.7em;\">{$item.description} &nbsp;<a href=\"{$item.URL}\" target=\"{$item.target}\" style=\"color:#bc2d09;font-weight:bold;\">详细&raquo;</a></div><br />\r\n</div>\r\n<!--{/loop}-->',0,0,'a:2:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}s:6:\"sdfsdf\";a:8:{s:4:\"flag\";s:6:\"sdfsdf\";s:4:\"name\";s:6:\"sdfsdf\";s:4:\"type\";s:6:\"select\";s:6:\"format\";s:3:\"int\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}}'),(4,'测试碎片','index',3,1,0,2,'a:1:{i:0;a:4:{s:4:\"test\";s:18:\"sadfasdfssssssssss\";s:5:\"test2\";s:19:\"2009-10-19 14:43:18\";s:6:\"sdfsfd\";a:1:{i:0;a:1:{s:3:\"aaa\";s:5:\"dddff\";}}s:3:\"img\";s:0:\"\";}}','',0,0,NULL,NULL,0,0,'a:5:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:1:\"2\";s:3:\"num\";s:1:\"1\";s:9:\"editwidth\";s:2:\"50\";s:5:\"isadd\";i:0;s:5:\"isdel\";i:0;s:7:\"isorder\";i:0;}s:4:\"test\";a:8:{s:4:\"flag\";s:4:\"test\";s:4:\"name\";s:7:\"测试1\";s:4:\"type\";s:5:\"input\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";s:2:\"60\";s:3:\"set\";a:4:{s:4:\"size\";s:2:\"40\";s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:5:\"test2\";a:8:{s:4:\"flag\";s:5:\"test2\";s:4:\"name\";s:7:\"测试2\";s:4:\"type\";s:4:\"time\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}s:6:\"sdfsfd\";a:2:{s:2:\"_g\";a:8:{s:4:\"flag\";s:6:\"sdfsfd\";s:4:\"name\";s:9:\"撒旦法\";s:4:\"type\";s:1:\"0\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}s:3:\"aaa\";a:8:{s:4:\"flag\";s:3:\"aaa\";s:4:\"name\";s:7:\"safsadf\";s:4:\"type\";s:5:\"input\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}}s:3:\"img\";a:8:{s:4:\"flag\";s:3:\"img\";s:4:\"name\";s:6:\"图片\";s:4:\"type\";s:8:\"imginput\";s:6:\"format\";s:0:\"\";s:9:\"editwidth\";N;s:3:\"set\";a:4:{s:4:\"size\";N;s:4:\"rows\";N;s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";}}');

/*Table structure for table `mycms_[class]` */

DROP TABLE IF EXISTS `mycms_[class]`;

CREATE TABLE `mycms_[class]` (
  `classid` int(11) NOT NULL auto_increment,
  `bclassid` int(11) default NULL,
  `classname` varchar(50) default NULL,
  `classimg` varchar(255) default NULL,
  `siteid` int(10) default '0',
  `modelid` int(11) default NULL,
  `dbname` varchar(200) default NULL,
  `sonclass` varchar(4000) default NULL,
  `fatherclass` varchar(4000) default NULL,
  `hits` int(11) default NULL,
  `classpath` text,
  `classtype` varchar(20) default NULL,
  `myorder` smallint(6) default NULL,
  `hostname` varchar(200) default NULL,
  `htmlintro` text,
  `keyword` varchar(11) default NULL,
  `description` varchar(255) default NULL,
  `isnothtml` tinyint(1) default NULL,
  `iscover` tinyint(1) default NULL,
  `cover_tohtml` tinyint(1) default NULL,
  `cover_cachetime` int(10) default NULL,
  `cover_tplid` int(11) default NULL,
  `cover_filename` varchar(200) default NULL,
  `cover_hiddenfilename` tinyint(1) default NULL,
  `islist` tinyint(1) default NULL,
  `list_tohtml` tinyint(1) default NULL,
  `list_cachetime` int(10) default NULL,
  `list_tplid` int(11) default NULL,
  `list_nosonclass` tinyint(1) default NULL,
  `list_pernum` smallint(3) default NULL,
  `list_allpage` smallint(6) default NULL,
  `list_byfield` varchar(100) default NULL,
  `list_orderby` varchar(4) default NULL,
  `list_filename` varchar(200) default NULL,
  `iscontent` tinyint(1) default NULL,
  `content_tohtml` tinyint(1) default NULL,
  `content_cachetime` int(10) default NULL,
  `content_tplid` int(11) default NULL,
  `content_pathtype` tinyint(1) default NULL,
  `content_path` varchar(255) default NULL,
  `content_selfpath` varchar(200) default NULL,
  `content_prefix` varchar(50) default NULL,
  `content_filenametype` tinyint(1) default NULL,
  `content_suffix` varchar(50) default NULL,
  `issearch` tinyint(1) default NULL,
  `search_tplid` int(11) default NULL,
  `search_byfield` varchar(50) default NULL,
  `search_orderby` varchar(4) default NULL,
  `search_cachetime` int(10) default NULL,
  `isnavshow` tinyint(1) default NULL,
  `manage_limit` mediumint(3) default NULL,
  `manage_orderbyfield` varchar(50) default NULL,
  `manage_orderby` varchar(4) default NULL,
  PRIMARY KEY  (`classid`),
  KEY `bclassid` (`bclassid`),
  KEY `siteid` (`siteid`),
  KEY `sonclass` (`sonclass`(333)),
  KEY `dbname` (`dbname`),
  KEY `myorder` (`myorder`),
  KEY `fatherclass` (`fatherclass`(333)),
  KEY `modelid` (`modelid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[class]` */

insert  into `mycms_[class]`(`classid`,`bclassid`,`classname`,`classimg`,`siteid`,`modelid`,`dbname`,`sonclass`,`fatherclass`,`hits`,`classpath`,`classtype`,`myorder`,`hostname`,`htmlintro`,`keyword`,`description`,`isnothtml`,`iscover`,`cover_tohtml`,`cover_cachetime`,`cover_tplid`,`cover_filename`,`cover_hiddenfilename`,`islist`,`list_tohtml`,`list_cachetime`,`list_tplid`,`list_nosonclass`,`list_pernum`,`list_allpage`,`list_byfield`,`list_orderby`,`list_filename`,`iscontent`,`content_tohtml`,`content_cachetime`,`content_tplid`,`content_pathtype`,`content_path`,`content_selfpath`,`content_prefix`,`content_filenametype`,`content_suffix`,`issearch`,`search_tplid`,`search_byfield`,`search_orderby`,`search_cachetime`,`isnavshow`,`manage_limit`,`manage_orderbyfield`,`manage_orderby`) values (1,0,'资讯','',3,1,'default/news','|2|3|',NULL,0,'news',NULL,0,'','','','',0,0,NULL,NULL,NULL,NULL,NULL,1,0,0,4,0,20,0,'id','DESC','list_{{page}}.html',0,0,0,3,0,NULL,'Y/md','',3,'.html',0,NULL,NULL,NULL,NULL,0,20,'id','DESC'),(2,1,'国内','',3,1,'default/news',NULL,'|1|',0,'news/china',NULL,0,'','','','',0,0,NULL,NULL,NULL,NULL,NULL,1,0,0,4,0,20,0,'id','DESC','list_{{page}}.html',1,0,0,3,0,NULL,'Y/m/d','',0,'.html',0,NULL,NULL,NULL,NULL,0,20,'id','DESC'),(3,1,'国际','',3,1,'default/news',NULL,'|1|',0,'news/world',NULL,0,'','','','',0,0,NULL,NULL,NULL,NULL,NULL,1,0,0,4,0,20,0,'id','DESC','list_{{page}}.html',1,0,0,3,0,NULL,'Y/m/d','',0,'.html',0,NULL,NULL,NULL,NULL,0,20,'id','DESC');

/*Table structure for table `mycms_[cron]` */

DROP TABLE IF EXISTS `mycms_[cron]`;

CREATE TABLE `mycms_[cron]` (
  `id` smallint(6) unsigned NOT NULL auto_increment COMMENT 'id',
  `available` tinyint(1) NOT NULL default '0' COMMENT '是否开启',
  `type` varchar(50) NOT NULL default 'user' COMMENT '任务类型',
  `name` char(50) NOT NULL default '' COMMENT '任务名称',
  `filename` char(50) NOT NULL default '' COMMENT '脚本名称',
  `starttime` int(10) default '0' COMMENT '开始时间',
  `endtime` int(10) default '0' COMMENT '结束时间',
  `lastime` int(10) unsigned NOT NULL default '0' COMMENT '上次执行时间',
  `nexttime` int(10) unsigned NOT NULL default '0' COMMENT '下次执行时间',
  `week` varchar(20) NOT NULL default '0' COMMENT '星期',
  `day` tinyint(2) NOT NULL default '0' COMMENT '天',
  `hour` tinyint(2) NOT NULL default '0' COMMENT '小时',
  `minute` char(36) NOT NULL default '' COMMENT '分钟',
  `taskmode` varchar(50) default NULL COMMENT '模式',
  `maxtimes` int(11) default NULL COMMENT '最大次数',
  `fintimes` int(11) default NULL COMMENT '完成次数',
  PRIMARY KEY  (`id`),
  KEY `nextrun` (`available`,`nexttime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `mycms_[cron]` */

/*Table structure for table `mycms_[customlist]` */

DROP TABLE IF EXISTS `mycms_[customlist]`;

CREATE TABLE `mycms_[customlist]` (
  `id` int(11) NOT NULL auto_increment,
  `pagename` varchar(255) default NULL,
  `filepath` varchar(255) default NULL,
  `filename` varchar(255) default NULL,
  `filename_suffix` varchar(10) default NULL,
  `cachetime` int(10) default '0' COMMENT '缓存时间',
  `istohtml` tinyint(1) default '1' COMMENT '是否静态输出，1静态 0 动态',
  `listsql` text COMMENT '列表sql',
  `totalsql` text COMMENT '统计sql',
  `totalnums` int(11) default NULL COMMENT '查询总条数',
  `pnums` int(11) default NULL COMMENT '每页显示条数',
  `pagetitle` varchar(255) default NULL,
  `title_flag` varchar(50) default NULL,
  `keyword` varchar(255) default NULL,
  `keywords_flag` varchar(50) default NULL,
  `pagedesc` text,
  `pagedesc_flag` varchar(50) default NULL,
  `content` text,
  `content_flag` varchar(59) default NULL,
  `cate` varchar(255) default NULL,
  `createtime` int(11) default NULL,
  `tplid` int(11) default NULL,
  `istpl` int(1) default '0',
  `isuse` int(1) default '0',
  `param` text,
  `edit_type` smallint(1) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[customlist]` */

/*Table structure for table `mycms_[custompage]` */

DROP TABLE IF EXISTS `mycms_[custompage]`;

CREATE TABLE `mycms_[custompage]` (
  `id` int(11) NOT NULL auto_increment,
  `pagename` varchar(255) default NULL,
  `filepath` varchar(255) default NULL,
  `filename` varchar(255) default NULL,
  `filename_suffix` varchar(10) default NULL,
  `pagetitle` varchar(255) default NULL,
  `title_flag` varchar(50) default NULL,
  `keyword` varchar(255) default NULL,
  `keywords_flag` varchar(50) default NULL,
  `pagedesc` text,
  `pagedesc_flag` varchar(50) default NULL,
  `content` text,
  `content_flag` varchar(59) default NULL,
  `cate` varchar(255) default NULL,
  `createtime` int(11) default NULL,
  `tplid` int(11) default NULL,
  `istpl` int(1) default '0',
  `isuse` int(1) default '0',
  `param` text,
  `edit_type` smallint(1) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[custompage]` */

/*Table structure for table `mycms_[dbtable]` */

DROP TABLE IF EXISTS `mycms_[dbtable]`;

CREATE TABLE `mycms_[dbtable]` (
  `id` smallint(6) NOT NULL auto_increment,
  `dbname` varchar(60) NOT NULL default '',
  `name` varchar(60) NOT NULL default '',
  `content` text,
  `isdefault` tinyint(1) default '0',
  `config` text,
  `isuse` tinyint(1) default '1',
  `myorder` tinyint(1) default '0',
  `ismemberdb` tinyint(1) default '0',
  `readbydbname` tinyint(1) default '0',
  `usedbmodel` tinyint(1) default '0',
  `modelconfig` text,
  `siteid` int(10) default '0',
  PRIMARY KEY  (`id`),
  KEY `myorder` (`myorder`),
  KEY `ismemberdb` (`ismemberdb`),
  KEY `isuse` (`isuse`),
  KEY `siteid` (`siteid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[dbtable]` */

insert  into `mycms_[dbtable]`(`id`,`dbname`,`name`,`content`,`isdefault`,`config`,`isuse`,`myorder`,`ismemberdb`,`readbydbname`,`usedbmodel`,`modelconfig`,`siteid`) values (1,'新闻数据表','default/news','',0,'a:2:{s:9:\"sys_field\";a:12:{s:2:\"id\";s:2:\"id\";s:5:\"title\";s:5:\"title\";s:9:\"imagenews\";s:5:\"image\";s:6:\"isshow\";s:6:\"isshow\";s:8:\"abstract\";s:8:\"abstract\";s:14:\"contentdb_page\";s:7:\"content\";s:8:\"class_id\";s:8:\"class_id\";s:10:\"class_name\";s:10:\"class_name\";s:8:\"filepath\";s:8:\"filepath\";s:8:\"filename\";s:8:\"filename\";s:9:\"iscommend\";s:9:\"iscommend\";s:6:\"writer\";s:4:\"sssf\";}s:5:\"field\";a:12:{s:2:\"id\";a:24:{s:4:\"name\";s:2:\"id\";s:6:\"dbname\";s:2:\"ID\";s:7:\"autoset\";s:2:\"id\";s:5:\"iskey\";b:1;s:6:\"isonly\";b:1;s:8:\"isnonull\";b:1;s:8:\"istofile\";b:0;s:4:\"type\";s:3:\"int\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:11;s:9:\"inputtype\";s:6:\"hidden\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:6:\"islist\";b:1;s:5:\"width\";i:60;s:5:\"align\";s:6:\"center\";s:7:\"tdclass\";s:3:\"td2\";}s:5:\"title\";a:24:{s:4:\"name\";s:5:\"title\";s:6:\"dbname\";s:6:\"标题\";s:7:\"autoset\";s:5:\"title\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:1;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"varchar\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:5:\"input\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:6:\"string\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:45;s:6:\"islist\";b:1;s:5:\"align\";s:0:\"\";s:7:\"tdclass\";s:3:\"td2\";}s:5:\"image\";a:21:{s:4:\"name\";s:5:\"image\";s:6:\"dbname\";s:12:\"标题图片\";s:7:\"autoset\";s:9:\"imagenews\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"varchar\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:8:\"imginput\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:30;}s:6:\"isshow\";a:26:{s:4:\"name\";s:6:\"isshow\";s:6:\"dbname\";s:12:\"是否发布\";s:7:\"autoset\";s:6:\"isshow\";s:5:\"iskey\";b:1;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"tinyint\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:1;s:9:\"inputtype\";s:5:\"radio\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:35:\"0|未审核\r\n1|发布\r\n-1|不发布\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:3:\"int\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:1;s:6:\"islist\";b:1;s:5:\"width\";i:55;s:5:\"align\";s:6:\"center\";s:7:\"tdclass\";s:3:\"td2\";s:7:\"boolean\";s:37:\"1|是\r\n0|<font color=\"red\">否</font>\";}s:8:\"abstract\";a:22:{s:4:\"name\";s:8:\"abstract\";s:6:\"dbname\";s:6:\"摘要\";s:7:\"autoset\";s:8:\"abstract\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:4:\"text\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:8:\"textarea\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:80;s:4:\"rows\";i:8;}s:7:\"content\";a:21:{s:4:\"name\";s:7:\"content\";s:6:\"dbname\";s:6:\"正文\";s:7:\"autoset\";s:14:\"contentdb_page\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:4:\"text\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:5:\"input\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:4:\"html\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"rows\";i:22;}s:8:\"class_id\";a:20:{s:4:\"name\";s:8:\"class_id\";s:6:\"dbname\";s:8:\"栏目ID\";s:7:\"autoset\";s:8:\"class_id\";s:5:\"iskey\";b:1;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:3:\"int\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:11;s:9:\"inputtype\";s:6:\"select\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;}s:10:\"class_name\";a:20:{s:4:\"name\";s:10:\"class_name\";s:6:\"dbname\";s:12:\"栏目名称\";s:7:\"autoset\";s:10:\"class_name\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"varchar\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:6:\"hidden\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;}s:8:\"filepath\";a:21:{s:4:\"name\";s:8:\"filepath\";s:6:\"dbname\";s:18:\"文件存放路径\";s:7:\"autoset\";s:8:\"filepath\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"varchar\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:5:\"input\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:8:\"filepath\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:30;}s:8:\"filename\";a:21:{s:4:\"name\";s:8:\"filename\";s:6:\"dbname\";s:9:\"文件名\";s:7:\"autoset\";s:8:\"filename\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"varchar\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:5:\"input\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:8:\"filename\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:16;}s:9:\"iscommend\";a:21:{s:4:\"name\";s:9:\"iscommend\";s:6:\"dbname\";s:12:\"是否推荐\";s:7:\"autoset\";s:9:\"iscommend\";s:5:\"iskey\";b:1;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"tinyint\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:1;s:9:\"inputtype\";s:6:\"select\";s:7:\"default\";s:1:\"0\";s:9:\"candidate\";s:137:\"0|不推荐\r\n1|1级推荐\r\n2|2级推荐\r\n3|3级推荐\r\n4|4级推荐\r\n5|5级推荐\r\n6|6级推荐\r\n7|7级推荐\r\n8|8级推荐\r\n9|9级推荐\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:3:\"int\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:1;}s:4:\"sssf\";a:21:{s:4:\"name\";s:4:\"sssf\";s:6:\"dbname\";s:18:\"撒旦法撒旦法\";s:7:\"autoset\";s:6:\"writer\";s:5:\"iskey\";b:0;s:6:\"isonly\";b:0;s:8:\"isnonull\";b:0;s:8:\"istofile\";b:0;s:4:\"type\";s:7:\"varchar\";s:7:\"usehtml\";i:0;s:4:\"html\";s:0:\"\";s:3:\"adv\";a:1:{s:2:\"_g\";a:8:{s:4:\"flag\";N;s:4:\"name\";N;s:4:\"type\";s:5:\"input\";s:3:\"num\";s:1:\"0\";s:9:\"editwidth\";N;s:5:\"isadd\";i:1;s:5:\"isdel\";i:1;s:7:\"isorder\";i:1;}}s:6:\"length\";i:255;s:9:\"inputtype\";s:5:\"input\";s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:5:\"class\";s:0:\"\";s:5:\"other\";s:0:\"\";s:6:\"format\";s:6:\"string\";s:7:\"comment\";s:0:\"\";s:9:\"editwidth\";N;s:4:\"size\";i:20;}}}',1,0,0,0,0,NULL,0);

/*Table structure for table `mycms_[member_group]` */

DROP TABLE IF EXISTS `mycms_[member_group]`;

CREATE TABLE `mycms_[member_group]` (
  `id` int(11) NOT NULL auto_increment COMMENT '用户组ID',
  `groupname` varchar(255) default NULL COMMENT '用户组名称',
  `competence` text COMMENT '权限内容',
  `class` text COMMENT '栏目ID',
  `db` text COMMENT '数据表名称',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `mycms_[member_group]` */

/*Table structure for table `mycms_[model]` */

DROP TABLE IF EXISTS `mycms_[model]`;

CREATE TABLE `mycms_[model]` (
  `id` int(11) NOT NULL auto_increment,
  `modelname` varchar(50) default NULL,
  `isuse` tinyint(1) default '1',
  `dbname` varchar(50) default NULL,
  `config` text,
  `isdefault` tinyint(1) default '0',
  `myorder` int(10) default '0',
  `siteid` int(10) default '0',
  PRIMARY KEY  (`id`),
  KEY `myorder` (`myorder`),
  KEY `isuse` (`isuse`),
  KEY `siteid` (`siteid`),
  KEY `dbname` (`dbname`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[model]` */

insert  into `mycms_[model]`(`id`,`modelname`,`isuse`,`dbname`,`config`,`isdefault`,`myorder`,`siteid`) values (1,'新闻模型',1,'default/news','a:8:{s:6:\"dbname\";N;s:9:\"adminlist\";a:4:{s:11:\"sys_commend\";a:4:{s:4:\"name\";s:6:\"评论\";s:5:\"isuse\";i:0;s:5:\"class\";s:4:\"btns\";s:6:\"target\";s:0:\"\";}s:8:\"sys_view\";a:4:{s:4:\"name\";s:6:\"查看\";s:5:\"isuse\";i:0;s:5:\"class\";s:4:\"btns\";s:6:\"target\";s:0:\"\";}s:8:\"sys_edit\";a:4:{s:4:\"name\";s:6:\"修改\";s:5:\"isuse\";i:1;s:5:\"class\";s:4:\"btns\";s:6:\"target\";s:0:\"\";}s:7:\"sys_del\";a:4:{s:4:\"name\";s:6:\"删除\";s:5:\"isuse\";i:1;s:5:\"class\";s:4:\"btns\";s:6:\"target\";N;}}s:9:\"adminedit\";a:3:{s:3:\"add\";N;s:4:\"edit\";N;s:3:\"del\";N;}s:5:\"field\";a:13:{s:2:\"id\";a:0:{}s:5:\"title\";a:9:{s:6:\"dbname\";s:9:\"标题123\";s:5:\"input\";b:1;s:6:\"editor\";b:1;s:4:\"view\";b:1;s:7:\"notnull\";b:1;s:5:\"caiji\";b:1;s:6:\"search\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:6:\"isshow\";a:5:{s:5:\"input\";b:1;s:6:\"editor\";b:1;s:5:\"caiji\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:5:\"image\";a:6:{s:5:\"input\";b:1;s:6:\"editor\";b:1;s:4:\"view\";b:1;s:5:\"caiji\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:8:\"abstract\";a:7:{s:3:\"tag\";s:6:\"高级\";s:5:\"input\";b:1;s:6:\"editor\";b:1;s:5:\"caiji\";b:1;s:6:\"search\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:7:\"content\";a:6:{s:5:\"input\";b:1;s:6:\"editor\";b:1;s:4:\"view\";b:1;s:5:\"caiji\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:8:\"class_id\";a:0:{}s:10:\"class_name\";a:0:{}s:8:\"filepath\";a:3:{s:4:\"view\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:8:\"filename\";a:3:{s:4:\"view\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:9:\"iscommend\";a:6:{s:5:\"input\";b:1;s:6:\"editor\";b:1;s:4:\"view\";b:1;s:5:\"jiehe\";b:1;s:4:\"list\";b:1;s:7:\"content\";b:1;}s:8:\"#special\";a:1:{s:6:\"dbname\";s:12:\"所属专题\";}s:8:\"#comment\";a:1:{s:6:\"dbname\";s:12:\"是否评论\";}}s:9:\"field_set\";N;s:5:\"dbset\";a:1:{s:8:\"abstract\";a:5:{s:4:\"type\";s:12:\"basehtmlarea\";s:3:\"set\";a:4:{s:5:\"class\";s:0:\"\";s:4:\"size\";s:2:\"80\";s:4:\"rows\";s:1:\"8\";s:5:\"other\";s:0:\"\";}s:7:\"default\";s:0:\"\";s:9:\"candidate\";s:0:\"\";s:6:\"format\";s:4:\"html\";}}s:4:\"list\";N;s:6:\"nolist\";N;}',0,0,0);

/*Table structure for table `mycms_[mydata]` */

DROP TABLE IF EXISTS `mycms_[mydata]`;

CREATE TABLE `mycms_[mydata]` (
  `id` int(11) NOT NULL auto_increment COMMENT '信息ID',
  `modelid` int(10) default NULL COMMENT '模板ID',
  `dbname` varchar(255) default NULL COMMENT '数据表名称',
  `classid` int(10) default NULL COMMENT '栏目ID',
  `is_use` tinyint(1) NOT NULL default '1' COMMENT '是否使用',
  `is_hot` tinyint(1) default NULL COMMENT '是否热门',
  `isheadlines` tinyint(1) default NULL COMMENT '是否头条',
  `ontop` tinyint(1) default NULL COMMENT '是否置顶',
  `commend` tinyint(1) default NULL COMMENT '是否推荐',
  `is_indexshow` tinyint(1) default NULL COMMENT '是否首页显示',
  `myorder` int(10) default NULL COMMENT '排序',
  `name` varchar(255) default NULL COMMENT '任务名称',
  `limit` int(11) default NULL COMMENT '条数',
  `start_number` varchar(11) default NULL COMMENT '数据开始数',
  `sql` text COMMENT 'sql语句',
  `list_byfield` varchar(100) default NULL COMMENT '按字段排序',
  `list_orderby` varchar(6) default NULL COMMENT '排序方式',
  `classname` varchar(255) default NULL COMMENT '栏目名称',
  `modelname` varchar(255) default NULL COMMENT '模板名称',
  `cache_time` int(11) default NULL COMMENT '缓存时间',
  `table_config` varchar(255) NOT NULL COMMENT '数据表配置',
  `type` tinyint(1) NOT NULL default '0' COMMENT '类型',
  `var_name` varchar(255) default NULL COMMENT '变量名称',
  `template_id` varchar(100) default NULL COMMENT '区块模板',
  `cate` varchar(50) default NULL COMMENT '模板分类',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[mydata]` */

/*Table structure for table `mycms_[mylink]` */

DROP TABLE IF EXISTS `mycms_[mylink]`;

CREATE TABLE `mycms_[mylink]` (
  `id` int(11) NOT NULL auto_increment COMMENT '链接ID',
  `mydata_id` int(11) default NULL COMMENT '数据调用ID',
  `name` varchar(255) default NULL COMMENT '链接名字',
  `mydata_title` varchar(255) default NULL COMMENT '标题对应的字段',
  `mydata_limit` int(11) default NULL COMMENT '运行的条数',
  `mydata_target` varchar(255) default NULL COMMENT '打开方式',
  `mydata_order` int(10) default NULL COMMENT '子链接排序',
  `myorder` int(10) default NULL COMMENT '排序',
  `count` int(11) default '0' COMMENT '子链接数目',
  `is_use` tinyint(1) NOT NULL default '1' COMMENT '是否使用',
  `content` text COMMENT '所有链接信息',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[mylink]` */

/*Table structure for table `mycms_[site]` */

DROP TABLE IF EXISTS `mycms_[site]`;

CREATE TABLE `mycms_[site]` (
  `id` int(11) NOT NULL auto_increment,
  `sitename` varchar(255) default NULL,
  `siteurl` varchar(255) default NULL,
  `sitehost` varchar(255) default NULL,
  `myorder` int(10) default NULL,
  `content` text,
  `config` text,
  `isuse` smallint(1) default NULL,
  `db` text,
  `model` text,
  `class` text,
  PRIMARY KEY  (`id`),
  KEY `myorder` (`myorder`),
  KEY `isuse` (`isuse`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `mycms_[site]` */

insert  into `mycms_[site]`(`id`,`sitename`,`siteurl`,`sitehost`,`myorder`,`content`,`config`,`isuse`,`db`,`model`,`class`) values (3,'新闻站','/news/','',0,'','a:2:{s:14:\"template_group\";s:7:\"default\";s:9:\"indexpage\";a:4:{s:5:\"isuse\";i:1;s:8:\"filename\";s:10:\"index.html\";s:8:\"filepath\";s:5:\"news/\";s:3:\"tpl\";s:1:\"5\";}}',1,NULL,NULL,NULL);

/*Table structure for table `mycms_[special]` */

DROP TABLE IF EXISTS `mycms_[special]`;

CREATE TABLE `mycms_[special]` (
  `sid` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(50) default '',
  `classides` text,
  `filepath` varchar(50) default NULL,
  `hostname` varchar(50) default NULL,
  `cate` char(10) default NULL,
  `thumb` varchar(255) default NULL,
  `keyword` varchar(50) default '',
  `description` varchar(255) default '',
  `cover_tplid` int(11) default '0',
  `list_tplid` int(11) default '0',
  `list_count` int(11) default '0',
  `list_pernum` int(11) default '20',
  `islist` tinyint(1) default '0',
  `iscover` tinyint(1) default '0',
  `cover_tohtml` tinyint(1) default '0',
  `list_tohtml` tinyint(1) default '0',
  `manage_pernum` int(11) default '20',
  `myorder` int(11) default '0',
  `isnothtml` tinyint(1) default '0',
  `htmlintro` varchar(255) default NULL,
  `cover_cachetime` int(11) default '0',
  `list_cachetime` int(11) default '0',
  `list_filename` varchar(50) default '',
  `cover_filename` varchar(50) default 'index.html',
  `list_orderby` varchar(50) default 'id',
  `list_byfield` varchar(50) default 'ASC',
  `isrecursion` int(1) default '1',
  PRIMARY KEY  (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `mycms_[special]` */

/*Table structure for table `mycms_[special_info]` */

DROP TABLE IF EXISTS `mycms_[special_info]`;

CREATE TABLE `mycms_[special_info]` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `infoid` int(11) NOT NULL default '0',
  `dbname` varchar(20) NOT NULL default '',
  `title` varchar(50) default '',
  `imagenews` varchar(255) default '',
  `linkurl` varchar(255) default '',
  `class_id` int(11) default '0',
  `class_name` varchar(20) default '',
  `createtime` int(11) default '0',
  `posttime` int(11) default '0',
  `isshow` tinyint(1) default '1',
  `isheadlines` tinyint(1) default '0',
  `ontop` tinyint(1) default '0',
  `ishot` tinyint(1) default '0',
  `iscommend` tinyint(1) default '0',
  `url` varchar(255) default '',
  `myorder` int(11) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `sid` (`sid`,`infoid`,`dbname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `mycms_[special_info]` */

/*Table structure for table `mycms_[tasks]` */

DROP TABLE IF EXISTS `mycms_[tasks]`;

CREATE TABLE `mycms_[tasks]` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL COMMENT '任务名称',
  `cate` varchar(50) default NULL COMMENT '任务类别',
  `isuse` tinyint(1) default '0' COMMENT '是否启用',
  `starttime` int(11) default NULL COMMENT '开始时间',
  `endtime` int(11) default NULL COMMENT '结束时间',
  `cycletype` tinyint(1) default NULL COMMENT '1秒2分3时4日5周6月7年8次数',
  `cycle` varchar(255) default NULL COMMENT '间隔数|分割',
  `userid` int(11) default NULL COMMENT '任务执行人',
  `taskfile` varchar(255) default NULL COMMENT '任务脚本',
  `taskmode` varchar(50) default NULL COMMENT '任务方式',
  `maxtimes` int(11) default '0' COMMENT '最大执行次数',
  `fin_times` int(11) default '0' COMMENT '完成次数',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[tasks]` */

/*Table structure for table `mycms_[template]` */

DROP TABLE IF EXISTS `mycms_[template]`;

CREATE TABLE `mycms_[template]` (
  `id` int(11) NOT NULL auto_increment,
  `tplname` varchar(50) default NULL,
  `group` varchar(200) default NULL,
  `type` varchar(20) default NULL,
  `isuse` tinyint(1) default NULL,
  `myorder` int(6) default NULL,
  `cate` varchar(100) default NULL,
  `content` longtext,
  `filename` varchar(200) default NULL,
  `filename_suffix` varchar(20) default NULL,
  `filemtime` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `myorder` (`myorder`),
  KEY `isuse` (`isuse`),
  KEY `group` (`group`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[template]` */

insert  into `mycms_[template]`(`id`,`tplname`,`group`,`type`,`isuse`,`myorder`,`cate`,`content`,`filename`,`filename_suffix`,`filemtime`) values (1,'页面头部','default','block',1,0,'默认','<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n<title><?php echo $title;?> - 麦琪网</title>\r\n<link href=\"/images/skins/web/style.css\" rel=\"stylesheet\" type=\"text/css\" />\r\n</head>\r\n<body>\r\n<div class=\"mainWidth\">\r\n	<div class=\"top_div\">\r\n		<div id=\"logo_div\"><a href=\"/\"><img src=\"/images/logo.gif\" width=\"128\" height=\"38\" alt=\"Myqee\" /></a></div>\r\n		<div id=\"top_right\"><strong>新闻</strong> 博客 论坛 评论</div>\r\n	</div>\r\n	<div id=\"banner_div\">\r\n		<ul class=\"ul menu\">\r\n			<li class=\"now\"><a href=\"/\">首页</a></li>\r\n			<li><a href=\"/news/\">资讯</a></li>\r\n			<li><a href=\"#\">下载</a></li>\r\n			<li><a href=\"#\">图片</a></li>\r\n			<li><a href=\"#\">博客</a></li>\r\n			<li><a href=\"#\">影视</a></li>\r\n			<li><a href=\"#\">招聘</a></li>\r\n		</ul>\r\n		<div id=\"banner_search\">搜索<input type=\"text\" name=\"keyword\" class=\"input\" />\r\n			<input type=\"submit\" class=\"submit\" onclick=\"this.blur();\" value=\"  \" />\r\n		</div>\r\n		<div id=\"banner_user\"> <a href=\"#\">会员</a> <a href=\"#\">设置</a> <a href=\"#\">退出</a> </div>\r\n	</div>\r\n</div>\r\n\r\n<div class=\"clear height\"></div>','header','.php',1255654726),(2,'首页','default','cover',1,0,'默认','<?php\r\n$this->view(\'header\',array(\'title\'=>\'首页\'));\r\n?>\r\n\r\n<div class=\"mainWidth\">\r\n	<div class=\"mainLeft\">\r\n		<div id=\"download_div\">\r\n<?php $this->block(\'index\',1);?>\r\n<?php \r\n\r\n//$this->block(\'index\',3);\r\n?>\r\n		</div>\r\n		<div class=\"clear height\"></div>\r\n		<div class=\"box_1\">\r\n			<div class=\"title\"><font color=\"#bc2d09\">每日</font>推荐</div>\r\n			<div class=\"main\" style=\"height:192px;overflow:hidden;\">\r\n<?php $data = $this->block(\'index\',2);?>\r\n			</div>\r\n		</div>\r\n	</div>\r\n	<div class=\"mainRight\">\r\n		<div id=\"headline_div\">\r\n			<div class=\"headline_text\">头条回顾</div>\r\n			<h1><a href=\"#\">网站头条新闻网abc闻网站头条新闻</a></h1>\r\n			<tt>[<a href=\"#\">链接一</a>][<a href=\"#\">链接二</a>][<a href=\"#\">链接三</a>][<a href=\"#\">链接三链接三</a>][<a href=\"#\">链接三链接三</a>]</tt>\r\n		</div>\r\n		\r\n		<div class=\"clear height\"></div>\r\n		<div style=\"float:left;width:350px;padding:5px;border-bottom:1px dashed #ccc;height:234px;overflow:hidden;\">\r\n			<ul class=\"ul list_1 link_1\">\r\n				<li><a href=\"#\">我国明确负面信用记录最长保留7年</a></li>\r\n				<li><a href=\"#\">中俄签署弹道导弹发射通报等12项协定</a></li>\r\n				<li><a href=\"#\">有效解决灰色清关问题</a></li>\r\n				<li><a href=\"#\">吕正操逝世 系最后一位\\辞世开国上将</a></li>\r\n				<li><a href=\"#\">中俄签署弹道导弹发射通报等12项协定</a></li>\r\n				<li><a href=\"#\">有效解决灰色清关问题</a></li>\r\n				<li><a href=\"#\">吕正操逝世 系最后一位\\辞世开国上将</a></li>\r\n				<li><a href=\"#\">我国明确负面信用记录最长保留7年</a></li>\r\n				<li><a href=\"#\">中俄签署弹道导弹发射通报等12项协定</a></li>\r\n			</ul>\r\n		</div>\r\n		<div style=\"float:right;width:340px;background:#ccc;height:245px;\">\r\n			<noscript>不支持JavaScript</noscript>\r\n			<script type=\"text/javascript\">\r\n			</script>\r\n		</div>\r\n	</div>\r\n</div>\r\n\r\n\r\n\r\n<div class=\"clear height\"></div>\r\n\r\n<div class=\"mainWidth\">\r\n	<div class=\"webmain_title title_blog\">\r\n		<a href=\"#\" class=\"title\">博客</a>\r\n		<a href=\"#\">娱乐</a> | <a href=\"#\">时尚</a> | <a href=\"#\">娱乐</a> | <a href=\"#\">时尚</a> | <a href=\"#\">娱乐</a> | <a href=\"#\">时尚</a>\r\n		<a href=\"#\" class=\"more\">【更多】</a>\r\n	</div>\r\n	<div class=\"clear height\"></div>\r\n	\r\n	<div class=\"mainLeft\">\r\n		<div class=\"clear\"></div>\r\n		<div class=\"box_1\">\r\n			<div class=\"title\"><font color=\"#bc2d09\">博客</font>排行</div>\r\n			<div class=\"main\" style=\"padding:3px\">\r\n				<ul class=\"ul list_paihang\">\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li onmousemove=\"this.style.backgroundColor=\'#ececec\';\" onmouseout=\"this.style.backgroundColor=\'\';\"><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n				</ul>\r\n				<div class=\"clear\"></div>\r\n				<div style=\"padding:10px 10px 0 0;height:18px;font-family:\'宋体\'\">\r\n					<a href=\"#\" class=\"more\">更多&gt;&gt;</a>\r\n				</div>\r\n			</div>\r\n		</div>\r\n	</div>\r\n	\r\n	\r\n	\r\n	\r\n	<div class=\"mainRight\">\r\n		<div class=\"box_1\">\r\n			<div class=\"title\"><font color=\"#bc2d09\">精彩博客</font>推荐</div>\r\n			<div class=\"main\" style=\"padding:3px\">\r\n				<div style=\"float:left;width:350px;border-right:1px dashed #ccc;\">\r\n					<ul class=\"ul list_2\" style=\"padding:2px 12px;\">\r\n						<li><a href=\"#\" style=\"color:#b00\">[汽车]</a> <a href=\"#\">福特在美再次召回450万辆汽车</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">09全国青少年摄影大赛作品</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[汽车]</a> <a href=\"#\">福特在美再次召回450万辆汽车</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">09全国青少年摄影大赛作品</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n					</ul>\r\n				</div>\r\n				<div style=\"float:right;width:350px;\">\r\n					<ul class=\"ul list_2\" style=\"padding:2px 12px;\">\r\n						<li><a href=\"#\" style=\"color:#b00\">[汽车]</a> <a href=\"#\">福特在美再次召回450万辆汽车</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">09全国青少年摄影大赛作品</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[汽车]</a> <a href=\"#\">福特在美再次召回450万辆汽车</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">09全国青少年摄影大赛作品</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n						<li><a href=\"#\" style=\"color:#b00\">[娱乐]</a> <a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a> <a href=\"#\" class=\"more\">姓名一</a></li>\r\n					</ul>\r\n				</div>\r\n				<div class=\"clear\"></div>\r\n			</div>\r\n		</div>\r\n		<div class=\"clear height\"></div>\r\n		<div class=\"box_1\">\r\n			<div class=\"title\">\r\n			<font color=\"#bc2d09\">图片博客</font>推荐\r\n			<span class=\"title_more link_2\"><a href=\"#\">图片博客图片库</a> | <a href=\"#\">体育图片</a> | <a href=\"#\">娱乐图片</a> | <a href=\"#\">动漫图片</a> | <a href=\"#\">游戏图片</a></span>\r\n			</div>\r\n			<div class=\"clear\"></div>\r\n			<div class=\"main\" style=\"padding:3px\">\r\n				<ul class=\"ul list_image\">\r\n					<li>\r\n						<img src=\"images/logo.gif\" class=\"image\" />\r\n						<ol><a href=\"#\">标题标题标题标题</a></ol>\r\n					</li>\r\n					<li>\r\n						<img src=\"images/logo.gif\" class=\"image\" />\r\n						<ol><a href=\"#\">标题标题标题标题</a></ol>\r\n					</li>\r\n					<li>\r\n						<img src=\"images/logo.gif\" class=\"image\" />\r\n						<ol><a href=\"#\">标题标题标题标题</a></ol>\r\n					</li>\r\n					<li>\r\n						<img src=\"images/logo.gif\" class=\"image\" />\r\n						<ol><a href=\"#\">标题标题标题标题</a></ol>\r\n					</li>\r\n					<li>\r\n						<img src=\"images/logo.gif\" class=\"image\" />\r\n						<ol><a href=\"#\">标题标题标题标题</a></ol>\r\n					</li>\r\n					<li>\r\n						<img src=\"images/logo.gif\" class=\"image\" />\r\n						<ol><a href=\"#\">标题标题标题标题</a></ol>\r\n					</li>\r\n				</ul>\r\n				<div class=\"clear\"></div>\r\n			</div>\r\n		</div>\r\n	</div>\r\n</div>\r\n\r\n\r\n\r\n\r\n\r\n<div class=\"clear height\"></div>\r\n\r\n<div class=\"mainWidth\">\r\n	<div class=\"webmain_title title_news\">\r\n		<a href=\"#\" class=\"title\">博客</a>\r\n		<a href=\"#\">娱乐</a> | <a href=\"#\">时尚</a> | <a href=\"#\">娱乐</a> | <a href=\"#\">时尚</a> | <a href=\"#\">娱乐</a> | <a href=\"#\">时尚</a>\r\n		<a href=\"#\" class=\"more\">【更多】</a>\r\n	</div>\r\n	<div class=\"clear height\"></div>\r\n	<div style=\"float:left;width:450px;overflow:hidden;\">\r\n		<div class=\"box_1\">\r\n			<div class=\"title\">\r\n			<font color=\"#bc2d09\">国内要闻</font>\r\n			<span class=\"title_more link_2\"><a href=\"#\">娱乐图片</a> | <a href=\"#\">动漫图片</a> | <a href=\"#\">游戏图片</a></span>\r\n			</div>\r\n			<div class=\"main\" style=\"padding:3px\">\r\n				<div class=\"h1_title\">\r\n					<h1><a href=\"#\">韩国中国韩国中国韩国中国韩国中国</a></h1>\r\n					<p>韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国韩国中国</p>\r\n				</div>\r\n				<ul class=\"ul list_3\" style=\"padding:2px 12px;\">\r\n					<li><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n					<li><a href=\"#\">福特在美再次召回450万辆汽车</a></li>\r\n					<li><a href=\"#\">09全国青少年摄影大赛作品</a></li>\r\n					<li><a href=\"#\">3.6亿大奖致彩票机构公信力遭疑</a></li>\r\n				</ul>\r\n				<div class=\"clear\"></div>\r\n			</div>\r\n		</div>\r\n	</div>\r\n</div>\r\n','index','.php',1257398186),(3,'新闻内容模板','default','content',1,0,'新闻','<?php\r\n$this->view(\'header\',array(\'title\'=>\'首页\'));\r\n\r\n\r\nprint_r($info);\r\n\r\n?>','news_content','.php',1255696400),(4,'新闻列表模板','default','list',1,0,'新闻','<?php\r\n$this->view(\'header\',array(\'title\'=>\'首页\'));\r\n\r\n\r\nprint_r($list);\r\n?>\r\n\r\n\r\n','news_list','.php',1255608806),(5,'新闻首页','default','cover',1,0,'新闻','<?php\r\n$this->view(\'header\',array(\'title\'=>\'首页\'));\r\n\r\n\r\n?>\r\n\r\n\r\n','news_index','.php',1255608844),(6,'首页','smarty','cover',1,0,'默认','{config_load file=test.conf section=\"setup\"}\r\n{include file=\"header.tpl\" title=foo}\r\n\r\n<PRE>\r\n\r\n{* bold and title are read from the config file *}\r\n{if #bold#}<b>{/if}\r\n{* capitalize the first letters of each word of the title *}\r\nTitle: {#title#|capitalize}\r\n{if #bold#}</b>{/if}\r\n\r\naaaaaaaaaa\r\n[!--MyQEE.block(index,2)--]\r\nbbbbbbbbbbddd\r\n\r\nThe current date and time is {$smarty.now|date_format:\"%Y-%m-%d %H:%M:%S\"}\r\n\r\nThe value of global assigned variable $SCRIPT_NAME is {$SCRIPT_NAME}\r\n\r\nExample of accessing server environment variable SERVER_NAME: {$smarty.server.SERVER_NAME}\r\n\r\nThe value of {ldelim}$Name{rdelim} is <b>{$Name}</b>\r\n\r\nvariable modifier example of {ldelim}$Name|upper{rdelim}\r\n\r\n<b>{$Name|upper}</b>\r\n\r\n\r\nAn example of a section loop:\r\n\r\n{section name=outer loop=$FirstName}\r\n{if $smarty.section.outer.index is odd by 2}\r\n	{$smarty.section.outer.rownum} . {$FirstName[outer]} {$LastName[outer]}\r\n{else}\r\n	{$smarty.section.outer.rownum} * {$FirstName[outer]} {$LastName[outer]}\r\n{/if}\r\n{sectionelse}\r\n	none\r\n{/section}\r\n\r\nAn example of section looped key values:\r\n\r\n{section name=sec1 loop=$contacts}\r\n	phone: {$contacts[sec1].phone}<br>\r\n	fax: {$contacts[sec1].fax}<br>\r\n	cell: {$contacts[sec1].cell}<br>\r\n{/section}\r\n<p>\r\n\r\ntesting strip tags\r\n{strip}\r\n<table border=0>\r\n	<tr>\r\n		<td>\r\n			<A HREF=\"{$SCRIPT_NAME}\">\r\n			<font color=\"red\">This is a  test     </font>\r\n			</A>\r\n		</td>\r\n	</tr>\r\n</table>\r\n{/strip}\r\n\r\n</PRE>\r\n\r\nThis is an example of the html_select_date function:\r\n\r\n<form>\r\n{html_select_date start_year=1998 end_year=2010}\r\n</form>\r\n\r\nThis is an example of the html_select_time function:\r\n\r\n<form>\r\n{html_select_time use_24_hours=false}\r\n</form>\r\n\r\nThis is an example of the html_options function:\r\n\r\n<form>\r\n<select name=states>\r\n{html_options values=$option_values selected=$option_selected output=$option_output}\r\n</select>\r\n</form>\r\n\r\n{include file=\"footer.tpl\"}','index','.tpl',1256094117),(7,'header','smarty','cover',1,0,'默认','<HTML>\r\n<HEAD>\r\n{popup_init src=\"/javascripts/overlib.js\"}\r\n<TITLE>{$title} - {$Name}</TITLE>\r\n</HEAD>\r\n<BODY bgcolor=\"#ffffff\">\r\n','header','.tpl',1255696533),(8,'footer','smarty','cover',1,0,'默认','</BODY>\r\n</HTML>','footer','.tpl',1255696553),(9,'test','smarty','cover',1,0,'默认','title = Welcome to Smarty!\r\ncutoff_size = 40\r\n\r\n[setup]\r\nbold = true\r\n','configs/test','.conf',1255697714),(10,'index','test2','cover',1,0,'默认','<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\r\n<html>\r\n<head>\r\n<title>{$title}</title>\r\n<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\r\n<link rel=\"stylesheet\" href=\"{images}/style.css\" type=\"text/css\" />\r\n</head>\r\n\r\n<body>\r\n{$dfds.dfsd.dfdd}\r\n{$_GET.feedid.test.te_st}\r\n<!-- 包含文件 -->\r\n<!-- include \"header.tpl\" -->\r\n\r\n<table width=\"90%\" border=\"0\">\r\n<tr>\r\n	<td width=\"210\">\r\n\r\n	<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse: collapse\" width=\"200\">\r\n	<!-- 定义循环域　如果使用 BEGIN 而非　BEBINB 则在循环中不能使用自带变量 BIT BIT 的值会在0和1之间互换. 用于行间换色等操作 ( 注:BIT在循环中使用不用加上循环域名 ) -->\r\n	<!--{loop $category[Myqee::url(\'dd/d\')] $key $values}-->\r\n	<tr>\r\n		<td style=\"border: 1px solid #111111\" height=\"25\" align=\"left\" bgcolor=\"<!--{if $bit==1}-->#6699FF<!--{else}-->#3399CC<!--{/if}-->\" colspan=\"2\">&nbsp;&nbsp;DDDSDF{$category.name}</td>\r\n	</tr>\r\n	<!--{loop $category[\'sub\'] $key1 $values1}-->\r\n	<tr>\r\n		<td width=\"30\" bgcolor=\"#999933\" style=\"border: 1px solid #111111\"></td><td style=\"border: 1px solid #111111\" height=\"25\" align=\"left\" bgcolor=\"<!--{if $bit==1}-->#FFCCFF<!--{else}-->#FF99FF<!--{/if}-->\">{category.name} ·aa{category.sub.name}</td>\r\n	</tr>\r\n	<!--{/loop}-->\r\n	<!--{/loop}-->\r\n	</table>		\r\n\r\n	</td>\r\n	<td valign=\"top\">\r\n		<table width=\"95%\" align=\"center\">\r\n		<tr>\r\n			<td>\r\n				<!-- $变量名$ 语言标签为直接调用模板文件中的变量的值和变量标签有所不同 -->\r\n				$lang.title$:{$title}#4#<br>\r\n				$lang.contents$:<!-- 在变量标签后面加上#数字# 可以对变量的值进行截取指定个字节长度. -->{$contents}#8#<br><br>\r\n				<!-- 在IF中判断某个 -->\r\n				<!-- IF current_time != \'\' -->$lang.system.nowtime$:<!-- 在变量标签后加上#时间格式# 可以对UNIX时间戳进行格式化 -->{$current_time}#Y年m月d日#<!-- ELSE --><!-- ENDIF -->\r\n			</td>\r\n		</tr>\r\n		</table>\r\n	</td>\r\n</tr>\r\n</table>\r\n\r\n<!-- INC \"footer.tpl\" -->\r\n\r\n</body>\r\n</html>\r\n','index','.tpl',1256187423),(11,'header','test2','cover',1,0,'默认','<img src=\"{images}/logo.gif\"><br>\r\n<hr size=\"1\" color=\"#000000\">','header','.tpl',1256175529),(12,'footer','test2','cover',1,0,'默认','<hr size=\"1\" color=\"#000000\">\r\ncopyright www.mop.com 2006','footer','.tpl',1256175556);

/*Table structure for table `mycms_[uploadfiles]` */

DROP TABLE IF EXISTS `mycms_[uploadfiles]`;

CREATE TABLE `mycms_[uploadfiles]` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `host` varchar(255) default NULL,
  `urlpath` varchar(1000) default NULL,
  `filename` varchar(255) default NULL,
  `suffix` varchar(10) default NULL,
  `size` varchar(255) default NULL,
  `filetype` varchar(20) default NULL,
  `uploadtime` int(11) default NULL,
  `width` int(6) default NULL,
  `height` int(6) default NULL,
  `content` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=264 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `mycms_[uploadfiles]` */

insert  into `mycms_[uploadfiles]`(`id`,`name`,`host`,`urlpath`,`filename`,`suffix`,`size`,`filetype`,`uploadtime`,`width`,`height`,`content`) values (262,'42l8mTWNqeUDzB5Pd0GC','','/upload/2009-10-20/42l8mTWNqeUDzB5Pd0GC.png','in_02.png','png','144108','png',1256006265,NULL,NULL,'in_02.png'),(263,'uz2YkrGvb48EnwNCoXcm','','/upload/2009/10/20/uz2YkrGvb48EnwNCoXcm.jpg','Shell00.jpg','jpg','147323','jpg',1256007259,NULL,NULL,'Shell00.jpg');

/*Table structure for table `mycms_news` */

DROP TABLE IF EXISTS `mycms_news`;

CREATE TABLE `mycms_news` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) default NULL,
  `isshow` tinyint(1) default NULL,
  `abstract` text,
  `content` text,
  `class_id` int(11) default NULL,
  `class_name` varchar(255) default NULL,
  `filepath` varchar(255) default NULL,
  `filename` varchar(255) default NULL,
  `iscommend` tinyint(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `isshow` (`isshow`),
  KEY `class_id` (`class_id`),
  KEY `iscommend` (`iscommend`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Data for the table `mycms_news` */

insert  into `mycms_news`(`id`,`title`,`image`,`isshow`,`abstract`,`content`,`class_id`,`class_name`,`filepath`,`filename`,`iscommend`) values (1,'不炒股不投基 投资11个车库六年稳赚百万元','',1,'“吃不穷，喝不穷，算计不到才是穷。”这是53岁水景文的理财观念。\r\n\r\n他不炒股、不投基，靠投资11个车库，六年稳赚百万元。他认为，停车位的增长速度要远远小于私家车的增长速度，所以，车库市场“钱景乐观”。\r\n','<div>　    “吃不穷，喝不穷，算计不到才是穷。”这是53岁水景文的理财观念。<br />\r\n<br />\r\n他不炒股、不投基，靠投资11个车库，六年稳赚百万元。他认为，停车位的增长速度要远远小于私家车的增长速度，所以，车库市场“钱景乐观”。<br />\r\n<br />\r\n<strong>　6年 买11个车库赚百万</strong><br />\r\n<br />\r\n2002年，水景文在报纸上看到上海居民“先买车位再买车”的新闻报道，敏锐地察觉到“车位投资”市场。随后，他在南塔附近以2500元/㎡的价格一次性购入4个车库。“那时车库的租金只有三四百元，我在债券市场赚了些小钱后，又先后在浑南、和平、东陵等地相继购入9个车库。除去6年中所赚的租金，车库的均价已从当时的2500元/㎡涨到目前的8000元/㎡，租金也涨到七八百元每月。”记者简单算了一笔账，水景文投资车库的资金，五六年中已翻两番。<br />\r\n<br />\r\n       <strong> 卖房 该出手时就出手</strong><br />\r\n<br />\r\n水景文对投资车库很有研究，每购入一个车库，他都要从地理位置、周围经济、人文环境等多方面考虑。而他的投资也不是光进不出，而是该出手时就出手。2003年，他以2300元/㎡的价钱买入一套80㎡的住房。今年4月，以5100元/㎡的价钱顺利出手。对此水景文表示：“这套房子如果出租，每月租金一千元左右，一年下来除去采暖费等费用，也就一万元入账。但如果把房子卖了，拿40万元再去投资，以年利5%计算，一年就能赚2万元。所以我把卖房子的钱投资到银行理财产品上。目前来看，上半年出手房产也是正确时机，卖了就赚了！”<br />\r\n<br />\r\n<strong>理念 不懂的绝对不投</strong><br />\r\n<br />\r\n对于自己的投资渠道，水景文认为是独特的、更是成功的。他说：“据了解，目前沈阳私家车60余万量，而实际停车位只有14万个。我的一个车库最多不超过10天就能租出去，所以这块市场很广阔。”去年，水景文也将少量资金投资了股票、基金，但出于风险考虑，盈利50%后，就毅然离场。<br />\r\n<br />\r\n他总结自己的理财观念，“我属于稳健保守型，不懂的，绝对不投，而且注重实物投资。就像我家的家具都是红木的，两年也增值了40%。在金融危机环境中，很多人开始投资古玩，我也很感兴趣，但由于不懂专业知识，所以我决定要多学习，先从浅显的化石、奇石开始收藏投资。”</div>',3,'国际',NULL,'d02986e8b6cd2415.html',0),(2,'避开反弹中可能存在的“地雷”——大小非','',1,'虽然市场整体处于转暖的过程中,但是我们还是不得不提醒投资者要注意大小非问题。12月份大小非解禁为全年第二个高峰。我们下面将对12月以及明年1月份的大小非解禁股票进行具体分析。','<div>        虽然市场整体处于转暖的过程中,但是我们还是不得不提醒投资者要注意大小非问题。12月份大小非解禁为全年第二个高峰。我们下面将对12月以及明年1月份的大小非解禁股票进行具体分析。<br />\r\n<br />\r\n<strong>解禁股数和解禁市值规模仍较大</strong><br />\r\n<br />\r\n2008年12月份解禁总市值规模有2000多亿元。2009年上半年的月均解禁股票市值在1400亿以上。但从解禁市值上看,与现在的市场状况相比,占比较大。</div>\r\n<div> </div>\r\n<div>       根据解禁股的时间安排表看,2006年底股权分置改革完成了90%,限售期两年以内的股份在2008年底将全部解禁。我们统计了单一股东持股比例 10%的各月解禁股数,从数量看仅占总解禁股数的10%左右。从股东的意愿看,我们认为实际控制人股东的减持意愿较低。从统计上看,具有减持意愿的解禁股份占总解禁股的10%左右。我们认为这一特点在未来的两年中都十分明显。这是与2007、2008年的最大不同之处。<br />\r\n<br />\r\n<strong>　12月份和明年1月份主要解禁公司</strong><br />\r\n<br />\r\n从解禁个股的情况看,2008年12月份有148个解禁。但从解禁的股东结构看,持股比例在25%以上的解禁股份数占比高达69%。2009年 1月份解禁次数112个,持股比例在25%的解禁股东数占到了75%。其中解禁股东持股占比较高的公司具有解禁数值大,但抛售压力较小的特点。从解禁股东类型看,首发股份、增发和定向等占比开始提升,2006年中期到2007年底的新IPO对市场的解禁压力开始出现。<br />\r\n<br />\r\n当市场迎来实际控制人股东的解禁高潮时,如何衡量解禁股可能的市场冲击就变得比较主观。这里我们采取单一股东持股比例20%以下为标准,以解禁股绝对数量为依据进行了排名。<br />\r\n<br />\r\n </div>\r\n<div> </div>\r\n<div> </div>\r\n<div><img src=\"file:///D:/My%20Documents/%E6%A1%8C%E9%9D%A2/%E6%9C%AA%E5%91%BD%E5%90%8D.JPG\" alt=\"\" /></div>\r\n<div><img src=\"file:///C:/DOCUME~1/ADMINI~1/LOCALS~1/Temp/moz-screenshot.jpg\" alt=\"\" /></div>',2,'国内',NULL,'f769d7d7ff20b10e.html',0),(4,'黄金投资：金价突破788美元才能看多','',1,'<p>上周金价曾跌至两周低点740.40美元，因疲弱的美国就业数据引发从股票到石油和商品等资产的全面抛售。 周一东京市场，美元兑主要货币走弱，金价得以反弹至774.65美元。日经指数收涨5.2％，投资者避险情绪缓解。</p>','<div>       上周金价曾跌至两周低点740.40美元，因疲弱的美国就业数据引发从股票到石油和商品等资产的全面抛售。 周一东京市场，美元兑主要货币走弱，金价得以反弹至774.65美元。日经指数收涨5.2％，投资者避险情绪缓解。<br />\r\n<br />\r\n    美国11月非农就业人口创下34年来最大降幅，令原本就已黯淡的消费者支出前景雪上加霜，亦削弱了这个过去10年来最可靠的全球经济增长动力。如果最近几次经济低迷可资借鉴的话，失业率可能要到2010年或甚至是2011年后才会触顶，这意味着全球经济不应冀望美国消费者支出短期内会强劲反弹。经济前景恶化为当选总统奥巴马带来更多压力，要求他推出规模更大的激励计划以遏制经济下滑。<br />\r\n<br />\r\n        本周公布的数据料将表明家庭支出已受到多大的冲击，以及一年来美国经济的衰退已产生多大的扩散效应。美联储将于周四发布的报告料显示，截至9月家庭财富连续第四个季度缩水。当日商务部将发布的另一份报告预计显示，美国贸易逆差缩窄，且出口和进口双双下滑。<br />\r\n<br />\r\n周五的零售销售和生产者物价指数(PPI)也是关注重点，这些报告预计将盖过购物季期间的任何好迹象，进一步证明美国经济的疲软形势。预计11月美国PPI将下滑1.7%，10月降幅为2.8%，同时11月零售销售预计为下滑1.4%。<br />\r\n<br />\r\n消费者支出放缓已抑制美国进口，并推动油价急剧下滑，而全球其它地区经济走软则损及美国出口，加重失业形势，并令恶性循环加快。目前不管是全球的还是国内的经济，都处于一个双向加强的螺旋式下行。<br />\r\n<br />\r\n近两日，交易商的焦点多放在股市走势及美国汽车业三巨头的命运，以从中寻找交投指引。金价这波反弹是否能持续，还需观察能否突破788美元阻力位。如不能有效突破，则交投区间还将陷于760美元附近，甚至下移至740美元下方。</div><div style=\"page-break-after: always\"><span style=\"display: none\"></span></div><div class=\"attributes\">\r\n<ul class=\"attributes-list\">\r\n    <li title=\"货号\">货号: 10903309.zyd</li>\r\n    <li title=\"性别\">性别: 女</li>\r\n    <li title=\"款式\">款式: 肩包</li>\r\n    <li title=\"背包方式\">背包方式: 单肩斜挎</li>\r\n    <li title=\"背包部位\">背包部位: 肩部</li>\r\n    <li title=\"质地\">质地: 牛皮</li>\r\n    <li title=\"皮质特征\">皮质特征: 软面皮</li>\r\n    <li title=\"肩带根数\">肩带根数: 三根</li>\r\n    <li title=\"提拎部件\">提拎部件: 软把</li>\r\n    <li title=\"箱包开袋方式\">箱包开袋方式: 拉链</li>\r\n    <li title=\"内部结构\">内部结构: 夹层拉链袋  证件袋  拉链暗袋 ...</li>\r\n    <li title=\"外袋种类\">外袋种类: 内贴袋</li>\r\n    <li title=\"品牌\">品牌: 浪美</li>\r\n    <li title=\"箱包流行元素\">箱包流行元素: 流苏</li>\r\n    <li title=\"风格\">风格: 甜美淑女</li>\r\n    <li title=\"箱包外形\">箱包外形: 其他</li>\r\n    <li title=\"箱包图案\">箱包图案: 纯色无图案</li>\r\n    <li title=\"颜色\">颜色: 酒红10903309 ...</li>\r\n    <li title=\"有无夹层\">有无夹层: 无</li>\r\n    <li title=\"硬度\">硬度: 软</li>\r\n    <li title=\"有无拉杆\">有无拉杆: 无</li>\r\n    <li title=\"可否折叠\">可否折叠: 否</li>\r\n    <li title=\"有无手腕带\">有无手腕带: 有</li>\r\n    <li title=\"价格区间\">价格区间: 101-500元</li>\r\n    <li title=\"成色\">成色: 全新</li>\r\n</ul>\r\n</div>\r\n<div id=\"J_DivItemDesc\" class=\"content\">\r\n<p><img border=\"0\" align=\"absMiddle\" alt=\"\" src=\"http://img07.taobaocdn.com/imgextra/i7/119662482/T25xteXiAI20NXXXXX_!!119662482.jpg\" usemap=\"#Map\" /><map name=\"Map\">\r\n<area href=\"http://item.taobao.com/auction/item_detail-0db2-32e7a38aea7259fb58dd15120c39eb4b.htm\" shape=\"RECT\" coords=\"395,28,752,141\" /></map></p>\r\n<table width=\"740\" height=\"18000\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\r\n    <tbody>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"757\" alt=\"\" src=\"http://img06.taobaocdn.com/imgextra/i6/119662482/T2YBJeXccd80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"371\" alt=\"\" src=\"http://img03.taobaocdn.com/imgextra/i3/119662482/T2UBJeXksc80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"672\" alt=\"\" src=\"http://img05.taobaocdn.com/imgextra/i5/119662482/T2eRNeXa3g80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"633\" alt=\"\" src=\"http://img02.taobaocdn.com/imgextra/i2/119662482/T2bRNeXfEf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"336\" alt=\"\" src=\"http://img08.taobaocdn.com/imgextra/i8/119662482/T2b8NeXhgf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"550\" alt=\"\" src=\"http://img04.taobaocdn.com/imgextra/i4/119662482/T29BJeXgEe80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"493\" alt=\"\" src=\"http://img01.taobaocdn.com/imgextra/i1/119662482/T2V8JeXosc80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"770\" alt=\"\" src=\"http://img01.taobaocdn.com/imgextra/i1/119662482/T2XRNeXXEf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"502\" alt=\"\" src=\"http://img08.taobaocdn.com/imgextra/i8/119662482/T25RJeXbge80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"737\" alt=\"\" src=\"http://img06.taobaocdn.com/imgextra/i6/119662482/T2f8NeXX7h80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"555\" alt=\"\" src=\"http://img04.taobaocdn.com/imgextra/i4/119662482/T2T8JeXeId80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"771\" alt=\"\" src=\"http://img04.taobaocdn.com/imgextra/i4/119662482/T20RJeXbMe80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"932\" alt=\"\" src=\"http://img05.taobaocdn.com/imgextra/i5/119662482/T21BJeXcse80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"950\" alt=\"\" src=\"http://img04.taobaocdn.com/imgextra/i4/119662482/T28RJeXo.e80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"642\" alt=\"\" src=\"http://img07.taobaocdn.com/imgextra/i7/119662482/T2W8JeXkkd80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"565\" alt=\"\" src=\"http://img07.taobaocdn.com/imgextra/i7/119662482/T2alNeXjsf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"564\" alt=\"\" src=\"http://img02.taobaocdn.com/imgextra/i2/119662482/T2ZRJeXmkd80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"565\" alt=\"\" src=\"http://img06.taobaocdn.com/imgextra/i6/119662482/T24BJeXioe80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"565\" alt=\"\" src=\"http://img01.taobaocdn.com/imgextra/i1/119662482/T23lJeXcZe80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"974\" alt=\"\" src=\"http://img05.taobaocdn.com/imgextra/i5/119662482/T2XBNeXmwf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"646\" alt=\"\" src=\"http://img03.taobaocdn.com/imgextra/i3/119662482/T218JeXjMe80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"792\" alt=\"\" src=\"http://img08.taobaocdn.com/imgextra/i8/119662482/T2.RJeXlMf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"535\" alt=\"\" src=\"http://img05.taobaocdn.com/imgextra/i5/119662482/T218JeXjwe80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"504\" alt=\"\" src=\"http://img05.taobaocdn.com/imgextra/i5/119662482/T2VlJeXkcd80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"723\" alt=\"\" src=\"http://img02.taobaocdn.com/imgextra/i2/119662482/T2UBJeXg.d80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"763\" alt=\"\" src=\"http://img02.taobaocdn.com/imgextra/i2/119662482/T2TBJeXiZd80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"686\" alt=\"\" src=\"http://img06.taobaocdn.com/imgextra/i6/119662482/T2LCFeXkxi_tNXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td><img width=\"740\" height=\"447\" alt=\"\" src=\"http://img01.taobaocdn.com/imgextra/i1/119662482/T2aBNeXmAf80NXXXXX_!!119662482.jpg\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</div>',2,'国内','2009/d10/15','2c0a24b1e1c583f3.html',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;