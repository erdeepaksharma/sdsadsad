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
class Sitereview_Api_ListingTypeSM extends Core_Api_Abstract {

    public function defaultCreation($listingTypeId, $main_menu = 1, $pinboard_layout = 0, $pageTable, $contentTable) {
        
        //GET LISTINGTYPE ITEM
        $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingTypeId);
$listingTypeApi = Engine_Api::_()->getApi('listingType', 'sitereview');
        if (empty($listingType)) {
            return;
        }
        $sitereviewListingtype = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype');
        if ($sitereviewListingtype && !empty($listingType->reference)) {
            Engine_Api::_()->getApi('coreSM', 'sitereviewlistingtype')->defaultTemplate($listingType, $listingType->reference, $pageTable, $contentTable);
        } else {
            $this->homePageCreate($listingType, $pageTable, $contentTable);
            $this->browsePageCreate($listingType, $pageTable, $contentTable);
            $this->mostratedPageCreate($listingType, $pageTable, $contentTable);
            $this->profilePageCreate($listingType, $pageTable, $contentTable);
        }
           if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $this->freePackageCreate($listingType, $pageTable, $contentTable);
      $this->createPackageNavigation($listingType->listingtype_id, $pageTable, $contentTable);
    }
        $this->ManageListingPageCreate($listingType, $pageTable, $contentTable);
        $this->mainNavigationCreate($listingType, $main_menu);
        $this->gutterNavigationCreate($listingType);
        $this->dashboardMenuOptions($listingType);
        $this->creationPage($listingType, $pageTable, $contentTable);
        $this->editPage($listingType, $pageTable, $contentTable);
        $this->editContactPage($listingType, $pageTable, $contentTable);
        $this->editStylePage($listingType, $pageTable, $contentTable);
        $this->editMetaDetailsPage($listingType, $pageTable, $contentTable);
        $this->editOverviewPage($listingType, $pageTable, $contentTable);
        $this->editLocationPage($listingType, $pageTable, $contentTable);
        $this->editPriceInfoPage($listingType, $pageTable, $contentTable);
        $this->editChangePhotosPage($listingType, $pageTable, $contentTable);
        $this->editAlbumPhotosPage($listingType, $pageTable, $contentTable);
        $this->editVideosPage($listingType, $pageTable, $contentTable);
    }

    public function dashboardMenuOptions($listingType) {
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
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');
        $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
('sitereview_index_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Dashboard Page Options Menu')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
('sitereview_quick_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Quick Navigation Menu')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_listtype_$listingTypeId', 'sitereview_quick_listtype_$listingTypeId', '')      
");

        
                $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
('sitereview_package_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Quick Navigation Menu')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_package_listtype_$listingTypeId', 'sitereview_package_listtype_$listingTypeId', '')      
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

        $menuItemsId = $menuItemsTable->select()
        ->from($menuItemsTableName, array('id'))
        ->where('name = ? ', "sitereview_create_listtype_$listingTypeId")
        ->query()
        ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
        
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editinfo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_profile_editinfo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
        
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_overview_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
        
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_change-photo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
        
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editcontact_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
        
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editlocation_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
        
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editphotos_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editvideos_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_priceinfo_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editmetadetails_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_dashboard_editstyle_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
            $menuItemsTable->insert(array(
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
    }

    //HOME PAGE WORK
    public function homePageCreate($listingType, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $columnHeight = 358;
        $columnWidth = 200;
        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_home_listtype_" . $listingTypeId)
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            //CREATE PAGE
            $db->insert($pageTable, array(
                'name' => "sitereview_index_home_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - ' . $titlePluUc . ' Home',
                'title' => $titlePluUc . ' Home',
                'description' => 'This is the ' . $titleSinLc . ' home page.',
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
                'params' => '{"listingtype_id":"' . $listingTypeId . '"}'
            ));

            //PLACE CATEGORY WIDGET FOR MOBILE APP AND TABLET APP.
            if ($pageTable == 'engine4_sitemobileapp_pages' || $pageTable == 'engine4_sitemobileapp_tablet_pages') {
                $db->insert($contentTable, array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'sitereview.categories-home',
                    'parent_content_id' => $main_middle_id,
                    'order' => $widgetCount++,
                    'params' => '{"listingtype_id":"' . $listingTypeId . '"}'
                ));
            }

            //TABED-CONTAINER
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.container-tabs-columns',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"layoutContainer":"horizontal","title":"","name":"sitemobile.container-tabs-columns"}'
            ));
            $main_middle_tabed_id = $db->lastInsertId();
            if ($contentTable == 'engine4_sitemobileapp_content' || $contentTable == 'engine4_sitemobileapp_tablet_content') {
                $viewType = 'gridview';
                $layout_views = '["gridview"]';
            } else {
                $viewType = 'listview';
                $layout_views = '["listview", "gridview"]';
            }
            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.listings-sitereview',
                'parent_content_id' => $main_middle_tabed_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Featured","titleCount":"true","statistics":["likeCount","reviewCount","viewCount","commentCount"],"viewType":"' . $viewType . '","columnWidth":"200","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","fea_spo":"featured","detactLocation":"0","defaultLocationDistance":"1000","layouts_views":' . $layout_views . ',"showContent":["price","endDate","location","postedDate"],"columnHeight":"' . $columnHeight . '","popularity":"view_count","postedby":"1","itemCount":"9","truncationList":"100","truncationGrid":"100","name":"sitereview.listings-sitereview","bottomLine":"2","bottomLineGrid":"2","showExpiry":"1"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.listings-sitereview',
                'parent_content_id' => $main_middle_tabed_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Sponsored","titleCount":"true","statistics":["likeCount","reviewCount","viewCount","commentCount"],"viewType":"' . $viewType . '","columnWidth":"200","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","fea_spo":"featured","detactLocation":"0","defaultLocationDistance":"1000","layouts_views":' . $layout_views . ',"showContent":["price","endDate","location","postedDate"],"columnHeight":"' . $columnHeight . '","popularity":"view_count","postedby":"1","itemCount":"9","truncationList":"100","truncationGrid":"100","name":"sitereview.listings-sitereview","bottomLine":"2","bottomLineGrid":"2","showExpiry":"1"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.listings-sitereview',
                'parent_content_id' => $main_middle_tabed_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Top Rated","titleCount":"true","statistics":["likeCount","reviewCount","viewCount","commentCount"],"viewType":"' . $viewType . '","columnWidth":"180","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","fea_spo":"","detactLocation":"0","defaultLocationDistance":"1000","layouts_views":' . $layout_views . ',"showContent":["price","endDate","location","postedDate"],"bottomLine":"2","bottomLineGrid":"2","columnHeight":"' . $columnHeight . '","popularity":"rating_avg","postedby":"1","itemCount":"9","truncationList":"100","truncationGrid":"100","name":"sitereview.listings-sitereview","showExpiry":"1"}'
            ));


            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.listings-sitereview',
                'parent_content_id' => $main_middle_tabed_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Most Popular","titleCount":"true","statistics":["likeCount","reviewCount","viewCount","commentCount"],"viewType":"' . $viewType . '","columnWidth":"180","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","fea_spo":"","detactLocation":"0","defaultLocationDistance":"1000","layouts_views":' . $layout_views . ',"showContent":["price","endDate","location","postedDate"],"bottomLine":"2","bottomLineGrid":"2","columnHeight":"' . $columnHeight . '","popularity":"view_count","postedby":"1","itemCount":"9","truncationList":"100","truncationGrid":"100","name":"sitereview.listings-sitereview","showExpiry":"1"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.listings-sitereview',
                'parent_content_id' => $main_middle_tabed_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Most Reviewed","titleCount":"true","statistics":["likeCount","reviewCount","viewCount","commentCount"],"viewType":"' . $viewType . '","columnWidth":"200","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","fea_spo":"","detactLocation":"0","defaultLocationDistance":"1000","layouts_views":' . $layout_views . ',"showContent":["price","location","endDate","postedDate"],"columnHeight":"' . $columnHeight . '","popularity":"review_count","postedby":"1","itemCount":"9","truncationList":"100","truncationGrid":"100","name":"sitereview.listings-sitereview","bottomLine":"2","bottomLineGrid":"2","showExpiry":"1"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.listings-sitereview',
                'parent_content_id' => $main_middle_tabed_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Recent","titleCount":"true","statistics":["likeCount","reviewCount","viewCount","commentCount"],"viewType":"' . $viewType . '","columnWidth":"200","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_avg","fea_spo":"","detactLocation":"0","defaultLocationDistance":"1000","layouts_views":' . $layout_views . ',"showContent":["price","location","endDate","postedDate"],"columnHeight":"' . $columnHeight . '","popularity":"creation_date","postedby":"1","itemCount":"9","truncationList":"100","truncationGrid":"100","name":"sitereview.listings-sitereview","bottomLine":"2","bottomLineGrid":"2","showExpiry":"1"}'
            ));
        }
    }

    //BROWSE PAGE WORK
    public function browsePageCreate($listingType, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $columnHeight = 358;
        $columnWidth = 200;
        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_index_listtype_" . $listingTypeId)
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (!$page_id) {

            $containerCount = 0;
            $widgetCount = 0;

            //CREATE PAGE
            $db->insert($pageTable, array(
                'name' => "sitereview_index_index_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Browse ' . $titlePluUc,
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
            if ($contentTable == 'engine4_sitemobile_content' || $contentTable == 'engine4_sitemobile_tablet_content') {
                $db->insert($contentTable, array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'sitereview.browse-breadcrumb-sitereview',
                    'parent_content_id' => $main_middle_id,
                    'order' => $widgetCount++,
                    'params' => '{"nomobile":"1"}',
                ));
            }
            if ($contentTable == 'engine4_sitemobileapp_content' || $contentTable == 'engine4_sitemobileapp_tablet_content') {
                $layout_order = '2';
                $layout_views = '["2"]';
            } else {
                $layout_order = '1';
                $layout_views = '["1", "2"]';
            }

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.browse-listings-sitereview',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"title":"","titleCount":true,"layouts_views":' . $layout_views . ',"layouts_order":"' . $layout_order . '","statistics":["viewCount","likeCount","reviewCount","commentCount"],"columnWidth":"200","truncationGrid":"100","listingtype_id":"' . $listingTypeId . '","ratingType":"rating_both","detactLocation":"0","defaultLocationDistance":"1000","columnHeight":"' . $columnHeight . '","showExpiry":"1","bottomLine":"2","postedby":"1","orderby":"spfesp","itemCount":"9","truncation":"100","name":"sitereview.browse-listings-sitereview","bottomLineGrid":"2","showContent":["price","location","endDate","postedDate"]}',
            ));
        }
    }

    //MOST RATED PAGE WORK
    public function mostratedPageCreate($listingType, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
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

    public function manageListingPageCreate($listingType, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);

        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_manage_listtype_" . $listingTypeId)
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (!$page_id) {

            $containerCount = 0;
            $widgetCount = 0;

            //CREATE PAGE
            $db->insert($pageTable, array(
                'name' => "sitereview_index_manage_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - Manage(My) ' . $titlePluUc,
                'title' => '',
                'description' => '',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();

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

            // Insert content
            $db->insert($contentTable, array(
                'type' => 'widget',
                'name' => 'core.content',
                'page_id' => $page_id,
                'parent_content_id' => $main_middle_id,
                'order' => 3,
            ));
        }
    }

    //MAIN NAVIGATION WORK
    public function mainNavigationCreate($listingType, $main_menu = 1) {

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
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "core_main_sitereview_listtype_$listingTypeId")
                ->query()
                ->fetchColumn();
        if (empty($menuItemsId)) {
            if ($listingType->reference) {
                $coremainparams = '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"home","listingtype_id":"' . $listingTypeId . '","icon":".\/application\/modules\/Sitereview\/externals\/images\/types\/' . $listingType->reference . '.png"}';
            } else {
                $coremainparams = '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"home","listingtype_id":"' . $listingTypeId . '"}';
            }
            $menuItemsTable->insert(array(
                'name' => "core_main_sitereview_listtype_$listingTypeId",
                'module' => 'sitereview',
                'label' => "$titlePluUc",
                'plugin' => 'Sitereview_Plugin_Menus::canViewSitereviews',
                'params' => $coremainparams,
                'menu' => "core_main",
                'submenu' => '',
                'enable_mobile' => $main_menu,
                'enable_tablet' => $main_menu,
                'order' => 40 + $listingTypeId,
            ));
        }

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
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
                'enable_mobile' => 1,
                'enable_tablet' => 1,
            ));
        }

        $browsePageNavigation = 1;
        if ($listingType->reference == 'property') {
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
                'enable_mobile' => $browsePageNavigation,
                'enable_tablet' => $browsePageNavigation,
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
                'enable_mobile' => $browsePageNavigation,
                'enable_tablet' => $browsePageNavigation,
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
                'enable_mobile' => $browsePageNavigation,
                'enable_tablet' => $browsePageNavigation,
            ));
        }

//    $menuItemsId = $menuItemsTable->select()
//            ->from($menuItemsTableName, array('id'))
//            ->where('name = ? ', "sitereview_main_create_listtype_$listingTypeId")
//            ->query()
//            ->fetchColumn();
//
//    if (empty($menuItemsId)) {
//      $menuItemsTable->insert(array(
//          'name' => "sitereview_main_create_listtype_$listingTypeId",
//          'module' => 'sitereview',
//          'label' => "Post a New $titleSinUc",
//          'plugin' => 'Sitereview_Plugin_Menus::canCreateSitereviews',
//          'params' => '{"route":"sitereview_general_listtype_' . $listingTypeId . '","action":"create","listingtype_id":"' . $listingTypeId . '"}',
//          'menu' => "sitereview_main_listtype_$listingTypeId",
//          'submenu' => '',
//          'order' => 5,
//      ));
//    }
    }

    //PROFILE PAGE WORK
    public function profilePageCreate($listingType, $pageTable, $contentTable) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $columnHeight = 328;
        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
        $page_id = $db->select()
                ->from($pageTable, 'page_id')
                ->where('name = ?', "sitereview_index_view_listtype_" . $listingTypeId)
                ->query()
                ->fetchColumn();

        if (empty($page_id)) {

            $containerCount = 0;
            $widgetCount = 0;

            $db->insert($pageTable, array(
                'name' => "sitereview_index_view_listtype_" . $listingTypeId,
                'displayname' => 'Multiple Listing Types - ' . $titleSinUc . ' Profile',
                'title' => $titleSinUc . ' Profile',
                'description' => 'This is ' . $titleSinUc . ' profile page.',
                'custom' => 0
            ));
            $page_id = $db->lastInsertId($pageTable);

            //TOP CONTAINER
            $db->insert($contentTable, array(
                'type' => 'container',
                'name' => 'top',
                'page_id' => $page_id,
                'order' => $containerCount++,
            ));
            $top_container_id = $db->lastInsertId();

            //INSERT TOP-MIDDLE
            $db->insert($contentTable, array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $top_container_id,
                'order' => $containerCount++,
            ));
            $top_middle_id = $db->lastInsertId();

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
            if (!($pageTable == 'engine4_sitemobileapp_pages' || $pageTable == 'engine4_sitemobileapp_tablet_pages')) {
                $db->insert($contentTable, array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'sitereview.list-profile-breadcrumb',
                    'parent_content_id' => $top_middle_id,
                    'order' => $widgetCount++,
                    'params' => '',
                ));
            }

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.list-information-profile',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"showContent":["postedDate","postedBy","viewCount","likeCount","commentCount","photo","photosCarousel","tags","location","description","title","compare","wishlist","reviewCreate"]}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.quick-specification-sitereview',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"itemCount":5}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.container-tabs-columns',
                'parent_content_id' => $main_middle_id,
                'order' => $widgetCount++,
                'params' => '{"max":"6"}',
            ));
            $tab_id = $db->lastInsertId($contentTable);

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.editor-reviews-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"titleEditor":"Review", "titleOverview":"Overview", "titleDescription":"Description", "titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.sitemobile-user-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"User Reviews","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.specification-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Specs","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.overview-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Overview","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.location-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Map","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.photos-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Photos","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.video-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Videos","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.discussion-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Discussions","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.price-info-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"DASHBOARD_' . $listing_singular_upper . '_WHERE_TO_BUY","titleCount":true,"layout_column":"0","limit":"20","loaded_by_ajax":"1"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.similar-items-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Best Alternatives","titleCount":"true","statistics":["likeCount","reviewCount","commentCount","viewCount"],"ratingType":"rating_avg","viewType":"listview","columnHeight":"' . $columnHeight . '","itemCount":"3","truncation":"45","nomobile":"1","name":"sitereview.similar-items-sitereview"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.related-listings-view-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Related Listings","titleCount":"true","statistics":["likeCount","reviewCount","commentCount","viewCount"],"ratingType":"rating_avg","related":"categories","viewType":"listview","columnHeight":"' . $columnHeight . '","itemCount":"3","truncation":"21","nomobile":"1","name":"sitereview.related-listings-view-sitereview"}',
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.userlisting-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"%s\'s Listings","titleCount":"true","statistics":["likeCount","reviewCount","commentCount","viewCount"],"ratingType":"rating_avg","viewType":"listview","columnHeight":"' . $columnHeight . '","count":"3","truncation":"21","nomobile":"1","name":"sitereview.userlisting-sitereview"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitereview.recently-viewed-sitereview',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Recently Viewed by You","titleCount":"true","statistics":["likeCount","reviewCount","commentCount","viewCount"],"listingtype_id":"' . $listingTypeId . '","ratingType":"rating_users","fea_spo":"","show":"0","viewType":"listview","columnHeight":"' . $columnHeight . '","truncation":"21","count":"3","nomobile":"1","name":"sitereview.recently-viewed-sitereview"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'seaocore.sitemobile-people-like',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Member Likes","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.profile-links',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Links","titleCount":"true"}'
            ));

            $db->insert($contentTable, array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'sitemobile.sitemobile-advfeed',
                'parent_content_id' => $tab_id,
                'order' => $widgetCount++,
                'params' => '{"title":"Updates"}'
            ));
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
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_menus` (`name`, `type`, `title`) VALUES
('sitereview_gutter_listtype_$listingTypeId', 'standard', 'Multiple Listing Types - $titlePluUc Profile Page Options Menu')      
");

        $db->query("INSERT IGNORE INTO `engine4_sitemobile_navigation` (`name`, `menu`, `subject_type`) VALUES
('sitereview_index_view_listtype_$listingTypeId', 'sitereview_gutter_listtype_$listingTypeId', 'sitereview_listing')      
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
                'enable_mobile' => 1,
                'enable_tablet' => 1
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
                'enable_mobile' => 1,
                'enable_tablet' => 1
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
                'enable_mobile' => 1,
                'enable_tablet' => 1
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
                'enable_mobile' => 1,
                'enable_tablet' => 1
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
                'enable_mobile' => 1,
                'enable_tablet' => 1
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
                'enable_mobile' => 0,
                'enable_tablet' => 0
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
                'enable_mobile' => 1,
                'enable_tablet' => 1
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
                'enable_mobile' => 0,
                'enable_tablet' => 0
            ));
        }
    }

    public function widgetizedPagesDelete($listingType, $pageName, $pageTable, $contentTable) {

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;

        //GET PAGE TABLE
        $pageTable = Engine_Api::_()->getDbTable($pageTable, 'sitemobile');
        $pageTableName = $pageTable->info('name');

        //DELETE HOME PAGE
        $pages_id = $pageTable->select()
                ->from($pageTableName, 'page_id')
                // ->where('name = ?', "sitereview_index_" . $pageName . "_listtype_" . $listingTypeId) 
                ->where('name Like (?)', "%_listtype_" . $listingTypeId . "%")
                ->query()
                ->fetchAll();

        foreach ($pages_id as $page_id) {
            if (!empty($page_id)) {
                Engine_Api::_()->getDbTable($contentTable, 'sitemobile')->delete(array('page_id = ?' => $page_id));
                $pageTable->delete(array('page_id = ?' => $page_id));
            }
        }
    }

    public function widgetizedPagesDeleteApp($listingType, $pageName, $pageTable, $contentTable) {

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;

        //GET PAGE TABLE
        $pageTable = Engine_Api::_()->getDbTable($pageTable, 'sitemobileapp');
        $pageTableName = $pageTable->info('name');

        //DELETE HOME PAGE
        $pages_id = $pageTable->select()
                ->from($pageTableName, 'page_id')
                //->where('name = ?', "sitereview_index_" . $pageName . "_listtype_" . $listingTypeId)
                ->where('name Like (?)', "%_listtype_" . $listingTypeId . "%")
                ->query()
                ->fetchAll();

       foreach ($pages_id as $page_id) {
            if (!empty($page_id)) {
                Engine_Api::_()->getDbTable($contentTable, 'sitemobile')->delete(array('page_id = ?' => $page_id));
                $pageTable->delete(array('page_id = ?' => $page_id));
            }
        }
    }

    public function gutterNavigationDelete($listingType) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;

        //GET SITEMOBILE MENUITEMS TABLE
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');

        $db->query("DELETE FROM `engine4_sitemobile_menus` WHERE `name` = 'sitereview_gutter_listtype_$listingTypeId'      
");

        $menuItemsTable->delete(array(
            'menu = ?' => "sitereview_gutter_listtype_$listingTypeId",
        ));

        //GET SITEMOBILE MENUITEMS TABLE
        $menuItemsTable = Engine_Api::_()->getDbTable('navigation', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');
        $db->query("DELETE FROM `engine4_sitemobile_navigation` WHERE `name` = 'sitereview_index_view_listtype_$listingTypeId'      
");
    }

    public function dashboardNavigationDelete($listingType) {
        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;

        //GET SITEMOBILE MENUITEMS TABLE
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');

        $db->query("DELETE FROM `engine4_sitemobile_menus` WHERE `name` = 'sitereview_index_listtype_$listingTypeId'      
");
        $db->query("DELETE FROM `engine4_sitemobile_menus` WHERE `name` = 'sitereview_quick_listtype_$listingTypeId'      
");

        $menuItemsTable->delete(array(
            'menu = ?' => "sitereview_quick_listtype_$listingTypeId",
        ));

        $menuItemsTable->delete(array(
            'menu = ?' => "sitereview_index_listtype_$listingTypeId",
        ));

        //GET SITEMOBILE MENUITEMS TABLE
        $menuItemsTable = Engine_Api::_()->getDbTable('navigation', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');
        $db->query("DELETE FROM `engine4_sitemobile_navigation` WHERE `menu` = 'sitereview_index_listtype_$listingTypeId'      
");

        $db->query("DELETE FROM `engine4_sitemobile_navigation` WHERE `menu` = 'sitereview_quick_listtype_$listingTypeId'      
");
    }

    public function mainNavigationDelete($listingType) {

        //GET DATABASE
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;

        //GET CORE MENUITEMS TABLE
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
        $menuItemsTableName = $menuItemsTable->info('name');

        $menuItemsTable->delete(array(
            'name = ?' => "core_main_sitereview_listtype_$listingTypeId",
        ));

        $db->query("DELETE FROM `engine4_sitemobile_menus` WHERE `name` = 'sitereview_main_listtype_$listingTypeId'      
");

        $menuItemsTable->delete(array(
            'menu = ?' => "sitereview_main_listtype_$listingTypeId",
        ));

        //GET CORE MENUITEMS TABLE
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
        $menuItemsTableName = $menuItemsTable->info('name');

        $menuItemsTable->delete(array(
            'name = ?' => "core_main_sitereview_listtype_$listingTypeId",
        ));

        $db->query("DELETE FROM `engine4_core_menus` WHERE `name` = 'sitereview_main_listtype_$listingTypeId'      
");

        $menuItemsTable->delete(array(
            'menu = ?' => "sitereview_main_listtype_$listingTypeId",
        ));
    }

    //CREATION PAGE WORK
    public function creationPage($listingType, $pageTable, $contentTable) {

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
    public function editPage($listingType, $pageTable, $contentTable) {

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

    public function editContactPage($listingType, $pageTable, $contentTable) {
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

    public function editStylePage($listingType, $pageTable, $contentTable) {
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

    public function editMetaDetailsPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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

    public function editOverviewPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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

    public function editLocationPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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

    public function editPriceInfoPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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
                'title' => 'Edit ' .$titleSinUc . ' Where to Buy',
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

    public function editChangePhotosPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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

    public function editAlbumPhotosPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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

    public function editVideosPage($listingType, $pageTable, $contentTable) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        //GET LISTING TYPE DETAILS
        $listingTypeId = $listingType->listingtype_id;
        $titleSinUc = ucfirst($listingType->title_singular);
        $titlePluUc = ucfirst($listingType->title_plural);
        $titleSinLc = strtolower($listingType->title_singular);
        $titlePluLc = strtolower($listingType->title_plural);
        $listing_singular_upper = strtoupper($listingType->title_singular);
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
    
      public function freePackageCreate($listingType, $pageTable, $contentTable) {
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
              ->from($pageTable, 'page_id')
              ->where('name = ?', "sitereviewpaidlisting_package_index_listtype_$listingTypeId")
              ->limit(1)
              ->query()
              ->fetchColumn();
      if (empty($page_id)) {

        $containerCount = 0;

        //CREATE PAGE
        $db->insert($pageTable, array(
            'name' => "sitereviewpaidlisting_package_index_listtype_$listingTypeId",
            'displayname' => 'Multiple Listing Types - Packages for ' . $titlePluUc,
            'title' => 'Packages for '.$titlePluUc,
            'description' => 'This is the Packages page for '.$titleSinLc.'.',
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

        //INSERT TOP-MIDDLE
        $db->insert($contentTable, array(
            'type' => 'container',
            'name' => 'middle',
            'page_id' => $page_id,
            'parent_content_id' => $top_container_id,
            'order' => $containerCount++,
        ));
        $top_middle_id = $db->lastInsertId();

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
            'parent_content_id' => $top_middle_id,
            'params' => '',
        ));

        $db->insert($contentTable, array(
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
  
  
   public function createPackageNavigation($listingTypeId, $pageTable, $contentTable) {

     $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');
     $menuItemsTableName = $menuItemsTable->info('name');

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereview_main_claim_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    $menuItemsId = $menuItemsTable->select()
            ->from($menuItemsTableName, array('id'))
            ->where('name = ? ', "sitereviewpackage_listtype_$listingTypeId")
            ->query()
            ->fetchColumn();

    if (empty($menuItemsId)) {
      $menuItemsTable->insert(array(
          'name' => "sitereviewpackage_listtype_$listingTypeId",
          'module' => 'sitereviewpaidlisting',
          'label' => "Packages",
          'plugin' => 'Sitereviewpaidlisting_Plugin_Menus::canViewPackages',
          'params' => '{"route":"sitereview_all_package_listtype_' . $listingTypeId . '", "action":"index", "listingtype_id":"' . $listingTypeId . '"}',
          'menu' => "sitereview_package_listtype_".$listingTypeId,
          'enable_mobile' => 1,
          'enable_tablet' => 1,
          'submenu' => '',
          'order' => 6,
      ));
    }
  }
          
    public function mainMenuEdit($listingType) {

        $listingTypeId = $listingType->listingtype_id;
        $redirection = isset($listingType->redirection) ? $listingType->redirection : 'home';
        $menuNames = array("core_main_sitereview_listtype_$listingTypeId", "mobi_browse_sitereview_listtype_$listingTypeId");

        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'sitemobile');

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