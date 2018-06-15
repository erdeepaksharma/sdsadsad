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
class Sitereview_Widget_EditorProfileReviewsSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //CHECK SUBJECT
    if (!Engine_Api::_()->core()->hasSubject('user')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $type = $this->_getParam('type', 'user');
    if ($type == 'editor' && !Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($user->user_id, 0)) {
      return $this->setNoRender();
    }

    //GET SETTINGS 
    $this->view->isAjax = $this->_getParam('isAjax', 0);
    $this->view->page = $page = $this->_getParam('page', 1);
    $this->view->itemCount = $itemCount = $this->_getParam('itemCount', 10);
    $sitereviewEditorProfileReview = Zend_Registry::isRegistered('sitereviewEditorProfileReview') ?  Zend_Registry::get('sitereviewEditorProfileReview') : null;

    //GET CATEGORY TABLE
    $this->view->tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

    $params = array();
    $params['owner_id'] = $user->getIdentity();
    $this->view->type = $params['type'] = $type;
    $this->view->truncation = $this->_getParam('truncation', 60);
    $params['limit'] = $itemCount;
    $params['popularity'] = 'review_id';
    $params['pagination'] = 1;
    $params['interval'] = $interval = $this->_getParam('interval', 'overall');
    $params['resource_type'] = 'sitereview_listing';
    $onlyListingtypeEditorReviews = $this->_getParam('onlyListingtypeEditorReviews', 1);
    if( empty($onlyListingtypeEditorReviews) && ( $type  == 'user')) {
			$params['listingtype_ids'] = Engine_Api::_()->getDbtable('editors', 'sitereview')->getListingTypeIds($user->user_id); 
    }
    $this->view->paginator = Engine_Api::_()->getDbTable('reviews', 'sitereview')->getReviews($params);
    $this->view->paginator->setItemCountPerPage($itemCount);
    $this->view->paginator->setCurrentPageNumber($page);
    $this->view->count = $count = $this->view->paginator->count();
    if (empty($count) && $this->view->type != 'editor') {
      return $this->setNoRender();
    }

    if ($this->view->type == 'editor') {
      $req = Zend_Controller_Front::getInstance()->getRequest();
      $this->view->showEditorLink = ($req->getModuleName() == 'sitereview' && $req->getControllerName() == 'editor') ? 0 : 1;

      if ((empty($count) && $this->view->showEditorLink) || empty($sitereviewEditorProfileReview)) {
        return $this->setNoRender();
      }
    }
    
    if(!$this->view->isAjax) {
        $this->view->params = array_merge($params, $this->_getAllParams());
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

}