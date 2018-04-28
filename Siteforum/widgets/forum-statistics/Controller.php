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
class Siteforum_Widget_ForumStatisticsController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $siteforum = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;

        $params = array();
        $this->view->forum_id = 0;
        if (!empty($siteforum) && $siteforum->getType() == 'forum') {
            $this->view->forum_id = $params['forum_id'] = $siteforum->getIdentity();
        }

        $siteforumStatistics = Zend_Registry::isRegistered('siteforumStatistics') ? Zend_Registry::get('siteforumStatistics') : null;
        $this->view->forumStatistics = Engine_Api::_()->getDbtable('forums', 'siteforum')->getForumStatistics($params);
        $this->view->statistics = $this->_getParam('statistics', array('0' => 'topicCount', '1' => 'postCount', '2' => 'activeUsers'));

        if(empty($siteforumStatistics))
            return $this->setNoRender();
        
        if (count($this->view->statistics) <= 0) {
            return $this->setNoRender();
        }
    }

}
