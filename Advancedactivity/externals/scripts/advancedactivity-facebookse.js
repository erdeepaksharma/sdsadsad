/* $Id: advancedactivity-facebookse.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
Technologies Pvt. Ltd. $
 */

//JAVA SCRIPT FOR USER FACEBOOK FRIENDS SHOWING ADDING TO LIST.

var url_param_time_until = 0;
var url_param_time_since = 0;
var feed_viewmore_fb, feed_no_more_fb, feed_loading_fb, feed_view_more_fb_link, activityUpdateHandler_FB, view_morefeed, firstfeedid_fb, update_freq_fb, redirect_childwindow;
var fb_loginURL = '';
var fb_loginURL_temp = '';
var action_logout_taken_fb = 0;
  

var AdvancedActivityUpdateHandler_FB = new Class({

  Implements : [Events, Options],
  options : {
    debug : false,
    baseUrl : '/',
    identity : false,
    delay : 5000,
    admin : false,
    idleTimeout : 600000,
    last_id : 0,
    subject_guid : null,
    showloading:true,
      
    feedContentURL: en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed'
  },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,
  
  initialize : function(options) {
    this.setOptions(options);
  },

  start : function() { 
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {
      timeout : this.options.idleTimeout
    });
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this.activestate = 1;
        this.state= true;
      }.bind(this),
      'onStateIdle' : function() {
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });
    this.loop();
  },

  stop : function() {
    this.state = false;
  },

  checkFeedUpdate_FB : function(action_id, subject_guid){ 
    
    if (action_logout_taken_fb == 1) return;
    if( en4.core.request.isRequestActive() ) return; 
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed',
      method: 'get',
      data : {
        'format' : 'html',
        'minid' : firstfeedid_fb,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'checkUpdate' : true,
        'is_ajax': 1
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
        if (activity_type == 3) { 
          if (typeof feedUpdate_fb != 'undefined') {
            feedUpdate_fb.innerHTML = responseHTML;
            feedUpdate_fb.style.display = 'block';
          }
        }
      }  
    }));
  },

  getFeedUpdate : function(last_id, currentaction){ 
    
    //if( en4.core.request.isRequestActive() ) return;
    var feednomore_fb = 0;
    if (feed_no_more_fb.style.display == 'none') { 
      feednomore_fb = 1;
    }
    if($('update_advfeed_fbblink'))
      $('update_advfeed_fbblink').style.display ='none';
    //if (this.options.showloading) {
    feedUpdate_fb.style.display = 'block';
    feedUpdate_fb.innerHTML = "<div class='aaf_feed_loading'><img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' /></div>";
      
    //}
    //activityUpdateHandler_FB.options.showloading=true; 
    //this.options.last_id = last_id;
    //document.title = this.title;
    var id = new Date().getTime(); 
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed?id=' + id,
      method:'get',
      data : {
        'format' : 'html',
        'minid' : firstfeedid_fb,
        'feedOnly' : true,
        'nolayout' : true,
        'getUpdate' : true,
        'subject' : this.options.subject_guid,
        'is_ajax' : 1,
        'id': id,
        'currentaction':currentaction 
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
        if (activity_type == 3) { 
          if($('update_advfeed_fbblink'))
            $('update_advfeed_fbblink').style.display = 'none';
          var newUl = document.createElement('ul',{
            'class':'feed'        
          });   
          Elements.from(responseHTML).reverse().inject(newUl, 'top');
          newUl.inject(activityFeed_fb, 'top');
          var feedSlide = new Fx.Slide(newUl, {         
            resetHeight:true
          }).hide();
          feedSlide.slideIn();
          (function(){         
            feedSlide.wrapper.destroy();           
            Elements.from(responseHTML).inject(activityFeed_fb, 'top');
          }).delay(450);   
        
          feedUpdate_fb.innerHTML = '';
          feedUpdate_fb.style.display = 'none';
          if (feednomore_fb == 1) {
            feed_no_more_fb.style.display = 'none';
          }
          
        }
      }  
    }));
  },

  loop : function() { 
		if (this.options.delay == 0) return;
    if( !this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.checkFeedUpdate_FB().addEvent('complete', function() {
        this.loop.delay(this.options.delay, this);
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }
  },
  // Utility

  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }

    // Firefox is dumb and causes problems sometimes with console
    try {
    //if( typeof(console) && $type(console) ) {
    //console.log(object);
    //}
    } catch( e ) {
    // Silence
    }
  }
});






window.addEvent('domready', function ()  { 
  getCommonFBElements();
  
  
});
function getCommonFBElements() {
  
  if ($('feed_viewmore_fb')) {
    feed_viewmore_fb = $('feed_viewmore_fb');
    feed_loading_fb = $('feed_loading_fb');
    feed_no_more_fb = $('feed_no_more_fb');
    feed_view_more_fb_link = $('feed_viewmore_fb_link');
    feedUpdate_fb = $('feed-update-fb');
    activityFeed_fb = $('FB_activity-feed');
  }
//  else if ($('feed_viewmore')) {
//    feed_viewmore_fb = $('feed_viewmore'); 
//    feed_loading_fb = $('feed_loading');
//    feed_no_more_fb = $('feed_no_more');
//    feed_view_more_fb_link = $('feed_viewmore_link');
//    feedUpdate_fb = $('feed-update');
//    activityFeed_fb = $('activity-feed');
//  }
}

//function AA_FB_ShowWallStream() { 
//  activity_type = 3;
//  getCommonFBElements();
//  if($('activity-post-container'))
//    $('activity-post-container').style.display = 'block';
//  resetAAFTextarea (composeInstance.plugins); 
//  if ($('composer_facebook_toggle')) {
//    $('composer_facebook_toggle').style.display = 'none';
//  }
//  if ($('composer_twitter_toggle')) {
//    $('composer_twitter_toggle').style.display = 'none';
//  }
//  if ($$('.adv_post_add_user')) 
//    $$('.adv_post_add_user').setStyle('display', 'none');
//  if ($('emoticons-button'))
//    $('emoticons-button').setStyle('display', 'none'); 
//  //  if ($('composer_socialengine_toggle')) {
//  //    $('composer_socialengine_toggle').style.display = 'block';
//  //  }
//  
//  if($('update_advfeed_fbblink'))
//    $('update_advfeed_fbblink').style.display = 'none';
//
//  if($('aaf_tabs_feed'))
//    $('aaf_tabs_feed').style.display = 'none';
//  if (typeof feedUpdate_fb != 'undefined')  
//    feedUpdate_fb.style.display = 'none';
//  if (typeof feed_no_more_fb != 'undefined')  
//    feed_no_more_fb.style.display = 'none';
//  if (typeof feed_viewmore_fb != 'undefined')  
//    feed_viewmore_fb.style.display = 'none';
//  
//  if (typeof feed_loading_fb != 'undefined')  
//    feed_loading_fb.style.display = '';
//  if ($$('.adv_post_container_tagging'))
//    $$('.adv_post_container_tagging').setStyle('display', 'none');
//  if ($('advancedactivity_friend_list'))
//    $('advancedactivity_friend_list').setStyle('display', 'none'); 
//  //SHOWING THE FACEBOOK PUBLISH CHECKBOX AND ICON ACTIVE WHEN THIS TAB IS CLICKED....
//  if ($('compose-facebook-form-input')) {
//    $('compose-facebook-form-input').set('checked', 1);
//    $('compose-facebook-form-input').parentNode.addClass('composer_facebook_toggle_active');
//  }   
// 
//  
//  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed'; 
//  var request = new Request.HTML({
//    'url' : userfeed_url,
//    //'method':'get',
//    'data' : {
//      'format' : 'html',
//      'is_ajax' : '1'
//				
//    },
//    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
//      if (activity_type == 3) {
//        activityFeed_fb.innerHTML = responseHTML;
//        feedUpdate_fb.innerHTML = '';
//        if (!$type(activityUpdateHandler_FB)) { 
//          Call_fbcheckUpdate();
//        }
//      }
//
//    }
//  });
//  request.send();
//			
//}
	
var Call_fbcheckUpdate = function () { 
	  
  en4.core.runonce.add(function() {
    try { 
      activityUpdateHandler_FB = new AdvancedActivityUpdateHandler_FB({
        'baseUrl' : en4.core.baseUrl,
        'basePath' : en4.core.basePath,
        'identity' : 4,
        'delay' : update_freq_fb,
        'last_id':url_param_time_since,
        'feedContentURL': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed'
            
      });
          
      setTimeout("activityUpdateHandler_FB.start('facebook')",update_freq_fb);
      //activityUpdateHandler.start();
      window._activityUpdateHandler_FB = activityUpdateHandler_FB;
      activityUpdateHandler_FB.options.last_id = url_param_time_since;
    } catch( e ) {
    //if( $type(console) ) console.log(e);
    }
  });
	  
}

   
//THIS FUNCTION EXECUTE WHEN USER WRITE STH IN USER STATUS BOX.

var setstatus_temp = 1;
function AAF_setStatus() {
  status1 = document.getElementById('aaf_set_status').value;
  if (status1 == '') {
    return ;
  }
  if (setstatus_temp == 1) {
    setstatus_temp = 2;
  }
  else {
    return;
  }
		
  
  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed'; 
  var request = new Request.JSON({
    'url' : userfeed_url,
    //'method':'get',
    'data' : {
      //'format' : 'json',
      'status' : status1,
      'is_ajax' : '2'
		
    },
    onSuccess : function(responseJSON) { 
      setstatus_temp = 1;
      if (!responseJSON){ 
        en4.core.showError('<div class="aaf_show_popup"><p>' + en4.core.language.translate("Your Facebook status could not be updated. Please try again.") + '</p><button onclick="Smoothbox.close()">' + en4.core.language.translate("Close") + '</button></div>');
					
      }
      else { 
        document.getElementById('aaf_set_status').value = en4.core.language.translate('What\'s on your mind?');

        activityFeed_fb.innerHTML = "<div class='aaf_feed_loading'><img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' /></div>";
  						
        AA_FB_ShowWallStream ();
        document.getElementById('aaf_set_status').set('class', 'sink_textarea');
        document.getElementById('aaf_share_button').style.display = 'none';
        cleanForm();
      }
    }
  });
  request.send();
  
}

/**
* URL Parameters
* http://www.netlobo.com/url_query_string_javascript.html
*/
//THIS FUNCTION RETURNS THE URL PARAMETER SINCE AND UNTIL.
function urlparameter( param, url )
{
  param = param.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+param+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( url);
  if( results == null )
    return "";
  else { 
    return results[1];
  }
}



function AAF_showText_More (type_text, id) {
  if (type_text == '1') { 
    $('fbmessage_text_full_' + id).style.display = 'block';
    $('fbmessage_text_short_' + id).style.display = 'none';
  }
  else if (type_text == '2') { 
    $('fbdescript_text_full_' + id).style.display = 'block';
    $('fbdescript_text_short_' + id).style.display = 'none';
  } 

}

//SHOW MORE TWITTER FEED ON SCROLLING.....
var last_oldfb_id = 0;
var enter_fbcount = 0;
var facebook_ActivityViewMore  = function () { 

  
  if (activity_type == 3 && enter_fbcount == 0) {
    feed_view_more_fb_link.removeEvents('click').addEvent('click', function(event){ 
      event.stop();
      enter_fbcount = 1;
      facebook_ActivityViewMore();
    });
  }

  enter_fbcount = 0;
  if( en4.core.request.isRequestActive() ) return; 
  if (activity_type != 3) { 
    return;
  }   
  feed_viewmore_fb.style.display = 'none';
  lastOldFB = 0;
  feed_loading_fb.style.display = 'block';
  


		
  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed'; 
  en4.core.request.send(new Request.HTML({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'html',
      'next_prev' : 'next',
      'duration' : url_param_time_until,
      'is_ajax' : '1'
		
    },
    evalScripts : true,
    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
      if (activity_type == 3) {
        countScrollAAFFB++;
        Elements.from(responseHTML).inject(activityFeed_fb);
      }
    //              }  en4.core.runonce.trigger();
    //                Smoothbox.bind($('activity-feed'));          
    }
  }));
    
}


//POST LIKE TO A POST ON FACEBOOK................//
var post_like_temp = 1;
var post_like_id_temp = '';
var Post_Like = function (post_id, action, Like_Count, post_url) { 
  if (post_like_id_temp == '') {
    post_like_id_temp = post_id;
  }
  if (post_like_temp == 1 || post_id !=  post_like_id_temp) {
    post_like_temp = 2;
    post_like_id_temp = post_id;
  }

  else { 
   
    return;
  }

  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed';
 
  var request = new Request.JSON({
    'url' : userfeed_url,
    'method':'get',
    'data' : { 
      'format':'json',
      'is_ajax' : '3',
      'post_id' : post_id,
      'FB_action': action,
      'like_count': Like_Count,
      'post_url':post_url
		
    },
    

    onSuccess : function(responseJSON) {  
      
      post_like_temp = 1;

      if ($('fbcomments_' + post_id))
        $('fbcomments_' + post_id).style.display = 'block';
      if ($('CommentonPost_' + post_id))
        $('CommentonPost_' + post_id).style.display = 'block';
      if($('FBCommentonPost_submit-' + post_id)) { 
        $('FBCommentonPost_submit-' + post_id).blur();
        if ($('FBCommentonPost_submit-' + post_id).value == en4.core.language.translate('Write a comment...'))
          $('FBCommentonPost_submit-' + post_id).setStyle('height', '19px');
                  
      }    
      if (responseJSON && responseJSON.success) { 
                
        //if (responseJSON.body) {  
                
        if (action == 'post') { 
          var current_likecount = parseInt(Like_Count) + parseInt(1);
								      
          if (current_likecount == 1)
											
            Elements.from(responseJSON.body).inject($('postcomment_fb-' + post_id), 'top') ;
                 		
          else { 
            $('FB_LikesCount_' + post_id).innerHTML = responseJSON.body;
          }
								 		
                    
          $('FB_Likes_' + post_id).innerHTML = '<a href="javascript:void(0);" onclick="Post_Like(\'' + post_id + '\', \'delete\', \'' + current_likecount + '\', \'' + post_url + '\')" title="' + en4.core.language.translate('Stop liking this item') + '">' + en4.core.language.translate('Unlike') + '</a>';
                    
        }
        else if (action == 'delete') 
        {  
          var current_likecount = parseInt(Like_Count) - parseInt(1);
          if (current_likecount == 0 || responseJSON.body == '') { 
            var currentdiv = $('FB_LikesCount_' + post_id).getParent();
            currentdiv.destroy();
          }
          else {
            $('FB_LikesCount_' + post_id).innerHTML = responseJSON.body;
          }
								 		
                    
          $('FB_Likes_' + post_id).innerHTML = '<a href="javascript:void(0);" onclick="Post_Like(\'' + post_id + '\', \'post\', \'' + current_likecount + '\', \'' + post_url + '\')" title="' + en4.core.language.translate('Like this item') + '">' + en4.core.language.translate('Like') + '</a>';
                   
        }
        if ($('count_fblike_' + post_id))
          $('count_fblike_' + post_id).innerHTML = responseJSON.like_count;
      // }
      }
      else {
        en4.core.showError('<div class="aaf_show_popup"><p>' + en4.core.language.translate("An error has occurred processing the request. The target may no longer exist.")  + '</p><button onclick="Smoothbox.close()">' + en4.core.language.translate("Close") + '</button></div>');
        return;
      }
    }
  });
  en4.core.request.send(request);
}


//SHOW COMMENTBOX..............................................

var show_commentbox = function (thisobj, post_id) { 
  thisobj.innerHTML = '';
  thisobj.focus();
  $('FB_activity-comment-submit-' + post_id).style.display = 'block';
  
}

var parent_post_id, parent_action, parent_post_url;
var confirm_deletecommentfb = function (post_id, action, post_url) {
  parent_post_id = post_id;
  parent_action = action;
  parent_post_url = post_url;
  Smoothbox.close();
  Smoothbox.instance = new Smoothbox.Modal.String({
    bodyText : '<div class="aaf_show_popup"><h3>' + en4.core.language.translate("Delete Comment?") + '</h3><p>' + en4.core.language.translate("Are you sure that you want to delete this comment? This action cannot be undone.") + '</p><button type="submit" onclick="javascript:parent.post_comment_onfb();return false;">' + en4.core.language.translate("Delete") + '</button>' + en4.core.language.translate(" or ") + '<a href="javascript:void(0);" onclick="parent.Smoothbox.close();">' + en4.core.language.translate("cancel") + '</a></div>'
  });
  
}

var post_comment_onfbtemp = 1;
var post_comment_onfb = function (post_id , action, post_url) { 
  if (post_comment_onfbtemp == 1) {
    post_comment_onfbtemp = 2;
  }
  else {
    return;
  }
  var closesmoothbox = 0; 
  if (!post_id) { 
    var closesmoothbox = 1; 
    post_id = parent_post_id;
    action=parent_action;
    post_url=parent_post_url;
  }
  
  var comment_content = '';
  if ($('FBCommentonPost_submit-' + post_id)) {
    comment_content = $('FBCommentonPost_submit-' + post_id).value;
    
  }
  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed'; 
  var request = new Request.JSON({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'json',
      'is_ajax' : '4',
      'post_id' : post_id,
      'FB_action': action,
      'content': comment_content,
      'post_url':post_url
		
    },
              
    onSuccess : function(responseJSON) { 
      post_comment_onfbtemp = 1;
      if (closesmoothbox ==1) {
        parent.Smoothbox.close();
      }
      if (action == 'post' && responseJSON && responseJSON.body) {
        Elements.from(responseJSON.body).inject($('postcomment_fb-' + post_id));
        if ($('FBCommentonPost_submit-' + post_id)) { 
          $('FBCommentonPost_submit-' + post_id).focus(); 
          $('FBCommentonPost_submit-' + post_id).value = '';
          $('FBCommentonPost_submit-' + post_id).setStyle('height', '19px');
        }
      }
      else if (responseJSON && responseJSON.success == 1) {
        var parentnode = $(post_id).parentNode;
        parentnode.removeChild($(post_id));
      }
      else { 
                   
        en4.core.showError('<div class="aaf_show_popup"><p>' + en4.core.language.translate("An error has occurred processing the request. The target may no longer exist.") + '</p><button onclick="Smoothbox.close()">' + en4.core.language.translate("Close") + '</button></div>');
        return;
      }
                 
    }
  });
  request.send();
}

var FB_doOnScrollLoadActivity = function () { 
  if( typeof( feed_viewmore_fb.offsetParent ) != 'undefined' ) {
    var elementPostionY = feed_viewmore_fb.offsetTop;
  }else{
    var elementPostionY = feed_viewmore_fb.y;
  }
  if((maxAutoScrollAAF == 0 || countScrollAAFFB < maxAutoScrollAAF) && autoScrollFeedAAFEnable && elementPostionY <= window.getScrollTop()+(window.getSize().y -40) ){
    facebook_ActivityViewMore(); 
    
  }
  else {
    if ($('feed_viewmore_fb')) {
      feed_viewmore_fb.style.display = 'block';
      feed_loading_fb.style.display = 'none'; 
      feed_view_more_fb_link.removeEvents('click').addEvent('click', function(event){ 
        event.stop();
        facebook_ActivityViewMore();
      });
    }
    if (!autoScrollFeedAAFEnable) {
      window.onscroll = "";
    }
  }
 
}

var Post_Comment_focus = function (focus_id) { 
  $('fbcomments_' + focus_id).style.display = 'block';
  $('CommentonPost_' + focus_id).style.display = 'block';
  (function() { 
    showHideCommentbox ($('FBCommentonPost_submit-' + focus_id), focus_id, 1)
  }).delay(60);  
}

var toggle_likecommentbox = function (action_id) { 
  if ($('fbcomments_' + action_id).style.display == 'block')
    $('fbcomments_' + action_id).style.display = 'none';
  else 
    $('fbcomments_' + action_id).style.display = 'block';
  $('CommentonPost_' + action_id).style.display = 'block';
}

var showHideCommentbox = function (thisobj, post_id, event) {
  
  if (event == 1) {
    var defalutcontent = thisobj.value.trim();
    if (defalutcontent == 'Write a comment...') { 
      thisobj.value = '';
      $('FB_activity-comment-submit-' + post_id).style.display = 'block';
      $('fbcomments_author_photo-' + post_id).setStyle('display', 'block'); 
      $('fbuser_picture-' + post_id).style.display = 'block';
      $('FBCommentonPost_submit-' + post_id).removeClass('aaf_color_light');
      thisobj.focus();
    }
  }
  else if (event == 2 && thisobj.value == '') {
    
    (function() {
      thisobj.value = 'Write a comment...';
      $('FBCommentonPost_submit-' + post_id).addClass('aaf_color_light');
      $('FB_activity-comment-submit-' + post_id).style.display = 'none';
      $('fbcomments_author_photo-' + post_id).setStyle('display', 'none');
    }).delay(50);
  }
  
}

var AAF_ShowFeedDialogue_FB = function (feedurl) {
  aaf_feed_type_tmp = 3;
  //  resetAAFTextarea (composeInstance.plugins);
  if (fb_loginURL_temp == '') {
    fb_loginURL_temp = feedurl;
  }
  activityfeedtype = 'facebook';
  if (typeof current_window_url != 'undefined' && current_window_url != ''){
    fb_loginURL_temp = en4.core.baseUrl + 'seaocore/auth/facebook?redirect_urimain=' + current_window_url + '?redirect_fb=1';
  }
  if (history.pushState)
    history.pushState( {}, document.title, current_window_url+"?activityfeedtype="+ activityfeedtype );
  var child_window = window.open (fb_loginURL_temp ,'mywindow','width=500,height=500');
  
}

var checkFB = function () { 
  if (fb_loginURL != '') { 
    if ($type($('compose-facebook-form-input'))) {
      $('compose-facebook-form-input').disabled = 'disabled';
      $('composer_facebook_toggle').addEvent('click', function(event) { 
        if (fb_loginURL != '') { 
          $('compose-facebook-form-input').checked = false; 
         
          var child_window = window.open (fb_loginURL ,'mywindow','width=500,height=500');
          event.stop();
        }
      } ); 
    }   
      
  }
  else {
    if ($type($('compose-facebook-form-input'))) {
      $('compose-facebook-form-input').disabled = '';
    }
  }
  
}


var logout_aaffacebook = function () {
 
  var request = new Request.JSON({
    'url' : en4.core.baseUrl + 'seaocore/auth/logout/',
    'method':'get',
    'data' : {
      'format' : 'json',
      'is_ajax' : '1',
      'logout_service' : 'facebook'
     
		
    },
              
    onSuccess : function(responseJSON) { 
      action_logout_taken_fb = 1;
      fb_loginURL = fb_loginURL_temp;
      if ($('compose-facebook-form-input')) {
        $('compose-facebook-form-input').set('checked', !$('compose-facebook-form-input').get('checked'));
        if($('composer_facebook_toggle').hasClass('composer_facebook_toggle_active') )
          $('composer_facebook_toggle').removeClass('composer_facebook_toggle_active');
      //$('composer_facebook_toggle').toggleClass('composer_facebook_toggle_active');
      }
      $('aaf_main_tab_logout').style.display = 'none';
      $('aaf_main_tab_refresh').style.display = 'none';
      if ($('activity-post-container'))
      $('activity-post-container').style.display = 'none';
      $('aaf_main_contener_feed_3').innerHTML = '<div class="aaf_feed_tip"><span>' + en4.core.language.translate('You need to be logged into Facebook to see your Facebook News Feed.') + ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_FB()" >' +  en4.core.language.translate('Click here') + '</a>.</span></div>'
     
    }
  });
  request.send();
}
