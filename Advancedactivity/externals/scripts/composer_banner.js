(function () {
  var $ = 'id' in document ? document.id : window.$;

  Composer.Plugin.Banner = new Class({

    Extends: Composer.Plugin.Interface,

    name: 'banner',
    loaded: false,
    handlerSpan: null,
    options: {
      title: 'Add Banner',
      lang: {},
      resetBanner: null,
    },
    activeBannerEl: null,
    activator: null,
    defaultBanner: null,
    initialize: function (options) {
      this.params = new Hash(this.params);
      this.parent(options);
    },
    loadPreReq: function () {
      if (this.options.requestOptions.banners.length > 0) {
        this.loaded = true;
        this.attach();
        return;
      }
      var self = this;
      var request = new Request.JSON({
        'url': en4.core.baseUrl + 'advancedactivitys/banners/load-banners',
        method: 'get',
        'data': {
          'format': 'json',
        },
        'onSuccess': function (responseJSON, responseHTML) {
          self.options.requestOptions.banners = responseJSON;
          self.loaded = true;
          self.attach();
        }
      });
      request.send();
    },
    attach: function () {
      if (!this.loaded) {
        this.loadPreReq();
        return;
      }
      this.parent();
      this.makeActivator();
      this.activate();
      var self = this;
      this.getComposer().addEvent('focus', function (e) {
        if (self.options.resetBanner) {
          self.setBannerImage(self.options.resetBanner, true);
          self.options.resetBanner = null;
        }
      });
      this.getComposer().addEvent('close', function (e) {
        if (this.activeBannerEl) {
          this.options.resetBanner = this.activeBannerEl;
          this.setDefaultBanner();
        }
      }.bind(this));
      this.getComposer().addEvent('changePluginReady', function () {
        if (this.getComposer().pluginReady) {
          if (this.activeBannerEl) {
            this.options.resetBanner = this.activeBannerEl;
            this.setDefaultBanner();
          }
          this.activator.addClass('dnone');
        } else {
          if (this.options.requestOptions.feed_length >= this.getComposer().elements.body.value.length) {
            if (this.options.resetBanner) {
              this.setBannerImage(self.options.resetBanner, true);
              this.options.resetBanner = null;
            }
            this.activator.removeClass('dnone');
          }
        }
      }.bind(this));
    },
    makeActivator: function () {
      if (!this.activator) {
        this.activator = new Element('span', {
          'id': 'compose-' + this.getName() + '-activator',
          'class': 'compose-activator',
          'href': 'javascript:void(0);',

        }).inject(this.getComposer().getForm().getElement('.composer_preview_display_tray'), "after");
        this.handlerSpan = new Element('div', {
          'class': 'compose-banner-handler compose-banner-activator-expand',
          'events': {
            'click': this.activate.bind(this)
          }
        }).inject(this.activator);

      }
    },
    activate: function () {
      if (this.active && this.bannerUl) {
        if (!this.bannerContainer.hasClass('compose-banner-handler-expand')) {
          this.bannerContainer.addClass('compose-banner-handler-expand');
          this.handlerSpan.addClass('compose-banner-activator-expand');
        } else {
          this.bannerContainer.removeClass('compose-banner-handler-expand');
          this.handlerSpan.removeClass('compose-banner-activator-expand');
        }
        return;
      }
      this.active = true;
      this.bannerContainer = new Element('div', {
        'id': 'compose-banner-container',
        'class': 'compose-banner-container compose-banner-handler-expand',
      }).inject(this.activator);

      this.bannerUl = new Element('ul', {
        'id': 'compose-banner-wrapper',
        'class': 'compose-banner-wrapper',
      }).inject(this.bannerContainer);
      this.bannerNoImage = new Element('li', {
        'class': 'compose-banner-image compose-banner-no-image aaf_active_banner',
        'data-source': '',
        'events': {
          'click': function (e) {
            this.options.resetBanner = null;
            this.setDefaultBanner(e);
          }.bind(this)
        }
      }).inject(this.bannerUl);
      this.hiddenInput = new Element('input', {
        'id': 'feed-banner-image',
        'name': 'composer[banner][image]',
        'type': 'hidden'
      }).inject(this.activator);
      this.hiddenInputColor = new Element('input', {
        'id': 'feed-banner-color',
        'name': 'composer[banner][color]',
        'type': 'hidden'
      }).inject(this.activator);
      this.hiddenInputBackgroundColor = new Element('input', {
        'id': 'feed-banner-color',
        'name': 'composer[banner][background-color]',
        'type': 'hidden'
      }).inject(this.activator);
      $('compose-tray').setStyle('display', 'none');
      this.setTextareaListener();
      var images = this.options.requestOptions.banners;
      var defaultBannerLi;
      for (var i = 0; i < images.length; i++) {
        var className = 'compose-banner-image';
        if (images[i]['highlighted'] && images[i]['highlighted'] == 1) {
          className += " compose-banner-image-highlighted";
          delete images[i]['highlighted'];
        }
        var bannerImage = new Element('li', {
          'class': className,
          'events': {
            'click': function (e) {
              this.setBanner(e);
            }.bind(this)
          }
        }).inject(this.bannerUl);
        bannerImage.store('bannerConfig', images[i]);
        bannerImage.setStyles(images[i]);
        if (this.options.defaultBanner) {
          var defaultBanner = this.options.defaultBanner;
          if (defaultBanner.color == images[i].color && defaultBanner.backgroundColor == images[i].backgroundColor && defaultBanner.backgroundImage == images[i].backgroundImage) {
            defaultBannerLi = bannerImage;
          }
        }
      }

      if (this.options.defaultBanner && !defaultBannerLi) {
        defaultBannerLi = new Element('li', {
          'class': className,
          'events': {
            'click': function (e) {
              this.setBanner(e);
            }.bind(this)
          }
        }).inject(this.bannerUl);
        defaultBannerLi.store('bannerConfig', this.options.defaultBanner);
        defaultBannerLi.setStyles(this.options.defaultBanner);
      }
      if (this.options.defaultBanner && defaultBannerLi) {
        this.options.resetBanner = null;
        this.setBannerImage(defaultBannerLi, true);
      }
    },
    setBanner: function (e) {
      this.options.resetBanner = null;
      this.setBannerImage(e.target, false);
      this.getComposer().autogrow.handle();
    },
    reset: function () {
      this.deactivate();
    },
    deactivate: function () {
      if (!this.active)
        return;
      //  this.parent();
      this.setDefaultBanner();
    },
    setTextareaListener: function () {
      var self = this;
      this.getComposer().elements.body.addEventListener('input', function () {
        // Haha !! Blunder,but you can try.
        if (self.options.requestOptions.feed_length < this.value.length) {
          if (!self.options.resetBanner) {
            self.options.resetBanner = self.activeBannerEl;
            self.setDefaultBanner();
          }
          if (!self.activator.hasClass('dnone')) {
            self.activator.addClass('dnone');
          }
        } else {
          if (self.options.resetBanner) {
            self.setBannerImage(self.options.resetBanner, false);
            self.options.resetBanner = null;
          }
          if (!self.getComposer().pluginReady && self.activator.hasClass('dnone')) {
            self.activator.removeClass('dnone');
          }
        }
      });
    },
    setDefaultBanner: function (e) {
      this.getComposer().getForm().getElements('.adv_post_container_box').setStyles({'backgroundImage': '', 'backgroundColor': '', color: ''}).removeClass('compose-box-banner-added');
      this.bannerUl.getElements('.compose-banner-image').each(function (item) {
        item.removeClass('aaf_active_banner');
      });
      this.getComposer().getForm().getElements('.composer-adv-photo').removeClass('dnone');
      this.getComposer().getForm().removeClass('compose-container-banner-image-added');
      if (this.activeBannerEl || !!e) {
        this.getComposer().elements.textarea.focus();
      }
      if (this.getComposer().elements.textarea.value.length > 0 && this.getComposer().elements.textarea.value.length <= composeInstance.options.textLimit) {
        this.getComposer().getForm().addClass('compose-container-text-decoration');
      }
      this.hiddenInputColor.value = '';
      this.hiddenInput.value = '';
      this.hiddenInputBackgroundColor.value = '';
      if (this.bannerNoImage)
        this.bannerNoImage.addClass('aaf_active_banner');
      this.activeBannerEl = null;
    },
    setBannerImage: function (element, igonreFocus) {
      var options = element ? element.retrieve('bannerConfig', {}) : {};
      this.bannerUl.getElements('.compose-banner-image').each(function (item) {
        item.removeClass('aaf_active_banner');
      });
      this.getComposer().getForm().getElements('.adv_post_container_box').setStyles(options).addClass('compose-box-banner-added');
      this.getComposer().getForm().addClass('compose-container-banner-image-added');
      this.getComposer().getForm().getElements('.composer-adv-photo').addClass('dnone');
      if (this.getComposer().getForm().hasClass('compose-container-text-decoration')) {
        this.getComposer().getForm().removeClass('compose-container-text-decoration');
      }
      this.hiddenInputColor.value = options.color;
      this.hiddenInput.value = options.backgroundImage;
      this.hiddenInputBackgroundColor.value = options.backgroundColor;
      this.activeBannerEl = element;
      element.addClass('aaf_active_banner');
      this.getComposer().focus();
    }
  });
})();
