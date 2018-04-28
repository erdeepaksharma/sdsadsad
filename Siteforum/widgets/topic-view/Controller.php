<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Widget_TopicViewController extends Engine_Content_Widget_Abstract {

    protected $_childCount;

    public function indexAction() {

        if (Engine_Api::_()->core()->hasSubject('forum_topic'))
            $this->view->topic = $topic = Engine_Api::_()->core()->getSubject('forum_topic');
        else
            return $this->setNoRender();

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->siteforum = $siteforum = $topic->getParent();

        if (!Engine_Api::_()->authorization()->isAllowed('forum', $viewer, "view")) {
            return false;
        }
        
        // Suggest to Friend link show work
        $is_suggestion_enabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('suggestion');
        $siteforumTopicView = Zend_Registry::isRegistered('siteforumTopicView') ? Zend_Registry::get('siteforumTopicView') : null;
        if(!empty($is_suggestion_enabled)){
        $modContentObj = Engine_Api::_()->suggestion()->getSuggestedFriend('siteforum', $topic->topic_id,null,null);
				if (!empty($modContentObj)) {
					$contentCreatePopup = @COUNT($modContentObj);
				}
        
        
          Engine_Api::_()->siteforum()->deleteSuggestion(Engine_Api::_()->user()->getViewer()->getIdentity(), 'forum', $topic->topic_id, 'siteforum_suggestion');
          if(!empty($contentCreatePopup)){
          $this->view->forumSuggLink = Engine_Api::_()->suggestion()->getModSettings('siteforum', 'link');}
        } else {
          $this->view->forumSuggLink = 0;
        }
        
        
        $this->view->viewer_id = $viewer->getIdentity();
        $this->view->topicTags = $topic->tags()->getTagMaps();
        // Settings
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $this->view->post_id = $post_id = (int) $this->_getParam('post_id');
        $this->view->decode_bbcode = $settings->getSetting('siteforum.bbcode');
        // Views
        if (!$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id) {
            $topic->view_count = new Zend_Db_Expr('view_count + 1');
            $topic->save();
        }
        // Check watching
        $isWatching = null;

        if ($viewer->getIdentity()) {
            $params = array();
            $params['resource_id'] = $siteforum->getIdentity();
            $params['topic_id'] = $topic->getIdentity();
            $params['user_id'] = $viewer->getIdentity();

            $isWatching = Engine_Api::_()->getDbtable('topicWatches', 'siteforum')->isWatching($params);

            if (false === $isWatching) {
                $isWatching = null;
            } else {
                $isWatching = (bool) $isWatching;
            }
        }
        $this->view->isWatching = $isWatching;

        // Auth for topic and post
        $canPost = false;
        $canEdit = false;
        $canDelete = false; 
        $canEdit_Post = false;
        $canDelete_Post = false;
        if (!$topic->closed && Engine_Api::_()->authorization()->isAllowed($siteforum, null, 'post.create')) {
            $canPost = true;
        }
        if ($viewer->getIdentity()) {
            $canEdit = Engine_Api::_()->authorization()->isAllowed('forum', $viewer->level_id, 'topic.edit');
            $canDelete = Engine_Api::_()->authorization()->isAllowed('forum', $viewer->level_id, 'topic.delete');
            $canEdit_Post = Engine_Api::_()->authorization()->isAllowed('forum', $viewer->level_id, 'post.edit');
            $canDelete_Post = Engine_Api::_()->authorization()->isAllowed('forum', $viewer->level_id, 'post.delete');
        }
        $this->view->canPost = $canPost;
        $this->view->canEdit = $canEdit;
        $this->view->canDelete = $canDelete;
        $this->view->canEdit_Post = $canEdit_Post;
        $this->view->canDelete_Post = $canDelete_Post; 
       
        $this->view->rating_count = Engine_Api::_()->getDbtable('ratings', 'siteforum')->ratingCount($topic->getIdentity());
        $this->view->rated = Engine_Api::_()->getDbtable('ratings', 'siteforum')->checkRated($topic->getIdentity(), $viewer->getIdentity()); 
        
        // Make form
        if ($canPost) {
            $this->view->form = $form = new Siteforum_Form_Post_Quick();
            $form->setAction($topic->getHref(array('action' => 'post-create')));
            $form->populate(array(
                'topic_id' => $topic->getIdentity(),
                'ref' => $topic->getHref(),
                'watch' => ( false === $isWatching ? '0' : '1' ),
            ));
        }

        // Keep track of topic user views to show them which ones have new posts
        if ($viewer->getIdentity()) {
            $topic->registerView($viewer);
        }
        
        $topicorder = $this->_getParam('topicsorder', 1);
        
        if ($topicorder) {
            $order = 'creation_date DESC';
        } else {
            $order = 'creation_date ASC';
        }

        $select = $topic->getChildrenSelect('forum_post', array('order' => $order));
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 25));

        // set up variables for pages
        $this->view->page_param = $page;
        $this->view->total_page = ceil(($paginator->getTotalItemCount()+1)/$this->_getParam('itemCountPerPage', 25));
        $post = Engine_Api::_()->getItem('forum_post', $post_id);

        if ($canPost) {
            $this->view->form = $form = new Siteforum_Form_Post_Quick();
            $form->setAction($topic->getHref(array('action' => 'post-create', 'page' => $this->view->total_page)));
            $form->populate(array(
                'topic_id' => $topic->getIdentity(),
                'ref' => $topic->getHref(),
                'watch' => ( false === $isWatching ? '0' : '1' ),
            ));
        }
        $page_param = (int) $this->_getParam('page');
        // if there is a post_id
        if ($post_id && $post && !$page_param) {
            $icpp = $paginator->getItemCountPerPage();
            $post_page = ceil(($post->getPostIndex() + 1) / $icpp);

            $paginator->setCurrentPageNumber($post_page);
        }
        // Use specified page
        else if ($page_param) {
            $paginator->setCurrentPageNumber($page_param);
        }

        $this->view->shareOption = $this->_getParam('shareOptions', array("0" => "facebook", "1" => "twitter", "2" => "linkedin", "3" => "google", "4" => "community"));

        $orientation = $this->view->layout()->orientation;
        if ($orientation == 'right-to-left') {
            $this->view->directionality = 'rtl';
        } else {
            $this->view->directionality = 'ltr';
        }

        $local_language = $this->view->locale()->getLocale()->__toString();
        $local_language = explode('_', $local_language);
        $this->view->language = $local_language[0];
        $this->view->upload_url = '';
        if (Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create')) {
            $this->view->upload_url = $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'upload-photo'), 'siteforum_general', true);
        }

        $orientation = $this->view->layout()->orientation;
        if ($orientation == 'right-to-left') {
            $this->view->directionality = 'rtl';
        } else {
            $this->view->directionality = 'ltr';
        }

        $local_language = $this->view->locale()->getLocale()->__toString();
        $local_language = explode('_', $local_language);
        $this->view->language = $local_language[0];
        $this->view->upload_url = '';
        if (Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create')) {
            $this->view->upload_url = $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'upload-photo'), 'siteforum_general', true);
        }
        
        if(empty($siteforumTopicView))
            return $this->setNoRender();

        $this->view->onlineIcon = $this->_getParam('onlineIcon', 1);
    }

}
