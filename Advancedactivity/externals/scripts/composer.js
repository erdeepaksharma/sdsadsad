/* $Id: composer.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep Technologies Pvt.
 Ltd. $ */



(function () { // START NAMESPACE
  var $ = 'id' in document ? document.id : window.$;
  Composer = new Class({
    Implements: [Events, Options],
    elements: {},
    plugins: {},
    autogrow: null,
    highlighterBody: null,
    hiddenBody: null,
    options: {
      lang: {},
      overText: true,
      allowEmptyWithoutAttachment: false,
      allowEmptyWithAttachment: true,
      hideSubmitOnBlur: true,
      submitElement: false,
      userPhoto: false,
      maxAllowActivator: 100,
    },
    iconCodes: [],
    highlighterText: '',
    initialize: function (element, options) {
      this.setOptions(options);
      this.elements = new Hash(this.elements);
      this.plugins = new Hash(this.plugins);
      this.elements.textarea = $(element);
      this.elements.textarea.store('Composer');
      this.attach();
      this.getTray();
      this.getTopTray();
      this.getMenu();
      this.pluginReady = false;

      this.getForm().addEvent('submit', function (e) {
        this.fireEvent('editorSubmit');
        this.saveContent();
        if (this.options.submitCallBack) {
          this.options.submitCallBack(this, e);
        }
      }.bind(this));
    },
    getMenu: function () {
      if (!$type(this.elements.menu)) {
        this.elements.menu = $try(function () {
          return $(this.options.menuElement);
        }.bind(this));
        if (!$type(this.elements.menu)) {
          this.elements.menu = new Element('div', {
            'id': 'compose-menu',
            'class': 'compose-menu'
          }).inject(this.getForm(), 'after');
        }
      }
      return this.elements.menu;
    },
    getActivatorContent: function () {
      if (!$type(this.elements.activatorContent)) {
        this.elements.activatorContent = $try(function () {
          return $(this.options.activatorContent);
        }.bind(this));
        if (!$type(this.elements.activatorContent)) {
          this.elements.menu = new Element('div', {
            'id': 'compose-activator-content',
            'class': 'adv_post_compose_menu'
          }).inject(this.getForm(), 'after');
        }
      }
      return this.elements.activatorContent;
    },
    getTopTray: function () {
      if (!$type(this.elements.topTray)) {
        this.elements.topTray = $try(function () {
          return $(this.options.topTrayElement);
        }.bind(this));
        if (!$type(this.elements.topTray)) {
          this.elements.topTray = new Element('div', {
            'id': 'compose-top-tray',
            'class': 'compose-top-tray',
            'styles': {
              'display': 'none'
            }
          }).inject(this.getForm(), 'after');
        }
      }
      return this.elements.topTray;
    },
    getTray: function () {
      if (!$type(this.elements.tray)) {
        this.elements.tray = $try(function () {
          return $(this.options.trayElement);
        }.bind(this));
        if (!$type(this.elements.tray)) {
          this.elements.tray = new Element('div', {
            'id': 'compose-tray',
            'class': 'compose-tray',
            'styles': {
              'display': 'none'
            }
          }).inject(this.getForm(), 'after');
        }
      }
      return this.elements.tray;
    },
    getInputArea: function () {
      if (!$type(this.elements.inputarea)) {
        var form = this.getForm();
        this.elements.inputarea = new Element('div', {
          'styles': {
            'display': 'none'
          }
        }).inject(form);
      }
      return this.elements.inputarea;
    },
    getForm: function () {
      return this.elements.textarea.getParent('form');
    },
    // Editor
    attach: function () {
      // Create container
      this.elements.container = new Element('div', {
        'id': 'compose-container',
        'class': 'compose-container',
        'styles': {
        }
      });
      this.elements.container.wraps(this.elements.textarea);
      // Create body
      if (this.options.userPhoto) {
        this.elements.photo = new Element('div', {
          'class': 'composer-adv-photo',
          'html': this.options.userPhoto,
          'styles': {
            'display': 'inline-block',
          //  'position': 'absolute',
           // 'zIndex': 20
          }
        }).inject(this.elements.container.getParent(), 'before');
        this.elements.container.getParent().getParent().addClass('adv-photo');
      }

      this.elements.body = this.elements.textarea;
      this.addHighlighter();
      // Attach blur event
      var self = this;
      var foucsMethod = function () {
        if (!self.getForm().hasClass('adv-active')) {
          self.elements.body.set('html', '');
          self.getForm().addClass('adv-active');
          if (self.getActivatorContent().hasClass('adv_post_compose_menu_anactive'))
            self.getActivatorContent().removeClass('adv_post_compose_menu_anactive');
        }
        self.getForm().getParent('.aaf_feed_box_container').addClass('aaf_feed_box_container_active');
        self.getMenu().setStyle('display', '');
        self.getMenu().removeClass('dnone');
      };
      if (self.options.hideSubmitOnBlur) {
        this.getMenu().setStyle('display', 'none');
        this.elements.textarea.addEvent('focus', foucsMethod);
        if (self.options.template !== 'activator_buttons') {
          $('activity-post-container').addEvent('click', foucsMethod);
        }
        this.addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'editorKeyDown' : 'editorKeyPress', function (event) {
          if (!self.getForm().hasClass('adv-active')) {
            foucsMethod();
          }
        }.bind(this));
      }
      if (self.options.template === 'activator_buttons') {
        $('activity-post-container').addEvent('click', function (event) {
          if (!event.target.hasClass('adv_post_close') && !$('feed_box').hasClass("feed_box")) {
            foucsMethod();
            self.getActivatorContent().addClass('composer_activator_expand_more_options');
            self.getActivatorContent().removeClass('composer_activator_collapse_more_options');
            $('feed_box').addClass("feed_box");
            if ($$('.adv_post_close')[0])
              $$('.adv_post_close')[0].removeClass('dnone');
            this.focus();
            var fx = self.getForm().retrieve('fxScroll');
            if (!fx && window.Fx && Fx.Scroll) {
              var form = self.getForm();
              fx = new Fx.Scroll(document.body, {
                transition: 'quad:out',
              });
              form.store('fvScroller', fx);
            }
            if (fx) {
              fx.toElementEdge(form, null, {y: 100});
            }
          }
        }.bind(this));
        $('feed_box').addEvent('click', function () {
          $('feed_box').removeClass("feed_box");
          self.getForm().getParent('.aaf_feed_box_container').removeClass('aaf_feed_box_container_active');
          self.getActivatorContent().removeClass('composer_activator_expand_more_options');
          self.getActivatorContent().addClass('composer_activator_collapse_more_options');
          if ($$('.adv_post_close')[0])
            $$('.adv_post_close')[0].addClass('dnone');
          $('composer_preview_display_tray').addClass('dnone');
          self.getMenu().addClass('dnone');
          self.close();
        }.bind(this));
      } else if (self.options.template === 'activator_top') {
        $('aaf_top_expand_more_options').addEvent('click', function (event) {
          var el = $(event.target);
          if (!el || el.getParent('.aaf_click_ignore') || $(event.target).hasClass('aaf_click_ignore')) {
            return;
          }
          self.getActivatorContent().toggleClass('composer_activator_expand_more_options');
          self.getActivatorContent().toggleClass('composer_activator_collapse_more_options');
        });
        $(document).addEvent('click', function (event) {
          var el = $(event.target);
          if (!el || el.getParent('.aaf_click_ignore') || $(event.target).hasClass('aaf_click_ignore') || el.getParent('.aaf_activaor_more') || $(event.target).hasClass('aaf_activaor_more')) {
            return;
          }
          self.getActivatorContent().removeClass('composer_activator_expand_more_options');
          self.getActivatorContent().addClass('composer_activator_collapse_more_options');
        });
      }

      this.autogrow = new Composer.Autogrow(this.elements.body, this.highlighterBody);

      this.elements.body.designMode = 'On';
      ['MouseUp', 'MouseDown', 'ContextMenu', 'Click', 'Dblclick', 'KeyPress', 'KeyUp', 'KeyDown', 'Paste', 'Cut'].each(function (eventName) {
        var method = (this['editor' + eventName] || function () {
        }).bind(this);
        this.elements.body.addEvent(eventName.toLowerCase(), method);
      }.bind(this));
      this.setContent(this.elements.textarea.value);
//      if (this.options.overText) {
//        this.elements.textarea.placeholder = this._lang('Post Something...');
//      }

      this.fireEvent('attach', this);
    },
    detach: function () {
      this.saveContent();
      this.textarea.setStyle('display', '').removeClass('compose-textarea').inject(this.container, 'before');
      this.container.dispose();
      this.fireEvent('detach', this);
      return this;
    },
    focus: function () {
      // needs the delay to get focus working
      (function () {
        this.elements.textarea.setStyle('display', '');
        this.elements.textarea.focus();
        this.fireEvent('focus', this);
      }).bind(this).delay(10);
      return this;
    },
    close: function () {
      this.fireEvent('close', this);
      return this;
    },
    reset: function () {
      this.signalPluginReady(false);
      this.setContent('');
      this.deactivate();
    },
    getContent: function () {
      return this.cleanup(this.elements.textarea.get('value'));
    },
    setContent: function (newContent) {
      this.elements.textarea.set('value', newContent);
      this.autogrow.handle();
      this.setHighlighterContent();
      return this;
    },
    saveContent: function () {
      this.elements.textarea.set('value', this.getContent());
      return this;
    },
    cleanup: function (html) {
      // @todo
      return html
              .replace(/<(br|p|div)[^<>]*?>/ig, "\r\n")
              .replace(/<[^<>]+?>/ig, ' ');
    },
    getCaretPosition: function () {
      var caretPosition = 0;
      if (document.selection) {
        this.elements.textarea.focus();
        var Sel = document.selection.createRange();
        Sel.moveStart('character', -this.elements.textarea.value.length);
        caretPosition = Sel.text.length;
      } else if (this.elements.textarea.selectionStart || this.elements.textarea.selectionStart == '0') {
        caretPosition = this.elements.textarea.selectionStart;
      }
      return caretPosition;
    },
    setCaretPosition: function (pos) {
      if (this.elements.textarea.createTextRange) {
        var range = this.elements.textarea.createTextRange();
        range.move("character", pos);
        range.select();
      } else if (this.elements.textarea.selectionStart) {
        this.elements.textarea.focus();
        this.elements.textarea.setSelectionRange(pos, pos);
      }
    },
    addHighlighter: function () {
      var wapper = new Element('div', {
        'id': this.elements.body.id + '-highlighter-wapper',
        'class': 'compose-highlighter-wapper',
      }).inject(this.elements.container, 'before');

      this.highlighterBody = new Element('div', {
        'id': this.elements.body.id + '-hightlighter',
        'class': 'compose-highlighter',
        'styles': {
          'height': this.elements.body.getSize().y + 'px'
        }
      }).inject(wapper, 'bottom');

      this.hiddenBody = new Element('input', {
        'type': 'hidden'
      }).inject(wapper);

      this.addEvent('editorKeyUp', this.setHighlighterContent);
      this.elements.body.addEvent('input', this.setHighlighterContent.bind(this));
      this.elements.container.style.position = 'relative';
    },
    setHighlighterContent: function () {
      var content = this.getContent();
      this.highlighterText = content;
      this.highlighterSegment = content;
      this.fireEvent('editorHighlighter');
      Object.each(this.iconCodes, function (iconSrc, iconCode) {
        var iconSRC = ('<i class="seaocore_emoji_composer_icon" style="background-image: IMG_SRC;" rev="' + iconCode + '">' + iconCode + '</i>').replace('IMG_SRC', "url('" + iconSrc + "')");
        this.highlighterText = this.highlighterText.replace(new RegExp(iconCode, 'g'), iconSRC);
      }.bind(this));
      this.highlighterBody.set('html', this.highlighterText);
      this.hiddenBody.set('value', this.highlighterSegment);
    },
    getHighlightString: function (str) {
      return '<span class="aaf_feed_composer_highlight_tag">' + str + '</span>';
    },
    getCaretCoordinates: function (stringLength) {
      var a, b, c, d, e, f, g, h, i, j, k;
      i = this.elements.body;
      if (i.selectionEnd == null)
        return;

      if (!stringLength || stringLength > i.selectionEnd) {
        stringLength = i.selectionEnd;
      }
      g = {
        position: "absolute",
        overflow: "auto",
        whiteSpace: "pre-wrap",
        wordWrap: "break-word",
        boxSizing: "content-box",
        top: 0,
        left: 9999
      }, h = ["boxSizing", "fontFamily", "fontSize", "fontStyle", "fontVariant", "fontWeight", "height", "letterSpacing", "lineHeight", "paddingBottom", "paddingLeft", "paddingRight", "paddingTop", "textAlign", "textDecoration", "textIndent", "textTransform", "width", "wordSpacing"];

      for (j = 0, k = h.length; j < k; j++) {
        e = h[j], g[e] = i.getStyle(e);
      }

      c = new Element('div', {styles: g}).inject(i, 'after');
      b = document.createTextNode(i.value.substring(0, stringLength)), c.appendChild(b);
      d = new Element('span', {'html': "&nbsp;"}).inject(c);
      a = document.createTextNode(i.value.substring(stringLength)), c.appendChild(a);
      c.scrollTop = i.scrollTop;
      f = d.getCoordinates(i.getOffsetParent());
      f.x = f.left - g.left;
      f.y = f.top - g.top;
      c.destroy();
      return f;
    },
    // Add Emotion Icon
    attachEmotionIcon: function (icon, iconSrc) {
      var pos = this.getCaretPosition();
      var text = this.elements.textarea.get('value');
      var t1 = text.substr(0, pos);
      var t2 = text.substr(pos);
      var iconCode = icon.get('data-icon');
      this.iconCodes[iconCode] = iconSrc + icon.get('data-url');
      iconCode = ' ' + iconCode + ' ';
      t1 = t1.trim();
      this.setContent(t1 + iconCode + t2);
      this.setCaretPosition(t1.length + iconCode.length);
    },
    // Plugins

    addPlugin: function (plugin) {
      var key = plugin.getName();
      this.plugins.set(key, plugin);
      plugin.setComposer(this);
      return this;
    },
    addPlugins: function (plugins) {
      plugins.each(function (plugin) {
        this.addPlugin(plugin);
      }.bind(this));
    },
    getPlugin: function (name) {
      return this.plugins.get(name);
    },
    activate: function (name) {
      this.deactivate();
      this.getMenu().setStyle();
      this.plugins.get(name).activate();
    },
    deactivate: function () {
      this.plugins.each(function (plugin) {
        plugin.deactivate();
      });
      this.getTray().empty();
      this.getTopTray().empty();
    },
    signalPluginReady: function (state) {
      this.pluginReady = state;
      this.fireEvent('changePluginReady', this);
    },
    hasSignalPluginReady: function () {
      return this.pluginReady;
    },
    hasActivePlugin: function () {
      var active = false;
      this.plugins.each(function (plugin) {
        active = active || plugin.active;
      });
      return active;
    },
    // Key events
    editorMouseUp: function (e) {
      this.fireEvent('editorMouseUp', e);
    },
    editorMouseDown: function (e) {
      this.fireEvent('editorMouseDown', e);
    },
    editorContextMenu: function (e) {
      this.fireEvent('editorContextMenu', e);
    },
    editorClick: function (e) {
      // make images selectable and draggable in Safari
      if (Browser.Engine.webkit) {
        var el = e.target;
        if (el.get('tag') == 'img') {
          this.selection.selectNode(el);
        }
      }

      this.fireEvent('editorClick', e);
    },
    editorDoubleClick: function (e) {
      this.fireEvent('editorDoubleClick', e);
    },
    editorKeyPress: function (e) {
      this.keyListener(e);
      this.fireEvent('editorKeyPress', e);
    },
    editorKeyUp: function (e) {
      this.fireEvent('editorKeyUp', e);
    },
    editorKeyDown: function (e) {

      this.fireEvent('editorKeyDown', e);
    },
    editorPaste: function (e) {
      getLinkContent();
      this.fireEvent('editorPaste', e);
    },
    keyListener: function (e) {

    },
    _lang: function () {
      try {
        if (arguments.length < 1) {
          return '';
        }

        var string = arguments[0];
        if ($type(this.options.lang) && $type(this.options.lang[string])) {
          string = this.options.lang[string];
        }

        if (arguments.length <= 1) {
          return string;
        }

        var args = new Array();
        for (var i = 1, l = arguments.length; i < l; i++) {
          args.push(arguments[i]);
        }

        return string.vsprintf(args);
      } catch (e) {
        alert(e);
      }
    },
    _supportsContentEditable: function () {
      return false;
    }
  });

  Composer.Autogrow = new Class({
    Implements: [Events, Options],
    resizing: false,
    element: null,
    highlighter: null,
    process: false,
    initialize: function (element, highlighter) {
      this.element = element;
      this.highlighter = highlighter;
      this.setStyles();
      this.attach();
      this.handle();
    },
    setStyles: function () {
      this.element.setStyles({
        'overflow-x': 'auto',
        'overflow-y': 'hidden',
        '-mox-box-sizing': 'border-box',
        '-ms-box-sizing': 'border-box',
        'resize': 'none'
      });
    },
    handle: function () {
      if (this.process) {
        return;
      }
      this.process = true;
      this.resetHeight();
      if (Browser.Engine.webkit || Browser.Engine.gecko) {
        this.shrink();
      }
      this.process = false;
    },
    resetHeight: function () {
      if (this.element.getScrollSize().y) {
        var newHeight = this.getHeight();
        if (newHeight !== this.element.getSize().y) {
          var height = newHeight + 'px';
          this.element.setStyles({
            maxHeight: height,
            height: height
          });
          this.setHighlighterHeight(height);
        }
      } else {
        this.element.setStyles({
          maxHeight: '',
          height: 'auto'
        });
        this.element.rows = (this.element.value.match(/(\r\n?|\n)/g) || []).length + 1;
      }
    },
    setHighlighterHeight: function (height) {
      if (this.highlighter) {
        this.highlighter.setStyles({
          maxHeight: height,
          height: height
        });
      }
    },
    shrink: function () {
      var useNullHeightShrink = true;
      if (useNullHeightShrink) {
        this.element.style.height = '0px';
        this.resetHeight();
      } else {
        var scrollHeight = this.element.getScrollSize().y;
        var paddingBottom = this.element.getStyle('padding-bottom').toInt();
        this.element.style.paddingBottom = paddingBottom + 1 + "px";
        var newHeight = this.getHeight() - 1;
        if (this.element.getStyle('max-height').toInt() != newHeight) {
          this.element.style.paddingBottom = paddingBottom + scrollHeight + "px";
          this.element.scrollTop = 0;
          var h = _getHeight() - scrollHeight + "px";
          this.element.style.maxHeight = h;
        }
        this.element.style.paddingBottom = paddingBottom + 'px';
      }
    },
    attach: function () {
      ['keyup', 'focus', 'paste', 'cut'].each(function (eventName) {
        this.element.addEvent(eventName, this.handle.bind(this));
      }.bind(this));
      if (Browser.Engine.webkit || Browser.Engine.trident) {
        this.element.addEvent('scroll', this.handle.bind(this));
      }
    },
    getHeight: function () {
      var height = this.element.getScrollSize().y;
      if (Browser.Engine.gecko || Browser.Engine.trident) {
        height += this.element.offsetHeight - this.element.clientHeight;
      } else if (Browser.Engine.webkit) {
        height += this.element.getStyle('border-top-width').toInt() + this.element.getStyle('border-bottom-width').toInt();
      } else if (Browser.Engine.presto) {
        height += this.element.getStyle('padding-bottom').toInt();
      }
      return height;
    }
  });


  Composer.Plugin = {};
  Composer.Plugin.Interface = new Class({
    Implements: [Options, Events],
    name: 'interface',
    active: false,
    composer: false,
    options: {
      loadingImage: en4.core.staticBaseUrl + 'application/modules/Seaocore/externals/images/core/loading.gif'
    },
    elements: {},
    persistentElements: ['activator', 'loadingImage'],
    params: {},
    initialize: function (options) {
      this.params = new Hash();
      this.elements = new Hash();
      this.reset();
      this.setOptions(options);
    },
    getName: function () {
      return this.name;
    },
    setComposer: function (composer) {
      this.composer = composer;
      this.attach();
      return this;
    },
    getComposer: function () {
      if (!this.composer)
        throw "No composer defined";
      return this.composer;
    },
    attach: function () {
      this.reset();
    },
    detach: function () {
      this.reset();
      if (this.elements.activator) {
        this.elements.activator.destroy();
        this.elements.erase('menu');
      }
    },
    reset: function () {
      this.elements.each(function (element, key) {
        if ($type(element) == 'element' && !this.persistentElements.contains(key)) {
          element.destroy();
          this.elements.erase(key);
        }
      }.bind(this));
      this.params = new Hash();
      this.elements = new Hash();
    },
    activate: function () {
      if (this.active)
        return;
      this.getComposer().deactivate();
      this.active = true;
      this.reset();
      this.getComposer().getTray().setStyle('display', '');
      this.getComposer().getTopTray().setStyle('display', '');
      this.getComposer().getMenu().setStyle('display', 'none');
      var submitButtonEl = $(this.getComposer().options.submitElement);
      if (submitButtonEl) {
        submitButtonEl.setStyle('display', 'none');
      }

      this.getComposer().getMenu().setStyle('border', 'none');
      this.getComposer().getForm().addClass('adv-active');
      this.getComposer().getActivatorContent().addClass('adv_post_compose_menu_anactive');
      if (this.getName() == "questionpoll" || this.getName() == "question") {
        this.getComposer().getTray().inject(this.getComposer().getForm(), "after");
      }
      switch ($type(this.options.loadingImage)) {
        case 'element':
          break;
        case 'string':
          this.elements.loadingImage = new Asset.image(this.options.loadingImage, {
            'id': 'compose-' + this.getName() + '-loading-image',
            'class': 'compose-loading-image'
          });
          break;
        default:
          this.elements.loadingImage = new Asset.image('loading.gif', {
            'id': 'compose-' + this.getName() + '-loading-image',
            'class': 'compose-loading-image'
          });
          break;
      }
      if (this.getName().indexOf('photo') >= 0 || this.getName().indexOf('music') >= 0) {
        var self = this;
        (function () {
          if (self.elements.formFancyUpload) {
            self.elements.body.setStyle('position', 'relative');
            self.elements.formFancyUpload.addEvent('reposition', function (coords, box, target) {
              box.setStyles(target.getCoordinates(self.elements.body));
            }.bind(self.elements.formFancyUpload));
          }
        }).delay(100);
      }
    },
    deactivate: function () {
      if (!this.active)
        return;
      this.active = false;
      this.reset();
      this.getComposer().getTray().setStyle('display', 'none');
      this.getComposer().getTopTray().setStyle('display', 'none');
      this.getComposer().getMenu().setStyle('display', '');
      var submitButtonEl = $(this.getComposer().options.submitElement);
      if (submitButtonEl) {
        submitButtonEl.setStyle('display', '');
      }
      this.getComposer().getActivatorContent().removeClass('adv_post_compose_menu_anactive');
      if (this.getName() == "questionpoll" || this.getName() == "question") {
        this.getComposer().getTray().inject(this.getComposer().getMenu(), "before");
      }

      this.getComposer().getMenu().set('style', '');
      this.getComposer().signalPluginReady(false);
    },
    ready: function () {
      this.getComposer().signalPluginReady(true);
      this.getComposer().getMenu().setStyle('display', '');
      var submitEl = $(this.getComposer().options.submitElement);
      if (submitEl) {
        submitEl.setStyle('display', '');
      }
    },
    // Utility

    makeActivator: function () {
      if (!this.elements.activator) {
        this.elements.activator = new Element('span', {
          'id': 'compose-' + this.getName() + '-activator',
          'class': 'compose-activator',
          'href': 'javascript:void(0);',
          'html': '<span>' + this._lang(this.options.title) + '</span>',
          'events': {
            'click': this.activate.bind(this)
          }
        }).inject(this.getComposer().getActivatorContent().getElement(".aaf_activaor_end"), "before");
        create_tooltip(this).inject(this.elements.activator);
        this.setActivatorPositions();
        this.setTextareaListener();
      }
    },
    setActivatorPositions: function () {
      var maxAllowActivator = this.getComposer().options.maxAllowActivator;
      var activatorContent = this.getComposer().getActivatorContent();
      var activators = this.getComposer().getActivatorContent().getElements('.compose-activator');
      if (activators.length <= maxAllowActivator) {
        activatorContent.getElement('.aaf_activaor_more').addClass('dnone');
        return;
      }
      activatorContent.getElement('.aaf_activaor_more').removeClass('dnone');
      activators.each(function (activator, key) {
        activator.removeClass('compose-activator-collapse')
        if (key >= maxAllowActivator) {
          activator.addClass('compose-activator-collapse');
        }
      });
      if (!activatorContent.getElement(".more-menu-compose-activator-list")) {
        return;
      }
      activatorContent.addClass('dnone');
      activators.each(function (activator, key) {
        if (key < maxAllowActivator) {
          activator.inject(activatorContent.getElement(".aaf_activaor_end"), "before");
        } else {
          if (!activator.getParent('.more-menu-compose-activator-list'))
            activator.inject(activatorContent.getElement(".more-menu-compose-activator-list"));
        }
      }.bind(this));
      activatorContent.removeClass('dnone');

    },
    makeTopMenu: function () {
      if (!this.elements.menu) {
        this.makeMenu();
        var tray = this.getComposer().getTopTray();
        this.elements.menu.inject(tray);
      }
    },
    makeMenu: function () {
      if (!this.elements.menu) {
        var tray = this.getComposer().getTray();
        this.elements.menu = new Element('div', {
          'id': 'compose-' + this.getName() + '-menu',
          'class': 'compose-menu'
        }).inject(tray);
        this.elements.menuTitle = new Element('span', {
          'html': this._lang(this.options.title)
        }).inject(this.elements.menu);
        this.elements.menuClose = new Element('a', {
          'href': 'javascript:void(0);',
          'html': this._lang('cancel'),
          'class': 'aaf-composer-cancel-menu',
          'events': {
            'click': function (e) {
              e.stop();
              this.getComposer().deactivate();
            }.bind(this)
          }
        }).inject(this.elements.menuTitle);
//        this.elements.menuTitle.appendText(')');
        if (showVariousTabs == 0 && this.getName() == 'photo' && typeof sitealbumInstalled != 'undefined') {

          this.elements.albumMenu = new Element('div', {
            'id': 'compose-album-' + this.getName() + '-menu',
            'class': 'compose-menu aaf-compose-menu_align'
          }).inject(this.elements.menu);
          this.elements.albumMenuseperator = new Element('span', {
            'class': 'aaf_media_sep',
          }).inject(this.elements.albumMenu);
          this.elements.albumMenuseperator.innerHTML = en4.core.language.translate("OR");
          if (showAddPhotoInLightbox) {
            this.elements.albumMenuTitle = new Element('a', {
              'href': 'javascript:void(0)',
              'class': 'seao_smoothbox item_icon_photo',
              'html': this._lang(this.options.albumTitle),
              'events': {
                'click': function (e) {
                  e.stop();
                  SmoothboxSEAO.open({
                    class: 'seao_add_photo_lightbox',
                    request: {
                      url: en4.core.baseUrl + 'albums/upload/'
                    }
                  });
                }.bind(this)
              }
            }).inject(this.elements.albumMenu);
          } else {
            this.elements.albumMenuTitle = new Element('a', {
              'href': 'javascript:void(0)',
              'class': 'item_icon_photo',
              'html': this._lang(this.options.albumTitle),
              'events': {
                'click': function (e) {
                  e.stop();
                  window.location.href = en4.core.baseUrl + 'albums/upload/';
                }.bind(this)
              }
            }).inject(this.elements.albumMenu);
          }
        }
      }
    },
    makeTopBody: function () {
      if (!this.elements.body) {
        this.makeBody();
        var tray = this.getComposer().getTopTray();
        this.elements.body.inject(tray);
      }
    },
    makeBody: function () {
      if (!this.elements.body) {
        var tray = this.getComposer().getTray();
        this.elements.body = new Element('div', {
          'id': 'compose-' + this.getName() + '-body',
          'class': 'compose-body'
        }).inject(tray);
      }
    },
    makeLoading: function (action) {
      if (!this.elements.loading) {
        if (action == 'empty') {
          this.elements.body.empty();
        } else if (action == 'hide') {
          this.elements.body.getChildren().each(function (element) {
            element.setStyle('display', 'none')
          });
        } else if (action == 'invisible') {
          this.elements.body.getChildren().each(function (element) {
            element.setStyle('height', '0px').setStyle('visibility', 'hidden')
          });
        }

        this.elements.loading = new Element('div', {
          'id': 'compose-' + this.getName() + '-loading',
          'class': 'compose-loading'
        }).inject(this.elements.body);
        var image = this.elements.loadingImage || (new Element('img', {
          'id': 'compose-' + this.getName() + '-loading-image',
          'class': 'compose-loading-image'
        }));
        image.inject(this.elements.loading);
        new Element('span', {
          'html': this._lang('Loading...')
        }).inject(this.elements.loading);
      }
    },
    makeError: function (message, action) {
      if (!$type(action))
        action = 'empty';
      message = message || 'An error has occurred';
      message = this._lang(message);
      this.elements.error = new Element('div', {
        'id': 'compose-' + this.getName() + '-error',
        'class': 'compose-error',
        'html': message
      }).inject(this.elements.body);
    },
    makeFormInputs: function (data) {
      this.ready();
      this.getComposer().getInputArea().empty();
      data.type = this.getName();
      $H(data).each(function (value, key) {
        this.setFormInputValue(key, value);
      }.bind(this));
    },
    setFormInputValue: function (key, value) {
      var elName = 'attachmentForm' + key.capitalize();
      if (!this.elements.has(elName)) {
        this.elements.set(elName, new Element('input', {
          'type': 'hidden',
          'name': 'attachment[' + key + ']',
          'value': value || ''
        }).inject(this.getComposer().getInputArea()));
      }
      this.elements.get(elName).value = value;
    },
    _lang: function () {
      try {
        if (arguments.length < 1) {
          return '';
        }

        var string = arguments[0];
        if ($type(this.options.lang) && $type(this.options.lang[string])) {
          string = this.options.lang[string];
        }

        if (arguments.length <= 1) {
          return string;
        }

        var args = new Array();
        for (var i = 1, l = arguments.length; i < l; i++) {
          args.push(arguments[i]);
        }

        return string.vsprintf(args);
      } catch (e) {
        alert(e);
      }
    },
    setMdashMdot: function (name) {
      if (!$('composer_preview_display_tray').getFirst('span').hasClass('mdash')) {
        this.elements.spanMdash = new Element('span', {
          'class': 'mdash',
          'html': '&mdash;',
        }).inject($('composer_preview_display_tray'), 'top');
      }
      if (!$('composer_preview_display_tray').getLast('span').hasClass('dot')) {
        this.elements.spanDot = new Element('span', {
          'class': 'dot',
          'html': '.',
        }).inject($('composer_preview_display_tray'), 'bottom');
      }
      $('composer_preview_display_tray').removeClass('dnone');
    },
    resetMdashMdot: function (name) {
      if ($('compose-feeling-composer-display') && $('compose-feeling-composer-display').innerHTML != "") {
        return;
      }
      if ($('compose-checkin-composer-display') && $('compose-checkin-composer-display').innerHTML != "") {
        return;
      }
      if ($('friendas_tag_body_aaf_content') && $('friendas_tag_body_aaf_content').innerHTML != "") {
        return;
      }
      if(!$('composer_preview_display_tray')){
         return;
      }
      if ($('composer_preview_display_tray').getFirst('span') && $('composer_preview_display_tray').getFirst('span').hasClass('mdash')) {
          $('composer_preview_display_tray').getFirst('span').destroy();
      }
      if ($('composer_preview_display_tray').getLast('span') && $('composer_preview_display_tray').getLast('span').hasClass('dot')) {
          $('composer_preview_display_tray').getLast('span').destroy();
      }
      $('composer_preview_display_tray').addClass('dnone');
    },
    setTextareaListener: function () {
      $('advanced_activity_body').addEventListener('input', function () {
        if (this.value.length > 0 && this.value.length < composeInstance.options.textLimit && !$('activity-form').hasClass('compose-container-banner-image-added')) {
          $('activity-form').addClass('compose-container-text-decoration');
        } else {
          $('activity-form').removeClass('compose-container-text-decoration');
        }
      });
    },

  });
  //http://mootools-users.660466.n2.nabble.com/Moo-onPaste-td4655487.html
  $extend(Element.NativeEvents, {
    'paste': 2,
    'input': 2
  });
  Element.Events.paste = {
    base: (Browser.Engine.presto || (Browser.Engine.gecko && Browser.Engine.version < 19)) ? 'input' : 'paste',
    condition: function (e) {
      this.fireEvent('paste', e, 1);
      return false;
    }
  };
})(); // END NAMESPACE

var create_tooltip = function (plugin_temp) {
  return new Element('p', {
    'class': 'adv_post_compose_menu_show_tip adv_composer_tip',
    'html': plugin_temp.options.title + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png">'
  });
};