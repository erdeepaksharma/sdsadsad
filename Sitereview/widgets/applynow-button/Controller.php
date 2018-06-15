<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_ApplynowButtonController extends Seaocore_Content_Widget_Abstract {

    public function indexAction() {

        $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        $this->view->widgetTitle = $this->_getParam('title', null);

        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return $this->setNoRender();
        }

        if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
            $listing_guid = $sitereview->getGuid();
            $this->view->listing_profile_page = 1;
        }

        $this->getElement()->removeDecorator('Title');
        $this->getElement()->removeDecorator('Container');

        $sitereview = Engine_Api::_()->getItemByGuid($listing_guid);
        $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
        $this->view->listing_id = $listing_id = $sitereview->listing_id;

        $apply = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'allow_apply');
        if (empty($apply)) {
            return $this->setNoRender();
        }
        
        $user = Engine_Api::_()->user()->getViewer();
        $allowApplyNow = Engine_Api::_()->authorization()->getPermission($user->level_id, 'sitereview_listing', "apply_listtype_$listingtype_id");
        if (empty($allowApplyNow)) {
            return $this->setNoRender();
        }
    }

}
