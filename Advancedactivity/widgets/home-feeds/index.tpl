<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script>
   window.feedAafSettings = <?php echo $this->jsonInline($this->widgetParams); ?>;
</script>
<?php if( !empty($this->widgetParams['pinboardColumn']) ) : ?>
  <?php
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/pinboard/mooMasonry.js');
  ?>
<script type="text/javascript">
    if(en4.advancedactivity) {
      en4.advancedactivity.pinboard.init();
    } else {
      en4.core.runonce.add(function(){
        en4.advancedactivity.pinboard.init();
      });
    }
</script>
<?php endif; ?>
<?php
if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepagemusic')) :
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'externals/soundmanager/script/soundmanager2'
                    . (APPLICATION_ENV == 'production' ? '-nodebug-jsmin' : '' ) . '.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitepagemusic/externals/scripts/core.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitepagemusic/externals/scripts/player.js');
endif;

if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitebusinessmusic')) :
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'externals/soundmanager/script/soundmanager2'
                    . (APPLICATION_ENV == 'production' ? '-nodebug-jsmin' : '' ) . '.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitebusinessmusic/externals/scripts/core.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitebusinessmusic/externals/scripts/player.js');
endif;

if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroupmusic')) :
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'externals/soundmanager/script/soundmanager2'
                    . (APPLICATION_ENV == 'production' ? '-nodebug-jsmin' : '' ) . '.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitegroupmusic/externals/scripts/core.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitegroupmusic/externals/scripts/player.js');
endif;

$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/core.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-facebookse.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-twitter.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/advancedactivity-linkedin.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js');
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
$this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/animocons/mo.min.js')
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/animocons/animations.js')
            ;

if (!empty($this->is_welcomeTabEnabled) && !empty($this->is_suggestionEnabled)) {
    $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Suggestion/externals/scripts/core.js');
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/usercontacts.js');
} else if (!empty($this->is_welcomeTabEnabled) && !empty($this->is_pymkEnabled)) {
    $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Peopleyoumayknow/externals/scripts/core.js');
    $this->headScript()
            ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/usercontacts.js');
}

$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Activity/externals/scripts/core.js');

$this->videoPlayerJs();

$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Seaocore/externals/styles/style_infotooltip.css');

$this->headTranslate(array('ADVADV_SHARE', 'Who are you with?', 'with', "Choose places to publish on Facebook", "Publish this post on Facebook %1s linked with this %2s.", "Publish this post on my Facebook Timeline."));
?>
<?php if (Engine_Api::_()->seaocore()->checkEnabledNestedComment('advancedactivity')): ?>
<?php echo $this->hooks('onRenderNestedcommentFeeds', $this); ?>
<?php endif; ?>
<?php $title = $this->settings('advancedactivity.sitetabtitle', "What's New!"); ?>
<?php if ($this->count_tabs == 1 && empty($this->title) && empty($this->hide) && $title): ?>
<h3> <?php echo $this->translate($title); ?></h3>
<?php endif; ?>

<!--SHOW SITE ACTIVITY FEED.-->
 <?php if ($this->count_tabs > 1): ?>
<div class="aaf_tabs aaf_main_tabs_feed <?php if ($this->count_tabs <= 1): ?> dnone <?php endif; ?>" id="aaf_main_tabs_feed">
   
    <ul class="aaf_tabs_apps">
      <?php if ($this->isWelcomeEnable): ?>
        <li <?php if ($this->activeTab == 4): ?> class="aaf_tab_active" <?php endif; ?> id="Welcometab_activityfeed" title="<?php echo $this->translate("Welcome"); ?>">
            <a href="javascript:void(0);" class='' onclick="tabSwitchAAFContent($(this), 'welcome');" >
              <?php if( 1 & $this->tabtype ): ?>
                <?php
                $photoName = $this->baseUrl() . '/' . $this->settings('advancedactivity_icon1', 'application/modules/Advancedactivity/externals/images/welcome-icon.png');
                ?>
                <img src="<?php echo $photoName ?>" alt="" <?php if( 2 & $this->tabtype ): ?>class="aaf_main_tabs_icon"<?php endif; ?> />
              <?php endif; ?>
              <?php if( 2 & $this->tabtype ): ?>
                <span class="aaf_main_tabs_txt"><?php echo $this->translate("Welcome"); ?></span>
              <?php endif; ?>
            </a>
        </li>
      <?php endif; ?>
      <?php if ($this->isAaffeedEnable): ?>
        <li <?php if( $this->activeTab == 1 ): ?> class="aaf_tab_active" <?php endif; ?> id="Site_activityfeed" title="<?php echo $this->translate($this->settings('advancedactivity.sitetabtitle', "What's New!")) ?>">
          <a href="javascript:void(0);"   onclick="tabSwitchAAFContent($(this), 'aaffeed');" >
            <span id="update_advfeed_blink" class="notification_star"></span>
            <?php if( 1 & $this->tabtype ): ?>
              <?php
              $photoName = $this->baseUrl() . '/' . $this->settings('advancedactivity_icon', 'application/modules/Advancedactivity/externals/images/web.png');
              ?>
              <img src="<?php echo $photoName ?>" <?php if( 2 & $this->tabtype ): ?>class="aaf_main_tabs_icon"<?php endif; ?> />
            <?php endif; ?>
            <?php if( 2 & $this->tabtype ): ?>
              <span class="aaf_main_tabs_txt">
                <?php echo $this->translate($this->settings('advancedactivity.sitetabtitle', "What's New!"));?>
              </span>
            <?php endif; ?>
          </a>
        </li>
      <?php endif; ?>

      <?php if ($this->isFacebookEnable && empty($this->FBloginURL) && !empty($this->web_values)): ?>
        <li <?php if( $this->activeTab == 3 ): ?> class="aaf_tab_active" <?php endif; ?> id="Facebook_activityfeed" title="<?php echo $this->translate("Facebook"); ?>" >
          <a href="javascript:void(0);" class='' onclick="javascript:tabSwitchAAFContent($(this), 'facebook');" >
            <span id="update_advfeed_fbblink" class="notification_star"></span>
                <?php if( 1 & $this->tabtype ): ?>
                  <i class="aaf_tabs_icon aaf_icon_facebook <?php if( 2 & $this->tabtype ): ?>aaf_main_tabs_icon <?php endif; ?>"></i>
                <?php endif; ?>
                <?php if( 2 & $this->tabtype ): ?>
                  <span class="aaf_main_tabs_txt"><?php echo $this->translate("Facebook"); ?></span>
                <?php endif; ?>
              </a>
            </li>
      <?php endif; ?>

      <?php if( $this->isTwitterEnable && empty($this->TwitterLoginURL) && !empty($this->web_values) ): ?>
        <li <?php if( $this->activeTab == 2 ): ?> class="aaf_tab_active" <?php endif; ?> id="Twitter_activityfeed" title="<?php echo $this->translate("Twitter"); ?>" >
          <a href="javascript:void(0);" class='' onclick="javascript:tabSwitchAAFContent($(this), 'twitter');" >
            <span id="update_advfeed_tweetblink" class="notification_star"></span>
            <?php if( 1 & $this->tabtype ): ?>
              <i class="aaf_tabs_icon aaf_icon_twitter <?php if( 2 & $this->tabtype ): ?>aaf_main_tabs_icon <?php endif; ?>" title='<?php echo $this->translate("Twitter"); ?>'></i>
            <?php endif; ?>
            <?php if( 2 & $this->tabtype ): ?>
              <span class="aaf_main_tabs_txt"><?php echo $this->translate("Twitter"); ?></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endif; ?>

      <?php if ($this->isTwitterEnable && !empty($this->TwitterLoginURL) && !empty($this->web_values) && in_array("twitter", $this->web_values)): ?>
        <li <?php if ($this->activeTab == 2): ?> class="aaf_tab_active" <?php endif; ?>id="Twitter_activityfeed" <?php if ($this->tabtype == 1): ?> title="<?php echo $this->translate("Twitter"); ?>" <?php endif; ?> >
            <a href="javascript:void(0);" onclick="AAF_ShowFeedDialogue_Tweet('<?php echo $this->TwitterLoginURL; ?>')" title="<?php echo $this->translate("Connect to Twitter"); ?>">
              <?php if (1 & $this->tabtype && !(2 & $this->tabtype)): ?>
                <i class="aaf_tabs_icon aaf_icon_twitter_add"></i>
              <?php elseif (2 & $this->tabtype && !(1 & $this->tabtype)): ?> 
                <i class="aaf_tabs_icon aaf_icon_app_add aaf_main_tabs_icon"></i>
                <span class="aaf_main_tabs_txt"><?php echo $this->translate("Twitter"); ?></span>
              <?php else: ?>
                <i class="aaf_tabs_icon aaf_icon_twitter_add aaf_main_tabs_icon"></i>
                <span class="aaf_main_tabs_txt"><?php echo $this->translate("Twitter"); ?></span>
              <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
          <li class="aaf_apps_op_wrapper">
            <div class="aaf_apps_ops_cont">
              <div class="aaf_apps_ops" id="aaf_main_tab_refresh" style="display: none;" >
                <span onclick="showDefaultContent();" title="<?php echo $this->translate("Refresh") ?>" ><img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/refresh.png' alt="Refresh" align="left" /></span>
              </div>	
              <div class="aaf_apps_ops" id="aaf_main_tab_logout" style="display:none;">
                <span title="<?php echo $this->translate("Logout"); ?>"><img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/logout.png' alt="Logout" align="left" /></span>
              </div>
            </div>
          </li>
    </ul>
    
</div>
<?php endif; ?>
<?php if ($this->showScrollTopButton): ?>
<a id="back_to_top_feed_button" href="#" class="seaocore_up_button Offscreen" title="<?php echo $this->translate("Scroll to Top"); ?>">
    <span></span>
</a>
<?php endif; ?>

<script type="text/javascript">
    var is_enter_submitothersocial = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.comment.show.bottom.post', 1); ?>";
    var autoScrollFeedAAFEnable = "<?php echo $this->autoScrollFeedEnable ? true : false; ?>";
    var aaf_showImmediately = "<?php echo $this->aafShowImmediately ? true : false; ?>";
    var feedToolTipAAFEnable = "<?php echo!empty($this->composerType) && $this->feedToolTipEnable; ?>";
  var maxAutoScrollAAF = "<?php echo $this->maxAutoScrollFeed ?>";
  var is_welcomeTab_default = 1;
  var current_window_url = '<?php echo (_ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->url() ?>';
  var activity_type = '<?php echo $this->activeTab; ?>';
  en4.core.runonce.add(function () {
<?php if (!empty($this->action_id)): ?>
      aaf_feed_actionId =<?php echo $this->action_id ?>;
      $$(".tab_<?php echo $this->identity ?>").each(function (element) {
          if (element.tagName.toLowerCase() == 'li') {
              tabContainerSwitch(element);
          }
      });

<?php endif; ?>
<?php if (!empty($this->viewAllLikes)): ?>
      show_likes =<?php echo $this->viewAllLikes ?>;
<?php endif; ?>
<?php if (!empty($this->viewAllComments)): ?>
      show_comments =<?php echo $this->viewAllComments ?>;
<?php endif; ?>
      //showDefaultContent();
      setContentAfterLoad(activity_type);
      //hide on body clicdk
      $(document.body).addEvent('click', function (event) {
        if (event && $(event.target).hasClass('aaf_pulldown_btn'))
          return;
        $$(".aaf_pulldown_btn_wrapper").removeClass('aaf_tabs_feed_tab_open').addClass('aaf_tabs_feed_tab_closed');
      });

  });

<?php if (!empty($this->FBloginURL)) : ?>
  fb_loginURL = '<?php echo $this->FBloginURL; ?>';
<?php endif; ?>

<?php if (!empty($this->TwitterLoginURL)) : ?>
  tweet_loginURL = '<?php echo $this->TwitterLoginURL; ?>';
<?php endif; ?>

<?php if (!empty($this->LinkedinloginURL)) : ?>
  linkedin_loginURL = '<?php echo $this->LinkedinloginURL; ?>';
<?php endif; ?>

<?php if (!empty($this->FBloginURL_temp)) : ?>
  fb_loginURL_temp = '<?php echo $this->FBloginURL_temp; ?>';
<?php endif; ?>

<?php if (!empty($this->TwitterLoginURL_temp)) : ?>
  tweet_loginURL_temp = '<?php echo $this->TwitterLoginURL_temp; ?>';
<?php endif; ?>

<?php if (!empty($this->LinkedinloginURL_temp)) : ?>
  linkedin_loginURL_temp = '<?php echo $this->LinkedinloginURL_temp; ?>';
<?php endif; ?>

  if (window.opener != null) {
<?php if (!empty($_GET['redirect_fb'])) : ?>

      if ($type(window.opener.$('compose-facebook-form-input'))) {
          window.opener.$('compose-facebook-form-input').disabled = '';
      }

      if (window.opener.aaf_feed_type_tmp == 3) {

          if ($type(window.opener.$('aaf_main_contener_feed_3'))) {
              window.opener.showDefaultContent();
              window.opener.action_logout_taken_fb = 0;
              if (window.opener.$('aaf_main_tab_logout'))
                  window.opener.$('aaf_main_tab_logout').style.display = 'block';
              if (window.opener.$('aaf_main_tab_refresh'))
                  window.opener.$('aaf_main_tab_refresh').style.display = 'block';

              if (fb_loginURL == '') {
                  if ($type(window.opener.$('Facebook_activityfeed'))) {
                      window.opener.$('Facebook_activityfeed').innerHTML = $('Facebook_activityfeed').innerHTML;
                  }
              }
          } else {
              if ($type(window.opener.$('Facebook_activityfeed'))) {
                  window.opener.$('Facebook_activityfeed').innerHTML = $('Facebook_activityfeed').innerHTML;
              }
              window.opener.tabSwitchAAFContent(window.opener.$('Facebook_activityfeed'), 'facebook');
          }
          window.opener.fb_loginURL = '';
      } else {
          if (fb_loginURL == '') {
              window.opener.$('compose-facebook-form-input').set('checked', !window.opener.$('compose-facebook-form-input').get('checked'));
              window.opener.$('composer_facebook_toggle').removeClass('composer_facebook_toggle_active');
              window.opener.$('composer_facebook_toggle').toggleClass('composer_facebook_toggle_active');
              var spanelement = window.opener.$('composer_facebook_toggle').getElement('.aaf_composer_tooltip');
              spanelement.innerHTML = en4.core.language.translate('Do not publish this on Facebook') + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
              if ($type(window.opener.$('Facebook_activityfeed'))) {
                  window.opener.$('Facebook_activityfeed').innerHTML = $('Facebook_activityfeed').innerHTML;
              }
              window.opener.fb_loginURL = '';
              window.opener.action_logout_taken_fb = 0;
          }
      }
      close();
<?php endif; ?>
<?php if (!empty($_GET['redirect_linkedin'])) : ?>

      if ($type(window.opener.$('compose-linkedin-form-input'))) {
          window.opener.$('compose-linkedin-form-input').disabled = '';
      }

      if (window.opener.aaf_feed_type_tmp == 5) {

          if ($type(window.opener.$('aaf_main_contener_feed_5'))) {
              window.opener.showDefaultContent();
              window.opener.action_logout_taken_linkedin = 0;
              if (window.opener.$('aaf_main_tab_logout'))
                  window.opener.$('aaf_main_tab_logout').style.display = 'block';
              if (window.opener.$('aaf_main_tab_refresh'))
                  window.opener.$('aaf_main_tab_refresh').style.display = 'block';

              if (linkedin_loginURL == '') {
                  if ($type(window.opener.$('Linkedin_activityfeed'))) {
                      window.opener.$('Linkedin_activityfeed').innerHTML = $('Linkedin_activityfeed').innerHTML;
                  }
              }
          } else {
              if ($type(window.opener.$('Linkedin_activityfeed'))) {
                  window.opener.$('Linkedin_activityfeed').innerHTML = $('Linkedin_activityfeed').innerHTML;
              }
              window.opener.tabSwitchAAFContent(window.opener.$('Linkedin_activityfeed'), 'linkedin');
          }
          window.opener.linkedin_loginURL = '';
      } else {
          if (linkedin_loginURL == '') {
              window.opener.$('compose-linkedin-form-input').set('checked', !window.opener.$('compose-linkedin-form-input').get('checked'));
              window.opener.$('composer_linkedin_toggle').removeClass('composer_linkedin_toggle_active');
              window.opener.$('composer_linkedin_toggle').toggleClass('composer_linkedin_toggle_active');
              var spanelement = window.opener.$('composer_linkedin_toggle').getElement('.aaf_composer_tooltip');
              spanelement.innerHTML = en4.core.language.translate('Do not publish this on Linkedin') + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
              if ($type(window.opener.$('Linkedin_activityfeed'))) {
                  window.opener.$('Linkedin_activityfeed').innerHTML = $('Linkedin_activityfeed').innerHTML;
              }
              window.opener.linkedin_loginURL = '';
              window.opener.action_logout_taken_linkedin = 0;
          }
      }
      close();
<?php endif; ?>

<?php if (!empty($_GET['redirect_tweet'])) : ?>

      if ($type(window.opener.$('compose-twitter-form-input'))) {
          window.opener.$('compose-twitter-form-input').disabled = '';
      }
      window.opener.tweet_loginURL = '';
      if (window.opener.aaf_feed_type_tmp == 2) {

          if ($type(window.opener.$('compose-twitter-form-input'))) {
              window.opener.$('compose-twitter-form-input').disabled = '';
          }

          window.opener.tweet_loginURL = '';
          if ($type(window.opener.$('aaf_main_contener_feed_2'))) {
              window.opener.showDefaultContent();
              window.opener.action_logout_taken_tweet = 0;
              if (window.opener.$('aaf_main_tab_logout'))
                  window.opener.$('aaf_main_tab_logout').style.display = 'block';
              if (window.opener.$('aaf_main_tab_refresh'))
                  window.opener.$('aaf_main_tab_refresh').style.display = 'block';
              if ($type(window.opener.$('Twitter_activityfeed'))) {
                  window.opener.$('Twitter_activityfeed').innerHTML = $('Twitter_activityfeed').innerHTML;
              }
          } else {
              if ($type(window.opener.$('Twitter_activityfeed'))) {
                  window.opener.$('Twitter_activityfeed').innerHTML = $('Twitter_activityfeed').innerHTML;
              }
              window.opener.tabSwitchAAFContent(window.opener.$('Twitter_activityfeed'), 'twitter');
          }
      } else {
          if (tweet_loginURL == '') {
              window.opener.$('compose-twitter-form-input').set('checked', !window.opener.$('compose-twitter-form-input').get('checked'));
              window.opener.$('composer_twitter_toggle').removeClass('composer_twitter_toggle_active');
              window.opener.$('composer_twitter_toggle').toggleClass('composer_twitter_toggle_active');
              var spanelement = window.opener.$('composer_twitter_toggle').getElement('.aaf_composer_tooltip');
              spanelement.innerHTML = en4.core.language.translate('Do not publish this on Twitter') + '<img alt="" src="application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" />';
              if ($type(window.opener.$('Twitter_activityfeed'))) {
                  window.opener.$('Twitter_activityfeed').innerHTML = $('Twitter_activityfeed').innerHTML;
              }
          }
          window.opener.tweet_loginURL = '';
          window.opener.action_logout_taken_tweet = 0;
      }
      close();
<?php endif; ?>
  }
</script>

<?php
if( $this->showPosts && $this->enableComposer ):
  echo $this->partial('_aafcomposer.tpl', 'advancedactivity', array_merge($this->widgetParams, array(
    'enableComposer' => $this->enableComposer,
    'showPrivacyDropdown' => $this->showPrivacyDropdown,
    'enableList' => $this->enableList,
    'lists' => $this->lists,
    'countList' => $this->countList,
    'composePartials' => $this->composePartials,
    'settingsApi' => $this->settingsApi,
    'availableLabels' => $this->availableLabels,
    'showDefaultInPrivacyDropdown' => $this->showDefaultInPrivacyDropdown,
    'privacylists' => $this->privacylists,
    'formToken' => $this->formToken,
    'enableNetworkList' => $this->enableNetworkList,
    'network_lists' => $this->network_lists,
    'categoriesList' => $this->categoriesList,
    'showDefault' => $this->activeTab == 1,
    'showTabs' => $this->showTabs,
    'parentType' => $this->parentType,
    'parentId' => $this->parentId,
    'statusBoxDesign' => !empty($this->widgetParams['statusBoxDesign']) ? $this->widgetParams['statusBoxDesign'] : 'activator_buttons'
  )));
endif;
?>
<?php if ($this->viewer()->getIdentity()): ?>
<script type="text/javascript">

  en4.user.viewer.iconUrl = '<?php echo $this->viewer()->getPhotoUrl('thumb.icon'); ?>';
  en4.user.viewer.title = '<?php echo $this->string()->escapeJavascript($this->viewer()->getTitle()); ?>';
  en4.user.viewer.href = '<?php echo $this->string()->escapeJavascript($this->viewer()->getHref()); ?>';

  if (!en4.user.viewer.iconUrl) {
      en4.user.viewer.iconUrl = en4.core.staticBaseUrl + 'application/modules/User/externals/images/nophoto_user_thumb_icon.png';
  }
  en4.advancedactivity.fewSecHTML = '<?php echo str_replace('timestamp-update', 'timestamp-fixed', $this->timestamp(time() - 2)); ?>';
</script>
<?php endif; ?>
<div id="adv_activityfeed">   
    <div id="aaf_main_container_lodding" style="display: none;">
        <div class="aaf_main_container_lodding"></div>
    </div>   
    <div id="aaf_main_contener_feed_<?php echo $this->activeTab ?>">
        <?php if ($this->activeTab == 1): ?>     
            <?php if (!$this->loadByAjax || $this->action_id): ?>
                <?php echo $this->content()->renderWidget("advancedactivity.feed", array("homefeed" => 1, "search" =>$this->search,"showPosts" =>$this->showPosts, "hide" =>$this->hide, "action_id" => $this->action_id, "show_likes" => $this->viewAllLikes, "show_comments" => $this->viewAllComments, "subject" => $this->subjectGuid,'integrateCommunityAdv'=> $this->integrateCommunityAdv, 'feedSettings' => $this->widgetParams)); ?>
            <?php else: ?>
        <script type="text/javascript">
         en4.core.runonce.add(function () {

              $("aaf_main_container_lodding").style.display = "block";

              var request = new Request.HTML({
                  url: en4.core.baseUrl + 'widget/index/name/advancedactivity.feed',
                  data: {
                      format: 'html',
                      'homefeed': true,
                      'action_id': '<?php echo $this->action_id ?>',
                      'show_likes': '<?php echo $this->viewAllLikes ?>',
                      'show_comments': '<?php echo $this->viewAllComments ?>',
                      'subject': '<?php echo $this->subjectGuid ?>',
                      'integrateCommunityAdv': '<?php echo $this->integrateCommunityAdv?>',
                      'feedSettings': window.feedAafSettings
                  },
                  evalScripts: true,
                  onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                      $("aaf_main_container_lodding").style.display = "none";
                      Elements.from(responseHTML).inject($('aaf_main_contener_feed_<?php echo $this->activeTab ?>'));
                      setContentAfterLoad(1);
                      en4.core.runonce.trigger();
                      Smoothbox.bind($('aaf_main_contener_feed_<?php echo $this->activeTab ?>'));
                      if (en4.sitevideolightboxview) {
                          en4.sitevideolightboxview.attachClickEvent(Array('sitevideo_thumb_viewer'));
                      }
                  }
              });
              request.send();
          });
        </script>
            <?php endif; ?>
        <?php elseif ($this->activeTab == 2): ?>
            <?php echo $this->content()->renderWidget("advancedactivity.advancedactivitytwitter-userfeed", array("homefeed" => 1, "subject" => $this->subjectGuid)); ?>
        <?php elseif ($this->activeTab == 3): ?>

            <?php echo $this->content()->renderWidget("advancedactivity.advancedactivityfacebook-userfeed", array("homefeed" => 1, "subject" => $this->subjectGuid)); ?>
        <?php elseif ($this->activeTab == 5): ?>

            <?php echo $this->content()->renderWidget("advancedactivity.advancedactivitylinkedin-userfeed", array("homefeed" => 1, "subject" => $this->subjectGuid)); ?>
        <?php elseif ( FALSE && $this->activeTab == 6): ?>

            <?php echo $this->content()->renderWidget("advancedactivity.advancedactivityinstagram-userfeed", array("homefeed" => 1, "subject" => $this->subjectGuid)); ?>
        <?php elseif ($this->activeTab == 4): ?>
            <?php echo $this->content('advancedactivity_index_welcometab'); ?>
        <?php endif; ?>
    </div>
</div>
<div class="dblock clr" style="height:0;"></div>

<style type="text/css">
  <?php if( !empty($this->widgetParams['customPhotoBlock']) ): ?>
    .feed_item_attachments.aaf_item_attachment_1 > [class*='feed_attachment_'] {
      max-height: <?php echo $this->widgetParams['photo_block_1_height']?>px;
      max-width: <?php echo $this->widgetParams['photo_block_1_width'].$this->widgetParams['photo_block_1_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_2 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_2_height']?>px;
      width: <?php echo $this->widgetParams['photo_block_2_width'].$this->widgetParams['photo_block_2_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_3 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_3_small_height']?>px;
      width: <?php echo $this->widgetParams['photo_block_3_small_width'].$this->widgetParams['photo_block_3_small_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_3 > [class*='feed_attachment_']:first-child {
      height: <?php echo $this->widgetParams['photo_block_3_height']?>px;
      width: <?php echo $this->widgetParams['photo_block_3_width'].$this->widgetParams['photo_block_3_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_4 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_4_small_height']?>px;
      width: <?php echo $this->widgetParams['photo_block_4_small_width'].$this->widgetParams['photo_block_4_small_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_4 > [class*='feed_attachment_']:first-child {
      height: <?php echo $this->widgetParams['photo_block_4_height']?>px;
      width: <?php echo $this->widgetParams['photo_block_4_width'].$this->widgetParams['photo_block_4_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_5 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_5_small_height']?>px;
      width: <?php echo $this->widgetParams['photo_block_5_small_width'].$this->widgetParams['photo_block_5_small_width_unit']?>;
    }
    .feed_item_attachments.aaf_item_attachment_5 > [class*='feed_attachment_']:nth-child(1),
    .feed_item_attachments.aaf_item_attachment_5 > [class*='feed_attachment_']:nth-child(2){
      height: <?php echo $this->widgetParams['photo_block_5_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_5_width'].$this->widgetParams['photo_block_6_width_unit'] ?>;
    }
    .feed_item_attachments.aaf_item_attachment_6 > [class*='feed_attachment_']:nth-child(1),
    .feed_item_attachments.aaf_item_attachment_6 > [class*='feed_attachment_']:nth-child(2){
      height: <?php echo $this->widgetParams['photo_block_6_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_6_width'].$this->widgetParams['photo_block_6_width_unit'] ?>;
    }
    .feed_item_attachments.aaf_item_attachment_6 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_6_small_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_6_small_width'].$this->widgetParams['photo_block_6_small_width_unit'] ?>;
    }

    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(1),
    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(2),
    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(3){
      height: <?php echo $this->widgetParams['photo_block_7_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_7_width'].$this->widgetParams['photo_block_7_width_unit'] ?>;
    }
    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(4),
    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(5),
    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(6),
    .feed_item_attachments.aaf_item_attachment_7 > [class*='feed_attachment_']:nth-child(7) {
      height: <?php echo $this->widgetParams['photo_block_7_small_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_7_small_width'].$this->widgetParams['photo_block_7_small_width_unit'] ?>;
    }

    .feed_item_attachments.aaf_item_attachment_8 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_8_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_8_width'].$this->widgetParams['photo_block_8_width_unit'] ?>;
    }
    .feed_item_attachments.aaf_item_attachment_9 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_9_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_9_width'].$this->widgetParams['photo_block_9_width_unit'] ?>;
    }
    .feed_item_attachments.aaf_item_attachment_10 > [class*='feed_attachment_'] {
      height: <?php echo $this->widgetParams['photo_block_10_height'] ?>px;
      width: <?php echo $this->widgetParams['photo_block_10_width'].$this->widgetParams['photo_block_10_width_unit'] ?>;
    }
  <?php endif; ?>
.adv_post_container .compose-container-text-decoration .adv-photo .adv_post_box .compose-container textarea, 
.adv_post_container .compose-container-text-decoration .adv-photo .adv_post_box .compose-highlighter {
  font-size: <?php echo $this->settings('advancedactivity.feed.font.size',30) ?>px !important;
  color: <?php echo $this->settings('advancedactivity.feed.font.color','#000') ?>;
  font-style: <?php echo $this->settings('advancedactivity.feed.font.style','normal') ?>;
}
</style>
