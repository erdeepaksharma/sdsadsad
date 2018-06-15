<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    IndexController.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_IndexController extends Siteapi_Controller_Action_Standard {
    /*
     * Store listing info
     */

    protected $_listingType;

    /*
     * Store listing type id
     */
    protected $_listingTypeId;

    public function init() {
        if (!Zend_Registry::isRegistered('Zend_Translate'))
            Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        // Set listing type array
        $this->_listingTypeId = $this->getRequestParam('listingtype_id', 1);

        if (empty($this->_listingTypeId))
            $this->_listingTypeId = $this->getRequestParam('listing_id', 1);

        if ($this->_listingTypeId != -1 && !empty($this->_listingTypeId)) {
            $this->_listingType = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->setListingTypeInRegistry($this->_listingTypeId);

            if (empty($this->_listingType))
                $this->respondWithError('no_record');

            if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$this->_listingTypeId")->isValid())
                $this->respondWithError('unauthorized');
        }

        // Update listing expiration.
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
            if ((Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewpaidlisting.task.updateexpiredlistings') + 900) <= time()) {
                Engine_Api::_()->sitereviewpaidlisting()->updateExpiredListings($this->_listingTypeId);
            }
        }
    }

    /*
     * Calling of adv search form
     * 
     * @return array
     */
    public function searchFormAction() {
        $this->validateRequestMethod();

        // Set the view
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        $restapilocation = $this->getRequestParam('restapilocation', null);
        $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getListingSearchForm($this->_listingTypeId, $restapilocation);

        $this->respondWithSuccess($response, true);
    }

    /*
     * Calling of browse listings
     * 
     * @return array
     */
    public function indexAction() {
        $this->validateRequestMethod();
        // Set view
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $level_id = !empty($viewer_id) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;

        $listing_plural_uc = @ucfirst($this->_listingType->title_plural);

        $response = $customFieldValues = array();
        $params = $this->_getAllParams();

        // Set the view type
        $params['type'] = 'browse';

        $params['listingtype_id'] = $this->_listingTypeId;

        // Set location of app dashboard
//        if (isset($_GET['restapilocation']) && !empty($_GET['restapilocation']))
//            $params['location'] = $_GET['restapilocation'];
//
//        // Set location of adv search form
//        if (isset($_GET['location']) && !empty($_GET['location']))
//            $params['location'] = $_GET['location'];

        if (isset($this->_listingType->location) && !empty($this->_listingType->location)) {
            // Set location of app dashboard
            if (isset($_GET['restapilocation']) && !empty($_GET['restapilocation']))
                $params['location'] = $_GET['restapilocation'];

            // Set location of adv search form
            if (isset($_GET['location']) && !empty($_GET['location']))
                $params['location'] = $_GET['location'];
        }
        else {
            if (isset($params['location']) && !empty($params['location']))
                unset($params['location']);
        }

        // Set the order by value
        if (!isset($params['orderby']) && empty($params['orderby']))
            $params['orderby'] = 'creation_date';

        // Set the default show closed value
        if (!isset($params['showClosed']) && empty($params['showClosed']))
            $params['showClosed'] = 1;

        // Set the min price value
        if (isset($params['min_price']) && !empty($params['min_price']))
            $params['price']['min'] = $params['min_price'];

        // Set the max price value
        if (isset($params['max_price']) && !empty($params['max_price']))
            $params['price']['max'] = $params['max_price'];

        // Set the page value
        if (!isset($params['page']) && empty($params['page']))
            $params['page'] = 1;

        // Set the page limit
        if (!isset($params['limit']) && empty($params['limit']))
            $params['limit'] = 20;

        // Set profile fields in array
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $profileFields = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getSearchProfileFields();
            if (isset($profileFields) && !empty($profileFields)) {
                foreach ($profileFields[$params['category_id']] as $element) {
                    if (isset($params[$element['name']]))
                        $customFieldValues[$element['name']] = $params[$element['name']];
                }
            }
        }

        // Set friends array in case of friends listing search.
        if (isset($params['show']) && $params['show'] == 2) {
            $friends = $viewer->membership()->getMembers();

            $ids = array();
            foreach ($friends as $friend) {
                $ids[] = $friend->user_id;
            }

            $params['users'] = $ids;
        }

        $row = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->getFieldsOptions('sitereview_listtype_' . $this->_listingTypeId, 'show');
        if ($viewer->getIdentity() && !empty($row) && !empty($row->display) && $params['show'] == 3 && !isset($_GET['show'])) {
            $params['show'] = 3;
        }

        try {
            // Seperate paginator for featured & sponsored events 
            if (isset($params['listing_filter']) && !empty($params['listing_filter']) && ($params['listing_filter'] == 'featured' || $params['listing_filter'] == 'sponsored')) {
                if ($params['listing_filter'] == 'featured') {
                    $params['featured'] = 1;
                } elseif ($params['listing_filter'] == 'sponsored') {
                    $params['sponsored'] = 1;
                }

                $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListing('', $params);
            } elseif ($params['listing_filter'] == 'favourite') {
                if (isset($params['wishlist_id']) && !empty($params['wishlist_id']))
                    $paginator = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->wishlistListings($params['wishlist_id'], $params);
                else {
                    $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
                    $wishlist_id = $wishlistTable->recentWishlistId($viewer_id, $this->_listingTypeId);
                    $paginator = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->wishlistListings($wishlist_id, $params);
                }
            } else {
                $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($params, $customFieldValues);
            }
            $paginator->setItemCountPerPage($params['limit']);
            $paginator = $paginator->setCurrentPageNumber($params['page']);
            $response['totalItemCount'] = $paginator->getTotalItemCount();
            $response['canCreate'] = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "create_listtype_$this->_listingTypeId");
            $response['packagesEnabled'] = $this->_sitereviewPackageEnabled($this->_listingTypeId);

            foreach ($paginator as $listing) {
                $listingArray = $listing->toArray();

                // Set the price & currency 
                if (isset($listingArray['price']) && $listingArray['price'] > 0) {
                    $listingArray['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
                } else if (isset($listingArray['price']))
                    unset($listingArray['price']);

                // Set owner images & title
                if (isset($listing->owner_id) && !empty($listing->owner_id)) {
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($listing, true);
                    $listingArray = array_merge($listingArray, $getContentImages);

                    $listingArray["owner_title"] = $listing->getOwner()->getTitle();
                }

                // Set listing images  
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($listing);
                $listingArray = array_merge($listingArray, $getContentImages);

                $isAllowedView = $listing->authorization()->isAllowed($viewer, 'view');
                $listingArray["allow_to_view"] = empty($isAllowedView) ? 0 : 1;

                $isAllowedEdit = $listing->authorization()->isAllowed($viewer, 'edit');
                $listingArray["edit"] = empty($isAllowedEdit) ? 0 : 1;

                $isAllowedDelete = $listing->authorization()->isAllowed($viewer, 'delete');
                $listingArray["delete"] = empty($isAllowedDelete) ? 0 : 1;
                $response['response'][] = $listingArray;
            }

            $this->respondWithSuccess($response, true);
        } catch (Exception $ex) {
            $this->respondWithError('internal_server_error', $ex->getMessage());
        }
    }

    /*
     * Calling of manage listings
     * 
     * @return array
     */
    public function manageAction() {
        $this->validateRequestMethod();

        // Set view
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            $this->respondWithError('unauthorized');

        $params = $this->_getAllParams();

        $params['type'] = 'manage';
        $params['user_id'] = $viewer_id;
        $params['listingtype_id'] = $this->_listingTypeId;
        $params['getGutterMenu'] = $this->getRequestParam('listingtype_id', 1);

        if (!isset($params['category_id']))
            $params['category_id'] = 0;

        if (!isset($params['subcategory_id']))
            $params['subcategory_id'] = 0;

        if (!isset($params['subsubcategory_id']))
            $params['subsubcategory_id'] = 0;

        // Set the order by value
        if (!isset($params['orderby']) && empty($params['orderby']))
            $params['orderby'] = 'listing_id';

        // Set the default show closed value
        if (!isset($params['showClosed']) && empty($params['showClosed']))
            $params['showClosed'] = 1;

        // Set the min price value
        if (!isset($params['min_price']) && empty($params['min_price']))
            $params['price']['min'] = $params['min_price'];

        // Set the max price value
        if (!isset($params['max_price']) && empty($params['max_price']))
            $params['price']['max'] = $params['max_price'];

        // Set the page value
        if (!isset($params['page']) && empty($params['page']))
            $params['page'] = 1;

        // Set the page limit
        if (!isset($params['limit']) && empty($params['limit']))
            $params['limit'] = 20;

        // Privacy check
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "create_listtype_$this->_listingTypeId")->isValid())
            $this->respondWithError('unauthorized');

        // Set can create variable
        $response['canCreate'] = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "create_listtype_$this->_listingTypeId")->checkRequire();
        $response['packagesEnabled'] = $this->_sitereviewPackageEnabled($this->_listingTypeId);

        try {
            $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($params);
            $paginator->setItemCountPerPage($params['limit']);
            $paginator->setCurrentPageNumber($params['page']);
            $response['totalItemCount'] = $paginator->getTotalItemCount();

            foreach ($paginator as $listing) {
                $listingArray = $listing->toArray();

                // Set owner listing images & title
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($listing, true);
                $listingArray = array_merge($listingArray, $getContentImages);
                $listingArray["owner_title"] = $listing->getOwner()->getTitle();

                // Set Listing Images  
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($listing);
                $listingArray = array_merge($listingArray, $getContentImages);

                if (empty($listingArray['price']))
                    unset($listingArray['price']);

                // Set the price & currency 
                if (isset($listingArray['price']) && $listingArray['price'] > 0)
                    $listingArray['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');

                $isAllowedView = $listing->authorization()->isAllowed($viewer, 'view');
                $listingArray["allow_to_view"] = empty($isAllowedView) ? 0 : 1;

                $isAllowedEdit = $listing->authorization()->isAllowed($viewer, 'edit');
                $listingArray["edit"] = empty($isAllowedEdit) ? 0 : 1;

                $isAllowedDelete = $listing->authorization()->isAllowed($viewer, 'delete');
                $listingArray["delete"] = empty($isAllowedDelete) ? 0 : 1;

                // listing package based info
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    if (!$listing->getPackage()->isFree()) {
                        if ($listing->status == "initial") {
                            $listingArray['paymentStatus'] = "Not made";
                        } elseif ($listing->status == "active") {
                            $listingArray['paymentStatus'] = "Yes";
                        } else {
                            $listingArray['paymentStatus'] = $listing->status;
                        }
                    }
                }

                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    if (!empty($listing->approved_date)) {
                        $listingArray['packageFirstApprovedOn'] = $listing->approved_date;
                        $expiry = $listing->getExpiryDate();
                        if ($expiry !== "Expired" && $expiry !== $this->translate('Never Expires'))
                            $listingArray['packageExpiredOn'] = $listing->approved_date;
                    }
                }

                // Set gutter menu array
                if (isset($params['getGutterMenu']) && !empty($params['getGutterMenu']))
                    $listingArray['gutterMenus'] = $this->_getListingMenus($listing, $this->_listingTypeId);

                $response['listings'][] = $listingArray;
            }

            $this->respondWithSuccess($response, true);
        } catch (Exception $ex) {
            $this->respondWithError('internal_server_error', $ex->getMessage());
        }
    }

    /*
     * Calling of packages browse
     * 
     * @return array
     */
    public function packagesAction() {
        //GET LISTING TYPE ID
        $listingtype_id = $this->_listingTypeId;

        $title = $this->_listingType->title_plural;

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        $page = $this->_getParam('page', 1);

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        Engine_Api::_()->getApi('Core', 'siteapi')->setLocal();

        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "create_listtype_$listingtype_id")->isValid())
            $this->respondWithError('unauthorized');

        // Return count 0 if module not enabled
        if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
            $bodyParams['getTotalItemCount'] = 0;
            $this->respondWithSuccess($bodyParams);
        }

        // Return count 0 if package settings are diabled from admin side
        if (!Engine_Api::_()->sitereviewpaidlisting()->hasPackageEnable() && empty($this->_listingType->package)) {
            $bodyParams['getTotalItemCount'] = 0;
            $this->respondWithSuccess($bodyParams);
        }

        // Return count 0 if only 1 package is enabled & is free package.
        $packageCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageCount($listingtype_id);
        if ($packageCount == 1) {
            $package = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getEnabledPackage($listingtype_id);
            if (($package->price == '0.00')) {
                $bodyParams['getTotalItemCount'] = 0;
                $this->respondWithSuccess($bodyParams);
            }
        }

        $listing_singular_lc = strtolower($this->_listingType->title_singular);
        $listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $listing_plural_lc = strtolower($this->_listingType->title_plural);
        $show_editor = $this->_listingType->show_editor;
        $package_view = $this->_listingType->package_view;
        $allow_review = $this->_listingType->reviews;
        $overview = $this->_listingType->overview;
        $wishlist = $this->_listingType->wishlist;
        $location = $this->_listingType->location;
        $package_description = $this->_listingType->package_description;
        try {

            $paginator = Engine_Api::_()->getDbtable('packages', 'sitereviewpaidlisting')->getPackagesSql($viewer_id, $listingtype_id);
            $paginator = $paginator->setCurrentPageNumber($page);
            $bodyParams["getTotalItemCount"] = $paginator->getTotalItemCount();
            foreach ($paginator as $package) {
                $packageShowArray = array();
                if (isset($package->package_id) && !empty($package->package_id))
                    $packageShowArray['package_id'] = $package->package_id;

                if (isset($package->title) && !empty($package->title)) {
                    $packageShowArray['title']['label'] = $this->translate('Title');
                    $packageShowArray['title']['value'] = $this->translate($package->title);
                }
                if (isset($package->description) && !empty($package->description)) {
                    $packageShowArray['description']['label'] = $this->translate("Description");
                    $packageShowArray['description']['value'] = $this->translate($package->description);
                }
                if ($package->price > 0.00) {
                    $packageShowArray['price']['label'] = $this->translate('Price');
                    $packageShowArray['price']['value'] = (int) $package->price;
                    $packageShowArray['price']['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
                } else {
                    $packageShowArray['price']['label'] = $this->translate('Price');
                    $packageShowArray['price']['value'] = $this->translate('FREE');
                }

                $packageShowArray['billing_cycle']['label'] = $this->translate('Billing Cycle');
                $packageShowArray['billing_cycle']['value'] = $package->getBillingCycle();

                $packageShowArray['duration']['label'] = $this->translate("Duration");
                $packageShowArray['duration']['value'] = $package->getPackageQuantity();

                if ($package->featured == 1) {
                    $packageShowArray['featured']['label'] = $this->translate('Featured');
                    $packageShowArray['featured']['value'] = $this->translate('Yes');
                } else {
                    $packageShowArray['featured']['label'] = $this->translate('Featured');
                    $packageShowArray['featured']['value'] = $this->translate('No');
                }

                if ($package->sponsored == 1) {
                    $packageShowArray['Sponsored']['label'] = $this->translate('Sponsored');
                    $packageShowArray['Sponsored']['value'] = $this->translate('Yes');
                } else {
                    $packageShowArray['Sponsored']['label'] = $this->translate('Sponsored');
                    $packageShowArray['Sponsored']['value'] = $this->translate('No');
                }

                if ($overview && (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "overview_listtype_" . "$listingtype_id"))) {
                    if ($package->overview == 1) {
                        $packageShowArray['rich_overview']['label'] = $this->translate('Rich Overview');
                        $packageShowArray['rich_overview']['value'] = $this->translate('Yes');
                    } else {
                        $packageShowArray['rich_overview']['label'] = $this->translate('Rich Overview');
                        $packageShowArray['rich_overview']['value'] = $this->translate('No');
                    }
                }

                if (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "video_listtype_" . "$listingtype_id")) {
                    if ($package->video == 1) {
                        if ($package->video_count) {
                            $packageShowArray['videos']['label'] = $this->translate('Videos');
                            $packageShowArray['videos']['value'] = $package->video_count;
                        } else {
                            $packageShowArray['videos']['label'] = $this->translate('Videos');
                            $packageShowArray['videos']['value'] = $this->translate("Unlimited");
                        }
                    } else {
                        $packageShowArray['videos']['label'] = $this->translate('Videos');
                        $packageShowArray['videos']['value'] = $this->translate('No');
                    }
                }

                if (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "photo_listtype_" . "$listingtype_id")) {
                    if ($package->photo == 1) {
                        if ($packagem->photo_count) {
                            $packageShowArray['photos']['label'] = $this->translate('Photos');
                            $packageShowArray['photos']['value'] = $package->photo_count;
                        } else {
                            $packageShowArray['photos']['label'] = $this->translate('Photos');
                            $packageShowArray['photos']['value'] = $this->translate("Unlimited");
                        }
                    } else {
                        $packageShowArray['photos']['label'] = $this->translate('Photos');
                        $packageShowArray['photos']['value'] = $this->translate('No');
                    }
                }

                if ($location) {
                    if ($package->map == 1) {
                        $packageShowArray['map']['label'] = $this->translate('Map');
                        $packageShowArray['map']['value'] = $this->translate('Yes');
                    } else {
                        $packageShowArray['map']['label'] = $this->translate('Map');
                        $packageShowArray['map']['value'] = $this->translate('No');
                    }
                }

                if (!empty($allow_review) && $allow_review != 1 && (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_create_listtype_" . "$listingtype_id"))) {
                    if ($package->user_review == 1) {
                        $packageShowArray['review']['label'] = $this->translate('User Review');
                        $packageShowArray['review']['value'] = $this->translate('Yes');
                    } else {
                        $packageShowArray['review']['label'] = $this->translate('User Review');
                        $packageShowArray['review']['value'] = $this->translate('No');
                    }
                }

                if ($wishlist) {
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite')) {
                        $packageShowArray['wishlist']['label'] = $this->translate('Favourite');
                    } else {
                        $packageShowArray['wishlist']['label'] = $this->translate('Wishlist');
                    }
                    if ($package->wishlist == 1) {
                        $packageShowArray['wishlist']['value'] = $this->translate('Yes');
                    } else {
                        $packageShowArray['wishlist']['value'] = $this->translate('No');
                    }
                }

                $packageArray['package'] = $packageShowArray;
                $tempMenu = array();
                $tempMenu[] = array(
                    'label' => $this->translate('Create Entry'),
                    'name' => 'create',
                    'url' => 'listings/create',
                    'urlParams' => array(
                        'package_id' => $package->package_id,
                        'listingtype_id' => $listingtype_id
                    )
                );
                $tempMenu[] = array(
                    'label' => $this->translate('Package Info'),
                    'name' => 'package_info',
                    'url' => 'listings/packages',
                    'urlParams' => array(
                        'package_id' => $package->package_id,
                        'listingtype_id' => $listingtype_id
                    )
                );

                $packageArray['menu'] = $tempMenu;
                $bodyParams['response'][] = $packageArray;
            }
            if (isset($bodyParams) && !empty($bodyParams))
                $this->respondWithSuccess($bodyParams);
        } catch (Exception $ex) {
            $this->respondWithError('internal_server_error', $ex->getMessage());
        }
    }

    //ACTION FOR PACKAGE UPGRADE CONFIRMATION
    public function upgradePackageAction() {

        //USER VALIDATION
        if (!$this->_helper->requireUser()->isValid())
            return;

        $listing_singular_lc = lcfirst($this->_listingType->title_singular);
        $listing_singular_uc = ucfirst($this->_listingType->title_singular);

        //GET LISTING ID, LISTING OBJECT AND THEN CHECK VALIDATIONS
        $listingtype_id = $this->_listingTypeId;

        $title = $this->_listingType->title_plural;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        $page = $this->_getParam('page', 1);

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        Engine_Api::_()->getApi('Core', 'siteapi')->setLocal();

        $listing_id = $this->_getParam('listing_id');
        if (empty($listing_id)) {
            $this->respondWithError('no_record');
        }
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (empty($sitereview)) {
            $this->respondWithError('no_record');
        }
        if (isset($sitereview->package_id) && !empty($sitereview->package_id))
            $currentPackage = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id);


        if ($this->getRequest()->isGet()) {
            try {

                $packages_select = Engine_Api::_()->getDbtable('packages', 'sitereviewpaidlisting')->getPackagesSql($viewer_id, $listingtype_id, 1)
                        ->where("update_list = ?", 1)
                        ->where("enabled = ?", 1)
                        ->where("package_id <> ?", $sitereview->package_id);
                $paginator = Zend_Paginator::factory($packages_select);

                $paginator = $paginator->setCurrentPageNumber($page);
                $bodyParams["getTotalItemCount"] = $paginator->getTotalItemCount();
                foreach ($paginator as $package) {
                    $packageShowArray = array();

                    if (isset($package->package_id) && !empty($package->package_id))
                        $packageShowArray['package_id'] = $package->package_id;

                    if (isset($package->title) && !empty($package->title)) {
                        $packageShowArray['title']['label'] = $this->translate('Title');
                        $packageShowArray['title']['value'] = $this->translate($package->title);
                    }
                    if (isset($package->description) && !empty($package->description)) {
                        $packageShowArray['description']['label'] = $this->translate("Description");
                        $packageShowArray['description']['value'] = $this->translate($package->description);
                    }
                    if ($package->price > 0.00) {
                        $packageShowArray['price']['label'] = $this->translate('Price');
                        $packageShowArray['price']['value'] = (int) $package->price;
                        $packageShowArray['price']['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
                    } else {
                        $packageShowArray['price']['label'] = $this->translate('Price');
                        $packageShowArray['price']['value'] = $this->translate('FREE');
                    }

                    $packageShowArray['billing_cycle']['label'] = $this->translate('Billing Cycle');
                    $packageShowArray['billing_cycle']['value'] = $package->getBillingCycle();

                    $packageShowArray['duration']['label'] = $this->translate("Duration");
                    $packageShowArray['duration']['value'] = $package->getPackageQuantity();

                    if ($package->featured == 1) {
                        $packageShowArray['featured']['label'] = $this->translate('Featured');
                        $packageShowArray['featured']['value'] = $this->translate('Yes');
                    } else {
                        $packageShowArray['featured']['label'] = $this->translate('Featured');
                        $packageShowArray['featured']['value'] = $this->translate('No');
                    }

                    if ($package->sponsored == 1) {
                        $packageShowArray['Sponsored']['label'] = $this->translate('Sponsored');
                        $packageShowArray['Sponsored']['value'] = $this->translate('Yes');
                    } else {
                        $packageShowArray['Sponsored']['label'] = $this->translate('Sponsored');
                        $packageShowArray['Sponsored']['value'] = $this->translate('No');
                    }

                    if ($overview && (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "overview_listtype_" . "$listingtype_id"))) {
                        if ($package->overview == 1) {
                            $packageShowArray['rich_overview']['label'] = $this->translate('Rich Overview');
                            $packageShowArray['rich_overview']['value'] = $this->translate('Yes');
                        } else {
                            $packageShowArray['rich_overview']['label'] = $this->translate('Rich Overview');
                            $packageShowArray['rich_overview']['value'] = $this->translate('No');
                        }
                    }

                    if (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "video_listtype_" . "$listingtype_id")) {
                        if ($package->video == 1) {
                            if ($package->video_count) {
                                $packageShowArray['videos']['label'] = $this->translate('Videos');
                                $packageShowArray['videos']['value'] = $package->video_count;
                            } else {
                                $packageShowArray['videos']['label'] = $this->translate('Videos');
                                $packageShowArray['videos']['value'] = $this->translate("Unlimited");
                            }
                        } else {
                            $packageShowArray['videos']['label'] = $this->translate('Videos');
                            $packageShowArray['videos']['value'] = $this->translate('No');
                        }
                    }

                    if (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "photo_listtype_" . "$listingtype_id")) {
                        if ($package->photo == 1) {
                            if ($packagem->photo_count) {
                                $packageShowArray['photos']['label'] = $this->translate('Photos');
                                $packageShowArray['photos']['value'] = $package->photo_count;
                            } else {
                                $packageShowArray['photos']['label'] = $this->translate('Photos');
                                $packageShowArray['photos']['value'] = $this->translate("Unlimited");
                            }
                        } else {
                            $packageShowArray['photos']['label'] = $this->translate('Photos');
                            $packageShowArray['photos']['value'] = $this->translate('No');
                        }
                    }

                    if ($location) {
                        if ($package->map == 1) {
                            $packageShowArray['map']['label'] = $this->translate('Map');
                            $packageShowArray['map']['value'] = $this->translate('Yes');
                        } else {
                            $packageShowArray['map']['label'] = $this->translate('Map');
                            $packageShowArray['map']['value'] = $this->translate('No');
                        }
                    }

                    if (!empty($allow_review) && $allow_review != 1 && (empty($level_id) || Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_create_listtype_" . "$listingtype_id"))) {
                        if ($package->user_review == 1) {
                            $packageShowArray['review']['label'] = $this->translate('User Review');
                            $packageShowArray['review']['value'] = $this->translate('Yes');
                        } else {
                            $packageShowArray['review']['label'] = $this->translate('User Review');
                            $packageShowArray['review']['value'] = $this->translate('No');
                        }
                    }

                    if ($wishlist) {
                        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite')) {
                            $packageShowArray['wishlist']['label'] = $this->translate('Favourite');
                        } else {
                            $packageShowArray['wishlist']['label'] = $this->translate('Wishlist');
                        }
                        if ($package->wishlist == 1) {
                            $packageShowArray['wishlist']['value'] = $this->translate('Yes');
                        } else {
                            $packageShowArray['wishlist']['value'] = $this->translate('No');
                        }
                    }

                    $packageArray['package'] = $packageShowArray;
                    $tempMenu = array();
                    $tempMenu[] = array(
                        'label' => $this->translate('Upgrade Package'),
                        'name' => 'upgrade_package',
                        'url' => 'listings/upgrade-package',
                        'urlParams' => array(
                            'package_id' => $package->package_id,
                            'listingtype_id' => $listingtype_id,
                            'listing_id' => $sitereview->getIdentity()
                        )
                    );
                    $tempMenu[] = array(
                        'label' => $this->translate('Package Info'),
                        'name' => 'package_info',
                        'url' => 'listings/upgrade-package',
                        'urlParams' => array(
                            'package_id' => $package->package_id,
                            'listingtype_id' => $listingtype_id,
                            'listing_id' => $sitereview->getIdentity()
                        )
                    );

                    $packageArray['menu'] = $tempMenu;
                    $bodyParams['response'][] = $packageArray;
                }
                if (isset($currentPackage) && !empty($currentPackage)) {
                    $bodyParams['currentPackage'] = $currentPackage->toArray();
                }

                if (isset($bodyParams) && !empty($bodyParams))
                    $this->respondWithSuccess($bodyParams);
            } catch (Exception $ex) {
                $this->respondWithError('internal_server_error', $ex->getMessage());
            }
        } elseif ($this->getRequest()->getPost()) {

            if (!empty($_POST['package_id'])) {
                $package_id = $this->_getParam('package_id');
                $package_chnage = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $package_id);

                if (empty($package_chnage) || !$package_chnage->enabled || (!empty($package_chnage->level_id) && !in_array($sitereview->getOwner()->level_id, explode(",", $package_chnage->level_id)))) {
                    $this->respondWithError('no_record');
                }
                $table = $sitereview->getTable();
                $db = $table->getAdapter();
                $db->beginTransaction();

                try {
                    $is_upgrade_package = true;
                    //APPLIED CHECKS BECAUSE CANCEL SHOULD NOT BE CALLED IF ALREADY CANCELLED 
                    if ($sitereview->status == 'active') {
                        $sitereview->cancel($is_upgrade_package);
                    }
                    $sitereview->package_id = $_POST['package_id'];
                    $package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id);

                    $sitereview->featured = $package->featured;
                    $sitereview->sponsored = $package->sponsored;
                    $sitereview->pending = 1;
                    $sitereview->expiration_date = new Zend_Db_Expr('NULL');
                    $sitereview->status = 'initial';
                    if (($package->isFree())) {
                        $sitereview->approved = $package->approved;
                    } else {
                        $sitereview->approved = 0;
                    }

                    if (!empty($sitereview->approved)) {
                        $sitereview->pending = 0;
                        $expirationDate = $package->getExpirationDate();
                        if (!empty($expirationDate))
                            $sitereview->expiration_date = date('Y-m-d H:i:s', $expirationDate);
                        else
                            $sitereview->expiration_date = '2250-01-01 00:00:00';

                        if (empty($sitereview->approved_date)) {
                            $sitereview->approved_date = date('Y-m-d H:i:s');
                            if ($sitereview->draft == 0 && $sitereview->search && time() >= strtotime($sitereview->creation_date)) {
                                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);
                                if ($action != null) {
                                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
                                }
                            }
                        }
                    }
                    $sitereview->save();
                    $db->commit();
                    $this->successResponseNoContent('no_content', true);
                } catch (Exception $e) {
                    $db->rollBack();
                }
            }
            /*  $this->_forward('success', 'utility', 'core', array(
              'smoothboxClose' => true,
              'format' => 'smoothbox',
              'parentRedirect' => $this->view->url(array('action' => 'update-package', 'listing_id' => $sitereview->listing_id), "sitereview_package_listtype_$sitereview->listingtype_id", true),
              'parentRedirectTime' => 15,
              'messages' => array(Zend_Registry::get('Zend_Translate')->_('The package for your Listing has been successfully changed.'))
              )); */
        }
    }

    /*
     * Calling of create listing
     * 
     * @return array
     */
    public function createAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            $this->respondWithError('unauthorized');

        $package_id = 0;
        $level_id = $viewer->level_id;

        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "create_listtype_$this->_listingTypeId")->isValid())
            $this->respondWithError('unauthorized');
        // Set view
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        if (_CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '1.7') || _CLIENT_TYPE == 'ios' && _IOS_VERSION >= '1.9')) {
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
                if (Engine_Api::_()->sitereviewpaidlisting()->hasPackageEnable() && !empty($this->_listingType->package)) {
                    $packageCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageCount($this->_listingTypeId);
                    if ($packageCount == 1) {
                        $package = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getEnabledPackage($this->_listingTypeId);
                        if (($package->price == '0.00')) {
                            $package_id = $package->package_id;
                            $package = Engine_Api::_()->getItemTable('sitereviewpaidlisting_package')->fetchRow(array('package_id = ?' => $package_id, 'listingtype_id = ?' => $this->_listingTypeId, 'enabled = ?' => '1'));
                        } else {
                            $package_id = $this->getRequestParam('package_id');
                            $package = Engine_Api::_()->getItemTable('sitereviewpaidlisting_package')->fetchRow(array('package_id = ?' => $package_id, 'listingtype_id = ?' => $this->_listingTypeId, 'enabled = ?' => '1'));
                            if (!isset($package) && empty($package))
                                $this->respondWithError('unauthorized', "Package_id is missing");
                        }
                    }
                    else {
                        $package_id = $this->getRequestParam('package_id');
                        if (!isset($package_id) && empty($package_id))
                            $this->respondWithError('unauthorized', "Package_id is missing");
                        $package = Engine_Api::_()->getItemTable('sitereviewpaidlisting_package')->fetchRow(array('package_id = ?' => $package_id, 'listingtype_id = ?' => $this->_listingTypeId, 'enabled = ?' => '1'));

                        if (!isset($package) && empty($package))
                            $this->respondWithError('unauthorized');
                    }
                }
                else {
                    $paginator = Engine_Api::_()->getDbtable('packages', 'sitereviewpaidlisting')->getPackagesSql($viewer_id, $this->_listingTypeId);
                    foreach ($paginator as $package) {
                        if ($package->isfree()) {
                            $package_id = $package->package_id;
                            $package = Engine_Api::_()->getItemTable('sitereviewpaidlisting_package')->fetchRow(array('package_id = ?' => $package_id, 'listingtype_id = ?' => $this->_listingTypeId, 'enabled = ?' => '1'));
                        } else {
                            continue;
                        }
                    }
                }
            } else {
                $package_id = 0;
            }
        } else {
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
                if (Engine_Api::_()->sitereviewpaidlisting()->hasPackageEnable() && !empty($this->_listingType->package)) {
                    $this->respondWithError('sitereview_package_error');
                } else {
                    $paginator = Engine_Api::_()->getDbtable('packages', 'sitereviewpaidlisting')->getPackagesSql($viewer_id, $this->_listingTypeId);
                    foreach ($paginator as $package) {
                        if ($package->isfree()) {
                            $package_id = $package->package_id;
                        } else {
                            continue;
                        }
                    }
                }
                if (isset($package_id) && $package_id != 0)
                    $package = Engine_Api::_()->getItemTable('sitereviewpaidlisting_package')->fetchRow(array('package_id = ?' => $package_id, 'listingtype_id = ?' => $this->_listingTypeId, 'enabled = ?' => '1'));
            }
        }

        if (!isset($package) && empty($package) && $package_id != 0)
            $this->respondWithError('unauthorized');

        // Check for user quota
        $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator(array(
            'user_id' => $viewer_id,
            'listingtype_id' => $this->_listingTypeId
        ));
        $count = $paginator->getTotalItemCount();

        $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "max_listtype_$this->_listingTypeId");

        if ($count >= $quota && !empty($quota))
            $this->respondWithError('listing_creation_quota_exceed');

//        $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
//        for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
//            $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
//        }
//        for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
//            $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
//        }
//        $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;
//        $bodyParams = array();


        if ($this->getRequest()->isGet()) {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getListingCreateForm($this->_listingTypeId);
            $this->respondWithSuccess($response);
        } else if ($this->getRequest()->isPost()) {
            $values = $data = $_REQUEST;

            foreach ($getForm['form'] as $element) {
                if (isset($_REQUEST[$element['name']]))
                    $values[$element['name']] = $_REQUEST[$element['name']];
            }

            // Listing title validation
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.duplicatetitle', 1)) {
                $isListingExists = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingColumn(array('listingtype_id' => $this->_listingTypeId, 'title' => $_POST['title']));

                if ($isListingExists) {
                    $error[] = $this->translate('Please choose the different listing title as listing with same title already exists.');
                    $this->respondWithValidationError('validation_fail', $error);
                }
            }

            // Form validation
            $listingValidators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getListingCreateFormValidators($values);
            $values['validators'] = $listingValidators;

            if (isset($values['category_id']) && empty($values['category_id']))
                unset($values['category_id']);

            $listingValidationMessage = $this->isValid($values);
            if (!@is_array($validationMessage) && isset($values['category_id'])) {
                $categoryIds = array();
                $categoryIds[] = $values['category_id'];
                if (isset($values['subcategory_id']) && !empty($values['subcategory_id'])) {
                    $categoryIds[] = $values['subcategory_id'];
                }
                if (isset($values['subsubcategory_id']) && !empty($values['subsubcategory_id'])) {
                    $categoryIds[] = $values['subsubcategory_id'];
                }
                // $categoryIds[] = $values['subcategory_id'];
                //$categoryIds[] = $values['subsubcategory_id'];  

                try {
                    $values['profile_type'] = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
                } catch (Exception $ex) {

                    $values['profile_type'] = 0;
                }

                // Profile fields validation
                if (isset($values['profile_type']) && !empty($values['profile_type'])) {
                    // START FORM VALIDATION
                    $profileFieldsValidators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getFieldsFormValidations($values);
                    $values['validators'] = $profileFieldsValidators;
                    $profileFieldsValidationMessage = $this->isValid($values);
                }
            }

            if (is_array($listingValidationMessage) && is_array($profileFieldsValidationMessage))
                $validationMessage = array_merge($listingValidationMessage, $profileFieldsValidationMessage);

            else if (is_array($listingValidationMessage))
                $validationMessage = $listingValidationMessage;
            else if (is_array($profileFieldsValidationMessage))
                $validationMessage = $profileFieldsValidationMessage;
            else
                $validationMessage = 1;

            if (!empty($validationMessage) && @is_array($validationMessage)) {
                $this->respondWithValidationError('validation_fail', $validationMessage);
            }

            $table = Engine_Api::_()->getItemTable('sitereview_listing');
            $db = $table->getAdapter();
            $db->beginTransaction();
            $user_level = $viewer->level_id;
            try {
                //Create sitereview
                if (!Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    $values = array_merge($_POST, array(
                        'listingtype_id' => $this->_listingTypeId,
                        'owner_type' => $viewer->getType(),
                        'owner_id' => $viewer_id,
                        'featured' => Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "featured_listtype_$this->_listingTypeId"),
                        'sponsored' => Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "sponsored_listtype_$this->_listingTypeId"),
                        'approved' => Engine_Api::_()->authorization()->getPermission($user_level, 'sitereview_listing', "approved_listtype_$this->_listingTypeId")
                    ));
                } else {
                    $values = array_merge($_POST, array(
                        'listingtype_id' => $this->_listingTypeId,
                        'owner_type' => $viewer->getType(),
                        'owner_id' => $viewer_id,
                        'featured' => $package->featured,
                        'sponsored' => $package->sponsored
                    ));

                    if ($package->isFree()) {
                        $values['approved'] = $package->approved;
                    } else
                        $values['approved'] = 0;
                }

                if (empty($values['subcategory_id'])) {
                    $values['subcategory_id'] = 0;
                }

                if (empty($values['subsubcategory_id'])) {
                    $values['subsubcategory_id'] = 0;
                }

                $expiry_setting = Engine_Api::_()->sitereview()->expirySettings($this->_listingTypeId);
                if ($expiry_setting == 1 && $values['end_date_enable'] == 1 && empty($values['end_date'])) {
                    $error[] = $this->translate('Please Select End Date');
                    $this->respondWithValidationError('validation_fail', $error);
                }
                if ($expiry_setting == 1 && $values['end_date_enable'] == 1) {
                    // Convert times
                    $oldTz = date_default_timezone_get();
                    date_default_timezone_set($viewer->timezone);
                    $end = strtotime($values['end_date']);
                    date_default_timezone_set($oldTz);
                    $values['end_date'] = date('Y-m-d H:i:s', $end);
                } elseif (isset($values['end_date'])) {
                    unset($values['end_date']);
                }

                if (Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
                    if (isset($values['networks_privacy']) && !empty($values['networks_privacy'])) {
                        if (in_array(0, $values['networks_privacy'])) {
                            unset($values['networks_privacy']);
                        }
                    }
                }

                $sitereview = $table->createRow();

                $sitereview->setFromArray($values);

                if ($sitereview->approved) {
                    $sitereview->approved_date = date('Y-m-d H:i:s');
                }

                //START PACKAGE WORK
                if (!empty($sitereview->approved)) {
                    if (isset($sitereview->pending))
                        $sitereview->pending = 0;
                    $sitereview->approved_date = date('Y-m-d H:i:s');
                    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                        $expirationDate = $package->getExpirationDate();
                        if (!empty($expirationDate))
                            $sitereview->expiration_date = date('Y-m-d H:i:s', $expirationDate);
                        else
                            $sitereview->expiration_date = '2250-01-01 00:00:00';
                    }
                }

                $sitereview->save();

                // Set package id
                if (isset($sitereview->package_id))
                    $sitereview->package_id = $package_id;
                $listing_id = $sitereview->listing_id;

                if ($this->_listingType->edit_creationdate && !$sitereview->draft) {

                    if (!isset($values['creation_date']) || empty($values['creation_date'])) {
                        $values['creation_date'] = time();
                        $values['creation_date'] = date('Y-m-d H:i:s', $values['creation_date']);
                    }

                    $sitereview->creation_date = $values['creation_date'];
                    $sitereview->save();
                }
                //START INTERGRATION EXTENSION WORK
                //START PAGE INTEGRATION WORK
                $page_id = $this->_getParam('page_id');
                if (!empty($page_id)) {
                    $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepageintegration');
                    if (!empty($moduleEnabled)) {
                        $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitepageintegration');
                        $row = $contentsTable->createRow();
                        $row->owner_id = $viewer_id;
                        $row->resource_owner_id = $sitereview->owner_id;
                        $row->page_id = $page_id;
                        $row->resource_type = 'sitereview_listing';
                        $row->resource_id = $sitereview->listing_id;
                        $row->save();
                    }
                }
                //END PAGE INTEGRATION WORK
                //START BUSINESS INTEGRATION WORK
                $business_id = $this->_getParam('business_id');
                if (!empty($business_id)) {
                    $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitebusinessintegration');
                    if (!empty($moduleEnabled)) {
                        $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitebusinessintegration');
                        $row = $contentsTable->createRow();
                        $row->owner_id = $viewer_id;
                        $row->resource_owner_id = $sitereview->owner_id;
                        $row->business_id = $business_id;
                        $row->resource_type = 'sitereview_listing';
                        $row->resource_id = $sitereview->listing_id;
                        $row->save();
                    }
                }
                //END BUSINESS INTEGRATION WORK
                //START GROUP INTEGRATION WORK
                $group_id = $this->_getParam('group_id');
                if (!empty($group_id)) {
                    $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroupintegration');
                    if (!empty($moduleEnabled)) {
                        $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitegroupintegration');
                        $row = $contentsTable->createRow();
                        $row->owner_id = $viewer_id;
                        $row->resource_owner_id = $sitereview->owner_id;
                        $row->group_id = $group_id;
                        $row->resource_type = 'sitereview_listing';
                        $row->resource_id = $sitereview->listing_id;
                        $row->save();
                    }
                }
                //END GROUP INTEGRATION WORK
                //START STORE INTEGRATION WORK
                $store_id = $this->_getParam('store_id');
                if (!empty($store_id)) {
                    $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitestoreintegration');
                    if (!empty($moduleEnabled)) {
                        $contentsTable = Engine_Api::_()->getDbtable('contents', 'sitestoreintegration');
                        $row = $contentsTable->createRow();
                        $row->owner_id = $viewer_id;
                        $row->resource_owner_id = $sitereview->owner_id;
                        $row->store_id = $store_id;
                        $row->resource_type = 'sitereview_listing';
                        $row->resource_id = $sitereview->listing_id;
                        $row->save();
                    }
                }

                // Set photo
                if (!empty($_FILES['photo'])) {
                    Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->setPhoto($_FILES['photo'], $sitereview);
                    $albumTable = Engine_Api::_()->getDbtable('albums', 'sitereview');
                    $album_id = $albumTable->update(array('photo_id' => $sitereview->photo_id), array('listing_id = ?' => $sitereview->listing_id));
                }

                // Set tags
                $keywords = '';
                if (isset($values['tags']) && !empty($values['tags'])) {
                    $tags = preg_split('/[,]+/', $values['tags']);
                    $tags = array_filter(array_map("trim", $tags));
                    $sitereview->tags()->addTagMaps($viewer, $tags);

                    foreach ($tags as $tag) {
                        $keywords .= " $tag";
                    }
                }

                // UPDATE KEYWORDS IN SEARCH TABLE
                if (!empty($keywords)) {
                    Engine_Api::_()->getDbTable('search', 'core')->update(array('keywords' => $keywords), array('type = ?' => 'sitereview_listing', 'id = ?' => $sitereview->listing_id));
                }

                $categoryIds = array();
                $categoryIds[] = $sitereview->category_id;
                $categoryIds[] = $sitereview->subcategory_id;
                $categoryIds[] = $sitereview->subsubcategory_id;
                try {
                    $profile_type = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
                } catch (Exception $ex) {
                    //Blank Exception
                }

                // Profile fields saving
                $sitereview->profile_type = (isset($profile_type) ? $profile_type : 0);
                $profileTypeField = null;
                $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_listing');
                if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
                    $profileTypeField = $topStructure[0]->getChild();
                }
                if ($profileTypeField) {
                    $profileTypeValue = $sitereview->profile_type;

                    if ($profileTypeValue) {
                        $profileValues = Engine_Api::_()->fields()->getFieldsValues($sitereview);

                        $valueRow = $profileValues->createRow();
                        $valueRow->field_id = $profileTypeField->field_id;
                        $valueRow->item_id = $sitereview->getIdentity();
                        $valueRow->value = $profileTypeValue;
                        $valueRow->save();
                    } else {
                        $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_listing');
                        if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
                            $profileTypeField = $topStructure[0]->getChild();
                            $options = $profileTypeField->getOptions();
                            if (count($options) == 1) {
                                $profileValues = Engine_Api::_()->fields()->getFieldsValues($sitereview);
                                $valueRow = $profileValues->createRow();
                                $valueRow->field_id = $profileTypeField->field_id;
                                $valueRow->item_id = $sitereview->getIdentity();
                                $valueRow->value = $options[0]->option_id;
                                $valueRow->save();
                            }
                        }
                    }
                    // Save the profile fields information.
                    Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->setProfileFields($sitereview, $data);
                }

                // Save as draft 
                if (!empty($sitereview->draft))
                    $sitereview->search = 0;

                $sitereview->save();

                // Set auth permission
                $auth = Engine_Api::_()->authorization()->context;
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                if (empty($values['auth_view'])) {
                    $values['auth_view'] = "everyone";
                }
                if (empty($values['auth_comment'])) {
                    $values['auth_comment'] = "everyone";
                }
                $viewMax = array_search($values['auth_view'], $roles);
                $commentMax = array_search($values['auth_comment'], $roles);
                foreach ($roles as $i => $role) {
                    $auth->setAllowed($sitereview, $role, "view_listtype_$this->_listingTypeId", ($i <= $viewMax));
                    $auth->setAllowed($sitereview, $role, "view", ($i <= $viewMax));
                    $auth->setAllowed($sitereview, $role, "comment_listtype_$this->_listingTypeId", ($i <= $commentMax));
                    $auth->setAllowed($sitereview, $role, "comment", ($i <= $commentMax));
                }
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
                if (empty($values['auth_topic'])) {
                    $values['auth_topic'] = "registered";
                }
                if (empty($values['auth_photo'])) {
                    $values['auth_photo'] = "registered";
                }
                if (!isset($values['auth_video']) && empty($values['auth_video'])) {
                    $values['auth_video'] = "registered";
                }
                if (isset($values['auth_event']) && empty($values['auth_event'])) {
                    $values['auth_event'] = "registered";
                }
                if (isset($values['auth_event']) && !empty($values['auth_event'])) {
                    $eventMax = array_search($values['auth_event'], $roles);
                    foreach ($roles as $i => $roles) {
                        $auth->setAllowed($sitereview, $roles, "event_listtype_$this->_listingTypeId", ($i <= $eventMax));
                    }
                }
                $topicMax = array_search($values['auth_topic'], $roles);
                $photoMax = array_search($values['auth_photo'], $roles);
                $videoMax = array_search($values['auth_video'], $roles);
                foreach ($roles as $i => $roles) {
                    $auth->setAllowed($sitereview, $roles, "topic_listtype_$this->_listingTypeId", ($i <= $topicMax));
                    $auth->setAllowed($sitereview, $roles, "photo_listtype_$this->_listingTypeId", ($i <= $photoMax));
                    $auth->setAllowed($sitereview, $roles, "video_listtype_$this->_listingTypeId", ($i <= $videoMax));
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }

            // Set 
            $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
            $db->beginTransaction();
            try {
                $row = $tableOtherinfo->getOtherinfo($listing_id);
                $overview = '';
                if (isset($values['overview'])) {
                    $overview = $values['overview'];
                }
                if (empty($row))
                    Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                        'listing_id' => $listing_id,
                        'overview' => $overview
                    ));
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }

            if (!empty($listing_id)) {
                $sitereview->setLocation();
            }

            $db->beginTransaction();
            try {
                if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting'))
                    $sitereview_pending = $sitereview->pending;
                else
                    $sitereview_pending = 0;


                // Start notification work
                if ($sitereview->draft == 0 && $sitereview->search && time() >= strtotime($sitereview->creation_date) && empty($sitereview_pending)) {
                    $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sitereview, 'sitereview_new_listtype_' . $this->_listingTypeId);
                    if ($action != null) {
                        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
                    }
                }
                $users = Engine_Api::_()->getDbtable('editors', 'sitereview')->getAllEditors($this->_listingTypeId, 0, 1);
                foreach ($users as $user_ids) {

                    $subjectOwner = Engine_Api::_()->getItem('user', $user_ids->user_id);

                    if (!($subjectOwner instanceof User_Model_User)) {
                        continue;
                    }
                    $host = $_SERVER['HTTP_HOST'];
                    $newVar = _ENGINE_SSL ? 'https://' : 'http://';
                    $object_link = $newVar . $host . $sitereview->getHref();

                    if (isset($subjectOwner->email) && !empty($subjectOwner->email)) {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($subjectOwner->email, 'SITEREVIEW_LISTING_CREATION_EDITOR', array(
                            'listing_type' => strtolower($this->_listingType->title_singular),
                            'object_link' => $object_link,
                            'object_title' => $sitereview->getTitle(),
                            'object_description' => $sitereview->getDescription(),
                            'queue' => true
                        ));
                    }
                }
                //SEND NOTIFICATIONS FOR SUBSCRIBERS
                if ($this->_listingType->subscription)
                    Engine_Api::_()->getDbtable('subscriptions', 'sitereview')->sendNotifications($sitereview, $this->_listingTypeId);
                $db->commit();

                // Change request method POST to GET
                $this->setRequestMethod();
                $this->_forward('view', 'index', 'sitereview', array(
                    'listing_id' => $sitereview->getIdentity()
                ));
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }
        }
    }

    /*
     * Calling of edit listing
     * 
     * @return array
     */
    public function editAction() {
        // Set view
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        $viewer = Engine_Api::_()->user()->getViewer();
        $listing_id = $this->getRequestParam('listing_id');
        $sitereviewObj = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        if (empty($sitereviewObj)) {
            $this->respondWithError('no_record');
        }

        //CHECK FOR CREATION PRIVACY
        if (!$this->_helper->requireAuth()->setAuthParams($sitereviewObj, $viewer, "edit_listtype_$this->_listingTypeId")->isValid())
            $this->respondWithError('unauthorized');

        Engine_Api::_()->core()->setSubject($sitereviewObj);

        if (isset($sitereviewObj) && !empty($sitereviewObj))
            $sitereview = $sitereviewObj->toArray();

        if (isset($sitereview['body']) && !empty($sitereview['body']))
            $sitereview['body'] = strip_tags($sitereview['body']);

        $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();

        //SEND LISTING TYPE TITLE TO TPL
        $title = $this->_listingType->title_plural;
        $values['user_id'] = $viewer_id;
        $values['listingtype_id'] = $this->_listingTypeId;
        $paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSitereviewsPaginator($values);
        $current_count = $paginator->getTotalItemCount();
        $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "max_listtype_$this->_listingTypeId");


//        $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
//        $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
//        $sitereviewListingTypeOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.listingtype.order', false);
//        $sitereviewProfileOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.profile.order', false);
//        $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
//        $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
//        $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);
        $category_count = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 1, $this->_listingTypeId, 0, 1, 0, 'cat_order', 0, array('category_id'));
        $sitereviewCategoryType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.category.type', false);

        $expiry_setting = $expiry_setting = Engine_Api::_()->sitereview()->expirySettings($this->_listingTypeId);

//        $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
//        for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
//            $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
//        }
//
//        for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
//            $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
//        }
//        $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;
        //GET DEFAULT PROFILE TYPE ID
        //GET PROFILE MAPPING ID
        $categoryIds = array();
        $categoryIds[] = $sitereviewObj->category_id;
        $categoryIds[] = $sitereviewObj->subcategory_id;
        $categoryIds[] = $sitereviewObj->subsubcategory_id;

        try {

            //to provide the basis of profile fields
            $sitereview['fieldCategoryLevel'] = "";
            if (isset($sitereviewObj->category_id) && !empty($sitereviewObj->category_id)) {
                $categoryObject = Engine_Api::_()->getItem('sitereview_category', $sitereviewObj->category_id);
                if (isset($categoryObject) && !empty($categoryObject) && isset($categoryObject->profile_type) && !empty($categoryObject->profile_type))
                    $sitereview['fieldCategoryLevel'] = 'category_id';
            }
            if (isset($sitereviewObj->subcategory_id) && !empty($sitereviewObj->subcategory_id)) {
                $categoryObject = Engine_Api::_()->getItem('sitereview_category', $sitereviewObj->subcategory_id);
                if (isset($categoryObject) && !empty($categoryObject) && isset($categoryObject->profile_type) && $categoryObject->profile_type)
                    $sitereview['fieldCategoryLevel'] = 'subcategory_id';
            }
            if (isset($sitereviewObj->subsubcategory_id) && !empty($sitereviewObj->subsubcategory_id)) {
                $categoryObject = Engine_Api::_()->getItem('sitereview_category', $sitereviewObj->subsubcategory_id);
                if (isset($categoryObject) && !empty($categoryObject) && isset($categoryObject->profile_type) && $categoryObject->profile_type)
                    $sitereview['fieldCategoryLevel'] = 'subsubcategory_id';
            }

            $previous_profile_type = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
        } catch (Exception $ex) {
            $previous_profile_type = $defaultProfileId;
        }

        // Set tag maps
        if (isset($this->_listingType->show_tag) && $this->_listingType->show_tag) {
            //prepare tags
            $sitereviewTags = $sitereviewObj->tags()->getTagMaps();
            $tagString = '';

            foreach ($sitereviewTags as $tagmap) {

                if ($tagString != '')
                    $tagString .= ', ';
                $tagString .= $tagmap->getTag()->getTitle();
            }

            $tagNamePrepared = $tagString;
            $sitereview['tags'] = $tagString;
        }

        // Edit Dates
        if ($this->_listingType->edit_creationdate && $sitereviewObj->creation_date && ($sitereviewObj->draft || (!$sitereviewObj->draft && (time() < strtotime($sitereviewObj->creation_date))))) {

            $creation_date = strtotime($sitereviewObj->creation_date);
            $oldTz = date_default_timezone_get();
            date_default_timezone_set($viewer->timezone);
            $creation_date = date('Y-m-d H:i:s', $creation_date);
            date_default_timezone_set($oldTz);
            $sitereview['creation_date'] = $creation_date;
        }

        if ($sitereviewObj->end_date && $sitereviewObj->end_date != '0000-00-00 00:00:00') {
            // Convert and re-populate times
            $end = strtotime($sitereviewObj->end_date);
            $oldTz = date_default_timezone_get();
            date_default_timezone_set($viewer->timezone);
            $end = date('Y-m-d H:i:s', $end);
            date_default_timezone_set($oldTz);
            $sitereview['end_date'] = $end;
            $sitereview['end_date_enable'] = '1';
        } else if (empty($sitereviewObj->end_date) || $sitereviewObj->end_date == '0000-00-00 00:00:00') {
            $date = (string) date('Y-m-d');
            $sitereview['end_date'] = $date . ' 00:00:00';
            $sitereview['end_date_enable'] = '0';
        }

        // Get auth roles
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach ($roles as $role) {
            if (1 == $auth->isAllowed($sitereviewObj, $role, "view_listtype_$this->_listingTypeId")) {
                $sitereview['auth_view'] = $role;
            }

            if ($form['form']['auth_comment']) {
                if (1 == $auth->isAllowed($sitereviewObj, $role, "comment_listtype_$this->_listingTypeId")) {
                    $sitereview['auth_comment'] = $role;
                }
            }
        }

        $roles_photo = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');

        foreach ($roles_photo as $role_topic) {
            if (1 == $auth->isAllowed($sitereviewObj, $role_topic, "topic_listtype_$this->_listingTypeId")) {
                $sitereview['auth_topic'] = $role_topic;
            }
        }

        foreach ($roles_photo as $role_photo) {
            if (1 == $auth->isAllowed($sitereviewObj, $role_photo, "photo_listtype_$this->_listingTypeId")) {
                $sitereview['auth_photo'] = $role_photo;
            }
        }

        foreach ($roles_photo as $role_photo) {
            if (1 == $auth->isAllowed($sitereviewObj, $role_photo, "event_listtype_$this->_listingTypeId")) {
                $sitereview['auth_event'] = $role_photo;
            }
        }

        $videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
        if ($videoEnable) {
            $roles_video = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
            foreach ($roles_video as $role_video) {
                if (1 == $auth->isAllowed($sitereviewObj, $role_video, "video_listtype_$this->_listingTypeId")) {
                    $sitereview['auth_video'] = $role_video;
                }
            }
        }

        if (Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
            if (empty($sitereview->networks_privacy)) {
                $sitereview['networks_privacy'] = array(0);
            }
        }

        // Get profile fields
        if (isset($sitereviewObj->profile_type) && !empty($sitereviewObj->profile_type)) {
            $profile_fields = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getInformation($sitereviewObj, 1);
            if (isset($profile_fields) && !empty($profile_fields))
                $sitereview = array_merge($sitereview, Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getInformation($sitereviewObj, 1));
        }

        if ($this->getRequest()->isGet()) {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getListingCreateForm($this->_listingTypeId, $sitereviewObj, $previous_profile_type);
            $response['formValues'] = $sitereview;
            $this->respondWithSuccess($response);
        } else if ($this->getRequest()->isPost() || $this->getRequest()->isPut()) {
            $getForm = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getListingCreateForm($this->_listingTypeId, $sitereviewObj);

            $data = $_REQUEST;
            $values = $sitereview;
            if ($sitereviewObj->category_id != $_REQUEST['category_id']) {
                $_REQUEST['subcategory_id'] = (!empty($_REQUEST['subcategory_id'])) ? $_REQUEST['subcategory_id'] : 0;
                $_REQUEST['subsubcategory_id'] = (!empty($_REQUEST['subsubcategory_id'])) ? $_REQUEST['subsubcategory_id'] : 0;
            }

            if ($sitereviewObj->subcategory_id != $_REQUEST['subcategory_id']) {
                $_REQUEST['subsubcategory_id'] = (!empty($_REQUEST['subsubcategory_id'])) ? $_REQUEST['subsubcategory_id'] : 0;
            }

            foreach ($getForm['form'] as $element) {
                if (isset($_REQUEST[$element['name']]))
                    $values[$element['name']] = $_REQUEST[$element['name']];
            }
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.duplicatetitle', 1)) {
                $isListingExists = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingColumn(array('listingtype_id' => $this->_listingTypeId, 'title' => $_POST['title']));

                if ($isListingExists) {
                    $error[] = 'Please choose the different listing title as listing with same title already exists.';
                    $this->respondWithValidationError('validation_fail', $error);
                }
            }

            // START FORM VALIDATION
            $listingValidators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getListingCreateFormValidators($values);
            $values['validators'] = $listingValidators;
            $listingValidationMessage = $this->isValid($values);
            if (!@is_array($validationMessage) && isset($values['category_id'])) {

                $categoryIds = array();
                $categoryIds[] = $values['category_id'];
                $categoryIds[] = $values['subcategory_id'];
                $categoryIds[] = $values['subsubcategory_id'];

                //@todo profile field work
                try {
                    $values['profile_type'] = $data['profile_type'] = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
                } catch (Exception $ex) {
                    
                }

                // Start profile fields validations
                if (isset($data['profile_type']) && !empty($data['profile_type'])) {
                    $profileFieldsValidators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getFieldsFormValidations($values, $values['profile_type']);
                    $data['validators'] = $profileFieldsValidators;
                    $profileFieldsValidationMessage = $this->isValid($data);
                }
            }
            if (is_array($listingValidationMessage) && is_array($profileFieldsValidationMessage))
                $validationMessage = array_merge($listingValidationMessage, $profileFieldsValidationMessage);

            else if (is_array($listingValidationMessage))
                $validationMessage = $listingValidationMessage;
            else if (is_array($profileFieldsValidationMessage))
                $validationMessage = $profileFieldsValidationMessage;
            else
                $validationMessage = 1;

            if (!empty($validationMessage) && @is_array($validationMessage)) {
                $this->respondWithValidationError('validation_fail', $validationMessage);
            }

            $tags = preg_split('/[,]+/', $values['tags']);
            $tags = array_filter(array_map("trim", $tags));

            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            try {

                if (Engine_Api::_()->sitereview()->listBaseNetworkEnable() && isset($values['networks_privacy']) && !empty($values['networks_privacy']) && in_array(0, $values['networks_privacy'])) {
                    $values['networks_privacy'] = new Zend_Db_Expr('NULL');
                    $sitereview['networks_privacy'] = array(0);
                }
                if ($expiry_setting == 1 && $values['end_date_enable'] == 1) {
                    // Convert times
                    $oldTz = date_default_timezone_get();
                    date_default_timezone_set($viewer->timezone);
                    $end = strtotime($values['end_date']);
                    date_default_timezone_set($oldTz);
                    $values['end_date'] = date('Y-m-d H:i:s', $end);
                } elseif ($expiry_setting == 1 && isset($values['end_date'])) {
                    $values['end_date'] = NULL;
                } elseif (isset($values['end_date'])) {
                    unset($values['end_date']);
                }
                if ($this->_listingType->edit_creationdate && $sitereviewObj->creation_date && ($sitereviewObj->draft || (!$sitereviewObj->draft && (time() < strtotime($sitereviewObj->creation_date))))) {
                    $oldTz = date_default_timezone_get();
                    date_default_timezone_set($viewer->timezone);
                    $creation = strtotime($values['creation_date']);
                    date_default_timezone_set($oldTz);
                    $values['creation_date'] = date('Y-m-d H:i:s', $creation);
                }

                $sitereviewObj->setFromArray($values);
                $sitereviewObj->modified_date = date('Y-m-d H:i:s');
                $sitereviewObj->tags()->setTagMaps($viewer, $tags);
                $sitereviewObj->save();

                // Save profile fields
                if (isset($values['category_id']) && !empty($values['category_id'])) {
                    $categoryIds = array();
                    $categoryIds[] = $sitereviewObj->category_id;
                    $categoryIds[] = $sitereviewObj->subcategory_id;
                    $categoryIds[] = $sitereviewObj->subsubcategory_id;
                    try {
                        $profile_type = $sitereviewObj->profile_type = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');
                    } catch (Exception $ex) {
                        $profile_type = $sitereviewObj->profile_type = $defaultProfileId;
                    }
                    $sitereviewObj->save();

                    if ($sitereviewObj->profile_type != $previous_profile_type) {

                        $fieldvalueTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'values');
                        $fieldvalueTable->delete(array('item_id = ?' => $sitereviewObj->listing_id));

                        Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->delete(array(
                            'item_id = ?' => $sitereviewObj->listing_id,
                        ));

                        if (!empty($sitereviewObj->profile_type) && !empty($previous_profile_type)) {
                            //PUT NEW PROFILE TYPE
                            $fieldvalueTable->insert(array(
                                'item_id' => $sitereviewObj->listing_id,
                                'field_id' => $defaultProfileId,
                                'index' => 0,
                                'value' => $sitereviewObj->profile_type,
                            ));
                        }
                    }

                    // Save the profile fields information.
                    Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->setProfileFields($sitereviewObj, $data);
                }
                $sitereviewObj->save();

                //NOT SEARCHABLE IF SAVED IN DRAFT MODE
                if (!empty($sitereviewObj->draft)) {
                    $sitereviewObj->search = 0;
                    $sitereviewObj->save();
                }

                if ($sitereviewObj->draft == 0 && $sitereviewObj->search && $inDraft && time() >= strtotime($sitereview->creation_date)) {
                    $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($sitereviewObj->getOwner(), $sitereviewObj, 'sitereview_new_listtype_' . $this->_listingTypeId);

                    if ($action != null) {
                        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereviewObj);
                    }
                }

                // Set auth roles
                $auth = Engine_Api::_()->authorization()->context;

                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

                if (empty($values['auth_view'])) {
                    $values['auth_view'] = "everyone";
                }

                if (empty($values['auth_comment'])) {
                    $values['auth_comment'] = "everyone";
                }

                $viewMax = array_search($values['auth_view'], $roles);
                $commentMax = array_search($values['auth_comment'], $roles);

                foreach ($roles as $i => $role) {
                    $auth->setAllowed($sitereviewObj, $role, "view_listtype_$this->_listingTypeId", ($i <= $viewMax));
                    $auth->setAllowed($sitereviewObj, $role, "view", ($i <= $viewMax));
                    $auth->setAllowed($sitereviewObj, $role, "comment_listtype_$this->_listingTypeId", ($i <= $commentMax));
                    $auth->setAllowed($sitereviewObj, $role, "comment", ($i <= $commentMax));
                }

                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');

                if ($values['auth_topic'])
                    $auth_topic = $values['auth_topic'];
                else
                    $auth_topic = "registered";
                $topicMax = array_search($auth_topic, $roles);

                foreach ($roles as $i => $role) {
                    $auth->setAllowed($sitereviewObj, $role, "topic_listtype_$this->_listingTypeId", ($i <= $topicMax));
                }

                if ($values['auth_photo'])
                    $auth_photo = $values['auth_photo'];
                else
                    $auth_photo = "registered";
                $photoMax = array_search($auth_photo, $roles);

                foreach ($roles as $i => $role) {
                    $auth->setAllowed($sitereviewObj, $role, "photo_listtype_$this->_listingTypeId", ($i <= $photoMax));
                }

                $roles_video = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
                if (!isset($values['auth_video']) && empty($values['auth_video'])) {
                    $values['auth_video'] = "registered";
                }

                $videoMax = array_search($values['auth_video'], $roles_video);
                foreach ($roles_video as $i => $role_video) {
                    $auth->setAllowed($sitereviewObj, $role_video, "video_listtype_$this->_listingTypeId", ($i <= $videoMax));
                }

                if (isset($values['auth_event'])) {
                    if ($values['auth_event'])
                        $auth_event = $values['auth_event'];
                    else
                        $auth_event = "registered";
                    $eventMax = array_search($auth_event, $roles);

                    foreach ($roles as $i => $role) {
                        $auth->setAllowed($sitereviewObj, $role, "event_listtype_$this->_listingTypeId", ($i <= $eventMax));
                    }
                }

                if ($previous_category_id != $sitereviewObj->category_id) {
                    Engine_Api::_()->getDbtable('ratings', 'sitereview')->editListingCategory($sitereviewObj->listing_id, $previous_category_id, $sitereviewObj->category_id, $sitereviewObj->getType());
                }

                //SEND NOTIFICATIONS FOR SUBSCRIBERS
                if ($this->_listingType->subscription)
                    Engine_Api::_()->getDbtable('subscriptions', 'sitereview')->sendNotifications($sitereviewObj);

                $db->commit();
                $sitereviewObj->setLocation();
                $db->beginTransaction();

                $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                foreach ($actionTable->getActionsByObject($sitereviewObj) as $action) {
                    $actionTable->resetActivityBindings($action);
                }
                $db->commit();
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }
        }
    }

    /**
     * Return the Listing View page.
     * 
     * @return array
     */
    public function viewAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $listing_id = $this->getRequestParam('listing_id');
        $subject = $sitereviewObj = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        if (empty($sitereviewObj)) {
            $this->respondWithError('no_record');
        }
        Engine_Api::_()->core()->setSubject($sitereviewObj);
        $this->_listingTypeId = $this->_listingType->listingtype_id;

        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        $bodyParams = array();

        $reviewApi = Engine_Api::_()->sitereview();
        $expirySettings = $reviewApi->expirySettings($this->_listingTypeId);

        $approveDate = null;
        if ($expirySettings == 2)
            $approveDate = $reviewApi->adminExpiryDuration($this->_listingTypeId);

        try {

            if (isset($sitereviewObj) && !empty($sitereviewObj)) {
                $sitereview = $sitereviewObj->toArray();

                if (empty($sitereview['price']))
                    unset($sitereview['price']);

                // Set the price & currency 
                if (isset($sitereview['price']) && $sitereview['price'] > 0)
                    $sitereview['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');

                // Add owner images
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($sitereviewObj, true);
                $sitereview = array_merge($sitereview, $getContentImages);

                $sitereview["owner_title"] = $sitereviewObj->getOwner()->getTitle();

                $sitereview['content_url'] = Engine_Api::_()->getApi('Core', 'siteapi')->getContentUrl($sitereviewObj);

                //GETTING CATEGORY and SUBCATEGORY,SUBSUBCATEGORY-if any
                $category_id = $sitereviewObj->category_id;
                if (!empty($category_id)) {

                    $sitereview['categoryName'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getTitle();

                    $subcategory_id = $sitereviewObj->subcategory_id;

                    if (!empty($subcategory_id)) {

                        $sitereview['subCategoryName'] = ucfirst(Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getTitle());

                        $subsubcategory_id = $sitereviewObj->subsubcategory_id;

                        if (!empty($subsubcategory_id)) {

                            $sitereview['subSubCategoryName'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getTitle();
                        }
                    }
                }

                //Advanced video integration..........................
                if (method_exists('Siteapi_Api_Core','isSitevideoPluginEnabled')) {
                    $subject_type = $sitereviewObj->getType();
                    $subject_id = $sitereviewObj->getIdentity();
                    $advVideoEnableArray = Engine_Api::_()->getApi('Core', 'siteapi')->isSitevideoPluginEnabled($subject_type, $subject_id);
                    if (!empty($advVideoEnableArray) && is_array($advVideoEnableArray)) {
                        $sitereview = array_merge($sitereview, $advVideoEnableArray);
                    } else {
                        $sitereview['sitevideoPluginEnabled'] = 0;
                    }
                } else {
                    $sitereview['sitevideoPluginEnabled'] = 0;
                }


                if (!empty($this->_listingType->overview)) {
                    $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
                    $overview = $tableOtherinfo->getColumnValue($sitereviewObj->getIdentity(), 'overview');
                    $staticBaseUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.static.baseurl', null);
                    $serverHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
                    $getDefaultStorageId = Engine_Api::_()->getDbtable('services', 'storage')->getDefaultServiceIdentity();
                    $getDefaultStorageType = Engine_Api::_()->getDbtable('services', 'storage')->getService($getDefaultStorageId)->getType();

                    $this->getHost = '';
                    if ($getDefaultStorageType == 'local')
                        $this->getHost = !empty($staticBaseUrl) ? $staticBaseUrl : $serverHost;
                    if (!empty($overview)) {
                        $sitereview['overview'] = $overview;
                        $sitereview['overview'] = str_replace('src="/', 'src="' . $this->getHost . '/', $sitereview['overview']);
                        $sitereview['overview'] = str_replace('"', "'", $sitereview['overview']);
                    }
                }

                if (isset($sitereviewObj->owner_id) && !empty($sitereviewObj->owner_id) && isset($viewer_id) && !empty($viewer_id) && $sitereviewObj->owner_id == $viewer_id) {
                    if ($approveDate && $approveDate > $sitereviewObj->approved_date) {
                        $sitereview['expiryString'] = $this->translate('Expired');
                        $sitereview['expiryStringColor'] = 'R';
                    } elseif ($expirySettings == 2 && $approveDate && $approveDate < $sitereviewObj->approved_date) {
                        $exp = $sitereviewObj->getExpiryTime();
                        $sitereview['expiryString'] = $exp ? $this->translate("Expiry On") . $exp : '';
                        $sitereview['expiryStringColor'] = 'G';
                    } elseif ($expirySettings == 1) {
                        $current_date = date("Y-m-d i:s:m", time());
                        if (!empty($sitereviewObj->end_date) && $sitereviewObj->end_date != '0000-00-00 00:00:00') {
                            if ($sitereviewObj->end_date >= $current_date) {
//                                $sitereview['expiryString'] = $this->translate("Ending On: ") . $sitereviewObj->end_date;
                                $sitereview['expiryString'] = $this->translate("Ending On ");
                                $sitereview['expiryStringColor'] = 'G';
                            } else {
                                $sitereview['expiryString'] = $this->translate("Ending On: Expired");
                                $sitereview['expiryString'] = $sitereview['expiryString'] . $this->translate('(You can edit the end date to make the listing live again.)');
                                $sitereview['expiryStringColor'] = 'R';
                            }
                        }
                    }
                }
                // Add images  
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($sitereviewObj);

                $sitereview = array_merge($sitereview, $getContentImages);

                if (($sitereviewObj->closed == 1 || empty($sitereviewObj->approved) || $sitereviewObj->draft == 1 || empty($sitereviewObj->search))) {
                    if ($sitereviewObj->owner_id != $viewer_id && $level_id != 1) {
                        $module_error_type = @ucfirst($subject->getShortType());
                        $this->respondWithError('unauthorized', 0, $module_error_type);
                    }
                }

                $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');
                $owner = Engine_Api::_()->getItem('user', $subject->owner_id);
                if (isset($owner) && !$subscriptionTable->checkSubscription($owner, $viewer, $this->_listingTypeId)) {
                    $sitereview['isSubscribed'] = 0;
                } else if (isset($owner)) {
                    $sitereview['isSubscribed'] = 1;
                }

                $sitereview['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');

                $album = $subject->getSingletonAlbum();
                if (isset($album) && !empty($album)) {
                    $photoPaginator = $album->getCollectiblesPaginator();
                    $photoPaginator->setCurrentPageNumber($this->_getParam('page', 1));
                    $photoPaginator->setItemCountPerPage(100);
                    $images = array();
                    $bodyParams['response']['totalItemCount'] = $photoPaginator->getTotalItemCount();
                    foreach ($photoPaginator as $photo) {
                        $tempImages = $photo->toArray();

                        // Getting viewer like or not to content.
                        $tempImages["is_like"] = Engine_Api::_()->getApi('Core', 'siteapi')->isLike($photo);

                        // Getting like count.
                        $tempImages["like_count"] = Engine_Api::_()->getApi('Core', 'siteapi')->getLikeCount($photo);

                        $thumbMain = $photo->getPhotoUrl('thumb.main');
                        $tempImages['image'] = ($thumbMain) ? (!strstr($thumbMain, 'http')) ? $this->getHost . $thumbMain : $thumbMain : '';

                        $thumbProfile = $photo->getPhotoUrl('thumb.profile');
                        $tempImages['image_profile'] = ($thumbProfile) ? (!strstr($thumbProfile, 'http')) ? $this->getHost . $thumbProfile : $thumbProfile : '';

                        $thubNormal = $photo->getPhotoUrl('thumb.normal');
                        $tempImages['image_normal'] = ($thubNormal) ? (!strstr($thubNormal, 'http')) ? $this->getHost . $thubNormal : $thubNormal : '';

                        $thumbIcon = $photo->getPhotoUrl('thumb.icon');
                        $tempImages['image_icon'] = ($thumbIcon) ? (!strstr($thubNormal, 'http')) ? $this->getHost . $thumbIcon : $thumbIcon : '';

//                    if ($viewer->getIdentity() && $photo->authorization()->isAllowed($viewer, 'edit')) {
//                        $tempImages['menu'][] = array(
//                            'label' => $this->translate('Delete'),
//                            'name' => 'delete',
//                            'url' => 'listings/photo/delete/' . $sitereviewObj->getIdentity(),
//                            'urlParams' => array(
//                                "photo_id" => $photo->getIdentity()
//                            )
//                        );
//                    }
                        if (!empty($viewer) && ($tempMenu = $this->getRequestParam('photoMenu', 1)) && !empty($tempMenu)) {
                            $menu = array();
                            $menu = $this->_imageGutterMenus($sitereviewObj, $photo);
                            if (isset($menu) && !empty($menu))
                                $tempImages['menu'] = $menu;
                        }

                        $images[] = $tempImages;
                    }
                }
                if (isset($images) && !empty($images))
                    $sitereview['images'] = $images;

                $isAllowedView = $sitereviewObj->authorization()->isAllowed($viewer, 'view');
                $sitereview["allow_to_view"] = empty($isAllowedView) ? 0 : 1;

                $isAllowedEdit = $sitereviewObj->authorization()->isAllowed($viewer, 'edit');
                $sitereview["edit"] = empty($isAllowedEdit) ? 0 : 1;

                // Getting viewer like or not to content.
                $sitereview["is_like"] = Engine_Api::_()->getApi('Core', 'siteapi')->isLike($subject);
                // Getting like count.
                $sitereview["like_count"] = Engine_Api::_()->getApi('Core', 'siteapi')->getLikeCount($subject);

                $uploadPhoto = Engine_Api::_()->authorization()->isAllowed($sitereviewObj, $viewer, "photo_listtype_$sitereviewObj->listingtype_id");
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    $package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereviewObj->package_id);
                    $album = $sitereviewObj->getSingletonAlbum();
                    $paginator = $album->getCollectiblesPaginator();
                    $total_images = $paginator->getTotalItemCount();
                    if (empty($package->photo_count))
                        $allowed_upload_photo = $uploadPhoto;
                    elseif ($package->photo_count > $total_images)
                        $allowed_upload_photo = $uploadPhoto;
                    else
                        $allowed_upload_photo = 0;
                }
                else {
                    $allowed_upload_photo = $uploadPhoto;
                }

                $sitereview['can_upload_photo'] = $allowed_upload_photo;
                $sitereview['canCreateVideo'] = $this->_helper->requireAuth()->setAuthParams('video', null, 'create')->checkRequire();

                $isAllowedDelete = $sitereviewObj->authorization()->isAllowed($viewer, 'delete');
                $sitereview["delete"] = empty($isAllowedDelete) ? 0 : 1;
                if ($this->getRequestParam('profile_fields', true)) {

                    if (isset($sitereviewObj->profile_type) && !empty($sitereviewObj->profile_type)) {
                        $tempProfileFields = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getInformation($subject);

                        if (isset($tempProfileFields) && !empty($tempProfileFields)) {
                            $sitereview['profile_fields'] = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getInformation($subject);

                            if (isset($_REQUEST['field_order']) && !empty($_REQUEST['field_order']) && $_REQUEST['field_order'] == 1) {

                                $sitereview['profile_fields'] = Engine_Api::_()->getApi('Core', 'siteapi')->responseFormat($sitereview['profile_fields']);
                            }
                        }
                    }

                    if (isset($sitereviewObj->price) && !empty($sitereviewObj->price) && $sitereviewObj->price > 0) {
                        $sitereview['profile_fields']['Price'] = $sitereviewObj->price;
                    }
                }

//            $sitereview['menus'] = $this->_getListingMenus($sitereview, $this->_listingTypeId);
                //        // GETTING THE GUTTER-MENUS.
                if ($this->getRequestParam('gutter_menu', true))
                    $sitereview['gutterMenu'] = $this->_gutterMenus($subject, $this->_listingTypeId);
                // GETTING THE EVENT PROFILE TABS.
                if ($this->getRequestParam('profile_tabs', 1))
                    $sitereview['profile_tabs'] = $this->_profileTAbsContainer($sitereviewObj);
            }

            $this->respondWithSuccess($sitereview, true);
        } catch (Exception $ex) {
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

    private function _gutterMenus($subject, $listingTypeId) {

        $viewer = Engine_Api::_()->user()->getViewer();
        $owner = $subject->getOwner();
        $owner_id = $owner->getIdentity();
        $menus = array();

        $getHost = Engine_Api::_()->getApi('core', 'siteapi')->getHost();
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $baseUrl = @trim($baseUrl, "/");

        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }
        $tempMenu = array();
        //GET EDIT AND DELETE SETTINGS
        $can_edit = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "edit_listtype_$listingTypeId")->checkRequire();
        $can_create = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "create_listtype_$listingTypeId")->checkRequire();
        $can_delete = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "delete_listtype_$listingTypeId")->checkRequire();
        $listing_singular_uc = ucfirst($this->_listingType->title_singular);

        //TELL A FRIEND
        $tempMenu[] = array(
            'name' => 'tellafriend',
            'label' => $this->translate('Tell a friend'),
            'url' => 'listing/tellafriend/' . $subject->getIdentity()
        );


        if (!empty($viewer_id)) {
            $tempMenu[] = array(
                'name' => 'share',
                'label' => $this->translate('Share'),
                'url' => 'activity/share',
                'urlParams' => array(
                    "type" => $subject->getType(),
                    "id" => $subject->getIdentity()
                )
            );


            //ABLE TO UPLOAD VIDEO OR NOT        
            $allowed_upload_video = 1;
            $allowed_upload_videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
            if (empty($allowed_upload_videoEnable))
                $allowed_upload_video = 0;

            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {
                //CHECK FOR SOCIAL ENGINE CORE VIDEO PLUGIN
                $allowed_upload_video_video = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'create');
                if (empty($allowed_upload_video_video))
                    $allowed_upload_video = 0;
            }

            $allowed_upload_video = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "video_listtype_$listingTypeId");
            if (empty($allowed_upload_video))
                $allowed_upload_video = 0;

            if ($allowed_upload_video) {
                //Advanced video integration..........................
                if (method_exists('Siteapi_Api_Core','isSitevideoPluginEnabled') && _CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '2.3.5') || _CLIENT_TYPE == 'ios' && _IOS_VERSION > '2.1.6')) {
                    $advVideoEnableArray = Engine_Api::_()->getApi('Core', 'siteapi')->isSitevideoPluginEnabled($subject->getType(), $subject->getIdentity());
                    if (isset($advVideoEnableArray['sitevideoPluginEnabled']) && !empty($advVideoEnableArray['sitevideoPluginEnabled'])) {
                        if (isset($advVideoEnableArray['canCreateVideo']) && !empty($advVideoEnableArray['canCreateVideo']))
                            $tempMenu[] = array(
                                'name' => 'videoCreate',
                                'label' => $this->translate('Add Video'),
                                'url' => 'advancedvideos/create',
                                "subject_type" => $subject->getType(),
                                "subject_id" => $subject->getIdentity()
                            );
                    } else {
                        $tempMenu[] = array(
                            'name' => 'videoCreate',
                            'label' => $this->translate('Add Video'),
                            'url' => 'listings/video/create/' . $subject->getIdentity(),
                        );
                    }
                }
            }
        }
        $allow_claim = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$listingTypeId");
        if (!empty($allow_claim)) {

            $userClaimValue = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($subject->listing_id, 'userclaim');

            if (!empty($userClaimValue) || $subject->owner_id != $viewer_id) {
                if (!empty($this->_listingType->claimlink)) {

                    $listmemberclaimsTable = Engine_Api::_()->getDbtable('listmemberclaims', 'sitereview');
                    $listmemberclaimsTablename = $listmemberclaimsTable->info('name');
                    $listingCount = $listmemberclaimsTable->select()->from($listmemberclaimsTablename, array('count(*) as total_count'))
                            ->where('listingtype_id = ?', $listingTypeId)
                            ->where('user_id = ?', $subject->owner_id)
                            ->query()
                            ->fetchColumn();

                    if ($listingCount) {
                        $total_count = 1;
                    }

                    if (!empty($total_count) && isset($viewer_id) && !empty($viewer_id)) {
                        $tempMenu[] = array(
                            'name' => 'claim',
                            'label' => $this->translate('Claim this listing'),
                            'url' => 'listing/claim-listing/' . $subject->getIdentity()
                        );
                    }
                }
            }
        }

        $listingTypeId = $subject->listingtype_id;
//GET LISTING TYPE ID
        $listingType = $this->_listingType;
        if ($subject->owner_id != $viewer->getIdentity()) {
            //CHECK EDITOR REVIEW IS ALLOWED OR NOT
            $allow_subscription = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingTypeId, 'subscription');
            if (!empty($allow_subscription)) {
                //MODIFY PARAMS
                $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');
                $owner = Engine_Api::_()->getItem('user', $subject->owner_id);
                if (isset($viewer_id) && !empty($viewer_id)) {
                    if (!$subscriptionTable->checkSubscription($owner, $viewer, $listingTypeId)) {
                        $tempMenu[] = array(
                            'name' => 'subscribe',
                            'label' => $this->translate('Subscribe'),
                            'url' => 'listing/add/' . $subject->getIdentity()
                        );
                    } else {
                        $tempMenu[] = array(
                            'name' => 'subscribe',
                            'label' => $this->translate('Unsubscribe'),
                            'url' => 'listing/remove/' . $subject->getIdentity()
                        );
                    }
                }
            }
        }

        $create_review = ($subject->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;
        if ($create_review) {

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
                $params['resource_id'] = $subject->listing_id;
                $params['resource_type'] = $subject->getType();
                $params['viewer_id'] = $viewer_id;
                $params['type'] = 'user';
                $hasPosted = $reviewTable->canPostReview($params);
            } else {
                $hasPosted = 0;
            }

            $autorizationApi = Engine_Api::_()->authorization();
            if ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingTypeId") && empty($hasPosted)) {
                $createAllow = 1;
            } elseif ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingTypeId") && !empty($hasPosted)) {
                $createAllow = 2;
            } else {
                $createAllow = 0;
            }

            if ($createAllow == 2) {
                $tempMenu[] = array(
                    'name' => 'update',
                    'label' => $this->translate('Update Review'),
                    'url' => 'listings/review/update/' . $subject->getIdentity(),
                    'urlParams' => array(
                        "review_id" => $hasPosted
                    )
                );
            }
        }

        if (!empty($listingType->reviews) && $listingType->reviews != 1 && !empty($viewer_id)) {
            //GET VIEWER   
            $viewer_id = $viewer->getIdentity();
            $create_review = ($subject->owner_id == $viewer_id) ? $listingType->allow_owner_review : 1;
            if (!empty($create_review)) {
                //GET REVIEW TABLE
                $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
                if ($viewer_id) {
                    $level_id = $viewer->level_id;
                    $params = array();
                    $params['resource_id'] = $subject->listing_id;
                    $params['resource_type'] = $subject->getType();
                    $params['viewer_id'] = $viewer_id;
                    $params['type'] = 'user';
                    $hasPosted = $reviewTable->canPostReview($params);
                } else {
                    $hasPosted = 0;
                    $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
                }

                $autorizationApi = Engine_Api::_()->authorization();
                if ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingTypeId") && empty($hasPosted)) {
                    $createAllow = 1;
                } elseif ($autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingTypeId") && !empty($hasPosted)) {
                    $createAllow = 2;
                } else {
                    $createAllow = 0;
                }

                if ($createAllow == 1) {
                    $tempMenu[] = array(
                        'name' => 'review',
                        'label' => $this->translate('Write a Review '),
                        'url' => 'listings/review/create/' . $subject->getIdentity(),
                    );
                }
            }
        }
        //SHOW MESSAGE OWNER LINK TO USER IF MESSAGING IS ENABLED FOR THIS LEVEL
        if (!empty($viewer_id)) {
//            SHOW MESSAGE OWNER LINK TO USER IF MESSAGING IS ENABLED FOR THIS LEVEL     
            $showMessageOwner = 0;
            $showMessageOwner = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'auth');
            if ($showMessageOwner != 'none') {
                $showMessageOwner = 1;
            }
        }
        //RETURN FALSE IF SUBJECT IS NOT SET
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
            if (Engine_Api::_()->core()->hasSubject('sitereview_listing') && !empty($viewer_id)) {
                //AUTHORIZATION CHECK
                if (empty($sitereview->draft) || !empty($sitereview->search) || !empty($sitereview->approved)) {
                    //CHECK LISTINGTYPE WISHLIST ALLOWED OR NOT
                    $wishlistAllow = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingTypeId, 'wishlist');
                    if (!empty($wishlistAllow)) {
                        //AUTHORIZATION CHECK
                        if (Engine_Api::_()->authorization()->isAllowed('sitereview_wishlist', $viewer, 'view')) {
                            $tempMenu[] = array(
                                'name' => 'wishlist',
                                'label' => $this->translate('Add to Wishlist'),
                                'url' => 'listings/wishlist/add/',
                                'urlParams' => array(
                                    "listing_id" => $subject->getIdentity()
                                )
                            );
                        }
                    }
                }
            }
        } elseif (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0) && ((_ANDROID_VERSION >= '1.8.5') || _IOS_VERSION >= '1.8.0')) {
            $tempMenu[] = array(
                'name' => 'favourite',
                'label' => $this->translate('Add to Favorite'),
                'url' => 'listings/wishlist/add/',
                'urlParams' => array(
                    "listing_id" => $subject->getIdentity()
                )
            );
        }
        //SHOW IF AUTHORIZED
        if ($subject->owner_id != $viewer_id && !empty($viewer_id) && !empty($showMessageOwner)) {
            if (!empty($viewer_id)) {
                $tempMenu[] = array(
                    'name' => 'messageowner',
                    'label' => $this->translate('Message Owner'),
                    'url' => 'listing/messageowner/' . $subject->getIdentity()
                );
            }
        }

        if ($can_edit && isset($viewer_id) && ($subject->authorization()->isAllowed($viewer, 'edit') || $viewer_id == $owner_id || $level_id == 1)) {
            $tempMenu[] = array(
                'label' => $this->translate('Edit Details'),
                'name' => 'edit',
                'url' => 'listing/edit/' . $subject->getIdentity(),
            );

            if (_CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '1.7') || _CLIENT_TYPE == 'ios' && _IOS_VERSION >= '1.5.3')) {
                $getOauthToken = Engine_Api::_()->getApi('oauth', 'siteapi')->getAccessOauthToken($viewer);
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($subject->listing_id)) {
                        $tempMenu[] = array(
                            'label' => $this->translate('Make Payment'),
                            'name' => 'makePayment',
                            'url' => $getHost . '/' . $baseUrl . "/listings/payment?token=" . $getOauthToken['token'] . "&listing_id=" . $subject->getIdentity() . "&listingtype_id=" . $subject->listingtype_id . "&disableHeaderAndFooter=1"
                        );
                    }
                }
            }
        }

        if (!empty($viewer_id) && !empty($owner_id) && $owner_id == $viewer_id && (_CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '1.8.7') || _CLIENT_TYPE == 'ios' && _IOS_VERSION >= '1.8.0'))) {
            $tempMenu[] = array(
                'label' => $this->translate('Upgrade Package'),
                'name' => 'upgrade_package',
                'url' => 'listings/upgrade-package',
                'urlParams' => array(
                    "listing_id" => $subject->getIdentity(),
                    "listingtype_id" => $subject->listingtype_id
                )
            );
        }

        if (!empty($viewer_id)) {
            $tempMenu[] = array(
                'name' => 'report',
                'label' => $this->translate('Report This Listing'),
                'url' => 'report/create/subject/' . $subject->getGuid(),
                'urlParams' => array(
                    "type" => $subject->getType(),
                    "id" => $subject->getIdentity()
                )
            );
        }

        if ($subject->draft == 1 && $can_edit && isset($viewer_id) && ($subject->authorization()->isAllowed($viewer, 'edit') || $viewer_id == $owner_id || $level_id == 1))
            $tempMenu[] = array(
                'label' => $this->translate('Publish ' . $listing_singular_uc),
                'name' => 'publish',
                'url' => ' ',
                'urlParams' => array(
                )
            );
        if (!$subject->closed && isset($viewer_id) && $can_edit && isset($viewer_id) && ($subject->authorization()->isAllowed($viewer, 'edit') || $viewer_id == $owner_id || $level_id == 1))
            $tempMenu[] = array(
                'label' => $this->translate('Close ' . $listing_singular_uc),
                'name' => 'close',
                'url' => 'listing/close/' . $subject->getIdentity(),
            );
        else if ($can_edit && isset($viewer_id) && ($subject->authorization()->isAllowed($viewer, 'edit') || $viewer_id == $owner_id || $level_id == 1))
            $tempMenu[] = array(
                'label' => $this->translate('Open ' . $listing_singular_uc),
                'name' => 'close',
                'url' => 'listing/close/' . $subject->getIdentity(),
            );

        if ($can_delete && isset($viewer_id) && ($subject->authorization()->isAllowed($viewer, 'edit') || $viewer_id == $owner_id || $level_id == 1))
            $tempMenu[] = array(
                'label' => $this->translate('Delete ' . $listing_singular_uc),
                'name' => 'delete',
                'url' => 'listing/delete/' . $subject->getIdentity(),
                'urlParams' => array(
                )
            );

        if (_CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '1.8.2') || _CLIENT_TYPE == 'ios' && _IOS_VERSION >= '1.6.6')) {
            $apply = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingTypeId, 'allow_apply');
            if (!empty($apply)) {
                $allowApplyNow = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "apply_listtype_$listingTypeId");
                if (!empty($allowApplyNow)) {
                    $tempMenu[] = array(
                        'label' => $this->translate('Apply Now'),
                        'name' => 'apply-now',
                        'url' => 'listing/apply-now/' . $subject->getIdentity(),
                    );
                }
            }
        }
        return $tempMenu;
    }

    private function _getListingMenus($item, $listingTypeId) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        $getHost = Engine_Api::_()->getApi('core', 'siteapi')->getHost();
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $baseUrl = @trim($baseUrl, "/");

        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        $tempMenu = array();
        // Get edit & delete settings
        $can_edit = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "edit_listtype_$listingTypeId")->checkRequire();
        $can_create = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "create_listtype_$listingTypeId")->checkRequire();
        $can_delete = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "delete_listtype_$listingTypeId")->checkRequire();
        $listing_singular_uc = ucfirst($this->_listingType->title_singular);

        if ($can_edit) {
            $tempMenu[] = array(
                'label' => $this->translate('Edit ' . $listing_singular_uc . ' Details'),
                'name' => 'edit',
                'url' => 'listing/edit/' . $item->getIdentity(),
            );
        }

        // Set upload photo menu
        $uploadPhoto = Engine_Api::_()->authorization()->isAllowed($item, $viewer, "photo_listtype_$item->listingtype_id");
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $package = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $item->package_id);
            $album = $item->getSingletonAlbum();
            $paginator = $album->getCollectiblesPaginator();
            $total_images = $paginator->getTotalItemCount();
            if (empty($package->photo_count))
                $allowed_upload_photo = $uploadPhoto;
            elseif ($package->photo_count > $total_images)
                $allowed_upload_photo = $uploadPhoto;
            else
                $allowed_upload_photo = 0;
        }
        else {
            $allowed_upload_photo = $uploadPhoto;
        }

        if ($allowed_upload_photo) {
            $tempMenu[] = array(
                'label' => $this->translate('Add Photo'),
                'name' => 'addPhotos',
                'url' => 'listings/photo/' . $item->getIdentity());
        }

        // Set upload video menu
        $allowed_upload_video = 1;
        $allowed_upload_videoEnable = Engine_Api::_()->sitereview()->enableVideoPlugin();
        if (empty($allowed_upload_videoEnable))
            $allowed_upload_video = 0;

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {
            $allowed_upload_video_video = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'create');
            if (empty($allowed_upload_video_video))
                $allowed_upload_video = 0;
        }

        $allowed_upload_video = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "video_listtype_$listingTypeId");
        if (empty($allowed_upload_video))
            $allowed_upload_video = 0;

        if ($allowed_upload_video) {
            //Advanced video integration..........................
            if (method_exists('Siteapi_Api_Core','isSitevideoPluginEnabled') && _CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '2.3.5') || _CLIENT_TYPE == 'ios' && _IOS_VERSION > '2.1.6')) {
                $advVideoEnableArray = Engine_Api::_()->getApi('Core', 'siteapi')->isSitevideoPluginEnabled($item->getType(), $item->getIdentity());
                if (isset($advVideoEnableArray['sitevideoPluginEnabled']) && !empty($advVideoEnableArray['sitevideoPluginEnabled'])) {
                    if (isset($advVideoEnableArray['canCreateVideo']) && !empty($advVideoEnableArray['canCreateVideo']))
                        $tempMenu[] = array(
                            'name' => 'videoCreate',
                            'label' => $this->translate('Add Video'),
                            'url' => 'advancedvideos/create',
                            "subject_type" => $item->getType(),
                            "subject_id" => $item->getIdentity()
                        );
                } else {
                    $tempMenu[] = array(
                        'name' => 'videoCreate',
                        'label' => $this->translate('Add Video'),
                        'url' => 'listings/video/create/' . $item->getIdentity(),
                    );
                }
            }
        }

        // Set publish listing menu
        if ($item->draft == 1 && $can_edit)
            $tempMenu[] = array(
                'label' => $this->translate('Publish ' . $listing_singular_uc),
                'name' => 'publish',
                'url' => 'listing/publish/' . $item->getIdentity(),
            );

        // Set open/close listing menu
        if (!$item->closed && $can_edit)
            $tempMenu[] = array(
                'label' => $this->translate('Close ' . $listing_singular_uc),
                'name' => 'close',
                'url' => 'listing/close/' . $item->getIdentity(),
            );
        else if ($can_edit)
            $tempMenu[] = array(
                'label' => $this->translate('Open ' . $listing_singular_uc),
                'name' => 'close',
                'url' => 'listing/close/' . $item->getIdentity(),
            );

        // Set can delete menu
        if ($can_delete)
            $tempMenu[] = array(
                'label' => $this->translate('Delete ' . $listing_singular_uc),
                'name' => 'delete',
                'url' => 'listing/delete/' . $item->getIdentity(),
            );


        if ($can_edit && isset($viewer_id) && ($item->authorization()->isAllowed($viewer, 'edit') || $viewer_id == $item->owner_id || $level_id == 1)) {

            if (_CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '1.7') || _CLIENT_TYPE == 'ios' && _IOS_VERSION >= '1.5.3')) {
                $getOauthToken = Engine_Api::_()->getApi('oauth', 'siteapi')->getAccessOauthToken($viewer);
                if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
                    if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($item->listing_id)) {
                        $tempMenu[] = array(
                            'label' => $this->translate('Make Payment'),
                            'name' => 'makePayment',
                            'url' => $getHost . '/' . $baseUrl . "/listings/payment?token=" . $getOauthToken['token'] . "&listing_id=" . $item->getIdentity() . "&listingtype_id=" . $item->listingtype_id . "&disableHeaderAndFooter=1"
                        );
                    }
                }
            }
        }


//        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
//            if (Engine_Api::_()->sitereviewpaidlisting()->canShowPaymentLink($item->listing_id)) {
//                $tempMenu[] = array(
//                    'label' => $this->translate('Make Payment'),
//                    'name' => 'makePayment',
//                    'url' => ' ',
//                );
//            }
//            if (Engine_Api::_()->sitereviewpaidlisting()->canShowRenewLink($item->listing_id)) {
//                $tempMenu[] = array(
//                    'label' => $this->translate('Renew' . $listing_singular_uc),
//                    'name' => 'renewPayment',
//                    'url' => ' ',
//                    'urlParams' => array(
//                    )
//                );
//            }
//        }

        return $tempMenu;
    }

    /**
     * Get the list of container tabs.
     * 
     * @return array
     */
    private function _profileTAbsContainer($subject) {
        $response[] = array(
            'name' => 'update',
            'label' => $this->translate('Updates'),
        );

        $getProfileInfo = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getInformation($subject);
        if (count($getProfileInfo) > 0 || !empty($subject->body)) {
            $response[] = array(
                'name' => 'specification',
                'label' => $this->translate('Specification'),
            );
        }

        if (!empty($this->_listingType->overview)) {
            $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
            $overview = $tableOtherinfo->getColumnValue($subject->getIdentity(), 'overview');
            if (!empty($overview))
                $response[] = array(
                    'name' => 'overview',
                    'label' => $this->translate('Overview'),
                );
        }

        if ($subject->getSingletonAlbum()->getCollectiblesPaginator()->getTotalItemCount() > 0) {
            $response[] = array(
                'name' => 'photos',
                'label' => $this->translate('Photos'),
                'totalItemCount' => $subject->getSingletonAlbum()->getCollectiblesPaginator()->getTotalItemCount(),
                'url' => 'listings/photo/' . $subject->getIdentity(),
            );
        }

        //VIDEO TABLE
        $getParams = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->isAdvancedVideoEnabled(array('subject_type' => $subject->getType(), 'subject_id' => $subject->getIdentity()));
        if (!empty($getParams['enabled']) && _CLIENT_TYPE && ((_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '2.3.5') || _CLIENT_TYPE == 'ios' && _IOS_VERSION > '2.1.6')) {
            $count = isset($getParams['count']) ? $getParams['count'] : 0;
            if (isset($count) && !empty($count))
                $response[] = array(
                    'name' => 'video',
                    'label' => $this->translate('Videos'),
                    'totalItemCount' => $count,
                    'url' => 'advancedvideos/index/' . $subject->getIdentity(),
                    "subject_type" => $subject->getType(),
                    "subject_id" => $subject->getIdentity()
                );
        } else {
            //VIDEO TABLE
            $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
            $videoCount = $videoTable->getListingVideoCount($subject->getIdentity());
            if ($videoCount > 0) {
                $response[] = array(
                    'name' => 'video',
                    'label' => $this->translate('Videos'),
                    'totalItemCount' => $videoCount,
                    'url' => 'listings/video/index/' . $subject->getIdentity()
                );
            }
        }

        $reviewParams = array();
        $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
        $reviewParams['resource_id'] = $subject->getIdentity();
        $reviewParams['resource_type'] = 'sitereview_listing';
        $reviewParams['type'] = 'user';
        $totalReviews = $reviewTable->totalReviews($reviewParams);
        if ($totalReviews > 0 && $subject->review_count > 0) {
            $response[] = array(
                'name' => 'reviews',
                'label' => $this->translate('User Reviews'),
                'url' => 'listings/reviews',
                'urlParams' => array(
                    'listing_id' => $subject->listing_id
                ),
                'totalItemCount' => $totalReviews
            );
        }

        if (!empty($this->_listingType->where_to_buy) && _CLIENT_TYPE && (_CLIENT_TYPE == 'android' && _ANDROID_VERSION >= '1.8.2')) {
            $priceInfoTable = Engine_Api::_()->getDbTable('priceinfo', 'sitereview');
            $priceInfos = $priceInfoTable->getPriceDetails($subject->listing_id);
            if (Count($priceInfos) > 0) {
                $response[] = array(
                    'name' => 'where-to-buy',
                    'label' => $this->translate('Where to buy'),
                    'url' => 'listing/where-to-buy/' . $subject->listing_id,
                    'totalItemCount' => Count($priceInfos)
                );
            }
        }

        return $response;
    }

    //ACTION FOR DELETE LISTING
    public function deleteAction() {
        // Validate request methods
        $this->validateRequestMethod('DELETE');

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (empty($viewer_id))
            $this->respondWithError('unauthorized');


        //GET LISTING ID AND OBJECT
        $listing_id = $this->getRequestParam('listing_id');
        $listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $listing_singular_lc = strtolower($this->_listingType->title_singular);
        $listing_plural_lc = strtolower($this->_listingType->title_plural);
        $listing_plural_uc = ucfirst($this->_listingType->title_plural);

        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        // RETURN IF NO SUBJECT AVAILABLE.
        if (empty($sitereview))
            $this->respondWithError('no_record');


        //GET LISTING TYPE ID
        $this->_listingTypeId = $sitereview->listingtype_id;
        $title = $this->_listingType->title_plural;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "delete_listtype_$this->_listingTypeId")->isValid()) {
            $this->respondWithError('unauthorized');
        }

        $db = $sitereview->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $sitereview->delete();
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    //ACTION FOR CLOSE / OPEN LISTING
    public function closeAction() {

        //CHECK METHOD
        $this->validateRequestMethod('POST');

        //CHECK USER VALIDATION
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET LISTING
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->getRequestParam('listing_id'));
        if (empty($sitereview))
            $this->respondWithError('no_record');

        //GET LISTING TYPE ID
        $this->_listingTypeId = $sitereview->listingtype_id;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, $viewer, "edit_listtype_$this->_listingTypeId")->isValid()) {
            $this->respondWithError('unauthorized');
        }

        //BEGIN TRANSCATION
        $db = Engine_Api::_()->getDbTable('listings', 'sitereview')->getAdapter();
        $db->beginTransaction();

        try {
            $sitereview->closed = empty($sitereview->closed) ? 1 : 0;
            $sitereview->save();
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    //ACTION FOR TELL A FRIEND ABOUT EVENT
    public function tellafriendAction() {
        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET FORM
        if ($this->getRequest()->isGet()) {
            $response['form'] = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getTellAFriendForm();
            if (!empty($viewer_id))
                $response['formValues'] = array(
                    'sender_name' => $viewer->displayname,
                    'sender_email' => $viewer->email
                );
            $this->respondWithSuccess($response, true);
        } else if ($this->getRequest()->isPost()) {
            //FORM VALIDATION
            //GET EVENT ID AND OBJECT
            $event_id = $this->getRequestParam('listing_id');
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $event_id);
            if (empty($sitereview))
                $this->respondWithError('no_record');
            //GET FORM VALUES
            $values = $this->_getAllParams();

            if (empty($values['sender_email']) && !isset($values['sender_email'])) {
                $errorMessage[] = $this->translate("Your Email field is required");
            }

            if (empty($values['sender_name']) && !isset($values['sender_name'])) {
                $errorMessage[] = $this->translate("Your Name field is required");
            }

            if (empty($values['message']) && !isset($values['message'])) {
                $errorMessage[] = $this->translate("Message field is required");
            }

            if (empty($values['receiver_emails']) && !isset($values['receiver_emails'])) {
                $errorMessage[] = $this->translate("To field is required");
            }

            if (isset($errorMessage) && !empty($errorMessage) && count($errorMessage) > 0)
                $this->respondWithValidationError('validation_fail', $errorMessage);
            //EXPLODE EMAIL IDS
            $reciver_ids = explode(',', $values['receiver_emails']);
            if (!empty($values['send_me'])) {
                $reciver_ids[] = $values['sender_email'];
            }
            $sender_email = $values['sender_email'];
            $heading = $sitereview->title;

            //CHECK VALID EMAIL ID FORMAT
            $validator = new Zend_Validate_EmailAddress();
            $validator->getHostnameValidator()->setValidateTld(false);

            if (!$validator->isValid($sender_email)) {
                $errorMessage['sender_email'] = $this->translate('Invalid sender email address value');
                $this->respondWithValidationError('validation_fail', $errorMessage);
            }

            foreach ($reciver_ids as $receiver_id) {
                $receiver_id = trim($receiver_id, ' ');
                if (!$validator->isValid($receiver_id)) {
                    $errorMessage['receiver_emails'] = $this->translate('Please enter correct email address of the receiver(s).');
                    $this->respondWithValidationError('validation_fail', $errorMessage);
                }
            }

            $sender = $values['sender_name'];
            $message = $values['message'];

            try {
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SITEREVIEW_TELLAFRIEND_EMAIL', array(
                    'host' => $_SERVER['HTTP_HOST'],
                    'sender' => $sender,
                    'heading' => $heading,
                    'message' => '<div>' . $message . '</div>',
                    'object_link' => $sitereview->getHref(),
                    'email' => $sender_email,
                    'queue' => true
                ));
            } catch (Exception $ex) {
                $this->respondWithError('internal_server_error', $ex->getMessage());
            }
            $this->successResponseNoContent('no_content', true);
        }
    }

    //ACTION FOR MESSAGING THE EVENT OWNER
    public function messageownerAction() {

        //LOGGED IN USER CAN SEND THE MESSAGE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET EVENT ID AND OBJECT
        $listing_id = $this->getRequestParam("listing_id");
        if (!empty($listing_id)) {
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            if (empty($sitereview))
                $this->respondWithError('no_record');
        }
        else {
            $this->respondWithError('parameter_missing');
        }
        //OWNER CANT SEND A MESSAGE TO HIMSELF
        //GET THE ORGANIZER ID TO WHOM THE MESSAGE HAS TO BE SEND
        $organizer_id = $this->_getParam("host_id");

        $leader_id = 0;
        if (empty($organizer_id)) {
            $leader_id = $organizer_id = $this->_getParam("leader_id");
        }

        if ($viewer_id == $organizer_id) {
            $this->respondWithError('unauthorized');
        }

        if (empty($organizer_id)) {
            $organizer_id = $sitereview->owner_id;
        }
        if ($this->getRequest()->isGet()) {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getMessageOwnerForm();
            $this->respondWithSuccess($response, true);
        } else if ($this->getRequest()->isPost()) {
            $values = $this->_getAllParams();

            //CHECK METHOD/DATA
            Engine_Api::_()->getApi('Core', 'siteapi')->setView();
            $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
            $db->beginTransaction();

            try {

                $is_error = 0;
                if (empty($values['title'])) {
                    $is_error = 1;
                }

                //SENDING MESSAGE
                if ($is_error == 1) {
                    $this->respondWithError('Subject is required field !');
                }

                $recipients = preg_split('/[,. ]+/', $organizer_id);

                //LIMIT RECIPIENTS IF IT IS NOT A SPECIAL SITEEVENT OF MEMBERS
                $recipients = array_slice($recipients, 0, 1000);

                //CLEAN THE RECIPIENTS FOR REPEATING IDS
                $recipients = array_unique($recipients);

                $user = Engine_Api::_()->getItem('user', $organizer_id);

                $listing_title = $sitereview->title;
                $http = _ENGINE_SSL ? 'https://' : 'http://';
//                $listing_title_with_link = '<a href =' . $http . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug())) . ">$listing_title</a>";

                $listing_title_with_link = '<a href =' . $http . $_SERVER['HTTP_HOST'] . $sitereview->getHref() . ">$listing_title</a>";

                $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $recipients, $values['title'], $values['body'] . "<br><br>" . $this->translate('This message corresponds to the Listing: ') . $listing_title_with_link);

                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');

                //INCREMENT MESSAGE COUNTER
                Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

                $db->commit();

                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithError('internal_server_error', $e->getMessage());
            }
        }
    }

    /**
     * Get Categories , Sub-Categories, SubSub-Categories and Events array
     */
    public function categoriesAction() {
        // VALIDATE REQUEST METHOD
        $this->validateRequestMethod();
        $this->_listingTypeId = $this->_listingType->listingtype_id;

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();

        // PREPARE RESPONSE
        $values = $response = array();
        $category_id = $this->getRequestParam('category_id', null);
        $subCategory_id = $this->getRequestParam('subCategory_id', null);
        $subsubcategory_id = $this->getRequestParam('subsubcategory_id', null);
        $showAllCategories = $this->getRequestParam('showAllCategories', 1);
        $showCategories = $this->getRequestParam('showCategories', 1);
        $showListings = $this->getRequestParam('showListings', 0);
        if ($this->getRequestParam('showCount')) {
            $showCount = 1;
        } else {
            $showCount = $this->getRequestParam('showCount', 0);
        }
        $orderBy = $this->getRequestParam('orderBy', 'category_name');

        $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');
        //GET LISTING TABLE
        $tableSitereview = Engine_Api::_()->getDbtable('listings', 'sitereview');

        $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        $categories = array();

        $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
        $siteeventShowAllCategories = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteeventshow.allcategories', 1);
        $showAllCategories = !empty($siteeventShowAllCategories) ? $showAllCategories : 0;
        if ($showCategories) {

            if ($showAllCategories) {
                $category_info = $tableCategory->getCategories(null, 0, $this->_listingTypeId, 0, 1, 0, $orderBy, 1, array('category_id', 'category_name', 'cat_order', 'listingtype_id', 'file_id'));
                $categoriesCount = count($category_info);
                foreach ($category_info as $value) {

                    $sub_cat_array = array();
                    $photoName = Engine_Api::_()->storage()->get($value['file_id'], '');

                    if ($showCount) {
                        $category_array = array('category_id' => $value->category_id,
                            'category_name' => $this->translate($value->category_name),
                            'order' => $value->cat_order,
                            'count' => $tableSitereview->getListingsCount($value->category_id, 'category_id', $this->_listingTypeId, 1)
                        );

                        if (!empty($photoName)) {
                            $category_array['image_icon'] = (strstr($photoName->getPhotoUrl(), 'http')) ? $photoName->getPhotoUrl() : $getHost . $photoName->getPhotoUrl();
                        } else {
                            $getDefaultImage = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($value);
                            $category_array['image_icon'] = $getDefaultImage['image_icon'];
                        }
                    } else {
                        $category_array = array('category_id' => $value->category_id,
                            'category_name' => $this->translate($value->category_name),
                            'order' => $value->cat_order
                        );

                        if (!empty($photoName)) {
                            $category_array['image_icon'] = (strstr($photoName->getPhotoUrl(), 'http')) ? $photoName->getPhotoUrl() : $getHost . $photoName->getPhotoUrl();
                        } else {
                            $getDefaultImage = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($value);
                            $category_array['image_icon'] = $getDefaultImage['image_icon'];
                        }
                    }
                    $categories[] = $category_array;
                }
            } else {
                $category_info = $tableCategory->getCategories(null, 0, $this->_listingTypeId, 0, 1, 0, $orderBy, 1, array('category_id', 'category_name', 'cat_order', 'listingtype_id', 'file_id'));
                $categoriesCount = count($category_info);
                foreach ($category_info as $value) {
                    if ($showCount) {
                        $category_array = array('category_id' => $value->category_id,
                            'category_name' => $value->category_name,
                            'order' => $value->cat_order,
                            'count' => $tableSitereview->getListingsCount($value->category_id, 'category_id', $this->_listingTypeId, 1)
                        );

                        if (!empty($photoName)) {
                            $category_array['image_icon'] = (strstr($photoName->getPhotoUrl(), 'http')) ? $photoName->getPhotoUrl() : $getHost . $photoName->getPhotoUrl();
                        } else {
                            $getDefaultImage = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($value);
                            $category_array['image_icon'] = $getDefaultImage['image_icon'];
                        }
                    } else {
                        $category_array = array('category_id' => $value->category_id,
                            'category_name' => $this->translate($value->category_name),
                            'order' => $value->cat_order
                        );

                        if (!empty($photoName)) {
                            $category_array['image_icon'] = (strstr($photoName->getPhotoUrl(), 'http')) ? $photoName->getPhotoUrl() : $getHost . $photoName->getPhotoUrl();
                        } else {
                            $getDefaultImage = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($value);
                            $category_array['image_icon'] = $getDefaultImage['image_icon'];
                        }
                    }
                    $categories[] = $category_array;
                }
            }

            $response['categories'] = $categories;

            if (!empty($category_id)) {

                if ($showAllCategories) {
                    $category_info2 = $tableCategory->getSubcategories($category_id, array('category_id', 'category_name', 'cat_order', 'file_id'));

                    foreach ($category_info2 as $subresults) {
                        if ($showCount) {
                            $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                                'sub_cat_name' => $this->translate($subresults->category_name),
                                'count' => $tableSitereview->getListingsCount($subresults->category_id, 'subcategory_id', $this->_listingTypeId, 1),
                                'order' => $subresults->cat_order);
                        } else {
                            $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                                'sub_cat_name' => $this->translate($subresults->category_name),
                                'order' => $subresults->cat_order);
                        }
                    }
                } else {
                    $category_info2 = $tableCategory->getCategorieshaslistings($this->_listingTypeId, $category_id, 'subcategory_id', null, array(), array('category_id', 'category_name', 'cat_order'));
                    foreach ($category_info2 as $subresults) {
                        if ($showCount) {
                            $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                                'sub_cat_name' => $this->translate($subresults->category_name),
                                'count' => $tableSitereview->getListingsCount($value->category_id, 'category_id', $this->_listingTypeId, 1),
                                'order' => $subresults->cat_order);
                        } else {
                            $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                                'sub_cat_name' => $this->translate($subresults->category_name),
                                'order' => $subresults->cat_order);
                        }
                    }
                }

                $response['subCategories'] = $sub_cat_array;
            }

            if (!empty($subCategory_id)) {
                if ($showAllCategories) {
                    $subcategory_info2 = $tableCategory->getSubcategories($subCategory_id, array('category_id', 'category_name', 'cat_order', 'file_id'));
                    $treesubarrays = array();
                    foreach ($subcategory_info2 as $subvalues) {
                        if ($showCount) {
                            $treesubarrays[] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                                'tree_sub_cat_name' => $this->translate($subvalues->category_name),
                                'count' => $tableSitereview->getListingsCount($subvalues->category_id, 'subsubcategory_id', $this->_listingTypeId, 1),
                                'order' => $subvalues->cat_order,
                            );
                        } else {
                            $treesubarrays[] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                                'tree_sub_cat_name' => $this->translate($subvalues->category_name),
                                'order' => $subvalues->cat_order,
                            );
                        }
                    }
                } else {
                    $subcategory_info2 = $tableCategory->getCategorieshaslistings($this->_listingTypeId, $subCategory_id, 'subcategory_id', null, array(), array('category_id', 'category_name', 'cat_order'));
                    $treesubarrays = array();
                    foreach ($subcategory_info2 as $subvalues) {
                        if ($showCount) {
                            $treesubarrays[] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                                'tree_sub_cat_name' => $this->translate($subvalues->category_name),
                                'order' => $subvalues->cat_order,
                                'count' => $tableSitereview->getListingsCount($subvalues->category_id, 'subsubcategory_id', $this->_listingTypeId, 1),
                            );
                        } else {
                            $treesubarrays[] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                                'tree_sub_cat_name' => $this->translate($subvalues->category_name),
                                'order' => $subvalues->cat_order
                            );
                        }
                    }
                }
                $response['subsubCategories'] = $treesubarrays;
            }
        }

        if ($showListings && isset($category_id) && !empty($category_id)) {
            $params = array();
            $params['popularity'] = $popularity = $this->getRequestParam('popularity', 'view_count');
            $params['interval'] = $interval = $this->getRequestParam('interval', 'overall');
            $params['limit'] = $totalPages = $this->getRequestParam('limit', 20);
            $params['truncation'] = $this->getRequestParam('truncation', 25);
            $params['location'] = $this->getRequestParam('restapilocation');


            //GET CATEGORIES
            $categories = array();
            $category_info = $tableCategory->getCategories(null, 0, $this->_listingTypeId, 0, 1, 0, $orderBy, 1, array('category_id', 'category_name', 'cat_order', 'listingtype_id'));

            $category_listing_array = array();
            $params['category_id'] = $category_id;
            if (!empty($subCategory_id))
                $params['subcategory_id'] = $subCategory_id;
            if (!empty($subsubcategory_id))
                $params['subsubcategory_id'] = $subsubcategory_id;
            //GET PAGE RESULTS
            $category_listing_info = Engine_Api::_()->getDbtable('listings', 'sitereview')->listingsBySettings($params);
            foreach ($category_listing_info as $result_info) {
                $sitereview = $result_info->toArray();

                if (isset($result_info->owner_id) && !empty($result_info->owner_id)) {
                    // Add owner images
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($result_info, true);
                    $sitereview = array_merge($sitereview, $getContentImages);

                    $sitereview["owner_title"] = $result_info->getOwner()->getTitle();
                }

                if (empty($sitereview['price']))
                    unset($sitereview['price']);

                // Set the price & currency 
                if (isset($sitereview['price']) && $sitereview['price'] > 0)
                    $sitereview['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');

                // Add images  
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($result_info);
                $sitereview = array_merge($sitereview, $getContentImages);

                $isAllowedView = $result_info->authorization()->isAllowed($viewer, 'view');
                $sitereview["allow_to_view"] = empty($isAllowedView) ? 0 : 1;

                $isAllowedEdit = $result_info->authorization()->isAllowed($viewer, 'edit');
                $sitereview["edit"] = empty($isAllowedEdit) ? 0 : 1;

                $isAllowedDelete = $result_info->authorization()->isAllowed($viewer, 'delete');
                $sitereview["delete"] = empty($isAllowedDelete) ? 0 : 1;

                $category_listing_array[] = $sitereview;
            }
            $response['listings'] = $category_listing_array;
            $response['totalListingCount'] = $tableSitereview->getListingsCount($category_id, 'category_id', $this->_listingTypeId, 1);
        }

        if (isset($categoriesCount) && !empty($categoriesCount))
            $response['totalItemCount'] = $categoriesCount;

        $response['canCreate'] = $this->_helper->requireAuth()->setAuthParams('sitereview_listing', $viewer, "create_listtype_$this->_listingTypeId")->checkRequire();
        $response['packagesEnabled'] = $this->_sitereviewPackageEnabled($this->_listingTypeId);

        $this->respondWithSuccess($response, true);
    }

    //ACTION FOR CLAIM A LISTING FROM THE LISTING PROFILE PAGE
    public function claimListingAction() {

        //CHECK USER VALIDATION
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        //GET LOGGED IN USER INFORMATION   
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        $this->_listingTypeId = $this->_listingType->listingtype_id;

        //GET LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $authorization = Engine_Api::_()->getItemTable('authorization_level')->fetchRow(array('type = ?' => 'public', 'flag = ?' => 'public'));
            if (!empty($authorization))
                $level_id = $authorization->level_id;
        }

        //GET LISTING ID  
        $listing_id = $this->_getParam('listing_id', null);
        $listingtypeArray = $this->_listingType;

        //SET PARAMS
        $params = array();
        $params['listing_id'] = $listing_id;
        $params['viewer_id'] = $viewer_id;
        $inforow = Engine_Api::_()->getDbtable('claims', 'sitereview')->getClaimStatus($params);

        $status = 0;
        if (!empty($inforow)) {
            $status = $inforow->status;
        }

        //GET ADMIN EMAIL
        $coreApiSettings = Engine_Api::_()->getApi('settings', 'core');
        $defaultEmail = $coreApiSettings->getSetting('core.mail.from', "email@domain.com");
        $adminEmail = $coreApiSettings->getSetting('core.mail.contact', $defaultEmail);
        if (!$adminEmail)
            $adminEmail = $defaultEmail;

        //CHECK STATUS
        if ($status == 2) {
            $this->respondWithValidationError('validation_fail', 'claim_applied');
        }

        $claimoption = $claimoption = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "claim_listtype_$this->_listingTypeId");

        //FETCH
        $params = array();
        $userclaim = $userclaim = 0;
        $params['listing_id'] = $listing_id;
        $params['listingtype_id'] = $this->_listingTypeId;
        $params['limit'] = 1;
        $listingclaiminfo = Engine_Api::_()->getDbtable('listings', 'sitereview')->getSuggestClaimListing($params);
        $userClaimValue = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($listing_id, 'userclaim');

        if (!$claimoption || !$userClaimValue || empty($listingtypeArray->claimlink)) {
            $this->respondWithError('unauthorized');
        }

        if (isset($userClaimValue)) {
            $userclaim = $userclaim = $userClaimValue;
        }

        if ($inforow['status'] == 3 || $inforow['status'] == 4) {
            $this->respondWithValidationError('validation_fail', 'claim_applied');
        }

        //POPULATE FORM
        if (!empty($viewer_id)) {
            $value['email'] = $viewer->email;
            $value['nickname'] = $viewer->displayname;
        }

        if (!$inforow['status'] && $claimoption && $userclaim && $this->getRequest()->isGet()) {
            //GET FORM 
            $response['form'] = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getClaimlistingForm($this->_listingType);
            if (is_array($value) && !empty($value)) {
                $response['formValues'] = $value;
            }
            $this->respondWithSuccess($response, true);
        }


        if ($this->getRequest()->isPost()) {

            // CONVERT POST DATA INTO THE ARRAY.
            $values = array();
            $getForm = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getClaimlistingForm($this->_listingType);
            foreach ($getForm as $element) {
                if (isset($_REQUEST[$element['name']]))
                    $values[$element['name']] = $_REQUEST[$element['name']];
            }
            // START FORM VALIDATION
            $data = $values;
            $validators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'Sitereview')->getClaimListingFormValidators();
            $data['validators'] = $validators;
            $validationMessage = $this->isValid($data);
            if (!empty($validationMessage) && @is_array($validationMessage)) {
                $this->respondWithValidationError('validation_fail', $validationMessage);
            }

            //GET EMAIL
            $email = $values['email'];

            //CHECK EMAIL VALIDATION
            $validator = new Zend_Validate_EmailAddress();
            $validator->getHostnameValidator()->setValidateTld(false);
            if (!$validator->isValid($email)) {
                $validationMessage = Zend_Registry::get('Zend_Translate')->_('Please enter a valid email address.');
                $this->respondWithValidationError('validation_fail', $validationMessage);
            }

            //GET CLAIM TABLE
            $tableClaim = Engine_Api::_()->getDbTable('claims', 'sitereview');
            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            try {
                //SAVE VALUES
                //GET SITEREVIEW ITEM
                $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
                $listing_type = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypeColumn($sitereview->listingtype_id, 'title_singular');

                $httpVar = _ENGINE_SSL ? 'https://' : 'http://';
                $list_baseurl = $httpVar . $_SERVER['HTTP_HOST'] .
                        Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$sitereview->listingtype_id", true);

                //MAKING LISTING TITLE LINK
                $list_title_link = '<a href="' . $list_baseurl . '"  >' . $sitereview->title . ' </a>';

                if ($listingtypeArray->claim_email) {
                    //SEND CLAIM OWNER EMAIL
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($adminEmail, 'SITEREVIEW_' . $listing_type . '_CLAIMOWNER_EMAIL', array(
                        'list_title' => $sitereview->title,
                        'list_title_with_link' => $list_title_link,
                        'object_link' => $list_baseurl,
                        'site_contact_us_link' => $httpVar . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/help/contact',
                        'email' => $defaultEmail,
                        'queue' => false
                    ));
                }

                $row = $tableClaim->createRow();
                $row->listing_id = $listing_id;
                $row->user_id = $viewer_id;
                $row->about = $values['about'];
                $row->nickname = $values['nickname'];
                $row->email = $email;
                $row->contactno = $values['contactno'];
                $row->usercomments = $values['usercomments'];
                $row->status = 3;
                $row->save();
                $db->commit();
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }
        }
    }

    public function addAction() {
        $this->validateRequestMethod('POST');
        //Must have a viewer
        if (!$this->_helper->requireUser()->isValid()) {
            $this->respondWithError('unauthorized');
        }

        //GET LISTING ID  
        $listing_id = $this->_getParam('listing_id', null);
        $listingtypeArray = $this->_listingType;

        //Get viewer and subject
        $viewer = Engine_Api::_()->user()->getViewer();

        $listing_id = $this->getRequestParam('listing_id');
        $subject = $sitereviewObj = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        if (empty($sitereviewObj)) {
            $this->respondWithError('no_record');
        }
        Engine_Api::_()->core()->setSubject($sitereviewObj);
        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject();
        $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);
        $this->_listingTypeId = $this->_listingType->listingtype_id;
        $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
        $listingTitlePlural = strtolower($listingTypeTable->getListingTypeColumn($this->_listingTypeId, 'title_plural'));

        //GET SUBSCRIPTION TABLE
        $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');

        //CHECK IF THEY ARE ALREADY SUBSCRIBED
        if ($subscriptionTable->checkSubscription($user, $viewer, $this->_listingTypeId)) {
            $this->respondWithError('unauthorized');
        }

        //PROCESS
        $db = $user->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $subscriptionTable->createSubscription($user, $viewer, $this->_listingTypeId);
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    public function removeAction() {
        //MUST HAVE A VIEWER
        if (!$this->_helper->requireUser()->isValid()) {
            $this->respondWithError('unauthorized');
        }

        //GET VIEWER AND SUBJECT
        $viewer = Engine_Api::_()->user()->getViewer();

        //GET LISTING SUBJECT
        $listing_id = $this->getRequestParam('listing_id');
        $subject = $sitereviewObj = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        if (empty($sitereviewObj)) {
            $this->respondWithError('no_record');
        }
        Engine_Api::_()->core()->setSubject($sitereviewObj);
        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject();
        $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);
        $this->_listingTypeId = $this->_listingType->listingtype_id;
        $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
        $listingTitlePlural = strtolower($listingTypeTable->getListingTypeColumn($this->_listingTypeId, 'title_plural'));

        //GET SUBSCRIPTION TABLE
        $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');


        //CHECK IF THEY ARE ALREADY NOT SUBSCRIBED
        if (!$subscriptionTable->checkSubscription($user, $viewer, $this->_listingTypeId)) {
            $this->respondWithError('unauthorized');
        }

        //PROCESS
        $db = $user->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $subscriptionTable->removeSubscription($user, $viewer, $this->_listingTypeId);
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    // Action for where to buy.
    public function whereToBuyAction() {
        $listing_id = $this->getRequestParam('listing_id');
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        $priceInfoTable = Engine_Api::_()->getDbTable('priceinfo', 'sitereview');
        $priceInfos = $priceInfoTable->getPriceDetails($sitereview->listing_id);
        $infoFinalArray['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
        if (Count($priceInfos) <= 0) {
            $this->respondWithError('no_record');
        }
        $min_price = (int) $priceInfoTable->getMinPrice($sitereview->listing_id);
        foreach ($priceInfos as $priceInfo) {
            $tempInfoArray = array();
            $tempInfoArray = $priceInfo->toArray();

            if (!empty($min_price) && !empty($priceInfo->price) && $min_price == (int) $priceInfo->price) {
                $tempInfoArray['minPriceOption'] = 1;
                $imageName = '/images/icons/tag_red.png';

                $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
                $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
                $baseUrl = @trim($baseUrl, "/");
                $path = '/application/modules/Sitereview/externals/';
                // Get file url
                $tempInfoArray['tag_image'] = $getHost . '/' . $baseUrl . $path . $imageName;
                if (strstr($imageUrl, 'index.php/'))
                    $tempInfoArray['tag_image'] = str_replace('index.php/', '', $tempInfoArray['tag_image']);
            }

            if ($priceInfo->photo_id) {
                $file = Engine_Api::_()->getItemTable('storage_file')->getFile($priceInfo->photo_id);
                if ($file) {
                    $imgSrc = $file->map();
                    $tempInfoArray['image'] = $imgSrc;
                }
            }
            $infoArray[] = $tempInfoArray;
        }
        $infoFinalArray['minPrice'] = (string) $min_price;
        $infoFinalArray['priceInfo'] = $infoArray;

        $this->respondWithSuccess($infoFinalArray);
    }

    public function applyNowAction() {
        //LOGGED IN USER CAN SEND THE MESSAGE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        try {
            //GET Listing ID AND OBJECT
            $listing_id = $this->getRequestParam("listing_id");
            if (!empty($listing_id)) {
                $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
                if (empty($sitereview))
                    $this->respondWithError('no_record');
            }
            else {
                $this->respondWithError('parameter_missing');
            }

            $this->_listingTypeId = $this->_listingType->listingtype_id;
            $listing_type_singular = ucfirst(Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($this->_listingTypeId, 'title_singular'));

            //CHECK STATUS
            $params = array();
            $params['listing_id'] = $listing_id;
            $params['viewer_id'] = $viewr_id;
            $checkStatus = Engine_Api::_()->getDbtable('jobs', 'sitereview')->getApplyStatus($params);
            if (!empty($checkStatus)) {
                $lcListingTypeTitle = lcfirst($listing_type_singular);
                $error = $this->translate("You have already applied for this $lcListingTypeTitle.");
                $this->respondWithError('unauthorized', $error);
            }

            if ($this->getRequest()->isGet()) {
                $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getApplyNowForm($this->_listingTypeId);
                $this->respondWithSuccess($response, true);
            } else if ($this->getRequest()->isPost()) {
                //GET FORM VALUES
                $values = $this->_getAllParams();

                $sender_email = '';
                $sender_name = '';
                $fileName = '';
                $contactNumber = '';
                $body = '';

                if (isset($values['sender_email']))
                    $sender_email = $values['sender_email'];
                if (isset($values['sender_name']))
                    $sender_name = $values['sender_name'];
                if (isset($_FILES['filename']))
                    $fileName = $_FILES['filename'];
                if (isset($values['contact']))
                    $contactNumber = $values['contact'];
                if (isset($values['body']))
                    $body = $values['body'];
                $listingTitle = $sitereview->title;

                if (empty($sender_name))
                    $displayName = $viewer->displayname;
                else
                    $displayName = $values['sender_name'];
                if (!empty($fileName)) {
                    //FILE EXTENSION SHOULD NOT DIFFER FROM ALLOWED TYPE
                    $ext = str_replace(".", "", strrchr($_FILES['filename']['name'], "."));
                    if (!in_array($ext, array('pdf', 'txt', 'ps', 'rtf', 'epub', 'odt', 'odp', 'ods', 'odg', 'odf', 'sxw', 'sxc', 'sxi', 'sxd', 'doc', 'ppt', 'pps', 'xls', 'docx', 'pptx', 'ppsx', 'xlsx', 'tif', 'tiff'))) {
                        $error['filename'] = $this->translate("Invalid file extension. Allowed extensions are :'pdf', 'txt', 'ps', 'rtf', 'epub', 'odt', 'odp', 'ods', 'odg', 'odf', 'sxw', 'sxc', 'sxi', 'sxd', 'doc', 'ppt', 'pps', 'xls', 'docx', 'pptx', 'ppsx', 'xlsx', 'tif', 'tiff'");
                        $this->respondWithValidationError('validation_fail', $error);
                    }
                }

                if (!empty($sender_email)) {
                    //CHECK VALID EMAIL ID FORMAT
                    $validator = new Zend_Validate_EmailAddress();
                    $validator->getHostnameValidator()->setValidateTld(false);

                    if (!$validator->isValid($sender_email)) {
                        $error['sender_email'] = Zend_Registry::get('Zend_Translate')->_('Invalid sender email address value');
                        $this->respondWithValidationError('validation_fail', $error);
                    }
                }

                //GET SITEREVIEW JOB TABLE
                $sitereviewJobTable = Engine_Api::_()->getDbtable('jobs', 'sitereview');

                $sitereviewJobRow = $sitereviewJobTable->createRow();
                $sitereviewJobRow->setFromArray($values);
                $sitereviewJobRow->user_id = $viewer_id;
                $sitereviewJobRow->listing_id = $listing_id;
                $sitereviewJobRow->save();

                if (!empty($fileName)) {
                    $file_id = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->setFile($fileName, $listing_id);
                    $sitereviewJobRow->file_id = $file_id;
                    $sitereviewJobRow->save();
                }

                $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);

                $http = _ENGINE_SSL ? 'https://' : 'http://';

                $listing_title_with_link = '<a href =' . $http . $_SERVER['HTTP_HOST'] . $sitereview->getHref() . ">$listing_title</a>";

                $file_id = Engine_Api::_()->getItem('sitereview_job', $sitereviewJobRow->job_id)->file_id;
                if (!empty($file_id)) {
                    $service_id = Engine_Api::_()->getItem('storage_file', $file_id)->service_id;
                    if ($service_id == 1)
                        $path = $http . $_SERVER['HTTP_HOST'] . Engine_Api::_()->getItem('storage_file', $file_id)->map();
                    else
                        $path = Engine_Api::_()->getItem('storage_file', $file_id)->map();
                } else
                    $path = '';

                $chekcSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitemailtemplates.check.setting', 0);
                if ((Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemailtemplates') && !empty($chekcSetting)) || empty($path)) {
                    $contactInfo = '';
                    if (!empty($sender_name))
                        $contactInfo .= 'Name: ' . $sender_name . '<br />';
                    if (!empty($sender_email))
                        $contactInfo .= 'Email: ' . $sender_email . '<br />';
                    if (!empty($contactNumber))
                        $contactInfo .= 'Contact Number: ' . $contactNumber . '<br />';
                    if (!empty($body))
                        $contactInfo .= 'Message: ' . $body;

                    //SEND MAIL WORK TO LISTING OWNER
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($user->email, 'SITEREVIEW_APPLYNOW_EMAIL', array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'sender' => $displayName,
                        'listing_type' => $listing_type_singular,
                        'heading' => $listingTitle,
                        'file_path' => $path,
                        'filename' => $fileName,
                        'contact_info' => $contactInfo,
                        'object_link' => $listing_title_link,
                        'queue' => false
                    ));
                    $this->successResponseNoContent('no_content', true);
                } else {
                    $user_message = 'Hello ' . $user->displayname . ',' . '<br /><br />' .
                            $displayName . ' has applied for the ' . $listing_type_singular . '  ' . $listing_title_link . '.' . ' Please find the summary below:' . '<br /><br />';
                    if (!empty($sender_name))
                        $user_message .= 'Name: ' . $sender_name . '<br />';
                    if (!empty($sender_email))
                        $user_message .= 'Email: ' . $sender_email . '<br />';
                    if (!empty($contactNumber))
                        $user_message .= 'Contact Number: ' . $contactNumber . '<br />';
                    if (!empty($body))
                        $user_message .= 'Message: ' . $body;

                    if ($path != '') {
                        $subject = $sender_name . ' has applied for ' . $listingTitle;
                        $mailApi = Engine_Api::_()->getApi('mail', 'core');
                        $mail = $mailApi->create();
                        $mail
                                ->setFrom($sender_email, $sender_name)
                                ->setSubject($subject)
                                ->setBodyHtml($user_message);

                        $mail->addTo($user->email);
                        $handle = @fopen($path, "r");
                        while (($buffer = fgets($handle)) !== false) {
                            $content .= $buffer;
                        }
                        $attachment = $mail->createAttachment($content);
                        $attachment->filename = $fileName;

                        $mailApi->send($mail);
                        $this->successResponseNoContent('no_content', true);
                    }
                }
            }
        } catch (Exception $e) {
            $this->successResponseNoContent('no_content', true);
        }
    }

    private function _imageGutterMenus($sitereview, $photo) {

        $menu = array();
        if ($photo->user_id == $viewer_id) {
            $menu[] = array(
                'label' => $this->translate('Edit'),
                'name' => 'edit',
                'url' => 'listings/photo/edit/' . $sitereview->getIdentity(),
                'urlParams' => array(
                    "photo_id" => $photo->getIdentity()
                )
            );

            $menu[] = array(
                'label' => $this->translate('Delete'),
                'name' => 'delete',
                'url' => 'listings/photo/delete/' . $sitereview->getIdentity(),
                'urlParams' => array(
                    "photo_id" => $photo->getIdentity()
                )
            );
        }
        $menu[] = array(
            'label' => $this->translate('Share'),
            'name' => 'share',
            'url' => 'activity/index/share',
            'urlParams' => array(
                "type" => $photo->getType(),
                "id" => $photo->getIdentity()
            )
        );

        $menu[] = array(
            'label' => $this->translate('Report'),
            'name' => 'report',
            'url' => 'report/create/subject/' . $photo->getGuid()
        );

        $menu[] = array(
            'label' => $this->translate('Make Profile Photo'),
            'name' => 'make_profile_photo',
            'url' => 'members/edit/external-photo',
            'urlParams' => array(
                "photo" => $photo->getGuid()
            )
        );

        if (isset($menu) && !empty($menu))
            return $menu;
    }

    private function _sitereviewPackageEnabled($listingType_id) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        // Return false if does not have create permission
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "create_listtype_$listingType_id"))
            return 0;

        $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingType_id);

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
            if (Engine_Api::_()->sitereviewpaidlisting()->hasPackageEnable() && !empty($listingType->package)) {
                $packageCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageCount($listingType_id);
                if ($packageCount == 1) {
                    $package = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getEnabledPackage($listingType_id);
                    // Return 0 if only one package & is free
                    if (($package->price == '0.00')) {
                        return 0;
                    } else {
                        return 1;
                    }
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

}
