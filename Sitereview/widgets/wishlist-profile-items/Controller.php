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
class Sitereview_Widget_WishlistProfileItemsController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
      return $this->setNoRender();
    }

    //GET SEARCH FORM
    $this->view->searchForm = $searchForm = new Sitereview_Form_Wishlist_Searchitems();
    $this->view->isAjax = $this->_getParam('isAjax', false);
     $this->view->autoContentLoad = $this->_getParam('isappajax', 0);
    if ($this->view->isAjax || $this->view->autoContentLoad) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }

    //GET SETTINGS
    $this->view->params = $params = $this->_getAllParams();
    $this->view->statisticsWishlist = $this->_getParam('statisticsWishlist', array("entryCount", "likeCount", "viewCount", "followCount"));
    $this->view->followLike = $this->_getParam('followLike', array("follow", "like"));
    $this->view->truncationDescription = $this->_getParam('truncationDescription', 100);
    $this->view->itemWidth = $this->view->params['itemWidth'] = $this->_getParam('itemWidth', 220);
    $this->view->postedby = $this->_getParam('postedby', 1);
    $this->view->postedbyInList = $this->_getParam('postedbyInList', 1);
    $this->view->itemCount = $params['itemCount'] = $itemCount = $this->_getParam('itemCount', 10);
    $params['orderby'] = $this->view->params['orderby'] = $this->_getParam('orderby', 'date');
    $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
    if (!$this->view->isAjax) {
      $this->view->shareOptions = $this->_getParam('shareOptions', array("siteShare", "friend", "report", "print", "socialShare"));
      $this->view->viewTypes = $viewTypes = $this->_getParam('viewTypes', array("list", "pin"));
      $viewTypeDefault = $this->_getParam('viewTypeDefault', 'pin');
      if (!in_array($viewTypeDefault, $viewTypes)) {
        $viewTypeDefault = $viewTypes[0];
      }
      $params['viewType'] = $viewTypeDefault;
      $searchForm->viewType->setValue($viewTypeDefault);
    }
    $this->view->params = $params;

    //GET SUBJECT
    $this->view->wishlist = $wishlist = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

    //GET VIEWER INFO
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    if (!empty($viewer_id)) {
      $this->view->level_id = $level_id = $viewer->level_id;
    } else {
      $this->view->level_id = $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    if (!$this->view->isAjax) {
      //SHOW MESSAGE OWNER LINK TO USER IF MESSAGING IS ENABLED FOR THIS LEVEL
      $showMessageOwner = 0;
      $showMessageOwner = Engine_Api::_()->authorization()->getPermission($level_id, 'messages', 'auth');
      if ($showMessageOwner != 'none') {
        $showMessageOwner = 1;
      }

      //RETURN IF NOT AUTHORIZED
      $this->view->messageOwner = 1;
      if ($wishlist->owner_id == $viewer_id || empty($viewer_id) || empty($showMessageOwner)) {
        $this->view->messageOwner = 0;
      }
    }
    //FETCH RESULTS
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->wishlistListings($wishlist->wishlist_id, $params);
    $this->view->paginator->setItemCountPerPage($itemCount);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('currentpage', 1));
    $this->view->total_item = $this->view->paginator->getTotalItemCount();
    $this->view->page = $this->_getParam('currentpage', 1);
    $this->view->totalPages = ceil(($paginator->getTotalItemCount()) /$itemCount);
   

    $this->view->show_buttons = $this->_getParam('show_buttons', array("wishlist", "compare", "comment", "like", 'share', 'facebook', 'twitter', 'pinit'));
    $this->view->statistics = $this->_getParam('statistics', array("likeCount","reviewCount","viewCount","followCount"));
    //SET LISTING TYPE
    Engine_Api::_()->sitereview()->setListingTypeInRegistry(-1);
  }
}
