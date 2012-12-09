<?php

/*

CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `key_str` varchar(255) NOT NULL,
  `value` longtext,
  `expire_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `expire_time` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

*/



$config = array(
    'default' => array(
        'database' => 'default',
        'tablename' => 'cache',
    ),
);