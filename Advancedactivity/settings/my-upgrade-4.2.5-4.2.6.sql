
-- --------------------------------------------------------

--
-- Table structure for table `engine4_advancedactivity_savefeed`
--

DROP TABLE IF EXISTS `engine4_advancedactivity_savefeeds`;
CREATE TABLE `engine4_advancedactivity_savefeeds` (
`user_id` INT( 11 ) NOT NULL ,
`action_type` VARCHAR( 128 ) NOT NULL ,
`action_id` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `user_id` , `action_id` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


INSERT IGNORE INTO `engine4_advancedactivity_contents` ( `module_name`, `filter_type`, `resource_title`, `content_tab`, `order`, `default`) VALUES ('network', 'only_network', 'My Networks', '0', '1', '1');


INSERT IGNORE INTO `engine4_advancedactivity_contents` ( `module_name`, `filter_type`, `resource_title`, `content_tab`, `order`, `default`) VALUES ('core', 'user_saved', 'Saved Feeds', '1', '999', '1');

UPDATE  `engine4_core_menuitems` SET  `params` =  '{"route":"admin_default","module":"seaocore","controller":"infotooltip", "action":"index","target":"_blank"}' WHERE  `engine4_core_menuitems`.`name` ='advancedactivity_admin_main_infotooltip';

CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_linkedin` (
  `user_id` int(10) unsigned NOT NULL,
  `linkedin_uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `linkedin_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `linkedin_secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `linkedin_uid` (`linkedin_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
