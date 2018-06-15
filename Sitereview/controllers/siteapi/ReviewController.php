<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    TopicController.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_ReviewController extends Siteapi_Controller_Action_Standard {

    public function init() {
        //SET REVIEW SUBJECT
        if (0 != ($review_id = (int) $this->getRequestParam('review_id')) &&
                null != ($review = Engine_Api::_()->getItem('sitereview_review', $review_id))) {
            Engine_Api::_()->core()->setSubject($review);
        } else if (0 != ($listing_id = (int) $this->getRequestParam('listing_id')) &&
                null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
            Engine_Api::_()->core()->setSubject($sitereview);
        }

        //SET LISTING TYPE ID AND OBJECT
        if ($this->getRequestParam('listingtype_id', null)) {
            $listingtype_id = $this->getRequestParam('listingtype_id', null);
        } else {
            if (!empty($review)) {
                $sitereview = $review->getParent();
            }
            $listingtype_id = $sitereview->listingtype_id;
        }

        $listingtype_id = $this->getRequestParam('listingtype_id', null);
        $this->_listingType = $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

        if (empty($this->_listingType->reviews) || $this->_listingType->reviews == 1) {
            $this->_forward('throw-error', 'index', 'sitereview', array(
                "error_code" => "no_record"
            ));
        }
        //AUTHORIZATION CHECK
        if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
            $this->respondWithError('unauthorized');
        return;
    }

    /**
     * Throw the init constructor errors.
     *
     * @return array
     */
    public function throwErrorAction() {
        $message = $this->getRequestParam("message", null);
        if (($error_code = $this->getRequestParam("error_code")) && !empty($error_code)) {
            if (!empty($message))
                $this->respondWithValidationError($error_code, $message);
            else
                $this->respondWithError($error_code);
        }

        return;
    }

    public function browseAction() {

        $this->validateRequestMethod();
         Engine_Api::_()->getApi('Core', 'siteapi')->setView();
         Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();
//GET VIEWER INFO
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

//EVENT SUBJECT SHOULD BE SET
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing'))
            $this->respondWithError('no_record');

        $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

        $listing_id = $sitereview->listing_id;

        $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
        $sitereviewProfileOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.profile.order', false);
        $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
        $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
        $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
        $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);
        $reviewDescriptionsTable = Engine_Api::_()->getDbtable('reviewDescriptions', 'sitereview');



//GET PARAMS
        $params['type'] = '';
        $reviewMenu = $this->getRequestParam('gutterMenus', 'true');
        $params = $this->_getAllParams();
        $getListings = $params['getListings'];
        if (!isset($params['order']) || empty($params['order']))
            $params['order'] = 'recent';
        if (isset($params['show'])) {

            switch ($params['show']) {
                case 'friends_reviews':
                    $params['user_ids'] = $viewer->membership()->getMembershipsOfIds();
                    if (empty($params['user_ids']))
                        $params['user_ids'] = -1;
                    break;
                case 'self_reviews':
                    $params['user_id'] = $viewer_id;
                    break;
                case 'featured':
                    $params['featured'] = 1;
                    break;
            }
        }
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        $params['resource_type'] = 'sitereview_listing';
        $params['listing_id'] = $sitereview_id;
        if (isset($params['user_id']) && !empty($params['user_id']))
            $user_id = $params['user_id'];
        else
            $user_id = $viewer_id;

        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        //GET REVIEW TABLE
        $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');

        //GET RATING TABLE
        $ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');

        $resource_type = $sitereview->getType();
        $listingtype_id = $sitereview->listingtype_id;

        $this->_listingType = $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
        $listing_singular_uc = ucfirst($listingType->title_singular);
        $listing_singular_lc = strtolower($listingType->title_singular);
        if (empty($listingType->reviews) || $listingType->reviews == 1) {
            $this->respondWithError('no_record');
        }

        $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
//        for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
//            $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
//        }
//
//        for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
//            $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
//        }
//        $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;

        $params = array();

        //SET PARAMS
//        $params = $this->getAllParams();
        //SET HAS POSTED
        if (empty($viewer_id)) {
            $hasPosted = $hasPosted = 0;
        } else {
            $params['resource_id'] = $sitereview->listing_id;
            $params['resource_type'] = $resource_type;
            $params['viewer_id'] = $viewer_id;
            $params['type'] = 'user';
            $hasPosted = $reviewTable->canPostReview($params);
        }

        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "user_review"))
                $this->respondWithError('unauthorized');
        }

        try {

            $autorizationApi = Engine_Api::_()->authorization();
            $create_level_allow = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id");

//            if (!empty($sitereviewViewType) || (!empty($sitereviewProfileOrder) && !empty($sitereviewListingTypeOrder) && ($sitereviewListingTypeOrder == $sitereviewProfileOrder))) {
//                $isEnabledListingType = true;
//            }

            $can_update = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");

            $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;

            if (!$create_review || empty($create_level_allow)) {
                $can_create = 0;
            } else {
                $can_create = 1;
            }

            //GET FILTER
            $reviewOption = $params['option'] = 'fullreviews';
            $setItemCountPerPage = $this->getRequestParam('limit', 5);
            //GET SORTING ORDER
            $reviewOrder = $params['order'] = $this->getRequestParam('order', 'creationDate');

            $params['resource_id'] = $listing_id;
            $params['resource_type'] = $sitereview->getType();
            $params['type'] = 'user';
            $noReviewCheck = $reviewTable->getAvgRecommendation($params);
            if (!empty($noReviewCheck) && isset($noReviewCheck[0])) {
                $recommend_percentage = round($noReviewCheck[0]['avg_recommend'] * 100, 3);
            }
            $params['rating'] = 'rating';
            $paginator = $reviewTable->listReviews($params);
            $paginator = $paginator->setItemCountPerPage($setItemCountPerPage);
            $current_page = $this->getRequestParam('page', 1);
            $paginator = $paginator->setCurrentPageNumber($current_page);

            //GET TOTAL REVIEWS
            $this->_childCount = $totalReviews = $paginator->getTotalItemCount();

            //FATCH REVIEW CATEGORIES
            $categoryIdsArray = array();
            $categoryIdsArray[] = $sitereview->category_id;
            $categoryIdsArray[] = $sitereview->subcategory_id;
            $categoryIdsArray[] = $sitereview->subsubcategory_id;

            $reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, $sitereview->getType());

            //COUNT REVIEW CATEGORY
            $total_reviewcats = Count($reviewCategory);
            //GET REVIEW RATE DATA
            if (isset($hasPosted) && !empty($hasPosted))
                $reviewRateData = $ratingTable->ratingsData($hasPosted, $viewer_id, $listing_id);
            //CAN DELETE
            $can_delete = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");

            //CAN REPLY
            $can_reply = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_reply_listtype_$listingtype_id");

            //CHECK PAGE
            $checkPage = "listingProfile";

            for ($i = 5; $i > 0; $i--) {
                $ratingCount[$i] = $ratingTable->getNumbersOfUserRating($listing_id, 'user', 0, $i, 0, 'sitereview_listing', array());
            }
            $ratingData = $ratingTable->ratingbyCategory($listing_id, $type, $sitereview->getType());
            if (isset($hasPosted) && !empty($hasPosted))
                $reviewRateMyData = $ratingTable->ratingsData($hasPosted);
            $coreApi = Engine_Api::_()->getApi('settings', 'core');

            $sitereview_proscons = $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
            $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
            $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);
            $sitereview_report = $coreApi->getSetting('sitereview.report', 1);
            $sitereview_email = $coreApi->getSetting('sitereview.email', 1);
            $sitereview_share = $coreApi->getSetting('sitereview.share', 1);

            $getRating = $this->getRequestParam('getRating', 1);

            if (isset($getRating) && !empty($getRating) && isset($hasPosted) && !empty($hasPosted)) {
                $ratings['rating_avg'] = $sitereview->rating_avg;
                $ratings['rating_users'] = $sitereview->rating_users;
                $ratings['breakdown_ratings_params'] = $ratingCount;
                $ratings['myRatings'] = $reviewRateMyData;
                $ratings['review_id'] = $hasPosted;
                $ratings['recomended'] = $recommend_percentage;
                $response['ratings'] = $ratings;
            }

            $metaParams = array();
            $response['total_reviews'] = $totalReviews;
            $response['content_title'] = $sitereview->getTitle();
            ;

            foreach ($paginator as $review) {

                $params = $review->toArray();

                if (isset($params['body']) && !empty($params['body']))
                    $params['body'] = strip_tags($params['body']);

                if (isset($params['pros']) && !empty($params['pros']))
                    $params['pros'] = strip_tags($params['pros']);

                if (isset($params['cons']) && !empty($params['cons']))
                    $params['cons'] = strip_tags($params['cons']);

                if (isset($review->owner_id) && !empty($review->owner_id)) {
                    $params ["owner_title"] = $review->getOwner()->getTitle();
                    // owner image Add images 
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($review, true);
                    $params = array_merge($params, $getContentImages);
                }

                if (isset($params['type']) && !empty($params['type']) && $params['type'] == 'visitor') {
                    $params['owner_title'] = (isset($params['anonymous_name']) && !empty($params['anonymous_name'])) ? $params['anonymous_name'] : "";
                }

                $listing_id = $review->resource_id;

                if (isset($getListings) && !empty($getListings)) {
                    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
                    $params['listing_title'] = $sitereview->title;
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($sitereview);
                    $params = array_merge($params, $getContentImages);
                }
                if (isset($review->owner_id) && !empty($review->owner_id)) {

                    $user_ratings = Engine_Api::_()->getDbtable('ratings', 'sitereview')->ratingsData($review->review_id, $review->getOwner()->getIdentity(), $review->resource_id, 0);
                    $params['overall_rating'] = $user_ratings[0]['rating'];
                }
                $params['category_name'] = Engine_Api::_()->getItem('sitereview_category', $sitereview->category_id)->category_name;
                $helpfulTable = Engine_Api::_()->getDbtable('helpful', 'sitereview');
                $helpful_entry = $helpfulTable->getHelpful($review->review_id, $viewer_id, 1);
                $nothelpful_entry = $helpfulTable->getHelpful($review->review_id, $viewer_id, 2);
                $params['is_helful'] = ($helpful_entry) ? true : false;
                $params['is_not_helful'] = ($nothelpful_entry) ? true : false;
                $params['is_helpful'] = ($helpful_entry) ? true : false;
                $params['is_not_helpful'] = ($nothelpful_entry) ? true : false;
                $params['helpful_count'] = $review->getCountHelpful(1);
                $params['nothelpful_count'] = $review->getCountHelpful(2);

                // Set Updated Review Array
                $reviewDescriptions = $reviewDescriptionsTable->getReviewDescriptions($review->review_id);
                if (count($reviewDescriptions) > 0) {
                    $updatedReviewArray = array();
                    foreach ($reviewDescriptions as $value) {
                        if ($value->body) {
                            $updatedReviewArray[] = $value->toArray();
                        }
                    }

                    if (isset($updatedReviewArray) && !empty($updatedReviewArray) && count($updatedReviewArray) > 0)
                        $params['updatedReviewArray'] = $updatedReviewArray;
                }

                if (isset($reviewMenu) && !empty($reviewMenu)) {
                    $params['gutterMenus'] = $this->_getGutterMenus($review);
                }

                $tempResponse[] = $params;
            }
            if (!empty($tempResponse))
                $response['reviews'] = $tempResponse;
            $this->respondWithSuccess($response, true);
        } catch (Exception $ex) {
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

//ACTION FOR WRITE A REVIEW
    public function createAction() {
//EVENT SUBJECT SHOULD BE SET
        //LISTING SUBJECT SHOULD BE SET
        if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
            $this->respondWithError('no_record');

//GET VIEWER INFO
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
//GET EVENT SUBJECT
        //GET LISTING SUBJECT
        $sitereview = Engine_Api::_()->core()->getSubject();

        $listingtype_id = $sitereview->listingtype_id;

//FETCH REVIEW CATEGORIES
        $categoryIdsArray = array();
        $categoryIdsArray[] = $sitereview->category_id;
        $categoryIdsArray[] = $sitereview->subcategory_id;
        $categoryIdsArray[] = $sitereview->subsubcategory_id;

        $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

//GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        $can_create = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id");

        if (empty($can_create)) {
            $this->respondWithError('unauthorized');
        }

        $coreApi = Engine_Api::_()->getApi('settings', 'core');
        $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
        $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
        $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);

        $ratingParams = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, 'sitereview_listing');
        $ratingParam[] = array(
            'type' => 'Rating',
            'name' => 'review_rate_0',
            'label' => $this->translate('Overall Rating')
        );

        foreach ($ratingParams as $ratingparam_id) {
            $ratingParam[] = array(
                'type' => 'Rating',
                'name' => 'review_rate_' . $ratingparam_id->ratingparam_id,
                'label' => $ratingparam_id->ratingparam_name
            );
        }
        if ($this->getRequest()->isGet()) {
            $response['ratingParams'] = $ratingParam;
            $response['form'] = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getReviewCreateForm(array("settingsReview" => array('sitereview_proscons' => $sitereview_proscons, 'sitereview_limit_proscons' => $sitereview_limit_proscons, 'sitereview_recommend' => $sitereview_recommend), 'item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
            $this->respondWithSuccess($response, true);
        }

        if ($this->getRequest()->isPost()) {
// CONVERT POST DATA INTO THE ARRAY.
            $values = array();

            $getForm = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getReviewCreateForm(array("settingsReview" => array('sitereview_proscons' => $sitereview_proscons, 'sitereview_limit_proscons' => $sitereview_limit_proscons, 'sitereview_recommend' => $sitereview_recommend), 'item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
            foreach ($getForm as $element) {
                if (isset($_REQUEST[$element['name']]))
                    $values[$element['name']] = $_REQUEST[$element['name']];
            }

// START FORM VALIDATION
            $validators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getReviewCreateFormValidators(array("settingsReview" => array('sitereview_proscons' => $sitereview_proscons, 'sitereview_limit_proscons' => $sitereview_limit_proscons, 'sitereview_recommend' => $sitereview_recommend), 'item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
            $values['validators'] = $validators;
            $validationMessage = $this->isValid($values);
            if (!empty($validationMessage) && @is_array($validationMessage)) {
                $this->respondWithValidationError('validation_fail', $validationMessage);
            }
            $postData = $this->_getAllParams();

            if (empty($_REQUEST['review_rate_0'])) {
                $this->respondWithValidationError('validation_fail', 'Overall Rating is required');
            }

            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            try {

                $values['owner_id'] = $viewer_id;
                $values['resource_id'] = $sitereview->listing_id;
                $values['resource_type'] = $sitereview->getType();
                $values['profile_type_review'] = $profileTypeReview;
                $values['type'] = $viewer_id ? 'user' : 'visitor';

                if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)) {
                    $values['recommend'] = 0;
                }
                $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
                $review = $reviewTable->createRow();
                $review->setFromArray($values);
                $review->view_count = 1;
                $review->save();

//                    if (!empty($profileTypeReview)) {
//                        //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
//                        $form = new Siteevent_Form_Review_Create(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
//                        $form->populate($postData);
//                        $customfieldform = $form->getSubForm('fields');
//                        $customfieldform->setItem($review);
//                        $customfieldform->saveValues();
//                    }
                //INCREASE REVIEW COUNT IN EVENT TABLE
                if (!empty($viewer_id))
                    $sitereview->review_count++;
                $sitereview->save();

//                $params['event_id'] = $sitereview->event_id;
//                foreach ($ratingParam as $rating) {
//                    if (isset($_REQUEST[$rating['name']])) {
//                        $params['rating_id'] = $rating['name'];
//                        $params['rating'] = $_REQUEST[$rating['name']];
//                        $this->_rate($params, $review);
//                    }
//                }
                $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
                if (!empty($review_id)) {
                    $reviewRatingTable->delete(array('review_id = ?' => $review->review_id));
                }

                $postData['user_id'] = $viewer_id;
                $postData['review_id'] = $review->review_id;
                $postData['category_id'] = $sitereview->category_id;
                $postData['resource_id'] = $review->resource_id;
                $postData['resource_type'] = $review->resource_type;

                $review_count = Engine_Api::_()->getDbtable('ratings', 'sitereview')->getReviewId($viewer_id, $sitereview->getType(), $review->resource_id);
                if (count($review_count) == 0 || empty($viewer_id)) {
                    //CREATE RATING DATA
                    $reviewRatingTable->createRatingData($postData, $values['type']);
                } else {
                    $reviewRatingTable->update(array('review_id' => $review->review_id), array('resource_type = ?' => $review->resource_type, 'user_id = ?' => $viewer_id, 'resource_id = ?' => $review->resource_id));
                }

                //UPDATE RATING IN RATING TABLE
                if (!empty($viewer_id)) {
                    $reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type);
                }

                if (empty($review_id) && !empty($viewer_id)) {
                    $activityApi = Engine_Api::_()->getDbtable('actions', 'seaocore');

                    //ACTIVITY FEED
                    $action = $activityApi->addActivity($viewer, $sitereview, 'sitereview_review_add');

                    if ($action != null) {
                        $activityApi->attachActivity($action, $review);

                        //START NOTIFICATION AND EMAIL WORK
                        //Engine_Api::_()->sitereview()->sendNotificationEmail($sitereview, $action, 'sitereview_write_review', 'SITEEVENT_REVIEW_WRITENOTIFICATION_EMAIL', null, null, 'created', $review);
                        $isChildIdLeader = Engine_Api::_()->getDbtable('listItems', 'sitereview')->checkLeader($sitereview);

                        if (!empty($isChildIdLeader)) {
                            Engine_Api::_()->sitereview()->sendNotificationToFollowers($sitereview, 'sitereview_write_review');
                        }
                        //END NOTIFICATION AND EMAIL WORK
                    }
                }

                $db->commit();
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }
        }
    }

//ACTION FOR UPDATE THE REVIEW
    public function updateAction() {

//REVIEW SUBJECT SHOULD BE SET
        if (!$this->_helper->requireSubject('sitereview_review')->isValid())
            $this->respondWithError('unauthorized');

//GET VIEWER INFO
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (Engine_Api::_()->core()->hasSubject())
            $review = Engine_Api::_()->core()->getSubject('sitereview_review');

        $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $review_id = $review->getIdentity();
        if (empty($sitereview))
            $this->respondWithError('no_record');

//FETCH REVIEW CATEGORIES
        $categoryIdsArray = array();
        $categoryIdsArray[] = $sitereview->category_id;
        $categoryIdsArray[] = $sitereview->subcategory_id;
        $categoryIdsArray[] = $sitereview->subsubcategory_id;
        $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

//GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }

        $can_update = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");


        if (empty($can_update)) {
            $this->respondWithError('unauthorized');
        }

//GET RATING TABLE
        $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');

        $coreApi = Engine_Api::_()->getApi('settings', 'core');
        $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
        $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
        $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);
        $review_id = (int) $this->getRequestParam('review_id');
        $review = Engine_Api::_()->core()->getSubject();

        $ratingParams = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, 'sitereview_listing');
        $ratingParam[] = array(
            'type' => 'Rating',
            'name' => 'review_rate_0',
            'label' => $this->translate($this->translate('Overall Rating'))
        );

        foreach ($ratingParams as $ratingparam_id) {
            $ratingParam[] = array(
                'type' => 'Rating',
                'name' => 'review_rate_' . $ratingparam_id->ratingparam_id,
                'label' => $this->translate($ratingparam_id->ratingparam_name)
            );
        }
        $ratingValues = array();
        $reviewRateMyDatas = $ratingTable->ratingsData($review_id);

        foreach ($reviewRateMyDatas as $reviewRateMyData) {
            $ratingValues['review_rate_' . $reviewRateMyData['ratingparam_id']] = $reviewRateMyData['rating'];
        }

        if ($this->getRequest()->isGet()) {
            $response['form'] = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getReviewUpdateForm();
            $response['ratingParams'] = $ratingParam;
            if (isset($ratingValues) && !empty($ratingValues))
                $response['formValues'] = $ratingValues;
            $this->respondWithSuccess($response, true);
        }

        if ($this->getRequest()->isGet()) {
            $response = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getReviewUpdateForm();
            $this->respondWithSuccess($response, true);
        }

        if ($this->getRequest()->isPost()) {
            $values = array();
            $getForm = Engine_Api::_()->getApi('Siteapi_Core', 'Sitereview')->getReviewUpdateForm();
            foreach ($getForm as $element) {
                if (isset($_REQUEST[$element['name']]))
                    $values[$element['name']] = $_REQUEST[$element['name']];
            }
            if (!empty($values['body']))
// START FORM VALIDATION
                $validators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getReviewUpdateFormValidators();
            $values['validators'] = $validators;
            $validationMessage = $this->isValid($values);
            if (!empty($validationMessage) && @is_array($validationMessage)) {
                $this->respondWithValidationError('validation_fail', $validationMessage);
            }
            $postData = $this->_getAllParams();
            if (empty($_REQUEST['review_rate_0'])) {
                $this->respondWithValidationError('validation_fail', "Overall Rating is required");
            }
            try {
                $postData['user_id'] = $viewer_id;
                $postData['category_id'] = $sitereview->category_id;
                $postData['resource_id'] = $review->resource_id;
                $postData['resource_type'] = $sitereview->getType();
                $postData['review_id'] = $review_id;
                $postData['profile_type_review'] = $profileTypeReview;
                $reviewDescription = Engine_Api::_()->getDbtable('reviewDescriptions', 'sitereview');
                $reviewDescription->insert(array('review_id' => $review_id, 'body' => $postData['body'], 'modified_date' => date('Y-m-d H:i:s'), 'user_id' => $viewer_id));
                $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
                $reviewRatingTable->delete(array('review_id = ?' => $review_id));

                //CREATE RATING DATA
                $reviewRatingTable->createRatingData($postData, 'user');

                Engine_Api::_()->getDbtable('ratings', 'sitereview')->listRatingUpdate($review->resource_id, $review->resource_type);
                $this->successResponseNoContent('no_content', true);

//                if (!empty($profileTypeReview)) {
//                    //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
//                    $form = new Siteevent_Form_Review_Create(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
//                    $form->populate($postData);
//                    $customfieldform = $form->getSubForm('fields');
//                    $customfieldform->setItem($review);
//                    $customfieldform->saveValues();
//                }
            } catch (Exception $ex) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $ex->getMessage());
            }
        }
    }

//ACTION FOR MARKING HELPFUL REVIEWS
    public function helpfulAction() {
        $this->validateRequestMethod('POST');
//NOT VALID USER THEN RETURN
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

//GET VIEWER DETAIL
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

//GET RATING
        $helpful = $this->getRequestParam('helpful');
        if (empty($helpful) && !isset($helpful))
            $this->respondWithValidationError('validation_fail', 'field is required');

//GET REVIEW ID
        $review_id = $this->getRequestParam('review_id');

        if (Engine_Api::_()->core()->hasSubject())
            $review = Engine_Api::_()->core()->getSubject();

        if (empty($review))
            $this->respondWithError('no_record');

        $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
        if (empty($sitereview))
            $this->respondWithError('no_record');

        try {
//GET HELPFUL TABLE
            $helpfulTable = Engine_Api::_()->getDbtable('helpful', 'sitereview');

            $already_entry = $helpfulTable->getHelpful($review_id, $viewer_id, $helpful);
            if ($already_entry == $helpful) {
                $this->respondWithValidationError('validation_fail', 'Already given feedback');
            }

//MAKE ENTRY FOR HELPFUL
            $helpfulTable->setHelful($review_id, $viewer_id, $helpful);

            $params = $review->toArray();
            $params ["owner_title"] = (isset($review->owner_id) && !empty($review->owner_id)) ? $review->getOwner()->getTitle() : "";
            // owner image Add images 
            if (isset($review->owner_id) && !empty($review->owner_id)) {
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($review, true);
                $params = array_merge($params, $getContentImages);
            }
            $listing_id = $review->resource_id;
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            $params['listing_title'] = $sitereview->title;
            $user_ratings = Engine_Api::_()->getDbtable('ratings', 'sitereview')->ratingsData($review->review_id, $review->owner_id, $review->resource_id, 0);
            $params['overall_rating'] = $user_ratings[0]['rating'];
            $params['category_name'] = Engine_Api::_()->getItem('sitereview_category', $sitereview->category_id)->category_name;
            $helpfulTable = Engine_Api::_()->getDbtable('helpful', 'sitereview');
            $helpful_entry = $helpfulTable->getHelpful($review->review_id, $viewer_id, 1);
            $nothelpful_entry = $helpfulTable->getHelpful($review->review_id, $viewer_id, 2);
            $params['is_helful'] = $helpful_entry;
            $params['is_not_helful'] = $nothelpful_entry;
            $params['is_helpful'] = $helpful_entry;
            $params['is_not_helpful'] = $nothelpful_entry;
            $params['helpful_count'] = $review->getCountHelpful(1);
            $params['nothelpful_count'] = $review->getCountHelpful(2);

// Add owner images
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($sitereview);
            $params = array_merge($params, $getContentImages);

            $this->respondWithSuccess($params, true);
        } catch (Exception $ex) {

            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

//ACTION FOR VIEW REVIEW
    public function viewAction() {
// Validate request methods
        $this->validateRequestMethod();

//IF ANONYMOUS USER THEN SEND HIM TO SIGN IN PAGE
        $check_anonymous_help = $this->getRequestParam('anonymous');
        if ($check_anonymous_help) {
            if (!$this->_helper->requireUser()->isValid())
                $this->respondWithError('unauthorized');
        }

//GET LOGGED IN USER INFORMATION
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (!Engine_Api::_()->core()->hasSubject('sitereview_review')) {
            $this->respondWithError('no_record');
        }

//GET EVENT ID AND OBJECT
        $sitereview = Engine_Api::_()->core()->getSubject()->getParent();

//WHO CAN VIEW THE EVENTS
        if (!$this->_helper->requireAuth()->setAuthParams($sitereview, null, "view")->isValid() || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.allowreview', 1)) {
            $this->respondWithError('unauthorized');
        }

        $review = Engine_Api::_()->core()->getSubject();
        if (empty($review)) {
            $this->respondWithError('no_record');
        }

//GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }
//GET LEVEL SETTING
        $can_view = Engine_Api::_()->authorization()->getPermission($level_id, 'siteevent_event', "view");

        if ($can_view != 2 && $viewer_id != $sitereview->owner_id && ($sitereview->draft == 1 || $sitereview->search == 0 || $sitereview->approved != 1)) {
            $this->respondWithError('unauthorized');
        }

        if ($can_view != 2 && ($review->status != 1 && empty($review->owner_id))) {
            $this->respondWithError('unauthorized');
        }

        $params = array();
        $params = $review->toArray();
        $params['owner_title'] = $review->getOwner()->getTitle();
        $params['helpful_count'] = Engine_Api::_()->getDbTable('helpful', 'sitereview')->getCountHelpful($review->review_id, 1);

        if (isset($params['type']) && !empty($params['type']) && $params['type'] == 'visitor') {
            $params['owner_title'] = (isset($params['anonymous_name']) && !empty($params['anonymous_name'])) ? $params['anonymous_name'] : "";
        }

//GET LOCATION
        if (!empty($sitereview->location) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.location', 1)) {
            $params['location'] = $sitereview->location;
        }
        $params['tag'] = $sitereview->getKeywords(', ');

//GET EVENT CATEGORY TABLE
        $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

        $category_id = $sitereview->category_id;
        if (!empty($category_id)) {

            $params['categoryname'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->category_name;

            $subcategory_id = $sitereview->subcategory_id;

            if (!empty($subcategory_id)) {

                $params['subcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->category_name;

                $subsubcategory_id = $sitereview->subsubcategory_id;

                if (!empty($subsubcategory_id)) {

                    $params['subsubcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->category_name;
                }
            }
        }
        $response['response'] = $params;

        $this->respondWithSuccess($response, true);
    }

//ACTION FOR DELETING REVIEW
    public function deleteAction() {

        $this->validateRequestMethod('DELETE');

//ONLY LOGGED IN USER CAN DELETE REVIEW
        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

//SUBJECT SHOULD BE SET
        if (!$this->_helper->requireSubject('sitereview_review')->isValid())
            $this->respondWithError('no_record');

//GET VIEWER ID
        $viewer = Engine_Api::_()->user()->getViewer();
        $review = Engine_Api::_()->core()->getSubject();
        $viewer_id = $viewer->getIdentity();
        $sitereview = $review->getParent();

        //GET REVIEW ID AND REVIEW OBJECT
        $review_id = $this->getRequestParam('review_id');
        $listingtype_id = $sitereview->listingtype_id;

        //AUTHORIZATION CHECK
        $can_delete = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");

//WHO CAN DELETE THE REVIEW
        if (empty($can_delete) || ($can_delete == 1 && $viewer_id != $review->owner_id)) {
            $this->respondWithError('unauthorized');
        }

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {

//DELETE REVIEW FROM DATABASE
            Engine_Api::_()->getItem('sitereview_review', (int) $review_id)->delete();
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $ex) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $ex->getMessage());
        }
    }

    private function _getGutterMenus($review) {
        //GET LOGGED IN USER INFORMATION
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        //GET USER LEVEL ID
        if (!empty($viewer_id)) {
            $level_id = $viewer->level_id;
        } else {
            $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
        }
        if ($viewer_id) {
            $tempMenu[] = array(
                'name' => 'report',
                'label' => $this->translate('Report'),
                'url' => 'report/create/subject/' . $review->getGuid(),
                'urlParams' => array(
                    "type" => $review->getType(),
                    "id" => $review->getIdentity()
                )
            );
        }

        if ($review->owner_id != 0) {
            $tempMenu[] = array(
                'name' => 'share',
                'label' => $this->translate('Share'),
                'url' => 'activity/share',
                'urlParams' => array(
                    "type" => $review->getType(),
                    "id" => $review->getIdentity()
                )
            );
        }

        $sitereview = $review->getParent();
        $listingtype_id = $sitereview->listingtype_id;
        $can_delete = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");
        if (!empty($can_delete) && ($can_delete != 1 || $viewer_id == $sitereview->owner_id)) {
            $tempMenu[] = array(
                'name' => 'delete',
                'label' => $this->translate('Delete Review'),
                'url' => 'listings/review/delete/' . $sitereview->getIdentity(),
                'urlParams' => array(
                    "review_id" => $review->getIdentity()
                )
            );
        }

        $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;
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

            if ($createAllow == 2) {
                $tempMenu[] = array(
                    'name' => 'update',
                    'label' => $this->translate('Update Review'),
                    'url' => 'listings/review/update/' . $sitereview->getIdentity(),
                    'urlParams' => array(
                        "review_id" => $review->getIdentity()
                    )
                );
            }
        }

        return $tempMenu;
    }

}
