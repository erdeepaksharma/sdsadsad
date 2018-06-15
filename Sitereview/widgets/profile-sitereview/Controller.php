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
class Sitereview_Widget_ProfileSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //GET VIEWER DETAIL
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $this->view->allParams = $this->_getAllParams();
    $this->view->isajax = $this->_getParam('isajax', 0);
    if ($this->_getParam('isajax', 0)) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    $this->view->viewmore = $this->_getParam('viewmore', false);
    $this->view->viewType = $this->_getParam('viewType', '');
    $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', -1);
    $this->view->title_truncation = $this->_getParam('truncation', 35);
    $this->view->statistics = $this->_getParam('statistics', array("viewCount", "likeCount", "commentCount", "reviewCount"));
     $this->view->limit = $itemCount = $this->_getParam('itemCount', 10);

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    
    if($listingtype_id > 0) {
      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
      if (!empty($this->view->statistics) && empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
        $key = array_search('reviewCount', $this->view->statistics);
        if (!empty($key)) {
          unset($this->view->statistics[$key]);
        }
      }
    }

    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $level_id = Engine_Api::_()->user()->getViewer()->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    //DON'T RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $subject = Engine_Api::_()->core()->getSubject();
    if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      return $this->setNoRender();
    }

    //FETCH RESULTS
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator(array(
        'type' => 'browse',
        'orderby' => 'listing_id',
        'user_id' => $subject->getIdentity(),
        'listingtype_id' => $listingtype_id,
            ));
    $paginator->setItemCountPerPage($itemCount);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $this->view->totalCount = $paginator->getTotalItemCount();
    $this->view->current_page = $this->_getParam('page', 1);
    $this->view->allParams['page'] = $this->view->current_page;
    //DONT RENDER IF RESULTS IS ZERO
    if ($paginator->getTotalItemCount() <= 0) {
      return $this->setNoRender();
    }

    //ADD LISTING COUNT
    if ($this->_getParam('titleCount', false)) {
      $this->_childCount = $paginator->getTotalItemCount();
    }
    
    if(!$this->view->isajax) {
        $this->view->params = $this->_getAllParams();
        if ($this->_getParam('loaded_by_ajax', true)) {
          $this->view->loaded_by_ajax = true;
          if ($this->_getParam('is_ajax_load', false)) {
            $this->view->is_ajax_load = true;
            $this->view->loaded_by_ajax = false;
            if (!$this->_getParam('onloadAdd', false))
              $this->getElement()->removeDecorator('Title');
            $this->getElement()->removeDecorator('Container');
          } else {
            return;
          }
        }
        $this->view->showContent = true;    
    }
    else {
        $this->view->showContent = true;
    }       
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}
