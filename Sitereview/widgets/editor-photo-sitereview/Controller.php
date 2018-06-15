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
class Sitereview_Widget_EditorPhotoSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('user')) {
      return $this->setNoRender();
    }

    //GET USER SUBJECT    
    $this->view->user = $user = Engine_Api::_()->core()->getSubject('user');
    $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');

    //GET EDITOR ID
    $editor_id = $editorTable->getColumnValue($user->getIdentity(), 'editor_id', 0);
    $this->view->editor = $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);    
    

    //GET TOTAL ENABLED LISTING TYPES COUNT
    $this->view->countListingtypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount(true);

    //GET EDITOR DETAILS
    $params = array();
    $params['visible'] = 1;
    $params['editorReviewAllow'] = 1;
    $this->view->getDetails = $editorTable->getEditorDetails($editor->user_id, 0, $params);

    $this->view->showContent = $this->_getParam('showContent', array("photo", "title", "about", "details", "designation", "forEditor", "emailMe"));
  }

}