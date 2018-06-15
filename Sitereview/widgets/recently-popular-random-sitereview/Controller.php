<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_RecentlyPopularRandomSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
//    if ($this->_getParam('is_ajax_load', false)) {
//      $this->view->is_ajax_load = true;
//      if ($this->_getParam('contentpage', 1) > 1)
//        $this->getElement()->removeDecorator('Title');
//      $this->getElement()->removeDecorator('Container');
//    } else {
//      if(!$this->_getParam('detactLocation', 0)){
//        $this->view->is_ajax_load = true;
//      }else{
//       $this->getElement()->removeDecorator('Title');
//      }
//      
//      $this->view->is_ajax_load = !$this->_getParam('loaded_by_ajax', true);
//    }        
    
    if ($this->_getParam('is_ajax_load', false)) {
      $this->view->is_ajax_load = true;
      if (!$this->_getParam('detactLocation', 0) || $this->_getParam('contentpage', 1) > 1)
        $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    } else {
      if ($this->_getParam('detactLocation', 0))
        $this->getElement()->removeDecorator('Title');

      $this->view->is_ajax_load = !$this->_getParam('loaded_by_ajax', true);
    }    
    
    $this->view->params = $params = $this->_getAllParams();
    $params['limit'] = $this->_getParam('limit', 12);
    $this->view->postedby = $params['postedby'] = $this->_getParam('postedby', 1);
    $this->view->statistics = $params['statistics'] = $this->_getParam('statistics', array("viewCount", "likeCount", "commentCount", "reviewCount"));
    $this->view->showContent = $params['showContent'] = $this->_getParam('showContent', array("price", "location")); 
    
    //GET CORE API
    $this->view->settings = Engine_Api::_()->getApi('settings', 'core');

    $this->view->is_ajax = $isAjax = $this->_getParam('is_ajax', 0);
    if (empty($isAjax)) {
      $showTabArray = $params['ajaxTabs'] = $this->_getParam('ajaxTabs', array("recent", "most_reviewed", "most_popular", "featured", "sponsored", "expiring_soon"));
      
      if($showTabArray) {
        foreach ($showTabArray as $key => $value)
          $showTabArray[$key] = str_replace("ZZZ", "_", $value);
      }
      else {
        $showTabArray = array();
      }
      
      $this->view->tabs = $showTabArray;
      $this->view->tabCount = count($showTabArray);
      if (empty($this->view->tabCount)) {
        return $this->setNoRender();
      }
      $this->view->tabs = $showTabArray = $this->setTabsOrder($showTabArray);
    } else {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    
    $layouts_views = $params['layouts_views'] = $this->_getParam('layouts_views', array("list_view", "grid_view", "map_view"));

    foreach ($layouts_views as $key => $value)
      $layouts_views[$key] = str_replace("ZZZ", "_", $value);

    $this->view->layouts_views = $layouts_views;
    $this->view->defaultLayout = str_replace("ZZZ", "_", $this->_getParam('defaultOrder', 'list_view'));
    //$this->_getParam('defaultOrder', 'list_view');

    $sitereviewTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    $listingtype_id = $this->view->listingtype_id = $params['listingtype_id'] = $this->_getParam('listingtype_id');
    if (empty($listingtype_id)) {
      $listingtype_id = $this->view->listingtype_id = $params['listingtype_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    }
    
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($params['listingtype_id']);

    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->category_id = 0;
    $this->view->subcategory_id = 0;
    $this->view->subsubcategory_id = 0;

    if (!empty($listingtype_id)) {
      $this->view->category_id = $params['category_id'] = $this->_getParam('hidden_category_id');
      $this->view->subcategory_id = $params['subcategory_id'] = $this->_getParam('hidden_subcategory_id');
      $this->view->subsubcategory_id = $params['subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id');
    }

    $this->view->ratingType = $params['ratingType'] = $this->_getParam('ratingType', 'rating_avg');
    $paramsContentType = $this->_getParam('content_type', null);
    $this->view->content_type = $paramsContentType = $paramsContentType ? $paramsContentType : $showTabArray[0];
    
    $this->view->detactLocation = $params['detactLocation'] = $this->_getParam('detactLocation', 0);
    if($listingtype_id && $this->view->detactLocation) {
      $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    }          
    if($this->view->detactLocation) {
      $params['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);    
      $params['latitude'] = $this->_getParam('latitude', 0);
      $params['longitude'] = $this->_getParam('longitude', 0);
    }

    if (empty($isAjax) && empty($this->view->detactLocation)) {
      //GET LISTS
      $listingCount = $sitereviewTable->hasListings($listingtype_id);

      if (empty($listingCount)) {
        return $this->setNoRender();
      }
    }        
    
    $this->view->enableLocation = $checkLocation = $listingtype_id > 0 ? Engine_Api::_()->sitereview()->enableLocation($listingtype_id) : 1;    
    
		//DO NOT RENDER IF ONLY MAP VIEW IS ENABLED AND LOCATION IS DISABLED
		if (in_array('map_view', $layouts_views) && Count($layouts_views) == 1 && !$this->view->enableLocation) {
			return $this->setNoRender();
		}	    
    
    $this->view->columnWidth = $values['columnWidth'] = $this->_getParam('columnWidth', '180');
    $this->view->columnHeight = $values['columnHeight'] = $this->_getParam('columnHeight', '328');
    $this->view->title_truncationList = $values['truncationList'] = $this->_getParam('truncationList', 600);
    $this->view->title_truncationGrid = $values['truncationGrid'] = $this->_getParam('truncationGrid', 90);
    $this->view->listViewType = $values['listViewType'] = $this->_getParam('listViewType', 'list');
    
    $this->view->paramsLocation = array_merge($params, $values);    
    
    $this->view->is_ajax_load = Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode') ? $this->view->is_ajax_load : true;
    if (!$this->view->is_ajax_load)
      return;    

    $this->view->paginator = $paginator = $sitereviewRecently = $sitereviewTable->getListing($paramsContentType, $params);
    $this->view->totalCount = $paginator->getTotalItemCount();

    $this->view->locations = array();
    if (in_array('map_view', $layouts_views)) {
      if ($checkLocation) {
        $listing_ids = array();
        $locationListing = array();
        $this->view->flagSponsored = $this->view->settings->getSetting('sitereview.map.sponsored', 1);
        foreach ($paginator as $item) {
          if ($item->location) {
            $listing_ids[] = $item->listing_id;
            $locationListing[$item->listing_id] = $item;
          }
        }

        if (count($listing_ids) > 0) {
          $values['listing_ids'] = $listing_ids;
          $values['listingtype_id'] = $listingtype_id;

          $this->view->locations = $locations = Engine_Api::_()->getDbtable('locations', 'sitereview')->getLocation($values);
          $this->view->locationsListing = $locationListing;
        }
      } else {
        unset($layouts_views[array_search('map_view', $layouts_views)]);
        $this->view->layouts_views = $layouts_views;
      }
    }  
  }

  public function setTabsOrder($tabs) {

    $tabsOrder['recent'] = $this->_getParam('recent_order', 1);
    $tabsOrder['most_reviewed'] = $this->_getParam('reviews_order', 2);
    $tabsOrder['most_popular'] = $this->_getParam('popular_order', 3);
    $tabsOrder['featured'] = $this->_getParam('featured_order', 4);
    $tabsOrder['sponsored'] = $this->_getParam('sponosred_order', 5);
    $tabsOrder['expiring_soon'] = $this->_getParam('expiring_order', 6);

    $tempTabs = array();
    foreach ($tabs as $tab) {
      $order = $tabsOrder[$tab];
      if (isset($tempTabs[$order]))
        $order++;
      $tempTabs[$order] = $tab;
    }
    ksort($tempTabs);
    $orderTabs = array();
    $i = 0;
    foreach ($tempTabs as $tab)
      $orderTabs[$i++] = $tab;

    return $orderTabs;
  }

}
