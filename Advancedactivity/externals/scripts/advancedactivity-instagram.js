/* $Id: advancedactivity-instagramse.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
Technologies Pvt. Ltd. $
 */

//JAVA SCRIPT FOR USER FACEBOOK FRIENDS SHOWING ADDING TO LIST.

var url_param_time_until = 0;
var url_param_time_since = 0;
var feed_viewmore_instagram, feed_no_more_instagram, feed_loading_instagram, feed_view_more_instagram_link, activityUpdateHandler_instagram, view_morefeed, firstfeedid_instagram, update_freq_instagram, redirect_childwindow, subject_instagram, message_instagram, last_instagram_timestemp, view_moreconnection_instagram;
var instagram_loginURL = '';
var instagram_loginURL_temp = '';
var action_logout_taken_instagram = 0;
var current_instagram_timestemp = 0;
var temp_instagram_login_window;
var isHomeFeedsWidget;

  

var AdvancedActivityUpdateHandler_instagram = new Class({

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
      
    feedContentURL: en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed'
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
//    this.loop();
  },

  stop : function() {
    this.state = false;
  },

  checkFeedUpdate_instagram : function(){  

    if (action_logout_taken_instagram == 1) return;
    if( en4.core.request.isRequestActive() ) return; 
																								
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed',
      method: 'get',
      data : {
        'format' : 'html',
        'minid' : current_instagram_timestemp,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'checkUpdate' : true,
        'is_ajax': 1
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
        if (activity_type == 5) { 
          if (typeof feedUpdate_instagram != 'undefined') {
            feedUpdate_instagram.innerHTML = responseHTML;
            feedUpdate_instagram.style.display = 'block';
          }
        }
      }  
    }));
  },

  getFeedUpdate : function(last_id){ 

    //if( en4.core.request.isRequestActive() ) return;
    var feednomore_instagram = 0;
    if (feed_no_more_instagram.style.display == 'none') { 
      feednomore_instagram = 1;
    }
    if($('update_advfeed_instagramblink'))
      $('update_advfeed_instagramblink').style.display ='none';
    //if (this.options.showloading) {
    feedUpdate_instagram.style.display = 'block';
    feedUpdate_instagram.innerHTML = "<div class='aaf_feed_loading'><img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' /></div>";		
    var id = new Date().getTime(); 
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed?id=' + id,
      method:'get',
      data : {
        'format' : 'html',
        'minid' : current_instagram_timestemp,
        'feedOnly' : true,
        'nolayout' : true,
        'getUpdate' : true,
        'subject' : this.options.subject_guid,
        'is_ajax' : 1,
        'id': id
        //'currentaction':currentaction 
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
        if (activity_type == 5) {  
					var htmlBody;
          if($('update_advfeed_instagramblink'))
            $('update_advfeed_instagramblink').style.display = 'none';
          var newUl = document.createElement('ul',{
            'class':'feed'        
          });   
          Elements.from(responseHTML).reverse().inject(newUl, 'top');
          newUl.inject(activityFeed_instagram, 'top');
          var feedSlide = new Fx.Slide(newUl, {         
            resetHeight:true
          }).hide();
          feedSlide.slideIn();
          (function(){         
            feedSlide.wrapper.destroy();
						htmlBody = responseHTML; 
						if( htmlBody ) htmlBody.stripScripts(true);     
            Elements.from(htmlBody).reverse().inject(activityFeed_instagram, 'top');
          }).delay(450);   
        
          feedUpdate_instagram.innerHTML = '';
          feedUpdate_instagram.style.display = 'none';
          if (feednomore_instagram == 1) {
            feed_no_more_instagram.style.display = 'none';
          }
          
        }
      }  
    }), {'force':true});
  },

  loop : function() { 
		if (this.options.delay == 0) return;
    if( !this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.checkFeedUpdate_instagram().addEvent('complete', function() {
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

window.addEvent('load', function ()  { 
  getCommonInstagramElements(); 
  
});
function getCommonInstagramElements() { 
  
  if ($('feed_viewmore_instagram')) {
    feed_viewmore_instagram = $('feed_viewmore_instagram');
    feed_loading_instagram = $('feed_loading_instagram');
    feed_no_more_instagram = $('feed_no_more_instagram');
    feed_view_more_instagram_link = $('feed_viewmore_instagram_link');
    feedUpdate_instagram = $('feed-update-instagram');    
  }
  activityFeed_instagram = $('instagram_activity-feed');

}


	
var Call_instagramcheckUpdate = function () { 
	  
  en4.core.runonce.add(function() {
    try { 
      activityUpdateHandler_instagram = new AdvancedActivityUpdateHandler_instagram({
        'baseUrl' : en4.core.baseUrl,
        'basePath' : en4.core.basePath,
        'identity' : 4,
        'delay' : update_freq_instagram,        
        'feedContentURL': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed'
            
      });
          
      setTimeout("activityUpdateHandler_instagram.start('instagram')",update_freq_instagram);
      
      window._activityUpdateHandler_instagram = activityUpdateHandler_instagram;
      
    } catch( e ) {
    //if( $type(console) ) console.log(e);
    }
  });
	  
}

function AAF_showText_More_instagram (type_text, thisobj) {
  if (type_text == '1') { 
    thisobj.getNext('.instagrammessage_text_full').style.display = 'block';
   thisobj.style.display = 'none';
  }
  else if (type_text == '2') { 
   thisobj.getNext('.instagramdescript_text_full').style.display = 'block';
    thisobj.style.display = 'none';
  } 

}

//SHOW MORE TWITTER FEED ON SCROLLING.....
var last_oldinstagram_id = 0;
var enter_instagramcount = 0;
var instagram_ActivityViewMore  = function () { 

  if (last_instagram_timestemp == '') {
		feed_viewmore_instagram.style.display = 'none';
  //lastOldFB = 0;
    feed_loading_instagram.style.display = 'none';
		feed_no_more_instagram.style.display = '';
		return;
	}
  if (activity_type == 6 && enter_instagramcount == 0) {
    feed_view_more_instagram_link.removeEvents('click').addEvent('click', function(event){ 
      event.stop();
      enter_instagramcount = 1;
      instagram_ActivityViewMore();
    });
  }

  enter_instagramcount = 0;
  if( en4.core.request.isRequestActive() ) return; 
  if (activity_type != 6) { 
    return;
  }   
  feed_viewmore_instagram.style.display = 'none';
  //lastOldFB = 0;
  feed_loading_instagram.style.display = 'block';
  
	


	
  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed'; 
  en4.core.request.send(new Request.HTML({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'html',
      'next_prev' : 'next',
      'duration' : last_instagram_timestemp,
      'is_ajax' : '1'
		
    },
    evalScripts : true,
    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
      if (activity_type == 6) {
        countScrollAAFInstagram++;
				
        Elements.from(responseHTML).inject(activityFeed_instagram);
				en4.core.runonce.trigger();
        Smoothbox.bind($('activity-feed')); 
      }
    }           
    
  }));
    
}

var viewNextFeeds = function(id){
  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed'; 
  en4.core.request.send(new Request.HTML({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'html',
      'nextMaxId': id,
    },
    evalScripts : true,
    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
      if (activity_type == 6) {
        countScrollAAFInstagram++;
				
        Elements.from(responseHTML).inject(new_instagram_data);
				en4.core.runonce.trigger();
        Smoothbox.bind($('activity-feed')); 
      }
    }           
    
  }));
}


//POST LIKE TO A POST ON FACEBOOK................//
var instagram_like_temp = 1;
var instagram_like_id_temp = '';
//var instagram_likes = array();
var instagram_like = function (thisobj, action_id, action, instagramlike_count) { 
  if (instagram_like_id_temp == '') {
    instagram_like_id_temp = action_id;
  }
  if (instagram_like_temp == 1 || action_id !=  instagram_like_id_temp) {
    instagram_like_temp = 2;
    instagram_like_id_temp = action_id;
		//instagram_likes[action_id] = thisobject;
  }

  else { 
   
    return;
  }

  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed';
 
  var request = new Request.JSON({
    'url' : userfeed_url,
    'method':'get',
    'data' : { 
      
      'is_ajax' : '3',
      'post_id' : action_id,
      'instagram_action': action,
      'like_count': instagramlike_count
      
		
    },
    

    onSuccess : function(responseJSON) {  
      
      instagram_like_temp = 1;
			var selfparent = thisobj.getParent('.aaf_feed_item_stats');
      
      if (selfparent.getNext ('.aaf_feed_comment_commentbox'))
        selfparent.getNext ('.aaf_feed_comment_commentbox').style.display = 'block';
      if (selfparent.getNext ('.CommentonPost_instagram'))
        selfparent.getNext ('.CommentonPost_instagram').style.display = 'block';
      if(selfparent.getNext ('.instagram_activity-comment-submit')) { 
        selfparent.getNext ('.instagram_activity-comment-submit').blur();
        if (selfparent.getNext ('.instagram_activity-comment-submit').value == en4.core.language.translate('Write a comment...'))
          selfparent.getNext ('.instagram_activity-comment-submit').setStyle('height', '19px');
                  
      }    
      if (responseJSON && responseJSON.success) { 
                
        //if (responseJSON.body) {  
                
        if (action == 'like') { 
          var current_likecount = parseInt(instagramlike_count) + parseInt(1);
						      
          if (current_likecount == 1)
											
            Elements.from(responseJSON.body).inject(selfparent.getNext('.aaf_feed_comment_commentbox').getFirst('.postcomment_instagram'), 'top') ;
                 		
          else { 

            selfparent.getNext('.aaf_feed_comment_commentbox').getElement('.instagram_LikesCount').innerHTML = responseJSON.body;
          }
								 		
                    
         thisobj.innerHTML = '<a href="javascript:void(0);" onclick="instagram_like(this, \'' + action_id + '\', \'unlike\', \'' + current_likecount + '\')" title="' + en4.core.language.translate('Click to unlike this update') + '">' + en4.core.language.translate('Like') + '<span class="count_instagramlike"> ('+ current_likecount +')</span></a>';
                    
        }
        else if (action == 'unlike') 
        {  
          var current_likecount = parseInt(instagramlike_count) - parseInt(1);
          if (current_likecount == 0 || responseJSON.body == '') { 
            var currentdiv = selfparent.getNext('.aaf_feed_comment_commentbox').getElement('.instagram_LikesCount').getParent('.aaf_feed_comment_likes_count');
            currentdiv.destroy();
						
						 thisobj.innerHTML = '<a href="javascript:void(0);" onclick="instagram_like(this, \'' + action_id + '\', \'like\', \'' + current_likecount + '\')" title="' + en4.core.language.translate('Click to like this update') + '">' + en4.core.language.translate('Like') + '</a>';
          }
          else { 
            selfparent.getNext('.aaf_feed_comment_commentbox').getElement('.instagram_LikesCount').innerHTML = responseJSON.body;
						
						 thisobj.innerHTML = '<a href="javascript:void(0);" onclick="instagram_like(this, \'' + action_id + '\', \'like\', \'' + current_likecount + '\')" title="' + en4.core.language.translate('Click to like this update') + '">' + en4.core.language.translate('Like') + '<span class="count_instagramlike"> ('+ current_likecount +')</span></a>';
          }
								 		
                    
         
                   
        }

      }
      else {
        en4.core.showError('<div class="aaf_show_popup"><p>' + en4.core.language.translate("An error has occurred processing the request. The target may no longer exist.")  + '</p><button onclick="Smoothbox.close()">' + en4.core.language.translate("Close") + '</button></div>');
        return;
      }
    }
  });
  en4.core.request.send(request);
}

var parent_post_id, parent_action, parent_post_url;
var post_comment_oninstagramtemp = 1;
var post_comment_oninstagram = function (thisobj, post_id, action) { 
	
	
  if (post_comment_oninstagramtemp == 1) {
    post_comment_oninstagramtemp = 2;
  }
  else {
    return;
  }
   var closesmoothbox = 0; 
  if (!post_id) { 
    var closesmoothbox = 1; 
    post_id = parent_post_id;
    action=parent_action;
    thisobj=$('comments_info_instagram-' + post_id);
  }
  
  var comment_content = '';
  if (thisobj.getPrevious('.instagramCommentonPost_submit') && thisobj.getPrevious('.instagramCommentonPost_submit').value != '') {
    comment_content = thisobj.getPrevious('.instagramCommentonPost_submit').value;
    
  }
  else { 
		 return;
	}

  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed'; 
  var request = new Request.JSON({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'json',
      'is_ajax' : '4',
      'post_id' : post_id,
      'instagram_action': action,
      'content': comment_content
     
		
    },
              
    onSuccess : function(responseJSON) { 
      post_comment_oninstagramtemp = 1;
      if (closesmoothbox ==1) {
        parent.Smoothbox.close();
      }
      if (action == 'post' && responseJSON && responseJSON.body) { 
        Elements.from(responseJSON.body).inject(thisobj.getParent('.aaf_fb_comment').getPrevious('.postcomment_instagram'));
        if (thisobj.getPrevious('.instagramCommentonPost_submit')) { 
          thisobj.getPrevious('.instagramCommentonPost_submit').focus(); 
          thisobj.getPrevious('.instagramCommentonPost_submit').value = '';
          thisobj.getPrevious('.instagramCommentonPost_submit').setStyle('height', '19px');
        }
      }
      else if (responseJSON && responseJSON.success == 1) {
        var parentnode = $('latestcomment-' + post_id).destroy();
        
      }
      else { 
                   
        en4.core.showError('<div class="aaf_show_popup"><p>' + en4.core.language.translate("An error has occurred processing the request. The target may no longer exist.") + '</p><button onclick="Smoothbox.close()">' + en4.core.language.translate("Close") + '</button></div>');
        return;
      }
                 
    }
  });
  request.send();
}

var instagram_doOnScrollLoadActivity = function () { 
  if( typeof( feed_viewmore_instagram.offsetParent ) != 'undefined' ) {
    var elementPostionY = feed_viewmore_instagram.offsetTop;
  }else{
    var elementPostionY = feed_viewmore_instagram.y;
  }
  if((maxAutoScrollAAF == 0 || countScrollAAFinstagram < maxAutoScrollAAF) && autoScrollFeedAAFEnable && elementPostionY <= window.getScrollTop()+(window.getSize().y -40) ){ 
    instagram_ActivityViewMore(); 
    
  }
  else {
    if ($('feed_viewmore_instagram')) {
      feed_viewmore_instagram.style.display = 'block';
      feed_loading_instagram.style.display = 'none'; 
      feed_view_more_instagram_link.removeEvents('click').addEvent('click', function(event){ 
        event.stop();
        instagram_ActivityViewMore();
      });
    }
    if (!autoScrollFeedAAFEnable) {
      window.onscroll = "";
    }
  }
 
}


var instagram_toggle_likecommentbox = function (thisobj) { 
  if (thisobj.style.display == 'block')
    thisobj.style.display = 'none';
  else 
    thisobj.style.display = 'block';
  thisobj.getElement('.CommentonPost_instagram').style.display = 'block';
}

var showHideCommentbox_instagram = function (thisobj, event) {
  
  if (event == 1) {
    var defalutcontent = thisobj.value.trim();
    if (defalutcontent == 'Write a comment...') { 
      thisobj.value = '';
      
//       $('fbcomments_author_photo-' + post_id).setStyle('display', 'block'); 
//       $('fbuser_picture-' + post_id).style.display = 'block';
      thisobj.removeClass('aaf_color_light');
      thisobj.focus();
			thisobj.autogrow();
			if(typeof is_enter_submitothersocial != 'undefined' && is_enter_submitothersocial == 1) {
					thisobj.addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'keydown':'keypress',function (event) {
						if (event.shift && event.key == 'enter') {      	
						} else if(event.key == 'enter') {
							event.stop();    
							post_comment_oninstagram(thisobj.getParent('.comments_info').getElement('.instagram_activity-comment-submit'), thisobj.getParent('.comments_info').getElementById('post_commentonfeed').value, 'post');
						}
					});
					// add blur event				
			}
			else {
					thisobj.getNext('.instagram_activity-comment-submit').style.display = 'block';
			}
    }
  }
  else if (event == 2 && thisobj.value == '') {
    
    (function() {
      thisobj.value = 'Write a comment...';
      thisobj.addClass('aaf_color_light');
      thisobj.getNext('.instagram_activity-comment-submit').style.display = 'none';
      //$('fbcomments_author_photo-' + post_id).setStyle('display', 'none');
    }).delay(50);
  }
  
}

var AAF_ShowFeedDialogue_Instagram = function (feedurl) {
  aaf_feed_type_tmp = 6;
  //  resetAAFTextarea (composeInstance.plugins);
  if (instagram_loginURL_temp == '') {
    instagram_loginURL_temp = feedurl;
  }
  activityfeedtype = 'instagram';
  if (typeof current_window_url != 'undefined' && current_window_url != ''){
    instagram_loginURL_temp = en4.core.baseUrl + 'seaocore/auth/instagram-check?redirect_urimain=' + current_window_url + '?redirect_instagram=1';
  }
  if (history.pushState && isHomeFeedsWidget)
    history.pushState( {}, document.title, current_window_url+"?activityfeedtype="+ activityfeedtype );
  var child_window = window.open (instagram_loginURL_temp ,'mywindow','width=800,height=700');
  
  temp_instagram_login_window = child_window;
}

var checkInstagram = function () { 
  if (instagram_loginURL != '') { 
    if ($type($('compose-instagram-form-input'))) {
      $('compose-instagram-form-input').disabled = 'disabled';
      $('composer_instagram_toggle').addEvent('click', function(event) { 
        if (instagram_loginURL != '') { 
          $('compose-instagram-form-input').checked = false; 
         
          var child_window = window.open (instagram_loginURL ,'mywindow','width=800,height=700');
          event.stop();
        }
      } ); 
    }   
      
  }
  else {
    if ($type($('compose-instagram-form-input'))) {
      $('compose-instagram-form-input').disabled = '';
    }
  }
  
}


var logout_aafinstagram = function () { 
 
  var request = new Request.JSON({
    'url' : en4.core.baseUrl + 'seaocore/auth/logout/',
    'method':'get',
    'data' : {     
      'is_ajax' : '1',
      'logout_service' : 'instagram'
     
		
    },
              
    onComplete : function(responseJSON) { 
      action_logout_taken_instagram = 1;
      instagram_loginURL = instagram_loginURL_temp;
      if ($('compose-instagram-form-input')) {
        $('compose-instagram-form-input').set('checked', !$('compose-instagram-form-input').get('checked'));
        if($('composer_instagram_toggle').hasClass('composer_instagram_toggle_active') )
          $('composer_instagram_toggle').removeClass('composer_instagram_toggle_active');
      //$('composer_instagram_toggle').toggleClass('composer_instagram_toggle_active');
      }
      $('aaf_main_tab_logout').style.display = 'none';
      $('aaf_main_tab_refresh').style.display = 'none';
      if ($('activity-post-container'))
        $('activity-post-container').style.display = 'none';
      $('aaf_main_contener_feed_6').innerHTML = '<div class="aaf_feed_tip"><span>' + en4.core.language.translate('You need to be logged into Instagram to see your Instagram Feeds.') + ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_Instagram()" >' +  en4.core.language.translate('Click here') + '</a>.</span></div> <br />';
      
    }
  });
  request.send();
}



var instagram_memberid;
//sending the message of the users whose feeds are displaying in the activity feed area.
var sendinstagramMessage = function (thisobj, method, connecter_name, connected_name, connecter_id) { 
	
  if (method == 'get') {
		  instagram_memberid = connecter_id;
			$('to-elementinstagram').innerHTML = connecter_name;
			$('toValuesinstagram').value = connecter_id;
			if (connected_name != '')
				$('subject_instagram').innerHTML = "Your new connection: " + connected_name;
			  var messagehtml = $('instagrammessage_html').innerHTML;
			  $('subject_instagram').innerHTML = '<br />';
			  $('toValuesinstagram').value = '';
			Smoothbox.open('<div>' + messagehtml + '</div>');
	}
	else {      
	 if (thisobj.getParent('.global_form').getElement('.compose-content').innerHTML == '' || thisobj.getParent('.global_form').getElement('.compose-textarea').value == '') {
		 
		 thisobj.getParent('.global_form').getElement('.show_errormessage').innerHTML = '<ul class="form-errors" style="margin:0px;"><li>Please fill the subject and message fields</li></ul>';
		 
		 return false;
		 
	 }
	 thisobj.getParent('.global_form').getElement('.show_errormessage').setStyle('display', 'none');
		$('titleinstagram').value = thisobj.getParent('.global_form').getElement('.compose-content').innerHTML;
		
		$('instagram_message_textarea').value = thisobj.getParent('.global_form').getElement('.compose-textarea').value;
		currentSearchParams = $('post_instagram_message').toQueryString();
		thisobj.getPrevious().style.display = 'inline-block';
    thisobj.getPrevious().innerHTML = "<img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' />";
		thisobj.setStyle ('display', 'none');
		currentSearchParams = currentSearchParams  + '&is_ajax=2&memberid='+ instagram_memberid ;
//   
		userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivityinstagram-userfeed'; 
  var request = new Request.JSON({
    url: userfeed_url,
		method:'get',
    data : currentSearchParams,
		onFailure: function (xhr) { //XMLHTTPREQUEST
			 en4.core.showError("<div class='aaf_show_popup'><p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button></div>');
			 active_submitrequest = 1;
		},
    onSuccess: function(responseJSON) { 
		   if (responseJSON && responseJSON.response.success == 1) {
				  
				  thisobj.getParent('.form-elements').innerHTML = en4.core.language.translate('Your message was successfully sent.');
			 }
			 else {
				 thisobj.getParent('.form-elements').innerHTML = en4.core.language.translate('An error occured. Please try again after some time.');
			 }
			 setTimeout("Smoothbox.close();", 1000);
	  }
	});
	request.send(); 
	 
	}
	
	return false;
}

var instagram_comment = function (thisobj) {
		var self1 = thisobj.getParent('.aaf_feed_item_stats');
		var commentform = self1.getNext('.aaf_feed_comment_commentbox').getElement('.CommentonPost_instagram');
		self1.getNext('.aaf_feed_comment_commentbox').style.display = 'block';
		commentform.setStyle('display', 'block');
 
  (function() { 
    showHideCommentbox_instagram (commentform.getElement('.instagramCommentonPost_submit'),  1)
  }).delay(60); 
	
	
}

var showAllComments = function (thisobj, classname) { 
	
	thisobj.setStyle('display', 'none');
	
	$$('.feedcomment-' + classname).each (function (element) { 
		
		element.setStyle('display', 'block');
		
		
		
	});
	
	
	
}