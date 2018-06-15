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
class Sitereview_Widget_RecentlyViewedSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //GET VIEWER ID
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if (empty($viewer_id)) {
      return $this->setNoRender();
    }

    //GET LISTING TYPE ID
    $params = array();
    $params['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id', null);
    if (empty($listingtype_id)) {
      $params['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    $this->view->statistics = $this->_getParam('statistics', array("likeCount", "reviewCount"));
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $sitereviewRecentlyViewed = Zend_Registry::isRegistered('sitereviewRecentlyViewed') ?  Zend_Registry::get('sitereviewRecentlyViewed') : null;
    
    if($listingtype_id > 0) {
      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
      if (!empty($this->view->statistics) && empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
        $key = array_search('reviewCount', $this->view->statistics);
        if (!empty($key)) {
          unset($this->view->statistics[$key]);
        }
      }
    }

    $this->view->count = $params['limit'] = $this->_getParam('count', 3);
    $fea_spo = $this->_getParam('fea_spo', '');
    if ($fea_spo == 'featured') {
      $params['featured'] = 1;
    } elseif ($fea_spo == 'sponsored') {
      $params['sponsored'] = 1;
    } elseif ($fea_spo == 'newlabel') {
      $params['newlabel'] = 1;
    } elseif ($fea_spo == 'fea_spo') {
      $params['sponsored_or_featured'] = 1;
    }
    $params['show'] = $this->_getParam('show', 1);
    $params['viewer_id'] = $viewer_id;
    $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $this->view->title_truncation = $this->_getParam('truncation', 16);

    //GET LISTINGS
    $this->view->listings = Engine_Api::_()->getDbTable('listings', 'sitereview')->recentlyViewed($params);

		if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			$this->view->listings->setCurrentPageNumber($this->_getParam('page'));
			$this->view->listings->setItemCountPerPage($params['limit']);
			if ($this->view->listings->getTotalItemCount() <= 0 || empty($sitereviewRecentlyViewed)) {
				return $this->setNoRender();
			}
      $this->_childCount = $this->view->listings->getTotalItemCount();
    } else {
			if (Count($this->view->listings) <= 0 || empty($sitereviewRecentlyViewed)) {
				return $this->setNoRender();
			}
      $this->_childCount = Count($this->view->listings);
    }

    $this->view->columnWidth = $this->_getParam('columnWidth', '180');
    $this->view->columnHeight = $this->_getParam('columnHeight', '328');
    $this->view->viewType = $this->_getParam('viewType', 'listview');
  }

	public function getChildCount() {
    return $this->_childCount;
  }

}
