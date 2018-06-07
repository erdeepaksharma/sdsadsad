<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_PostFeedController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->category_id = $request->getParam('category_id', 0);
// 
    $this->view->description = $this->_getParam("description", null);
    if ($this->view->category_id)
      $this->view->category = Engine_Api::_()->getItem("advancedactivity_category", $this->view->category_id);
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id))
      return $this->setNoRender();
    $subject = null;
    if (Engine_Api::_()->core()->hasSubject()) {
			// Get subject
			$parentSubject = $subject = Engine_Api::_()->core()->getSubject();
			if($subject->getType() == 'siteevent_event' ) {
				$parentSubject = Engine_Api::_()->getItem($subject->getParent()->getType(), $subject->getParent()->getIdentity());
			}

      if (!in_array($subject->getType(), array('sitepage_page', 'sitepageevent_event', 'sitegroup_group', 'sitegroupevent_event', 'sitestore_store', 'sitestoreevent_event', 'sitebusiness_business', 'sitebusinessevent_event')) && !in_array($parentSubject->getType(), array('sitepage_page', 'sitegroup_group', 'sitestore_store', 'sitebusiness_business'))) {
        if (!$subject->authorization()->isAllowed($viewer, 'view') && !$parentSubject->authorization()->isAllowed($viewer, 'view') ) {
          return $this->setNoRender();
        }
      } else if (in_array($subject->getType(), array('sitepage_page', 'sitepageevent_event')) || ($parentSubject->getType() == 'sitepage_page')) {
        $pageSubject = $parentSubject;
        if ($subject->getType() == 'sitepageevent_event')
          $pageSubject = Engine_Api::_()->getItem('sitepage_page', $subject->page_id);
        $isManageAdmin = Engine_Api::_()->sitepage()->isManageAdmin($pageSubject, 'view');
        if (empty($isManageAdmin)) {
          return $this->setNoRender();
        }
      } else if (in_array($subject->getType(), array('sitebusiness_business', 'sitebusinessevent_event')) || ($parentSubject->getType() == 'sitebusiness_business')) {
        $businessSubject = $parentSubject;
        if ($subject->getType() == 'sitebusinessevent_event')
          $businessSubject = Engine_Api::_()->getItem('sitebusiness_business', $subject->business_id);
        $isManageAdmin = Engine_Api::_()->sitebusiness()->isManageAdmin($businessSubject, 'view');
        if (empty($isManageAdmin)) {
          return $this->setNoRender();
        }
      } else if (in_array($subject->getType(), array('sitegroup_group', 'sitegroupevent_event')) || ($parentSubject->getType() == 'sitegroup_group')) {
        $groupSubject = $parentSubject;
        if ($subject->getType() == 'sitegroupevent_event')
          $groupSubject = Engine_Api::_()->getItem('sitegroup_group', $subject->group_id);
        $isManageAdmin = Engine_Api::_()->sitegroup()->isManageAdmin($groupSubject, 'view');
        if (empty($isManageAdmin)) {
          return $this->setNoRender();
        }
      } else if (in_array($subject->getType(), array('sitestore_store', 'sitestoreevent_event')) || ($parentSubject->getType() == 'sitestore_store')) {
        $storeSubject = $parentSubject;
        if ($subject->getType() == 'sitestoreevent_event')
          $storeSubject = Engine_Api::_()->getItem('sitestore_store', $subject->store_id);
        $isManageAdmin = Engine_Api::_()->sitestore()->isManageAdmin($storeSubject, 'view');
        if (empty($isManageAdmin)) {
          return $this->setNoRender();
        }
      }
    }


    $this->view->enableComposer = false;
    if (Engine_Api::_()->seaocore()->isLessThan420ActivityModule()) {
      if (!$subject || $subject->authorization()->isAllowed($viewer, 'comment')) {
        $this->view->enableComposer = true;
      }
    } else {
      if (!$subject || ($subject instanceof Core_Model_Item_Abstract && $subject->isSelf($viewer))) {
        if (Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'user', 'status')) {
          $this->view->enableComposer = true;
        }
      } else if ($subject) {
        if (Engine_Api::_()->authorization()->isAllowed($subject, $viewer, 'comment')) {
          $this->view->enableComposer = true;
        }
      }
    }
    if (!empty($subject)) {

			// Get subject
			$parentSubject = $subject = Engine_Api::_()->core()->getSubject();
			if($subject->getType() == 'siteevent_event' ) {
				$parentSubject = Engine_Api::_()->getItem($subject->getParent()->getType(), $subject->getParent()->getIdentity());
			}
      if ($subject->getType() == 'sitepage_page' || $subject->getType() == 'sitepageevent_event' || $parentSubject->getType() == 'sitepage_page') {
        $pageSubject = $parentSubject;
        if ($subject->getType() == 'sitepageevent_event')
          $pageSubject = Engine_Api::_()->getItem('sitepage_page', $subject->page_id);
        $isManageAdmin = Engine_Api::_()->sitepage()->isManageAdmin($pageSubject, 'comment');
        if (!empty($isManageAdmin)) {
          $this->view->enableComposer = true;
          if (!$pageSubject->all_post && !Engine_Api::_()->sitepage()->isPageOwner($pageSubject)) {
            $this->view->enableComposer = false;
          }
        }
        if ($this->view->enableComposer) {
          $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
          $activityFeedType = null;
          if (Engine_Api::_()->sitepage()->isPageOwner($pageSubject) && Engine_Api::_()->sitepage()->isFeedTypePageEnable())
            $activityFeedType = 'sitepage_post_self';
          else
            $activityFeedType = 'sitepage_post';
          if (!$actionSettingsTable->checkEnabledAction($viewer, $activityFeedType)) {
            $this->view->enableComposer = false;
          }
        }
      } else if ($subject->getType() == 'sitebusiness_business' || $subject->getType() == 'sitebusinessevent_event' || $parentSubject->getType() == 'sitebusiness_business') {
        $businessSubject = $subject;
        if ($subject->getType() == 'sitebusinessevent_event')
          $businessSubject = Engine_Api::_()->getItem('sitebusiness_business', $subject->business_id);
        $isManageAdmin = Engine_Api::_()->sitebusiness()->isManageAdmin($businessSubject, 'comment');
        if (!empty($isManageAdmin)) {
          $this->view->enableComposer = true;
          if (!$businessSubject->all_post && !Engine_Api::_()->sitebusiness()->isBusinessOwner($businessSubject)) {
            $this->view->enableComposer = false;
          }
        }
        if ($this->view->enableComposer) {
          $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
          $activityFeedType = null;

          if (Engine_Api::_()->sitebusiness()->isBusinessOwner($businessSubject) && Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable())
            $activityFeedType = 'sitebusiness_post_self';
          elseif ($businessSubject->all_post || Engine_Api::_()->sitebusiness()->isBusinessOwner($businessSubject))
            $activityFeedType = 'sitebusiness_post';
          if (!empty($activityFeedType) && !$actionSettingsTable->checkEnabledAction($viewer, $activityFeedType)) {
            $this->view->enableComposer = false;
          }
        }
      } elseif ($subject->getType() == 'sitegroup_group' || $subject->getType() == 'sitegroupevent_event' || $parentSubject->getType() == 'sitegroup_group') {
        $groupSubject = $parentSubject;
        if ($subject->getType() == 'sitegroupevent_event')
          $groupSubject = Engine_Api::_()->getItem('sitegroup_group', $subject->group_id);
        $isManageAdmin = Engine_Api::_()->sitegroup()->isManageAdmin($groupSubject, 'comment');
        if (!empty($isManageAdmin)) {
          $this->view->enableComposer = true;
          if (!$groupSubject->all_post && !Engine_Api::_()->sitegroup()->isGroupOwner($groupSubject)) {
            $this->view->enableComposer = false;
          }
        }
        if ($this->view->enableComposer) {
          $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
          $activityFeedType = null;
          if (Engine_Api::_()->sitegroup()->isGroupOwner($groupSubject) && Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable())
            $activityFeedType = 'sitegroup_post_self';
          else
            $activityFeedType = 'sitegroup_post';
          if (!$actionSettingsTable->checkEnabledAction($viewer, $activityFeedType)) {
            $this->view->enableComposer = false;
          }
        }
      } elseif ($subject->getType() == 'sitestore_store' || $subject->getType() == 'sitestoreevent_event' || $parentSubject->getType() == 'sitestore_store') {
        $storeSubject = $parentSubject;
        if ($subject->getType() == 'sitestoreevent_event')
          $storeSubject = Engine_Api::_()->getItem('sitestore_store', $subject->store_id);
        $isManageAdmin = Engine_Api::_()->sitestore()->isManageAdmin($storeSubject, 'comment');
        if (!empty($isManageAdmin)) {
          $this->view->enableComposer = true;
          if (!$storeSubject->all_post && !Engine_Api::_()->sitestore()->isStoreOwner($storeSubject)) {
            $this->view->enableComposer = false;
          }
        }
        if ($this->view->enableComposer) {
          $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
          $activityFeedType = null;
          if (Engine_Api::_()->sitestore()->isStoreOwner($storeSubject) && Engine_Api::_()->sitestore()->isFeedTypeStoreEnable())
            $activityFeedType = 'sitestore_post_self';
          else
            $activityFeedType = 'sitestore_post';
          if (!$actionSettingsTable->checkEnabledAction($viewer, $activityFeedType)) {
            $this->view->enableComposer = false;
          }
        }
      }
    }

    if (!$this->view->enableComposer) {
      return $this->setNoRender();
    }
// Assign the composing values
    $composePartials = array();
    foreach (Zend_Registry::get('Engine_Manifest') as $data) {
      if (empty($data['composer']) || !empty($data['composer']['facebook']) || !empty($data['composer']['twitter'])) {
        continue;
      }
      if (!empty($data['composer']['advanced_facebook']) && !$this->isFacebookEnable()) {
        continue;
      }
      if (!empty($data['composer']['advanced_twitter']) && !$this->isTwitterEnable()) {
        continue;
      }
      if (!empty($data['composer']['advanced_linkedin']) && !$this->isLinkdinEnable()) {
        continue;
      }
      foreach ($data['composer'] as $type => $config) {
        if (isset($config['script'][1]) && $config['script'][1] == 'video' && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ynvideo'))
          continue;
        if (isset($config['script'][1]) && $config['script'][1] == 'album' && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('advalbum'))
          continue;


        if (!empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1])) {
          continue;
        }
        if ($type == "tag" && $config['script'][1] == 'core')
          continue;
        if ($config['script'][1] == 'album') {
          $config['script'][1] = 'advancedactivity';
        }
        $composePartials[] = $config['script'];
      }
    }

    $this->view->composePartials = $composePartials;
// Get lists
    $this->view->settingsApi = $settings = Engine_Api::_()->getApi('settings', 'core');
    if (empty($subject) || $viewer->isSelf($subject)) {
      $this->view->showPrivacyDropdown = in_array('userprivacy', $settings->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy")));
      if ($this->view->showPrivacyDropdown)
        $this->view->showDefaultInPrivacyDropdown = $userPrivacy = Engine_Api::_()->getDbtable('settings', 'user')->getSetting($viewer, "aaf_post_privacy");
      if (empty($userPrivacy))
        $this->view->showDefaultInPrivacyDropdown = $userPrivacy = $settings->getSetting('activity.content', 'everyone');

      $this->view->availableLabels = $availableLabels = array('everyone' => 'Everyone', 'networks' => 'Friends &amp; Networks', 'friends' => 'Friends Only', 'onlyme' => 'Only Me');
      $enableNetworkList = $settings->getSetting('advancedactivity.networklist.privacy', 0);
      if ($enableNetworkList) {
        $this->view->network_lists = $networkLists = Engine_Api::_()->advancedactivity()->getNetworks($enableNetworkList, $viewer);
        $this->view->enableNetworkList = count($networkLists);

        if (Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($userPrivacy)) {
          $ids = Engine_Api::_()->advancedactivity()->isNetworkBasePrivacyIds($userPrivacy);
          $privacyNetwork = array();
          $privacyNetworkIds = array();
          foreach ($networkLists as $network) {
            if (in_array($network->getIdentity(), $ids)) {
              $privacyNetwork["network_" . $network->getIdentity()] = $network->getTitle();
              $privacyNetworkIds[] = "network_" . $network->getIdentity();
            }
          }
          if (count($privacyNetwork) > 0) {
            $this->view->privacylists = $privacyNetwork;
            $this->view->showDefaultInPrivacyDropdown = $userPrivacy = join(",", $privacyNetworkIds);
          } else {
            $this->view->showDefaultInPrivacyDropdown = $userPrivacy = "networks";
          }
        }
      }
      $this->view->enableList = $userFriendListEnable = $settings->getSetting('user.friends.lists');

      $viewer_id = $viewer->getIdentity();
      if ($userFriendListEnable && !empty($viewer_id)) {
        $listTable = Engine_Api::_()->getItemTable('user_list');
        $this->view->lists = $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $viewer->getIdentity()));
        $this->view->countList = $countList = @count($lists);
        if (!empty($countList) && !empty($userPrivacy) && !in_array($userPrivacy, array('everyone', 'networks', 'friends', 'onlyme')) && !Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($userPrivacy)) {
          $privacylists = $listTable->fetchAll($listTable->select()->where('list_id IN(?)', array(explode(",", $userPrivacy))));
          $temp_list = array();
          foreach ($privacylists as $plist) {
            $temp_list[$plist->list_id] = $plist->title;
          }
          if (count($temp_list) > 0) {
            $this->view->privacylists = $temp_list;
          } else {
            $this->view->showDefaultInPrivacyDropdown = $userPrivacy = "friends";
          }
        }
      } else {
        $userFriendListEnable = 0;
      }
      $this->view->enableList = $userFriendListEnable;
      $this->view->canCreateCategroyList = 1;
      $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivity');
      if (!$this->view->category_id)
        $this->view->categoriesList = $tableCategories->getCategories();
    }


    if (!Engine_Api::_()->seaocore()->isLessThan420ActivityModule()) {
// Form token
      $session = new Zend_Session_Namespace('ActivityFormToken');
//$session->setExpirationHops(10);
      if (empty($session->token)) {
        $this->view->formToken = $session->token = md5(time() . $viewer->getIdentity() . get_class($this));
      } else {
        $this->view->formToken = $session->token;
      }
    }
  }

  public function isLinkdinEnable() {
    $isLinkedinLogin = true;
    $linkedin_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey');
    $linkedin_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey');
    $linkedin_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.enable', 0);
    if (!empty($linkedin_apikey) && !empty($linkedin_secret) && ($linkedin_enable)) {


      $Api_linkedin = Engine_Api::_()->getApi('linkedin_Api', 'seaocore');
      $OBJ_linkedin = $Api_linkedin->getApi();

      if ($OBJ_linkedin && $Api_linkedin->isConnected()) {
        $OBJ_linkedin->setToken(array('oauth_token' => $_SESSION['linkedin_token2'], 'oauth_token_secret' => $_SESSION['linkedin_secret2']));
        $OBJ_linkedin->setCallbackUrl(( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_linkedin=1');


        try {
          $options = '?count=1';
          $LinkedinUserFeed = $OBJ_linkedin->updates($options);

          if ($LinkedinUserFeed['success'] != TRUE) {

            $isLinkedinLogin = false;
          }
        } catch (Exception $e) {
          $isLinkedinLogin = false;
        }
      } else {
        $isLinkedinLogin = false;
      }
    } else {
      $isLinkedinLogin = false;
    }
    return $isLinkedinLogin;
  }

  public function isFacebookEnable($subject) {
//FIRST CHECKING IF ADMIN HAS ENABLED THE THIRD PARTY SERVICES OR NOT....
    $isFB = true;
    if ($subject && ($subject->getType() == 'sitepage_page' || $subject->getType() == 'sitebusiness_business'))
      $managepage = true;
    else
      $managepage = false;


    $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    if (!empty($settings['appid']) && !empty($settings['secret'])) {
      $FBloginURL = Zend_Controller_Front::getInstance()->getRouter()
                      ->assemble(array('module' => 'seaocore', 'controller' => 'auth', 'action' => 'facebook'), 'default', true) . '?' . http_build_query(array('redirect_urimain' => urlencode(( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_fb=1'), 'manage_pages' => $managepage));

      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('facebook.enable', Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable == 'publish' ? 1 : 0)) {
        $session = new Zend_Session_Namespace();
        $Api_facebook = Engine_Api::_()->getApi('facebook_Facebookinvite', 'seaocore');
        $facebook_userfeed = $Api_facebook->getFBInstance();
        $session_userfeed = $facebook_userfeed;

        if (!empty($facebook_userfeed)) {
          $checksiteIntegrate = true;
          $facebookCheck = new Seaocore_Api_Facebook_Facebookinvite();
          $fb_checkconnection = $facebookCheck->checkConnection(null, $facebook_userfeed);

          if ($session_userfeed && $fb_checkconnection) {
//$session->fb_checkconnection = true;
            $core_fbenable = Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable;
            $enable_socialdnamodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdna');
            if (('publish' == $core_fbenable || 'login' == $core_fbenable || $enable_socialdnamodule) && (!$fb_checkconnection)) {
              $checksiteIntegrate = false;
            } else {
              try {
                if (!isset($session->fb_canread)) {
                  $permissions = $facebook_userfeed->api("/me/permissions");
                  if (!array_key_exists('read_stream', $permissions['data'][0])) {
                    $checksiteIntegrate = false;
                  } else {
                    $session->fb_canread = true;
                  }
                  if (!array_key_exists('manage_pages', $permissions['data'][0])) {
                    $session->fb_can_managepages = false;
                  } else {
                    $session->fb_can_managepages = true;
                  }
                }
                if ($subject && ($subject->getType() == 'sitepage_page' || $subject->getType() == 'sitebusiness_business') && !$session->fb_can_managepages) {
                  $checksiteIntegrate = false;
                }
              } catch (Exception $e) {
                $checksiteIntegrate = false;
              }
            }
          }
          if (!$session_userfeed || !$fb_checkconnection || !$checksiteIntegrate) {
            $isFB = false;
          }
        }
      } else {
        $isFB = false;
      }
    }

    return $isFB;
  }

  public function isTwitterEnable() {
    $isTwitter = true;
    $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
    if (function_exists('mb_strlen') && !empty($settings['key']) && !empty($settings['secret']) && Engine_Api::_()->getApi('settings', 'core')->getSetting('twitter.enable', Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable == 'publish' ? 1 : 0)) {

      try {
        $Api_twitter = Engine_Api::_()->getApi('twitter_Api', 'seaocore');
        $twitterOauth = $twitter = $Api_twitter->getApi();
        if ($twitter && $Api_twitter->isConnected()) {
          $twitterData = (array) $twitterOauth->get(
                          'statuses/home_timeline', array('count' => 1)
          );
          if (isset($twitterData['errors']))
            $isTwitter = false;
        } else {

          $isTwitter = false;
        }
      } catch (Exception $e) {
        $isTwitter = false;
      }
    } else {
      $isTwitter = false;
    }
  }

}
?>