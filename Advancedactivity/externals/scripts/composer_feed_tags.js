
/* $Id: composer_photo.js 9572 2011-12-27 23:41:06Z john $ */



(function () { // START NAMESPACE
  var $ = 'id' in document ? document.id : window.$;

  Composer.Plugin.AafFeedTags = new Class({

    Extends: Composer.Plugin.Interface,

    name: 'feed-tag',
    suggest: null,
    addUserArray: new Array(),
    options: {
      title: 'Tag Friends',
      lang: {}
    },
    initialize: function (options) {
      this.elements = new Hash(this.elements);
      this.params = new Hash(this.params);
      this.parent(options);

    },
    makeActivator: function () {
      if (!this.elements.activator) {
        var self = this;
        this.elements.activator = new Element('span', {
          'id': 'compose-' + this.getName() + '-activator',
          'class': 'adv_post_add_user compose-activator',
          'href': 'javascript:void(0);',
          'html': '<span>' + this._lang(this.options.title) + '</span>',
          'events': {
            'click': function () {
              self.activate();
              setTimeout(function () {
                self.toggleTagWith()
              }, 100);
            }.bind(this)
          }
        }).inject(this.getComposer().getActivatorContent().getElement(".aaf_activaor_end"), "before");
        create_tooltip(this).inject(this.elements.activator);
        this.setActivatorPositions();
      }
    },
    attach: function () {
      this.makeActivator();
      this.getComposer().elements.body.addEvent('focus', function () {
        if ($('adv_post_container_tagging')) {
          $('adv_post_container_tagging').setStyle('display', 'none');
        }
      }.bind(this));
      return this;
    },
    activate: function () {
      if (this.elements.taggingCountContainer)
        return;
      this.elements.taggingContainer = new Element('div', {
        'id': 'adv_post_container_tagging',
        'class': 'adv_post_container_tagging',
        'styles': {
          'display': 'none'
        },
        'title': 'Who are you with?'
      }).inject($('compose-tray'), 'after');
      this.elements.taggedWrapper = new Element('div', {
        'id': 'tagged-wrapper',
        'class': 'form-wrapper',
      }).inject(this.elements.taggingContainer);
      this.elements.taggedLabel = new Element('div', {
        'id': 'tagged-label',
        'class': 'form-label',
        'html': en4.core.language.translate('With')
      }).inject(this.elements.taggedWrapper);
      this.elements.taggedElement = new Element('div', {
        'id': 'tagged-element',
        'class': 'form-element',
        'styles': {
          'height': '0px'
        }
      }).inject(this.elements.taggedWrapper);

      this.elements.inputHidden = new Element('input', {
        'id': 'tagged_freinds_ids',
        'name': 'tagged_freinds_ids',
        'type': 'hidden',
        'value': ''
      }).inject(this.elements.taggingContainer);
      this.elements.taggedTextWrapper = new Element('div', {
        'id': 'tagged-text-wapeer',
        'class': 'tagged-text-wapeer',
        'styles': {
          'height': '0px'
        }
      }).inject(this.elements.taggingContainer);
      this.elements.input = new Element('input', {
        'id': 'friendas_tag_body_aaf',
        'name': 'friendas_tag_body_aaf',
        'placeholder': 'Who are you with?',
        'type': 'text',
        'autocomplete': 'off',
        'class': 'compose-textarea'
      }).inject(this.elements.taggedTextWrapper);
      if ($('compose-checkin-composer-display')) {
        this.elements.taggingCountContainer = new Element('div', {
          'class': 'adv_post_container_tagged_cont'
        }).inject($('compose-checkin-composer-display'), 'before');
      } else {
        this.elements.taggingCountContainer = new Element('div', {
          'class': 'adv_post_container_tagged_cont'
        }).inject($('composer_preview_display_tray'));
      }

      this.elements.taggingMDash = new Element('span', {
        'class': 'friendas_tag_body_aaf_content',
        'id': 'friendas_tag_body_aaf_content'
      }).inject(this.elements.taggingCountContainer);
      this.getSuggest();
    },
    deactivate: function () {
      if (this.elements.taggingCountContainer)
        this.elements.taggingCountContainer.destroy();
      this.elements.taggingCountContainer = null;
      this.resetMdashMdot();
      if ($('adv_post_container_tagging')) {
          $('adv_post_container_tagging').setStyle('display', 'none');
      }
    },
    getSuggest: function () {
      var el = $('adv_post_container_tagging');
      if (el && el.style.display == 'block') {
        el.style.display = 'none';
      }
      try {
        this.elements.taggedElement.getElements('.tag').each(function (elemnt) {
          elemnt.destroy();
        });
      } catch (e) {
        console.log(e);
      }
      if (this.elements.inputHidden)
        this.elements.inputHidden.value = '';
      if ($('friendas_tag_body_aaf_content'))
        $('friendas_tag_body_aaf_content').innerHTML = "";
      var self = this;
      new Autocompleter.Request.JSON(this.elements.input, en4.core.baseUrl + 'advancedactivity/friends/suggest', {
        'minLength': 1,
        'maxRecipients': 20,
        'delay': 250,
        'selectMode': 'pick',
        'multiple': false,
        'className': 'tag-autosuggest seaocore-autosuggest',
        'customChoices': true,
        'filterSubset': true,
        'tokenFormat': 'object',
        'tokenValueKey': 'label',
        'ignoreKeys': true,
        'postData': {
          'subject': en4.core.subject.guid
        },
        'injectChoice': function (token) {

          var choice = new Element('li', {
            'class': 'autocompleter-choices',
            'html': token.photo,
            'id': token.label
          });
          new Element('div', {
            'html': this.markQueryValue(token.label),
            'class': 'autocompleter-choice'
          }).inject(choice);
          new Element('input', {
            'type': 'hidden',
            'value': JSON.encode(token)
          }).inject(choice);
          this.addChoiceEvents(choice).inject(this.choices);
          choice.store('autocompleteChoice', token);

        },
        onPush: function () {
          if (this.elements.inputHidden.value.split(',').length >= this.maxRecipients) {
            this.elements.input.disabled = true;
          }
        },
        onChoiceSelect: function (choice) {
          var data = JSON.decode(choice.getElement('input').value);
          if (self.elements.inputHidden.value.split(',').indexOf("" + data.id) == -1) {
            self.elements.inputHidden.value = self.elements.inputHidden.value ? self.elements.inputHidden.value + ',' + data.id : data.id;
            self.addUserArray[data.id] = new Array();
            self.addUserArray[data.id]['label'] = data.label;
            self.addUserArray[data.id]['url'] = data.url;
            self.doPushTag(data.label, data.id, 'tagged_freinds_ids', true);
            setTimeout(function () {
              self.setContentTagUserAAF()
            }, 100);
          } else {
            self.elements.input.value = "";
            return;
          }


        },
        onCommand: function (e) {
          // This code is copy to Autocompleter JS amd hack minor for check that stop the key event
          if (e && e.key && !e.shift) {
            switch (e.key) {
              case 'enter':
                e.stop();
                if (!this.selected) {
                  if (!this.options.customChoices) {
                    // @todo support multiple
                    this.element.value = '';
                  }
                  return true;
                }
                if (this.selected && this.visible) {
                  this.choiceSelect(this.selected);
                  return !!(this.options.autoSubmit);
                }
                break;
              case 'up':
              case 'down':
                var value = this.element.value;
                if (!this.prefetch() && this.queryValue !== null) {
                  var up = (e.key == 'up');
                  if (this.selected)
                    this.selected.removeClass('autocompleter-selected');
                  if (!(this.selected)[
                          ((up) ? 'getPrevious' : 'getNext')
                  ](this.options.choicesMatch)) {
                    this.selected = null;
                  }

                  this.choiceOver(
                          (this.selected || this.choices)[
                          (this.selected) ? ((up) ? 'getPrevious' : 'getNext') : ((up) ? 'getLast' : 'getFirst')
                  ](this.options.choicesMatch), true);
                  this.element.value = value;
                }
                return false;
              case 'esc':
                this.hideChoices(true);
                if (!this.options.customChoices)
                  this.element.value = '';
                //if (this.options.autocompleteType=='message') this.element.value="";               
                break;
              case 'tab':
                if (this.selected && this.visible) {
                  this.choiceSelect(this.selected);
                  return !!(this.options.autoSubmit);
                } else {
                  this.hideChoices(true);
                  if (!this.options.customChoices)
                    this.element.value = '';
                  //if (this.options.autocompleteType=='message') this.element.value="";
                  break;
                }
            }
          }
        }
      });
    },
    setContentTagUserAAF: function () {
      var toValues = this.elements.inputHidden.value;
      if (toValues == "") {
        $('friendas_tag_body_aaf_content').innerHTML = '';
        this.resetMdashMdot();
      } else {
        var toValueArray = toValues.split(",");
        if (toValueArray.length > 0) {
          this.setMdashMdot();
          var content = '&nbsp;' + en4.core.language.translate('with') + ' ';
          var id = toValueArray[0];
          var newString = '<a href="' + this.addUserArray[id]['url'] + '">' + this.addUserArray[id]['label'] + '</a>';
          content = content + newString;

          if (toValueArray.length == 2) {
            content = content + '&nbsp;' + en4.core.language.translate('and') + '&nbsp;'
            id = toValueArray[1];
            newString = '<a href="' + this.addUserArray[id]['url'] + '">' + this.addUserArray[id]['label'] + '</a>';
            content = content + newString;

          } else if (toValueArray.length > 2) {
            content = content + '&nbsp;' + en4.core.language.translate('and') + '&nbsp;'
            newString = '<a href="javascript:void(0)">' + (parseInt(toValueArray.length) -
                    1) + '&nbsp;' + en4.core.language.translate('others') + '</a>';
            content = content + newString;
          }
          $('friendas_tag_body_aaf_content').innerHTML = content;
        } else {
          $('friendas_tag_body_aaf_content').innerHTML = "";
          this.resetMdashMdot();
        }
      }
      $('composer_preview_display_tray').removeClass('dnone');
      this.elements.input.focus();
      this.elements.input.set('value', '');
    },
    toggleTagWith: function () {
      var el = $('adv_post_container_tagging');
      if (el.style.display == 'block') {
        el.style.display = 'none';
        if ($('compose-checkin-container-checkin')) {
          $('compose-checkin-container-checkin').getFirst('label').focus();
        }
        composeInstance.focus();
      } else {
        el.style.display = 'block';
        if ($('compose-checkin-container-checkin')) {
          $('compose-checkin-container-checkin').getFirst('label').focus();
        }
        (function () {
          $('friendas_tag_body_aaf').focus();
        }).delay(100);
        $('friendas_tag_body_aaf').value = '';
      }
    },
    doPushTag: function (name, toID, newItem, hideLoc) {
      var self = this;
      var tagElement = new Element("span");
      tagElement.id = "tospan_" + name + "_" + toID;
      tagElement.innerHTML = name;
      tagElement.title = en4.core.language.translate('You are with ') + name + '.';
      new Element('a', {
        'html': 'x',
        'href': 'javascript:void(0);',
        'class': 'autocompleter-choice',
        'title': en4.core.language.translate('Remove'),
        'events': {
          'click': function (e) {
            if (e.target.getParent())
              e.target.getParent().destroy();
            self.removeTaggedFriend(toID, hideLoc);
          }.bind(self)
        }
      }).inject(tagElement);
      $('tagged-wrapper').setStyle('height', 'auto');
      tagElement.addClass("tag");
      this.elements.taggedElement.appendChild(tagElement);
      this.fireEvent('push');
    },
    removeTaggedFriend: function (id) {
      id = "" + id;
      var toValues = this.elements.inputHidden.value;
      var toValueArray = toValues.split(",");
      var checkMulti = id.search(/,/);
      if (checkMulti != -1) {
        var recipientsArray = id.split(",");
        for (var i = 0; i < recipientsArray.length; i++) {
          this.removeToTagValueAAF(recipientsArray[i], toValueArray);
        }
      } else {
        this.removeToTagValueAAF(id, toValueArray);
      }
      if (this.elements.inputHidden.value == "") {
        $('tagged-wrapper').setStyle('height', '0');
      }
      $('friendas_tag_body_aaf').disabled = false;
    },
    removeToTagValueAAF: function (id, toValueArray) {
      for (var i = 0; i < toValueArray.length; i++) {
        if (toValueArray[i] == id)
          toValueIndex = i;
      }
      toValueArray.splice(toValueIndex, 1);
      this.elements.inputHidden.value = toValueArray.join();
      var self = this;
      setTimeout(function () {
        self.setContentTagUserAAF()
      }, 100);
    }
  });



})(); // END NAMESPACE
