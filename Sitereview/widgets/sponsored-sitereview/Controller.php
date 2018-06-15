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
class Sitereview_Widget_SponsoredSitereviewController extends Engine_Content_Widget_Abstract {

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

    $this->view->titleLink = $this->_getParam('titleLink', '');
    $this->view->showPagination = $this->_getParam('showPagination', 1);
    $this->view->vertical = $values['viewType'] = $this->_getParam('viewType', 0);
    $values = array();
    $this->view->listingtype_id = $values['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id');
    $sitereviewSponsored = Zend_Registry::isRegistered('sitereviewSponsored') ?  Zend_Registry::get('sitereviewSponsored') : null;
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $values['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    }

    if (!empty($listingtype_id)) {
      $this->view->category_id = $values['category_id'] = $this->_getParam('hidden_category_id');
      $this->view->subcategory_id = $values['subcategory_id'] = $this->_getParam('hidden_subcategory_id');
      $this->view->subsubcategory_id = $values['subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id');
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->view->interval = $values['interval'] = $this->_getParam('interval', 300);
    $this->view->blockHeight = $values['blockHeight'] = $this->_getParam('blockHeight', 240);
    $this->view->blockWidth = $values['blockWidth'] = $this->_getParam('blockWidth', 150);
    $this->view->showOptions = $values['showOptions'] = $this->_getParam('showOptions', array("category","rating","review","compare","wishlist"));

    $this->view->title_truncation = $values['truncation'] = $this->_getParam('truncation', 50);
    $this->view->ratingType = $values['ratingType'] = $this->_getParam('ratingType', 'rating_avg');
    $this->view->viewType = $values['viewType'] = $this->_getParam('viewType', 0);
    $this->view->limit = $values['limit'] = $this->_getParam('itemCount', 3);
    $this->view->sponsoredIcon = $values['sponsoredIcon'] = $this->_getParam('sponsoredIcon', 1);
    $this->view->featuredIcon = $values['featuredIcon'] = $this->_getParam('featuredIcon', 1);
    $this->view->newIcon = $values['newIcon'] = $this->_getParam('newIcon', 1);
    $this->view->popularity = $values['popularity'] = $this->_getParam('popularity', 'creation_date');
    $this->view->fea_spo = $fea_spo = $values['fea_spo'] = $this->_getParam('fea_spo', null);
    if ($fea_spo == 'featured') {
      $values['featured'] = 1;
    } elseif ($fea_spo == 'newlabel') {
      $values['newlabel'] = 1;
    } elseif ($fea_spo == 'sponsored') {
      $values['sponsored'] = 1;
    } elseif ($fea_spo == 'fea_spo') {
      $values['sponsored_or_featured'] = 1;
    }
    
    $this->view->detactLocation = $values['detactLocation'] = $this->_getParam('detactLocation', 0);
    if($listingtype_id && $this->view->detactLocation) {
      $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    }          
    $this->view->defaultLocationDistance = 1000;
    $this->view->latitude = 0;
    $this->view->longitude = 0; 
    if($this->view->detactLocation) {
      $this->view->defaultLocationDistance = $values['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);
      $this->view->latitude = $values['latitude'] = $this->_getParam('latitude', 0);
      $this->view->longitude = $values['longitude'] = $this->_getParam('longitude', 0);
    }
    
    $this->view->params = $values;
    
    //FETCH SPONSERED LISTINGS
    $this->view->listings = $listing = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListing('', $values);

    //GET LIST COUNT
    $this->view->totalCount = $listing->getTotalItemCount();
    if ( ($this->view->totalCount <= 0) || empty($sitereviewSponsored) ) {
      return $this->setNoRender();
    }
  }

}
