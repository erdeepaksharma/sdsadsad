<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: WishlistController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_WishlistController extends Seaocore_Controller_Action_Standard {

  //COMMON FUNCTION WHICH CALL AUTOMATICALLY BEFORE EVERY ACTION OF THIS CONTROLLER
  public function init() {

    $listingtype_id = $this->_getParam('listingtype_id');
    if (!empty($listingtype_id)) {
      //AUTHORIZATION CHECK
      if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
        return;
    }

    $listing_id = $this->_getParam('listing_id');
    if (!empty($listing_id)) {

      //GET LISTING TYPE ID
      $listingType = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      if (!empty($listingType)) {
        $listingtype_id = $listingType->listingtype_id;

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
          return;
      }
    }

    $sitereviewWishlistView = Zend_Registry::isRegistered('sitereviewWishlistView') ? Zend_Registry::get('sitereviewWishlistView') : null;
    if (empty($sitereviewWishlistView))
      $this->_setParam('listing_id', 0);

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_wishlist', null, "view")->isValid())
      return;

    //SET LISTING TYPE
    Engine_Api::_()->sitereview()->setListingTypeInRegistry(-1);
  }

  //NONE USER SPECIFIC METHODS
  public function browseAction() {

    //DO NOT RENDER IF FAVOURITE FUNCTIONALITY IS ENABLED  
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    //GET SEARCH TEXT
    if ($this->_getParam('search', null)) {
      $metaParams['search'] = $this->_getParam('search', null);

      //SET META KEYWORDS
      Engine_Api::_()->sitereview()->setMetaKeywords($metaParams);
    }

    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      //Zend_Registry::set('setFixedCreationForm', true);
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
      Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Browse Wishlists'));
    }

    $this->_helper->content
            ->setNoRender()
            ->setEnabled();
  }

  //NONE USER SPECIFIC METHODS
  public function profileAction() {

    //GET WISHLIST ID AND OBJECT
    $wishlist_id = $this->_getParam('wishlist_id');
    $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    //CHECK AUTHENTICATION
    if (!Engine_Api::_()->authorization()->isAllowed($wishlist, null, "view")) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET VIEWER ID
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    //DO NOT RENDER IF FAVOURITE FUNCTIONALITY IS ENABLED      
    $recentWishlistId = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->recentWishlistId($viewer_id);
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0) && (($recentWishlistId != $wishlist_id && $level_id != 1) || ($level_id != 1 && $wishlist->owner_id != $viewer_id))) {
      return $this->_forwardCustom('notfound', 'error', 'core');
    }

    //SET SITEREVIEW SUBJECT
    Engine_Api::_()->core()->setSubject($wishlist);

    $params['wishlist'] = 'Wishlists';
    Engine_Api::_()->sitereview()->setMetaTitles($params);

    $params['wishlist_creator_name'] = $wishlist->getOwner()->getTitle();
    Engine_Api::_()->sitereview()->setMetaKeywords($params);

    //INCREASE VIEW COUNT IF VIEWER IS NOT OWNER
    if (!$wishlist->getOwner()->isSelf($viewer)) {
      $wishlist->view_count++;
      $wishlist->save();
    }

    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      //Zend_Registry::set('setFixedCreationForm', true);
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
      Zend_Registry::set('setFixedCreationHeaderTitle', Engine_Api::_()->seaocore()->seaocoreTruncateText($wishlist->getTitle(), 20));
    }
    $this->_helper->content
//            ->setContentName($pageObject->page_id)
            ->setNoRender()
            ->setEnabled();
  }

  //ACTION FOR ADDING THE ITEM IN WISHLIST
  public function addAction() {

    $param = $this->_getParam('param');
    $request_url = $this->_getParam('request_url');
    $return_url = $this->_getParam('return_url');
    $front = Zend_Controller_Front::getInstance();
    $base_url = $front->getBaseUrl();

    // CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid()) {
      if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        return;
      }
      $host = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://";
      if ($base_url == '') {
        $URL_Home = $host . $_SERVER['HTTP_HOST'] . '/login';
      } else {
        if ($request_url)
          $URL_Home = $host . $_SERVER['HTTP_HOST'] . '/' . $request_url . '/login';
        else
          $URL_Home = $host . $_SERVER['HTTP_HOST'] . $base_url . '/login';
      }
      if (empty($param)) {
        return $this->_helper->redirector->gotoUrl($URL_Home, array('prependBase' => false));
      } else {
        return $this->_helper->redirector->gotoUrl($URL_Home . '?return_url=' . urlencode($return_url), array('prependBase' => false));
      }
    }

    //SET LAYOUT
    //$this->_helper->layout->setLayout('default-simple');

    //ONLY LOGGED IN USER CAN CREATE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET PAGE ID AND CHECK PAGE ID VALIDATION
    $listing_id = $this->_getParam('listing_id');
    if (empty($listing_id)) {
      return $this->_forward('notfound', 'error', 'core');
    }

    //GET VIEWER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
      //GET USER WISHLISTS
      $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
      $wishlistDatas = $wishlistTable->getUserWishlists($viewer_id);
      $this->view->wishlistDatasCount = $wishlistDataCount = Count($wishlistDatas);

      //LISING WILL ADD IF YOU CAN VIEW THIS
      $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('listing_id'));
      $listingtype_id = $sitereview->listingtype_id;
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

      //START PACKAGE WORK
      if (Engine_Api::_()->sitereview()->hasPackageEnable($sitereview->listingtype_id)) {
        if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "wishlist", $sitereview->listingtype_id))
          return $this->_forward('notfound', 'error', 'core');
      }
      //END PACKAGE WORK

      $this->view->listing_singular_uc = ucfirst($listingType->title_singular);
      $this->view->can_add = 1;
      if (!$this->_helper->requireAuth()->setAuthParams($sitereview, null, "view_listtype_$listingtype_id")->isValid()) {
        $this->view->can_add = 0;
      }

      //AUTHORIZATION CHECK
      if (empty($listingType->wishlist) || !empty($sitereview->draft) || empty($sitereview->search) || empty($sitereview->approved)) {
        $this->view->can_add = 0;
      }

      //FORM GENERATION
      $this->view->form = $form = new Sitereview_Form_Wishlist_Add();
      if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
        $this->_helper->layout->setLayout('default');
        $this->_setParam('contentType', 'page');
        Zend_Registry::set('setFixedCreationForm', true);
        Zend_Registry::set('setFixedCreationFormBack', 'Back');
        Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_($form->getTitle()));
        Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Save'));
        $this->view->form->setAttrib('id', 'addToWishlistFormSR');
        Zend_Registry::set('setFixedCreationFormId', '#addToWishlistFormSR');
        $this->view->form->removeElement('submit');
        $this->view->form->removeElement('cancel');
        $form->setTitle('');
      }

      $this->view->success = 0;

      //FORM VALIDATION
      if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
        return;
      }

      //GET FORM VALUES
      $values = $form->getValues();

      //CHECK FOR NEW ADDED WISHLIST TITLE
      if (!empty($values['body']) && empty($values['title'])) {

        $error = $this->view->translate('Please enter the wishlist title otherwise remove the wishlist note.');
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_($error);
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      //GET WISHLIST PAGE TABLE
      $wishlistListingTable = Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview');

      $wishlistOldIds = array();

      //GET FOLLOW TABLE
      $followTable = Engine_Api::_()->getDbTable('follows', 'seaocore');

      //GET NOTIFY API
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');

      //WORK ON PREVIOUSLY CREATED WISHLIST
      if (!empty($wishlistDatas)) {

        foreach ($wishlistDatas as $wishlistData) {
          $key_name = 'wishlist_' . $wishlistData->wishlist_id;
          if (isset($values[$key_name]) && !empty($values[$key_name])) {

            $wishlistListingTable->insert(array(
                'wishlist_id' => $wishlistData->wishlist_id,
                'listing_id' => $listing_id,
            ));

            //WISHLIST COVER PHOTO
            $wishlistTable->update(
                    array(
                'listing_id' => $listing_id,
                    ), array(
                'wishlist_id = ?' => $wishlistData->wishlist_id,
                'listing_id = ?' => 0
                    )
            );

            //GET FOLLOWERS
            $followers = $followTable->getFollowers('sitereview_wishlist', $wishlistData->wishlist_id, $viewer_id);
            foreach ($followers as $follower) {
              $followerObject = Engine_Api::_()->getItem('user', $follower->poster_id);
              $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlistData->wishlist_id);
              $http = _ENGINE_SSL ? 'https://' : 'http://';
              $wishlist_link = '<a href="' . $http . $_SERVER['HTTP_HOST'] . '/' . $wishlist->getHref() . '">' . $wishlist->getTitle() . '</a>';
              $notifyApi->addNotification($followerObject, $viewer, $sitereview, 'sitereview_wishlist_followers', array("wishlist" => $wishlist_link));
            }

            if (time() >= strtotime($sitereview->creation_date)) {
              $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
              $action = $activityApi->addActivity($viewer, $wishlistData, "sitereview_wishlist_add_listing_listtype_" . $sitereview->listingtype_id, '', array('listing' => array($sitereview->getType(), $sitereview->getIdentity())));
              if ($action)
                $activityApi->attachActivity($action, $sitereview);
            }
          }

          $in_key_name = 'inWishlist_' . $wishlistData->wishlist_id;
          if (isset($values[$in_key_name]) && empty($values[$in_key_name])) {
            $wishlistOldIds[$wishlistData->wishlist_id] = $wishlistData;
            $wishlistListingTable->delete(array('wishlist_id = ?' => $wishlistData->wishlist_id, 'listing_id = ?' => $listing_id));

            //DELETE ACTIVITY FEED
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            $actionTableName = $actionTable->info('name');

            $action_id = $actionTable->select()
                    ->setIntegrityCheck(false)
                    ->from($actionTableName, 'action_id')
                    ->joinInner('engine4_activity_attachments', "engine4_activity_attachments.action_id = $actionTableName.action_id", array())
                    ->where('engine4_activity_attachments.id = ?', $listing_id)
                    ->where($actionTableName . '.type = ?', "sitereview_wishlist_add_listing_listtype_$sitereview->listingtype_id")
                    ->where($actionTableName . '.subject_type = ?', 'user')
                    ->where($actionTableName . '.object_type = ?', 'sitereview_wishlist')
                    ->where($actionTableName . '.object_id = ?', $wishlistData->wishlist_id)
                    ->query()
                    ->fetchColumn();

            if (!empty($action_id)) {
              $activity = Engine_Api::_()->getItem('activity_action', $action_id);
              if (!empty($activity)) {
                $activity->delete();
              }
            }
          }
        }
      }

      if (!empty($values['title'])) {

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {

          //CREATE WISHLIST
          $wishlist = $wishlistTable->createRow();
          $wishlist->setFromArray($values);
          $wishlist->owner_id = $viewer_id;
          $wishlist->listing_id = $listing_id; //WISHLIST COVER PHOTO
          $wishlist->save();

          //PRIVACY WORK
          $auth = Engine_Api::_()->authorization()->context;
          $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

          if (empty($values['auth_view'])) {
            $values['auth_view'] = 'everyone';
          }

          $viewMax = array_search($values['auth_view'], $roles);
          foreach ($roles as $i => $role) {
            $auth->setAllowed($wishlist, $role, 'view', ($i <= $viewMax));
          }

          $db->commit();
        } catch (Exception $e) {
          $db->rollback();
          throw $e;
        }

        $wishlistListingTable->insert(array(
            'wishlist_id' => $wishlist->wishlist_id,
            'listing_id' => $listing_id,
            'date' => new Zend_Db_Expr('NOW()')
        ));

        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activityApi->addActivity($viewer, $wishlist, "sitereview_wishlist_add_listing_listtype_" . $sitereview->listingtype_id, '', array('listing' => array($sitereview->getType(), $sitereview->getIdentity()),
        ));
        if ($action)
          $activityApi->attachActivity($action, $sitereview);
      }

      $this->view->wishlistOldDatas = $wishlistOldIds;
      $this->view->wishlistNewDatas = $wishlistListingTable->pageWishlists($listing_id, $viewer_id);
      $this->view->success = 1;
      if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
       // $this->view->notSuccessMessage = true;
        if (!Engine_Api::_()->seaocore()->isSitemobileApp())
        return $this->_forwardCustom('success', 'utility', 'core', array(
                    //'smoothboxClose' => true,
                    'parentRedirect' => $sitereview->getHref(),
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Entry has been added to wishlist successfully.'))
        ));
        else
          return $this->_forward('success', 'utility', 'core', array(
              'parentRedirect' => $sitereview->getHref(),
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Entry has been added to wishlist successfully.'))
          ));
                
      }
    } else {
      $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
      $wishlist_id = $wishlistTable->recentWishlistId($viewer_id, $listing_id);
      $action = $this->_getParam('perform', 'add');
      Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview')->performWishlistMapAction($wishlist_id, $listing_id, $action);
      echo json_encode(array('success' => 'true'));
      exit();
    }
  }

  //ACTION FOR MESSAGING THE LISTING OWNER
  public function messageOwnerAction() {

    //LOGGED IN USER CAN SEND THE MESSAGE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET LISTING ID AND OBJECT
    $wishlist_id = $this->_getParam("wishlist_id");
    $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    //OWNER CANT SEND A MESSAGE TO HIMSELF
    if ($viewer_id == $wishlist->owner_id) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //MAKE FORM
    $this->view->form = $form = new Messages_Form_Compose();
    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      Zend_Registry::set('setFixedCreationForm', true);
       $this->_helper->layout->setLayout('default');
        $this->_setParam('contentType', 'page');
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
      Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Compose Message'));
      Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Send'));
      $this->view->form->setAttrib('id', 'messages_compose');
      Zend_Registry::set('setFixedCreationFormId', '#messages_compose');
      $this->view->form->removeElement('submit');
      $form->setTitle('To: ' . $wishlist->getOwner()->getTitle());
      $form->toValues->setLabel("");
    }
    $form->setDescription('Create your message with the form given below. (This message will be sent to the owner of this Wishlist.)');
    $form->removeElement('to');
    $form->toValues->setValue("$wishlist->owner_id");

    //CHECK METHOD/DATA
    if (!$this->getRequest()->isPost()) {
      return;
    }

    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();

    try {
      $values = $this->getRequest()->getPost();

      $form->populate($values);

      $is_error = 0;
      if (empty($values['title'])) {
        $is_error = 1;
      }

      //SENDING MESSAGE
      if ($is_error == 1) {
        $error = $this->view->translate('Subject is required field !');
        $error = Zend_Registry::get('Zend_Translate')->_($error);

        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      $recipients = preg_split('/[,. ]+/', $values['toValues']);

      //LIMIT RECIPIENTS
      $recipients = array_slice($recipients, 0, 1000);

      //CLEAN THE RECIPIENTS FOR REPEATING IDS
      $recipients = array_unique($recipients);

      //GET USER
      $user = Engine_Api::_()->getItem('user', $wishlist->owner_id);

      $wishlist_title = $wishlist->getTitle();
      $wishlist_title_with_link = '<a href = http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('wishlist_id' => $wishlist_id, 'slug' => $wishlist->getSlug()), "sitereview_wishlist_view") . ">$wishlist_title</a>";

      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $recipients, $values['title'], $values['body'] . "<br><br>" . $this->view->translate('This message corresponds to the Wishlist: ') . $wishlist_title_with_link);

      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');

      //INCREMENT MESSAGE COUNTER
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

      $db->commit();
      if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
        return $this->_forward('success', 'utility', 'core', array(
                    'parentRedirect' => $wishlist->getHref(),
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.'))
        ));
      }
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => true,
                  'parentRefresh' => true,
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.'))
      ));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  //ACTION FOR REMOVE ITEM FROM THIS WISHLIST
  public function removeAction() {

    //SET LAYOUT
    $this->_helper->layout->setLayout('default-simple');

    //GET WISHLIST AND PAGE ID 
    $this->view->wishlist_id = $wishlist_id = $this->_getParam('wishlist_id');
    $listing_id = $this->_getParam('listing_id');
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    $favouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //DELETE FROM DATABASE
        Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview')->delete(array('wishlist_id = ?' => $wishlist_id, 'listing_id = ?' => $listing_id));

        //DELETE ACTIVITY FEED
        $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
        $actionTableName = $actionTable->info('name');

        $action_id = $actionTable->select()
                ->setIntegrityCheck(false)
                ->from($actionTableName, 'action_id')
                ->joinInner('engine4_activity_attachments', "engine4_activity_attachments.action_id = $actionTableName.action_id", array())
                ->where('engine4_activity_attachments.id = ?', $listing_id)
                ->where($actionTableName . '.type = ?', "sitereview_wishlist_add_listing_listtype_$sitereview->listingtype_id")
                ->where($actionTableName . '.subject_type = ?', 'user')
                ->where($actionTableName . '.object_type = ?', 'sitereview_wishlist')
                ->where($actionTableName . '.object_id = ?', $wishlist_id)
                ->query()
                ->fetchColumn();

        if (!empty($action_id)) {
          $activity = Engine_Api::_()->getItem('activity_action', $action_id);
          if (!empty($activity)) {
            $activity->delete();
          }
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      if ($favouriteSetting) {
        $message = 'This entry has been removed successfully from your favourites.';
      } else {
        $message = 'This entry has been removed successfully from this wishlist!';
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_($message))
      ));
    }
    $this->renderScript('wishlist/remove.tpl');
  }

  //ACTION FOR TELL TO THE FRIEND FOR THIS WISHLIST
  public function tellAFriendAction() {

    //DEFAULT LAYOUT
    $this->_helper->layout->setLayout('default-simple');

    //GET VIEWER DETAIL
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewr_id = $viewer->getIdentity();

    //GET WISHLIST ID AND WISHLIST OBJECT
    $wishlist_id = $this->_getParam('wishlist_id', $this->_getParam('wishlist_id', null));
    $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    //FORM GENERATION
    $this->view->form = $form = new Sitereview_Form_Wishlist_TellAFriend();
    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
        $this->_helper->layout->setLayout('default');
        $this->_setParam('contentType', 'page');
        Zend_Registry::set('setFixedCreationForm', true);
        Zend_Registry::set('setFixedCreationFormBack', 'Back');
        Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_($form->getTitle()));
        Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Send'));
        $this->view->form->setAttrib('id', 'tellAFriendFromWishlist');
        Zend_Registry::set('setFixedCreationFormId', '#tellAFriendFromWishlist');
        $this->view->form->removeElement('wishlist_send');
        $this->view->form->removeElement('cancel');
        $form->setTitle('');
      }
    if (!empty($viewr_id)) {
      $value['wishlist_sender_email'] = $viewer->email;
      $value['wishlist_sender_name'] = $viewer->displayname;
      $form->populate($value);
    }

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET FORM VALUES
      $values = $form->getValues();

      //EMAIL IDS
      $reciver_ids = explode(',', $values['wishlist_reciver_emails']);

      if (!empty($values['wishlist_send_me'])) {
        $reciver_ids[] = $values['wishlist_sender_email'];
      }

      $reciver_ids = array_unique($reciver_ids);

      $sender_email = $values['wishlist_sender_email'];

      //CHECK VALID EMAIL ID FORMITE
      $validator = new Zend_Validate_EmailAddress();
      $validator->getHostnameValidator()->setValidateTld(false);

      if (!$validator->isValid($sender_email)) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid sender email address value'));
        return;
      }
      foreach ($reciver_ids as $reciver_id) {
        $reciver_id = trim($reciver_id, ' ');
        if (!$validator->isValid($reciver_id)) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter correct email address of the receiver(s).'));
          return;
        }
      }

      //GET EMAIL DETAILS
      $sender = $values['wishlist_sender_name'];
      $message = $values['wishlist_message'];
      $params['wishlist_id'] = $wishlist_id;
      $params['slug'] = $wishlist->getSlug();
      $heading = ucfirst($wishlist->getTitle());

      Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SITEREVIEW_WISHLIST_TELLAFRIEND_EMAIL', array(
          'host' => $_SERVER['HTTP_HOST'],
          'sender_name' => $sender,
          'wishlist_title' => $heading,
          'message' => '<div>' . $message . '</div>',
          'object_link' => $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble($params, 'sitereview_wishlist_view', true),
          'sender_email' => $sender_email,
          'queue' => true
      ));

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRefresh' => false,
          'format' => 'smoothbox',
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message to your friend has been sent successfully.'))
      ));
    }
  }

  //ACTION FOR CREATING THE WISHLIST
  public function createAction() {



    //ONLY LOGGED IN USER CAN CREATE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    //FORM GENERATION
    $this->view->form = $form = new Sitereview_Form_Wishlist_Create();
    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      $this->_helper->layout->setLayout('default');
      $this->_setParam('contentType', 'page');
      Zend_Registry::set('setFixedCreationForm', true);
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
      Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Create Wishlist'));
      Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Create'));
      $this->view->form->setAttrib('id', 'form-upload-wishlist');
      Zend_Registry::set('setFixedCreationFormId', '#form-upload-wishlist');
      $this->view->form->removeElement('submit');
      $this->view->form->removeElement('cancel');
      $form->setTitle('');
    } else {
      //SET LAYOUT
      $this->_helper->layout->setLayout('default-simple');
    }
    if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //GET WISHLIST TABLE
    $wishlistTable = Engine_Api::_()->getItemTable('sitereview_wishlist');
    $db = $wishlistTable->getAdapter();
    $db->beginTransaction();

    try {

      //GET FORM VALUES
      $values = $form->getValues();
      $values['owner_id'] = $viewer->getIdentity();

      //CREATE WISHLIST
      $wishlist = $wishlistTable->createRow();
      $wishlist->setFromArray($values);
      $wishlist->save();

      //PRIVACY WORK
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if (empty($values['auth_view'])) {
        $values['auth_view'] = 'everyone';
      }
      $viewMax = array_search($values['auth_view'], $roles);

      $values['auth_comment'] = 'everyone';
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach ($roles as $i => $role) {
        $auth->setAllowed($wishlist, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($wishlist, $role, 'comment', ($i <= $commentMax));
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }

    //GET URL
    $url = $this->_helper->url->url(array('wishlist_id' => $wishlist->wishlist_id, 'slug' => $wishlist->getSlug()), "sitereview_wishlist_view", true);

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'smoothboxClose' => 10,
        'parentRedirect' => $url,
        'parentRedirectTime' => 10,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your wishlist has been created successfully.'))
    ));
  }

  //ACTION FOR EDIT WISHLIST
  public function editAction() {

   

    //ONLY LOGGED IN USER CAN CREATE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET WISHLIST ID AND CHECK VALIDATION
    $wishlist_id = $this->_getParam('wishlist_id');
    if (empty($wishlist_id)) {
      return $this->_forward('notfound', 'error', 'core');
    }

    //GET WISHLIST OBJECT
    $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $level_id = $viewer->level_id;

    if ($level_id != 1 && $wishlist->owner_id != $viewer_id) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //FORM GENERATION
    $this->view->form = $form = new Sitereview_Form_Wishlist_Edit();
    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      Zend_Registry::set('setFixedCreationForm', true);
       $this->_setParam('contentType', 'page');
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
      Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_($form->getTitle()));
      Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Save'));
      $this->view->form->setAttrib('id', 'form-edit-wishlist-sr');
      Zend_Registry::set('setFixedCreationFormId', '#form-edit-wishlist-sr');
      $this->view->form->removeElement('submit');
      $this->view->form->removeElement('cancel');
      $form->setTitle('');
    } else {
      //SET LAYOUT
      $this->_helper->layout->setLayout('default-simple');
    }
    if (!$this->getRequest()->isPost()) {

      //PRIVACY WORK
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      $perms = array();
      foreach ($roles as $roleString) {
        $role = $roleString;
        if ($auth->isAllowed($wishlist, $role, 'view')) {
          $perms['auth_view'] = $roleString;
        }
      }

      $form->populate($wishlist->toArray());
      $form->populate($perms);
      return;
    }

    //FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $db = Engine_Api::_()->getItemTable('sitereview_wishlist')->getAdapter();
    $db->beginTransaction();

    try {

      //GET FORM VALUES
      $values = $form->getValues();

      //SAVE DATA
      $wishlist->setFromArray($values);
      $wishlist->save();

      //PRIVACTY WORK
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if (empty($values['auth_view'])) {
        $values['auth_view'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($wishlist, $role, 'view', ($i <= $viewMax));
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    //GET URL
    $url = $this->_helper->url->url(array('wishlist_id' => $wishlist->wishlist_id, 'slug' => $wishlist->getSlug()), "sitereview_wishlist_view", true);

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'smoothboxClose' => 10,
        'parentRedirect' => $url,
        'parentRedirectTime' => 10,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your wishlist has been edited successfully.'))
    ));
  }

  //ACTION FOR PRINT WISHLIST
  public function printAction() {

    //SET LAYOUT
    $this->_helper->layout->setLayout('default-simple');

    //GET WISHLIST ID AND OBJECT
    $wishlist_id = $this->_getParam('wishlist_id');
    $this->view->wishlist = $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    $content_id = $this->_getParam('content_id', 0);
    $params = Engine_Api::_()->sitereview()->getWidgetInfo('sitereview.wishlist-profile-items', $content_id)->params;
    $this->view->statisticsWishlist = array("entryCount", "likeCount", "viewCount", "followCount");
    if (isset($params['statisticsWishlist'])) {
      $this->view->statisticsWishlist = $params['statisticsWishlist'];
    }

    //FETCH RESULTS
    $this->view->paginator = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->wishlistListings($wishlist->wishlist_id);
    $this->view->paginator->setItemCountPerPage(500);
    $this->view->total_item = $this->view->paginator->getTotalItemCount();
  }

  public function coverPhotoAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //SMOOTHBOX
    if (null == $this->_helper->ajaxContext->getCurrentContext()) {
      $this->_helper->layout->setLayout('default-simple');
    } else {
      //NO LAYOUT
      $this->_helper->layout->disableLayout(true);
    }

    //GET LISTING ID
    $listing_id = $this->view->listing_id = $this->_getParam('listing_id');
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    if (empty($sitereview)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET LISTING ID
    $wishlist_id = $this->view->wishlist_id = $this->_getParam('wishlist_id');
    $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //AUTHORIZATION CHECK
    if ($viewer->level_id != 1 && $wishlist->owner_id != $viewer_id) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //DELETE WISHLIST CONTENT
        $wishlist->listing_id = $listing_id;
        $wishlist->save();

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRedirect' => $this->_helper->url->url(array('action' => 'browse'), "sitereview_wishlist_general", true),
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'))
      ));
    } else {
      $this->renderScript('wishlist/cover-photo.tpl');
    }
  }

  //ACTION FOR DELETE WISHLIST
  public function deleteAction() {

    //DEFAULT LAYOUT
    $this->_helper->layout->setLayout('default-simple');

    //ONLY LOGGED IN USER CAN CREATE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET WISHLIST ID
    $this->view->wishlist_id = $wishlist_id = $this->_getParam('wishlist_id');

    $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $level_id = $viewer->level_id;

    if ($level_id != 1 && $wishlist->owner_id != $viewer_id) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //DELETE WISHLIST CONTENT
        $wishlist->delete();

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRedirect' => $this->_helper->url->url(array('action' => 'browse'), "sitereview_wishlist_general", true),
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your wishlist has been deleted successfully.'))
      ));
    } else {
      $this->renderScript('wishlist/delete.tpl');
    }
  }

}
