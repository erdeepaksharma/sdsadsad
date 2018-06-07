/* $Id: composer_socialengine.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep
Technologies Pvt. Ltd. $ */


Composer.Plugin.Socialengine = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'advanced_socialengine',

  options : {
    title : 'Publish this on Socialengine',
    lang : {
        'Publish this on Socialengine': 'Publish this on Socialengine'
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
      'id' : 'composer_socialengine_toggle',
      'class' : 'composer_socialengine_toggle',
      'href'  : 'javascript:void(0);',
      'events' : {
        'click' : this.toggle.bind(this)
      },
      'css': 'background-position:right !important;padding-right:15px;'
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-socialengine-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_socialengine',
      'style' : 'display:block;',
      'events' : {
        'click' : this.toggle_checkbox.bind(this)
      }
    });
    
    this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-socialengine-form-input',
      'class' : 'composer_socialengine_tooltip',
      'html' : this.options.lang['Publish this on Socialengine']
      
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
    $('compose-socialengine-form-input').set('checked', !$('compose-socialengine-form-input').get('checked'));
    
    event.target.toggleClass('composer_socialengine_toggle_active');
    composeInstance.plugins['advanced_socialengine'].active=true;
    setTimeout(function(){
      composeInstance.plugins['advanced_socialengine'].active=false;
    }, 300);
  },
  
  toggle_checkbox : function(event) { 
   
    $('compose-socialengine-form-input').set('checked', !$('compose-socialengine-form-input').get('checked'));
    $('compose-socialengine-form-input').parentNode.toggleClass('composer_socialengine_toggle_active');
    composeInstance.plugins['advanced_socialengine'].active=true;
    setTimeout(function(){
      composeInstance.plugins['advanced_socialengine'].active=false;
    }, 300);
  }
  
  
});