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
class Advancedactivity_Widget_AdvancedactivitytwitterUserfeedController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    $settingsTwitter = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
    //CHECK IF Twitter KEYS ARE NOT THERE THEN SET NO RENDER:
    if (empty($settingsTwitter['secret']) || empty($settingsTwitter['key']))
      return $this->setNoRender();

    $view = new Zend_View();
    $front = Zend_Controller_Front::getInstance();
    $this->view->curr_url = $front->getRequest()->getRequestUri(); // Return the current URL.
    $this->view->facebooksepage_fbinvite = Engine_Api::_()->getApi('settings', 'core')->getSetting('facebooksepage.fbinvite', 0);
    $aafTwiterType = Zend_Registry::isRegistered('advancedactivity_twitterType') ? Zend_Registry::get('advancedactivity_twitterType') : null;
    $length = $this->_getParam('sitemobiletwitterfeed_length', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
    $limit = $front->getRequest()->getParam('limit', $length); // WHEN THE CURRENT LOADED TWEETS ARE LESS THEN THIS LIMIT VIEW MORE IS NOT SHOWN
    $this->view->checkUpdate = $checkUpdate = $this->_getParam('checkUpdate', false); //PARAMETER TURNS TRUE WHEN THERE ARE NEW UPDATES TO VIEW
    $this->view->getUpdate = $getUpdate = $this->_getParam('getUpdate', false); //USED TO CHECK IF NEW UPDATES ARE AVAILABLE
    $TwitterloginURL = Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble(array('module' => 'seaocore', 'controller' => 'auth',
                        'action' => 'twitter'), 'default', true) . '?return_url=' . 'http://' . $_SERVER['HTTP_HOST'] . $this->view->curr_url;
    $this->view->TwitterLoginURL = '';
    $this->view->isajax = $is_ajax = $this->_getParam('is_ajax', '0');
    $this->view->tabaction = $tabaction = $this->_getParam('tabaction', '0');

    if (!empty($is_ajax) || !empty($getUpdate) || !empty($tabaction)) {

      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    $this->view->twitterGetUpdate = $twitterGetUpdate = Engine_Api::_()->getApi('settings', 'core')->getSetting('getaaf.twitterGetUpdate', 0);
    try {
      $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
      $Api_twitter = new Seaocore_Api_Twitter_Api();
      $twitterOauth = $twitter = $Api_twitter->getApi();
      if (!empty($aafTwiterType) && $Api_twitter->isConnected() && !empty($twitterGetUpdate)) { //CHECKS FOR AUTHORIZATION AND IS TWITTER ENABLED IN API
        // @todo truncation?
        // @todo attachment
        $this->view->endOfFeed_twitter = $limit;
        //GETS THE AUTHORIZED LOGGED IN USER DETAILS
        $account_verifycredentials = (array) $twitterOauth->get(
                        'account/verify_credentials'
        );

        //IF THE USER IS NOT LOGGED IN OR AUTHORIZED THEN SET THE URL TO LOGIN PAGE OF TWITTER
        if (empty($account_verifycredentials) || !isset($account_verifycredentials['id'])) {
          $this->view->TwitterLoginURL = $TwitterloginURL;
          $this->view->session_id = 0;
        } else {

          $this->view->id_CurrentLoggedTweetUser = $account_verifycredentials['id'];
          $this->view->image_CurrentLoggedTweetUser = $account_verifycredentials['profile_image_url'];
          $this->view->screenname_CurrentLoggedTweetUser = $account_verifycredentials['screen_name'];
        }

        //IF USER IS LOGGED IN, IF TWITTER WIDGET IS ENABLED AND IF THE USER IS VIEWING THIS AFTER PAGE LOAD THE GET THE USERS HOME FEEDS
        if (empty($is_ajax) && empty($getUpdate) && empty($this->view->TwitterLoginURL)) {
          $logged_TwitterUserfeed = (array) $twitterOauth->get(
                          'statuses/home_timeline', array('count' => $limit)
          );

          $count = 0;

          //UNSET THE INDEX NOT IN USE AS COUNT WILL BE APPLIED ON ARRAY
          if (isset($logged_TwitterUserfeed['httpstatus']))
            unset($logged_TwitterUserfeed['httpstatus']);

          $this->view->logged_TwitterUserfeed = $logged_TwitterUserfeed;
          $count = count($logged_TwitterUserfeed);
          $count_tweets = $count;
          // --COUNT_TWEET INDEX IS THE LAST AVAILABLE FEED
          $this->view->lastOldTweet = $lastOldTweet = $logged_TwitterUserfeed[--$count_tweets]->id_str;
          if ($count) {
            //IF THE RETWEETED_STATUS IS SET TO THE FIRST INDEX: SET IT TO THE CURRENT TWEET STATUS
            if (isset($logged_TwitterUserfeed[0]->retweeted_status) && !empty($logged_TwitterUserfeed[0]->retweeted_status)) {
              $current_tweet_statusid = $logged_TwitterUserfeed[0]->retweeted_status->id_str;
            } else {
              $current_tweet_statusid = $logged_TwitterUserfeed[0]->id_str;
            }
          }
          $this->view->id_CurrentLoggedTweetUser = $account_verifycredentials['id'];
          $this->view->nextid_twitter = ++$count_tweets;

          //FETCHING THE TWEETS WHICH ARE RETWITTED BY THE LOGGED IN USER
          $retweeted_by_me = (array) $twitterOauth->get(
                          'statuses/user_timeline', array('since_id' => $lastOldTweet)
          );

          //MAKING AN ARRAY OF THE TWEET STATUS IDS WHICH I HAVE RETWEETED.
          $retweets_by_me = array();
          foreach ($retweeted_by_me as $retweet_by_me) {
            if (isset($retweet_by_me->retweeted_status))
              $retweets_by_me[] = $retweet_by_me->retweeted_status->id_str;
          }

          $this->view->retweets_by_me = $retweets_by_me;
          $this->view->current_tweet_statusid = $current_tweet_statusid;
        } else if ($is_ajax == 1) { // IS_AJAX IS SET 1 BEFORE LOGOUT REQUEST OR WHEN VIEW MORE IS CALLED
          $next_prev = $this->_getParam('next_prev', '');
          $max_id = $this->_getParam('max_id', '');
          $since_id = $this->_getParam('since_id', '');
          $duration = $this->_getParam('duration', '');
          $this->view->task = $task = $this->_getParam('task', '');
          $params = array('count' => $limit);
          if (!empty($max_id))
            $params['max_id'] = $max_id;

          $this->view->logged_TwitterUserfeed = $logged_TwitterUserfeed = (array) $twitterOauth->get(
                          'statuses/home_timeline', $params
          );

          if (isset($logged_TwitterUserfeed['httpstatus']))
            unset($logged_TwitterUserfeed['httpstatus']);

          $count = count($logged_TwitterUserfeed);
          $count_tweets = $count;
          $this->view->lastOldTweet = $lastOldTweet = $logged_TwitterUserfeed[--$count_tweets]->id_str;
          if ($count) {
            if (isset($logged_TwitterUserfeed[0]->retweeted_status) && !empty($logged_TwitterUserfeed[0]->retweeted_status)) {
              $current_tweet_statusid = $logged_TwitterUserfeed[0]->retweeted_status->id_str;
            } else {
              $current_tweet_statusid = $logged_TwitterUserfeed[0]->id_str;
            }
            if (!empty($task)) {
              $current_tweet_statusid = $since_id;
            }
          }
          $retweeted_by_me = (array) $twitterOauth->get(
                          'statuses/user_timeline', array('since_id' => $lastOldTweet)
          );

          //MAKING AN ARRAY OF THE TWEET STATUS IDS WHICH I HAVE RETWEETED.

          $retweets_by_me = array();
          foreach ($retweeted_by_me as $retweet_by_me) {
            if (isset($retweet_by_me->retweeted_status))
              $retweets_by_me[] = $retweet_by_me->retweeted_status->id_str;
          }

          $this->view->retweets_by_me = $retweets_by_me;
          $this->view->nextid_twitter = ++$count_tweets;
        } else if ($is_ajax == 2) { // IS_AJAX IS SET 2 WHEN THE USER REPLY ON A POST
          $viewerName = Engine_Api::_()->user()->getViewer()->username;
          $viewerURL = Engine_Api::_()->user()->getViewer()->getHref();
          $viewer = '<a href="' . $viewerURL . '" target="_blank">' . $viewerName . '</a>';
          $post_status = $this->_getParam('post_status', ''); // TEXT IN THE REPLYBOX
          $tweetstatus_id = $this->_getParam('tweetstatus_id', ''); //STATUS ID ON WHICH THE REPLY IS MADE 

          $tweetReply = $twitterOauth->post(
                  'statuses/update', array('in_reply_to_status_id' => $tweetstatus_id, 'status' => html_entity_decode($post_status))
          );


          echo Zend_Json::encode(array('Twitter_statusreply' => 1, 'viewer' => $viewer));
          exit();
        } else if ($is_ajax == 3) { // IS_AJAX IS SET 3 WHEN THE USER RETWEETS
          $tweetstatus_id = $this->_getParam('tweetstatus_id', '');
          $reTweetUpdate = $twitterOauth->post(
                  'statuses/retweet/' . $tweetstatus_id
          );

          echo Zend_Json::encode(array('success' => $reTweetUpdate));
          exit();
        } else if ($is_ajax == 4) {
          $post_status = $this->_getParam('status', '');
          $TweetUpdate = $twitterOauth->post(
                  'statuses/update', array('status' => html_entity_decode($post_status))
          );

          echo Zend_Json::encode(array('success' => $TweetUpdate));
          exit();
        } else if ($is_ajax == 5) { // IS_AJAX IS SET 5 WHEN USER MAKES TWEET FAVORATE/UNFAVORATE 
          $favorite_status_id = $this->_getParam('tweetstatus_id', '');
          $favorite_action = $this->_getParam('favorite_action', '');
          if ($favorite_action == 1) {
            $TweetUpdate = $twitterOauth->post(
                    'favorites/create', array('id' => $favorite_status_id)
            );
          } else {
            $TweetUpdate = $twitterOauth->post(
                    'favorites/destroy', array('id' => $favorite_status_id)
            );
          }
          echo Zend_Json::encode(array('success' => $TweetUpdate));
          exit();
        } else if ($is_ajax == 6) { // IS_AJAX IS SET 6 WHEN USER DELETES A TWEET
          $tweet_status_id = $this->_getParam('tweetstatus_id', '');
          $TweetUpdate = $twitterOauth->post(
                  'statuses/destroy/' . $tweet_status_id
          );

          echo Zend_Json::encode(array('success' => $TweetUpdate));
          exit();
        }

        if (!empty($checkUpdate)) {

          $min_id = $this->_getParam('minid');
          if (!empty($min_id)) {
            $this->view->logged_TwitterUserfeed = $logged_TwitterUserfeed = (array) $twitterOauth->get(
                            'statuses/home_timeline', array('count' => $limit, 'since_id' => $min_id)
            );

            $count_tweets = 0;

            foreach ($logged_TwitterUserfeed as $key => $tweetfeed) {
              $count_tweets++;
              if (empty($task) && $count_tweets > 0) {
                if (isset($tweetfeed->retweeted_status) && !empty($tweetfeed->retweeted_status) && $tweetfeed->retweeted_status->id_str == $min_id) {
                  unset($logged_TwitterUserfeed[$key]);
                  $count_tweets--;
                  break;
                } else if ($tweetfeed->id_str == $min_id) {
                  $count_tweets--;
                  unset($logged_TwitterUserfeed[$key]);
                  break;
                } else {
                  continue;
                }
              }
            }

            if ($count_tweets > 0) {
              if (isset($logged_TwitterUserfeed[0]->retweeted_status) && !empty($logged_TwitterUserfeed[0]->retweeted_status)) {
                $min_id = $logged_TwitterUserfeed[0]->retweeted_status->id_str;
              } else {
                if(isset($logged_TwitterUserfeed[0]))
                $min_id = $logged_TwitterUserfeed[0]->id_str;
              }
            }

            $this->view->Tweet_count = $count_tweets;
            $this->view->current_tweet_statusid = $min_id;
          }
        }

        if (!empty($getUpdate)) {
          if ($this->_getParam('currentaction', '') != 'post_new') {
            $min_id = $this->_getParam('minid');
            $this->view->logged_TwitterUserfeed = $logged_TwitterUserfeed = (array) $twitterOauth->get(
                            'statuses/home_timeline', array('count' => $limit, 'since_id' => $min_id)
            );
            //$logged_TwitterUserfeed = $twitter->statuses_homeTimeline(array('since_id' => $min_id));
            if (isset($logged_TwitterUserfeed['httpstatus']))
              unset($logged_TwitterUserfeed['httpstatus']);

            $count_tweets = 0;
            foreach ($logged_TwitterUserfeed as $key => $tweetfeed) {
              $count_tweets++;
              if ($count_tweets == 1) {
                if (isset($tweetfeed->retweeted_status) && !empty($tweetfeed->retweeted_status)) {
                  $current_tweet_statusid = $tweetfeed->retweeted_status->id_str;
                } else {
                  $current_tweet_statusid = $tweetfeed->id_str;
                }
              }

              if (empty($task) && $count_tweets > 0) {
                if (isset($tweetfeed->retweeted_status) && !empty($tweetfeed->retweeted_status) && $tweetfeed->retweeted_status->id_str == $min_id) {
                  unset($logged_TwitterUserfeed[$key]);
                  $count_tweets--;
                  break;
                } else if ($tweetfeed->id_str == $min_id) {
                  $count_tweets--;
                  unset($logged_TwitterUserfeed[$key]);
                  break;
                } else {
                  continue;
                }
              }
            }
          } else {
            $logged_TwitterUserfeed[0] = $this->_getParam('feedobj');
            if (isset($logged_TwitterUserfeed[0]) && isset($logged_TwitterUserfeed[0]->id_str)) {
              $current_tweet_statusid = $logged_TwitterUserfeed[0]->id_str;
              $count_tweets = 1;
            } else
              $count_tweets = 0;

            $current_tweet_statusid = 0;
          }

          $this->view->logged_TwitterUserfeed = $logged_TwitterUserfeed;
          $account_verifycredentials = (array) $twitterOauth->get(
                          'account/verify_credentials'
          );
          $this->view->id_CurrentLoggedTweetUser = $account_verifycredentials['id'];

          if (!isset($min_id))
            $min_id = 0;

          $this->view->current_tweet_statusid = $current_tweet_statusid;
          $retweeted_by_me = (array) $twitterOauth->get(
                          'statuses/user_timeline', array('since_id' => $min_id)
          );

          //MAKING AN ARRAY OF THE TWEET STATUS IDS WHICH I HAVE RETWEETED.
          $retweets_by_me = array();
          foreach ($retweeted_by_me as $retweet_by_me) {
            if (isset($retweet_by_me->retweeted_status))
              $retweets_by_me[] = $retweet_by_me->retweeted_status->id_str;
          }

          $this->view->retweets_by_me = $retweets_by_me;
        }

        $this->view->Tweet_count = $count_tweets;
        if (empty($this->view->TwitterLoginURL))
          $this->view->session_id = 1;

        if (!empty($is_ajax) || !empty($getUpdate)) {
          $this->getElement()->removeDecorator('Title');
          $this->getElement()->removeDecorator('Container');
        }
      } else {
        $this->view->session_id = 0;
        $this->view->TwitterLoginURL = $TwitterloginURL;
      }
    } catch (Exception $e) {
      $this->view->TwitterLoginURL = $TwitterloginURL;
      $this->view->session_id = 0;
      // Silence
    }
    if (isset($logged_TwitterUserfeed['errors'])) {
      $this->view->TwitterLoginURL = $TwitterloginURL;
      $this->view->session_id = 0;
    }

    //Make all params array which is required when scrolling feeds.

    $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
    $autoScrollFeedEnable = $this->_getParam('sitemobiletwitterfeed_scroll_autoload', 1);
    $maxAutoScrollFeed = $coreSettingsApi->getSetting('advancedactivity.maxautoload', 0);

    if ($this->view->nextid_twitter < $limit)
      $endOfFeed = true;
    else
      $endOfFeed = false;

    $this->view->allParams = array(
        'format' => 'html',
        'max_id' => $this->view->lastOldTweet,
        'next_id' => $this->view->lastOldTweet,
        'endOfFeed' => $endOfFeed,
        'next_prev' => 'next',
        'is_ajax' => 1,
        'url' => 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
        'autoScrollFeedAAFEnable' => $autoScrollFeedEnable,
        'sitemobiletwitterfeed_scroll_autoload' => $autoScrollFeedEnable,
        'maxAutoScrollAAF' => $maxAutoScrollFeed,
        'sitemobiletwitterfeed_length' => $limit,
        'task' => 'activity_more',
        'since_id' => $this->view->current_tweet_statusid
    );

    $photoUploadUrl = 'album/album/compose-upload/type/wall';
    $videoUploadUrl = 'video/index/compose-upload/format/json/c_type/wall';
    $videoDeleteUrl = 'video/index/delete';
    $musicUploadUrl = 'music/playlist/add-song/format/json?ul=1&type=wall';
    $attachmentsURL = array(
        'photourl' => $photoUploadUrl,
        'videourl' => $videoUploadUrl,
        'videodeleteurl' => $videoDeleteUrl,
        'musicurl' => $musicUploadUrl
    );
    $this->view->attachmentsURL = $attachmentsURL;
  }

}
