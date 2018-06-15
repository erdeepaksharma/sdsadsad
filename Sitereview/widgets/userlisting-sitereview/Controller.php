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
class Sitereview_Widget_UserlistingSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    $this->view->statistics = $this->_getParam('statistics', array("likeCount", "reviewCount", "commentCount"));

    //GET LISTING SUBJECT
    $this->view->listing = $listing = Engine_Api::_()->core()->getSubject('sitereview_listing');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listing->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listing->listingtype_id);

    $this->view->listing_singular_uc = ucfirst($listingtypeArray->title_singular);

    if (!empty($this->view->statistics) && empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
      $key = array_search('reviewCount', $this->view->statistics);
      if (!empty($key)) {
        unset($this->view->statistics[$key]);
      }
    }
    
    $this->view->count = $limit = $this->_getParam('count', 3);
    $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $this->view->truncation = $this->_getParam('truncation', 24);

    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			$this->view->listings = Engine_Api::_()->getDbTable('listings', 'sitereview')->userListing($listing->owner_id, $listing->listing_id, $listing->listingtype_id, $limit);
      $this->_childCount = count($this->view->listings);
    } else {
			$this->view->listings = Engine_Api::_()->getDbTable('listings', 'sitereview')->userListing($listing->owner_id, $listing->listing_id, $listing->listingtype_id, $limit);
      $this->view->listings->setCurrentPageNumber($this->_getParam('page'));
      $this->view->listings->setItemCountPerPage($limit);
      $this->_childCount = $this->view->listings->getTotalItemCount();
    }

    if ($this->_childCount <= 0) {
      return $this->setNoRender();
    }
    $this->view->viewType = $this->_getParam('viewType', 'listview');
    $this->view->columnWidth = $this->_getParam('columnWidth', '180');
    $this->view->columnHeight = $this->_getParam('columnHeight', '328');
    //SET WIDGET TITLE
    $element = $this->getElement();
    $translate = Zend_Registry::get('Zend_Translate');
    $element->setTitle(sprintf($translate->translate($element->getTitle()), $listing->getOwner()->getTitle()));
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}