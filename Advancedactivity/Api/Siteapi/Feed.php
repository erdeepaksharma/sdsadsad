<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    Feed.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Api_Siteapi_Feed extends Core_Api_Abstract {

    /**
     * Make an array for activity feeds
     *
     * @return array
     */
    public function getFeeds($actions = null, array $data = array()) {
        if (null == $actions || (!is_array($actions) && !($actions instanceof Zend_Db_Table_Rowset_Abstract)))
            return '';

        $allowEdit = 0;
        $activity_moderate = "";
        $privacyDropdownList = null;
        $is_owner = $add_saved_feed = $allowEditCategory = false;
        $viewer = Engine_Api::_()->user()->getViewer();

        if ($viewer->getIdentity()) {
            $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
            if (Engine_Api::_()->core()->hasSubject() && $viewer->isSelf(Engine_Api::_()->core()->getSubject())) {
                $allowEdit = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.post.canedit', 1);
//        if ( $allowEdit )
//          $privacyDropdownList = $this->getPrivacyDropdownList();

                if (Engine_Api::_()->hasModuleBootstrap('advancedactivitypost')) {
                    $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivitypost');
                    $categoriesList = $tableCategories->getCategories();
                    $allowEditCategory = count($categoriesList);
                }
            }

            if (!Engine_Api::_()->core()->hasSubject()) {
                $add_saved_feed_row = Engine_Api::_()->getDbtable('contents', 'advancedactivity')->getContentList(array('content_tab' => 1, 'filter_type' => 'user_saved'));
                $add_saved_feed = !empty($add_saved_feed_row) ? true : false;
            } else {
                if (Engine_Api::_()->core()->hasSubject())
                    $subject = Engine_Api::_()->core()->getSubject();

                if (empty($subject))
                    return;

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
                    case 'sitestorevent_event':
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
        }

        $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.options', array("emotions", "withtags"));

        // Prepare response
        $data = array_merge($data, array(
            'actions' => $actions,
            'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
            'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
            'commentShowBottomPost' => Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.comment.show.bottom.post', 1),
            'isMobile' => 1, //Engine_Api::_()->advancedactivity()->isMobile(),
            'activity_moderate' => $activity_moderate,
            'allowEdit' => $allowEdit,
            'allowEditCategory' => $allowEditCategory,
            'privacyDropdownList' => $privacyDropdownList,
            'allowEmotionsIcon' => in_array("emotions", $composerOptions),
            'allowSaveFeed' => $add_saved_feed,
            'is_owner' => $is_owner,
                //'showLargePhoto' => Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.largephoto.enable', 1)
        ));

        $_activityText = $this->_activityText($data);

        return $_activityText;
    }

    /**
     * Get a helper
     * 
     * @param string $name
     * @return Activity_Model_Helper_Abstract
     */
    public function getHelper($name) {
        $name = $this->_normalizeHelperName($name);
        if (!isset($this->_helpers[$name])) {
            $helper = $this->getPluginLoader()->load($name);
            $this->_helpers[$name] = new $helper;
        }

        return $this->_helpers[$name];
    }

    protected $_flagBodyIndex;

    /**
     * Activity template parsing
     * 
     * @param string $body
     * @param array $params
     * @return string
     */
    public function assemble($body, array $params = array()) {
        $body = $this->getHelper('translate')->direct($body);

        // By pass for un supported modules.
//        $getDefaultAPPModules = DEFAULT_APP_MODULES;
//        if (!empty($getDefaultAPPModules)) {
//            $getDefaultAPPModuleArray = @explode(",", DEFAULT_APP_MODULES);
//            if (!empty($params['object']) && is_object($params['object'])) {
//                $moduleName = $params['object']->getModuleName();
//                $moduleName = strtolower($moduleName);
//                if (!in_array($moduleName, $getDefaultAPPModuleArray))
//                    return $body;
//            }
//        }
        // Do other stuff
        preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);
        $this->_flagBodyIndex = 0;
        $feedParams = array();
        $isBodyParamSet = null;

        foreach ($matches as $match) {
            $tag = $match[0];
            $args = explode(':', $match[1]);
            $helper = array_shift($args);

            $tempParams = $helperArgs = array();
            foreach ($args as $arg) {
                if (substr($arg, 0, 1) === '$') {
                    $arg = substr($arg, 1);
                    $helperArgs[] = ( isset($params[$arg]) ? $params[$arg] : null );
                } else {
                    $helperArgs[] = $arg;
                }
            }
            $action = $params['actionObj'];
            if ($tag == '{item:$action:new photos}') {
                $tempParams['search'] = $tag;
                $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate(preg_replace('/<\/?a[^>]*>/', '', $helperArgs[1]));
                $tempParams['type'] = $action->getType();
                $tempParams['id'] = $action->getIdentity();
                $feedParams[] = $tempParams;
            }

            if ($tag == '{itemSeaoChild:$object:sitenews_news:$child_id}' && is_array($helperArgs) && $helperArgs[1] == 'sitenews_news') {
                $helperArgs[1] = 'sitenews_news';
                $helperArgs[2] = (isset($params['params'][$arg]) && !empty($params['params'][$arg])) ? $params['params'][$arg] : ' ';
                if (!empty($helperArgs[1]) && !empty($helperArgs[2])) {
                    $helperArgs[0] = Engine_Api::_()->getItem($helperArgs[1], $helperArgs[2]);
                    unset($helperArgs[1]);
                    unset($helperArgs[2]);
                }
            }
            
            //Work for reply on topic
            if ($tag == '{itemChild:$object:sitegroup_topic:$child_id}'  && is_array($helperArgs) && $helperArgs[1] == 'sitegroup_topic') {
                
               if (!empty($helperArgs[1]) && !empty($helperArgs[2])) {
                    $helperArgs[0] = Engine_Api::_()->getItem($helperArgs[1], $helperArgs[2]);
                    unset($helperArgs[1]);
                    unset($helperArgs[2]);
                }
            }

            if ($tag == '{item:$listing}' && is_array($helperArgs) && $helperArgs[0][0] == 'sitereview_listing') {
                $helperArgs[0] = Engine_Api::_()->getItem($helperArgs[0][0], $helperArgs[0][1]);
            }

            if ($tag == '{item:$product}' && is_array($helperArgs) && $helperArgs[0][0] = "sitestoreproduct_product")
                $helperArgs[0] = Engine_Api::_()->getItem($helperArgs[0][0], $helperArgs[0][1]);

            if ($tag == '{body:$body}') {
                $this->_idBodyContentAvailable = true;
                $action = $params['actionObj'];
                $getAttachment = $action->getFirstAttachment();
                $getAttachment = (isset($getAttachment) && !empty($getAttachment)) ? $getAttachment : array();
                foreach ($getAttachment as $attachment) {
                    if (isset($attachment->type) && isset($attachment->id)) {
                        $getObj = Engine_Api::_()->getItem($attachment->type, $attachment->id);

                        if (isset($getObj->body)) {
                            $tempBodyArray['search'] = $tag;
                            $tempBodyArray['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($getObj->body);

                            if (isset($tempBodyArray['label']))
                                $tempBodyArray['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate(@trim($tempBodyArray['label']));

                            if (isset($this->_flagBodyIndex) && !empty($this->_flagBodyIndex))
                                $feedParams[$this->_flagBodyIndex] = $tempBodyArray;
                            else
                                $feedParams[] = $tempBodyArray;

                            continue;
                        }
                    }else {
                        $tempBodyArray['search'] = $tag;
                        $tempBodyArray['label'] = "";
                        $getElementArray = array_keys($feedParams);
                        $this->_flagBodyIndex = end($getElementArray);
                        $feedParams[++$this->_flagBodyIndex] = $tempBodyArray;
                        continue;
                    }
                }
            }

            if (isset($params['flag']) && !empty($params['flag'])) { // Make a feed type body params for dynamic Feed Title                               
                if (isset($helperArgs[0]) && !empty($helperArgs[0])) {
                    if (strstr($tag, 'siteEvent')) {
                        $tag = str_replace("siteEvent", "siteevent", $tag);
                    }

                    if (strstr($tag, '{itemSeaoChild:$object:siteevent_diary:$child_id}')) {
                        $tag = str_replace("siteevent", "siteEvent", $tag);
                    }

                    if (is_object($helperArgs[0])) {
                        $tempParams['search'] = $tag;
                        $tempParams['label'] = (isset($helperArgs[1]) && !empty($helperArgs[1]) && is_string($helperArgs[1]) && ($tag != '{item:$object:topic}')) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate($helperArgs[1]) : Engine_Api::_()->getApi('Core', 'siteapi')->translate($helperArgs[0]->getTitle());

                        // @Todo: Bypass in case of this Advanced Event. We will make it, whenever work on Adv Events API.
                        if (($tag == '{itemSeaoChild:$object:siteEvent_topic:$child_id}') || ($tag == '{itemSeaoChild:$object:siteevent_topic:$child_id}'))
                            $tempParams['label'] = '';

                        $tempParams['type'] = $helperArgs[0]->getType();
                        $tempParams['id'] = $helperArgs[0]->getIdentity();
                        
                        if ((strstr($tag, 'siteEvent_diary')) || (strstr($tag, 'siteevent_diary'))) {
                            $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate(str_replace("siteEvent_diary", "", $tempParams['label']));
                        }
                        if ($tag == '{item:$object:topic}') {
                            $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('topic');
                            $tempParams['slug'] = $helperArgs[0]->getSlug();
                        }

                        if ($tag == '{itemParent:$object:forum}') {
                            $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($helperArgs[0]->getParent()->getTitle());
                            $tempParams['type'] = $helperArgs[0]->getParent()->getType();
                            $tempParams['id'] = $helperArgs[0]->getParent()->getIdentity();
                            $tempParams['slug'] = $helperArgs[0]->getParent()->getSlug();
                        }

                        if ($tag == '{itemParent:$object:siteforum}') {
                            $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($helperArgs[0]->getParent()->getTitle());
                            $tempParams['type'] = $helperArgs[0]->getParent()->getType();
                            $tempParams['id'] = $helperArgs[0]->getParent()->getIdentity();
                            $tempParams['slug'] = $helperArgs[0]->getParent()->getSlug();
                        }
                        // Add URL in case, if feed not related to app modules. So that we can open webview for that feed.
                        if (!empty($helperArgs[0])) {
                            $getFeedBodyParamURL = $this->addURLInFeedBodyParam($helperArgs[0]);
                            if (!empty($getFeedBodyParamURL))
                                $tempParams['url'] = $getFeedBodyParamURL;
                        }

                        if (isset($helperArgs[1]) && is_object($helperArgs[1]) && strstr($tag, '{actors:$subject:$object}')) {
                            $tempParams['object']['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($helperArgs[1]->getTitle());
                            $tempParams['object']['type'] = $helperArgs[1]->getType();
                            $tempParams['object']['id'] = $helperArgs[1]->getIdentity();

                            // Add URL in case, if feed not related to app modules. So that we can open webview for that feed.
                            if (!empty($helperArgs[1])) {
                                $getFeedBodyParamURL = $this->addURLInFeedBodyParam($helperArgs[1]);
                                if (!empty($getFeedBodyParamURL))
                                    $tempParams['object']['url'] = $getFeedBodyParamURL;
                            }
                        }
                    } else {
                        $tempParams['search'] = $tag;
                        $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate(preg_replace('/<\/?a[^>]*>/', '', $helperArgs[0]));

                        // In case of GUID, create object and send respective array to client.
                        if (isset($helperArgs[0]) && !empty($helperArgs[0]) && is_string($helperArgs[0]) && strstr($helperArgs[0], '_')) {
                            $explodeItemTypes = @explode("_", $helperArgs[0]);
                            $id = @end($explodeItemTypes);
                            array_pop($explodeItemTypes);
                            $type = @implode("_", $explodeItemTypes);
                            if (!empty($type) && !empty($id)) {
                                try {
                                    $getObj = Engine_Api::_()->getItem($type, $id);
                                    if (!empty($getObj)) {
                                        $tempParams['search'] = $tag;
                                        $tempParams['label'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($getObj->getTitle());
                                        $tempParams['type'] = $getObj->getType();
                                        $tempParams['id'] = $getObj->getIdentity();

                                        // Add URL in case, if feed not related to app modules. So that we can open webview for
                                        $getFeedBodyParamURL = $this->addURLInFeedBodyParam($getObj);
                                        if (!empty($getFeedBodyParamURL))
                                            $tempParams['url'] = $getFeedBodyParamURL;
                                    }
                                } catch (Exception $ex) {
                                    // Blank Exception
                                }
                            }
                        }
                    }

                    if (isset($tempParams['label']))
                        $tempParams['label'] = @html_entity_decode(Engine_Api::_()->getApi('Core', 'siteapi')->translate(@trim($tempParams['label'])), ENT_QUOTES, "utf-8");

                    // @Todo: Need to remove in future.
                    if (isset($tempParams['search']) && $tempParams['search'] == '{itemChild:$object:siteFicha_album:$child_id}')
                        $tempParams['label'] = '';

                    if (isset($tempParams['search']) && !empty($tempParams['search']))
                        $tempParams['search'] = @strtolower($tempParams['search']);

                    $feedParams[] = $tempParams;
                }
            } else { // Make a Feed Title
                try {
                    $helper = $this->getHelper($helper);
                    $r = new ReflectionMethod($helper, 'direct');
                    $content = $r->invokeArgs($helper, $helperArgs);
                    $content = preg_replace('/\$(\d)/', '\\\\$\1', $content);
                    $body = preg_replace("/" . preg_quote($tag) . "/", $content, $body, 1);
                } catch (Exception $ex) {
                    return $body;
                }
            }
        }

        if (isset($params['flag']) && !empty($params['flag'])) {
            return $feedParams;
        } else {
            $body = @strip_tags(html_entity_decode($body, ENT_QUOTES, "utf-8"));
            return $body;
        }
    }

    /*
     * Get the URL of content for the modules.
     * 
     * @param $obj content object
     * @return string OR false
     */

    private function addURLInFeedBodyParam($obj) {
        try {
            if (!empty($obj)) {
                $tempHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
                $getModuleName = $obj->getModuleName();
                $getModuleName = (!empty($getModuleName)) ? strtolower($getModuleName) : '';
                $defaultModules = @explode(",", DEFAULT_APP_MODULES);
                if (!in_array($getModuleName, $defaultModules) && $obj->getHref()) {
                    $getHref = $obj->getHref();
                    return (!strstr($getHref, 'http')) ? $tempHost . $obj->getHref() : $obj->getHref();
                }
            }
        } catch (Exception $ex) {
            // blank exception
        }

        return;
    }

    /**
     * Prepare activity feeds array
     *
     * @return array
     */
    private function _activityText($data) {
        if (!Zend_Registry::isRegistered('Zend_Translate'))
            Engine_Api::_()->getApi('Core', 'siteapi')->setTranslate();
        $sharesTable = Engine_Api::_()->getDbtable('shares', 'advancedactivity');
        if (empty($data['actions']))
            return "The action you are looking for does not exist.";
        $actions = $data['actions'];
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $staticBaseUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.static.baseurl', null);
        $tempHost = $serverHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();

        $getDefaultStorageId = Engine_Api::_()->getDbtable('services', 'storage')->getDefaultServiceIdentity();
        $getDefaultStorageType = Engine_Api::_()->getDbtable('services', 'storage')->getService($getDefaultStorageId)->getType();
        $getHost = $getPhotoHost = '';
        if ($getDefaultStorageType == 'local')
            $getPhotoHost = $getHost = !empty($staticBaseUrl) ? $staticBaseUrl : $serverHost;

        $advancedactivityCoreApi = Engine_Api::_()->advancedactivity();
        $advancedactivitySaveFeed = Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity');
        if (Engine_Api::_()->core()->hasSubject())
            $getSubject = Engine_Api::_()->core()->getSubject();

        $wordStyling = $this->_wordStyling();
        $feedDecorationSettings = $this->_feedDecorationSetting();
        // Manage feeds
        foreach ($actions as $action) {
            try { // prevents a bad feed item from destroying the entire page
                // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
                if (!$action->getTypeInfo()->enabled)
                    continue;
                if (!$action->getSubject() || !$action->getSubject()->getIdentity())
                    continue;
                if (!$action->getObject() || !$action->getObject()->getIdentity())
                    continue;

                try {
                    $objectUrl = $action->getObject()->getHref();
                } catch (Exception $ex) {
                    continue;
                }


                $activityMenu = $activityFooterMenus = $activityFeedArray = array();
                $getFeedTypeInfo = $action->getTypeInfo()->toArray();

                $item = $itemPhoto = (isset($action->getTypeInfo()->is_object_thumb) && !empty($action->getTypeInfo()->is_object_thumb)) ? $action->getObject() : $action->getSubject();

                $itemPhoto = (isset($action->getTypeInfo()->is_object_thumb) && $action->getTypeInfo()->is_object_thumb === 2) ? $action->getObject()->getParent() : $itemPhoto;

                // Prepare the feed array
                $activityFeedArray['feed'] = $action->toArray();
                $network_type = array();

                //Custom network Work
                $network_type = explode(",", $activityFeedArray['feed']['privacy']);
                if (strstr($network_type[0], 'network') && count($network_type) > 1) {
                    $activityFeedArray['feed']['privacy_icon'] = 'network_list_custom';
                } elseif (is_numeric($network_type[0]) && count($network_type) > 1) {
                    $activityFeedArray['feed']['privacy_icon'] = 'friend_list_custom';
                } else
                    $activityFeedArray['feed']['privacy_icon'] = $activityFeedArray['feed']['privacy'];

                if (isset($action->params) && !empty($action->params)) {
                    $activityFeedArray['feed']['userTag'] = $this->tagUserArray($action->params);
                } else {
                    $activityFeedArray['feed']['userTag'] = '';
                }

                // Banner Work
                if (isset($action->params) && !empty($action->params)) {
                    $bannerflag = $this->formatActionParam($action->params);
                    $activityFeedArray['feed']['params'] = $bannerflag;
                }



                // feeling Work
                if (isset($action->params) && !empty($action->params)) {
                    $feelingActivity = $this->formatFeelingParam($action->params);
                    if (!empty($feelingActivity))
                        $activityFeedArray['feed']['params'] = $feelingActivity;
                }


                //Word styling work
                if (isset($action->body) && !empty($action->body)) {
                    $wordStylingArray = $this->_checkWordStyling($action->body, $wordStyling);
                    if (!empty($wordStylingArray))
                        $activityFeedArray['feed']['wordStyle'] = $wordStylingArray;
                    else
                        $activityFeedArray['feed']['wordStyle'] = array();
                }
                else {
                    $activityFeedArray['feed']['wordStyle'] = array();
                }

                //Feed decoration
                if (isset($action->body) && !empty($action->body)) {
                    if (is_string($action->body) && strlen($action->body) <= $feedDecorationSettings['char_length']) {
                        $activityFeedArray['feed']['decoration'] = $feedDecorationSettings;
                    } else {
                        $activityFeedArray['feed']['decoration'] = array();
                    }
                } else {
                    $activityFeedArray['feed']['decoration'] = array();
                }


                //Publish date Work
                $tz = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
                if (!empty($viewer_id)) {
                    $tz = $viewer->timezone;
                }

                if (isset($activityFeedArray['feed']['publish_date']) && !empty($activityFeedArray['feed']['publish_date']) && isset($tz)) {
                    $publishDateObject = new Zend_Date(strtotime($activityFeedArray['feed']['publish_date']));
                    $publishDateObject->setTimezone($tz);
                    $activityFeedArray['feed']['publish_date'] = $publishDateObject->get('YYYY-MM-dd HH:mm:ss');
                }
//End work of publish date

                if (isset($activityFeedArray['feed']['object_type']) && strstr($activityFeedArray['feed']['object_type'], 'sitepage')) {
                    $activityFeedArray['feed']['feed_type'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitepage.feed.type', 1);
                } else if (isset($activityFeedArray['feed']['object_type']) && strstr($activityFeedArray['feed']['object_type'], 'sitegroup')) {
                    $activityFeedArray['feed']['feed_type'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitegroup.feed.type', 1);
                } else if (isset($activityFeedArray['feed']['object_type']) && strstr($activityFeedArray['feed']['object_type'], 'sitestore')) {
                    $activityFeedArray['feed']['feed_type'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitestore.feed.type', 1);
                }

                // hashtag work
                if (Engine_Api::_()->hasModuleBootstrap('sitehashtag')) {
                    $hashtags = $this->getHashtagNames($action);
                    if (!empty($hashtags))
                        $activityFeedArray['hashtags'] = $hashtags;
                }

                $activityFeedArray['feed']['time_value'] = $action->getTimeValue();

                $activityFeedArray['feed']['body'] = @strip_tags(html_entity_decode($activityFeedArray['feed']['body'], ENT_QUOTES, "utf-8"));

                // Set feed subject information on request
                if (isset($data['subject_info']) && !empty($data['subject_info'])) {
                    $activityFeedArray['feed']['subject'] = $action->getSubject()->toArray();
                    $getSubjectModName = $action->getSubject()->getModuleName();
                    $activityFeedArray['feed']['subject']['name'] = (!empty($getSubjectModName)) ? strtolower($getSubjectModName) : '';
                    $activityFeedArray['feed']['subject']["url"] = $tempHost . $action->getSubject()->getHref();

                    if ((strstr($action->getSubject()->getPhotoUrl, 'http://')) || (strstr($action->getSubject()->getPhotoUrl, 'http://'))) {
                        $getHost = '';
                    }

                    $activityFeedArray['feed']['subject']["image"] = ($action->getSubject()->getPhotoUrl('thumb.main')) ? $getHost . $action->getSubject()->getPhotoUrl('thumb.main') : '';
                    $activityFeedArray['feed']['subject']["image_icon"] = ($action->getSubject()->getPhotoUrl('thumb.icon')) ? $getHost . $action->getSubject()->getPhotoUrl('thumb.icon') : '';
                    $activityFeedArray['feed']['subject']["image_profile"] = ($action->getSubject()->getPhotoUrl('thumb.profile')) ? $getHost . $action->getSubject()->getPhotoUrl('thumb.profile') : '';
                    $activityFeedArray['feed']['subject']["image_normal"] = ($action->getSubject()->getPhotoUrl('thumb.normal')) ? $getHost . $action->getSubject()->getPhotoUrl('thumb.normal') : '';

                    //assign getHost the correct value
                    $getHost = $getPhotoHost;
                    try {
                        $owner = $action->getObject()->getOwner();
                        $owner_url = $owner->getHref();
                        $owner_title = $owner->getTitle();
                    } catch (Exception $ex) {
                        $owner_url = '';
                        $owner_title = '';
                    }
                    $activityFeedArray['feed']['object']["owner_url"] = ($owner_url) ? $getHost . $owner_url : '';
                    $activityFeedArray['feed']['object']["owner_title"] = $owner_title;

                    if (isset($activityFeedArray['feed']['subject']['creation_ip']))
                        unset($activityFeedArray['feed']['subject']['creation_ip']);

                    if (isset($activityFeedArray['feed']['subject']['lastlogin_ip']))
                        unset($activityFeedArray['feed']['subject']['lastlogin_ip']);
                }

                // Set feed object information on request
                if (isset($data['object_info']) && !empty($data['object_info'])) {
                    $activityFeedArray['feed']['object'] = $action->getObject()->toArray();
                    if (isset($activityFeedArray['feed']['object']['wishlist_id']) && isset($activityFeedArray['feed']['object']['product_id']) && isset($activityFeedArray['feed']['params']['product']))
                        $activityFeedArray['feed']['object']['product_id'] = $activityFeedArray['feed']['params']['product'][1];
                    $getObjectModName = $action->getObject()->getModuleName();
                    $activityFeedArray['feed']['object']['name'] = (!empty($getObjectModName)) ? strtolower($getObjectModName) : '';
                    $activityFeedArray['feed']['object']["url"] = $tempHost . $action->getObject()->getHref();

                    if ((strstr($action->getObject()->getPhotoUrl(), 'http://')) || (strstr($action->getObject()->getPhotoUrl(), 'https://'))) {
                        $getHost = '';
                    }

                    // Add images
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($action->getObject());
                    if (isset($getContentImages) && !empty($getContentImages))
                        $activityFeedArray['feed']['object'] = array_merge($activityFeedArray['feed']['object'], $getContentImages);

//                    $activityFeedArray['feed']['object']["image"] = ($action->getObject()->getPhotoUrl('thumb.main')) ? $getHost . '/' . $action->getObject()->getPhotoUrl('thumb.main') : '';
//                    $activityFeedArray['feed']['object']["image_icon"] = ($action->getObject()->getPhotoUrl('thumb.icon')) ? $getHost . $action->getObject()->getPhotoUrl('thumb.icon') : '';
//                    $activityFeedArray['feed']['object']["image_profile"] = ($action->getObject()->getPhotoUrl('thumb.profile')) ? $getHost . $action->getObject()->getPhotoUrl('thumb.profile') : '';
//                    $activityFeedArray['feed']['object']["image_normal"] = ($action->getObject()->getPhotoUrl('thumb.normal')) ? $getHost . $action->getObject()->getPhotoUrl('thumb.normal') : '';
                    //assign getHost the correct value
                    $getHost = $getPhotoHost;

                    $activityFeedArray['feed']['object']["owner_url"] = ($action->getObject()->getOwner()->getHref()) ? $getHost . $action->getObject()->getOwner()->getHref() : '';
                    $activityFeedArray['feed']['object']["owner_title"] = $action->getObject()->getOwner()->getTitle();


                    if (isset($activityFeedArray['feed']['object']['creation_ip']))
                        unset($activityFeedArray['feed']['object']['creation_ip']);

                    if (isset($activityFeedArray['feed']['object']['lastlogin_ip']))
                        unset($activityFeedArray['feed']['object']['lastlogin_ip']);
                }


                // Set feed like count
                $activityFeedArray['feed']['like_count'] = $action->likes()->getLikePaginator()->getTotalItemCount();

                // Set feed comment count
                $activityFeedArray['feed']['comment_count'] = $action->comments()->getCommentCount();

                if ((strstr($itemPhoto->getPhotoUrl(), 'http://')) || (strstr($itemPhoto->getPhotoUrl(), 'https://'))) {
                    $getHost = '';
                }
                $feedIconImage = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($itemPhoto);
                // Set feed icon
                $activityFeedArray['feed']['feed_icon'] = ($itemPhoto->getPhotoUrl('thumb.profile')) ? $getHost . $itemPhoto->getPhotoUrl('thumb.profile') : $feedIconImage['image_profile'];

                $getHost = $getPhotoHost;

                $privacy_titile = $privacy_icon_class = null;
                $privacy_titile_array = array();

                // Get the tag information
                $getTags = Engine_Api::_()->advancedactivity()->getTag($action);
                if (!empty($getTags)) {
                    foreach ($getTags as $tagFriend) {
                        $tempTag = $tagFriend->toArray();
                        $getTagedObj = Engine_Api::_()->getItem($tagFriend->tag_type, $tagFriend->tag_id);
                        if (!empty($getTagedObj)) {
                            $tempTag['tag_obj'] = $getTagedObj->toArray();
                            $tempTag['tag_obj']["image_icon"] = ($getTagedObj->getPhotoUrl('thumb.icon')) ? $getHost . $getTagedObj->getPhotoUrl('thumb.icon') : '';
                        }

                        if (isset($tempTag['tag_obj']['lastlogin_ip']) && !empty($tempTag['tag_obj']['lastlogin_ip']))
                            unset($tempTag['tag_obj']['lastlogin_ip']);

                        if (isset($tempTag['tag_obj']['creation_ip']) && !empty($tempTag['tag_obj']['creation_ip']))
                            unset($tempTag['tag_obj']['creation_ip']);


                        $activityFeedArray['feed']['tags'][] = $tempTag;
                    }
                }

                /* Start Attachement Work */
                if ($action->getTypeInfo()->attachable && $action->attachment_count > 0) {
                    if (false && $action->getAttachments()) {
                        // @TODO: IN CASE OF 1 ATTACHMENT OR GETRICHCONTENT CASE, WE ARE NOT USING GETRICHCONTENT AND USING THE DEFAULT ATTACHEMENT MENTHODS.
                    } else {
                        $attachmentArray = array();
                        $attachedImageCount = 0;

                        foreach ($action->getAttachments() as $attachment) {
                            $tempAttachmentArray = array();
                            if ($action->type == 'share') {
                                try {
                                    $parentObj = $attachment->item->getParent();
                                    $tempAttachmentArray['id'] = $parentObj->getIdentity();
                                } catch (Exception $ex) {
                                    
                                }
                            }
                            if ($attachment->meta->mode == 0) {
                                
                            } elseif (($attachment->meta->mode == 1) || ($attachment->meta->mode == 2)) {

                                // In case of mode-1 set the attachment title and description.
                                if ($attachment->meta->mode == 1) {
                                    $tempAttachmentArray['title'] = $attachment->item->getTitle();
                                    //$tempAttachmentArray['body'] = $attachment->item->getDescription();
                                    $tempAttachmentBody = $attachment->item->getDescription();
                                    $tempAttachmentArray['body'] = (isset($activityFeedArray['feed']['body']) && !empty($activityFeedArray['feed']['body']) && ($activityFeedArray['feed']['body'] === $tempAttachmentBody)) ? '' : $tempAttachmentBody;
                                }

                                $tempAttachmentArray["attachment_type"] = $attachment->item->getType();
                                try {
                                    //Advanced activity sell
                                    if (!empty($tempAttachmentArray["attachment_type"]) && $tempAttachmentArray["attachment_type"] == 'advancedactivity_sell') {
                                        $tempAttachmentArray["place"] = $attachment->item->place;
                                        $tempAttachmentArray["currency"] = $attachment->item->currency;
                                        $tempAttachmentArray["price"] = $attachment->item->price;
                                    }
                                } catch (Exception $ex) {
                                    
                                }
                                //Advanced activity sell

                                $activityFeedArray['feed']['attachment_content_type'] = $attachment->item->getType();
                                if (isset($activityFeedArray['feed']['type']) && ($activityFeedArray['feed']['type'] == 'share')) {
                                    $activityFeedArray['feed']['share_params_type'] = $attachment->item->getType();
                                    $activityFeedArray['feed']['share_params_id'] = $attachment->item->getIdentity();

                                    if (strstr($activityFeedArray['feed']['share_params_type'], 'sitereview')) {
                                        if (isset($activityFeedArray['feed']['share_params_id']) && $activityFeedArray['feed']['share_params_type']) {
                                            if ($activityFeedArray['feed']['share_params_type'] == 'sitereview_listing')
                                                $sitereviewObj = Engine_Api::_()->getItem('sitereview_listing', $activityFeedArray['feed']['share_params_id']);
                                            else {
                                                $tempObj = Engine_Api::_()->getItem($activityFeedArray['feed']['share_params_type'], $activityFeedArray['feed']['share_params_id']);
                                                if (isset($tempObj) && !empty($tempObj))
                                                    $sitereviewObj = $tempObj->getParent();
                                            }

                                            if (isset($sitereviewObj) && !empty($sitereviewObj) && isset($sitereviewObj->listingtype_id)) {
                                                if (isset($tempAttachmentArray) && !empty($tempAttachmentArray))
                                                    $tempAttachmentArray['listingtype_id'] = $sitereviewObj->listingtype_id;
                                                $tempAttachmentArray['listing_id'] = $sitereviewObj->listing_id;
                                            }
                                        }
                                    }
                                }

                                if ($tempAttachmentArray["attachment_type"] == 'music_playlist_song')
                                    $tempAttachmentArray["playlist_id"] = $attachment->item->playlist_id;

                                //@todo code need to be updated for all types of attachment[for now working for status update only]
                                if ($tempAttachmentArray["attachment_type"] == 'activity_action') {

                                    $tempAttachmentArray["attachment_id"] = $attachment->item->getIdentity();
                                    $attachedActionId = $attachment->meta->id;
                                    $attachedActionObject = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($attachedActionId);

                                    if (isset($attachedActionObject) &&
                                            !empty($attachedActionObject) &&
                                            $attachedActionObject->type == 'status' &&
                                            $attachedActionObject->object_type == 'user'
                                    ) {
                                        $oldcode = 'href="/profile';
                                        if (strstr($tempAttachmentArray['body'], $oldcode)) {
                                            $newcode = 'href="' . $tempHost . '/profile';
                                            $tempAttachmentArray['body'] = str_replace($oldcode, $newcode, $tempAttachmentArray['body']);
                                        }
                                    }
                                }

                                if (!empty($attachment->item))
                                    $tempAttachmentArray["attachment_id"] = $attachment->item->getIdentity();

                                if (isset($activityFeedArray['feed']['attachment_content_type']) && !empty($activityFeedArray['feed']['attachment_content_type']) && (strstr($activityFeedArray['feed']['attachment_content_type'], "sitereview_wishlist") || strstr($activityFeedArray['feed']['attachment_content_type'], "sitereview_review") || strstr($activityFeedArray['feed']['attachment_content_type'], "sitereview_listing"))) {
                                    $sitereviewInfo = $this->_getSitereviewInfo($tempAttachmentArray["attachment_type"], $tempAttachmentArray["attachment_id"]);
                                    if (isset($sitereviewInfo) && !empty($sitereviewInfo)) {
                                        $tempAttachmentArray = array_merge($tempAttachmentArray, $sitereviewInfo);
                                    }
                                }

                                // Siteevent title for Review in Advanced event
                                if (isset($activityFeedArray['feed']['attachment_content_type']) && !empty($activityFeedArray['feed']['attachment_content_type']) && (strstr($activityFeedArray['feed']['attachment_content_type'], "siteevent_review")) && isset($tempAttachmentArray['attachment_type']) && !empty($tempAttachmentArray['attachment_type']) && isset($tempAttachmentArray['attachment_id']) && !empty($tempAttachmentArray['attachment_id'])) {
                                    $getEventReviewItem = Engine_Api::_()->getItem('siteevent_review', $tempAttachmentArray['attachment_id']);
                                    if (isset($getEventReviewItem) && !empty($getEventReviewItem) && !empty($getEventReviewItem->resource_id))
                                        $tempAttachmentArray['event_id'] = $getEventReviewItem->resource_id;
                                }



                                if ($tempAttachmentArray["attachment_type"] == 'core_link')
                                    $tempAttachmentArray["uri"] = $attachment->item->uri;

                                if ($tempAttachmentArray["attachment_type"] == 'sitestoreproduct_product')
                                    $tempAttachmentArray["uri"] = $tempHost . $attachment->item->getHref();

                                try {
                                    $tempAttachmentArray["uri"] = $tempHost . $attachment->item->getHref();
                                } catch (Exception $ex) {
                                    
                                }

                                if ($tempAttachmentArray["attachment_type"] == 'sitenews_news') {
                                    $tempAttachmentArray["uri"] = $tempHost . $attachment->item->getHref();
                                    if (_CLIENT_TYPE && (_CLIENT_TYPE == 'ios')) {
                                        $activityFeedArray['feed']['object']["url"] = $tempAttachmentArray["uri"];
                                    }
                                }

                                if (strstr($tempAttachmentArray["attachment_type"], 'video') && isset($attachment->item->type) && !empty($attachment->item->type)) {

                                    $tempAttachmentArray['attachment_video_type'] = $this->videoType($attachment->item->type);
                                    try {
                                        $tempAttachmentArray['attachment_video_url'] = $this->getVideoURL($attachment->item);
                                        $contentUrl = Engine_Api::_()->getApi('Core', 'siteapi')->getContentUrl($attachment->item);
                                        if (isset($contentUrl) && !empty($contentUrl))
                                            $tempAttachmentArray['content_url'] = $contentUrl['content_url'];
                                    } catch (Exception $ex) {
                                        $tempAttachmentArray['attachment_video_url'] = "";
                                    }
                                }

                                // If attachment type related to photo then set the respective photo like and comment count information because it will be required in Photo Lightbox(IOS_VERSION >= '2.1.8' || ANDROID_VERSION >= '2.4')) {
                                if (strpos($attachment->meta->type, '_photo') && $attachment->item->getType()!= 'sitegifplayer_photo') {
                                    if ($attachedImageCount == 3 && empty($_REQUEST['action_id']) && ((_IOS_VERSION && _IOS_VERSION >= '2.1.8') || (_ANDROID_VERSION && _ANDROID_VERSION >= '2.3'))) {
                                        break;
                                    }
                                    $getAttachmentItem = $attachment->item;
                                    $tempAttachmentArray["attachment_id"] = (isset($getAttachmentItem->album_id)) ? $getAttachmentItem->album_id : $attachment->item->getAlbum()->getIdentity();
                                    $tempAttachmentArray["album_id"] = (isset($getAttachmentItem->album_id)) ? $getAttachmentItem->album_id : $attachment->item->getAlbum()->getIdentity();
                                    $tempAttachmentArray["photo_id"] = ($getAttachmentItem->getIdentity()) ? $getAttachmentItem->getIdentity() : 0;
                                    $tempAttachmentArray['tags'] = array();
                                    if (!empty($tempAttachmentArray["photo_id"])) {
                                        $photo = Engine_Api::_()->getItem('album_photo', $tempAttachmentArray["photo_id"]);

                                        $tempAttachmentArray['tags'] = $this->getPhotoTag($photo);
                                    }
                                    $getLikeCount = $attachment->item->likes()->getLikePaginator();
                                    if (isset($getLikeCount)) {
                                        $tempAttachmentArray['likes_count'] = $getLikeCount->getTotalItemCount();
                                    }

                                    $tempAttachmentArray['comment_count'] = $attachment->item->comments()->getCommentCount();
                                    $tempAttachmentArray['is_like'] = ($attachment->item->likes()->isLike($viewer)) ? 1 : 0;
                                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereaction') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereaction.reaction.active', 1)) {
                                        $tempAttachmentArray['reactions'] = $this->_getPhotoReaction($getAttachmentItem);
                                    }
                                    if ($viewer->getIdentity() && isset($tempAttachmentArray["album_id"]) && !empty($tempAttachmentArray["album_id"]) && isset($tempAttachmentArray["photo_id"]) && !empty($tempAttachmentArray["photo_id"])) {
                                        $photo = $getAttachmentItem;
                                        $tempAttachmentArray['menu'] = $this->_getPhotoMenuUrl($photo, $tempAttachmentArray);
                                    }
                                }
                                elseif($attachment->item->getType() == 'sitegifplayer_photo'){
                                    $getAttachmentItem = $attachment->item;
                                    $tempAttachmentArray["photo_id"] = $tempAttachmentArray["photo_id"] = ($getAttachmentItem->getIdentity()) ? $getAttachmentItem->getIdentity() : 0;
                                    
                                      $tempAttachmentArray["attachment_id"] =$getAttachmentItem->getIdentity();
                                      $tempAttachmentArray["title"]=$getAttachmentItem->title;
                                }

                                if (isset($activityFeedArray['feed']['attachment_content_type']) && !empty($activityFeedArray['feed']['attachment_content_type']) && (strstr($activityFeedArray['feed']['attachment_content_type'], "sitereview_photo") && isset($tempAttachmentArray["attachment_type"]) && !empty($tempAttachmentArray["attachment_type"]) && isset($tempAttachmentArray["attachment_id"]) && !empty($tempAttachmentArray["attachment_id"]) && isset($tempAttachmentArray["photo_id"]) && !empty($tempAttachmentArray["photo_id"]))) {
                                    $sitereviewInfo = $this->_getSitereviewInfo($tempAttachmentArray["attachment_type"], $tempAttachmentArray["attachment_id"], $tempAttachmentArray["photo_id"]);
                                    if (isset($sitereviewInfo) && !empty($sitereviewInfo)) {
                                        $tempAttachmentArray = array_merge($tempAttachmentArray, $sitereviewInfo);
                                    }
                                }

                                // Set the feed image in case of feed item if image.
                                if (empty($_GET['getAttachedImageDimention'])) {

                                    if ($action->attachment_count > 1 && $imageUrl = $attachment->item->getPhotoUrl('thumb.main')) {
                                        $getParentHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
                                        $baseParentUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
                                        $baseParentUrl = @trim($baseParentUrl, "/");
                                        if (!strstr($imageUrl, 'http'))
                                            $imageUrl = $getParentHost . DIRECTORY_SEPARATOR . $baseParentUrl . $imageUrl;
                                        $tempAttachmentArray["image_main"] = array(
                                            "src" => $imageUrl,
                                        );
                                        $imageMedium = $attachment->item->getPhotoUrl('thumb.medium');

                                        if (!strstr($imageMedium, 'http'))
                                            $imageMedium = $getParentHost . DIRECTORY_SEPARATOR . $baseParentUrl . $imageMedium;

                                        $tempAttachmentArray["image_medium"] = $imageMedium;
                                        $attachedImageCount++;
                                    } elseif ($params = $this->getPhotoUrl($attachment->item, 'thumb.main')) {
                                        if ($attachment->item->getType() != 'advancedactivity_sell') {
                                            $attachedImageCount++;
                                            $imageUrl = $params['url'];
                                            if (!strstr($imageUrl, 'http'))
                                                $imageUrl = $getHost . DIRECTORY_SEPARATOR . $baseParentUrl . $imageUrl;
                                            ;
                                            try {
                                                if (!empty($params)) {
                                                    $tempAttachmentArray["image_main"] = array(
                                                        "src" => $imageUrl,
                                                        "size" => $params['params']
                                                    );
                                                }
                                            } catch (Exception $ex) {
                                                
                                            }
                                        } else {
                                            try {
                                                if (!empty($attachment->item->photo_id)) {
                                                    $sell_photo_ids = explode(" ", rtrim($attachment->item->photo_id));
                                                    foreach ($sell_photo_ids as $sell_photo_id) {
                                                        $attachedImageCount++;
                                                        $photoTable = Engine_Api::_()->getItemTable('album_photo');
                                                        $id = $photoTable->select()
                                                                ->from($photoTable, 'file_id')
                                                                ->where('photo_id = ?', $sell_photo_id)
                                                                ->query()
                                                                ->fetchColumn();
                                                        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($id, 'thumb.main');
                                                        $file1 = Engine_Api::_()->getItemTable('storage_file')->getFile($id, 'thumb.medium');
                                                        $tempArray = array(
                                                            "image_main" => strstr($file->map(), 'http') ? $file->map() : $getHost . $file->map(),
                                                            "image_medium" => strstr($file1->map(), 'http') ? $file1->map() : $getHost . $file1->map()
                                                        );
                                                        $tempAttachmentArray['sell_image'][] = $tempArray;
                                                    }
                                                }
                                            } catch (Exception $ex) {
                                                
                                            }
                                        }
                                    }
                                } else {
                                    if ($attachment->item->getPhotoUrl()) {
                                        $attachedImageCount++;

                                        if ((strstr($attachment->item->getPhotoUrl(), 'http://')) || (strstr($attachment->item->getPhotoUrl(), 'https://'))) {
                                            $getHost = '';
                                        }

                                        $tempAttachmentArray["image_main"] = $getHost . $attachment->item->getPhotoUrl('thumb.main');
//                                        $getimagesize = @getimagesize($imageUrl);
//                                        if (!empty($getimagesize)) {
//                                            $tempAttachmentArray["image_main"] = array(
//                                                "src" => $imageUrl,
//                                                "size" => array("width" => $getimagesize[0], "height" => $getimagesize[1])
//                                            );
//                                        }
//                                        $imageUrl = $getHost . $attachment->item->getPhotoUrl('thumb.icon');
//                                        $getimagesize = @getimagesize($imageUrl);
//                                        if (!empty($getimagesize)) {
//                                            $tempAttachmentArray["image_icon"] = array(
//                                                "src" => $imageUrl,
//                                                "size" => array("width" => $getimagesize[0], "height" => $getimagesize[1])
//                                            );
//                                        }
//                                        $imageUrl = $getHost . $attachment->item->getPhotoUrl('thumb.profile');
//                                        $getimagesize = @getimagesize($imageUrl);
//                                        if (!empty($getimagesize)) {
//                                            $tempAttachmentArray["image_profile"] = array(
//                                                "src" => $imageUrl,
//                                                "size" => array("width" => $getimagesize[0], "height" => $getimagesize[1])
//                                            );
//                                        }
//
//                                        $imageUrl = $getHost . $attachment->item->getPhotoUrl('thumb.normal');
//                                        $getimagesize = @getimagesize($imageUrl);
//                                        if (!empty($getimagesize)) {
//                                            $tempAttachmentArray["image_normal"] = array(
//                                                "src" => $imageUrl,
//                                                "size" => array("width" => $getimagesize[0], "height" => $getimagesize[1])
//                                            );
//                                        }

                                        $tempAttachmentArray["image_medium"] = $getHost . $attachment->item->getPhotoUrl('thumb.medium');
//                                        $getimagesize = @getimagesize($imageUrl);
//                                        if (!empty($getimagesize)) {
//                                            $tempAttachmentArray["image_medium"] = array(
//                                                "src" => $imageUrl,
//                                                "size" => array("width" => $getimagesize[0], "height" => $getimagesize[1])
//                                            );
//                                        }
                                        $getHost = $getPhotoHost;
                                    } else {
                                        $imageData = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($attachment->item, false);
                                        $tempAttachmentArray['image_main'] = $imageData['image_icon'];
                                        $tempAttachmentArray['image_medium'] = $imageData['image_normal'];
                                        $attachedImageCount++;
                                    }
                                }
                            } elseif ($attachment->meta->mode == 3) { // Description Type Only
                                $tempAttachmentArray["description"] = $attachment->item->getDescription();
                            } else if ($attachment->meta->mode == 4) {
                                
                            }
                            $tempAttachmentArray['mode'] = $attachment->meta->mode;

                            if (($attachment->meta->mode == 1) && ($activityFeedArray['feed']['type'] == 'share' || $activityFeedArray['feed']['type'] == 'react') && $tempAttachmentArray['attachment_type'] == 'activity_action') {
                                try {
                                    // Add body in case of share feed
                                    if (isset($attachment->item->body) && !empty($attachment->item->body))
                                        $tempAttachmentArray['body'] = $attachment->item->body;

                                    // Add uri in case of share feed
                                    if ($attachment->item->getHref())
                                        $tempAttachmentArray['uri'] = $tempHost . $attachment->item->getHref();
                                } catch (Exception $ex) {
                                    // Blank Exception
                                }
                            }

                            // Following code added to bypass iOS app crashing issue, when click on photo's
                            // @Todo: Need to remove in next release
                            if (_CLIENT_TYPE && (_CLIENT_TYPE == 'ios')) {
                                try {
                                    if (isset($tempAttachmentArray['attachment_type']) && !empty($tempAttachmentArray['attachment_type']) && isset($tempAttachmentArray['attachment_id']) || !empty($tempAttachmentArray['attachment_id'])) {
                                        if ($tempAttachmentArray['attachment_type'] == 'sitepage_photo') {
                                            $attachmentTempObj = Engine_Api::_()->getItem($tempAttachmentArray['attachment_type'], $tempAttachmentArray['attachment_id']);
                                            if (!empty($attachmentTempObj)) {
                                                if (@method_exists($attachmentTempObj, 'getParent')) {
                                                    $tempAttachmentArray['attachment_type'] = $attachmentTempObj->getParent()->getType();
                                                    $tempAttachmentArray['attachment_id'] = $attachmentTempObj->getParent()->getIdentity();
                                                    if (@method_exists($attachmentTempObj->getParent(), 'getParent')) {
                                                        $tempAttachmentArray['attachment_type'] = $attachmentTempObj->getParent()->getParent()->getType();
                                                        $tempAttachmentArray['attachment_id'] = $attachmentTempObj->getParent()->getParent()->getIdentity();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } catch (Exception $ex) {
                                    // Blank Exception
                                }
                            }

                            if (!isset($tempAttachmentArray['title']) || empty($tempAttachmentArray['title'])) {
                                $tempAttachmentArray['title'] = "";
                            }

                            if (isset($tempAttachmentArray['title']) && !empty($tempAttachmentArray['title'])) {
                                $tempAttachmentArray['title'] = @strip_tags(html_entity_decode($tempAttachmentArray['title'], ENT_QUOTES, "utf-8"));
                            }
                            if (isset($tempAttachmentArray['body']) && !empty($tempAttachmentArray['body'])) {
                                $tempAttachmentArray['body'] = @strip_tags(html_entity_decode($tempAttachmentArray['body'], ENT_QUOTES, "utf-8"));
                            }

                            if (isset($tempAttachmentArray['attachment_type']) && !empty($tempAttachmentArray['attachment_type']) && isset($tempAttachmentArray['attachment_id']) && !empty($tempAttachmentArray['attachment_id']) && ($tempAttachmentArray['attachment_type'] == 'sitegroupreview_review' || $tempAttachmentArray['attachment_type'] == 'sitepagereview_review')) {
                                $attachmentObj = Engine_Api::_()->getItem($tempAttachmentArray['attachment_type'], $tempAttachmentArray['attachment_id']);
                                if (isset($attachmentObj) && !empty($attachmentObj)) {
                                    $parentObjType = $attachmentObj->getParent()->getType();
                                    $parentObjId = $attachmentObj->getParent()->getIdentity();
                                    if (!empty($parentObjType) && $parentObjType == 'sitegroup_group')
                                        $tempAttachmentArray['group_id'] = $parentObjId;
                                    else if (!empty($parentObjType) && $parentObjType == 'sitepage_page')
                                        $tempAttachmentArray['page_id'] = $parentObjId;
                                }
                            }

                            $attachmentArray[] = $tempAttachmentArray;
                        }
                        // Set the attachements
                        $activityFeedArray['feed']['attachment'] = $attachmentArray;
                        $activityFeedArray['feed']['photo_attachment_count'] = !empty($attachedImageCount) ? $attachedImageCount : 0;
                    }
                }
                /* End Attachement Work */

                // Set the feed comment allow permission.
                $canComment = ($action->getTypeInfo()->commentable && $action->commentable &&
                        $viewer->getIdentity() &&
                        Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment'));
                $activityFeedArray['canComment'] = $canLike = !empty($canComment) ? 1 : 0;
                $activityFeedArray['can_comment'] = $canLike = !empty($canComment) ? 1 : 0;

                // Set the feed like allow permission.
                $isLike = $action->likes()->isLike($viewer);
                $activityFeedArray['is_like'] = !empty($isLike) ? 1 : 0;

                //Sitereaction Plugin work start here
                if ($this->isSitereactionPluginLive()) {
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereaction') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereaction.reaction.active', 1)) {
                        $popularity = Engine_Api::_()->getApi('core', 'sitereaction')->getLikesReactionPopularity($action);
                        $feedReactionIcons = Engine_Api::_()->getApi('Siteapi_Core', 'sitereaction')->getLikesReactionIcons($popularity, 1);
                        $activityFeedArray['feed_reactions'] = $feedReactionIcons;

                        if (isset($viewer_id) && !empty($viewer_id)) {
                            $myReaction = $action->likes()->getLike($viewer);
                            if (isset($myReaction) && !empty($myReaction) && isset($myReaction->reaction) && !empty($myReaction->reaction)) {
                                $myReactionIcon = Engine_Api::_()->getApi('Siteapi_Core', 'sitereaction')->getIcons($myReaction->reaction, 1);
                                $activityFeedArray['my_feed_reaction'] = $myReactionIcon;
                            }
                        }
                    }
                }
                //Sitereaction Plugin work end here

                $isShareable = ($action->getTypeInfo()->shareable && $action->shareable && $viewer->getIdentity()) ? 1 : 0;

                /* ------------ START FEED MENU WORK ---------------- */

                if (empty($isLike)) {
                    $activityFooterMenus["like"]["name"] = "like";
                    $activityFooterMenus["like"]["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Like");
                    $activityFooterMenus["like"]["url"] = "like";
                    $activityFooterMenus["like"]['urlParams'] = array(
                        "action_id" => $action->action_id
                    );
                } else {
                    $activityFooterMenus["like"]["name"] = "unlike";
                    $activityFooterMenus["like"]["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Unlike");
                    $activityFooterMenus["like"]["url"] = "unlike";
                    $activityFooterMenus["like"]['urlParams'] = array(
                        "action_id" => $action->action_id
                    );
                }

                $object = $action->getObject();
                $activityFeedArray['can_share'] = 0;
                if ($action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment())) {
                    if ($attachment->item->getType() != 'sitereaction_sticker') {
                        $activityFeedArray['can_share'] = $isShareable;
                        $activityFooterMenus["share"]["name"] = "share";
                        $activityFooterMenus["share"]["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Share");
                        $activityFooterMenus["share"]["url"] = "activity/share";
                        $activityFooterMenus["share"]['urlParams'] = array(
                            "type" => $attachment->item->getType(),
                            "id" => $attachment->item->getIdentity()
                        );
                    }
                } else if ($action->getTypeInfo()->shareable == 2 && isset($subject) && !empty($subject)) {
                    $activityFeedArray['can_share'] = $isShareable;
                    $activityFooterMenus["share"]["name"] = "share";
                    $activityFooterMenus["share"]["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Share");
                    $activityFooterMenus["share"]["url"] = "activity/share";
                    $activityFooterMenus["share"]['urlParams'] = array(
                        "type" => $subject->getType(),
                        "id" => $subject->getIdentity()
                    );
                } elseif ($action->getTypeInfo()->shareable == 3 && isset($object) && !empty($object)) {
                    $activityFeedArray['can_share'] = $isShareable;
                    $activityFooterMenus["share"]["name"] = "share";
                    $activityFooterMenus["share"]["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Share");
                    $activityFooterMenus["share"]["url"] = "activity/share";
                    $activityFooterMenus["share"]['urlParams'] = array(
                        "type" => $object->getType(),
                        "id" => $object->getIdentity()
                    );
                } else if ($action->getTypeInfo()->shareable == 4) {
                    $activityFeedArray['can_share'] = $isShareable;
                    $activityFooterMenus["share"]["name"] = "share";
                    $activityFooterMenus["share"]["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Share");
                    $activityFooterMenus["share"]["url"] = "activity/share";
                    $activityFooterMenus["share"]['urlParams'] = array(
                        "type" => $action->getType(),
                        "id" => $action->getIdentity()
                    );
                }

                // Edit menu Work
                if (($action->getSubject()->getOwner()->getIdentity() == $viewer_id) && (((_CLIENT_TYPE == 'android') && (_ANDROID_VERSION >= '1.6.2' )) || ((_CLIENT_TYPE == 'ios') && (_IOS_VERSION >= '1.4.3')))) {
                    if ($action->canEdit()) {
                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "edit_feed";
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Edit Feed');
                        $tempActivityMenu["url"] = "advancedactivity/edit-feed";
                        $tempActivityMenu['urlParams'] = array(
                            "action_id" => $action->action_id
                        );

                        $activityMenu[] = $tempActivityMenu;
                    }
                }

                if (_ANDROID_VERSION >= '2.3') {
                    $tempActivityMenu = array();
                    $getHostlink = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
                    $feedlink = $item->getHref(array('action_id' => $action->action_id, 'show_comments' => true));
                    $activityFeedArray['feed_link'] = strstr($feedlink, 'http') ? $feedlink : $getHostlink . $feedlink;
                    $tempActivityMenu["name"] = "feed_link";
                    $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Copy Link");
                    $activityMenu[] = $tempActivityMenu;
                    $tempActivityMenu = array();
                    $offNotification = Engine_Api::_()->getDbtable('notificationsettings', 'advancedactivity')->isSetNotificationOff($action->action_id, $viewer_id);
                    $activityFeedArray['isNotificationTurnedOn'] = $offNotification ? false : true;
                    if ($offNotification) {
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Turn On Notification');
                    } else {
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Turn Off Notification');
                    }
                    $tempActivityMenu["name"] = "on_off_notification";

                    $tempActivityMenu["url"] = "advancedactivity/turn-on-off-notification";
                    $tempActivityMenu['urlParams'] = array(
                        "action_id" => $action->action_id
                    );
                    $activityMenu[] = $tempActivityMenu;
                }

                if (empty($getSubject) && !empty($viewer_id) && $action->getTypeInfo()->type != 'birthday_post' && (!$viewer->isSelf($action->getSubject()))) {
                    if (!Engine_Api::_()->core()->hasSubject()) {

                        $add_saved_feed_row = Engine_Api::_()->getDbtable('contents', 'advancedactivity')->getContentList(array('content_tab' => 1, 'filter_type' => 'user_saved'));
                        if (!empty($add_saved_feed_row)) {
                            $activityFeedArray['isSaveFeedOption'] = ($advancedactivitySaveFeed->getSaveFeed($viewer, $action->action_id)) ? 0 : 1;
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "update_save_feed";
                            $tempActivityMenu["label"] = ($advancedactivitySaveFeed->getSaveFeed($viewer, $action->action_id)) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unsave Feed') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save Feed');
                            $tempActivityMenu["url"] = "advancedactivity/update-save-feed";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->getIdentity()
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }

                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "hide";
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Hide");
                        $tempActivityMenu["url"] = "advancedactivity/feeds/hide-item";
                        $tempActivityMenu['urlParams'] = array(
                            "type" => $action->getType(),
                            "id" => $action->getIdentity()
                        );
                        $activityMenu[] = $tempActivityMenu;
                    }

                    $tempActivityMenu = array();
                    $tempActivityMenu["name"] = "report_feed";
                    $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate("Report Feed");
                    $tempActivityMenu["url"] = "advancedactivity/feeds/hide-item";
                    $tempActivityMenu['urlParams'] = array(
                        "type" => $action->getType(),
                        "id" => $action->getIdentity(),
                        "hide_report" => 1
                    );
                    $activityMenu[] = $tempActivityMenu;

                    // Remove Hide all menu due to UI issues in app
//                    if (!Engine_Api::_()->core()->hasSubject()) {
//                        $item = (isset($action->getTypeInfo()->is_object_thumb) && !empty($action->getTypeInfo()->is_object_thumb)) ? $action->getObject() : $action->getSubject();
//                        $tempActivityMenu = array();
//                        $tempActivityMenu["name"] = "hits_feed";
//                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Hide all by ') . $item->getTitle();
//                        $tempActivityMenu["url"] = "advancedactivity/feeds/hide-item";
//                        $tempActivityMenu['urlParams'] = array(
//                            "type" => $item->getType(),
//                            "id" => $item->getIdentity()
//                        );
//                        $activityMenu[] = $tempActivityMenu;
//                    }

                    if ($viewer_id && (
                            $data['activity_moderate'] || $data['is_owner'] || (
                            $data['allow_delete'] && (
                            ('user' == $action->subject_type && $viewer_id == $action->subject_id) ||
                            ('user' == $action->object_type && $viewer_id == $action->object_id)
                            )
                            )
                            )) {

                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "delete_feed";
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete Feed');
                        $tempActivityMenu["url"] = "advancedactivity/delete";
                        $tempActivityMenu['urlParams'] = array(
                            "action_id" => $action->action_id
                        );


                        $activityMenu[] = $tempActivityMenu;


                        if ($action->getTypeInfo()->commentable) {
                            // Disable Comment
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "disable_comment";
                            $tempActivityMenu["label"] = ($action->commentable) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Disable Comments') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Enable Comments');
                            $tempActivityMenu["url"] = "advancedactivity/update-commentable";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->action_id
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }

                        if ($action->getTypeInfo()->shareable > 1 || ($action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment()))) {
                            // Lock this Feed
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "lock_this_feed";
                            $tempActivityMenu["label"] = ($action->shareable) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Lock this Feed') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unlock this Feed');
                            $tempActivityMenu["url"] = "advancedactivity/update-shareable";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->action_id
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }
                    }
                } elseif (Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.post.canedit', 1) && !empty($action->privacy) && in_array($action->getTypeInfo()->type, array("post", "post_self", "status", 'sitetagcheckin_add_to_map', 'sitetagcheckin_content', 'sitetagcheckin_status', 'sitetagcheckin_post_self', 'sitetagcheckin_post', 'sitetagcheckin_checkin', 'sitetagcheckin_lct_add_to_map', 'post_self_photo', 'post_self_video', 'post_self_music', 'post_self_link')) && $viewer->getIdentity() && (('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id))) {
                    if (!empty($data['allowSaveFeed']) && $viewer_id) {
                        $activityFeedArray['isSaveFeedOption'] = ($advancedactivitySaveFeed->getSaveFeed($viewer, $action->action_id)) ? 0 : 1;
                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "update_save_feed";
                        $tempActivityMenu["label"] = ($advancedactivitySaveFeed->getSaveFeed($viewer, $action->action_id)) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unsave Feed') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save Feed');
                        $tempActivityMenu["url"] = "advancedactivity/update-save-feed";
                        $tempActivityMenu['urlParams'] = array(
                            "action_id" => $action->getIdentity()
                        );
                        $activityMenu[] = $tempActivityMenu;
                    }

                    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
                    $allowToDelete = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete');


                    if (Engine_Api::_()->core()->hasSubject()) {
                        $subject = Engine_Api::_()->core()->getSubject();
                        $is_owner = $viewer->isSelf($subject);
                    }

                    if ($activity_moderate || $allowToDelete || $is_owner) {
                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "delete_feed";
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete Feed');
                        $tempActivityMenu["url"] = "advancedactivity/delete";
                        $tempActivityMenu['urlParams'] = array(
                            "action_id" => $action->action_id
                        );
                        $activityMenu[] = $tempActivityMenu;

                        if (_ANDROID_VERSION >= '2.3') {
                            //pin unpin gutter menu
                            try {
                                if (!empty($_REQUEST['subject_type']) && !empty($_REQUEST['subject_id'])) {
                                    $itemSubject = Engine_Api::_()->getItem($_REQUEST['subject_type'], $_REQUEST['subject_id']);
                                    if (!empty($itemSubject)) {
                                        if ($itemSubject->getGuid() == $action->getObject()->getGuid()) {
                                            $tempActivityMenu = array();
                                            $activityFeedArray['pin_post_duration'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.pin.reset.days', 7);
                                            $pinTable = Engine_APi::_()->getDbTable('pinsettings', 'advancedactivity');
                                            $alreadyPin = $pinTable->select()
                                                    ->where('action_id = ? ', $action->action_id)
                                                    ->query()
                                                    ->fetchColumn();

                                            if (!empty($alreadyPin)) {
                                                $activityFeedArray['isPinned'] = true;
                                                $tempActivityMenu["name"] = "unpin_post";
                                                $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unpin This Post');
                                            } else {
                                                $activityFeedArray['isPinned'] = false;
                                                $tempActivityMenu["name"] = "pin_post";
                                                $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Pin This Post');
                                            }

                                            $tempActivityMenu["url"] = "advancedactivity/pin-unpin";
                                            $tempActivityMenu['urlParams'] = array(
                                                "action_id" => $action->action_id,
                                                "type" => $action->getObject()->getGuid()
                                            );
                                            $activityMenu[] = $tempActivityMenu;
                                        }
                                    }
                                }
                            } catch (Exception $ex) {
                                
                            }
                        }
                        if ($action->getTypeInfo()->commentable) {
                            // Disable Comment
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "disable_comment";
                            $tempActivityMenu["label"] = ($action->commentable) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Disable Comments') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Enable Comments');
                            $tempActivityMenu["url"] = "advancedactivity/update-commentable";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->action_id
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }

                        if ($action->getTypeInfo()->shareable > 1 || ($action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment()))) {
                            // Lock this Feed
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "lock_this_feed";
                            $tempActivityMenu["label"] = ($action->shareable) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Lock this Feed') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unlock this Feed');
                            $tempActivityMenu["url"] = "advancedactivity/update-shareable";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->action_id
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }
                    }
                } else {
                    if (!empty($data['allowSaveFeed']) && $viewer_id) {
                        $activityFeedArray['isSaveFeedOption'] = ($advancedactivitySaveFeed->getSaveFeed($viewer, $action->action_id)) ? 0 : 1;
                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "update_save_feed";
                        $tempActivityMenu["label"] = ($advancedactivitySaveFeed->getSaveFeed($viewer, $action->action_id)) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unsave Feed') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Save Feed');
                        $tempActivityMenu["url"] = "advancedactivity/update-save-feed";
                        $tempActivityMenu['urlParams'] = array(
                            "action_id" => $action->getIdentity()
                        );
                        $activityMenu[] = $tempActivityMenu;
                    }


                    if (Engine_Api::_()->core()->hasSubject()) {
                        $subject = Engine_Api::_()->core()->getSubject();
                        $is_owner = $viewer->isSelf($subject);
                    }

                    if (isset($viewer) && !empty($viewer) && isset($action) && !empty($action))
                        $is_owner = $viewer->isSelf($action->getSubject());

                    if (!empty($is_owner) || (isset($viewer->level_id) && ($viewer->level_id == 1))) {
                        $tempActivityMenu = array();
                        $tempActivityMenu["name"] = "delete_feed";
                        $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete Feed');
                        $tempActivityMenu["url"] = "advancedactivity/delete";
                        $tempActivityMenu['urlParams'] = array(
                            "action_id" => $action->action_id
                        );
                        $activityMenu[] = $tempActivityMenu;

                        if (_ANDROID_VERSION >= '2.3') {
                            try {
                                if (!empty($_REQUEST['subject_type']) && !empty($_REQUEST['subject_id'])) {
                                    $itemSubject = Engine_Api::_()->getItem($_REQUEST['subject_type'], $_REQUEST['subject_id']);
                                    if (!empty($itemSubject)) {
                                        if ($itemSubject->getGuid() == $action->getObject()->getGuid()) {
                                            $tempActivityMenu = array();
                                            $activityFeedArray['pin_post_duration'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.pin.reset.days', 7);
                                            $pinTable = Engine_APi::_()->getDbTable('pinsettings', 'advancedactivity');
                                            $alreadyPin = $pinTable->select()
                                                    ->where('action_id = ? ', $action->action_id)
                                                    ->query()
                                                    ->fetchColumn();

                                            if (!empty($alreadyPin)) {
                                                $activityFeedArray['isPinned'] = true;
                                                $tempActivityMenu["name"] = "unpin_post";
                                                $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unpin This Post');
                                            } else {
                                                $activityFeedArray['isPinned'] = false;
                                                $tempActivityMenu["name"] = "pin_post";
                                                $tempActivityMenu["label"] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Pin This Post');
                                            }

                                            $tempActivityMenu["url"] = "advancedactivity/pin-unpin";
                                            $tempActivityMenu['urlParams'] = array(
                                                "action_id" => $action->action_id,
                                                "type" => $action->getObject()->getGuid()
                                            );
                                            $activityMenu[] = $tempActivityMenu;
                                        }
                                    }
                                }
                            } catch (Exception $ex) {
                                
                            }
                        }
                        if ($action->getTypeInfo()->commentable) {
                            // Disable Comment
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "disable_comment";
                            $tempActivityMenu["label"] = ($action->commentable) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Disable Comments') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Enable Comments');
                            $tempActivityMenu["url"] = "advancedactivity/update-commentable";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->action_id
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }

                        if ($action->getTypeInfo()->shareable > 1 || ($action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment()))) {
                            // Lock this Feed
                            $tempActivityMenu = array();
                            $tempActivityMenu["name"] = "lock_this_feed";
                            $tempActivityMenu["label"] = ($action->shareable) ? Engine_Api::_()->getApi('Core', 'siteapi')->translate('Lock this Feed') : Engine_Api::_()->getApi('Core', 'siteapi')->translate('Unlock this Feed');
                            $tempActivityMenu["url"] = "advancedactivity/update-shareable";
                            $tempActivityMenu['urlParams'] = array(
                                "action_id" => $action->action_id
                            );
                            $activityMenu[] = $tempActivityMenu;
                        }
                    }
                }


                if (!empty($viewer_id)) {
                    $activityFeedArray['feed_menus'] = $activityMenu;
                    $activityFeedArray['feed_footer_menus'] = $activityFooterMenus;
                }

                // Set Feed Title
                $activityFeedArray['feed']['feed_title'] = $this->getContent($action);

                // Set activity feed type - body and these params array. So that Feed Title could be create at dynamically APP side.
                $activityFeedArray['feed']['action_type_body'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate($getFeedTypeInfo['body']);
                $activityFeedArray['feed']['action_type_body'] = @strtolower($activityFeedArray['feed']['action_type_body']);
                $this->_idBodyContentAvailable = false;
                $activityFeedArray['feed']['action_type_body_params'] = $this->getContent($action, 1);
                $final_action_type_body = $this->_actionTypeBody($activityFeedArray['feed']['action_type_body'], $activityFeedArray['feed']['action_type_body_params'], $action);

                $final_action_type_body = preg_replace('/\s+/', ' ', $final_action_type_body);

                $activityFeedArray['feed']['isRequired'] = 0;
                //Added Code to decrease the complexity from iOS end. Need to remove it in the next release
                if ($activityFeedArray['feed']['type'] == 'album_photo_new') {
                    if (strstr($activityFeedArray['feed']['action_type_body'], '{var:$count}') && isset($activityFeedArray['feed']['photo_attachment_count']) && !empty($activityFeedArray['feed']['photo_attachment_count']) && isset($activityFeedArray['feed']['action_type_body_params']) && !empty($activityFeedArray['feed']['action_type_body_params'])) {
                        foreach ($activityFeedArray['feed']['action_type_body_params'] as $key => $paramArray) {
                            if (isset($paramArray['search']) && !empty($paramArray['search']) && strstr($paramArray['search'], '{var:$count}')) {
                                continue;
                            } else {
                                $modifiedActionTypeBodyParams[] = $paramArray;
                            }
                        }
                        if (isset($modifiedActionTypeBodyParams) && !empty($modifiedActionTypeBodyParams)) {
                            $activityFeedArray['feed']['action_type_body_params'] = $modifiedActionTypeBodyParams;
                            $final_action_type_body = str_replace('{var:$count}', $activityFeedArray['feed']['photo_attachment_count'], $final_action_type_body);
                        }
                    }
                }

                $activityFeedArray['feed']['action_type_body'] = $final_action_type_body;
                if (isset($activityFeedArray['feed']['params']['feelings']) && !empty($activityFeedArray['feed']['params']['feelings'])) {
                    $activityFeedArray['feed']['isRequired'] = 1;
                    $activityFeedArray['feed']['is_translation'] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('is');
                    if (strstr($activityFeedArray['feed']['action_type_body'], '{item:$subject} is')) {
                        $activityFeedArray['feed']['action_type_body'] = str_replace('{item:$subject} is', '{item:$subject}', $activityFeedArray['feed']['action_type_body']);
                    }
                }

                // @Todo: Following code should be modified and move in "getContent()" method.
                if (!empty($this->_idBodyContentAvailable)) {
                    $tempBodyArray = $finalArray = array();
                    $getActionTypeBodyParams = $activityFeedArray['feed']['action_type_body_params'];
                    $getDefaultKey = false;
                    foreach ($getActionTypeBodyParams as $key => $paramArray) {
                        if ($paramArray['search'] == '{body:$body}') {
                            // Create temporary body array.
                            $tempBodyArray[] = $paramArray;
//                            if (!empty($getDefaultKey))
//                                $finalArray[$getDefaultKey] = $paramArray;
//                            else
//                                $finalArray[] = $paramArray;
//
//                            $getDefaultKey = $key;
                        } else {
                            if (isset($paramArray['label']) && !empty($paramArray['label']) && !strstr($paramArray['label'], " ") && strstr($paramArray['label'], "_")) {
                                try {
                                    if (isset($paramArray['type']) && isset($paramArray['id']) && !empty($paramArray['type']) && !empty($paramArray['id'])) {
                                        $getTempObj = Engine_Api::_()->getItem($paramArray['type'], $paramArray['id']);
                                        if ($getTempObj->getTitle())
                                            $paramArray['label'] = $getTempObj->getTitle();
                                    }else {
                                        $paramArray['label'] = "";
                                    }
                                } catch (Exception $ex) {
                                    $paramArray['label'] = "";
                                }
                            }

                            $finalArray[] = $paramArray;
                        }
                    }

                    // Set last value of temporary array to final array
                    if (!empty($tempBodyArray))
                        $finalArray[] = @end($tempBodyArray);

                    $activityFeedArray['feed']['action_type_body_params'] = $finalArray;
                }

                /* ------------ END FEED MENU WORK ---------------- */
            } catch (Exception $e) {
                
            }
            if (!empty($activityFeedArray))
                $activityFeed[] = $activityFeedArray;
        }

        return $activityFeed;
    }

    protected $_idBodyContentAvailable = false;

    /**
     * Feed Title
     *
     * @return array
     */
    private function getContent($action, $flag = false) {
        $params = array_merge(
                $action->toArray(), (array) $action->params, array(
            'subject' => $action->getSubject(),
            'object' => $action->getObject()
                )
        );

        $params['actionObj'] = $action;
        $params['flag'] = $flag;

        $content = $this->assemble($action->getTypeInfo()->body, $params);
        return $content;
    }

    protected $_pluginLoader;

    /**
     * Feed Title - Load the Plugins
     *
     * @return array
     */
    private function getPluginLoader() {
        if (null === $this->_pluginLoader) {
            $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
                    . 'modules' . DIRECTORY_SEPARATOR
                    . 'Activity';
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Activity_Model_Helper_' => $path . '/Model/Helper/'
            ));
        }

        return $this->_pluginLoader;
    }

    /**
     * Normalize helper name
     * 
     * @param string $name
     * @return string
     */
    private function _normalizeHelperName($name) {
        $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
        $name = ucfirst($name);
        return $name;
    }

    /*
     *  Returns a multidimentional array with hashtags separated for separate action_id
     * 
     * @param $action object
     * @return array
     */

    private function getHashtagNames($action) {

        $hashTagMapTable = Engine_Api::_()->getDbtable('tagmaps', 'sitehashtag');
        $hashtagNames = array();
        preg_match_all("/\B#\w*[a-zA-Z]+\w*/", $action->body, $hashtags);
        $hashtagName = array();
        $hashtagmaps = $hashTagMapTable->getActionTagMaps($action->action_id);
        foreach ($hashtagmaps as $hashtagmap) {
            $tag = Engine_Api::_()->getItem('sitehashtag_tag', $hashtagmap->tag_id);
            if ($tag && !in_array($tag->text, $hashtags[0])) {
                $hashtagName[] = $tag->text;
            }
        }
        return $hashtagName;
    }

    /*
     * Get review information
     * 
     * @param $attachementType string
     * @param $attachmentId in
     * @return array
     * 
     */

    private function _getSitereviewInfo($attachementType, $attachmentId, $photo_id = 0) {
        if (empty($attachementType) || empty($attachmentId))
            return;

        if (strstr($attachementType, 'sitereview_wishlist')) {
            $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $attachmentId);

            if (isset($wishlist) && !empty($wishlist)) {
                $wishlistListing = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->wishlistListings($wishlist->wishlist_id);
                $sitereviewInfo['count'] = $wishlistListing->getTotalItemCount();
            }
        } else if (strstr($attachementType, 'sitereview_review')) {
            $review = Engine_Api::_()->getItem('sitereview_review', $attachmentId);
            $sitereview = $review->getParent();
            $listing_id = $sitereview->getIdentity();
            if (isset($review) && !empty($review) && !empty($listing_id)) {
                $reviewParams = array();
                $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
                $reviewParams['resource_id'] = $sitereview->getIdentity();
                $reviewParams['resource_type'] = 'sitereview_listing';
                $reviewParams['type'] = 'user';
                $sitereviewInfo['listing_title'] = $sitereview->getTitle();
                $sitereviewInfo['listing_id'] = $listing_id;
                $sitereviewInfo['count'] = $reviewTable->totalReviews($reviewParams);
                $sitereviewInfo['listingtype_id'] = $sitereview->listingtype_id;
            }
        } else if (strstr($attachementType, 'sitereview_listing')) {
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $attachmentId);

            if (isset($sitereview) && !empty($sitereview))
                $sitereviewInfo['listingtype_id'] = $sitereview->listingtype_id;
        }else if (strstr($attachementType, 'sitereview_photo') && isset($photo_id) && !empty($photo_id)) {
            $sitereviewPhotoObj = Engine_Api::_()->getItem('sitereview_photo', $photo_id);
            if (isset($sitereviewPhotoObj) && !empty($sitereviewPhotoObj))
                $sitereviewId = $sitereviewPhotoObj->listing_id;
            if (isset($sitereviewId) && !empty($sitereviewId))
                $sitereview = Engine_Api::_()->getItem('sitereview_listing', $sitereviewId);
            if (isset($sitereview) && !empty($sitereview)) {
                $listing_id = $sitereview->getIdentity();
                $sitereviewInfo['listing_id'] = $listing_id;
                $sitereviewInfo['listingtype_id'] = $sitereview->listingtype_id;
            }
        }

        if (isset($sitereviewInfo) && !empty($sitereviewInfo))
            return $sitereviewInfo;
    }

    /*
     * Get attached image menu according to module information
     * 
     * @param $subject string
     * @param $tempAttachmentArray array
     * @return array
     * 
     */

    private function _getPhotoMenuUrl($subject, $tempAttachmentArray) {
        $menu = array();
        $type = $subject->getType();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

//        if ($type == 'sitepage_photo') {
//            $parentObject = $subject->getParent();
//            $page_id = $parentObject->getIdentity();
//
//            if (!empty($parentObject) && !empty($page_id))
//                $canEdit = Engine_Api::_()->sitepage()->isManageAdmin($parentObject, 'edit');
//
//            if (!empty($canEdit)) {
//                $menu[] = array(
//                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Edit'),
//                    'name' => 'edit',
//                    'url' => 'sitepage/photos/editphoto/' . $page_id . "/" . $tempAttachmentArray["album_id"] . "/" . $tempAttachmentArray["photo_id"],
//                );
//
//                $menu[] = array(
//                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete'),
//                    'name' => 'delete',
//                    'url' => 'sitepage/photos/deletephoto/' . $page_id . "/" . $tempAttachmentArray["album_id"] . "/" . $tempAttachmentArray["photo_id"],
//                );
//            }
//        } else if ($type == 'sitegroup_photo') {
//            $parentObject = $subject->getParent();
//            $group_id = $parentObject->getIdentity();
//
//            if (!empty($parentObject) && !empty($group_id))
//                $canEdit = Engine_Api::_()->sitegroup()->isManageAdmin($parentObject, 'edit');
//
//            if (!empty($canEdit)) {
//                $menu[] = array(
//                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Edit'),
//                    'name' => 'edit',
//                    'url' => 'advancedgroups/photos/editphoto/' . $group_id . "/" . $tempAttachmentArray["album_id"] . "/" . $tempAttachmentArray["photo_id"],
//                );
//
//                $menu[] = array(
//                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete'),
//                    'name' => 'delete',
//                    'url' => 'advancedgroups/photos/deletephoto/' . $group_id . "/" . $tempAttachmentArray["album_id"] . "/" . $tempAttachmentArray["photo_id"],
//                );
//            }
//        } elseif ($type == 'siteevent_photo') {
//            $canEdit = $subject->canEdit($viewer);
//            if (!empty($canEdit)) {
//                $menu[] = array(
//                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Edit'),
//                    'name' => 'edit',
//                    'url' => 'advancedevents/photo/edit/' . $tempAttachmentArray["photo_id"],
//                );
//
//                $menu[] = array(
//                    'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete'),
//                    'name' => 'delete',
//                    'url' => 'advancedevents/photo/delete/' . $tempAttachmentArray["photo_id"],
//                );
//            }
//        } elseif ($type == 'sitereview_photo') {
//            $parentObject = $subject->getParent();
//            if (isset($subject->user_id) && !empty($subject->user_id) && !empty($parentObject) && $subject->user_id == $viewer_id) {
//                $menu[] = array(
//                    'label' => $this->translate('Edit'),
//                    'name' => 'edit',
//                    'url' => 'listings/photo/edit/' . $parentObject->getIdentity(),
//                    'urlParams' => array(
//                        "photo_id" => $tempAttachmentArray["photo_id"]
//                    )
//                );
//
//                $menu[] = array(
//                    'label' => $this->translate('Delete'),
//                    'name' => 'delete',
//                    'url' => 'listings/photo/delete/' . $parentObject->getIdentity(),
//                    'urlParams' => array(
//                        "photo_id" => $tempAttachmentArray["photo_id"]
//                    )
//                );
//            }
//        } else {
//            try {
//                $canEdit = $subject->authorization()->isAllowed($viewer, 'edit');
//            } catch (Exception $ex) {
//                $canEdit = 0;
//            }
//
//            if (!empty($canEdit)) {
//                $attachmentType = $tempAttachmentArray['attachment_type'];
//                $attachmentArrayName = explode("_", $attachmentType);
//                $moduleName = $attachmentArrayName[0] . 's';
//
//                if (!empty($moduleName)) {
//                    $menu[] = array(
//                        'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Edit'),
//                        'name' => 'edit',
//                        'url' => $moduleName . '/photo/edit',
//                        'urlParams' => array(
//                            "album_id" => $tempAttachmentArray["album_id"],
//                            "photo_id" => $tempAttachmentArray["photo_id"]
//                        )
//                    );
//
//                    $menu[] = array(
//                        'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Delete'),
//                        'name' => 'delete',
//                        'url' => $moduleName . '/photo/delete',
//                        'urlParams' => array(
//                            "album_id" => $tempAttachmentArray["album_id"],
//                            "photo_id" => $tempAttachmentArray["photo_id"]
//                        )
//                    );
//                }
//            }
//        }

        $menu[] = array(
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Share'),
            'name' => 'share',
            'url' => 'activity/share',
            'urlParams' => array(
                "type" => $subject->getType(),
                "id" => $tempAttachmentArray["photo_id"]
            )
        );

        $menu[] = array(
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Report'),
            'name' => 'report',
            'url' => 'report/create/subject/' . $subject->getGuid(),
            'urlParams' => array(
                "type" => $subject->getType(),
                "id" => $tempAttachmentArray["photo_id"]
            )
        );

        $menu[] = array(
            'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Make Profile Photo'),
            'name' => 'make_profile_photo',
            'url' => 'members/edit/external-photo',
            'urlParams' => array(
                "photo" => $subject->getGuid()
            )
        );

        return $menu;
    }

    public function isSitereactionPluginLive() {
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereaction')) {
            $reactionModule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitereaction');
            $reactionModule = $reactionModule->version;
            if (version_compare($reactionModule, '4.8.12p4', '>=')) {

                if ((_ANDROID_VERSION && _ANDROID_VERSION >= '1.7.5') || (_IOS_VERSION && _IOS_VERSION >= '1.6.4')) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isSitestickerPluginLive() {
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereaction')) {
            $reactionModule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitereaction');
            $reactionModule = $reactionModule->version;
            if (version_compare($reactionModule, '4.8.12p4', '>=')) {

                if ((_ANDROID_VERSION && _ANDROID_VERSION >= '1.7.6') || (_IOS_VERSION && _IOS_VERSION >= '1.6.8')) {
                    return true;
                }
            }
        }
        return false;
    }

    private function _getPhotoReaction($subject) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if (!isset($subject) || empty($subject))
            return;

        try {

            //Sitereaction Plugin work start here
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereaction') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereaction.reaction.active', 1)) {
                $popularity = Engine_Api::_()->getApi('core', 'sitereaction')->getLikesReactionPopularity($subject);
                $feedReactionIcons = Engine_Api::_()->getApi('Siteapi_Core', 'sitereaction')->getLikesReactionIcons($popularity, 1);
                $response['feed_reactions'] = $feedReactionIcons;

                if (isset($viewer_id) && !empty($viewer_id)) {
                    $myReaction = $subject->likes()->getLike($viewer);
                    if (isset($myReaction) && !empty($myReaction) && isset($myReaction->reaction) && !empty($myReaction->reaction)) {
                        $myReactionIcon = Engine_Api::_()->getApi('Siteapi_Core', 'sitereaction')->getIcons($myReaction->reaction, 1);
                        $response['my_feed_reaction'] = $myReactionIcon;
                    }
                }
            }
            return $response;
        } catch (Exception $ex) {
            return;
        }
        //Sitereaction Plugin work end here
    }

    // To do for site video
    public function videoSource() {
        $viewer = Engine_Api::_()->user()->getViewer();
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitevideo')) {
            try {
                $allowedSources = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitevideo.allowed.video', array(1, 2, 3, 4, 5, 6));
                $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
                $allowedSources_level = $permissionsTable->getAllowed('video', Engine_Api::_()->user()->getViewer()->level_id, 'source');
                $allowedSources_level = array_flip($allowedSources_level);
                $allowedSources = array_flip($allowedSources);
                $coreSettings = Engine_Api::_()->getApi('settings', 'core');
                $key = $coreSettings->getSetting('video.youtube.apikey');
                if (isset($allowedSources[1]) && $key && isset($allowedSources_level[1])) {
                    $video_options[1] = 'YouTube';
                }
                if (isset($allowedSources[2]) && isset($allowedSources_level[2]))
                    $video_options[2] = 'Vimeo';
                if (isset($allowedSources[3]) && isset($allowedSources_level[3]))
                    $video_options[4] = 'Dailymotion';

                //My Computer
                $allowed_upload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'create');
                $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->sitevideo_ffmpeg_path;
                if (isset($allowedSources[4]) && !empty($ffmpeg_path) && $allowed_upload && isset($allowedSources_level[4])) {
                    if (Engine_Api::_()->hasModuleBootstrap('mobi') && Engine_Api::_()->mobi()->isMobile()) {
                        $video_options[3] = 'My Device';
                    } else {
                        $video_options[3] = 'My Device';
                    }
                }
                if (isset($allowedSources[6]) && isset($allowedSources_level[6])) {
                    $video_options[6] = 'External Sites';
                }
            } catch (Exception $ex) {
                
            }
        } else {
            $video_options[1] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('YouTube');
            $video_options[2] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('Vimeo');

            //My Computer
            $allowed_upload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'upload');
            $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
            if (!empty($ffmpeg_path) && $allowed_upload) {
                $video_options[3] = Engine_Api::_()->getApi('Core', 'siteapi')->translate('My Device');
            }
        }

        return $video_options;
    }

    public function tagUserArray($param = array()) {
        $users = array();
        $tagUsers = array();
        if (isset($param) && !empty($param)) {
            if (is_array($param) && count($param) > 0 && isset($param['tags'])) {
                $tagUsers = $param['tags'];
            } else if (!is_array($param)) {
                $temp = json_decode($param);
                if (isset($temp->tags) && !empty($temp->tags))
                    $tagUsers = $temp->tags;
            }


            foreach ($tagUsers as $key => $userName) {
                $user = array();
                $user = explode('_', $key);
                $users[] = array(
                    "resource_name" => $userName,
                    "resource_id" => $user[1],
                    "type" => $user[0]
                );
            }
        }

        return $users;
    }

    public function formatActionParam($param = array()) {
        $users = array();
        try {

            $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
            if (isset($param) && !empty($param)) {
                if (is_array($param) && count($param) > 0) {
                    $banner = $param['feed-banner'];
                    if (empty($banner)) {
                        $param['feed-banner'] = '';
                        return $param;
                    }
                } else {
                    $temp = json_decode($param);
                    if (!empty($temp['feed-banner']))
                        $banner = $temp['feed-banner'];
                    else {
                        $param['feed-banner'] = '';
                        return $param;
                    }
                }
                if (empty($banner['image'])) {
                    $param['feed-banner'] = '';
                    return $param;
                }
                $tempurl = str_replace('url(', '', $banner['image']);
                $finalurl = str_replace(')', '', $tempurl);
                if (!empty($finalurl)) {
                    if (strstr($finalurl, "radial-gradient"))
                        $param['feed-banner']['feed_banner_url'] = "";
                    else {
                        $param['feed-banner']['feed_banner_url'] = strstr($finalurl, "http") ? $finalurl : $getHost . $finalurl;
                        $param['feed-banner']['feed_banner_url'] = preg_replace('/\s+/', '', $param['feed-banner']['feed_banner_url']);
                    }
                } else {
                    $param['feed-banner']['feed_banner_url'] = '';
                }

                //$param =json_encode($param);
                return $param;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function formatFeelingParam($param = array()) {
        $users = array();
        try {

            $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
            if (isset($param) && !empty($param)) {
                if (is_array($param) && count($param) > 0) {
                    $feelings = $param['feelings'];
                    if (empty($feelings))
                        return false;
                } else {
                    $temp = json_decode($param);
                    if (!empty($temp['feelings']))
                        $feelings = $temp['feelings'];
                    else
                        return false;
                }
                if (!empty($feelings['parent'])) {
                    $table = Engine_Api::_()->getDbtable('feelingtypes', 'advancedactivity');
                    $select = $table->select($table->info('name'))
                            ->where("enabled =?", 1)
                            ->where("feelingtype_id=?", $feelings['parent'])
                            ->order('order ASC');
                    $feelingtype = $table->fetchRow($select);
                }
                if (!empty($feelingtype)) {
                    $param['feelings']['parenttitle'] = $feelingtype->getTitle();
                    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($feelingtype->photo_id, 'thumb.icon');
                    $photoParent = $file->map();

                    $photoParent = strstr($photoParent, 'http') ? $photoParent : $getHost . $photoParent;
                    $param['feelings']['iconUrl'] = $photoParent;
                }

                if (!empty($feelings['child']) && $feelings['child'] != 'custom_feeling') {

                    $feelingTable = Engine_Api::_()->getItemTable('advancedactivity_feeling');
                    $select = $feelingTable->select()
                            ->where('feelingtype_id = ?', $feelings['parent'])
                            ->where("feeling_id=?", $feelings['child'])
                            ->order('order ASC');
                    $feelingObj = $feelingTable->fetchRow($select);
                    if (!empty($feelingObj)) {
                        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($feelingObj->file_id, 'thumb.icon');
                        $photoChild = $file->map();

                        $photoChild = strstr($photoChild, 'http') ? $photoChild : $getHost . $photoChild;
                        $param['feelings']['iconUrl'] = $photoChild;
                    }
                }
                return $param;
            }
        } catch (Exception $ex) {
            // echo $ex;die;
        }
    }

    public function videoType($type) {
        switch ($type) {
            case 1:
            case 'youtube':
                return 1;
            case 2:
            case 'vimeo':
                return 2;
            case 3:
            case 'mydevice':
            case 'upload' :
                return 3;
            case 4:
            case 'dailymotion':
                return 4;
            case 5:
            case 'embedcode':
                return 5;
            case 6;
            case 'iframely':
                return 6;
            default : return $type;
        }
    }

    private function _actionTypeBody($action_type_body, $action_type_body_params, $action) {
        $params = array_merge(
                $action->toArray(), (array) $action->params, array(
            'subject' => $action->getSubject(),
            'object' => $action->getObject()
                )
        );
        $search = array();
        foreach ($action_type_body_params as $key => $value) {
            $search[] = $value['search'];
        }
        $body = $action->getTypeInfo()->body;
        preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);
        foreach ($matches as $key => $match) {
            $tag = $match[0];
            if (!in_array($tag, $search)) {
                $action_type_body = str_replace($tag, ' ', $action_type_body);
            }
        }
        return $action_type_body;
    }

    public function getPhotoUrl($item, $type = null) {
        $params = array();
        $params['width'] = 0;
        $params['height'] = 0;
//        if ($item->getType() == 'core_link') {
//            $array = $this->_preview($item->uri);
//            if (!empty($array))
//                return $array;
//        }
        if ($item->getType() == 'album' || $item->getType() == 'sitealbum') {
            if (empty($this->photo_id)) {
                $photoTable = Engine_Api::_()->getItemTable('album_photo');
                $photoInfo = $photoTable->select()
                        ->from($photoTable, array('photo_id', 'file_id'))
                        ->where('album_id = ?', $item->album_id)
                        ->order('order ASC')
                        ->limit(1)
                        ->query()
                        ->fetch();
                if (!empty($photoInfo)) {
                    $item->photo_id = $photo_id = $photoInfo['photo_id'];
                    $item->save();
                    $id = $photoInfo['file_id'];
                } else {
                    $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($item);
                    $array = array(
                        'url' => $getContentImages['image_profile'],
                        'params' => $params
                    );
                    return $array;
                }
            } else {
                $photoTable = Engine_Api::_()->getItemTable('album_photo');
                $id = $photoTable->select()
                        ->from($photoTable, 'file_id')
                        ->where('photo_id = ?', $item->photo_id)
                        ->query()
                        ->fetchColumn();
            }
        } elseif (strstr($item->getType(), 'video')) {
            if (!strstr($item->getType(), 'sitevideo_channel') && !strstr($item->getType(), 'playlist'))
                $id = $item->photo_id;
            else {
                $id = $item->file_id;
            }
        } elseif (strstr($item->getType(), 'music')) {
            $playlist = Engine_Api::_()->getItem('music_playlist', $item->playlist_id);
            if (!empty($playlist))
                $id = $playlist->photo_id;
            else {
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($item);
                $array = array(
                    'url' => $getContentImages['image_profile'],
                    'params' => $params
                );
                return $array;
            }
        } elseif (empty($item->file_id) && empty($item->photo_id)) {
            if (!isset($item->file_id) && !isset($item->photo_id)) {
                return array();
            }
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($item);
            $array = array(
                'url' => $getContentImages['image_profile'],
                'params' => $params
            );
            return $array;
        } elseif (!empty($item->file_id)) {
            $id = $item->file_id;
        } else
            $id = $item->photo_id;

        if (empty($id)) {
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($item);
            $array = array(
                'url' => $getContentImages['image_profile'],
                'params' => $params
            );
            return $array;
        }

        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($id, $type);
        if (!$file) {
            $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($item);
            $array = array(
                'url' => $getContentImages['image_profile'],
                'params' => $params
            );
            return $array;
        }

        If (isset($file->params) && !empty($file->params)) {
            $paramsString = $file->params;
        }
        if (isset($paramsString) && !empty($paramsString)) {
            $params = Zend_Json_Decoder::decode($paramsString);
        }
        $array = array(
            'url' => $file->map(),
            'params' => $params
        );
        return $array;
    }

    public function getVideoURL($video, $autoplay = true) {
// YouTube
        if ($video->type == 1 || $video->type == 'youtube') {
            return 'www.youtube.com/embed/' . $video->code . '?wmode=opaque' . ($autoplay ? "&autoplay=1" : "");
        } elseif ($video->type == 2 || $video->type == 'vimeo') { // Vimeo
            return 'player.vimeo.com/video/' . $video->code . '?title=0&amp;byline=0&amp;portrait=0&amp;wmode=opaque' . ($autoplay ? "&amp;autoplay=1" : "");
        } elseif ($video->type == 4 || $video->type == 'dailymotion') {
            return 'www.dailymotion.com/embed/video/' . $video->code . '?wmode=opaque' . ($autoplay ? "&amp;autoplay=1" : "");
        } elseif ($video->type == 3 || $video->type == 'upload' || $video->type == 'mydevice') { // Uploded Videos
            $staticBaseUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.static.baseurl', null);

            $getHost = Engine_Api::_()->getApi('Core', 'siteapi')->getHost();
            $getDefaultStorageId = Engine_Api::_()->getDbtable('services', 'storage')->getDefaultServiceIdentity();
            $getDefaultStorageType = Engine_Api::_()->getDbtable('services', 'storage')->getService($getDefaultStorageId)->getType();

            $host = '';
            if ($getDefaultStorageType == 'local')
                $host = !empty($staticBaseUrl) ? $staticBaseUrl : $getHost;

            $video_location = Engine_Api::_()->storage()->get($video->file_id, $video->getType())->getHref();

            $video_location = strstr($video_location, 'http') ? $video_location : $host . $video_location;

            return $video_location;
        }
        elseif ($video->type == 6 || $video->type == 'embedcode' || $video->type == 'iframely') {

            if (isset($video->code) && !empty($video->code))
                return $video->code;
            else
                return '';
        }
    }

    public function getPhotoTag($photo) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $response = array();
        if (empty($photo))
            return;
        foreach ($photo->tags()->getTagMaps() as $tagmap) {
            if (($viewer->getIdentity() == $tagmap->tag_id) || ($photo->owner_id == $viewer->getIdentity())) {
                $isRemove = 1;
            } else
                $isRemove = 0;

            $tags = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id,
                "isRemove" => $isRemove
            ));
            try {
                $subject = Engine_Api::_()->getItem('user', $tagmap->tag_id);
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($subject);
                $tags = array_merge($tags, $getContentImages);
                if (!empty($isRemove)) {
                    $menu['menus'] = array(
                        'label' => Engine_Api::_()->getApi('Core', 'siteapi')->translate('Remove Tag'),
                        'name' => 'delete_tag',
                        'url' => 'tags/remove',
                        'urlParams' => array(
                            "subject_type" => $photo->getType(),
                            "subject_id" => $photo->getIdentity(),
                            "tagmap_id" => $tagmap->tagmap_id
                        )
                    );
                    $tags = array_merge($tags, $menu);
                }
            } catch (Exception $ex) {
                
            }
            $response[] = $tags;
        }
        return $response;
    }

    private function _preview($uri) {

        // clean URL for html code
        $uri = trim(strip_tags($uri));
        $displayUri = $uri;
        $info = parse_url($displayUri);
        if (!empty($info['path'])) {
            $displayUri = str_replace($info['path'], urldecode($info['path']), $displayUri);
        }

        try {
            $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
            if (!empty($config['host']) && $config['host'] != 'none') {
                $array = $this->_getFromIframely($config, $uri);
            } else {
                $array = $this->_getFromClientRequest($uri);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $array;
    }

    protected function _getFromIframely($config, $uri) {
        $iframely = Engine_Iframely::factory($config)->get($uri);
        $images = array();
        if (!empty($iframely['links']['thumbnail'])) {
            $images = $iframely['links']['thumbnail'][0]['href'];
            $params = $iframely['links']['thumbnail'][0]['media'];
            if (empty($params)) {
                $params = array("width" => 0,
                    "height" => 0
                );
            }
            $getimagesize = @getimagesize($images);
            $array = array(
                'url' => $images,
                'params' => array("width" => $getimagesize[0], "height" => $getimagesize[1])
            );
        }

        $allowRichHtmlTyes = array(
            'player',
            'image',
            'reader',
            'survey',
            'file'
        );
        $typeOfContent = array_intersect(array_keys($iframely['links']), $allowRichHtmlTyes);
        return $array;
    }

    protected function _getFromClientRequest($uri) {
        $info = parse_url($uri);
        if (!empty($info['path'])) {
            $path = urldecode($info['path']);
            foreach (explode('/', $info['path']) as $path) {
                $paths[] = urlencode($path);
            }
            $uri = str_replace($info['path'], join('/', $paths), $uri);
        }
        $client = new Zend_Http_Client($uri, array(
            'maxredirects' => 2,
            'timeout' => 10,
        ));
        // Try to mimic the requesting user's UA
        $client->setHeaders(array(
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'X-Powered-By' => 'Zend Framework'
        ));
        $response = $client->request();
        // Get content-type
        list($contentType) = explode(';', $response->getHeader('content-type'));

        // Handling based on content-type
        switch (strtolower($contentType)) {
            // Images
            case 'image/gif':
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/tif': // Might not work
            case 'image/xbm':
            case 'image/xpm':
            case 'image/png':
            case 'image/bmp': // Might not work
                $this->_previewImage($uri, $response);
                break;
            // HTML
            case '':
            case 'text/html':
                $this->_previewHtml($uri, $response);
                break;
            // Plain text
            case 'text/plain':
                $this->_previewText($uri, $response);
                break;
            // Unknown
            default:
                break;
        }
    }

    protected function _previewImage($uri, Zend_Http_Response $response) {

        $images = array($uri);
    }

    protected function _previewText($uri, Zend_Http_Response $response) {
        $body = $response->getBody();
        if (preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
                preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches)) {
            $charset = trim($matches[1]);
        } else {
            $charset = 'UTF-8';
        }
        // Reduce whitespace
        $body = preg_replace('/[\n\r\t\v ]+/', ' ', $body);
    }

    protected function _previewHtml($uri, Zend_Http_Response $response) {
        $body = $response->getBody();
        $body = trim($body);
        if (preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
                preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches)) {
            $charset = trim($matches[1]);
        } else {
            $charset = 'UTF-8';
        }
        if (function_exists('mb_convert_encoding')) {
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', $charset);
        }
        // Get DOM
        if (class_exists('DOMDocument')) {
            $dom = new Zend_Dom_Query($body);
        } else {
            $dom = null; // Maybe add b/c later
        }
        $title = null;
        if ($dom) {
            $titleList = $dom->query('title');
            if (count($titleList) > 0) {
                $title = trim($titleList->current()->textContent);
            }
        }

        $description = null;
        if ($dom) {
            $descriptionList = $dom->queryXpath("//meta[@name='description']");
            // Why are they using caps? -_-
            if (count($descriptionList) == 0) {
                $descriptionList = $dom->queryXpath("//meta[@name='Description']");
            }
            // Try to get description which is set under og tag
            if (count($descriptionList) == 0) {
                $descriptionList = $dom->queryXpath("//meta[@property='og:description']");
            }
            if (count($descriptionList) > 0) {
                $description = trim($descriptionList->current()->getAttribute('content'));
            }
        }

        $thumb = null;
        if ($dom) {
            $thumbList = $dom->queryXpath("//link[@rel='image_src']");
            $attributeType = 'href';
            if (count($thumbList) == 0) {
                $thumbList = $dom->queryXpath("//meta[@property='og:image']");
                $attributeType = 'content';
            }
            if (count($thumbList) > 0) {
                $thumb = $thumbList->current()->getAttribute($attributeType);
            }
        }

        $medium = null;
        if ($dom) {
            $mediumList = $dom->queryXpath("//meta[@name='medium']");
            if (count($mediumList) > 0) {
                $medium = $mediumList->current()->getAttribute('content');
            }
        }

        // Get baseUrl and baseHref to parse . paths
        $baseUrlInfo = parse_url($uri);
        $baseUrl = null;
        $baseHostUrl = null;
        $baseUrlScheme = $baseUrlInfo['scheme'];
        $baseUrlHost = $baseUrlInfo['host'];
        if ($dom) {
            $baseUrlList = $dom->query('base');
            if ($baseUrlList && count($baseUrlList) > 0 && $baseUrlList->current()->getAttribute('href')) {
                $baseUrl = $baseUrlList->current()->getAttribute('href');
                $baseUrlInfo = parse_url($baseUrl);
                if (!isset($baseUrlInfo['scheme']) || empty($baseUrlInfo['scheme'])) {
                    $baseUrlInfo['scheme'] = $baseUrlScheme;
                }
                if (!isset($baseUrlInfo['host']) || empty($baseUrlInfo['host'])) {
                    $baseUrlInfo['host'] = $baseUrlHost;
                }
                $baseHostUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/';
            }
        }
        if (!$baseUrl) {
            $baseHostUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/';
            if (empty($baseUrlInfo['path'])) {
                $baseUrl = $baseHostUrl;
            } else {
                $baseUrl = explode('/', $baseUrlInfo['path']);
                array_pop($baseUrl);
                $baseUrl = join('/', $baseUrl);
                $baseUrl = trim($baseUrl, '/');
                $baseUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/' . $baseUrl . '/';
            }
        }
        $images = array();
        if ($thumb) {
            $images[] = $thumb;
        }
        if ($dom) {
            $imageQuery = $dom->query('img');
            foreach ($imageQuery as $image) {
                $src = $image->getAttribute('src');
                // Ignore images that don't have a src
                if (!$src || false === ($srcInfo = @parse_url($src))) {
                    continue;
                }
                $ext = ltrim(strrchr($src, '.'), '.');
                // Detect absolute url
                if (strpos($src, '/') === 0) {
                    // If relative to root, add host
                    $src = $baseHostUrl . ltrim($src, '/');
                } elseif (strpos($src, './') === 0) {
                    // If relative to current path, add baseUrl
                    $src = $baseUrl . substr($src, 2);
                } elseif (!empty($srcInfo['scheme']) && !empty($srcInfo['host'])) {
                    // Contians host and scheme, do nothing
                } elseif (empty($srcInfo['scheme']) && empty($srcInfo['host'])) {
                    // if not contains scheme or host, add base
                    $src = $baseUrl . ltrim($src, '/');
                } elseif (empty($srcInfo['scheme']) && !empty($srcInfo['host'])) {
                    // if contains host, but not scheme, add scheme?
                    $src = $baseUrlInfo['scheme'] . ltrim($src, '/');
                } else {
                    // Just add base
                    $src = $baseUrl . ltrim($src, '/');
                }

                if (!in_array($src, $images)) {
                    $images[] = $src;
                }
            }
        }
        // Unique
        $images = array_values(array_unique($images));
        // Truncate if greater than 20
        if (count($images) > 30) {
            array_splice($images, 30, count($images));
        }
        if (count($images) > 1) {
            $getimagesize = @getimagesize($images[1]);
            $array = array(
                'url' => $images[1],
                'params' => array("width" => $getimagesize[0], "height" => $getimagesize[1])
            );
        } elseif (count($images) > 0) {
            $getimagesize = @getimagesize($images[0]);
            $array = array(
                'url' => $images[0],
                'params' => array("width" => $getimagesize[0], "height" => $getimagesize[1])
            );
        } else {
            $images = array();
        }
        return $images;
    }

    private function _wordStyling() {
        $table = Engine_Api::_()->getDbtable('words', 'advancedactivity');
        $select = $table->select()
                ->order('word_id ASC');
        $results = $table->fetchAll($select);
        foreach ($results as $result) {
            $tempresult[] = $result->toArray();
        }
        return $tempresult;
    }

    //check Word styling word
    public function _checkWordStyling($body, $words) {
        try {

            $flag = 0;
            $wordsStyle = array();
            foreach ($words as $key => $value) {
                if (stristr($body, $value['title'])) {
                    $wordsStyle[] = $value;
                }
            }
        } catch (Exception $ex) {
            
        }
        return $wordsStyle;
    }

    private function _feedDecorationSetting() {
        $decorationSetting = array();
        $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
        $decorationSetting['char_length'] = $coreSettingsApi->getSetting('advancedactivity.feed.char.length', 50);
        $decorationSetting['font_size'] = $coreSettingsApi->getSetting('advancedactivity.feed.font.size', 30);
        $decorationSetting['font_color'] = $coreSettingsApi->getSetting('advancedactivity.feed.font.color', '#000');
        $decorationSetting['font_style'] = $coreSettingsApi->getSetting('advancedactivity.feed.font.style', 'normal');
        return $decorationSetting;
    }

}
