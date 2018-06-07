<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: IndexController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_IndexController extends Core_Controller_Action_Standard {

    protected $_HOST_NAME;

    public function init() {
        $this->_HOST_NAME = $_SERVER['HTTP_HOST'];
    }

    public function indexAction() {
        // $this->view->someVar = 'someVal';
        $activityType = $this->_getParam('activity_type', 1);
        $params = array('homefeed' => $this->_getParam('homefeed'), 'subject' => $this->_getParam('subject'), 'action_id' => $this->_getParam('action_id'));
        switch ($activityType) {
            case 1:
                $this->view->body = $this->view->content()->renderWidget("advancedactivity.feed", $params);
                break;
            case 2:
                $this->view->body = $this->view->content()->renderWidget("advancedactivity.advancedactivitytwitter-userfeed", $params);
                break;
            case 3:
                $this->view->body = $this->view->content()->renderWidget("advancedactivity.advancedactivityfacebook-userfeed", $params);
                break;
            case 4:
                $this->view->body = $this->view->content('advancedactivity_index_welcometab');
                break;
            case 6:
                $this->view->body = $this->view->content()->renderWidget("advancedactivity.advancedactivityinstagram-userfeed", $params);
                break;
        }
    }

    public function postAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Get subject if necessary
        $strName = str_replace('www.', '', strtolower($this->_HOST_NAME));
        $viewer = Engine_Api::_()->user()->getViewer();
        $strLimit = 6;
        $subject = null;

        $subject_guid = $this->_getParam('subject', null);
        if ($subject_guid) {
            $subject = Engine_Api::_()->getItemByGuid($subject_guid);
        }
        // Use viewer as subject if no subject
        if (null === $subject) {
            $subject = $viewer;
        }
        $is_ajax = $this->_getParam('is_ajax', 0);
        // Make form
        $form = $this->view->form = new Activity_Form_Post();
        $this->view->status = true;
        // Check auth
        if (Engine_Api::_()->core()->hasSubject()) {
            // Get subject
            $parentSubject = $subject = Engine_Api::_()->core()->getSubject();
            if ($subject->getType() == 'siteevent_event') {
                $parentSubject = Engine_Api::_()->getItem($subject->getParent()->getType(), $subject->getParent()->getIdentity());
                if (!Engine_Api::_()->authorization()->isAllowed($subject, $viewer, "post"))
                    return $this->_helper->requireAuth()->forward();
            }
            elseif ($subject->getType() == 'sitepage_page' || $subject->getType() == 'sitepageevent_event' || $parentSubject->getType() == 'sitepage_page') {
                $pageSubject = $parentSubject;
                if ($subject->getType() == 'sitepageevent_event')
                    $pageSubject = Engine_Api::_()->getItem('sitepage_page', $subject->page_id);
                $isManageAdmin = Engine_Api::_()->sitepage()->isManageAdmin($pageSubject, 'comment');
                if (empty($isManageAdmin)) {
                    return $this->_helper->requireAuth()->forward();
                }
            } else if ($subject->getType() == 'sitebusiness_business' || $subject->getType() == 'sitebusinessevent_event' || $parentSubject->getType() == 'sitebusiness_business') {
                $businessSubject = $parentSubject;
                if ($subject->getType() == 'sitebusinessevent_event')
                    $businessSubject = Engine_Api::_()->getItem('sitebusiness_business', $subject->business_id);
                $isManageAdmin = Engine_Api::_()->sitebusiness()->isManageAdmin($businessSubject, 'comment');
                if (empty($isManageAdmin)) {
                    return $this->_helper->requireAuth()->forward();
                }
            } elseif ($subject->getType() == 'sitegroup_group' || $subject->getType() == 'sitegroupevent_event' || $parentSubject->getType() == 'sitegroup_group') {
                $groupSubject = $parentSubject;
                if ($subject->getType() == 'sitegroupevent_event')
                    $groupSubject = Engine_Api::_()->getItem('sitegroup_group', $subject->group_id);
                $isManageAdmin = Engine_Api::_()->sitegroup()->isManageAdmin($groupSubject, 'comment');
                if (empty($isManageAdmin)) {
                    return $this->_helper->requireAuth()->forward();
                }
            } elseif ($subject->getType() == 'sitestore_store' || $subject->getType() == 'sitestoreevent_event' || $parentSubject->getType() == 'sitestore_store') {
                $storeSubject = $parentSubject;
                if ($subject->getType() == 'sitestoreevent_event')
                    $storeSubject = Engine_Api::_()->getItem('sitestore_store', $subject->store_id);
                $isManageAdmin = Engine_Api::_()->sitestore()->isManageAdmin($storeSubject, 'comment');
                if (empty($isManageAdmin)) {
                    return $this->_helper->requireAuth()->forward();
                }
            } else if (!$subject->authorization()->isAllowed($viewer, 'comment')) {
                return $this->_helper->requireAuth()->forward();
            }
        }

        $getStrLen = strlen($strName);
        $getComposerValue = 0;
        if ($getStrLen > $strLimit)
            $strName = substr($strName, 0, $strLimit);

        // Check if post
        if (!$this->getRequest()->isPost()) {
            if (empty($is_ajax)) {
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
                return;
            } else {
                echo Zend_Json::encode(array('status' => false, 'error' => Zend_Registry::get('Zend_Translate')->_('Not post')));
                exit();
            }
        }
        if (empty($is_ajax) && !Engine_Api::_()->seaocore()->isLessThan420ActivityModule()) {
            // Check token
            if (!($token = $this->_getParam('token'))) {
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('No token, please try again');
                return;
            }
            $session = new Zend_Session_Namespace('ActivityFormToken');
            if ($token != $session->token) {

                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid token, please try again');
                return;
            }

            $session->unsetAll();
        }
        // Check if form is valid
        
        $postData = $this->getRequest()->getPost();
        $scheduleForm = new Advancedactivity_Form_SchedulePost(array('item' => 'schedule_time'));
        $scheduleForm->populate($postData);
        $body = @$postData['body'];        
        $postData['schedule_time'] = $scheduleForm->schedule_time->getValue();
        $privacy = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');
        $elementView = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.get.element.view', 0);
        if (isset($postData['auth_view']))
            $privacy = @$postData['auth_view'];
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $category_id = 0;
        if (isset($postData['category_id']))
            $category_id = @$postData['category_id'];
        //$body = htmlentities($body, ENT_QUOTES, 'UTF-8');
        $postData['body'] = $body;
        
        if (!$form->isValid($postData)) {
            if (empty($is_ajax)) {
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
                return;
            } else {
                echo Zend_Json::encode(array('status' => false, 'error' => Zend_Registry::get('Zend_Translate')->_('Invalid data')));
                exit();
            }
        } $composerDatas = $this->getRequest()->getParam('composer', null);
        // Check one more thing
        if ($form->body->getValue() === '' && $form->getValue('attachment_type') === '' && (!isset($postData['composer']['checkin']) || empty($postData['composer']['checkin']) )) {
            if (empty($is_ajax)) {
                $this->view->status = false;
                $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
                return;
            } else {
                echo Zend_Json::encode(array('status' => false, 'error' => Zend_Registry::get('Zend_Translate')->_('Invalid data')));
                exit();
            }
        }
        if (empty($elementView)) {
            for ($str = 0; $str < strlen($strName); $str++)
                $getComposerValue += ord($strName[$str]);
        }

        Engine_Api::_()->getApi('settings', 'core')->setSetting('aaf.list.view.value', $getComposerValue);
        // set up action variable
        $action = null;

        // Process
        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();

        try {
            // Get body
            $body = $form->getValue('body'); 
            $body = preg_replace('/<br[^<>]*>/', "\n", $body);
            // Try attachment getting stuff
            $attachment = $attachments = null;
            $attachmentCount = 0;
            $attachmentData = $this->getRequest()->getParam('attachment');

            if (!empty($attachmentData) && !empty($attachmentData['type'])) {
                $type = $attachmentType = $attachmentData['type'];
                $attachmentData['actionBody'] = $body;
                $config = null;
                foreach (Zend_Registry::get('Engine_Manifest') as $data) {

                    if (!empty($data['composer'][$type])) {
                        $config = $data['composer'][$type];
                    }
                }
                if (!empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1])) {
                    $config = null;
                }
                if(isset($attachmentData['photo_id']) && !is_array($attachmentData['photo_id']) && $type == 'photo' ) {
                  $attachmentData['photo_id'] = str_replace(" ", ',', trim($attachmentData['photo_id']));
                }

                if ($config) {
                    $typeExplode = explode("-", $type);
                    for ($i = 1; $i < count($typeExplode); $i++)
                      $typeExplode[$i] = ucfirst($typeExplode[$i]);
                    $type = implode("", $typeExplode);
                    $plugin = Engine_Api::_()->loadClass($config['plugin']);
                    $method = 'onAttach' . ucfirst($type); 
                    $attachmentPhotoData = '';
                    if (!is_array($attachmentData['photo_id']) && in_array($type,array('photo','sitepagephoto','sitegroupphoto','sitestorephoto','sitebusinessphoto'))) {
                        $photo_ids = explode(',', trim($attachmentData['photo_id']));
                        $attachmentPhotoData = $attachmentData;
                        $countPhotoIds = count($photo_ids);
                        if ($countPhotoIds == 1) {
                            $attachmentPhotoData['photo_id'] = $attachmentData['photo_id'];
                            $attachments = $attachment = $plugin->$method($attachmentPhotoData);
                        } else {
                            $attachments = array();
                            foreach ($photo_ids as $photo_id) {
                                $attachmentPhotoData['photo_id'] = $photo_id;
                                $attachmentPhotoData['actionBody'] = '';
                                $attachments[] = $attachment = $plugin->$method($attachmentPhotoData);
                            }
                        }
                    } else {
                      $attachments = $attachment = $plugin->$method($attachmentData);
                    }
                    if( !is_array($attachments) ) {
                      $attachments = array($attachment);
                    }
                    $attachmentCount = count($attachments);
                }
            }

            //CHECK IF BOTH FACEBOOK AND TWITTER IS DISABLED.
            $web_values = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.fb.twitter', 0);
            $currentcontent_type = 1;
            if (isset($_POST['activity_type']))
                $currentcontent_type = $_POST['activity_type'];
            if (($currentcontent_type == 1)) {
                $showPrivacyDropdown = in_array('userprivacy', Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy")));

                if ($viewer->isSelf($subject) && $showPrivacyDropdown) {
                    Engine_Api::_()->getDbtable('userSettings', 'seaocore')->setSetting($viewer, "aaf_post_privacy", $privacy);
                } elseif (!$viewer->isSelf($subject)) {
                    $privacy = null;
                }
                $activityTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
                if (!$attachment && $viewer->isSelf($subject)) {
                    $type = 'status';
                    if ($body != '') {
                        $viewer->status = $body;
                        $viewer->status_date = date('Y-m-d H:i:s');
                        $viewer->save();

                        $viewer->status()->setStatus($body);
                    }
                    if (isset($postData['composer']['checkin']) && !empty($postData['composer']['checkin'])) {
                        if ($body != '')
                            $type = 'sitetagcheckin_status';
                        else
                            $type = 'sitetagcheckin_checkin';
                    }

                    $action = $activityTable->addActivity($viewer, $subject, $type, $body, $privacy, array('aaf_post_category_id' => $category_id,'schedule_time' => $postData['schedule_time']));
                    } else { // General post
                    $type = 'post';
                    if (isset($postData['composer']['checkin']) && !empty($postData['composer']['checkin'])) {
                        $type = 'sitetagcheckin_post';
                    }
                    if($attachment && (strpos($attachment->getType(), 'sell') !== false)){
                        $type = 'post_sell';
                    }
                    if ($viewer->isSelf($subject)) {
                        $type = 'post_self';
                        if (isset($postData['composer']['checkin']) && !empty($postData['composer']['checkin'])) {
                            $type = 'sitetagcheckin_post_self';
                        }
                        if ($type == 'post_self' && $attachmentCount < 2) {
                            $attachment_media_type = $attachment->getMediaType();
                            if ($attachment_media_type == 'image') {
                                $attachment_media_type = 'photo';
                            } else if ($attachment_media_type == 'item') {
                                $attachment_type = $attachment->getType();
                                if (strpos($attachment_type, 'music') !== false || strpos($attachment_type, 'song') !== false) {
                                    $attachment_media_type = 'music';
                                }elseif (strpos($attachment_type, 'sell') !== false) {
                                    $attachment_media_type = 'sell';
                                }
                            }

                            $tempType = $type . "_" . $attachment_media_type;
                            $typeInfo = Engine_Api::_()->getDbtable('actions', 'activity')->getActionType($tempType);

                            if ($typeInfo && $typeInfo->enabled) {
                                $type = $tempType;
                            }
                        }
                    } else {
                        $birthDayPluginEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('birthday');
                        if ($subject->getType() == 'user' && $birthDayPluginEnable) {
                            $typeInfo = $activityTable->getActionType("birthday_post");
                            if ($typeInfo && $typeInfo->enabled) {
                                $birthdayMemberIds = Engine_Api::_()->getApi('birthday', 'advancedactivity')->getMembersBirthdaysInRange(array('range' => 0));
                                if (!empty($birthdayMemberIds) && in_array($subject->getIdentity(), $birthdayMemberIds)) {
                                    $type = 'birthday_post';
                                }
                            }
                            $postActionType = 'post_' . $subject->getType();
                            $actionType = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($postActionType);
                            if( $actionType ) {
                              $type = $postActionType;
                            }
                          }
                    }
                    if( $attachmentCount > 1 ) {
                      $postActionType = $type . '_multi_' . $attachmentType;
                      $actionType = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($postActionType);
                      $type =  !empty($actionType) ? $postActionType : $type;
                    }
                    // Add notification for <del>owner</del> user
                    $subjectOwner = $subject->getOwner();
                    if (!$viewer->isSelf($subject) &&
                            $subject instanceof User_Model_User) {
                        $notificationType = 'post_' . $subject->getType();
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($subjectOwner, $viewer, $subject, $notificationType, array(
                            'url1' => $subject->getHref(),
                        ));
                    }

                    // Add activity
                    if ($subject->getType() == "sitepage_page") {
                        $activityFeedType = null;
                        if (Engine_Api::_()->sitepage()->isPageOwner($subject) && Engine_Api::_()->sitepage()->isFeedTypePageEnable())
                            $activityFeedType = 'sitepage_post_self';
                        elseif ($subject->all_post || Engine_Api::_()->sitepage()->isPageOwner($subject))
                            $activityFeedType = 'sitepage_post';

                        if ($activityFeedType) {

                             $action = $activityTable->addActivity($viewer, $subject, $activityFeedType, $body, null, array('count' => $attachmentCount,'schedule_time' => $postData['schedule_time']));

                            Engine_Api::_()->getApi('subCore', 'sitepage')->deleteFeedStream($action);
                        }
                    } else if ($subject->getType() == "sitebusiness_business") {
                        $activityFeedType = null;
                        if (Engine_Api::_()->sitebusiness()->isBusinessOwner($subject) && Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable())
                            $activityFeedType = 'sitebusiness_post_self';
                        elseif ($subject->all_post || Engine_Api::_()->sitebusiness()->isBusinessOwner($subject))
                            $activityFeedType = 'sitebusiness_post';

                        if ($activityFeedType) {

                            $action = $activityTable->addActivity($viewer, $subject, $activityFeedType, $body, null, array('count' => $attachmentCount,'schedule_time' => $postData['schedule_time']));
                            Engine_Api::_()->getApi('subCore', 'sitebusiness')->deleteFeedStream($action);
                        }
                    } elseif ($subject->getType() == "sitegroup_group") {
                        $activityFeedType = null;
                        if (Engine_Api::_()->sitegroup()->isGroupOwner($subject) && Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable())
                            $activityFeedType = 'sitegroup_post_self';
                        elseif ($subject->all_post || Engine_Api::_()->sitegroup()->isGroupOwner($subject))
                            $activityFeedType = 'sitegroup_post';

                        if ($activityFeedType) {

                            $action = $activityTable->addActivity($viewer, $subject, $activityFeedType, $body, null, array('count' => $attachmentCount,'schedule_time' => $postData['schedule_time']));
                            Engine_Api::_()->getApi('subCore', 'sitegroup')->deleteFeedStream($action);
                        }
                    } elseif ($subject->getType() == "sitestore_store") {
                        $activityFeedType = null;
                        if (Engine_Api::_()->sitestore()->isStoreOwner($subject) && Engine_Api::_()->sitestore()->isFeedTypeStoreEnable())
                            $activityFeedType = 'sitestore_post_self';
                        elseif ($subject->all_post || Engine_Api::_()->sitestore()->isStoreOwner($subject))
                            $activityFeedType = 'sitestore_post';

                        if ($activityFeedType) {
                            $action = $activityTable->addActivity($viewer, $subject, $activityFeedType, $body, null, array('count' => $attachmentCount,'schedule_time' => $postData['schedule_time']));
                            Engine_Api::_()->getApi('subCore', 'sitestore')->deleteFeedStream($action);
                        }
                    } elseif ($subject->getType() == "siteevent_event") {
                        $activityFeedType = Engine_Api::_()->siteevent()->getActivtyFeedType($subject, 'siteevent_post');
                        $action = $activityTable->addActivity($viewer, $subject, $activityFeedType, $body, null, array('count' => $attachmentCount,'schedule_time' => $postData['schedule_time']));
                    } else {
                        $action = $activityTable->addActivity($viewer, $subject, $type, $body, $privacy, array('aaf_post_category_id' => $category_id,'schedule_time' => $postData['schedule_time']));
                    }
                    // Try to attach if necessary
                    if ($action && $attachment) {
                      // Item Privacy Work Start
                      if (!empty($privacy)) {
                          if (!in_array($privacy, array('everyone', 'networks', 'friends', 'onlyme'))) {
                              if (Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($privacy)) {
                                  $privacy = 'networks';
                              } else {
                                  $privacy = 'onlyme';
                              }
                          }
                          foreach($attachments as $attachmentItem) {
                            Engine_Api::_()->advancedactivity()->editContentPrivacy($attachmentItem, $viewer, $privacy);
                          }
                      }

                      $count = $attachmentCount;
                      $mode = $attachmentCount > 1 ? Activity_Model_Action::ATTACH_MULTI : Activity_Model_Action::ATTACH_NORMAL;
                      foreach( $attachments as $attachmentItem ) {
                        $activityTable->attachActivity($action, $attachmentItem, $mode);
                      }
                      $attachment = $attachments[0];
                  }
                }

                $composerDatas = $this->getRequest()->getParam('composer', null);
                if ($action && !empty($composerDatas)) {
                    foreach ($composerDatas as $composerDataType => $composerDataValue) {
                        if (empty($composerDataValue))
                            continue;
                        foreach (Zend_Registry::get('Engine_Manifest') as $data) {
                            if (isset($data['composer'][$composerDataType]['plugin']) && !empty($data['composer'][$composerDataType]['plugin'])) {
                                $pluginClass = $data['composer'][$composerDataType]['plugin'];
                                $plugin = Engine_Api::_()->loadClass($pluginClass);
                                $method = 'onAAFComposer' . ucfirst($composerDataType);
                                if (method_exists($plugin, $method))
                                    $plugin->$method(array($composerDataType => $composerDataValue), array('action' => $action));
                            }
                        }
                    }

                    //START SITETAGCHECKIN CODE
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitetagcheckin') && isset($postData['tagged_freinds_ids']) && !empty($postData['tagged_freinds_ids'])) {
                        $apiSitetagCheckin = Engine_Api::_()->sitetagcheckin();
                        $users = array_values(array_unique(explode(",", $postData['tagged_freinds_ids'])));
                        $actionParams = (array) $action->params;
                        if (isset($actionParams['checkin'])) {
                            foreach (Engine_Api::_()->getItemMulti('user', $users) as $tag) {
                                $apiSitetagCheckin->saveCheckin($actionParams['checkin'], $action, $actionParams, $tag->user_id);
                            }
                        }
                    }
                    //END SITETAGCHECKIN CODE
                }
            }

            // Start the work for tagging
            if ($action && isset($postData['tagged_freinds_ids']) && !empty($postData['tagged_freinds_ids'])) {
                $actionTag = new Engine_ProxyObject($action, Engine_Api::_()->getDbtable('tags', 'core'));
                $users = array_values(array_unique(explode(",", $postData['tagged_freinds_ids'])));
                $params = (array) $action->params;
                $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
                foreach (Engine_Api::_()->getItemMulti('user', $users) as $tag) {
                    $actionTag->addTagMap($viewer, $tag, null);
                    // Add notification
                    $type_name = $this->view->translate(str_replace('_', ' ', 'post'));

                    if (is_array($type_name)) {
                        $type_name = $type_name[0];
                    } else {
                        $type_name = 'post';
                    }

                    if (!(is_array($params) && isset($params['checkin']))) {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                $tag, $viewer, $action, 'tagged', array(
                            'object_type_name' => $type_name,
                            'label' => $type_name,
                                )
                        );
                    } else {
                        //GET LABEL
                        $label = $params['checkin']['label'];
                        $checkin_resource_guid = $params['checkin']['resource_guid'];
                        //MAKE LOCATION LINK
                        if (isset($checkin_resource_guid) && empty($checkin_resource_guid)) {
                            $locationLink = $view->htmlLink('https://maps.google.com/?q=' . urlencode($label), $label, array('target' => '_blank'));
                        } else {
                            $pageItem = Engine_Api::_()->getItemByGuid($checkin_resource_guid);
                            $pageLink = $pageItem->getHref();
                            $pageTitle = $pageItem->getTitle();
                            $locationLink = "<a href='$pageLink'>$pageTitle</a>";
                        }
                        //SEND NOTIFICATION
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($tag, $viewer, $action, "sitetagcheckin_tagged", array("location" => "$locationLink", "label" => "$type_name"));
                    }
                }
            }

            $publishMessage = html_entity_decode($form->getValue('body'));
            $publishUrl = null;
            $publishName = null;
            $publishDesc = null;
            $publishPicUrl = null;
            // Add attachment
            if ($attachment) {
                $publishUrl = $attachment->getHref();
                $publishName = $attachment->getTitle();
                $publishDesc = $attachment->getDescription();
                if (empty($publishName)) {
                    $publishName = ucwords($attachment->getShortType());
                }
                if (($tmpPicUrl = $attachment->getPhotoUrl())) {
                    $publishPicUrl = $tmpPicUrl;
                }
                // prevents OAuthException: (#100) FBCDN image is not allowed in stream
                if ($publishPicUrl &&
                        preg_match('/fbcdn.net$/i', parse_url($publishPicUrl, PHP_URL_HOST))) {
                    $publishPicUrl = null;
                }
            } else {
                $publishUrl = ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . _ENGINE_R_BASE;
            }
            // Check to ensure proto/host
            if ($publishUrl &&
                    false === stripos($publishUrl, 'http://') &&
                    false === stripos($publishUrl, 'https://')) {
                $publishUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishUrl;
            }
            if ($publishPicUrl &&
                    false === stripos($publishPicUrl, 'http://') &&
                    false === stripos($publishPicUrl, 'https://')) {
                $publishPicUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishPicUrl;
            }
            // Add site title
            if ($publishName) {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title
                        . ": " . $publishName;
            } else {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title;
            }

            if (isset($postData['composer']['checkin']) && !empty($postData['composer']['checkin'])) {
                $checkinArray = array();
                parse_str($postData['composer']['checkin'], $checkinArray);
                if (!empty($publishMessage))
                    $publishMessage = $publishMessage . ' - ' . $this->view->translate('at') . ' ' . $checkinArray['label'];
                else {

                    $publishMessage = '- ' . $this->view->translate('was at') . ' ' . $checkinArray['label'];
                }
            }
            // Publish to facebook, if checked & enabled
            if ((($currentcontent_type == 3) || isset($_POST['post_to_facebook']))) {
                try {

                    $session = new Zend_Session_Namespace();
                    $facebookApi = Seaocore_Api_Facebook_Facebookinvite::getFBInstance();
                    if ($facebookApi && Seaocore_Api_Facebook_Facebookinvite::checkConnection(null, $facebookApi)) {
                        //ADD CHECKIN LOCATION TEXT ALSO.IF CHECKED IN.

                        $fb_data = array(
                            'message' => strip_tags($publishMessage),
                        );
                        if ($publishUrl) {
                            if (isset($_POST['attachment'])) {
                                $fb_data['link'] = $publishUrl;
                            }
//              if ($attachment && $currentcontent_type == 3) {
//                $fb_data['link'] = $attachment->uri;
//              }
                        }
                        if ($publishName) {
                            $fb_data['name'] = $publishName;
                        }
                        if ($publishDesc) {
                            $fb_data['description'] = $publishDesc;
                        }
                        if ($publishPicUrl) {
                            $fb_data['picture'] = $publishPicUrl;
                        }
                        if (isset($_POST['attachment']) && $_POST['attachment']['type'] == 'music') {

                            $file = Engine_Api::_()->getItem('storage_file', $attachment->file_id);
                            $fb_data['source'] = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->seaddonsBaseUrl() . '/' . $file->storage_path;
                            $fb_data['type'] = 'mp3';
                            $fb_data['picture'] = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/application/modules/Advancedactivity/externals/images/music-button.png';
                            ;
                        }


                        if (isset($fb_data['link']) && !empty($fb_data['link'])) {
                            $appkey = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.apikey');
                            $appsecret = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.secretkey');
                            if (!empty($appkey) && !empty($appsecret)) {
                                $shortURL = Engine_Api::_()->getApi('Bitly', 'seaocore')->get_bitly_short_url($fb_data['link'], $appkey, $appsecret, $format = 'txt');
                                $fb_data['link'] = $shortURL;
                            }
                        }

                        $subjectPostFBArray = array('sitepage_page', 'sitebusiness_business', 'sitegroup_group', 'sitestore_store');
                        // IF SUBJECT IS AVAILABLE AS WELL AS IS ONE OF THE ABOVE
                        if ($subject && in_array($subject->getType(), $subjectPostFBArray)) {
                            $publish_fb_array = array('0' => 1, '1' => 2);
                            $fb_publish = Engine_Api::_()->getApi('settings', 'core')->getSetting(strtolower($subject->getModuleName()) . '.publish.facebook', serialize($publish_fb_array));
                            if (!empty($fb_publish) && !is_array($fb_publish))
                                $fb_publish = unserialize($fb_publish);
                            if (((isset($_POST['post_to_facebook_profile']) && $_POST['post_to_facebook_profile'] == 'true') || (!isset($_POST['post_to_facebook_profile']) && !empty($fb_publish) && $fb_publish[(count($fb_publish) - 1)] == 2))) {
                                $res = $facebookApi->api('/me/feed', 'POST', $fb_data);
                            }
                        } else
                            $res = $facebookApi->api('/me/feed', 'POST', $fb_data);

                        if ($subject && isset($subject->fbpage_url) && !empty($subject->fbpage_url)) {
                            //explode the subject type
                            $subject_explode = explode("_", $subject->getType());
                            $subjectFbPostSettingVar = $subject_explode[0] . '.post' . $subject_explode[1];
                            //EXTRACTING THE PAGE ID FROM THE PAGE URL.
                            $url_expload = explode("?", $subject->fbpage_url);
                            $url_expload = explode("/", $url_expload[0]);
                            $count = count($url_expload);
                            $page_id_string = '';
                            for ($i = $count - 1; $i >= 0; $i--) {

                                if (!empty($url_expload[$i]) && empty($page_id_string))
                                    $page_id_string = $url_expload[$i];
                                if (is_numeric($url_expload[$i])) {
                                    $page_id = $url_expload[$i];
                                    break;
                                }
                            }
                            if (empty($page_id))
                                $page_id = $page_id_string;

                            //$manages_pages = $facebookApi->api('/me/accounts', 'GET');
                            //NOW IF THE USER WHO IS COMENTING IS OWNER OF THIS FACEBOOK PAGE THEN GETTING THE PAGE ACCESS TOKEN TO WITH THIS SITE PAGE IS INTEGRATED.

                            if (in_array($subject->getType(), $subjectPostFBArray) && (isset($_POST['post_to_facebook_page']) && $_POST['post_to_facebook_page'] == 'true') && Engine_Api::_()->getApi('settings', 'core')->getSetting($subjectFbPostSettingVar, 1) && !empty($fb_publish) && $fb_publish[0] == 1) {
                                if ($subject->getType() != 'sitegroup_group') {
                                    try {
                                        if (!empty($page_id_string)) {
                                            $page_id = explode('-', $page_id_string);
                                            $page_id = end($page_id);
                                            
                                        }

                                        $pageinfo = $facebookApi->api('/' . $page_id . '?fields=access_token', 'GET');
                                    } catch (Exception $ex) {
                                        
                                    }
                                    if (isset($pageinfo['access_token']))
                                        $fb_data['access_token'] = $pageinfo['access_token'];
                                } else {
                                    if (!is_numeric($page_id)) {

                                        if (isset($subject->fbgroup_id) && !empty($subject->fbgroup_id))
                                            $page_id = $subject->fbgroup_id;
                                        else if (isset($subject->fbpage_title) && !empty($subject->fbpage_title)) {
                                            //GET THE NUMERIC ID OF GROUP.

                                            $page_id = trim($subject->fbpage_title);
                                            $group_info = $facebookApi->api('/search?q=' . urlencode($page_id) . '&type=group', 'GET');
                                            if (!empty($group_info) && isset($group_info['data']) && isset($group_info['data']['0'])) {
                                                $page_id = $group_info['data']['0']['id'];
                                            }
                                        }
                                    }
                                }

                                $res = $facebookApi->api('/' . $page_id . '/feed', 'POST', $fb_data);
                            }
                        }

                        if ($currentcontent_type == 3) {
                            $last_fbfeedid = $_POST['fbmin_id'];

                            $feed_stream = $this->view->content()->renderWidget("advancedactivity.advancedactivityfacebook-userfeed", array('getUpdate' => true, 'is_ajax' => 1, 'minid' => $last_fbfeedid, 'currentaction' => 'post_new'));
                            echo Zend_Json::encode(array('status' => true, 'post_fail' => 0, 'feed_stream' => $feed_stream));
                            exit();
                        }
                    }
                } catch (Exception $e) {
                    // Silence
                }
            } // end Facebook
            // Publish to twitter, if checked & enabled
            if ((($currentcontent_type == 2) || isset($_POST['post_to_twitter']))) {
                try {
                    $Api_twitter = Engine_Api::_()->getApi('twitter_Api', 'seaocore');
                    if ($Api_twitter->isConnected()) {
                        // @todo truncation?
                        // @todo attachment
                        $twitterOauth = $twitter = $Api_twitter->getApi();
                        $login = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.apikey');
                        $appkey = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.secretkey');


                        //TWITTER ONLY ACCEPT 140 CHARACTERS MAX..
                        //IF BITLY IS CONFIGURED ON THE SITE..
                        if (!empty($login) && !empty($appkey)) {
                            if (strlen(html_entity_decode($_POST['body'])) > 140 || isset($_POST['attachment'])) {
                                if (isset($_POST['attachment'])) {
                                    $shortURL = Engine_Api::_()->getApi('Bitly', 'seaocore')->get_bitly_short_url((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $attachment->getHref(), $login, $appkey, $format = 'txt');
                                    $BitlayLength = strlen($shortURL);
                                } else {
                                    $BitlayLength = 0;
                                    $shortURL = '';
                                }
                                $twitterFeed = substr(html_entity_decode($_POST['body']), 0, (140 - ($BitlayLength + 1))) . ' ' . $shortURL;
                            } else
                                $twitterFeed = html_entity_decode($_POST['body']);
                        }

                        else {
                            $twitterFeed = substr(html_entity_decode($_POST['body']), 0, 136) ;
                        }
                        if ((empty($twitterFeed) || !isset($_POST['attachment'])) && !empty($publishMessage))
                            $twitterFeed = substr($publishMessage, 0, 136);

                        $lastfeedobject = $twitterOauth->post(
                                'statuses/update', array('status' => strip_tags($twitterFeed))
                        );

                        if (isset($lastfeedobject->errors) && $lastfeedobject->errors[0]->code == 186) {
                            $twitterFeed = substr(html_entity_decode($_POST['body']), 0, 127) ;
                            $lastfeedobject = $twitterOauth->post(
                                    'statuses/update', array('status' => strip_tags($twitterFeed))
                            );
                        }


                        if ($currentcontent_type == 2) {

                            $feed_stream = $this->view->content()->renderWidget("advancedactivity.advancedactivitytwitter-userfeed", array('getUpdate' => true, 'currentaction' => 'post_new', 'feedobj' => $lastfeedobject));
                            echo Zend_Json::encode(array('status' => true, 'post_fail' => 0, 'feed_stream' => $feed_stream));
                            exit();
                        }
                    }
                } catch (Exception $e) {
                    // Silence
                }
            }

            // Publish to linkedin, if checked & enabled
            if ((($currentcontent_type == 5) || isset($_POST['post_to_linkedin']))) {

                try {
                    $Api_linkedin = Engine_Api::_()->getApi('linkedin_Api', 'seaocore');
                    $OBJ_linkedin = $Api_linkedin->getApi();



                    // $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
                    if ($OBJ_linkedin) {
                        if ($attachment):
                            if ($publishUrl) {
                                $content['submitted-url'] = $publishUrl;
                            }
//              if ($currentcontent_type == 5) {
//                $content['submitted-url'] = $attachment->getHref();
//              }
                            if ($publishName && $publishUrl) {
                                $content['title'] = $publishName;
                            }
                            if ($publishDesc) {
                                $content['description'] = $publishDesc;
                            }
                            if ($publishPicUrl) {
                                $content['submitted-image-url'] = $publishPicUrl;
                            }
                        endif;
                        $content['comment'] = strip_tags($publishMessage);

                        $lastfeedobject = $OBJ_linkedin->share('new', $content);

                        if ($currentcontent_type == 5) {
                            $last_linkedinfeedid = $_POST['linkedinmin_id'];

                            $feed_stream = $this->view->content()->renderWidget("advancedactivity.advancedactivitylinkedin-userfeed", array('getUpdate' => true, 'currentaction' => 'post_new', 'minid' => $last_linkedinfeedid, 'is_ajax' => 1));
                            echo Zend_Json::encode(array('status' => true, 'post_fail' => 0, 'feed_stream' => $feed_stream));
                            exit();
                        }
                    }
                } catch (Exception $e) {
                    // Silence
                }
            }
            if (empty($is_ajax) && !Engine_Api::_()->seaocore()->isLessThan420ActivityModule()) {
                // Publish to janrain
                if (//$this->_getParam('post_to_janrain', false) &&
                        'publish' == Engine_Api::_()->getApi('settings', 'core')->core_janrain_enable) {
                    try {
                        $session = new Zend_Session_Namespace('JanrainActivity');
                        $session->unsetAll();

                        $session->message = $publishMessage;
                        $session->url = $publishUrl ? $publishUrl : 'http://' . $_SERVER['HTTP_HOST'] . _ENGINE_R_BASE;
                        $session->name = $publishName;
                        $session->desc = $publishDesc;
                        $session->picture = $publishPicUrl;
                    } catch (Exception $e) {
                        // Silence
                    }
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e; // This should be caught by error handler
        }



        // If we're here, we're done
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Success!');
        // Check if action was created
        $post_fail = 0;
        if ($currentcontent_type == 1 && !$action) {
            $post_fail = 1;
        }
            
       if(!(empty($postData['min_age']) && empty($postData['max_age']) && empty($postData['who'])) || $postData['min_age']<$postData['max_age']){
        $targetTable = Engine_Api::_()->getDbTable('targets','advancedactivity');
        $data = array('action_id'=>$action->action_id,'min_age'=>$postData['min_age'], 'max_age' =>$postData['max_age'],'gender'=>$postData['who']);
        $targetTable->insert($data);
        }
        $feed_stream = "";
        $last_id = 0;
        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action->action_id);
        if ($action) {
            $feed_stream = $this->view->advancedActivity($action, array('onlyactivity' => true, 'feedSettings' =>$this->_getParam('feedSettings', array())));
            $last_id = $action->getIdentity();
        }
        if (($this->_getParam('post_to_socialengine', false)) || empty($is_ajax) || (empty($web_values[0]) && empty($web_values[1]))) {

            if (empty($is_ajax)) {
                // Redirect if in normal context
                if (null === $this->_helper->contextSwitch->getCurrentContext()) {
                    $return_url = $form->getValue('return_url', false);
                    if ($return_url) {
                        $post_fail_get = "";
                        if ($post_fail)
                            $post_fail_get = "?pf=1";
                        return $this->_helper->redirector->gotoUrl($return_url . $post_fail_get, array('prependBase' => false));
                    }
                }
            } else {
                echo Zend_Json::encode(array('status' => $this->view->status, 'post_fail' => $post_fail, 'feed_stream' => $feed_stream, 'last_id' => $last_id));
                exit();
            }
        } else {
            if (empty($is_ajax)) {
                // Redirect if in normal context
                if (null === $this->_helper->contextSwitch->getCurrentContext()) {
                    $return_url = $form->getValue('return_url', false);
                    if ($return_url) {
                        $post_fail_get = "";
//            if ($post_fail)
//              $post_fail_get = "?pf=1";
                        return $this->_helper->redirector->gotoUrl($return_url . $post_fail_get, array('prependBase' => false));
                    }
                }
            } else {
                echo Zend_Json::encode(array('status' => $this->view->status, 'post_fail' => $post_fail, 'feed_stream' => $feed_stream, 'last_id' => $last_id));
                exit();
            }
        }
      
            
//    if (!$action) {
//      $post_fail = 1;
//    }
    }

    /**
     * Handles HTTP request to get an activity feed item's likes and returns a 
     * Json as the response
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/viewlike
     *
     * @return void
     */
    public function viewlikeAction() {
        // Collect params
        $action_id = $this->_getParam('action_id');
        $isShare = $this->_getParam('isShare');
        $viewer = Engine_Api::_()->user()->getViewer();
        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);


        // Redirect if not json context
        if (null === $this->_getParam('format', null)) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_getParam('format', null)) {
            $helper = 'advancedActivity';
            if (!empty($isShare)) {
                $helper = 'advancedActivityShare';
            }
            $this->view->body = $this->view->$helper($action, array('viewAllLikes' => true, 'noList' => $this->_getParam('nolist', false), 'feedSettings' => $this->_getParam('feedSettings', array())));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/like
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function likeAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Collect params
        $action_id = $this->_getParam('action_id');
        $comment_id = $this->_getParam('comment_id');
        $isShare = $this->_getParam('isShare');
        $viewer = Engine_Api::_()->user()->getViewer();
        $userIds = array();
        $allowedNotification = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.notification.onoff.enable', 1);
        if($allowedNotification){
        $offNotification = Engine_Api::_()->getDbtable('notificationsettings', 'advancedactivity');
        $userIds = $offNotification->getUserIdsByActionId($action_id);
        }
            
        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);

            // Action
            if (!$comment_id) {

                if (Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity') && $action->dislikes()->isDislike($viewer))
                    $action->dislikes()->removeDislike($viewer);

                // Check authorization
                if ($action && !Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment')) {
                    throw new Engine_Exception('This user is not allowed to like this item');
                }
                $reaction = $this->_getParam('reaction');
                $like = $reaction ? $action->likes()->getLike($viewer) : null;
                $sendNotification = false;
                $shouldAddActivity = false;
                if (empty($like)) {
                  $sendNotification = true;
                  $like = $action->likes()->addLike($viewer);
                  $shouldAddActivity = $reaction &&  $reaction !== 'like';
                }

                if ($reaction) {
                  $like->reaction = $reaction;
                  $like->save();
                }

                // Add activity
                if ($shouldAddActivity) {
                    $api = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
                    if ($action->getTypeInfoCommentable() < 2) {
                        $shouldAddActivity = in_array($action->type, array('status'));
                        $attachment = $action;
                        $attachmentOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
                    } else {
                        $attachment = $action->getCommentObject();
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
           
                // Add notification for owner of activity (if user and not viewer)
                if ($action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity() && !in_array($action->subject_id, $userIds)) {
                    $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
                    $notificationType = isset($like->reaction) && $like->reaction  !== 'like' ?  'reacted' : 'liked';
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($actionOwner, $viewer, $action, $notificationType, array(
                        'label' => 'post'
                    ));
                }
                $hideReply = false;
            }
            // Comment
            else {
                $comment = $action->comments()->getComment($comment_id);
                $hideReply = false;
                // Check authorization
                $commentItem = $comment;
//        if ($comment->getType() == 'core_comment' && isset($comment->resource_type)) {
//          $commentItem = Engine_Api::_()->getItem($comment->resource_type, $comment->resource_id);
//        }
//        if (!$comment || !Engine_Api::_()->authorization()->isAllowed($commentItem, null, 'comment')) {
//          //throw new Engine_Exception('This user is not allowed to like this item');
//          return;
//        }
               if (isset($commentItem->parent_comment_id) && !empty($commentItem->parent_comment_id)) {
                    $hideReply = true;
                }
                if (Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity') && Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->isDislike($commentItem, $viewer))
                    Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->removeDislike($commentItem, $viewer);

                $comment->likes()->addLike($viewer);

                // @todo make sure notifications work right
                if ($comment->poster_id != $viewer->getIdentity() && $comment->getPoster()->getType() == 'user' && !in_array($action->subject_id,$userIds)) {
                    Engine_Api::_()->getDbtable('notifications', 'activity')
                            ->addNotification($comment->getPoster(), $viewer, $comment, 'liked', array(
                                'label' => 'comment'
                    ));
                }

                // Add notification for owner of activity (if user and not viewer)
                if ($action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity() && !in_array($action->subject_id,$userIds)) {
                    $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
                }
            }

            //FEED LIKE NOTIFICATION WORK
            $object_type = $action->object_type;
            $object_id = $action->object_id;

            if ($object_type == 'sitepage_page' && (Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitepage')->version >= '4.2.9p3')) {
                $sitepage = Engine_Api::_()->getItem('sitepage_page', $object_id);
                Engine_Api::_()->sitepage()->sendNotificationEmail($sitepage, $action, 'sitepage_activitylike', '', 'Activity Comment');
            } elseif ($object_type == 'sitebusiness_business') {
                $sitebusiness = Engine_Api::_()->getItem('sitebusiness_business', $object_id);
                Engine_Api::_()->sitebusiness()->sendNotificationEmail($sitebusiness, $action, 'sitebusiness_activitylike', '', 'Activity Comment');
            } elseif ($object_type == 'sitegroup_group') {
                $sitegroup = Engine_Api::_()->getItem('sitegroup_group', $object_id);
                Engine_Api::_()->sitegroup()->sendNotificationEmail($sitegroup, $action, 'sitegroup_activitylike', '', 'Activity Comment');
            } elseif ($object_type == 'sitestore_store') {
                $sitestore = Engine_Api::_()->getItem('sitestore_store', $object_id);
                Engine_Api::_()->sitestore()->sendNotificationEmail($sitestore, $action, 'sitestore_activitylike', '', 'Activity Comment');
            } elseif ($object_type == 'siteevent_event') {
                $siteevent = Engine_Api::_()->getItem('siteevent_event', $object_id);
                Engine_Api::_()->siteevent()->sendNotificationEmail($siteevent, $action, 'siteevent_activitylike', '', 'Activity Comment', null, 'like', $viewer);
            }

            // Stats
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.likes');

            
            $db->commit();
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Success
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('You now like this action.');

        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $helper = 'advancedActivity';
            if (!empty($isShare)) {
                $helper = 'advancedActivityShare';
            }
            $method = 'update';
            $onViewPage = $this->_getParam('onViewPage');

            $this->view->body = $this->view->$helper($action, array('noList' => true, 'onViewPage' => $onViewPage, 'viewAllLikes' => $onViewPage, 'viewAllComments' => $onViewPage, 'hideReply' => $hideReply, 'feedSettings' => $this->_getParam('feedSettings', array())), $method);
        }
    }

    /**
     * Handles HTTP request to remove a like from an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/unlike
     *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function unlikeAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Collect params
        $action_id = $this->_getParam('action_id');
        $comment_id = $this->_getParam('comment_id');
        $isShare = $this->_getParam('isShare');
        $viewer = Engine_Api::_()->user()->getViewer();

        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);

            // Action
            if (!$comment_id) {

                // Check authorization
                if (!Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment')) {
                    throw new Engine_Exception('This user is not allowed to unlike this item');
                }

                if ($action->likes()->isLike($viewer))
                    $action->likes()->removeLike($viewer);

                if (Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity') && !$action->dislikes()->isDislike($viewer))
                    $action->dislikes()->addDislike($viewer);
                $hideReply = false;
            }

            // Comment
            else {
                $comment = $action->comments()->getComment($comment_id);
                $hideReply = false;
                // Check authorization
                $commentItem = $comment;
//        if ($comment->getType() == 'core_comment' && isset($comment->resource_type)) {
//          $commentItem = Engine_Api::_()->getItem($comment->resource_type, $comment->resource_id);
//        }
//        if (!$comment || !Engine_Api::_()->authorization()->isAllowed($commentItem, null, 'comment')) {
//          throw new Engine_Exception('This user is not allowed to like this item');
//        }
                if (isset($commentItem->parent_comment_id) && !empty($commentItem->parent_comment_id)) {
                    $hideReply = true;
                }
                if ($commentItem->likes()->isLike($viewer))
                    $commentItem->likes()->removeLike($viewer);

                if (Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity') && !Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->isDislike($commentItem, $viewer))
                    Engine_Api::_()->getDbtable('dislikes', 'nestedcomment')->addDislike($commentItem, $viewer);
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Success
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('You no longer like this action.');

        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $helper = 'advancedActivity';
            if (!empty($isShare)) {
                $helper = 'advancedActivityShare';
            }
            $method = 'update';
            $onViewPage = $this->_getParam('onViewPage');
            $this->view->body = $this->view->$helper($action, array('noList' => true, 'onViewPage' => $onViewPage, 'viewAllLikes' => $onViewPage, 'feedSettings' => $this->_getParam('feedSettings', array()), 'viewAllComments' => $onViewPage, 'hideReply' => $hideReply), $method = 'update');
        }
    }

    /**
     * Handles HTTP request to get an activity feed item's comments and returns 
     * a Json as the response
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/viewcomment
     *
     * @return void
     */
    public function viewcommentAction() {
        // Collect params
        $action_id = $this->_getParam('action_id');
        $isShare = $this->_getParam('isShare');
        $viewer = Engine_Api::_()->user()->getViewer();

        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
        $form = $this->view->form = new Activity_Form_Comment();
        $form->setActionIdentity($action_id);
        $form->setAttrib('class', 'Activity_Form_Comment');

        // Redirect if not json context
        if (null === $this->_getParam('format', null)) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_getParam('format', null)) {
            $helper = 'advancedActivity';
            if (!empty($isShare)) {
                $helper = 'advancedActivityShare';
            }
            $this->view->body = $this->view->$helper($action, array('viewAllComments' => true, 'noList' => $this->_getParam('nolist', false), 'feedSettings' => $this->_getParam('feedSettings', array())));
        }
    }

    /**
     * Handles HTTP POST request to comment on an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/comment
     *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function commentAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Make form
        $this->view->form = $form = new Activity_Form_Comment();
        $isShare = $this->_getParam('isShare');
        // Not post
        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not a post');
            return;
        }
        $settings = Engine_Api::_()->getApi('settings', 'core');
        // Not valid
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }
        if (!empty($settings->aaf_composer_value) && ($settings->aaf_composer_value != ($settings->aaf_list_view_value + $settings->aaf_publish_str_value))) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }
        // Start transaction
        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $action_id = $this->view->action_id = $this->_getParam('action_id', $this->_getParam('action', null));
            $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
            $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
            $body = $form->getValue('body');
            // Check authorization
            if (!Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment'))
                throw new Engine_Exception('This user is not allowed to comment on this item.');

            // Add the comment
            $subject = $viewer;
            if (Engine_Api::_()->advancedactivity()->isBaseOnContentOwner($viewer, $action->getObject()))
                $subject = $action->getObject();
            if ($subject->getType() == 'siteevent_event') {
                $subject = $subject->getParent();
            }
            $row = $action->comments()->addComment($subject, $body);

            $userIds = array();
            $allowedNotification = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.notification.onoff.enable', 1);
            if($allowedNotification) {
            $offNotification = Engine_Api::_()->getDbtable('notificationsettings', 'advancedactivity');
            $userIds = $offNotification->getUserIdsByActionId($action_id);
            }
            // Notifications
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            
            // Add notification for owner of activity (if user and not viewer)
            if ($action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity() && !in_array($action->subject_id, $userIds) ) {
                $notifyApi->addNotification($actionOwner, $subject, $action, 'commented', array(
                    'label' => 'post'
                ));
            }

            // Add a notification for all users that commented or like except the viewer and poster
            // @todo we should probably limit this
            $commentedUserNotifications = array();
            foreach ($action->comments()->getAllCommentsUsers() as $notifyUser) {
                if ($notifyUser->getType() == 'user' && $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() && !in_array($notifyUser->getIdentity(),$userIds)) {

                    $commentedUserNotifications[] = $notifyUser->getIdentity();
                    $notifyApi->addNotification($notifyUser, $subject, $action, 'commented_commented', array(
                        'label' => 'post'
                    ));
                }
            }

            // Add a notification for all users that commented or like except the viewer and poster
            // @todo we should probably limit this
            foreach ($action->likes()->getAllLikesUsers() as $notifyUser) {
                if ($notifyUser->getType() == 'user' && $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() && !in_array($notifyUser->getIdentity(),$userIds)) {

                    // Don't send a notification if the user both commented and liked this
                    if (in_array($notifyUser->getIdentity(), $commentedUserNotifications))
                        continue;

                    $notifyApi->addNotification($notifyUser, $subject, $action, 'liked_commented', array(
                        'label' => 'post'
                    ));
                }
            }

            //PAGE COMMENT CREATE NOTIFICATION WORK
            $object_type = $action->object_type;
            $object_id = $action->object_id;


            if ($object_type == 'sitepage_page' && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepage')) {
                if (Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitepage')->version) {
                    $sitepage = Engine_Api::_()->getItem('sitepage_page', $object_id);
                    Engine_Api::_()->sitepage()->sendNotificationEmail($sitepage, $action, 'sitepage_activitycomment', '', 'Activity Comment');
                }
            } else if ($object_type == 'sitegroup_group') {
                $sitegroup = Engine_Api::_()->getItem('sitegroup_group', $object_id);
                Engine_Api::_()->sitegroup()->sendNotificationEmail($sitegroup, $action, 'sitegroup_activitycomment', '', 'Activity Comment');
            } else if ($object_type == 'sitestore_store') {
                $sitestore = Engine_Api::_()->getItem('sitestore_store', $object_id);
                Engine_Api::_()->sitestore()->sendNotificationEmail($sitestore, $action, 'sitestore_activitycomment', '', 'Activity Comment');
            } else if ($object_type == 'sitebusiness_business') {
                $sitebusiness = Engine_Api::_()->getItem('sitebusiness_business', $object_id);
                Engine_Api::_()->sitebusiness()->sendNotificationEmail($sitebusiness, $action, 'sitebusiness_activitycomment', '', 'Activity Comment');
            } else if ($object_type == 'siteevent_event') {
                $siteevent = Engine_Api::_()->getItem('siteevent_event', $object_id);
                Engine_Api::_()->siteevent()->sendNotificationEmail($siteevent, $action, 'siteevent_activitycomment', '', 'Activity Comment', null, 'comment', $viewer);
            }

            $attachment = null;
            $attachmentPhotoValue = $this->_getParam('photo_id');
            $attachmentType = $this->_getParam('type');
            if ($attachmentPhotoValue && $attachmentType) {
                $attachment = Engine_Api::_()->getItem('album_photo', $attachmentPhotoValue);
            }
            $attachmentParams = $this->_getParam('attachment');
            if ($attachmentParams && $attachmentParams['type']=== 'sticker') {
                $attachment = Engine_Api::_()->getItemByGuid($attachmentParams['stikcer_guid']);
            }
            if ($attachmentParams && $attachmentParams['type']=== 'gifPhoto') {
                if( !empty($attachmentParams['gif_guid']) ) {
                  $attachment = Engine_Api::_()->getItemByGuid($attachmentParams['gif_guid']);
                } elseif( !empty($attachmentParams['gif_code']) ) {
                  $photoTable = Engine_Api::_()->getDbtable('photos', 'sitegifplayer');
                  $attachment = $photoTable->getByCode($attachmentParams['gif_code']);
                  if( empty($attachment) ) {
                    $url = sprintf(Sitegifplayer_Api_Core::GIPHY_MEDIA_URL, $attachmentParams['gif_code']);
                    $attachment = $photoTable->addPhoto($url, array(
                      'code' => $attachmentParams['gif_code'],
                      'title' => $attachmentParams['title'],
                      'enabled' => 0
                      ), true);
                  }
                }
            }
            if ($attachment && isset($row->attachment_type) && isset($row->attachment_id)) {
                $row->attachment_type = $attachment->getType();
                $row->attachment_id = $attachment->getIdentity();
                $row->save();
            }

            $composerDatas = $this->getRequest()->getParam('composer', null);

            $tagsArray = array();
            parse_str($composerDatas['tag'], $tagsArray);
            if (!empty($tagsArray)) {

//                if ($action) {
//                    $actionParams = (array) $action->params;
//                    $action->params = array_merge((array) $action->params, array('tags' => $tagsArray));
//                    $action->save();
//                }
                $viewer = Engine_Api::_()->_()->user()->getViewer();
                $type_name = Zend_Registry::get('Zend_Translate')->translate('post');
                if (is_array($type_name)) {
                    $type_name = $type_name[0];
                } else {
                    $type_name = 'post';
                }
                $notificationAPi = Engine_Api::_()->getDbtable('notifications', 'activity');

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

                if ($action) {
                    $data = array_merge((array) $action->params, array('tags' => $tagsArray));
                    $row->params = Zend_Json::encode($data);
                }
                $row->save();
            }
            // Stats
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

            // check public settings
             
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Assign message for json
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment posted');
        $this->view->comment_id = $row->getIdentity();
        // Redirect if not json
        if (null === $this->_getParam('format', null)) {
            $this->_redirect($form->return_url->getValue(), array('prependBase' => false));
        } else if ('json' === $this->_getParam('format', null)) {
            $helper = 'advancedActivity';
            if (!empty($isShare)) {
                $helper = 'advancedActivityShare';
            }
            $method = 'update';
            $show_all_comments = $this->_getParam('show_all_comments');

            $onViewPage = $this->_getParam('onViewPage');

            if ($onViewPage) {
                $show_all_comments = true;
            }

            $this->view->body = $this->view->$helper($action, array('noList' => true, 'submitComment' => true, 'onViewPage' => $onViewPage, 'viewAllLikes' => $onViewPage, 'viewAllComments' => $onViewPage, 'hideReply' => false, 'action_id' => $action_id, 'feedSettings' => $this->_getParam('feedSettings', array())), $method, $show_all_comments);
        }
    }

    public function addMoreListAction() {
        if (!$this->_helper->requireUser()->isValid())
            return;
        $this->view->action_id = $this->_getParam('action_id', '0');
    }

    public function addMoreListNetworkAction() {
        if (!$this->_helper->requireUser()->isValid())
            return;
        $this->view->action_id = $this->_getParam('action_id', '0');
        $viewer = Engine_Api::_()->user()->getViewer();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $enableNetworkList = $settings->getSetting('advancedactivity.networklist.privacy', 1);
        $this->view->network_lists = $networkLists = Engine_Api::_()->advancedactivity()->getNetworks($enableNetworkList, $viewer);

        $this->view->enableNetworkList = count($networkLists);
    }

    public function suggestAction() {
        if (!$this->_helper->requireUser()->isValid())
            return;

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer->getIdentity()) {
            $data = null;
        } else {
            $data = array();
            // first get friend lists created by the user
            $listTable = Engine_Api::_()->getItemTable('user_list');
            $listSelect = $listTable->select()->where('owner_id = ?', $viewer->getIdentity());
            if (0 < ($limit = (int) $this->_getParam('limit', 10))) {
                $listSelect->limit($limit);
            }

            if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                $listSelect->where('`' . $listTable->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
            }
            $lists = $listTable->fetchAll($listSelect);

            foreach ($lists as $key => $list) {

                $data[] = array(
                    'id' => $list->list_id,
                    'label' => $list->title,
                );
            }
        }

        if ($this->_getParam('sendNow', true)) {
            return $this->_helper->json($data);
        } else {
            $this->_helper->viewRenderer->setNoRender(true);
            $data = Zend_Json::encode($data);
            $this->getResponse()->setBody($data);
        }
    }

    public function shareItemAction() {
        // $this->view->type = $type = $this->_getParam('type', null);
        //  $this->view->id = $id = $this->_getParam('id', null);
        $this->view->action_id = $action_id = $this->_getParam('action_id', null);
        $actionIds = Engine_Api::_()->getDbtable('shares', 'advancedactivity')->getShareActionIdsForFeed(array('parent_action_id' => $action_id));

        // $this->view->attachment = $attachment = Engine_Api::_()->getItem($type, $id);
        $actionTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
        $viewer = Engine_Api::_()->user()->getViewer();

        $actions = $actionTable->getActivityOfShare($viewer, array('action_ids' => $actionIds));
        $hideItems = array();
        if ($viewer->getIdentity())
            $hideItems = Engine_Api::_()->getDbtable('hide', 'advancedactivity')->getHideItemByMember($viewer);

        // Pre-process
        $activity = array();
        if (count($actions) > 0) {
            foreach ($actions as $action) {

                // skip disabled actions
                if (!$action->getTypeInfo() || !$action->getTypeInfo()->enabled)
                    continue;
                // skip items with missing items
                if (!$action->getSubject() || !$action->getSubject()->getIdentity())
                    continue;
                if (!$action->getObject() || !$action->getObject()->getIdentity())
                    continue;

                // skip the hide actions and content        
                if (!empty($hideItems)) {
                    if (isset($hideItems[$action->getType()]) && in_array($action->getIdentity(), $hideItems[$action->getType()])) {
                        continue;
                    }
                    if (!$action->getTypeInfo()->is_object_thumb && isset($hideItems[$action->getSubject()->getType()]) && in_array($action->getSubject()->getIdentity(), $hideItems[$action->getSubject()->getType()])) {
                        continue;
                    }
                    if (($action->getTypeInfo()->is_object_thumb || $action->getObject()->getType() == 'user' ) && isset($hideItems[$action->getObject()->getType()]) && in_array($action->getObject()->getIdentity(), $hideItems[$action->getObject()->getType()])) {
                        continue;
                    }
                }

                // track/remove users who do too much (but only in the main feed)
                if (empty($subject)) {
                    $actionSubject = $action->getSubject();
                    $actionObject = $action->getObject();
                    if (!isset($itemActionCounts[$actionSubject->getGuid()])) {
                        $itemActionCounts[$actionSubject->getGuid()] = 1;
                    } else {
                        $itemActionCounts[$actionSubject->getGuid()] ++;
                    }
                }
                // remove items with disabled module attachments
                try {
                    $attachments = $action->getAttachments();
                } catch (Exception $e) {
                    // if a module is disabled, getAttachments() will throw an Engine_Api_Exception; catch and continue
                    continue;
                }

                // add to list

                $activity[] = $action;
            }
        }

        $this->view->activity = $activity;
        $this->view->activityCount = count($activity);
        $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Advancedactivity/View/Helper', 'Advancedactivity_View_Helper');
    }

    /**
     * Handles HTTP POST request to delete a comment or an activity feed item
     * @return void
     */
    function deleteAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');

        if (!$this->_helper->requireUser()->isValid())
            return;

        // Identify if it's an action_id or comment_id being deleted
        $this->view->comment_id = $comment_id = $this->_getParam('comment_id', null);
        $this->view->action_id = $action_id = $this->_getParam('action_id', null);

        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
        if (!$action) {
            // tell smoothbox to close
            $this->view->status = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it has been removed.');
            $this->view->smoothboxClose = true;
            return $this->render('delete');
        }

        // Send to view script if not POST
        if (!$this->getRequest()->isPost())
            return;
        $is_owner = false;
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
                ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) || // owner of profile being commented on
                ('user' == $action->object_type && $viewer->getIdentity() == $action->object_id))) {   // commenter
            // Delete action item and all comments/likes
            $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
            $db->beginTransaction();
            try {
                if ($action->getTypeInfo()->commentable <= 1) {
                    $comments = $action->getComments(1);
                    if ($comments) {
                        foreach ($comments as $action_comments) {
                            $action_comments->delete();
                        }
                    }
                }
                $action->deleteItem();
                $db->commit();

                // tell smoothbox to close
                $this->view->status = true;
                $this->view->message = Zend_Registry::get('Zend_Translate')->_('This activity item has been removed.');
                $this->view->smoothboxClose = true;
                return $this->render('delete');
            } catch (Exception $e) {
                $db->rollback();
                $this->view->status = false;
            }
        } elseif ($comment_id) {
            $comment = $action->comments()->getComment($comment_id);
            // allow delete if profile/entry owner
            $db = Engine_Api::_()->getDbtable('comments', 'activity')->getAdapter();
            $db->beginTransaction();
            if ($activity_moderate || $is_owner ||
                    ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
                    ('user' == $action->object_type && $viewer->getIdentity() == $action->object_id)) {
                try {
                    $action->comments()->removeComment($comment_id);


                    $db->commit();
                    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
                    return $this->render('delete');
                } catch (Exception $e) {
                    $db->rollback();
                    $this->view->status = false;
                }
            } else {
                $this->view->message = Zend_Registry::get('Zend_Translate')->_('You do not have the privilege to delete this comment');
                return $this->render('delete');
            }
        } else {
            // neither the item owner, nor the item subject.  Denied!
            $this->_forward('requireauth', 'error', 'core');
        }
    }

    // This is widgetized page where - we are display the welcome tab content.
    public function welcometabAction() {
        // Disable Layout.
        $viewer = Engine_Api::_()->user()->getViewer();
        $getCustomBlockSettings = true;
        $allowOtherWidgets = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcomeTab.isOtherWid', 0);
        if (empty($allowOtherWidgets)) {
            $getCustomBlockSettings = Engine_Api::_()->advancedactivity()->getCustomBlockSettings(array());
        }

        $viewerId = $viewer->getIdentity();
        if (empty($viewerId) || empty($getCustomBlockSettings)) {
            return $this->_forward('requireauth', 'error', 'core');
        }
        $coremodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core');
        $coreversion = $coremodule->version;
        if ($coreversion < '4.1.0') {
            $this->_helper->content->render();
        } else {
            $this->_helper->content->setNoRender()->setEnabled();
        }
    }

    public function customBlockViewAction() {
        if (!$this->_helper->requireUser()->isValid())
            return;

        $id = $this->_getParam('id', 0);
        if (empty($id)) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->itemObj = $itemObj = Engine_Api::_()->getItem('advancedactivity_customblock', $id);
        $is_auth = Engine_Api::_()->advancedactivity()->customBlockAuth($itemObj);
        if (empty($is_auth)) {
            return $this->_forward('requireauth', 'error', 'core');
        }
    }

    public function suggestItemAction(){
        $itemType = $this->_getParam('type', null);
        $value = $this->_getParam('text', '');
        if(empty($itemType)){
            return;
        }
        
        $item = Engine_Api::_()->getItemTable("$itemType");
        $select = $item->select();
        if(!empty($value)){
            $select->where('title like ?','%'.$value.'%');
        }
        $select->order('title');
        $contents = $item->fetchAll($select);
        $data = array();
        foreach ($contents as $content){
         $data[] = array(
             'id'=> $content->getIdentity(),
             'label'=>$content->getTitle(),
             'photo' => $this->view->itemPhoto($content, 'thumb.icon'),
             'url' => $content->getPhotoUrl()
         );   
        }
        
        echo json_encode($data);
        die;
    }

    public function webcamimageAction() {
        $temFileName = null;
        $session = new Zend_Session_Namespace();
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/webcam';

        $webcam_type = $this->_getParam('webcam_type');
        if (!empty($webcam_type)) {
            $session->webcam_type = $webcam_type;
        }
        $webcam_type = !empty($session->webcam_type) ? $session->webcam_type : $webcam_type;
        $this->view->webcam_type = $webcam_type;


        $aaf_type = $this->_getParam('aaf_type');
        if (!empty($aaf_type)) {
            $session->aaf_type = $aaf_type;
        }
        $aaf_type = !empty($session->aaf_type) ? $session->aaf_type : $aaf_type;
        $this->view->aaf_type = $aaf_type;


        $subject_id = $this->_getParam('subject_id');
        if (!empty($subject_id)) {
            $session->subject_id = $subject_id;
        }
        $subject_id = !empty($session->subject_id) ? $session->subject_id : $subject_id;
        $this->view->subject_id = $subject_id;


        if (isset($session->tem_file_name)) {
            $temFileName = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->seaddonsBaseUrl() . '/public/webcam/' . $session->tem_file_name;
            @chmod($temFileName, 0777);

            if (strstr($webcam_type, 'profile_photo')) {
                // Make profile photo of loggden user.
                $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
                $viewer->setPhoto('public/webcam/' . $session->tem_file_name);
                $this->view->photo_name = $path . DIRECTORY_SEPARATOR . $session->tem_file_name;
            } else if (strstr($webcam_type, 'album_photo')) {

                $Filepath = $path . '/' . $session->tem_file_name;
                if (!Engine_Api::_()->user()->getViewer()->getIdentity()) {
                    $this->_redirect('login');
                    return;
                }

                if (empty($Filepath)) {
                    $this->view->status = false;
                    $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
                    return;
                }

                $viewer = Engine_Api::_()->user()->getViewer();
                $isAdvAlbumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('advalbum');
                $isAlbumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album');

                switch ($aaf_type) {
                    case '0':
                        $resourceObj = $viewer;
                        if (!empty($isAdvAlbumEnabled) && empty($isAlbumEnabled)) {
                            $table = Engine_Api::_()->getDbtable('albums', 'advalbum');
                            $photoTable = Engine_Api::_()->getDbtable('photos', 'advalbum');
                            $isAdvAlbumFlag = true;
                        } else {
                            $table = Engine_Api::_()->getDbtable('albums', 'album');
                            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
                            $isAdvAlbumFlag = false;
                        }
                        break;

                    case '1':
                        $resourceObj = Engine_Api::_()->getItem('sitepage_page', $subject_id);
                        $table = Engine_Api::_()->getDbtable('albums', 'sitepage');
                        $photoTable = Engine_Api::_()->getDbtable('photos', 'sitepage');
                        break;

                    case '2':
                        $resourceObj = Engine_Api::_()->getItem('sitebusiness_business', $subject_id);
                        $table = Engine_Api::_()->getDbtable('albums', 'sitebusiness');
                        $photoTable = Engine_Api::_()->getDbtable('photos', 'sitebusiness');
                        break;
                    case '3':
                        $resourceObj = Engine_Api::_()->getItem('sitegroup_group', $subject_id);
                        $table = Engine_Api::_()->getDbtable('albums', 'sitegroup');
                        $photoTable = Engine_Api::_()->getDbtable('photos', 'sitegroup');
                        break;
                    case '4':
                        $resourceObj = Engine_Api::_()->getItem('sitestore_store', $subject_id);
                        $table = Engine_Api::_()->getDbtable('albums', 'sitestore');
                        $photoTable = Engine_Api::_()->getDbtable('photos', 'sitestore');
                        break;
                }

                // Get album
                $db = $table->getAdapter();
                $db->beginTransaction();

                try {
                    $type = $this->_getParam('type', 'wall');

                    if (empty($type))
                        $type = 'wall';

                    $album = $table->getSpecialAlbum($resourceObj, $type);
                    $photo = $photoTable->createRow();

                    // Check "Advanced Album" ( 3rd party plugin ) enabled or not, If that plugin enabled then we call function accordingly because they have differ method for photo save.
                    if (!empty($isAdvAlbumFlag)) {
                        $params = array(
                            'owner_type' => 'user',
                            'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
                        );

                        $file = array(
                            'tmp_name' => $Filepath,
                            'name' => $session->tem_file_name
                        );

                        $photo = Engine_Api::_()->advalbum()->createPhoto($params, $file);
                    } else {
                        $photo->setFromArray(array(
                            'owner_type' => 'user',
                            'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
                        ));
                        $photo->save();
                        $photo->setPhoto($Filepath);
                    }

                    $photo->order = $photo->photo_id;
                    $photo->album_id = $album->album_id;

                    // Code only for 'Page Plugin' & 'Business Plugin'
                    if ($aaf_type == 1) {
                        $photo->collection_id = $album->album_id;
                        $photo->page_id = $subject_id;
                        $photo->user_id = $viewer->getIdentity();
                    } else if ($aaf_type == 2) {
                        $photo->collection_id = $album->album_id;
                        $photo->business_id = $subject_id;
                        $photo->user_id = $viewer->getIdentity();
                    } else if ($aaf_type == 3) { // Code For Site Group Plugin
                        $photo->collection_id = $album->album_id;
                        $photo->group_id = $subject_id;
                        $photo->user_id = $viewer->getIdentity();
                    } else if ($aaf_type == 4) { // Code For Site Store Plugin
                        $photo->collection_id = $album->album_id;
                        $photo->store_id = $subject_id;
                        $photo->user_id = $viewer->getIdentity();
                    }

                    $photo->save();

                    if (!$album->photo_id) {
                        $album->photo_id = $photo->getIdentity();
                        $album->save();
                    }
                    $db->commit();

                    $this->view->status = true;
                    $this->view->photo_id = $photo->photo_id;
                    $this->view->album_id = $album->album_id;
                    $this->view->src = $photo->getPhotoUrl();
                } catch (Exception $e) {
                    $db->rollBack();
                    //throw $e;
                    $this->view->status = false;
                }
            }

            unset($session->webcam_type);
            unset($session->aaf_type);
            unset($session->subject_id);
        }
        $this->view->tem_file_name = $temFileName;
        unset($session->tem_file_name);
    }

    public function uploadimageAction() {

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        //GET FORM
        $this->view->form = $form = new Advancedactivity_Form_Photo();

        //CHECK FORM VALIDATION
        if (!$this->getRequest()->isPost()) {
            return;
        }

        //CHECK FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        //UPLOAD PHOTO
        if ($form->Filedata->getValue() !== null) {
            //GET DB
            $db = $viewer->getTable()->getAdapter();
            $db->beginTransaction();
            //PROCESS
            try {
                //SET PHOTO
                $viewer->setPhoto($form->Filedata);
                $db->commit();
            } catch (Engine_Image_Adapter_Exception $e) {
                $db->rollBack();
                $form->addError(Zend_Registry::get('Zend_Translate')->_('The uploaded file is not supported or is corrupt.'));
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else if ($form->getValue('coordinates') !== '') {
            
        }
        $this->view->smoothboxClose = true;
    }

    /**
     * Handles HTTP POST request to share an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/share
     *
     * @return void
     */
    public function shareAction() {
        if (!$this->_helper->requireUser()->isValid())
            return;

        $type = $this->_getParam('type');
        $id = $this->_getParam('id');
        $parent_action_id = $this->_getparam('action_id', null);


        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->attachment = $attachment = Engine_Api::_()->getItem($type, $id);
        $this->view->form = $form = new Advancedactivity_Form_Share();

        if (!$attachment) {
            // tell smoothbox to close
            $this->view->status = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it
has been removed.');
            $this->view->smoothboxClose = true;
            return $this->render('deletedItem');
        }


        // hide facebook and twitter option if not logged in
//    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
//    if (!$facebookTable->isConnected()) {
//      $form->removeElement('post_to_facebook');
//    }
//
//    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
//    if (!$twitterTable->isConnected()) {
//      $form->removeElement('post_to_twitter');
//    }




        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process

        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();

        try {
            // Get body
            $body = $form->getValue('body');
// Set Params for Attachment
            $params = array(
                'type' => '<a href="' . $attachment->getHref() . '">' . $attachment->getMediaType() . '</a>',
            );
            // Add activity
            $api = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
            // $action = $api->addActivity($viewer, $viewer, 'post_self', $body);
            $action = $api->addActivity($viewer, $attachment->getOwner(), 'share', $body, $params);
            if ($action) {
                $api->attachActivity($action, $attachment);


                if (!empty($parent_action_id)) {
                    $shareTable = Engine_Api::_()->getDbtable('shares', 'advancedactivity');
                    $shareTable->insert(array(
                        'resource_type' => (string) $type,
                        'resource_id' => (int) $id,
                        'parent_action_id' => $parent_action_id,
                        'action_id' => $action->action_id,
                    ));
                }
            }
            $db->commit();
            // Notifications
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            // Add notification for owner of activity (if user and not viewer)
            if ($action->subject_type == 'user' && $attachment->getOwner()->getIdentity() != $viewer->getIdentity()) {
                $notifyApi->addNotification($attachment->getOwner(), $viewer, $action, 'shared', array(
                    'label' => $attachment->getMediaType(),
                ));
            }

            // Preprocess attachment parameters
            $publishMessage = html_entity_decode($form->getValue('body'));
            $publishUrl = null;
            $publishName = null;
            $publishDesc = null;
            $publishPicUrl = null;
            // Add attachment
            if ($attachment) {
                $publishUrl = $attachment->getHref();
                $publishName = $attachment->getTitle();
                $publishDesc = $attachment->getDescription();
                if (empty($publishName)) {
                    $publishName = ucwords($attachment->getShortType());
                }
                if (($tmpPicUrl = $attachment->getPhotoUrl())) {
                    $publishPicUrl = $tmpPicUrl;
                }
                // prevents OAuthException: (#100) FBCDN image is not allowed in stream
                if ($publishPicUrl &&
                        preg_match('/fbcdn.net$/i', parse_url($publishPicUrl, PHP_URL_HOST))) {
                    $publishPicUrl = null;
                }
            } else {
                $publishUrl = $action->getHref();
            }
            // Check to ensure proto/host
            if ($publishUrl &&
                    false === stripos($publishUrl, 'http://') &&
                    false === stripos($publishUrl, 'https://')) {
                $publishUrl = 'http://' . $this->_HOST_NAME . $publishUrl;
            }
            if ($publishPicUrl &&
                    false === stripos($publishPicUrl, 'http://') &&
                    false === stripos($publishPicUrl, 'https://')) {
                $publishPicUrl = 'http://' . $this->_HOST_NAME . $publishPicUrl;
            }
            // Add site title
            if ($publishName) {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title
                        . ": " . $publishName;
            } else {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title;
            }


            // Publish to facebook, if checked & enabled
            $enable_socialdnamodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdna');
            if ($this->_getParam('post_to_facebook', false) &&
                    ('publish' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable || $enable_socialdnamodule)) {
                try {

                    //$facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
                    $session = new Zend_Session_Namespace();

                    $facebookApi = $facebook = Seaocore_Api_Facebook_Facebookinvite::getFBInstance();
                    //$fb_uid = $facebookTable->find($viewer->getIdentity())->current();

                    if ($facebookApi && Seaocore_Api_Facebook_Facebookinvite::checkConnection(null, $facebookApi)) {
                        $fb_data = array(
                            'message' => $publishMessage,
                        );
                        if ($publishUrl) {
                            $fb_data['link'] = $publishUrl;
                        }
                        if ($publishName) {
                            $fb_data['name'] = $publishName;
                        }
                        if ($publishDesc) {
                            $fb_data['description'] = $publishDesc;
                        }
                        if ($publishPicUrl) {
                            $fb_data['picture'] = $publishPicUrl;
                        }
                        if (isset($fb_data['link']) && !empty($fb_data['link'])) {
                            $appkey = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.apikey');
                            $appsecret = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.secretkey');
                            if (!empty($appkey) && !empty($appsecret)) {
                                $shortURL = Engine_Api::_()->getApi('Bitly', 'seaocore')->get_bitly_short_url($fb_data['link'], $appkey, $appsecret, $format = 'txt');
                                $fb_data['link'] = $shortURL;
                            }
                        }
                        $res = $facebookApi->api('/me/feed', 'POST', $fb_data);
                    }
                } catch (Exception $e) {
                    // Silence
                }
            } // end Facebook
            // Publish to twitter, if checked & enabled
            if ($this->_getParam('post_to_twitter', false) &&
                    'publish' == Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable) {
                try {
                    $Api_twitter = Engine_Api::_()->getApi('twitter_Api', 'seaocore');

                    if ($Api_twitter->isConnected()) {

                        $twitterOauth = $twitter = $Api_twitter->getApi();
                        $login = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.apikey');
                        $appkey = Engine_Api::_()->getApi('settings', 'core')->getSetting('bitly.secretkey');


                        //TWITTER ONLY ACCEPT 140 CHARACTERS MAX..
                        //IF BITLY IS CONFIGURED ON THE SITE..
                        if (!empty($login) && !empty($appkey)) {
                            if (strlen(html_entity_decode($_POST['body'])) > 140 || isset($_POST['attachment'])) {
                                if (isset($_POST['attachment'])) {
                                    $shortURL = Engine_Api::_()->getApi('Bitly', 'seaocore')->get_bitly_short_url((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $attachment->getHref(), $login, $appkey, $format = 'txt');
                                    $BitlayLength = strlen($shortURL);
                                } else {
                                    $BitlayLength = 0;
                                    $shortURL = '';
                                }
                                $twitterFeed = substr(html_entity_decode($_POST['body']), 0, (140 - ($BitlayLength + 1))) . ' ' . $shortURL;
                            } else
                                $twitterFeed = html_entity_decode($_POST['body']);
                        }
                        else {
                            $twitterFeed = substr(html_entity_decode($_POST['body']), 0, 137);
                        }

                        $lastfeedobject = $twitterOauth->post(
                                'statuses/update', array('status' => $twitterFeed)
                        );
                        //$twitter->statuses->update($message);
                    }
                } catch (Exception $e) {
                    // Silence
                }
            }


            // Publish to janrain
            if (//$this->_getParam('post_to_janrain', false) &&
                    'publish' == Engine_Api::_()->getApi('settings', 'core')->core_janrain_enable) {
                try {
                    $session = new Zend_Session_Namespace('JanrainActivity');
                    $session->unsetAll();

                    $session->message = $publishMessage;
                    $session->url = $publishUrl ? $publishUrl : 'http://' . $this->_HOST_NAME . _ENGINE_R_BASE;
                    $session->name = $publishName;
                    $session->desc = $publishDesc;
                    $session->picture = $publishPicUrl;
                } catch (Exception $e) {
                    // Silence
                }
            }
        } catch (Exception $e) {
            $db->rollBack();
            throw $e; // This should be caught by error handler
        }

        // If we're here, we're done
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Success!');

        // Redirect if in normal context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $return_url = $form->getValue('return_url', false);
            if (!$return_url) {
                $return_url = $this->view->url(array(), 'default', true);
            }
            return $this->_helper->redirector->gotoUrl($return_url, array('prependBase' => false));
        } else if ('smoothbox' === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                //'parentRefresh'=> 10,
                'messages' => array('')
            ));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/update-commentable
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function updateCommentableAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Collect params
        $action_id = $this->_getParam('action_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        // Start transaction
        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();
        try {
            $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
            $action->commentable = !$action->commentable;
            $action->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Success
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('This change has been saved.');

        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->view->body = $this->view->advancedActivity($action, array('noList' => true, 'feedSettings' => $this->_getParam('feedSettings', array())));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/update-shareable
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function updateShareableAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Collect params
        $action_id = $this->_getParam('action_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        // Start transaction
        $db = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getAdapter();
        $db->beginTransaction();
        try {
            $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
            $action->shareable = !$action->shareable;
            $action->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Success
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('This change has been saved.');


        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $helper = 'advancedActivity';
            $this->view->body = $this->view->$helper($action, array('noList' => true, 'feedSettings' => $this->_getParam('feedSettings', array())));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/add-friend
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function addFriendAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;
        $feedSettings = $this->_getParam('feedSettings');
        // Collect params
        $action_id = $this->_getParam('action_id');
        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
        $viewer = Engine_Api::_()->user()->getViewer();
        $user_id = (int) $this->_getParam('user_id');
        $user = Engine_Api::_()->user()->getUser($user_id);
        $message = '';
        if (!$viewer->isSelf($user) && !$user->membership()->isMember($viewer) && !$viewer->isBlocked($user)) {



            // Start transaction
            $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
            $db->beginTransaction();
            try {

                $user->membership()->addMember($viewer)->setUserApproved($viewer);
                // if one way friendship and verification not required
                if (!$user->membership()->isUserApprovalRequired() && !$user->membership()->isReciprocal()) {
                    // Add activity
                    Engine_Api::_()->getDbtable('actions', 'advancedactivity')->addActivity($viewer, $user, 'friends_follow', '{item:$subject} is now following {item:$object}.');

                    // Add notification
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $user, 'friend_follow');

                    $message = "You are now following this member.";
                }

                // if two way friendship and verification not required
                else if (!$user->membership()->isUserApprovalRequired() && $user->membership()->isReciprocal()) {
                    // Add activity
                    Engine_Api::_()->getDbtable('actions', 'advancedactivity')->addActivity($user, $viewer, 'friends', '{item:$object} is now friends with {item:$subject}.');
                    Engine_Api::_()->getDbtable('actions', 'advancedactivity')->addActivity($viewer, $user, 'friends', '{item:$object} is now friends with {item:$subject}.');

                    // Add notification
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $user, 'friend_accepted');
                }

                // if one way friendship and verification required
                else if (!$user->membership()->isReciprocal()) {
                    // Add notification
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $user, 'friend_follow_request');
                }

                // if two way friendship and verification required
                else if ($user->membership()->isReciprocal()) {
                    // Add notification
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $user, 'friend_request');
                }
                $db->commit();
                $this->view->status = true;
            } catch (Exception $e) {
                $db->rollBack();
                $this->view->status = false;
                $this->view->exception = $e->__toString();
            }
        }
        // Success

        $this->view->message = ($message) ? Zend_Registry::get('Zend_Translate')->_($message) : '';


        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $helper = 'advancedActivity';
            $this->view->body = $this->view->$helper($action, array('noList' => true,'feedSettings' => $feedSettings));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/add-friend
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function cancelFriendAction() {
        // Make sure user exists
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireUser()->isValid())
            return;
        $feedSettings = $this->_getParam('feedSettings');
        $user_id = (int) $this->_getParam('user_id');
        $user = Engine_Api::_()->user()->getUser($user_id);
        // Process
        $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
        $db->beginTransaction();

        try {
            $user->membership()->removeMember($viewer);

            // Set the requests as handled
            $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
                    ->getNotificationBySubjectAndType($user, $viewer, 'friend_request');
            if ($notification) {
                $notification->mitigated = true;
                $notification->read = 1;
                $notification->save();
            }
            $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
                    ->getNotificationBySubjectAndType($user, $viewer, 'friend_follow_request');
            if ($notification) {
                $notification->mitigated = true;
                $notification->read = 1;
                $notification->save();
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();

            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
            $this->view->exception = $e->__toString();
        }
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your friend request has been cancelled.');
        // Collect params
        $action_id = $this->_getParam('action_id');
        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else if ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $helper = 'advancedActivity';
            $this->view->body = $this->view->$helper($action, array('noList' => true,'feedSettings' => $feedSettings));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/update-save-feed
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function updateSaveFeedAction() {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid())
            return;

        // Collect params
        $action_id = $this->_getParam('action_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id);
        // Start transaction
        $table = Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity');
        $table->setSaveFeeds($viewer, $action_id, $action->type);
        // Success
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('This change has been saved.');

        // Redirect if not json context
        $this->view->body = $this->view->advancedActivity($action, array('noList' => true, 'feedSettings' => $this->_getParam('feedSettings', array())));
    }

    public function getLikesAction() {
        $action_id = $this->_getParam('action_id');
        $comment_id = $this->_getParam('comment_id');

        if (!$action_id ||
                !$comment_id ||
                !($action = Engine_Api::_()->getDbtable('actions', 'advancedactivity')->getActionById($action_id)) ||
                !($comment = $action->comments()->getComment($comment_id))) {
            $this->view->status = false;
            $this->view->body = '-';
            return;
        }

        $likes = $comment->likes()->getAllLikesUsers();
        $this->view->body = $this->view->translate(array('%s likes this', '%s like this',
            count($likes)), strip_tags($this->view->fluentList($likes)));
        $this->view->status = true;
    }
    
     public function turnOnOffNotificationAction() {
        $action_id = $this->_getParam('action_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        if(empty($action_id) || empty($viewer) ){
            return;
        }
        $offNotification = Engine_Api::_()->getDbtable('notificationsettings', 'advancedactivity');
        $userIds = $offNotification->getUserIdsByActionId($action_id);
        $row =$offNotification->select()->from($offNotification->info('name'),array('action_id'))
                ->where('action_id  = ?', $action_id)
                ->query()
               ->fetchColumn();
            
        if(empty($userIds) && empty($row)){
            $data = array('action_id'=>$action_id,'user_ids'=>  json_encode(array($viewer->getIdentity())));
            $offNotification->insert($data);
        } elseif (!in_array($viewer->getIdentity(),$userIds) || empty($row)) {
            $data = array('user_ids'=>json_encode(array_merge($userIds,array($viewer->getIdentity()))));
            $offNotification->update($data,"action_id = $action_id");
        } else {
            $diff = array_diff($userIds,array($viewer->getIdentity()));
//            if(empty(array_diff($userIds,array($viewer->getIdentity())))){
//                
//            }
            $data = array('user_ids'=>json_encode(array_diff($userIds,array($viewer->getIdentity()))));
            $offNotification->update($data,"action_id = $action_id");
                    
        }
    return true;                
        }
      
  public function pinUnpinAction()
  {

    $action_id = $this->_getParam('action_id');
    $type = $this->_getParam('type');
    $time = $this->_getParam('time', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    if( empty($action_id) || empty($viewer) ) {
      return;
    }
    $pinTable = Engine_APi::_()->getDbTable('pinsettings', 'advancedactivity');
    $alreadyPin = $pinTable->select()
      ->where('action_id = ? ', $action_id)
      ->query()
      ->fetchColumn();


    if( !empty($alreadyPin) ) {
      $pinTable->delete(array('pinsetting_id = ?' => $alreadyPin));
      return;
    }
    $date = date('Y-m-d H:i:s');
    if( empty($time) ) {
      return;
    }
    $hours = (int) ($time * 24);
    $new_date = date("Y-m-d H:i:s", strtotime($date . " +$hours hours"));
    $row = $pinTable->createRow();
    $row->setFromArray(array('action_id' => $action_id, 'object_type' => $type, 'reset_date' => $new_date));
    $row->save();
    return;
  }
  /**  @desc close the current greeting by setting into cookie
    *  @param string greeting_id
    *  @return void
  */
  public function setCookieAction(){
      $greeting_id = $this->_getParam('greeting_id');
      $request = new Zend_Controller_Request_Http();
      $greetingCookie = $request->getCookie('closed_greetings');
      if(!empty($greetingCookie)){
         $greeting_id =  $greetingCookie.','.$greeting_id;
      }
      
      setcookie("closed_greetings", $greeting_id, time()+86400, '/');
  }
 
}
