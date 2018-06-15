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
class Sitereview_Widget_RelatedListingsViewSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing') && !Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    //GET LISTING SUBJECT
    if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      $subject = Engine_Api::_()->core()->getSubject();
    } elseif (Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      $subject = Engine_Api::_()->core()->getSubject()->getParent();
    }

    //GET VARIOUS WIDGET SETTINGS
    $this->view->title_truncation = $this->_getParam('truncation', 24);
    $this->view->related = $related = $this->_getParam('related', 'categories');
    $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $this->view->statistics = $this->_getParam('statistics', array("likeCount", "reviewCount", "commentCount"));

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($subject->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $subject->listingtype_id);
    if (!empty($this->view->statistics) && empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
      $key = array_search('reviewCount', $this->view->statistics);
      if (!empty($key)) {
        unset($this->view->statistics[$key]);
      }
    }

    $params = array();

    If ($related == 'tags') {

      //GET TAGS
      $listingTags = $subject->tags()->getTagMaps();

      $params['tags'] = array();
      foreach ($listingTags as $tag) {
        $params['tags'][] = $tag->getTag()->tag_id;
      }

      if (empty($params['tags'])) {
        return $this->setNoRender();
      }
    } elseif ($related == 'categories') {
      $params['category_id'] = $subject->category_id;
    } else {
      return $this->setNoRender();
    }

    //FETCH LISTINGS
    $params['listing_id'] = $subject->listing_id;
    $params['orderby'] = 'RAND()';
    $this->view->count = $limit = $params['limit'] = $this->_getParam('itemCount', 3);
    $params['listingtype_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    $this->view->paginator = Engine_Api::_()->getDbtable('listings', 'sitereview')->widgetListingsData($params);
    
		if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      $this->_childCount = count($this->view->paginator);
    } else {
      $this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
      $this->view->paginator->setItemCountPerPage($limit);
      $this->_childCount = $this->view->paginator->getTotalItemCount();
    }

		if ($this->_childCount <= 0) {
      return $this->setNoRender();
    }

		$this->view->columnWidth = $this->_getParam('columnWidth', '180');
		$this->view->columnHeight = $this->_getParam('columnHeight', '328');
		$this->view->viewType = $this->_getParam('viewType', 'listview');
  }

	public function getChildCount() {
    return $this->_childCount;
  }

}