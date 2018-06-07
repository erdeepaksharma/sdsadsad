<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _aafcomposer.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headScript()
  ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/seaomooscroll/SEAOMooHorizontalScrollBar.js')
;
$this->headLink()
  ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_statusbar.css');
?>
<div class="aaf_feed_box_container" style="margin-bottom: 15px;">
  <div class="adv_post_container nolinks" id="aaf-twitter-post-container" style="" >
    <form method="post" action="" enctype="application/x-www-form-urlencoded" id="aaf-twitter-form">
      <div class="adv_post_container_box seaocore_box_sizing">
        <div class="adv_post_box">
          <textarea id="aaf-tweet_activity_body" cols="1" rows="1" name="body" placeholder="<?php echo $this->escape($this->translate('What\'s happening?')) ?>" ></textarea>
        </div>
        <input type="hidden" name="return_url" value="<?php echo $this->url() ?>" />
        <?php if( $this->viewer() && $this->subject() && !$this->viewer()->isSelf($this->subject()) ): ?>
          <input type="hidden" name="subject" value="<?php echo $this->subject()->getGuid() ?>" />
          <input type="hidden" name="activity_type" value="2" />
        <?php endif; ?>
        <div class="aaf_postbox_options" id="twitter_postbox_options">
          <?php
          echo $this->partial('_emojiBoard.tpl', 'advancedactivity', array(
            'idPrefix' => 'twitter_postbox_'
          ));
          ?>
          <script type="text/javascript">
            en4.core.runonce.add(function () {
              var wapper = $('twitter_postbox_emoticons-nested-comment-icons_emoji');
              wapper.addEvent('seaoEmojiSelected', function (el) {
                window.composeInstanceTweeter.attachEmotionIcon(el, wapper.retrieve('iconPathPrefix'));
              });
            });
          </script>
        </div>
      </div>
      <div class="adv-activeity-post-container-bottom" id="aaf-twitter_compose-menu">
        <div class="advanced_compose-menu-buttons" id="aaf-twitter_compose-menu-buttons">
          <div id="aaf-twitter_show_loading_main" class="show_loading" style="display:none;">140</div>
          <div id="aaf-twitter_aaf_composer_loading" class="show_loading" style="display:none;"><img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/loading.gif' alt="Loading" /></div>
          <button id="aaf-twitter_compose-submit" type="submit"><?php echo $this->translate("Tweet") ?></button>
        </div>
      </div>
    </form>
  </div>
</div>
<div id="aaf_twitter_user_photo">
  <a href= "https://twitter.com/<?php echo $this->screenName; ?>" target="_blank" >
    <img class="item_thumb_icon" src="<?php echo str_replace("http://", "https://", $this->userImageSrc); ?>" alt="" />
  </a>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    window.composeInstanceTweeter = composeInstanceTweeter = new Composer('aaf-tweet_activity_body', {
      menuElement: 'aaf-twitter_compose-menu',
      activatorContent: (new Element('div')),
      trayElement: null,
      topTrayElement: null,
      baseHref: '<?php echo $this->baseUrl() ?>',
      userPhoto: $('aaf_twitter_user_photo').get('html'),
      lang: {
        'Tweet': '<?php echo $this->string()->escapeJavascript($this->translate('Tweet')) ?>'
      },
      hideSubmitOnBlur: true,
      textLimit: '0',
    });
    $('aaf_twitter_user_photo').destroy();
    composeInstanceTweeter.getForm().addEvent('submit', function (e) {
      e.stop();
      composeInstanceTweeter.fireEvent('editorSubmit');
      if (composeInstanceTweeter.getContent().trim() == '') {
        return;
      }
      if (Tweet_lenght) {
        return;
      }
      if (active_submitrequest == 1) {
        active_submitrequest = 2;
        var submitUri = "<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'index', 'action' => 'post'), 'default', true) ?>";
        submitFormAjax(submitUri, composeInstanceTweeter);
      }
    });
    composeInstanceTweeter.addEvent('editorSubmitSucess', function () {
      $('show_loading_main').style.display = 'none';
      $('aaf-twitter_show_loading_main').set('html', 140);
      composeInstanceTweeter.reset();
    });
    bindKeyUpEventForTweetPost();
    if (window.checkTwitter) {
      checkTwitter();
    }
    getCommonTweetElements();
  });
</script>