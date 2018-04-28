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
class Siteforum_Widget_PopularPostsController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        // Get siteforums allowed to be viewed by current user
        $viewer = Engine_Api::_()->user()->getViewer();
        $siteforumIds = array();
        $params = array();
        $authTable = Engine_Api::_()->getDbtable('allow', 'authorization');
        $perms = $authTable->select()
                ->where('resource_type = ?', 'forum')
                ->where('action = ?', 'view')
                ->query()
                ->fetchAll();

        foreach ($perms as $perm) {
            if ($perm['role'] == 'everyone') {
                $siteforumIds[] = $perm['resource_id'];
            } else if ($viewer &&
                    $viewer->getIdentity() &&
                    $perm['role'] == 'authorization_level' &&
                    $perm['role_id'] == $viewer->level_id) {
                $siteforumIds[] = $perm['resource_id'];
            }
        }
        if (empty($siteforumIds)) {
            return $this->setNoRender();
        }

        $params['forum_ids'] = $siteforumIds;
        $params['popular_criteria'] = $this->_getParam('popular_criteria', 'creation_date');
        $params['limit'] = $this->_getParam('itemCountPerPage', 5);

        $this->view->statistics = $this->_getParam('statistics', array("thankCount", "likeCount"));
        $this->view->truncationDescription = $this->_getParam('truncationDescription', 64);
        $this->view->truncationLastPost = $this->_getParam('truncationLastPost', 30) ? $this->_getParam('truncationLastPost', 25) : 25;

        $siteforumPopularPosts = Zend_Registry::isRegistered('siteforumPopularPosts') ? Zend_Registry::get('siteforumPopularPosts') : null;
        $this->view->posts = Engine_Api::_()->getDbtable('posts', 'siteforum')->getPopularPosts($params);

        if(empty($siteforumPopularPosts))
            return $this->setNoRender();
        
        if (count($this->view->posts) <= 0) {
            return $this->setNoRender();
        }
    }

}
