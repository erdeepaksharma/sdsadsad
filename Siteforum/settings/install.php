<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: install.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Installer extends Engine_Package_Installer_Module {

    public function onPreInstall() {
        $getErrorMsg = $this->getVersion();
        if (!empty($getErrorMsg)) {
            return $this->_error($getErrorMsg);
        }



        $db = $this->getDb();
        $PRODUCT_TYPE = 'siteforum';
        $PLUGIN_TITLE = 'Siteforum';
        $PLUGIN_VERSION = '4.10.2';
        $PLUGIN_CATEGORY = 'plugin';
        $PRODUCT_DESCRIPTION = 'Advanced Forums';
        $PRODUCT_TITLE = 'Advanced Forums';
        $_PRODUCT_FINAL_FILE = 0;
        $SocialEngineAddOns_version = '4.8.9p19';
        $file_path = APPLICATION_PATH . "/application/modules/$PLUGIN_TITLE/controllers/license/ilicense.php";
        $is_file = file_exists($file_path);
        if (empty($is_file)) {
            include APPLICATION_PATH . "/application/modules/$PLUGIN_TITLE/controllers/license/license3.php";
        } else {
            $db = $this->getDb();
            $select = new Zend_Db_Select($db);
            $select->from('engine4_core_modules')->where('name = ?', $PRODUCT_TYPE);
            $is_Mod = $select->query()->fetchObject();
            if (empty($is_Mod)) {
                include_once $file_path;
            }
        }
        parent::onPreinstall();
    }

    public function onInstall() {
        $this->_addDefaultSettings();
        $this->_addUserProfileContent();
        $this->_addSiteforumIndexPage();
        $this->_addSiteforumViewPage();
        $this->_addTopicViewPage();
        $this->_addTopicCreatePage();
        $this->addDatabaseQueries();
        $this->_addTopicSearchPage();
        $this->_addTagsCloudPage();
        parent::onInstall();
    }

    /*
     * Add default settings, which need to be run while plugin install/upgrade.
     */

    private function _addDefaultSettings() {
        $db = $this->getDb();

        $db->query("UPDATE  `engine4_seaocores` SET  `is_activate` =  '1' WHERE  `engine4_seaocores`.`module_name` ='siteforum';");
        return;
    }

    //THIS FUNCTION WILL EXECUTE FIRST TIME INSTALLATION ONLY
    protected function addDatabaseQueries() {
        $db = $this->getDb();
        // sitelike integration
        $sitelike = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitelike')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (!empty($sitelike)) {

            $isModExist = $db->query("SELECT * FROM `engine4_sitelike_mixsettings` WHERE `module` LIKE 'siteforum' LIMIT 1")->fetch();
            if (empty($isModExist)) {
                $db->query("INSERT IGNORE INTO `engine4_sitelike_mixsettings` (`module`, `resource_type`, `resource_id`, `item_title`, `title_items`, `value`, `default`, `enabled`) VALUES ('siteforum', 'forum_post', 'post_id', 'Forum Posts', 'Forum Post', 1, 0, 1);");

                $db->query("UPDATE `engine4_sitelike_mixsettings` SET `module` =  'siteforum' , `resource_id` = 'topic_id' , `item_title` = 'Forum Topics' , `title_items` = 'Forum Topic'  WHERE `module` = 'forum' Limit 1;");
            }
        }
        
        $sitehashtag = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'sitehashtag')
                ->limit(1)
                ->query()
                ->fetchColumn();
        
        if (!empty($sitehashtag)) {
          $isModExist = $db->query("SELECT * FROM `engine4_sitehashtag_contents` WHERE `module_name` LIKE 'siteforum' LIMIT 1")->fetch();
            if (empty($isModExist)) {
              $db->query('INSERT IGNORE INTO `engine4_sitehashtag_contents` (`module_name`, `resource_type`, `enabled`) VALUES ("siteforum", "siteforum", 1);');
            }
        }

        //facebookse integration
        $facebookse = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'facebookse')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (!empty($facebookse)) {

            $isModExist = $db->query("SELECT * FROM `engine4_facebookse_mixsettings` WHERE `module` LIKE 'siteforum' LIMIT 1")->fetch();
            if (empty($isModExist)) {
                $db->query("INSERT INTO `engine4_facebookse_mixsettings` (`module`, `module_name`, `resource_type`, `resource_id`, `owner_field`, `module_title`, `module_description`, `enable`, `send_button`, `like_type`, `like_faces`, `like_width`, `like_font`, `like_color`, `layout_style`, `opengraph_enable`, `title`, `photo_id`, `description`, `types`, `fbadmin_appid`, `commentbox_enable`, `commentbox_privacy`, `commentbox_width`, `commentbox_color`, `module_enable`, `default`, `activityfeed_type`, `streampublish_message`, `streampublish_story_title`, `streampublish_link`, `streampublish_caption`, `streampublish_description`, `streampublish_action_link_text`, `streampublish_action_link_url`, `streampublishenable`, `activityfeedtype_text`, `action_type`, `object_type`, `like_commentbox`, `fbbutton_liketext`, `fbbutton_unliketext`, `show_customicon`, `fbbutton_likeicon`, `fbbutton_unlikeicon`) VALUES ('siteforum', 'Forum Topic', 'forum_topic', 'topic_id', 'user_id', 'title', 'description', 1, 1, 'like', 0, 450, '', '', 'standard', 0, '', 0, '', '', 1, 1, 1, 450, 'light', 1, 0, 'siteforum_topic_create', 'View my Forum Topic!', '{*siteforum_title*}', '{*siteforum_url*}', '{*actor*} created a Siteforum on {*site_title*}: {*site_url*}.', '{*siteforum_desc*}', 'View Siteforum', '{*siteforum_url*}', 1, 'Creating a Siteforum', 'og.likes', 'object', 1, 'Like', 'Unlike', 1, '', '');");
            }
        }

        //suggestion integration
        $suggestion = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'suggestion')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (!empty($suggestion)) {
            $isModExist = $db->query("SELECT * FROM `engine4_suggestion_module_settings` WHERE `module` LIKE 'siteforum' LIMIT 1")->fetch();
            if (empty($isModExist)) {

                $db->query("INSERT IGNORE INTO `engine4_suggestion_module_settings` (`module`, `item_type`, `field_name`, `owner_field`, `item_title`, `button_title`, `enabled`, `notification_type`, `quality`, `link`, `popup`, `recommendation`, `default`, `settings`) VALUES ('siteforum', 'forum_topic', 'topic_id', 'user_id', 'Advanced Forum', 'View this Forum Topic', 1, 'siteforum_suggestion', 1, 1, 1, 1, 1, 'a:2:{s:7:\"default\";i:1;s:16:\"after_forum_join\";s:0:\"\";}');");

                $db->query("UPDATE `engine4_suggestion_module_settings` SET `enabled` = '0' WHERE `module` = 'forum';");
            }
        }
        $table_page_exist = $db->query('SHOW TABLES LIKE "engine4_advancedactivity_contents"')->fetch();
        if (!empty($table_page_exist)) {
            $db->query("UPDATE `engine4_advancedactivity_contents` SET `module_name` = 'siteforum' AND `filter_type` = 'siteforum' WHERE `engine4_advancedactivity_contents`.`module_name` = 'forum';");
        }

        //communityad integration
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_modules')
                ->where('name = ?', 'communityad')
                ->where('enabled = ?', 1);
        $is_communityad_object = $select->query()->fetchObject();
        if (!empty($is_communityad_object)) {
            $db->query("DELETE FROM `engine4_communityad_modules` WHERE `engine4_communityad_modules`.`module_name` = 'forum' LIMIT 1");
        }
        $siteforumEnabled = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'siteforum')
                ->limit(1)
                ->query()
                ->fetchColumn();
        if (!empty($siteforumEnabled)) {
            return;
        }
        $forumEnabled = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'forum')
                ->limit(1)
                ->query()
                ->fetchColumn();

        $ynForumEnabled = $db->select()
                ->from('engine4_core_modules')
                ->where('name = ?', 'ynforum')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (empty($forumEnabled) && empty($ynForumEnabled)) {
            include_once APPLICATION_PATH . '/application/modules/Siteforum/settings/installation_queries.php';
        }

        if (!empty($ynForumEnabled)) {

            if ($this->_isTableExist('engine4_forum_categories')) {
                if ($this->_isColumnExist('engine4_forum_categories', 'parent_category_id')) {
                    $categories = $db->select()
                            ->from('engine4_forum_categories')
                            ->query()
                            ->fetchAll();

                    foreach ($categories as $category) {
                        $parent_category_id = $db->select()
                                ->from('engine4_forum_categories')
                                ->where('category_id = ?', $category['category_id'])
                                ->limit(1)
                                ->query()
                                ->fetchAll();
                        if (empty($parent_category_id[0]['parent_category_id'])) {
                            continue;
                        } else {
                            while (!empty($parent_category_id[0]['parent_category_id'])) {

                                $temp = $parent_category_id[0]['parent_category_id'];
                                $parent_category_id = $db->select()
                                        ->from('engine4_forum_categories')
                                        ->where('category_id = ?', $parent_category_id[0]['parent_category_id'])
                                        ->limit(1)
                                        ->query()
                                        ->fetchAll();

                                if (empty($parent_category_id[0]['parent_category_id'])) {
                                    $parent_category_id = $temp;
                                    break;
                                }

                                $temp_parent_category_id = $db->select()
                                        ->from('engine4_forum_categories')
                                        ->where('category_id = ?', $parent_category_id[0]['parent_category_id'])
                                        ->limit(1)
                                        ->query()
                                        ->fetchAll();


                                if (empty($temp_parent_category_id[0]['parent_category_id'])) {
                                    $parent_category_id = $temp_parent_category_id[0]['category_id'];
                                    break;
                                }
                            }
                            $db->query("UPDATE `engine4_forum_categories` SET `parent_category_id` = $parent_category_id WHERE `category_id` = " . $category['category_id']);
                        }
                    }


                    $db->query("UPDATE `engine4_forum_categories` SET `parent_category_id` = '0' WHERE `parent_category_id` IS NULL; ");

                    $db->query("ALTER TABLE `engine4_forum_categories` change `parent_category_id` `cat_dependency` INT(11) Default '0';");
                }
                if ($this->_isColumnExist('engine4_forum_categories', 'owner_id')) {
                    $db->query("ALTER TABLE `engine4_forum_categories`  DROP `owner_id`;");
                }
                if ($this->_isColumnExist('engine4_forum_categories', 'level')) {
                    $db->query("ALTER TABLE `engine4_forum_categories`  DROP `level`;");
                }
                if ($this->_isColumnExist('engine4_forum_categories', 'photo_id')) {
                    $db->query("ALTER TABLE `engine4_forum_categories`  MODIFY `photo_id` INT(11) NOT NULL DEFAULT '0';");
                }
            }

            if ($this->_isTableExist('engine4_forum_forums')) {
                if ($this->_isColumnExist('engine4_forum_forums', 'approved_topic_count')) {
                    $db->query("ALTER TABLE `engine4_forum_forums` DROP `approved_topic_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_forums', 'approved_post_count')) {
                    $db->query("ALTER TABLE `engine4_forum_forums`  DROP `approved_post_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_forums', 'parent_forum_id')) {
                    $db->query("ALTER TABLE `engine4_forum_forums`  DROP `parent_forum_id`;");
                }
                if ($this->_isColumnExist('engine4_forum_forums', 'level')) {
                    $db->query("ALTER TABLE `engine4_forum_forums`  DROP `level`;");
                }
                if (!$this->_isColumnExist('engine4_forum_forums', 'subcategory_id')) {
                    $db->query("ALTER TABLE `engine4_forum_forums`  ADD `subcategory_id` INT(11) NOT NULL DEFAULT 0;");
                }
                $forums = $db->select()
                        ->from('engine4_forum_forums')
                        ->query()
                        ->fetchAll();

                foreach ($forums as $forum) {
                    $parent_category_id = $db->select()
                            ->from('engine4_forum_categories', 'cat_dependency')
                            ->where('category_id = ?', $forum['category_id'])
                            ->limit(1)
                            ->query()
                            ->fetchColumn();
                    if (!empty($parent_category_id)) {

                        $db->query("UPDATE `engine4_forum_forums` SET `subcategory_id` = " . $forum['category_id'] . " WHERE `forum_id` = " . $forum['forum_id']);
                        $db->query("UPDATE `engine4_forum_forums` SET `category_id` = $parent_category_id WHERE `forum_id` = " . $forum['forum_id']);
                    }
                }
            }



            if ($this->_isTableExist('engine4_forum_posts')) {
                if ($this->_isColumnExist('engine4_forum_posts', 'approved')) {
                    $db->query("ALTER TABLE `engine4_forum_posts` DROP `approved`;");
                }
                if ($this->_isColumnExist('engine4_forum_posts', 'thanked_count')) {
                    $db->query("ALTER TABLE `engine4_forum_posts`  DROP `thanked_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_posts', 'icon_id')) {
                    $db->query("ALTER TABLE `engine4_forum_posts`  DROP `icon_id`;");
                }
                if ($this->_isColumnExist('engine4_forum_posts', 'title')) {
                    $db->query("ALTER TABLE `engine4_forum_posts`  DROP `title`;");
                }
                if ($this->_isColumnExist('engine4_forum_posts', 'photo_id')) {
                    $db->query("ALTER TABLE `engine4_forum_posts`  DROP `photo_id`;");
                }
            }

            if ($this->_isTableExist('engine4_forum_signatures')) {
                if ($this->_isColumnExist('engine4_forum_signatures', 'approved_post_count')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures` DROP `approved_post_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_signatures', 'thanks_count')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures`  DROP `thanks_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_signatures', 'thanked_count')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures`  DROP `thanked_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_signatures', 'reputation')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures`  DROP `reputation`;");
                }
                if ($this->_isColumnExist('engine4_forum_signatures', 'positive')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures`  DROP `positive`;");
                }
                if ($this->_isColumnExist('engine4_forum_signatures', 'neg_positive')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures`  DROP `neg_positive`;");
                }
                if ($this->_isColumnExist('engine4_forum_signatures', 'signature')) {
                    $db->query("ALTER TABLE `engine4_forum_signatures`  DROP `signature`;");
                }
            }

            if ($this->_isTableExist('engine4_forum_topics')) {
                if ($this->_isColumnExist('engine4_forum_topics', 'approved')) {
                    $db->query("ALTER TABLE `engine4_forum_topics` DROP `approved`;");
                }
                if ($this->_isColumnExist('engine4_forum_topics', 'approved_post_count')) {
                    $db->query("ALTER TABLE `engine4_forum_topics`  DROP `approved_post_count`;");
                }
                if ($this->_isColumnExist('engine4_forum_topics', 'firstpost_id')) {
                    $db->query("ALTER TABLE `engine4_forum_topics`  DROP `firstpost_id`;");
                }
                if ($this->_isColumnExist('engine4_forum_topics', 'icon_id')) {
                    $db->query("ALTER TABLE `engine4_forum_topics`  DROP `icon_id`;");
                }
            }

            if ($this->_isTableExist('engine4_forum_topicviews')) {
                if ($this->_isColumnExist('engine4_forum_topicviews', 'last_post_id')) {
                    $db->query("ALTER TABLE `engine4_forum_topicviews` DROP `last_post_id`;");
                }
            }

            $db->query("UPDATE `engine4_core_modules` SET `enabled` = 0 WHERE name = 'ynforum'");
        }

        //NOW RUN THE OTHER ALTERATION QURIES
        if ($this->_isTableExist('engine4_forum_categories')) {
            if (!$this->_isColumnExist('engine4_forum_categories', 'cat_dependency')) {
                $db->query("ALTER TABLE `engine4_forum_categories` ADD `cat_dependency` INT(11) NOT NULL DEFAULT '0';");
            }
            if (!$this->_isColumnExist('engine4_forum_categories', 'photo_id')) {
                $db->query("ALTER TABLE `engine4_forum_categories`  ADD `photo_id` INT(11) NOT NULL DEFAULT '0';");
            }
        }
        if ($this->_isTableExist('engine4_forum_posts')) {
            if (!$this->_isColumnExist('engine4_forum_posts', 'thanks_count')) {
                $db->query("ALTER TABLE `engine4_forum_posts`  ADD `thanks_count` int(11) unsigned NOT NULL DEFAULT '0';");
            }
            if (!$this->_isColumnExist('engine4_forum_posts', 'like_count')) {
                $db->query("ALTER TABLE `engine4_forum_posts`  ADD `like_count` int(11) unsigned NOT NULL DEFAULT '0';");
            }
        }
        if ($this->_isTableExist('engine4_forum_topics')) {
            if (!$this->_isColumnExist('engine4_forum_topics', 'rating')) {
                $db->query("ALTER TABLE `engine4_forum_topics` ADD `rating` FLOAT NOT NULL ;");
            }
            if (!$this->_isColumnExist('engine4_forum_topics', 'like_count')) {
                $db->query("ALTER TABLE `engine4_forum_topics`  ADD `like_count` int(11) unsigned NOT NULL DEFAULT '0';");
            }
        }
        if ($this->_isTableExist('engine4_forum_forums')) {
            if (!$this->_isColumnExist('engine4_forum_forums', 'subcategory_id')) {
                $db->query("ALTER TABLE `engine4_forum_forums`  ADD `subcategory_id` INT(11) NOT NULL DEFAULT 0;");
            }
            if (!$this->_isColumnExist('engine4_forum_forums', 'photo_id')) {
                $db->query("ALTER TABLE `engine4_forum_forums`  ADD `photo_id` INT(11) NOT  NULL DEFAULT 0;");
            }

            if ($this->_isColumnExist('engine4_forum_forums', 'description')) {
                $db->query("UPDATE `engine4_forum_forums` SET `description` = 'Contains topics related to ongoing issues / changes happening around.' WHERE `title` = 'News and Announcements' and `description` = ''");
                $db->query("UPDATE `engine4_forum_forums` SET `description` = 'Here users can raise questions against the issues they are facing.' WHERE `title` = 'Support' and `description` = ''");

                $db->query("UPDATE `engine4_forum_forums` SET `description` = 'Users can ask help / suggestions from other users.' WHERE `title` = 'Suggestions' and `description` = ''");

                $db->query("UPDATE `engine4_forum_forums` SET `description` = 'Users can ask any question, it can be about anything which comes to their mind.' WHERE `title` = 'Off-Topic Discussions' and `description` = ''");

                $db->query("UPDATE `engine4_forum_forums` SET `description` = 'Users can find / make new friends.' WHERE `title` = 'Introduce Yourself' and `description` = ''");
            }
        }
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_main_forum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_admin_main_plugins_forum'");

        //REMOVE FORUM POST WIDGET FROM USER PROFILE PAGE
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_pages')
                ->where('name = ?', 'user_profile_index')
                ->limit(1);
        $page_id = $select->query()->fetchObject()->page_id;
        $db->query("DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = 'forum.profile-forum-posts' AND `engine4_core_content`.`page_id` = $page_id");
    }

    protected function _isTableExist($tableName) {
        $db = $this->getDb();
        return $db->query("SHOW TABLES LIKE '$tableName'")->fetch();
    }

    protected function _isColumnExist($tableName, $columnName) {
        $db = $this->getDb();
        return $db->query("SHOW COLUMNS FROM $tableName LIKE '$columnName'")->fetch();
    }

    protected function _addTopicViewPage() {
        $db = $this->getDb();
        // check page
        $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'siteforum_topic_view')
                ->limit(1)
                ->query()
                ->fetchColumn();
        // insert if it doesn't exist yet
        if (!$page_id) {
            $count = 1;
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'siteforum_topic_view',
                'displayname' => 'Advanced Forums - Topic View Page',
                'title' => 'Forum Topic View',
                'description' => 'This is the view topic page.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();
            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
            ));
            $main_id = $db->lastInsertId();
            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
            ));
            $middle_id = $db->lastInsertId();
            // Insert breadcrumb
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.breadcrumb',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"","showDashboardLink":"0","title":"","nomobile":"0","name":"siteforum.breadcrumb"}',
            ));
            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.quick-navigation',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => $count++,
                'params' => '{"show_navigation":["navigation","dashboard"],"show_empty_category":"0","hierarchy":"2","itemCountPerPage":"","title":"","nomobile":"0","name":"siteforum.quick-navigation"}'
            ));
            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.topic-view',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => $count++
            ));
        }
    }

    protected function _addSiteforumIndexPage() {
        $db = $this->getDb();
        // check page
        $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'siteforum_index_index')
                ->limit(1)
                ->query()
                ->fetchColumn();
        // insert if it doesn't exist yet
        if (!$page_id) {
            $count = 1;
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'siteforum_index_index',
                'displayname' => 'Advanced Forums - Forums Home Page',
                'title' => 'Forums Home',
                'description' => 'This is the forums home page.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();
            // Insert top
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'top',
                'page_id' => $page_id,
                'order' => 1,
            ));
            $top_id = $db->lastInsertId();
            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
                'order' => 2,
            ));
            $main_id = $db->lastInsertId();
            // Insert top-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $top_id,
            ));
            $top_middle_id = $db->lastInsertId();
            // Insert main-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 2,
            ));
            $main_middle_id = $db->lastInsertId();
            // Insert main-right
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'right',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 1,
            ));
            $main_right_id = $db->lastInsertId();


            // Insert quick navigation
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.quick-navigation',
                'page_id' => $page_id,
                'parent_content_id' => $top_middle_id,
                'order' => $count++,
                'params' => '{"show_navigation":["navigation","dashboard"],"show_empty_category":"0","hierarchy":"2","itemCountPerPage":"","title":"","nomobile":"0","name":"siteforum.quick-navigation"}'
            ));

            // Insert browse search
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.browse-search',
                'page_id' => $page_id,
                'parent_content_id' => $top_middle_id,
                'order' => $count++,
                'params' => '{"title":"Search Forum Topics","viewType":"horizontal","searchWidth":"600","forumWidth":"300","nomobile":"0","name":"siteforum.browse-search"}'
            ));
            // Insert fourm-categories
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.forum-categories',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => $count++,
                'params' => '{"show_expand":"1","show_empty_category":"0","show_icon":["category","subcategory","forum"],"itemCountPerPage":"","title":"","nomobile":"0","name":"siteforum.forum-categories"}'
            ));
            // Insert fourm-statistics
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.forum-statistics',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"","statistics":["totalForums","topicCount","postCount","activeUsers","totalUsers"],"title":"Forum Statistics","nomobile":"0","name":"siteforum.forum-statistics"}'
            ));


            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.popular-topics',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"Most Popular Topics","popular_criteria":"view_count","statistics":["viewCount","ratings","postCount","likeCount"],"itemCountPerPage":"3","truncationDescription":"64","nomobile":"0","name":"siteforum.popular-topics"}'
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.popular-users',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"Most Active Members","popular_criteria":"post_count","show_online_user":"1","onlineIcon":"1","itemCountPerPage":"3","nomobile":"0","name":"siteforum.popular-users"}'
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.tags-cloud',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"","orderingType":"0","totalTags":"25","title":"Popular Tags","nomobile":"0","name":"siteforum.tags-cloud"}'
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'seaocore.layout-width',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"","layoutWidth":"300","layoutWidthType":"px","nomobile":"0","name":"seaocore.layout-width"}'
            ));
        }
    }

    protected function _addSiteforumViewPage() {
        $db = $this->getDb();
        // check page
        $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'siteforum_forum_view')
                ->limit(1)
                ->query()
                ->fetchColumn();
        // insert if it doesn't exist yet
        if (!$page_id) {
            $count = 1;
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'siteforum_forum_view',
                'displayname' => 'Advanced Forums - Forum View Page',
                'title' => 'Forum View',
                'description' => 'This is the view forum page.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();
            // Insert top
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'top',
                'page_id' => $page_id,
                'order' => 1,
            ));
            $top_id = $db->lastInsertId();
            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
                'order' => 2,
            ));
            $main_id = $db->lastInsertId();
            // Insert top-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $top_id,
            ));
            $top_middle_id = $db->lastInsertId();
            // Insert main-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 2,
            ));
            $main_middle_id = $db->lastInsertId();
            // Insert main-right
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'right',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 1,
            ));
            $main_right_id = $db->lastInsertId();
            // Insert breadcrumb
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.breadcrumb',
                'page_id' => $page_id,
                'parent_content_id' => $top_middle_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"","showDashboardLink":"1","title":"","nomobile":"0","name":"siteforum.breadcrumb"}',
            ));
            // Insert quick-navigation
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.quick-navigation',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => $count++,
                'params' => '{"show_empty_category":"0","hierarchy":"2","show_navigation":["navigation"],"itemCountPerPage":"","title":"","nomobile":"0","name":"siteforum.quick-navigation"}'
            ));
            // Insert forum-topics
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.forum-topics',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"15","popular_criteria":"creation_date","statistics":["viewCount","ratings","postCount","likeCount"],"title":"","nomobile":"0","name":"siteforum.forum-topics"}'
            ));
            // Insert fourm-statistics
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.forum-statistics',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"","statistics":["totalForums","topicCount","postCount","activeUsers","totalUsers"],"title":"Forum Statistics","nomobile":"0","name":"siteforum.forum-statistics"}'
            ));
            // Insert popular-posts
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.popular-posts',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"Most Thanked Posts","popular_criteria":"thanks_count","statistics":["thankCount","likeCount"],"itemCountPerPage":"5","truncationDescription":"64","nomobile":"0","name":"siteforum.popular-posts"}'
            ));
            // Insert popular-topics
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.popular-topics',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"Most Liked Topics","popular_criteria":"like_count","statistics":["viewCount","ratings","postCount","likeCount"],"itemCountPerPage":"5","truncationDescription":"64","nomobile":"0","name":"siteforum.popular-topics"}'
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'seaocore.layout-width',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"","layoutWidth":"300","layoutWidthType":"px","nomobile":"0","name":"seaocore.layout-width"}'
            ));
        }
    }

    protected function _addUserProfileContent() {
        $db = $this->getDb();
        $select = new Zend_Db_Select($db);
        // profile page
        $select
                ->from('engine4_core_pages')
                ->where('name = ?', 'user_profile_index')
                ->limit(1);
        $page_id = $select->query()->fetchObject()->page_id;
        // siteforum.profile-siteforum-posts
        // Check if it's already been placed
        $select = new Zend_Db_Select($db);
        $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $page_id)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'siteforum.profile-siteforum-posts')
        ;
        $info = $select->query()->fetch();
        if (empty($info)) {
            // container_id (will always be there)
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_content')
                    ->where('page_id = ?', $page_id)
                    ->where('type = ?', 'container')
                    ->limit(1);
            $container_id = $select->query()->fetchObject()->content_id;
            // middle_id (will always be there)
            $select = new Zend_Db_Select($db);
            $select
                    ->from('engine4_core_content')
                    ->where('parent_content_id = ?', $container_id)
                    ->where('type = ?', 'container')
                    ->where('name = ?', 'middle')
                    ->limit(1);
            $middle_id = $select->query()->fetchObject()->content_id;
            // tab_id (tab container) may not always be there
            $select
                    ->reset('where')
                    ->where('type = ?', 'widget')
                    ->where('name = ?', 'core.container-tabs')
                    ->where('page_id = ?', $page_id)
                    ->limit(1);
            $tab_id = $select->query()->fetchObject();
            if ($tab_id && @$tab_id->content_id) {
                $tab_id = $tab_id->content_id;
            } else {
                $tab_id = null;
            }
            // tab on profile
            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'siteforum.profile-siteforum-posts',
                'parent_content_id' => ($tab_id ? $tab_id : $middle_id),
                'order' => 9,
                'params' => '{"title":"Forum Posts","titleCount":true,"popular_criteria":"creation_date","statistics":["thankCount","likeCount"],"itemCountPerPage":"10","truncationDescription":"64","nomobile":"0","name":"siteforum.profile-siteforum-posts"}',
            ));
            // tab on profile
            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'siteforum.profile-siteforum-topics',
                'parent_content_id' => ($tab_id ? $tab_id : $middle_id),
                'order' => 10,
                'params' => '{"title":"Forum Topics","titleCount":true,"popular_criteria":"creation_date","statistics":["viewCount","postCount","likeCount"],"itemCountPerPage":"10","truncationDescription":"64","nomobile":"0","name":"siteforum.profile-siteforum-topics"}',
            ));
        }
    }

    protected function _addTopicCreatePage() {
        $db = $this->getDb();
        // check page
        $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'siteforum_forum_topic-create')
                ->limit(1)
                ->query()
                ->fetchColumn();
        // insert if it doesn't exist yet
        if (!$page_id) {
            $count = 1;
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'siteforum_forum_topic-create',
                'displayname' => 'Advanced Forums - Topic Creation Page',
                'title' => 'Forum Topic Create Page',
                'description' => 'This is the create topic page.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();
            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
            ));
            $main_id = $db->lastInsertId();
            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
            ));
            $middle_id = $db->lastInsertId();
            // Insert breadcrumb
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.breadcrumb',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => $count++
            ));
            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.content',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => $count++
            ));
        }
    }

    protected function _addTopicSearchPage() {
        $db = $this->getDb();
        // check page
        $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'siteforum_index_search')
                ->limit(1)
                ->query()
                ->fetchColumn();
        // insert if it doesn't exist yet
        if (!$page_id) {
            $count = 1;
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'siteforum_index_search',
                'displayname' => 'Advanced Forums - Search Forum\'s Topic Page',
                'title' => 'Search Forum\'s Topic',
                'description' => 'This is forum\'s topic search page.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();
            // Insert top
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'top',
                'page_id' => $page_id,
                'order' => 1,
            ));
            $top_id = $db->lastInsertId();
            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
                'order' => 2,
            ));
            $main_id = $db->lastInsertId();
            // Insert top-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $top_id,
            ));
            $top_middle_id = $db->lastInsertId();
            // Insert main-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 2,
            ));
            $main_middle_id = $db->lastInsertId();
            // Insert main-right
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'right',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 1,
            ));
            $main_right_id = $db->lastInsertId();
            // Insert browse-search
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.browse-search',
                'page_id' => $page_id,
                'parent_content_id' => $top_middle_id,
                'order' => $count++,
                'params' => '{"searchWidth":"600","forumWidth":"300","viewType":"horizontal","title":"","nomobile":"0","name":"siteforum.browse-search"}'
            ));
            // Insert forum-topics
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.forum-topics',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"25","popular_criteria":"creation_date","statistics":["viewCount","ratings","postCount","likeCount"],"title":"","nomobile":"0","name":"siteforum.forum-topics"}'
            ));
            // Insert quick-navigation
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.quick-navigation',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"show_navigation":["navigation"],"show_empty_category":"0","hierarchy":"2","itemCountPerPage":"","title":"Quick Navigation","nomobile":"0","name":"siteforum.quick-navigation"}'
            ));
            // Insert popular-topics
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.popular-topics',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"Most Rated Topics","popular_criteria":"rating","statistics":["viewCount","ratings","postCount","likeCount"],"itemCountPerPage":"5","truncationDescription":"64","nomobile":"0","name":"siteforum.popular-topics"}'
            ));
            // Insert tags-cloud
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'siteforum.tags-cloud',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"itemCountPerPage":"","orderingType":"0","totalTags":"25","title":"Popular Tags","nomobile":"0","name":"siteforum.tags-cloud"}'
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'seaocore.layout-width',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => $count++,
                'params' => '{"title":"","layoutWidth":"300","layoutWidthType":"px","nomobile":"0","name":"seaocore.layout-width"}'
            ));
        }
    }

    protected function _addTagsCloudPage() {
        $db = $this->getDb();
        // check page
        $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'siteforum_index_tags-cloud')
                ->limit(1)
                ->query()
                ->fetchColumn();
        // insert if it doesn't exist yet
        if (empty($page_id)) {
            $containerCount = 0;
            $widgetCount = 0;
            //CREATE PAGE
            $db->insert('engine4_core_pages', array(
                'name' => "siteforum_index_tags-cloud",
                'displayname' => 'Advanced Forums - Topic Tags Page',
                'title' => 'Popular Topic Tags',
                'description' => 'This is the topic tags page.',
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
                'name' => 'siteforum.quick-navigation',
                'parent_content_id' => $top_middle_id,
                'order' => $widgetCount++,
                'params' => '{"show_navigation":["navigation","dashboard"],"show_empty_category":"0","hierarchy":"2","itemCountPerPage":"","title":"","nomobile":"0","name":"siteforum.quick-navigation"}'
            ));
            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'seaocore.scroll-top',
                'parent_content_id' => $top_middle_id,
                'order' => $widgetCount++,
                'params' => '',
            ));
            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'siteforum.tags-cloud',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"itemCountPerPage":"","orderingType":"0","totalTags":"200","title":"","nomobile":"0","name":"siteforum.tags-cloud"}',
            ));
        }
    }

    public function onEnable() {
        $db = $this->getDb();
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_main_forum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 1 WHERE name = 'core_admin_main_plugins_siteforum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_admin_main_plugins_forum'");
        
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 1 WHERE name = 'core_main_siteforum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_main_forum'");
        parent::onEnable();
    }

    public function onDisable() {
        $db = $this->getDb();
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 1 WHERE name = 'core_main_forum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_admin_main_plugins_siteforum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 1 WHERE name = 'core_admin_main_plugins_forum'");
        
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 0 WHERE name = 'core_main_siteforum'");
        $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = 1 WHERE name = 'core_main_forum'");
        parent::onDisable();
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

    private function getVersion() {

        $db = $this->getDb();

        $errorMsg = '';
        $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();

        $modArray = array(
            'sitemobile' => '4.8.10p2',
            'advancedactivity' => '4.8.10p5'
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
                    $finalModules[$key] = $getModVersion->title;
                }
            }
        }

        foreach ($finalModules as $modArray) {
            $errorMsg .= '<div class="tip"><span style="background-color: #da5252;color:#FFFFFF;">Note: You do not have the latest version of the "' . $modArray . '". Please upgrade "' . $modArray . '" on your website to the latest version available in your SocialEngineAddOns Client Area to enable its integration with "' . $modArray . '".<br/> Please <a class="" href="' . $base_url . '/manage">Click here</a> to go Manage Packages.</span></div>';
        }

        return $errorMsg;
    }

}
