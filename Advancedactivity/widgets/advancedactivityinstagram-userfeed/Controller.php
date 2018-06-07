<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_AdvancedactivityinstagramUserfeedController extends Engine_Content_Widget_Abstract {

    public function indexAction() {
        return $this->setNoRender();
        $view = new Zend_View();
        $front = Zend_Controller_Front::getInstance();
        $module = $front->getRequest()->getModuleName();
        $action = $front->getRequest()->getActionName();

        $this->view->isHomeFeeds = $ishomefeeds = $this->_getParam('homefeed');

        if (!empty($ishomefeeds)) {
            $showInHeader = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.show.in.header', 1);
            $feedCount = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.feed.count', 8);
            $feedwidth = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.image.width', 150);
        } else {
            $showInHeader = $this->_getParam('advancedactivity_show_in_header', 1);
            $feedCount = $this->_getParam('instagram_feed_count', 8);
            $feedwidth = $this->_getParam('instagram_image_width', 150);
            $this->view->disableViewMore = $this->_getParam('instagram_disable_viewmore', 0);
        }
        $this->view->showInHeader = $showInHeader;
        $this->view->feedCount = $feedCount;
        $this->view->feedwidth = $feedwidth;

        $showFeedSize = 'thumbnail';
        if (($feedwidth * $feedwidth) >= 22500) {
            $showFeedSize = 'standard_resolution';
        } elseif (($feedwidth * $feedwidth) >= 576) {
            $showFeedSize = 'thumbnail';
        } elseif (($feedwidth * $feedwidth) < 576) {
            $showFeedSize = 'low_resolution';
        }
        $this->view->showFeedSize = $showFeedSize;

        $this->view->curr_url = $curr_url = $front->getRequest()->getRequestUri(); // Return the current URL.
        $this->view->isajax = $is_ajax = $this->_getParam('is_ajax', '0');

        if ($this->_getParam('autoload', true)) {
            $this->view->autoload = true;
            if ($this->_getParam('is_ajax_load', false)) {
                $this->view->is_ajax_load = true;
                $this->view->autoload = false;
                if ($this->_getParam('contentpage', 1) > 1)
                    $this->getElement()->removeDecorator('Title');
                $this->getElement()->removeDecorator('Container');
            } else {

                if ($ishomefeeds)
                    $this->getElement()->removeDecorator('Title');
            }
        } else {
            $this->view->is_ajax_load = $this->_getParam('is_ajax_load', false);
            if ($this->_getParam('contentpage', 1) > 1) {
                $this->getElement()->removeDecorator('Title');
                $this->getElement()->removeDecorator('Container');
            }
        }

        $this->view->tabaction = $tabaction = $this->_getParam('tabaction', '0');
        if ($is_ajax || $tabaction) {
            $this->getElement()->removeDecorator('Title');
            $this->getElement()->removeDecorator('Container');
        }
        $enable_fboldversion = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdna');
        if (!empty($enable_fboldversion)) {
            $socialdnamodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('socialdna');
            $socialdnaversion = $socialdnamodule->version;
            if ($socialdnaversion >= '4.1.1') {
                $enable_fboldversion = 0;
            }
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

        $subject = null;
        if (Engine_Api::_()->core()->hasSubject()) {
            // Get subject
            $subject = Engine_Api::_()->core()->getSubject();
            if (!$subject->authorization()->isAllowed($viewer, 'view')) {
                return $this->setNoRender();
            }
        }
        //CHECK IF LInkedin KEYS ARE NOT THERE THEN SET NO RENDER:

        $instagram_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.apikey');
        $instagram_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.secretkey');
        if (empty($instagram_apikey) || empty($instagram_secret))
            return $this->setNoRender();
        $this->view->enable_fboldversion = $enable_fboldversion;
        $length = $this->_getParam('sitemobileinstagramfeed_length', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $limit = $request->getParam('limit', $length);

        $instagramloginURL = Zend_Controller_Front::getInstance()->getRouter()
                        ->assemble(array('module' => 'seaocore', 'controller' => 'auth', 'action' => 'instagram-check'), 'default', true) . '?' . http_build_query(array('redirect_urimain' => urlencode('http://' . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_instagram=1')));

        $this->view->instagramLoginURL = $instagramloginURL;
        //Session based API call.

        $limit = $request->getParam('limit', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
        try {
            $Api_instagram = new Seaocore_Api_Instagram_Api();
            $callbackUrl = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_urimain=1';
            $instagramTable = Engine_Api::_()->getDbtable('instagram', 'advancedactivity');
            $Api_instagram = Engine_Api::_()->getApi('instagram_Api', 'seaocore');
            $instagramObj = $Api_instagram->getApi();
            $accessToken = $instagramObj->getAccessToken();
            $_SESSION['instagram_token'] = $accessToken;
            $instagramObj->setAccessToken($accessToken);
            $nextMaxId = $this->_getParam('new_max_id', null);
            $paginationNextUrlLikes = $this->_getParam('pagination_next_url_likes', null);
            $paginationNextUrlUploades = $this->_getParam('pagination_next_url_uploades', null);
            $isfirsttimenull = $this->_getParam('is_instagram_ajax', 'null');
            $isAjaxRequest = $this->_getParam('is_ajax_request', false);
            if ($isfirsttimenull == 'null')
                $this->view->is_first_time = 1;
            else
                $this->view->is_first_time = 0;

            $this->view->is_instagram_ajax = $is_instagram_ajax = $this->_getParam('is_instagram_ajax', 1);

            if (!empty($isAjaxRequest)) {
                $response_likes = $instagramObj->getPaginatorNext($paginationNextUrlLikes);
                $response_uploades = $instagramObj->getPaginatorNext($paginationNextUrlUploades);
            } else {
                $response_likes = $instagramObj->getUserLikes($feedCount);
                $response_uploades = $instagramObj->getUserMedia('self', $feedCount);
            }


            if (isset($response_likes->meta->code) && $response_likes->meta->code != 200) {
                $this->view->paginator = $this->view->response = null;
            } else {


                $response = array_merge((array) $response_likes->data, (array) $response_uploades->data);

                $this->view->paginator = $this->view->response = $response;
                $this->view->paginationNextUrlLikes = $response_likes->pagination->next_url;
                $this->view->paginationNextUrlUploades = $response_uploades->pagination->next_url;
            }
        } catch (Exception $e) {
            $this->view->instagramLoginURL = $instagramloginURL;
            $this->view->logged_userfeed = '';
        }
    }

}
