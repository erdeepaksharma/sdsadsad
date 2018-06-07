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
class Advancedactivity_Widget_HomeFeedsController extends Engine_Content_Widget_Abstract
{

  private $_cacheApi;

  public function indexAction()
  {
    $this->_cacheApi = Engine_Api::_()->getApi('cache', 'advancedactivity');
    $this->viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $this->viewer_id = $this->viewer->getIdentity();
    $this->subject = $subject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;

    // Don't render this if not authorized
    $cacheId = __CLASS__ . 'shouldNoRender';
    if( $this->_cacheApi->test($cacheId) ) {
      $shouldNoRender = $this->shouldNoRender();
      $this->_cacheApi->save($shouldNoRender, $cacheId);
    } else {
      $shouldNoRender = $this->_cacheApi->load($cacheId);
    }
    if( $shouldNoRender ) {
      return $this->setNoRender();
    }
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->module_name = $module = $request->getModuleName();
    $this->view->action_name = $action = $request->getActionName();
    $this->view->title = $this->_getParam('title', null);
    $enableComposer = $this->_getParam('enableComposer', true);
    $controller = $request->getControllerName();

    $this->view->showTabs = $this->_getParam('showTabs', 0);
    $this->view->showPosts = $this->_getParam('showPosts', 1);
    $this->view->integrateCommunityAdv = $this->_getParam('integrateCommunityAdv', 1);
    $this->view->hide = ($module == 'sitehashtag' && $controller == 'index' && $action == 'index');
    $loadByAjax = Engine_Api::_()->core()->hasSubject();
    $this->view->loadByAjax = $this->_getParam('loadByAjax', $loadByAjax);

    if( Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode') && (Engine_API::_()->seaocore()->isMobile() || Engine_API::_()->seaocore()->isTabletDevice()) ) {
      $this->view->loadByAjax = 0;
    }



    $this->view->videoWidth = $this->_getParam("videowidth", 0);
    $this->view->widgetParams = $this->_getAllParams();
    $this->view->isMobile = Engine_Api::_()->advancedactivity()->isMobile();
    // Don't render this if not authorized
    $aafInfoTooltips = null;
    $subject = $this->subject;
    // Get some other info
    if( !empty($subject) ) {
      $this->view->subjectGuid = $subject->getGuid(false);
    }


    $this->view->enableComposer = false;
    $this->view->action_id = $request->getParam('action_id');
    $this->view->viewAllLikes = $request->getParam('viewAllLikes', $request->getParam('show_likes', false));
    $this->view->viewAllComments = $request->getParam('viewAllComments', $request->getParam('show_comments', false));
    /*  Customization Start */
    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Advancedactivity/View/Helper', 'Advancedactivity_View_Helper');
    $aafInfotooltipPost = Zend_Registry::isRegistered('advancedactivity_infotooltip_post') ? Zend_Registry::get('advancedactivity_infotooltip_post') : null;
    if( $enableComposer && $this->viewer->getIdentity() && !$request->getParam('action_id') ) {
      if( !$subject || ($subject instanceof Core_Model_Item_Abstract && $subject->isSelf($this->viewer)) ) {
        $this->view->enableComposer = Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'user', 'status');
      } else if( $subject ) {
        $this->view->enableComposer = Engine_Api::_()->authorization()->isAllowed($subject, $this->viewer, 'comment');
        $parentSubject = $subject;
        $this->view->parentType = $subject->getType();
        $this->view->parentId = $subject->getIdentity();
        if( $subject->getType() == 'siteevent_event' ) {
          $parentSubject = Engine_Api::_()->getItem($subject->parent_type, $subject->parent_id);
          if( !Engine_Api::_()->authorization()->isAllowed($subject, $this->viewer, "post") )
            $this->view->enableComposer = false;
        }
        else if( $subject->getType() == 'sitepage_page' || $subject->getType() == 'sitepageevent_event' || $parentSubject->getType() == 'sitepage_page' ) {
          $pageSubject = $parentSubject;
          if( $subject->getType() == 'sitepageevent_event' )
            $pageSubject = Engine_Api::_()->getItem('sitepage_page', $subject->page_id);
          $isManageAdmin = Engine_Api::_()->sitepage()->isManageAdmin($pageSubject, 'comment');
          if( !empty($isManageAdmin) ) {
            $this->view->enableComposer = true;
            if( !$pageSubject->all_post && !Engine_Api::_()->sitepage()->isPageOwner($pageSubject) ) {
              $this->view->enableComposer = false;
            }
          }
          if( $this->view->enableComposer ) {
            $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
            $activityFeedType = null;
            if( Engine_Api::_()->sitepage()->isPageOwner($pageSubject) && Engine_Api::_()->sitepage()->isFeedTypePageEnable() )
              $activityFeedType = 'sitepage_post_self';
            else
              $activityFeedType = 'sitepage_post';
            if( !$actionSettingsTable->checkEnabledAction($this->viewer, $activityFeedType) ) {
              $this->view->enableComposer = false;
            }
          }
        } else if( $subject->getType() == 'sitebusiness_business' || $subject->getType() == 'sitebusinessevent_event' || $parentSubject->getType() == 'sitebusiness_business' ) {
          $businessSubject = $parentSubject;
          if( $subject->getType() == 'sitebusinessevent_event' )
            $businessSubject = Engine_Api::_()->getItem('sitebusiness_business', $subject->business_id);
          $isManageAdmin = Engine_Api::_()->sitebusiness()->isManageAdmin($businessSubject, 'comment');
          if( !empty($isManageAdmin) ) {
            $this->view->enableComposer = true;
            if( !$businessSubject->all_post && !Engine_Api::_()->sitebusiness()->isBusinessOwner($businessSubject) ) {
              $this->view->enableComposer = false;
            }
          }
          if( $this->view->enableComposer ) {
            $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
            $activityFeedType = null;
            if( Engine_Api::_()->sitebusiness()->isBusinessOwner($businessSubject) && Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable() )
              $activityFeedType = 'sitebusiness_post_self';
            elseif( $businessSubject->all_post || Engine_Api::_()->sitebusiness()->isBusinessOwner($businessSubject) )
              $activityFeedType = 'sitebusiness_post';
            if( !empty($activityFeedType) && !$actionSettingsTable->checkEnabledAction($this->viewer, $activityFeedType) ) {
              $this->view->enableComposer = false;
            }
          }
        } elseif( $subject->getType() == 'sitegroup_group' || $subject->getType() == 'sitegroupevent_event' || $parentSubject->getType() == 'sitebusiness_business' ) {
          $groupSubject = $parentSubject;
          if( $subject->getType() == 'sitegroupevent_event' )
            $groupSubject = Engine_Api::_()->getItem('sitegroup_group', $subject->group_id);
          $isManageAdmin = Engine_Api::_()->sitegroup()->isManageAdmin($groupSubject, 'comment');
          if( !empty($isManageAdmin) ) {
            $this->view->enableComposer = true;
            if( !$groupSubject->all_post && !Engine_Api::_()->sitegroup()->isGroupOwner($groupSubject) ) {
              $this->view->enableComposer = false;
            }
          }
          if( $this->view->enableComposer ) {
            $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
            $activityFeedType = null;
            if( Engine_Api::_()->sitegroup()->isGroupOwner($groupSubject) && Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable() )
              $activityFeedType = 'sitegroup_post_self';
            else
              $activityFeedType = 'sitegroup_post';
            if( !$actionSettingsTable->checkEnabledAction($this->viewer, $activityFeedType) ) {
              $this->view->enableComposer = false;
            }
          }
        } elseif( $subject->getType() == 'sitestore_store' || $parentSubject->getType() == 'sitestore_store' ) {
          $storeSubject = $parentSubject;
          $isManageAdmin = Engine_Api::_()->sitestore()->isManageAdmin($storeSubject, 'comment');
          if( !empty($isManageAdmin) ) {
            $this->view->enableComposer = true;
            if( !$storeSubject->all_post && !Engine_Api::_()->sitestore()->isStoreOwner($storeSubject) ) {
              $this->view->enableComposer = false;
            }
          }
          if( $this->view->enableComposer ) {
            $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
            $activityFeedType = null;
            if( Engine_Api::_()->sitestore()->isStoreOwner($storeSubject) && Engine_Api::_()->sitestore()->isFeedTypeStoreEnable() )
              $activityFeedType = 'sitestore_post_self';
            else
              $activityFeedType = 'sitestore_post';
            if( !$actionSettingsTable->checkEnabledAction($this->viewer, $activityFeedType) ) {
              $this->view->enableComposer = false;
            }
          }
        }
      }
    }

    if( $this->view->enableComposer ) {
      if( $this->_cacheApi->test('composer_partials') ) {
        $this->view->composePartials = $this->_cacheApi->load('composer_partials');
      } else {
        // Assign the composing values
        $composerList = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.menuoptions', Engine_Api::_()->advancedactivity()->getComposerMenuList());
        ksort($composerList);
        $composePartials = array();
        foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
          if( empty($data['composer']) || !empty($data['composer']['facebook']) || !empty($data['composer']['twitter']) ) {
            continue;
          }
          foreach( $data['composer'] as $type => $config ) {
            $key = $type . 'XXX' . $config['script'][1];
            if( !in_array($type, array('advanced_facebook', 'advanced_twitter', 'advanced_linkedin', 'tag', 'hashtag')) && !in_array($key, $composerList) ) {
              continue;
            }
            if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
              continue;
            }
            if( $type == "tag" && $config['script'][1] == 'core' )
              continue;
            if( $type == "link" && $config['script'][1] == 'core' ) {
              $config['script'][1] = 'advancedactivity';
            }
            $composePartials[$key] = $config['script'];
          }
        }
        $p = array();
        foreach( $composePartials as $key => $partials ) {
          if( isset($partials[1]) && $partials[1] == 'album' && Engine_Api::_()->hasModuleBootstrap('sitealbum') ) {
            $partials[1] = 'sitealbum';
          }
          if( ($key === 'videoXXXvideo' || $key === 'videoXXXsitevideo') && Engine_Api::_()->hasModuleBootstrap('sitevideo') ) {
            $partials[1] = 'sitevideo';
            $key = 'videoXXXsitevideo';
          }
          $p[$key] = $partials;
        }
        $sortComposer = array();
        foreach( $composerList as $composerkey ) {
          if( isset($p[$composerkey]) ) {
            $sortComposer[$composerkey] = $p[$composerkey];
          }
        }
        $composerKeys = array_keys($p);
        foreach( $composerKeys as $key ) {
          if( !isset($sortComposer[$key]) ) {
            $sortComposer[$key] = $p[$key];
          }
        }
        $this->view->composePartials = $sortComposer;
        $this->_cacheApi->save($this->view->composePartials, 'composer_partials');
      }
    }


    // Get lists if viewing own profile
    // if( $viewer->isSelf($subject) ) {
    // Get lists
    $this->view->settingsApi = $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->tabtype = $settings->getSetting('advancedactivity.tabtype', 3);
    $this->view->composerType = $settings->getSetting('advancedactivity.composer.type', 0);

    if( empty($subject) || $this->viewer->isSelf($subject) ) {
      $this->view->showPrivacyDropdown = in_array('userprivacy', $settings->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy")));
      if( $this->view->showPrivacyDropdown )
        $this->view->showDefaultInPrivacyDropdown = $userPrivacy = Engine_Api::_()->getDbtable('settings', 'user')->getSetting($this->viewer, "aaf_post_privacy");
      if( empty($userPrivacy) )
        $this->view->showDefaultInPrivacyDropdown = $userPrivacy = $settings->getSetting('activity.content', 'everyone');
      $this->view->availableLabels = $availableLabels = array('everyone' => 'Everyone', 'networks' => 'Friends &amp; Networks', 'friends' => 'Friends Only', 'onlyme' => 'Only Me');
      $enableNetworkList = $settings->getSetting('advancedactivity.networklist.privacy', 0);
      if( $enableNetworkList ) {
        $cacheKey = __CLASS__ . '_NETWORK_PRIVACY_DROPDOWN';
        if( $this->_cacheApi->test($cacheKey) ) {
          $cacheNetworkPrivacy = $this->_cacheApi->load($cacheKey);
          $this->view->privacylists = $privacyNetwork = $cacheNetworkPrivacy['privacylists'];
          $this->view->showDefaultInPrivacyDropdown = $userPrivacy = $cacheNetworkPrivacy['showDefaultInPrivacyDropdown'];
        } else {
          $this->view->network_lists = $networkLists = Engine_Api::_()->advancedactivity()->getNetworks($enableNetworkList, $this->viewer);
          $this->view->enableNetworkList = count($networkLists);
          if( Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($userPrivacy) ) {
            $ids = Engine_Api::_()->advancedactivity()->isNetworkBasePrivacyIds($userPrivacy);
            $privacyNetwork = array();
            $privacyNetworkIds = array();
            foreach( $networkLists as $network ) {
              if( in_array($network->getIdentity(), $ids) ) {
                $privacyNetwork["network_" . $network->getIdentity()] = $network->getTitle();
                $privacyNetworkIds[] = "network_" . $network->getIdentity();
              }
            }
            if( count($privacyNetwork) > 0 ) {
              $this->view->privacylists = $privacyNetwork;
              $this->view->showDefaultInPrivacyDropdown = $userPrivacy = join(",", $privacyNetworkIds);
            } else {
              $this->view->showDefaultInPrivacyDropdown = $userPrivacy = "networks";
            }
          }
          $this->_cacheApi->save(array(
            'privacylists' => $this->view->privacylists,
            'showDefaultInPrivacyDropdown' => $this->view->showDefaultInPrivacyDropdown
          ));
        }
      }

      $this->view->enableList = $userFriendListEnable = $settings->getSetting('user.friends.lists');
      $this->viewer_id = $this->viewer->getIdentity();
      if( $userFriendListEnable && !empty($this->viewer_id) ) {
        $cacheKey = 'user.friends.lists';
        if( $this->_cacheApi->test($cacheKey) ) {
          $data = $this->_cacheApi->load($cacheKey);
          $this->view->lists = $lists = $data['lists'];
          $this->view->countList = $countList = $data['countList'];
          $this->view->privacylists = $data['privacylists'];
          $this->view->showDefaultInPrivacyDropdown = $userPrivacy = $data['showDefaultInPrivacyDropdown'];
        } else {
          $listTable = Engine_Api::_()->getItemTable('user_list');
          $this->view->lists = $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $this->viewer->getIdentity()));
          $this->view->countList = $countList = @count($lists);
          if( !empty($countList) && !empty($userPrivacy) && !in_array($userPrivacy, array('everyone', 'networks', 'friends', 'onlyme')) && !Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($userPrivacy) ) {
            $privacylists = $listTable->fetchAll($listTable->select()->where('list_id IN(?)', array(explode(",", $userPrivacy))));
            $temp_list = array();
            foreach( $privacylists as $plist ) {
              $temp_list[$plist->list_id] = $plist->title;
            }
            if( count($temp_list) > 0 ) {
              $this->view->privacylists = $temp_list;
            } else {
              $this->view->showDefaultInPrivacyDropdown = $userPrivacy = "friends";
            }
          }
          $this->_cacheApi->save(array(
            'lists' => $lists,
            'countList' => $countList,
            'privacylists' => $this->view->privacylists,
            'showDefaultInPrivacyDropdown' => $this->view->showDefaultInPrivacyDropdown
          ));
        }
      } else {
        $userFriendListEnable = 0;
      }
      $this->view->enableList = $userFriendListEnable;
      if( Engine_Api::_()->hasModuleBootstrap('advancedactivitypost') ) {
        $this->view->canCreateCategroyList = 1;
        $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivitypost');
        $this->view->categoriesList = $tableCategories->getCategories();
      }
    }
    $this->web_values = $web_values = $this->_getParam('advancedactivity_tabs', array());
    if( count($this->web_values) <= 1 ) {
      $this->view->hide = TRUE;
    }


    //registry error
    $isFeedEnabled = Zend_Registry::isRegistered('advancedactivity_feedEnabled') ? Zend_Registry::get('advancedactivity_feedEnabled') : null;
    if( !empty($subject) || $this->view->isMobile || empty($this->viewer_id) ) {
      $this->web_values = $web_values = array("aaffeed");
    }
    // Geting Welcome Tab Info
    $welcome_key = null;
    if( !empty($web_values) ) {
      $welcome_key = array_search('welcome', $web_values);
    }
    if( $welcome_key !== FALSE ) {
      $session = new Zend_Session_Namespace();
      // Include JS files of "Suggestion Plugin" or "People You May Know Plugin" for Welcome Tab.
      $this->view->is_suggestionEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('suggestion');
      $this->view->is_pymkEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('peopleyoumayknow');
      $this->view->is_welcomeTabEnabled = true;
      $getCustomBlockSettings = Engine_Api::_()->advancedactivity()->getCustomBlockSettings(array());
      if( empty($getCustomBlockSettings) ) {
        if( isset($web_values[$welcome_key]) ) {
          unset($web_values[$welcome_key]);
        }
      } else {
        $is_welcomeTabDefault = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcomeTab.is.default', 0);
        if( !empty($session->isUserSignup) ) {
          $is_welcomeTabDefault = true;
          unset($session->isUserSignup);
        }
        if( empty($is_welcomeTabDefault) && is_array($web_values) && array_search('aaffeed', $web_values) ) {
          $this->view->activeTab = 1;
        }
      }
    }
    $this->view->search = urldecode($request->getParam('search'));
    if( $this->view->enableComposer ) {
        $this->vaildateSocialPosting();
    }
    $this->view->web_values = $web_values;
    $count = 0;
    if( !empty($web_values) )
      $count = count($web_values);
    if( empty($count) ) {
      return $this->setNoRender();
    }
    foreach( $web_values as $value ) {
      if( empty($this->view->activeTab) ) {
        $this->view->activeTab = array_search($value, array("1" => "aaffeed", "3" => "facebook", "2" => "twitter", "4" => "welcome", "5" => "linkedin", "6" => "instagram"));
      }
      $tab = "is" . ucfirst($value) . "Enable";
      $this->view->$tab = 1;
    }
    if( isset($_GET['activityfeedtype']) ) {
      switch( $_GET['activityfeedtype'] ) {
        case 'site':
          if( $this->view->isAaffeedEnable ) {
            $this->view->activeTab = 1;
          }
          break;
        case 'twitter':
          if( $this->view->isTwitterEnable ) {
            $this->view->activeTab = 2;
          }
          break;
        case 'welcome':
          if( $this->view->isWelcomeEnable ) {
            $this->view->activeTab = 4;
          }
          break;
      }
    }
    $this->view->count_tabs = $count;
    $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
    $this->view->maxAutoScrollFeed = 0;
    if( $this->view->isMobile ) {
      $this->view->autoScrollFeedEnable = 0;
      $this->view->feedToolTipEnable = 0;
    } else {
      $this->view->autoScrollFeedEnable = $coreSettingsApi->getSetting('advancedactivity.scroll.autoload', 1);
      $this->view->aafShowImmediately = $coreSettingsApi->getSetting('advancedactivity.feed.autoload', 0);
      $this->view->maxAutoScrollFeed = $coreSettingsApi->getSetting('advancedactivity.maxautoload', 0);
      if( !empty($aafInfotooltipPost) ) {
        $aafInfoTooltips = $coreSettingsApi->getSetting('advancedactivity.info.tooltips', 1);
      }
      $this->view->feedToolTipEnable = $aafInfoTooltips;
    }
    // Form token
    $session = new Zend_Session_Namespace('ActivityFormToken');
    if( empty($session->token) ) {
      $this->view->formToken = $session->token = md5(time() . $this->viewer->getIdentity() . get_class($this));
    } else {
      $this->view->formToken = $session->token;
    }
  }

  protected function shouldNoRender()
  {
    if( $this->subject ) {
      $this->viewer = Engine_Api::_()->user()->getViewer();
      // Get subject
      $parentSubject = $subject = $this->subject;
      if( $subject->getType() == 'siteevent_event' ) {
        $parentSubject = $subject->getParent();
      }
      if( in_array($subject->getType(), array('sitepage_page', 'sitepageevent_event')) || ($parentSubject->getType() == 'sitepage_page') ) {
        $pageSubject = $subject->getType() == 'sitepageevent_event' ? $subject->getParentPage() : $parentSubject;
        $isManageAdmin = Engine_Api::_()->sitepage()->isManageAdmin($pageSubject, 'view');
        return empty($isManageAdmin);
      }
      if( in_array($subject->getType(), array('sitebusiness_business', 'sitebusinessevent_event')) || ($parentSubject->getType() == 'sitebusiness_business') ) {
        $businessSubject = $subject->getType() != 'sitebusinessevent_event' ? $parentSubject : Engine_Api::_()->getItem('sitebusiness_business', $subject->business_id);
        $isManageAdmin = Engine_Api::_()->sitebusiness()->isManageAdmin($businessSubject, 'view');
        return empty($isManageAdmin);
      }
      if( in_array($subject->getType(), array('sitegroup_group', 'sitegroupevent_event')) || ($parentSubject->getType() == 'sitegroup_group') ) {
        $groupSubject = $subject->getType() != 'sitegroupevent_event' ? $parentSubject : Engine_Api::_()->getItem('sitegroup_group', $subject->group_id);
        $isManageAdmin = Engine_Api::_()->sitegroup()->isManageAdmin($groupSubject, 'view');
        return empty($isManageAdmin);
      }
      if( in_array($subject->getType(), array('sitestore_store')) || ($parentSubject->getType() == 'sitestore_store') ) {
        $isManageAdmin = Engine_Api::_()->sitestore()->isManageAdmin($parentSubject, 'view');
        return empty($isManageAdmin);
      }
      return !$subject->authorization()->isAllowed($this->viewer, 'view') && !$parentSubject->authorization()->isAllowed($this->viewer, 'view');
    }
  }

  function vaildateSocialPosting() {
        //LINKEDIN WORK..................................................//
        $web_values = $this->web_values;
        $linkedin_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey');
        $linkedin_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey');
        if(is_array($web_values)) {
            $linkedin_key = array_search('linkedin', $web_values);
        }
        $linkedin_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.enable', 0);
        if (!empty($linkedin_apikey) && !empty($linkedin_secret) && ($linkedin_enable || $linkedin_key != FALSE)) {


            $Api_linkedin = Engine_Api::_()->getApi('linkedin_Api', 'seaocore');
            //$linkedinTable = Engine_Api::_()->getDbtable('linkedin', 'advancedactivity');
            $OBJ_linkedin = $Api_linkedin->getApi();
            $this->view->LinkedinloginURL = '';
            $this->view->LinkedinloginURL_temp = $LinkedinloginURL = Zend_Controller_Front::getInstance()->getRouter()
                            ->assemble(array('module' => 'seaocore', 'controller' => 'auth', 'action' => 'linkedin'), 'default', true) . '?' . http_build_query(array('redirect_urimain' => urlencode(( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_linkedin=1')));
                if ($OBJ_linkedin && $Api_linkedin->isConnected()) {
                    $OBJ_linkedin->setToken(array('oauth_token' => $_SESSION['linkedin_token2'], 'oauth_token_secret' => $_SESSION['linkedin_secret2']));
                    $OBJ_linkedin->setCallbackUrl(( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_linkedin=1');


                    $this->view->LinkedinloginURL_temp = $LinkedinloginURL;
                    $this->view->LinkedinloginURL = '';

                    try {
                        $options = '?count=1';
                        $LinkedinUserFeed = $OBJ_linkedin->profile();

                        if ($LinkedinUserFeed['success'] != TRUE) {

                            $this->view->LinkedinloginURL = $LinkedinloginURL;
                        }
                    } catch (Exception $e) {
                        $this->view->LinkedinloginURL = $LinkedinloginURL;
                    }
                } else {
                    $this->view->LinkedinloginURL = $LinkedinloginURL;
                }
            } else if ($linkedin_key !== FALSE && isset($web_values[$linkedin_key])) {
                unset($web_values[$linkedin_key]);
            }
        //FACEBOOK WORK..................................................//
        $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
        if(is_array($web_values)) {
          $fb_key = array_search('facebook', $web_values);
        }
        $Api_facebook = Engine_Api::_()->getApi('facebook_Facebookinvite', 'seaocore');
        //THIS IS A SPACIAL CONDITION TILL 30TH APRIL 2016 IF THE APP IS CREATED AFTER 30 APRIL THEN WE WILL NOT SHOW FACEBOOK TAB HERE.      
        if (method_exists($Api_facebook, 'checkAppReadPermission') && !$Api_facebook->checkAppReadPermission() && $fb_key !== FALSE && isset($web_values[$fb_key]) && $web_values[$fb_key]) {
            unset($web_values[$fb_key]);
        }

        $facebook_userfeed = $Api_facebook->getFBInstance();
        if(is_array($web_values)) {
            $fb_key = array_search('facebook', $web_values);
        }
        if (!empty($settings['appid']) && !empty($settings['secret'])) {
            $FBloginURL = ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()
                            ->assemble(array('module' => 'seaocore', 'controller' => 'auth', 'action' => 'facebook'), 'default', true) . '?' . http_build_query(array('redirect_urimain' => urlencode(( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_fb=1'), 'manage_pages' => $managepage, 'user_managed_groups' => $user_managed_groups));

            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('facebook.enable', Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable == 'publish' ? 1 : 0) || $fb_key !== FALSE) {
                $session = new Zend_Session_Namespace();


                $session_userfeed = $facebook_userfeed;
                $this->view->FBloginURL = '';
                if (!empty($facebook_userfeed)) {

                    $this->view->FBloginURL_temp = $FBloginURL = $FBloginURL;

                    $this->view->FBloginURL = '';
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
                                    if (!$facebookCheck->checkPermission('read_stream', $permissions)) {
                                        $checksiteIntegrate = false;
                                    } else {
                                        $session->fb_canread = true;
                                    }

                                    if (!$facebookCheck->checkPermission('manage_pages', $permissions)) {
                                        $session->fb_can_managepages = false;
                                    } else {
                                        $session->fb_can_managepages = true;
                                    }

                                    if (!$facebookCheck->checkPermission('user_managed_groups', $permissions)) {
                                        $session->fb_can_managegroups = false;
                                    } else {
                                        $session->fb_can_managegroups = true;
                                    }
                                }

                                if ($subject && ((($subject->getType() == 'sitepage_page' || $subject->getType() == 'sitebusiness_business' || $subject->getType() == 'sitestore_store') && !$session->fb_can_managepages) || (($subject->getType() == 'sitegroup_group') && !$session->fb_can_managegroups ))) {
                                  $checksiteIntegrate = false;
                                }
                            } catch (Exception $e) {
                                $checksiteIntegrate = false;
                            }
                        }
                    }
                    if (!$session_userfeed || !$fb_checkconnection || !$checksiteIntegrate) {
                        $this->view->FBloginURL = $FBloginURL;
                    }
                }
            }
        } else if ($fb_key !== FALSE && isset($web_values[$fb_key])) {
            unset($web_values[$fb_key]);
            $fb_key = false;
      }

      //TWITTER WORK............................................................//
      $tweet_key = null;
      if(is_array($web_values)) {
          $tweet_key = array_search('twitter', $web_values);
      }
      
      $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
      if( function_exists('mb_strlen') && !empty($settings['key']) && !empty($settings['secret']) ) {
        $this->view->TwitterLoginURL_temp = $TwitterloginURL = Zend_Controller_Front::getInstance()->getRouter()
            ->assemble(array('module' => 'seaocore', 'controller' => 'auth',
              'action' => 'twitter'), 'default', true) . '?return_url=' . ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->view->url();
        $this->view->TwitterLoginURL = '';
        if( Engine_Api::_()->getApi('settings', 'core')->getSetting('twitter.enable', Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable == 'publish' ? 1 : 0) || $tweet_key !== FALSE ) {
          try {
            $Api_twitter = Engine_Api::_()->getApi('twitter_Api', 'seaocore');
            $twitterOauth = $twitter = $Api_twitter->getApi();
            if( $twitter && $Api_twitter->isConnected() ) {
              $twitterData = (array) $twitterOauth->get(
                  'statuses/home_timeline', array('count' => 1)
              );
              if( isset($twitterData['errors']) )
                $this->view->TwitterLoginURL = $TwitterloginURL;
              //$logged_TwitterUserfeed = $twitter->statuses_homeTimeline(array('count' => 1));
            } else {
              $this->view->TwitterLoginURL = $TwitterloginURL;
            }
          } catch( Exception $e ) {
            $this->view->TwitterLoginURL = $TwitterloginURL;
            // Silence
          }
        }
      } else if( $tweet_key !== FALSE && isset($web_values[$tweet_key]) ) {
        unset($web_values[$tweet_key]);
      }
  }
}
