<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

runquery('DROP TABLE IF EXISTS `cdb_plugin_member_verify`');

runquery('CREATE TABLE IF NOT EXISTS `pre_plugin_member_verify` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`uid` mediumint(8) unsigned DEFAULT NULL,
	`realname` varchar(255) NOT NULL,
	`awardyear` smallint(4) NOT NULL,
	`awardschool` varchar(255) NOT NULL,
	`subserial` tinyint(2) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `awardyear` (`awardyear`,`awardschool`,`subserial`),
	UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

$finish = TRUE;

?>
