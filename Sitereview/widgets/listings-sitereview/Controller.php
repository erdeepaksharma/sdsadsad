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
class Sitereview_Widget_ListingsSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
      
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->isajax = $this->_getParam('isajax', false);
    if ($this->view->isajax) {
        $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    $this->view->viewmore = $this->_getParam('viewmore', false);
//    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
//      //IN MOBILE SITE WE DONT NEED THIS
//       $this->view->is_ajax_load = true;
//    }
    if ($this->_getParam('is_ajax_load', false)) {
      $this->view->is_ajax_load = true;
      if ($this->_getParam('contentpage', 1) > 1 || $this->_getParam('page', 1) > 1)
        $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    } else {

      if(!$this->_getParam('detactLocation', 0)){
        $this->view->is_ajax_load = true;
      }else{
       $this->getElement()->removeDecorator('Title');
      }
    }       

    $params = array();
    $params['popularity'] = $this->view->popularity = $this->_getParam('popularity', 'creation_date');
    $params['limit'] = $params['itemCount'] = $this->_getParam('itemCount', 10);
    $fea_spo = $params['fea_spo'] = $this->_getParam('fea_spo', '');
    if ($fea_spo == 'featured') {
      $params['featured'] = 1;
    } elseif ($fea_spo == 'sponsored') {
      $params['sponsored'] = 1;
    } elseif($fea_spo == 'newlabel') {
      $params['newlabel'] = 1;
    } elseif ($fea_spo == 'fea_spo') {
      $params['sponsored_or_featured'] = 1;
    } elseif ($fea_spo == 'createdbyfriends') {
      if($viewer->getIdentity()) {
				$params['createdbyfriends'] = 2;
				//GET AN ARRAY OF FRIEND IDS
				$friends = $viewer->membership()->getMembers();
				$ids = array();
				foreach ($friends as $friend) {
					$ids[] = $friend->user_id;
				}
				$params['users'] = $ids;
			}
    } 

    $this->view->statistics = $params['statistics'] = $this->_getParam('statistics', array("likeCount", "reviewCount"));
    $this->view->layouts_views = $params['layouts_views'] = $this->_getParam('layouts_views',array("listview", "gridview"));
    $this->view->postedby = $params['postedby'] = $this->_getParam('postedby', 1);
    $params['ratingType'] = $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $params['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id');
    $sitereviewListingView = Zend_Registry::isRegistered('sitereviewListingView') ?  Zend_Registry::get('sitereviewListingView') : null;
    if (empty($listingtype_id)) {
      $params['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    }

    if (!empty($listingtype_id)) {
      $this->view->category_id = $params['category_id'] = $this->_getParam('hidden_category_id');
      $params['subcategory_id'] = $this->_getParam('hidden_subcategory_id');
      $params['subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id');
    }
    
    
    if(!$this->_getParam('hidden_category_id')) {
        
       $this->view->category_id = $params['hidden_category_id'] = $params['category_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('category_id');
    }
    
    if(!$this->_getParam('hidden_subcategory_id')) {
        
       $this->view->category_id = $params['hidden_subcategory_id'] = $params['subcategory_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('subcategory_id');
    }
    
    if(!$this->_getParam('hidden_subsubcategory_id')) {
        
       $this->view->category_id = $params['hidden_subsubcategory_id'] = $params['subsubcategory_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('subsubcategory_id');
    }
    
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->showContent = $params['showContent'] = $this->_getParam('showContent', array("price", "location")); 
    $this->view->truncation = $params['truncation'] = $this->_getParam('truncation', 16);
    $this->view->truncationList = $params['truncationList'] = $this->_getParam('truncationList', 100);
    $this->view->bottomLine = $params['bottomLine'] = $this->_getParam('bottomLine', 2);
    $this->view->bottomLineGrid = $params['bottomLineGrid'] = $this->_getParam('bottomLineGrid', 2);
    $this->view->columnWidth = $params['columnWidth'] = $this->_getParam('columnWidth', '180');
    $this->view->truncationGrid = $params['truncationGrid'] = $this->_getParam('truncationGrid', 100);
    $this->view->columnHeight = $params['columnHeight'] = $this->_getParam('columnHeight', '328');
    $params['interval'] = $interval = $this->_getParam('interval', 'overall');

    $this->view->detactLocation = $params['detactLocation'] = $this->_getParam('detactLocation', 0);
    if($listingtype_id && $this->view->detactLocation) {
      $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    }          
    if($this->view->detactLocation) {
      $params['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);    
      $params['latitude'] = $this->_getParam('latitude', 0);
      $params['longitude'] = $this->_getParam('longitude', 0);
    }
     $this->view->enableLocation = $checkLocation = $listingtype_id > 0 ? Engine_Api::_()->sitereview()->enableLocation($listingtype_id) : 1;
    $params['paginator'] = 1;
    //$params['format'] = 'html';
    
    $params['page'] = $this->_getParam('page', 1);
    $this->view->identity = $params['identity'] = $this->_getParam('identity', $this->view->identity);
    $this->view->params= $params;
   
    //GET LISTINGS
    $this->view->listings = $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->listingsBySettings($params);

    $this->view->totalCount = $paginator->getTotalItemCount();
    //DON'T RENDER IF RESULTS IS ZERO
    if (($this->view->totalCount <= 0) || empty($sitereviewListingView) ) { 
      return $this->setNoRender();
    }
    $this->view->viewType = $params['viewType'] = $this->_getParam('viewType', 'gridview');
    $this->view->listingType = $params['listingType'] = $this->_getParam('listingType', 'gridview');
    //Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->view->listingtype_id = $listingtype_id;
  }

}
