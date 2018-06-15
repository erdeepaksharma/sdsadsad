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
class Sitereview_Widget_WriteSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET MODULE NAME
    $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
    if ($module != 'sitereview') {
      return $this->setNoRender();
    }

    if ($this->_getParam('removeContent', false)) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }

    //GET VIEWER ID
    $sitereviewWriteReview = Zend_Registry::isRegistered('sitereviewWriteReview') ?  Zend_Registry::get('sitereviewWriteReview') : null;
    if( empty($sitereviewWriteReview) ) {
      return $this->setNoRender();
    }
    $this->view->viewer_id = $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->isOwner = 0;

    //DONT RENDER IF SUBJECT IS NOT SET
    if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('sitereview_listing');
      $this->view->subjectId = $subject->listing_id;
      
      if ($subject->owner_id == $viewer_id) {
        $this->view->isOwner = 1;
      }
      
      $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
      $this->view->aboutSubject = $tableOtherinfo->getColumnValue($this->view->subjectId, 'about');
      $this->view->listingTypeId = $subject->listingtype_id;
    } elseif (Engine_Api::_()->core()->hasSubject('user')) {
      
      $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('user');
      $this->view->subjectId = $subject->user_id;
      if ($subject->user_id == $viewer_id) {
        $this->view->isOwner = 1;
      }

      $user_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('user_id', null);
      $editor_id = Engine_Api::_()->getDbTable('editors', 'sitereview')->getColumnValue($user_id, 'editor_id', 0);
      $this->view->editor = $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
      $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
      $this->view->aboutSubject = $editor->about;
      $this->view->listingTypeId = $editor->listingtype_id;
    } else {
      return $this->setNoRender();
    }
    
    if(!$this->view->aboutSubject && empty($this->view->isOwner )){
        return $this->setNoRender();
    }
  }

}