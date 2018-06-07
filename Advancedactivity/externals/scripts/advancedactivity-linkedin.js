/* $Id: advancedactivity-linkedinse.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
Technologies Pvt. Ltd. $
 */

//JAVA SCRIPT FOR USER FACEBOOK FRIENDS SHOWING ADDING TO LIST.

var url_param_time_until = 0;
var url_param_time_since = 0;
var feed_viewmore_linkedin, feed_no_more_linkedin, feed_loading_linkedin, feed_view_more_linkedin_link, activityUpdateHandler_Linkedin, view_morefeed, firstfeedid_linkedin, update_freq_linkedin, redirect_childwindow, subject_linkedin, message_linkedin, last_linkedin_timestemp, view_moreconnection_linkedin;
var linkedin_loginURL = '';
var linkedin_loginURL_temp = '';
var action_logout_taken_linkedin = 0;
var current_linkedin_timestemp = 0;
  

var AdvancedActivityUpdateHandler_Linkedin = new Class({

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
      
    feedContentURL: en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed'
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

  checkFeedUpdate_Linkedin : function(){  
   
    if (action_logout_taken_linkedin == 1) return;
    if( en4.core.request.isRequestActive() ) return; 
																								
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed',
      method: 'get',
      data : {
        'format' : 'html',
        'minid' : current_linkedin_timestemp,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'checkUpdate' : true,
        'is_ajax': 1
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
        if (activity_type == 5) { 
          if (typeof feedUpdate_linkedin != 'undefined') {
            feedUpdate_linkedin.innerHTML = responseHTML;
            feedUpdate_linkedin.style.display = 'block';
          }
        }
      }  
    }));
  },

  getFeedUpdate : function(last_id){ 
    
    //if( en4.core.request.isRequestActive() ) return;
    var feednomore_linkedin = 0;
    if (feed_no_more_linkedin.style.display == 'none') { 
      feednomore_linkedin = 1;
    }
    if($('update_advfeed_linkedinblink'))
      $('update_advfeed_linkedinblink').style.display ='none';
    //if (this.options.showloading) {
    feedUpdate_linkedin.style.display = 'block';
    feedUpdate_linkedin.innerHTML = "<div class='aaf_feed_loading'><img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' /></div>";		
    var id = new Date().getTime(); 
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed?id=' + id,
      method:'get',
      data : {
        'format' : 'html',
        'minid' : current_linkedin_timestemp,
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
          if($('update_advfeed_linkedinblink'))
            $('update_advfeed_linkedinblink').style.display = 'none';
          var newUl = document.createElement('ul',{
            'class':'feed'        
          });   
          Elements.from(responseHTML).reverse().inject(newUl, 'top');
          newUl.inject(activityFeed_linkedin, 'top');
          var feedSlide = new Fx.Slide(newUl, {         
            resetHeight:true
          }).hide();
          feedSlide.slideIn();
          (function(){         
            feedSlide.wrapper.destroy();
						htmlBody = responseHTML; 
						if( htmlBody ) htmlBody.stripScripts(true);     
            Elements.from(htmlBody).reverse().inject(activityFeed_linkedin, 'top');
          }).delay(450);   
        
          feedUpdate_linkedin.innerHTML = '';
          feedUpdate_linkedin.style.display = 'none';
          if (feednomore_linkedin == 1) {
            feed_no_more_linkedin.style.display = 'none';
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
      this.checkFeedUpdate_Linkedin().addEvent('complete', function() {
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
  getCommonLinkedinElements(); 
  
});
function getCommonLinkedinElements() { 
  
  if ($('feed_viewmore_linkedin')) {
    feed_viewmore_linkedin = $('feed_viewmore_linkedin');
    feed_loading_linkedin = $('feed_loading_linkedin');
    feed_no_more_linkedin = $('feed_no_more_linkedin');
    feed_view_more_linkedin_link = $('feed_viewmore_linkedin_link');
    feedUpdate_linkedin = $('feed-update-linkedin');    
  }
  activityFeed_linkedin = $('Linkedin_activity-feed');

}


	
var Call_linkedincheckUpdate = function () { 
	  
  en4.core.runonce.add(function() {
    try { 
      activityUpdateHandler_Linkedin = new AdvancedActivityUpdateHandler_Linkedin({
        'baseUrl' : en4.core.baseUrl,
        'basePath' : en4.core.basePath,
        'identity' : 4,
        'delay' : update_freq_linkedin,        
        'feedContentURL': en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed'
            
      });
          
      setTimeout("activityUpdateHandler_Linkedin.start('linkedin')",update_freq_linkedin);
      
      window._activityUpdateHandler_Linkedin = activityUpdateHandler_Linkedin;
      
    } catch( e ) {
    //if( $type(console) ) console.log(e);
    }
  });
	  
}

function AAF_showText_More_linkedin (type_text, thisobj) {
  if (type_text == '1') { 
    thisobj.getNext('.linkedinmessage_text_full').style.display = 'block';
   thisobj.style.display = 'none';
  }
  else if (type_text == '2') { 
   thisobj.getNext('.linkedindescript_text_full').style.display = 'block';
    thisobj.style.display = 'none';
  } 

}

//SHOW MORE TWITTER FEED ON SCROLLING.....
var last_oldlinkedin_id = 0;
var enter_linkedincount = 0;
var linkedin_ActivityViewMore  = function () { 

  if (last_linkedin_timestemp == '') {
		feed_viewmore_linkedin.style.display = 'none';
  //lastOldFB = 0;
    feed_loading_linkedin.style.display = 'none';
		feed_no_more_linkedin.style.display = '';
		return;
	}
  if (activity_type == 5 && enter_linkedincount == 0) {
    feed_view_more_linkedin_link.removeEvents('click').addEvent('click', function(event){ 
      event.stop();
      enter_linkedincount = 1;
      linkedin_ActivityViewMore();
    });
  }

  enter_linkedincount = 0;
  if( en4.core.request.isRequestActive() ) return; 
  if (activity_type != 5) { 
    return;
  }   
  feed_viewmore_linkedin.style.display = 'none';
  //lastOldFB = 0;
  feed_loading_linkedin.style.display = 'block';
  
	


	
  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed'; 
  en4.core.request.send(new Request.HTML({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'html',
      'next_prev' : 'next',
      'duration' : last_linkedin_timestemp,
      'is_ajax' : '1'
		
    },
    evalScripts : true,
    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) { 
      if (activity_type == 5) {
        countScrollAAFLinkedin++;
				
        Elements.from(responseHTML).inject(activityFeed_linkedin);
				en4.core.runonce.trigger();
        Smoothbox.bind($('activity-feed')); 
      }
    }           
    
  }));
    
}


//POST LIKE TO A POST ON FACEBOOK................//
var linkedin_like_temp = 1;
var linkedin_like_id_temp = '';
//var linkedin_likes = array();
var linkedin_like = function (thisobj, action_id, action, linkedinlike_count) { 
  if (linkedin_like_id_temp == '') {
    linkedin_like_id_temp = action_id;
  }
  if (linkedin_like_temp == 1 || action_id !=  linkedin_like_id_temp) {
    linkedin_like_temp = 2;
    linkedin_like_id_temp = action_id;
		//linkedin_likes[action_id] = thisobject;
  }

  else { 
   
    return;
  }

  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed';
 
  var request = new Request.JSON({
    'url' : userfeed_url,
    'method':'get',
    'data' : { 
      
      'is_ajax' : '3',
      'post_id' : action_id,
      'Linkedin_action': action,
      'like_count': linkedinlike_count
      
		
    },
    

    onSuccess : function(responseJSON) {  
      
      linkedin_like_temp = 1;
			var selfparent = thisobj.getParent('.aaf_feed_item_stats');
      
      if (selfparent.getNext ('.aaf_feed_comment_commentbox'))
        selfparent.getNext ('.aaf_feed_comment_commentbox').style.display = 'block';
      if (selfparent.getNext ('.CommentonPost_linkedin'))
        selfparent.getNext ('.CommentonPost_linkedin').style.display = 'block';
      if(selfparent.getNext ('.Linkedin_activity-comment-submit')) { 
        selfparent.getNext ('.Linkedin_activity-comment-submit').blur();
        if (selfparent.getNext ('.Linkedin_activity-comment-submit').value == en4.core.language.translate('Write a comment...'))
          selfparent.getNext ('.Linkedin_activity-comment-submit').setStyle('height', '19px');
                  
      }    
      if (responseJSON && responseJSON.success) { 
                
        //if (responseJSON.body) {  
                
        if (action == 'like') { 
          var current_likecount = parseInt(linkedinlike_count) + parseInt(1);
						      
          if (current_likecount == 1)
											
            Elements.from(responseJSON.body).inject(selfparent.getNext('.aaf_feed_comment_commentbox').getFirst('.postcomment_linkedin'), 'top') ;
                 		
          else { 

            selfparent.getNext('.aaf_feed_comment_commentbox').getElement('.Linkedin_LikesCount').innerHTML = responseJSON.body;
          }
								 		
                    
         thisobj.innerHTML = '<a href="javascript:void(0);" onclick="linkedin_like(this, \'' + action_id + '\', \'unlike\', \'' + current_likecount + '\')" title="' + en4.core.language.translate('Click to unlike this update') + '">' + en4.core.language.translate('Like') + '<span class="count_linkedinlike"> ('+ current_likecount +')</span></a>';
                    
        }
        else if (action == 'unlike') 
        {  
          var current_likecount = parseInt(linkedinlike_count) - parseInt(1);
          if (current_likecount == 0 || responseJSON.body == '') { 
            var currentdiv = selfparent.getNext('.aaf_feed_comment_commentbox').getElement('.Linkedin_LikesCount').getParent('.aaf_feed_comment_likes_count');
            currentdiv.destroy();
						
						 thisobj.innerHTML = '<a href="javascript:void(0);" onclick="linkedin_like(this, \'' + action_id + '\', \'like\', \'' + current_likecount + '\')" title="' + en4.core.language.translate('Click to like this update') + '">' + en4.core.language.translate('Like') + '</a>';
          }
          else { 
            selfparent.getNext('.aaf_feed_comment_commentbox').getElement('.Linkedin_LikesCount').innerHTML = responseJSON.body;
						
						 thisobj.innerHTML = '<a href="javascript:void(0);" onclick="linkedin_like(this, \'' + action_id + '\', \'like\', \'' + current_likecount + '\')" title="' + en4.core.language.translate('Click to like this update') + '">' + en4.core.language.translate('Like') + '<span class="count_linkedinlike"> ('+ current_likecount +')</span></a>';
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
var post_comment_onlinkedintemp = 1;
var post_comment_onlinkedin = function (thisobj, post_id, action) { 
	
	
  if (post_comment_onlinkedintemp == 1) {
    post_comment_onlinkedintemp = 2;
  }
  else {
    return;
  }
   var closesmoothbox = 0; 
  if (!post_id) { 
    var closesmoothbox = 1; 
    post_id = parent_post_id;
    action=parent_action;
    thisobj=$('comments_info_linkedin-' + post_id);
  }
  
  var comment_content = '';
  if (thisobj.getPrevious('.LinkedinCommentonPost_submit') && thisobj.getPrevious('.LinkedinCommentonPost_submit').value != '') {
    comment_content = thisobj.getPrevious('.LinkedinCommentonPost_submit').value;
    
  }
  else { 
		 return;
	}

  userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed'; 
  var request = new Request.JSON({
    'url' : userfeed_url,
    'method':'get',
    'data' : {
      'format' : 'json',
      'is_ajax' : '4',
      'post_id' : post_id,
      'Linkedin_action': action,
      'content': comment_content
     
		
    },
              
    onSuccess : function(responseJSON) { 
      post_comment_onlinkedintemp = 1;
      if (closesmoothbox ==1) {
        parent.Smoothbox.close();
      }
      if (action == 'post' && responseJSON && responseJSON.body) { 
        Elements.from(responseJSON.body).inject(thisobj.getParent('.aaf_fb_comment').getPrevious('.postcomment_linkedin'));
        if (thisobj.getPrevious('.LinkedinCommentonPost_submit')) { 
          thisobj.getPrevious('.LinkedinCommentonPost_submit').focus(); 
          thisobj.getPrevious('.LinkedinCommentonPost_submit').value = '';
          thisobj.getPrevious('.LinkedinCommentonPost_submit').setStyle('height', '19px');
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

var Linkedin_doOnScrollLoadActivity = function () { 
  if( typeof( feed_viewmore_linkedin.offsetParent ) != 'undefined' ) {
    var elementPostionY = feed_viewmore_linkedin.offsetTop;
  }else{
    var elementPostionY = feed_viewmore_linkedin.y;
  }
  if((maxAutoScrollAAF == 0 || countScrollAAFLinkedin < maxAutoScrollAAF) && autoScrollFeedAAFEnable && elementPostionY <= window.getScrollTop()+(window.getSize().y -40) ){ 
    linkedin_ActivityViewMore(); 
    
  }
  else {
    if ($('feed_viewmore_linkedin')) {
      feed_viewmore_linkedin.style.display = 'block';
      feed_loading_linkedin.style.display = 'none'; 
      feed_view_more_linkedin_link.removeEvents('click').addEvent('click', function(event){ 
        event.stop();
        linkedin_ActivityViewMore();
      });
    }
    if (!autoScrollFeedAAFEnable) {
      window.onscroll = "";
    }
  }
 
}


var linkedin_toggle_likecommentbox = function (thisobj) { 
  if (thisobj.style.display == 'block')
    thisobj.style.display = 'none';
  else 
    thisobj.style.display = 'block';
  thisobj.getElement('.CommentonPost_linkedin').style.display = 'block';
}

var showHideCommentbox_linkedin = function (thisobj, event) {
  
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
							post_comment_onlinkedin(thisobj.getParent('.comments_info').getElement('.Linkedin_activity-comment-submit'), thisobj.getParent('.comments_info').getElementById('post_commentonfeed').value, 'post');
						}
					});
					// add blur event				
			}
			else {
					thisobj.getNext('.Linkedin_activity-comment-submit').style.display = 'block';
			}
    }
  }
  else if (event == 2 && thisobj.value == '') {
    
    (function() {
      thisobj.value = 'Write a comment...';
      thisobj.addClass('aaf_color_light');
      thisobj.getNext('.Linkedin_activity-comment-submit').style.display = 'none';
      //$('fbcomments_author_photo-' + post_id).setStyle('display', 'none');
    }).delay(50);
  }
  
}

var AAF_ShowFeedDialogue_Linkedin = function (feedurl) { 
  aaf_feed_type_tmp = 5;
  //  resetAAFTextarea (composeInstance.plugins);
  if (linkedin_loginURL_temp == '') {
    linkedin_loginURL_temp = feedurl;
  }
  activityfeedtype = 'linkedin';
  if (typeof current_window_url != 'undefined' && current_window_url != ''){
    linkedin_loginURL_temp = en4.core.baseUrl + 'seaocore/auth/linkedin?redirect_urimain=' + current_window_url + '?redirect_linkedin=1';
  }
  if (history.pushState)
    history.pushState( {}, document.title, current_window_url+"?activityfeedtype="+ activityfeedtype );
  var child_window = window.open (linkedin_loginURL_temp ,'mywindow','width=800,height=700');
  
}

var checkLinkedin = function () { 
  if (linkedin_loginURL != '') { 
    if ($type($('compose-linkedin-form-input'))) {
      $('compose-linkedin-form-input').disabled = 'disabled';
      $('composer_linkedin_toggle').addEvent('click', function(event) { 
        if (linkedin_loginURL != '') { 
          $('compose-linkedin-form-input').checked = false; 
         
          var child_window = window.open (linkedin_loginURL ,'mywindow','width=800,height=700');
          event.stop();
        }
      } ); 
    }   
      
  }
  else {
    if ($type($('compose-linkedin-form-input'))) {
      $('compose-linkedin-form-input').disabled = '';
    }
  }
  
}


var logout_aaflinkedin = function () { 
 
  var request = new Request.JSON({
    'url' : en4.core.baseUrl + 'seaocore/auth/logout/',
    'method':'get',
    'data' : {     
      'is_ajax' : '1',
      'logout_service' : 'linkedin'
     
		
    },
              
    onComplete : function(responseJSON) { 
      action_logout_taken_linkedin = 1;
      linkedin_loginURL = linkedin_loginURL_temp;
      if ($('compose-linkedin-form-input')) {
        $('compose-linkedin-form-input').set('checked', !$('compose-linkedin-form-input').get('checked'));
        if($('composer_linkedin_toggle').hasClass('composer_linkedin_toggle_active') )
          $('composer_linkedin_toggle').removeClass('composer_linkedin_toggle_active');
      //$('composer_linkedin_toggle').toggleClass('composer_linkedin_toggle_active');
      }
      $('aaf_main_tab_logout').style.display = 'none';
      $('aaf_main_tab_refresh').style.display = 'none';
      if ($('activity-post-container'))
      $('activity-post-container').style.display = 'none';
      $('aaf_main_contener_feed_5').innerHTML = '<div class="aaf_feed_tip"><span>' + en4.core.language.translate('You need to be logged into LinkedIn to see your LinkedIn Connections Feed.') + ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_Linkedin()" >' +  en4.core.language.translate('Click here') + '</a>.</span></div> <br />';
      
    }
  });
  request.send();
}



var linkedin_memberid;
//sending the message of the users whose feeds are displaying in the activity feed area.
var sendLinkedinMessage = function (thisobj, method, connecter_name, connected_name, connecter_id) { 
	
  if (method == 'get') {
		  linkedin_memberid = connecter_id;
			$('to-elementlinkedin').innerHTML = connecter_name;
			$('toValueslinkedin').value = connecter_id;
			if (connected_name != '')
				$('subject_linkedin').innerHTML = "Your new connection: " + connected_name;
			  var messagehtml = $('linkedinmessage_html').innerHTML;
			  $('subject_linkedin').innerHTML = '<br />';
			  $('toValueslinkedin').value = '';
			Smoothbox.open('<div>' + messagehtml + '</div>');
	}
	else {      
	 if (thisobj.getParent('.global_form').getElement('.compose-content').innerHTML == '' || thisobj.getParent('.global_form').getElement('.compose-textarea').value == '') {
		 
		 thisobj.getParent('.global_form').getElement('.show_errormessage').innerHTML = '<ul class="form-errors" style="margin:0px;"><li>Please fill the subject and message fields</li></ul>';
		 
		 return false;
		 
	 }
	 thisobj.getParent('.global_form').getElement('.show_errormessage').setStyle('display', 'none');
		$('titlelinkedin').value = thisobj.getParent('.global_form').getElement('.compose-content').innerHTML;
		
		$('linkedin_message_textarea').value = thisobj.getParent('.global_form').getElement('.compose-textarea').value;
		currentSearchParams = $('post_linkedin_message').toQueryString();
		thisobj.getPrevious().style.display = 'inline-block';
    thisobj.getPrevious().innerHTML = "<img src='application/modules/Seaocore/externals/images/core/loading.gif' alt='Loading' />";
		thisobj.setStyle ('display', 'none');
		currentSearchParams = currentSearchParams  + '&is_ajax=2&memberid='+ linkedin_memberid ;
//   
		userfeed_url = en4.core.baseUrl + 'widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed'; 
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

var linkedin_comment = function (thisobj) {
		var self1 = thisobj.getParent('.aaf_feed_item_stats');
		var commentform = self1.getNext('.aaf_feed_comment_commentbox').getElement('.CommentonPost_linkedin');
		self1.getNext('.aaf_feed_comment_commentbox').style.display = 'block';
		commentform.setStyle('display', 'block');
 
  (function() { 
    showHideCommentbox_linkedin (commentform.getElement('.LinkedinCommentonPost_submit'),  1)
  }).delay(60); 
	
	
}

var showAllComments = function (thisobj, classname) { 
	
	thisobj.setStyle('display', 'none');
	
	$$('.feedcomment-' + classname).each (function (element) { 
		
		element.setStyle('display', 'block');
		
		
		
	});
	
	
	
}