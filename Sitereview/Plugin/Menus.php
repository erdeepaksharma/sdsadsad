<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Menus.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Plugin_Menus {

    public function canCreateSitereviews($row) {

        //MUST BE LOGGED IN USER
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer || !$viewer->getIdentity()) {
            return false;
        }

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //MUST BE ABLE TO VIEW LISTINGS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view_listtype_$listingtype_id")) {
            return false;
        }

        //MUST BE ABLE TO CRETE LISTINGS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "create_listtype_$listingtype_id")) {
            return false;
        }

        return true;
    }

    public function canViewSitereviews($row) {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //MUST BE ABLE TO VIEW LISTINGS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view_listtype_$listingtype_id")) {
            return false;
        }

        return true;
    }

    public function canViewBrosweReview($row) {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //MUST BE ABLE TO VIEW WISHLISTS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view")) {
            return false;
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $listingtype_id = $request->getParam('listingtype_id');
        if (($listingtype_id && $listingtype_id > 0 ) || ($listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount()) == 1) {
            if (!($listingtype_id && $listingtype_id > 0 ) && $listingTypeCount == 1)
                $listingtype_id = 1;
            Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
            $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
            if (empty($listingtypeArray->reviews) || empty($listingtypeArray->allow_review))
                return false;
        }
        if ($listingtype_id > 0) {
            $route['route'] = 'sitereview_review_browse_listtype_' . $listingtype_id;
            if ('sitereview' == $request->getModuleName() &&
                    'review' == $request->getControllerName() &&
                    'browse' == $request->getActionName()) {
                $route['active'] = true;
            }
            return $route;
        }
        return true;
    }

    public function canViewCategories($row) {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //MUST BE ABLE TO VIEW WISHLISTS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view")) {
            return false;
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $listingtype_id = $request->getParam('listingtype_id');
        if (($listingtype_id && $listingtype_id > 0 ) || ($listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount()) == 1) {
            if (!($listingtype_id && $listingtype_id > 0 ) && $listingTypeCount == 1)
                $listingtype_id = 1;

            $route['route'] = 'sitereview_review_categories_' . $listingtype_id;
            $route['action'] = 'categories';
            if ('sitereview' == $request->getModuleName() &&
                    'index' == $request->getControllerName() &&
                    'categories' == $request->getActionName()) {
                $route['active'] = true;
            } else {
                $route['active'] = false;
            }
            return $route;
        }
        return true;
    }

    public function canViewWishlist($row) {

        //FAVOURITE FUNCTIONALITY SHOULD BE DISABLED
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //MUST BE ABLE TO VIEW WISHLISTS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view")) {
            return false;
        }

        //MUST BE ABLE TO VIEW WISHLISTS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', $viewer, "view")) {
            return false;
        }

        $listingtype_id = 0;
        $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();
        if ($listingTypeCount == 1) {
            $listingtype_id == 1;
        } else {
            $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
        }

        if (!empty($listingtype_id)) {
            Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
            $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
            if (empty($listingtypeArray->wishlist)) {
                return false;
            }
        }

        return true;
    }

    public function canViewEditors($row) {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //MUST BE ABLE TO VIEW LISTINGS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view")) {
            return false;
        }

        $editorsCount = Engine_Api::_()->getDbTable('editors', 'sitereview')->getEditorsCount(0);

        if ($editorsCount <= 0) {
            return false;
        }

        $listingtype_id = 0;
        $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();
        if ($listingTypeCount == 1) {
            $listingtype_id == 1;
        } else {
            $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
        }

        if (!empty($listingtype_id)) {
            Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
            $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
            if ($listingtypeArray->reviews == 2 || empty($listingtypeArray->reviews)) {
                return false;
            }
        }

        return true;
    }

    public function userProfileWishlist() {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //MUST BE ABLE TO VIEW WISHLISTS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', $viewer, "view")) {
            return false;
        }

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('user')) {
            return false;
        }

        $user = Engine_Api::_()->core()->getSubject('user');

        $favourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);
        if ($favourite) {

            if ($viewer->getIdentity() != $user->getIdentity()) {
                return false;
            }

            $wishlist_id = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->recentWishlistId($viewer->getIdentity());
            $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

            if (empty($wishlist))
                return false;

            return array(
                'class' => 'buttonlink',
                'route' => 'sitereview_wishlist_view',
                'label' => Zend_Registry::get('Zend_Translate')->_('My Favourites'),
/*                'icon' => Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/icons/wishlist.png',*/
                'params' => array(
                    'wishlist_id' => $wishlist_id,
                    'slug' => $wishlist->getSlug(),
                ),
            );
        }
        else {
            $wishlist_id = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->getRecentWishlistId($user->user_id);

            if (!empty($wishlist_id)) {
                return array(
                    'class' => 'buttonlink',
                    'route' => 'sitereview_wishlist_general',
/*                    'icon' => Zend_Registry::get('Zend_View')->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/icons/wishlist.png',*/
                    'params' => array(
                        'text' => $user->getTitle(),
                    ),
                );
            } else {
                return false;
            }
        }
    }

    public function sitereviewGutterEdit($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //AUTHORIZATION CHECK
        if (!$sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id")) {
            return false;
        }

        return array(
            'class' => 'buttonlink seaocore_icon_edit',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'edit',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditoverview($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        if (empty($listingType->overview)) {
            return false;
        }

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (!$sitereview->authorization()->isAllowed($viewer, 'overview_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "overview"))
                return false;
        }

        return array(
            'class' => 'buttonlink sitereview_gutter_editoverview',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'overview',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditstyle($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (!$sitereview->authorization()->isAllowed($viewer, 'style_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        return array(
            'class' => 'buttonlink sitereview_gutter_editstyle',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'editstyle',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditlocation($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        //IF LOCATION SETTING IS ENABLED
        if (!Engine_Api::_()->sitereview()->enableLocation($sitereview->listingtype_id)) {
            return false;
        }

        //START PACKAGE WORK
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "map"))
                return false;
        }
        //END PACKAGE WORK        

        return array(
            'class' => 'buttonlink sitereview_gutter_editlocation',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'editlocation',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditcontact($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (!$sitereview->authorization()->isAllowed($viewer, 'contact_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }
        
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
        
        if(!$listingType->contact_detail)
            return false;

        return array(
            'class' => 'buttonlink sitereview_gutter_editcontact',
            'route' => "sitereview_dashboard_listtype_$listingtype_id",
            'action' => 'contact',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterWhereToBuy($row) {
        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (!$sitereview->allowWhereToBuy()) {
            return false;
        }

        return array(
            'class' => 'buttonlink sitereview_gutter_editwheretobuy',
            'route' => "sitereview_priceinfo_listtype_$listingtype_id",
            'action' => 'index',
            'params' => array(
                'id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditmetadetails($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];
        $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);


        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (empty($listingType->metakeyword)) {
            return false;
        }

        if (!$sitereview->authorization()->isAllowed($viewer, 'metakeyword_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        return array(
            'class' => 'buttonlink sitereview_gutter_editmetadetails',
            'route' => "sitereview_dashboard_listtype_$listingtype_id",
            'action' => 'meta-detail',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditPhotos($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo") && Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id"))
                $allowPhotoUpload = 1;
            else
                $allowPhotoUpload = 0;

            if (!$allowPhotoUpload)
                return false;
        } else {
            if (!Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id"))
                return false;
        }

        return array(
            'class' => 'buttonlink sitereview_gutter_editphotos',
            'route' => "sitereview_albumspecific_listtype_$listingtype_id",
            'action' => 'editphotos',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterEditVideos($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!$sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id)) {
            return false;
        }

        $allowVideoUpload = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer);

        if (!$allowVideoUpload)
            return false;

        return array(
            'class' => 'buttonlink sitereview_gutter_editvideos',
            'route' => "sitereview_videospecific_listtype_$listingtype_id",
            'action' => 'edit',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterShare() {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }

        return array(
            'class' => 'smoothbox seaocore_icon_share buttonlink',
            'route' => 'default',
            'params' => array(
                'module' => 'activity',
                'controller' => 'index',
                'action' => 'share',
                'type' => $sitereview->getType(),
                'id' => $sitereview->getIdentity(),
                'format' => 'smoothbox',
            ),
        );
    }

    public function sitereviewGutterMessageowner($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER INFO
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //RETURN IF NOT AUTHORIZED
        if (empty($viewer_id)) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //SHOW MESSAGE OWNER LINK TO USER IF MESSAGING IS ENABLED FOR THIS LEVEL
        $showMessageOwner = 0;
        $showMessageOwner = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'auth');
        if ($showMessageOwner != 'none') {
            $showMessageOwner = 1;
        }

        //RETURN IF NOT AUTHORIZED
        if ($sitereview->owner_id == $viewer_id || empty($viewer_id) || empty($showMessageOwner)) {
            return false;
        }

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        return array(
            'class' => 'smoothbox icon_sitereviews_messageowner buttonlink',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'messageowner',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterTfriend($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        return array(
            'class' => 'smoothbox buttonlink seaocore_icon_tellafriend',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'tellafriend',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterPrint($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        return array(
            'class' => 'buttonlink seaocore_icon_print',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'print',
            'target' => '_blank',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterPublish($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF NOT AUTHORIZED
        if ($sitereview->draft != 1 || ($viewer_id != $sitereview->owner_id)) {
            return false;
        }

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        return array(
            'class' => 'buttonlink smoothbox icon_sitereview_publish',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'publish',
            'params' => array(
                'listing_id' => $sitereview->getIdentity()
            ),
        );
    }

    public function sitereviewGutterEditorPick($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
        
        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];
        
        $select_alternatives = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'select_alternatives');

        //GET VIEWER DETAILS
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        $isEditor = Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($viewer_id, $sitereview->listingtype_id);

        if ((empty($isEditor) && $viewer->level_id != 1) && ($sitereview->owner_id != $viewer_id || !$select_alternatives)) {
                return false;
        }

        return array(
            'class' => 'buttonlink seaocore_icon_add',
            'route' => "sitereview_editor_general_listtype_$listingtype_id",
            'action' => 'similar-items',
            'params' => array(
                'listing_id' => $sitereview->getIdentity()
            ),
        );
    }

    public function sitereviewGutterReview() {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //NON LOGGED IN USER CAN'T BE THE EDITOR
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        if (empty($viewer_id)) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $sitereview->listingtype_id;

        //CHECK EDITOR REVIEW IS ALLOWED OR NOT
        $allow_editor_review = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'reviews');
        if (empty($allow_editor_review) || $allow_editor_review == 2) {
            return false;
        }

        //SHOW THIS LINK ONLY EDITOR FOR THIS LISTING TYPE
        $isEditor = Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($viewer_id, $listingtype_id);
        if (empty($isEditor)) {
            return false;
        }

        //EDITOR REVIEW HAS BEEN POSTED OR NOT
        $params = array();
        $params['resource_id'] = $sitereview->listing_id;
        $params['resource_type'] = $sitereview->getType();
        $params['type'] = 'editor';
        $params['notIncludeStatusCheck'] = 1;
        $isEditorReviewed = Engine_Api::_()->getDbTable('reviews', 'sitereview')->canPostReview($params);

        $params = array();
        $params['listing_id'] = $sitereview->getIdentity();
        if (!empty($isEditorReviewed)) {

            $editorreview = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.editorreview', 0);
            $review = Engine_Api::_()->getItem('sitereview_review', $isEditorReviewed);
            if (empty($editorreview) && $viewer_id != $review->owner_id) {
                return false;
            }

            $label = Zend_Registry::get('Zend_Translate')->_('Edit an Editor Review');
            $action = 'edit';
            $params['review_id'] = $isEditorReviewed;
        } else {
            $label = Zend_Registry::get('Zend_Translate')->_('Write an Editor Review');
            $action = 'create';
        }

        return array(
            'label' => $label,
            'class' => 'buttonlink icon_sitereviews_review',
            'route' => "sitereview_extended_listtype_$listingtype_id",
            'controller' => 'editor',
            'action' => $action,
            'params' => $params,
        );
    }

    public function sitereviewGutterClose() {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF NOT AUTHORIZED
        if ($viewer_id != $sitereview->owner_id) {
            return false;
        }

        $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
        $title_singular = ucfirst($listingType->title_singular);

        if (!empty($sitereview->closed)) {
            $label = Zend_Registry::get('Zend_Translate')->_('Open');
            $class = 'buttonlink icon_sitereviews_open';
        } else {
            $label = Zend_Registry::get('Zend_Translate')->_('Close');
            $class = 'buttonlink seaocore_icon_close';
        }

        $label = sprintf($label, $title_singular);

        return array(
            'label' => $label,
            'class' => $class,
            'route' => 'sitereview_specific_listtype_' . $sitereview->listingtype_id,
            'params' => array(
                'action' => 'close',
                'listing_id' => $sitereview->getIdentity()
            ),
        );
    }

    public function sitereviewGutterDelete($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //LISTING DELETE PRIVACY
        $can_delete = $sitereview->authorization()->isAllowed(null, "delete_listtype_$listingtype_id");

        //AUTHORIZATION CHECK
        if (empty($can_delete) || empty($viewer_id)) {
            return false;
        }

        return array(
            'class' => 'buttonlink seaocore_icon_delete',
            'route' => 'sitereview_specific_listtype_' . $listingtype_id,
            'params' => array(
                'action' => 'delete',
                'listing_id' => $sitereview->getIdentity()
            ),
        );
    }

    public function sitereviewGutterReport() {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        if (empty($viewer_id)) {
            return false;
        }

        //GET SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        return array(
            'class' => 'smoothbox buttonlink seaocore_icon_report',
            'route' => 'default',
            'params' => array(
                'module' => 'core',
                'controller' => 'report',
                'action' => 'create',
                'route' => 'default',
                'subject' => $sitereview->getGuid()
            ),
        );
    }

    public function sitereviewGutterWishlist($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing') || Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //AUTHORIZATION CHECK
        if (!empty($sitereview->draft) || empty($sitereview->search) || empty($sitereview->approved)) {
            return false;
        }

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //CHECK LISTINGTYPE WISHLIST ALLOWED OR NOT
        $wishlistAllow = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'wishlist');
        if (empty($wishlistAllow)) {
            return false;
        }

        //AUTHORIZATION CHECK
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', $viewer, 'view')) {
            return false;
        }

        return array(
            'class' => 'buttonlink smoothbox seaocore_icon_add',
            'route' => "sitereview_wishlist_general",
            'action' => 'add',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterChangephoto($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            return false;
        }

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //AUTHORIZATION CHECK
        if (!$sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id")) {
            return false;
        }

        return array(
            'class' => 'buttonlink icon_sitereview_edit',
            'route' => "sitereview_specific_listtype_$listingtype_id",
            'action' => 'change-photo',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    public function sitereviewGutterSubscription($row) {
        //CHECK VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        if ($sitereview->owner_id == $viewer->getIdentity()) {
            return false;
        }

        $listingtype_id = $sitereview->listingtype_id;

        //GET LISTING TYPE ID
        $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        //CHECK EDITOR REVIEW IS ALLOWED OR NOT
        $allow_subscription = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'subscription');
        if (empty($allow_subscription)) {
            return false;
        }

        //MODIFY PARAMS
        $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');
        $owner = Engine_Api::_()->getItem('user', $sitereview->owner_id);

        if (!$subscriptionTable->checkSubscription($owner, $viewer, $listingtype_id)) {
            $label = Zend_Registry::get('Zend_Translate')->_('Subscribe');
            $class = 'buttonlink smoothbox icon_sitereview_subscribe';
            $action = 'add';
        } else {
            $label = Zend_Registry::get('Zend_Translate')->_('Unsubscribe');
            $class = 'buttonlink smoothbox icon_sitereview_unsubscribe';
            $action = 'remove';
        }

        return array(
            'label' => $label,
            'class' => $class,
            'route' => "sitereview_subscription_listtype_$listingtype_id",
            'action' => $action,
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
            ),
        );
    }

    // Wishlist Profile page Gutter 
    public function onMenuInitialize_sitereviewWishlistGutterEdit($row) {
        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
            return false;
        }
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }

        //GET LISTING SUBJECT
        $subject = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

        if ($viewer_id != $subject->owner_id)
            return false;

        return array(
            'class' => 'buttonlink smoothbox seaocore_icon_edit',
            'route' => "sitereview_wishlist_general",
            'action' => 'edit',
            'params' => array(
                'wishlist_id' => $subject->getIdentity(),
            ),
        );
    }

    // Wishlist Profile page Gutter 
    public function onMenuInitialize_sitereviewWishlistGutterDelete($row) {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
            return false;
        }
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }

        //GET LISTING SUBJECT
        $subject = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

        if ($viewer_id != $subject->owner_id)
            return false;


        return array(
            'class' => 'buttonlink smoothbox seaocore_icon_delete',
            'route' => "sitereview_wishlist_general",
            'action' => 'delete',
            'params' => array(
                'wishlist_id' => $subject->getIdentity(),
            ),
        );
    }

    public function onMenuInitialize_sitereviewWishlistGutterShare() {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
            return false;
        }
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }
        //GET SUBJECT
        $subject = Engine_Api::_()->core()->getSubject('sitereview_wishlist');
        return array(
            'class' => 'smoothbox seaocore_icon_share buttonlink',
            'route' => 'default',
            'params' => array(
                'module' => 'activity',
                'controller' => 'index',
                'action' => 'share',
                'type' => $subject->getType(),
                'id' => $subject->getIdentity(),
                'format' => 'smoothbox',
            ),
        );
    }

    public function onMenuInitialize_sitereviewWishlistGutterReport() {

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
            return false;
        }
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }

        //GET SUBJECT
        $subject = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

        return array(
            'class' => 'smoothbox buttonlink seaocore_icon_report',
            'route' => 'default',
            'params' => array(
                'module' => 'core',
                'controller' => 'report',
                'action' => 'create',
                'route' => 'default',
                'subject' => $subject->getGuid()
            ),
        );
    }

    public function onMenuInitialize_sitereviewWishlistGutterTfriend($row) {
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        if (empty($viewer_id)) {
            return false;
        }

        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
            return false;
        }

        //GET SUBJECT
        $subject = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

        return array(
            'class' => 'smoothbox buttonlink seaocore_icon_tellafriend',
            'route' => "sitereview_wishlist_general",
            'params' => array(
                'action' => 'tell-a-friend',
                'type' => $subject->getType(),
                'wishlist_id' => $subject->getIdentity(),
            ),
        );
    }

    public function onMenuInitialize_sitereviewWishlistGutterCreate($row) {
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }

        return array(
            'class' => 'buttonlink smoothbox sr_icon_wishlist_add',
            'route' => "sitereview_wishlist_general",
            'action' => 'create',
        );
    }

    public function canCreateWishlist($row) {
        //GET VIEWER ID
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //RETURN IF VIEWER IS EMPTY
        if (empty($viewer_id)) {
            return false;
        }

        return true;
    }

    public function onMenuInitialize_SitereviewTopicWatch() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();

        $listingtype_id = $sitereview->listingtype_id;
        $isWatching = null;
        $canPost = $sitereview->authorization()->isAllowed($viewer, "topic_listtype_$listingtype_id");
        if (!$canPost && !$viewer->getIdentity())
            return false;

        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
        $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $sitereview->getIdentity())
                ->where('topic_id = ?', $subject->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetchColumn(0)
        ;

        if (false === $isWatching) {
            $isWatching = null;
        } else {
            $isWatching = (bool) $isWatching;
        }

        if (!$isWatching) {
            return array(
                'label' => 'Watch Topic',
                'route' => 'default',
                'class' => 'smoothbox ui-btn-default ui-btn-action',
                'params' => array(
                    'module' => 'sitereview',
                    'controller' => 'topic',
                    'action' => 'watch',
                    'watch' => 1,
                    'topic_id' => $subject->getIdentity(),
                    'listingtype_id' => $listingtype_id
                )
            );
        } else {
            return array(
                'label' => 'Stop Watching Topic',
                'route' => 'default',
                'class' => 'smoothbox ui-btn-default ui-btn-action',
                'params' => array(
                    'module' => 'sitereview',
                    'controller' => 'topic',
                    'action' => 'watch',
                    'watch' => 0,
                    'topic_id' => $subject->getIdentity(),
                    'listingtype_id' => $listingtype_id
                )
            );
        }
    }

    public function onMenuInitialize_SitereviewTopicRename() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id");
        if (!$canEdit && !$viewer->getIdentity())
            return false;

        return array(
            'label' => 'Rename',
            'route' => 'default',
            'class' => 'smoothbox ui-btn-default ui-btn-action',
            'params' => array(
                'module' => 'sitereview',
                'controller' => 'topic',
                'action' => 'rename',
                'topic_id' => $subject->getIdentity(),
                'listingtype_id' => $listingtype_id
            )
        );
    }

    public function onMenuInitialize_SitereviewTopicDelete() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id");

        if (!$canEdit && !$viewer->getIdentity())
            return false;

        return array(
            'label' => 'Delete Topic',
            'route' => 'default',
            'class' => 'smoothbox ui-btn-default ui-btn-danger',
            'params' => array(
                'module' => 'sitereview',
                'controller' => 'topic',
                'action' => 'delete',
                'topic_id' => $subject->getIdentity(),
                'listingtype_id' => $listingtype_id,
                'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
            )
        );
    }

    public function onMenuInitialize_SitereviewTopicOpen() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id");

        if (!$canEdit && !$viewer->getIdentity())
            return false;

        if (!$subject->closed) {
            return array(
                'label' => 'Close',
                'route' => 'default',
                'class' => 'smoothbox ui-btn-default ui-btn-action',
                'params' => array(
                    'module' => 'sitereview',
                    'controller' => 'topic',
                    'action' => 'close',
                    'topic_id' => $subject->getIdentity(),
                    'closed' => 1,
                    'listingtype_id' => $listingtype_id,
                    'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
                )
            );
        } else {
            return array(
                'label' => 'Open',
                'route' => 'default',
                'class' => 'smoothbox ui-btn-default ui-btn-action',
                'params' => array(
                    'module' => 'sitereview',
                    'controller' => 'topic',
                    'action' => 'close',
                    'topic_id' => $subject->getIdentity(),
                    'closed' => 0,
                    'listingtype_id' => $listingtype_id,
                    'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
                )
            );
        }
    }

    public function onMenuInitialize_SitereviewTopicSticky() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id");

        if (!$canEdit && !$viewer->getIdentity())
            return false;

        if (!$subject->sticky) {
            return array(
                'label' => 'Make Sticky',
                'route' => 'default',
                'class' => 'smoothbox ui-btn-default ui-btn-action',
                'params' => array(
                    'module' => 'sitereview',
                    'controller' => 'topic',
                    'action' => 'sticky',
                    'topic_id' => $subject->getIdentity(),
                    'sticky' => 1,
                    'listingtype_id' => $listingtype_id,
                    'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
                )
            );
        } else {
            return array(
                'label' => 'Remove Sticky',
                'route' => 'default',
                'class' => 'smoothbox ui-btn-default ui-btn-action',
                'params' => array(
                    'module' => 'sitereview',
                    'controller' => 'topic',
                    'action' => 'sticky',
                    'topic_id' => $subject->getIdentity(),
                    'sticky' => 0,
                    'listingtype_id' => $listingtype_id,
                    'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
                )
            );
        }
    }

    public function onMenuInitialize_SitereviewPhotoEdit($row) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent()->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $canEdit = $subject->getCollection()->authorization()->isAllowed(null, "edit_listtype_$listingtype_id");
        if (!$canEdit && !$viewer->getIdentity() && $subject->user_id != $viewer->getIdentity())
            return false;

        return array(
            'label' => 'Edit',
            'route' => "sitereview_photo_extended_listtype_$listingtype_id",
            'class' => 'ui-btn-action smoothbox',
            'params' => array(
                'action' => 'edit',
                'photo_id' => $subject->getIdentity(),
            //'tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab')
            )
        );
    }

    public function onMenuInitialize_SitereviewPhotoDelete($row) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent()->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $canDelete = $subject->getCollection()->authorization()->isAllowed(null, "delete_listtype_$listingtype_id");
        if (!$canDelete && !$viewer->getIdentity() && $subject->user_id != $viewer->getIdentity())
            return false;

        return array(
            'label' => 'Delete',
            'route' => "sitereview_photo_extended_listtype_$listingtype_id",
            'class' => 'ui-btn-danger smoothbox',
            'params' => array(
                'action' => 'remove',
                'photo_id' => $subject->getIdentity()
            )
        );
    }

    public function onMenuInitialize_SitereviewPhotoShare($row) {

        $subject = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!SEA_PHOTOLIGHTBOX_SHARE && !$viewer->getIdentity())
            return false;

        return array(
            'label' => 'Share',
            'class' => 'ui-btn-action smoothbox',
            'route' => 'default',
            'params' => array(
                'module' => 'activity',
                'action' => 'share',
                'type' => $subject->getType(),
                'id' => $subject->getIdentity(),
            )
        );
    }

    public function onMenuInitialize_SitereviewPhotoReport($row) {

        $subject = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!SEA_PHOTOLIGHTBOX_REPORT && !$viewer->getIdentity())
            return false;

        return array(
            'label' => 'Report',
            'class' => 'ui-btn-action smoothbox',
            'route' => 'default',
            'params' => array(
                'module' => 'core',
                'controller' => 'report',
                'action' => 'create',
                'subject' => $subject->getGuid(),
            )
        );
    }

    public function onMenuInitialize_SitereviewPhotoProfile($row) {

        $subject = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!SEA_PHOTOLIGHTBOX_MAKEPROFILEPHOTO && !$viewer->getIdentity())
            return false;

        return array(
            'label' => 'Make Profile Photo',
            'route' => 'user_extended',
            'class' => 'ui-btn-action smoothbox',
            'params' => array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'external-photo',
                'photo' => $subject->getGuid()
            )
        );
    }

    //SITEMOBILE PAGE VIDEO MENUS
    public function onMenuInitialize_SitereviewVideoAdd($row) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;

        $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');
        $paginator = Engine_Api::_()->getDbTable('clasfvideos', 'sitereview')->getListingVideos($sitereview->listing_id, 1, $type_video);
        $totalVideo = $paginator->getTotalItemCount();

        $canCreate = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer, $totalVideo, $uploadVideo = 1);
        if (!$canCreate)
            return false;

        return array(
            'label' => 'Add Video',
            'route' => "sitereview_video_create_listtype_$listingtype_id",
            'class' => 'ui-btn-action',
            'params' => array(
                'listing_id' => $sitereview->listing_id,
                'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
            )
        );
    }

    public function onMenuInitialize_SitereviewVideoEdit($row) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;

        $can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id");
        if (!$can_edit && $viewer->getIdentity() != $subject->owner_id)
            return false;

        return array(
            'label' => 'Edit Video',
            'route' => "sitereview_video_edit_listtype_$listingtype_id",
            'class' => 'ui-btn-action',
            'params' => array(
                'video_id' => $subject->video_id,
                'listing_id' => $sitereview->listing_id,
                'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
            )
        );
    }

    public function onMenuInitialize_SitereviewVideoDelete($row) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;

        $can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$listingtype_id");
        if (!$can_edit && $viewer->getIdentity() != $subject->owner_id)
            return false;

        return array(
            'label' => 'Delete Video',
            'route' => "sitereview_video_delete_listtype_$listingtype_id",
            'class' => 'ui-btn-danger',
            'params' => array(
                'video_id' => $subject->video_id,
                'listing_id' => $sitereview->listing_id,
                'content_id' => Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id')
            )
        );
    }

    //SITEMOBILE PAGE REVIEW MENUS
    public function onMenuInitialize_SitereviewReviewUpdate($row) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
        if (empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
            return;
        }
        //GET VIEWER   
        $viewer_id = $viewer->getIdentity();
        $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;
        if (empty($create_review)) {
            return;
        }
        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

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

        $autorizationApi = Engine_Api::_()->authorization();
        if ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id") && empty($hasPosted)) {
            $createAllow = 1;
        } elseif ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id") && !empty($hasPosted)) {
            $createAllow = 2;
        } else {
            $createAllow = 0;
        }

        if ($createAllow != 2)
            return;

        return array(
            'label' => 'Update your Review',
            'action' => 'update',
            'route' => "sitereview_user_general_listtype_$listingtype_id",
            'class' => 'ui-btn-action',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
                'review_id' => $subject->getIdentity(),
                'tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab')
            )
        );
    }

    //SITEMOBILE PAGE REVIEW MENUS
    public function onMenuInitialize_SitereviewReviewCreate($row) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
        if (empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1) {
            return;
        }

        //GET VIEWER   
        $viewer_id = $viewer->getIdentity();
        $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;
        if (empty($create_review)) {
            return;
        }

        //GET REVIEW TABLE
        $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
        if ($viewer_id) {
            $level_id = $viewer->level_id;
            $params = array();
            $params['resource_id'] = $sitereview->listing_id;
            $params['resource_type'] = $sitereview->getType();
            $params['viewer_id'] = $viewer_id;
            $params['type'] = 'user';
            $hasPosted = $reviewTable->canPostReview($params);
        } else {
            $hasPosted = 0;
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        $autorizationApi = Engine_Api::_()->authorization();
        if ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id") && empty($hasPosted)) {
            $createAllow = 1;
        } elseif ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id") && !empty($hasPosted)) {
            $createAllow = 2;
        } else {
            $createAllow = 0;
        }

        if ($createAllow != 1)
            return;
        return array(
            'label' => 'Write a Review',
            'action' => 'create',
            'route' => "sitereview_user_general_listtype_$listingtype_id",
            'class' => 'ui-btn-action',
            'params' => array(
                'listing_id' => $sitereview->getIdentity(),
                'tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab')
            )
        );
    }

    public function onMenuInitialize_SitereviewReviewShare($row) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        //GET VIEWER   
        $viewer_id = $viewer->getIdentity();
        $coreApi = Engine_Api::_()->getApi('settings', 'core');
        $sitereview_share = $coreApi->getSetting('sitereview.share', 1);
        //array('action' => 'share', 'module' => 'seaocore', 'controller' => 'activity', 'type' => $review->getType(), 'id' => $review->review_id, 'format' => 'smoothbox', 'not_parent_refresh' => 1)
        if ($sitereview_share && $sitereview->owner_id != 0):
            return array(
                'label' => 'Share Review',
                'route' => "default",
                'action' => 'share',
                'module' => 'activity', 'controller' => 'index',
                'class' => 'ui-btn-action smoothbox',
                'params' => array(
                    'id' => $subject->getIdentity(),
                    'type' => $subject->getType()
                )
            );
        endif;
        return;
    }

    public function onMenuInitialize_SitereviewReviewEmail($row) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        //GET VIEWER   
        $viewer_id = $viewer->getIdentity();
        $coreApi = Engine_Api::_()->getApi('settings', 'core');
        $sitereview_email = $coreApi->getSetting('sitereview.email', 1);

        if ($sitereview_email):
            return array(
                'label' => 'Email Review',
                'route' => "sitereview_user_general_listtype_$listingtype_id",
                'action' => 'email',
                'class' => 'ui-btn-action smoothbox',
                'params' => array(
                    'listing_id' => $sitereview->getIdentity(),
                    'review_id' => $subject->getIdentity(),
                    'tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab')
                )
            );
        endif;
        return;
    }

    public function onMenuInitialize_SitereviewReviewDelete($row) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        //GET VIEWER   
        $viewer_id = $viewer->getIdentity();
        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }
        $can_delete = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");
        if (!empty($can_delete) && ($can_delete != 1 || $viewer_id == $sitereview->owner_id)) :
            return array(
                'label' => 'Delete Review',
                'route' => "sitereview_user_general_listtype_$listingtype_id",
                'action' => 'delete',
                'class' => 'ui-btn-danger smoothbox',
                'params' => array(
                    'listing_id' => $sitereview->getIdentity(),
                    'review_id' => $subject->getIdentity(),
                    'tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab')
                )
            );
        endif;
        return;
    }

//
    public function onMenuInitialize_SitereviewReviewReport($row) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $sitereview = $subject->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        //GET VIEWER   
        $viewer_id = $viewer->getIdentity();
        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }
        $coreApi = Engine_Api::_()->getApi('settings', 'core');
        $sitereview_report = $coreApi->getSetting('sitereview.report', 1);
        if ($sitereview_report && $viewer_id):
            return array(
                'label' => 'Report',
                'route' => 'default',
                'class' => 'ui-btn-action smoothbox',
                'params' => array(
                    'module' => 'core',
                    'controller' => 'report',
                    'action' => 'create',
                    'subject' => $subject->getGuid(),
                // 'format' => 'smoothbox'
                )
            );
        endif;
        return;
    }

    public function canViewClaims($row) {

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        //MUST BE ABLE TO VIEW LISTINGS
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view_listtype_$listingtype_id")) {
            return false;
        }

        $viewer_id = $viewer->getIdentity();
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $authorizationTable = Engine_Api::_()->getItemTable('authorization_level');
            $authorization = $authorizationTable->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
            if (!empty($authorization))
                $level_id = $authorization->level_id;
        }

        $allow_claim = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$listingtype_id");

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        if (empty($listingtypeArray->claimlink) || empty($allow_claim)) {
            return false;
        }

        $table = Engine_Api::_()->getDbtable('listings', 'sitereview');
        $tablename = $table->info('name');
        $select = $table->select()->from($tablename, array('count(*) as count'))
                ->where($tablename . '.closed = ?', '0')
                ->where($tablename . '.listingtype_id = ?', $listingtype_id)
                ->where($tablename . '.approved = ?', '1')
                ->where($tablename . '.draft = ?', '0')
                ->where($tablename . '.creation_date <= NOW()');
        if (Engine_Api::_()->sitereview()->hasPackageEnable())
            $select->where($tablename . '.expiration_date  > ?', date("Y-m-d H:i:s"));
        $countListing = $select
                ->query()
                ->fetchColumn();
        if (!$countListing) {
            return false;
        }
        return true;
    }

    public function sitereviewGutterClaim($row) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET LISTING TYPE ID
        $listingtype_id = $row->params['listingtype_id'];

        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $authorizationTable = Engine_Api::_()->getItemTable('authorization_level');
            $authorization = $authorizationTable->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
            if (!empty($authorization))
                $level_id = $authorization->level_id;
        }

        $allow_claim = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$listingtype_id");
        if (empty($allow_claim))
            return false;

        $subject = Engine_Api::_()->core()->getSubject();
        if ($subject->getType() !== 'sitereview_listing')
            return false;

        $userClaimValue = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($subject->listing_id, 'userclaim');

        if (empty($userClaimValue) || $subject->owner_id == $viewer_id)
            return false;

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
        $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

        if (empty($listingtypeArray->claimlink)) {
            return false;
        }

        $listmemberclaimsTable = Engine_Api::_()->getDbtable('listmemberclaims', 'sitereview');
        $listmemberclaimsTablename = $listmemberclaimsTable->info('name');
        $listingCount = $listmemberclaimsTable->select()->from($listmemberclaimsTablename, array('count(*) as total_count'))
                ->where('listingtype_id = ?', $listingtype_id)
                ->where('user_id = ?', $subject->owner_id)
                ->query()
                ->fetchColumn();

        if ($listingCount) {
            $total_count = 1;
        }

        if (empty($total_count))
            return false;

        if ($viewer_id) {
            return array(
                'class' => 'smoothbox buttonlink icon_sitereviews_claim',
                'route' => 'sitereview_claim_listtype_' . $listingtype_id,
                'params' => array(
                    'action' => 'claim-listing',
                    'listingtype_id' => $listingtype_id,
                    'listing_id' => $subject->getIdentity(),
                ),
            );
        } else {
            return array(
                'class' => 'buttonlink icon_sitereviews_claim',
                'route' => 'user_login',
                'params' => array(
                    'return_url' => '64-' . base64_encode($subject->getHref()),
                ),
            );
        }
    }

}