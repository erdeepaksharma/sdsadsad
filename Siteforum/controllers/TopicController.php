<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: TopicController.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_TopicController extends Core_Controller_Action_Standard {

    public function init() {
        if (0 !== ($topic_id = (int) $this->_getParam('topic_id')) &&
                null !== ($topic = Engine_Api::_()->getItem('forum_topic', $topic_id)) &&
                $topic instanceof Siteforum_Model_Topic) {
            Engine_Api::_()->core()->setSubject($topic);
        }
    }

    public function reputationViewAction() {

        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.reputation', 1)) {
            return;
        }
        $params = array();
        $params['post_id'] = $this->view->topic_post_id = $this->_getParam('topic_post_id');
        $this->view->reputation = $params['reputation'] = $this->_getParam('reputation', 1);
        $this->view->increase_count = Engine_Api::_()->getDbTable('reputations', 'siteforum')->reputationCounts(1, $this->_getParam('topic_post_id'));
        $this->view->decrease_count = Engine_Api::_()->getDbTable('reputations', 'siteforum')->reputationCounts(0, $this->_getParam('topic_post_id'));
        $this->view->post = Engine_Api::_()->getItem('forum_post', $this->getParam('topic_post_id'));
        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($page);
        $this->view->totalMembers = $paginator->getTotalItemCount();
    }

    public function deleteAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();
        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.delete')->isValid()) {
            return;
        }

        $this->view->form = $form = new Siteforum_Form_Topic_Delete();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $table = Engine_Api::_()->getItemTable('forum_topic');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic->delete();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic has been deleted successfully.')),
                    'layout' => 'default-simple',
                    'parentRedirect' => $siteforum->getHref(),
        ));
    }

    public function editAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();
        $siteforumUpdate = Zend_Registry::isRegistered('siteforumUpdate') ? Zend_Registry::get('siteforumUpdate') : null;

        if (empty($siteforumUpdate))
            return;

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.edit')->isValid()) {
            return;
        }

        $this->view->form = $form = new Siteforum_Form_Topic_Edit();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $table = Engine_Api::_()->getItemTable('forum_topic');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $values = $form->getValues();

            $topic->setFromArray($values);
            $topic->save();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function viewAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;

        if (empty($siteforumCanView))
            return;

        $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'view')->isValid()) {
            return;
        }

        $this->_helper->content
                //->setNoRender()
                ->setEnabled()
        ;
    }

    public function stickyAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if (empty($siteforumCanView))
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.edit')->isValid()) {
            return;
        }

        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->sticky = ( null === $this->_getParam('sticky') ? !$topic->sticky : (bool) $this->_getParam('sticky') );
            $topic->save();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->_redirectCustom($topic);
    }

    public function closeAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.edit')->isValid()) {
            return;
        }

        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->closed = ( null === $this->_getParam('closed') ? !$topic->closed : (bool) $this->_getParam('closed') );
            $topic->save();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->_redirectCustom($topic);
    }

    public function thankAction() {

        $post_id = (int) $this->_getParam('post_id');
        $user_id = (int) $this->_getParam('user_id');
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        Engine_Api::_()->getDbTable('thanks', 'siteforum')->setThanks($user_id, $viewer_id, $post_id);

        $data = array();
        $data[] = array(
            'user' => $viewer_id,
            'thanks' => Engine_Api::_()->getDbtable('thanks', 'siteforum')->countThanks($user_id),
            'thanked' => Engine_Api::_()->getDbtable('thanks', 'siteforum')->countThanked($viewer_id),
        );
        return $this->_helper->json($data);

//        $data = Zend_Json::encode($data);
//        $this->getResponse()->setBody($data);
    }

    public function renameAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.edit')->isValid()) {
            return;
        }

        $this->view->form = $form = new Siteforum_Form_Topic_Rename();

        if (!$this->getRequest()->isPost()) {
            $form->title->setValue(htmlspecialchars_decode(($topic->title)));
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $title = $form->getValue('title');
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->title = $title;
            $topic->save();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic has been renamed successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function moveAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if (empty($siteforumCanView))
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $previous_forum_id = $topic->forum_id;
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.edit')->isValid()) {
            return;
        }

        $this->view->form = $form = new Siteforum_Form_Topic_Move();

        // Populate with options
        $multiOptions = array();
        $select = Engine_Api::_()->getItemTable('forum')->select()->where('forum_id != ?', $topic->forum_id);
        foreach (Engine_Api::_()->getItemTable('forum')->fetchAll($select) as $siteforum) {
            $multiOptions[$siteforum->getIdentity()] = $this->view->translate($siteforum->getTitle());
        }
        $form->getElement('forum_id')->setMultiOptions($multiOptions);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $values = $form->getValues();

        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'siteforum');
        try {
            // Update topic
            $topic->forum_id = $values['forum_id'];
            $topicWatchesTable->update(array('resource_id' => $topic->forum_id), array('topic_id = ?' => $topic->getIdentity(), 'resource_id = ?' => $previous_forum_id));
            $topic->save();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic has been moved successfully.')),
                    'layout' => 'default-simple',
                    //'parentRefresh' => true,
                    'parentRedirect' => $topic->getHref(),
        ));
    }

    public function postCreateAction() {

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $forumGlobalView = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.view', 0);
        $forumManageType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.manage.type', 0);
        $forumGlobalType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.type', 0);
        $hostType = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));
        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if (empty($siteforumCanView))
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'post.create')->isValid()) {
            return;
        }

        if ($topic->closed) {
            return;
        }

        $this->view->form = $form = new Siteforum_Form_Post_Create();

        // Remove the file element if there is no file being posted
        if ($this->getRequest()->isPost() && empty($_FILES['photo'])) {
            $form->removeElement('photo');
        }

        $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.html', 1);
        $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.bbcode', 0);

        $quote_id = $this->getRequest()->getParam('quote_id');
        if (!empty($quote_id)) {
            $quote = Engine_Api::_()->getItem('forum_post', $quote_id);
            if ($quote->user_id == 0) {
                $owner_name = Zend_Registry::get('Zend_Translate')->_('Deleted Member');
            } else {
                $owner_name = $quote->getOwner()->__toString();
            }
            if (!$allowHtml && !$allowBbcode) {
                $form->body->setValue(strip_tags($this->view->translate('%1$s said:', $owner_name)) . " ''" . strip_tags($quote->body) . "''\n-------------\n");
            } elseif ($allowHtml && !$allowBbcode) {
                $form->body->setValue("<blockquote class='siteforum_icon_quote'><strong>" . $this->view->translate('%1$s said:', $owner_name) . "</strong><br />" . $quote->body . "</blockquote><br />");
            } else {
                $form->body->setValue("[blockquote class='siteforum_icon_quote'][b]" . strip_tags($this->view->translate('%1$s said:', $owner_name)) . "[/b]\r\n" . htmlspecialchars_decode($quote->body, ENT_COMPAT) . "[/blockquote]\r\n");
            }
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $siteforumAdminType = Zend_Registry::isRegistered('siteforumAdminType') ? Zend_Registry::get('siteforumAdminType') : null;
        if (empty($siteforumAdminType))
            return;

        // Process
        $values = $form->getValues();
        $values['body'] = $values['body'];
        $values['user_id'] = $viewer->getIdentity();
        $values['topic_id'] = $topic->getIdentity();
        $values['forum_id'] = $siteforum->getIdentity();

        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'siteforum');
        $postTable = Engine_Api::_()->getDbtable('posts', 'siteforum');
        $userTable = Engine_Api::_()->getItemTable('user');
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

        $viewer = Engine_Api::_()->user()->getViewer();
        $topicOwner = $topic->getOwner();

        $watch = (bool) $values['watch'];
        $params = array();
        $params['resource_id'] = $siteforum->getIdentity();
        $params['topic_id'] = $topic->getIdentity();
        $params['user_id'] = $viewer->getIdentity();

        if (empty($forumGlobalType)) {
            for ($check = 0; $check < strlen($hostType); $check++) {
                $tempHostType += @ord($hostType[$check]);
            }
            $tempHostType = $tempHostType + $forumGlobalView;
        }

        $isWatching = $topicWatchesTable->isWatching($params);

        $db = $postTable->getAdapter();
        $db->beginTransaction();

        try {

            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();

            if (!empty($values['photo'])) {
                $post->setPhoto($form->photo);
            }

            // Watch
            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $siteforum->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool) $watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool) $watch,
                        ), array(
                    'resource_id = ?' => $siteforum->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }

            // Activity
            $action = $activityApi->addActivity($viewer, $topic, 'siteforum_topic_reply');
            if ($action) {
                $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
            }

            // Notifications
            $notifyUserIds = $topicWatchesTable->select()
                    ->from($topicWatchesTable->info('name'), 'user_id')
                    ->where('resource_id = ?', $siteforum->getIdentity())
                    ->where('topic_id = ?', $topic->getIdentity())
                    ->where('watch = ?', 1)
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN)
            ;

            if (!empty($tempHostType) && ($tempHostType != $forumManageType))
                Engine_Api::_()->getApi('settings', 'core')->setSetting('siteforum.viewtypeinfo.type', 0);

            foreach ($userTable->find($notifyUserIds) as $notifyUser) {
                // Don't notify self
                if ($notifyUser->isSelf($viewer)) {
                    continue;
                }
                if ($notifyUser->isSelf($topicOwner)) {
                    $type = 'siteforum_topic_response';
                } else {
                    $type = 'siteforum_topic_reply';
                }
                $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
                    'message' => $this->view->BBCode($post->body), // @todo make sure this works
                    //'url' => $this->getRequest()->getServer('HTTP_REFERER'),
                    'postGuid' => $post->getGuid(),
                ));
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        //SENDING ACTIVITY FEED TO FACEBOOK.
        $enable_Facebooksefeed = $enable_fboldversion = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('facebooksefeed');
        if (!empty($enable_Facebooksefeed)) {

          $siteforum_array = array();
          $siteforum_array['type'] = 'siteforum_topic_create';
          $siteforum_array['object'] = $post;

            Engine_Api::_()->facebooksefeed()->sendFacebookFeed($siteforum_array);
        }
        if($this->_getParam('page'))
            $redirct_Url = $post->getHref(array('page' => $this->_getParam('page')))."#siteforum_post_".$post->getIdentity();
        else
            $redirct_Url = $post->getHref()."#siteforum_post_".$post->getIdentity();
        
        return $this->_redirectCustom($redirct_Url);
    }

    public function watchAction() {

        if (!$this->_helper->requireSubject('forum_topic')->isValid()) {
            return;
        }

        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if (empty($siteforumCanView))
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, $viewer, 'view')->isValid()) {
            return;
        }

        $watch = $this->_getParam('watch', true);

        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'siteforum');
        $db = $topicWatchesTable->getAdapter();
        $db->beginTransaction();

        try {

            $params = array();
            $params['resource_id'] = $siteforum->getIdentity();
            $params['topic_id'] = $topic->getIdentity();
            $params['user_id'] = $viewer->getIdentity();

            $isWatching = $topicWatchesTable->isWatching($params);

            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $siteforum->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool) $watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool) $watch,
                        ), array(
                    'resource_id = ?' => $siteforum->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }

            $data = array();
            $data[] = array(
                'isWatching' => $isWatching,
            );


            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        return $this->_helper->json($data);
        //$this->_redirectCustom($topic);
    }

    public function getUserAction() {

        $params['username'] = $this->_getParam('username');
        $params['post_id'] = $this->_getParam('post_id');
        $this->view->reputation = $params['reputation'] = $this->_getParam('reputation', 1);

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($page);
        $this->view->totalMembers = $paginator->getTotalItemCount();
    }

}
