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
class Sitereview_Widget_SimilarItemsSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing') && !Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();
    } elseif (Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();

    $this->view->viewType = $this->_getParam('viewType', 0);
    $this->view->statistics = $this->_getParam('statistics', array("likeCount", "reviewCount", "commentCount"));
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    if (!empty($this->view->statistics) && empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
      $key = array_search('reviewCount', $this->view->statistics);
      if (!empty($key)) {
        unset($this->view->statistics[$key]);
      }
    }

    $values = array();
    $values['listing_id'] = $sitereview->listing_id;
    $this->view->count = $limit = $values['limit'] = $this->_getParam('itemCount', 3);
    $values['similar_items_order'] = 1;
    $this->view->title_truncation = $this->_getParam('truncation', 24);
    $values['ratingType'] = $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');

    $similar_items = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($sitereview->listing_id, 'similar_items');
    $similarItems = array();
    if (!empty($similar_items)) {
      $similarItems = Zend_Json_Decoder::decode($similar_items);
    }

    $showSameCategoryListings = $this->_getParam('showSameCategoryListings', 1);
    
    if (!empty($similar_items) && !empty($similarItems) && Count($similarItems) >= 0) {
      $values['similarItems'] = $similarItems;
      $this->view->listings = $listingTable->getListing('', $values);
    } else {
        
      if(!$showSameCategoryListings) {
          return $this->setNoRender();
      }  
        
      $values['listingtype_id'] = $sitereview->listingtype_id;

      if ($sitereview->subsubcategory_id) {
        $values['subsubcategory_id'] = $sitereview->subsubcategory_id;
      } elseif ($sitereview->subcategory_id) {
        $values['subcategory_id'] = $sitereview->subcategory_id;
      } elseif ($sitereview->category_id) {
        $values['category_id'] = $sitereview->category_id;
      } else {
        return $this->setNoRender();
      }

      $this->view->listings = $listingTable->getListing('', $values);
    }

    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      $this->_childCount = count($this->view->listings);
    } else {
      $this->view->listings->setCurrentPageNumber($this->_getParam('page'));
      $this->view->listings->setItemCountPerPage($limit);
      $this->_childCount = $this->view->listings->getTotalItemCount();
    }

    if ($this->_childCount <= 0) {
      return $this->setNoRender();
    }

    $this->view->columnWidth = $this->_getParam('columnWidth', '180');
    $this->view->columnHeight = $this->_getParam('columnHeight', '328');
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}