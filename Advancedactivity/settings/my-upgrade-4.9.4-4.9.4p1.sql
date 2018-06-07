-- --------------------------------------------------------
--
-- Table structure for table `engine4_advancedactivity_targets`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_targets` (
  `action_id` int(11) NOT NULL,
  `min_age` int(3) NOT NULL,
  `max_age` int(3) NOT NULL,
  `gender` varchar(10) NOT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_pinsettings`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_pinsettings` (
  `pinsetting_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `reset_date` datetime NOT NULL,
  `object_type` varchar(100) NOT NULL,
   PRIMARY KEY (`pinsetting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_feelingtypes`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_feelingtypes` (
  `feelingtype_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `photo_id` int(10) unsigned NOT NULL DEFAULT '0',
  `enabled` int(10) unsigned NOT NULL DEFAULT '1',
  `order` int(11) unsigned NOT NULL DEFAULT '999',
  `type` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `default` tinyint(4) NOT NULL,
  `tagline` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `params` TEXT NULL,
   PRIMARY KEY (`feelingtype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_words`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_words` (
  `word_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `color` varchar(10) NOT NULL,
  `background_color` varchar(10) NOT NULL,
  `style` varchar(10) NOT NULL,
  `params` TEXT NULL,
    PRIMARY KEY (`word_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `engine4_advancedactivity_words`
--

INSERT IGNORE INTO `engine4_advancedactivity_words` (`title`, `color`, `background_color`, `style`, `params`) VALUES
('Happy New Year', '#4f39e3', '#FFFFFF', 'normal', '{"animation":"background-happy-new-year","bg_enabled":"0"}'),
('Happy Birthday', '#09961e', '#ffffff', 'normal', '{"animation":"background-happy-birthday","bg_enabled":"0"}'),
('Merry Christmas', '#a1361f', '#FFFFFF', 'normal', '{"animation":"background-merry-christmas","bg_enabled":"0"}'),
('Congratulations', '#0fd159', '#FFFFFF', 'normal', '{"animation":"background-congratulations","bg_enabled":"0"}'),
('Happy Easter', '#0bb0b3', '#FFFFFF', 'normal', '{"animation":"background-happy-easter","bg_enabled":"0"}'),
('Happy Thanksgiving', '#b5aa09', '#FFFFFF', 'normal', '{"animation":"background-happy-thanksgiving","bg_enabled":"0"}');

 INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES ('advancedactivity.share.options.0','timeline');



-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_feelings`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_feelings` (
  `feeling_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feelingtype_id` int(11) unsigned NOT NULL,
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order` int(11) unsigned NOT NULL DEFAULT '999',
  `params` TEXT NULL,
   PRIMARY KEY (`feeling_id`)
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_greetings`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_greetings` (
  `greeting_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `repeat` tinyint(4) NOT NULL,
  `params` TEXT NULL,
  PRIMARY KEY (`greeting_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Table structure for table `engine4_advancedactivity_notificationsettings`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_notificationsettings` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_ids` varchar(3000) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES		
('advancedactivity_admin_main_activity_faq', 'advancedactivity', 'Activity Feed', '', '{"route":"admin_default","module":"advancedactivity","controller":"settings" ,"action":"faq"}', 'advancedactivity_admin_main_faq', '', 1);


-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_sells`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_sells` (
  `sell_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(1000) NOT NULL,
  `description` longtext DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `date` datetime,
  `place` varchar(3000) NOT NULL,
  `price` int(11) NOT NULL,
  `photo_id` varchar(1000) NOT NULL,
  `params` TEXT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `closed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_sell_fields_maps`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_sell_fields_maps` (
  `field_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `order` smallint(6) NOT NULL,
  PRIMARY KEY (`field_id`,`option_id`,`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_sell_fields_meta`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_sell_fields_meta` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `display` tinyint(1) unsigned NOT NULL,
  `search` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `order` smallint(3) unsigned NOT NULL DEFAULT '999',
  `config` text COLLATE utf8_unicode_ci NOT NULL,
  `validators` text COLLATE utf8_unicode_ci,
  `filters` text COLLATE utf8_unicode_ci,
  `style` text COLLATE utf8_unicode_ci,
  `error` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------


--
-- Table structure for table `engine4_advancedactivity_sell_fields_options`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_sell_fields_options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `field_id` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` smallint(6) NOT NULL DEFAULT '999',
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_sell_fields_search`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_sell_fields_search` (
  `item_id` int(11) NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_sell_fields_values`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_sell_fields_values` (
  `item_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `index` smallint(3) NOT NULL DEFAULT '0',
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`item_id`,`field_id`,`index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`, `is_object_thumb`) VALUES
('post_self_sell', 'advancedactivity', '{item:$subject} {body:$body}', 1, 7, 1, 1, 1, 1, 0, 0),
('post_sell', 'advancedactivity', '{actors:$subject:$object} {body:$body}', 1, 7, 1, 1, 1, 1, 0, 0);


INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`, `processes`, `semaphore`, `started_last`, `started_count`, `completed_last`, `completed_count`, `failure_last`, `failure_count`, `success_last`, `success_count`) VALUES
('Reset pinned & mismatch Feed and scheduled post', 'advancedactivity', 'Advancedactivity_Plugin_Task_Feed', 180, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_banners`
--

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_banners` (
  `banner_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `file_id` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `background_color` varchar(30) DEFAULT NULL,
  `gradient` varchar(200) DEFAULT NULL,
  `order` tinyint(4) NOT NULL,
  `highlighted` tinyint(4) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


ALTER TABLE `engine4_activity_actions` CHANGE `body` `body` BLOB NULL DEFAULT NULL;
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` ='core_admin_main_plugins_sitehashtag' AND `engine4_core_menuitems`.`module` = 'sitehashtag';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` ='core_admin_main_plugins_sitereaction' AND `engine4_core_menuitems`.`module` = 'sitereaction';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` ='core_admin_main_plugins_sitetagcheckin' AND `engine4_core_menuitems`.`module` = 'sitetagcheckin';
INSERT IGNORE INTO `engine4_advancedactivity_contents` (`module_name`, `filter_type`, `resource_title`, `content_tab`, `order`, `default`) VALUES 
('advancedactivity', 'schedule_post', 'Scheduled Posts', '1', '7', '0'),
('advancedactivity', 'hidden_post', 'Hidden Posts', '1', '8', '0'),
('advancedactivity', 'memories', 'On This Day', '1', '9', '0'),
('advancedactivity', 'advertise', 'Buy Sell', '1', '10', '0');
