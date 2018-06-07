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
if( empty($this->action) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
  $action = $this->action;
}
$this->feedSettings = @array_merge(array(
    'viewMaxPhoto' => 8, 'memberPhotoStyle' => 'left'
    ), $this->feedSettings);
$this->viewMaxPhoto = $this->feedSettings['viewMaxPhoto'];
?>
<?php
try { // prevents a bad feed item from destroying the entire page
  // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
  if( !$action->getTypeInfo()->enabled )
    return;
  if( !$action->getSubject() || !$action->getSubject()->getIdentity() )
    return;
  if( !$action->getObject() || !$action->getObject()->getIdentity() )
    return;

  $subject = $action->getSubject();
  $object = $action->getObject();
  ?>

  <?php
  $item = $itemPhoto = (isset($action->getTypeInfo()->is_object_thumb) && !empty($action->getTypeInfo()->is_object_thumb)) ? $action->getObject() : $action->getSubject();
  $itemPhoto = (isset($action->getTypeInfo()->is_object_thumb) && $action->getTypeInfo()->is_object_thumb === 2) ? $action->getObject()->getParent() : $itemPhoto;
  ?>
  <div class="feed_activity_attachment">
    <div class="aaf_feed_top_section aaf_feed_section_<?php echo $this->feedSettings['memberPhotoStyle'] ?>" style="margin-top:0">
      <?php // User's profile photo    ?>
      <div class='feed_item_photo aaf_feed_thumb'> <?php
        echo $this->htmlLink($itemPhoto->getHref(), $this->itemPhoto($itemPhoto, 'thumb.icon', $itemPhoto->getTitle()), array('class' => 'sea_add_tooltip_link', 'rel' => $itemPhoto->getType() . ' ' . $itemPhoto->getIdentity())
        )
        ?></div>
      <div class="aaf_feed_top_section_title">
        <div class="<?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">
          <?php
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
          <?php $belongsTo = $subject->getIdentity() != $this->viewer()->getIdentity() ? ucwords($subject->getTitle()) : 'Your'; ?>
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
    <?php
    $isTopAttachment = false;
    $class = 'class = "';
    $attachmentContent = '';
    if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ): // Attachments  
      ?>
      <?php
      $isTopAttachment = in_array($attachment->item->getType(), array('sitereaction_sticker'));
      if( $isTopAttachment ) {
        $class .= 'feed_item_top_attachment item_feed_' . $attachment->item->getType();
        $attachmentContent = '<div>' . $this->itemPhoto($attachment->item, $attachment->item->getPhotoUrl()) . '</div>';
      }
      ?>
    <?php endif; ?>
    <?php
    if( !empty($params['feed-banner']['background-color']) ):
      $class .= ' feed_item_body aaf_feed-banner-added" style="background-image: ' . $params['feed-banner']['image'] . ';';
      $class .= 'background-color:' . $params['feed-banner']['background-color'] . ';';
      $class .= 'color:' . $params['feed-banner']['color'] . '";';
    else:
      $class .= ' feed_item_body"';
    endif;
    ?>
    <div <?php echo $class ?> >
      <?php // Main Content   ?>
      <?php echo $attachmentContent; ?>
      <div class="feed_item_body_content feed_item_bodytext">
        <?php echo $this->getActionBody($action); ?>
      </div>
      <!--          <div>
      <?php // echo $allowEdit ? $this->editPost($action) : '';   ?>
                </div>-->
      <?php // Attachments ?>
      <?php if( !$isTopAttachment && ( ($action->getTypeInfo()->attachable && $action->attachment_count > 0 ) || $action->getCustomAttachments()) ): // Attachments   ?>
        <?php $attachments = $action->getAttachments(); ?>
        <?php $attachmentsCount = count($action->getAttachments()); ?>
        <?php
        if( $attachmentsCount == 0 ):
          $attachments = $action->getCustomAttachments();
          $attachmentsCount = count($attachmentsCount);
        endif;
        ?>
        <div class='feed_item_attachments <?php echo $attachmentsCount > $this->viewMaxPhoto ? 'aaf_item_attachment_more aaf_item_attachment_' . $this->viewMaxPhoto : 'aaf_item_attachment_' . $attachmentsCount ?>'>
          <?php if( $attachmentsCount > 0 ): ?>
            <?php
            if( $action->attachment_count > 0 && $attachmentsCount == 1 &&
              null != ( $richContent = $this->getRichContent(current($action->getAttachments())->item, $this->feedSettings)) ):
              ?>
              <?php echo $richContent; ?>
            <?php else: ?>
              <?php $i = 0; ?>
              <?php foreach( $attachments as $attachmentKey => $attachment ): ?>
                <?php $i++; ?>
                <?php
                $attribs = array();
                ?>
                <?php if( SEA_ACTIVITYFEED_LIGHTBOX && strpos($attachment->meta->type, '_photo') ): ?>
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
                <span class='feed_attachment_<?php echo $attachment->meta->type; ?> feed_attachment_<?php echo (strpos($attachment->meta->type, '_photo') && (!$attachmentTitle && !$attachmentDescription) ? 'photo' : 'item'); ?> <?php echo $attachmentsCount == 1 && !$attachment->item->getPhotoUrl() ? ' no_item_photo ' : '' ?>;'>
                  <?php if( $attachment->meta->mode == 0 ): // Silence  ?>
                  <?php elseif( $attachment->meta->mode == 1 && $attachmentsCount == 1 ): // Thumb/text/title type actions       ?>
                    <div class="feed_attachment_aaf">
                      <?php
                      if( $attachment->item->getType() == "core_link" ) {
                        $attribs['target'] = '_blank';
                      }
                      ?>
                      <?php if( $attachment->item->getPhotoUrl() ): ?>
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
                              <?php $host = strpos('www.', $parse['host']) == 0 ? substr($parse['host'], 4) : $parse['host'] ?>
                              <div class='feed_item_link_domain'><?php echo $this->htmlLink($attachment->item->getHref(), $host, $attribs) ?></div>
                            <?php endif; ?>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php elseif( $attachment->meta->mode == 2 || ($attachment->meta->mode == 1 && count($action->getAttachments()) > 1) ):  // Thumb only type actions    ?>
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
                      $photoContent = $this->itemPhoto($attachment->item, $attachmentsCount < 6 ? 'thumb.main' : 'thumb.normal', $attachment->item->getTitle(), array());
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
    </div>

  </div>
  <?php
} catch( Exception $e ) {
  if( APPLICATION_ENV === 'development' ) {
    echo $e->__toString();
  }
};
?>