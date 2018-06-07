/* $Id: composer_linkedin.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
Technologies Pvt. Ltd. $
 */


Composer.Plugin.AdvLinkedin = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'advanced_linkedin',

  options : {
    title : 'Publish this on LinkedIn',
    lang : {
        'Publish this on LinkedIn': 'Publish this on LinkedIn',
				'Do not publish this on LinkedIn': 'Do not publish this on LinkedIn'
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
      'id'    : 'composer_linkedin_toggle',
      'class' : 'composer_linkedin_toggle',
      'href'  : 'javascript:void(0);',
      'events' : {
        'click' : this.toggle.bind(this)
      },
      'css': 'background-position:right !important;padding-right:15px;'
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-linkedin-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_linkedin',
      'style' : 'display:none;',
      'events' : {
        'click' : this.toggle_checkbox.bind(this)
      }
    });
    
    this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-linkedin-form-input',
      'class' : 'aaf_composer_tooltip',
      'html' : this.options.lang['Publish this on LinkedIn'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />'
			
      
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
    if (linkedin_loginURL == '') {
    $('compose-linkedin-form-input').set('checked', !$('compose-linkedin-form-input').get('checked'));
    
    event.target.toggleClass('composer_linkedin_toggle_active');
    composeInstance.plugins['advanced_linkedin'].active=true;
    setTimeout(function(){
      composeInstance.plugins['advanced_linkedin'].active=false;
    }, 300);
		
		if (!event.target.hasClass('composer_linkedin_toggle_active')) { 
				this.elements.spanTooltip.innerHTML = this.options.lang['Publish this on LinkedIn'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
		 }
		 else {
			 this.elements.spanTooltip.innerHTML = this.options.lang['Do not publish this on LinkedIn'] + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
		 }
    } 
  },
  
  toggle_checkbox : function(event) { 
   
    $('compose-linkedin-form-input').set('checked', !$('compose-linkedin-form-input').get('checked'));
    $('compose-linkedin-form-input').parentNode.toggleClass('composer_linkedin_toggle_active');
    composeInstance.plugins['advanced_linkedin'].active=true;
    setTimeout(function(){
      composeInstance.plugins['advanced_linkedin'].active=false;
    }, 300);
  }

});