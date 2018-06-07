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
class Advancedactivity_IndexController extends Siteapi_Controller_Action_Standard {

    /**
     * Feed action id
     *
     * @var int
     */
    protected $_action_id;

    /**
     * Feed object
     *
     * @var object
     */
    protected $_action;

    /**
     * Init model
     *
     */
    public function init() {
        // Throw error for logged-out user.
        if (!$this->_helper->requireUser()->isValid()) {
            $this->_forward('throw-error', 'index', 'advancedactivity', array(
                "error_code" => "unauthorized"
            ));
            return;
        }
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        // If action_id available then create the feed object otherwise return the error.
        if (($this->_action_id = (int) $this->getRequestParam('action_id', 0)) && !empty($this->_action_id)) {
            $this->_action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($this->_action_id);
        } else {
            $this->_forward('throw-error', 'index', 'advancedactivity', array(
                "error_code" => "parameter_missing",
                "message" => "action_id"
            ));
            return;
        }

        // Set subject
        $subject_type = $this->getRequestParam('subject_type');
        if (0 !== ($subject_id = (int) $this->getRequestParam('subject_id')) &&
                null !== ($subject = Engine_Api::_()->getItem($subject_type, $subject_id)))
            Engine_Api::_()->core()->setSubject($subject);
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
     * Feed Menus - Save Feed (Save the activity feed for logged-in user)  
     *
     * @return array
     */
    public function updateSaveFeedAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');

        $isFeedSaved = 0;
        $viewer = Engine_Api::_()->user()->getViewer();

        $table = Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity');
        $table->setSaveFeeds($viewer, $this->_action_id, $this->_action->type);

        if (null === ($prev = $table->getSaveFeed($viewer, $this->_action_id)) ||
                false === $prev)
            $isFeedSaved = 1;

        $this->respondWithSuccess($isFeedSaved);
    }

    /**
     * Edit feed body
     */
    public function editFeedAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $action_id = $this->_getParam('action_id');

        if (!$action_id)
            $this->respondWithValidationError('parameter_missing', 'action_id');

        $subject = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($this->_action_id);

        if (!$subject)
            $this->respondWithError('no_record');

        // Check logged-in user ownership for feed.
        if ($subject->getType() == 'siteevent_event' && ($subject->getParent()->getType() == 'sitepage_page' || $subject->getParent()->getType() == 'sitbusiness_business' || $subject->getParent()->getType() == 'sitegroup_group' || $subject->getParent()->getType() == 'sitestore_store')) {
            $subject = Engine_Api::_()->getItem($subject->getParent()->getType(), $subject->getParent()->getIdentity());
        }

        switch ($subject->getType()) {
            case 'user':
                $is_owner = $viewer->isSelf($subject);
                break;
            case 'sitepage_page':
            case 'sitebusiness_business':
            case 'sitegroup_group':
            case 'sitestore_store':
                $is_owner = $subject->isOwner($viewer);
                break;
            case 'sitepageevent_event':
            case 'sitebusinessevent_event':
            case 'sitegroupevent_event':
            case 'sitestoreevent_event':
                $is_owner = $viewer->isSelf($subject);
                if (empty($is_owner)) {
                    $is_owner = $subject->getParent()->isOwner($viewer);
                }
                break;
            default :
                $is_owner = $viewer->isSelf($subject->getOwner());
                break;
        }

        $values = $_REQUEST;
        if (!$activity_moderate && !$is_owner)
            $this->respondWithError('unauthorized');

        $body = $this->_getParam('body');

        if (empty($body))
            $this->respondWithValidationError('parameter_missing', 'body');
        if (!empty($values['schedule_time']) && $values['schedule_time'] != '0000-00-00') {

            $dbTime = Engine_Api::_()->advancedactivity()->userToDbDateTime(array('starttime' => $values['schedule_time']));
            $values['publish_date'] = $dbTime['starttime'];
        }

        $subject->setFromArray($values);
        if ($values['auth_view'] != $subject->privacy) {
            $subject->privacy = $values['auth_view'];
            $privacy = $values['auth_view'];
        }
        $subject->save();

        if ($privacy && $subject->attachment_count > 0) {
            if (!in_array($privacy, array('everyone', 'networks', 'friends', 'onlyme'))) {
                $privacy = 'onlyme';
            }
            foreach ($subject->getAttachments() as $attachment) {
                Engine_Api::_()->advancedactivity()->editContentPrivacy($attachment->item, $viewer, $privacy);
            }
        }
        if ($privacy) {
            Engine_Api::_()->getDbtable('actions', 'advancedactivity')->resetActivityBindings($subject);
        }

        // Decode the checkin string to array
        if (isset($values['composer']) && !is_array($values['composer']['checkin']))
            $values['composer'] = Zend_Json::decode($values['composer']);

        $composerDatas = $values['composer'];
        foreach ($composerDatas as $composerDataType => $composerDataValue) {
            if (empty($composerDataValue))
                continue;

            if (isset($values['composer']['checkin']) && !empty($values['composer']['checkin']) && $composerDataType == 'checkin') {
                $data['composer'][$composerDataType]['plugin'] = array(
                    'plugin' => 'Sitetagcheckin_Plugin_Composer'
                );
                // @Todo: NEED TO CHECK
                if (isset($data['composer'][$composerDataType]['plugin']) && !empty($data['composer'][$composerDataType]['plugin'])) {
                    $pluginClass = $data['composer'][$composerDataType]['plugin'];
                    Engine_Api::_()->getApi('Siteapi_Core', 'sitetagcheckin')->onAAFComposerCheckin(array($composerDataType => $composerDataValue), array('action' => $subject));
                }
            } elseif (isset($values['composer']['tag']) && !empty($values['composer']['tag']) && $composerDataType == 'tag') {
                try {
                    $pluginClass = 'Advancedactivity_Plugin_Composer_Tag';
                    $plugin = Engine_Api::_()->loadClass($pluginClass);
                    $method = 'onAAFComposer' . ucfirst($composerDataType);
                    if (method_exists($plugin, $method))
                        $plugin->$method(array($composerDataType => $composerDataValue), array('action' => $subject));
                } catch (Exception $ex) {
                    
                }
            } elseif (isset($values['composer']['banner']) && !empty($values['composer']['banner']) && $composerDataType == 'banner') {
                $pluginClass = 'Advancedactivity_Plugin_Composer_Banner';
                $plugin = Engine_Api::_()->loadClass($pluginClass);
                $method = 'onAAFComposer' . ucfirst($composerDataType);

                if (method_exists($plugin, $method))
                    $plugin->$method(array($composerDataType => $composerDataValue), array('action' => $subject));
            }
            elseif (isset($values['composer']['feeling']) && !empty($values['composer']['feeling']) && $composerDataType == 'feeling') {
                $pluginClass = 'Advancedactivity_Plugin_Composer_Feeling';
                $plugin = Engine_Api::_()->loadClass($pluginClass);
                $method = 'onAAFComposer' . ucfirst($composerDataType);

                if (method_exists($plugin, $method))
                    $plugin->$method(array($composerDataType => $composerDataValue), array('action' => $subject));
            }
        }

        $this->successResponseNoContent('no_content');
    }

    /**
     * Feed Menus - Delete Feed (Delete feed OR feed comment)  
     *
     * @return array
     */
    function deleteAction() {
        // Validate request methods
        $this->validateRequestMethod('DELETE');

        // Get params
        $is_owner = false;
        $comment_id = $this->getRequestParam('comment_id', null);
        $viewer = Engine_Api::_()->user()->getViewer();
        $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');

        // Check logged-in user ownership for feed.
        if (Engine_Api::_()->core()->hasSubject()) {
            $subject = Engine_Api::_()->core()->getSubject();
            if ($subject->getType() == 'siteevent_event' && ($subject->getParent()->getType() == 'sitepage_page' || $subject->getParent()->getType() == 'sitbusiness_business' || $subject->getParent()->getType() == 'sitegroup_group' || $subject->getParent()->getType() == 'sitestore_store')) {
                $subject = Engine_Api::_()->getItem($subject->getParent()->getType(), $subject->getParent()->getIdentity());
            }
            switch ($subject->getType()) {
                case 'user':
                    $is_owner = $viewer->isSelf($subject);
                    break;
                case 'sitepage_page':
                case 'sitebusiness_business':
                case 'sitegroup_group':
                case 'sitestore_store':
                    $is_owner = $subject->isOwner($viewer);
                    break;
                case 'sitepageevent_event':
                case 'sitebusinessevent_event':
                case 'sitegroupevent_event':
                case 'sitestoreevent_event':
                    $is_owner = $viewer->isSelf($subject);
                    if (empty($is_owner)) {
                        $is_owner = $subject->getParent()->isOwner($viewer);
                    }
                    break;
                default :
                    $is_owner = $viewer->isSelf($subject->getOwner());
                    break;
            }
        }

        // Both the author and the person being written about get to delete the action_id
        if (!$comment_id && (
                $activity_moderate || $is_owner ||
                ('user' == $this->_action->subject_type && $viewer->getIdentity() == $this->_action->subject_id) || // owner of profile being commented on
                ('user' == $this->_action->object_type && $viewer->getIdentity() == $this->_action->object_id))) {   // commenter
            // Delete action item and all comments/likes
            $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
            $db->beginTransaction();
            try {
                if ($this->_action->getTypeInfo()->commentable <= 1) {
                    $comments = $this->_action->getComments(1);
                    if ($comments) {
                        foreach ($comments as $action_comments) {
                            $action_comments->delete();
                        }
                    }
                }
                $this->_action->deleteItem();
                $db->commit();

                $this->successResponseNoContent('no_content', 'feed_index_homefeed');
            } catch (Exception $e) {
                $db->rollBack();
                $this->respondWithValidationError('internal_server_error', $e->getMessage());
            }
        } elseif ($comment_id) {
            $comment = $this->_action->comments()->getComment($comment_id);
            $db = Engine_Api::_()->getDbtable('comments', 'activity')->getAdapter();
            $db->beginTransaction();
            if ($activity_moderate || $is_owner ||
                    ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
                    ('user' == $this->_action->object_type && $viewer->getIdentity() == $this->_action->object_id)) {
                try {
                    $this->_action->comments()->removeComment($comment_id);
                    $db->commit();

                    $this->successResponseNoContent('no_content', 'feed_index_homefeed');
                } catch (Exception $e) {
                    $db->rollBack();
                    $this->respondWithValidationError('internal_server_error', $e->getMessage());
                }
            } else {
                $this->respondWithError('unauthorized');
            }
        }
    }

    /**
     * Feed Menus - Enable Comments / Disable Comments (Modified the comments for feed)  
     *
     * @return array
     */
    public function updateCommentableAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');

        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();
        try {
            $this->_action->commentable = !$this->_action->commentable;
            $this->_action->save();
            $db->commit();

            $this->respondWithSuccess($this->_action->commentable);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    /**
     * Feed Menus - Lock this Feed / Unlock this Feed (Modified the shareable for feed)  
     *
     * @return array
     */
    public function updateShareableAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');

        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();
        try {
            $this->_action->shareable = !$this->_action->shareable;
            $this->_action->save();
            $db->commit();

            $this->respondWithSuccess($this->_action->shareable);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * @return array
     */
    public function likeAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();

        // Get Params
        $comment_id = $this->getRequestParam('comment_id', null);
        $sendAppNotification = $this->getRequestParam('sendNotification', 1);

        $viewer = Engine_Api::_()->user()->getViewer();

        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            // Action
            if (!$comment_id) {

                if (!isset($this->_action) || empty($this->_action)) {
                    $this->respondWithError('no_record');
                }
                
                $canComment = ($this->_action->getTypeInfo()->commentable && $this->_action->commentable &&
                        $viewer->getIdentity() &&
                        Engine_Api::_()->authorization()->isAllowed($this->_action->getCommentObject(), null, 'comment'));
                
                // Check authorization
                if (!$canComment)
                    $this->respondWithError('unauthorized');
                
                //new reaction code start
                $reaction = $this->getRequestParam('reaction');
                $like = $this->_action->likes()->getLike($viewer);
                $sendNotification = false;
                $shouldAddActivity = false;
                if (empty($like)) {
                    $sendNotification = true;
                    $like = $this->_action->likes()->addLike($viewer);
                    $shouldAddActivity = $reaction && $reaction !== 'like';
                }

                if ($reaction) {
                    $like->reaction = $reaction;
                    $like->save();
                }
                // Add activity
                if (isset($sendAppNotification) && !empty($sendAppNotification)) {
                    if ($shouldAddActivity) {
                        $api = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
                        if ($this->_action->getTypeInfoCommentable() < 2) {
                            $shouldAddActivity = in_array($this->_action->type, array('status'));
                            $attachment = $action;
                            $attachmentOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);
                        } else {
                            $attachment = $this->_action->getCommentObject();
                            $attachmentOwner = $attachment->getOwner();
                        }
                        // Add activity for owner of activity (if user and not viewer)
                        if ($shouldAddActivity && $attachmentOwner->getType() == 'user' && $attachmentOwner->getIdentity() != $viewer->getIdentity()) {
                            $params = array(
                                'type' => $attachment->getMediaType(),
                                'owner' => $attachmentOwner->getGuid(),
                            );
                            $likeAction = $api->addActivity($viewer, $attachment, 'react', '', '', $params);
                            if ($likeAction) {
                                $api->attachActivity($likeAction, $attachment);
                            }
                        }
                    }

                    //reaction code end
                    // Add notification for owner of activity (if user and not viewer)
                    if ($this->_action->subject_type == 'user' && $this->_action->subject_id != $viewer->getIdentity()) {
                        $actionOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);

                        $notificationType = isset($like->reaction) && $like->reaction !== 'like' ? 'reacted' : 'liked';
                        Engine_Api::_()->getApi('Siteapi_Core', 'activity')->addNotification($actionOwner, $viewer, $this->_action, $notificationType, array(
                            'label' => 'post'
                        ));
                    }
                }
            }
            // Comment
            else {
                $comment = $this->_action->comments()->getComment($comment_id);

                // Check authorization
//                if (!$comment || !Engine_Api::_()->authorization()->isAllowed($comment, null, 'comment'))
//                    $this->respondWithError('unauthorized');

                if (!$comment)
                    $this->respondWithError('no_record');

                $comment->likes()->addLike($viewer);

                // @todo make sure notifications work right
                if ($comment->poster_id != $viewer->getIdentity()) {
                    Engine_Api::_()->getApi('Siteapi_Core', 'activity')
                            ->addNotification($comment->getPoster(), $viewer, $comment, 'liked', array(
                                'label' => 'comment'
                    ));
                }

                // Add notification for owner of activity (if user and not viewer)
                if ($this->_action->subject_type == 'user' && $this->_action->subject_id != $viewer->getIdentity()) {
                    $actionOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);
                }
            }

            // Stats
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.likes');

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }

//    $bodyArray = array();
//    $bodyArray["name"] = "unlike";
//    $bodyArray["label"] = $this->translate("Unlike");
//    $bodyArray["isLike"] = 1;
//
//    if ( !empty($comment_id) ) {
//      $bodyArray["url"] = "unlike";
//      $bodyArray["urlParams"] = array(
//          "action_id" => $this->_action_id,
//          "subject_type" => $this->_action->getObject()->getType(),
//          "subject_id" => $this->_action->getObject()->getIdentity(),
//          "comment_id" => $comment_id
//      );
//    } else {
//      $bodyArray["url"] = "unlike";
//      $bodyArray["urlParams"] = array(
//          "action_id" => $this->_action_id,
//          "subject_type" => $this->_action->getObject()->getType(),
//          "subject_id" => $this->_action->getObject()->getIdentity()
//      );
//    }
//    $this->respondWithSuccess($bodyArray);

        $this->successResponseNoContent('no_content');
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * @return array
     */
    public function sendLikeNotitficationAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        $reaction = $this->getRequestParam('reaction');

        $viewer = Engine_Api::_()->user()->getViewer();

        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $like = $reaction ? $this->_action->likes()->getLike($viewer) : null;
            $shouldAddActivity = false;
            $sendNotification = true;
            $shouldAddActivity = $reaction && $reaction !== 'like';
            // Add activity
            if ($shouldAddActivity) {
                $api = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
                if ($this->_action->getTypeInfoCommentable() < 2) {
                    $shouldAddActivity = in_array($this->_action->type, array('status'));
                    $attachment = $this->_action;
                    $attachmentOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);
                } else {
                    $attachment = $this->_action->getCommentObject();
                    $attachmentOwner = $attachment->getOwner();
                }
                // Add activity for owner of activity (if user and not viewer)
                if ($shouldAddActivity && $attachmentOwner->getType() == 'user' && $attachmentOwner->getIdentity() != $viewer->getIdentity()) {
                    $params = array(
                        'type' => $attachment->getMediaType(),
                        'owner' => $attachmentOwner->getGuid(),
                    );
                    $likeAction = $api->addActivity($viewer, $attachment, 'react', '', '', $params);
                    if ($likeAction) {
                        $api->attachActivity($likeAction, $attachment);
                    }
                }
            }

            //reaction code end
            // Add notification for owner of activity (if user and not viewer)
            if ($this->_action->subject_type == 'user' && $this->_action->subject_id != $viewer->getIdentity()) {
                $actionOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);

                $notificationType = isset($like->reaction) && $like->reaction !== 'like' ? 'reacted' : 'liked';
                Engine_Api::_()->getApi('Siteapi_Core', 'activity')->addNotification($actionOwner, $viewer, $this->_action, $notificationType, array(
                    'label' => 'post'
                ));
            }
        } catch (Exception $e) {
            //Blank Exception 
        }
        $this->successResponseNoContent('no_content');
    }

    /**
     * Handles HTTP request to remove a like from an activity feed item
     *
     * @return array
     */
    public function unlikeAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');

        $comment_id = $this->getRequestParam('comment_id');
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$comment_id) {
            // Check authorization
            if (!Engine_Api::_()->authorization()->isAllowed($this->_action->getObject(), null, 'comment'))
                $this->respondWithError('unauthorized');
        }else {
            $comment = $this->_action->comments()->getComment($comment_id);

            // Check authorization
//            if (!$comment || !Engine_Api::_()->authorization()->isAllowed($comment, null, 'comment'))
//                $this->respondWithError('unauthorized');

            if (!$comment)
                $this->respondWithError('no_record');
        }

        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            // Action
            if (!$comment_id)
                $this->_action->likes()->removeLike($viewer);
            else
                $comment->likes()->removeLike($viewer);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }

        $this->successResponseNoContent('no_content');
    }

    /**
     * Handles HTTP POST request to comment on an activity feed item
     *
     * @return array
     */
    public function commentAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
        //TRY ATTACHMENT GETTING STUFF
        $attachment = null;
        $body = '';
        $attachmentData = $this->getRequestParam('attachment_id');
        $attachmentType = $this->getRequestParam('attachment_type');
        $composerDatas = $this->getRequestParam('composer');
        if (!empty($composerDatas)) {
            $composerDatas = Zend_Json::decode($composerDatas);
        } else {
            $composerDatas = array();
        }
        // $composerDatas = !empty($this->getRequestParam('composer'))  ? Zend_Json::decode($this->getRequestParam('composer', null)) : array();
        // Start transaction
        $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
        $db->beginTransaction();
        $send_notification = $this->getRequestParam('send_notification', 1);
        try {
            $viewer = Engine_Api::_()->user()->getViewer();

            $postData = $_REQUEST;

            if (isset($postData['body']) && !empty($postData['body']))
                $body = $postData['body'];

            if (empty($body) && !isset($attachmentType) && empty($attachmentType))
                $this->respondWithError('validation_fail');

            // Check authorization
            if (!Engine_Api::_()->authorization()->isAllowed($this->_action->getObject(), null, 'comment'))
                $this->respondWithError('unauthorized');

            //Reaction post work start 
            if (isset($attachmentType) && !empty($attachmentType)) {
                if ($attachmentType == 'sticker' && isset($attachmentData))
                    $attachment = Engine_Api::_()->getItemByGuid($attachmentData);
                if ($attachmentType == 'photo' && !empty($_FILES['photo'])) {
                    $table = Engine_Api::_()->getDbtable('albums', 'album');
                    $type = $this->getRequestParam('image_type', 'comment');
                    $album = $this->getSpecialAlbum($viewer, $type);
                    $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
                    $photo = $photoTable->createRow();
                    $photo->owner_type = 'user';
                    $photo->owner_id = $viewer->getIdentity();
                    $photo->save();
// Set the photo
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitealbum')) {
                        $photo = Engine_Api::_()->getApi('core', 'siteapi')->setPhoto($_FILES['photo'], $photo);
                    } else {
                        $photo = $this->_setPhoto($_FILES['photo'], $photo);
                    }
                    $photo->order = $photo->photo_id;
                    $photo->album_id = $album->album_id;
                    $photo->save();
                    if (!$album->photo_id) {
                        $album->photo_id = $photo->getIdentity();
                        $album->save();
                    }
                    $auth = Engine_Api::_()->authorization()->context;
                    $auth->setAllowed($photo, 'everyone', 'view', true);
                    $auth->setAllowed($photo, 'everyone', 'comment', true);
                    $attachment = $photo;
                }
            }

            // Reaction post work end
            // Add the comment
            $comment = $this->_action->comments()->addComment($viewer, $body);
            if (empty($comment))
                $this->respondWithError('unauthorized');

            if ($attachment) {
                if (isset($comment->attachment_type))
                    $comment->attachment_type = ( $attachment ? $attachment->getType() : '' );
                if (isset($comment->attachment_id))
                    $comment->attachment_id = ( $attachment ? $attachment->getIdentity() : 0 );
                $comment->save();
            }

            // Notifications
            $notifyApi = Engine_Api::_()->getApi('Siteapi_Core', 'activity');

            if (isset($send_notification) && !empty($send_notification)) {
                $actionOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);
                // Add notification for owner of activity (if user and not viewer)
                if ($this->_action->subject_type == 'user' && $this->_action->subject_id != $viewer->getIdentity()) {
                    $notifyApi->addNotification($actionOwner, $viewer, $this->_action, 'commented', array(
                        'label' => 'post'
                    ));
                }

//             Add a notification for all users that commented or like except the viewer and poster
//             @todo we should probably limit this
                foreach ($this->_action->comments()->getAllCommentsUsers() as $notifyUser) {
                    if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
                        $notifyApi->addNotification($notifyUser, $viewer, $this->_action, 'commented_commented', array(
                            'label' => 'post'
                        ));
                    }
                }
//
//            // Add a notification for all users that commented or like except the viewer and poster
//            // @todo we should probably limit this
                foreach ($this->_action->likes()->getAllLikesUsers() as $notifyUser) {
                    if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {

                        $notifyApi->addNotification($notifyUser, $viewer, $this->_action, 'liked_commented', array(
                            'label' => 'post'
                        ));
                    }
                }
            }
            $canComment = Engine_Api::_()->authorization()->isAllowed($this->_action->getObject(), null, 'comment');
            $canDelete = Engine_Api::_()->authorization()->isAllowed($this->_action->getObject(), null, 'edit');

            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

            //User Tagging work start
            $tagsArray = array();
            parse_str($composerDatas['tag'], $tagsArray);
            if (!empty($tagsArray)) {
                if ($this->_action) {
                    $data = array_merge((array) $action->params, array('tags' => $tagsArray));
                    $comment->params = Zend_Json::encode($data);
                }
                $comment->save();
            }

            //User Tagging work start
            $commentInfo = array();
            $poster = Engine_Api::_()->getItem($comment->poster_type, $comment->poster_id);
            $commentInfo["action_id"] = $this->_action_id;
            $commentInfo["comment_id"] = $comment->comment_id;

            // Add images
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($poster);
            $commentInfo = array_merge($commentInfo, $getContentImages);

            //to provide the same image names as in likes-comment response
            $getContentImages = array();
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($poster, false, 'author');
            $commentInfo = array_merge($commentInfo, $getContentImages);
            $commentInfo["author_title"] = $poster->getTitle();
            $commentInfo["user_id"] = $poster->getIdentity();
            $commentInfo["comment_body"] = $comment->body;
            if (isset($comment->params) && !empty($comment->params))
                $commentInfo["userTag"] = Engine_Api::_()->getApi('Siteapi_Feed', 'advancedactivity')->tagUserArray($comment->params);
            else {
                $commentInfo["userTag"] = '';
            }
            $commentInfo["params"] = isset($comment->params) ? $comment->params : "";
            $commentInfo["comment_date"] = $comment->creation_date;

            if (Engine_Api::_()->getApi('Siteapi_Feed', 'advancedactivity')->isSitestickerPluginLive()) {
                if (isset($comment->attachment_type) && !empty($comment->attachment_type) && isset($comment->attachment_id) && !empty($comment->attachment_id)) {
                    $attachment = Engine_Api::_()->getItem($comment->attachment_type, $comment->attachment_id);
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($attachment, false);
                    $commentInfo['attachment'] = $getContentImages;
                    $commentInfo['attachment_type'] = $comment->attachment_type;
                    $commentInfo['attachment_id'] = $comment->attachment_id;
                }
            }

            if (!empty($canDelete) || $poster->isSelf($viewer)) {
                $commentInfo["delete"] = array(
                    "name" => "delete",
                    "label" => $this->translate("Delete"),
                    "url" => "comment-delete",
                    'urlParams' => array(
                        "action_id" => $this->_action_id,
                        "subject_type" => $this->_action->getType(),
                        "subject_id" => $this->_action->getIdentity(),
                        "comment_id" => $comment->comment_id
                    )
                );
            } else {
                $commentInfo["delete"] = null;
            }

            if (!empty($canComment)) {
                $isLiked = $comment->likes()->isLike($viewer);
                if (empty($isLiked)) {
                    $likeInfo["name"] = "like";
                    $likeInfo["label"] = $this->translate("Like");
                    $likeInfo["url"] = "like";
                    $likeInfo["urlParams"] = array(
                        "action_id" => $this->_action_id,
                        "subject_type" => $this->_action->getType(),
                        "subject_id" => $this->_action->getIdentity(),
                        "comment_id" => $comment->getIdentity()
                    );
                    $likeInfo["isLike"] = 0;
                } else {
                    $likeInfo["name"] = "unlike";
                    $likeInfo["label"] = $this->translate("Unlike");
                    $likeInfo["url"] = "unlike";
                    $likeInfo["urlParams"] = array(
                        "action_id" => $this->_action_id,
                        "subject_type" => $this->_action->getType(),
                        "subject_id" => $this->_action->getIdentity(),
                        "comment_id" => $comment->getIdentity()
                    );

                    $likeInfo["isLike"] = 1;
                }
                $commentInfo["like_count"] = $comment->likes()->getLikeCount();
                $commentInfo["like"] = $likeInfo;
            } else {
                $commentInfo["like"] = null;
            }

            $db->commit();
            $this->respondWithSuccess($commentInfo);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    public function addCommentNotificationsAction() {
        // Validate request methods
        $this->validateRequestMethod('POST');

        // Start transaction
        $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $actionOwner = Engine_Api::_()->getItemByGuid($this->_action->subject_type . "_" . $this->_action->subject_id);

            $postData = $_REQUEST;
            $comment_id = $this->getRequestParam('comment_id');

            $comment = $this->_action->comments()->getComment($comment_id);

            if (empty($comment))
                $this->respondWithError('validation_fail');

            // Check authorization
            if (!Engine_Api::_()->authorization()->isAllowed($this->_action->getObject(), null, 'comment'))
                $this->respondWithError('unauthorized');


            // Notifications
            $notifyApi = Engine_Api::_()->getApi('Siteapi_Core', 'activity');


            // Add notification for owner of activity (if user and not viewer)
            if ($this->_action->subject_type == 'user' && $this->_action->subject_id != $viewer->getIdentity()) {
                $notifyApi->addNotification($actionOwner, $viewer, $this->_action, 'commented', array(
                    'label' => 'post'
                ));
            }
//             Add a notification for all users that commented or like except the viewer and poster
//             @todo we should probably limit this
            foreach ($this->_action->comments()->getAllCommentsUsers() as $notifyUser) {
                if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
                    $notifyApi->addNotification($notifyUser, $viewer, $this->_action, 'commented_commented', array(
                        'label' => 'post'
                    ));
                }
            }
//
//            // Add a notification for all users that commented or like except the viewer and poster
//            // @todo we should probably limit this
            foreach ($this->_action->likes()->getAllLikesUsers() as $notifyUser) {
                if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
                    $notifyApi->addNotification($notifyUser, $viewer, $this->_action, 'liked_commented', array(
                        'label' => 'post'
                    ));
                }
            }

            $tagsArray = array();
             $composerDatas = $this->getRequestParam('composer');
        if (!empty($composerDatas)) {
            $composerDatas = Zend_Json::decode($composerDatas);
        } else {
            $composerDatas = array();
        }
        
            parse_str($composerDatas['tag'], $tagsArray);
            if (!empty($tagsArray)) {
                $viewer = Engine_Api::_()->_()->user()->getViewer();
                $type_name = Zend_Registry::get('Zend_Translate')->translate('post');
                if (is_array($type_name)) {
                    $type_name = $type_name[0];
                } else {
                    $type_name = 'post';
                }
                $notificationAPi = Engine_Api::_()->getApi('Siteapi_Core', 'activity');
                foreach ($tagsArray as $key => $tagStrValue) {
                    $tag = Engine_Api::_()->getItemByGuid($key);
                    // Don't send a notification if the user both commented and liked this
                    if (in_array($tag->getIdentity(), $commentedUserNotifications))
                        continue;

                    if ($action && $tag && ($tag instanceof User_Model_User) && !$tag->isSelf($viewer)) {
                        $notificationAPi->addNotification($tag, $viewer, $action, 'tagged', array(
                            'object_type_name' => $type_name,
                            'label' => $type_name,
                        ));
                    } else if ($tag && ($tag instanceof Sitepage_Model_Page)) {
                        $subject_title = $viewer->getTitle();
                        $page_title = $tag->getTitle();
                        foreach ($tag->getPageAdmins() as $owner) {
                            if ($action && $owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
                                $notificationAPi->addNotification($owner, $viewer, $action, 'sitepage_tagged', array(
                                    'subject_title' => $subject_title,
                                    'label' => $type_name,
                                    'object_type_name' => $type_name,
                                    'page_title' => $page_title
                                ));
                            }
                        }
                    } else if ($tag && ($tag instanceof Sitebusiness_Model_Business)) {
                        $subject_title = $viewer->getTitle();
                        $business_title = $tag->getTitle();
                        foreach ($tag->getBusinessAdmins() as $owner) {
                            if ($action && $owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
                                $notificationAPi->addNotification($owner, $viewer, $action, 'sitebusiness_tagged', array(
                                    'subject_title' => $subject_title,
                                    'label' => $type_name,
                                    'object_type_name' => $type_name,
                                    'business_title' => $business_title
                                ));
                            }
                        }
                    } else if ($tag && ($tag instanceof Sitegroup_Model_Group)) {
                        $subject_title = $viewer->getTitle();
                        $store_title = $tag->getTitle();
                        foreach ($tag->getGroupAdmins() as $owner) {
                            if ($action && $owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
                                $notificationAPi->addNotification($owner, $viewer, $action, 'sitegroup_tagged', array(
                                    'subject_title' => $subject_title,
                                    'label' => $type_name,
                                    'object_type_name' => $type_name,
                                    'group_title' => $store_title
                                ));
                            }
                        }
                    } else if ($tag && ($tag instanceof Sitestore_Model_Store)) {
                        $subject_title = $viewer->getTitle();
                        $store_title = $tag->getTitle();
                        foreach ($tag->getStoreAdmins() as $owner) {
                            if ($action && $owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
                                $notificationAPi->addNotification($owner, $viewer, $action, 'sitestore_tagged', array(
                                    'subject_title' => $subject_title,
                                    'label' => $type_name,
                                    'object_type_name' => $type_name,
                                    'store_title' => $store_title
                                ));
                            }
                        }
                    } else if ($tag && ($tag instanceof Core_Model_Item_Abstract)) {
                        $subject_title = $viewer->getTitle();
                        $item_type = Zend_Registry::get('Zend_Translate')->translate($tag->getShortType());
                        $item_title = $tag->getTitle();
                        $owner = $tag->getOwner();
                        if ($action && $owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
                            $notificationAPi->addNotification($owner, $viewer, $action, 'aaf_tagged', array(
                                'subject_title' => $subject_title,
                                'label' => $type_name,
                                'object_type_name' => $type_name,
                                'item_title' => $item_title,
                                'item_type' => $item_type
                            ));
                        }
                        if (($tag instanceof Group_Model_Group)) {
                            foreach ($tag->getOfficerList()->getAll() as $offices) {
                                $owner = Engine_Api::_()->getItem('user', $offices->child_id);
                                if ($action && $owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
                                    $notificationAPi->addNotification($owner, $viewer, $action, 'aaf_tagged', array(
                                        'subject_title' => $subject_title,
                                        'label' => $type_name,
                                        'object_type_name' => $type_name,
                                        'item_title' => $item_title,
                                        'item_type' => $item_type
                                    ));
                                }
                            }
                        }
                    }
                }
            }

            $db->commit();
            $this->successResponseNoContent('no_content');
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    public function composeUploadAction() {
        $this->validateRequestMethod('POST');

        if (!Engine_Api::_()->user()->getViewer()->getIdentity()) {
            $this->respondWithError('unauthorized');
        }

        if (empty($_FILES['photo'])) {
            $this->respondWithError('file_not_uploaded');
        }

        // Get album
        $viewer = Engine_Api::_()->user()->getViewer();
        $table = Engine_Api::_()->getDbtable('albums', 'album');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $type = $this->_getParam('type', 'comment');

            if (empty($type))
                $type = 'comment';
            $album = $this->getSpecialAlbum($viewer, $type);

            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
            $photo = $photoTable->createRow();
            $photo->setFromArray(array(
                'owner_type' => 'user',
                'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
            ));
            $photo->save();
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitealbum')) {
                $photo = Engine_Api::_()->getApi('core', 'siteapi')->setPhoto($_FILES['photo'], $photo);
            } else
                $photo->setPhoto($_FILES['photo']);
            $photo->order = $photo->photo_id;
            $photo->album_id = $album->album_id;
            $photo->save();

            if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
            }
            // Authorizations
            $auth = Engine_Api::_()->authorization()->context;
            $auth->setAllowed($photo, 'everyone', 'view', true);
            $auth->setAllowed($photo, 'everyone', 'comment', true);

            $db->commit();
            $response['response'] = $photo->toArray();
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($photo);
            $response['response'] = array_merge($response['response'], $getContentImages);
            $this->respondWithSuccess($response, true);
        } catch (Exception $e) {
            $db->rollBack();
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
    }

    public function turnOnOffNotificationAction() {
        $action_id = $this->_getParam('action_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (empty($action_id) || empty($viewer)) {
            return;
        }
        $offNotification = Engine_Api::_()->getDbtable('notificationsettings', 'advancedactivity');
        $userIds = $offNotification->getUserIdsByActionId($action_id);
        $row = $offNotification->select()->from($offNotification->info('name'), array('action_id'))
                ->where('action_id  = ?', $action_id)
                ->query()
                ->fetchColumn();

        if (empty($userIds) && empty($row)) {
            $data = array('action_id' => $action_id, 'user_ids' => json_encode(array($viewer->getIdentity())));
            $offNotification->insert($data);
        } elseif (!in_array($viewer->getIdentity(), $userIds) || empty($row)) {
            $data = array('user_ids' => json_encode(array_merge($userIds, array($viewer->getIdentity()))));
            $offNotification->update($data, "action_id = $action_id");
        } else {
            $diff = array_diff($userIds, array($viewer->getIdentity()));
//            if(empty(array_diff($userIds,array($viewer->getIdentity())))){
//                
//            }
            $data = array('user_ids' => json_encode(array_diff($userIds, array($viewer->getIdentity()))));
            $offNotification->update($data, "action_id = $action_id");
        }
        $this->successResponseNoContent('no_content', true);
    }

    public function pinUnpinAction() {

        $action_id = $this->_getParam('action_id');
        $type = $this->_getParam('type');
        $time = $this->_getParam('time', null);
        $viewer = Engine_Api::_()->user()->getViewer();
        if (empty($action_id) || empty($viewer)) {
            $this->respondWithError('unauthorized');
        }
        
        $pin_post_duration= Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.pin.reset.days', 7);
        if($pin_post_duration <$time){
            $this->respondWithValidationError('validation_fail', array("time" => 'Time should not be greater than '.$pin_post_duration.' Day(s).'));
        }
        
        $pinTable = Engine_APi::_()->getDbTable('pinsettings', 'advancedactivity');
        $alreadyPin = $pinTable->select()
                ->where('action_id = ? ', $action_id)
                ->query()
                ->fetchColumn();


        if (!empty($alreadyPin)) {
            $pinTable->delete(array('pinsetting_id = ?' => $alreadyPin));
            $this->successResponseNoContent('no_content', true);
        }
        $date = date('Y-m-d H:i:s');
        if (empty($time)) {
            $this->respondWithValidationError('validation_fail', array("time" => 'Time should not be empty'));
        }
        $hours = (int) ($time * 24);
        $new_date = date("Y-m-d H:i:s", strtotime($date . " +$hours hours"));
        $row = $pinTable->createRow();
        $row->setFromArray(array('action_id' => $action_id, 'object_type' => $type, 'reset_date' => $new_date));
        $row->save();
        $this->successResponseNoContent('no_content', true);
    }

    public function getSpecialAlbum(User_Model_User $user, $type) {
        if (!in_array($type, array('comment'))) {
            throw new Album_Model_Exception('Unknown special album type');
        }

        $table = Engine_Api::_()->getDbtable('albums', 'album');
        $select = $table->select()
                ->where('owner_type = ?', $user->getType())
                ->where('owner_id = ?', $user->getIdentity())
                ->where('type = ?', $type)
                ->order('album_id ASC')
                ->limit(1);

        $album = $table->fetchRow($select);

        // Create wall photos album if it doesn't exist yet
        if (null === $album) {
            $translate = Zend_Registry::get('Zend_Translate');
            $album = $table->createRow();
            $album->owner_type = 'user';
            $album->owner_id = $user->getIdentity();
            $album->title = $translate->_(ucfirst(str_replace("_", " ", $type)) . ' Photos');
            $album->type = $type;
            $album->search = 1;
            $album->save();

            // Authorizations
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            foreach ($roles as $i => $role) {
                $auth->setAllowed($album, $role, 'view', true);
                $auth->setAllowed($album, $role, 'comment', true);
            }
        }

        return $album;
    }

    /**
     * Set the uploaded photo from activity post.
     *
     * @return object
     */
    private function _setPhoto($photo, $subject) {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
        } else {
            throw new Group_Model_Exception('invalid argument passed to setPhoto');
        }
        $fileName = $photo['name'];
        $name = basename($file);
        $extension = ltrim(strrchr($fileName, '.'), '.');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
        $params = array(
            'parent_type' => $subject->getType(),
            'parent_id' => $subject->getIdentity(),
            'user_id' => $subject->owner_id,
            'name' => $fileName,
        );
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
// Resize image (main)
        $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(720, 720)
                ->write($mainPath)
                ->destroy();
// Resize image (normal)
        $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(140, 160)
                ->write($normalPath)
                ->destroy();
// Store
        try {
            $iMain = $filesTable->createFile($mainPath, $params);
            $iIconNormal = $filesTable->createFile($normalPath, $params);
            $iMain->bridge($iIconNormal, 'thumb.normal');
        } catch (Exception $e) {
            $this->respondWithValidationError('internal_server_error', $e->getMessage());
        }
// Remove temp files
        @unlink($mainPath);
        @unlink($normalPath);
// Update row
        $subject->modified_date = date('Y-m-d H:i:s');
        $subject->file_id = $iMain->file_id;
        $subject->save();
        return $subject;
    }

}
