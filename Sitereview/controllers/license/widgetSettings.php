<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: widgetSettings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
$db = Zend_Db_Table_Abstract::getDefaultAdapter();

$db->query("INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES ('sitereview_admin_main_extensions', 'sitereview', 'Extensions', '', '{\"route\":\"admin_default\",\"module\":\"sitereview\",\"controller\":\"extension\",\"action\":\"upgrade\"}', 'sitereview_admin_main', '', 1, 0, 99);");

$viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
$db->query("INSERT IGNORE INTO `engine4_sitereview_editors` (`user_id`, `listingtype_id`, `designation`, `details`, `about`, `badge_id`, `super_editor`) VALUES ($viewer_id,1,'Super Editor','','',0,1)");

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_claim", "sitereview", "Manage Claims", "", \'{"route":"admin_default","module":"sitereview","controller":"claim"}\', "sitereview_admin_main", "", 1, 0, 70)');

$db->query("INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('SITEREVIEW_EMAIL_FRIEND', 'sitereview', '[host],[email],[recipient_title],[recipient_link],[review_title],[review_title_with_link],[user_email],[userComment]'),
('SITEREVIEW_REVIEW_WRITE', 'sitereview', '[host],[email],[recipient_title],[recipient_link],[review_title],[review_description],[review_link],[listing_name],[listing_title_with_link],[user_name]'),
('SITEREVIEW_REVIEW_DISAPPROVED', 'sitereview', '[host],[email],[recipient_title],[recipient_link],[review_title],[review_description],[review_link]'),
('SITEREVIEW_REVIEW_APPROVED', 'sitereview', '[host],[email],[recipient_title],[recipient_link],[review_title],[review_description],[review_link]'),
('SITEREVIEW_LISTING_CREATION_EDITOR', 'sitereview', '[host],[object_title],[object_link],[object_description],[listing_type]'),
('SITEREVIEW_EDITOR_EMAIL', 'sitereview', '[host],[email],[sender],[message]'),
('SITEREVIEW_EDITOR_ASSIGN_EMAIL', 'sitereview', '[sender],[editor_page_url],[listing_type]'),
('notify_sitereview_write_review', 'sitereview', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_description],[object_parent_link],[object_parent_title],[object_parent_with_link]'),
('SITEREVIEW_EDITORREVIEW_CREATION', 'sitereview', '[host],[editor_name],[editor],[object_title],[object_parent_with_link],[object_link], [object_parent_title],[object_description]'),
('notify_sitereview_approved_review', 'sitereview', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_title],[object_link],[object_description],[object_parent_link],[object_parent_title],[object_parent_with_link],[anonymous_name]'),
('SITEREVIEW_APPROVED_EMAIL_NOTIFICATION', 'sitereview', '[host],[email],[subject],[title],[message][object_link]'),
('SITEREVIEW_TELLAFRIEND_EMAIL', 'sitereview', '[host],[email],[sender],[message][object_link]');");

$db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES 
  
("comment_sitereview_photo", "sitereview", \'{item:$subject} commented on {item:$owner}\'\'s {item:$object:photo}: {body:$body}\', 1, 1, 1, 1, 1, 0),
("comment_sitereview_video", "sitereview", \'{item:$subject} commented on {item:$owner}\'\'s {item:$object:video}: {body:$body}\', 1, 1, 1, 1, 1, 0),
("comment_sitereview_listing", "sitereview", \'{item:$subject} commented on {item:$owner}\'\'s {var:$listingtype} listing {item:$object:$title}: {body:$body}\', "1", "1", "1", "1", "1", 1),
("comment_sitereview_review", "sitereview", \'{item:$subject} commented on {item:$owner}\'\'s review {item:$object:$title}: {body:$body}\', "1", "1", "1", "1", "1", 1),
("nestedcomment_sitereview_listing", "sitereview", \'{item:$subject} replied to a comment on {item:$owner}\'\'s {var:$listingtype} listing {item:$object:$title}: {body:$body}\', "1", "1", "1", "1", "1", 1),
("nestedcomment_sitereview_review", "sitereview", \'{item:$subject} replied to a comment on {item:$owner}\'\'s review {item:$object:$title}: {body:$body}\', "1", "1", "1", "1", "1", 1),
("follow_sitereview_wishlist", "sitereview", \'{item:$subject} is following {item:$owner}\'\'s {item:$object:wishlist}: {body:$body}\', 1, 1, 1, 1, 1, 1);');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("sitereview_discussion_reply", "sitereview", \'{item:$subject} has {item:$object:posted} on a {itemParent:$object::listing topic} you posted on.\', 0, ""),
("sitereview_discussion_response", "sitereview", \'{item:$subject} has {item:$object:posted} on a {itemParent:$object::listing topic} you created.\', 0, ""),
("sitereview_video_processed", "sitereview", \'Your {item:$object:listing video} is ready to be viewed.\', 0, ""),
("sitereview_video_processed_failed", "sitereview", \'Your {item:$object:listing video} has failed to process.\', 0, ""),
("sitereview_write_review", "sitereview", \'{item:$subject} has written a {item:$object:review} for the {itemParent:$object::listing}.\', "0", ""),
("sitereview_editorreview", "sitereview", \'{item:$subject} has written a {item:$object:review} for the {itemParent:$object::listing}.\', "0", ""),
("sitereview_wishlist_followers", "sitereview", \'{item:$subject} has added a new {item:$object:entry} in {var:$wishlist}.\', "0", ""),
("sitereview_approved_review", "sitereview", \'{item:$subject} has approved a {item:$object:review} by {var:$anonymous_name} on your {itemParent:$object::listing}.\', "0", ""),
("follow_sitereview_wishlist", "sitereview", \'{item:$subject} is following {item:$object}\', "0", "");');

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sitereviewvideo_main_browse", "sitereview", "Browse Videos", "", \'{"route":"sitereview_video_general"}\', "sitereviewvideo_main", "", 1);');

$db->query('INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
("sitereviewvideo_main", "standard", "Multiple Listing Types - Video Main Navigation Menu");');



// Insert default listing type in Communityad & Suggestion plugins.
$getListingType = $db->query("SELECT * FROM `engine4_sitereview_listingtypes` LIMIT 0 , 30")->fetchAll();
$isSuggestionEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("suggestion");
$isCommunityadEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("communityad");
$isSitemenuEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sitemenu");
if (!empty($getListingType)) {
    foreach ($getListingType as $listingType) {
        if (!empty($isSuggestionEnabled)) {
            // Make Table exist conditions.
            $notificationType = "sitereview_" . $listingType['listingtype_id'] . "_suggestion";
            $isExist = $db->query("SELECT * FROM `engine4_suggestion_module_settings` WHERE `notification_type` LIKE '" . $notificationType . "' LIMIT 1")->fetch();

            $isSuggModTable = $db->query("SHOW TABLES LIKE 'engine4_suggestion_module_settings'")->fetch();
            if (empty($isExist) && !empty($isSuggModTable)) {
                $tempReviewTitle = $listingType["title_singular"];
                $tempReviewTitle = strtolower($tempReviewTitle);
                $getReviewTitle = @ucfirst($tempReviewTitle);
                $suggSettingId = array("default" => 1, "listing_id" => $listingType['listingtype_id']);
                $suggNotificationType = $notificationType;
                $suggNotificationBody = '{item:$subject} has suggested to you a {item:$object:' . $tempReviewTitle . '}.';
                $suggestionModuleTable = Engine_Api::_()->getItemTable('suggestion_modinfo');
                $suggestionModuleTableName = $suggestionModuleTable->info('name');

                // Insert Notification Type in notification table.
                $db->query("INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type` , `module` , `body` , `is_request` ,`handler`) VALUES ('$suggNotificationType', 'suggestion', '$suggNotificationBody', 1, 'suggestion.widget.get-notify')");

                // Insert in Mail Template Table.
                $emailtemType = 'notify_' . $suggNotificationType;
                $db->query("INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES ('$emailtemType', 'suggestion', '[suggestion_sender], [suggestion_entity], [email], [link]'
      );");

                // Show "Suggest to Friend" link on "Listing Profile Page".
                $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name` , `module` , `label` , `plugin` ,`params`, `menu`, `enabled`, `custom`, `order`) VALUES ("sitereview_gutter_suggesttofriend_' . $listingType['listingtype_id'] . '", "suggestion", "Suggest to Friends", \'Suggestion_Plugin_Menus::showSitereview\', \'{"route":"suggest_to_friend_link","class":"buttonlink icon_review_friend_suggestion smoothbox", "listing_id": "' . $listingType['listingtype_id'] . '", "type":"popup"}\', "sitereview_gutter_listtype_' . $listingType['listingtype_id'] . '", 1, 0, 999 )');

                // Insert in Language Files.
                $language1 = array('You have a ' . $getReviewTitle . ' suggestion');
                $language2 = array('View all ' . $getReviewTitle . ' suggestions');
                $language3 = array('This ' . $tempReviewTitle . ' was suggested by');

                $temprequestWidgetLan = "sitereview " . $listingType['listingtype_id'] . " suggestion";
                $requestTab = array(
                    "%s " . $temprequestWidgetLan => array("%s " . strtolower($getReviewTitle) . " suggestion", "%s " . strtolower($getReviewTitle) . " suggestions")
                );


                $languageModTitle = "SITEREVIEW_" . $listingType['listingtype_id'];
                $makeEmailArray = array(
                    "_EMAIL_NOTIFY_" . $languageModTitle . "_SUGGESTION_TITLE" => $getReviewTitle . " Suggestion",
                    "_EMAIL_NOTIFY_" . $languageModTitle . "_SUGGESTION_DESCRIPTION" => "This email is sent to the member when someone suggest a " . $getReviewTitle . '.',
                    "_EMAIL_NOTIFY_" . $languageModTitle . "_SUGGESTION_SUBJECT" => $getReviewTitle . " Suggestion",
                    "_EMAIL_NOTIFY_" . $languageModTitle . "_SUGGESTION_BODY" => "[header]

      [sender_title] has suggested to you a " . $getReviewTitle . ". To view this suggestion please click on: <a href='http://[host][object_link]'>http://[host][object_link]</a>.

      [footer]"
                );
                $userSettingsNotfication = array("ACTIVITY_TYPE_" . $languageModTitle . "_SUGGESTION" => "When I receive a " . strtolower($getReviewTitle) . " suggestion.");
                $userNotification = array($notificationLanguage => $notificationLanguage);

                $this->addPhraseAction($makeEmailArray);
                $this->addPhraseAction($userSettingsNotfication);
                $this->addPhraseAction($userNotification);

                $this->addPhraseAction($language1);
                $this->addPhraseAction($language2);
                $this->addPhraseAction($language3);
                $this->addPhraseAction($requestTab);

                // Insert in Suggestion modules tables.
                $row = $suggestionModuleTable->createRow();
                $row->module = "sitereview";
                $row->item_type = "sitereview_listing";
                $row->field_name = "listing_id";
                $row->owner_field = "owner_id";
                $row->item_title = $getReviewTitle;
                $row->button_title = "View this " . @ucfirst($tempReviewTitle);
                $row->enabled = "1";
                $row->notification_type = $suggNotificationType;
                $row->quality = "1";
                $row->link = "1";
                $row->popup = "1";
                $row->recommendation = "1";
                $row->default = "1";
                $row->settings = @serialize($suggSettingId);
                $row->save();
            }
        }

        if (!empty($isCommunityadEnabled)) {
            $communityadModuleTable = Engine_Api::_()->getDbTable('modules', 'communityad');
            $communityadModuleTableName = $communityadModuleTable->info('name');
            $temTableName = "sitereview_listing_" . $listingType["listingtype_id"];

            $isAdsExist = $db->query("SELECT * FROM `engine4_communityad_modules` WHERE `table_name` LIKE '" . $temTableName . "' LIMIT 1")->fetch();
            if (empty($isAdsExist)) {
                $row = $communityadModuleTable->createRow();
                $row->module_name = "sitereview";
                $row->module_title = $listingType["title_singular"];
                $row->table_name = $temTableName;
                $row->title_field = "title";
                $row->body_field = "body";
                $row->owner_field = "owner_id";
                $row->displayable = "7";
                $row->is_delete = "1";
                $row->save();
            }
        }

        //WORK FOR ADDING DEFAULT LISTING TYPE IN SITEMENU MODULES
        if (!empty($isSitemenuEnabled)) {
            $sitemenuModuleTable = Engine_Api::_()->getDbTable('modules', 'sitemenu');
            $tempTableName = "sitereview_listing_" . $listingType["listingtype_id"];

            $isContentModuleExist = $db->query("SELECT * FROM `engine4_sitemenu_modules` WHERE `item_type` LIKE '" . $tempTableName . "' LIMIT 1")->fetch();
            if (empty($isContentModuleExist)) {
                $row = $sitemenuModuleTable->createRow();
                $row->module_name = "sitereview";
                $row->module_title = $listingType["title_singular"];
                $row->item_type = $tempTableName;
                $row->title_field = "title";
                $row->body_field = "body";
                $row->owner_field = "owner_id";
                $row->like_field = "like_count";
                $row->comment_field = "comment_count";
                $row->date_field = "creation_date";
                $row->featured_field = "featured";
                $row->sponsored_field = "sponsored";
                $row->status = "1";
                $row->image_option = "1";
                $row->category_name = "sitereview_category";
                $row->category_title_field = "category_name";
                $row->is_delete = "1";
                $row->save();
            }
        }
    }
}

$contentTable = Engine_Api::_()->getDbtable('content', 'core');
$contentTableName = $contentTable->info('name');

//Check if it's already been placed
$select = new Zend_Db_Select($db);
$select
        ->from('engine4_core_pages')
        ->where('name = ?', 'sitereview_video_view')
        ->limit(1);

$info = $select->query()->fetch();

if (empty($info)) {
    $db->insert('engine4_core_pages', array(
        'name' => 'sitereview_video_view',
        'displayname' => 'Multiple Listing Types - Video View Page',
        'title' => 'Video Profile',
        'description' => 'This is the video view page.',
        'custom' => 0,
        'provides' => 'subject=sitereview',
    ));
    $page_id = $db->lastInsertId('engine4_core_pages');

    //containers
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'order' => 1,
        'params' => '',
    ));
    $container_id = $db->lastInsertId('engine4_core_content');

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'right',
        'parent_content_id' => $container_id,
        'order' => 1,
        'params' => '',
    ));
    $right_id = $db->lastInsertId('engine4_core_content');

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $container_id,
        'order' => 3,
        'params' => '',
    ));
    $middle_id = $db->lastInsertId('engine4_core_content');

    //middle column content
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.video-content',
        'parent_content_id' => $middle_id,
        'order' => 1,
        'params' => '',
    ));

    //right column
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.show-same-tags',
        'parent_content_id' => $right_id,
        'order' => 1,
        'params' => '{"title":"Similar Videos","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.show-also-liked',
        'parent_content_id' => $right_id,
        'order' => 2,
        'params' => '{"title":"People Also Liked","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.show-same-poster',
        'parent_content_id' => $right_id,
        'order' => 3,
        'params' => '{"title":"Other Videos From Listing","nomobile":"1"}',
    ));
}

//CREATE BROWSE, HOME AND PROFILE PAGE FOR THIS LISTING TYPE
Engine_Api::_()->getApi('listingType', 'sitereview')->defaultCreation(1);

//WISHLIST PROFILE PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_wishlist_profile")
        ->limit(1)
        ->query()
        ->fetchColumn();

if (!$page_id) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => "sitereview_wishlist_profile",
        'displayname' => 'Multiple Listing Types - Wishlist Profile',
        'title' => 'Wishlist Profile',
        'description' => 'This is the wishlist profile page.',
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

    //INSERT TOP-MIDDLE
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $top_container_id,
        'order' => $containerCount++,
    ));
    $top_middle_id = $db->lastInsertId();
    //MAIN CONTAINER
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
        'order' => $containerCount++,
    ));
    $main_container_id = $db->lastInsertId();

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
        'name' => 'sitereview.listtypes-categories',
        'parent_content_id' => $top_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-profile-items',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"followLike":["follow","like"],"shareOptions":["siteShare","friend","report","print","socialShare"],"viewTypes":["list","pin"],"statistics":["likeCount","reviewCount"],"statisticsWishlist":["entryCount","likeCount","viewCount","followCount"],"show_buttons":["wishlist","comment","like","share","facebook","pinit"]}',
    ));
}

//WISHLIST HOME PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_wishlist_browse")
        ->limit(1)
        ->query()
        ->fetchColumn();

if (!$page_id) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => "sitereview_wishlist_browse",
        'displayname' => 'Multiple Listing Types - Browse Wishlists',
        'title' => 'Browse Wishlists',
        'description' => 'This is the wishlist browse page.',
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

    //RIGHT CONTAINER
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $page_id,
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
    ));
    $right_container_id = $db->lastInsertId();

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
        'name' => 'sitereview.wishlist-browse-search',
        'parent_content_id' => $top_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-creation-link',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-listings',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"My Friends\' Wishlists","type":"friends","statisticsWishlist":["likeCount","followCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-listings',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Wishlists Having Most Items","orderby":"total_item","statisticsWishlist":["entryCount","followCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-listings',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Most Followed Wishlists","orderby":"follow_count","statisticsWishlist":["entryCount","followCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-listings',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Most Liked Wishlists","orderby":"like_count","statisticsWishlist":["likeCount","followCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-listings',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Most Viewed Wishlists","orderby":"view_count","statisticsWishlist":["likeCount","viewCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.wishlist-browse',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"followLike":["follow","like"],"viewTypes":["list","grid"],"statisticsWishlist":["entryCount","likeCount","viewCount","followCount"],"viewTypeDefault":"grid","listThumbsCount":"4","itemCount":"20"}',
    ));
}

//REVIEW PROFILE PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_review_view")
        ->limit(1)
        ->query()
        ->fetchColumn();

//CREATE PAGE IF NOT EXIST
if (!$page_id) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => "sitereview_review_view",
        'displayname' => 'Multiple Listing Types - Review Profile',
        'title' => 'Review Profile',
        'description' => 'This is the review profile page.',
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

    //RIGHT CONTAINER
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $page_id,
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
    ));
    $right_container_id = $db->lastInsertId();

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
        'name' => 'sitereview.listtypes-categories',
        'parent_content_id' => $top_middle_id,
        'order' => $widgetCount++,
        'params' => '',
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
        'name' => 'sitereview.profile-review-breadcrumb-sitereview',
        'parent_content_id' => $top_middle_id,
        'order' => $widgetCount++,
        'params' => '{"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.price-info-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Where to Buy","titleCount":true,"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.quick-specification-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Quick Specifications","titleCount":true,"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.socialshare-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Social Share","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.related-listings-view-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Related Listings","statistics":["likeCount","reviewCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.ownerreviews-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"statistics":["likeCount","replyCount","commentCount"],"nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.profile-review-sitereview',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"title":"","titleCount":true,"loaded_by_ajax":"1","name":"sitereview.profile-review-sitereview"}',
    ));
}

//CATEGORIES HOME PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_index_categories")
        ->limit(1)
        ->query()
        ->fetchColumn();

if (!$page_id) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => "sitereview_index_categories",
        'displayname' => 'Multiple Listing Types - Categories Home',
        'title' => 'Categories Home',
        'description' => 'This is the categories home page.',
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
        'name' => 'sitereview.categories-sponsored',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Sponsored Categories","titleCount":"true", "listingtype_id":"-1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.categories-home',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"listingtype_id":"-1"}',
    ));
}

//MEMBER PROFILE PAGE WIDGETS
$page_id = $db->select()
        ->from('engine4_core_pages', array('page_id'))
        ->where('name =?', 'user_profile_index')
        ->limit(1)
        ->query()
        ->fetchColumn();

if (!empty($page_id)) {

    $tab_id = $db->select()
            ->from('engine4_core_content', array('content_id'))
            ->where('page_id =?', $page_id)
            ->where('type = ?', 'widget')
            ->where('name = ?', 'core.container-tabs')
            ->limit(1)
            ->query()
            ->fetchColumn();

    if (!empty($tab_id)) {

        $content_id = $db->select()
                ->from('engine4_core_content', array('content_id'))
                ->where('page_id =?', $page_id)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'sitereview.profile-sitereview')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (empty($content_id)) {
            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.profile-sitereview',
                'parent_content_id' => $tab_id,
                'order' => 999,
                'params' => '{"title":"Listings","titleCount":"true","statistics":["viewCount","likeCount","commentCount","reviewCount"]}',
            ));
        }

        $content_id = $db->select()
                ->from('engine4_core_content', array('content_id'))
                ->where('page_id =?', $page_id)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'sitereview.editor-profile-reviews-sitereview')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (empty($content_id)) {

            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.editor-profile-reviews-sitereview',
                'parent_content_id' => $tab_id,
                'order' => 999,
                'params' => '{"title":"Reviews As Editor","type":"editor"}',
            ));

            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.editor-profile-reviews-sitereview',
                'parent_content_id' => $tab_id,
                'order' => 999,
                'params' => '{"title":"Reviews As User","type":"user", "onlyListingtypeEditorReviews":"1"}',
            ));
        }
    }
}

//COMPARE PRODUCTS PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_compare_compare")
        ->limit(1)
        ->query()
        ->fetchColumn();

if (empty($page_id)) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => 'sitereview_compare_compare',
        'displayname' => 'Multiple Listing Types - Listings Comparison',
        'title' => 'Listings Comparison',
        'description' => 'This is the listings comparison page.',
        'custom' => 0
    ));
    $page_id = $db->lastInsertId('engine4_core_pages');

    //MAIN CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'order' => $containerCount++,
        'params' => '',
    ));
    $main_container_id = $db->lastInsertId('engine4_core_content');

    //MIDDLE CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
        'params' => '',
    ));
    $main_middle_id = $db->lastInsertId('engine4_core_content');

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.listtypes-categories',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'seaocore.scroll-top',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.content',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));
}

//REVIEW BROWSE PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_review_browse")
        ->limit(1)
        ->query()
        ->fetchColumn();

if (empty($page_id)) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => 'sitereview_review_browse',
        'displayname' => 'Multiple Listing Types - Browse Reviews',
        'title' => 'Browse Reviews',
        'description' => 'This is the review browse page.',
        'custom' => 0
    ));

    $page_id = $db->lastInsertId('engine4_core_pages');

    //TOP CONTAINER
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $page_id,
        'order' => $containerCount++,
    ));
    $top_container_id = $db->lastInsertId();

    //INSERT TOP-MIDDLE
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $top_container_id,
        'order' => $containerCount++,
    ));
    $top_middle_id = $db->lastInsertId();

    //MAIN CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'order' => $containerCount++,
        'params' => '',
    ));
    $main_container_id = $db->lastInsertId('engine4_core_content');

    //RIGHT CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'right',
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
        'params' => '',
    ));
    $right_container_id = $db->lastInsertId('engine4_core_content');

    //MIDDLE CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
        'params' => '',
    ));
    $main_middle_id = $db->lastInsertId('engine4_core_content');

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
        'name' => 'seaocore.scroll-top',
        'parent_content_id' => $top_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.review-of-the-day',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Review of the Day","titleCount":"true","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.review-browse-search',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.reviews-statistics',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Reviews Statistics","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.content',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));
}

//EDITOR HOME PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_editor_home")
        ->limit(1)
        ->query()
        ->fetchColumn();

//CREATE PAGE IF NOT EXIST
if (!$page_id) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => "sitereview_editor_home",
        'displayname' => 'Multiple Listing Types - Editors Home',
        'title' => 'Editors Home',
        'description' => 'This is the editors home page.',
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

    //RIGHT CONTAINER
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $page_id,
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
    ));
    $right_container_id = $db->lastInsertId();

    //LEFT CONTAINER
    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'left',
        'page_id' => $page_id,
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
    ));
    $left_container_id = $db->lastInsertId();

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
        'name' => 'sitereview.popular-reviews-sitereview',
        'parent_content_id' => $left_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Most Recent Reviews","groupby":"0","type":"editor","popularity":"review_id","titleCount":"true","itemCount":"5","statistics":"","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.popular-reviews-sitereview',
        'parent_content_id' => $left_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Most Viewed Reviews","groupby":"0","type":"editor","popularity":"view_count","titleCount":"true","itemCount":"5","statistics":"","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-featured-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Featured Editor","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editors-home-statistics-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Statistics","titleCount":"true","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.top-reviewers-sitereview',
        'parent_content_id' => $right_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Top Reviewers","type":"editor","titleCount":"true","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editors-home',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Review Editors","titleCount":"true"}',
    ));
}

//EDITOR PROFILE PAGE
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', "sitereview_editor_profile")
        ->limit(1)
        ->query()
        ->fetchColumn();

if (!$page_id) {

    $containerCount = 0;
    $widgetCount = 0;

    $db->insert('engine4_core_pages', array(
        'name' => "sitereview_editor_profile",
        'displayname' => 'Multiple Listing Types - Editor Profile',
        'title' => 'Editor Profile',
        'description' => 'This is the editor profile page.',
        'custom' => 0,
    ));
    $page_id = $db->lastInsertId();

    //MAIN CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'order' => $containerCount++,
        'params' => '',
    ));
    $main_container_id = $db->lastInsertId('engine4_core_content');

    //RIGHT CONTAINER
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'left',
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
        'params' => '',
    ));
    $left_container_id = $db->lastInsertId('engine4_core_content');

    //MIDDLE CONTAINER  
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $main_container_id,
        'order' => $containerCount++,
        'params' => '',
    ));
    $main_middle_id = $db->lastInsertId('engine4_core_content');

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-photo-sitereview',
        'parent_content_id' => $left_container_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-info',
        'parent_content_id' => $left_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"About Editor","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-statistics',
        'parent_content_id' => $left_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Statistics","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.socialshare-sitereview',
        'parent_content_id' => $left_container_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Social Share","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'seaocore.scroll-top',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-title',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.container-tabs',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"max":"6"}',
    ));
    $tab_id = $db->lastInsertId('engine4_core_content');

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-reviews-sitereview',
        'parent_content_id' => $tab_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Reviews As Editor","type":"editor"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-reviews-sitereview',
        'parent_content_id' => $tab_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Reviews As User","type":"user", "onlyListingtypeEditorReviews":"0"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editor-replies-sitereview',
        'parent_content_id' => $tab_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Comments", "onlyListingtypeEditor":"0"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.editors-sitereview',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"title":"Similar Editors","listingtype_id":"-1","nomobile":"1"}',
    ));

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.content',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));
}

$db->query("INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('sitereview_main_common', 'standard', 'Multiple Listing Types - Common Main Navigation Menu')
");

$db->query('
  
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES

("sitereview_admin_main_listingtypes", "sitereview", "Manage Listing Types", "", \'{"route":"admin_default","module":"sitereview","controller":"general","action":"listing-types"}\', "sitereview_admin_main", "", 1, 0, 5), 

("sitereview_admin_main_level", "sitereview", "Member Level Settings", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"level-type"}\', "sitereview_admin_main", "", 1, 0, 10),

("sitereview_admin_main_categories", "sitereview", "Categories", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"categories"}\', "sitereview_admin_main", "", 1, 0, 15),

("sitereview_admin_main_fields", "sitereview", "Profile Fields", "", \'{"route":"admin_default","module":"sitereview","controller":"fields"}\', "sitereview_admin_main", "", 1, 0, 20),

("sitereview_admin_main_profilemaps", "sitereview", "Category-Listing Profile Mapping", "", \'{"route":"admin_default","module":"sitereview","controller":"profilemaps","action":"manage"}\', "sitereview_admin_main", "", 1, 0, 25),

("sitereview_admin_main_review", "sitereview", "Reviews & Ratings", "", \'{"route":"admin_default","module":"sitereview","controller":"review"}\', "sitereview_admin_main", "", 1, 0, 30),

("sitereview_admin_reviewmain_general", "sitereview", "Review Settings", "", \'{"route":"admin_default","module":"sitereview","controller":"review"}\', "sitereview_admin_reviewmain", "", 1, 0, 1),

("sitereview_admin_reviewmain_manage", "sitereview", "Manage Reviews & Ratings", "", \'{"route":"admin_default","module":"sitereview","controller":"review", "action":"manage"}\', "sitereview_admin_reviewmain", "", 1, 0, 2),

("sitereview_admin_reviewmain_fields", "sitereview", "Review Profile Fields", "", \'{"route":"admin_default","module":"sitereview","controller":"fields-review"}\', "sitereview_admin_reviewmain", "", 1, 0, 3),

("sitereview_admin_reviewmain_profilemaps", "sitereview", "Category-Review Profile Mapping", "", \'{"route":"admin_default","module":"sitereview","controller":"profilemaps-review","action":"manage"}\', "sitereview_admin_reviewmain", "", 1, 0, 4),

("sitereview_admin_reviewmain_ratingparams", "sitereview", "Rating Parameters", "", \'{"route":"admin_default","module":"sitereview","controller":"ratingparameters","action":"manage"}\', "sitereview_admin_reviewmain", "", 1, 0, 5),

("sitereview_admin_main_manage", "sitereview", "Manage Listings", "", \'{"route":"admin_default","module":"sitereview","controller":"manage"}\', "sitereview_admin_main", "", 1, 0, 35),

("sitereview_admin_main_compare", "sitereview", "Comparison Settings","", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"compare"}\', "sitereview_admin_main", "", 1, 0, 40),

("sitereview_admin_main_editors", "sitereview", "Manage Editors", "", \'{"route":"admin_default","module":"sitereview","controller":"editors", "action":"manage"}\', "sitereview_admin_main", "", 1, 0, 45),

("sitereview_admin_main_wishlist", "sitereview", "Manage Wishlists", "", \'{"route":"admin_default","module":"sitereview","controller":"wishlist","action":"manage"}\', "sitereview_admin_main", "", 1, 0, 50),

("sitereview_admin_main_video", "sitereview", "Video Settings", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"show-video"}\', "sitereview_admin_main", "", 1, 0, 55),

("sitereview_admin_submain_general_tab", "sitereview", "Video Settings", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"show-video"}\', "sitereview_admin_submain", "", 1, 0, 1),

("sitereview_admin_submain_manage_tab", "sitereview", "Manage Review Videos", "", \'{"route":"admin_default","module":"sitereview","controller":"video","action": "manage"}\', "sitereview_admin_submain", "", 1, 0, 2),

("sitereview_admin_submain_utilities_tab", "sitereview", "Review Video Utilities", "", \'{"route":"admin_default","module":"sitereview","controller":"video", "action": "utility"}\', "sitereview_admin_submain", "", 1, 0, 3),

("sitereview_admin_main_wheretobuy", "sitereview", "Where To Buy", "", \'{"route":"admin_default","module":"sitereview","controller":"where-to-buy"}\', "sitereview_admin_main", "", 1, 0, 60),

("sitereview_admin_main_formsearch", "sitereview", "Search Form Settings", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"form-search"}\', "sitereview_admin_main", "", 1, 0, 65),

("sitereview_admin_main_statistic", "sitereview", "Statistics", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"statistic"}\', "sitereview_admin_main", "", 1, 0, 72),

("sitereview_admin_main_import", "sitereview", "Import", "", \'{"route":"admin_default","module":"sitereview","controller":"importlisting"}\', "sitereview_admin_main", "", 1, 0, 75),

("sitereview_admin_main_ads", "sitereview", "Ad Settings", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"adsettings"}\', "sitereview_admin_main", "", 1, 0, 80),

("sitereview_admin_main_integrations", "sitereview", "Plugin Integrations", "", \'{"route":"admin_default","module":"sitereview","controller":"settings","action":"integrations"}\', "sitereview_admin_main", "", 1, 0, 85)

 ');

$db->query("
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT 
              level_id as `level_id`, 
              'sitereview_listing' as `type`, 
              'where_to_buy_listtype_1' as `name`, 
              1 as `value`, 
              NULL as `params` 
        FROM `engine4_authorization_levels` WHERE `type` IN('moderator','admin','user');
      ");

$db->query("UPDATE `engine4_sitereview_categories` SET `apply_compare` = '1' WHERE `engine4_sitereview_categories`.`cat_dependency` = 0");
$wheretobuyIcon = array(
    "2" => 'amazon.gif',
    "3" => 'ebuy.gif',
    "4" => 'target.gif',
    "5" => 'tesco.png',
    "6" => 'best_buy.gif',
    "7" => 'comet.png',
    "8" => 'data_vision_computer_video.gif',
    "9" => 'newegg.gif',
    "10" => 'sears.gif',
    "11" => 'tiger_direct.gif',
    "12" => 'pc_connectiorr.gif',
    "13" => 'next_warehouse.gif',
    "14" => 'amazon_marketplace.gif',
    "15" => 'beachcamera.gif',
    "16" => "buydig.gif",
    "17" => "pcrush.gif"
);

$db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = '0' WHERE `engine4_activity_actiontypes`.`type` = 'video_sitereview_listtype_1' ");

$wheretobuyList = Engine_Api::_()->getItemTable('sitereview_wheretobuy')->getList();
$defaultPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . "application/modules/Sitereview/externals/images/wheretobuy/";
foreach ($wheretobuyList as $item):
    if (isset($wheretobuyIcon[$item->getIdentity()]) && $item->getIdentity() != 1 && empty($item->photo_id)) {
        $item->setPhoto($defaultPath . $wheretobuyIcon[$item->getIdentity()]);
    }
endforeach;


$select = new Zend_Db_Select($db);
$select
        ->from('engine4_core_modules')
        ->where('name = ?', 'siteevent')
        ->where('enabled = ?', 1);
$is_siteevent_object = $select->query()->fetchObject();
if (!empty($is_siteevent_object)) {

    $select = new Zend_Db_Select($db);
    $listingtypeObject = $select
            ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'))
            ->query()
            ->fetchAll();
    foreach ($listingtypeObject as $values) {
        $listingtype_id = $values['listingtype_id'];
        $title_singular = ucfirst($values['title_singular']);
        $db->query("INSERT IGNORE INTO `engine4_siteevent_modules` (`item_type`, `item_id`, `item_module`, `enabled`, `integrated`, `item_title`) VALUES ('sitereview_listing_$listingtype_id', 'listing_id', 'sitereview', '0', '0', '$title_singular Events')");
        $db->query("INSERT IGNORE INTO `engine4_core_settings` ( `name`, `value`) VALUES( 'siteevent.event.leader.owner.sitereview.listing.$listingtype_id', '0');");
    }

    $db->query('INSERT IGNORE INTO `engine4_core_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_manageevent", "siteevent", "Manage Events", "", \'{"uri":"admin/siteevent/manage/index/contentType/sitereview_listing_1/contentModule/sitereview"}\', "sitereview_admin_main", "", 1, 0, 83);');
}

$select = new Zend_Db_Select($db);
$select
        ->from('engine4_core_modules')
        ->where('name = ?', 'sitevideointegration')
        ->where('enabled = ?', 1);
$is_sitevideointegration_object = $select->query()->fetchObject();
if (!empty($is_sitevideointegration_object)) {

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


$select = new Zend_Db_Select($db);
$select
        ->from('engine4_core_modules')
        ->where('name = ?', 'documentintegration')
        ->where('enabled = ?', 1);
$is_document_object = $select->query()->fetchObject();
if (!empty($is_document_object)) {

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
    $listingtypeObject = $select
            ->from('engine4_sitereview_listingtypes', array('listingtype_id', 'title_singular'))
            ->query()
            ->fetchAll();
            
    foreach ($listingtypeObject as $values) {
        $listingtype_id = $values['listingtype_id'];
        $title_singular = ucfirst($values['title_singular']);

        $db->query("INSERT IGNORE INTO `engine4_sitecrowdfunding_modules` (`item_type`, `item_id`, `item_module`, `enabled`, `integrated`, `item_title`, `item_membertype`) VALUES ('sitereview_listing_$listingtype_id', 'listing_id', 'sitereview', '0', '0', '$title_singular Projects', '')");

        $db->query("INSERT IGNORE INTO `engine4_core_settings` ( `name`, `value`) VALUES( 'sitecrowdfunding.leader.owner.sitereview.listing.$listingtype_id', '0');");
    }

        $db->query('INSERT IGNORE INTO `engine4_core_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES("sitereview_admin_main_manageproject", "sitecrowdfunding", "Manage Projects", "", \'{"uri":"admin/sitecrowdfunding/manage/index/contentType/sitereview_listing_1/contentModule/sitereview"}\', "sitereview_admin_main", "", 0, 0, 24);');
    }
}
