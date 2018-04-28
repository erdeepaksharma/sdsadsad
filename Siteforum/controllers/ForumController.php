<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ForumController.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_ForumController extends Core_Controller_Action_Standard {

    public function init() {
        if (0 !== ($forum_id = (int) $this->_getParam('forum_id')) &&
                null !== ($siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id))) {
            Engine_Api::_()->core()->setSubject($siteforum);
        } else if (0 !== ($category_id = (int) $this->_getParam('category_id')) &&
                null !== ($category = Engine_Api::_()->getItem('forum_category', $category_id))) {
            Engine_Api::_()->core()->setSubject($category);
        }
    }

    public function viewAction() {

        if (!$this->_helper->requireSubject('forum')->isValid()) {
            return;
        }

        $siteforum = Engine_Api::_()->core()->getSubject();
        if (!$this->_helper->requireAuth->setAuthParams($siteforum, null, 'view')->isValid()) {
            return;
        }
        
        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if(empty($siteforumCanView))
            return;

        // Render
        $this->_helper->content
                //->setNoRender()
                ->setEnabled()
        ;
    }

    public function topicCreateAction() {

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (!$this->_helper->requireSubject('forum')->isValid()) {
            return;
        }
        $tempSitemenuLtype=0;
        $forumGlobalView = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.view', 0);
        $forumLSettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.lsettings', 0);
        $forumInfoType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.info.type', 0);
        $forumGlobalType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.type', 0);
        $siteforumCanView = Zend_Registry::isRegistered('siteforumCanView') ? Zend_Registry::get('siteforumCanView') : null;
        if(empty($siteforumCanView))
            return;

        // Render
        $this->_helper->content
                //->setNoRender()
                ->setEnabled()
        ;

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->siteforum = $siteforum = Engine_Api::_()->core()->getSubject();

        if (!$this->_helper->requireAuth()->setAuthParams($siteforum, null, 'topic.create')->isValid()) {
            return;
        }

        $this->view->form = $form = new Siteforum_Form_Topic_Create();

        // Remove the file element if there is no file being posted
        if ($this->getRequest()->isPost() && empty($_FILES['photo'])) {
            $form->removeElement('photo');
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        
        $siteforumAdminType = Zend_Registry::isRegistered('siteforumAdminType') ? Zend_Registry::get('siteforumAdminType') : null;
        if(empty($siteforumAdminType))
            return;
        
        if (empty($forumGlobalType)) {
            for ($check = 0; $check < strlen($forumLSettings); $check++) {
                $tempSitemenuLtype += @ord($forumLSettings[$check]);
            }
            $tempSitemenuLtype = $tempSitemenuLtype + $forumGlobalView;
        }       

        // Process
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['forum_id'] = $siteforum->getIdentity();

        $topicTable = Engine_Api::_()->getDbtable('topics', 'siteforum');
        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'siteforum');
        $postTable = Engine_Api::_()->getDbtable('posts', 'siteforum');

        $db = $topicTable->getAdapter();
        $db->beginTransaction();
        $topic = $topicTable->createRow();
        $topic->setFromArray($values);
        try {

            // Create topic
            $topic->title = $values['title'];
            $topic->description = $values['body'];
            $topic->save();

            // Create post
            $values['topic_id'] = $topic->getIdentity();

            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();

            if (!empty($values['photo'])) {
                $post->setPhoto($form->photo);
            }
            
            if(!empty($tempSitemenuLtype) && ($tempSitemenuLtype != $forumInfoType))
                Engine_Api::_()->getApi('settings', 'core')->setSetting('siteforum.viewtypeinfo.settings', 0);

            $auth = Engine_Api::_()->authorization()->context;
            $auth->setAllowed($topic, 'registered', 'create', true);

            // Create topic watch
            $topicWatchesTable->insert(array(
                'resource_id' => $siteforum->getIdentity(),
                'topic_id' => $topic->getIdentity(),
                'user_id' => $viewer->getIdentity(),
                'watch' => (bool) $values['watch'],
            ));

            // Add activity
            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $topic, 'siteforum_topic_create');
            if ($action) {
                $action->attach($topic);
            }

            $tags = preg_split('/[,]+/', $values['tags']);
            $topic->tags()->addTagMaps($viewer, $tags);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_redirectCustom($post);
    }

}
