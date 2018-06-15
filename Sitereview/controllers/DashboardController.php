<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: DashboardController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_DashboardController extends Core_Controller_Action_Standard {

  //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
  public function init() {

    $listingtype_id = $this->_getParam('listingtype_id', null);
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
      //FOR UPDATE EXPIRATION
      if ((Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewpaidlisting.task.updateexpiredlistings') + 900) <= time()) {
        Engine_Api::_()->sitereviewpaidlisting()->updateExpiredListings($listingtype_id);
      }
    }
  }

  //ACTION FOR CONTACT INFORMATION
  public function contactAction() {

    //ONLY LOGGED IN USER CAN ADD OVERVIEW
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING ID AND OBJECT
    $listing_id = $this->_getParam('listing_id');

    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //GET LISTING TYPE ID
    $listingtype_id = $sitereview->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    Engine_Api::_()->core()->setSubject($sitereview);  
    if(!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->_helper->content
            ->setContentName("sitereview_dashboard_contact_listtype_$listingtype_id")
            //->setNoRender()
            ->setEnabled();
    
    }      
      
    if (empty($listingType->contact_detail)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $listingtype_id)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if (!Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "contact_listtype_$listingtype_id")) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //SELECTED TAB
    $this->view->TabActive = "contactdetails";

    //SET FORM
    $this->view->form = $form = new Sitereview_Form_Contactinfo();
    $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');

    //POPULATE FORM
    $row = $tableOtherinfo->getOtherinfo($listing_id);
    $value['email'] = $row->email;
    $value['phone'] = $row->phone;
    $value['website'] = $row->website;

    $form->populate($value);

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      //GET FORM VALUES
      $values = $form->getValues();


    $values['phone'] = $this->view->string()->stripTags($values['phone']);
    $values['email'] = $this->view->string()->stripTags($values['email']);
    $values['website'] = $this->view->string()->stripTags($values['website']);
      if (isset($values['email'])) {
        $email_id = $values['email'];

        //CHECK EMAIL VALIDATION
        $validator = new Zend_Validate_EmailAddress();
        $validator->getHostnameValidator()->setValidateTld(false);
        if (!empty($email_id)) {
          if (!$validator->isValid($email_id)) {
            $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter a valid email address.'));
            return;
          } else {
            $tableOtherinfo->update(array('email' => $email_id), array('listing_id = ?' => $listing_id));
          }
        } else {
          $tableOtherinfo->update(array('email' => $email_id), array('listing_id = ?' => $listing_id));
        }
      }

      //CHECK PHONE OPTION IS THERE OR NOT
      if (isset($values['phone'])) {
        $tableOtherinfo->update(array('phone' => $values['phone']), array('listing_id = ?' => $listing_id));
      }

      //CHECK WEBSITE OPTION IS THERE OR NOT
      if (isset($values['website'])) {
        $tableOtherinfo->update(array('website' => $values['website']), array('listing_id = ?' => $listing_id));
      }

      //SHOW SUCCESS MESSAGE
      $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
    }
  }

  //ACTION FOR CHANING THE PHOTO
  public function changePhotoAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET LISTING ID
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');

    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING ITEM
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    Engine_Api::_()->core()->setSubject($sitereview);
    //IF THERE IS NO SITEREVIEW.
    if (empty($sitereview)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET LISTING TYPE ID
    $listingtype_id = $sitereview->listingtype_id;

    //SELECTED TAB
    $this->view->TabActive = "profilepicture";

    //CAN EDIT OR NOT
    if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
      return;
    }

    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      //AUTHORIZATION CHECK
      $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");
      if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
        $allowed_upload_photo;
      }
      else
        $allowed_upload_photo = 0;
    }
    else
      $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");

    if (empty($allowed_upload_photo)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //AUTHORIZATION CHECK
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    if ($listingType->photo_type != 'listing') {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_ChangePhoto();

    //CHECK FORM VALIDATION
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //CHECK FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //UPLOAD PHOTO
    if ($form->Filedata->getValue() !== null) {
      //GET DB
      $db = Engine_Api::_()->getDbTable('listings', 'sitereview')->getAdapter();
      $db->beginTransaction();
      //PROCESS
      try {
        //SET PHOTO
        $sitereview->setPhoto($form->Filedata);
        $db->commit();
      } catch (Engine_Image_Adapter_Exception $e) {
        $db->rollBack();
        $form->addError(Zend_Registry::get('Zend_Translate')->_('The uploaded file is not supported or is corrupt.'));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    } else if ($form->getValue('coordinates') !== '') {
      $storage = Engine_Api::_()->storage();
      $iProfile = $storage->get($sitereview->photo_id, 'thumb.profile');
      $iSquare = $storage->get($sitereview->photo_id, 'thumb.icon');
      $pName = $iProfile->getStorageService()->temporary($iProfile);
      $iName = dirname($pName) . '/nis_' . basename($pName);
      list($x, $y, $w, $h) = explode(':', $form->getValue('coordinates'));
      $image = Engine_Image::factory();
      $image->open($pName)
              ->resample($x + .1, $y + .1, $w - .1, $h - .1, 48, 48)
              ->write($iName)
              ->destroy();
      $iSquare->store($iName);
      @unlink($iName);
    }

    $file_id = Engine_Api::_()->getDbtable('photos', 'sitereview')->getPhotoId($listing_id, $sitereview->photo_id);

    $photo = Engine_Api::_()->getItem('sitereview_photo', $file_id);

    if (!$sitereview->draft && time() >= strtotime($sitereview->creation_date)) {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sitereview, 'sitereview_change_photo_listtype_' . $listingtype_id);

      if ($action != null) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);
      }
    }

    if (!empty($sitereview->photo_id)) {
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

    return $this->_helper->redirector->gotoRoute(array('action' => 'change-photo', 'listing_id' => $listing_id), "sitereview_dashboard_listtype_$listingtype_id", true);
  }

  //ACTION FOR REMOVE THE PHOTO
  public function removePhotoAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET LISTING ID
    $listing_id = $this->_getParam('listing_id');

    //GET LISTING ITEM
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING TYPE ID
    $listingtype_id = $sitereview->listingtype_id;

    //CAN EDIT OR NOT
    if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
      return;
    }

    //GET FILE ID
    $file_id = Engine_Api::_()->getDbtable('photos', 'sitereview')->getPhotoId($listing_id, $sitereview->photo_id);

    //DELETE PHOTO
    if (!empty($file_id)) {
      $photo = Engine_Api::_()->getItem('sitereview_photo', $file_id);
      $photo->delete();
    }

    //SET PHOTO ID TO ZERO
    $sitereview->photo_id = 0;
    $sitereview->save();

    return $this->_helper->redirector->gotoRoute(array('action' => 'change-photo', 'listing_id' => $listing_id), "sitereview_dashboard_listtype_$listingtype_id", true);
  }

  //ACTION FOR CONTACT INFORMATION
  public function metaDetailAction() {

    //ONLY LOGGED IN USER CAN ADD OVERVIEW
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING ID AND OBJECT
    $listing_id = $this->_getParam('listing_id');

    
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    Engine_Api::_()->core()->setSubject($sitereview);    
    //GET LISTING TYPE ID
    $listingtype_id = $sitereview->listingtype_id;

    if(!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $this->_helper->content
            ->setContentName("sitereview_dashboard_metadetails_listtype_$listingtype_id")
            //->setNoRender()
            ->setEnabled();
    
    }
    
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    if (empty($listingType->metakeyword)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $listingtype_id)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if (!Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "metakeyword_listtype_$listingtype_id")) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //SELECTED TAB
    $this->view->TabActive = "metadetails";

    //SET FORM
    $this->view->form = $form = new Sitereview_Form_Metainfo();

    $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');

    //POPULATE FORM
    $value['keywords'] = $tableOtherinfo->getColumnValue($listing_id, 'keywords');

    $form->populate($value);

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      //GET FORM VALUES
      $values = $form->getValues();
      $tableOtherinfo->update(array('keywords' => $values['keywords']), array('listing_id = ?' => $listing_id));

      //SHOW SUCCESS MESSAGE
      $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
    }
  }

  public function downloadApplicationAction() {

    $file_id = Engine_Api::_()->getItem('sitereview_job', $this->_getParam('job_id', NULL))->file_id;

    $storageObeject = Engine_Api::_()->getItem('storage_file', $file_id);
    if ($storageObeject->service_id == 1)
      $path = (string) APPLICATION_PATH . '/' . Engine_Api::_()->getItem('storage_file', $file_id)->storage_path;
    else
      $path = $storageObeject->map();

    $flieName = $storageObeject->name;
    if (true) {
      @chmod($path, 0777);
      // zend's ob
      $isGZIPEnabled = false;
      if (ob_get_level()) {
        $isGZIPEnabled = true;
        @ob_end_clean();
      }

      header("Content-Disposition: attachment; filename=" . $flieName, true);
      header("Content-Transfer-Encoding: Binary", true);
      header("Content-Type: application/force-download", true);
      header("Content-Type: application/octet-stream", true);
      header("Content-Type: application/download", true);
      header("Content-Description: File Transfer", true);

      if (empty($isGZIPEnabled)) {
        header("Content-Length: " . filesize($path), true);
        flush();
      }

      $fp = fopen($path, "r");
      while (!feof($fp)) {
        echo fread($fp, 65536);
        if (empty($isGZIPEnabled))
          flush();
      }
      fclose($fp);
    }

    exit();
  }

  public function deleteApplicationAction() {

    //ONLY LOGGED IN USER 
    if (!$this->_helper->requireUser()->isValid())
      return;

    $id = $this->_getParam('id');
    $this->view->job_id = $id;

    if ($this->getRequest()->isPost()) {
      $jobItem = Engine_Api::_()->getItem('sitereview_job', $id);

      //IF TABLE OBJECT NOT EMPTY THEN DELETE ROW
      if (!empty($jobItem))
        $jobItem->delete();

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Application deleted successfully.'))
      ));
    }
  }

  public function multiDeleteApplicationAction() {

    //GET LISTING ID AND LISTING TYPE ID
    $listing_id = $this->_getParam('listing_id');
    $listingtype_id = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id)->listingtype_id;

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();

      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          Engine_Api::_()->getItem('sitereview_job', (int) $value)->delete();
        }
      }
    }
    //REDIRECTING
    return $this->_helper->redirector->gotoRoute(array('action' => 'show-application', 'listing_id' => $listing_id), "sitereview_dashboard_listtype_$listingtype_id", true);
  }

  public function applicationDetailAction() {

    //JOB ID OF APPLICATION
    $job_id = $this->_getParam('job_id');
    $this->view->applicationDetail = Engine_Api::_()->getItem('sitereview_job', $job_id);
  }


  //ACTION TO CHOOSE THE PROJECT FOR THE LISTING FOR THE DONATION
  public function chooseProjectAction() { 

       //ONLY LOGGED IN USER CAN EDIT THE STYLE
      if (!$this->_helper->requireUser()->isValid())
          return; 
      $this->view->TabActive = 'projects';

       //GET VIEWER
      $viewer = Engine_Api::_()->user()->getViewer(); 
      //GET LISTING ID AND OBJECT
      $listing_id = $this->_getParam('listing_id');
      $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      $this->view->parent_id = $sitereview->getIdentity();
      $this->view->parent_type = $sitereview->getType();
      $listingtype_id = $sitereview->listingtype_id; 

      //IF ADMIN HAVE SELECTED ANY PROJECT FOR LISTING PROFILE THAN DO NOT SHOW THE PROJECTS TAB OF DASHBAORD
      $adminSelectedProject = Engine_Api::_()->sitecrowdfunding()->adminSelectedProject("sitereview_index_view_listtype_".$listingtype_id);
      if(!empty($adminSelectedProject)) {
        return;
      }
      //SET sitereview SUBJECT
      Engine_Api::_()->core()->setSubject($sitereview);
      if (!$sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id")) {
          return $this->_forwardCustom('requireauth', 'error', 'core');
      }  
       //CAN EDIT OR NOT
      if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$listingtype_id")->isValid()) {
        return;
      } 
      $this->view->form = $form = new Sitecrowdfunding_Form_ChooseProjectContentModule(array('item' => $sitereview));

      //CHECK METHOD
      if (!$this->getRequest()->isPost()) {
          return;
      } 
      //FORM VALIDATION
      if (!$form->isValid($this->getRequest()->getPost())) {
          return;
      }
      $values = $form->getValues();
      foreach ($values as $key => $value) {
          if (Engine_Api::_()->getApi('settings', 'core')->hasSetting($key)) {
              Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
          }
          if (is_null($value)) {
              $value = "";
          }
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice($this->view->translate('Your changes have been saved successfully.'));
  }

}