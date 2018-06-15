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
class Sitereview_PhotoController extends Siteapi_Controller_Action_Standard {

    public function init() {

        //GET LISTING TYPE ID
        $listingtype_id = $this->getRequestParam('listingtype_id', null);
        if (!empty($listingtype_id)) {
            $this->_listingType = $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);

            //AUTHORIZATION CHECK
            if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
                $this->respondWithError('unauthorized');
        }
        //SET SUBJECT
        if (!Engine_Api::_()->core()->hasSubject()) {

            if (0 != ($photo_id = (int) $this->getRequestParam('photo_id')) &&
                    null != ($photo = Engine_Api::_()->getItem('sitereview_photo', $photo_id))) {
                Engine_Api::_()->core()->setSubject($photo);
            } else if (0 != ($listing_id = (int) $this->getRequestParam('listing_id')) &&
                    null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
                Engine_Api::_()->core()->setSubject($sitereview);
            }
        }
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

    /**
     * RETURN THE LIST OF ALL PHOTOS OF SITEREVIEW AND UPLOAD PHOTOS ALSO.
     * 
     * @return array
     */
    public function listAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $photo_id = (int) $this->_getParam('photo_id');

        // CHECK AUTHENTICATION
        // CHECK AUTHENTICATION
        if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
            $sitereview = $subject = Engine_Api::_()->core()->getSubject('sitereview_listing');
        } else if (Engine_Api::_()->core()->hasSubject('sitereview_photo')) {
            $photo = $subject = Engine_Api::_()->core()->getSubject('sitereview_photo');
            $listing_id = $photo->listing_id;
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        }
        $bodyResponse = $tempResponse = array();
        $listing_singular_uc = ucfirst($this->_listingType->title_singular);
        $can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");
        $listingtype_id = $this->_listingType->listingtype_id;
        //AUTHORIZATION CHECK
        $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $photoCount = Engine_Api::_()->getItem('sitereviewpaidlisting_package', $sitereview->package_id)->photo_count;
            $paginator = $sitereview->getSingletonAlbum()->getCollectiblesPaginator();

            if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
                $allowed_upload_photo = $allowed_upload_photo;
                if (empty($photoCount))
                    $allowed_upload_photo = $allowed_upload_photo;
                elseif ($photoCount <= $paginator->getTotalItemCount())
                    $allowed_upload_photo = 0;
            } else
                $allowed_upload_photo = 0;
        } else
            $allowed_upload_photo = $allowed_upload_photo;

        //GET ALBUM
        $album = $sitereview->getSingletonAlbum();


        /* RETURN THE LIST OF IMAGES, IF FOLLOWED THE FOLLOWING CASES:   
         * - IF THERE ARE GET METHOD AVAILABLE.
         * - iF THERE ARE NO $_FILES AVAILABLE.
         */
        if (empty($_FILES) && $this->getRequest()->isGet()) {
            $requestLimit = $this->getRequestParam("limit", 10);
            $page = $requestPage = $this->getRequestParam("page", 1);

            //GET PAGINATOR
            $album = $sitereview->getSingletonAlbum();
            $paginator = $album->getCollectiblesPaginator();

            $bodyResponse[' totalPhotoCount'] = $totalItemCount = $bodyResponse['totalItemCount'] = $paginator->getTotalItemCount();
            $paginator->setItemCountPerPage($requestLimit);
            $paginator->setCurrentPageNumber($requestPage);
            // Check the Page Number for pass photo_id.
            if (!empty($photo_id)) {
                for ($page = 1; $page <= ceil($totalItemCount / $requestLimit); $page++) {
                    $paginator->setCurrentPageNumber($page);
                    $tmpGetPhotoIds = array();
                    foreach ($paginator as $photo) {
                        $tmpGetPhotoIds[] = $photo->photo_id;
                    }
                    if (in_array($photo_id, $tmpGetPhotoIds)) {
                        $bodyResponse['page'] = $page;
                        break;
                    }
                }
            }

            if ($totalItemCount > 0) {
                foreach ($paginator as $photo) {
                    $tempImages = $photo->toArray();

                    // Add images
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($photo);
                    $tempImages = array_merge($tempImages, $getContentImages);

                    $tempImages['user_title'] = $photo->getOwner()->getTitle();
                    $tempImages['likes_count'] = $photo->likes()->getLikeCount();
                    $tempImages['is_like'] = ($photo->likes()->isLike($viewer)) ? 1 : 0;

                    if (!empty($viewer) && ($tempMenu = $this->getRequestParam('menu', 1)) && !empty($tempMenu)) {
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

                        $tempImages['menu'] = $menu;
                    }

                    if (isset($tempImages) && !empty($tempImages))
                        $bodyResponse['images'][] = $tempImages;
                }
            }
            $bodyResponse['canUpload'] = $allowed_upload_photo;
            $this->respondWithSuccess($bodyResponse, true);
        } else if (isset($_FILES) && $this->getRequest()->isPost()) { // UPLOAD IMAGES TO RESPECTIVE EVENT
            if (empty($viewer_id) || empty($allowed_upload_photo))
                $this->respondWithError('unauthorized');
            $tablePhoto = Engine_Api::_()->getDbtable('photos', 'sitereview');
            $db = $tablePhoto->getAdapter();
            $db->beginTransaction();

            try {
                $viewer = Engine_Api::_()->user()->getViewer();
                $album = $sitereview->getSingletonAlbum();
                $rows = $tablePhoto->fetchRow($tablePhoto->select()->from($tablePhoto->info('name'), 'order')->order('order DESC')->limit(1));
                $order = 0;
                if (!empty($rows)) {
                    $order = $rows->order + 1;
                }
                $params = array(
                    'collection_id' => $album->getIdentity(),
                    'album_id' => $album->getIdentity(),
                    'listing_id' => $sitereview->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'order' => $order
                );
                $photoCount = count($_FILES);
                if (isset($_FILES['photo']) && $photoCount == 1) {
                    $photo_id = Engine_Api::_()->sitereview()->createPhoto($params, $_FILES['photo'])->photo_id;
                    if (!$sitereview->photo_id) {
                        $sitereview->photo_id = $photo_id;
                        $sitereview->save();
                    }
                } else if (!empty($_FILES) && $photoCount > 1) {
                    foreach ($_FILES as $photo) {
                        Engine_Api::_()->sitereview()->createPhoto($params, $photo);
                    }
                }

                $db->commit();
                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
    }

    /**
     * VIEW THE PHOTO
     * 
     * @return array
     */
    public function viewAction() {
        // Validate request methods
        $this->validateRequestMethod();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        //START PACKAGE LEVEL WORK
        if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
            $package_id = Engine_Api::_()->getItem('sitereview_listing', $photo->listing_id)->package_id;
            if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($package_id, "photo"))
                $this->respondWithError('unauthorized');
        }

        $listingtype_id = $this->_listingType->listingtype_id;

        $photo = Engine_Api::_()->core()->getSubject();

        if (empty($photo) && !isset($photo))
            $this->respondWithError('no_record');

        $tempPhoto = $photo->toArray();

        if (!$viewer || !$viewer->getIdentity() || $photo->user_id != $viewer->getIdentity()) {
            $photo->view_count = new Zend_Db_Expr('view_count + 1');
            $photo->save();
        }

        //GET SETTINGS
        $canEdit = 0; //photo->authorization()->isAllowed(null, "edit_listtype_$listingtype_id");
        if (empty($canEdit) && $photo->user_id == $viewer_id) {
            $canEdit = 1;
        }

        $canDelete = 0; //$photo->authorization()->isAllowed(null, "delete_listtype_$listingtype_id");
        if (empty($canDelete) && $photo->user_id == $viewer_id) {
            $canDelete = 1;
        }
        // Add images
        $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($photo);
        $tempPhoto = array_merge($tempPhoto, $getContentImages);

        if (!empty($viewer) && ($tempMenu = $this->getRequestParam('menu', true)) && !empty($tempMenu)) {
            $menu = array();
            if ($canEdit) {
                $menu[] = array(
                    'label' => $this->translate('Edit'),
                    'name' => 'edit',
                    'url' => 'listings/photo/edit/' . $photo->getIdentity(),
                );
            }
            if ($canDelete) {
                $menu[] = array(
                    'label' => $this->translate('Delete'),
                    'name' => 'delete',
                    'url' => 'listings/photo/delete/' . $photo->getIdentity(),
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

            $tempPhoto['menu'] = $menu;
        }

        $this->respondWithSuccess($tempPhoto);
    }

    /**
     * EDIT PHOTO - ADD TITLE AND DESCRIPTION
     * 
     * @return array
     */
    public function editAction() {
        //GET PHOTO SUBJECT
        $photo = Engine_Api::_()->core()->getSubject();

        //GET VIEWER
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

        //GET LISTING TYPE ID
        $listingtype_id = $this->_listingType->listingtype_id;

        //AUTHORIZATION CHECK
        $canEdit = 0; //$photo->authorization()->isAllowed(null, "edit_listtype_$listingtype_id");
        if (empty($canEdit) && $photo->user_id == $viewer_id) {
            $canEdit = 1;
        }

        if (empty($canEdit)) {
            $this->respondWithError('unauthorized');
        }

        /* RETURN THE EVENT PHOTO EDIT FORM IN THE FOLLOWING CASES:      
         * - IF THERE ARE GET METHOD AVAILABLE.
         * - IF THERE ARE NO FORM POST VALUES AVAILABLE.
         */
        if ($this->getRequest()->isGet()) {
            $formValues = $photo->toArray();
            $this->respondWithSuccess(array(
                'form' => Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getPhotoEditForm(),
                'formValues' => $formValues
            ));
        } else if ($this->getRequest()->isPut() || $this->getRequest()->isPost()) {
            /* SAVE VALUES IN DATABASE AND THEN RETURN RESPONSE ACCORDINGLY. IN THE FOLLOWING CASES:  
             * - IF THERE ARE POST METHOD AVAILABLE.
             * - IF THERE ARE FORM POST VALUES AVAILABLE IN VALUES PARAMETER.
             */

            $db = Engine_Api::_()->getDbtable('photos', 'sitereview')->getAdapter();
            $db->beginTransaction();
            try {
                // CONVERT POST DATA INTO THE ARRAY.
                $values = $photo->toArray();
                $getForm = Engine_Api::_()->getApi('Siteapi_Core', 'sitereview')->getPhotoEditForm();
                foreach ($getForm as $element) {

                    if (isset($_REQUEST[$element['name']]))
                        $values[$element['name']] = $_REQUEST[$element['name']];
                }

                // START FORM VALIDATION
                $validators = Engine_Api::_()->getApi('Siteapi_FormValidators', 'sitereview')->getPhotoEditValidators();
                $values['validators'] = $validators;
                $validationMessage = $this->isValid($values);
                if (!empty($validationMessage) && @is_array($validationMessage)) {
                    $this->respondWithValidationError('validation_fail', $validationMessage);
                }

                $photo->setFromArray($values)->save();
                $db->commit();

                $this->successResponseNoContent('no_content', true);
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }
        }
    }

    /**
     * Delete Image
     * 
     * @return array
     */
    public function deleteAction() {
        // Validate request methods
        $this->validateRequestMethod('DELETE');

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (empty($viewer_id))
            $this->respondWithError('unauthorized');

        $photo = Engine_Api::_()->core()->getSubject();

        if ($photo->user_id != $viewer_id) {
            $this->respondWithError('unauthorized');
        }

        $db = Engine_Api::_()->getDbtable('photos', 'sitereview')->getAdapter();
        $db->beginTransaction();

        try {
            $photo->delete();
            $db->commit();
            $this->successResponseNoContent('no_content', true);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

}
