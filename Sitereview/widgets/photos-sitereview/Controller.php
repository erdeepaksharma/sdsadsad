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
class Sitereview_Widget_PhotosSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    //GET PAGINATOR
    $this->view->album = $album = $sitereview->getSingletonAlbum();
    $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    $this->view->total_images = $total_images = $paginator->getTotalItemCount();

    $uploadPhoto = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id);
      if (empty($package->photo_count))
        $this->view->allowed_upload_photo = $uploadPhoto;
      elseif ($package->photo_count > $total_images)
        $this->view->allowed_upload_photo = $uploadPhoto;
      else
        $this->view->allowed_upload_photo = 0;
    }
    else
      $this->view->allowed_upload_photo = $uploadPhoto;

    if (empty($total_images) && !$this->view->allowed_upload_photo) {
      return $this->setNoRender();
    }

    //ADD COUNT TO TITLE
    if ($this->_getParam('titleCount', false) && $total_images > 0) {
      $this->_childCount = $total_images;
    }

    $this->view->params = $params = $this->_getAllParams();
    if ($this->_getParam('loaded_by_ajax', false)) {
      $this->view->loaded_by_ajax = true;
      if ($this->_getParam('is_ajax_load', false)) {
        $this->view->is_ajax_load = true;
        $this->view->loaded_by_ajax = false;
        if (!$this->_getParam('onloadAdd', false))
          $this->getElement()->removeDecorator('Title');
        $this->getElement()->removeDecorator('Container');
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
      } else {
        return;
      }
    } else {
      if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo"))
          return $this->setNoRender();
      }
    }
    $this->view->showContent = true;
    $this->view->itemCount = $this->_getParam('itemCount', 20);

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);

    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $paginator->setItemCountPerPage($this->view->itemCount);
    $this->view->can_edit = $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}