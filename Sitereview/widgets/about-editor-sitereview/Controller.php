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
class Sitereview_Widget_AboutEditorSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET LISTING SUBJECT
    $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');

    //EDITOR REVIEW HAS BEEN POSTED OR NOT
    $params = array();
    $params['resource_id'] = $sitereview->listing_id;
    $params['resource_type'] = $sitereview->getType();
    $params['viewer_id'] = 0;
    $params['type'] = 'editor';
    $isEditorReviewed = $reviewTable->canPostReview($params);
    if (empty($isEditorReviewed)) {
      return $this->setNoRender();
    }

    //GET USER ID
    $user_id = $reviewTable->getColumnValue($isEditorReviewed, 'owner_id');
    if (empty($user_id)) {
      return $this->setNoRender();
    }

    $editor_id = Engine_Api::_()->getDbTable('editors', 'sitereview')->getColumnValue($user_id, 'editor_id', $sitereview->listingtype_id);

    $this->view->editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
    $this->view->user = Engine_Api::_()->getItem('user', $user_id);
  }

}
