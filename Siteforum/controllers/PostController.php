<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: PostController.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_PostController extends Core_Controller_Action_Standard {

    public function init() {
        if (0 !== ($post_id = (int) $this->_getParam('post_id')) &&
                null !== ($post = Engine_Api::_()->getItem('forum_post', $post_id)) &&
                $post instanceof Siteforum_Model_Post) {
            Engine_Api::_()->core()->setSubject($post);
        }
    }

    public function viewAction() {

        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.thanks', 1)) {
            return;
        }
        
        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if(empty($siteforumCanView))
            return;

        $params = array();
        $params['post_id'] = $this->getParam('topic_post_id');

        $this->view->post = Engine_Api::_()->getItem('forum_post', $this->getParam('topic_post_id'));
        $this->view->topic_post_id = $this->getParam('topic_post_id');
        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('posts', 'siteforum')->getPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($page);

        $this->view->totalMembers = $paginator->getTotalItemCount();
    }

    public function reputationAction() {

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (!$this->_helper->requireSubject('forum_post')->isValid()) {
            return;
        }
        
        $forumGlobalView = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.view', 0);
        $forumManageType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.manage.type', 0);
        $forumGlobalType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.type', 0);
        $hostType = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->post = $post = Engine_Api::_()->core()->getSubject('forum_post');
        $this->view->topic = $topic = $post->getParent();
        $this->view->siteforum = $siteforum = $topic->getParent();

        $this->view->form = $form = new Siteforum_Form_Post_Reputation();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        
        if (empty($forumGlobalType)) {
            for ($check = 0; $check < strlen($hostType); $check++) {
                $tempHostType += @ord($hostType[$check]);
            }
            $tempHostType = $tempHostType + $forumGlobalView;
        }
        
        $siteforumAdminType = Zend_Registry::isRegistered('siteforumAdminType') ? Zend_Registry::get('siteforumAdminType') : null;
        if(empty($siteforumAdminType))
            return;       

        if(!empty($tempHostType) && ($tempHostType != $forumManageType))
            return;
        
        //process
        $post_id = (int) $this->_getParam('post_id');
        $user_id = (int) $this->_getParam('user_id');
        
          
        $viewer_id = $viewer->getIdentity();
        $reputation = $form->getValue('reputation');
        Engine_Api::_()->getDbtable('reputations', 'siteforum')->setReputation($user_id, $viewer_id, $post_id, $reputation);
        $topic_id = $post->topic_id;
        $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
        
        if($this->_getParam('page'))
            $href = ( null === $topic ? $siteforum->getHref() : $topic->getHref(array('page' => $this->_getParam('page'))).'#siteforum_post_'.$post_id );
        else
          $href = ( null === $topic ? $siteforum->getHref() : $topic->getHref().'#siteforum_post_'.$post_id );

        return $this->_forward('success', 'utility', 'core', array(
                    'smoothboxClose' => true,
                    'parentRedirectTime' => 500,
                    'parentRedirect' => $href,
                    'parentRefresh' => true,
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Reputation has been added successfully.')),
                    'format' => 'smoothbox'
        ));
    }

    public function deleteAction() {

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (!$this->_helper->requireSubject('forum_post')->isValid()) {
            return;
        }
        
        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if(empty($siteforumCanView))
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->post = $post = Engine_Api::_()->core()->getSubject('forum_post');
        $this->view->topic = $topic = $post->getParent();
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($post, null, 'delete')->checkRequire() &&
                !$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.delete')->checkRequire()) {
            return $this->_helper->requireAuth()->forward();
        }

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (!$this->_helper->requireSubject('forum_post')->isValid()) {
            return;
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->post = $post = Engine_Api::_()->core()->getSubject('forum_post');
        $this->view->topic = $topic = $post->getParent();
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($post, null, 'delete')->checkRequire() &&
                !$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.delete')->checkRequire()) {
            return $this->_helper->requireAuth()->forward();
        }

        $this->view->form = $form = new Siteforum_Form_Post_Delete();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        // Process
        $table = Engine_Api::_()->getItemTable('forum_post');
        $db = $table->getAdapter();
        $db->beginTransaction();
        $topic_id = $post->topic_id;

        try {
            $post->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
        $href = ( null === $topic ? $siteforum->getHref() : $topic->getHref() );
        return $this->_forward('success', 'utility', 'core', array(
                    'closeSmoothbox' => true,
                    'parentRedirect' => $href,
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post has been deleted successfully.')),
                    'format' => 'smoothbox'
        ));
    }

    public function editAction() {

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (!$this->_helper->requireSubject('forum_post')->isValid()) {
            return;
        }
        
        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if(empty($siteforumCanView))
            return;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->post = $post = Engine_Api::_()->core()->getSubject('forum_post');
        $this->view->topic = $topic = $post->getParent();
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!$this->_helper->requireAuth()->setAuthParams($post, null, 'edit')->checkRequire() &&
                !$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.edit')->checkRequire()) {
            return $this->_helper->requireAuth()->forward();
        }

        $this->view->form = $form = new Siteforum_Form_Post_Edit(array('post' => $post));
        $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.html', 1);

        if ($allowHtml) {
            $body = $post->body;
        } else {
            $body = htmlspecialchars_decode($post->body, ENT_COMPAT);
        }

        $form->body->setValue($body);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $table = Engine_Api::_()->getItemTable('forum_post');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $values = $form->getValues();
            $post->body = $values['body'];
            $post->edit_id = $viewer->getIdentity();

            //DELETE photo here.
            if (!empty($values['photo_delete']) && $values['photo_delete']) {
                $post->deletePhoto();
            }

            if (!empty($values['photo'])) {
                $post->setPhoto($form->photo);
            }

            $post->save();

            $db->commit();

            if($this->_getParam('page'))
            $redirct_Url = $post->getHref(array('page' => $this->_getParam('page')))."#siteforum_post_".$post->getIdentity();
        else
            $redirct_Url = $post->getHref()."#siteforum_post_".$post->getIdentity();
            
            return $this->_redirectCustom($redirct_Url);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function getUserAction() {

        $params['username'] = $this->_getParam('username');
        $params['post_id'] = $this->_getParam('post_id');

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('posts', 'siteforum')->getPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($page);
        $this->view->totalMembers = $paginator->getTotalItemCount();
    }

}
