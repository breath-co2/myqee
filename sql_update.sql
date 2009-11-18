/* ---------------- 2009-2-24 */
alter table `{{table_prefix}}[class]`
 add column `cover_cachetime` int (10)  NULL  after `cover_tohtml`,
 add column `list_cachetime` int (10)  NULL  after `list_tohtml`,
 add column `content_cachetime` int (10)  NULL  after `content_tohtml`,
 add column `search_cachetime` int (10)  NULL  after `search_orderby`;
 

/* ---------------- 2009-2-25 */
alter table `{{table_prefix}}[dbtable]` add column `usedbmodel` tinyint (1) DEFAULT '0' NULL  after `ismemberdb`; 
alter table `{{table_prefix}}[dbtable]` add column `readbydbname` tinyint (1) DEFAULT '0' NULL  after `ismemberdb`;


/* ---------------- 2009-4-22 */
alter table `{{table_prefix}}[class]` add column `manage_limit` mediumint (3)  NULL  after `isnavshow`;
update `{{table_prefix}}[class]` set `manage_limit` = 20;

/* ---------------- 2009-4-24 */
alter table `{{table_prefix}}[class]` change `search_orderby` `search_orderby` varchar (4)  NULL ;


/* ---------------- 2009-5-12 */
alter table `{{table_prefix}}[class]` change `keyword` `keyword` varchar (255) CHARACTER SET utf8  COLLATE utf8_general_ci   NULL , change `description` `description` varchar (4000) CHARACTER SET utf8  COLLATE utf8_general_ci   NULL; 


/* ---------------- 2009-6-3 */
alter table `{{table_prefix}}[admin_group]` add column `site` text  CHARSET utf8  DEFAULT '0'  NULL  after `competence`;
alter table `{{table_prefix}}[admin]` add column `siteset` text  CHARSET utf8  DEFAULT '0'  NULL  after `auto_classset`;
alter table `{{table_prefix}}[class]` add column `siteid` int (10) DEFAULT '0' NULL  after `classimg`;
alter table `{{table_prefix}}[class]` add index `siteid` (`siteid`);
alter table `{{table_prefix}}[class]` add index `modelid` (`modelid`);

/* ---------------- 2009-6-3 */
alter table `{{table_prefix}}[class]` change `sonclass` `sonclass` varchar (4000)  NULL , change `fatherclass` `fatherclass` varchar (4000)  NULL ;
alter table `{{table_prefix}}[class]` add index `sonclass` (`sonclass`);
alter table `{{table_prefix}}[class]` add index `fatherclass` (`fatherclass`);
alter table `{{table_prefix}}[class]` add index `dbname` (`dbname`);
alter table `{{table_prefix}}[class]` add index `myorder` (`myorder`);

create table `{{table_prefix}}[site]` (  `id` int (11) NOT NULL AUTO_INCREMENT , `sitename` varchar (255) , `siteurl` varchar (255) , `sitehost` varchar (255) , `myorder` int (10) , `content` text , `config` text , `isdefault` smallint (1) , `isuse` smallint (1) , PRIMARY KEY (`id`));  
alter table `{{table_prefix}}[site]` add index `myorder` (`myorder`);
alter table `{{table_prefix}}[site]` add index `isdefault` (`isdefault`);
alter table `{{table_prefix}}[site]` add index `isuse` (`isuse`);

alter table `{{table_prefix}}[admin]` add column `auto_siteset` smallint (1) DEFAULT '1' NULL  after `siteset`,change `auto_classset` `auto_classset` smallint (1) DEFAULT '1' NULL , change `auto_dbset` `auto_dbset` smallint (1) DEFAULT '1' NULL ;



/* ---------------- 2009-6-8 */
alter table `{{table_prefix}}[admin_group]` add column `defaultsite` int (10) DEFAULT '0' NULL  after `competence`;
alter table `{{table_prefix}}[admin]` add column `defaultsite` int (10) DEFAULT '0' NULL  after `auto_classset`;
alter table `{{table_prefix}}[admin]` add column `auto_defaultsite` smallint (1) DEFAULT '1' NULL  after `auto_classset`;
alter table `{{table_prefix}}[model]` add column `siteid` int (10) DEFAULT '0' NULL  after `myorder`;
alter table `{{table_prefix}}[dbtable]` add column `siteid` int (10) DEFAULT '0' NULL  after `modelconfig`;


/* ---------------- 2009-6-9 */
alter table `{{table_prefix}}[dbtable]` add index `siteid` (`siteid`);
alter table `{{table_prefix}}[model]` add index `siteid` (`siteid`);
alter table `{{table_prefix}}[model]` add index `dbname` (`dbname`);


/* ---------------- 2009-6-10 */
alter table `{{table_prefix}}[site]` drop column `isdefault`,add column `db` text   NULL  after `isuse`, add column `model` text   NULL  after `db`, add column `class` text   NULL  after `model`;
alter table `{{table_prefix}}[template]` add index `group` (`group`);


/* ---------------- 2009-6-10 */
alter table `{{table_prefix}}[model]` drop column `siteid`;
alter table `{{table_prefix}}[dbtable]` drop column `siteid`;

/* ---------------- 2009-6-24 */
/*********订阅咨询的classId**********/
alter table `{{table_prefix}}[member]` add column `booknews` tinytext;


/* ---------------- 2009-9-1 */
ALTER TABLE `{{table_prefix}}[acquisition_data]` ADD COLUMN `info_url_md5` VARCHAR(32) NULL COMMENT '信息地址md5' AFTER `info_url`;
ALTER TABLE `{{table_prefix}}[acquisition_data]` ADD INDEX `acqu_id` (`acqu_id`);
ALTER TABLE `{{table_prefix}}[acquisition_data]` ADD INDEX `node_id` (`node_id`);
ALTER TABLE `{{table_prefix}}[acquisition_data]` ADD INDEX `info_url_md5` (`info_url_md5`);
ALTER TABLE `{{table_prefix}}[acquisition_data]` ADD INDEX `is_todb` (`is_todb`);
ALTER TABLE `{{table_prefix}}[acquisition_data]` ADD COLUMN `dotime` VARCHAR(25) NULL AFTER `model_id`;
ALTER TABLE `{{table_prefix}}[template]` ADD COLUMN `filemtime` INT(10) NULL AFTER `filename_suffix`;
ALTER TABLE `{{table_prefix}}[custompage]` ADD COLUMN `edit_type` SMALLINT(1) NULL AFTER `param`;



/* ---------------- 2009-9-9 */

CREATE TABLE `{{table_prefix}}[block]` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `type` VARCHAR(100) DEFAULT NULL,
  `no` INT(10) DEFAULT NULL,
  `isuse` SMALLINT(1) DEFAULT '1',
  `myorder` INT(10) DEFAULT NULL,
  `show_type` SMALLINT(1) DEFAULT NULL,
  `content` TEXT,
  `varname` VARCHAR(200) DEFAULT NULL COMMENT '变量名',
  `len` INT(10) DEFAULT '10',
  `tpl_id` INT(11) DEFAULT '0',
  `mydata_id` INT(11) DEFAULT '0',
  `cache_time` INT(10) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `no` (`no`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8

/* ---------------- 2009-10-16 */
ALTER TABLE `{{table_prefix}}[special]` ADD COLUMN `cover_filename` VARCHAR(50) DEFAULT 'index.html' ;
ALTER TABLE `{{table_prefix}}[special]` ADD COLUMN `list_orderby` VARCHAR(50) DEFAULT 'id';
ALTER TABLE `{{table_prefix}}[special]` ADD COLUMN `list_byfield` VARCHAR(50) DEFAULT 'ASC';
ALTER TABLE `{{table_prefix}}[special]` ADD COLUMN `isrecursion` int (1) DEFAULT 1;


/* ---------------- 2009-10-19 */
ALTER TABLE `{{table_prefix}}[block]` ADD COLUMN `advfield` TEXT NULL AFTER `cache_time`; 



/* ---------------- 2009-11-02 */
ALTER TABLE `{{table_prefix}}[block]` ADD COLUMN `template` TEXT NULL AFTER `tpl_id`;
ALTER TABLE `{{table_prefix}}[block]` ADD COLUMN `tpl_engie` VARCHAR(200) NULL AFTER `tpl_id`;



