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
class Siteforum_Widget_ProfileSiteforumTopicsController extends Engine_Content_Widget_Abstract {

    protected $_childCount;

    public function indexAction() {

        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            return $this->setNoRender();
        }

        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject();
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            return $this->setNoRender();
        }

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

        $this->view->statistics = $statistics = $this->_getParam('statistics', array("0" => "viewCount", "1" => "postCount", "2" => "likeCount", "3" => "ratings",));
        $this->view->truncationDescription = $this->_getParam('truncationDescription', 64);
        $this->view->itemCountPerPage = $this->_getParam('itemCountPerPage', 10);

        $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('topics', 'siteforum')->getPopularTopics($params);
        $siteforumProfileForumTopics = Zend_Registry::isRegistered('siteforumProfileForumTopics') ? Zend_Registry::get('siteforumProfileForumTopics') : null;

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->view->itemCountPerPage);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Do not render if nothing to show
        if ($paginator->getTotalItemCount() <= 0) {
            return $this->setNoRender();
        }
        
        if(empty($siteforumProfileForumTopics))
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
