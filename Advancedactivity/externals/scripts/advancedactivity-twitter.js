/* $Id: advancedactivity-twitter.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
 Technologies Pvt. Ltd. $  */
//JAVA SCRIPT FOR USER FACEBOOK FRIENDS SHOWING ADDING TO LIST.
//COPIED FROM USER MODULE/WIDGET/PROFILE-FRINED/INDEX.TPL
var userfeed_limit = 5;
var firstfeedid_Tweet, feed_viewmore_tweet, feed_no_more_tweet, feed_loading_tweet, feed_view_more_tweet_link, activityUpdateHandler_Tweet, feedUpdate_tweet, activityFeed_tweet, update_freq_tweet;
var tweet_loginURL = '';
var tweet_loginURL_temp = '';
var action_logout_taken_tweet = 0;
var Tweet_lenght = 0;


//THIS FUNCTION SHOWS USER'S RECENT FACEBOOK FEEDS.

function rhtmlspecialchars(str) {
  if (typeof (str) == "string") {
    str = str.replace(/&gt;/ig, ">");
    str = str.replace(/&lt;/ig, "<");
    str = str.replace(/&#039;/g, "'");
    str = str.replace(/&quot;/ig, '"');
    str = str.replace(/&amp;/ig, '&'); /* must do &amp; last */
  }
  return str;
}

function bindKeyUpEventForTweetPost() {
  if (typeof window.composeInstanceTweeter != 'undefined') {
    var body = window.composeInstanceTweeter.elements.body;
    body.addEvent('keyup', function () {
      //body.set('value', composeInstance.getContent());
      $('aaf-twitter_show_loading_main').style.display = 'block';
      body.value = rhtmlspecialchars(body.value);
      var tweetlength_temp = body.value.length;
      if (tweetlength_temp > 140) {
        $('aaf-twitter_show_loading_main').innerHTML = parseInt(140) - tweetlength_temp;
        $('aaf-twitter_show_loading_main').setStyle('color', 'red');
        Tweet_lenght = 1;
        var html = body.value.substring(0, 140)+'<em style="background:#ffb8c2">'+body.value.substring(140)+'</em>';
        window.composeInstanceTweeter.highlighterBody.set('html', html);
      } else {
        Tweet_lenght = 0;
        $('aaf-twitter_show_loading_main').innerHTML = parseInt(140) - tweetlength_temp;
        $('aaf-twitter_show_loading_main').setStyle('color', '');
      }
    });
  }
}

function setKeyUpEvent_Tweet() {

}

function getCommonTweetElements() {
  if ($('feed_viewmore_tweet')) {
    feed_viewmore_tweet = $('feed_viewmore_tweet');
    feed_loading_tweet = $('feed_loading_tweet');
    feed_no_more_tweet = $('feed_no_more_tweet');
    feed_view_more_tweet_link = $('feed_viewmore_tweet_link');
    feedUpdate_tweet = $('feed-update-tweet');
    activityFeed_tweet = $('Tweet_activity-feed');
  }
}

var Call_TweetcheckUpdate = function (last_id) {

  //en4.core.runonce.add(function() {
  try {
    activityUpdateHandler_Tweet = new AdvancedActivityUpdateHandler_Tweet({
      'baseUrl': en4.core.baseUrl,
      'basePath': en4.core.basePath,
      'identity': 4,
      'delay': update_freq_tweet,
      'last_id': last_id,
      'feedContentURL': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed'

    });

    setTimeout("activityUpdateHandler_Tweet.start('twitter')", update_freq_tweet);
    //activityUpdateHandler.start();
    window._activityUpdateHandler_Tweet = activityUpdateHandler_Tweet;

  } catch (e) {
    //if( $type(console) ) console.log(e);
  }
  // });

}

//THIS FUNCTION EXECUTE WHEN USER WRITE STH IN USER STATUS BOX.

//FUNCTION NOT BEING CALLED FROM ANY PLUGIN OR MODULE
//function AAF_TweetsetStatus() {
//  status1 = document.getElementById('aaf_set_Tweetstatus').value;
//  if (status1 == '') {
//		return ;
//  }
//		
//		$('show_loading_main').innerHTML = '<div class="seaocore_view_more"><img src="application/modules/Seaocore/externals/images/core/loading.gif" /></div>';
//   userfeed_url =  en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed'; 
//		var request = new Request.JSON({
//			'url' : userfeed_url,
//			//'method':'get',
//			'data' : {
//								//'format' : 'json',
//               	'status' : status1,
//								'is_ajax' : '4'
//		
//							},
//			onSuccess : function(responseJSON) { 
//				if (!responseJSON){ 
//				  en4.core.showError('<div class="aaf_show_popup"><p>Your Twitter status could not be updated. Please try again.' + '</p><button onclick="Smoothbox.close()">Close</button></div>');
//					
//				}
//				else { 
//					activityUpdateHandler_Tweet_temp = new AdvancedActivityUpdateHandler_Tweet({});
//			    setTimeout("activityUpdateHandler_Tweet_temp.getFeedUpdate(firstfeedid_Tweet)", 3000);			
//					
//					document.getElementById('aaf_set_Tweetstatus').set('class', 'sink_textarea');
//					 $('show_loading_main').innerHTML = 140;
//					 document.getElementById('aaf_set_Tweetstatus').value = '';
//					 document.getElementById('aaf_set_Tweetstatus').focus();
//					
//				}
//      }
//    });
//    request.send();
//  
//}


//THIS  FUNCTIONS SHOWS ALL FACEBOOK NONSITE FRINEDS.
var limit = 5;
var start = 0;
var next = 0;


function showText_More(type_text, id) {
  if (type_text == '1') {
    var full_text = $('fbmessage_text_full_' + id).innerHTML;
    $('fbmessage_text_short_' + id).innerHTML = full_text;
  } else if (type_text == '2') {
    var full_text = $('fbdescript_text_full_' + id).innerHTML;
    $('fbdescript_text_short_' + id).innerHTML = full_text;
  }

}

//MAKING A TEXTAREA FOR REPLYING TO A TWEET...............
var prev_tweetstatus_id = 0;
var prev_tweetstatus_screenname = 0;

var reply_Tweet = function (tweetstatus_id, screen_name) {
  if (typeof is_enter_submitothersocial != 'undefined' && is_enter_submitothersocial == 1) {
    $('activity-comment-body-twitter-submit').setStyle('display', 'none');
  }
  if (prev_tweetstatus_id != 0 && prev_tweetstatus_id != tweetstatus_id) {
    var Container = $('reply_' + prev_tweetstatus_id);
    if (Container != null) {
      var catarea = Container.parentNode;
      catarea.removeChild(Container);
    }

  }
  var newdiv = document.createElement('div');
  if ($('reply_' + tweetstatus_id) == null) {
    prev_tweetstatus_id = tweetstatus_id;
    prev_tweetstatus_screenname = screen_name;

    var referenceNode = $('reply_retweet_' + tweetstatus_id);
    var parent = referenceNode.parentNode;
    newdiv = document.createElement('div');
    newdiv.id = 'reply_' + tweetstatus_id;
    newdiv.set('class', 'comments aaf_tweet_reply_box');
    newdiv.innerHTML = $('reply_textarea').innerHTML;
    parent.insertBefore(newdiv, referenceNode.nextSibling);

    $(newdiv.id).getElement('.activity-comment-body-twitter').focus();
    $(newdiv.id).getElement('.activity-comment-body-twitter').value = '@' + screen_name + ' ';
    $(newdiv.id).getElement('.reply_headingid').innerHTML = 'Reply to @' + screen_name;
    $(newdiv.id).getElement('.show_loading').innerHTML = 140 - $(newdiv.id).getElement('.activity-comment-body-twitter').value.length;
    $(newdiv.id).getElement('.activity-comment-body-twitter').autogrow();

  } else {
    if ($('reply_' + tweetstatus_id).getElement('.activity-comment-body-twitter').value == '') {
      $('reply_' + tweetstatus_id).getElement('.activity-comment-body-twitter').value = '@' + screen_name + ' ';
    }
  }

  var thisobj = $('activity-comment-body-twitter');
  if (typeof is_enter_submitothersocial != 'undefined' && is_enter_submitothersocial == 1) {
    thisobj.addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'keydown' : 'keypress', function (event) {
      if (event.shift && event.key == 'enter') {
      } else if (event.key == 'enter') {
        event.stop();
        post_status();
      }
    });
    // add blur event				
  }
}


var post_status = function () {

  $('reply_' + prev_tweetstatus_id).getElement('.show_loading').innerHTML = '<div><img src="application/modules/Seaocore/externals/images/core/loading.gif" /></div>';
  var request = new Request.JSON({
    'url': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
    //'method':'get',
    'data': {
      //'format' : 'json',
      'is_ajax': '2',
      'post_status': $('reply_' + prev_tweetstatus_id).getElement('.activity-comment-body-twitter').value,
      'tweetstatus_id': prev_tweetstatus_id

    },
    onSuccess: function (responseJSON) {
      if (responseJSON != null && responseJSON.Twitter_statusreply == 1) {
        $('reply_' + prev_tweetstatus_id).set('class', 'comments aaf_tweet_reply_box');
        $('reply_' + prev_tweetstatus_id).innerHTML = '<ul><li><div class="comments_likes"><b class="aaf_icon_success"><img src="./application/modules/Seaocore/externals/images/core/notice.png" class="aaf_icon" alt="Success" />' + en4.core.language.translate('Shared on Twitter as reply to') + ' @' + prev_tweetstatus_screenname + '</b></div></li><li><div class="comments_author_photo">' + $('User_Photo').innerHTML + '</div><div class="comments_info"><span class="comments_author"><a href="https://twitter.com/' + prev_tweetstatus_screenname + '" target="_blank">' + prev_tweetstatus_screenname + '</a></span>' + ' ' + $('reply_' + prev_tweetstatus_id).getElement('.activity-comment-body-twitter').value + '</div></li></ul>';

        setTimeout("fade_Twitter('reply_' + prev_tweetstatus_id, 2)", 5000);
        activityUpdateHandler_Tweet_temp = new AdvancedActivityUpdateHandler_Tweet({});
        setTimeout("activityUpdateHandler_Tweet_temp.getFeedUpdate(firstfeedid_Tweet)", 4000);
      } else {
        $('show_loading').innerHTML = 140 - $('activity-comment-body-twitter').value.length;
        Smoothbox.open('<div>' + en4.core.language.translate('We were unable to process your request. Wait a few moments and try again.') + '<button name="close" onclick="javascript:Smoothbox.close();"</button></div>')
      }

    }
  });
  request.send();
}


// ===============================================	Start - Fading Effects ================================================

var TimeToFade_aaf = 300.0;

function fade_Twitter(eid, modFlag)
{
  var element = document.getElementById(eid);
  if (element == null)
    return;

  element.FadeState = modFlag;
  if (element.FadeState == null)
  {
    if (element.style.opacity == null
            || element.style.opacity == ''
            || element.style.opacity == '1')
    {
      element.FadeState = 2;
    } else
    {
      element.FadeState = -2;
    }
  }

  if (element.FadeState == 1 || element.FadeState == -1)
  {
    element.FadeState = element.FadeState == 1 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade_aaf - element.FadeTimeLeft;
    var catarea = $(eid).parentNode;
    catarea.removeChild($(eid));
  } else
  {
    element.FadeState = element.FadeState == 2 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade_aaf;
    setTimeout("animateFade_Twitter(" + new Date().getTime() + ",'" + eid + "')", 33);
  }

}





function animateFade_Twitter(lastTick, eid)
{
  var curTick = new Date().getTime();
  var elapsedTicks = curTick - lastTick;

  var element = document.getElementById(eid);

  if (element.FadeTimeLeft <= elapsedTicks)
  {
    element.style.opacity = element.FadeState == 1 ? '1' : '0';
    element.style.filter = 'alpha(opacity = '
            + (element.FadeState == 1 ? '100' : '0') + ')';
    element.FadeState = element.FadeState == 1 ? 2 : -2;
    var catarea = $(eid).parentNode;
    catarea.removeChild($(eid));
    return;
  }

  element.FadeTimeLeft -= elapsedTicks;
  var newOpVal = element.FadeTimeLeft / TimeToFade_aaf;
  if (element.FadeState == 1)
    newOpVal = 1 - newOpVal;

  element.style.opacity = newOpVal;
  element.style.filter = 'alpha(opacity = ' + (newOpVal * 100) + ')';

  setTimeout("animateFade_Twitter(" + curTick + ",'" + eid + "')", 33);
}

// ===============================================	End - Fading Effects ================================================


//PUTTING THE LIMIT ON THE TEXT AREA CONTENT TO BE ONLY 140 CHARACTER:

function limitText(limitField, limitNum) {
  limitField = $('reply_' + prev_tweetstatus_id).getElement('.activity-comment-body-twitter');
  if (limitField.value.length > limitNum) {
    limitField.value = limitField.value.substring(0, limitNum);
  } else {
    $('reply_' + prev_tweetstatus_id).getElement('.show_loading').innerHTML = limitNum - limitField.value.length;
  }
}


//RETWEET A TWEET :

var reTweet = function (tweetstatus_id) {

  $('retweet_tweet_' + tweetstatus_id).innerHTML = en4.core.language.translate('Updating...');
  var request = new Request.JSON({
    'url': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
    //'method':'get',
    'data': {
      //'format' : 'json',
      'is_ajax': '3',
      'tweetstatus_id': tweetstatus_id

    },
    onSuccess: function (responseJSON) {
      $('retweet_tweet_' + tweetstatus_id).innerHTML = '<span class="aaf_tweet_icon_retweeted aaf_tweet_icon">Retweeted</span>';
    }
  });
  request.send();
}

//SHOW MORE TWITTER FEED ON SCROLLING.....
var last_oldtweet_id = 0;
var twitter_ActivityViewMore = function (since_id) {


  if (activity_type == 2) {
    feed_view_more_tweet_link.removeEvents('click').addEvent('click', function (event) {
      event.stop();
      twitter_ActivityViewMore(lastOldTweet);
    });
  }
  if (en4.core.request.isRequestActive())
    return;
  if (activity_type != 2) {
    return;
  }
  //lastOldTweet = 0;

  feed_viewmore_tweet.style.display = 'none';
  feed_loading_tweet.style.display = 'block';
  en4.core.request.send(new Request.HTML({
    'url': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
    //'method':'get',
    'data': {
      'format': 'html',
      'is_ajax': '1',
      'max_id': since_id,
      'since_id': firstfeedid_Tweet,
      'task': 'activity_more'

    },
    onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
      if (activity_type == 2) {
        countScrollAAFTweet++;
        en4.core.runonce.trigger();
        //feed_loading_tweet.style.display = 'none';
        //feed_viewmore_tweet.style.display = 'block';
//  		  var newdiv = document.createElement('div');
//  		  newdiv.id = 'tweeterfeed_' + since_id;
        //newdiv.innerHTML = responseHTML;
        if (activityFeed_tweet) {
          Elements.from(responseHTML).inject(activityFeed_tweet);
        } else if ($('Tweet_activity-feed')) {
          Elements.from(responseHTML).inject($('Tweet_activity-feed'));
        }

      }

    }
  }));

}


var Tweet_doOnScrollLoadActivity = function () {
  if (typeof (feed_viewmore_tweet.offsetParent) != 'undefined') {
    var elementPostionY = feed_viewmore_tweet.offsetTop;
  } else {
    var elementPostionY = feed_viewmore_tweet.y;
  }
  if ((maxAutoScrollAAF == 0 || countScrollAAFTweet < maxAutoScrollAAF) && autoScrollFeedAAFEnable && elementPostionY <= window.getScrollTop() + (window.getSize().y - 40)) {
    twitter_ActivityViewMore(lastOldTweet);

  } else {
    if (feed_viewmore_tweet) {
      feed_viewmore_tweet.style.display = '';
      feed_loading_tweet.style.display = 'none';

      feed_view_more_tweet_link.removeEvents('click').addEvent('click', function (event) {
        event.stop();
        twitter_ActivityViewMore(lastOldTweet);
      });
    }
    if (!autoScrollFeedAAFEnable) {
      window.onscroll = "";
    }
  }

}

var favorite_Tweet = function (tweet_id, action) {
  if (en4.core.request.isRequestActive())
    return;
  $('favorite_tweet_' + tweet_id).innerHTML = en4.core.language.translate('Updating...');
  en4.core.request.send(new Request.JSON({
    'url': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
    //'method':'get',
    'data': {
      //'format' : 'json',
      'is_ajax': '5',
      'tweetstatus_id': tweet_id,
      'favorite_action': action

    },
    onSuccess: function (responseJSON) {
      if ($type(responseJSON)) {
        if (action == 1) {
          $('favorite_tweet_' + tweet_id).innerHTML = '<a href="javascript:void(0);" onclick="favorite_Tweet(\'' + tweet_id + '\', 0)" title="Unfavorite" class="aaf_tweet_icon aaf_tweet_icon_unfav">' + en4.core.language.translate('Unfavorite') + '</a>';
        } else {
          $('favorite_tweet_' + tweet_id).innerHTML = '<a href="javascript:void(0);" onclick="favorite_Tweet(\'' + tweet_id + '\', 1)" title="Favorite" class="aaf_tweet_icon aaf_tweet_icon_fav">' + en4.core.language.translate('Favorite') + '</a>';
        }
      }
    }
  }));
}



var parent_tweet_id;
var confirm_deletecommenttweet = function (post_id) {
  parent_tweet_id = post_id;

  Smoothbox.open('<div class="aaf_show_popup"><h3>Delete Tweet?</h3><p>' + en4.core.language.translate('Are you sure that you want to delete this tweet? This action cannot be undone.') + '</p><button type="submit" onclick="javascript:parent.delete_MyTweet();return false;">' + en4.core.language.translate('Delete') + '</button> or <a href="javascript:void(0);" onclick="parent.Smoothbox.close();">' + en4.core.language.translate('cancel') + '</a></div>');


}


var delete_MyTweet = function () {

  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed';
  var request = new Request.JSON({
    'url': userfeed_url,
    //'method':'get',
    'data': {
      //'format' : 'json',
      'is_ajax': '6',
      'tweetstatus_id': parent_tweet_id

    },

    onSuccess: function (responseJSON) {



      if (responseJSON) {
        setTimeout("fade_Twitter('post_delete_tweet_' + parent_tweet_id, 2)", 500);
        parent.Smoothbox.close();
      } else {
        en4.core.showError('<div class="aaf_show_popup"><p>' + en4.core.language.translate("An error has occurred processing the request. The target may no longer exist.") + '</p><button onclick="Smoothbox.close()">' + en4.core.language.translate("Close") + '</button></div>');
        return;
      }

    }
  });
  request.send();
}

var AAF_ShowFeedDialogue_Tweet = function (feedurl) {
  aaf_feed_type_tmp = 2;
  if (tweet_loginURL_temp == '') {
    tweet_loginURL_temp = feedurl;
  }
  activityfeedtype = 'twitter';
  if (typeof current_window_url != 'undefined' && current_window_url != '') {
    tweet_loginURL_temp = en4.core.baseUrl + 'seaocore/auth/twitter?return_url=' + current_window_url;
  }
  if (history.pushState)
    history.pushState({}, document.title, current_window_url + "?activityfeedtype=" + activityfeedtype);
  var child_window = window.open(tweet_loginURL_temp, 'mywindow', 'width=800,height=700');

}

var checkTwitter = function () {
  if (tweet_loginURL != '') {
    if ($type($('compose-twitter-form-input'))) {
      $('compose-twitter-form-input').disabled = 'disabled';
      $('composer_twitter_toggle').addEvent('click', function (event) {
        if (tweet_loginURL != '') {
          $('compose-twitter-form-input').checked = false;
          //event.target.toggleClass('composer_twitter_toggle_active');
          var child_window = window.open(tweet_loginURL, 'mywindow', 'width=800,height=700');
          event.stop();
        }
      });
    }

  } else {
    if ($type($('compose-twitter-form-input'))) {
      $('compose-twitter-form-input').disabled = '';
    }
  }

}

var logout_aaftwitter = function () {
alert("12334346346");
  var request = new Request.JSON({
    'url': en4.core.baseUrl + 'seaocore/auth/logout/',
    'method': 'get',
    'data': {
      'format': 'json',
      'is_ajax': '1',
      'logout_service': 'twitter'


    },

    onSuccess: function (responseJSON) {
      action_logout_taken_tweet = 1;
      tweet_loginURL = tweet_loginURL_temp;
      if ($('compose-twitter-form-input')) {
        $('compose-twitter-form-input').set('checked', !$('compose-twitter-form-input').get('checked'));

      }
      $('aaf_main_tab_logout').style.display = 'none';
      $('aaf_main_tab_refresh').style.display = 'none';
      if ($('activity-post-container'))
        $('activity-post-container').style.display = 'none';
      $('aaf_main_contener_feed_2').innerHTML = '<div class="aaf_feed_tip"><span>' + en4.core.language.translate('You need to be logged into Twitter to see your Twitter tweets.') + ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_Tweet()" >' + en4.core.language.translate('Click here') + '</a>.</span></div>';

    }
  });
  request.send();
}


var AdvancedActivityUpdateHandler_Tweet = new Class({

  Implements: [Events, Options],
  options: {
    debug: false,
    baseUrl: '/',
    identity: false,
    delay: 5000,
    admin: false,
    idleTimeout: 600000,
    last_id: 0,
    subject_guid: null,
    showloading: true,

    feedContentURL: en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed'
  },

  state: true,

  activestate: 1,

  fresh: true,

  lastEventTime: false,

  title: document.title,

  initialize: function (options) {
    this.setOptions(options);
  },

  start: function () {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout: this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive': function () {
        this.activestate = 1;
        this.state = true;
      }.bind(this),
      'onStateIdle': function () {
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });
    this.loop();
  },

  stop: function () {
    this.state = false;
  },

  checkFeedUpdate_Twitter: function (action_id, subject_guid) {

    var a = this.options.last_id;
    if (action_logout_taken_tweet == 1)
      return;
    if (en4.core.request.isRequestActive())
      return;
    en4.core.request.send(new Request.HTML({
      url: en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
      data: {
        'format': 'html',
        'minid': firstfeedid_Tweet,
        'feedOnly': true,
        'nolayout': true,
        'subject': this.options.subject_guid,
        'checkUpdate': true
      },
      onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
        if (activity_type == 2) {
          feedUpdate_tweet.innerHTML = responseHTML;
          feedUpdate_tweet.style.display = 'block';
        }
      }
    }));
  },

// THIS IS CALLED WHENEVER NEW FEED IS REQUESTED
  getFeedUpdate: function (last_id) {
    //if( en4.core.request.isRequestActive() ) return;
    if ($('update_advfeed_tweetblink'))
      $('update_advfeed_tweetblink').style.display = 'none';
    //if (this.options.showloading) {  
    feedUpdate_tweet.style.display = 'block';
    feedUpdate_tweet.innerHTML = "<div class='aaf_feed_loading'><img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' /></div>";
    // }
    activityUpdateHandler_Tweet.options.showloading = true;
    var min_id = firstfeedid_Tweet;
    this.options.last_id = last_id;
    //document.title = this.title;
    var feednomore_tweet = 0;
    if (feed_no_more_tweet.style.display == 'none') {
      feednomore_tweet = 1;
    }

    en4.core.request.send(new Request.HTML({
      url: en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed',
      data: {
        'format': 'html',
        'minid': min_id,
        'feedOnly': true,
        'nolayout': true,
        'getUpdate': true


      },
      onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {

        if (activity_type == 2) {
          if ($('update_advfeed_tweetblink'))
            $('update_advfeed_tweetblink').style.display = 'none';

          var newUl = document.createElement('ul', {
            'class': 'feed'
          });
          Elements.from(responseHTML).reverse().inject(newUl, 'top');
          newUl.inject(activityFeed_tweet, 'top');
          var feedSlide = new Fx.Slide(newUl, {
            resetHeight: true
          }).hide();
          feedSlide.slideIn();
          (function () {
            feedSlide.wrapper.destroy();
            Elements.from(responseHTML).inject(activityFeed_tweet, 'top');
          }).delay(450);
          feedUpdate_tweet.innerHTML = '';
          feedUpdate_tweet.style.display = 'none';
          if (feednomore_tweet == 1) {
            feed_no_more_tweet.style.display = 'none';
          }

        }
      }
    }));
  },

  loop: function () {
    if (this.options.delay == 0)
      return;
    if (!this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.checkFeedUpdate_Twitter().addEvent('complete', function () {
        this.loop.delay(this.options.delay, this);
      }.bind(this));
    } catch (e) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }
  },
  // Utility

  _log: function (object) {
    if (!this.options.debug) {
      return;
    }

    // Firefox is dumb and causes problems sometimes with console
    try {
      //if( typeof(console) && $type(console) ) {
      //console.log(object);
      //}
    } catch (e) {
      // Silence
    }
  }
});

