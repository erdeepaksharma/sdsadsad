<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ListingType.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Api_ListingType extends Core_Api_Abstract {

  public function defaultCreation($listingTypeId, $main_menu = 1, $pinboard_layout = 0, $template_type = '') {

    //GET LISTINGTYPE ITEM
    $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingTypeId);

    if (empty($listingType)) {
      return;
    }

    $listingTypeApi = Engine_Api::_()->getApi('listingType', 'sitereview');
    $sitereviewListingtype = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype');
    if ($sitereviewListingtype && !empty($template_type) && $template_type != 'default_type') {
      Engine_Api::_()->sitereviewlistingtype()->defaultTemplate($listingType, $template_type);
    } else {

      if ($pinboard_layout) {
        $listingTypeApi->setPinBoardLayoutHomePage($listingType);
      } else {
        $listingTypeApi->homePageCreate($listingType);
      }
      $listingTypeApi->browsePageCreate($listingType);
      $listingTypeApi->mostratedPageCreate($listingType);
      $listingTypeApi->profilePageCreate($listingType);
    }
    
    $listingTypeApi->managePageCreate($listingType);
    $listingTypeApi->creationPageCreate($listingType);
    $listingTypeApi->editPageCreate($listingType);

    //START PACKAGE WORK
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $listingTypeApi->freePackageCreate($listingType);
      $listingTypeApi->createPackageNavigation($listingType->listingtype_id);
    }
    //END PACKAGE WORK

    if (isset($listingType->claimlink) && $listingType->claimlink)
      $listingTypeApi->createClaimPage($listingType);

    $listingTypeApi->browseLocationPageCreate($listingType);

    $listingTypeApi->mainNavigationCreate($listingType, $main_menu, $template_type);
    $listingTypeApi->gutterNavigationCreate($listingType);
    $listingTypeApi->searchFormSettingCreate($listingType);
    $listingTypeApi->activityFeedQueryCreate($listingType);
    // $listingTypeApi->setActivityFeedsLanguage($listingType);
    $listingTypeApi->addSuperEditor($listingType);
    $listingTypeApi->addBannedUrls($listingType);

    Engine_Api::_()->getApi('language', 'sitereview')->setTranslateForListType($listingType);

    if ($listingTypeId == 1) {
      $listingTypeApi->defaultMemberLevelSettings($listingType->listingtype_id);
    }

    //INTEGRATION WITH FACEBOOK PLUGIN WHEN THIS NEW LISTING TYPE CREATES.
    $fbmodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('facebookse');
    $checkVersion = Engine_Api::_()->sitereview()->checkVersion($fbmodule->version, '4.2.7p1');
    if (!empty($fbmodule) && !empty($fbmodule->enabled) && $checkVersion == 1) {
      Engine_Api::_()->facebookse()->addReviewList($listingType, 'add');
    }

    //INTEGRATION WITH SITEMOBILE PLUGIN WHEN THIS NEW LISTING TYPE CREATES.    
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile')) {
      $moduleTable = Engine_Api::_()->getDbTable('modules', 'sitemobile');
      $select = $moduleTable->select()->from($moduleTable->info('name'))->where('name = ?', 'sitereview')->where('integrated = ?', 1);
      $is_sitemobile_object = $select->query()->fetchObject();
      if ($is_sitemobile_object) {
        $listingTypeApi = Engine_Api::_()->getApi('listingTypeSM', 'sitereview');
        $listingTypeApi->defaultCreation($listingTypeId, 1, 0, 'engine4_sitemobile_pages', 'engine4_sitemobile_content');
        $listingTypeApi->defaultCreation($listingTypeId, 1, 0, 'engine4_sitemobile_tablet_pages', 'engine4_sitemobile_tablet_content');
        //NOW CHECK IF THERE IS SITEMOBILEAPP MODULE THEN WE WILL ALSO CREATE LAYOUTS FOR SITEMOBILEAPP.
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobileapp')) {
          $listingTypeApi->defaultCreation($listingTypeId, 1, 0, 'engine4_sitemobileapp_pages', 'engine4_sitemobileapp_content');
          $listingTypeApi->defaultCreation($listingTypeId, 1, 0, 'engine4_sitemobileapp_tablet_pages', 'engine4_sitemobileapp_tablet_content');
        }
      }
    }
  }

  public function defaultMemberLevelSettings($listingTypeId) {

    $getListingReviewType = $this->getListingReviewType();

    if (empty($listingTypeId) || empty($getListingReviewType)) {
      return;
    }

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    $var_array1 = array("auth_topic_listtype_$listingTypeId", "auth_comment_listtype_$listingTypeId", "auth_photo_listtype_$listingTypeId", "auth_topic_listtype_$listingTypeId", "auth_video_listtype_$listingTypeId");
    foreach ($var_array1 as $value) {
      $db->query('
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT level_id as `level_id`, 
          "sitereview_listing" as `type`, 
          "' . $value . '" as `name`, 
          5 as `value`, 
          \'["registered","owner_network","owner_member_member","owner_member","owner"]\' as `params` 
        FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");
      ');
    }

    $var_array2 = array("create_listtype_$listingTypeId", "style_listtype_$listingTypeId", "approved_listtype_$listingTypeId", "review_reply_listtype_$listingTypeId", "contact_listtype_$listingTypeId", "metakeyword_listtype_$listingTypeId", "overview_listtype_$listingTypeId", "review_update_listtype_$listingTypeId", "where_to_buy_listtype_$listingTypeId");
    foreach ($var_array2 as $value) {
      $db->query("
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT 
              level_id as `level_id`, 
              'sitereview_listing' as `type`, 
              '$value' as `name`, 
              1 as `value`, 
              NULL as `params` 
        FROM `engine4_authorization_levels` WHERE `type` IN('moderator','admin','user');
      ");
    }

    $var_array3 = array("delete_listtype_$listingTypeId", "edit_listtype_$listingTypeId", "view_listtype_$listingTypeId", "topic_listtype_$listingTypeId", "comment_listtype_$listingTypeId", "css_listtype_$listingTypeId", "photo_listtype_$listingTypeId", "video_listtype_$listingTypeId", "review_delete_listtype_$listingTypeId");
    foreach ($var_array3 as $value) {
      $db->query("
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT 
                level_id as `level_id`, 
                'sitereview_listing' as `type`, 
                '$value' as `name`, 
                2 as `value`, 
                NULL as `params` 
        FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
      ");
    }

    $var_array4 = array("delete_listtype_$listingTypeId", "css_listtype_$listingTypeId", "edit_listtype_$listingTypeId", "view_listtype_$listingTypeId", "topic_listtype_$listingTypeId", "comment_listtype_$listingTypeId", "photo_listtype_$listingTypeId", "video_listtype_$listingTypeId");
    foreach ($var_array4 as $value) {
      $db->query("
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT 
          level_id as `level_id`, 
          'sitereview_listing' as `type`, 
          '$value' as `name`, 
          1 as `value`, 
          NULL as `params` 
        FROM `engine4_authorization_levels` WHERE `type` IN('user');
      ");
    }

    $var_array5 = array("featured_listtype_$listingTypeId", "sponsored_listtype_$listingTypeId");
    foreach ($var_array5 as $value) {
      $db->query("
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT 
          level_id as `level_id`, 
          'sitereview_listing' as `type`, 
          '$value' as `name`, 
          0 as `value`, 
          NULL as `params` 
        FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin', 'user'); 
      ");
    }

    $db->query('
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT level_id as `level_id`, 
        "sitereview_listing" as `type`, 
        "auth_view_listtype_' . $listingTypeId . '" as `name`,
        5 as `value`, 
        \'["everyone","registered","owner_network","owner_member_member","owner_member","owner"]\' as `params` 
      FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");
    ');

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'max_listtype_$listingTypeId' as `name`, 
        3 as `value`, 
        1000 as `params` 
      FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
    ");

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'max_listtype_$listingTypeId' as `name`, 
        3 as `value`, 
        50 as `params` 
      FROM `engine4_authorization_levels` WHERE `type` IN('user');
    ");

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'view_listtype_$listingTypeId' as `name`, 
        1 as `value`, 
        NULL as `params` 
      FROM `engine4_authorization_levels` WHERE `type` IN('public');
    ");

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'review_create_listtype_$listingTypeId' as `name`, 
        1 as `value`, 
        NULL as `params` 
      FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin', 'user', 'public');
    ");

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'review_delete_listtype_$listingTypeId' as `name`, 
        0 as `value`, 
        NULL as `params` 
      FROM `engine4_authorization_levels` WHERE `type` IN('user');
    ");

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'auth_html_listtype_$listingTypeId' as `name`, 
        3 as `value`, 
        'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr' as `params` 
      FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
    ");

    $db->query("
      INSERT IGNORE INTO `engine4_authorization_permissions` 
      SELECT 
        level_id as `level_id`, 
        'sitereview_listing' as `type`, 
        'claim_listtype_$listingTypeId' as `name`, 
        1 as `value`, 
        NULL as `params` 
      FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin', 'user', 'public');
    ");
  }

  //DEFAULT PACKAGE CREATION WORK LISTINGTYPE
  public function freePackageCreate($listingType) {
    global $sitereviewPackageInfo;
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUpper = strtoupper($listingType->title_singular);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $sitereviewPackageTable = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting');
    $alreadyExistPackage = $sitereviewPackageTable->select()
            ->from($sitereviewPackageTable->info('name'), array('listingtype_id'))
            ->where('listingtype_id = ? ', $listingTypeId)
            ->where('`defaultpackage` = ? ', 1)
            ->query()
            ->fetchColumn();
    if (!empty($sitereviewPackageInfo) && empty($alreadyExistPackage)) {
      $db->query("INSERT IGNORE INTO `engine4_sitereviewpaidlisting_packages` (`listingtype_id`, `title`, `description`, `level_id`, `price`, `recurrence`, `recurrence_type`, `duration`, `duration_type`, `sponsored`, `featured`, `overview`, `map`, `video`, `video_count`, `photo`, `photo_count`, `wishlist`, `user_review`, `approved`, `enabled`, `defaultpackage`, `renew`, `renew_before`, `profile`, `profilefields`, `order`, `update_list`) VALUES
	($listingTypeId, 'Free Listing Package', 'This is a free listing package. One does not need to pay for creating a listing of this package.', '0', '0.00', 0, 'forever', 0, 'forever', 0, 0, 1, 1, 1, 10, 1, 10, 1, 1, 1, 1, 1, 0, 0, 1, NULL, 0, 1);");
    }

    if (!empty($sitereviewPackageInfo) && $listingType->package) {
      $db->query('INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
("sitereview_' . $titleSinLc . '_active", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [site_title][list_title], [list_description],[object_link]"),
("sitereview_' . $titleSinLc . '_cancelled", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_title_with_link], [object_link]"),
("sitereview_' . $titleSinLc . '_expired", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_title_with_link], [object_link]"),
("sitereview_' . $titleSinLc . '_renew", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_title_with_link], [object_link]"),
("sitereview_' . $titleSinLc . '_overdue", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_title_with_link], [object_link]"),
("sitereview_' . $titleSinLc . '_pending", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description], [object_link]"),
("sitereview_' . $titleSinLc . '_refunded", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description], [object_link]"),
("sitereview_' . $titleSinLc . '_approved", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description], [object_link]"),
("sitereview_' . $titleSinLc . '_disapproved", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description], [object_link]"),
("sitereview_' . $titleSinLc . '_approval_pending", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description], [object_link]"),
("sitereview_' . $titleSinLc . '_declined", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description]"),
("sitereview_' . $titleSinLc . '_recurrence", "sitereview", "[host], [email], [recipient_title], [recipient_link], [recipient_photo], [list_title], [list_description], [object_link]");');

      //PACKAGE CREATE PAGE CREATION
      $page_id = $db->select()
              ->from('engine4_core_pages', 'page_id')
              ->where('name = ?', "sitereviewpaidlisting_package_index_listtype_$listingTypeId")
              ->limit(1)
              ->query()
              ->fetchColumn();
      if (empty($page_id)) {

        $containerCount = 0;

        //CREATE PAGE
        $db->insert('engine4_core_pages', array(
            'name' => "sitereviewpaidlisting_package_index_listtype_$listingTypeId",
            'displayname' => 'Multiple Listing Types - Packages for ' . $titlePluUc,
            'title' => 'Packages for '.$titlePluUc,
            'description' => 'This is the Packages page for '.$titleSinLc.'.',
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
            'params' => '',
        ));

        $db->insert('engine4_core_content', array(
            'type' => 'widget',
            'name' => 'sitereviewpaidlisting.list-packages',
            'page_id' => $page_id,
            'parent_content_id' => $main_middle_id,
            'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
            'order' => 1,
        ));
      }
    }
  }

  //HOME PAGE WORK
  public function homePageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_index_home_listtype_" . $listingTypeId)
            ->limit(1)
            ->query()
            ->fetchColumn();

    if (empty($page_id)) {

      $containerCount = 0;
      $widgetCount = 0;

      //CREATE PAGE
      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_index_home_listtype_" . $listingTypeId,
          'displayname' => 'Multiple Listing Types - ' . $titlePluUc . ' Home',
          'title' => $titlePluUc . ' Home',
          'description' => 'This is the ' . $titleSinLc . ' home page.',
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

      //LEFT CONTAINER
      $db->insert('engine4_core_content', array(
          'type' => 'container',
          'name' => 'left',
          'page_id' => $page_id,
          'parent_content_id' => $main_container_id,
          'order' => $containerCount++,
      ));
      $left_container_id = $db->lastInsertId();

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
          'name' => 'sitereview.zerolisting-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.slideshow-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Featured ' . $titlePluUc . '","listingtype_id":"' . $listingTypeId . '","titleCount":"true","fea_spo":"featured","statistics":["viewCount","likeCount","commentCount","reviewCount"],"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.sponsored-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Sponsored ' . $titlePluUc . '","listingtype_id":"' . $listingTypeId . '","titleCount":"true","viewType":"0","fea_spo":"sponsored","itemCount":"3","showOptions":["category","rating","review","compare","wishlist"],"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.categories-middle-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Categories","listingtype_id":"' . $listingTypeId . '","titleCount":"true","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.category-listings-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Popular ' . $titlePluUc . '","listingtype_id":"' . $listingTypeId . '","titleCount":"true","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.recently-popular-random-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"statistics":["viewCount","likeCount","commentCount","reviewCount"],"listingtype_id":"' . $listingTypeId . '","layouts_views":["listZZZview","gridZZZview","mapZZZview"],"ajaxTabs":["recent","mostZZZreviewed","featured","sponsored", "expiring_soon"],"name":"sitereview.recently-popular-random-sitereview","showContent":["price","location"]}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.search-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.newlisting-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.mostrated-browse-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));


      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.popularlocation-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Popular Locations","listingtype_id":"' . $listingTypeId . '","titleCount":"true","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.tagcloud-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title": "Popular Tags (%s)","listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.listtypes-categories',
          'parent_content_id' => $left_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","viewDisplayHR":"0","title":"","nomobile":"0","name":"sitereview.listtypes-categories","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.item-sitereview',
          'parent_content_id' => $left_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"' . $titleSinUc . ' of the Day","titleCount":"true","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.listings-sitereview',
          'parent_content_id' => $left_container_id,
          'order' => $widgetCount++,
          'params' => '{"popularity":"rating_avg","title":"Top Rated ' . $titlePluUc . '","listingtype_id":"' . $listingTypeId . '","titleCount":"true","statistics":["likeCount","reviewCount"],"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.listings-sitereview',
          'parent_content_id' => $left_container_id,
          'order' => $widgetCount++,
          'params' => '{"popularity":"like_count","title":"Most Liked ' . $titlePluUc . '","listingtype_id":"' . $listingTypeId . '","titleCount":"true","statistics":["likeCount","reviewCount"],"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.listings-sitereview',
          'parent_content_id' => $left_container_id,
          'order' => $widgetCount++,
          'params' => '{"popularity":"comment_count","title":"Most Commented ' . $titlePluUc . '","listingtype_id":"' . $listingTypeId . '","titleCount":"true","statistics":["commentCount","reviewCount"],"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.recently-viewed-sitereview',
          'parent_content_id' => $left_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Recently Viewed By Friends","listingtype_id":"' . $listingTypeId . '","titleCount":"true","statistics":["likeCount","reviewCount"],"nomobile":"1"}',
      ));
    }
  }

//BROWSE PAGE WORK
  //BROWSE PAGE WORK
  public function mostratedPageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_index_top-rated_listtype_" . $listingTypeId)
            ->limit(1)
            ->query()
            ->fetchColumn();

    if (!$page_id) {

      $containerCount = 0;
      $widgetCount = 0;

      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_index_top-rated_listtype_" . $listingTypeId,
          'displayname' => 'Multiple Listing Types - Browse Top Rated ' . $titlePluUc,
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
          'name' => 'sitereview.browse-breadcrumb-sitereview',
          'parent_content_id' => $top_middle_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.categories-sidebar-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Categories","listingtype_id":"' . $listingTypeId . '","titleCount":"true","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.search-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.newlisting-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.tagcloud-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title": "Popular Tags (%s)","listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.categories-banner-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.rated-listings-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","ratingType":"rating_both","statistics":["viewCount","likeCount","commentCount","reviewCount"],"layouts_views":["1","2","3"]}',
      ));
    }
  }

  //BROWSE PAGE WORK
  public function browsePageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_index_index_listtype_" . $listingTypeId)
            ->limit(1)
            ->query()
            ->fetchColumn();

    if (!$page_id) {

      $containerCount = 0;
      $widgetCount = 0;

      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_index_index_listtype_" . $listingTypeId,
          'displayname' => 'Multiple Listing Types - Browse ' . $titlePluUc,
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
          'name' => 'sitereview.browse-breadcrumb-sitereview',
          'parent_content_id' => $top_middle_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.categories-sidebar-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Categories","listingtype_id":"' . $listingTypeId . '","titleCount":"true","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.search-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.newlisting-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.tagcloud-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title": "Popular Tags (%s)","listingtype_id":"' . $listingTypeId . '","nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.categories-banner-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.browse-listings-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '","ratingType":"rating_both","statistics":["viewCount","likeCount","commentCount","reviewCount"],"layouts_views":["1","2","3"]}',
      ));
    }
  }
  
  //MANAGE PAGE WORK
  public function managePageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

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

//      //RIGHT CONTAINER
//      $db->insert('engine4_core_content', array(
//          'type' => 'container',
//          'name' => 'right',
//          'page_id' => $page_id,
//          'parent_content_id' => $main_container_id,
//          'order' => $containerCount++,
//      ));
//      $right_container_id = $db->lastInsertId();

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
  }  
  
  //CREATION PAGE WORK
  public function creationPageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

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

//      //RIGHT CONTAINER
//      $db->insert('engine4_core_content', array(
//          'type' => 'container',
//          'name' => 'right',
//          'page_id' => $page_id,
//          'parent_content_id' => $main_container_id,
//          'order' => $containerCount++,
//      ));
//      $right_container_id = $db->lastInsertId();

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
  }    

  //EDIT PAGE WORK
  public function editPageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

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

//      //RIGHT CONTAINER
//      $db->insert('engine4_core_content', array(
//          'type' => 'container',
//          'name' => 'right',
//          'page_id' => $page_id,
//          'parent_content_id' => $main_container_id,
//          'order' => $containerCount++,
//      ));
//      $right_container_id = $db->lastInsertId();

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
  }    
  
  public function browseLocationPageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    //START THE WORK FOR MAKE WIDGETIZE PAGE OF Locatio or map.
    $select = new Zend_Db_Select($db);
    $select
            ->from('engine4_core_pages')
            ->where('name = ?', "sitereview_index_map_listtype_" . $listingTypeId)
            ->limit(1);
    $info = $select->query()->fetch();

    if (empty($info)) {

      $containerCount = 0;
      $widgetCount = 0;

      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_index_map_listtype_" . $listingTypeId,
          'displayname' => "Multiple Listing Types - Browse $titlePluUc' Locations",
          'title' => "Browse $titlePluUc' Locations",
          'description' => 'This is the ' . $titleSinLc . ' browse locations page.',
          'custom' => 0,
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
          'name' => 'sitereview.location-search',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '", "title":"", "titleCount":"true", "street":"1", "city":"1", "state":"1", "country":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.browselocation-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"listingtype_id":"' . $listingTypeId . '", "title":"","titleCount":"true"}',
      ));
    }
  }

  //PROFILE PAGE WORK
  public function profilePageCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);
    $listing_singular_upper = strtoupper($listingType->title_singular);
    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_index_view_listtype_" . $listingTypeId)
            ->query()
            ->fetchColumn();

    if (empty($page_id)) {

      $containerCount = 0;
      $widgetCount = 0;

      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_index_view_listtype_" . $listingTypeId,
          'displayname' => 'Multiple Listing Types - ' . $titleSinUc . ' Profile',
          'title' => $titleSinUc . ' Profile',
          'description' => 'This is ' . $titleSinUc . ' profile page.',
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
          'name' => 'sitereview.listtypes-categories',
          'parent_content_id' => $top_middle_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.list-profile-breadcrumb',
          'parent_content_id' => $top_middle_id,
          'order' => $widgetCount++,
          'params' => '',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.overall-ratings',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"","show_rating":"both"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.quick-specification-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Quick Specifications","titleCount":"true","nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.write-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.price-info-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"DASHBOARD_' . $listing_singular_upper . '_WHERE_TO_BUY","titleCount":"true","nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.about-editor-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"About Editor","titleCount":"true","nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.share',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Share and Report","titleCount":"true","options":["siteShare","friend","report","print","socialShare"],"nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.information-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Information","showContent":["ownerPhoto","ownerName","modifiedDate","viewCount","likeCount","commentCount","tags","location","compare","price"],"nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.similar-items-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Best Alternatives","statistics":["likeCount","reviewCount","commentCount"],"nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.related-listings-view-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Related Listings","related":"tags","titleCount":"true","statistics":["likeCount","reviewCount","commentCount"],"nomobile":"1"}',
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.userlisting-sitereview',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"statistics":["likeCount","reviewCount","commentCount"],"title":"%s\'s Listings","nomobile":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'seaocore.people-like',
          'parent_content_id' => $right_container_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}'
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
          'name' => 'sitereview.list-information-profile',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"showContent":["postedDate","postedBy","viewCount","likeCount","commentCount","photo","photosCarousel","tags","location","description","title","compare","wishlist","reviewCreate"]}'
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
          'name' => 'sitereview.editor-reviews-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"titleEditor":"Review", "titleOverview":"Overview", "titleDescription":"Description", "titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.user-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"User Reviews","titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.specification-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Specs","titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.overview-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Overview","titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.location-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Map","titleCount":"true"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.photos-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Photos","titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.video-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Videos","titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.discussion-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Discussions","titleCount":"true","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.price-info-sitereview',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Where to Buy","titleCount":true,"layout_column":"0","limit":"20","loaded_by_ajax":"1"}'
      ));

      $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'core.profile-links',
          'parent_content_id' => $tab_id,
          'order' => $widgetCount++,
          'params' => '{"title":"Links","titleCount":"true"}'
      ));

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('advancedactivity')) {
        $db->insert('engine4_core_content', array(
            'page_id' => $page_id,
            'type' => 'widget',
            'name' => 'advancedactivity.home-feeds',
            'parent_content_id' => $tab_id,
            'order' => $widgetCount++,
            'params' => '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0"}'
        ));
      } else {
        $db->insert('engine4_core_content', array(
            'page_id' => $page_id,
            'type' => 'widget',
            'name' => 'activity.feed',
            'parent_content_id' => $tab_id,
            'order' => $widgetCount++,
            'params' => '{"title":"Updates"}'
        ));
      }
    }
  }

  //MAIN NAVIGATION WORK
  public function mainNavigationCreate($listingType, $main_menu = 1, $template_type = '') {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);
    $titleSinUpper = strtoupper($listingType->title_singular);
    //GET CORE MENUITEMS TABLE
    $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
    $menuItemsTableName = $menuItemsTable->info('name');
    
    $redirection = isset($listingType->redirection) ? $listingType->redirection : 'home';

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "core_main_sitereview_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();
    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "core_main_sitereview_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "$titlePluUc",
          'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"'.$redirection.'","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "core_main",
          'submenu' => '',
          'enabled' => $main_menu,
          'order' => 999 + $listingTypeId,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "mobi_browse_sitereview_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();
    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "mobi_browse_sitereview_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "$titlePluUc",
          'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"'.$redirection.'","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "mobi_browse",
          'submenu' => '',
          'enabled' => $main_menu,
          'order' => 999 + $listingTypeId,
      ));
    }

    $db->query("INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('sitereview_main_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Main Navigation Menu')      
");

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_home_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_home_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "$titlePluUc Home",
          'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"home","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 1,
      ));
    }

    $browsePageNavigation = 1;
    if ($template_type == 'property') {
      $browsePageNavigation = 0;
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_browse_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_browse_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Browse $titlePluUc",
          'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"index","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 2,
          'enabled' => $browsePageNavigation,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_rated_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_rated_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Top Rated $titlePluUc",
          'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"top-rated","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 3,
          'enabled' => $browsePageNavigation,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_browse_location_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_browse_location_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Locations",
          'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"map","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 3,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_manage_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_manage_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "My $titlePluUc",
          //'label' => "My Listings",          
          'plugin' => 'Sitereview_Plugin_Menus::canCreateSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"manage","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 4,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_create_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_create_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Post a New $titleSinUc",
          'plugin' => 'Sitereview_Plugin_Menus::canCreateSitereviews',
          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"create","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 5,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_claim_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_claim_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Claim a $titleSinUc",
          'plugin' => 'Sitereview_Plugin_Menus::canViewClaims',
          'params' => '{"route":"sitereview_claim_listtype_' . $listingTypeId . '","listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 6,
      ));
    }
  }

  public function createPackageNavigation($listingTypeId) {

    $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
    $menuItemsTableName = $menuItemsTable->info('name');

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_claim_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_package_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_main_package_listtype_$listingTypeId",
          'module' => 'sitereviewpaidlisting',
          'label' => "Packages",
          'plugin' => 'Sitereviewpaidlisting_Plugin_Menus::canViewPackages',
          'params' => '{"route":"sitereview_all_package_listtype_' . $listingTypeId . '", "action":"index", "listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_main_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 6,
      ));
    }
  }

  public function getListingTypeInfo($listing_id = 0, $getListingOrder = 0) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $tempGetListingType = false;
    $getFieldsType = Engine_Api::_()->sitereview()->getFieldsType('sitereviewlistingtype');
    $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
    $sitereviewListingTypeOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.listingtype.order', false);
    $sitereviewProfileOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.profile.order', false);
    $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
    $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
    $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);

    if (!empty($getListingOrder) && $getListingOrder == 1) {
      $getListingOrder = 0;
      return false;
    }
    if (!empty($getListingOrder) && $getListingOrder == 2) {
      $getListingOrder = 1;
      return true;
    }
    if (!empty($getListingOrder) && $getListingOrder == 3) {
      $getListingOrder = 2;
      return true;
    }
    if (!empty($getListingOrder) && $getListingOrder == 4) {
      $getListingOrder = 3;
      return false;
    }
    if (!empty($getListingOrder) && $getListingOrder == 5) {
      $getListingOrder = 4;
      return true;
    }

    $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);

    $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
    for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
      $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
    }

    if (!empty($listing_id)) {
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      if (empty($sitereview)) {
        return;
      } else {
        $setListingType = $sitereview;
      }
    }

    for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
      $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
    }
    $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;

    if (!empty($getListingOrder) && !empty($listing_id)) {
      $auth = Engine_Api::_()->authorization()->context;
      $viewEveryone = $auth->getAllowed($listing_id, 'everyone', 'view', true);
      $commentEveryone = $auth->getAllowed($listing_id, 'everyone', 'comment', true);
      $getViewEveryone = $auth->getAllowed($listing_id, 'everyone', 'view', true);
      $getCommentEveryone = $auth->getAllowed($listing_id, 'everyone', 'comment', true);
    }

    if (!empty($sitereviewViewType) || (!empty($sitereviewProfileOrder) && !empty($sitereviewListingTypeOrder) && ($sitereviewListingTypeOrder == $sitereviewProfileOrder))) {
      return true;
    } else {
      $getHostTypeArray = array();
      $requestListType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.request.listtype', false);
      if (!empty($requestListType)) {
        $getHostTypeArray = @unserialize($requestListType);
      }
      $getHostTypeArray[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
      $getHostTypeArray = @serialize($getHostTypeArray);
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.request.listtype', $getHostTypeArray);

      $getReviewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.get.ltype', false);
      if (empty($getReviewType)) {
        $TempLtype[] = '3';
        $TempLtype[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
        $TempLtype[] = date("Y-m-d H:i:s");
        $TempLtype[] = $_SERVER['REQUEST_URI'];
        $TempLtype = @serialize($TempLtype);
        Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.get.ltype', $TempLtype);
      }

      $getFieldsType['sitereview.cat.listing'] = 0;
      foreach ($getFieldsType as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      return false;
    }
  }

  //GUTTER NAVIGATION MENU WORK
  public function gutterNavigationCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titleSinUpper = strtoupper($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    //GET CORE MENUITEMS TABLE
    $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
    $menuItemsTableName = $menuItemsTable->info('name');

    $db->query("INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('sitereview_gutter_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Profile Page Options Menu')      
");
    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_wishlist_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_wishlist_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Add to Wishlist",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterWishlist',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 1,
          'enabled' => 0,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_messageowner_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_messageowner_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Message Owner",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterMessageowner',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 2,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_print_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_print_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Print",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterPrint',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 3,
      ));
    }


    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_share_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_share_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Share",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterShare',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 4,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_tfriend_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_tfriend_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Tell a Friend",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterTfriend',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 5,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_report_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_report_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Report",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterReport',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 6,
      ));
    }
    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_edit_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_edit_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Edit Details",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEdit',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 7,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_editoverview_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_editoverview_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => 'DASHBOARD_' . $titleSinUpper . '_EDIT_OVERVIEW',
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditoverview',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'enabled' => 0,
          'order' => 8,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_editstyle_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_editstyle_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Edit Style",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditstyle',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'enabled' => 0,
          'order' => 9,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_close_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_close_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Open / Close",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterClose',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 10,
      ));
    }
    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_publish_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_publish_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Publish",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterPublish',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 11,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_delete_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_delete_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Delete",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterDelete',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 12,
      ));
    }
    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_editorpick_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_editorpick_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => 'Add Best Alternatives',
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterEditorPick',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 13,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_review_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_review_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Write / Edit a Editor Review",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterReview',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 14,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_subscription_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_subscription_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Subscribe / Unsubscribe",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterSubscription',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 15,
      ));
    }

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_gutter_claim_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereview_gutter_claim_listtype_$listingTypeId",
          'module' => 'sitereview',
          'label' => "Claim this $titleSinUc",
          'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterClaim',
          'params' => '{"listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_gutter_listtype_$listingTypeId",
          'submenu' => '',
          'order' => 16,
      ));
    }
  }

  public function createClaimPage($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listing_type_id = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titleSinUpper = strtoupper($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $db->query("
        INSERT IGNORE INTO `engine4_authorization_permissions` 
        SELECT 
          level_id as `level_id`, 
          'sitereview_listing' as `type`, 
          'claim_listtype_$listing_type_id' as `name`, 
          1 as `value`, 
          NULL as `params` 
        FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin', 'user', 'public');
      ");


    $db->query('INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
("SITEREVIEW_' . $titleSinUpper . '_CLAIM_APPROVED_EMAIL", "sitereview", "[host],[email],[list_title],[object_link],[list_title_with_link],[comments],[my_claim_listings_link]"),
("SITEREVIEW_' . $titleSinUpper . '_BECOMEOWNER_EMAIL", "sitereview", "[host],[email],[list_title],[object_link],[list_title_with_link],[comments],[my_claim_listings_link]"),
("SITEREVIEW_' . $titleSinUpper . '_CLAIMOWNER_EMAIL", "sitereview", "[host],[email],[list_title],[object_link],[list_title_with_link],[comments],[my_claim_listings_link]"),
("SITEREVIEW_' . $titleSinUpper . '_CLAIM_HOLDING_EMAIL", "sitereview", "[host],[email],[list_title],[object_link],[list_title_with_link],[comments]"),
  ("SITEREVIEW_' . $titleSinUpper . '_CHANGEOWNER_EMAIL", "sitereview", "[host],[email],[list_title],[object_link],[list_title_with_link],[comments]"),
("SITEREVIEW_' . $titleSinUpper . '_CLAIM_DECLINED_EMAIL", "sitereview", "[host],[email],[list_title],[object_link],[list_title_with_link],[comments]");');


    //CLAIM CREATE PAGE CREATION
    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_claim_index_listtype_$listing_type_id")
            ->limit(1)
            ->query()
            ->fetchColumn();
    if (empty($page_id)) {

      $containerCount = 0;

      //CREATE PAGE
      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_claim_index_listtype_$listing_type_id",
          'displayname' => 'Multiple Listing Types - ' . "'Claim a $titleSinUc'" . ' Page',
          'title' => 'Claim a ' . $titleSinUc,
          'description' => 'This page allow members to create a claim for ' . $titlePluLc . ' listing type.',
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
          'params' => '',
      ));

      $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'sitereview.create-claim',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));
    }

    //EVENT CREATE PAGE CREATION
    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_claim_my-listings_listtype_$listing_type_id")
            ->limit(1)
            ->query()
            ->fetchColumn();

    if (empty($page_id)) {

      $containerCount = 0;

      //CREATE PAGE
      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_claim_my-listings_listtype_$listing_type_id",
          'displayname' => 'Multiple Listing Types - My Claimed ' . $titlePluUc,
          'title' => 'Claimed ' . $titlePluUc,
          'description' => 'This page display a list of all the ' . $titleSinLc . ' listings claimed by member.',
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
          'params' => '',
      ));

      $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'sitereview.claimed-listings',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));
    }
  }

  public function getListingReviewType($listing_id = 0) {
    $getListingTypeInfo = $this->getListingTypeInfo();
    return $getListingTypeInfo;
    $isListingTypeModEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype');
    if (!empty($isListingTypeModEnabled)) {
      $flagValue = '';
      $tempStr = $getListingOrder = null;
      $tempFlagArray = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewltype.cat.attempt', false);
      $tempFlagArray = !empty($tempFlagArray) ? @unserialize($tempFlagArray) : array();
      $getLtypeAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.ltype.attempt', false);
      $sitereviewCatListing = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.cat.listing', false);
      $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
      $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
      $getFieldsType = Engine_Api::_()->sitereview()->getFieldsType('sitereviewlistingtype');
      $sitereviewlistingtypeLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewlistingtype.lsettings', false);
      $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
      $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
      $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);

      if (!empty($getListingOrder) && $getListingOrder == 1) {
        return false;
      }
      if (!empty($getListingOrder) && $getListingOrder == 2) {
        return true;
      }
      if (!empty($getListingOrder) && $getListingOrder == 3) {
        return true;
      }
      if (!empty($getListingOrder) && $getListingOrder == 4) {
        return false;
      }
      if (!empty($getListingOrder) && $getListingOrder == 5) {
        return true;
      }

      $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
      for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
        $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
      }

      for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
        $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
      }

      if (!empty($listing_id)) {
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (empty($sitereview)) {
          $this->view->setListingType = false;
        } else {
          $this->view->setListingType = $sitereview;
        }
      }

      $tempGetFinalNumber = $sitereviewSponsoredOrder . $sitereviewFeaturedOrder;
      $tempGetFinalNumber = (int) $tempGetFinalNumber;
      $tempGetFinalNumber += $getLtypeAttempt;
      $tempGetFinalNumber = (string) $tempGetFinalNumber;

      for ($tempFlag = 0; $tempFlag < 6; $tempFlag++) {
        $tempStr .= $tempGetFinalNumber[$tempFlag];
      }

      foreach ($tempFlagArray as $key) {
        $flagValue .= $sitereviewlistingtypeLsettings[$key];
      }

      if (!empty($sitereviewCatListing) || !empty($sitereviewViewType) || (!empty($tempStr) && !empty($flagValue) && ($tempStr == $flagValue))) {
        return true;
      } else {
        $getHostTypeArray = array();
        $requestListType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.request.listtype', false);
        if (!empty($requestListType)) {
          $getHostTypeArray = @unserialize($requestListType);
        }
        $getHostTypeArray[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
        $getHostTypeArray = @serialize($getHostTypeArray);
        Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.request.listtype', $getHostTypeArray);

        $getReviewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.get.ltype', false);
        if (empty($getReviewType)) {
          $TempLtype[] = '4';
          $TempLtype[] = str_replace("www.", "", strtolower($_SERVER['HTTP_HOST']));
          $TempLtype[] = date("Y-m-d H:i:s");
          $TempLtype[] = $_SERVER['REQUEST_URI'];
          $TempLtype = @serialize($TempLtype);
          Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.get.ltype', $TempLtype);
        }

        foreach ($getFieldsType as $key => $value) {
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        return false;
      }
    }
    return true;
  }

  //SEARCH FORM SETTING WORK
  public function searchFormSettingCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $db->query("INSERT IGNORE INTO `engine4_seaocore_searchformsetting`(`module`, `name`, `display`, `order`, `label`) VALUES 
    ('sitereview_listtype_$listingTypeId', 'closed', '1', '10', 'Status'),
    ('sitereview_listtype_$listingTypeId', 'show', '1', '20', 'Show (For Browse $titlePluUc and $titlePluUc Home pages)'),
    ('sitereview_listtype_$listingTypeId', 'orderby', '1', '30', 'Browse By'),
    ('sitereview_listtype_$listingTypeId', 'search', '1', '40', 'Name / Keyword'),
    ('sitereview_listtype_$listingTypeId', 'location', '1', '50', 'Location'),
    ('sitereview_listtype_$listingTypeId', 'proximity', '1', '60', 'Proximity Search'),
    ('sitereview_listtype_$listingTypeId', 'price', '1', '70', 'Price'),
    ('sitereview_listtype_$listingTypeId', 'has_review', '1', '80', 'Having Reviews'),
    ('sitereview_listtype_$listingTypeId', 'category_id', '1', '90', 'Category'),
    ('sitereview_listtype_$listingTypeId', 'has_photo', '1', '10000070', 'Only $titlePluUc With Photos');
   
");
  }

  //SEARCH FORM SETTING WORK
  public function activityFeedQueryCreate($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $subject = '$subject';
    $body = '$body';
    $count = '$count';
    $object = '$object';
    $title = '$title';
    $listing = '$listing';

    //INSERT QUERIES RELATED TO ACTIVITY FEED
    $db->query("INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES 
            
('sitereview_new_listtype_$listingTypeId', 'sitereview', '{item:$subject} posted a new $titleSinLc listing:', 1, 5, 1, 3, 1, 1), 
    
('sitereview_new_module_listtype_$listingTypeId', 'sitereview', '{item:$subject} posted a new $titleSinLc listing in {item:$object}:', 1, 7, 1, 3, 1, 1), 
    
('sitereview_photo_upload_listtype_$listingTypeId', 'sitereview', '{item:$subject} added {var:$count} photo(s) to the $titleSinLc listing: {item:$object:$title} {body:$body}', 1, 7, 2, 1, 1, 1), 
       
('sitereview_topic_create_listtype_$listingTypeId', 'sitereview', '{item:$subject} posted a new discussion {item:$object:topic} in the $titleSinLc listing {itemParent:$object:sitereview_listing}: {body:$body}', 1, 7, 2, 1, 1, 1), 
            
('sitereview_topic_reply_listtype_$listingTypeId', 'sitereview', '{item:$subject} replied to discussion {item:$object:topic} in the $titleSinLc listing {itemParent:$object:sitereview_listing}: {body:$body}', 1, 3, 2, 1, 1, 1),           
            
('sitereview_change_photo_listtype_$listingTypeId', 'sitereview', '{item:$subject} changed the profile picture of the $titleSinLc listing {item:$object:$title}:', 1, 3, 2, 1, 1, 1),            
         
('sitereview_wishlist_add_listing_listtype_$listingTypeId', 'sitereview', '{item:$subject} added {item:$listing} to wishlist {item:$object}:', 1, 7, 1, 1, 1, 1),     
            
('video_sitereview_listtype_$listingTypeId', 'sitereview', '{item:$subject} added a new video in the $titleSinLc listing {item:$object:$title}: {body:$body}', 1, 7, 2, 1, 1, 1), 
            
('sitereview_video_new_listtype_$listingTypeId', 'sitereview', '{item:$subject} added a new video in the $titleSinLc listing {item:$object}:', 1, 3, 1, 1, 1, 1),      

('sitereview_review_add_listtype_$listingTypeId', 'sitereview', '{item:$subject} rated and wrote a review for the $titleSinLc listing {item:$object}:', 1, 7, 1, 1, 1, 1);            
    ");


    $db = Engine_Db_Table::getDefaultAdapter();
    $columns = $db->query(" SHOW COLUMNS FROM engine4_activity_actiontypes like 'is_object_thumb' ")->fetchAll();

    if ( !empty( $columns )) {  
      $db->query("INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`, `is_object_thumb`) VALUES 

      ('sitereview_admin_new_module_listtype_$listingTypeId', 'sitereview', '{item:$object} posted a new $titleSinLc listing in {item:$object}:', 1, 7, 1, 3, 1, 1, 1)" );
    } else {
      $db->query("INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated` ) VALUES 

      ('sitereview_admin_new_module_listtype_$listingTypeId', 'sitereview', '{item:$object} posted a new $titleSinLc listing in {item:$object}:', 1, 7, 1, 3, 1, 1)" );

    }
    
  }

  public function setActivityFeedsLanguage($listingType) {

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $title_singular = strtolower($listingType->title_singular);
    $title_plural = strtolower($listingType->title_plural);
    $listingtype_id = $listingType->listingtype_id;
    $listType = '_LISTTYPE_' . $listingtype_id;
    $listtype = '_listtype_' . $listingtype_id;
    $subject = '$subject';
    $body = '$body';
    $count = '$count';
    $object = '$object';
    $title = '$title';

    //FOR DEFAULT LISTING TYPE WE HAVE ALREADY PUT THESE TEXT TO LANGUAGE FILE
    if ($listingtype_id == 1) {
      return;
    }

    //APPEND THE LANGUAGE FILE BY ADDING NEW ACITIVTY FEED VARIABLES
    $activity_feed_string = '';
    $activity_feed_string .= PHP_EOL . '"{item:$subject} posted a new ' . $title_singular . ' listing:";"{item:$subject} posted a new ' . $title_singular . ' listing:"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} added {var:$count} photo(s) in the ' . $title_singular . ' listing {item:$object:$title}: {body:$body}";"{item:$subject} added {var:$count} photo(s) in the ' . $title_singular . ' listing {item:$object:$title}: {body:$body}"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} posted a new discussion {item:$object:topic} in the ' . $title_singular . ' listing {itemParent:$object:sitereview}: {body:$body}";"{item:$subject} posted a new discussion {item:$object:topic} in the ' . $title_singular . ' listing {itemParent:$object:sitereview}: {body:$body}"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} replied to a {item:$object:topic} in the ' . $title_singular . ' listing {itemParent:$object:sitereview}: {body:$body}";"{item:$subject} replied to a {item:$object:topic} in the ' . $title_singular . ' listing {itemParent:$object:sitereview}: {body:$body}"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} changed the profile picture of the ' . $title_singular . ' listing {item:$object:$title}:";"{item:$subject} changed the profile picture of the ' . $title_singular . ' listing {item:$object:$title}:"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} added a new video in the ' . $title_singular . ' listing: {item:$object:$title} {body:$body}";"{item:$subject} added a new video in the ' . $title_singular . ' listing: {item:$object:$title} {body:$body}"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} added a new video in the ' . $title_singular . ' listing: {item:$object}";"{item:$subject} added a new video in the ' . $title_singular . ' listing: {item:$object}"';

    $activity_feed_string .= PHP_EOL . '"{item:$subject} rated and wrote a review for the ' . $title_singular . ' listing {item:$object}:";"{item:$subject} rated and wrote a review for the ' . $title_singular . ' listing {item:$object}:"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_NEW' . $listType . '";"Multiple Listing Types - When a new ' . $title_singular . ' is posted."';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_NEW' . $listType . '";"New ' . $title_singular . '"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_PHOTO_UPLOAD' . $listType . '";"Multiple Listing Types - When someone uploads a photo to a ' . $title_singular . '"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_PHOTO_UPLOAD' . $listType . '";"Uploading a photo to a ' . $title_singular . '"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_VIDEO_SITEREVIEW' . $listType . '";"Multiple Listing Types - When someone uploads a video to a ' . $title_singular . '"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_VIDEO_SITEREVIEW' . $listType . '";"Uploading a video to a ' . $title_singular . '"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_VIDEO_NEW' . $listType . '";"Multiple Listing Types - When someone uploads a video to a ' . $title_singular . '"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_VIDEO_NEW_' . $listType . '";"Uploading a video to a ' . $title_singular . '"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_REVIEW_ADD' . $listType . '";"Multiple Listing Types - When someone write a review on ' . $title_singular . '"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_REVIEW_ADD' . $listType . '";"Writing a new ' . $title_singular . ' review"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_TOPIC_CREATE' . $listType . '";"Multiple Listing Types - When someone creates a new ' . $title_singular . ' topic"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_TOPIC_CREATE' . $listType . '";"Creating a ' . $title_singular . ' discussion topic"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_TOPIC_REPLY' . $listType . '";"Multiple Listing Types - When someone replies to a ' . $title_singular . ' topic"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_TOPIC_REPLY' . $listType . '";"Replying to a ' . $title_singular . ' discussion topic"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_CHANGE_PHOTO' . $listType . '";"Multiple Listing Types - When ' . $title_singular . ' Owner change their ' . $title_singular . ' profile photo as User"';
    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_CHANGE_PHOTO' . $listType . '";"When ' . $title_singular . ' Owner change their ' . $title_singular . ' profile photo as User"';

    $activity_feed_string .= PHP_EOL . '"ADMIN_ACTIVITY_TYPE_SITEREVIEW_WISHLIST_ADD_LISTING' . $listType . '";"Multiple Listing Types - When someone adds a ' . $title_singular . ' in wishlist."';

    $activity_feed_string .= PHP_EOL . '"_ACTIVITY_ACTIONTYPE_SITEREVIEW_WISHLIST_ADD_LISTING' . $listType . '";"Adding ' . $title_singular . ' to wishlist."';

    $path = APPLICATION_PATH . '/application/languages/en/custom.csv';
    $folder_path = APPLICATION_PATH . '/application/languages';
    @chmod($folder_path, 0777);
    @chmod($path, 0777);
    $fp = fopen($path, 'a+');
    fwrite($fp, $activity_feed_string . PHP_EOL);
    fclose($fp);
  }

  public function mainNavigationEdit($listingType) {

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    //GET CORE MENUITEMS TABLE
    $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
    $menuItemsTableName = $menuItemsTable->info('name');

    if ($listingType->claim_show_menu == 1) {
      $menuItemsTable->update(array('menu' => 'core_footer', 'params' => '{"route":"sitereview_claim_listtype_' . $listingTypeId . '","listingtype_id":"' . $listingTypeId . '"}'), array('name =?' => "sitereview_main_claim_listtype_$listingTypeId"));
    } else if ($listingType->claim_show_menu == 2) {
      $menuItemsTable->update(array('menu' => "sitereview_main_listtype_$listingTypeId", 'params' => '{"route":"sitereview_claim_listtype_' . $listingTypeId . '","listingtype_id":"' . $listingTypeId . '"}'), array('name =?' => "sitereview_main_claim_listtype_$listingTypeId"));
    } else if (empty($listingType->claim_show_menu)) {
      $menuItemsTable->update(array('menu' => '', 'params' => ''), array('name =?' => "sitereview_main_claim_listtype_$listingTypeId"));
    }


    $menuItemsTable->update(array('label' => "$titlePluUc"), array(
        'name = ?' => "core_main_sitereview_listtype_$listingTypeId"
    ));

    $menuItemsTable->update(array('label' => "$titlePluUc"), array(
        'name = ?' => "mobi_browse_sitereview_listtype_$listingTypeId"
    ));

    $db->query("UPDATE `engine4_core_menus` SET `title` = 'Multiple Listing Types - $titlePluUc Main Navigation Menu' WHERE `name`='sitereview_main_listtype_$listingTypeId'");

    $menuItemsTable->update(array('label' => "$titlePluUc Home"), array(
        'name = ?' => "sitereview_main_home_listtype_$listingTypeId"
    ));

    $menuItemsTable->update(array('label' => "Browse $titlePluUc"), array(
        'name = ?' => "sitereview_main_browse_listtype_$listingTypeId"
    ));

    $menuItemsTable->update(array('label' => "Browse $titlePluUc"), array(
        'name = ?' => "sitereview_main_rated_listtype_$listingTypeId"
    ));

    $menuItemsTable->update(array('label' => "My $titlePluUc"), array(
        'name = ?' => "sitereview_main_manage_listtype_$listingTypeId",
    ));

    $menuItemsTable->update(array('label' => "Post a New $titleSinUc"), array(
        'name = ?' => "sitereview_main_create_listtype_$listingTypeId"
    ));

    $menuItemsTable->update(array('label' => "Claim a $titleSinUc"), array(
        'name = ?' => "sitereview_main_listtype_$listingTypeId"
    ));

    //CHECK IF SITEMOBILE PLUGIN IS ENABLED THEN UPDATE THE VALUE IN SITEMOBILE PLUGIN TABLES ALSO:
    $enable_sitemobilemodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
    if ($enable_sitemobilemodule) {
      //GET CORE MENUITEMS TABLE
      $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
      $menuItemsTableName = $menuItemsTable->info('name');

      $menuItemsTable->update(array('label' => "$titlePluUc"), array(
          'name = ?' => "core_main_sitereview_listtype_$listingTypeId"
      ));


      $db->query("UPDATE `engine4_sitemobile_menus` SET `title` = 'Multiple Listing Types - $titlePluUc Main Navigation Menu' WHERE `name`='sitereview_main_listtype_$listingTypeId'");

      $menuItemsTable->update(array('label' => "$titlePluUc Home"), array(
          'name = ?' => "sitereview_main_home_listtype_$listingTypeId"
      ));

      $menuItemsTable->update(array('label' => "Browse $titlePluUc"), array(
          'name = ?' => "sitereview_main_browse_listtype_$listingTypeId"
      ));

      $menuItemsTable->update(array('label' => "Browse $titlePluUc"), array(
          'name = ?' => "sitereview_main_rated_listtype_$listingTypeId"
      ));

      $menuItemsTable->update(array('label' => "My $titlePluUc"), array(
          'name = ?' => "sitereview_main_manage_listtype_$listingTypeId",
      ));

      $menuItemsTable->update(array('label' => "Post a New $titleSinUc"), array(
          'name = ?' => "sitereview_main_create_listtype_$listingTypeId"
      ));
    }
  }

  public function gutterNavigationEdit($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinUc = ucfirst($listingType->title_singular);

    $db->query("UPDATE `engine4_core_menus` SET `title` = '$titlePluUc Profile Page Options Menu' WHERE `name` = 
'sitereview_gutter_listtype_$listingTypeId'");

    $db->query("UPDATE `engine4_core_menuitems` SET `label` = 'Claim this $titleSinUc' WHERE `name` =
'sitereview_gutter_claim_listtype_$listingTypeId'");

    //CHECK IF SITEMOBILE PLUGIN IS ENABLED THEN UPDATE THE VALUE IN SITEMOBILE PLUGIN TABLES ALSO:
    $enable_sitemobilemodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
    if ($enable_sitemobilemodule) {
      $db->query("UPDATE `engine4_sitemobile_menus` SET `title` = '$titlePluUc Profile Page Options Menu' WHERE `name` = 
'sitereview_gutter_listtype_$listingTypeId'");
    }
  }

  public function widgetizedPagesEdit($listingType, $pageName, $previousTitleSin, $previousTitlePlu) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);

    //GET PAGE TABLE
    $pageTable = Engine_Api::_()->getDbTable('pages', 'core');
    $pageTableName = $pageTable->info('name');

    //DELETE HOME PAGE
    $page_id = $pageTable->select()
            ->from($pageTableName, 'page_id')
            ->where('name = ?', "sitereview_index_$pageName" . "_listtype_" . $listingTypeId)
            ->query()
            ->fetchColumn();

    if (!empty($page_id)) {

      $db->query("UPDATE `engine4_core_pages` SET `displayname` = REPLACE(displayname, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `displayname` Like '%$previousTitlePlu%'");

      $db->query("UPDATE `engine4_core_pages` SET `title` = REPLACE(title, '$previousTitlePlu', '$titlePluUc'), `description` = REPLACE(description, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `title` Like '%$previousTitlePlu%'");

      $db->query("UPDATE `engine4_core_pages` SET `description` = REPLACE(description, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `description` Like '%$previousTitlePlu%'");

      $db->query("UPDATE `engine4_core_pages` SET `displayname` = REPLACE(displayname, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `displayname` Like '%$previousTitleSin%'");

      $db->query("UPDATE `engine4_core_pages` SET `title` = REPLACE(title, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `title` Like '%$previousTitleSin%'");

      $db->query("UPDATE `engine4_core_pages` SET `description` = REPLACE(description, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `description` Like '%$previousTitleSin%'");

      $db->query("UPDATE `engine4_core_content` SET `params` = REPLACE(params, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `params` Like '%$previousTitlePlu%'");

      $db->query("UPDATE `engine4_core_content` SET `params` = REPLACE(params, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `params` Like '%$previousTitleSin%'");
    }

    //CHECK IF SITEMOBILE PLUGIN IS ENABLED THEN UPDATE THE VALUE IN SITEMOBILE PLUGIN TABLES ALSO:
    $enable_sitemobilemodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
    if ($enable_sitemobilemodule) {

      //GET PAGE TABLE
      $pageTable = Engine_Api::_()->getDbTable('pages', 'sitemobile');
      $pageTableName = $pageTable->info('name');

      //DELETE HOME PAGE
      $page_id = $pageTable->select()
              ->from($pageTableName, 'page_id')
              ->where('name = ?', "sitereview_index_$pageName" . "_listtype_" . $listingTypeId)
              ->query()
              ->fetchColumn();



      if (!empty($page_id)) {

        //EDIT THE PAGE AND CONTENT TABLE FOR MOBILE MODE.

        $db->query("UPDATE `engine4_sitemobile_pages` SET `displayname` = REPLACE(displayname, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `displayname` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_pages` SET `title` = REPLACE(title, '$previousTitlePlu', '$titlePluUc'), `description` = REPLACE(description, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `title` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_pages` SET `description` = REPLACE(description, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `description` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_pages` SET `displayname` = REPLACE(displayname, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `displayname` Like '%$previousTitleSin%'");

        $db->query("UPDATE `engine4_sitemobile_pages` SET `title` = REPLACE(title, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `title` Like '%$previousTitleSin%'");

        $db->query("UPDATE `engine4_sitemobile_pages` SET `description` = REPLACE(description, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `description` Like '%$previousTitleSin%'");

        $db->query("UPDATE `engine4_sitemobile_content` SET `params` = REPLACE(params, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `params` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_content` SET `params` = REPLACE(params, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `params` Like '%$previousTitleSin%'");

        //EDIT THE PAGE AND CONTENT TABLE FOR TABLET MODE.

        $db->query("UPDATE `engine4_sitemobile_tablet_pages` SET `displayname` = REPLACE(displayname, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `displayname` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_pages` SET `title` = REPLACE(title, '$previousTitlePlu', '$titlePluUc'), `description` = REPLACE(description, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `title` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_pages` SET `description` = REPLACE(description, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `description` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_pages` SET `displayname` = REPLACE(displayname, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `displayname` Like '%$previousTitleSin%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_pages` SET `title` = REPLACE(title, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `title` Like '%$previousTitleSin%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_pages` SET `description` = REPLACE(description, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `description` Like '%$previousTitleSin%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_content` SET `params` = REPLACE(params, '$previousTitlePlu', '$titlePluUc') WHERE `page_id` = $page_id AND `params` Like '%$previousTitlePlu%'");

        $db->query("UPDATE `engine4_sitemobile_tablet_content` SET `params` = REPLACE(params, '$previousTitleSin', '$titleSinUc') WHERE `page_id` = $page_id AND `params` Like '%$previousTitleSin%'");
      }
    }
  }

  //SEARCH FORM SETTING WORK
  public function activityFeedQueryEdit($listingType, $previousTitleSin, $previousTitlePlu) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titlePluUc = strtolower($listingType->title_plural);
    $titleSinUc = strtolower($listingType->title_singular);
    $previousTitleSin = strtolower($previousTitleSin);
    $previousTitlePlu = strtolower($previousTitlePlu);

    $db->query("UPDATE `engine4_activity_actiontypes` SET `body` = REPLACE(body, '$previousTitleSin', '$titleSinUc') WHERE `type` LIKE '%_listtype_$listingTypeId' AND `module` = 'sitereview' AND  `body` LIKE '%$previousTitleSin%'");
    $db->query("UPDATE `engine4_activity_actiontypes` SET `body` = REPLACE(body, '$previousTitlePlu', '$titleSinUc') WHERE `type` LIKE '%_listtype_$listingTypeId' AND `module` = 'sitereview' AND  `body` LIKE '%$previousTitlePlu%'");
  }

  public function searchFormSettingEdit($listingType, $previousTitleSin, $previousTitlePlu) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinUc = ucfirst($listingType->title_singular);

    $db->query("UPDATE `engine4_seaocore_searchformsetting` SET `label` = REPLACE(label, '$previousTitleSin', '$titleSinUc') WHERE `module` = 
'sitereview_listtype_$listingTypeId' AND `label` LIKE '%$previousTitleSin%'");

    $db->query("UPDATE `engine4_seaocore_searchformsetting` SET `label` = REPLACE(label, '$previousTitlePlu', '$titlePluUc') WHERE `module` = 
'sitereview_listtype_$listingTypeId' AND `label` LIKE '%$previousTitlePlu%'");
  }

  public function authorizationAllowEdit($previous_listingtype_id, $current_listingtype_id) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    $db->query("UPDATE `engine4_authorization_allow` SET `action` = REPLACE(action, 'listtype_$previous_listingtype_id', 'listtype_$current_listingtype_id') WHERE `action` LIKE '%_listtype_$previous_listingtype_id'");
  }

  public function activityFeedEdit($previous_listingtype_id, $current_listingtype_id) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    $db->query("UPDATE `engine4_activity_actions` SET `type` = REPLACE(type, 'listtype_$previous_listingtype_id', 'listtype_$current_listingtype_id') WHERE `type` LIKE '%_listtype_$previous_listingtype_id'");
  }

  public function mainNavigationDelete($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    //GET CORE MENUITEMS TABLE
    $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
    $menuItemsTableName = $menuItemsTable->info('name');

    $menuItemsTable->delete(array(
        'name = ?' => "core_main_sitereview_listtype_$listingTypeId",
    ));

    $menuItemsTable->delete(array(
        'name = ?' => "mobi_browse_sitereview_listtype_$listingTypeId",
    ));

    $db->query("DELETE FROM `engine4_core_menus` WHERE `name` = 'sitereview_main_listtype_$listingTypeId'      
");

    $menuItemsTable->delete(array(
        'menu = ?' => "sitereview_main_listtype_$listingTypeId",
    ));
  }

  public function gutterNavigationDelete($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    //GET CORE MENUITEMS TABLE
    $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
    $menuItemsTableName = $menuItemsTable->info('name');

    $db->query("DELETE FROM `engine4_core_menus` WHERE `name` = 'sitereview_gutter_listtype_$listingTypeId'      
");

    $menuItemsTable->delete(array(
        'menu = ?' => "sitereview_gutter_listtype_$listingTypeId",
    ));
  }

  public function widgetizedPagesDelete($listingType, $pageName) {

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    //GET PAGE TABLE
    $pageTable = Engine_Api::_()->getDbTable('pages', 'core');
    $pageTableName = $pageTable->info('name');

    //DELETE HOME PAGE
    $page_id = $pageTable->select()
            ->from($pageTableName, 'page_id')
            ->where('name = ?', "sitereview_index_" . $pageName . "_listtype_" . $listingTypeId)
            ->query()
            ->fetchColumn();

    if (!empty($page_id)) {
      Engine_Api::_()->getDbTable('content', 'core')->delete(array('page_id = ?' => $page_id));
      $pageTable->delete(array('page_id = ?' => $page_id));
    }
  }

  public function activityFeedQueryDelete($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    $db->query("DELETE FROM `engine4_activity_actiontypes` WHERE `type` LIKE '%_listtype_$listingTypeId'");
  }

  public function searchFormSettingDelete($listingType) {

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    Engine_Api::_()->getDbtable('searchformsetting', 'seaocore')->delete(array(
        'module = ?' => "sitereview_listtype_$listingTypeId",
    ));
  }

  public function authorizationPermissionsDelete($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    $db->query("DELETE FROM `engine4_authorization_permissions` WHERE `name` LIKE '%_listtype_$listingTypeId'");
  }

  public function addSuperEditor($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    //GET VIEWER ID
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    if ($listingTypeId == 1) {
      $db->query("INSERT IGNORE INTO `engine4_sitereview_editors` (`user_id`, `listingtype_id`, `designation`, `details`, `about`, `badge_id`, `super_editor`) VALUES ($viewer_id,1,'Super Editor','','',0,1)");
    } else {

      //GET SUPER EDITOR ID
      $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');
      $editor_id = $editorTable->select()
              ->from($editorTable->info('name'), 'editor_id')
              ->where('super_editor = ?', 1)
              ->limit(1)
              ->query()
              ->fetchColumn();

      //CREATE ROW IF SUPER EDITOR EXIST
      if ($editor_id) {
        $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
        $db->query("INSERT IGNORE INTO `engine4_sitereview_editors` (`user_id`, `listingtype_id`, `designation`, `details`, `about`, `badge_id`, `super_editor`) VALUES ($editor->user_id,$listingTypeId,'$editor->designation',\"$editor->details\",'$editor->about',$editor->badge_id,1)");
      }
    }
  }

  public function addBannedUrls($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $seocoreBannedUrlsTable = $db->query('SHOW TABLES LIKE \'engine4_seaocore_bannedpageurls\'')->fetch();
    if (!empty($seocoreBannedUrlsTable)) {
      $bannedPageurlsTable = Engine_Api::_()->getDbtable('BannedPageurls', 'seaocore');
      $bannedPageurlsTableName = $bannedPageurlsTable->info('name');

      //$db = $bannedPageurlsTable->getAdapter();
      //$db->beginTransaction();

      try {
        $urls = array("$listingType->slug_plural", "$listingType->slug_singular");

        $data = $bannedPageurlsTable->select()->from($bannedPageurlsTableName, 'word')
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);

        foreach ($urls as $url) {
          $bannedWordsNew = preg_split('/\s*[,\n]+\s*/', $url);
          $words = array_map('strtolower', array_filter(array_values($bannedWordsNew)));

          if (in_array($words[0], $data)) {
            continue;
          }
          $bannedPageurlsTable->setWords($bannedWordsNew);
        }
        //$db->commit();
      } catch (Exception $e) {
        //$db->rollback();
        throw $e;
      }
    }
  }

  public function locationMenuUpdate($listingType) {

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $db->query("UPDATE `engine4_core_menuitems` SET `enabled` = $listingType->location WHERE `engine4_core_menuitems`.`name` = 'sitereview_main_browse_location_listtype_" . $listingType->listingtype_id . "' LIMIT 1 ;");
  }

  public function setPinBoardLayoutHomePage($listingType) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;
    $titleSinUc = ucfirst($listingType->title_singular);
    $titlePluUc = ucfirst($listingType->title_plural);
    $titleSinLc = strtolower($listingType->title_singular);
    $titlePluLc = strtolower($listingType->title_plural);

    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', "sitereview_index_home_listtype_" . $listingTypeId)
            ->limit(1)
            ->query()
            ->fetchColumn();
    $containerCount = 0;
    $widgetCount = 0;
    if (empty($page_id)) {
      //CREATE PAGE
      $db->insert('engine4_core_pages', array(
          'name' => "sitereview_index_home_listtype_" . $listingTypeId,
          'displayname' => 'Multiple Listing Types - ' . $titlePluUc . ' Home',
          'title' => $titlePluUc . ' Home',
          'description' => 'This is the ' . $titleSinLc . ' home page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
    } else {
      $db->query("DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`page_id` = $page_id ");
    }

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
        'name' => 'seaocore.scroll-top',
        'parent_content_id' => $top_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.zerolisting-sitereview',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '',
    ));
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'sitereview.pinboard-listings-sitereview',
        'parent_content_id' => $main_middle_id,
        'order' => $widgetCount++,
        'params' => '{"title":"","statistics":["viewCount","likeCount","commentCount"],"show_buttons":["comment","like","share","facebook","pinit"],"listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","popularity":"creation_date","interval":"overall","postedby":"1","autoload":"1","itemWidth":"237","itemCount":"16","noOfTimes":"0"}',
    ));
  }

  public function updateWidgetParams($listingType, $overview = null, $wheretobuy = null, $tags = null) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //GET LISTING TYPE DETAILS
    $listingTypeId = $listingType->listingtype_id;

    //GET PAGE TABLE
    $pageTable = Engine_Api::_()->getDbTable('pages', 'core');
    $pageTableName = $pageTable->info('name');

    //GET PAGE TABLE
    $contentTable = Engine_Api::_()->getDbTable('content', 'core');
    $contentTableName = $contentTable->info('name');

    $page_id = $pageTable->select()
            ->from($pageTableName, 'page_id')
            ->where('name = ?', "sitereview_index_view_listtype_" . $listingTypeId)
            ->query()
            ->fetchColumn();

    if (!empty($page_id)) {
      $params = $contentTable->select()
              ->from($contentTableName, 'params')
              ->where('page_id =?', $page_id)
              ->where('name =?', 'sitereview.overview-sitereview')
              ->query()
              ->fetchColumn();
      $paramsArray = !empty($params) ? Zend_Json::decode($params) : array();
      if (isset($paramsArray['title'])) {
        $paramsArray['title'] = ucfirst($overview);
      }
      $params = Zend_Json::encode($paramsArray);
      $db->query("UPDATE $contentTableName SET `params` = '$params' WHERE page_id ='$page_id' and name ='sitereview.overview-sitereview'");

      $select = $contentTable->select()
              ->from($contentTableName, array('params', 'content_id'))
              ->where('page_id =?', $page_id)
              ->where('name =?', 'sitereview.price-info-sitereview');

      $datas = $contentTable->fetchAll($select);
      foreach ($datas as $data) {
        $paramsArray = $data->params;
        if (isset($paramsArray['title'])) {
          $paramsArray['title'] = ucfirst($wheretobuy);
        }
        $params = Zend_Json::encode($paramsArray);
        $db->query("UPDATE $contentTableName SET `params` = '$params' WHERE page_id ='$page_id' AND name ='sitereview.price-info-sitereview' AND content_id = $data->content_id");
      }
    }

    $page_id = $pageTable->select()
            ->from($pageTableName, 'page_id')
            ->where('name = ?', "sitereview_index_home_listtype_" . $listingTypeId)
            ->query()
            ->fetchColumn();

    if (!empty($page_id)) {

      $params = $contentTable->select()
              ->from($contentTableName, 'params')
              ->where('page_id =?', $page_id)
              ->where('name =?', 'sitereview.tagcloud-sitereview')
              ->query()
              ->fetchColumn();

      $paramsArray = !empty($params) ? Zend_Json::decode($params) : array();
      if (isset($paramsArray['title'])) {
        $paramsArray['title'] = 'Popular ' . ucfirst($tags) . ' (%s)';
      }
      $params = Zend_Json::encode($paramsArray);
      $db->query("UPDATE $contentTableName SET `params` = '$params' WHERE page_id ='$page_id' and name ='sitereview.tagcloud-sitereview'");
    }
  }
  
    public function mainMenuEdit($listingType) {

        $listingTypeId = $listingType->listingtype_id;
        $redirection = isset($listingType->redirection) ? $listingType->redirection : 'home';
        $menuNames = array("core_main_sitereview_listtype_$listingTypeId", "mobi_browse_sitereview_listtype_$listingTypeId");

        $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'core');

        foreach($menuNames as $menuName) {
            $menuItemSelect = $menuItemsTable->select()
                    ->where('name = ?', $menuName);
            $menuItem = $menuItemsTable->fetchRow($menuItemSelect);

            if (!empty($menuItem)) {
                $menuItemData = $menuItem->toArray();

                if (!empty($menuItemData)) {
                    $menuItemData['params']['action'] = $redirection;
                    $menuItem->params = $menuItemData['params'];
                    $menuItem->save();
                }
            }
        }
    }  

}
