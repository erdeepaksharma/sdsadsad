/* $Id: composer_tag.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep Technologies
 Pvt.Ltd. $ */

Composer.Plugin.Aaftag = new Class({
  Extends: Composer.Plugin.Interface,
  name: 'tag',
  options: {
    'enabled': false,
    requestOptions: {},
    'suggestOptions': {
      'minLength': 0,
      'maxChoices': 100,
      'delay': 250,
      'selectMode': 'pick',
      'multiple': false,
      'filterSubset': true,
      'tokenFormat': 'object',
      'tokenValueKey': 'label',
      'injectChoice': $empty,
      'onPush': $empty,
      'prefetchOnInit': true,
      'alwaysOpen': false,
      'ignoreKeys': true,
      'stopKeysEvent': false
    }
  },
  initialize: function (options) {
    this.params = new Hash(this.params);
    this.parent(options);
  },
  suggest: false,
  attach: function () {

    if (!this.options.enabled || DetectMobileQuick() || DetectIpad())
      return;
    this.parent();

    // Key Events
    var self = this;
    this.getComposer().addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'editorKeyDown' : 'editorKeyPress',
            function (event) {
              if (self.suggest && self.suggest.visible && event) {
                self.suggest.onCommand(event);
                if (self.suggest.stopKeysEvent) {
                  event.stop();
                  return;
                }
              }
              self.monitor.bind(self)(event);
            }
    );
    this.getComposer().addEvent('editorClick', this.monitor.bind(this));
    this.getComposer().addEvent('editorHighlighter', this.highlight.bind(this));
    // Submit
    this.getComposer().addEvent('editorSubmit', this.submit.bind(this));

    return this;
  },
  detach: function () {
    if (!this.options.enabled)
      return;
    this.parent();
    this.getComposer().removeEvent('editorKeyPress', this.monitor.bind(this));
    this.getComposer().removeEvent('editorClick', this.monitor.bind(this));
    this.getComposer().removeEvent('editorSubmit', this.submit.bind(this));
    this.getComposer().removeEvent('editorHighlighter', this.highlight.bind(this));
    if (this.interval)
      $clear(this.interval);
    return this;
  },
  activate: $empty,
  deactivate: $empty,
  poll: function () {

  },
  caretPosition: 0,
  monitor: function (e) {
    this.getComposer().getCaretCoordinates();
    if (activity_type != 1)
      return this.endSuggest();
    // seems like we have to do this stupid delay or otherwise the last key
    // doesn't get in the content
    (function () {

      var content = this.getComposer().getContent();
      if (!content)
        return;
      this.caretPosition = this.getComposer().getCaretPosition();
      this.currentTagStartIndex = false;
      content = content.substring(0, this.caretPosition);
      var currentIndex = content.lastIndexOf('@');
      if (currentIndex === -1) {
        return this.endSuggest();
      }

      var value = content.replace(/\n/gi, ' ');
      if (currentIndex > 0 && value.substr((currentIndex - 1), 1) !== ' ')
      {
        return this.endSuggest();
      }

      // Get the current at segment
      var segment = content.substring(currentIndex + 1, this.caretPosition).trim();
      // Check next space
      var spaceIndex = segment.indexOf(' ');
      if (spaceIndex > -1) {
        segment = segment.substring(0, spaceIndex);
      }

      if (segment.length > 20 || segment.length < 1) {
        return this.endSuggest();
      }
      this.currentTagStartIndex = currentIndex;
      this.doSuggest(segment);

    }).delay(5, this);
  },
  doSuggest: function (text) {
    if (text.length <= 0) {
      return this.endSuggest();
    }
    this.currentText = text;
    var suggest = this.getSuggest();
    var input = this.getHiddenInput();
    input.set('value', text);
    input.value = text;
    suggest.prefetch();
    this.setAutoSuggestPosition();
  },
  endSuggest: function () {
    this.currentText = '';
    var input = this.getHiddenInput();
    input.set('value', this.currentText);
    input.value = this.currentText;
    this.getSuggest().prefetch();
    this.currentTagStartIndex = false;
//    this.positions = {};
//    if (this.suggest) {
//      this.getSuggest().destroy();
//      this.suggest = false;
//      delete this.suggest;
//    }
  },
  getHiddenInput: function () {
    if (!this.hiddenInput) {
      this.hiddenInput = new Element('input', {
        'type': 'text',
        'styles': {
          'display': 'none'
        }
      }).inject(document.body);
    }
    return this.hiddenInput;
  },
  getSuggest: function () {
    if (!this.suggest) {
      var width = this.getComposer().elements.body.getSize().x;
      if (width > 200) {
        width = 200;
      }
      this.choices = new Element('ul', {
        'class': 'tag-autosuggest aaf-feed-tag-autosuggest',
        'styles': {
          'width': (width - 2) + 'px'
        }
      }).inject(this.getComposer().elements.body, 'after');

      var self = this;
      var options = $merge(this.options.suggestOptions, {
        minLength: 1,
        'customChoices': this.choices,
        'injectChoice': function (token) {
          var choice = new Element('li', {
            'class': 'autocompleter-choices',
            //'value': token.id,
            'html': token.photo || '',
            'id': token.guid
          });
          var divEl = new Element('div', {
            'html': this.markQueryValue(token.label),
            'class': 'autocompleter-choice'
          });
          if (token.type != 'user') {
            new Element('div', {
              'html': this.markQueryValue(token.type)
            }).inject(divEl);
          }
          divEl.inject(choice);
          new Element('input', {
            'type': 'hidden',
            'value': JSON.encode(token)
          }).inject(choice);
          this.addChoiceEvents(choice).inject(this.choices);
          choice.store('autocompleteChoice', token);
        },
        'onChoiceSelect': function (choice) {
          var data = JSON.decode(choice.getElement('input').value);
          var composer = self.getComposer();
          var tagString = '@' + self.currentText;
          var content = self.replaceTextInPosition(composer.getContent(), tagString, data.label, self.caretPosition);

          var text = composer.hiddenBody.get('value')
                  .replace(tagString, '#tags@;' + data.guid + '@;' + data.label + '#')
                  .replace(/\r/g, " <br />");

          composer.hiddenBody.set('value', text);
          composer.setContent(content);
          composer.setCaretPosition(self.caretPosition + 1 - tagString.length + data.label.length);
        },
        'emptyChoices': function () {
          this.fireEvent('onHide', [this.element, this.choices]);
        },
        onShow: function () {
          self.setAutoSuggestPosition();
        },
        'onCommand': function (e) {
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
                  this.stopKeysEvent = true;
                  this.choiceSelect(this.selected);
                  return !!(this.options.autoSubmit);
                }
                break;
              case 'up':
              case 'down':
                var value = this.element.value;
                if (!this.prefetch() && this.queryValue !== null) {
                  this.stopKeysEvent = true;
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
                this.stopKeysEvent = true;
                this.hideChoices(true);
                if (!this.options.customChoices)
                  this.element.value = '';
                break;
              case 'tab':
                this.stopKeysEvent = true;
                if (this.selected && this.visible) {
                  this.choiceSelect(this.selected);
                  return !!(this.options.autoSubmit);
                } else {
                  this.hideChoices(true);
                  if (!this.options.customChoices)
                    this.element.value = '';
                  break;
                }
              default :
                this.stopKeysEvent = false;
            }
          }

        }
      });

      if (this.options.suggestProto == 'local') {
        this.suggest = new Autocompleter.Local(this.getHiddenInput(), this.options.suggestParam, options);
      } else if (this.options.suggestProto == 'request.json') {
        this.suggest = new Autocompleter.Request.JSON(this.getHiddenInput(), this.options.suggestOptions.url, options);
      }
      if (this.suggest && !this.suggest.options.alwaysOpen) {
        this.getComposer().elements.body
                .addEvent('blur', this.suggestHideChoices.create({
                  bind: this
                }));
      }
    }

    return this.suggest;
  },
  setAutoSuggestPosition: function () {
    var position = this.getComposer().getCaretCoordinates(this.currentTagStartIndex), body = this.getComposer().elements.body;
    //    lineHeight = parseInt(body.getStyle('lineHeight'), 10) || 18;
    // this.choices.setStyles({left: position.left, 'top': lineHeight + position.top});
    this.choices.setStyles({left: position.x, 'top': position.bottom});

    //check if the right position of auto complete is larger than the right position of the text area
    //if yes, reset the left of auto complete list to make it fit the input
    var bodyCoords = this.getComposer().elements.body.getCoordinates(body.getOffsetParent()),
            choicesCoords = this.choices.getCoordinates(this.choices.getOffsetParent());
    if (bodyCoords.right <= choicesCoords.right) {
      this.choices.setStyle('left', Math.abs(choicesCoords.left - (choicesCoords.right - bodyCoords.right)));
    }
  },
  highlight: function () {
    if (activity_type != 1)
      return;
    var text = this.getComposer().hiddenBody.get('value'), newStr = this.getComposer().highlighterSegment, tagReg = /#tags@;\w+@;[^\#]+#/gim;
    var tagMatch = text.match(tagReg);

    if (tagMatch == null)
    {
      return;
    }

    var textSplit = text.split(tagReg);
    var tagLabel = new Array();
    var len = tagMatch.length;
    for (var i = 0; i < len; ++i)
    {
      tagLabel[i] = tagMatch[i].replace(/^#tags+@;\w+@;/, '').replace(/\#$/, '').trim();
    }

    var highlighterSegment = '';
    var highlighterText = '';
    for (var i = 0; i < len; ++i)
    {
      var indexOfLabel = newStr.indexOf(tagLabel[i]);
      if (indexOfLabel > -1) {
        textSplit[i] = newStr.substr(0, indexOfLabel);
        newStr = newStr.substr(indexOfLabel + tagLabel[i].length);
      } else {
        tagMatch[i] = tagLabel[i] = '';
      }

      var subText = textSplit[i] || '';
      highlighterSegment += subText + tagMatch[i];
      if (tagLabel[i]) {
        var newString = this.getComposer().getHighlightString(tagLabel[i]);
        highlighterText += subText + newString;
      }
    }

    var tagConvertedStr = '';
    if (i > 0) {
      tagConvertedStr = newStr.replace(tagLabel[i - 1], '');
      highlighterSegment += tagConvertedStr;
      highlighterText += tagConvertedStr;
    }

    this.getComposer().highlighterSegment = highlighterSegment;
    this.getComposer().highlighterText = highlighterText.replace(/<iframe/gim, '&lt;iframe')
            .replace(/<\/iframe>/gim, '&lt;/iframe&gt;')
            .replace(/<style/gim, '&lt;style')
            .replace(/<\/style>/gim, '&lt;/style&gt;')
            .replace(/<img/gim, '&lt;img')
            .replace(/\n/g, '<br />')
            .replace(/(#hashtags)@([^\#]+)#/gim, '<a href="$1@$2@">#$2</a>')
            .replace(/­/gim, '');
  },
  replaceTextInPosition: function (text, search, replace, pos) {
    var pos2 = text.substr(0, pos).lastIndexOf(search);
    pos = pos2 < 0 ? pos : pos2;
    var t1 = text.substr(0, pos);
    var t2 = text.substr(pos + search.length);
    return t1 + replace + t2;
  },
  suggestHideChoices: function () {
    var suggest = this.suggest;
    if (suggest) {
      suggest.hideChoices(true);
      if (!suggest.options.customChoices)
        suggest.element.value = '';
    }
  },
  submit: function () {
    this.makeFormInputs({
      tag: this.getAAFTagsFromComposer().toQueryString()
    });
  },
  // get the tags which are in composer
  getAAFTagsFromComposer: function ()
  {
    var composerTags = new Hash(), text = this.getComposer().hiddenBody.get('value'), tagReg = /#tags@;\w+@;[^\#]+#/gim;
    var tagMatch = text.match(tagReg);
    if (tagMatch == null)
    {
      return composerTags;
    }
    var guid = '';
    var tagLabel = new Array();
    var len = tagMatch.length;
    for (var i = 0; i < len; ++i)
    {
      var tag = tagMatch[i];
      tagLabel[i] = tag.replace(/^#tags+@;\w+@;/, '').replace(/\#$/, '');
      guid = tag.replace(tag.replace(/^#tags+@;\w+@;/, ''), '');
      guid = guid.replace(/^#tags+@;/, '').replace(/@;$/, '');
      composerTags[guid] = tagLabel[i].trim();
    }
    return composerTags;
  },
  makeFormInputs: function (data) {
    $H(data).each(function (value, key) {
      this.setFormInputValue(key, value);
    }.bind(this));
  },
  // make tag hidden input and set value into composer form
  setFormInputValue: function (key, value) {
    var elName = 'aafComposerForm' + key.capitalize();
    var composerObj = this.getComposer();
    if (composerObj.elements.has(elName))
      composerObj.elements.get(elName).destroy();
    composerObj.elements.set(elName, new Element('input', {
      'type': 'hidden',
      'name': 'composer[' + key + ']',
      'value': value || ''
    }).inject(composerObj.getInputArea()));
    composerObj.elements.get(elName).value = value;
  },
  mapTags: function (tagsData) {
    var composer = this.getComposer();
    var text = composer.hiddenBody.get('value');
    for (var i = 0; i < tagsData.length; i++) {
      text = text.split(tagsData[i].label).join('#tags@;' + tagsData[i].guid + '@;' + tagsData[i].label + '#');
    }
    composer.hiddenBody.set('value', text);
  }
});