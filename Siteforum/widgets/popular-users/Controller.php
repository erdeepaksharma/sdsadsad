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
class Siteforum_Widget_PopularUsersController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $params = array();
        $params['limit'] = $this->_getParam('itemCountPerPage', 3);

        switch ($this->_getParam('popular_criteria', 'topic_count')) {
            case 'topic_count':
                $params['table'] = 'topics';
                $this->view->topic = 1;
                break;
            case 'post_count':
                $params['table'] = 'posts';
                $this->view->post = 1;
                break;
            case 'thank_count':
                $params['table'] = 'thanks';
                $this->view->thanks = 1;
                break;
            case 'reputation_count':
                $params['table'] = 'reputations';
                $this->view->reputation = 1;
                break;
            default:
                $params['table'] = 'topics';
                $this->view->topic = 1;
                break;
        }

        $siteforumPopularUser = Zend_Registry::isRegistered('siteforumPopularUser') ? Zend_Registry::get('siteforumPopularUser') : null;
        $this->view->show_online_user = $params['show_online_user'] = $this->_getParam('show_online_user', 1);
        $this->view->users = Engine_Api::_()->getDbTable('topics', 'siteforum')->getPopularUsers($params);

        if(empty($siteforumPopularUser))
            return $this->setNoRender();
        
        // Do not render if nothing to show
        if (count($this->view->users) <= 0) {
            return $this->setNoRender();
        }

        $this->view->onlineIcon = $this->_getParam('onlineIcon', 1);
    }

}
