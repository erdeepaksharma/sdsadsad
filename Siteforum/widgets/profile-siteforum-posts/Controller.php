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
class Siteforum_Widget_ProfileSiteforumPostsController extends Engine_Content_Widget_Abstract {

    protected $_childCount;

    public function indexAction() {

        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            return $this->setNoRender();
        }

        // Get subject and check auth
        $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            return $this->setNoRender();
        }

        $params = array();
        // Get siteforums allowed to be viewed by current user
        $siteforumIds = array();
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
        $params['user_id'] = $subject->getIdentity();

        $siteforumProfileForum = Zend_Registry::isRegistered('siteforumProfileForum') ? Zend_Registry::get('siteforumProfileForum') : null;
        $this->view->statistics = $this->_getParam('statistics', array("thankCount", "likeCount"));
        $this->view->truncationDescription = $this->_getParam('truncationDescription', 64);
        $this->view->decode_bbcode = Engine_Api::_()->getDbTable('settings', 'core')->getSetting('siteforum.bbcode');
        $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('posts', 'siteforum')->getPopularPosts($params);

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Do not render if nothing to show
        if ($paginator->getTotalItemCount() <= 0) {
            return $this->setNoRender();
        }
        
        if(empty($siteforumProfileForum))
            return $this->setNoRender();

        // Add count to title if configured
        if ($this->_getParam('titleCount', false) && $paginator->getTotalItemCount() > 0) {
            $this->_childCount = $paginator->getTotalItemCount();
        }
    }

    public function getChildCount() {
        return $this->_childCount;
    }

}
