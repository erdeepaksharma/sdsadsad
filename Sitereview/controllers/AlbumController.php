<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AlbumController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AlbumController extends Core_Controller_Action_Standard {

  protected $_listingType;

  //ACTION FOR EDIT PHOTO
  public function editphotosAction() {

    //LOGGEND IN USER CAN EDIT PHOTO
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET LISTING ID AND OBJECT
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
    $change_url = $this->_getParam('change_url', 0);
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    $listingtype_id = $sitereview->listingtype_id;

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    $this->view->listing_singular_uc = $listing_singular_uc = ucfirst($this->_listingType->title_singular);
    $this->view->listing_singular_lc = $listing_singular_lc = strtolower($this->_listingType->title_singular);
    $this->view->listing_plural_lc = $listing_plural_lc = strtolower($this->_listingType->title_plural);
    $this->view->listing_plural_uc = $listing_plural_uc = ucfirst($this->_listingType->title_plural);

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
      return;

    //IF SITEREVIEW IS NOT EXIST
    if (empty($sitereview)) {
      return $this->_forward('notfound', 'error', 'core');
    }

    //SET LISTING SUBJECT
    Engine_Api::_()->core()->setSubject($sitereview);

    if(!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->_helper->content
            ->setContentName("sitereview_album_editphotos_listtype_$listingtype_id")
            //->setNoRender()
            ->setEnabled();
    
    }
    
    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //AUTHORIZATION CHECK
    $this->view->allowed_upload_video = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer);
    
    if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
      return;
    }

    //SELECTED TAB
    $this->view->TabActive = "photo";

    //PREPARE DATA
    $this->view->album = $album = $sitereview->getSingletonAlbum();
    $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $paginator->setItemCountPerPage($paginator->getTotalItemCount());
    $this->view->count = count($paginator);
    
    //AUTHORIZATION CHECK
    $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");
     
    $this->view->upload_photo = 0;
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id);
			if(Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
			  $allowed_upload_photo = 1;
				if(empty($package->photo_count))
				$this->view->upload_photo = 1;
				elseif($package->photo_count > $paginator->getTotalItemCount()) 
				$this->view->upload_photo = 1;
			}
			else
			$allowed_upload_photo = 0;
    }
    else {
      $this->view->upload_photo = $allowed_upload_photo;
    }
    
  
    if (empty($allowed_upload_photo)) {
      return $this->_forward('requireauth', 'error', 'core');
    }
    
    $this->view->slideShowEnanle = $slideShowEnanle = $slideShowEnable = $this->slideShowEnable($listingtype_id);

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Album_Photos();
    $this->view->enableVideoPlugin = $slideShowEnanle ? Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer) : 0;
    if ($this->view->enableVideoPlugin) {
      $form->addElement('Radio', 'video_snapshot_id', array(
          'label' => 'Video Snapshot',
      ));
    }
    foreach ($paginator as $photo) {
      $subform = new Sitereview_Form_Photo_SubEdit(array('elementsBelongTo' => $photo->getGuid()));
      if (empty($slideShowEnable)) {
        $subform->removeElement('show_slidishow');
      }
      $subform->populate($photo->toArray());
      $form->addSubForm($subform, $photo->getGuid());
      $form->cover->addMultiOption($photo->file_id, $photo->file_id);
      if ($this->view->enableVideoPlugin) {
        $form->video_snapshot_id->addMultiOption($photo->photo_id, $photo->photo_id);
      }
    }

    //CHECK METHOD
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    
    $getListingTypeInfo = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingTypeInfo();
    $table = Engine_Api::_()->getDbTable('albums', 'sitereview');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      if (!empty($values['cover']) && $sitereview->photo_id !=$values['cover']) {

        $album->photo_id = $values['cover'];
        $album->save();

        $sitereview->photo_id = $values['cover'];
        $sitereview->save();
        $sitereview->updateAllCoverPhotos();
      }

      if (!empty($values['video_snapshot_id'])) {
        $sitereview->video_snapshot_id = $values['video_snapshot_id'];
        $sitereview->save();
      }

      //PROCESS
      foreach ($paginator as $photo) {

        $subform = $form->getSubForm($photo->getGuid());
        $values = $subform->getValues();
        $values = $values[$photo->getGuid()];

        if (isset($values['delete']) && $values['delete'] == '1') {
          $photo->delete();
        } else {
          $photo->setFromArray($values);
          $photo->save();
        }
      }

      if(!empty($sitereview->photo_id)) {
				$photoTable = Engine_Api::_()->getItemTable('sitereview_photo');
				$order = $photoTable->select()
														->from($photoTable->info('name'), array('order'))
														->where('listing_id = ?', $sitereview->listing_id)
														->group('photo_id')
														->order('order ASC')
														->limit(1)
														->query()
														->fetchColumn();
        
				$photoTable->update(array('order' => $order - 1), array('file_id = ?' => $sitereview->photo_id));
      
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    if (empty($change_url)) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'editphotos', 'listing_id' => $album->listing_id), "sitereview_albumspecific_listtype_$listingtype_id", true);
    } else {
      return $this->_helper->redirector->gotoRoute(array('action' => 'change-photo', 'listing_id' => $album->listing_id), "sitereview_dashboard_listtype_$listingtype_id", true);
    }
  }

  public function orderAction() {
    
    if (!$this->_helper->requireUser()->isValid())
      return;
    
    if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
      return;

    $subject = Engine_Api::_()->core()->getSubject();

    $order = $this->_getParam('order');
    if (!$order) {
      $this->view->status = false;
      return;
    }

    $album = $subject->getSingletonAlbum();
    
    // Get a list of all photos in this album, by order
    $photoTable = Engine_Api::_()->getItemTable('sitereview_photo');
    $currentOrder = $photoTable->select()
            ->from($photoTable, 'photo_id')
            ->where('album_id = ?', $album->getIdentity())
            ->where('listing_id = ?', $subject->getIdentity())
            ->order('order ASC')
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN)
    ;

    // Find the starting point?
    $start = null;
    $end = null;
    for ($i = 0, $l = count($currentOrder); $i < $l; $i++) {
      if (in_array($currentOrder[$i], $order)) {
        $start = $i;
        $end = $i + count($order);
        break;
      }
    }

    if (null === $start || null === $end) {
      $this->view->status = false;
      return;
    }

    for ($i = 0, $l = count($currentOrder); $i < $l; $i++) {
      if ($i >= $start && $i <= $end) {
        $photo_id = $order[$i - $start];
      } else {
        $photo_id = $currentOrder[$i];
      }
      $photoTable->update(array(
          'order' => $i,
              ), array(
          'photo_id = ?' => $photo_id,
      ));
    }

    $this->view->status = true;
  }

  public function slideShowEnable($listingtype_id) {
    
    //GET CONTENT TABLE
    $tableContent = Engine_Api::_()->getDbtable('content', 'core');
    $tableContentName = $tableContent->info('name');

    //GET PAGE TABLE
    $tablePage = Engine_Api::_()->getDbtable('pages', 'core');
    $tablePageName = $tablePage->info('name');
    //GET PAGE ID
    $page_id = $tablePage->select()
            ->from($tablePageName, array('page_id'))
            ->where('name = ?', "sitereview_index_view_listtype_$listingtype_id")
            ->query()
            ->fetchColumn();

    if (empty($page_id)) {
      return false;
    }

    $content_id = $tableContent->select()
            ->from($tableContent->info('name'), array('content_id'))
            ->where('page_id = ?', $page_id)
            ->where('name = ?', 'sitereview.slideshow-list-photo')
            ->query()
            ->fetchColumn();

    if ($content_id)
      return true;

    $params = $tableContent->select()
            ->from($tableContent->info('name'), array('params'))
            ->where('page_id = ?', $page_id)
            ->where('name = ?', 'sitereview.editor-reviews-sitereview')
            ->query()
            ->fetchColumn();
    if ($params) {
      $params = Zend_Json::decode($params);
      if (!isset($params['show_slideshow']) || $params['show_slideshow']) {
        return true;
      }
      return false;
    }
  }

}