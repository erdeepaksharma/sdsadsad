/* $Id: composer_twitter.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep Technologies
Pvt. Ltd. $
 */


Composer.Plugin.AdvTwitter = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'advanced_twitter',

  options : {
    title : 'Publish this on Twitter',
    lang : {
        'Publish this on Twitter': 'Publish this on Twitter',
				'Do not publish this on Twitter': 'Do not publish this on Twitter'
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
      'id' : 'composer_twitter_toggle',
      'class' : 'composer_twitter_toggle',
      'href'  : 'javascript:void(0);',
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-twitter-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_twitter',
      'style' : 'display:none;',
      'events' : {
        'click' : this.toggle_checkbox.bind(this)
      }
      
    });
    
    this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-twitter-form-input',
      'class' : 'aaf_composer_tooltip',
      'html' : this.options.lang['Publish this on Twitter'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />'
    });

    this.elements.formCheckbox.inject(this.elements.spanToggle);
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
    if (tweet_loginURL == '') {
      $('compose-twitter-form-input').set('checked', !$('compose-twitter-form-input').get('checked'));
      event.target.toggleClass('composer_twitter_toggle_active');
      composeInstance.plugins['advanced_twitter'].active = true;
      setTimeout(function(){
        composeInstance.plugins['advanced_twitter'].active = false;
      }, 300);
			if (!event.target.hasClass('composer_twitter_toggle_active')) { 
				this.elements.spanTooltip.innerHTML = this.options.lang['Publish this on Twitter'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
		 }
		 else {
			 this.elements.spanTooltip.innerHTML = this.options.lang['Do not publish this on Twitter'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
		 }
    }
  },
  
  toggle_checkbox : function(event) { 
    
    $('compose-twitter-form-input').set('checked', !$('compose-twitter-form-input').get('checked'));
    $('compose-twitter-form-input').parentNode.toggleClass('composer_twitter_toggle_active');
    composeInstance.plugins['advanced_twitter'].active = true;
    setTimeout(function(){
      composeInstance.plugins['advanced_twitter'].active = false;
    }, 300);
    
  }

});