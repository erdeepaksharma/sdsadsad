<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: TopicController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_TopicController extends Seaocore_Controller_Action_Standard {

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

    //RETURN IF SUBJECT IS ALREADY SET
    if (Engine_Api::_()->core()->hasSubject())
      return;

    //SET TOPIC OR LISTING SUBJECT
    if (0 != ($topic_id = (int) $this->_getParam('topic_id')) &&
            null != ($topic = Engine_Api::_()->getItem('sitereview_topic', $topic_id))) {
      Engine_Api::_()->core()->setSubject($topic);
    } else if (0 != ($listing_id = (int) $this->_getParam('listing_id')) &&
            null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
      Engine_Api::_()->core()->setSubject($sitereview);
    }
  }

  //ACTION TO BROWSE ALL TOPICS
  public function indexAction() {

    //RETURN IF LISTING SUBJECT IS NOT SET
    if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
      return;

    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();

    $this->view->listingtype_id = $sitereview->listingtype_id;

    //SEND THE TAB ID TO THE TPL
    $this->view->tab_selected_id = $this->_getParam('tab');
    $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);

    //GET PAGINATOR
    $this->view->paginator = Engine_Api::_()->getDbtable('topics', 'sitereview')->getListingTopices($sitereview->getIdentity());
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page'));

    //CAN POST DISCUSSION IF DISCUSSION PRIVACY IS ALLOWED
    $this->view->can_post = $this->_helper->requireAuth->setAuthParams('sitereview_listing', null, "topic_listtype_$sitereview->listingtype_id")->checkRequire();
  }

  //ACTION TO VIEW TOPIC
  public function viewAction() {

    //RETURN IF TOPIC SUBJECT IS NOT SET
    if (!$this->_helper->requireSubject('sitereview_topic')->isValid())
      return;

    $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

    //GET VIEWER
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    //SEND TAB ID TO THE TPL
    $this->view->tab_selected_id = $this->_getParam('content_id');
    //GET TOPIC  SUBJECT
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject();

    //GET SITEREVIEW OBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $topic->listing_id);

    //WHO CAN POST TOPIC
    $this->view->canPost = $canPost = $sitereview->authorization()->isAllowed($viewer, "topic_listtype_$listingtype_id");

    //INCREASE THE VIEW COUNT
    if (!$viewer || !$viewer_id || $viewer_id != $topic->user_id) {
      $topic->view_count = new Zend_Db_Expr('view_count + 1');
      $topic->save();
    }

    //CHECK WATHCHING
    $isWatching = null;
    if ($viewer->getIdentity()) {
      $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
      $isWatching = $topicWatchesTable->isWatching($sitereview->getIdentity(), $topic->getIdentity(), $viewer_id);
      if (empty($isWatching)) {
        $isWatching = null;
      } else {
        $isWatching = 1;
      }
    }
    $this->view->isWatching = $isWatching;

    //GET POST ID
    $this->view->post_id = $post_id = (int) $this->_getParam('post');

    $table = Engine_Api::_()->getDbtable('posts', 'sitereview');
    $select = $table->select()
            ->where('listing_id = ?', $sitereview->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->order('creation_date DESC');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    //SKIP TO PAGE OF SPECIFIED POST
    if (0 != ($post_id = (int) $this->_getParam('post_id')) &&
            null != ($post = Engine_Api::_()->getItem('sitereview_post', $post_id))) {
      $icpp = $paginator->getItemCountPerPage();
      $page = ceil(($post->getPostIndex() + 1) / $icpp);
      $paginator->setCurrentPageNumber($page);
    }
    //USE SPECIFIED PAGE
    else if (0 != ($page = (int) $this->_getParam('page'))) {
      $paginator->setCurrentPageNumber($this->_getParam('page'));
    }

    if ($canPost && !$topic->closed) {
      $this->view->form = $form = new Sitereview_Form_Post_Create();
      $form->populate(array(
          'topic_id' => $topic->getIdentity(),
          'ref' => $topic->getHref(),
          'watch' => ( false == $isWatching ? '0' : '1' ),
      ));
    }

    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			$coremodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core');
			$coreversion = $coremodule->version;
      $checkVersion = Engine_Api::_()->sitereview()->checkVersion($coreversion, '4.1.0');
			if ($checkVersion == 0) {
				$this->_helper->content->render();
			} else {
				$this->_helper->content
								->setNoRender()
								->setEnabled();
			}   
    }
  }

  public function createAction() {

    //ONLY LOGGED IN USER CAN CREATE TOPIC
    if (!$this->_helper->requireUser()->isValid())
      return;

    //LISTING SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
      return;

    //GET LISTING TYPE ID
    $listingtype_id = $this->_listingType->listingtype_id;
    $this->view->tab_selected_id = $this->_getParam('content_id');
    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "topic_listtype_$listingtype_id")->isValid())
      return;

    //GET LISTING
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

    //GET VIEWER
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Topic_Create();

    //CHECK METHOD/DATA
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //PROCESS
    $values = $form->getValues();
    $values['user_id'] = $viewer->getIdentity();
    $values['listing_id'] = $sitereview->getIdentity();

    //GET TABLES
    $topicTable = Engine_Api::_()->getDbtable('topics', 'sitereview');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
    $postTable = Engine_Api::_()->getDbtable('posts', 'sitereview');

    $db = Engine_Api::_()->getDbTable('listings', 'sitereview')->getAdapter();
    $db->beginTransaction();

    try {
      //CREATE TOPIC
      $topic = $topicTable->createRow();
      $topic->setFromArray($values);
      $topic->save();

      //CREATE POST
      $values['topic_id'] = $topic->topic_id;

      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();

      //CREATE TOPIC WATCH
      $topicWatchesTable->insert(array(
          'resource_id' => $sitereview->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $values['watch'],
      ));

      //ADD ACTIVITY
      if (time() >= strtotime($sitereview->creation_date)) {      
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activityApi->addActivity($viewer, $topic, 'sitereview_topic_create_listtype_' . $listingtype_id);

        if ($action) {
          $action->attach($topic);
        }
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    //REDIRECT TO THE TOPIC VIEW PAGE
    return $this->_redirectCustom($topic->getHref(array('content_id' => $this->view->tab_selected_id)), array('prependBase' => false));
  }

  //ACTION FOR TOPIC POST
  public function postAction() {

    //LOGGED IN USER CAN POST
    if (!$this->_helper->requireUser()->isValid())
      return;

    //TOPIC SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_topic')->isValid())
      return;

    //GET LISTING TYPE ID
    $listingtype_id = $this->_listingType->listingtype_id;
    //SEND THE TAB ID TO THE TPL
    $this->view->tab_selected_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id');

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "topic_listtype_$listingtype_id")->isValid())
      return;

    //GET TOPIC SUBJECT
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject();

    //GET SITEREVIEW OBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $topic->listing_id);

    if ($topic->closed) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('This has been closed for posting.');
      return;
    }

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Post_Create();

    //CHECK METHOD
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //PROCESS
    $viewer = Engine_Api::_()->user()->getViewer();
    $topicOwner = $topic->getOwner();
    $isOwnTopic = $viewer->isSelf($topicOwner);

    $postTable = Engine_Api::_()->getDbtable('posts', 'sitereview');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
    $userTable = Engine_Api::_()->getItemTable('user');
    $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

    $values = $form->getValues();
    $values['user_id'] = $viewer->getIdentity();
    $values['listing_id'] = $sitereview->getIdentity();
    $values['topic_id'] = $topic->getIdentity();

    $watch = (bool) $values['watch'];
    $isWatching = $topicWatchesTable->isWatching($sitereview->getIdentity(), $topic->getIdentity(), $viewer->getIdentity());

    $db = Engine_Api::_()->getDbTable('listings', 'sitereview')->getAdapter();
    $db->beginTransaction();

    try {

      //CREATE POST
      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();

      //WATCH
      if (false === $isWatching) {
        $topicWatchesTable->insert(array(
            'resource_id' => $sitereview->getIdentity(),
            'topic_id' => $topic->getIdentity(),
            'user_id' => $viewer->getIdentity(),
            'watch' => (bool) $watch,
        ));
      } elseif($watch != $isWatching) {
        $topicWatchesTable->update(array(
            'watch' => (bool) $watch,
                ), array(
            'resource_id = ?' => $sitereview->getIdentity(),
            'topic_id = ?' => $topic->getIdentity(),
            'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      //ACTIVITY
      if(time() >= strtotime($sitereview->creation_date)) {
        $action = $activityApi->addActivity($viewer, $topic, 'sitereview_topic_reply_listtype_' . $listingtype_id);
        if ($action) {
          $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
        }
      }

      //NOTIFICATIONS
      $notifyUserIds = $topicWatchesTable->getNotifyUserIds($values);

      foreach ($userTable->find($notifyUserIds) as $notifyUser) {

        //DONT NOTIFY SELF
        if ($notifyUser->isSelf($viewer)) {
          continue;
        }

        if ($notifyUser->isSelf($topicOwner)) {
          $type = 'sitereview_discussion_response';
        } else {
          $type = 'sitereview_discussion_reply';
        }

        $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
            'message' => $this->view->BBCode($post->body),
        ));
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    //REDIRECT TO THE TOPIC VIEW PAGE
    return $this->_redirectCustom($topic->getHref(array('content_id' => $this->view->tab_selected_id)), array('prependBase' => false));
  }

  //ACTION FOR MAKE STICKY
  public function stickyAction() {

    //TOPIC SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_topic')->isValid())
      return;

    //GET LISTING TYPE ID
    $listingtype_id = $this->_listingType->listingtype_id;

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "edit_listtype_$listingtype_id")->isValid())
      return;

    //GET TOPIC SUBJECT
    $topic = Engine_Api::_()->core()->getSubject();

    //GET TOPIC TABLE
    $table = Engine_Api::_()->getDbTable('topics', 'sitereview');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->sticky = ( null == $this->_getParam('sticky') ? !$topic->sticky : (bool) $this->_getParam('sticky') );
      $topic->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirectCustom($topic);
  }

  //ACTINO FOR CLOSING THE TOPIC
  public function closeAction() {

    //TOPIC SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_topic')->isValid())
      return;

    //GET LISTING TYPE ID
    $listingtype_id = $this->_listingType->listingtype_id;

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "edit_listtype_$listingtype_id")->isValid())
      return;

    //GET TOPIC SUBJECT
    $topic = Engine_Api::_()->core()->getSubject();

    //GET TOPIC TABLE
    $table = Engine_Api::_()->getDbTable('topics', 'sitereview');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->closed = ( null == $this->_getParam('closed') ? !$topic->closed : (bool) $this->_getParam('closed') );
      $topic->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirectCustom($topic);
  }

  //ACTION FOR RENAME THE TOPIC
  public function renameAction() {

    //TOPIC SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_topic')->isValid())
      return;

    $listingtype_id = $this->_listingType->listingtype_id;

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "edit_listtype_$listingtype_id")->isValid())
      return;

    //GET TOPIC SUBJECT
    $topic = Engine_Api::_()->core()->getSubject();

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Topic_Rename();

    //CHECK METHOD
    if (!$this->getRequest()->isPost()) {
      $form->title->setValue(htmlspecialchars_decode($topic->title));
      return;
    }

    //FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //GET TOPIC TABLE
    $table = Engine_Api::_()->getDbTable('topics', 'sitereview');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $title = htmlspecialchars($form->getValue('title'));
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->title = $title;
      $topic->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forwardCustom('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic renamed.')),
                'layout' => 'default-simple',
                'parentRefresh' => true,
            ));
  }

  //ACTION FOR DELETING THE TOPIC
  public function deleteAction() {

    //TOPIC SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_topic')->isValid())
      return;

    //GET LISTING TYPE ID
    $listingtype_id = $this->_listingType->listingtype_id;

    //AUTHORIZATION CHECK
    if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "edit_listtype_$listingtype_id")->isValid())
      return;

    //GET TOPIC SUBJECT
    $topic = Engine_Api::_()->core()->getSubject();

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Topic_Delete();

    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //FORM VALIDATION
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //GET TOPIC TABLE
    $table = Engine_Api::_()->getDbTable('topics', 'sitereview');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $topic = Engine_Api::_()->core()->getSubject();
      $sitereview = $topic->getParent('sitereview_listing');
      $topic->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forwardCustom('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic deleted.')),
                'layout' => 'default-simple',
                'parentRedirect' => $sitereview->getHref(),
            ));
  }

  //ACTION FOR TOPIC WATCH
  public function watchAction() {

    //GET TOPIC SUBJECT
    $topic = Engine_Api::_()->core()->getSubject();

    //GET SITEREVIEW OBJECT
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $topic->listing_id);

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    $watch = $this->_getParam('watch', true);
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
    $db = $topicWatchesTable->getAdapter();
    $db->beginTransaction();
    try {
      $resultWatch = $topicWatchesTable
              ->select()
              ->from($topicWatchesTable->info('name'), 'watch')
              ->where('resource_id = ?', $sitereview->getIdentity())
              ->where('topic_id = ?', $topic->getIdentity())
              ->where('user_id = ?', $viewer->getIdentity())
              ->limit(1)
              ->query()
              ->fetchAll();
      if (empty($resultWatch))
        $isWatching = 0;
      else
        $isWatching = 1;

      if (false == $isWatching) {
        $topicWatchesTable->insert(array(
            'resource_id' => $sitereview->getIdentity(),
            'topic_id' => $topic->getIdentity(),
            'user_id' => $viewer->getIdentity(),
            'watch' => (bool) $watch,
        ));
      } else {
        $topicWatchesTable->update(array(
            'watch' => (bool) $watch,
                ), array(
            'resource_id = ?' => $sitereview->getIdentity(),
            'topic_id = ?' => $topic->getIdentity(),
            'user_id = ?' => $viewer->getIdentity(),
        ));
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirectCustom($topic);
  }

}