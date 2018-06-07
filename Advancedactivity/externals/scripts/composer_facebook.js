/* $Id: composer_facebook.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
Technologies Pvt. Ltd. $
 */


Composer.Plugin.AdvFacebook = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'advanced_facebook',

  options : {
    title : 'Publish this on Facebook',
    lang : {
        'Publish this on Facebook': 'Publish this on Facebook',
				'Do not publish this on Facebook': 'Do not publish this on Facebook'
    },
    requestOptions : false,
    fancyUploadEnabled : false,
    fancyUploadOptions : {}
  },

  initialize : function(options) { 
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
		
  },

  attach : function() {
    this.elements.spanToggle = new Element('span', {
      'id'    : 'composer_facebook_toggle',
      'class' : 'composer_facebook_toggle',
      'href'  : 'javascript:void(0);',
      'events' : {
        'click' : this.toggle.bind(this)
      },
      'css': 'background-position:right !important;padding-right:15px;'
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-facebook-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_facebook',
      'style' : 'display:none;',
      'events' : {
        'click' : this.toggle_checkbox.bind(this)
      }
    });
    this.elements.formCheckbox_fbprofile = new Element('input', {
      'id'    : 'post-facebook-profile',      
      'type'  : 'hidden',
      'name'  : 'post_to_facebook_profile',
      'value' :  true
    });
    this.elements.formCheckbox_fbpage = new Element('input', {
      'id'    : 'post-facebook-page',      
      'type'  : 'hidden',
      'name'  : 'post_to_facebook_page',
      'value' : true
    });
    
    this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-facebook-form-input',
      'class' : 'aaf_composer_tooltip',
      'html' : this.options.lang['Publish this on Facebook'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />'
			
      
    });

    this.elements.formCheckbox.inject(this.elements.spanToggle);
    this.elements.formCheckbox_fbpage.inject(this.elements.spanToggle);
    this.elements.formCheckbox_fbprofile.inject(this.elements.spanToggle);
    this.elements.spanTooltip.inject(this.elements.spanToggle);
    this.elements.spanToggle.inject($('advanced_compose-menu'));

    //this.parent();
    //this.makeActivator();
    return this;
  },

  detach : function() {
    this.parent();
    return this;
  },

  toggle : function(event) { 
    if (fb_loginURL == '') {
    $('compose-facebook-form-input').set('checked', !$('compose-facebook-form-input').get('checked'));
    
    event.target.toggleClass('composer_facebook_toggle_active');
    composeInstance.plugins['advanced_facebook'].active=true;
    setTimeout(function(){
      composeInstance.plugins['advanced_facebook'].active=false;
    }, 300);
		 
		 if (!event.target.hasClass('composer_facebook_toggle_active')) { 
				this.elements.spanTooltip.innerHTML = this.options.lang['Publish this on Facebook'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
		 }
		 else {  
       var subjectPostFBArray = ['sitepage_page', 'sitebusiness_business', 'sitegroup_group', 'sitestore_store'];
       if(en4.core.subject && subjectPostFBArray.indexOf(en4.core.subject.type) != -1) {  
          var subjectName = en4.core.subject.type.split('_');
          if(subjectName[1] == 'group')
             var contenttype = '<a href="'+ fblinkedpage +'" target="_blank">' + en4.core.language.translate('Group') + '</a>';
           else
             var contenttype = '<a href="'+ fblinkedpage +'" target="_blank">' + en4.core.language.translate('Page') + '</a>';;
          //CHECK IF THE ADMIN IS ALLOWED TO PUBLISH ON FACEBOOK OR NOT.
          if(fbpublishconfirmbox != null && fbpublishconfirmbox != 'undefined') 
            //fbpublishconfirmbox = fbpublishconfirmbox.split('-');
          if(fbpublishconfirmbox == '1-1-2' ) {
            $('post-facebook-page').value = true;
            $('post-facebook-profile').value = true;
            responseHTML = '<div><h3 class="mbot10">' + en4.core.language.translate('Choose places to publish on Facebook') +'</h3></div> <div><input type="checkbox" id="post-facebook-page-temp" class="compose-form-input " name="post_to_facebook_page_temp" onclick="$(\'post-facebook-page\').value= $(\'post-facebook-page-temp\').get(\'checked\')" checked="checked"><span>' + en4.core.language.translate('Publish this post on Facebook %1s linked with this %2s.', contenttype, subjectName[1].capitalize()) + '</span><br /><input type="checkbox" id="post-facebook-profile-temp" class="compose-form-input " name="post_to_facebook_profile_temp" onclick="$(\'post-facebook-profile\').value= $(\'post-facebook-profile-temp\').get(\'checked\')" checked="checked"><span>'+ en4.core.language.translate('Publish this post on my Facebook Timeline.') + '</span></div><br /><div class="form-wrapper buttons-wrapper"><button onclick="SmoothboxSEAO.close();">' + en4.core.language.translate('Done') + '</button></div>'
            SmoothboxSEAO.open('<div id="siteevent_dayevents" style="width:550px;">'+ responseHTML + '</div>');
          }
          else {
            this.elements.formCheckbox_fbpage.destroy();
            this.elements.formCheckbox_fbprofile.destroy();
          }
       }
			 this.elements.spanTooltip.innerHTML = this.options.lang['Do not publish this on Facebook'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
		 }
    } 
  },
  
  toggle_checkbox : function(event) {    
    $('compose-facebook-form-input').set('checked', !$('compose-facebook-form-input').get('checked'));
    $('compose-facebook-form-input').parentNode.toggleClass('composer_facebook_toggle_active');
    composeInstance.plugins['advanced_facebook'].active=true;
    setTimeout(function(){
      composeInstance.plugins['advanced_facebook'].active=false;
    }, 300);
  }

});