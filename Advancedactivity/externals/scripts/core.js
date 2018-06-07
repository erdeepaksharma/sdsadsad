/* $Id: core.js 2012-26-01 00:00:00Z SocialEngineAddOns Copyright 2011-2012 BigStep Technologies Pvt. Ltd. $
 */

var adfShare = 0, Share_Translate = en4.core.language.translate('ADVADV_SHARE'), maxAutoScrollAAF = 0, countScrollAAFSocial = 0, countScrollAAFFB = 0, countScrollAAFTweet = 0, countScrollAAFLinkedin = 0, countScrollAAFInstagram = 0;
en4.advancedactivity = {    
  pinboard: {
    columnWidth: 0,
    oldHtml: '',
    delay : 200,
    init: function () {
      if(window.getSize().x < 601){
          return;
      }
      if(!this.hasDisable()) {
        en4.advancedactivity.pinboard.oldHtml = $('activity-feed').get('html');
      }
      en4.advancedactivity.pinboard.attachEvents();
      en4.advancedactivity.pinboard.setLayout();
    },
    hasDisable: function() {
        return (activity_type != 1) || !$('activity-feed');
    },
    setLayout: function () {
      if(this.hasDisable()) {
          return;
      }
    if (!$('activity-feed').get('html')) {
        $('activity-feed').setStyle('height', '');
    } else {
        if (this.columnWidth == 0) {
          this.columnWidth = $('activity-feed').getSize().x / parseInt($('activity-feed').getSize().x / $('activity-feed').getElement('.activity-item').getSize().x);
        }
        $('activity-feed').pinBoardSeaoMasonry({
            columnWidth: this.columnWidth, //224 columnWidth does not need to be set if singleMode is set to true.
            singleMode: true,
            itemSelector: '.activity-item'
        });
    }
    },
    attachEvents: function () {
      this.interval = (function() {
        if (!this.hasDisable() && this.oldHtml !== $('activity-feed').get('html')) {        
          this.setLayout();
          this.oldHtml = $('activity-feed').get('html');
        }
      }).periodical(this.delay, this);
    }
  },
  bindEditFeed: function (action_id, composerOptions) {
    var editComposeInstance = new Composer('edit-body-' + action_id, {
      lang: composerOptions.lang,
      menuElement: 'edit_advanced_compose-menu',
      hideSubmitOnBlur: false,
      allowEmptyWithoutAttachment: composerOptions.allowEmptyWithoutAttachment,
    });

    document.store('editComposeInstance' + action_id, editComposeInstance);
    if (composerOptions.inPopup == 0) {
      $('activity-item-' + action_id).getElement('.feed_item_option_edit').addEvent('click', function (event) {

        var el = $(event.target);
        var parent = el.getParent('.activity-item');
        parent.getElement('.feed_item_body_content').setStyle('display', 'none');
        parent.getElement('.feed_item_body_edit_content').setStyle('display', 'block');
      });
    }
    editComposeInstance.getForm().getElement('.feed-edit-cancel').addEvent('click', function (event) {
      var el = $(event.target);
      if (composerOptions.inPopup) {
        SmoothboxSEAO.close();
      } else {
        var parent = el.getParent('.activity-item');
        parent.getElement('.feed_item_body_edit_content').setStyle('display', 'none');
        parent.getElement('.feed_item_body_content').setStyle('display', 'block');
      }
    });

    var wapper = $('edit_feed_' + action_id + '_emoticons-nested-comment-icons_emoji');
    if (wapper) {
      var iconSrc = wapper.retrieve('iconPathPrefix');
      var str = editComposeInstance.getContent();
      wapper.getElements(".seaocore_emoji_icon").each(function (icon) {
        var iconCode = icon.get('data-icon');
        if (str.indexOf(iconCode) >= 0)
          editComposeInstance.iconCodes[iconCode] = iconSrc + icon.get('data-url');
      });
      editComposeInstance.setHighlighterContent();
      wapper.addEvent('seaoEmojiSelected', function (el) {
        editComposeInstance.attachEmotionIcon(el, iconSrc);
      });
    }
    editComposeInstance.getForm().addEvent('submit', function (event) {
      event.stop();
      if (!editComposeInstance.options.allowEmptyWithoutAttachment && editComposeInstance.getContent() == '') {
        return;
      }
      this.fireEvent('editorSubmit');
      var params = editComposeInstance.getForm().toQueryString().parseQueryString();
      en4.core.request.send(new Request.JSON({
        url: editComposeInstance.getForm().get('action'),
        data: $merge({
          format: 'json',
          subject: en4.core.subject.guid,
          feedSettings: window.feedAafSettings
        }, params),
        method: 'POST', //or post
        onRequest: function () {
          editComposeInstance.getForm().getElementById('edit_advanced_compose-menu-buttons').empty();
          en4.core.loader.inject(editComposeInstance.getForm().getElementById('edit_advanced_compose-menu-buttons'));
          en4.core.runonce.add(function () {
            if (composerOptions.inPopup) {
              (function () {
                SmoothboxSEAO.close();
              }).delay(50);
            }
          });
        }
      }), {
        'force': true,
        'element': $('activity-item-' + action_id),
        'updateHtmlMode': 'comments'
      });
    }).bind(editComposeInstance);
  },
  addfriend: function (action_id, user_id) {
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/add-friend',
      data: {
        format: 'json',
        action_id: action_id,
        user_id: user_id,
        subject: en4.core.subject.guid,
        feedSettings: window.feedAafSettings
      }
    }), {
      'force': true,
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  cancelfriend: function (action_id, user_id) {
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/cancel-friend',
      data: {
        format: 'json',
        action_id: action_id,
        user_id: user_id,
        subject: en4.core.subject.guid,
        feedSettings: window.feedAafSettings
      }
    }), {
      'force': true,
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  like: function (el, action_id, comment_id, reaction) {
    if (el.retrieve('isActive', false))
      return;
    var oldHtml = el.innerHTML;
    el.store('isActive', true);
    el.innerHTML = el.get('action-title');
    var element = el.getParent('.comment-likes-activity-item');
    var hasViewPage = element.get('id').indexOf('view') < 0 ? 0 : 1;
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/like',
      data: {
        format: 'json',
        action_id: action_id,
        comment_id: comment_id,
        subject: en4.core.subject.guid,
        isShare: adfShare,
        reaction: reaction,
        onViewPage: hasViewPage,
        feedSettings: window.feedAafSettings
      },
      onSuccess: function (response, response2, response3, response4) {
        if ((!response && !response3 && $type(options.updateHtmlElement)) || ($type(response) == 'object' && $type(response.status) && response.status == false)) {
          el.store('isActive', false);
          el.innerHTML = oldHtml;
        }
      }
    }), {
      'force': true,
      'element': element
    });
  },
  unlike: function (el, action_id, comment_id) {
    if (el.retrieve('isActive', false))
      return;
    var oldHtml = el.innerHTML;
    el.store('isActive', true);
    el.innerHTML = el.get('action-title');
    var element = el.getParent('.comment-likes-activity-item');
    var hasViewPage = element.get('id').indexOf('view') < 0 ? 0 : 1;
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/unlike',
      data: {
        format: 'json',
        action_id: action_id,
        comment_id: comment_id,
        subject: en4.core.subject.guid,
        isShare: adfShare,
        onViewPage: hasViewPage,
        feedSettings: window.feedAafSettings
      },
      onSuccess: function (response, response2, response3, response4) {
        if ((!response && !response3 && $type(options.updateHtmlElement)) || ($type(response) == 'object' && $type(response.status) && response.status == false)) {
          el.store('isActive', false);
          el.innerHTML = oldHtml;
        }
      }
    }), {
      'force': true,
      'element': element
    });
  },
  comment: function (action_id, body, extendClass) {
    if (body.trim() == '')
    {
      return;
    }
    var show_all_comments_value = 0;
    if (typeof show_all_comments != 'undefined') {
      show_all_comments_value = show_all_comments.value;
    }
    var CommentHTML = '<div class="comments_author_photo"><a href="' + en4.user.viewer.href + '" ><img src="' + en4.user.viewer.iconUrl + '"  class="thumb_icon item_photo_user  thumb_icon"></a></div><div class="comments_info"><span class="comments_author"><a href="' + en4.user.viewer.href + '" class="sea_add_tooltip_link" rel="user 1">' + en4.user.viewer.title + '</a></span><span class="comments_body">' + body + '</span><ul class="comments_date"><li class="comments_timestamp">' + en4.advancedactivity.fewSecHTML + '</li></ul></div>';
    if ($("feed-comment-form-open-li_" + extendClass + action_id)) {
      new Element('li', {
        'html': CommentHTML
      }).inject($("feed-comment-form-open-li_" + extendClass + action_id), 'before');
    } else {
      new Element('li', {
        'html': CommentHTML
      }).inject($('comment-likes-activity-item-' + extendClass + action_id).getElement('.comments').getElement('ul'));
    }

    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/comment',
      data: {
        format: 'json',
        action_id: action_id,
        body: body,
        subject: en4.core.subject.guid,
        isShare: adfShare,
        show_all_comments: show_all_comments_value,
        onViewPage: extendClass,
        feedSettings: window.feedAafSettings
      }
    }), {
      'force': true,
      'element': $('comment-likes-activity-item-' + extendClass + action_id)
    });
  },
  attachComment: function (formElement, is_enter_submit) {
    var bind = this;
    formElement.style.display = "none";
    var hasViewPage = formElement.get('id').indexOf('view') < 0 ? 0 : 1;
    var extendClass = '';
    if (hasViewPage) {
      extendClass = 'view-';
    }
    if (is_enter_submit == 1) {
      formElement.addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'keydown' : 'keypress', function (event) {
        if (event.shift && event.key == 'enter') {
        } else if (event.key == 'enter') {
          event.stop();
          if (formElement.body.value.trim() == '' || formElement.retrieve('sendReq', false))
          {
            return;
          }
          bind.comment(formElement.action_id.value, formElement.body.value, extendClass);
          //       formElement.store('sendReq', true);
//          setTimeout(function() {
          formElement.body.value = '';
          formElement.style.display = "none";
//          }, 2000);
        }
      });

      // add blur event
      formElement.body.addEvent('blur', function () {
        //  setTimeout(function(){ 
        formElement.style.display = "none";
        if ($("feed-comment-form-open-li_" + extendClass + formElement.action_id.value))
          $("feed-comment-form-open-li_" + extendClass + formElement.action_id.value).style.display = "block";
        // },20);
      });
    }
    formElement.addEvent('submit', function (event) {
      event.stop();
      if (formElement.body.value.trim() == '' || formElement.retrieve('sendReq', false))
      {
        return;
      }
      bind.comment(formElement.action_id.value, formElement.body.value, extendClass);
      // formElement.store('sendReq', true);
//      setTimeout(function() {
//        formElement.store('sendReq', false);
      formElement.body.value = '';
      formElement.style.display = "none";
//      }, 1000);
    });
  },
  viewComments: function (action_id) {

    if ($('show_view_all_loading')) {
      $('show_view_all_loading').style.display = 'block';
    }

    if ($('comments_viewall')) {
      $('comments_viewall').style.display = 'none';
    }
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/viewComment',
      data: {
        format: 'json',
        action_id: action_id,
        nolist: true,
        isShare: adfShare,
        feedSettings: window.feedAafSettings
      }
    }), {
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  viewLikes: function (action_id) {
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/viewLike',
      data: {
        format: 'json',
        action_id: action_id,
        nolist: true,
        isShare: adfShare,
        feedSettings: window.feedAafSettings
      }
    }), {
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  updateCommentable: function (action_id) {
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/update-commentable',
      data: {
        format: 'json',
        action_id: action_id,
        subject: en4.core.subject.guid,
        feedSettings: window.feedAafSettings
      }
    }), {
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  updateShareable: function (action_id) {
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/update-shareable',
      data: {
        format: 'json',
        action_id: action_id,
        subject: en4.core.subject.guid,
        feedSettings: window.feedAafSettings
      }
    }), {
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  updateSaveFeed: function (action_id) {
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'advancedactivity/index/update-save-feed',
      data: {
        format: 'json',
        action_id: action_id,
        subject: en4.core.subject.guid,
        feedSettings: window.feedAafSettings
      }
    }), {
      'element': $('activity-item-' + action_id),
      'updateHtmlMode': 'comments'
    });
  },
  translateFeed: function (body) {
    if (!body)
      return;
    var userLang = navigator.language || navigator.userLanguage;
    var lang = userLang.substring(0, userLang.indexOf("-"));
    window.open("https://translate.google.com/#auto/" + lang + "/" + body, "Translated Feed", "toolbar=no, menubar=no,location=no status=1,resizable=1,width=950,height=300", "POS");
  },
  bindOnLoadForViewerFeeds: function (options) {
    var CommentLikesTooltips;

    $('activity-feed').getElements('.seao_icons_toolbar_attach').each(function (el) {
      if (($('activity-feed').getCoordinates().right * 0.65) < el.getCoordinates().left) {
        el.addClass('seao_icons_toolbar_right');
      } else {
        el.removeClass('seao_icons_toolbar_right');
      }
    });
    if ($('activity-feed').get('class').indexOf('feed_sections_pinboard_col') !== false) {
      $('activity-feed').getElements('.seao_icons_toolbar_attach').addEvent('mouseover', function () {
        var el = $(this);
        if (($('activity-feed').getCoordinates().right * 0.65) < el.getCoordinates().left) {
          if (el.getElement('.icons-toolbar-container').getCoordinates().width + el.getCoordinates().left > $('activity-feed').getCoordinates().right) {
            var diff = el.getElement('.icons-toolbar-container').getCoordinates().width - (el.getCoordinates().left + el.getElement('.icons-toolbar-container').getCoordinates().width - $('activity-feed').getCoordinates().right);
          } else {
            var diff = el.getElement('.icons-toolbar-container').getCoordinates().width;
          }
          el.getElement('.icons-toolbar-container').setStyle('right', '-' + (diff - el.getCoordinates().width - 10) + 'px');
          el.addClass('seao_icons_toolbar_right');
        } else {
          el.removeClass('seao_icons_toolbar_right');
          el.getElement('.icons-toolbar-container').setStyle('right', '');
        }
      });
    }
    if (options.allowReaction) {
      try {
        en4.sitereaction.attachReaction();
      } catch (e) {
      }
    }

    SmoothboxSEAO.bind($('activity-feed'));
    // Add hover event to get likes
    $$('.comments_comment_likes').addEvent('mouseover', function (event) {
      var el = $(event.target);
      if (!el.retrieve('tip-loaded', false)) {
        el.store('tip-loaded', true);
        el.store('tip:title', en4.core.language.translate('Loading...'));
        el.store('tip:text', '');
        var id = el.get('id').match(/\d+/)[0];
        // Load the likes
        var url = options.likeLoadUrl;
        var req = new Request.JSON({
          url: url,
          data: {
            format: 'json',
            //type : 'core_comment',
            action_id: el.getParent('li').getParent('li').getParent('li').get('id').match(/\d+/)[0],
            comment_id: id
          },
          onComplete: function (responseJSON) {
            el.store('tip:title', responseJSON.body);
            el.store('tip:text', '');
            CommentLikesTooltips.elementEnter(event, el); // Force it to update the text

          }
        });
        req.send();
      }
    });
    // Add tooltips
    CommentLikesTooltips = new Tips($$('.comments_comment_likes'), {
      fixed: true,
      className: 'comments_comment_likes_tips',
      offset: {
        'x': 20,
        'y': 10
      }
    });
    // Enable links in comments
    $$('.comments_body').enableLinks();

  },
  resetTargetPost: function (force) {
    if (!$('advancedactivity_post_target_options')) {
      return;
    }
    if (!$('advancedactivity_post_target_options').hasClass('target_added') || force) {
      $('min_age').set('value', 0);
      $('max_age').set('value', 0);
      $('who-').set('checked', 'checked');
      $('advancedactivity_post_target_options').removeClass('target_added');
      $$('.adv_button_target_post').removeClass('active');
    }
    $('advancedactivity_post_target_options').addClass('dnone');
  },
  resetScheduleTime: function () {
    if (!$('schedule_time-wrapper')) {
      return;
    }
    if ($("schedule_time-date").get('value') && $("tab_advFeed_schedule_post").getParent('.aaf_tabs_feed')) {
      getTabBaseContentFeed('schedule_post', '0');
      $("tab_advFeed_schedule_post").getParent('.aaf_tabs_feed').getParent().getElements('ul > li').removeClass('aaf_tab_active');
      $("tab_advFeed_schedule_post").addClass('aaf_tab_active');
    }
    $('schedule_time-date').set('value', '');
    $('schedule_time-hour').set('value', '');
    $('schedule_time-minute').set('value', '');
    if ($('schedule_time-ampm'))
      $('schedule_time-ampm').set('value', '');
    cal_schedule_time.calendars[0].val = null;
    cal_schedule_time.write(cal_schedule_time.calendars[0]);
    $('aaf_schedule_time').addClass('dnone');
    $$('.adv_button_schedule_post').removeClass('active');
  },
  bindEmojiIcons: function (idPrefix) {
    var wapper = $(idPrefix + 'emoticons-nested-comment-icons_emoji');
    if (wapper) {
      wapper.destroy();
    }
    wapper = $(idPrefix + 'emoticons-nested-comment-icons_emoji_dummy');
    wapper.set('id', idPrefix + 'emoticons-nested-comment-icons_emoji');
    wapper.setStyle('zIndex', 1000);
    var injectOffestParent = document.body;
    if (SmoothboxSEAO.active) {
      injectOffestParent = SmoothboxSEAO.contentHTML;
    }
    wapper.inject(injectOffestParent);
    wapper.store('iconPathPrefix', en4.core.staticBaseUrl + 'application/modules/Seaocore/externals/emoji/');
    $(idPrefix + 'adv_post_smile').addEvent('click', function (event) {

      wapper.setStyles({
        left: ($(idPrefix + 'adv_post_smile').getCoordinates(injectOffestParent).left - 293) + 'px',
        top: ($(idPrefix + 'adv_post_smile').getCoordinates(injectOffestParent).top + 35) + 'px'
      });
      if (!wapper.retrieve('iconsLoaded', false)) {
        var path = en4.core.staticBaseUrl + 'application/modules/Seaocore/externals/emoji/';
        wapper.getElements(".seaocore_emoji_icon").each(function (item) {
          item.setStyle('backgroundImage', 'url(' + path + item.get('data-url') + ')');
        });
        wapper.store('iconsLoaded', true);
      }
      wapper.toggleClass('dnone');

      $(idPrefix + 'seaocore_emoji_category').retrieve('scrollbars').updateScrollBars();
    });
    wapper.getElements('.aaf_emoj_icon').addEvent('click', function (event) {
      var el = event.target;
      if (el.get('tag') == 'span') {
        el = el.getElement('i');
      }
      wapper.addClass('dnone');
      wapper.fireEvent('seaoEmojiSelected', el);
    });
    document.body.addEvent('click', function (event) {
      if (event.target.getParent('.seaoemoji_noclose') || event.target.hasClass('.seaoemoji_noclose')) {
        return;
      }
      if (!wapper.hasClass('dnone'))
        wapper.addClass('dnone');
    });

    wapper.getElements('.seaocore_emoji_tabs li').addEvent('click', function (event) {
      event.stopPropagation();
      var el = event.target.get('tag') == 'li' ? event.target : event.target.getParent('li');
      wapper.getElements('.seaocore_emoji_tab_content').removeClass('active');
      $(idPrefix + 'seaocore_emoji_category_' + el.get('data-target')).addClass('active');
      var scrollbarContent = $(idPrefix + 'seaocore_emoji_category').getElement('.scrollbar-content');
      scrollbarContent.scrollTo(
              scrollbarContent.getScroll().x,
              $(idPrefix + 'seaocore_emoji_category_' + el.get('data-target')).getOffsets().y - scrollbarContent.getPosition().y
              );
    });

    $(idPrefix + 'seaocore_emoji_category').scrollbars({
      scrollBarSize: 10,
      fade: true
    });
    $(idPrefix + 'seaocore_emoji_category').getElement('.scrollbar-content').addEvent('scroll', function () {
      var activeli = '';
      wapper.getElements('.seaocore_emoji_tab_content').each(function (el) {
        var scrollbarContent = $(idPrefix + 'seaocore_emoji_category').getElement('.scrollbar-content');
        if (el.getOffsets().y - scrollbarContent.getPosition().y - 150 <= scrollbarContent.getScroll().y) {
          activeli = el.get('id').replace(idPrefix + 'seaocore_emoji_category_', '');
        }
      });
      if ($(idPrefix + 'seaocore_emoji_tab_' + activeli)) {
        wapper.getElements('.seaocore_emoji_tabs li').removeClass('active');
        $(idPrefix + 'seaocore_emoji_tab_' + activeli).addClass('active');
      }
    });
  }
};


var autoScrollFeedAAFEnable = 1;
var feedToolTipAAFEnable = 1;
var activity_type = 1;
var aaf_feed_type_tmp = 1;
var current_window_url = window.location.href;
var aaf_showImmediately = false;
var AdvancedactivityUpdateHandler = new Class({
  Implements: [Events, Options],
  options: {
    debug: false,
    baseUrl: '/',
    identity: false,
    delay: 5000,
    admin: false,
    idleTimeout: 600000,
    last_id: 0,
    next_id: null,
    subject_guid: null,
    showImmediately: false,
    showloading: true
  },
  state: true,
  activestate: 1,
  fresh: true,
  lastEventTime: false,
  title: document.title,
  //loopId : false,

  initialize: function (options) {
    this.setOptions(options);
  },
  start: function () {
    this.state = true;
    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {
      timeout: this.options.idleTimeout
    });
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive': function () {
        this._log('activity loop onStateActive');
        this.activestate = 1;
        this.state = true;
      }.bind(this),
      'onStateIdle': function () {
        this._log('activity loop onStateIdle');
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });
    this.loop();
    //this.loopId = this.loop.periodical(this.options.delay, this);
  },
  stop: function () {
    this.state = false;
  },
  checkFeedUpdate: function (action_id, subject_guid) {
    //  if (en4.core.request.isRequestActive())  return;
    var req = new Request.HTML({
      url: en4.core.baseUrl + 'widget/index/name/advancedactivity.feed',
      data: {
        'format': 'html',
        'minid': this.options.last_id + 1,
        'feedOnly': true,
        'nolayout': true,
        'subject': this.options.subject_guid,
        'checkUpdate': true,
        'actionFilter': this.options.actionFilter
      },
      onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
        if (activity_type == 1 && $('feed-update')) {
          $('feed-update').innerHTML = responseHTML;
        }

      }

    });
    en4.core.request.send(req, {
      'force': true
    });
    req.addEvent('complete', function () {
      (function () {
        if (this.options.showImmediately && $('feed-update').getChildren().length > 0) {
          $('feed-update').setStyle('display', 'none');
          $('feed-update').empty();
          this.getFeedUpdate(this.options.next_id);
        }
      }).delay(50, this);
    }.bind(this));
    return req;
  },
  getFeedUpdate: function (last_id) {
    //if( en4.core.request.isRequestActive() ) return;

    var min_id = this.options.last_id + 1;
    this.options.last_id = last_id;
    document.title = this.title;
    if ($('update_advfeed_blink'))
      $('update_advfeed_blink').style.display = 'none';
    if (this.options.showloading && $('aaf_feed_update_loading'))
      $('aaf_feed_update_loading').style.display = 'block';

    var req = new Request.HTML({
      url: en4.core.baseUrl + 'widget/index/name/advancedactivity.feed',
      data: {
        'format': 'html',
        'minid': min_id,
        'feedOnly': true,
        'nolayout': true,
        'getUpdate': true,
        'subject': this.options.subject_guid,
        'actionFilter': this.options.actionFilter,
        feedSettings: window.feedAafSettings
      },
      onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
        if ($('aaf_feed_update_loading'))
          $('aaf_feed_update_loading').style.display = 'none';

        var htmlBody;
        var jsBody;
        // Get response
        if ($type(responseHTML) == 'string') { // HTML response
          htmlBody = responseHTML;
          jsBody = responseJavaScript;
        }
        // An error probably occurred
        if (!responseTree && !responseHTML) {
          en4.core.showError(en4.core.language.translate('An error has occurred processing the request. The target may no longer exist.'));
          return;
        }

        var newUl = document.createElement('ul', {
        });
        newUl.className = "feed";
        Elements.from(htmlBody).reverse().inject(newUl, 'top');
        $('activity-feed').getParent().insertBefore(newUl, $('activity-feed'));
        //Smoothbox.bind($(options.updateHtmlElement));
        var feedSlide = new Fx.Slide(newUl, {
          resetHeight: true
        }).hide();
        feedSlide.slideIn();
        (function () {
          feedSlide.wrapper.destroy();
          if (htmlBody)
            htmlBody.stripScripts(true);
          if (jsBody)
            eval(jsBody);
          Elements.from(htmlBody).reverse().inject($('activity-feed'), 'top');
          Smoothbox.bind($('activity-feed'));
          en4.core.runonce.trigger();
        }).delay(450);
      }
    });
    en4.core.request.send(req, {
      'force': true
    });
    return req;
  },
  loop: function () {
    this._log('activity update loop start');

    if (!this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }


    //    try {
    //      this.checkFeedUpdate().addEvent('complete', function() {
    //        this.loop.delay(this.options.delay, this);
    //      }.bind(this));
    //    } catch( e ) {
    //      this.loop.delay(this.options.delay, this);
    //      this._log(e);
    //    }

    try {
      this.checkFeedUpdate().addEvent('complete', function () {
        try {
          this._log('activity loop req complete');
          this.loop.delay(this.options.delay, this);
        } catch (e) {
          this.loop.delay(this.options.delay, this);
          this._log(e);
        }
      }.bind(this));
    } catch (e) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }

    this._log('activity update loop stop');
  },
  // Utility
  _log: function (object) {
    if (!this.options.debug) {
      return;
    }

    try {
      if ('console' in window && typeof (console) && 'log' in console) {
        console.log(object);
      }
    } catch (e) {
      // Silence
    }
  }
})

var update_freq_aaf, aaf_last_id, aaf_subjectGuid, advancedactivityUpdateHandler;
var Call_aafcheckUpdate = function () {

  en4.core.runonce.add(function () {
    try {
      advancedactivityUpdateHandler = new AdvancedactivityUpdateHandler({
        'baseUrl': en4.core.baseUrl,
        'basePath': en4.core.basePath,
        'identity': 4,
        'delay': update_freq_aaf,
        'last_id': aaf_last_id,
        'subject_guid': aaf_subjectGuid,
        'actionFilter': 'all',
        'showImmediately': aaf_showImmediately,
        'showloading': !aaf_showImmediately

      });

      setTimeout("advancedactivityUpdateHandler.start()", 1250);
      // advancedactivityUpdateHandler.start();

      window._advancedactivityUpdateHandler = advancedactivityUpdateHandler;



    } catch (e) {

      // if( $type(console) ) console.log(e);

    }

  });

}


window.addEvent('domready', function () {

  hidestatusbox();
});

var submitFormAjax = function (submitUri, dumpComposeInstance) {
  if (activity_type == 2) {
    active_submitrequest = 1;
    if (Tweet_lenght == 1) {
      en4.core.showError("<div class='aaf_show_popup'><p>" + en4.core.language.translate("Your Tweet was over 140 characters. You'll have to be more clever.") + '</p><button onclick="Smoothbox.close()">Close</button></div>');
      return;
    }
    $("aaf-twitter_aaf_composer_loading").setStyle('display', 'block');
  } else {
    $("aaf_composer_loading").setStyle('display', 'block');
  }
  if (!dumpComposeInstance) {
    dumpComposeInstance = composeInstance;
  }
  dumpComposeInstance.saveContent();
  currentSearchParams = dumpComposeInstance.getForm().toQueryString().parseQueryString();
  var request = new Request.JSON({
    url: submitUri,
    data: $merge({
      format: 'json',
      is_ajax: 1,
      activity_type: activity_type,
      method: 'post',
      subject: en4.core.subject.guid,
      feedSettings: window.feedAafSettings,
    }, currentSearchParams),
    onFailure: function (xhr) { //XMLHTTPREQUEST
      en4.core.showError("<div class='aaf_show_popup'><p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button></div>');
      active_submitrequest = 1;
    },
    onSuccess: function (responseJSON) {
      active_submitrequest = 1;

      dumpComposeInstance.fireEvent('editorSubmitAfter');
      if ($type(responseJSON) && responseJSON.post_fail == 1) {
        en4.core.showError("<div class='aaf_show_popup'><p>" + en4.core.language.translate("The post was not added to the feed. Please check your privacy settings.") + '</p><button onclick="Smoothbox.close()">Close</button></div>');
      } else if ($type(responseJSON) && responseJSON.status == false) {
        en4.core.showError("<div class='aaf_show_popup'><p>" + responseJSON.error + '</p><button onclick="Smoothbox.close()">Close</button></div>');
      } else {
        dumpComposeInstance.fireEvent('editorSubmitSucess');
        if (activity_type == 1) {
          if ($('activity-feed') && typeof advancedactivityUpdateHandler != 'undefined' && typeof aaf_last_id != 'undefined' && aaf_last_id != 0) {
            $("feed-update").empty();
            document.title = advancedactivityUpdateHandler.title;

            var htmlBody;
            // Get response
            if (responseJSON.feed_stream) { // HTML response

              advancedactivityUpdateHandler.options.last_id = responseJSON.last_id;
              htmlBody = responseJSON.feed_stream;
              if (htmlBody)
                htmlBody.stripScripts(true);
              Elements.from(htmlBody).reverse().inject($('activity-feed'), 'top');
              Smoothbox.bind($('activity-feed'));
              en4.core.runonce.trigger();
            }
          } else {
            showDefaultContent();
          }
          $("aaf_composer_loading").setStyle('display', 'none');
          dumpComposeInstance.plugins.each(function (plugin) {
            plugin.detach();
            if (plugin.name == 'advanced_facebook' || plugin.name == 'advanced_twitter' || plugin.name == 'advanced_linkedin' || plugin.name == 'advanced_instagram') {
              plugin.attach();
            }
          });
        } else if (activity_type == 2) {

          var htmlBody;
          var divwrapper;
          // Get response
          if (responseJSON.feed_stream) { // HTML response
            htmlBody = responseJSON.feed_stream;
            if (htmlBody)
              htmlBody.stripScripts(true);
            Elements.from(htmlBody).each(function (element) {
              divwrapper = element;
            });

            Elements.from(divwrapper.innerHTML).reverse().inject(activityFeed_tweet, 'top');
            feedUpdate_tweet.empty();
            $("aaf-twitter_aaf_composer_loading").setStyle('display', 'none');
          }
        }
      }
      resetAAFTextarea();

    }
  });
  request.send();

}

var resetAAFTextarea = function () {
  if (activity_type != 1 && $('activity-post-container')) {
    $('activity-post-container').style.display = 'none';
  }
  if (activity_type == 2) {
    if (window.checkTwitter) {
      checkTwitter();
    }
    $('show_loading_main').style.display = 'none';
    $('aaf-twitter_show_loading_main').set('html', 140);
//    $('compose-submit').innerHTML = en4.core.language.translate('Tweet');
    if (!$('aaf_main_tab_logout')) {
      return;
    }
    $('aaf_main_tab_logout').style.display = action_logout_taken_tweet != 1 ? 'block' : 'none';

    $('aaf_main_tab_logout').innerHTML = '<span onclick="logout_aaftwitter();" title="' + en4.core.language.translate('Disconnect from Twitter') + '"><img src="application/modules/Advancedactivity/externals/images/logout.png" alt="Logout" /></span>';
    return;
  }

  if (activity_type != 1 || !$('activity-post-container')) {
    return;
  }
  $('show_loading_main').style.display = 'block';
  $('activity-post-container').style.display = 'block';
  // $('activity-form').innerHTML = formhtml;
  composeInstance.reset();
  if ($('aaf_main_tab_logout'))
    $('aaf_main_tab_logout').style.display = 'none';
  if ($$('.advancedactivity_privacy_list'))
    $$('.advancedactivity_privacy_list').setStyle('display', 'inline-block');
  if ($('composer_facebook_toggle')) {
    $('composer_facebook_toggle').removeClass('composer_facebook_toggle_active');
    var spanelement = $('composer_facebook_toggle').getElement('.aaf_composer_tooltip');
    spanelement.innerHTML = en4.core.language.translate('Publish this on Facebook') + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
  }
  if ($('composer_twitter_toggle')) {
    $('composer_twitter_toggle').removeClass('composer_twitter_toggle_active');
    var spanelement = $('composer_twitter_toggle').getElement('.aaf_composer_tooltip');
    spanelement.innerHTML = en4.core.language.translate('Publish this on Twitter') + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
  }

  if ($('composer_linkedin_toggle')) {
    $('composer_linkedin_toggle').removeClass('composer_linkedin_toggle_active');
    var spanelement = $('composer_linkedin_toggle').getElement('.aaf_composer_tooltip');
    spanelement.innerHTML = en4.core.language.translate('Publish this on LinkedIn') + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
  }
  if (window.checkFB) {
    checkFB();
  }

  if (window.checkTwitter) {
    checkTwitter();
  }

  if (window.checkLinkedin) {
    checkLinkedin();
  }

  if ($('adv_post_container_icons'))
    $('adv_post_container_icons').setStyle('display', 'block');
  if ($$('.adv_post_add_user'))
    $$('.adv_post_add_user').setStyle('display', 'block');
  if ($('emoticons-button'))
    $('emoticons-button').setStyle('display', 'block');
  if ($('compose-checkin-activator'))
    $('compose-checkin-activator').setStyle('display', 'block');
  if (composeInstance.options.template == 'activator_buttons')
    composeInstance.getMenu().addClass('dnone');

  $('show_loading_main').style.display = 'none';
  $('compose-submit').innerHTML = Share_Translate;

  if ($('adv_post_container_icons').hasClass('adv_post_compose_menu_anactive'))
    $('adv_post_container_icons').removeClass('adv_post_compose_menu_anactive');
  composeInstance.fireEvent('editorReset');
  composeInstance.elements.textarea.setStyle('display', 'none');
  composeInstance.elements.body.setStyle('display', '');
  if ($('adv_post_container_icons').get('data-expand-always') == 0) {
    $('adv_post_container_icons').removeClass('composer_activator_expand_more_options');
    $('adv_post_container_icons').addClass('composer_activator_collapse_more_options');
  }

  if ($('compose-feeling-container-feeling')) {
    $('compose-feeling-container-feeling').addClass('dnone');
  }
  if ($('feed_box')) {
    $('feed_box').removeClass("feed_box");
  }
  if ($('aaf_feed_box_container')) {
    $('aaf_feed_box_container').removeClass('aaf_feed_box_container_active');
  }

  en4.advancedactivity.resetTargetPost(true);
  en4.advancedactivity.resetScheduleTime();
  composeInstance.getForm().removeClass('compose-container-text-decoration');

//  if ($$('.adv_post_close')[0])
//    $$('.adv_post_close')[0].addClass('dnone');
}

var getLinkContent = function (event) {
  //var hasActive = composeInstance.hasActivePlugin(); 

  // IF TWITTER IS ACTIVE THEN WE WILL NOT DETECTE LINK AUTO.
  if (activity_type == 2)
    return;

  if (composeInstance.pluginReady)
    return;

  var content = composeInstance.getContent();
  content = content ? content.replace('&nbsp;', ' ') : '';

  if (!content)
    return;
  var splitContent = content.split(" ");
  var uri_link = '', i = (splitContent.length - 1), matcheslink;

  for (i; i >= 0; i--) {
    uri_link = splitContent[i].trim();
    matcheslink = uri_link.match(/(https?\:\/\/|www\.)+([a-zA-Z0-9._-]+\.[a-zA-Z.]{2,5})?[^\s]*/i);
    if (matcheslink && matcheslink.length > 0) {
      break;
    }
  }

  var matchesvideolink = uri_link.match(/(www\.|)youtube\.com\/watch/ig) || uri_link.match(/(www\.|)youtu\.be/ig) || uri_link.match(/(www\.|)vimeo\.com\/[0-9]{1,}/ig);
  if (!matchesvideolink) {
    if (!matcheslink) {
      return;
    }
    if (matcheslink.length != 3) {
      return;
    }
    if (!matcheslink[0] || !matcheslink[1] || !matcheslink[2]) {
      return;
    }
  }
  var linkPlugin = composeInstance.getPlugin('link');
  var videoPlugin = composeInstance.getPlugin('video');
  var sitepagevideoPlugin = composeInstance.getPlugin('sitepagevideo');
  var sitebusinessvideoPlugin = composeInstance.getPlugin('sitebusinessvideo');
  var sitegroupvideoPlugin = composeInstance.getPlugin('sitegroupvideo');
  var sitestorevideoPlugin = composeInstance.getPlugin('sitestorevideo');
  // Add in page video
  if (sitepagevideoPlugin && matchesvideolink) {
    sitepagevideoPlugin.activate();
    var videoType = 1;
    if (uri_link.match(/(www\.|)vimeo\.com\/[0-9]{1,}/ig))
      videoType = 2;
    $("compose-sitepagevideo-form-type").options[videoType].selected = true;
    sitepagevideoPlugin.updateSitepagevideoFields.bind(sitepagevideoPlugin)();
    sitepagevideoPlugin.elements.formInput.value = matcheslink[0];
    sitepagevideoPlugin.doAttach();
    sitepagevideoPlugin.active = true;
    // deactivate_plugins();
  } else if (sitebusinessvideoPlugin && matchesvideolink) {
    sitebusinessvideoPlugin.activate();
    var videoType = 1;
    if (uri_link.match(/(www\.|)vimeo\.com\/[0-9]{1,}/ig))
      videoType = 2;
    $("compose-sitebusinessvideo-form-type").options[videoType].selected = true;
    sitebusinessvideoPlugin.updateSitebusinessvideoFields.bind(sitepagevideoPlugin)();
    sitebusinessvideoPlugin.elements.formInput.value = matcheslink[0];
    sitebusinessvideoPlugin.doAttach();
    sitebusinessvideoPlugin.active = true;
    // deactivate_plugins();
  } else if (sitegroupvideoPlugin && matchesvideolink) {
    sitegroupvideoPlugin.activate();
    var videoType = 1;
    if (uri_link.match(/(www\.|)vimeo\.com\/[0-9]{1,}/ig))
      videoType = 2;
    $("compose-sitegroupvideo-form-type").options[videoType].selected = true;
    sitegroupvideoPlugin.updateSitegroupvideoFields.bind(sitegroupvideoPlugin)();
    sitegroupvideoPlugin.elements.formInput.value = matcheslink[0];
    sitegroupvideoPlugin.doAttach();
    sitegroupvideoPlugin.active = true;
    // deactivate_plugins();
  } else if (sitestorevideoPlugin && matchesvideolink) {
    sitestorevideoPlugin.activate();
    var videoType = 1;
    if (uri_link.match(/(www\.|)vimeo\.com\/[0-9]{1,}/ig))
      videoType = 2;
    $("compose-sitestorevideo-form-type").options[videoType].selected = true;
    sitestorevideoPlugin.updateSitestorevideoFields.bind(sitestorevideoPlugin)();
    sitestorevideoPlugin.elements.formInput.value = matcheslink[0];
    sitestorevideoPlugin.doAttach();
    sitestorevideoPlugin.active = true;
    // deactivate_plugins();
  } else if (videoPlugin && matchesvideolink) {
    videoPlugin.activate();
    var videoType = 1;
    if (uri_link.match(/(www\.|)vimeo\.com\/[0-9]{1,}/ig))
      videoType = 2;
    $("compose-video-form-type").options[videoType].selected = true;
    videoPlugin.updateVideoFields.bind(videoPlugin)();
    videoPlugin.elements.formInput.value = matcheslink[0];
    videoPlugin.doAttach();
    videoPlugin.active = true;
    // deactivate_plugins();
  } else if (linkPlugin && !linkPlugin.active) {
    linkPlugin.activate();
    linkPlugin.elements.formInput.value = matcheslink[0];
    linkPlugin.doAttach();
    linkPlugin.active = true;

    // deactivate_plugins();
  }

}

var current_activeplugin;
var doAttachment = function () {
}

var deactivate_plugins = function () {
}

var create_tooltip = function (plugin_temp) {
}

var hidestatusbox = function () {
  if (typeof composeInstance != 'undefined') {
    resetAAFTextarea();
    setTimeout(function () {
      composeInstance.getForm().removeClass('adv-active');
      if ($('aaf_feed_box_container')) {
        $('aaf_feed_box_container').removeClass('aaf_feed_box_container_active');
      }
    }, 100);
  }
}
// End Composer JS

var aaffeedOnScroll, facebookOnScroll, twitterOnScroll, linkedinOnScroll, instagramOnScroll;
var tabSwitchAAFContent = function (element, type) {
  //if(aafReqActive)return;
  if (element.tagName.toLowerCase() == 'a') {
    element = element.getParent('li');
  }
  var element_id = "aaf_main_contener_feed_" + activity_type;
  if ($(element_id))
    $(element_id).style.display = "none";
  if ($('aaf_main_tab_logout'))
    $('aaf_main_tab_logout').style.display = "none";
  $("aaf_main_container_lodding").style.display = "none";
  if (activity_type == 1) {
    if (autoScrollFeedAAFEnable)
      aaffeedOnScroll = window.onscroll;
  } else if (activity_type == 2) {
    if (autoScrollFeedAAFEnable)
      twitterOnScroll = window.onscroll;
  }
  var myContainer = element.getParent('.aaf_main_tabs_feed');
  myContainer.getElements('ul > li').removeClass('aaf_tab_active');
  element.addClass('aaf_tab_active');
  var activityfeedtype;
  if (type == "aaffeed") {
    activity_type = 1;
    activityfeedtype = 'site';
    if ($('activity-post-container'))
      $('activity-post-container').style.display = 'block';
  } else if (type == "twitter") {
    if ($('activity-post-container'))
      $('activity-post-container').style.display = 'none';
    activity_type = 2;
    activityfeedtype = 'twitter';
    if ($('aaf_main_tab_logout'))
      $('aaf_main_tab_logout').innerHTML = '<span onclick="logout_aaftwitter();" title="' + en4.core.language.translate('Disconnect from Twitter') + '"><img src="application/modules/Advancedactivity/externals/images/logout.png" alt="Logout" /></span>';
    if (action_logout_taken_tweet != 1) {
      $('aaf_main_tab_logout').style.display = 'block';

    } else {
      $('aaf_main_tab_logout').style.display = 'none';
      if ($('aaf_main_tab_refresh'))
        $('aaf_main_tab_refresh').style.display = 'none';
    }
  } else if (type == "welcome") {
    activity_type = 4;
    activityfeedtype = 'welcome';
  }
  element_id = "aaf_main_contener_feed_" + activity_type;
  if (!$(element_id)) {
    showDefaultContent();
  } else {
    if (activity_type == 2) {
      if ($('aaf_main_tab_refresh'))
        $('aaf_main_tab_refresh').style.display = 'block';
    } else if (activity_type == 4) {
      if ($('aaf_main_tab_refresh'))
        $('aaf_main_tab_refresh').style.display = 'none';
    }

    $(element_id).style.display = "block";
    Smoothbox.bind($(element_id));

    resetAAFTextarea();
    if (activity_type == 1) {
      if (autoScrollFeedAAFEnable)
        window.onscroll = aaffeedOnScroll;
      if ($('update_advfeed_blink') && $('update_advfeed_blink').style.display == 'block') {
        if (typeof previousActionFilter == "undefined" || previousActionFilter == 'all') {
          if (typeof advancedactivityUpdateHandler != 'undefined')
            advancedactivityUpdateHandler.getFeedUpdate(advancedactivityUpdateHandler.options.last_id);
        } else {
          getTabBaseContentFeed('all', '0');
          if (typeof activeAAFAllTAb != 'undefined')
            activeAAFAllTAb();
        }
        $("feed-update").empty();
        $("feed-update").style.display = "none";
      }


    } else if (activity_type == 2) {
      if (autoScrollFeedAAFEnable)
        window.onscroll = twitterOnScroll;
      if (action_logout_taken_tweet == 0 && !$type($('feed-update-tweet'))) {
        showDefaultContent();
      } else if ($('update_advfeed_tweetblink') && $('update_advfeed_tweetblink').style.display == 'block') {
        if (typeof activityUpdateHandler_Tweet != 'undefined')
          activityUpdateHandler_Tweet.getFeedUpdate(activityUpdateHandler_Tweet.options.last_id);

      }
    }

  }
  if (history.pushState)
    history.pushState({}, document.title, current_window_url + "?activityfeedtype=" + activityfeedtype);
}

var aaf_feed_actionId, show_likes = 0, show_comments = 0;
var showDefaultContent = function () {
  if (activity_type == 4) {
    showDefaultContent_Welcome();
  } else {
    var current_tab_type = activity_type;
    var URL = null;
    var action_id = 0;
    if ($('activity-post-container'))
      $('activity-post-container').style.display = 'none';
    if ($('aaf_main_tab_refresh'))
      $('aaf_main_tab_refresh').style.display = 'none';
    if (activity_type == 1) {
      countScrollAAFSocial = 0;
      URL = en4.core.baseUrl + 'widget/index/name/advancedactivity.feed';
      if (typeof aaf_feed_actionId != 'undefined')
        action_id = aaf_feed_actionId;
    } else if (activity_type == 2) {
      countScrollAAFTweet = 0;
      URL = en4.core.baseUrl + 'widget/index/name/advancedactivity.advancedactivitytwitter-userfeed';
    } else if (activity_type == 4) {
      URL = en4.core.baseUrl + 'advancedactivity/index/welcometab';
    }
    $("aaf_main_container_lodding").style.display = "block";
    var element_id = "aaf_main_contener_feed_" + activity_type;
    if ($(element_id))
      $(element_id).style.display = "none";

    var request = new Request.HTML({
      url: URL,
      data: {
        format: 'html',
        homefeed: true,
        subject: en4.core.subject.guid,
        action_id: action_id,
        show_likes: show_likes,
        show_comments: show_comments,
        feedSettings: window.feedAafSettings
      },
      evalScripts: true,
      onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {

        var element_id = "aaf_main_contener_feed_" + current_tab_type;
        var element;
        if ($(element_id)) {
          element = $(element_id);
        } else {
          element = new Element('div', {
            'id': element_id,
            'styles': {
              'display': 'none'
            }
          }).inject($('adv_activityfeed'));
        }
        $("aaf_main_container_lodding").style.display = "none";
        element.innerHTML = responseHTML;
        en4.core.runonce.trigger();
        Smoothbox.bind(element);
        if (element && current_tab_type == activity_type) {
          element.style.display = "block";
        }
        setContentAfterLoad(current_tab_type);
      }
    });
    request.send();
  }
}

var setContentAfterLoad = function (current_tab_type) {
  if (current_tab_type == 1) {
    if (typeof composeInstance != 'undefined')
      resetAAFTextarea();
    if ($('aaf_main_tab_refresh'))
      $('aaf_main_tab_refresh').style.display = 'block';
  } else {
    if ($('aaf_main_tab_refresh'))
      $('aaf_main_tab_refresh').style.display = 'none';
  }
  if (current_tab_type == 2) {

    if (window.getCommonTweetElements) {
      getCommonTweetElements();
    }
    if (!$type(activityUpdateHandler_Tweet) && update_freq_tweet != 0) {
      Call_TweetcheckUpdate(firstfeedid_Tweet);
    }
  } else if (current_tab_type == 1 && typeof update_freq_aaf != 'undefined') {
    Call_aafcheckUpdate();
    en4.core.runonce.trigger();
  }
  //showDefaultContent();
}

var showDefaultContent_Welcome = function () {

  var current_tab_type = activity_type;
  var action_id = false;
  if ($('activity-post-container'))
    $('activity-post-container').style.display = 'none';
  if ($('aaf_main_tab_refresh'))
    $('aaf_main_tab_refresh').style.display = 'none';

  $("aaf_main_container_lodding").style.display = "block";
  var element_id = "aaf_main_contener_feed_" + activity_type;
  if ($(element_id))
    $(element_id).style.display = "none";
  var element;
  if ($(element_id)) {
    element = $(element_id);
  } else {
    element = new Element('div', {
      'id': element_id,
      'styles': {
        'display': 'none'
      }
    }).inject($('adv_activityfeed'));
  }


  var request = new Request.JSON({
    url: en4.core.baseUrl + 'advancedactivity/index/index',
    data: {
      format: 'json',
      homefeed: true,
      activity_type: activity_type,
      subject: en4.core.subject.guid,
      action_id: action_id,
      feedSettings: window.feedAafSettings
    },
    evalScripts: true,
    onSuccess: function (response, response2, response3, response4) {
      var htmlBody;
      // Get response
      if ($type(response) == 'object') { // JSON response
        htmlBody = response['body'];
      }
      if (!response) {
        en4.core.showError('An error has occurred processing the request. The target may no longer exist.');
        return;
      }
      var element_id = "aaf_main_contener_feed_" + current_tab_type;
      var element;
      if ($(element_id)) {
        element = $(element_id);
      } else {
        element = new Element('div', {
          'id': element_id,
          'styles': {
            'display': 'none'
          }
        }).inject($('adv_activityfeed'));
      }
      $("aaf_main_container_lodding").style.display = "none";
      element.innerHTML = htmlBody;
      Smoothbox.bind(element);
      if (element && current_tab_type == activity_type) {
        element.style.display = "block";
      }
      if ($('activity-post-container'))
        $('activity-post-container').style.display = 'none';
      if ($('aaf_main_tab_refresh'))
        $('aaf_main_tab_refresh').style.display = 'none';

    }
  });


  en4.core.request.send(request, {
    'force': true,
    'element': element
  });

}
var editPostStatusPrivacy = function (el, privacy) {
  if (en4.core.request.isRequestActive())
    return;
  var action_id = 'edit';
  switch (privacy) {
    case "custom_0":
      en4.core.showError('<div class=\'aaf_show_popup\'><div class=\'tip\'><span>You have currently not organized your friends into lists. To create new friend lists, go to the "Friends" section of your profile."</span></div><div><button onclick="Smoothbox.close()">Close</button></div></div>');
      $("TB_window").setStyle('zIndex', 1050);
      break;
    case "custom_1":
      en4.core.showError('<div class=\'aaf_show_popup\'><div class=\'tip\'><span>You have currently created only one list to organize your friends. Create more friend lists from the "Friends" section of  your profile."</span></div><div><button onclick="Smoothbox.close()">Close</button></div></div>');
      $("TB_window").setStyle('zIndex', 1050);
      break;
    case "custom_2":
      Smoothbox.open(en4.core.baseUrl + 'advancedactivity/index/add-more-list?action_id=' + action_id);
      $("TB_window").setStyle('zIndex', 1050);
      break;
    case "network_custom":
      Smoothbox.open(en4.core.baseUrl + 'advancedactivity/index/add-more-list-network?action_id=' + action_id);
      $("TB_window").setStyle('zIndex', 1050);
      break;
    default:
      var oldValue = $('aaf_edit_auth_view').value;
      var oldValueArray = oldValue.split(",");
      for (var i = 0; i < oldValueArray.length; i++) {
        var tempListElement = $('aaf_edit_privacy_list_' + oldValueArray[i]);
        tempListElement.removeClass('aaf_tab_active').addClass('aaf_tab_unactive');
      }
      var tempListElement = $('aaf_edit_privacy_list_' + privacy);
      tempListElement.addClass('aaf_tab_active').removeClass('aaf_tab_unactive');
      var iconClass = el.getElement('i').get('class');
      $('show_aaf_edit_privacy').innerHTML = '<i class="' + iconClass + ' "></i><span>' + el.getElement('div').get('html') + '</span><i class="aaf_privacy_pulldown_arrow"></i>';



      $("adv_edit_privacy_lable_tip").innerHTML = el.getElement('div').get('title');
      $('aaf_edit_auth_view').set('value', privacy);
  }
}


// Insert Webcam
window.addEvent('domready', function () {
  if ((typeof _is_webcam_enable != 'undefined') && (typeof _aaf_webcam_type != 'undefined')) {
    setTimeout(function () {
      if ((_is_webcam_enable == 1) && $('compose-photo-activator') && (_aaf_webcam_type == 0)) {
        $('compose-photo-activator').addEvent('click', function () {
          setTimeout("aafWebcam('compose-tray', 0)", 100);
        });
      } else if ((_is_webcam_enable == 1) && $('compose-sitepagephoto-activator') && (_aaf_webcam_type == 1)) {
        $('compose-sitepagephoto-activator').addEvent('click', function () {
          setTimeout("aafWebcam('compose-sitepagephoto-body', 1)", 100);
        });
      } else if ((_is_webcam_enable == 1) && $('compose-sitebusinessphoto-activator') && (_aaf_webcam_type == 2)) {
        $('compose-sitebusinessphoto-activator').addEvent('click', function () {
          setTimeout("aafWebcam('compose-sitebusinessphoto-body', 2)", 100);
        });
      } else if ((_is_webcam_enable == 1) && $('compose-sitegroupphoto-activator') && (_aaf_webcam_type == 3)) {
        $('compose-sitegroupphoto-activator').addEvent('click', function () {
          setTimeout("aafWebcam('compose-sitegroupphoto-body', 3)", 100);
        });
      } else if ((_is_webcam_enable == 1) && $('compose-sitestorephoto-activator') && (_aaf_webcam_type == 4)) {
        $('compose-sitestorephoto-activator').addEvent('click', function () {
          setTimeout("aafWebcam('compose-sitestorephoto-body', 3)", 100);
        });
      }
    }, 300);
  }
});


function aafWebcam(web_class, type) {
    if (!document.getElementById('compose-webcam-body') && $(web_class)) {
        var activator = $(web_class).getParent();
        var composePhotoMenu = null;
        if(web_class !== 'compose-tray' && activator) {
            composePhotoMenu = activator.getFirst('div');
        }else if(web_class === 'compose-tray') {
            composePhotoMenu = $(web_class).getFirst('div');
        }else{
            return;
        }
    
        composePhotoMenu.querySelectorAll("a")[0].destroy();
        var webcamURL = "'" + en4.core.baseUrl + 'advancedactivity/index/webcamimage?webcam_type=album_photo&aaf_type=' + type + '&subject_id=' + _subject_id + '' + "'";
        var insertWebcam = new Element('div', {
          'id': 'compose-webcam-body',
          'class': 'compose-webcam-body aaf-compose-menu_align'
        });
        insertWebcam.innerHTML = '<span class="aaf_media_sep"> ' + en4.core.language.translate("OR") + '</span><a class="aaf_icon_webcam" href="javascript: void(0);" onClick="uploadImage(' + webcamURL + ')"> ' + en4.core.language.translate("Use Webcam") + ' </a>';
        insertWebcam.inject($(web_class));
        $('compose-webcam-body').inject(composePhotoMenu, 'bottom');
        new Element('a', {
          'class': 'aaf-composer-cancel-menu',
          'href': 'javascript:void(0)',
          'onclick': 'resetAAFTextarea()',
          'html': en4.core.language.translate("cancel")
        }).inject(composePhotoMenu, 'bottom');
  }
}

function uploadImage(url) {
  Smoothbox.open(url);
}

function basicAafActivator(menu_name) {
  composeInstance.deactivate();
  $$('.compose-menu').removeClass('active');
  $('compose-' + menu_name + '-menu-link').addClass('active');
}
function showAafPhotoActivator() {
  basicAafActivator('photo');
  composeInstance.getPlugin('photo').activate();
  $('compose-photo-menu-link').addClass('active');
  setTimeout("aafWebcam('compose-tray', 0)", 100);
}

function showAafVideoActivator() {
  basicAafActivator('video');
  composeInstance.getPlugin('video').activate();
  $('compose-video-menu-link').addClass('active');
}

function showAafDefaultActivator() {
  basicAafActivator('status');
  if ($$('.compose-container')[0].getElement('.overTxtLabel')) {
    $$('.compose-container')[0].getElement('.overTxtLabel').innerHTML = en4.core.language.translate('Post Something...');
  }
}
window.onbeforeunload = function (event) {
  if (DetectMobileQuick() || (activity_type == 1 && postbyAjax == 0)) {
    return;
  }
  if (composeInstance.pluginReady || composeInstance.getContent().trim() != '') {
    return en4.core.language.translate(' You have not finished your post yet. Do you want to leave without finishing it? ');
  }
};
