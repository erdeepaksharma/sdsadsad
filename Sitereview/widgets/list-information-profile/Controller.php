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
class Sitereview_Widget_ListInformationProfileController extends Seaocore_Content_Widget_Abstract {

    public function indexAction() {
        $this->_mobileAppFile = true;
        //DONT RENDER IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return $this->setNoRender();
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
        //DONT RENDER IF NOT AUTHORIZED
        $this->view->sitereview_like = true;

        //GET SUBJECT
        $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
        $this->view->listingType = $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
        $listingtype_id = $sitereview->listingtype_id;
        $this->view->listing_singular_upper = strtoupper($listingType->title_singular);
        $this->view->listingtype_id = $sitereview->listingtype_id;
        $this->view->resource_id = $resource_id = $sitereview->getIdentity();
        $this->view->resource_type = $resource_type = $sitereview->getType();

        $this->view->showContent = $this->_getParam('showContent', array("photo", "title", "postedBy", "postedDate", "viewCount", "likeCount", "commentCount", "tags", "endDate", "location", "phone", "email", "website", "price", "description", "newlabel", "sponsored", "featured", "photosCarousel", "compare", "wishlist", "reviewCreate", "endDate"));
        $this->view->actionLinks = $this->_getParam('actionLinks', 1);
        $this->view->truncationDescription = $this->_getParam('truncationDescription', 300);

        //IF FACEBOOK PLUGIN IS THERE THEN WE WILL SHOW DEFAULT FACEBOOK LIKE BUTTON.
        $fbmodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('facebookse');
        $default_like = 1;
        $this->view->success_showFBLikeButton = 0;
        $checkVersion = Engine_Api::_()->sitereview()->checkVersion($fbmodule->version, '4.2.7p1');
        if (!empty($fbmodule) && !empty($fbmodule->enabled) && $checkVersion == 1) {
            $this->view->success_showFBLikeButton = Engine_Api::_()->facebookse()->showFBLikeButton('sitereview');
            $default_like = 2;
        }
        $this->view->like_button = $this->_getParam('like_button', $default_like);

        //GET CATEGORY TABLE
        $this->view->tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
        if (!empty($sitereview->category_id)) {
            $this->view->category_name = $this->view->tableCategory->getCategory($sitereview->category_id)->category_name;

            if (!empty($sitereview->subcategory_id)) {
                $this->view->subcategory_name = $this->view->tableCategory->getCategory($sitereview->subcategory_id)->category_name;

                if (!empty($sitereview->subsubcategory_id)) {
                    $this->view->subsubcategory_name = $this->view->tableCategory->getCategory($sitereview->subsubcategory_id)->category_name;
                }
            }
        }

        //GET LISTING TAGS
        $this->view->sitereviewTags = $sitereview->tags()->getTagMaps();
        $this->view->can_edit = $sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id);
        $this->view->ratingType = $this->_getParam('ratingType', 'rating_both');
        //POPULATE FORM
        $row = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getOtherinfo($sitereview->listing_id);

        $this->view->email = $this->view->phone = $this->view->website = '';
        if (!empty($row)) {
            $this->view->email = $row->email;
            $this->view->phone = $row->phone;
            $this->view->website = $row->website;
        }

        // START PACKAGE WORK
        $showReviewButton = 0;
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            //PACKAGE IS ENABLED FOR LISTING TYPES
            if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "user_review"))
                $showReviewButton = 1;
            // END PACKAGE WORK
        } else {
            //PACKAGE IS DISABLED FOR LISTING TYPES THEN CHECK MEMBER LEVEL SETTINGS
            //GET REVIEW TABLE
            $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
            if ($viewer_id) {
                $params = array();
                $params['resource_id'] = $sitereview->listing_id;
                $params['resource_type'] = $sitereview->getType();
                $params['viewer_id'] = $viewer_id;
                $params['type'] = 'user';
                $hasPosted = $reviewTable->canPostReview($params);
            } else {
                $hasPosted = 0;
            }

            if (!empty($viewer_id)) {
                $level_id = $viewer->level_id;
            } else {
                $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
            }

            $autorizationApi = Engine_Api::_()->authorization();

            if ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id") && empty($hasPosted)) {

                $showReviewButton = 1;
            } elseif ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id") && !empty($hasPosted)) {

                $showReviewButton = 2;
            } else {

                $showReviewButton = 0;
            }
        }

        $this->view->create_review = ($sitereview->owner_id == $viewer->getIdentity()) ? $listingType->allow_owner_review : 1;
        if (($listingType->reviews == 0 || $listingType->reviews == 1) || empty($this->view->showContent) || !in_array('reviewCreate', $this->view->showContent) || empty($showReviewButton)) {
            $this->view->create_review = 0;
        }
        //GET NAVIGATION
        $this->view->gutterNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_gutter_listtype_$listingType->listingtype_id");
        if (!empty($listingType->price)) {
            $this->view->price = $sitereview->price;
        }
        $this->view->owner = Engine_Api::_()->core()->getSubject()->getOwner();
    }

}
