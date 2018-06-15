<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: PhotoController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_PhotoController extends Seaocore_Controller_Action_Standard {

    protected $_listingType;

    //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
    public function init() {

        //GET LISTING TYPE ID
        $listingtype_id = $this->_getParam('listingtype_id', null);
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
            return;

        //SET SUBJECT
        if (!Engine_Api::_()->core()->hasSubject()) {

            if (0 != ($photo_id = (int) $this->_getParam('photo_id')) &&
                    null != ($photo = Engine_Api::_()->getItem('sitereview_photo', $photo_id))) {
                Engine_Api::_()->core()->setSubject($photo);
            } else if (0 != ($listing_id = (int) $this->_getParam('listing_id')) &&
                    null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
                Engine_Api::_()->core()->setSubject($sitereview);
            }
        }

        $this->_helper->requireUser->addActionRequires(array(
            'upload',
            'upload-photo',
            'edit',
        ));

        $this->_helper->requireSubject->setActionRequireTypes(array(
            'sitereview' => 'sitereview_listing',
            'upload' => 'sitereview_listing',
            'view' => 'sitereview_photo',
            'edit' => 'sitereview_photo',
        ));
    }

    //ACTION FOR UPLOAD PHOTO
    public function uploadAction() {

        if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {

            if (isset($_GET['ul']))
                return $this->_forwardCustom('upload-photo', null, null, array('format' => 'json'));

            if (isset($_FILES['Filedata']))
                $_POST['file'] = $this->uploadPhotoAction();

            //GET VIEWER
            $viewer = Engine_Api::_()->user()->getViewer();
            $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

            //GET LISTING
            $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
            $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
            $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            $this->view->can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");

            //GET LISTING TYPE ID
            $listingtype_id = $this->_listingType->listingtype_id;
            $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");

            //AUTHORIZATION CHECK
            $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");

            if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                $photoCount = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id)->photo_count;
                $paginator = $sitereview->getSingletonAlbum()->getCollectiblesPaginator();

                if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
                    $this->view->allowed_upload_photo = $allowed_upload_photo;
                    if (empty($photoCount))
                        $this->view->allowed_upload_photo = $allowed_upload_photo;
                    elseif ($photoCount <= $paginator->getTotalItemCount())
                        $this->view->allowed_upload_photo = 0;
                } else
                    $this->view->allowed_upload_photo = 0;
            } else
                $this->view->allowed_upload_photo = $allowed_upload_photo;

            if (!$this->getRequest()->isPost()) {
                if (empty($this->view->allowed_upload_photo)) {
                    return $this->_forwardCustom('requireauth', 'error', 'core');
                }
            }

            //GET SETTINGS
            $this->view->allowed_upload_video = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer);

            //SELECTED TAB
            $this->view->TabActive = "photo";

//      if (isset($_GET['ul']) || isset($_FILES['Filedata'])) {
//        return $this->_forwardCustom('upload-photo', null, null, array('format' => 'json', 'listing_id' => (int) $sitereview->getIdentity()));
//      }
            //GET ALBUM
            $album = $sitereview->getSingletonAlbum();

            //MAKE FORM
            $this->view->form = $form = new Sitereview_Form_Photo_Upload();

            $form->file->setAttrib('data', array('listing_id' => $sitereview->getIdentity()));

            $this->view->tab_id = $content_id = $this->_getParam('content_id');
            //CHECK METHOD
            if (!$this->getRequest()->isPost()) {
                return;
            }

            //FORM VALIDATION
            if (!$form->isValid($this->getRequest()->getPost())) {
                return;
            }

            //PROCESS
            $table = Engine_Api::_()->getItemTable('sitereview_photo');
            $db = $table->getAdapter();
            $db->beginTransaction();

            try {

                $values = $form->getValues();
                $params = array(
                    'listing_id' => $sitereview->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                );

                //ADD ACTION AND ATTACHMENTS
                $count = count($values['file']);
                if (!$sitereview->draft && time() >= strtotime($sitereview->creation_date)) {
                    $api = Engine_Api::_()->getDbtable('actions', 'activity');
                    $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $sitereview, 'sitereview_photo_upload_listtype_' . $listingtype_id, null, array('count' => count($values['file']), 'title' => $sitereview->title));
                }

                $count = 0;

                foreach ($values['file'] as $photo_id) {
                    $photo = Engine_Api::_()->getItem("sitereview_photo", $photo_id);

                    if (!($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity())
                        continue;

                    $photo->collection_id = $album->album_id;
                    $photo->album_id = $album->album_id;
                    $photo->save();

                    if ($sitereview->photo_id == 0) {
                        $sitereview->photo_id = $photo->file_id;
                        $sitereview->save();
                    }

                    if (time() >= strtotime($sitereview->creation_date)) {
                        if ($action instanceof Activity_Model_Action && $count < 8) {
                            $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
                        }
                    }
                    $count++;
                }

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }

            if ($this->view->can_edit) {
                return $this->_gotoRouteCustom(array('action' => 'editphotos', 'listing_id' => $album->listing_id), "sitereview_albumspecific_listtype_$listingtype_id", true);
            } else {
                return $this->_gotoRouteCustom(array('listing_id' => $album->listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $content_id), "sitereview_entry_view_listtype_$listingtype_id", true);
            }
        } else {
            //GET VIEWER
            $viewer = Engine_Api::_()->user()->getViewer();
            $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

            //GET LISTING
            $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
            $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);
            $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            $this->view->can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");
            $this->view->tab_selected_id = $content_id = $this->_getParam('content_id');
            //GET LISTING TYPE ID
            $listingtype_id = $this->_listingType->listingtype_id;
            $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");

            if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                //AUTHORIZATION CHECK
                $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "auth_photo_listtype_$listingtype_id");
                if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
                    $this->view->allowed_upload_photo = $allowed_upload_photo;
                } else
                    $this->view->allowed_upload_photo = 0;
            } else
                $this->view->allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");

            if (empty($this->view->allowed_upload_photo)) {
                return $this->_forwardCustom('requireauth', 'error', 'core');
            }
            //GET ALBUM
            $album = $sitereview->getSingletonAlbum();
            $set_cover = true;
            //MAKE FORM
            $this->view->form = $form = new Sitereview_Form_Photo_SitemobileUpload();
            $form->file->setAttrib('data', array('listing_id' => $sitereview->getIdentity()));
            if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
                Zend_Registry::set('setFixedCreationForm', true);
                //Zend_Registry::set('setFixedCreationFormBack', 'Cancel');
                Zend_Registry::set('setFixedCreationHeaderTitle', 'Add Photo: ' . $sitereview->getTitle());
                Zend_Registry::set('setFixedCreationHeaderSubmit', 'Save');
                $this->view->form->setAttrib('id', 'form_sitereview_album_creation');
                Zend_Registry::set('setFixedCreationFormId', '#form_sitereview_album_creation');
                $this->view->form->removeElement('submit');
                $form->setTitle('');
            }
            //IF NOT POST OR FORM NOT VALID, RETURN
            if (!$this->getRequest()->isPost()) {
                return;
            }

            //IF NOT POST OR FORM NOT VALID, RETURN
            if (!$form->isValid($this->getRequest()->getPost())) {
                return;
            }
            $this->view->clear_cache = true;
            //CHECK MAX FILE SIZE
            if (!$this->_helper->requireUser()->checkRequire()) {
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
                return;
            }

            //IF NOT POST OR FORM NOT VALID, RETURN
            if (!$this->getRequest()->isPost()) {
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
                return;
            }

            //FORM VALUES
            $values = $this->getRequest()->getPost();
            if (empty($values)) {
                return;
            }
            //PROCESS
            $tablePhoto = Engine_Api::_()->getDbtable('photos', 'sitereview');
            $db = $tablePhoto->getAdapter();
            $db->beginTransaction();

            //COUNT NO. OF PHOTOS (CHECK ATLEAST SINGLE PHOTO UPLOAD).
            $count = 0;
            foreach ($_FILES['Filedata']['name'] as $data) {
                if (!empty($data)) {
                    $count = 1;
                    break;
                }
            }

            try {

                if (!isset($_FILES['Filedata']) || !isset($_FILES['Filedata']['name']) || $count == 0) {
                    $this->view->status = false;
                    $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid Upload'));
                    return;
                }

                $values['file'] = array();
                foreach ($_FILES['Filedata']['name'] as $key => $uploadFile) {
                    $viewer = Engine_Api::_()->user()->getViewer();
                    $album = $sitereview->getSingletonAlbum();
                    $rows = $tablePhoto->fetchRow($tablePhoto->select()->from($tablePhoto->info('name'), 'order')->order('order DESC')->limit(1));
                    $order = 0;
                    if (!empty($rows)) {
                        $order = $rows->order + 1;
                    }
                    $params = array(
                        'collection_id' => $album->getIdentity(),
                        'album_id' => $album->getIdentity(),
                        'listing_id' => $sitereview->getIdentity(),
                        'user_id' => $viewer->getIdentity(),
                        'order' => $order
                    );

                    $file = array('name' => $_FILES['Filedata']['name'][$key], 'tmp_name' => $_FILES['Filedata']['tmp_name'][$key], 'type' => $_FILES['Filedata']['type'][$key], 'size' => $_FILES['Filedata']['size'][$key], 'error' => $_FILES['Filedata']['error'][$key]);

                    if (!is_uploaded_file($file['tmp_name'])) {
                        continue;
                    }

                    $photo = Engine_Api::_()->sitereview()->createPhoto($params, $file);
                    $photo_id = $photo->photo_id;
                    if (!$sitereview->photo_id) {
                        $sitereview->photo_id = $photo_id;
                        $sitereview->save();
                    }
                    $this->view->status = true;
                    $this->view->name = $_FILES['Filedata']['name'];
                    $this->view->photo_id = $photo_id;
                    $values['file'][] = $photo->photo_id;
                    $db->commit();
                    $order++;
                }
                $api = Engine_Api::_()->getDbtable('actions', 'activity');
                if (!$sitereview->draft && time() >= strtotime($sitereview->creation_date)) {
                    $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $sitereview, 'sitereview_photo_upload_listtype_' . $listingtype_id, null, array('count' => count($values['file']), 'title' => $sitereview->title));
                }
                $count = 0;
                foreach ($values['file'] as $photo_id) {
                    $photo = Engine_Api::_()->getItem("sitereview_photo", $photo_id);

                    if (!($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity())
                        continue;

                    $photo->collection_id = $album->album_id;
                    $photo->album_id = $album->album_id;
                    $photo->save();

                    if ($sitereview->photo_id == 0) {
                        $sitereview->photo_id = $photo->file_id;
                        $sitereview->save();
                    }

                    if (time() >= strtotime($sitereview->creation_date)) {
                        if ($action instanceof Activity_Model_Action && $count < 8) {
                            $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
                        }
                    }
                    $count++;
                }
            } catch (Exception $e) {
                $db->rollBack();
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
                return;
            }
// 			if ($this->view->can_edit) {
// 				return $this->_gotoRouteCustom(array('action' => 'editphotos', 'listing_id' => $album->listing_id), "sitereview_albumspecific_listtype_$listingtype_id", true);
// 			} else {

            return $this->_gotoRouteCustom(array('listing_id' => $album->listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $content_id), "sitereview_entry_view_listtype_$listingtype_id", true);
            //	}
        }
    }

    //ACTION FOR UPLOAD PHOTO
    public function uploadPhotoAction() {

        //GET SITEREVIEW
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', (int) $this->_getParam('listing_id'));

        if (!$this->_helper->requireUser()->checkRequire()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
            return;
        }

        //AUTHORIZATION CHECK
        $allowed_upload_photo = 1;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $photoCount = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id)->photo_count;
            if (!empty($photoCount)) {
                $paginator = $sitereview->getSingletonAlbum()->getCollectiblesPaginator();

                if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
                    if ($photoCount <= $paginator->getTotalItemCount())
                        $allowed_upload_photo = 0;
                } else {
                    $allowed_upload_photo = 0;
                }
            }
        }

        if (empty($allowed_upload_photo)) {
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max photo upload limit exceeded (probably).');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        $values = $this->getRequest()->getPost();
        if (empty($values['Filename']) && !isset($_FILES['Filedata'])) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
            return;
        }

        if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
        }
        $tablePhoto = Engine_Api::_()->getDbtable('photos', 'sitereview');
        $db = $tablePhoto->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $album = $sitereview->getSingletonAlbum();
            $rows = $tablePhoto->fetchRow($tablePhoto->select()->from($tablePhoto->info('name'), 'order')->order('order DESC')->limit(1));
            $order = 0;
            if (!empty($rows)) {
                $order = $rows->order + 1;
            }
            $params = array(
                'collection_id' => $album->getIdentity(),
                'album_id' => $album->getIdentity(),
                'listing_id' => $sitereview->getIdentity(),
                'user_id' => $viewer->getIdentity(),
                'order' => $order
            );
            $photo_id = Engine_Api::_()->sitereview()->createPhoto($params, $_FILES['Filedata'])->photo_id;

            if (!$sitereview->photo_id) {
                $sitereview->photo_id = $photo_id;
                $sitereview->save();
            }

            $this->view->status = true;
            $this->view->name = $_FILES['Filedata']['name'];
            $this->view->photo_id = $photo_id;
            $db->commit();
            return $photo_id;
        } catch (Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
            return;
        }
    }

    //ACTION FOR EDITING OF PHOTOS TITLE AND DISCRIPTION
    public function editAction() {

        //GET PHOTO SUBJECT
        $photo = Engine_Api::_()->core()->getSubject();

        //GET VIEWER
        $this->view->viewer_id = $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //GET LISTING TYPE ID
        $listingtype_id = $this->_listingType->listingtype_id;

        //AUTHORIZATION CHECK
        $this->view->canEdit = 0; //$photo->authorization()->isAllowed(null, "edit_listtype_$listingtype_id");
        if (empty($this->view->canEdit) && $photo->user_id == $viewer_id) {
            $this->view->canEdit = 1;
        }

        if (empty($this->view->canEdit)) {
            return $this->_forwardCustom('requireauth', 'error', 'core');
        }

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Photo_Edit();

        //CHECK METHOD
        if (!$this->getRequest()->isPost()) {
            $form->populate($photo->toArray());
            return;
        }

        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        //PROCESS
        $db = Engine_Api::_()->getDbtable('photos', 'sitereview')->getAdapter();
        $db->beginTransaction();

        try {
            $photo->setFromArray($form->getValues())->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forwardCustom('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Changes saved')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
                    'closeSmoothbox' => true,
        ));
    }

    //ACTION FOR PHOTO DELETE
    public function removeAction() {

        //GET PHOTO ID AND ITEM
        $photo_id = (int) $this->_getParam('photo_id');
        $photo = Engine_Api::_()->getItem('sitereview_photo', $photo_id);

        //GET LISTING
        $sitereview = $photo->getParent('sitereview_listing');

        $isajax = (int) $this->_getParam('isajax');
        if ($isajax) {
            $db = Engine_Api::_()->getDbTable('photos', 'sitereview')->getAdapter();
            $db->beginTransaction();

            try {
                $photo->delete();
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }

        //MAKE FORM
        $this->view->form = $form = new Sitereview_Form_Photo_Delete();

        //CHECK METHOD
        if (!$this->getRequest()->isPost()) {
            $form->populate($photo->toArray());
            return;
        }

        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $db = Engine_Api::_()->getDbTable('photos', 'sitereview')->getAdapter();
        $db->beginTransaction();

        try {
            $photo->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forwardCustom('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Photo deleted')),
                    'layout' => 'default-simple',
                    'parentRedirect' => $sitereview->getHref(),
                    'closeSmoothbox' => true,
        ));
    }

    //ACTION FOR VIEWING THE PHOTO
    public function viewAction() {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

        //GET PHOTOS
        $this->view->image = $photo = Engine_Api::_()->core()->getSubject();

        //START PACKAGE LEVEL WORK
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $package_id = Engine_Api::_()->getItem('sitereview_listing', $photo->listing_id)->package_id;
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($package_id, "photo"))
                return $this->_forwardCustom('requireauth', 'error', 'core');
        }
        //END PACKAGE LEVEL WORK
        //GET SITEREVIEW DETAILS
        $this->view->sitereview = $photo->getCollection();

        //GET LISTING TYPE ID
        $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

        //GET SETTINGS
        $this->view->canEdit = 0; //photo->authorization()->isAllowed(null, "edit_listtype_$listingtype_id");
        if (empty($this->view->canEdit) && $photo->user_id == $viewer_id) {
            $this->view->canEdit = 1;
        }

        $this->view->canDelete = 0; //$photo->authorization()->isAllowed(null, "delete_listtype_$listingtype_id");
        if (empty($this->view->canDelete) && $photo->user_id == $viewer_id) {
            $this->view->canDelete = 1;
        }

        if (!$viewer || !$viewer_id || $photo->user_id != $viewer->getIdentity()) {
            $photo->view_count = new Zend_Db_Expr('view_count + 1');
            $photo->save();
        }

        $this->view->report = 1;
        $this->view->share = 1;
        $this->view->enablePinit = Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.photo.pinit', 0);
    }

}
