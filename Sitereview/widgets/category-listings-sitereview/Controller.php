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
class Sitereview_Widget_CategoryListingsSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    if ($this->_getParam('is_ajax_load', false)) {
      $this->view->is_ajax_load = true;
      if ($this->_getParam('contentpage', 1) > 1)
        $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    } else {
      if(!$this->_getParam('detactLocation', 0)){
        $this->view->is_ajax_load = true;
      }else{
       $this->getElement()->removeDecorator('Title');
      }
    }    

    //GET PARAMETERS FOR SORTING THE RESULTS
    $params = array();
    $itemCount = $params['itemCount'] = $this->_getParam('itemCount', 0);
    $params['popularity'] = $popularity = $this->_getParam('popularity', 'view_count');
    $params['interval'] = $interval = $this->_getParam('interval', 'overall');
    $params['limit'] = $totalPages = $this->_getParam('listingCount', 5);
    $this->view->title_truncation = $params['truncation'] = $this->_getParam('truncation', 25);
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id');
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }
    
    $sitereviewCatListingReview = Zend_Registry::isRegistered('sitereviewCatListingReview') ?  Zend_Registry::get('sitereviewCatListingReview') : null;

    $params['listingtype_id'] = $listingtype_id;
    
    $this->view->detactLocation = $params['detactLocation'] = $this->_getParam('detactLocation', 0);
    if($listingtype_id && $this->view->detactLocation) {
      $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    }          
    if($this->view->detactLocation) {
      $params['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);    
      $params['latitude'] = $this->_getParam('latitude', 0);
      $params['longitude'] = $this->_getParam('longitude', 0);
    }       
    
    $this->view->params = $params;

    //GET CATEGORIES
    $categories = array();
    $category_info = Engine_Api::_()->getDbtable('categories', 'sitereview')->getCategorieshaslistings($listingtype_id, 0, 'category_id', $itemCount, $params, array('category_id', 'category_name', 'cat_order'));

    foreach ($category_info as $value) {
      $category_listings_array = array();

      $params['category_id'] = $value['category_id'];

      //GET PAGE RESULTS
      $category_listings_info = $category_listings_info = Engine_Api::_()->getDbtable('listings', 'sitereview')->listingsBySettings($params);

      foreach ($category_listings_info as $result_info) {
        $tmp_array = array('listing_id' => $result_info->listing_id,
            'imageSrc' => $result_info->getPhotoUrl('thumb.icon'),
            'listing_title' => $result_info->title,
            'owner_id' => $result_info->owner_id,
            'populirityCount' => $result_info->$popularity,
            'slug' => $result_info->getSlug());
        $category_listings_array[] = $tmp_array;
      }
      $category_array = array('category_id' => $value->category_id,
          'category_name' => $value->category_name,
          'order' => $value->cat_order,
          'category_listings' => $category_listings_array
      );
      $categories[] = $category_array;
    }
    $this->view->categories = $categories;

    //SET NO RENDER
    if (!(count($this->view->categories) > 0) || empty($sitereviewCatListingReview)) {
      return $this->setNoRender();
    }
  }

}