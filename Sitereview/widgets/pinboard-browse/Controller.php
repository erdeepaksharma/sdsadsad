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
class Sitereview_Widget_PinboardBrowseController extends Seaocore_Content_Widget_Abstract {

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
                $this->getElement()->removeDecorator('Title');
            }
        } else {
            $this->view->is_ajax_load = $this->_getParam('is_ajax_load', false);
            if ($this->_getParam('contentpage', 1) > 1) {
                $this->getElement()->removeDecorator('Title');
                $this->getElement()->removeDecorator('Container');
            }
        }

        $params = $this->view->params;
        $params['itemWidth'] = $this->_getParam('itemWidth', 237);
        $params['limit'] = $this->_getParam('itemCount', 12);
        $this->view->postedby = $this->_getParam('postedby', 1);
        $this->view->userComment = $this->_getParam('userComment', 1);
        $this->view->statistics = $this->_getParam('statistics', array("likeCount", "reviewCount"));
        $this->view->truncationDescription = $this->_getParam('truncationDescription', 100);
        $params['ratingType'] = $this->view->ratingType = $this->_getParam('ratingType', 'rating_avg');
        $params['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id');
        if (empty($listingtype_id)) {
            $params['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
        }

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        $params['paginator'] = 1;

        $this->view->detactLocation = $params['detactLocation'] = $this->_getParam('detactLocation', 0);
        if ($listingtype_id && $this->view->detactLocation) {
            $this->view->detactLocation = Engine_Api::_()->sitereview()->enableLocation($listingtype_id);
        }

        if ($this->view->detactLocation) {
            $this->view->defaultLocationDistance = $params['defaultLocationDistance'] = $this->_getParam('defaultLocationDistance', 1000);
            $params['latitude'] = $this->_getParam('latitude', 0);
            $params['longitude'] = $this->_getParam('longitude', 0);
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->view->params = $params = array_merge($request->getParams(), $params);

        //GET VIEWER DETAILS
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->viewer_id = $viewer->getIdentity();

        $values = array();
        if (!empty($params)) {
            $values = array_merge($values, $params);
        }

        $form = new Sitereview_Form_Search(array('type' => 'sitereview_listing', 'listingTypeId' => $listingtype_id));

        if (!empty($params)) {
            $form->populate($params);
        }

        $this->view->formValues = $form->getValues();

        $values = array_merge($values, $form->getValues());

        //GET LISITNG FOR PUBLIC PAGE SET VALUE
        $values['type'] = 'browse';

        if (@$values['show'] == 2) {

            //GET AN ARRAY OF FRIEND IDS
            $friends = $viewer->membership()->getMembers();

            $ids = array();
            foreach ($friends as $friend) {
                $ids[] = $friend->user_id;
            }

            $values['users'] = $ids;
        }

        $assign = 1;
        if (is_string($values)) {
            if ('_' == substr($values, 0, 1)) {
                $assign = 0;
            }
        }

        if ($assign) {
            if(isset($values['autoload'])) {
                unset($values['autoload']);
            }
            $this->view->assign($values);
        }

        //CUSTOM FIELD WORK
        $customFieldValues = array_intersect_key($values, $form->getFieldElements());
        
        $row = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->getFieldsOptions('sitereview_listtype_' . $listingtype_id, 'show');
        if ($viewer->getIdentity() && !empty($row) && !empty($row->display) && $form->show->getValue() == 3 && !isset($_GET['show'])) {
            @$values['show'] = 3;
        }

        //GET LISTINGS
        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($values, $customFieldValues);
        $this->view->totalCount = $paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($this->_getParam('contentpage', 1));
        $paginator->setItemCountPerPage($params['limit']);

        $this->view->countPage = $paginator->count();
        if (isset($this->view->params['noOfTimes']) && $this->view->params['noOfTimes'] > $this->view->countPage) {
            $this->view->params['noOfTimes'] = $this->view->countPage;
        }

        $this->view->show_buttons = $this->_getParam('show_buttons', array("wishlist", "compare", "comment", "like", 'share', 'facebook', 'twitter', 'pinit'));
    }

}
