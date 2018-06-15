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
class Sitereview_Widget_WishlistBrowseController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
      
    //DO NOT RENDER IF FAVOURITE FUNCTIONALITY IS ENABLED  
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
      return $this->setNoRender();
    }      

    //GET ZEND REQUEST OBJECT
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $requestParams = $request->getParams();
    $this->view->viewTypes = $viewTypes = $this->_getParam('viewTypes', array("list", "grid"));
    $this->view->followLike = $this->_getParam('followLike', array("follow", "like"));
    $this->view->statisticsWishlist = $this->_getParam('statisticsWishlist', array("entryCount", "likeCount", "viewCount", "followCount"));
    $viewTypeDefault = $this->_getParam('viewTypeDefault', 'grid');
    if (!in_array($viewTypeDefault, $viewTypes)) {
      $viewTypeDefault = $viewTypes[0];
    }
    if (!isset($requestParams['viewType'])) {
      $this->view->setAlsoInForm = true;
      $requestParams['viewType'] = $viewTypeDefault;
    }
    //GENERATE SEARCH FORM
    $this->view->form = $form = new Sitereview_Form_Wishlist_Search();
    $form->populate($requestParams);
    $this->view->formValues = $form->getValues();
    $page = $request->getParam('page', 1);

    //GET PAGINATOR
    $params = array();
    $params['pagination'] = 1;
    $params = array_merge($requestParams, $params);
    $itemCount = $this->_getParam('itemCount', 20);
    $this->view->isSearched = Count($params);
    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->getBrowseWishlists($params);
    $sitereviewBrowseWishlist = Zend_Registry::isRegistered('sitereviewBrowseWishlist') ?  Zend_Registry::get('sitereviewBrowseWishlist') : null;
    $this->view->paginator->setItemCountPerPage($itemCount);
    $this->view->paginator->setCurrentPageNumber($page);
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    $this->view->listThumbsCount = $this->_getParam('listThumbsCount', 4);
    $this->view->isAjax = $this->_getParam('isAjax', false);
    $this->view->page = $page;
    $this->view->totalPages = ceil(($paginator->getTotalItemCount()) /$itemCount);
    $this->view->autoContentLoad = $this->_getParam('isappajax', 0);
    if ($this->view->isAjax || $this->view->autoContentLoad) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    $this->view->params = $params = $this->_getAllParams();

    if ($viewer_id)
      $this->view->allowFollow = 1;
    
    if(empty($sitereviewBrowseWishlist) ) {
      return $this->setNoRender();
    }
  }

}
