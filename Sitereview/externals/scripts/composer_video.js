
/* $Id: composer_video.js 6590 2013-04-01 00:00:00Z SocialEngineAddOns Copyright 2012-2013 BigStep Technologies Pvt. Ltd. $ */


Composer.Plugin.Sitereview = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'sitereview',

  options : {
    title : 'Add Video',
    // Options for the link preview request
    requestOptions : {},
    // Various image filtering options
    imageMaxAspect : ( 10 / 3 ),
    imageMinAspect : ( 3 / 10 ),
    imageMinSize : 48,
    imageMaxSize : 5000,
    imageMinPixels : 2304,
    imageMaxPixels : 1000000,
    imageTimeout : 5000,
    // Delay to detect links in input
    monitorDelay : 250
  },

  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.parent();
    this.makeActivator();
    return this;
  },

  detach : function() {
    this.parent();
    return this;
  },

  activate : function() {
    if( this.active ) return;
    this.parent();

    this.makeMenu();
    this.makeBody();

    // Generate body contents
    // Generate form

    this.elements.formInput = new Element('select', {
      'id' : 'compose-sitereview_video-form-type',
      'class' : 'compose-form-input',
      'option' : 'test',
      'events' : {
        'change' : this.updateSitereviewvideoFields.bind(this)
      }
    }).inject(this.elements.body);
    var options = 0;
    $('compose-sitereview_video-form-type').options[options++] = new Option(this._lang('Choose Source'), '0');
    if (this.options.youtubeEnabled == 1) {
      $('compose-sitereview_video-form-type').options[options++] = new Option(this._lang('YouTube'), '1');
    }
    $('compose-sitereview_video-form-type').options[options++] = new Option(this._lang('Vimeo'), '2');

    if (this.options.allowed == 1){
      $('compose-sitereview_video-form-type').options[options++] = new Option(this._lang('My Computer'), '3');
    }
    //link to full composer for now
    //$('compose-sitereview_video-form-type').options[options++] = new Option('My Computer','3');
    
    this.elements.formInput = new Element('input', {
      'id' : 'compose-sitereview_video-form-input',
      'class' : 'compose-form-input',
      'type' : 'text',
      'style': 'display:none;'
    }).inject(this.elements.body);

    this.elements.previewDescription = new Element('div', {
      'id' : 'compose-sitereview_video-upload',
      'class' : 'compose-sitereview_video-upload',
      'html' : this._lang('To upload a video from your computer, please use our full uploader.'),
      'style': 'display:none;'
    }).inject(this.elements.body);


    this.elements.formSubmit = new Element('button', {
      'id' : 'compose-sitereview_video-form-submit',
      'class' : 'compose-form-submit',
      'style': 'display:none;',
      'html' : this._lang('Attach'),
      'events' : {
        'click' : function(e) {
          e.stop();
          this.doAttach();
        }.bind(this)
      }
    }).inject(this.elements.body);

    this.elements.formInput.focus();
  },

  deactivate : function() {
    // clean video out if not attached
    if (this.params.video_id)
      new Request.JSON({
        url: en4.core.basePath + 'sitereview/video/delete',
        data: {
          format: 'json',
          video_id: this.params.video_id
        }
      }).send();
    if( !this.active ) return;
    this.parent();

    this.request = false;
  },



  // Getting into the core stuff now

  doAttach : function(e) {
    var val = this.elements.formInput.value;
    if( !val )
    {
      return;
    }
    if( !val.match(/^[a-zA-Z]{1,5}:\/\//) )
    {
      val = 'http://' + val;
    }
    this.params.set('uri', val)
    // Input is empty, ignore attachment
    if( val == '' ) {
      e.stop();
      return;
    }
    
    var sitereview_video_element = document.getElementById("compose-sitereview_video-form-type");
    var type = sitereview_video_element.value;
    // Send request to get attachment
    var options = $merge({
      'data' : {
        'format' : 'json',
        'uri' : val,
        'type': type
      },
      'onComplete' : this.doProcessResponse.bind(this)
    }, this.options.requestOptions);

    // Inject loading
    this.makeLoading('empty');

    // Send request
    this.request = new Request.JSON(options);
    this.request.send();
  },

  doProcessResponse : function(responseJSON, responseText) {
    // Handle error
    if( ($type(responseJSON) != 'hash' && $type(responseJSON) != 'object') || $type(responseJSON.src) != 'string' || $type(parseInt(responseJSON.video_id)) != 'number' ) {
      //this.elements.body.empty();
      if( this.elements.loading ) this.elements.loading.destroy();
      //this.makeaError(responseJSON.message, 'empty');
      this.makeError(responseJSON.message);

      //compose-video-error
      //ignore test
      this.elements.ignoreValidation = new Element('a', {
        'href' : this.params.uri,
        'html' : this.params.title,
        'events' : {
          'click' : function(e) {
            e.stop();
            self.doAttach(this);
          }
        }
      }).inject(this.elements.previewTitle);
      
      return;
    //throw "unable to upload image";
    }

    var title = responseJSON.title || this.params.get('uri').replace('http://', '');
    

    this.params.set('title', responseJSON.title);
    this.params.set('description', responseJSON.description);
    this.params.set('photo_id', responseJSON.photo_id);
    this.params.set('video_id', responseJSON.video_id);
    this.elements.preview = Asset.image(responseJSON.src, {
      'id' : 'compose-sitereview_video-preview-image',
      'class' : 'compose-preview-image',
      'onload' : this.doImageLoaded.bind(this)
    });
  },

  doImageLoaded : function() {
    var self = this;

    if( this.elements.loading ) this.elements.loading.destroy();
    this.elements.preview.erase('width');
    this.elements.preview.erase('height');
    this.elements.preview.inject(this.elements.body);

    this.elements.previewInfo = new Element('div', {
      'id' : 'compose-sitereview_video-preview-info',
      'class' : 'compose-preview-info'
    }).inject(this.elements.body);

    this.elements.previewTitle = new Element('div', {
      'id' : 'compose-sitereview_video-preview-title',
      'class' : 'compose-preview-title'
    }).inject(this.elements.previewInfo);

    this.elements.previewTitleLink = new Element('a', {
      'href' : this.params.uri,
      'html' : this.params.title,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditTitle(this);
        }
      }
    }).inject(this.elements.previewTitle);

    this.elements.previewDescription = new Element('div', {
      'id' : 'compose-sitereview_video-preview-description',
      'class' : 'compose-preview-description',
      'html' : this.params.description,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditDescription(this);
        }
      }
    }).inject(this.elements.previewInfo);
    this.makeFormInputs();
  },

  makeFormInputs : function() {
    this.ready();
    this.parent({
      'photo_id' : this.params.photo_id,
      'video_id' : this.params.video_id,
      'title' : this.params.title,
      'description' : this.params.description
    });
  },

  updateSitereviewvideoFields : function(element) {
    var sitereview_video_element = document.getElementById("compose-sitereview_video-form-type");
    var url_element = document.getElementById("compose-sitereview_video-form-input");
    var post_element = document.getElementById("compose-sitereview_video-form-submit");
    var upload_element = document.getElementById("compose-sitereview_video-upload");
    // clear url if input field on change
    $('compose-sitereview_video-form-input').value = "";

    // If sitereview_video source is empty
    if (sitereview_video_element.value == 0)
    {
      upload_element.style.display = "none";
      post_element.style.display = "none";
      url_element.style.display = "none";
    }

    // If sitereview_video source is youtube or vimeo
    if (sitereview_video_element.value == 1 || sitereview_video_element.value == 2)
    {
      upload_element.style.display = "none";
      post_element.style.display = "block";
      url_element.style.display = "block";
      url_element.focus();
    }

    // if sitereview_video source is upload
    if (sitereview_video_element.value == 3)
    {
      upload_element.style.display = "block";
      post_element.style.display = "none";
      url_element.style.display = "none";
    }
  },
  handleEditTitle : function(element) {
    element.setStyle('display', 'none');
    var input = new Element('input', {
      'type' : 'text',
      'value' : htmlspecialchars_decode(element.get('html').trim()),
      'events' : {
        'blur' : function() {
          if( input.value.trim() != '' ) {
            this.params.title = input.value;
            element.set('html', this.params.title);
            this.setFormInputValue('title', this.params.title);
          }
          element.setStyle('display', '');
          input.destroy();
        }.bind(this)
      }
    }).inject(element, 'after');
    input.focus();
  },
  handleEditDescription : function(element) {
    element.setStyle('display', 'none');
    var input = new Element('textarea', {
      'html' : htmlspecialchars_decode(element.get('html').trim()),
      'events' : {
        'blur' : function() {
          if( input.value.trim() != '' ) {
            this.params.description = input.value;
            element.set('html', this.params.description);
            this.setFormInputValue('description', this.params.description);
          }
          else{
            this.params.description = '';
            element.set('html', '');
            this.setFormInputValue('description', '');
          }
          element.setStyle('display', '');
          input.destroy();
        }.bind(this)
      }
    }).inject(element, 'after');
    input.focus();
  }
});