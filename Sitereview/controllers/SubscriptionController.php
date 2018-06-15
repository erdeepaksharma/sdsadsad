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
class Sitereview_SubscriptionController extends Core_Controller_Action_Standard {

  protected $_listingType;

  public function init()
  {

		if (0 != ($listing_id = (int) $this->_getParam('listing_id')) &&
						null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
			Engine_Api::_()->core()->setSubject($sitereview);
		}

    $listingtype_id = $sitereview->listingtype_id;
		Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
		$this->_listingType = Zend_Registry::isRegistered('listingtypeArray' . $listingtype_id) ? Zend_Registry::get('listingtypeArray' . $listingtype_id) : '';

		if (empty($this->_listingType) && empty($this->_listingType->subscription)) {
			return;
		}

		//AUTHORIZATION CHECK
		if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
			return;

  }

  public function addAction() {

    //Must have a viewer
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    //Get viewer and subject
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING SUBJECT
    $sitereview = Engine_Api::_()->core()->getSubject();
    $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);
    $listingtype_id = $this->_listingType->listingtype_id;
    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
   // $listingTitleSingular = strtolower($listingTypeTable->getListingTypeColumn($listingtype_id, 'title_singular'));
    $listingTitlePlural = strtolower($listingTypeTable->getListingTypeColumn($listingtype_id, 'title_plural'));
    //GET SUBSCRIPTION TABLE
    $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');

    //CHECK IF THEY ARE ALREADY SUBSCRIBED
    if( $subscriptionTable->checkSubscription($user, $viewer, $listingtype_id) ) {
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')
          ->_("You are already subscribed to this member's $listingTitlePlural.");

      return $this->_forward('success' ,'utility', 'core', array(
        'parentRefresh' => true,
        'messages' => array($this->view->message)
      ));
    }

    //MAKE FORM
    $this->view->form = $form = new Core_Form_Confirm(array(
      'title' => 'Subscribe?',
      'description' => "Would you like to subscribe to this member's $listingTitlePlural?",
      'class' => 'global_form_popup',
      'submitLabel' => 'Subscribe',
      'cancelHref' => 'javascript:parent.Smoothbox.close();',
    ));

    //CHECK METHOD
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    //CHECK VALID
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    //PROCESS
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $subscriptionTable->createSubscription($user, $viewer, $listingtype_id);
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    //SUCCESS
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')
        ->_("You are now subscribed to this member's $listingTitlePlural.");

    return $this->_forward('success' ,'utility', 'core', array(
      'parentRefresh' => true,
      'messages' => array($this->view->message)
    ));
  }

  public function removeAction()
  {
    //MUST HAVE A VIEWER
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    //GET VIEWER AND SUBJECT
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING SUBJECT
    $sitereview = Engine_Api::_()->core()->getSubject();
    $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);
    $listingtype_id = $this->_listingType->listingtype_id;
    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
    //$listingTitleSingular = strtolower($listingTypeTable->getListingTypeColumn($listingtype_id, 'title_singular'));
    $listingTitlePlural = strtolower($listingTypeTable->getListingTypeColumn($listingtype_id, 'title_plural'));

    //GET SUBSCRIPTION TABLE
    $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');

    //CHECK IF THEY ARE ALREADY NOT SUBSCRIBED
    if( !$subscriptionTable->checkSubscription($user, $viewer, $listingtype_id) ) {
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')
          ->_("You are already not subscribed to this member's $listingTitlePlural.");

      return $this->_forward('success' ,'utility', 'core', array(
        'parentRefresh' => true,
        'messages' => array($this->view->message)
      ));
    }

    //MAKE FORM
    $this->view->form = $form = new Core_Form_Confirm(array(
      'title' => 'Unsubscribe?',
      'description' => "Would you like to unsubscribe from this member's $listingTitlePlural?",
      'class' => 'global_form_popup',
      'submitLabel' => 'Unsubscribe',
      'cancelHref' => 'javascript:parent.Smoothbox.close();',
    ));

    //CHECK METHOD
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    //CHECK VALID
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    //PROCESS
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $subscriptionTable->removeSubscription($user, $viewer, $listingtype_id);
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    //SUCCESS
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')
        ->_("You are no longer subscribed to this member's $listingTitlePlural.");

    return $this->_forward('success' ,'utility', 'core', array(
      'parentRefresh' => true,
      'messages' => array($this->view->message)
    ));
  }

}