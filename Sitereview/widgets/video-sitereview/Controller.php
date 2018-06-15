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
class Sitereview_Widget_VideoSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET VIEWER DETAIL
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    $this->view->type_video = $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');

    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $level_id = Engine_Api::_()->user()->getViewer()->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

    //VIDEO IS ENABLED OR NOT
    $allowed_upload_videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
    if (!$allowed_upload_videoEnable) {
      return $this->setNoRender();
    }

    $this->view->title_truncation = $this->_getParam('truncation', 35);
    $this->view->itemCount = $itemCount = $this->_getParam('count', 10);

    //FETCH RESULTS
    $this->view->paginator = Engine_Api::_()->getDbTable('clasfvideos', 'sitereview')->getListingVideos($sitereview->listing_id, 1, $type_video);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    $this->view->paginator->setItemCountPerPage($itemCount);

    $counter = $this->view->paginator->getTotalItemCount();
    $this->view->allowed_upload_video = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer, $counter, $uploadVideo = 1);

    if (empty($counter) && empty($this->view->allowed_upload_video)) {
      return $this->setNoRender();
    }

    //ADD VIDEO COUNT
    if ($this->_getParam('titleCount', false)) {
      $this->_childCount = $counter;
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
    } else {

      if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "video"))
          return $this->setNoRender();
      }
    }
    $this->view->showContent = true;

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $this->view->can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");

    //IS SITEVIDEOVIEW MODULE ENABLED
    $this->view->sitevideoviewEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitevideoview');
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}
