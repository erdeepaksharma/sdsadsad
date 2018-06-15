<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 2014-02-16 5:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_CreateClaimController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
   
    //LISTING ID    
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);

    $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 0);
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_plural_lc = lcfirst($listingtypeArray->title_plural);
    
    $front = Zend_Controller_Front::getInstance();

    //GET LOGGED IN USER INFORMATION   
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET LEVEL ID
    $level_id = 0;
    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $authorization = Engine_Api::_()->getItemTable('authorization_level')->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
      if (!empty($authorization))
        $level_id = $authorization->level_id;
    }

    //FETCH
    $params = array();
    $params['listingtype_id'] = $listingtype_id;
    $params['viewer_id'] = $viewer_id;
    $params['limit'] = $this->_getParam('limit', 40);
    $usersitereviews = Engine_Api::_()->getDbtable('listings', 'sitereview')->getSuggestClaimListing($params);

    //IF THERE IS NO LISTINGS THEN SHOWING THE TIP THERE IS NO LISTINGS
    if (!empty($usersitereviews)) {
      $usersitereview = $usersitereviews->toarray();
      if (empty($usersitereview))
        $this->view->showtip = 1;
    }

    //FORM 
    $this->view->form = $form = new Sitereview_Form_Claim_Createclaim();

    //POPULATE FORM
    if (!empty($viewer_id)) {
      $value['email'] = $viewer->email;
      $value['nickname'] = $viewer->displayname;
      $form->populate($value);
    }

    //CHECK FORM VALIDAION
    if ($front->getRequest()->isPost() && $form->isValid($front->getRequest()->getPost())) {
      if ($listing_id == 0) {
        $error = $this->view->translate("This is an invalid listing name. Please select a valid listing name from the autosuggest given below.");
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_($error);
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }
      $values = $form->getValues();

      //GET SITEREVIEW ITEM
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      if (!empty($sitereview)) {
        $items = array();
        $items['listing_id'] = $listing_id;
        $items['viewer_id'] = $viewer_id;
        $claimlistings = Engine_Api::_()->getDbtable('claims', 'sitereview')->getClaimStatus($items);
        if (!empty($claimlistings)) {
          if ($claimlistings->status == 3 || $claimlistings->status == 4) {
            $error = $this->view->translate("You have already filed a claim for the listing: \"%s\", which is either on hold or is awaiting action by administration.", $sitereview->title);
            $this->view->status = false;
            $form->getElement("listing_id")->setValue("0");
            $error = Zend_Registry::get('Zend_Translate')->_($error);
            $form->getDecorator('errors')->setOption('escape', false);
            $form->addError($error);
            return;
          } elseif ($claimlistings->status == 2) {
            $error = $this->view->translate("You have already filed a claim for the listing: \"%s\", which has been declined by the site admin.", $sitereview->title);
            $this->view->status = false;
            $error = Zend_Registry::get('Zend_Translate')->_($error);
            $form->getDecorator('errors')->setOption('escape', false);
            $form->getElement("listing_id")->setValue("0");
            $form->addError($error);
            return;
          }
        }
      }
      //GET EMAIl
      $email = $values['email'];

      //CHECK EMAIL VALIDATION
      $validator = new Zend_Validate_EmailAddress();
      $validator->getHostnameValidator()->setValidateTld(false);
      if (!$validator->isValid($email)) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter a valid email address.'));
        return;
      }

      //GET ADMIN EMAIL
      $coreApiSettings = Engine_Api::_()->getApi('settings', 'core');
      $adminEmail = $coreApiSettings->getSetting('core.mail.contact', $coreApiSettings->getSetting('core.mail.from', "email@domain.com"));
      if (!$adminEmail)
        $adminEmail = $coreApiSettings->getSetting('core.mail.from', "email@domain.com");

      //GET CLAIM TABLE
      $tableClaim = Engine_Api::_()->getDbTable('claims', 'sitereview');
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
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
              'email' => $coreApiSettings->getSetting('core.mail.from', "email@domain.com"),
              'queue' => true
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
      $this->view->successmessage = 1;
    }
  }

}