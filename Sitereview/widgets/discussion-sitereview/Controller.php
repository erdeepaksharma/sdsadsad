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
class Sitereview_Widget_DiscussionSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTINGTYPE ID
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    //WHO CAN POST THE DISCUSSION
    $this->view->canPost = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, 'topic_listtype_' . $sitereview->listingtype_id);
    
    $sitereviewDiscussion = Zend_Registry::isRegistered('sitereviewDiscussion') ?  Zend_Registry::get('sitereviewDiscussion') : null;

    //GET PAGINATOR
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('sitereview_topic')->getListingTopices($sitereview->getIdentity());

    //DONT RENDER IF NOTHING TO SHOW
    if (($paginator->getTotalItemCount() <= 0 && (!$viewer->getIdentity() || empty($this->view->canPost))) || empty($sitereviewDiscussion)) {
      return $this->setNoRender();
    }

    //ADD COUNT TO TITLE
    if ($this->_getParam('titleCount', false) && $paginator->getTotalItemCount() > 0) {
      $this->_childCount = $paginator->getTotalItemCount();
    }

    $params = $this->_getAllParams();
    $this->view->params = $params;
    if ($this->_getParam('loaded_by_ajax', false)) {
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
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}