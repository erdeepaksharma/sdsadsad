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
class Advancedactivity_Widget_AdvancedactivityfacebookUserfeedController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {
 
    $view = new Zend_View();
    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $action = $front->getRequest()->getActionName();
    $this->view->curr_url = $curr_url = $front->getRequest()->getRequestUri(); // Return the current URL.
    $this->view->isajax = $is_ajax = $this->_getParam('is_ajax', '0');
    $this->view->tabaction = $tabaction = $this->_getParam('tabaction', '0');
    if ($is_ajax || $tabaction) {
      
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    $enable_fboldversion = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdna');
    if (!empty($enable_fboldversion)) {
      $socialdnamodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('socialdna');
      $socialdnaversion = $socialdnamodule->version;
      if ($socialdnaversion >= '4.1.1') {
        $enable_fboldversion = 0;
      }
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    $aafacebookType = Zend_Registry::isRegistered('advancedactivity_facebookType') ? Zend_Registry::get('advancedactivity_facebookType') : null;
    $subject = null;
    if (Engine_Api::_()->core()->hasSubject()) {
      // Get subject
      $subject = Engine_Api::_()->core()->getSubject();
      if (!$subject->authorization()->isAllowed($viewer, 'view')) {
        return $this->setNoRender();
      }
    }

    //CHECK IF FACEBOOK KEYS ARE NOT THERE THEN SET NO RENDER:
    
    $settingsFB = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    if (empty($settingsFB['secret']) || empty($settingsFB['appid']))
       return $this->setNoRender();
    $this->view->enable_fboldversion = $enable_fboldversion;
    $length = $this->_getParam('sitemobilefacebookfeed_length', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $limit = $request->getParam('limit', $length);

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->client_id = $settings->core_facebook_appid;
    $session = new Zend_Session_Namespace();
    $Api_facebook = Engine_Api::_()->getApi('facebook_Facebookinvite', 'seaocore');
    if(!$Api_facebook->checkAppReadPermission())
       return $this->setNoRender();
    $facebook_userfeed = Seaocore_Api_Facebook_Facebookinvite::getFBInstance();
    $this->view->getFacebokStream = $getFacebokStream = Engine_Api::_()->getApi('settings', 'core')->getSetting('getaaf.facebook.stream', 0);
    $session_userfeed = $facebook_userfeed;

    $FBloginURL = Zend_Controller_Front::getInstance()->getRouter()
		->assemble(array('module' => 'seaocore', 'controller' => 'auth',	'action' => 'facebook'), 'default', true). '?'.http_build_query(array('redirect_urimain' => urlencode('http://' . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_fb=1')));
    $logged_user = null;
    $uid = 0;
    if (empty($aafacebookType) || empty($getFacebokStream)) {
      //return;
    } 
    // Session based API call.
    $facebookCheck = new Seaocore_Api_Facebook_Facebookinvite();
    $checksiteIntegrate = true;
    $fecheck_Connection = $facebookCheck->checkConnection(null, $facebook_userfeed);
   
    if ($session_userfeed && $fecheck_Connection) { 
          $core_fbenable = Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable;
          $enable_socialdnamodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdna');
          if ( ('publish' == $core_fbenable || 'login' == $core_fbenable || $enable_socialdnamodule) && (!$fecheck_Connection)) { 
             $checksiteIntegrate = false; 
          }
          
    }
    if ($session_userfeed && $checksiteIntegrate) 
    { 
      try {
        
        $this->view->FBuid = $uid = @$_SESSION['facebook_uid'];


        if ($is_ajax == 1) {
          $this->view->checkUpdate = $checkUpdate = $this->_getParam('checkUpdate', '');
          $this->view->getUpdate = $getUpdate = $this->_getParam('getUpdate', '');
          $this->view->changefirstid = 0;
          $this->view->next_previous = $next_prev = $this->_getParam('next_prev', '');
          $duration = $this->_getParam('duration', '');

          if ($next_prev == 'next') {
            $logged_userfeed = $facebook_userfeed->api('/me/home', array('limit' => $limit, 'until' => $duration));
          } else if (!empty($checkUpdate)) {
            $this->view->last_current_id = $last_current_id = $this->_getParam('minid', 0);

            $logged_userfeed = $facebook_userfeed->api('/me/home', array('limit' => $limit, 'since' => $last_current_id));
            $this->view->Facebook_FeedCount = count($logged_userfeed['data']);
          } else if (!empty($getUpdate)) {
            $last_current_id = $this->_getParam('minid', 0);
            $this->view->changefirstid = 1;
            $last_current_action = $this->_getParam('currentaction', '');
            
            if ($last_current_action == 'post_new') {
              $logged_userfeed = $facebook_userfeed->api('/me/feed', array('limit' => $limit, 'since' => $last_current_id)); 
              
            }
            else {
              $logged_userfeed = $facebook_userfeed->api('/me/home', array('limit' => $limit, 'since' => $last_current_id));
            }
             
          } else {
            $this->view->changefirstid = 1;
            $logged_userfeed = $facebook_userfeed->api('/me/home', array('limit' => (int)$limit));
          }


          if (!empty($logged_userfeed)) {
            $total_feedcount = count(@$logged_userfeed['data']);
            if ($total_feedcount < $limit) {
              $this->view->view_morefeed = false;
            } else {
              $this->view->view_morefeed = true;
            }
            if (!empty($getUpdate)) {
              $this->view->data = @array_reverse($logged_userfeed['data']);
            } else {
              $this->view->data = @$logged_userfeed['data'];
            }
            $this->view->paging = @$logged_userfeed['paging'];
            $this->view->logged_userfeed = $logged_userfeed;
          } else {
            $this->view->logged_userfeed = '';
            $this->view->view_morefeed = false;
          }
        } else if ($is_ajax == 2) {
          $current_status = $this->_getParam('status', '');
          $statusUpdate = $facebook_userfeed->api('/me/feed', 'post', array('message' => $current_status, 'cb' => ''));
          echo Zend_Json::encode(array('success' => 1));
          exit();
        } else if ($is_ajax == 3) {
          $post_comment_id = $this->_getParam('post_id', '');
          $action = $this->_getParam('FB_action', '');
          $post_url = $this->_getParam('post_url', '');
          $like_count = $this->_getParam('like_count', '');
          $accessToken = $facebook_userfeed->getAccessToken();
          $statusUpdate = $facebook_userfeed->api('/' . $post_comment_id . '/likes/', $action, array('access_token' => $accessToken, 'cb' => '', 'url' => $post_url));
          if ($action == 'post') :
            $like_people = '<a href="https://www.facebook.com/browse/likes/?id=' . $post_comment_id . '" target="_blank" title="' . $this->view->translate('See people who like this item') . '">' . $like_count . ' ' . $this->view->translate('others') . '</a>';
            $Like_Content = $like_count > 1 ? $this->view->translate('You and ') . $like_people . $this->view->translate(' like this.') : '<li class="aaf_feed_comment_likes_count"><div id="FB_LikesCount_' . $post_comment_id . '">' . $this->view->translate('You like this.') . '</div></li>';
            $like_count++;

          elseif ($action == 'delete'):
            $like_people = '<a href="https://www.facebook.com/browse/likes/?id=' . $post_comment_id . '" target="_blank" title="' . $this->view->translate('See people who like this item') . '">' . --$like_count . ' ' . $this->view->translate('people') . '</a>';
            $Like_Content = $like_count > 1 ? $like_people . $this->view->translate(' like this.') : '';

          endif;
          echo Zend_Json::encode(array('success' => $statusUpdate, 'body' => $Like_Content, 'like_count' => $like_count));
          exit();
        }

        else if ($is_ajax == 4) {
          $post_comment_id = $this->_getParam('post_id', '');
          $action = $this->_getParam('FB_action', '');
          $post_url = $this->_getParam('post_url', '');
          $fbcomment = $this->_getParam('content', '');
          $accessToken = $facebook_userfeed->getAccessToken();
          if ($action == 'post') {
            $statusUpdate = $facebook_userfeed->api('/' . $post_comment_id . '/comments/', 'post', array('access_token' => $accessToken, 'message' => $fbcomment, 'scope' => 'publish_actions'));
          } else {
            $statusUpdate = $facebook_userfeed->api('/' . $post_comment_id . '/', 'delete');
          }
          if ($statusUpdate && $action == 'post') {

            $Message_Length = strlen($fbcomment);
            $id_text = $statusUpdate['id'];
            if ($Message_Length > 200) {
              $message = substr($fbcomment, 0, 200);
              $message = $message . '... <a href="javascript:void(0);" onclick="AAF_showText_More(1, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
              $fbcomment_short = $message;
            } else {
              $fbcomment_short = $fbcomment;
            }
            $fbcomment_long = Engine_Api::_()->advancedactivity()->getURLString($fbcomment);
            $fbcomment_short = Engine_Api::_()->advancedactivity()->getURLString($fbcomment_short);
            $fbcomment_message = '<span id="comment_message"><p id="fbmessage_text_short_' . $id_text . '">' . nl2br($fbcomment_short) . '</p><div id="fbmessage_text_full_' . $id_text . '" style="display:none;">' . nl2br($fbcomment_long) . '</div></span>';
            $fb_comment = '<li id="' . $statusUpdate['id'] . '"><div class="comments_author_photo"><a href="' . $logged_user['link'] . '" target="_blank"><img src="https://graph.facebook.com/' . $_SESSION['facebook_uid'] . '/picture" alt="" width="32" /></a></div><div class="comments_info"><a href="javascript:void(0);" onclick="confirm_deletecommentfb(\'' . $statusUpdate['id'] . '\',\'delete\',\'' . $post_url . '\')" class="aaf_fb_comment_remove" title="'. $this->view->translate('Remove') .'"></a><span class="comments_author"><a href="' . $logged_user['link'] . '" target="_blanck">' . $logged_user['name'] . ' ' . '</a></span>' . $fbcomment_message . '<ul class="comments_date"><li class="comments_delete">' . $this->view->timestamp(time()) . '</li></ul></div></li>';
            echo Zend_Json::encode(array('body' => $fb_comment));
          } else if ($statusUpdate) {
            echo Zend_Json::encode(array('success' => $statusUpdate));
          }

          exit();
        } else if ($is_ajax == 0) {

          $logged_userfeed = $facebook_userfeed->api('/me/home', array('limit' => (int)$limit));
         
          $this->view->data = @$logged_userfeed['data'];

          $this->view->paging = @$logged_userfeed['paging'];

          $this->view->logged_userfeed = $logged_userfeed;
          $total_feedcount = count(@$logged_userfeed['data']);
          if ($total_feedcount < $limit) {
            $this->view->view_morefeed = 0;
          } else {
            $this->view->view_morefeed = 1;
          }
          $this->view->changefirstid = 1;


          $this->view->enableComposer = false;
          if ($viewer->getIdentity() && !$this->_getParam('action_id')) {
            if (!$subject || $subject->authorization()->isAllowed($viewer, 'comment')) {
              $this->view->enableComposer = true;
            }
          }
          // Assign the composing values

          $composePartials = array();
          foreach (Zend_Registry::get('Engine_Manifest') as $data) {
            if (empty($data['composer']) || !empty($data['composer']['facebook']) || !empty($data['composer']['twitter'])) {
              continue;
            }
            foreach ($data['composer'] as $type => $config) {
              if (!empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1])) {
                continue;
              }
              $composePartials[] = $config['script'];
            }
          }

          $this->view->composePartials = $composePartials;
        }
        
        //Make all params array which is required when scrolling feeds.
        $paging = $this->view->paging; 
        $last_fbid = 0;
        if (isset($paging['next'])) {
          $last_fbid = explode("&until=",$paging['next']);
          $last_fbid = $last_fbid[1];
        }
        
        $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
        $autoScrollFeedEnable = $this->_getParam('sitemobilefacebookfeed_scroll_autoload', 1);
        $maxAutoScrollFeed = $coreSettingsApi->getSetting('advancedactivity.maxautoload', 0);        
        
        $this->view->allParams = array('format' => 'html', 'duration' => $last_fbid, 'next_id' => $last_fbid, 'endOfFeed' => !$this->view->view_morefeed ,'next_prev' => 'next', 'is_ajax' => 1, 'url' => 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed', 'autoScrollFeedAAFEnable' => $autoScrollFeedEnable, 'sitemobilefacebookfeed_scroll_autoload' => $autoScrollFeedEnable, 'maxAutoScrollAAF' => $maxAutoScrollFeed, 'sitemobilefacebookfeed_length' => $limit);
        
        
        $photoUploadUrl = 'album/album/compose-upload/type/wall';
        $videoUploadUrl = 'video/index/compose-upload/format/json/c_type/wall';
        $videoDeleteUrl = 'video/index/delete';
        $musicUploadUrl = 'music/playlist/add-song/format/json?ul=1&type=wall';
        $attachmentsURL = array('photourl' => $photoUploadUrl, 'videourl' => $videoUploadUrl, 'videodeleteurl' => $videoDeleteUrl,
        'musicurl' => $musicUploadUrl);
        $this->view->attachmentsURL = $attachmentsURL;

        if (!empty($is_ajax)) {
          $this->getElement()->removeDecorator('Title');
          $this->getElement()->removeDecorator('Container');
        }
      } catch (Exception $e) {
        $this->view->loginUrl = $FBloginURL;
        $this->view->logged_userfeed = '';
      }
    }
    
    if (!$uid) {
      $this->view->loginUrl = $FBloginURL;
      //return $this->setNoRender();
    } else if (empty($this->view->loginUrl)) {
      $this->view->session_id = $uid;
      
    }   
      
  }

}