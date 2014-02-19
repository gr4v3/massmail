DROP TABLE IF EXISTS `cronJob`;
CREATE TABLE IF NOT EXISTS `cronJob` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique identifier',
  `crontabId` int(11) NOT NULL default '0',
  `startTimestamp` timestamp NOT NULL COMMENT 'start',
  `endTimestamp` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'end of run',
  `code` longtext NOT NULL COMMENT 'PHP code to be executed',
  `concurrent` tinyint(4) NOT NULL default '0' COMMENT 'handles concurrency',
  `implementationId` int(11) NOT NULL,
  `results` longtext NOT NULL COMMENT 'output of script',
  `pid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `startTimestamp` (`startTimestamp`),
  KEY `crontabId` (`crontabId`),
) TYPE=InnoDB AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `crontab`;
CREATE TABLE IF NOT EXISTS `crontab` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `code` longtext NOT NULL,
  `concurrent` tinyint(4) NOT NULL,
  `implementationId` tinyint(4) NOT NULL,
  `cronDefinition` text NOT NULL,
  `lastActualTimestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB AUTO_INCREMENT=1 ;
