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
class Sitereview_Widget_DescriptionSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    if (empty($this->view->sitereview->body))
      return $this->setNoRender();

    $this->view->showComments = $this->_getParam('showComments', 0);


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

    $showAlways = $this->_getParam('showAlways', 1);
    if ($showAlways < 2) {
      $hasOverview = true;
      if (!empty($listingType->overview)) {
        $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
        $overview = $tableOtherinfo->getColumnValue($sitereview->getIdentity(), 'overview');
        $hasOverview = !empty($overview);
      }


      //GET EDITOR REVIEW ID
      $params = array();
      $params['resource_id'] = $sitereview->listing_id;
      $params['resource_type'] = $sitereview->getType();
      $params['type'] = 'editor';

      $editor_review_id = Engine_Api::_()->getDbTable('reviews', 'sitereview')->canPostReview($params);
      //DONT RENDER IF NO REVIEW ID IS EXIST
      $hasReview = !empty($editor_review_id);

      if (!($hasOverview || $hasReview))
        return $this->setNoRender();
    }
  }

}