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
class Advancedactivity_Widget_AdvancedactivitylinkedinUserfeedController extends Seaocore_Content_Widget_Abstract {
  
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
   
    $subject = null;
    if (Engine_Api::_()->core()->hasSubject()) {
      // Get subject
      $subject = Engine_Api::_()->core()->getSubject();
      if (!$subject->authorization()->isAllowed($viewer, 'view')) {
        return $this->setNoRender();
      }
    }
    //CHECK IF LInkedin KEYS ARE NOT THERE THEN SET NO RENDER:
    
    $linkedin_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey');
    $linkedin_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey');
    if (empty($linkedin_apikey) || empty($linkedin_secret))
       return $this->setNoRender();
    $this->view->enable_fboldversion = $enable_fboldversion;
    $length = $this->_getParam('sitemobilelinkedinfeed_length', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $limit = $request->getParam('limit', $length);


     $LinkedinloginURL = Zend_Controller_Front::getInstance()->getRouter()
		->assemble(array('module' => 'seaocore', 'controller' => 'auth',	'action' => 'linkedin'), 'default', true). '?'.http_build_query(array('redirect_urimain' => urlencode('http://' . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_linkedin=1'))); 

    
   
    $this->view->LinkedinLoginURL = $LinkedinloginURL;
    //Session based API call.
    
    $limit = $request->getParam('limit', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
      try { 
        $Api_linkedin = new Seaocore_Api_Linkedin_Api();
        $callbackUrl = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url() . '?redirect_urimain=1';
        $linkedinTable = Engine_Api::_()->getDbtable('linkedin', 'advancedactivity');
        $Api_linkedin = Engine_Api::_()->getApi('linkedin_Api', 'seaocore');
        $LinkedinObj = $Api_linkedin->getApi();
        if (!$LinkedinObj) return;
        $getlinkedintUserinfo = $LinkedinObj->profile('~:(id)');
        $getlinkedintUserinfo = json_decode(json_encode((array) simplexml_load_string($getlinkedintUserinfo['linkedin'])), 1);
        if (isset($getlinkedintUserinfo['id'])) {
					$this->view->currentuser_id = $getlinkedintUserinfo['id'];
        } else
          return;
       
        
        if ($is_ajax == 1) { 
          $this->view->checkUpdate = $checkUpdate = $this->_getParam('checkUpdate', '');
          $this->view->getUpdate = $getUpdate = $this->_getParam('getUpdate', '');
          $this->view->changefirstid = 0;
          $this->view->next_previous = $next_prev = $this->_getParam('next_prev', '');
          $duration = $this->_getParam('duration', '');
          $last_current_action = '';
          $LinkedinFeeds = array();
          if ($next_prev == 'next') { 
            $limit_next = $limit++; 
            $options = '?count='.$limit_next.'&before=' .$duration ; 
          
            $feedinfo = $Api_linkedin->getContent ($limit_next, $callbackUrl, 'before', $duration);
            array_shift($feedinfo['feedcontent']['update']);
            $LinkedinFeeds = $feedinfo['feedcontent'];
            
          } else if (!empty($checkUpdate)) { 
							$this->view->last_current_id = $last_current_id = $this->_getParam('minid', 0);
							if (!empty($last_current_id)) {
								$feedinfo = $Api_linkedin->getContent ($limit, $callbackUrl, 'after', $last_current_id);
								$LinkedinFeeds = $feedinfo['feedcontent'];
               
                $this->view->Linkedin_FeedCount = $total_feedcount = count($LinkedinFeeds['update']);
                foreach($LinkedinFeeds['update'] as $key => $feed) {
                  if($last_current_id == $feed['timestamp']) { 
                       unset($LinkedinFeeds['update'][$key]);
                    }
                }
                
							}
            
          } else if (!empty($getUpdate)) {
							$last_current_id = $this->_getParam('minid', 0);
							$this->view->changefirstid = 1;
							$last_current_action = $this->_getParam('currentaction', '');           
							if (!empty($last_current_action)) 
							   $feedinfo = $Api_linkedin->getContent (2, $callbackUrl);
							else
								$feedinfo = $Api_linkedin->getContent ($limit, $callbackUrl, 'after', $this->_getParam('minid', 0));
              if(!empty($last_current_id)) {
                foreach($feedinfo['feedcontent']['update'] as $key => $feed) {
                  if($last_current_id == $feed['timestamp']) { 
                       unset($feedinfo['feedcontent']['update'][$key]);
                    }
                }
              }
              
							$LinkedinFeeds = $feedinfo['feedcontent'];   
             
          } else {
							$this->view->changefirstid = 1;
							$options = '?count='.$limit;  
							$LinkedinFeeds = $Api_linkedin->getContent ($limit, $callbackUrl);            
          }
          
          if (isset($LinkedinFeeds['update']) && $LinkedinFeeds['update'][0]) { 
						  $this->view->Linkedin_FeedCount = $total_feedcount = count($LinkedinFeeds['update']);
							if (!empty($checkUpdate) || (!empty($getUpdate) && empty($last_current_action)))
									array_pop($LinkedinFeeds['update']);
							else if (!empty($next_prev))
									array_shift($LinkedinFeeds['update']);
							else if (!empty($last_current_action) && $total_feedcount == 2)	
									array_pop($LinkedinFeeds['update']);
							$this->view->Linkedin_FeedCount = count($LinkedinFeeds['update']);
							   if ($total_feedcount == 1 && empty($last_current_action)) 
									  $this->view->Linkedin_FeedCount = 0;
					}
					else {
						$this->view->Linkedin_FeedCount = $total_feedcount= 1;
						if ((!empty($next_prev) || !empty($checkUpdate) || (!empty($getUpdate) && empty($last_current_action)))) {
								unset($LinkedinFeeds['update']);
								$this->view->Linkedin_FeedCount = 0;
						}
						
					}
					
					if (!isset ($LinkedinFeeds['update'])) {
					
					  $this->view->Linkedin_FeedCount = 0;
					}
					
          $this->view->LinkedinFeeds = $LinkedinFeeds; 

					} else if ($is_ajax == 2) { 
      
							$message = (string)$this->_getParam('body', '');
							$recepient = $this->_getParam('memberid', '');
							$subject = $this->_getParam('title', '');
							$response = $LinkedinObj->message (array('0' => $recepient), $subject, $message);							
							echo Zend_Json::encode(array('response' => $response));         
							exit();
						} else if ($is_ajax == 3) {
								$post_like_id = $this->_getParam('post_id', '');
								$action = $this->_getParam('Linkedin_action', '');
								$like_count = $this->_getParam('like_count', '');
								if ($action == 'like') :
										$response = $LinkedinObj->like ($post_like_id);
								
										$Like_Content = $like_count > 1 ? $this->view->translate('You and ') . $like_count . ' ' . $this->view->translate('others') . $this->view->translate(' like this.') : '<li class="aaf_feed_comment_likes_count"><i class="aaf_feed_linkedin_like_icon aaf_feed_fb_icon"></i><div class="Linkedin_LikesCount">' . $this->view->translate('You like this.') . '</div></li>';
																$like_count++;
								else :
										$response = $LinkedinObj->unlike ($post_like_id);				   
									$Like_Content = $like_count > 1 ? --$like_count . ' ' . $this->view->translate('people') . $this->view->translate(' like this.') : '';
								endif;
								
								echo Zend_Json::encode(array('success' => $response['success'], 'body' => $Like_Content, 'like_count' => $like_count));
								exit();        
					  }
   

						else if ($is_ajax == 4) {
							$post_comment_id = $this->_getParam('post_id', '');
							$action = $this->_getParam('Linkedin_action', '');
							$linkedincomment = $this->_getParam('content', '');
							$likecount = $this->_getParam('like_count', 0);
							if ($action == 'post') {
								$response = $LinkedinObj->comment($post_comment_id, $linkedincomment);
								//GET COMMENTED USER INFO.
							
								$getcommetUserinfo = $LinkedinObj->profile('~:(first-name,last-name,site-standard-profile-request,picture-url)');
								$getcommetUserinfo = json_decode(json_encode((array) simplexml_load_string($getcommetUserinfo['linkedin'])), 1); 
								//CHECK IF SITEMOBILE PLUGIN IS ENABLED AND SITE IS IN MOBILE MODE:
								$enable_sitemobilemodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
								if ($enable_sitemobilemodule)
									$checkMobMode = Engine_Api::_()->sitemobile()->checkMode('mobile-mode') || Engine_Api::_()->sitemobile()->checkMode('tablet-mode');
								else
									$checkMobMode = 0;
							  if ($checkMobMode) { 
							    $data['body'] = $linkedincomment;
							    $data['post_id'] = $post_comment_id;
							    $data['like_count'] = $likecount;
									$data = array_merge($data, array(
										'user' => $getcommetUserinfo
												));
								$body =  $this->view->partial(
																'application/modules/Sitemobile/modules/Advancedactivity/views/scripts/socialfeed/getlinkedincomment.tpl', 'advancedactivity', $data
								);
								  echo Zend_Json::encode(array('body' => $body));
									exit();
							  }
							}
							if ($action == 'post' && !$checkMobMode) {
								
								$Message_Length = strlen($linkedincomment);
								$id_text = '';
								if ($Message_Length > 200) {
									$message = substr($linkedincomment, 0, 200);
									$message = $message . '... <a href="javascript:void(0);"  class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
									$linkedincomment_short = $message;
								} else {
										$linkedincomment_short = $linkedincomment;
								}
							
								
								$linkedincomment_long = Engine_Api::_()->advancedactivity()->getURLString($linkedincomment);
								$linkedincomment_short = Engine_Api::_()->advancedactivity()->getURLString($linkedincomment_short);
								$linkedincomment_message = '<span id="comment_message"><p class="linkedinmessage_text_short" onclick="AAF_showText_More_linkedin(1, this);">' . nl2br($linkedincomment_short) . '</p><div class="linkedinmessage_text_full" style="display:none;">' . nl2br($linkedincomment_long) . '</div></span>';
								if (isset($getcommetUserinfo['picture-url'])) {
									$image_url = $getcommetUserinfo['picture-url'];
								
								}
								else {
									$image_url = $this->view->layout()->staticBaseUrl. 'application/modules/User/externals/images/nophoto_user_thumb_icon.png';
								}
								$commenter_photo = '<img src="'.  $image_url. '" alt="" class="thumb_icon item_photo_user" />';
								$linkedin_comment = '<li id="latestcomment-' . $id_text . '"><div class="comments_author_photo"><a href="' . $getcommetUserinfo['person']['site-standard-profile-request']['url'] . '" target="_blank">'. $commenter_photo .'</a></div><div class="comments_info" id="comments_info_linkedin-'.$id_text.'" >	<span class="comments_author">
																									<a href="' . $getcommetUserinfo['site-standard-profile-request']['url'] . '" target="_blanck">' . $getcommetUserinfo['first-name'] . ' ' . $getcommetUserinfo['last-name'] . '</a></span>' . $linkedincomment_message . '<ul class="comments_date"><li class="comments_delete">' . $this->view->timestamp(time()) . '</li></ul></div></li>';
							
							
							echo Zend_Json::encode(array('body' => $linkedin_comment));
							} else if ($statusUpdate) {
								echo Zend_Json::encode(array('success' => 1));
							}

							exit();
						} else if ($is_ajax == 0) { 
           
								$feedinfo = $Api_linkedin->getContent ($limit,$callbackUrl);
								
								if (isset ($feedinfo['feedcontent']['update'])) {
									if (isset($feedinfo['feedcontent']['update'][0])) 
										$this->view->Linkedin_FeedCount = count($feedinfo['feedcontent']['update']);
									else
										$this->view->Linkedin_FeedCount = 1;
										
										$this->view->LinkedinFeeds = $feedinfo['feedcontent'];
								} else {
								    $this->view->Linkedin_FeedCount = 0;
								}
								
								   $this->view->LinkedinLoginURL = '';
           
//								if (empty($this->view->Linkedin_FeedCount))
//									$this->view->LinkedinLoginURL = $LinkedinloginURL;         
						}

						
      } catch (Exception $e) {
        $this->view->LinkedinLoginURL = $LinkedinloginURL;
        $this->view->logged_userfeed = '';
      }
      
      $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
      $autoScrollFeedEnable = $this->_getParam('sitemobilelinkedinfeed_scroll_autoload', 1);
      $maxAutoScrollFeed = $coreSettingsApi->getSetting('advancedactivity.maxautoload', 0);        

      if ($this->view->Linkedin_FeedCount > 1)
      $last_linkedin_timestemp = $this->view->LinkedinFeeds['update'][--$this->view->Linkedin_FeedCount]['timestamp'];
    else if ($this->view->Linkedin_FeedCount == 1)
      $last_linkedin_timestemp = $this->view->LinkedinFeeds['update']['timestamp'];
    else
      $last_linkedin_timestemp = 0;

      $endOfFeed = false;
      if ($this->view->Linkedin_FeedCount < ($limit-1)) 
        $endOfFeed = true;
      
      $this->view->allParams = array('format' => 'html', 'duration' => $last_linkedin_timestemp, 'next_id' => $last_linkedin_timestemp, 'endOfFeed' => $endOfFeed ,'next_prev' => 'next', 'is_ajax' => 1, 'url' => 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed', 'autoScrollFeedAAFEnable' => $autoScrollFeedEnable, 'sitemobilelinkedinfeed_scroll_autoload' => $autoScrollFeedEnable, 'maxAutoScrollAAF' => $maxAutoScrollFeed, 'sitemobilelinkedinfeed_length' => $limit, 'count' => $this->view->Linkedin_FeedCount);


      $photoUploadUrl = 'album/album/compose-upload/type/wall';
      $videoUploadUrl = 'video/index/compose-upload/format/json/c_type/wall';
      $videoDeleteUrl = 'video/index/delete';
      $musicUploadUrl = 'music/playlist/add-song/format/json?ul=1&type=wall';
      $attachmentsURL = array('photourl' => $photoUploadUrl, 'videourl' => $videoUploadUrl, 'videodeleteurl' => $videoDeleteUrl,
      'musicurl' => $musicUploadUrl);
      $this->view->attachmentsURL = $attachmentsURL;  
    
  } 
 
}