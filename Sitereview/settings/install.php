<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: install.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Installer extends Engine_Package_Installer_Module {

    function onPreinstall() {

        $getErrorMsg = $this->getVersion();
        if (!empty($getErrorMsg)) {
            return $this->_error($getErrorMsg);
        }

        $db = $this->getDb();
        $PRODUCT_TYPE = 'sitereview';
        $PLUGIN_TITLE = 'Sitereview';
        $PLUGIN_VERSION = '4.10.1p1';
        $PLUGIN_CATEGORY = 'plugin';
        $PRODUCT_DESCRIPTION = 'Multiple Listing Types';
        $_PRODUCT_FINAL_FILE = 0;
        $SocialEngineAddOns_version = '4.8.11';
        $PRODUCT_TITLE = 'Multiple Listing Types';
        $file_path = APPLICATION_PATH . "/application/modules/$PLUGIN_TITLE/controllers/license/ilicense.php";
        $is_file = file_exists($file_path);

        if (empty($is_file)) {
            include_once APPLICATION_PATH . "/application/modules/$PLUGIN_TITLE/controllers/license/license3.php";
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
        $table_sitereview_location_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_locations\'')->fetch();
        if (!empty($table_sitereview_location_exist)) {
            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `location` `location` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;");

            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `formatted_address` `formatted_address` text COLLATE utf8_unicode_ci;");

            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `country` `country` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;");

            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `state` `state` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;");

            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `zipcode` `zipcode` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;");

            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `city` `city` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;");
            $db->query("ALTER TABLE `engine4_sitereview_locations` CHANGE `address` `address` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;");
        }
        $table_listingtype_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_listingtypes\'')->fetch();
        if (!empty($table_listingtype_exist)) {
            $redirection_column = $db->query("SHOW COLUMNS FROM `engine4_sitereview_listingtypes` LIKE 'select_alternatives'")->fetch();
            if (empty($redirection_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `select_alternatives` TINYINT(1) NOT NULL DEFAULT '0';");
            }
        }

        $select = new Zend_Db_Select($db);
        $select->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_plural', 'title_singular'));
        $listingTypes = $select->query()->fetchAll();

        foreach ($listingTypes as $listingType) {

            $listingTypeId = $listingType['listingtype_id'];
            $titlePluUc = ucfirst($listingType['title_plural']);
            $titleSinUc = ucfirst($listingType['title_singular']);

            $page_id = $db->select()
                    ->from('engine4_core_pages', 'page_id')
                    ->where('name = ?', "sitereview_index_manage_listtype_" . $listingTypeId)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();

            if (!$page_id) {

                $containerCount = 0;
                $widgetCount = 0;

                $db->insert('engine4_core_pages', array(
                    'name' => "sitereview_index_manage_listtype_" . $listingTypeId,
                    'displayname' => 'Multiple Listing Types - Manage (My) ' . $titlePluUc,
                    'title' => '',
                    'description' => '',
                    'custom' => 0,
                ));
                $page_id = $db->lastInsertId();

                //TOP CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'top',
                    'page_id' => $page_id,
                    'order' => $containerCount++,
                ));
                $top_container_id = $db->lastInsertId();

                //MAIN CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'main',
                    'page_id' => $page_id,
                    'order' => $containerCount++,
                ));
                $main_container_id = $db->lastInsertId();

                //INSERT TOP-MIDDLE
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'middle',
                    'page_id' => $page_id,
                    'parent_content_id' => $top_container_id,
                    'order' => $containerCount++,
                ));
                $top_middle_id = $db->lastInsertId();

                //MAIN-MIDDLE CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'middle',
                    'page_id' => $page_id,
                    'parent_content_id' => $main_container_id,
                    'order' => $containerCount++,
                ));
                $main_middle_id = $db->lastInsertId();

                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'sitereview.navigation-sitereview',
                    'parent_content_id' => $top_middle_id,
                    'order' => $widgetCount++,
                    'params' => '',
                ));

                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'core.content',
                    'parent_content_id' => $main_middle_id,
                    'order' => $widgetCount++,
                ));
            }

            $page_id = $db->select()
                    ->from('engine4_core_pages', 'page_id')
                    ->where('name = ?', "sitereview_index_create_listtype_" . $listingTypeId)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();

            if (!$page_id) {

                $containerCount = 0;
                $widgetCount = 0;

                $db->insert('engine4_core_pages', array(
                    'name' => "sitereview_index_create_listtype_" . $listingTypeId,
                    'displayname' => "Multiple Listing Types - Create $titleSinUc Page",
                    'title' => '',
                    'description' => '',
                    'custom' => 0,
                ));
                $page_id = $db->lastInsertId();

                //TOP CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'top',
                    'page_id' => $page_id,
                    'order' => $containerCount++,
                ));
                $top_container_id = $db->lastInsertId();

                //MAIN CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'main',
                    'page_id' => $page_id,
                    'order' => $containerCount++,
                ));
                $main_container_id = $db->lastInsertId();

                //INSERT TOP-MIDDLE
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'middle',
                    'page_id' => $page_id,
                    'parent_content_id' => $top_container_id,
                    'order' => $containerCount++,
                ));
                $top_middle_id = $db->lastInsertId();

                //MAIN-MIDDLE CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'middle',
                    'page_id' => $page_id,
                    'parent_content_id' => $main_container_id,
                    'order' => $containerCount++,
                ));
                $main_middle_id = $db->lastInsertId();

                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'sitereview.navigation-sitereview',
                    'parent_content_id' => $top_middle_id,
                    'order' => $widgetCount++,
                    'params' => '',
                ));

                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'core.content',
                    'parent_content_id' => $main_middle_id,
                    'order' => $widgetCount++,
                ));
            }

            $page_id = $db->select()
                    ->from('engine4_core_pages', 'page_id')
                    ->where('name = ?', "sitereview_index_edit_listtype_" . $listingTypeId)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();

            if (!$page_id) {

                $containerCount = 0;
                $widgetCount = 0;

                $db->insert('engine4_core_pages', array(
                    'name' => "sitereview_index_edit_listtype_" . $listingTypeId,
                    'displayname' => "Multiple Listing Types - Edit $titleSinUc Page",
                    'title' => '',
                    'description' => '',
                    'custom' => 0,
                ));
                $page_id = $db->lastInsertId();

                //TOP CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'top',
                    'page_id' => $page_id,
                    'order' => $containerCount++,
                ));
                $top_container_id = $db->lastInsertId();

                //MAIN CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'main',
                    'page_id' => $page_id,
                    'order' => $containerCount++,
                ));
                $main_container_id = $db->lastInsertId();

                //INSERT TOP-MIDDLE
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'middle',
                    'page_id' => $page_id,
                    'parent_content_id' => $top_container_id,
                    'order' => $containerCount++,
                ));
                $top_middle_id = $db->lastInsertId();

                //MAIN-MIDDLE CONTAINER
                $db->insert('engine4_core_content', array(
                    'type' => 'container',
                    'name' => 'middle',
                    'page_id' => $page_id,
                    'parent_content_id' => $main_container_id,
                    'order' => $containerCount++,
                ));
                $main_middle_id = $db->lastInsertId();

                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'sitereview.navigation-sitereview',
                    'parent_content_id' => $top_middle_id,
                    'order' => $widgetCount++,
                    'params' => '',
                ));

                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'core.content',
                    'parent_content_id' => $main_middle_id,
                    'order' => $widgetCount++,
                ));
            }

            //START: PUT THE BACK WIDGET ON LISTING PROFILE PAGE IF LISTING IS INTEGRATED WITH CROWDFUNDING
            $page_id = $db->select()
                    ->from('engine4_core_pages', 'page_id')
                    ->where('name = ?', "sitereview_index_view_listtype_" . $listingTypeId)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
            if (!empty($page_id)) {

                $select = new Zend_Db_Select($db);
                $select
                        ->from('engine4_core_modules')
                        ->where('name = ?', 'sitecrowdfunding') 
                        ->where('enabled = ?', 1);
                $versionCheckCrowdfunding = $select->query()->fetchObject();
                if(!empty($versionCheckCrowdfunding)) {
                    $select = new Zend_Db_Select($db);
                    $select
                            ->from('engine4_core_modules')
                            ->where('name = ?', 'sitecrowdfundingintegration')
                            ->where('enabled = ?', 1);
                    $sitecrowdfundingintegrationEnabled = $select->query()->fetchObject();

                    $select = new Zend_Db_Select($db);
                    $select
                            ->from('engine4_sitecrowdfunding_modules')
                            ->where('enabled = ?', 1)
                            ->where('item_type = ?', "sitereview_listing_".$listingTypeId)
                            ->where('item_module = ?', 'sitereview');
                    $sitereviewIntegratedWithCrowdfunding = $select->query()->fetchObject();
                    
                    if (!empty($sitecrowdfundingintegrationEnabled) && !empty($sitereviewIntegratedWithCrowdfunding)) {  

                        $select = new Zend_Db_Select($db);
                        $select_content = $select
                            ->from('engine4_core_content')
                            ->where('page_id = ?', $page_id)
                            ->where('type = ?', 'widget')
                            ->where('name = ?', 'sitecrowdfunding.back-project')
                            ->limit(1);
                        $content_id = $select_content->query()->fetchObject()->content_id;

                        if (empty($content_id)) {
                            $select = new Zend_Db_Select($db);
                            $select_right = $select
                                ->from('engine4_core_content')
                                ->where('page_id = ?', $page_id)
                                ->where('type = ?', 'container')
                                ->where('name = ?', 'right')
                                ->limit(1);
                            $right_id = $select_right->query()->fetchObject()->content_id;
                            if (!empty($right_id)) {
                                $select = new Zend_Db_Select($db);
                                $db->insert('engine4_core_content', array(
                                    'page_id' => $page_id,
                                    'type' => 'widget',
                                    'name' => 'sitecrowdfunding.back-project',
                                    'parent_content_id' => $right_id,
                                    'order' => 5,
                                    'params' => '{"title":"","titleCount":true,"backTitle":"Donate Now"}',
                                ));
                            } 
                        }
                    }
                } 
            }
            //END: PUT THE BACK WIDGET ON LISTING PROFILE PAGE IF LISTING IS INTEGRATED WITH CROWDFUNDING 
        }


        $table_listingtype_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_listingtypes\'')->fetch();
        if (!empty($table_listingtype_exist)) {
            $redirection_column = $db->query("SHOW COLUMNS FROM `engine4_sitereview_listingtypes` LIKE 'redirection'")->fetch();
            if (empty($redirection_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `redirection` VARCHAR( 8 ) NOT NULL DEFAULT 'home';");
            }
        }

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules', array('version'))
                ->where('name = ?', 'sitereview')
                ->where('enabled = ?', 1);
        $checkVersion = $select->query()->fetchColumn();
        if (!empty($checkVersion) && $this->checkVersion($checkVersion, '4.8.6p11') <= 0) {
            $db->query("UPDATE `engine4_core_modules` SET `title` = 'Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)',`description` = 'Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)' WHERE `engine4_core_modules`.`name` = 'sitereview' LIMIT 1 ;");
        }

        //START: UPGRADE QUERIES
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules', array('version'))
                ->where('name = ?', 'sitereview')
                ->where('enabled = ?', 1);
        $checkVersion = $select->query()->fetchColumn();
        if (!empty($checkVersion) && $this->checkVersion($checkVersion, '4.8.3p2') <= 0) {
            $tableFacebookMixsettings = $db->query("SHOW TABLES LIKE 'engine4_facebookse_mixsettings'")->fetch();
            if (!empty($tableFacebookMixsettings)) {
                $db->query("UPDATE `engine4_facebookse_mixsettings` SET `module_name` = REPLACE(module_name, 'Reviews -', 'Multiple Listing Types -') WHERE `module_name` Like 'Reviews -%' AND `module` = 'sitereview'");
            }

            $tableSitemobileMenus = $db->query("SHOW TABLES LIKE 'engine4_sitemobile_menus'")->fetch();
            if (!empty($tableSitemobileMenus)) {

                //CODE FOR INCREASE THE SIZE OF engine4_sitemobile_menus's FIELD type
                $type_array = $db->query("SHOW COLUMNS FROM engine4_sitemobile_menus LIKE 'title'")->fetch();
                if (!empty($type_array)) {
                    $varchar = $type_array['Type'];
                    $length_varchar = explode("(", $varchar);
                    $length = explode(")", $length_varchar[1]);
                    $length_type = $length[0];
                    if ($length_type <= 64) {
                        $db->query("ALTER TABLE `engine4_sitemobile_menus` CHANGE `title` `title` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL");
                    }
                }

                $db->query("UPDATE `engine4_sitemobile_menus` SET `title` = REPLACE(title, 'Reviews -', 'Multiple Listing Types -') WHERE `title` Like 'Reviews -%'");

                $db->query("UPDATE `engine4_sitemobile_menus` SET `title` = 'Multiple Listing Types - Common Main Navigation Menu' WHERE `name` = 'sitereview_main_common'");
            }

            //CODE FOR INCREASE THE SIZE OF engine4_core_menus's FIELD type
            $type_array = $db->query("SHOW COLUMNS FROM engine4_core_menus LIKE 'title'")->fetch();
            if (!empty($type_array)) {
                $varchar = $type_array['Type'];
                $length_varchar = explode("(", $varchar);
                $length = explode(")", $length_varchar[1]);
                $length_type = $length[0];
                if ($length_type <= 64) {
                    $db->query("ALTER TABLE `engine4_core_menus` CHANGE `title` `title` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL");
                }
            }

            $db->query("UPDATE `engine4_core_menus` SET `title` = REPLACE(title, 'Reviews -', 'Multiple Listing Types -') WHERE `title` Like 'Reviews -%'");

            $db->query("UPDATE `engine4_core_menus` SET `title` = 'Multiple Listing Types - Video Main Navigation Menu' WHERE `name` = 'sitereviewvideo_main'");

            $db->query("UPDATE `engine4_core_menus` SET `title` = 'Multiple Listing Types - Video Main Navigation Menu' WHERE `name` = 'sitereviewvideo_main'");

            $db->query("UPDATE `engine4_core_menus` SET `title` = 'Multiple Listing Types - Common Main Navigation Menu' WHERE `name` = 'sitereview_main_common'");

            $db->query("UPDATE `engine4_core_pages` SET `displayname` = REPLACE(displayname, 'Reviews -', 'Multiple Listing Types -') WHERE `displayname` Like 'Reviews -%' AND `name` LIKE '%sitereview%'");
            $tablePages = $db->query("SHOW TABLES LIKE 'engine4_sitemobileapp_pages'")->fetch();
            if (!empty($tablePages)) {
                $db->query("UPDATE `engine4_sitemobileapp_pages` SET `displayname` = REPLACE(displayname, 'Reviews -', 'Multiple Listing Types -') WHERE `displayname` Like 'Reviews -%' AND `name` LIKE '%sitereview%'");
            }

            $tablePages = $db->query("SHOW TABLES LIKE 'engine4_sitemobileapp_tablet_pages'")->fetch();
            if (!empty($tablePages)) {
                $db->query("UPDATE `engine4_sitemobileapp_tablet_pages` SET `displayname` = REPLACE(displayname, 'Reviews -', 'Multiple Listing Types -') WHERE `displayname` Like 'Reviews -%' AND `name` LIKE '%sitereview%'");
            }

            $tablePages = $db->query("SHOW TABLES LIKE 'engine4_sitemobile_pages'")->fetch();
            if (!empty($tablePages)) {
                $db->query("UPDATE `engine4_sitemobile_pages` SET `displayname` = REPLACE(displayname, 'Reviews -', 'Multiple Listing Types -') WHERE `displayname` Like 'Reviews -%' AND `name` LIKE '%sitereview%'");
            }

            $tablePages = $db->query("SHOW TABLES LIKE 'engine4_sitemobile_tables_pages'")->fetch();
            if (!empty($tablePages)) {
                $db->query("UPDATE `engine4_sitemobile_tables_pages` SET `displayname` = REPLACE(displayname, 'Reviews -', 'Multiple Listing Types -') WHERE `displayname` Like 'Reviews -%' AND `name` LIKE '%sitereview%'");
            }
        }
        //END: UPGRADE QUERIES

        $tableSitemobileMenus = $db->query("SHOW TABLES LIKE 'engine4_sitemobile_menus'")->fetch();
        if (!empty($tableSitemobileMenus)) {
            $type_array = $db->query("SHOW COLUMNS FROM engine4_sitemobile_menus LIKE 'name'")->fetch();
            if (!empty($type_array)) {
                $varchar = $type_array['Type'];
                $length_varchar = explode("(", $varchar);
                $length = explode(")", $length_varchar[1]);
                $length_type = $length[0];
                if ($length_type <= 64) {
                    $db->query("ALTER TABLE `engine4_sitemobile_menus` CHANGE `name` `name` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL");
                }
            }
        }

        $tableSitemobileMenuItems = $db->query("SHOW TABLES LIKE 'engine4_sitemobile_menuitems'")->fetch();

        if (!empty($tableSitemobileMenuItems)) {
            $type_array = $db->query("SHOW COLUMNS FROM engine4_sitemobile_menuitems LIKE 'label'")->fetch();
            if (!empty($type_array)) {
                $varchar = $type_array['Type'];
                $length_varchar = explode("(", $varchar);
                $length = explode(")", $length_varchar[1]);
                $length_type = $length[0];
                if ($length_type <= 64) {
                    $db->query("ALTER TABLE `engine4_sitemobile_menuitems` CHANGE `label` `label` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL");
                }
            }
        }

        //CHECK THAT SITEREVIEW PLUGIN IS ACTIVATED OR NOT
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_settings')
                ->where('name = ?', 'sitereview.isActivate')
                ->limit(1);
        $sitereview_settings = $select->query()->fetchAll();
        $sitereview_is_active = !empty($sitereview_settings) ? $sitereview_settings[0]['value'] : 0;

        //CLAIM QUERY
        if (!empty($sitereview_is_active)) {
            $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_claim", "sitereview", "Manage Claims", "", \'{"route":"admin_default","module":"sitereview","controller":"claim"}\', "sitereview_admin_main", "", 1, 0, 70)');

            $db->query("UPDATE `engine4_core_menuitems` SET `order` = '72' WHERE `engine4_core_menuitems`.`name` ='sitereview_admin_main_statistic';");

            $db->query("INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES ('sitereview_admin_main_extensions', 'sitereview', 'Extensions', '', '{\"route\":\"admin_default\",\"module\":\"sitereview\",\"controller\":\"extension\",\"action\":\"upgrade\"}', 'sitereview_admin_main', '', 1, 0, 99);");
        }

        $db->query("DROP TABLE IF EXISTS `engine4_sitereview_listmemberclaims`;");
        $db->query("CREATE TABLE IF NOT EXISTS `engine4_sitereview_listmemberclaims` (
  `listmemberclaim_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `listingtype_id` int(5) NOT NULL,
  PRIMARY KEY (`listmemberclaim_id`),
  KEY `user_id` (`user_id`),
  KEY `listingtype_id` (`listingtype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

        $db->query("DROP TABLE IF EXISTS `engine4_sitereview_claims`;");
        $db->query("CREATE TABLE IF NOT EXISTS `engine4_sitereview_claims` (
  `claim_id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nickname` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `about` text COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `contactno` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `usercomments` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`claim_id`),
  KEY `listing_id` (`listing_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

        $db->query("DROP TABLE IF EXISTS `engine4_sitereview_jobs`;");
        $db->query("CREATE TABLE IF NOT EXISTS `engine4_sitereview_jobs` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_name` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `sender_email` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `file_id` int(10) NOT NULL,
  PRIMARY KEY (`job_id`),
  KEY `listing_id` (`listing_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

        $db->query("INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
            ('SITEREVIEW_APPLYNOW_EMAIL', 'sitereview', '[host],[contact_info],[sender],[listing_type],[message][object_link]')");

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules', array('version'))
                ->where('name = ?', 'sitereview')
                ->where('enabled = ?', 1);
        $checkVersion = $select->query()->fetchColumn();
        if (!empty($checkVersion) && $this->checkVersion($checkVersion, '4.7.1p2') <= 0) {

            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            $select = new Zend_Db_Select($db);
            $select->from('engine4_sitereview_listingtypes', array('listingtype_id'));
            $listingTypes = $select->query()->fetchAll();

            $string = '["registered","owner_network","owner_member_member","owner_member","owner"]';
            foreach ($listingTypes as $listingType) {
                $listing_type_id = $listingType['listingtype_id'];

                $db->query("
          INSERT IGNORE INTO `engine4_authorization_permissions`
            SELECT
              level_id as `level_id`,
              'sitereview_listing' as `type`,
              'auth_topic_listtype_$listing_type_id' as `name`,
              5 as `value`,
              '$string' as `params`
            FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');      
        ");

                $db->query("
          INSERT IGNORE INTO `engine4_authorization_permissions`
            SELECT
              level_id as `level_id`,
              'sitereview_listing' as `type`,
              'topic_listtype_$listing_type_id' as `name`,
              1 as `value`,
              NULL as `params`
            FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');  
        ");

                $db->query("
          INSERT IGNORE INTO `engine4_authorization_permissions`
            SELECT
              level_id as `level_id`,
              'sitereview_listing' as `type`,
              'where_to_buy_listtype_$listing_type_id' as `name`,
              1 as `value`,
              NULL as `params`
            FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');  
        ");
            }

            $select = new Zend_Db_Select($db);
            $select = $select->from('engine4_authorization_allow', array('resource_id', 'role', 'action'))
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('action LIKE "%comment_listtype_%"');
            $commentPrivacyDatas = $select->query()->fetchAll();
            foreach ($commentPrivacyDatas as $commentPrivacyData) {

                $resource_id = $commentPrivacyData['resource_id'];
                $role = $commentPrivacyData['role'];
                $action = $commentPrivacyData['action'];
                $action = str_replace('comment', 'topic', $action);

                $db->query("INSERT IGNORE INTO `engine4_authorization_allow` (`resource_type`,`resource_id`,`action`,`role`,`value`) VALUES ('sitereview_listing',$resource_id,'$action','$role',1);");
            }

            $select = new Zend_Db_Select($db);
            $select->from('engine4_core_content as c1', array('content_id', 'params'))->join('engine4_core_content as c2', 'c1.parent_content_id = c2.content_id', array())->where('c1.name = ?', 'sitereview.price-info-sitereview')->where('c2.name = "core.container-tabs"');
            $datas = $select->query()->fetchAll();
            foreach ($datas as $data) {
                $content_id = $data['content_id'];
                if (!empty($data['params'])) {
                    $params = Zend_Json::decode($data['params']);
                    $params['loaded_by_ajax'] = '1';
                    $params = Zend_Json::encode($params);
                    $db->query("UPDATE `engine4_core_content` SET `params` = '$params' WHERE `content_id` = $content_id AND `type` = 'widget' LIMIT 1;");
                }
            }
        }

        //ADD COLUMN IN LISTINGTYPE TABLE
        $sitereview_listingtype_edit_creationdate = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'edit_creationdate'")->fetch();
        if (empty($sitereview_listingtype_edit_creationdate)) {
            $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `edit_creationdate` TINYINT NOT NULL DEFAULT '0'");
        }

        $select = new Zend_Db_Select($db);
        $select->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'));
        $listingTypes = $select->query()->fetchAll();
        foreach ($listingTypes as $listingType) {
            $listingTypeId = $listingType['listingtype_id'];
            $listingtypeTitle = strtolower($listingType['title_singular']);

            $db->update('engine4_activity_actiontypes', array('body' => '{item:$subject} replied to discussion {item:$object:topic} in the ' . $listingtypeTitle . ' listing {itemParent:$object:sitereview_listing}: {body:$body}'), array('type = ?' => 'sitereview_topic_reply_listtype_' . $listingTypeId));
            
            $is_type_exists = $db->query("SELECT * FROM `engine4_activity_actiontypes` WHERE `type` LIKE 'sitereview_new_module_listtype_$listingTypeId'");
            
            if(empty($is_type_exists->type)){
                $subject = '$subject';
                $object = '$object';
                $db->query("INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES ('sitereview_new_module_listtype_$listingTypeId', 'sitereview', '{item:$subject} posted a new $listingtypeTitle listing in {item:$object}:', 1, 7, 1, 3, 1, 1)");
            }
        }

        $editorTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_editors\'')->fetch();
        if (!empty($editorTable)) {
            $db->query('DELETE FROM `engine4_sitereview_editors` WHERE `listingtype_id` = 0');
        }

        //START: INDEXING WORK
        $listingTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_listings\'')->fetch();
        if (!empty($listingTable)) {
            $searchIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_listings` WHERE Key_name = 'search'")->fetch();
            if (!empty($searchIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_listings` DROP INDEX `search`");
            }

            $profileTypeIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_listings` WHERE Key_name = 'profile_type'")->fetch();
            if (empty($profileTypeIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_listings` ADD INDEX ( `profile_type` )");
            }

            $newLabelIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_listings` WHERE Key_name = 'profile_type'")->fetch();
            if (empty($newLabelIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_listings` ADD INDEX ( `newlabel` )");
            }
        }

        $categoriesTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_categories\'')->fetch();
        if (!empty($categoriesTable)) {
            $categorySlugIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_categories` WHERE Key_name = 'category_slug'")->fetch();
            if (!empty($categorySlugIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_categories` DROP INDEX `category_slug`");
            }

            $catDependencyIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_categories` WHERE Key_name = 'cat_dependency'")->fetch();
            if (empty($catDependencyIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_categories` ADD INDEX ( `cat_dependency` )");
            }

            $subcatDependencyIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_categories` WHERE Key_name = 'subcat_dependency'")->fetch();
            if (empty($subcatDependencyIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_categories` ADD INDEX ( `subcat_dependency` )");
            }
        }

        $albumsTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_albums\'')->fetch();
        if (!empty($albumsTable)) {
            $newLabelIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_albums` WHERE Key_name = 'search'")->fetch();
            if (empty($newLabelIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_albums` ADD INDEX ( `search` )");
            }
        }

        $wishlistTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_wishlists\'')->fetch();
        if (!empty($wishlistTable)) {
            $ownerIdIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_wishlists` WHERE Key_name = 'owner_id'")->fetch();
            if (empty($ownerIdIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_wishlists` ADD INDEX ( `owner_id` )");
            }
        }

        $ratingparamTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_ratingparams\'')->fetch();
        if (!empty($ratingparamTable)) {
            $categoryIdIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_ratingparams` WHERE Key_name = 'category_id'")->fetch();
            if (!empty($categoryIdIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_ratingparams` DROP INDEX `category_id`");
                $db->query("ALTER TABLE `engine4_sitereview_ratingparams` ADD INDEX ( `resource_type` , `category_id` )");
            }
        }

        $ratingTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_ratings\'')->fetch();
        if (!empty($ratingTable)) {
            $resourceIdIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_ratings` WHERE Key_name = 'resource_id'")->fetch();
            $resourceTypeIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_ratings` WHERE Key_name = 'resource_type'")->fetch();
            if (!empty($resourceIdIndex) && !empty($resourceTypeIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_ratings` DROP INDEX `resource_id`");
                $db->query("ALTER TABLE `engine4_sitereview_ratings` DROP INDEX `resource_type`");
                $db->query("ALTER TABLE `engine4_sitereview_ratings` ADD INDEX resource ( `resource_id` , `resource_type` )");
            }
        }

        $reviewTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_reviews\'')->fetch();
        if (!empty($reviewTable)) {
            $resourceIdIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_reviews` WHERE Key_name = 'resource_id'")->fetch();
            $resourceTypeIndex = $db->query("SHOW INDEX FROM `engine4_sitereview_reviews` WHERE Key_name = 'resource_type'")->fetch();
            if (!empty($resourceIdIndex) && !empty($resourceTypeIndex)) {
                $db->query("ALTER TABLE `engine4_sitereview_reviews` DROP INDEX `resource_id`");
                $db->query("ALTER TABLE `engine4_sitereview_reviews` DROP INDEX `resource_type`");
                $db->query("ALTER TABLE `engine4_sitereview_reviews` ADD INDEX resource ( `resource_id` , `resource_type` )");
            }
        }
        //END: INDEXING WORK     

        $table_import_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_imports\'')->fetch();
        if (!empty($table_import_exist)) {
            $img_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports LIKE 'img_name'")->fetch();
            if (empty($img_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_imports` ADD `img_name` VARCHAR( 512 ) NOT NULL AFTER `tags`");
            }
            $column_subsub_category = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports LIKE 'subsub_category'")->fetch();
            if (empty($column_subsub_category)) {
                $db->query("ALTER TABLE `engine4_sitereview_imports` ADD `subsub_category` VARCHAR( 255 ) NOT NULL AFTER `sub_category`");
            }

            $column_exist = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports LIKE 'price'")->fetch();
            if (empty($column_exist)) {
                $db->query("ALTER TABLE `engine4_sitereview_imports` ADD `price` decimal(16,2) unsigned NOT NULL DEFAULT '0.00'");
            }
        }
        $table_importfiles_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_importfiles\'')->fetch();
        if (!empty($table_importfiles_exist)) {
            $column_filename = $db->query("SHOW COLUMNS FROM engine4_sitereview_importfiles LIKE 'photo_filename'")->fetch();
            if (empty($column_filename)) {
                $db->query("ALTER TABLE `engine4_sitereview_importfiles` ADD `photo_filename` VARCHAR( 255 ) NOT NULL AFTER `filename`");
            }
        }

        $table_categories = $db->query('SHOW TABLES LIKE \'engine4_sitereview_categories\'')->fetch();
        if (!empty($table_categories)) {
            $db->query("ALTER TABLE  `engine4_sitereview_categories` CHANGE  `top_content`  `top_content` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE  `bottom_content`  `bottom_content` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
        }

        $db->query("UPDATE `engine4_activity_actiontypes` SET `commentable` = 1 WHERE `type` LIKE 'sitereview_review_add_listtype_' AND `module` = 'sitereview'");

        $db->query("INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('SITEREVIEW_CHANGEOWNER_EMAIL', 'sitereview', '[host],[email],[list_title],[object_link],[listing_type],[list_title_with_link],[site_contact_us_link]'),
('SITEREVIEW_BECOMEOWNER_EMAIL', 'sitereview', '[host],[email],[list_title],[object_link],[listing_type],[list_title_with_link], [site_contact_us_link]')");

        //ADD REFERENCE COLUMN IN LISTINGTYPE TABLE
        $sitereview_listingtype_reference = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'reference'")->fetch();
        if (empty($sitereview_listingtype_reference)) {
            $db->query("ALTER TABLE  `engine4_sitereview_listingtypes` ADD  `reference` VARCHAR( 128 ) DEFAULT NULL");
            $listingTypes = $db->select()
                    ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular', 'slug_singular'))
                    ->where('listingtype_id <>?', 1)
                    ->query()
                    ->fetchAll();
            $templateTypes = array('food', 'tourism', 'blog', 'fashion', 'electronic', 'sport', 'classified', 'property', 'entertainment');
            foreach ($listingTypes as $listingType) {
                $listingtypeId = $listingType['listingtype_id'];
                $listingTitleSinglar = strtolower($listingType['title_singular']);
                $listingSlugSinglar = strtolower($listingType['slug_singular']);
                foreach ($templateTypes as $types) {
                    if (stripos($listingTitleSinglar, $types) !== false || stripos($listingSlugSinglar, $types) !== false) {
                        $db->query("UPDATE `engine4_sitereview_listingtypes` SET  `reference` =  '$types' WHERE  `engine4_sitereview_listingtypes`.`listingtype_id` = '$listingtypeId' LIMIT 1 ;");
                        break;
                    }
                }
            }
        }

        $sitereview_listingtype_allow_review = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'allow_review'")->fetch();
        if (empty($sitereview_listingtype_allow_review)) {
            $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `allow_review` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `reference` ");
        }

        $show_editor_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'show_editor'")->fetch();
        if (empty($show_editor_column)) {
            $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `show_editor` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `allow_review`");
        }

        $show_tag_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'show_tag'")->fetch();
        if (empty($show_tag_column)) {
            $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `show_tag` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `show_editor`");
        }

        $show_status_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'show_status'")->fetch();
        if (empty($show_status_column)) {
            $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `show_status` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `show_tag`");
        }

        $show_browse_option_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'show_browse'")->fetch();
        if (empty($show_browse_option_column)) {
            $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `show_browse` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `show_status`");
        }

        //ADD COLUMNS FOR CLAIM FUNCTIONALITY
        $tableListingtypes = $db->query("SHOW TABLES LIKE 'engine4_sitereview_listingtypes'")->fetch();
        if ($tableListingtypes) {
            $sitereview_claimlink_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'claimlink'")->fetch();
            if (empty($sitereview_claimlink_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `claimlink` TINYINT( 1 ) NOT NULL AFTER `show_browse`");
            }

            $sitereview_claim_show_menu_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'claim_show_menu'")->fetch();
            if (empty($sitereview_claim_show_menu_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `claim_show_menu` TINYINT( 2 ) NOT NULL DEFAULT '2' AFTER `claimlink`");
            }

            $sitereview_claim_email_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'claim_email'")->fetch();
            if (empty($sitereview_claim_email_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `claim_email` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `claim_show_menu` ");
            }
            $sitereview_apply_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'allow_apply'")->fetch();
            if (empty($sitereview_apply_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `allow_apply` TINYINT( 1 ) NOT NULL AFTER `allow_review`");
            }

            $sitereview_listing_type_show_application_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listingtypes LIKE 'show_application'")->fetch();
            if (empty($sitereview_listing_type_show_application_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listingtypes` ADD `show_application` TINYINT( 1 ) NOT NULL DEFAULT '1'");
            }
        }

        $tableOtherinfo = $db->query("SHOW TABLES LIKE 'engine4_sitereview_otherinfo'")->fetch();
        if ($tableOtherinfo) {
            $sitereview_otherinfo_user_claim_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_otherinfo LIKE 'userclaim'")->fetch();
            if (empty($sitereview_otherinfo_user_claim_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_otherinfo` ADD `userclaim` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `keywords`");
            }
        }

        $tableListings = $db->query("SHOW TABLES LIKE 'engine4_sitereview_listings'")->fetch();
        if ($tableListings) {
            $sitereview_user_claim_column = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'userclaim'")->fetch();
            if (!empty($sitereview_user_claim_column)) {
                $db->query("ALTER TABLE `engine4_sitereview_listings` DROP `userclaim`;");
            }
        }

        //ADD email_notify COLUMN IN EDITOR TABLE
        $email_notifiy = $db->query("SHOW COLUMNS FROM engine4_sitereview_editors LIKE 'email_notify'")->fetch();
        if (empty($email_notifiy)) {
            $db->query("ALTER TABLE `engine4_sitereview_editors` ADD `email_notify` TINYINT( 1 ) NOT NULL DEFAULT '1'");
        }

        //CODE FOR INCREASE THE SIZE OF engine4_activity_notifications's FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_activity_actionsettings LIKE 'type'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 64) {
                $run_query = $db->query("ALTER TABLE `engine4_activity_actionsettings` CHANGE `type` `type` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_activity_notifications's FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_activity_actiontypes LIKE 'type'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 64) {
                $run_query = $db->query("ALTER TABLE `engine4_activity_actiontypes` CHANGE `type` `type` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        $db = $this->getDb();

        $db->query("UPDATE  `engine4_seaocores` SET  `is_activate` =  '1' WHERE  `engine4_seaocores`.`module_name` ='sitereview';");

        $otherInfoTable = $db->query('SHOW TABLES LIKE \'engine4_sitereview_otherinfo\'')->fetch();
        if (!empty($otherInfoTable)) {
            $db->query("ALTER TABLE `engine4_sitereview_otherinfo` CHANGE `overview` `overview` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");
            $db->query("ALTER TABLE `engine4_sitereview_otherinfo` CHANGE `phone` `phone` VARCHAR( 32 ) NULL DEFAULT NULL");
            $db->query("ALTER TABLE `engine4_sitereview_otherinfo` CHANGE `email` `email` VARCHAR( 128 ) NULL DEFAULT NULL");
            $db->query("ALTER TABLE `engine4_sitereview_otherinfo` CHANGE `website` `website` VARCHAR( 255 ) NULL DEFAULT NULL");
        }

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules', array('version'))
                ->where('name = ?', 'sitereview');
        $version = $select->query()->fetchColumn(); 

        if (!empty($version) && $this->checkVersion($version, '4.3.0p3') == 0) {
            $listingTypes = $db->select()
                    ->from('engine4_sitereview_listingtypes', 'listingtype_id')
                    ->query()
                    ->fetchAll();
            foreach ($listingTypes as $listingType) {
                $listingTypeId = $listingType['listingtype_id'];

                $params = $db->select()
                        ->from('engine4_core_menuitems', 'params')
                        ->where("name = ?", "core_main_sitereview_listtype_$listingTypeId")
                        ->limit(1)
                        ->query()
                        ->fetchColumn();

                if (!empty($params)) {
                    $params = Zend_Json::decode($params);
                    $params['route'] = "sitereview_general_listtype_" . $listingTypeId;
                    $params['listingtype_id'] = $listingTypeId;
                    $params = Zend_Json::encode($params);

                    $db->query("UPDATE `engine4_core_menuitems` SET `plugin` = 'Sitereview_Plugin_Menus::canViewSitereviews', `params` = '$params' WHERE `name` Like 'core_main_sitereview_listtype_$listingTypeId'");
                }

                $paramsMobile = $db->select()
                        ->from('engine4_core_menuitems', 'params')
                        ->where("name = ?", "mobi_browse_sitereview_listtype_$listingTypeId")
                        ->limit(1)
                        ->query()
                        ->fetchColumn();

                if (!empty($paramsMobile)) {
                    $paramsMobile = Zend_Json::decode($paramsMobile);
                    $paramsMobile['route'] = "sitereview_general_listtype_" . $listingTypeId;
                    $paramsMobile['listingtype_id'] = $listingTypeId;
                    $paramsMobile = Zend_Json::encode($paramsMobile);

                    $db->query("UPDATE `engine4_core_menuitems` SET `plugin` = 'Sitereview_Plugin_Menus::canViewSitereviews', `params` = '$paramsMobile' WHERE `name` Like 'mobi_browse_sitereview_listtype_$listingTypeId'");
                }
            }
        }

        //IF 'engine4_seaocore_follows' TABLE IS NOT EXIST THAN CREATE'
        $seocoreFollowTable = $db->query('SHOW TABLES LIKE \'engine4_seaocore_follows\'')->fetch();
        if (empty($seocoreFollowTable)) {
            $db->query("CREATE TABLE IF NOT EXISTS `engine4_seaocore_follows` (
        `follow_id` int(11) unsigned NOT NULL auto_increment,
        `resource_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
        `resource_id` int(11) unsigned NOT NULL,
        `poster_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
        `poster_id` int(11) unsigned NOT NULL,
        `creation_date` datetime NOT NULL,
        PRIMARY KEY  (`follow_id`),
        KEY `resource_type` (`resource_type`, `resource_id`),
        KEY `poster_type` (`poster_type`, `poster_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;");

            $select = new Zend_Db_Select($db);
            $advancedactivity = $select->from('engine4_core_modules', 'name')
                    ->where('name = ?', 'advancedactivity')
                    ->query()
                    ->fetchcolumn();

            $is_enabled = $select->query()->fetchObject();
            if (!empty($advancedactivity)) {
                $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_grouped`) VALUES ("follow_sitereview_wishlist", "sitereview", \'{item:$subject} is following {item:$owner}\'\'s {item:$object:wishlist}: {body:$body}\', 1, 5, 1, 1, 1, 1, 1)');
            } else {
                $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES ("follow_sitereview_wishlist", "sitereview", \'{item:$subject} is following {item:$owner}\'\'s {item:$object:wishlist}: {body:$body}\', 1, 1, 1, 1, 1, 1)');
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_activity_actions FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'type'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 64) {
                $run_query = $db->query("ALTER TABLE `engine4_activity_actions` CHANGE `type` `type` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_activity_stream FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_activity_stream LIKE 'type'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 64) {
                $run_query = $db->query("ALTER TABLE `engine4_activity_stream` CHANGE `type` `type` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_authorization_permissions's FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_authorization_permissions LIKE 'type'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 32) {
                $run_query = $db->query("ALTER TABLE `engine4_authorization_permissions` CHANGE `type` `type` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_authorization_permissions's FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_authorization_permissions LIKE 'name'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 32) {
                $run_query = $db->query("ALTER TABLE `engine4_authorization_permissions` CHANGE `name` `name` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_authorization_allow's FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_authorization_allow LIKE 'resource_type'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 32) {
                $run_query = $db->query("ALTER TABLE `engine4_authorization_allow` CHANGE `resource_type` `resource_type` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        //CHANGE IN CORE COMMENT TABLE
        $table_exist = $db->query("SHOW TABLES LIKE 'engine4_core_comments'")->fetch();
        if (!empty($table_exist)) {
            $column_exist = $db->query("SHOW COLUMNS FROM `engine4_core_comments` LIKE 'parent_comment_id'")->fetch();
            if (empty($column_exist)) {
                $db->query("ALTER TABLE  `engine4_core_comments` ADD  `parent_comment_id` INT( 11 ) NOT NULL DEFAULT  '0';");
            }
        }

        //CODE FOR INCREASE THE SIZE OF engine4_authorization_allow's FIELD type
        $type_array = $db->query("SHOW COLUMNS FROM engine4_authorization_allow LIKE 'action'")->fetch();
        if (!empty($type_array)) {
            $varchar = $type_array['Type'];
            $length_varchar = explode("(", $varchar);
            $length = explode(")", $length_varchar[1]);
            $length_type = $length[0];
            if ($length_type < 32) {
                $run_query = $db->query("ALTER TABLE `engine4_authorization_allow` CHANGE `action` `action` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL");
            }
        }

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'siteevent')
                ->where('enabled = ?', 1);
        $is_siteevent_object = $select->query()->fetchObject();
        if (!empty($is_siteevent_object)) {
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_settings')
                    ->where('name = ?', 'sitereview.isActivate')
                    ->where('value = ?', 1);
            $sitereview_isActivate_object = $select->query()->fetchObject();
            if ($sitereview_isActivate_object) {
                $select = new Zend_Db_Select($db);
                $listingtypeObject = $select
                        ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'))
                        ->query()
                        ->fetchAll();
                foreach ($listingtypeObject as $values) {
                    $listingtype_id = $values['listingtype_id'];
                    $title_singular = ucfirst($values['title_singular']);
                    $db->query("INSERT IGNORE INTO `engine4_siteevent_modules` (`item_type`, `item_id`, `item_module`, `enabled`, `integrated`, `item_title`, `item_membertype`) VALUES ('sitereview_listing_$listingtype_id', 'listing_id', 'sitereview', '0', '0', '$title_singular Events', 'a:1:{i:0;s:18:\"contentlikemembers\";}')");
                    $db->query("INSERT IGNORE INTO `engine4_core_settings` ( `name`, `value`) VALUES( 'siteevent.event.leader.owner.sitereview.listing.$listingtype_id', '0');");
                }

                $db->query('INSERT IGNORE INTO `engine4_core_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_manageevent", "siteevent", "Manage Events", "", \'{"uri":"admin/siteevent/manage/index/contentType/sitereview_listing_1/contentModule/sitereview"}\', "sitereview_admin_main", "", 1, 0, 83);');
            }
        }


        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'documentintegration')
                ->where('enabled = ?', 1);
        $is_document_object = $select->query()->fetchObject();
        if (!empty($is_document_object)) {
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_settings')
                    ->where('name = ?', 'sitereview.isActivate')
                    ->where('value = ?', 1);
            $sitereview_isActivate_object = $select->query()->fetchObject();
            if ($sitereview_isActivate_object) {
                $select = new Zend_Db_Select($db);
                $listingtypeObject = $select
                        ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'))
                        ->query()
                        ->fetchAll();
                foreach ($listingtypeObject as $values) {
                    $listingtype_id = $values['listingtype_id'];
                    $title_singular = ucfirst($values['title_singular']);
                    $db->query("INSERT IGNORE INTO `engine4_document_modules` (`item_type`, `item_id`, `item_module`, `enabled`, `integrated`, `item_title`) VALUES ('sitereview_listing_$listingtype_id', 'listing_id', 'sitereview', '0', '0', '$title_singular Documents')");
                    $db->query("INSERT IGNORE INTO `engine4_core_settings` ( `name`, `value`) VALUES( 'document.leader.owner.sitereview.listing.$listingtype_id', '0');");
                }

                $db->query('INSERT IGNORE INTO `engine4_core_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_managedocument", "document", "Manage Documents", "", \'{"uri":"admin/document/manage/index/contentType/sitereview_listing_1/contentModule/sitereview"}\', "sitereview_admin_main", "", 1, 0, 83);');
            }
        }

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitevideointegration')
                ->where('enabled = ?', 1);
        $is_sitevideointegration_object = $select->query()->fetchObject();
        if (!empty($is_sitevideointegration_object)) {
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_settings')
                    ->where('name = ?', 'sitereview.isActivate')
                    ->where('value = ?', 1);
            $sitereview_isActivate_object = $select->query()->fetchObject();
            if ($sitereview_isActivate_object) {
                $table_listingtype_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_listingtypes\'')->fetch();
                if (!empty($table_listingtype_exist)) {
                    $select = new Zend_Db_Select($db);
                    $listingtypeObject = $select
                            ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'))
                            ->query()
                            ->fetchAll();
                    foreach ($listingtypeObject as $values) {
                        $listingtype_id = $values['listingtype_id'];
                        $singular_title = ucfirst($values['title_singular']);
                        $db->query("INSERT IGNORE INTO `engine4_sitevideo_modules` (`item_type`, `item_id`, `item_module`, `enabled`, `integrated`, `item_title`, `item_membertype`) VALUES ('sitereview_listing_$listingtype_id', 'listing_id', 'sitereview', '0', '0', '$singular_title Videos', 'a:1:{i:0;s:18:\"contentlikemembers\";}')");
                        $db->query('INSERT IGNORE INTO `engine4_core_settings` ( `name`, `value`) VALUES( "sitevideo.video.leader.owner.sitereview.listing.' . $listingtype_id . '", "1");');
                    }

                    $db->query('INSERT IGNORE INTO `engine4_core_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_managevideo", "sitevideointegration", "Manage Videos", "", \'{"uri":"admin/sitevideo/manage-video/index/contentType/sitereview_listing_1/contentModule/sitereview"}\', "sitereview_admin_main", "", 0, 0, 55);');

                    $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = '0' WHERE `engine4_core_menuitems`.`name` = 'sitereview_admin_main_video'");
                }
            }
        }
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_modules')
            ->where('name = ?', 'sitecrowdfunding') 
            ->where('enabled = ?', 1);
        $crowdfundingEnabled = $select->query()->fetchObject();
        if(!empty($crowdfundingEnabled)) {
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_modules')
                    ->where('name = ?', 'sitecrowdfundingintegration')
                    ->where('enabled = ?', 1);
            $is_sitecrowdfundingintegration_object = $select->query()->fetchObject();
            if ($is_sitecrowdfundingintegration_object) {
                $select = new Zend_Db_Select($db);
                $select
                        ->from('engine4_core_settings')
                        ->where('name = ?', 'sitereview.isActivate')
                        ->where('value = ?', 1);
                $sitereview_isActivate_object = $select->query()->fetchObject();
                if ($sitereview_isActivate_object) {
                    $table_listingtype_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_listingtypes\'')->fetch();
                    if (!empty($table_listingtype_exist)) {
                        $select = new Zend_Db_Select($db);
                        $listingtypeObject = $select
                                ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'))
                                ->query()
                                ->fetchAll();
                        foreach ($listingtypeObject as $values) {
                            $listingtype_id = $values['listingtype_id'];
                            $singular_title = ucfirst($values['title_singular']);

                            $db->query("INSERT IGNORE INTO `engine4_sitecrowdfunding_modules` (`item_type`, `item_id`, `item_module`, `enabled`, `integrated`, `item_title`, `item_membertype`) VALUES ('sitereview_listing_$listingtype_id', 'listing_id', 'sitereview', '0', '0', '$singular_title Videos', 'a:1:{i:0;s:18:\"contentlikemembers\";}')");

                            $db->query('INSERT IGNORE INTO `engine4_core_settings` ( `name`, `value`) VALUES( "sitecrowdfunding.project.leader.owner.sitereview.listing.' . $listingtype_id . '", "1");');
                        }

                        $db->query('INSERT IGNORE INTO `engine4_core_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_manageproject", "sitecrowdfunding", "Manage Videos", "", \'{"uri":"admin/sitecrowdfunding/manage/index/contentType/sitereview_listing_1/contentModule/sitereview"}\', "sitereview_admin_main", "", 0, 0, 55);');
                    }
                }
            }
        }
        $this->_checkFfmpegPath();

        $string = '%,,%';
        $paramsObject = $db->select()
                ->from('engine4_core_content', array('params', 'content_id'))
                ->where('name like ?', '%sitereview.price-info-sitereview%')
                ->where('params like ?', $string)
                ->query()
                ->fetchAll();
        foreach ($paramsObject as $params) {
            $content_id = $params['content_id'];
            $haystack = $params['params'];
            $needle = ',,';
            if (strpos($haystack, $needle) !== false) {
                $params = str_replace(',,', ',', $params['params']);
                $db->update('engine4_core_content', array('params' => "$params"), array('content_id =?' => $content_id));
            }
        }

        //START: UPGRADE QUERIES - LISTING CREATION - MOBILE COMPATIBLE 
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitemobile')
                ->where('enabled = ?', 1);
        $is_sitemobile_object = $select->query()->fetchObject();
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitemobileapp')
                ->where('enabled = ?', 1);
        $is_sitemobileapp_object = $select->query()->fetchObject();

        $listingTypess = $db->select()
                ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular', 'title_plural'))
                ->query()
                ->fetchAll();
        foreach ($listingTypess as $listingType) {
            $listingTypeId = $listingType['listingtype_id'];
            $titleSinUc = ucfirst($listingType['title_singular']);
            $titlePluUc = ucfirst($listingType['title_plural']);
            $titleSinLc = strtolower($listingType['title_singular']);
            $titlePluLc = strtolower($listingType['title_plural']);
            if ($is_sitemobile_object) {
                $this->mostratedPageCreate($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->mostratedPageCreate($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->dashboardMenuOptions($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->creationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editContactPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editStylePage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editMetaDetailsPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editOverviewPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editLocationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editPriceInfoPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editChangePhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editAlbumPhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
                $this->editVideosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');

                $this->dashboardMenuOptions($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->creationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editContactPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editStylePage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editMetaDetailsPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editOverviewPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editLocationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editPriceInfoPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editChangePhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editAlbumPhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
                $this->editVideosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
            }

            if ($is_sitemobileapp_object) {
                $this->mostratedPageCreate($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->mostratedPageCreate($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->dashboardMenuOptions($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobile_pages', 'engine4_sitemobileapp_content');
                $this->creationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editContactPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobile_content');
                $this->editStylePage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editMetaDetailsPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editOverviewPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editLocationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editPriceInfoPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editChangePhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editAlbumPhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
                $this->editVideosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');

                $this->dashboardMenuOptions($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->creationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editContactPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editStylePage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editMetaDetailsPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editOverviewPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editLocationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editPriceInfoPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editChangePhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editAlbumPhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
                $this->editVideosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
            }
        }

        $table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitereview_videos'")->fetch();
        if (!empty($table_exist)) {
            $email_field = $db->query("SHOW COLUMNS FROM engine4_sitereview_videos LIKE 'rotation'")->fetch();
            if (empty($email_field)) {
                $db->query("ALTER TABLE `engine4_sitereview_videos` ADD `rotation` SMALLINT(5) NOT NULL DEFAULT '0'");
            }
        }
        parent::onInstall();
    }

    protected function _checkFfmpegPath() {

        $db = $this->getDb();

        //CHECK FFMPEG PATH FOR CORRECTNESS
        if (function_exists('exec') && function_exists('shell_exec') && extension_loaded("ffmpeg")) {

            //API IS NOT AVAILABLE
            //$ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
            $ffmpeg_path = $db->select()
                    ->from('engine4_core_settings', 'value')
                    ->where('name = ?', 'sitereview_video.ffmpeg.path')
                    ->limit(1)
                    ->query()
                    ->fetchColumn(0);

            $output = null;
            $return = null;
            if (!empty($ffmpeg_path)) {
                exec($ffmpeg_path . ' -version', $output, $return);
            }

            //TRY TO AUTO-GUESS FFMPEG PATH IF IT IS NOT SET CORRECTLY
            $ffmpeg_path_original = $ffmpeg_path;
            if (empty($ffmpeg_path) || $return > 0 || stripos(join('', $output), 'ffmpeg') === false) {
                $ffmpeg_path = null;

                //WINDOWS
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // @todo
                }
                //NOT WINDOWS
                else {
                    $output = null;
                    $return = null;
                    @exec('which ffmpeg', $output, $return);
                    if (0 == $return) {
                        $ffmpeg_path = array_shift($output);
                        $output = null;
                        $return = null;
                        exec($ffmpeg_path . ' -version', $output, $return);
                        if (0 == $return) {
                            $ffmpeg_path = null;
                        }
                    }
                }
            }
            if ($ffmpeg_path != $ffmpeg_path_original) {
                $count = $db->update('engine4_core_settings', array(
                    'value' => $ffmpeg_path,
                        ), array(
                    'name = ?' => 'sitereview.video.ffmpeg.path',
                ));
                if ($count === 0) {
                    try {
                        $db->insert('engine4_core_settings', array(
                            'value' => $ffmpeg_path,
                            'name' => 'sitereview.video.ffmpeg.path',
                        ));
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }

    function onDisable() {

        $db = $this->getDb();

        $select = new Zend_Db_Select($db);
        $isSitereviewEnabled = $select
                ->from('engine4_core_modules', 'enabled')
                ->where('name = ?', 'sitereviewlistingtype')
                ->where('enabled = ?', 1)
                ->query()
                ->fetchColumn()
        ;

        if ($isSitereviewEnabled) {
            $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
            $error_msg1 = Zend_Registry::get('Zend_Translate')->_('Note: Please disable "Multiple Listing Types - Listing Type Creation Extension" before disabling the "Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)" itself.');
            echo "<div style='background-color: #E9F4FA;border-radius:7px 7px 7px 7px;float:left;overflow: hidden;padding:10px;'><div style='background:#FFFFFF;border:1px solid #D7E8F1;overflow:hidden;padding:20px;'><span style='color:red'>$error_msg1</span> <a href='" . $base_url . "/manage'>Click here</a> to go Manage Packages.</div></div>";
            die;
        }

        parent::onDisable();
    }

    public function onPostInstall() {

        $db = $this->getDb();
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitemobile')
                ->where('enabled = ?', 1);
        $is_sitemobile_object = $select->query()->fetchObject();
        if (!empty($is_sitemobile_object)) {
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_modules', array('version'))
                    ->where('name = ?', 'sitereview');
            $version = $select->query()->fetchColumn();
            if (!empty($version) && $this->checkVersion($version, '4.6.0p3') <= 0) {
                $select = new Zend_Db_Select($db);
                $select
                        ->from('engine4_sitemobile_modules')
                        ->where('name = ?', 'sitemobile')
                        ->where('integrated = ?', 1);
                $is_sitemobile_ingrated = $select->query()->fetchObject();
                if ($is_sitemobile_ingrated) {
                    $db->query("DELETE FROM `engine4_sitemobile_pages` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("DELETE FROM `engine4_sitemobile_content` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("DELETE FROM `engine4_sitemobile_tablet_pages` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("DELETE FROM `engine4_sitemobile_tablet_content` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("DELETE FROM `engine4_sitemobile_menuitems` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("DELETE FROM `engine4_sitemobile_menus` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("UPDATE  `engine4_sitemobile_modules` SET  `integrated` =  '0' WHERE  `engine4_sitemobile_modules`.`name` ='sitereview'");
                    $db->query("DELETE FROM `engine4_sitemobile_navigation` WHERE `name` LIKE  '%sitereview%';");
                    $db->query("DELETE FROM `engine4_sitemobile_searchform` WHERE `name` LIKE  '%sitereview%';");
                }
            }
        }

        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitemobile')
                ->where('enabled = ?', 1);
        $is_sitemobile_object = $select->query()->fetchObject();
        if (!empty($is_sitemobile_object)) {
            $db->query("INSERT IGNORE INTO `engine4_sitemobile_modules` (`name`, `visibility`) VALUES
('sitereview','1')");
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_sitemobile_modules')
                    ->where('name = ?', 'sitereview')
                    ->where('integrated = ?', 0);
            $is_sitemobile_object = $select->query()->fetchObject();
            if ($is_sitemobile_object) {
                $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
                $controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
                if ($controllerName == 'manage' && $actionName == 'install') {
                    $view = new Zend_View();
                    $baseUrl = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"]) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('install/', '', $view->url(array(), 'default', true));
                    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                    $redirector->gotoUrl($baseUrl . 'admin/sitemobile/module/enable-mobile/enable_mobile/1/name/sitereview/integrated/0/redirect/install');
                }
            }
        }
    }

    private function getVersion() {

        $db = $this->getDb();

        $errorMsg = '';
        $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();

        $modArray = array(
            'sitemobile' => '4.8.0p2',
        );

        $finalModules = array();
        foreach ($modArray as $key => $value) {
            $select = new Zend_Db_Select($db);
            $select->from('engine4_core_modules')
                    ->where('name = ?', "$key")
                    ->where('enabled = ?', 1);
            $isModEnabled = $select->query()->fetchObject();
            if (!empty($isModEnabled)) {
                $select = new Zend_Db_Select($db);
                $select->from('engine4_core_modules', array('title', 'version'))
                        ->where('name = ?', "$key")
                        ->where('enabled = ?', 1);
                $getModVersion = $select->query()->fetchObject();

                $isModSupport = $this->checkVersion($getModVersion->version, $value);
                if (empty($isModSupport)) {
                    $finalModules[] = $getModVersion->title;
                }
            }
        }

        foreach ($finalModules as $modArray) {
            $errorMsg .= '<div class="tip"><span style="background-color: #da5252;color:#FFFFFF;">Note: You do not have the latest version of the "' . $modArray . '". Please upgrade "' . $modArray . '" on your website to the latest version available in your SocialEngineAddOns Client Area to enable its integration with "Mobile / Tablet Plugin".<br/> Please <a class="" href="' . $base_url . '/manage">Click here</a> to go Manage Packages.</span></div>';
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

    //MOST RATED PAGE WORK
    public function mostratedPageCreate($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $columnHeight = 358;
        $columnWidth = 200;
        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_top-rated_listtype_" . $listingTypeId)
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (!$page_id) {

            $containerCount = 0;
            $widgetCount = 0;

            //CREATE PAGE
            $db->insert($pageTable, array(
                'name' => "sitereview_index_top-rated_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Browse Top Rated ' . $titlePluUc,
                'title' => 'Browse ' . $titlePluUc,
                'description' => 'This is the ' . $titleSinLc . ' browse page.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();

            //TOP CONTAINER
            $db->insert($contentTable, array(
                'type' => 'container',
                'name' => 'top',
                'page_id' => $page_id,
                'order' => $containerCount++,
            ));
            $top_container_id = $db->lastInsertId();

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
                'order' => $containerCount++,
            ));
            $main_container_id = $db->lastInsertId();

            //MAIN-MIDDLE CONTAINER
            $db->insert($contentTable, array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
            ));
            $main_middle_id = $db->lastInsertId();

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.navigation-sitereview',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-advancedsearch',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.browse-breadcrumb-sitereview',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"nomobile":"1"}',
            ));

            $layoutViews = ($pageTable == 'engine4_sitemobileapp_pages' || $pageTable == 'engine4_sitemobileapp_tablet_pages') ? '"layouts_views":["2"]' : '"layouts_views":["1","2"]';
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.rated-listings-sitereview',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"title":"","titleCount":true,' . $layoutViews . ',"layouts_order":"2","statistics":["viewCount","likeCount","reviewCount","commentCount"],"columnWidth":"200","truncationGrid":"100","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_both","detactLocation":"0","defaultLocationDistance":"1000","columnHeight":"' . $columnHeight . '","showExpiry":"1","bottomLine":"2","postedby":"1","orderby":"spfesp","itemCount":"9","truncation":"100","name":"sitereview.browse-listings-sitereview","bottomLineGrid":"2","showContent":["price","location","endDate","postedDate"]}',
            ));
        }
    }

    //CREATION PAGE WORK
    public function creationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_create_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_index_create_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - ' . $titleSinUc . ' Creation',
                'title' => $titleSinUc . ' Creation',
                'description' => 'This is ' . $titleSinLc . ' creation page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.navigation-sitereview',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    //EDIT PAGE WORK
    public function editPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_edit_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_index_edit_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types -  Edit ' . $titleSinUc,
                'title' => 'Edit ' . $titleSinUc,
                'description' => 'This is ' . $titleSinLc . ' edit page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editContactPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_dashboard_contact_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_dashboard_contact_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . ' Contact Details',
                'title' => 'Edit ' . $titleSinUc . ' Contact Details',
                'description' => 'This is edit ' . $titleSinLc . ' contact details page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editStylePage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_editstyle_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_index_editstyle_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . '  Style',
                'title' => 'Edit ' . $titleSinUc . ' Style',
                'description' => 'This is edit ' . $titleSinLc . ' style page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editMetaDetailsPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_dashboard_metadetails_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_dashboard_metadetails_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . ' Meta Keywords',
                'title' => 'Edit ' . $titleSinUc . ' Meta Keywords',
                'description' => 'This is edit ' . $titleSinLc . ' meta keywords page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editOverviewPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_editoverview_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_index_editoverview_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . ' Overview',
                'title' => 'Edit ' . $titleSinUc . ' Overview',
                'description' => 'This is edit ' . $titleSinLc . ' overview page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editLocationPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_editlocation_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_index_editlocation_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . '  Location',
                'title' => 'Edit ' . $titleSinUc . ' Location',
                'description' => 'This is edit ' . $titleSinLc . ' location page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editPriceInfoPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_priceinfo_index_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_priceinfo_index_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . ' Where to Buy',
                'title' => 'Edit ' . $titleSinUc . ' Where to Buy',
                'description' => 'This is ' . $titleSinLc . ' Where to Buy edit page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editChangePhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_dashboard_change-photo_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_dashboard_change-photo_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . ' Profile Picture',
                'title' => 'Edit ' . $titlePluUc . ' Profile Picture',
                'description' => 'This is edit ' . $titleSinLc . ' profile picture page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editAlbumPhotosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_album_editphotos_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_album_editphotos_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . ' Photos',
                'title' => 'Edit ' . $titleSinUc . ' Photos',
                'description' => 'This is edit ' . $titleSinLc . ' photos  page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function editVideosPage($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_videoedit_edit_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_videoedit_edit_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Edit ' . $titleSinUc . '  Videos',
                'title' => 'Edit ' . $titleSinUc . ' Videos',
                'description' => 'This is edit ' . $titleSinLc . ' video page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //MAIN CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'main',
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_container_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => 'middle',
                'parent_content_id' => $main_container_id,
                'order' => $containerCount++,
                'params' => '',
            ));
            $main_middle_id = $db->lastInsertId($contentTable);

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-options',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));

            //MIDDLE CONTAINER  
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.content',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
        }
    }

    public function dashboardMenuOptions($listingTypeId, $titleSinUc, $titlePluUc, $titleSinLc, $titlePluLc, $pageTable, $contentTable) {
        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET CORE MENUITEMS TABLE

        $menuItemsTableName = 'engine4_sitemobile_menuitems';
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
('sitereview_index_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Dashboard Page Options Menu')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
('sitereview_quick_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Quick Navigation Menu')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_listtype_$listingTypeId', 'sitereview_quick_listtype_$listingTypeId', '')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_dashboard_change-photo_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_album_editphotos_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_videoedit_edit_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_index_edit_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_index_overview_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_index_editlocation_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_price-info_index_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_dashboard_contact_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_index_editstyle_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_dashboard_meta-detail_listtype_$listingTypeId', 'sitereview_index_listtype_$listingTypeId', 'sitereview_listing')      
");

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_create_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_create_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Post A New $titleSinUc",
                'plugin' => 'Sitereview_Plugin_Menus::canCreateSitereviews',
                'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"create","listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_quick_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 1,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editvideos_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editvideos_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Videos",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditVideos',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 7,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editphotos_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editphotos_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Photos",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditPhotos',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 6,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_change-photo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_change-photo_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Profile Picture",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterChangephoto',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 3,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }


        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_priceinfo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_priceinfo_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc 'Where to Buy'",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterWhereToBuy',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 8,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editlocation_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editlocation_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Location",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditlocation',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 5,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_overview_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_overview_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Overview",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditoverview',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 2,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editmetadetails_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editmetadetails_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Meta Keywords",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditmetadetails',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 9,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editstyle_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editstyle_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Style",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditstyle',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 10,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editcontact_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editcontact_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Contact Details",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditcontact',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 4,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editinfo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_dashboard_editinfo_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Details",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEdit',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_index_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 1,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }

        $menuItemsId = $db->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_profile_editinfo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $db->insert($menuItemsTableName, array(
                'name' => "sitereview_profile_editinfo_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "Edit $titleSinUc Details",
                'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEdit',
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
                'menu' => "sitereview_gutter_listtype_$listingTypeId",
                'submenu' => '',
                'order' => 1,
                'enable_mobile' => 1,
                'enable_tablet' => 1
            ));
        }
    }

}
