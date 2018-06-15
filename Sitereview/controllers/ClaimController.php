<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ClaimController.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_ClaimController extends Core_Controller_Action_Standard {

  protected $_listingType;

  public function init() {

    //SET LISTING TYPE ID AND OBJECT
    $listingtype_id = $this->_getParam('listingtype_id', null);
    if ($listingtype_id != -1 && !empty($listingtype_id)) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    }
  }

  //ACTION FOR CLAIMING THE LISTING
  public function indexAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    $listingtype_id = $this->_getParam('listingtype_id', null);

    //GET LOGGED IN USER INFORMATION   
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    //GET LEVEL ID
    $level_id = 0;
    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $authorization = Engine_Api::_()->getItemTable('authorization_level')->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
      if (!empty($authorization))
        $level_id = $authorization->level_id;
    }

    //CHECK USER HAVE TO ALLOW CLAIM OR NOT
    $allow_claim = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$listingtype_id");
    if (empty($this->_listingType->claimlink) || empty($allow_claim))
      return $this->_forward('requireauth', 'error', 'core');

    $this->_helper->content
            ->setContentName("sitereview_claim_index_listtype_$listingtype_id")
            ->setNoRender()
            ->setEnabled();
  }

  //ACTION FOR SHOW LISTINGS ON WHICH I HAVE CLAIMED
  public function myListingsAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    $listingtype_id = $this->_listingType->listingtype_id;

    //CHECK CLAIM IS ENABLED OR NOT
    if (empty($this->_listingType->claimlink)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET LOGGED IN USER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    //GET LEVEL ID
    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $authorization = Engine_Api::_()->getItemTable('authorization_level')->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
      if (!empty($authorization))
        $level_id = $authorization->level_id;
    }

    //CHECK THAT MEMBER HAS ALLOED FOR CLAIM OR NOT
    $canClaim = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$listingtype_id");
    if (empty($canClaim)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    $this->_helper->content
            ->setContentName("sitereview_claim_my-listings_listtype_$listingtype_id")
            ->setNoRender()
            ->setEnabled();
  }

  //ACTION FOR CLAIM A LISTING FROM THE LISTING PROFILE PAGE
  public function claimListingAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET LOGGED IN USER INFORMATION   
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $listingtype_id = $this->_listingType->listingtype_id;

    //GET LEVEL ID
    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $authorization = Engine_Api::_()->getItemTable('authorization_level')->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
      if (!empty($authorization))
        $level_id = $authorization->level_id;
    }

    $this->_helper->layout->setLayout('default-simple');

    //GET LISTING ID  
    $listing_id = $this->_getParam('listing_id', null);
    $listingtypeArray = $this->_listingType;

    //SET PARAMS
    $params = array();
    $params['listing_id'] = $listing_id;
    $params['viewer_id'] = $viewer_id;
    $inforow = Engine_Api::_()->getDbtable('claims', 'sitereview')->getClaimStatus($params);

    $this->view->status = 0;
    if (!empty($inforow)) {
      $this->view->status = $inforow->status;
    }

    //GET ADMIN EMAIL
    $coreApiSettings = Engine_Api::_()->getApi('settings', 'core');
    $defaultEmail = $coreApiSettings->getSetting('core.mail.from', "email@domain.com");
    $adminEmail = $coreApiSettings->getSetting('core.mail.contact', $defaultEmail);
    if (!$adminEmail)
      $adminEmail = $defaultEmail;

    //CHECK STATUS
    if ($this->view->status == 2) {
      echo '<div class="global_form" style="margin:15px 0 0 15px;"><div><div><h3>' . $this->view->translate("Alert!") . '</h3>';
      echo '<div class="form-elements" style="margin-top:10px;"><div class="form-wrapper" style="margin-bottom:10px;">' . $this->view->translate("You have already send a request to claim for this listing which has been declined by the site admin.") . '</div>';
      echo '<div class="form-wrapper"><button onclick="parent.Smoothbox.close()">' . $this->view->translate("Close") . '</button></div></div></div></div></div>';
    }

    $this->view->claimoption = $claimoption = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$listingtype_id");

    //FETCH
    $params = array();
    $this->view->userclaim = $userclaim = 0;
    $params['listing_id'] = $listing_id;
    $params['listingtype_id'] = $listingtype_id;
    $params['limit'] = 1;
    $listingclaiminfo = Engine_Api::_()->getDbtable('listings', 'sitereview')->getSuggestClaimListing($params);
    $userClaimValue = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($listing_id, 'userclaim');

    if (!$claimoption || !$userClaimValue || empty($listingtypeArray->claimlink)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if (isset($userClaimValue)) {
      $this->view->userclaim = $userclaim = $userClaimValue;
    }

    if ($inforow['status'] == 3 || $inforow['status'] == 4) {
      echo '<div class="global_form" style="margin:15px 0 0 15px;"><div><div><h3>' . $this->view->translate("Alert!") . '</h3>';
      echo '<div class="form-elements" style="margin-top:10px;"><div class="form-wrapper" style="margin-bottom:10px;">' . $this->view->translate("You have already filed a claim for this listing: \"%s\", which is either on hold or is awaiting action by administration.", Engine_Api::_()->getItem('sitereview_listing', $listing_id)->title) . '</div>';
      echo '<div class="form-wrapper"><button onclick="parent.Smoothbox.close()">' . $this->view->translate("Close") . '</button></div></div></div></div></div>';
    }

    if (!$inforow['status'] && $claimoption && $userclaim) {
      //GET FORM 
      $this->view->form = $form = new Sitereview_Form_Claim_Claimlisting();

      //POPULATE FORM
      if (!empty($viewer_id)) {
        $value['email'] = $viewer->email;
        $value['nickname'] = $viewer->displayname;
        $form->populate($value);
      }

      if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        //GET FORM VALUES
        $values = $form->getValues();

        //GET EMAIL
        $email = $values['email'];

        //CHECK EMAIL VALIDATION
        $validator = new Zend_Validate_EmailAddress();
        $validator->getHostnameValidator()->setValidateTld(false);
        if (!$validator->isValid($email)) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter a valid email address.'));
          return;
        }

        //GET CLAIM TABLE
        $tableClaim = Engine_Api::_()->getDbTable('claims', 'sitereview');
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
          //SAVE VALUES
          //GET SITEREVIEW ITEM
          $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
          $listing_type = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypeColumn($sitereview->listingtype_id, 'title_singular');

          $httpVar = _ENGINE_SSL ? 'https://' : 'http://';
          $list_baseurl = $httpVar . $_SERVER['HTTP_HOST'] .
                  Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$sitereview->listingtype_id", true);

          //MAKING LISTING TITLE LINK
          $list_title_link = '<a href="' . $list_baseurl . '"  >' . $sitereview->title . ' </a>';

          if ($listingtypeArray->claim_email) {
            //SEND CLAIM OWNER EMAIL
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($adminEmail, 'SITEREVIEW_'.$listing_type.'_CLAIMOWNER_EMAIL', array(
                'list_title' => $sitereview->title,
                'list_title_with_link' => $list_title_link,
                'object_link' => $list_baseurl,
                'site_contact_us_link' => $httpVar . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/help/contact',
                'email' => $defaultEmail,
                'queue' => false
            ));
          }

          $row = $tableClaim->createRow();
          $row->listing_id = $listing_id;
          $row->user_id = $viewer_id;
          $row->about = $values['about'];
          $row->nickname = $values['nickname'];
          $row->email = $email;
          $row->contactno = $values['contactno'];
          $row->usercomments = $values['usercomments'];
          $row->status = 3;
          $row->save();

          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefreshTime' => '60',
            'parentRefresh' => 'true',
            'format' => 'smoothbox',
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your request has been send successfully. You will now receive an email confirming Admin approval of your request.'))
        ));
      }
    }
  }

  //ACTION FOR GETTING LISTINGS ON WHICH USER CAN CLAIM
  public function getListingsAction() {

    //FETCH
    $paramss = array();
    $paramss['listingtype_id'] = $this->_listingType->listingtype_id;
    $paramss['title'] = $this->_getParam('text');
    $paramss['viewer_id'] = Engine_Api::_()->user()->getViewer()->getIdentity();
    $paramss['limit'] = $this->_getParam('limit', 40);
    $paramss['orderby'] = 'title ASC';
    $usersitereviews = Engine_Api::_()->getDbtable('listings', 'sitereview')->getSuggestClaimListing($paramss);
    $data = array();
    $mode = $this->_getParam('struct');
    if ($mode == 'text') {
      foreach ($usersitereviews as $usersitereview) {
        $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
        $data[] = array(
            'id' => $usersitereview->listing_id,
            'label' => $usersitereview->title,
            'photo' => $content_photo
        );
      }
    } else {
      foreach ($usersitereviews as $usersitereview) {
        $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
        $data[] = array(
            'id' => $usersitereview->listing_id,
            'label' => $usersitereview->title,
            'photo' => $content_photo
        );
      }
    }
    return $this->_helper->json($data);
  }

  //ACTION FOR DELETING THE CLAIM REQUEST
  public function deleteAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //SET LAYOUT  
    $this->_helper->layout->setLayout('default-simple');

    //GET CLAIM ID
    $claim_id = $this->_getParam('claim_id', 'null');

    //GET CLAIM ITEM
    $claim = Engine_Api::_()->getItem('sitereview_claim', $claim_id);
    if ($claim->user_id != Engine_Api::_()->user()->getViewer()->getIdentity()) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Claim_Deleteclaim();

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $claim->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRefresh' => true,
          'format' => 'smoothbox',
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully deleted your claim request.'))
      ));
    }
  }

  //ACTION FOR SHOWING THE TREM OF THE CLAIM
  public function termsAction() {

    $this->_helper->layout->setLayout('default-simple');
  }

}