<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: WishlistController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_WishlistController extends Siteapi_Controller_Action_Standard {

    //COMMON FUNCTION WHICH CALL AUTOMATICALLY BEFORE EVERY ACTION OF THIS CONTROLLER
    public function init() {

        $listingtype_id = $this->getRequestParam('listingtype_id');
        if (!empty($listingtype_id)) {
            //AUTHORIZATION CHECK
            if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
                $this->respondWithError('unauthorized');
        }

        $listing_id = $this->getRequestParam('listing_id');
        if (!empty($listing_id)) {

            //GET LISTING TYPE ID
            $listingType = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            if (!empty($listingType)) {
                $listingtype_id = $listingType->listingtype_id;

                //AUTHORIZATION CHECK
                if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
                    $this->respondWithError('unauthorized');
            }
        }

        $sitereviewWishlistView = Zend_Registry::isRegistered('sitereviewWishlistView') ? Zend_Registry::get('sitereviewWishlistView') : null;
        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_wishlist', null, "view")->isValid())
            $this->respondWithError('unauthorized');
    }

    /**
     * RETURN THE LIST AND DETAILS OF ALL WISHLIST WITH SEARCH PARAMETERS.
     * 
     * @return array
     */
    public function browseAction() {
        $this->validateRequestMethod();
        // Prepare the response
        $params = $response = array();
        $params = $this->_getAllParams();
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        //GET PAGINATOR
        $params['pagination'] = 1;
        $paginator = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->getBrowseWishlists($params);
        $page = $this->_getParam('page', 1);
        $limit = $this->_getParam('limit', 20);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        $totalItemCount = $paginator->getTotalItemCount();
        $totalPages = ceil(($totalItemCount) / $limit);
        $response['totalItemCount'] = $totalItemCount;
        if (!empty($totalItemCount)) {
            foreach ($paginator as $wishlistObj) {
                $wishlist = $wishlistObj->toArray();
                if (isset($wishlist['body']) && !empty($wishlist['body']))
                    $wishlist['body'] = strip_tags($wishlist['body']);
                $lists = $wishlistObj->getWishlistMap(array('orderby' => 'listing_id'));
                $count = $lists->getTotalItemCount();
                $tempListings = array();
                $counter = 0;
                if (_ANDROID_VERSION >= '1.8.6' || _IOS_VERSION >= '1.8.0') {
                    if (empty($count) || !isset($count) || $count == 0) {
                        $tempListings['images_' . $counter] = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($wishlistObj);
                    } else {
                        foreach ($lists as $listings) {
                            if ($counter >= 3)
                                break;
                            else {
                                $counter++;
                                $tempListings['images_' . $counter] = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($listings);
                            }
                        }
                    }
                } else {
                    if (empty($count) || !isset($count) || $count == 0) {
                        $tempListings['listing_images_' . $counter] = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($wishlistObj);
                    } else {
                        foreach ($lists as $listings) {
                            if ($counter >= 3)
                                break;
                            else {
                                $counter++;
                                $tempListings['listing_images_' . $counter] = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($listings);
                            }
                        }
                    }
                }
                $wishlist = array_merge($wishlist, $tempListings);
                $check_availability = Engine_Api::_()->sitereview()->check_availability('sitereview_wishlist', $wishlistObj->wishlist_id);
                $checkFollowAvailablity = $wishlistObj->follows()->isFollow($viewer);
                $tempMenu = array();
                if (!empty($viewer_id)) {
                    if (empty($check_availability)) {
                        $wishlist['isLike'] = 0;
                        $tempMenu[] = array(
                            'name' => 'like',
                            'label' => $this->translate('Like'),
                            'url' => '/like',
                            'urlParams' => array(
                                "subject_type" => 'sitereview_wishlist',
                                'subject_id' => $wishlistObj->getIdentity()
                            )
                        );
                    } else {
                        $wishlist['isLike'] = 1;
                        $tempMenu[] = array(
                            'name' => 'like',
                            'label' => $this->translate('Unlike'),
                            'url' => '/unlike',
                            'urlParams' => array(
                                "subject_type" => 'sitereview_wishlist',
                                'subject_id' => $wishlistObj->getIdentity()
                            )
                        );
                    }

                    if (!empty($checkFollowAvailablity)) {
                        $wishlist['followed'] = 1;
                        $tempMenu[] = array(
                            'name' => 'follow',
                            'label' => $this->translate('Unfollow'),
                            'url' => '/listings/wishlist/follow/' . $wishlistObj->getIdentity(),
                            'urlParams' => array()
                        );
                    } else {
                        $wishlist['followed'] = 0;
                        $tempMenu[] = array(
                            'name' => 'follow',
                            'label' => $this->translate('Follow'),
                            'url' => '/listings/wishlist/follow/' . $wishlistObj->getIdentity(),
                            'urlParams' => array()
                        );
                    }

                    $wishlist['gutterMenu'] = $tempMenu;
                }
                $tempResponse[] = $wishlist;
            }
        }
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }
        $can_create = ($viewer_id) ? 1 : 0;
        $response['canCreate'] = $can_create;
        if (!empty($tempResponse))
            $response['response'] = $tempResponse;
        $this->respondWithSuccess($response, true);
    }

    /**
     * Return the "Diary Browse Search" form. 
     * 
     * @return array
     */
    public function searchFormAction() {

        // Validate request methods
        $this->validateRequestMethod();
        $response = array();
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, 'view'))
            $this->respondWithError('unauthorized');

        try {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getWishlistSearchForm();

            $this->respondWithSuccess($response, true);
        } catch (Expection $ex) {
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

    /**
     * Add item to wishlist.
     * 
     * @return status
     */
    public function addAction() {

        //ONLY LOGGED IN USER CAN CREATE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');


        //GET PAGE ID AND CHECK PAGE ID VALIDATION
        $listing_id = $this->_getParam('listing_id');
        if (empty($listing_id)) {
            $this->respondWithError('no_record');
        }

        //GET VIEWER INFORMATION
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        //GET USER DIARIES
        $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
        $wishlistDatas = $wishlistTable->userWishlists($viewer);
        $wishlistDataCount = Count($wishlistDatas);
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('listing_id'));
        if (empty($sitereview)) {
            $this->respondWithError('no_record');
        }

        //FORM GENERATION
        if ($this->getRequest()->isGet()) {
            $response= Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getAddToWishlistForm();
           
            $this->respondWithSuccess($response, true);
        } else if ($this->getRequest()->isPost()) {
            $values = $this->_getAllParams();
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
                //CHECK FOR NEW ADDED DIARY TITLE OR 
                if (!empty($values['body']) && empty($values['title'])) {
                    $this->respondWithError('parameter_missing');
                }
                //CHECK FOR TITLE IF NO DIARY
                if (empty($wishlistDatas) && empty($values['title']))
                    $this->respondWithError('Title feild required');

                //GET DIARY PAGE TABLE
                $wishlistEventTable = Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview');

                $wishlistOldIds = array();

                //GET NOTIFY API
                $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');


                //WORK ON PREVIOUSLY CREATED DIARY
                if (!empty($wishlistDatas)) {
                    foreach ($wishlistDatas as $wishlistData) {
                        $key_name = 'wishlist_' . $wishlistData->wishlist_id;
                        if (isset($values[$key_name]) && !empty($values[$key_name])) {
                            $wishlistEventTable->insert(array(
                                'wishlist_id' => $wishlistData->wishlist_id,
                                'listing_id' => $listing_id,
                            ));

                            //DIARY COVER PHOTO
                            $wishlistTable->update(
                                    array(
                                'listing_id' => $listing_id,
                                    ), array(
                                'wishlist_id = ?' => $wishlistData->wishlist_id,
                                'listing_id = ?' => 0
                                    )
                            );

                            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                            $action = $activityApi->addActivity($viewer, $wishlistData, "sitereview_wishlist_add_listing_listtype_" . $sitereview->listingtype_id, '', array('listing' => array($sitereview->getType(), $sitereview->getIdentity())));

                            if ($action)
                                $activityApi->attachActivity($action, $sitereview);
                        }
                        $in_key_name = 'inWishlist_' . $wishlistData->wishlist_id;
                        if (isset($values[$in_key_name]) && empty($values[$in_key_name])) {
                            $wishlistOldIds[$wishlistData->wishlist_id] = $wishlistData;
                            $wishlistEventTable->delete(array('wishlist_id = ?' => $wishlistData->wishlist_id, 'listing_id = ?' => $listing_id));

                            //DELETE ACTIVITY FEED
                            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                            $actionTableName = $actionTable->info('name');

                            $action_id = $actionTable->select()
                                    ->setIntegrityCheck(false)
                                    ->from($actionTableName, 'action_id')
                                    ->joinInner('engine4_activity_attachments', "engine4_activity_attachments.action_id = $actionTableName.action_id", array())
                                    ->where('engine4_activity_attachments.id = ?', $listing_id)
                                    ->where($actionTableName . '.type = ?', "sitereview_wishlist_add_listing")
                                    ->where($actionTableName . '.subject_type = ?', 'user')
                                    ->where($actionTableName . '.object_type = ?', 'sitereview_wishlist')
                                    ->where($actionTableName . '.object_id = ?', $wishlistData->wishlist_id)
                                    ->query()
                                    ->fetchColumn();

                            if (!empty($action_id)) {
                                $activity = Engine_Api::_()->getItem('activity_action', $action_id);
                                if (!empty($activity)) {
                                    $activity->delete();
                                }
                            }
                        }
                    }
                }

                if (!empty($values['title'])) {

                    $db = Engine_Db_Table::getDefaultAdapter();
                    $db->beginTransaction();

                    try {
                        //CREATE DIARY
                        $wishlist = $wishlistTable->createRow();
                        $wishlist->setFromArray($values);
                        $wishlist->owner_id = $viewer_id;
                        $wishlist->listing_id = $listing_id; //DIARY COVER PHOTO
                        $wishlist->save();

                        //PRIVACY WORK
                        $auth = Engine_Api::_()->authorization()->context;
                        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

                        if (empty($values['auth_view'])) {
                            $values['auth_view'] = 'owner';
                        }

                        $viewMax = array_search($values['auth_view'], $roles);
                        foreach ($roles as $i => $role) {
                            $auth->setAllowed($wishlist, $role, 'view', ($i <= $viewMax));
                        }

                        $db->commit();
                        $wishlistEventTable->insert(array(
                            'wishlist_id' => $wishlist->wishlist_id,
                            'listing_id' => $listing_id,
                            'date' => new Zend_Db_Expr('NOW()')
                        ));

                        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                        $action = $activityApi->addActivity($viewer, $sitereview, "sitereview_wishlist_add_listing", null, array('child_id' => $wishlist->wishlist_id));

                        if ($action) {
                            $activityApi->attachActivity($action, $sitereview);
                        }
                    } catch (Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                }
                $this->successResponseNoContent('no_content', true);
            } else {
                try {
                    $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
                    $wishlist_id = $wishlistTable->recentWishlistId($viewer_id, $listing_id);
                    $action = $this->_getParam('perform', 'add');
                    Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview')->performWishlistMapAction($wishlist_id, $listing_id, $action);
                    $this->successResponseNoContent('no_content', true);
                } catch (Exception $ex) {
                    $this->respondWithError('internal_server_error', $ex->getMessage());
                }
            }
        }
    }

    /**
     * Return the Create Wishlist Form.
     * 
     * @return array
     */
    public function createAction() {
        //ONLY LOGGED IN USER CAN CREATE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        //GET VIEWER INFORMATION
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //FORM GENERATION
        if ($this->getRequest()->isGet()) {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getCreateWishlistForm();
            $this->respondWithSuccess($response, true);
        } else if ($this->getRequest()->isPost()) {

            //GET DIARY TABLE
            $wishlistTable = Engine_Api::_()->getItemTable('sitereview_wishlist');
            $db = $wishlistTable->getAdapter();
            $db->beginTransaction();

            try {
                //GET FORM VALUES
                $values = $this->_getAllParams();
                if (empty($values['title'])) {
                    $this->respondWithValidationError('validation_fail', 'Please complete this field - it is required.');
                }
                $values['owner_id'] = $viewer->getIdentity();

                //CREATE DIARY
                $wishlist = $wishlistTable->createRow();
                $wishlist->setFromArray($values);
                $wishlist->save();

                //PRIVACY WORK
                $auth = Engine_Api::_()->authorization()->context;
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

                if (empty($values['auth_view'])) {
                    $values['auth_view'] = 'owner';
                }
                $viewMax = array_search($values['auth_view'], $roles);

                foreach ($roles as $i => $role) {
                    $auth->setAllowed($wishlist, $role, 'view', ($i <= $viewMax));
                }
                $db->commit();
                // Change request method POST to GET
                $this->setRequestMethod();
                $this->_forward('profile', 'wishlist', 'sitereview', array(
                    'wishlist_id' => $wishlist->getIdentity()
                ));
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
        }
    }

    /**
     * Return the Wishlist View page.
     * 
     * @return array
     */
    public function profileAction() {
        // Validate request methods
        $this->validateRequestMethod();

        // Set the translations for zend library.
        if (!Zend_Registry::isRegistered('Zend_Translate'))
            Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();

        //GET DIARY ID AND SUBJECT
        if (Engine_Api::_()->core()->hasSubject())
            $subject = $wishlist = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

        $wishlist_id = $this->_getParam('wishlist_id');
        if (isset($wishlist_id) && !empty($wishlist_id)) {
            $subject = $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);
            if (isset($wishlist) && !empty($wishlist))
                Engine_Api::_()->core()->setSubject($wishlist);
            else
                $this->respondWithError('no_record');
        } else {
            $this->respondWithError('no_record');
        }

        if (empty($wishlist)) {
            $this->respondWithError('no_record');
        }

        $wishlist_id = $this->_getParam('wishlist_id');

        $viewer = Engine_Api::_()->user()->getViewer();

        //INCREASE VIEW COUNT IF VIEWER IS NOT OWNER
        if (!$wishlist->getOwner()->isSelf($viewer)) {
            $wishlist->view_count++;
            $wishlist->save();
        }

        // PREPARE RESPONSE ARRAY
        $bodyParams['response'] = $subject->toArray();

        if (isset($bodyParams['response']['body']) && !empty($bodyParams['response']['body']))
            $bodyParams['response']['body'] = strip_tags($bodyParams['response']['body']);

        $bodyParams['response'] = array_merge($bodyParams['response'], Engine_Api::_()->getApi('Core', 'siteapi')->getContentUrl($subject));

        $viewer_id = $viewer->getIdentity();
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        $showMessageOwner = 0;
        $showMessageOwner = Engine_Api::_()->authorization()->getPermission($level_id, 'messages', 'auth');
        if ($showMessageOwner != 'none') {
            $showMessageOwner = 1;
        }

        $messageOwner = 1;
        if ($wishlist->owner_id == $viewer_id || empty($viewer_id) || empty($showMessageOwner)) {
            $messageOwner = 0;
        }
        //GET LEVEL SETTING
        $can_create = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_wishlist', "create");
        $bodyParams['response']['wishlist_creator_name'] = $wishlist->getOwner()->getTitle();

        $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($subject, true);
        $bodyParams['response'] = array_merge($bodyParams['response'], $getContentImages);
        $perms = array();
        //PRIVACY WORK
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $perms = array();
        foreach ($roles as $roleString) {
            $role = $roleString;
            if ($auth->isAllowed($su, $role, 'view')) {
                $perms['auth_view'] = $roleString;
            }
        }
        $bodyParams['response'] = array_merge($bodyParams['response'], $perms);

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        try {
            //FETCH RESULTS
            $paginator = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->wishlistListings($wishlist->wishlist_id);
            $paginator->setItemCountPerPage($itemCount);
            $paginator->setCurrentPageNumber($this->_getParam('currentpage', 1));
            $total_item = $paginator->getTotalItemCount();
            $bodyParams['response']['totallistings'] = $total_item;

            foreach ($paginator as $sitereviewObj) {

                $sitereview = $sitereviewObj->toArray();

                if (isset($sitereviewObj->owner_id) && !empty($sitereviewObj->owner_id)) {
                    // Add owner images
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($sitereviewObj, true);
                    $sitereview = array_merge($sitereview, $getContentImages);

                    $sitereview["owner_title"] = $sitereviewObj->getOwner()->getTitle();
                }

                if (empty($sitereview['price']))
                    unset($sitereview['price']);

                // Set the price & currency 
                if (isset($sitereview['price']) && $sitereview['price'] > 0)
                    $sitereview['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');

                // Add images  
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($sitereviewObj);
                $sitereview = array_merge($sitereview, $getContentImages);

                if (isset($wishlist->listing_id) && !empty($wishlist->listing_id) && $wishlist->listing_id == $sitereviewObj->listing_id) {
                    if (!isset($bodyParams['response']['image']) && empty($bodyParams['response']['image']))
                        $bodyParams['response'] = array_merge($bodyParams['response'], $getContentImages);
                }


                $isAllowedView = $sitereviewObj->authorization()->isAllowed($viewer, 'view');
                $sitereview["allow_to_view"] = empty($isAllowedView) ? 0 : 1;

                $isAllowedEdit = $sitereviewObj->authorization()->isAllowed($viewer, 'edit');
                $sitereview["edit"] = empty($isAllowedEdit) ? 0 : 1;

                $isAllowedDelete = $sitereviewObj->authorization()->isAllowed($viewer, 'delete');
                $sitereview["delete"] = empty($isAllowedDelete) ? 0 : 1;
                $ListingMenu = array();
                $ListingMenu[] = array(
                    'name' => 'remove',
                    'label' => $this->translate('Remove'),
                    'url' => 'listings/wishlist/remove',
                    'urlParams' => array(
                        "listing_id" => $sitereviewObj->getIdentity(),
                        'wishlist_id' => $wishlist->getIdentity()
                    )
                );
                if ($wishlist->owner_id == $viewer_id || $level_id == 1)
                    $sitereview['gutter_menu'] = $ListingMenu;
                $tempResponse[] = $sitereview;
            }
            if (!empty($tempResponse)) {
                $bodyParams['response']['listing'] = $tempResponse;
            }

            if (!isset($bodyParams['response']['image']) && empty($bodyParams['response']['image'])) {
                $bodyParams['response'] = array_merge($bodyParams['response'], $getContentImages);
            }

            if ($viewer_id) {
                $wishlistMenus[] = array(
                    'name' => 'memberWishlist',
                    'label' => $this->translate($wishlist->getOwner()->getTitle() . "'s" . " Wishlists"),
                    'url' => 'listings/wishlist',
                    'urlParams' => array(
                        "text" => $wishlist->getOwner()->getTitle())
                );
                if ($can_create) {
                    $wishlistMenus[] = array(
                        'name' => 'create',
                        'label' => $this->translate('Create New Wishlist'),
                        'url' => 'listings/wishlist/create',
                    );
                }
                if (!empty($messageOwner)) {
                    $wishlistMenus[] = array(
                        'name' => 'messageOwner',
                        'label' => $this->translate('Message Owner'),
                        'url' => 'listings/wishlist/message-owner',
                        'urlParams' => array(
                            "wishlist_id" => $wishlist->getIdentity())
                    );
                }
                $wishlistMenus[] = array(
                    'name' => 'report',
                    'label' => $this->translate('Report'),
                    'url' => 'report/create/subject/' . $subject->getGuid(),
                    'urlParams' => array(
                        "type" => $wishlist->getType(),
                        "id" => $wishlist->getIdentity()
                    )
                );

                $wishlistMenus[] = array(
                    'name' => 'share',
                    'label' => $this->translate('Share'),
                    'url' => 'activity/share',
                    'urlParams' => array(
                        "type" => $wishlist->getType(),
                        "id" => $wishlist->getIdentity()
                    )
                );

                if ($wishlist->owner_id == $viewer_id || $level_id == 1) {
                    $wishlistMenus[] = array(
                        'name' => 'edit',
                        'label' => $this->translate('Edit Wishlist'),
                        'url' => 'listings/wishlist/edit/' . $wishlist->getIdentity(),
                    );
                    $wishlistMenus[] = array(
                        'name' => 'delete',
                        'label' => $this->translate('Delete Wishlist'),
                        'url' => 'listings/wishlist/delete/' . $wishlist->getIdentity(),
                    );
                }
            }

            $wishlistMenus[] = array(
                'name' => 'tellafriend',
                'label' => $this->translate('Tell A Friend'),
                'url' => 'listings/wishlist/tell-a-friend',
                'urlParams' => array(
                    "wishlist_id" => $wishlist->getIdentity())
            );
            if (!empty($wishlistMenus)) {
                $bodyParams['gutterMenus'] = $wishlistMenus;
            }

            $this->respondWithSuccess($bodyParams);
        } catch (Exception $ex) {
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

    /**
     * Return the Message Owner Form and Send message.
     * 
     * @return array
     */
    public function messageOwnerAction() {

        //LOGGED IN USER CAN SEND THE MESSAGE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET EVENT ID AND OBJECT
        $wishlist_id = $this->_getParam("wishlist_id");
        $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

        if (empty($wishlist))
            $this->respondWithError('no_record');

        $owner_id = $wishlist->owner_id;

        //OWNER CANT SEND A MESSAGE TO HIMSELF
        if ($viewer_id == $wishlist->owner_id) {
            $this->respondWithError('unauthorized');
        }

        //MAKE FORM
        if ($this->getRequest()->isGet()) {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getMessageOwnerForm();
            $this->respondWithSuccess($response, true);
        } else if ($this->getRequest()->isPost()) {
            $values = $this->_getAllParams();


            $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
            $db->beginTransaction();

            try {

                $is_error = 0;
                if (empty($values['title'])) {
                    $this->respondWithValidationError('validation_fail', 'Subject field is required');
                }

                $recipients = preg_split('/[,. ]+/', $owner_id);

                //LIMIT RECIPIENTS
                $recipients = array_slice($recipients, 0, 1000);

                //CLEAN THE RECIPIENTS FOR REPEATING IDS
                $recipients = array_unique($recipients);

                //GET USER
                $user = Engine_Api::_()->getItem('user', $wishlist->owner_id);

                $wishlist_title = $wishlist->getTitle();
                $wishlist_title_with_link = '<a href = http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('wishlist_id' => $wishlist_id, 'slug' => $wishlist->getSlug()), "sitereview_wishlist_view") . ">$wishlist_title</a>";

                $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $recipients, $values['title'], $values['body'] . "<br><br>" . 'This message corresponds to the Wishlist: ' . $wishlist_title_with_link);

                try {
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');
                } catch (Exception $e) {
                    //Blank Exception
                }
                //INCREMENT MESSAGE COUNTER
                Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

                $db->commit();
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Return the Diary Edit Form.
     * 
     * @return array
     */
    public function editAction() {

        //ONLY LOGGED IN USER CAN CREATE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');


        $wishlist_id = $this->_getParam('wishlist_id');
        if (isset($wishlist_id) && !empty($wishlist_id)) {
            $subject = $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);
            Engine_Api::_()->core()->setSubject($wishlist);
        } else {
            $this->respondWithError('no_record');
        }

        //GET VIEWER INFORMATION
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $level_id = $viewer->level_id;


        if ($level_id != 1 && $wishlist->owner_id != $viewer_id) {
            $this->respondWithError('unauthorized');
        }
        //GET USER DIARIES
        $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
        $wishlistDatas = $wishlistTable->userWishlists($viewer);
        //PRIVACY WORK
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $perms = array();
        foreach ($roles as $roleString) {
            $role = $roleString;
            if ($auth->isAllowed($wishlist, $role, 'view')) {
                $perms['auth_view'] = $roleString;
            }
        }
        //FORM GENERATION
        if ($this->getRequest()->isGet()) {
            $formValues = $wishlist->toArray();
            $formValues = array_merge($formValues, $perms);

            if (isset($formValues['body']) && !empty($formValues['body']))
                $formValues['body'] = strip_tags($formValues['body']);


            $this->respondWithSuccess(array(
                'form' => Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getCreateWishlistForm(),
                'formValues' => $formValues
            ));
        }

        //FORM VALIDATION
        else if ($this->getRequest()->isPut() || $this->getRequest()->isPost()) {

            $db = Engine_Api::_()->getItemTable('sitereview_listing')->getAdapter();
            $db->beginTransaction();
            try {
                $values = array();
                $getForm = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getCreateWishlistForm();
                foreach ($getForm as $element) {

                    if (isset($_REQUEST[$element['name']]))
                        $values[$element['name']] = $_REQUEST[$element['name']];
                }
                if (empty($values['title'])) {
                    $validationMessage = "title is required";
                    $this->respondWithValidationError('validation_fail', $validationMessage);
                }

                $wishlist->setFromArray($values)->save();
                $db->commit();

                //PRIVACTY WORK
                $auth = Engine_Api::_()->authorization()->context;
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

                if (empty($values['auth_view'])) {
                    $values['auth_view'] = 'owner';
                }

                $viewMax = array_search($values['auth_view'], $roles);
                foreach ($roles as $i => $role) {
                    $auth->setAllowed($wishlist, $role, 'view', ($i <= $viewMax));
                }
                $db->commit();
                // Change request method POST to GET
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Return the Diary Tell A Friend Form.
     * 
     * @return array
     */
    public function tellAFriendAction() {
        $wishlist_id = $this->_getParam('wishlist_id', $this->_getParam('wishlist_id', null));
        $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);
        $errorMessage = array();
        if (empty($wishlist))
            $this->respondWithError('no_record');

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
            $heading = $wishlist->title;
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

            $wishlistLink = $wishlist->getHref();
            if (strstr($wishlistLink, '/listings/')) {
                $wishlistLink = str_replace("/listings", "", $wishlistLink);
            }

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SITEREVIEW_TELLAFRIEND_EMAIL', array(
                'host' => $_SERVER['HTTP_HOST'],
                'sender' => $sender,
                'heading' => $heading,
                'message' => '<div>' . $message . '</div>',
                'object_link' => $wishlistLink,
                'email' => $sender_email,
                'queue' => true
            ));
            $this->successResponseNoContent('no_content', true);
        }
    }

    /**
     * Remove listing from wishlist.
     * 
     * @return status
     */
    public function removeAction() {

        // Validate request methods
        $this->validateRequestMethod('POST');

        //GET DIARY ID AND SUBJECT
        if (Engine_Api::_()->core()->hasSubject())
            $wishlist = Engine_Api::_()->core()->getSubject('sitereview_wishlist');

        $wishlist_id = $this->_getParam('wishlist_id');
        if (isset($wishlist_id) && !empty($wishlist_id)) {
            $subject = $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);
            Engine_Api::_()->core()->setSubject($wishlist);
        } else {
            $this->respondWithError('no_record');
        }


        if (empty($wishlist))
            $this->respondWithError('no_record');

        $wishlist_id = $this->_getParam('wishlist_id');

        $viewer = Engine_Api::_()->user()->getViewer();

        //GET EVENT ID AND EVENT
        $listing_id = $this->_getParam('listing_id');
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

        if (empty($sitereview) && !isset($sitereview))
            $this->respondWithError('no_record');

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

            //DELETE FROM DATABASE
            Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview')->delete(array('wishlist_id = ?' => $wishlist_id, 'listing_id = ?' => $listing_id));

            try {
                //DELETE ACTIVITY FEED
                //SQL ERROR TO BE CORRECTED
                $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                $actionTableName = $actionTable->info('name');

                $action_id = $actionTable->select()
                        ->setIntegrityCheck(false)
                        ->from($actionTableName, 'action_id')
                        ->joinInner('engine4_activity_attachments', "engine4_activity_attachments.action_id = $actionTableName.action_id", array())
                        ->where('engine4_activity_attachments.id = ?', $listing_id)
                        ->where($actionTableName . '.type = ?', "sitereview_wishlist_add_listing")
                        ->where($actionTableName . '.subject_type = ?', 'user')
                        ->where($actionTableName . '.object_type = ?', 'sitereview_listing')
                        ->where($actionTableName . '.object_id = ?', $listing_id)
                        //->where($actionTableName . '.params like(?)', '{"child_id":' . $wishlist_id . '}')
                        ->query()
                        ->fetchColumn();
            } catch (Exception $ex) {
                $this->respondWithValidationError('internal_server_error', $ex->getMessage());
            }
            if (!empty($action_id)) {
                $activity = Engine_Api::_()->getItem('activity_action', $action_id);
                if (!empty($activity)) {
                    $activity->delete();
                }
            }
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete Diary.
     * 
     * @return status
     */
    public function deleteAction() {
        // Validate request methods
        $this->validateRequestMethod('DELETE');

        //ONLY LOGGED IN USER CAN CREATE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');


        //GET DIARY ID
        $wishlist_id = $this->_getParam('wishlist_id');

        $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);

        if (empty($wishlist) && !isset($wishlist))
            $this->respondWithError('no_record');

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $level_id = $viewer->level_id;

        if ($level_id != 1 && $wishlist->owner_id != $viewer_id) {
            $this->respondWithError('unauthorized');
        }
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

            //DELETE DIARY CONTENT
            $wishlist->delete();

            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /*
     * Follow/Unfollow listing wishlist
     */

    public function followAction() {
        $this->validateRequestMethod('POST');
// Set the translations for zend library.
        if (!Zend_Registry::isRegistered('Zend_Translate'))
            Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();

        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        //ONLY LOGGED IN USER CAN CREATE
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');


        $wishlist_id = $this->_getParam('wishlist_id');
        if (isset($wishlist_id) && !empty($wishlist_id)) {
            $subject = $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);
            Engine_Api::_()->core()->setSubject($wishlist);
        } else {
            $this->respondWithError('no_record');
        }

        //GET VIEWER INFORMATION
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        //GET THE VALUE OF RESOURCE ID AND TYPE 
        $resource_id = $wishlist_id;
        $resource_type = 'sitereview_wishlist';

        $isFollow = $wishlist->follows()->isFollow($viewer);
        $follow_id = empty($isFollow) ? 0 : 1;

        //GET FOLLOW TABLE
        $followTable = Engine_Api::_()->getDbTable('follows', 'seaocore');
        $follow_name = $followTable->info('name');

        //GET OBJECT
        $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
        if (empty($follow_id)) {

            //CHECKING IF USER HAS MAKING DUPLICATE ENTRY OF LIKING AN APPLICATION.
            $follow_id_temp = $resource->follows()->isFollow($viewer);
            if (empty($follow_id_temp)) {

                if (!empty($resource)) {
                    $follow_id = $followTable->addFollow($resource, $viewer);
                    if ($viewer_id != $resource->getOwner()->getIdentity() && $resource->getOwner()->getIdentity()) {
                        //ADD NOTIFICATION
                        try {
                            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($resource->getOwner(), $viewer, $resource, 'follow_' . $resource_type, array());
                        } catch (Exception $ex) {
                            
                        }
                        if ($resource_type != 'siteevent_event') {
                            //ADD ACTIVITY FEED
                            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                            if ($resource_type != 'sitepage_page' || $resource_type != 'sitebusiness_business' || $resource_type != 'sitegroup_group' || $resource_type != 'sitestore_store') {
                                $action = $activityApi->addActivity($viewer, $resource, 'follow_' . $resource_type, '', array(
                                    'owner' => $resource->getOwner()->getGuid(),
                                ));
                            } else {
                                $action = $activityApi->addActivity($viewer, $resource, 'follow_' . $resource_type);
                            }

                            if (!empty($action))
                                $activityApi->attachActivity($action, $resource);
                        }
                    }
                }
            }
        } else {
            if (!empty($resource)) {
                $followTable->removeFollow($resource, $viewer);

                if ($viewer_id != $resource->getOwner()->getIdentity()) {
                    //DELETE NOTIFICATION
                    $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType($resource->getOwner(), $resource, 'follow_' . $resource_type);
                    if ($notification) {
                        $notification->delete();
                    }

                    //DELETE ACTIVITY FEED
                    $action_id = Engine_Api::_()->getDbtable('actions', 'activity')
                            ->select()
                            ->from('engine4_activity_actions', 'action_id')
                            ->where('type = ?', "follow_$resource_type")
                            ->where('subject_id = ?', $viewer_id)
                            ->where('subject_type = ?', 'user')
                            ->where('object_type = ?', $resource_type)
                            ->where('object_id = ?', $resource->getIdentity())
                            ->query()
                            ->fetchColumn();

                    if (!empty($action_id)) {
                        $activity = Engine_Api::_()->getItem('activity_action', $action_id);
                        if (!empty($activity)) {
                            $activity->delete();
                        }
                    }
                }
            }
        }

        $this->successResponseNoContent('no_content', true);
    }

}
