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
class Sitereview_Widget_EditorsSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
    
    //GET SETTINGS
    $params = array();
    $this->view->count = $params['limit'] = $this->_getParam('itemCount', 4);
    $this->view->listingtype_id = $params['listingtype_id'] = $this->_getParam('listingtype_id', null);
    $this->view->viewType = $this->_getParam('viewType', 1);
    $this->view->superEditor = $this->_getParam('superEditor', 1);
    //GET TOTAL ENABLED LISTING TYPES COUNT
    $this->view->countListingtypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount(true);

    //GET EDITOR TABLE
    $this->view->editorTable = $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');

    //GET USER SUBJECT IF WIDGET IS PLACED AT EDITOR PROFILE PAGE
    if (Engine_Api::_()->core()->hasSubject('user')) {
      $user = Engine_Api::_()->core()->getSubject('user');
      $params['user_id'] = $user->getIdentity();
    }
    
    if (!$this->view->superEditor) {
      $params['super_editor_user_id'] = $editorTable->getSuperEditor('user_id');
    }    
    
    //GET EDITORS
    $this->view->editors = $editorTable->getSimilarEditors($params);
    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			$this->view->editors->setCurrentPageNumber($this->_getParam('page'));
			$this->view->editors->setItemCountPerPage($params['limit']);
			if ($this->view->editors->getTotalItemCount() <= 0) {
				return $this->setNoRender();
			}
    } else {
			if (Count($this->view->editors) <= 0) {
				return $this->setNoRender();
			}
    }

  }

}