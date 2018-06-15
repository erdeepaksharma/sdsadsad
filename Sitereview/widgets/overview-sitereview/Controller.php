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
class Sitereview_Widget_OverviewSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    $this->view->showComments = $this->_getParam('showComments', 0);

    //GET EDITOR REVIEW ID
    $params = array();
    $params['resource_id'] = $sitereview->listing_id;
    $params['resource_type'] = $sitereview->getType();
    $params['type'] = 'editor';
    $showAfterEditorReview = $this->_getParam('showAfterEditorReview', 1);


    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "overview"))
        return $this->setNoRender();
    }

    if ($showAfterEditorReview < 2) {
      $editor_review_id = Engine_Api::_()->getDbTable('reviews', 'sitereview')->canPostReview($params);

      //DONT RENDER IF NO REVIEW ID IS EXIST
      if (empty($editor_review_id) || empty($showAfterEditorReview)) {
        return $this->setNoRender();
      }
    }

    $this->view->params = $params = $this->_getAllParams();
    $this->view->params = $params = array_merge($params, array('listingtype_id'=> $sitereview->listingtype_id));
    if ($this->_getParam('loaded_by_ajax', false)) {
      $this->view->loaded_by_ajax = true;
      if ($this->_getParam('is_ajax_load', false)) {
        $this->view->is_ajax_load = true;
        $this->view->loaded_by_ajax = false;
        if (!$this->_getParam('onloadAdd', false))
          $this->getElement()->removeDecorator('Title');
        $this->getElement()->removeDecorator('Container');
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
        $this->view->showContent = true;
      }
    } else {
      $this->view->showContent = true;
    }

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_singular_lc = strtolower($listingType->title_singular);
    $this->view->listing_singular_upper = strtoupper($listingType->title_singular);

    if (empty($listingType->overview)) {
      return $this->setNoRender();
    }

    //GET VIEWER DETAIL
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
    $this->view->overview = $overview = $tableOtherinfo->getColumnValue($sitereview->getIdentity(), 'overview');

    if (empty($overview) && !$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
      return $this->setNoRender();
    }

    if (empty($overview) && !$sitereview->authorization()->isAllowed($viewer, 'overview_listtype_' . $sitereview->listingtype_id)) {
      return $this->setNoRender();
    }
  }

}