<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: install.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Installer extends Engine_Package_Installer_Module {
    protected $_version = '4.10.0p2';
    function onPreinstall() {
        $db = $this->getDb();
        $PRODUCT_TYPE = 'advancedactivity';
        $PLUGIN_TITLE = 'Advancedactivity';
        $PLUGIN_VERSION = $this->_version;
        $PLUGIN_CATEGORY = 'plugin';
        $PRODUCT_DESCRIPTION = 'Advanced Activity Feeds Wall Plugin';
        $_PRODUCT_FINAL_FILE = 0;
        $SocialEngineAddOns_version = '4.9.4p7';
        $PRODUCT_TITLE = 'Advanced Activity Feeds / Wall Plugin';
        $getErrorMsg = $this->getVersion();
        if (!empty($getErrorMsg)) {
            return $this->_error($getErrorMsg);
    }
    $file_path = APPLICATION_PATH . "/application/modules/$PLUGIN_TITLE/controllers/license/ilicense.php";
    $is_file = file_exists($file_path);
    if (empty($is_file)) {
      include_once APPLICATION_PATH . "/application/modules/Advancedactivity/controllers/license/license3.php";
    } else {
      $select = new Zend_Db_Select($db);
      $select->from('engine4_core_modules')->where('name = ?', $PRODUCT_TYPE);
      $is_Mod = $select->query()->fetchObject();
      if (empty($is_Mod)) {
        include_once $file_path;
      }
    }
    parent::onPreinstall();
  }

  function onInstall() {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);
    $select
            ->from('engine4_core_modules')
            ->where('name = ?', 'siteforum')
            ->where('enabled = ?', 1);
    $check_siteforum = $select->query()->fetchObject();
    if ($check_siteforum) {
      $table_page_exist = $db->query('SHOW TABLES LIKE "engine4_advancedactivity_contents"')->fetch();
      if (!empty($table_page_exist)) {
        $db->query("UPDATE `engine4_advancedactivity_contents` SET `module_name` = 'siteforum' AND `filter_type` = 'siteforum' WHERE `engine4_advancedactivity_contents`.`module_name` = 'forum';");
      }
    }
    // Notification Queue Work
    $this->notifactionQueue();
        // Remove modified_date columns in both action tables.
        if ($this->_columnExist('engine4_activity_stream', 'modified_date')) {
            $db->query("ALTER  TABLE engine4_activity_stream DROP COLUMN `modified_date`;");
        }

        // remove the Trigger also
        $connection = Engine_Db_Export::factory($db)->getAdapter()->getConnection();
        if (method_exists($connection, "query")) {
            $connection->query("DROP trigger if exists `set_stream_modified_date`");
            $connection->query("DROP trigger if exists `set_actions_modified_date`");
        }
        //Start Group feed work
        $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_actiontypes'")->fetch();
        if (!empty($table_exist)) {
            $widgetAdminColumn = $db->query("SHOW COLUMNS FROM `engine4_activity_actiontypes` LIKE 'is_grouped'")->fetch();
            if (empty($widgetAdminColumn)) {
                $db->query("ALTER TABLE `engine4_activity_actiontypes` ADD `is_grouped` TINYINT( 1 ) NOT NULL DEFAULT '0'");
                $db->query("UPDATE `engine4_activity_actiontypes` SET `is_grouped` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'tagged' LIMIT 1;");
                $db->query("UPDATE `engine4_activity_actiontypes` SET `is_grouped` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'friends' LIMIT 1;");

                //For like feed work.
                $isMod = $db->query("SELECT * FROM `engine4_activity_actiontypes` WHERE `type` LIKE '%like_%' AND `is_grouped` = '0'")->fetchAll();
                if (!empty($isMod)) {
                    foreach ($isMod as $modArray) {
                        $db->query("UPDATE `engine4_activity_actiontypes` SET `is_grouped` = '1' WHERE `engine4_activity_actiontypes`.`type` = '" . $modArray['type'] . "' LIMIT 1;");
                    }
                }
            } else {
                $db->query("UPDATE `engine4_activity_actiontypes` SET `is_grouped` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'tagged' LIMIT 1;");
                $db->query("UPDATE `engine4_activity_actiontypes` SET `is_grouped` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'friends' LIMIT 1;");
            }
        }
        //End Group feed work

        $table_engine4_album_albums_exist = $db->query("SHOW TABLES LIKE 'engine4_album_albums'")->fetch();
        if ($table_engine4_album_albums_exist) {
            $album_type = $db->query("SHOW COLUMNS FROM engine4_album_albums LIKE 'type'")->fetch();
            if (!empty($album_type)) {
                $select = new Zend_Db_Select($db);
                $select->from('engine4_core_modules', array('title', 'version'))
                        ->where('name = ?', "album");

                $getModVersion = $select->query()->fetchObject();

                if ($getModVersion->version < '4.8.13') {
                    $db->query("ALTER TABLE `engine4_album_albums` CHANGE `type` `type` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL;");
                }
            }
        }
        $db->query("UPDATE  `engine4_seaocores` SET  `is_activate` =  '1' WHERE  `engine4_seaocores`.`module_name` ='advancedactivity';");

        $table_engine4_music_playlists_exist = $db->query("SHOW TABLES LIKE 'engine4_music_playlists'")->fetch();
        if ($table_engine4_music_playlists_exist) {
            $column = $db->query("SHOW COLUMNS FROM `engine4_music_playlists` LIKE 'special'")->fetch();
            if (!empty($column)) {
                $type = $column['Type'];
                if (!strpos($type, "'wall', 'wall_friend', 'wall_network',")) {
                    $type = str_replace("'wall',", "'wall', 'wall_friend', 'wall_network', 'wall_onlyme', ", $type);
                    $db->query("ALTER TABLE `engine4_music_playlists` CHANGE `special` `special` $type CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");
                } else if (!strpos($type, "'wall', 'wall_friend', 'wall_network', 'wall_onlyme',")) {
                    $type = str_replace("'wall', 'wall_friend', 'wall_network', ", "'wall', 'wall_friend', 'wall_network', 'wall_onlyme', ", $type);
                    $db->query("ALTER TABLE `engine4_music_playlists` CHANGE `special` `special` $type CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");
                }
            }
        }

        $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_actiontypes'")->fetch();
        if (!empty($table_exist)) {
            $widgetAdminColumn = $db->query("SHOW COLUMNS FROM `engine4_activity_actiontypes` LIKE 'is_object_thumb'")->fetch();
            if (empty($widgetAdminColumn)) {
                $db->query("ALTER TABLE `engine4_activity_actiontypes` ADD `is_object_thumb` BOOL NOT NULL DEFAULT '0'");
            }
        }

        $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_actions'")->fetch();
        if (!empty($table_exist)) {
            $column_exist = $db->query("SHOW COLUMNS FROM `engine4_activity_actions` LIKE 'privacy'")->fetch();
            if (empty($column_exist)) {
                $db->query("ALTER TABLE `engine4_activity_actions` ADD `privacy` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
            }
        }



        //CHECK THAT SITEPAGE PLUGIN IS INSTALLED OR NOT
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitepage')
                ->where('version >= ?', '4.2.3')
                ->where('enabled = ?', 1);
        $check_sitepage = $select->query()->fetchObject();
        if (!empty($check_sitepage) && !empty($sitepage_is_active)) {
            //ADD NEW COLUMN IN engine4_sitepage_imports TABLE
            $table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitepage_pages'")->fetch();
            if (!empty($table_exist)) {

                $column_exist = $db->query("SHOW COLUMNS FROM engine4_sitepage_pages LIKE 'fbpage_id'")->fetch();
                if (empty($column_exist)) {
                    $db->query("ALTER TABLE `engine4_sitepage_pages` ADD `fbpage_id` VARCHAR( 32 ) NOT NULL");
                }
            }
        }
        $db->query("INSERT IGNORE INTO `engine4_advancedactivity_customtypes` ( `module_name`, `resource_type`, `resource_title`, `enabled`, `order`, `default`) VALUES
('sitebusiness', 'sitebusiness_business', 'Businesses', 1, 12, 1)");
        $db->query("INSERT IGNORE INTO `engine4_advancedactivity_contents` ( `module_name`, `filter_type`, `resource_title`, `content_tab`, `order`, `default`) VALUES
('sitebusiness', 'sitebusiness', 'Businesses', 1, 7, 1)");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `is_generated` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'video_new'");

        $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("post_self_photo", "advancedactivity", "{item:$subject}\r\n{body:$body}", 1, 5, 1, 1, 1, 0)');
        $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("post_self_video", "advancedactivity", "{item:$subject}\r\n{body:$body}", 1, 5, 1, 1, 1, 0)');
        $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("post_self_music", "advancedactivity", "{item:$subject}\r\n{body:$body}", 1, 5, 1, 1, 1, 0)');
        $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("post_self_link", "advancedactivity", "{item:$subject}\r\n{body:$body}", 1, 5, 1, 1, 1, 0)');

        $commentableColumn = $db->query("SHOW COLUMNS FROM `engine4_activity_actions` LIKE 'commentable'")->fetch();
        if (empty($commentableColumn)) {
            $db->query("ALTER TABLE `engine4_activity_actions` ADD `commentable` TINYINT( 1 ) NOT NULL DEFAULT '1',
ADD `shareable` TINYINT( 1 ) NOT NULL DEFAULT '1';");
        }
        $db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
(\'aaf_tagged\', \'advancedactivity\', \'{item:$subject} tagged your {var:$item_type} in a {item:$object:$label}.\', 0, \'\', 1);');

        //ADDING THE DEFAULT LINKEDIN ENTRY FOR MEMBER HOME PAGE ACTIVITY FEED.

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'advancedactivity')
                ->where('version < ?', '4.2.6');
        $check_advfeedversion = $select->query()->fetchObject();
        if (!empty($check_advfeedversion)) {
            $select = new Zend_Db_Select($db);
            $select->from('engine4_core_content', array('params'))
                    ->where('name = ?', 'advancedactivity.home-feeds')
                    ->where('page_id = ?', 4);
            $result = $select->query()->fetchObject();
            if (!empty($result->params)) {
                $params = Zend_Json::decode($result->params);


                if (isset($params['advancedactivity_tabs'])) {

                    $params['advancedactivity_tabs'][] = 'instagram';
                    $params = Zend_Json::encode($params);
                    $params = str_replace("'", "\'", $params);
                    $db->query("UPDATE `engine4_core_content` SET `params` = '" . $params . "' WHERE `engine4_core_content`.`name` = 'advancedactivity.home-feeds' AND `engine4_core_content`.`page_id` = 4 LIMIT 1;");
                }
            }
        }

        $db->query("UPDATE `engine4_activity_actiontypes` SET `body` = '{item:" . '$subject' . "} shared {item:" . '$object' . "}''s {var:" . '$type' . "}. {body:" . '$body' . "}' WHERE `engine4_activity_actiontypes`.`type` = 'share' LIMIT 1");

        //ADD COLUMN FOR CHECKING BY WHICH DEVICE THE FEED IS POSTED.
        $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_actions'")->fetch();
        if (!empty($table_exist)) {
            $Column = $db->query("SHOW COLUMNS FROM `engine4_activity_actions` LIKE 'user_agent'")->fetch();
            if (empty($Column)) {
                $db->query("ALTER TABLE `engine4_activity_actions` ADD `user_agent` TEXT NULL DEFAULT NULL");
            } else {
                $db->query("ALTER TABLE `engine4_activity_actions` CHANGE `user_agent` `user_agent` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");
            }
        }

        //CHECK IF SITEMOBILE PLUGIN IS ENABLED AND ADVANCEDACTIVITY HAS LESS VERSION TO 4.6.0 THEN DE-INTEGRATE ADVANCEDACTIVITY WITH SITEMOBILE
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitemobile')
                ->where('enabled = ?', 1);
        $is_sitemobile_object = $select->query()->fetchObject();
        if (!empty($is_sitemobile_object)) {
            //CHECK IF ADVANCEDACTIVITY HAS THE LESS VERSION
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_modules')
                    ->where('name = ?', 'advancedactivity')
                    ->where('version <= ?', '4.6.0');
            $check_advfeedversion = $select->query()->fetchObject();
            if (!empty($check_advfeedversion)) {
                $select = new Zend_Db_Select($db);
                $select
                        ->from('engine4_sitemobile_modules')
                        ->where('name = ?', 'advancedactivity')
                        ->where('integrated = ?', 1);
                $is_sitemobile_object = $select->query()->fetchObject();
                if ($is_sitemobile_object) {
                    $db->query("UPDATE `engine4_sitemobile_modules` SET `integrated` = '0' WHERE `engine4_sitemobile_modules`.`name` = 'advancedactivity'");
                }
            }
        }


        $select = new Zend_Db_Select($db);
        $siteevent = $select->from('engine4_core_modules', 'name')
                ->where('name = ?', 'siteevent')
                ->query()
                ->fetchcolumn();

        $is_enabled = $select->query()->fetchObject();
        if (!empty($siteevent)) {
            $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`, `is_object_thumb`) VALUES ("siteevent_date_time_extended_parent", "siteevent", \'{itemParent:$object} has extended the event {item:$object} to {var:$newtime}.\', "1", "2", "2", "1", "1", "1", "0", "2");');

            $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`, `is_object_thumb`) VALUES ("siteevent_date_time_updated_parent", "siteevent", \'{itemParent:$object} has changed the location of the event {item:$object} to {var:$newlocation}.\', "1", "2", "2", "1", "1", "1", "0", "2");');

            $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`, `is_object_thumb`) VALUES ("siteevent_location_updated_parent", "siteevent", \'{itemParent:$object} has changed the location of the event {item:$object} to {var:$newlocation}.\', "1", "2", "2", "1", "1", "1", "0", "2");');

            $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`, `is_object_thumb`) VALUES ("siteevent_title_updated_parent", "siteevent", \'{itemParent:$object} has changed the title of the event {var:$oldtitle} to {var:$newtitle}.\', "1", "2", "2", "1", "1", "1", "0", "2");');

            $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`, `is_object_thumb`) VALUES ("siteevent_venue_updated_parent", "siteevent", \'{itemParent:$object} has changed the venue of the event {item:$object} to {var:$newvenue}.\', "1", "2", "2", "1", "1", "1", "0", "2");');

            $db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
					SELECT
						level_id as `level_id`,
						'siteevent_event' as `type`,
						'post' as `name`,
						2 as `value`,
						NULL as `params`
					FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');");

            $db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
					SELECT
						level_id as `level_id`,
						'siteevent_event' as `type`,
						'post' as `name`,
						1 as `value`,
						NULL as `params`
					FROM `engine4_authorization_levels` WHERE `type` IN('user');");

            $db->query('
						INSERT IGNORE INTO `engine4_authorization_permissions` 
						SELECT level_id as `level_id`, 
							"siteevent_event" as `type`, 
							"auth_post" as `name`, 
							5 as `value`, 
							\'["registered","owner_network","owner_member_member","owner_member","like_member","member","leader"]\' as `params` 
						FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");
					');
        }


        $Column = $db->query("SHOW COLUMNS FROM `engine4_advancedactivity_lists` LIKE 'type'")->fetch();
        if (empty($Column)) {
            $db->query("ALTER TABLE `engine4_advancedactivity_lists` ADD `type` VARCHAR( 64 ) NULL DEFAULT 'default'");
        }

        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules', array('title', 'version'))
                ->where('name = ?', "advancedactivity")
                ->where('enabled = ?', 1);
        $getModVersion = $select->query()->fetchObject();

        $isModSupport = $this->checkVersion($getModVersion->version, '4.8.6');
        if (empty($isModSupport)) {
            $db->query("UPDATE `engine4_storage_files` SET `type` = 'thumb.medium' WHERE `engine4_storage_files`.`type` = 'thumb.feed'");
        }

        $core_facebook_details = $db->query("SELECT `value` FROM `engine4_core_settings` WHERE `name` = 'core.facebook.appid' LIMIT 1")->fetchColumn();

        if (empty($core_facebook_details)) {
            $db->query("INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES ('facebookse.app.created.after', true);");
        }

        $instagram_table_exist = $db->query("SHOW TABLES LIKE 'engine4_advancedactivity_instagram'")->fetch();
        if (empty($instagram_table_exist)) {
            $db->query(
                    "CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_instagram` (
  `user_id` int(10) unsigned NOT NULL,
  `instagram_uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `instagram_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `instagram_secret` varchar(255) COLLATE utf8_unicode_ci NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `instagram_uid` (`instagram_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }


    $notificationMenu = $db->query("SELECT `name` FROM `engine4_core_menuitems` WHERE `name` = 'advancedactivity_admin_main_notificationsettings' LIMIT 1")->fetchColumn();
    if (!empty($notificationMenu))
      $db->query("UPDATE `engine4_core_menuitems` SET `label` = 'Notification'  WHERE `engine4_core_menuitems`.`name` = 'advancedactivity_admin_main_notificationsettings';");

    
    //Story Table
        $story_table_exist = $db->query("SHOW TABLES LIKE 'engine4_advancedactivity_stories'")->fetch();
        if (empty($story_table_exist)) {
            $db->query("CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_stories` (
  `story_id` int(11) NOT NULL auto_increment,
  `owner_id` int(11) NOT NULL,
  `owner_type` varchar(24) NOT NULL,
  `photo_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `rotation` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `expiry_date` datetime NOT NULL,
  `privacy` varchar(24) NOT NULL,
  `view_count` int(11) unsigned NOT NULL,
  `comment_count` int(11) unsigned NOT NULL,
  PRIMARY KEY (`story_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
            
        }
        $storyviewer_table_exist = $db->query("SHOW TABLES LIKE 'engine4_advancedactivity_storyviewer'")->fetch();
        if (empty($storyviewer_table_exist)) {
            $db->query("CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_storyviewer` (
  `viewer_id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NOT NULL,
  `subject_type` varchar(24) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_type` varchar(24) NOT NULL,
  PRIMARY KEY (`viewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        }
    $this->se410ColumnConflicts();
    parent::onInstall();
  }

  private function getVersion() {
    $db = $this->getDb();

    $errorMsg = '';
    $finalModules = $getResultArray = array();
    $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();

    $modArray = array(
        'sitepage' => '4.2.0',
        'sitelike' => '4.2.0',
        'sitealbum' => '4.8.10p5',
        'suggestion' => '4.2.0',
        'peopleyoumayknow' => '4.2.0',
        'poke' => '4.2.0',
        'list' => '4.2.0',
        'recipe' => '4.2.0',
        'birthday' => '4.2.0',
        'Sitepageadmincontact' => '4.1.8p2',
        'Sitepagealbum' => '4.2.0',
        'Sitepagebadge' => '4.2.0',
        'Sitepagediscussion' => '4.2.0',
        'Sitepagedocument' => '4.2.0',
        'Sitepageevent' => '4.2.0',
        'Sitepageform' => '4.2.0',
        'Sitepageinvite' => '4.2.0',
        'Sitepagelikebox' => '4.1.8',
        'Sitepagemusic' => '4.2.0',
        'Sitepagenote' => '4.2.0',
        'Sitepageoffer' => '4.2.0',
        'Sitepagepoll' => '4.2.0',
        'Sitepagereview' => '4.2.0',
        'Sitepageurl' => '4.2.0',
        'Sitepagevideo' => '4.2.0',
        'siteevent' => '4.8.12p5'
    );
    foreach ($modArray as $key => $value) {
      $isMod = $db->query("SELECT * FROM  `engine4_core_modules` WHERE  `name` LIKE  '" . $key . "'")->fetch();
      if (!empty($isMod) && !empty($isMod['version'])) {
        $isModSupport = $this->checkVersion($isMod['version'], $value);
        if (empty($isModSupport)) {
          $finalModules['modName'] = $key;
          $finalModules['title'] = $isMod['title'];
          $finalModules['versionRequired'] = $value;
          $finalModules['versionUse'] = $isMod['version'];
          $getResultArray[] = $finalModules;
        }
    }
                }

        foreach ($getResultArray as $modArray) {
            $errorMsg .= '<div class="tip"><span>Note: Your website does not have the latest version of "' . $modArray['title'] . '". Please upgrade "' . $modArray['title'] . '" on your website to the latest version available in your SocialEngineAddOns Client Area to enable its integration with "Advanced Activity Feeds / Wall Plugin".<br/> Please <a href="' . $base_url . '/manage">Click here</a> to go Manage Packages.</span></div>';
        }

        return $errorMsg;
    }

    private function checkVersion($databaseVersion, $checkDependancyVersion) {
        $f = $databaseVersion;
        $s = $checkDependancyVersion;
        if (strcasecmp($f, $s) == 0)
            return -1;

        $fArr = explode(".", $f);
        $sArr = explode('.', $s);
        if (count($fArr) <= count($sArr))
            $count = count($fArr);
        else
            $count = count($sArr);

        for ($i = 0; $i < $count; $i++) {
            $fValue = $fArr[$i];
            $sValue = $sArr[$i];
            if (is_numeric($fValue) && is_numeric($sValue)) {
                if ($fValue > $sValue)
                    return 1;
                elseif ($fValue < $sValue)
                    return 0;
                else {
                    if (($i + 1) == $count) {
                        return -1;
                    } else
                        continue;
                }
            }
            elseif (is_string($fValue) && is_numeric($sValue)) {
                $fsArr = explode("p", $fValue);

                if ($fsArr[0] > $sValue)
                    return 1;
                elseif ($fsArr[0] < $sValue)
                    return 0;
                else {
                    return 1;
                }
            } elseif (is_numeric($fValue) && is_string($sValue)) {
                $ssArr = explode("p", $sValue);

                if ($fValue > $ssArr[0])
                    return 1;
                elseif ($fValue < $ssArr[0])
                    return 0;
                else {
                    return 0;
                }
            } elseif (is_string($fValue) && is_string($sValue)) {
                $fsArr = explode("p", $fValue);
                $ssArr = explode("p", $sValue);
                if ($fsArr[0] > $ssArr[0])
                    return 1;
                elseif ($fsArr[0] < $ssArr[0])
                    return 0;
                else {
                    if ($fsArr[1] > $ssArr[1])
                        return 1;
                    elseif ($fsArr[1] < $ssArr[1])
                        return 0;
                    else {
                        return -1;
                    }
                }
            }
        }
    }

    protected function notifactionQueue() {
        $db = $this->getDb();
        $db->query("INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES ('Notification Queue', 'advancedactivity', 'Advancedactivity_Plugin_Task_Notification', 5);");
        $db->query("CREATE TABLE IF NOT EXISTS `engine4_advancedactivity_notificationqueues` (
                `notification_id` int(11) unsigned NOT NULL auto_increment,
                `user_id` int(11) unsigned NOT NULL,
                `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
                `subject_id` int(11) unsigned NOT NULL,
                `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
                `object_id` int(11) unsigned NOT NULL,
                `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
                `params` text COLLATE utf8_unicode_ci,
                `date` datetime NOT NULL,
                  PRIMARY KEY (`notification_id`),
                  KEY `LOOKUP` (`user_id`,`date`),
                  KEY `subject` (`subject_type`,`subject_id`),
                  KEY `object` (`object_type`,`object_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }

    public function onEnable() {
        $db = $this->getDb();
        parent::onEnable();
        try {
            // Execute these for enableing Sitereaction, Sitetagcheckin & Sitehashtag plugins 
            $this->view->packageManager = $packageManager = new Engine_Package_Manager(array(
                "basePath" => APPLICATION_PATH,
                "db" => $db,
            ));
            $packagePath = APPLICATION_PATH . "/application/modules/Advancedactivity/packages/";

            $files = array_diff(scandir($packagePath), array('.', '..'));
            foreach ($files as $file) {

                $packageFile = $packagePath . $file;
                $package = new Engine_Package_Manifest($packageFile, array(
                    'basePath' => APPLICATION_PATH,
                ));

                $package->setVersion($this->_version);
                $operation = new Engine_Package_Manager_Operation_Install($packageManager, $package);
                $packageManager->execute($operation, "enable");
            }
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }

    public function onDisable() {

        $db = $this->getDb();
        parent::onDisable();
        try {
            // Execute these for disableing Sitereaction, Sitetagcheckin & Sitehashtag plugins 
            $packageManager = new Engine_Package_Manager(array(
                "basePath" => APPLICATION_PATH,
                "db" => $db,
            ));
            $packagePath = APPLICATION_PATH . "/application/modules/Advancedactivity/packages/";

            $files = array_diff(scandir($packagePath), array('.', '..'));
            foreach ($files as $file) {

                $packageFile = $packagePath . $file;
                $package = new Engine_Package_Manifest($packageFile, array(
                    'basePath' => APPLICATION_PATH,
                ));

                $package->setVersion($this->_version);
                $operation = new Engine_Package_Manager_Operation_Install($packageManager, $package);
                $packageManager->execute($operation, "disable");
            }
        } catch (Exception $e) {
            die(" Exception " . $e);
        }
    }
    protected function _columnExist($table, $column) {
        $db = $this->getDb();
        $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
        $columnName = $db->query($sql)->fetch();
        return !empty($columnName);
    }
    protected function _setFeedsForEditable() {
        $db = $this->getDb();
        if (!$this->_columnExist('engine4_activity_actiontypes', 'editable')) {
            $sql = "ALTER TABLE `engine4_activity_actiontypes` ADD COLUMN `editable` TINYINT(1) NOT NULL DEFAULT '0'";
            $db->query($sql);
        }
        $db->query("UPDATE `engine4_activity_actiontypes` SET `editable` = 1 WHERE `type` "
                . "IN ('post','post_self','status','sitetagcheckin_add_to_map','sitetagcheckin_content','sitetagcheckin_status','sitetagcheckin_post_self','sitetagcheckin_post', 'sitetagcheckin_checkin','sitetagcheckin_lct_add_to_map','post_self_photo','post_self_video','post_self_music','post_self_link','share','sitegroup_post','sitepage_post','sitebusiness_post','sitegroup_post_self','siteevent_post', 'siteevent_post_parent','sitebusiness_post_self','sitepage_post_self')");
    }
    public function onPostInstall() {
        $db = $this->getDb();
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules', array('title', 'version'))
                ->where('name = ?', "advancedactivity")
                ->where('enabled = ?', 1);
        $getModVersion = $select->query()->fetchObject();
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_settings')
                ->where('name = ?', "advancedactivity.isActivate")
                ;
        $isActivate = $select->query()->fetchObject();
            try {
                // Execute these for installing Sitereaction, Sitetagcheckin & Sitehashtag plugins 
                $this->view->packageManager = $packageManager = new Engine_Package_Manager(array(
                    "basePath" => APPLICATION_PATH,
                    "db" => $db,
                ));
                $packagePath = APPLICATION_PATH . "/application/modules/Advancedactivity/packages/";

                $files = array_diff(scandir($packagePath), array('.', '..'));
                foreach ($files as $file) {

                    $packageFile = $packagePath . $file;
                    $package = new Engine_Package_Manifest($packageFile, array(
                        'basePath' => APPLICATION_PATH,
                    ));

                    $package->setVersion($this->_version);
                    $operation = new Engine_Package_Manager_Operation_Install($packageManager, $package);
                    $packageManager->execute($operation, "preinstall");
                    $packageManager->execute($operation, "install");
                    //  $packageManager->execute($operation, "postinstall");
                }
            } catch (Exception $e) {
                $this->view->error = $e->getMessage();
            }
            if(!empty($isActivate->value) && !$this->checkVersion($this->_currentVersion, '4.9.4p2')) {
                $view = new Zend_View();
                $baseUrl = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"]) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('install/', '', $view->url(array(), 'default', true));
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->gotoUrl($baseUrl . 'admin/advancedactivity/settings/upgrade-settings/redirect/install');

        }
    }

    protected function se410ColumnConflicts () {
        $db = $this->getDb();
        $hasColumn1 = $db->query("SHOW COLUMNS FROM `engine4_activity_actions` LIKE 'privacy'")->fetch();
        $hasColumn2 = $db->query("SHOW COLUMNS FROM `engine4_activity_actions` LIKE 'aaf_privacy'")->fetch();
        if ($hasColumn1 && $hasColumn2) {
            $db->query("UPDATE `engine4_activity_actions` SET `privacy` = `aaf_privacy` WHERE `aaf_privacy` IS NOT NULL");
            $db->query("ALTER TABLE `engine4_activity_actions` DROP COLUMN `aaf_privacy`");
        }
    }
       
  }
   
?>
