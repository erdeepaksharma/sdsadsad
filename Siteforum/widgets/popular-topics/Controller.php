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
class Siteforum_Widget_PopularTopicsController extends Engine_Content_Widget_Abstract {

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

        $siteforumPopularTopic = Zend_Registry::isRegistered('siteforumPopularTopic') ? Zend_Registry::get('siteforumPopularTopic') : null;
        $this->view->statistics = $this->_getParam('statistics', array("0" => "views", "1" => "postCount", "2" => "likeCount", "3" => "ratings",));
        $this->view->truncationTitle = $this->_getParam('truncationTitle', 25) ? $this->_getParam('truncationTitle', 25) : 25;

        $this->view->topics = Engine_Api::_()->getDbtable('topics', 'siteforum')->getPopularTopics($params);

        if(empty($siteforumPopularTopic))
            return $this->setNoRender();
        
        // Do not render if nothing to show
        if (count($this->view->topics) <= 0) {
            return $this->setNoRender();
        }
    }

}
