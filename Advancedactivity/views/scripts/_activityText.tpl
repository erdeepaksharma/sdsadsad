<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _activityText.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$thisViewer = $this->viewer();
if(!empty($thisViewer)){
  $viewer_id = $thisViewer->getIdentity();
}
if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
  $actions = $this->actions;
}
$this->feedSettings = @array_merge(array(
  'viewMaxPhoto' => 8, 'memberPhotoStyle' => 'left'
  ), $this->feedSettings);
$this->viewMaxPhoto = $this->feedSettings['viewMaxPhoto'];
?>

<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Activity/externals/scripts/core.js')
;

$this->headLink()
  ->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/style_icon_toolbar.css');

$this->videoPlayerJs();
?>


<script type="text/javascript">
  en4.core.language.addData({
    "Stories from %s are hidden now and will not appear in your Activity Feed anymore.": "<?php echo $this->string()->escapeJavascript($this->translate("Stories from %s are hidden now and will not appear in your Activity Feed anymore.")); ?>",
    'Loading...': '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>'
  });
  var hideItemFeeds;
  var unhideItemFeed;
  var unhideReqActive = false;
  var el_siteevent;
  en4.core.runonce.add(function () {
    if (feedToolTipAAFEnable) {
      // Add hover event to get tool-tip
      var show_tool_tip = false;
      var counter_req_pendding = 0;
      $$('.sea_add_tooltip_link').addEvent('mouseover', function (event) {
        //var el = $(event.target);
        var el = $(this);
        el_siteevent = el;
        ItemTooltips.options.offset.y = el.offsetHeight;
        ItemTooltips.options.showDelay = 0;
        if (!el.get('rel')) {
          el = el.parentNode;
        }
        if (el && !el.retrieve('tip-loaded', false)) {
          el.store('tip:title', '');
          el.store('tip:text', '');
        }
        if (el.getParent('.layout_advancedactivitypost_feed_short'))
          return;
        show_tool_tip = true;
        if (!el.retrieve('tip-loaded', false)) {
          counter_req_pendding++;
          var resource = '';
          if (el.get('rel'))
            resource = el.get('rel');
          if (resource == '')
            return;

          el.store('tip-loaded', true);
          el.store('tip:title', '<div class="" style="">' +
                  ' <div class="uiOverlay info_tip" style="width: 300px; top: 0px; ">' +
                  '<div class="info_tip_content_wrapper" ><div class="info_tip_content"><div class="info_tip_content_loader">' +
                  '<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" alt="Loading" /><?php echo $this->translate("Loading ...") ?></div>' +
                  '</div></div></div></div>'
                  );
          el.store('tip:text', '');
          // Load the likes
          var url = '<?php echo $this->url(array('module' => 'seaocore', 'controller' => 'feed', 'action' => 'show-tooltip-info'), 'default', true) ?>';
          el.addEvent('mouseleave', function () {
            show_tool_tip = false;
          });

          var req = new Request.HTML({
            url: url,
            data: {
              format: 'html',
              'resource': resource
            },
            evalScripts: true,
            onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
              el.store('tip:title', '');
              el.store('tip:text', responseHTML);
              ItemTooltips.options.showDelay = 0;
              ItemTooltips.elementEnter(event, el); // Force it to update the text 
              counter_req_pendding--;
              if (!show_tool_tip || counter_req_pendding > 0) {
                //ItemTooltips.hide(el);
                ItemTooltips.elementLeave(event, el);
              }
              var tipEl = ItemTooltips.toElement();
              tipEl.addEvents({
                'mouseenter': function () {
                  ItemTooltips.options.canHide = false;
                  ItemTooltips.show(el);
                },
                'mouseleave': function () {
                  ItemTooltips.options.canHide = true;
                  ItemTooltips.hide(el);
                }
              });
              Smoothbox.bind($$(".sea_add_tooltip_link_tips"));

            }
          });
          req.send();
        }
      });
      // Add tooltips
      var window_size = window.getSize()
      var ItemTooltips = new SEATips($$('.sea_add_tooltip_link'), {
        fixed: true,
        title: '',
        className: 'sea_add_tooltip_link_tips',
        hideDelay: 200,
        offset: {'x': 0, 'y': 0},
        windowPadding: {'x': 370, 'y': (window_size.y / 2)}
      }
      );
    }
<?php if(!empty($viewer_id)): ?>
      hideItemFeeds = function (type, id, parent_type, parent_id, parent_html, report_url) {
        if (en4.core.request.isRequestActive())
          return;
        var url = '<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'feed', 'action' => 'hide-item'), 'default', true); ?>';
        var req = new Request.JSON({
          url: url,
          data: {
            format: 'json',
            type: type,
            id: id
          },
          onComplete: function (responseJSON) {

            if (type == 'activity_action' && $('activity-item-' + id)) {

              if ($('activity-item-undo-' + id))
                $('activity-item-undo-' + id).destroy();
              $('activity-item-' + id).style.display = 'none';
              var innerHTML = "<li id='activity-item-undo-" + id + "'><div class='feed_item_hide'>"
                      + "<b><?php echo $this->string()->escapeJavascript($this->translate("This story is now hidden from your Activity Feed.")) ?></b>" + " <a href='javascript:void(0);' onclick='unhideItemFeed(\"" + type + "\" , \"" + id + "\")'>" + "<?php echo $this->string()->escapeJavascript($this->translate("Undo")) ?> </a> <br /> ";
              if (report_url == '') {
                innerHTML = innerHTML + "<span> <a href='javascript:void(0);' onclick='hideItemFeeds(\"" + parent_type + "\" , \"" + parent_id + "\",\"\",\"" + id + "\", \"" + parent_html + "\",\"\")'>"
                        + '<?php
  echo
  $this->string()->escapeJavascript($this->translate('Hide all by '));
  ?>' + parent_html + "</a></span>";
              } else {
                innerHTML = innerHTML + "<span> <?php echo $this->string()->escapeJavascript($this->translate("To mark it offensive, please ")) ?> <a href='javascript:void(0);' onclick='Smoothbox.open(\"" + report_url + "\")'>" + "<?php echo $this->string()->escapeJavascript($this->translate("file a report")) ?>" + "</a>" + "<?php echo '.' ?>" + "</span>";
              }

              innerHTML = innerHTML + "</div></li>";
              Elements.from(innerHTML).inject($('activity-item-' + id), 'after');

            } else {
              if ($('activity-item-undo-' + parent_id))
                $('activity-item-undo-' + parent_id).destroy();
              var innerHTML = "<li id='activity-item-undo-" + id + "'><b>" + en4.core.language.translate("Stories from %s are hidden now and will not appear in your Activity Feed anymore.", parent_html) + "</b> <a href='javascript:void(0);' onclick='unhideItemFeed(\"" + type + "\" , \"" + id + "\")'>" + "<?php echo $this->string()->escapeJavascript($this->translate("Undo")) ?> </a>" + "</li>";
              Elements.from(innerHTML).inject($('activity-item-' + parent_id), 'after');

              var className = '.Hide_' + type + '_' + id;
              var myElements = $$(className);
              for (var i = 0; i < myElements.length; i++) {
                myElements[i].style.display = 'none';
              }
            }
          }
        });
        req.send();
      }
      unhideItemFeed = function (type, id) {
        if (unhideReqActive)
          return;
        unhideReqActive = true;
        var url = '<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'feed', 'action' => 'un-hide-item'), 'default', true); ?>';
        var req = new Request.JSON({
          url: url,
          data: {
            format: 'json',
            type: type,
            id: id
          },
          onComplete: function (responseJSON) {
            if ($('activity-item-undo-' + id))
              $('activity-item-undo-' + id).destroy();
            if (type == 'activity_action' && $('activity-item-' + id)) {

              $('activity-item-' + id).style.display = '';
              //document.getElementById('activity-feed').removeChild($('activity-item-undo-'+id));
            } else {
              var className = '.Hide_' + type + '_' + id;
              var myElements = $$(className);
              for (var i = 0; i < myElements.length; i++) {
                myElements[i].style.display = '';
              }
              //  document.getElementById('activity-feed').removeChild($('activity-item-undo-'+id));
            }
            unhideReqActive = false;
          }
        });
        req.send();
      }

  <?php if( !$this->feedOnly && !$this->onlyactivity ): ?>
        moreEditOptionsSwitch = function (el) {
          var hasAlreadyOpen = el.getParent().hasClass('aaf_tabs_feed_tab_open');
          $$('.aaf_pulldown_btn_wrapper').removeClass('aaf_tabs_feed_tab_open').addClass('aaf_tabs_feed_tab_closed');
          if (hasAlreadyOpen) {
            return;
          }
          el.getParent().addClass('aaf_tabs_feed_tab_open');

        }
  <?php endif; ?>
<?php endif; ?>
    SmoothboxSEAO.bind($('activity-feed'));
    if (en4.sitevideoview) {
      en4.sitevideoview.attachClickEvent(Array('feed', 'feed_video_title', 'feed_sitepagevideo_title', 'feed_sitebusinessvideo_title', 'feed_ynvideo_title', 'feed_sitegroupvideo_title', 'feed_sitestorevideo_title'));
    }

    if (en4.sitevideolightboxview) {
      en4.sitevideolightboxview.attachClickEvent(Array('feed', 'feed_video_title', 'feed_sitepagevideo_title', 'feed_sitebusinessvideo_title', 'feed_ynvideo_title', 'feed_sitegroupvideo_title', 'feed_sitestorevideo_title', 'sitevideo_thumb_viewer'));
    }
  });
</script>

<?php if( !$this->feedOnly && !$this->onlyactivity ): ?>
<ul class='feed feed_sections_<?php echo $this->feedSettings['memberPhotoStyle'] ?> <?php echo !empty($this->feedSettings['pinboardColumn']) ? ' feed_sections_pinboard_col_'.$this->feedSettings['pinboardColumn'].' ' : ''?>' id="activity-feed">
  <?php endif ?>
  <?php
  $advancedactivityCoreApi = Engine_Api::_()->advancedactivity();
  $advancedactivitySaveFeed = Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity');
  $offNotification = Engine_Api::_()->getDbtable('notificationsettings', 'advancedactivity');
  $pinSettings = Engine_Api::_()->getDbtable('pinsettings', 'advancedactivity');
  ?>
  <?php
  $feedAdvCount = 0;
  $displayAdd = $this->integrateCommunityAdv;
  $settings = Engine_Api::_()->getApi('settings', 'core');
  $this->advCount = $settings->getSetting('advancedactivity.adv.count', 5);
  $ignoreScriptInclude = false;
  foreach( $actions as $action ): // (goes to the end of the file)
    try { // prevents a bad feed item from destroying the entire page
      // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
      if( !$action->getTypeInfo()->enabled )
        continue;
      if( !$action->getSubject() || !$action->getSubject()->getIdentity() )
        continue;
      if( !$action->getObject() || !$action->getObject()->getIdentity() )
        continue;
      ob_start();
      $subject = $action->getSubject();
      $object = $action->getObject();

      if( !$this->noList && !$this->subject() && $action->getTypeInfo()->type == 'birthday_post' ):
        echo $this->birthdayActivityLoop($action, array(
          'action_id' => $this->action_id,
          'viewAllComments' => $this->viewAllComments,
          'viewAllLikes' => $this->viewAllLikes,
          'commentShowBottomPost' => $this->commentShowBottomPost
        ));
        ob_end_flush();
        continue;
      endif;
      ?>

      <?php
      $item = $itemPhoto = (isset($action->getTypeInfo()->is_object_thumb) && !empty($action->getTypeInfo()->is_object_thumb)) ? $action->getObject() : $action->getSubject();
      $itemPhoto = (isset($action->getTypeInfo()->is_object_thumb) && $action->getTypeInfo()->is_object_thumb === 2) ? $action->getObject()->getParent() : $itemPhoto;
      ?>
      <?php if( !$this->noList ): ?>
        <li class="activity-item <?php echo 'Hide_' . $item->getType() . "_" . $item->getIdentity() ?>" id="activity-item-<?php echo $action->action_id ?>"  data-activity-feed-item="<?php echo $action->action_id ?>" <?php if( $this->onViewPage ): ?>style="padding-bottom: 30px;" <?php endif; ?> ><?php endif; ?>
        <?php $actionBaseId = $this->onViewPage ? "view-" . $action->action_id : $action->action_id; ?>
        <div class="aaf_feed_top_section aaf_feed_section_<?php echo $this->feedSettings['memberPhotoStyle'] ?>">
          <?php // User's profile photo  ?>
          <div class='feed_item_photo aaf_feed_thumb'> <?php
            echo $this->htmlLink($itemPhoto->getHref(), $this->itemPhoto($itemPhoto, 'thumb.icon', $itemPhoto->getTitle()), array('class' => 'sea_add_tooltip_link', 'rel' => $itemPhoto->getType() . ' ' . $itemPhoto->getIdentity())
            )
            ?></div>

          <?php
          $allowEdit = $action->canEdit();
          ?>
          <div class="aaf_feed_top_section_title">
            <?php 
            if(!empty($viewer_id)) : 
            // Add Right Side Dropdown Options
            include APPLICATION_PATH_MOD . '/Advancedactivity/views/scripts/_activityDropDownOptions.tpl';
            endif;
            ?>
            <div class="<?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">
              <?php
              /* Start Working group feed. */
              $groupedFeeds = null;
              if( $action->type == 'friends' ) {
                $subject_guid = $action->getSubject()->getGuid();
                $total_guid = $action->type . '_' . $subject_guid;
              } elseif( $action->type == 'tagged' ) {
                foreach( $action->getAttachments() as $attachment ) {
                  $object_guid = $attachment->item->getGuid();
                  $Subject_guid = $action->getSubject()->getGuid();
                  $total_guid = $action->type . '_' . $object_guid . '_' . $Subject_guid;
                }
              } else {
                $subject_guid = $action->getObject()->getGuid();
                $total_guid = $action->type . '_' . $subject_guid;
              }
              if( !isset($grouped_actions[$total_guid]) && isset($this->groupedFeeds[$total_guid]) ) {
                $groupedFeeds = $this->groupedFeeds[$total_guid];
              }
              /* End Working group feed. */
              echo $this->getContent($action, false, $groupedFeeds, array('similarActivities' => $this->similarActivities));
              ?>
            </div>
            <div class="aaf_feed_top_footer">
              <?php
              $icon_type = 'activity_icon_' . $action->type;
              list($attachment) = $action->getAttachments();
              if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ):
                $icon_type .= ' item_icon_' . $attachment->item->getType() . ' ';
              endif;
              ?>
              <?php if( is_array($action->params) && isset($action->params['checkin']) && !empty($action->params['checkin']) ): ?>
                <?php if( isset($action->params['checkin']['type']) && $action->params['checkin']['type'] == 'Page' ): ?>
                  <?php $icon_type = "item_icon_sitepage"; ?>
                <?php elseif( isset($action->params['checkin']['type']) && $action->params['checkin']['type'] == 'Business' ): ?>
                  <?php $icon_type = "item_icon_sitebusiness"; ?>
                <?php elseif( isset($action->params['checkin']['type']) && $action->params['checkin']['type'] == 'Group' ): ?>
                  <?php $icon_type = "item_icon_sitegroup"; ?>
                <?php elseif( isset($action->params['checkin']['type']) && $action->params['checkin']['type'] == 'Store' ): ?>
                  <?php $icon_type = "item_icon_sitestore"; ?>
                <?php else: ?>
                  <?php $icon_type = "item_icon_sitetagcheckin"; ?>
                <?php endif; ?>
              <?php endif; ?>
              <i class="feed_item_date feed_item_icon <?php echo $icon_type ?>"></i>
              <span class="notranslate feed_item_time"><?php echo $this->timestamp($action->getTimeValue()) ?></span>
              <?php $belongsTo = $subject->getIdentity() != $viewer_id ? ucwords($subject->getTitle()) : 'Your'; ?>
              <?php $availableLabels = array('everyone' => 'Everyone', 'networks' => 'Friends &amp; Networks', 'friends' => $this->translate('%s\'s friends', $belongsTo), 'onlyme' => 'Only Me', 'custom' => 'Customized', 'list' => 'Friend List', 'network_list' => 'Network'); ?>
              <?php if( $action->privacy ): ?>
                <?php
                $privacy = in_array($action->privacy, array("everyone", "networks", "friends", "onlyme")) ? $action->privacy : 'custom';
                $privacy_titile = $availableLabels[$privacy];
                if( $privacy === 'custom' ) {
                  if( Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($action->privacy) ) {
                    $privacy = 'network_list';
                    $networkTitles = Engine_Api::_()->advancedactivity()->getNetworkTitleBasePrivacyIds($action->privacy);
                    $privacy_titile = join(', ', $networkTitles);
                    if( count($networkTitles) > 1 ) {
                      $privacy_titile = $this->translate('Networks (%s)', $privacy_titile);
                    } else {
                      $privacy_titile = $this->translate('Network (%s)', $privacy_titile);
                    }
                  } else {
                    $privacy = 'list';
                    $memberListTitle = array();
                    foreach( explode(",", $action->privacy) as $memberListId ):
                      $item = Engine_Api::_()->getItem('user_list', $memberListId);
                      if( !$item ) {
                        continue;
                      }
                      $memberListTitle[] = $item->getTitle();
                    endforeach;
                    $privacy_titile = join(', ', $memberListTitle);
                    if( count($memberListTitle) > 1 ) {
                      $privacy_titile = $this->translate('%1$s\'s Friends Lists (%2$s)', $belongsTo, $privacy_titile);
                    } else {
                      $privacy_titile = $this->translate('%1$s\'s Friends List (%2$s)', $belongsTo, $privacy_titile);
                    }
                  }
                }
                ?>
                <span class="seaocore_txt_light">&middot;</span>
                <span class="aaf_icon_feed_<?php echo $privacy ?> feed_item_privacy" title="<?php echo $this->translate("Shared with: %s", $this->translate($privacy_titile)) ?>">
                </span>
              <?php endif; ?>
              <?php if( null != ( $postAgent = $action->postAgent()) ): ?>
                <span class="seaocore_txt_light">&middot;</span>
                <span>
                  <?php echo $this->translate("via %s", $postAgent); ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php $params = $action->params; ?>
        <?php $isTopAttachment = false; $class ='class = "';$attachmentContent = '';
        if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ): // Attachments  ?>
          <?php $isTopAttachment = in_array($attachment->item->getType(), array('sitereaction_sticker'));
              if ($isTopAttachment) {
                $class .= 'feed_item_top_attachment item_feed_'.$attachment->item->getType();
                $attachmentContent = '<div>'.$this->itemPhoto($attachment->item, $attachment->item->getPhotoUrl()).'</div>';
              }
          ?>
          <?php endif; ?>
        <?php
        if( !empty($params['feed-banner']['background-color']) ):
          $class .= ' feed_item_body aaf_feed-banner-added" style="background-image: '.$params['feed-banner']['image'].';';
          $class .= 'background-color:' . $params['feed-banner']['background-color'] . ';';
          $class .= 'color:' . $params['feed-banner']['color'] . '";';
        else:
          $class .= ' feed_item_body"';
        endif;
        ?>
        <div <?php echo $class ?> >
          <?php // Main Content   ?>
          <?php echo $attachmentContent; ?>
          <?php $actionBody = $this->getActionBody($action); ?>
          <?php if($actionBody): ?>
          <div class="feed_item_body_content feed_item_bodytext">
            <?php echo $actionBody; ?>
          </div>
          <?php endif; ?>
<!--          <div>
            <?php // echo $allowEdit ? $this->editPost($action) : ''; ?>
          </div>-->
          <?php // Attachments ?>
          <?php if( !$isTopAttachment && ( ($action->getTypeInfo()->attachable && $action->attachment_count > 0 ) || $action->getCustomAttachments()) ): // Attachments  ?>
            <?php $attachments = $action->getAttachments();?>
            <?php $attachmentsCount = count($action->getAttachments()); ?>
            <?php if ($attachmentsCount == 0):
              $attachments = $action->getCustomAttachments();
              $attachmentsCount = count($attachmentsCount);
            endif; ?>
            <div class='feed_item_attachments <?php echo $attachmentsCount > $this->viewMaxPhoto ? 'aaf_item_attachment_more aaf_item_attachment_' . $this->viewMaxPhoto : 'aaf_item_attachment_' . $attachmentsCount ?>'>
              <?php if( $attachmentsCount > 0 ): ?>
                <?php
                if($action->attachment_count > 0 && $attachmentsCount == 1 &&
                  null != ( $richContent = $this->getRichContent(current($action->getAttachments())->item, array_merge( array('action' => $action), $this->feedSettings))) ):
                  ?>
                  <?php echo $richContent; ?>
                <?php else: ?>
                  <?php $i = 0; ?>
                  <?php foreach( $attachments as $attachmentKey => $attachment ): ?>
                    <?php $i++; ?>
                    <?php
                    $attribs = array();
                    ?>
                    <?php if( SEA_ACTIVITYFEED_LIGHTBOX && strpos($attachment->meta->type, '_photo') && $attachment->item->getHref()): ?>
                      <?php $attribs['onclick'] = 'openSeaocoreLightBox("' . $attachment->item->getHref() . '");return false;'; ?>
                    <?php endif; ?>
                    <?php
                    $attachmentTitle = $attachment->item->getTitle();
                    $attachmentDescription = '';
                    if( $attachment->item->getType() == "activity_action" ) {
                      $attachmentDescription = $this->getContent($attachment->item, true);
                    } elseif( $action->body != $attachment->item->getDescription() ) {
                      $attachmentDescription = $attachment->item->getDescription();
                      $attachmentDescription = $this->viewMore($attachmentDescription);
                    }
                    ?>
                    <span class='feed_attachment_<?php echo $attachment->meta->type; ?> feed_attachment_<?php echo (strpos($attachment->meta->type, '_photo') && (!$attachmentTitle && !$attachmentDescription) ? 'photo' : 'item'); ?> <?php echo $attachmentsCount == 1 && !$attachment->item->getPhotoUrl() ? ' no_item_photo ' : '' ?>'>
                      <?php if( $attachment->meta->mode == 0 ): // Silence  ?>
                      <?php elseif( $attachment->meta->mode == 1 && $attachmentsCount == 1 ): // Thumb/text/title type actions     ?>
                        <div class="feed_attachment_aaf">
                          <?php
                          if( $attachment->item->getType() == "core_link" ) {
                            $attribs['target'] = '_blank';
                          }
                          ?>
                           <?php if( $action->type== 'profile_photo_update' ): ?>
                            <?php echo $this->htmlLink($attachment->item->getHref(), $this->partial(
          '_coverProfilePhoto.tpl', 'advancedactivity', array('action' => $action, 'actionSubject' => $subject, 'attachment' => $attachment->item)), $attribs) ?>
                          <?php elseif( $attachment->item->getPhotoUrl() ): ?>
                          <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $attachment->item->getPhotoUrl() ? 'thumb.main' : 'thumb.normal', $attachment->item->getTitle()), $attribs) ?>
                          <?php endif; ?>
                          <?php if( $attachmentTitle || $attachmentDescription ): ?>
                            <div class="aaf_feed_item_info">
                              <?php if( $attachmentTitle ): ?>
                                <div class='feed_item_link_title'>
                                  <?php
                                  if( $attachment->item->getType() == "core_link" ) {
                                    $attribs = Array('target' => '_blank');
                                  } else {
                                    $attribs = array('class' => 'sea_add_tooltip_link', 'rel' => $attachment->item->getType() . ' ' . $attachment->item->getIdentity());
                                  }
                                  echo $this->htmlLink($attachment->item->getHref(), $attachment->item->getTitle() ? $attachment->item->getTitle() : '', $attribs);
                                  ?>
                                </div>
                              <?php endif; ?>
                              <?php if( $attachmentDescription ): ?>
                                <div class='feed_item_link_desc'>
                                  <?php echo $attachmentDescription; ?>
                                </div>
                              <?php endif; ?>
                              <?php if( $attachment->item->getType() == "core_link" ): ?>
                                <?php $parse = parse_url($attachment->item->uri); ?>
                                <?php if( !empty($parse['host']) ): ?>
                                  <?php $host = strpos($parse['host'], 'www.') === 0 ? substr($parse['host'], 4) : $parse['host'] ?>
                                  <div class='feed_item_link_domain'><?php echo $this->htmlLink($attachment->item->getHref(), $host, $attribs) ?></div>
                                <?php endif; ?>
                              <?php endif; ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      <?php elseif( $attachment->meta->mode == 2 || ($attachment->meta->mode == 1 && count($action->getAttachments()) > 1) ):  // Thumb only type actions  ?>
                        <div class="feed_attachment_photo aaf_feed_attachment_photo">
                          <?php if( !$this->action_id && $attachmentKey === $this->viewMaxPhoto - 1 ): ?>
                            <a href="<?php echo $action->getHref(); ?>">
                              <span class="feed_attachment_photo_overlay"></span>
                              <span class="feed_attachment_photo_more_count"><?php echo '+' . ($attachmentsCount - $this->viewMaxPhoto + 1) ?></span>
                              <?php echo $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle(), array()) ?>
                            </a>
                            <?php break; ?>
                          <?php endif; ?>
                          <?php
                          $photoContent = $this->itemPhoto($attachment->item, $attachmentsCount < 6 ? 'thumb.main' : 'thumb.medium', $attachment->item->getTitle(), array());
                          echo $this->htmlLink($attachment->item->getHref(), $photoContent, $attribs);
                          ?>
                        </div>
                      <?php elseif( $attachment->meta->mode == 3 ): // Description only type actions   ?>
                        <?php echo $this->viewMore($attachment->item->getDescription()); ?>
                      <?php elseif( $attachment->meta->mode == 4 ): // Multi collectible thingy (@todo)  ?>
                      <?php endif; ?>
                    </span>
                  <?php endforeach; ?>
                <?php endif; ?>
              <?php endif; ?>
            </div>

          <?php else: ?>
            <?php
            $checkInParams = $action->params;
            if( !empty($checkInParams['checkin']) ) :
              ?>
              <div class='feed_item_attachments'><?php echo $this->getSitetagCheckinMap($action, $action->params) ?></div>
            <?php endif; ?>
          <?php endif; ?>
          <!-- Related Hash tags -->
          <?php if( !empty($this->hashtag[$action->action_id]) && !empty($this->showHashtags) ): ?>
            <div class="hashtag_activity_item">
              <ul>
                <?php
                $url = $this->url(array('controller' => 'index', 'action' => 'index'), "sitehashtag_general") . "?search=";
                for( $i = 0; $i < count($this->hashtag[$action->action_id]); $i++ ) {
                  ?>
                  <li>
                    <a href="<?php echo $url . urlencode($this->hashtag[$action->action_id][$i]); ?>"><?php echo $this->hashtag[$action->action_id][$i]; ?></a>
                  </li>
                <?php } ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
        <?php // Icon, time since, action links   ?>
        <?php if( !empty($action->publish_date) ) : ?>
          <div class="aaf_scheduled_feed_tip">
            <span>
              <?php
              $userTime = Engine_Api::_()->advancedactivity()->dbToUserDateTime(array('starttime' => $action->publish_date));
              echo $this->translate(" This feed will post on %s", $userTime['starttime']);
              ?></span>
          </div>

        <?php else: ?>
          <div id='comment-likes-activity-item-<?php echo $actionBaseId ?>' class='comment-likes-activity-item'>
            <?php echo $this->advancedActivityViewerActions(array_merge($this->getVars(), array('action' => $action, 'ignoreScriptInclude' => $ignoreScriptInclude))); ?>
            <?php $ignoreScriptInclude = true; ?>
          </div>
        <?php endif; ?>
        <?php if( !$this->noList ): ?>
          <?php
          $feedAdvCount++;
          if( $feedAdvCount == $this->advCount ): {
              $feedAdvCount = 0;
              echo $this->content()->renderWidget("advancedactivity.community-ads", array('integrateCommunityAdv' => $displayAdd));
            }
          endif;
          ?>
        </li>  <?php endif; ?>
      <?php
      ob_end_flush();
    } catch( Exception $e ) {
      ob_end_clean();
      if( APPLICATION_ENV === 'development' ) {
        echo $e->__toString();
      }
    };
  endforeach;
  ?>
  <?php if( !$this->feedOnly && !$this->onlyactivity ): ?>
  </ul>
<?php endif ?>

<script type="text/javascript">

  function deletefeed(action_id, comment_id, action_link) {
    if (comment_id == 0) {

      var msg = "<div class='aaf_show_popup'><h3>" + "<?php echo $this->translate('Delete Activity Item?') ?>" + "</h3><p>" + "<?php echo $this->string()->escapeJavascript($this->translate('Are you sure that you want to delete this activity item? This action cannot be undone.')) ?>" + "</p>" + "<button type='submit' onclick='content_delete_act(" + action_id + ", 0); return false;'>" + "<?php echo $this->string()->escapeJavascript($this->translate('Delete')) ?>" + "</button>" + " <?php echo $this->string()->escapeJavascript($this->translate('or')) ?> " + "<a href='javascript:void(0);'onclick='AAFSmoothboxClose();'>" + "<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>" + "</a></div>"

    } else {
      var msg = "<div class='aaf_show_popup'><h3>" + "<?php echo $this->string()->escapeJavascript($this->translate('Delete Comment?')) ?>" + "</h3><p>" + "<?php echo $this->string()->escapeJavascript($this->translate('Are you sure that you want to delete this comment? This action cannot be undone.')) ?>" + "</p>" + "<button type='submit' onclick='content_delete_act(" + action_id + "," + comment_id + "); return false;'>" + "<?php echo $this->string()->escapeJavascript($this->translate('Delete')) ?>" + "</button>" + " <?php echo $this->string()->escapeJavascript($this->translate('or')) ?> " + "<a href='javascript:void(0);'onclick='AAFSmoothboxClose();'>" + "<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>" + "</a></div>"
    }

    Smoothbox.open(msg);
  }

</script>

<script type="text/javascript">
  var maxTime = parseInt('<?php echo Engine_Api::_()->getApi("settings", "core")->getSetting("advancedactivity.pin.reset.days", 7); ?>');
  var content_delete_act = function (action_id, comment_id) {
    if (comment_id == 0) {
      if ($('activity-item-' + action_id))
        $('activity-item-' + action_id).destroy();
    } else {
      if ($('comment-' + comment_id))
        $('comment-' + comment_id).destroy();
    }
    AAFSmoothboxClose();
    url = en4.core.baseUrl + 'advancedactivity/index/delete';
    var request = new Request.JSON({
      'url': url,
      'method': 'post',
      'data': {
        'format': 'json',
        'action_id': action_id,
        'comment_id': comment_id,
        'subject': en4.core.subject.guid
      }
    });
    request.send();
  }

  function turnOnOffNotification(action_id, onoff) {

    if (onoff == 1) {
      $('turn_on_notification_' + action_id).style.display = 'none';
      $('turn_off_notification_' + action_id).style.display = 'block';
    } else {
      $("turn_on_notification_" + action_id).style.display = 'block';
      $("turn_off_notification_" + action_id).style.display = 'none';

    }

    url = en4.core.baseUrl + 'advancedactivity/index/turn-on-off-notification';
    var request = new Request.JSON({
      'url': url,
      'method': 'post',
      'data': {
        'format': 'json',
        'action_id': action_id,
        'subject': en4.core.subject.guid
      }
    });
    request.send();
  }

  function setPinTime(action_id, type, onoff) {
    if (onoff == 0) {
      pinUnpin(action_id, type, onoff);
      return;
    }
    var typecast = String(type);
    var content = "<div class='aaf_show_popup'><h3>" + "<?php echo $this->translate('Set Pin Reset Time') ?>" + "</h3><p>" + "<?php echo $this->string()->escapeJavascript($this->translate('You can set the time after which pinned feed will automatically reset to unpin.(Please enter number of days like 5 ,5.5 or 5.25  etc.)')) ?>" + "</p>" + "<input type='text' value='" + maxTime + "' name='aaf_pin_reset_time' id='aaf_pin_reset_time' placeholder='Enter the time in day' /> <br /><span id='pin_span_error' > Please Enter Number of days less than or equal to " + maxTime + "</span> <br /> <button onclick= 'pinUnpin(" + action_id + ",\"" + typecast + "\"," + onoff + "); return false;'>" + "<?php echo $this->string()->escapeJavascript($this->translate('Pin')) ?>" + "</button>" + " <?php echo $this->string()->escapeJavascript($this->translate('or')) ?> " + "<a href='javascript:void(0);'onclick='AAFSmoothboxClose();'>" + "<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>" + "</a></div>";
    Smoothbox.open(content);
  }

  function pinUnpin(action_id, type, onoff) {

    if (onoff == 1) {

      var time = $("aaf_pin_reset_time").value;
      if (time > maxTime) {
        $('pin_span_error').style.color = "red"
        return;
      } else {
        AAFSmoothboxClose();
      }
      $('turn_pin_' + action_id).style.display = 'none';
      $('turn_unpin_' + action_id).style.display = 'block';
    } else {
      $("pin_icon_" + action_id).style.display = 'none';
      $("turn_pin_" + action_id).style.display = 'block';
      $("turn_unpin_" + action_id).style.display = 'none';
    }

    url = en4.core.baseUrl + 'advancedactivity/index/pin-unpin';
    var request = new Request.JSON({
      'url': url,
      'method': 'post',
      'data': {
        'format': 'json',
        'action_id': action_id,
        'type': type,
        'time': time,
        'subject': en4.core.subject.guid
      },
      'onSuccess': function (responseTree, responseElements, responseJSON) {
        $('feed-update').empty();
        tabProfileContainerSwitch($("tab_advFeed_everyone"), true);
        getTabBaseContentFeed('all', '0');
      }
    });
    request.send();
  }


  function showLinkPost(url) {
    url = '<?php echo ((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] ?>' + url;
    var content = '<div class="aaf_gtp_pup"><h3><?php echo $this->string()->escapeJavascript($this->translate('Link to this Feed')) ?></h3><div class="aaf_gtp_feed_url">\n\
<p><?php echo $this->string()->escapeJavascript($this->translate('Copy this link to send this feed to others:')) ?></p>\n\
<div>\n\
<input type="text" id="show_link_post_input"  value="' + url + '" readonly="readonly"><span class="bold" style="margin-left:10px;"><a href="' + url + '" target="_blank" noreferrer="true"><?php echo $this->string()->escapeJavascript($this->translate('Go!')) ?> </a></span></div>\n\
</div>\n\
<div>\n\
<p><button name="close" onclick="AAFSmoothboxClose()"><?php echo $this->string()->escapeJavascript($this->translate('Close')) ?></button></p>\n\
</div>\n\
</div>';
    Smoothbox.open(content);
    $('show_link_post_input').select();
  }
  function AAFSmoothboxClose() {
    if (typeof parent.Smoothbox == 'undefined') {
      Smoothbox.close();
    } else {
      parent.Smoothbox.close();
    }
  }
</script>
