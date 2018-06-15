<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_PinboardListingsSitereviewController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $this->view->params = $this->_getAllParams();
        $this->view->params['defaultLoadingImage'] = $this->_getParam('defaultLoadingImage', 1);
        if (!isset($this->view->params['noOfTimes']) || empty($this->view->params['noOfTimes']))
            $this->view->params['noOfTimes'] = 1000;

        if ($this->_getParam('autoload', true)) {
            $this->view->autoload = true;
            if ($this->_getParam('is_ajax_load', false)) {
                $this->view->is_ajax_load = true;
                $this->view->autoload = false;
                if ($this->_getParam('contentpage', 1) > 1)
                    $this->getElement()->removeDecorator('Title');
                $this->getElement()->removeDecorator('Container');
            } else {
                //  $this->view->layoutColumn = $this->_getParam('layoutColumn', 'middle');
                $this->getElement()->removeDecorator('Title');
                //return;
            }
        } else {
            $this->view->is_ajax_load = $this->_getParam('is_ajax_load', false);
            if ($this->_getParam('contentpage', 1) > 1) {
                $this->getElement()->removeDecorator('Title');
                $this->getElement()->removeDecorator('Container');
            }
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $params = array();
        $params['popularity'] = $this->view->popularity = $this->_getParam('popularity', 'creation_date');
        $params['limit'] = $this->_getParam('itemCount', 12);
        $fea_spo = $this->_getParam('fea_spo', '');
        if ($fea_spo == 'featured') {
            $params['featured'] = 1;
        } elseif ($fea_spo == 'newlabel') {
            $params['newlabel'] = 1;
        } elseif ($fea_spo == 'sponsored') {
            $params['sponsored'] = 1;
        } elseif ($fea_spo == 'fea_spo') {
            $params['sponsored_or_featured'] = 1;
        } elseif ($fea_spo == 'createdbyfriends') {
            if ($viewer->getIdentity()) {
                $params['createdbyfriends'] = 2;
                //GET AN ARRAY OF FRIEND IDS
                $friends = $viewer->membership()->getMembers();
                $ids = array();
                foreach ($friends as $friend) {
                    $ids[] = $friend->user_id;
                }
                $params['users'] = $ids;
            }
        }

        $this->view->postedby = $this->_getParam('postedby', 1);
        $this->view->userComment = $this->_getParam('userComment', 1);
        $this->view->statistics = $this->_getParam('statistics', array("likeCount", "reviewCount"));
        $this->view->truncationDescription = $this->_getParam('truncationDescription', 100);
        $params['ratingType'] = $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
        $params['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id');
        if (empty($listingtype_id)) {
            $params['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
        }

        if (!empty($listingtype_id)) {
            $this->view->category_id = $params['category_id'] = $this->_getParam('hidden_category_id');
            $params['subcategory_id'] = $this->_getParam('hidden_subcategory_id');
            $params['subsubcategory_id'] = $this->_getParam('hidden_subsubcategory_id');
        }

        $params['interval'] = $interval = $this->_getParam('interval', 'overall');
        $params['paginator'] = 1;

        $this->view->detactLocation = $params['detactLocation'] = $this->_getParam('detactLocation', 0);
        if ($listingtype_id && $this->view->detactLocation) {
            $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
        }
        if ($this->view->detactLocation) {
            $this->view->defaultLocationDistance = $params['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);
            $params['latitude'] = $this->_getParam('latitude', 0);
            $params['longitude'] = $this->_getParam('longitude', 0);

            if (!$this->_getParam('autoload', true) || ($this->_getParam('autoload', true) && empty($params['latitude']) && empty($params['longitude']))) {

                $cookieLocation = Engine_Api::_()->seaocore()->getMyLocationDetailsCookie();
                $params['latitude'] = !empty($cookieLocation['latitude']) ? $cookieLocation['latitude'] : $this->_getParam('latitude', 0);
                $params['longitude'] = !empty($cookieLocation['longitude']) ? $cookieLocation['longitude'] : $this->_getParam('longitude', 0);
                $this->view->defaultLocationDistance = $params['defaultLocationDistance'] = !empty($cookieLocation['locationmiles']) && $params['defaultLocationDistance'] == 1000 ? $cookieLocation['locationmiles'] : $this->_getParam('defaultLocationDistance', 1000);
            }
        }

        //GET LISTINGS
        $this->view->listings = $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->listingsBySettings($params);
        $this->view->totalCount = $paginator->getTotalItemCount();

        $paginator->setCurrentPageNumber($this->_getParam('contentpage', 1));
        $paginator->setItemCountPerPage($params['limit']);
        //DON'T RENDER IF RESULTS IS ZERO
        if ($this->view->totalCount <= 0) {
            return $this->setNoRender();
        }

        $this->view->countPage = $paginator->count();
        if ($this->view->params['noOfTimes'] > $this->view->countPage)
            $this->view->params['noOfTimes'] = $this->view->countPage;

        $this->view->show_buttons = $this->_getParam('show_buttons', array("wishlist", "compare", "comment", "like", 'share', 'facebook', 'twitter', 'pinit'));

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    }

}
