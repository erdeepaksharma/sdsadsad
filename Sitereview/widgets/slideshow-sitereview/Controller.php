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
class Sitereview_Widget_SlideshowSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
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

    $values = array();
    $values['limit'] = $this->_getParam('count', 10);
    $values['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id');
    $this->view->statistics = $values['statistics'] = $this->_getParam('statistics', array("viewCount", "likeCount", "commentCount", "reviewCount"));

    if (empty($listingtype_id)) {
      $values['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    }

    $sitereviewSlideshow = Zend_Registry::isRegistered('sitereviewSlideshow') ?  Zend_Registry::get('sitereviewSlideshow') : null;
    $this->view->category_id = $values['category_id'] = $this->_getParam('hidden_category_id', 0);
    if ($values['category_id']) {
      $values['subcategory_id'] = $this->_getParam('hidden_subcategory_id', 0);
      if ($values['subcategory_id'])
        $values['subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id', 0);
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    $this->view->title_truncation = $values['truncation'] = $this->_getParam('truncation', 45);
    $this->view->ratingType = $values['ratingType'] = $this->_getParam('ratingType', 'rating_avg');
    $this->view->popularity = $values['popularity'] = $this->_getParam('popularity', 'creation_date');
    $this->view->fea_spo = $fea_spo = $values['fea_spo'] = $this->_getParam('fea_spo', '');
    $this->view->sponsoredIcon = $values['sponsoredIcon'] = $this->_getParam('sponsoredIcon', 1);
    $this->view->featuredIcon = $values['featuredIcon'] = $this->_getParam('featuredIcon', 1);
    $this->view->newIcon = $values['newIcon'] = $this->_getParam('newIcon', 1);
    $this->view->showExpiry = $values['showExpiry'] = $this->_getParam('showExpiry', 0);
    if ($fea_spo == 'featured') {
      $values['featured'] = 1;
    } elseif ($fea_spo == 'sponsored') {
      $values['sponsored'] = 1;
    } elseif ($fea_spo == 'newlabel') {
      $values['newlabel'] = 1;
    } elseif ($fea_spo == 'fea_spo') {
      $values['sponsored_or_featured'] = 1;
    } elseif ($fea_spo == 'createdbyfriends') {
      if($viewer->getIdentity()) {
				$values['createdbyfriends'] = 2;
				//GET AN ARRAY OF FRIEND IDS
				$friends = $viewer->membership()->getMembers();
				$ids = array();
				foreach ($friends as $friend) {
					$ids[] = $friend->user_id;
				}
				$values['users'] = $ids;
			}
    }
    $values['interval'] = $interval = $this->_getParam('interval', 'overall');
    
    $this->view->detactLocation = $values['detactLocation'] = $this->_getParam('detactLocation', 0);
    if($listingtype_id && $this->view->detactLocation) {
      $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
    }          
    if($this->view->detactLocation) {
      $values['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);    
      $values['latitude'] = $this->_getParam('latitude', 0);
      $values['longitude'] = $this->_getParam('longitude', 0);
    }    
    
    $this->view->params = $values;

    //FETCH FEATURED LISTINGS
    $this->view->show_slideshow_object = Engine_Api::_()->getDbTable('listings', 'sitereview')->listingsBySettings($values);

    //RESULTS COUNT
    $this->view->num_of_slideshow = count($this->view->show_slideshow_object) > $values['limit'] ? $values['limit'] : count($this->view->show_slideshow_object);
    if (($this->view->num_of_slideshow <= 0) || empty($sitereviewSlideshow)) {
      return $this->setNoRender();
    }

    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
  }

}