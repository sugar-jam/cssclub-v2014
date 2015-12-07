
CREATE TABLE IF NOT EXISTS `pre_takashiro_lovewins_couple` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `uid1` mediumint(8) unsigned NOT NULL,
  `uid2` mediumint(8) unsigned NOT NULL,
  `coinnum` smallint(10) unsigned NOT NULL,
  `success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid1` (`uid1`,`uid2`),
  KEY `coinnum` (`coinnum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `pre_takashiro_lovewins_couplelog` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `coupleid` mediumint(8) unsigned NOT NULL,
  `voterid` mediumint(8) unsigned NOT NULL,
  `dateline` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `coupleid` (`coupleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `pre_takashiro_lovewins_love` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `fromid` mediumint(8) unsigned NOT NULL,
  `toid` mediumint(8) unsigned NOT NULL,
  `dateline` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fromid` (`fromid`,`toid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
