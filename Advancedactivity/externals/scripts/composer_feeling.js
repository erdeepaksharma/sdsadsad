
/* $Id: composer_photo.js 9572 2011-12-27 23:41:06Z john $ */



(function () { // START NAMESPACE
  var $ = 'id' in document ? document.id : window.$;
  Composer.Plugin.AafFeeling = new Class({

    Extends: Composer.Plugin.Interface,

    name: 'feeling',

    options: {
      title: 'Feeling/Activity',
      lang: {}
    },
    preAutoSuggest: [],
    call_empty_suggest: false,
    add_location: false,
    navigator_location_shared: false,
    listAutoSuggest: false,
    selectedListData: null,
    selectedFeelingData: null,
    initialize: function (options) {
      this.elements = new Hash(this.elements);
      this.params = new Hash(this.params);
      this.parent(options);

    },
    makeActivator: function () {
      if (!this.elements.activator) {
        this.elements.activator = new Element('span', {
          'id': 'compose-' + this.getName() + '-activator',
          'class': 'adv-post-feeling compose-activator',
          'href': 'javascript:void(0);',
          'html': '<span>' + this._lang(this.options.title) + '</span>',
          'events': {
            'click': this.feelingToogle.bind(this)
          }
        }).inject(this.getComposer().getActivatorContent().getElement(".aaf_activaor_end"), "before");
        create_tooltip(this).inject(this.elements.activator);
        this.setActivatorPositions();
      }
    },
    attach: function () {
      if (!this.elements.feeling) {

        var composer = this.getComposer();
        this.makeActivator();
        var composer_tray = composer.getTray();

        this.elements.container = new Element('div', {
          'id': 'compose-' + this.getName() + '-container-feeling',
          'class': 'adv_post_container_feeling dnone',
          'title': this._lang('What are you doing?'),
          'style': {

          }
        });
        this.elements.contentdisplay = new Element('div', {
          'id': 'compose-' + this.getName() + '-container-display',
          'class': 'dnone',
          'title': this._lang('What are you doing?')
        }).inject(this.elements.container);

        this.elements.feelingListDisplay = new Element('div', {
          'class': 'composer-feeling-list-display',
        }).inject(this.elements.contentdisplay);
        this.elements.feelingContainer = new Element('div', {
          'class': 'composer-feeling-container',
        }).inject(this.elements.contentdisplay);
        this.elements.input = new Element('input', {
          'type': 'text',
          'id': 'compose-' + this.getName(),
          'name': 'compose-' + this.getName(),
          'placeholder': this._lang('What are you doing?'),
          'class': 'compose-textarea'
        }).inject(this.elements.feelingContainer);
        this.elements.feelingClose = new Element('a', {
          'html': '',
          'class': 'compose-feeling-remove'
        }).inject(this.elements.feelingContainer);
        this.elements.input.addEvent('input', this.removeFeelingChild.bind(this));
        this.elements.feelingClose.addEvent('click', this.resetData.bind(this));
        this.elements.feelingListContainer = new Element('div', {
          'class': 'composer-feeling-list-container',
        }).inject(this.elements.container);
        this.elements.listInput = new Element('input', {
          'type': 'text',
          'id': 'compose-list-' + this.getName(),
          'name': 'compose-list-' + this.getName(),
          'placeholder': this._lang('What are you doing?'),
          'class': 'compose-textarea'
        }).inject(this.elements.feelingListContainer);


        this.elements.container.inject(composer_tray, "before");
        var self = this;
        this.elements.comnposerTrydisplay = new Element('span', {
          'id': 'compose-' + this.getName() + '-composer-display',
          'class': 'compose-feeling-display',
        }).inject($('composer_preview_display_tray'), 'top');
        this.elements.comnposerTrydisplay.addEvent('click', this.showContentdisplay.bind(this));
        // Submit
        composer.addEvent('editorSubmit', this.submit.bind(this));
        this.setPreChoices();
        // After Submit
        composer.addEvent('editorSubmitAfter', this.submitAfter.bind(this));
        composer.addEvent('editorReset', this.resetcontent.bind(this));
      }

      return this;
    },
    setPreChoices: function () {
      var self = this;
      var request = new Request.JSON({
        'url': en4.core.baseUrl + 'advancedactivity/feeling/getpre-choices',
        'method': 'post',
        'data': {
          'format': 'json',
        },
        'onSuccess': function (responseJSON, responseHTML) {
          self.preAutoSuggest = responseJSON;
          self.setListAutoSuggest(responseJSON.parent);
        }
      });
      request.send();
    },
    reset: function () {
    },
    detach: function () {
      return this;
    },
    activate: function () {
    },
    deactivate: function () {

    },
    resetcontent: function () {
      this.resetData();
      this.elements.container.addClass('dnone');
    },
    feelingToogle: function () {
      if (!this.elements.container.hasClass("dnone") && !this.selectedListData) {
        this.resetcontent();
      } else {
        this.elements.container.removeClass('dnone');
        this.elements.listInput.focus();
      }

    },
    getEmptySuggest: function () {
      if (this.call_empty_suggest)
        return;
      this.elements.input.focus();
      if (this.suggest && this.suggest.element.value == '') {
        this.suggest.queryValue = ' ';
        this.suggest.prefetch();
      }

      this.call_empty_suggest = true;
    },
    setListAutoSuggest: function (lists) {
      this.listSuggestContener = new Element('div', {
        'class': 'feeling-autosuggest-container',
        'id': 'feeling-list-autosuggest-container',
      });

      this.listChoicesSliderArea = new Element('div', {
        'class': 'feeling-autosuggest-wapper'
      });

      this.listChoices = new Element('ul', {
        'class': 'feeling-autosuggest',
      }).inject(this.listChoicesSliderArea);

      this.listChoicesSliderArea.inject(this.listSuggestContener);
      new Element('div', {
        'class': 'clr'
      }).inject(this.listSuggestContener);
      var self = this;

      this.listSuggestContener.inject(this.elements.listInput, 'after');
      this.listScroller = new SEAOMooVerticalScroll(this.listChoicesSliderArea, this.listChoices, {});
      var options = $merge(this.options.suggestOptions, {
        'cache': false,
        'selectMode': 'pick',
        'postVar': 'value',
        'minLength': 0,
        'maxLength': 40,
        delay: 1,
        'className': 'feeling-list-autosuggest',
        'filterSubset': true,
        'tokenValueKey': 'title',
        'tokenFormat': 'object',
        'prefetchOnInit': true,
        'customChoices': this.listChoices,
        'maxChoices': 50,
        'injectChoice': function (token) {
          var choice = new Element('li', {
            'class': 'autocompleter-choices',
            'value': this.markQueryValue(token.title),
            'id': token.id
          });
          var divEl = new Element('a', {
            'class': 'autocompleter-choice',
            'html': token.photo || '',
            'href': 'javascript:void(0);',
          }).inject(choice);
          var spenEl = new Element('span', {
            'html': this.markQueryValue(token.title),
            'class': 'autocompleter-choice-label'
          }).inject(divEl);
          this.addChoiceEvents(choice).inject(this.choices);
          choice.store('autocompleteChoice', token);
          self.listScroller.update();
          (function () {
            self.listScroller.update();
          }).delay(500);
        },
        'onFocus': function () {
          this.prefetch();
        },
        'onShow': function () {
          self.listSuggestContener.removeClass('dnone');
          (function () {
            self.listScroller.update();
          }).delay(500);
        },
        'onHide': function () {
          (function () {
            self.listSuggestContener.addClass('dnone');
          }).delay(500);
        },
        'onChoiceSelect': function (choice) {

          var token = choice.retrieve('autocompleteChoice');
          self.setFeelingList(token);
        },
      });
      this.listAutoSuggest = new Autocompleter.Local(this.elements.listInput, lists, options);
//      this.listAutoSuggest.element
//			.addEvent('focus', this.listAutoSuggest.toggleFocus.create({bind: this, arguments: true, delay: 1}));
    },
    getSuggest: function () {

      if (!this.suggest) {
        this.suggestContener = new Element('div', {
          'class': 'feeling-autosuggest-container dnone',
          'id': 'feeling-autosuggest-container',
        });

        this.choicesSliderArea = new Element('div', {
          'class': 'feeling-autosuggest-wapper'
        });

        this.choices = new Element('ul', {
          'class': 'feeling-autosuggest',
        }).inject(this.choicesSliderArea);

        this.choicesSliderArea.inject(this.suggestContener);
        new Element('div', {
          'class': 'clr'
        }).inject(this.suggestContener);


        this.suggestContener.inject(this.elements.input, 'after');
        if (!this.scroller) {
          this.scroller = new SEAOMooVerticalScroll(this.choicesSliderArea, this.choices, {});
        }
        var self = this;
        var options = $merge(this.options.suggestOptions, {
          'cache': false,
          'selectMode': 'pick',
          'postVar': 'value',
          'minLength': 0,
          'maxLength': 40,
          'className': 'feeling-list-autosuggest',
          'filterSubset': true,
          'tokenValueKey': 'title',
          'tokenFormat': 'object',
          'prefetchOnInit': true,
          delay: 1,
          'customChoices': this.choices,
          'maxChoices': 50,
          'injectChoice': function (token) {

            var choice = new Element('li', {
              'class': 'autocompleter-choices',
              'value': this.markQueryValue(token.title),
              'id': token.id
            });
            var divEl = new Element('a', {
              'class': 'autocompleter-choice',
              'html': token.photo || '',
              'href': 'javascript:void(0);',
            }).inject(choice);
            var spenEl = new Element('span', {
              'html': this.markQueryValue(token.title),
              'class': 'autocompleter-choice-label'
            }).inject(divEl);
            divEl.inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
            self.scroller.update();
          },
          'onHide': function () {
            (function () {
              self.suggestContener.addClass('dnone');
            }).delay(500);
          },
          'onShow': function () {
            self.suggestContener.removeClass('dnone');
            (function () {
              self.scroller.update();
            }).delay(500);
          },
          filter: function (tokens, queryValue) {
            queryValue = queryValue || this.queryValue;
            tokens = tokens || this.tokens;
            tokens = tokens.filter(function (token) {
              return token.id != 'custom_feeling';
            });
            if (queryValue) {
              var listData = self.selectedListData;
              var key = this.options.tokenValueKey;
              var testTokens = tokens.filter(function (token) {
                return token[key] && token[key].toUpperCase() === queryValue.toUpperCase();
              });
              if (testTokens.length === 0) {
                tokens.push({
                  id: 'custom_feeling',
                  title: queryValue,
                  url: listData.url,
                  type: '',
                  photo: listData.photo
                });
              }
            }
            var regex = new RegExp(((this.options.filterSubset) ? '' : '^') + queryValue.escapeRegExp(), (this.options.filterCase) ? '' : 'i');
            if (this.options.tokenFormat == 'object') {
              var key = this.options.tokenValueKey;
              return tokens.filter(function (token) {
                return regex.test(token[key]);
              });
            } else {
              return tokens.filter(function (token) {
                return regex.test(token);
              });
            }
            //this.currentFilterDelta = delta;
            return tokens;
          },
          'onChoiceSelect': function (choice) {
            var token = choice.retrieve('autocompleteChoice');
            self.setFeeling(token);
          },
        });
        this.suggest = new Autocompleter.Local(this.elements.input, [], options);
        this.suggest.addEvent('onCommand', function (e) {
          if (e && e.key == 'backspace' && !e.shift && self.suggest.element.value == '') {
            self.removeFeeling();
          }
        }.bind(self));
      }

      return this.suggest;
    },
    setFeelingList: function (data) {
      this.selectedListData = data;
      if (!data.tagline) {
        this.elements.input.set('placeholder', this._lang('What are you ' + data.title + '?'));
      } else {
        this.elements.input.set('placeholder', data.tagline);
      }
      this.elements.input.value = '';
      var self = this;
      this.elements.contentdisplay.removeClass('dnone');
      this.elements.feelingListContainer.addClass('dnone');

      this.elements.listFeelingspan = new Element('span', {
        'class': 'tag',
        'html': data.title,
        'id': 'parent_feeling_id',
        'feeling_id': data.id,
        'events': {
          'click': this.removeFeeling.bind(this)
        }
      }).inject(this.elements.feelingListDisplay);
      this.getSuggest();
      var tokens = this.preAutoSuggest.child[data.id];
      this.suggest.tokens = tokens;
      this.suggest.update(tokens);
      this.elements.input.focus();
    },
    showContentdisplay: function () {
      this.elements.contentdisplay.removeClass('dnone');
      this.elements.input.focus();
      this.suggest.prefetch();
    },
    setFeeling: function (data) {
      var self = this;
      this.selectedFeelingData = data;
      this.elements.input.value = data.title;
      this.elements.feelingContainer.addClass('feeling_added');
      this.elements.contentdisplay.addClass('dnone');

      var content = ' ' + this.selectedListData.title + '  ';
      this.elements.comnposerTrydisplay.innerHTML = content;
      var content = '<a id="child_feeling" href = "javascript:void(0)">' + data.title + '</a>' + '  ';
      this.elements.comnposerTrydisplay.innerHTML = ' <img id="child_feeling_image" src="' + data.url + '" height="20px" width="20px" />' + this.elements.comnposerTrydisplay.innerHTML + ' ' + content;
      this.elements.comnposerTrydisplay.style.display = 'block';

      this.setMdashMdot();
      this.getComposer().focus();
      this.getComposer().pluginReady = true;
      var submitEl = $(this.getComposer().options.submitElement);
      if (submitEl) {
        submitEl.setStyle('display', '');
      }
      $('compose-feeling-activator').addClass('active');
    },
    removeFeeling: function () {
      if (!this.selectedListData) {
        return;
      }
      this.elements.feelingListContainer.removeClass('dnone');
      this.elements.contentdisplay.addClass('dnone');
      this.elements.feelingListDisplay.empty();
      this.elements.input.value = '';
      this.selectedListData = null;
      this.selectedFeelingData = null;
      this.elements.comnposerTrydisplay.empty();
      this.elements.comnposerTrydisplay.style.display = 'none';
      this.elements.listInput.focus();
      this.elements.input.set('placeholder', this._lang('What are you doing?'));
      this.resetMdashMdot();

    },
    removeFeelingChild: function () {
      if (!this.selectedFeelingData) {
        return;
      }
      this.elements.feelingContainer.removeClass('feeling_added');
      if (!this.selectedListData.tagline) {
        this.elements.input.set('placeholder', this._lang('What are you ' + this.selectedListData.title + '?'));
      } else {
        this.elements.input.set('placeholder', this.selectedListData.tagline);
      }
      this.selectedFeelingData = null;
      this.elements.comnposerTrydisplay.empty();
      this.elements.comnposerTrydisplay.style.display = 'none';
      this.elements.input.focus();
      this.resetMdashMdot();
      this.getComposer().signalPluginReady(false);
      $('compose-feeling-activator').removeClass('active');
    },
    resetData: function () {
      this.removeFeelingChild();
      this.removeFeeling();
      this.elements.comnposerTrydisplay.empty();
      this.elements.comnposerTrydisplay.style.display = 'none';
      this.resetMdashMdot();
    },
    submit: function () {
      if (this.selectedListData && this.selectedFeelingData) {
        this.makeFormInputs({
          parent: this.selectedListData.id
        });
        this.makeFormInputs({
          child: this.selectedFeelingData.id
        });
        this.makeFormInputs({
          type: this.selectedFeelingData.type
        });
        this.makeFormInputs({
          childtitle: this.selectedFeelingData.title
        });
      }
    },
    submitAfter: function () {
      if (this.elements.container && this.elements.container.hasClass("dblock")) {
        this.elements.container.removeClass("dblock").addClass("dnone")
      }
      if ($('composer-input-feeling-childtitle')) {
        $('composer-input-feeling-childtitle').destroy();
      }
      if ($('composer-input-feeling-child')) {
        $('composer-input-feeling-child').destroy();
      }
      if ($('composer-input-feeling-parent')) {
        $('composer-input-feeling-parent').destroy();
      }
      if ($('composer-input-feeling-type')) {
        $('composer-input-feeling-type').destroy();
      }
    },
    makeFormInputs: function (data) {
      $H(data).each(function (value, key) {
        this.setFormInputValue(key, value);
      }.bind(this));
    },

    // make feeling hidden input and set value into composer form
    setFormInputValue: function (key, value) {
      var elName = 'aafComposerForm' + key.capitalize();
      var composerObj = this.getComposer();
      if (composerObj.elements.has(elName))
        composerObj.elements.get(elName).destroy();
      composerObj.elements.set(elName, new Element('input', {
        'type': 'hidden',
        'name': 'composer[feeling][' + key + ']',
        'id': 'composer-input-feeling-' + key,
        'value': value || ''
      }).inject(composerObj.getInputArea()));
      composerObj.elements.get(elName).value = value;
    },

  });
})(); // END NAMESPACE
