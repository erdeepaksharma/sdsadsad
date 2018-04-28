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
class Siteforum_Widget_ForumTopicsController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $params = array();
        $params['popular_criteria'] = $this->_getParam('popular_criteria', 'creation_date');
        $params['limit'] = $this->view->itemCountPerPage = $this->_getParam('itemCountPerPage', 25);
        $siteforumTopics = Zend_Registry::isRegistered('siteforumTopics') ? Zend_Registry::get('siteforumTopics') : null;

        if (isset($_GET['tag_id'])) {
            $params['tag_id'] = $_GET['tag_id'];
        }

        if ($module == 'siteforum' && $controller == 'index' && $action == 'search') {

            $params['forum_id'] = $request->getParam('forum_id');
            $params['search'] = $request->getParam('search');
        } else {

            $this->view->siteforum = $siteforum = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;

            if (empty($siteforum) || $siteforum->getType() != 'forum') {
                return $this->setNoRender();
            }

            // Increment view count
            $siteforum->view_count = new Zend_Db_Expr('view_count + 1');
            $siteforum->save();

            $this->view->canPost = $canPost = $siteforum->authorization()->isAllowed(null, 'topic.create');

            $params['forum_id'] = $siteforum->getIdentity();

            $list = $siteforum->getModeratorList();
            $moderators = $this->view->moderators = $list->getAllChildren();
            $this->view->moderatorCount = 0;
            if (!empty($moderators)) {
                $this->view->moderatorCount = COUNT($moderators->toArray());
            }
        }
        
        if(empty($siteforumTopics))
            return $this->setNoRender();
        
        $this->view->statistics = $this->_getParam('statistics', array("views", "postCount", "likeCount", "ratings",));
        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getForumTopics($params);
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->view->itemCountPerPage);

        $this->view->show_rating = $this->_getParam('show_rating', 1);
        $this->view->onlineIcon = $this->_getParam('onlineIcon', 1);
    }

}
