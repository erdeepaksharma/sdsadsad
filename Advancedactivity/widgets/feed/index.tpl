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
<?php 
    $apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
    $this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
    $firstActionId = $this->firstid;
?>
<script type="text/javascript">
<?php if (!empty($this->getUpdate) || empty($this->feedOnly) || ($this->isFromTab && $this->actionFilter == 'all')) : ?>
  var firstfeedid =<?php echo sprintf('%d', $firstActionId) ?>;
    <?php if ($this->updateSettings && !$this->action_id): ?>
  en4.core.runonce.add(function () {
      if ($type(advancedactivityUpdateHandler)) {
          if (<?php echo (!empty($this->getUpdate) ? 1 : 0) ?>) {
              if (firstfeedid)
                  advancedactivityUpdateHandler.options.last_id = firstfeedid;
          } else {
              advancedactivityUpdateHandler.options.last_id = firstfeedid;
          }
      }

  });
    <?php endif; ?>
<?php endif; ?>
</script>
<?php
if ((!empty($this->feedOnly) || !$this->endOfFeed ) &&
        (empty($this->getUpdate) && empty($this->checkUpdate))):
    ?>
<script type="text/javascript">

  (function () {

      var activity_count = <?php echo sprintf('%d', $this->activityCount) ?>;
      var next_id = <?php echo sprintf('%d', $this->nextid) ?>;
      var subject_guid = '<?php echo $this->subjectGuid ?>';
      var endOfFeed = <?php echo ( $this->endOfFeed ? 'true' : 'false' ) ?>;
      var actionFilter = '<?php echo $this->actionFilter ?>';
      var list_id =<?php echo sprintf('%d', $this->list_id) ?>;
      var activityFeedViewMoreActive = false;
      var integrateCommunityAdv = <?php echo sprintf('%d',$this->integrateCommunityAdv) ?>;
      var activityViewMore = function (next_id, subject_guid) {
          if (activity_type != 1)
              return;

          if (activityFeedViewMoreActive == true)
              return;
          // if( en4.core.request.isRequestActive() ) return;
          if (autoScrollFeedAAFEnable)
              window.onscroll = "";
          activityFeedViewMoreActive = true;
          var url = en4.core.baseUrl + 'widget/index/name/advancedactivity.feed';
          $('feed_viewmore').style.display = 'none';
          $('feed_loading').style.display = '';
          var request = new Request.HTML({
              url: url,
              data: {
                  format: 'html',
                  'maxid': next_id,
                  'feedOnly': true,
                  'nolayout': true,
                  'subject': subject_guid,
                  'actionFilter': actionFilter,
                  'list_id': list_id,
                  'integrateCommunityAdv': integrateCommunityAdv,
                  'feedSettings': <?php echo $this->jsonInline($this->feedSettings) ?>,
              },
              evalScripts: true,
              onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
                  activityFeedViewMoreActive = false;
                  if (activity_type == 1) {
                      countScrollAAFSocial++;
                      Elements.from(responseHTML).inject($('activity-feed'));
                      en4.core.runonce.trigger();
                      Smoothbox.bind($('activity-feed'));
                  }
                  if (en4.sitevideolightboxview) {
                      en4.sitevideolightboxview.attachClickEvent(Array('sitevideo_thumb_viewer'));
                  }
              }
          });
          request.send();
      }

      en4.core.runonce.add(function () {
          if (activity_type == 1) {
              if (next_id > 0 && !endOfFeed) {
                  if (autoScrollFeedAAFEnable)
                      window.onscroll = doOnScrollLoadActivity;
                  $('feed_viewmore').style.display = '';
                  //$('feed_viewmore').style.display = 'none';
                  $('feed_loading').style.display = 'none';
                  $('feed_loading').style.display = 'none';
                  $('feed_viewmore_link').removeEvents('click').addEvent('click', function (event) {
                      event.stop();
                      activityViewMore(next_id, subject_guid);
                  });
              } else {
                  if (autoScrollFeedAAFEnable)
                      window.onscroll = "";
                  $('feed_viewmore').style.display = 'none';
                  $('feed_loading').style.display = 'none';
                  $('feed_no_more').style.display = '';
              }
          }
          function doOnScrollLoadActivity()
          {
              if ((maxAutoScrollAAF == 0 || countScrollAAFSocial < maxAutoScrollAAF) && autoScrollFeedAAFEnable && activity_type == 1 && $('feed_viewmore')) {
                  if (typeof ($('feed_viewmore').offsetParent) != 'undefined') {
                      var elementPostionY = $('feed_viewmore').offsetTop;
                  } else {
                      var elementPostionY = $('feed_viewmore').y;
                  }
                  if (elementPostionY <= window.getScrollTop() + (window.getSize().y - 40)) {
                      activityViewMore(next_id, subject_guid);
                  }
              }
          }
      });
  })();


</script>
<?php endif; ?>

<?php
if (!empty($this->feedOnly) && empty($this->checkUpdate)): // Simple feed only for AJAX
    /*  Customization Start */
    echo $this->advancedActivityLoop($this->activity, array(
        'action_id' => $this->action_id,
        'viewAllComments' => $this->viewAllComments,
        'viewAllLikes' => $this->viewAllLikes,
        'feedOnly' => $this->feedOnly,
        'groupedFeeds' => $this->groupedFeeds,
        'onViewPage' => $this->onViewPage,
        'hashtag' => $this->hashtag,
        'showHashtags' => $this->showHashtags,
        'integrateCommunityAdv' => $this->integrateCommunityAdv,
        'feedSettings' => $this->feedSettings,
        'similarActivities' => $this->similarActivities,
        
    ));
    /*  Customization End */
    return; // Do no render the rest of the script in this mode
endif;
?>
<?php
    if (!empty($this->checkUpdate)): // if this is for the live update
        if ($this->activityCount)
            echo "<script type='text/javascript'>
          
          document.title = '($this->activityCount) ' + advancedactivityUpdateHandler.title;            
        </script>
      <div class='aaf_feed_tip_more'>
        <a href='javascript:void(0);' onclick='javascript:advancedactivityUpdateHandler.getFeedUpdate(" . $firstActionId . ");$(\"feed-update\").empty(); $(\"feed-update\").style.display = \"none\"; '>
        	<i class='aaf_feed_more_arrow'></i><b>{$this->translate(array('%d new update is available - click this to show it.', '%d new updates are available - click this to show them.', $this->activityCount), $this->activityCount)}</b>
        </a>
       </div>
       <script type='text/javascript'>
           showFeedUpdateTip();
        </script>";
        return; // Do no render the rest of the script in this mode
    endif;
    ?>

<?php if (!empty($this->getUpdate)): // if this is for the get live update    ?>
<script type="text/javascript">
  advancedactivityUpdateHandler.options.last_id = <?php echo sprintf('%d', $firstActionId) ?>;
</script>
<?php endif; ?>

<script type="text/javascript">
  var previousActionFilter;
  var activityUpdateHandler;
<?php if ($this->updateSettings && !$this->action_id): // wrap this code around a php if statement to check if there is live feed update turned on       ?>

  update_freq_aaf = <?php echo $this->updateSettings; ?>;
  aaf_last_id = <?php echo sprintf('%d', $firstActionId) ?>;
  aaf_subjectGuid = '<?php echo $this->subjectGuid; ?>';
  // Call_aafcheckUpdate();
  function showFeedUpdateTip() {
      if ($('aaf_feed_update_loading'))
          $('aaf_feed_update_loading').style.display = 'none';
      if (activity_type == 1 && (typeof previousActionFilter == "undefined" || previousActionFilter == 'all')) {
          $('feed-update').style.display = 'block';
      } else {
          if ($('update_advfeed_blink'))
              $('update_advfeed_blink').style.display = 'block';
      }
  }
<?php endif; ?>
  var getTabBaseContentFeed = function (actionFilter, list_id) {
      //if( en4.core.request.isRequestActive() ) return;           
      previousActionFilter = actionFilter;
      activity_type = 1;
      if ($('activity-post-container'))
          $('activity-post-container').style.display = 'block';

      if ($('aaf_tabs_feed'))
          $('aaf_tabs_feed').style.display = 'block';
      if ($("aaf_tabs_loader"))
          $('aaf_tabs_loader').style.display = 'block';
      if ($('feed-update')) {
          $('feed-update').style.display = 'none';
          $('feed-update').innerHTML = '';
      }

      //SHOWING THE FACEBOOK PUBLISH CHECKBOX AND ICON ACTIVE WHEN THIS TAB IS CLICKED....
      if ($('compose-socialengine-form-input')) {
          $('compose-socialengine-form-input').set('checked', 1);
          $('compose-socialengine-form-input').parentNode.addClass('composer_socialengine_toggle_active');
      }
      if (actionFilter == 'all') {
          if ($('update_advfeed_blink'))
              $('update_advfeed_blink').style.display = 'none';
          if (advancedactivityUpdateHandler)
              document.title = advancedactivityUpdateHandler.title;
      }

      if ($('feed_no_more'))
          $('feed_no_more').style.display = 'none';
      if ($('feed_viewmore'))
          $('feed_viewmore').style.display = 'none';
      if ($('feed_loading'))
          $('feed_loading').style.display = 'none';

      countScrollAAFSocial = 0;
      var request = new Request.HTML({
          url: en4.core.baseUrl + 'widget/index/name/advancedactivity.feed',
          data: {
              format: 'html',
              'actionFilter': actionFilter,
              'list_id': list_id,
              'feedOnly': true,
              'nolayout': true,
              'isFromTab': true,
              'subject': '<?php echo $this->subjectGuid ?>',
              'integrateCommunityAdv': '<?php echo $this->integrateCommunityAdv ?>',
              'feedSettings': <?php echo  $this->jsonInline($this->feedSettings) ?>,
          },
          evalScripts: true,
          onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
              if ($("aaf_tabs_loader"))
                  $("aaf_tabs_loader").style.display = 'none';
              if (activity_type == 1) {
                  if (advancedactivityUpdateHandler && actionFilter == 'all') {
                      advancedactivityUpdateHandler.options.last_id = firstfeedid;
                  }
                  $('activity-feed').innerHTML = "";
                  Elements.from(responseHTML).inject($('activity-feed'));
                  en4.core.runonce.trigger();
                  Smoothbox.bind($('activity-feed'));

                  if (en4.sitevideolightboxview) {
                      en4.sitevideolightboxview.attachClickEvent(Array('sitevideo_thumb_viewer'));
                  }
              }
          }
      });
      en4.core.request.send(request, {force: true});
  }
</script>
<?php // SHOW SITE ACTIVITY FEED.  ?>
<?php if ($this->activity || $this->actionFilter != 'all'): ?>
    <?php
    if ($this->action_id == 'false'): $this->action_id = 0;
    endif;
    ?>
    <?php
    if ($this->showPosts) {
    if (!empty($this->subjectGuid) && !$this->action_id):
            echo $this->partial('widgets-feed/profile-content-tabs.tpl', 'advancedactivity', array('onthisday' => $this->onthisday));
        elseif (empty($this->subjectGuid) && !$this->action_id && ($this->enableContentTabs || $this->canCreateCustomList)):
            echo $this->partial('widgets-feed/content-tabs.tpl', 'advancedactivity', array(
                'filterTabs' => $this->filterTabs,
                'actionFilter' => $this->actionFilter,
                'contentTabMax' => $this->contentTabMax,
                'canCreateCustomList' => $this->canCreateCustomList,
                'canCreateCategroyList' => $this->canCreateCategroyList
            ));
        endif;
    if( empty($this->subjectGuid) && (!empty($this->feedSettings['showGreetings']) && $this->feedSettings['showGreetings'] != 'none') ) {
        echo $this->content()->renderWidget('advancedactivity.greeting', array('widgetCall' => 'home-feeds', 'greetingType' => $this->feedSettings['showGreetings']));
      }
    }
    ?>
<?php endif; ?>
<div id="feed-update" style="display: none;"></div>
<div id="aaf_feed_update_loading" class='aaf_feed_loading' style="display: none;">
    <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' alt="Loading" />
    <?php echo $this->translate("Loading ...") ?>
</div>
<?php if ($this->post_failed == 1): ?>
<div class="aaf_feed_tip">
        <?php $url = $this->url(array('module' => 'user', 'controller' => 'settings', 'action' => 'privacy'), 'default', true) ?>
        <?php echo $this->translate('The post was not added to the feed. Please check your %1$sprivacy settings%2$s.', '<a href="' . $url . '">', '</a>.') ?>
</div>
<?php endif; ?>

<?php if (!$this->activity && $this->actionFilter == 'all'): ?>
  <?php // If requesting a single action and it doesn't exist, show error ?>
  <?php if( $this->action_id ) { ?>
    <h2><?php echo $this->translate("Activity Item Not Found") ?></h2>
    <p><?php echo $this->translate("The page you have attempted to access could not be found.") ?></p>
    <?php
    return;
  } else {
    ?>

    <div class="aaf_feed_tip" id="tip_feed">
      <?php
      if( empty($this->hide) ) {
        echo $this->translate("Nothing has been posted here yet - be the first!");
      } else {
        echo $this->translate("No results found!");
      }
      ?>

    </div>
  <?php } ?>
  <?php return; ?>
<?php elseif( !$this->activity ): ?>
  <ul class='feed' id="activity-feed"></ul>
<?php endif; ?>



<?php
echo $this->advancedActivityLoop($this->activity, array(
    'action_id' => $this->action_id,
    'viewAllComments' => $this->viewAllComments,
    'viewAllLikes' => $this->viewAllLikes,
    'feedOnly' => $this->feedOnly,
    'groupedFeeds' => $this->groupedFeeds,
    'onViewPage' => $this->onViewPage,
    'hashtag' => $this->hashtag,
    'showHashtags' => $this->showHashtags,
    'integrateCommunityAdv' => $this->integrateCommunityAdv,
    'feedSettings' => $this->feedSettings,
    'similarActivities' => $this->similarActivities,
));
?>

<div class="seaocore_view_more" id="feed_viewmore" style="display: none;">
    <?php
    echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
        'id' => 'feed_viewmore_link',
        'class' => 'buttonlink icon_viewmore'
    ))
    ?>
</div>

<div id="feed_loading" style="display: none;" class="aaf_feed_loading">
    <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' alt="Loading" />
    <?php echo $this->translate("Loading ...") ?>
</div>

<div class="aaf_feed_tip" id="feed_no_more" style="display: <?php echo (!$this->endOfFeed || !empty($this->action_id)) ? 'none' : '' ?>;"> 
    <?php
        if(empty($this->hide))
            echo $this->translate("There are no more posts to show.");
        else
            echo $this->translate("There are no more results to show.");
    ?>
</div>

<script type="text/javascript">
  aaf_last_id = <?php echo sprintf('%d', $firstActionId) ?>;
</script>
<?php if ($this->updateSettings && !$this->action_id): ?>
<script type="text/javascript">
  en4.core.runonce.add(function () {
      setTimeout(function () {
          if ($type(advancedactivityUpdateHandler)) {
              advancedactivityUpdateHandler.options.actionFilter = '<?php echo $this->actionFilter ?>';
          }
      });
  }, 300);
</script>
<?php endif; ?>
