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
class Sitereview_Widget_MostDiscussedListingsController extends Engine_Content_Widget_Abstract
{  
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
    
    $params = array();
    $params['limit'] = $this->_getParam('itemCount', 3);
    $fea_spo = $this->_getParam('fea_spo', '');
    if ($fea_spo == 'featured') {
      $params['featured'] = 1;
    } elseif ($fea_spo == 'sponsored') {
      $params['sponsored'] = 1;
    } elseif($fea_spo == 'newlabel') {
      $params['newlabel'] = 1;
    } elseif ($fea_spo == 'fea_spo') {
      $params['sponsored_or_featured'] = 1;
    }

    $params['ratingType'] = $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $params['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id');
    if (empty($listingtype_id)) {
      $params['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    }

    if (!empty($listingtype_id)) {
      $this->view->category_id = $params['category_id'] = $this->_getParam('hidden_category_id');
      $params['subcategory_id'] = $params['hidden_subcategory_id'] = $this->_getParam('hidden_subcategory_id');
      $params['subsubcategory_id'] = $params['hidden_subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id');
    }

    $this->view->truncation = $params['truncation'] = $this->_getParam('truncation', 16);
    $this->view->columnWidth = $params['columnWidth'] = $this->_getParam('columnWidth', '180');
    $this->view->columnHeight = $params['columnHeight'] = $this->_getParam('columnHeight', '328');
    
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

    //GET LISTINGS
    $this->view->listings = Engine_Api::_()->getDbTable('listings', 'sitereview')->getDiscussedListing($params);

    //DON'T RENDER IF RESULTS IS ZERO
    if (count($this->view->listings) <= 0) {
      return $this->setNoRender();
    }
    $this->view->viewType = $this->_getParam('viewType', 'listview');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
  }  
  
}
