<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _targetUser.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$viewerId = $this->viewer()->getIdentity();
?>
<?php if( $viewerId ): ?>
  <?php
  $allowPin = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $this->viewer(), 'aaf_pinunpin_enable');
  $allowNotificationOnOff = $this->settings('aaf.notification.onoff.enable', 1);

  $privacy_titile_array = array();
  $allowAction = $viewerId && (
    $this->activity_moderate || $this->is_owner || (
    $this->allow_delete && ( $this->viewer()->isSelf($action->getSubject()) || $this->viewer()->isSelf($action->getObject()))
    ));

  $allowReport = !$this->subject() && $viewerId && !$this->viewer()->isSelf($action->getSubject()) && $action->getTypeInfo()->type != 'birthday_post';
  ?>

  <div class="aaf_actions_links">
    <span class="aaf_tabs_feed_tab_closed aaf_pulldown_btn_wrapper" style="float:left">
      <div class="aaf_pulldown_contents_wrapper">
        <div class="aaf_pulldown_contents">
          <ul>
            <?php if( $this->allowSaveFeed ): ?>
              <li class="feed_item_option_delete">
                <a href="javascript:void(0);" title="" onclick="en4.advancedactivity.updateSaveFeed('<?php echo $action->action_id ?>')">
                  <?php echo $this->translate(($advancedactivitySaveFeed->getSaveFeed($this->viewer(), $action->action_id)) ? 'Unsaved Feed' : 'Save Feed') ?></a>
              </li>
            <?php endif; ?>
            <li>
              <a href="javascript:void(0);" onclick='showLinkPost("<?php echo $item->getHref(array('action_id' => $action->action_id, 'show_comments' => true)) ?>")'><?php echo $this->translate('Feed Link'); ?></a>
            </li>
            <?php if( $allowReport ) : ?>
              <!--<li class="sep"></li>-->
              <li>
                <a href="javascript:void(0);"
                   onclick='hideItemFeeds("<?php echo $action->getType() ?>", "<?php echo $action->getIdentity() ?>", "<?php echo $item->getType() ?>", "<?php echo $item->getIdentity() ?>", "<?php echo $this->string()->escapeJavascript(html_entity_decode($item->getTitle())); ?>", "");' 
                   ><?php echo $this->translate('Hide'); ?></a>
              </li>
              <li>
                <a href="javascript:void(0);"
                   onclick='hideItemFeeds("<?php echo $action->getType() ?>", "<?php echo $action->getIdentity() ?>", "<?php echo $item->getType() ?>", "<?php echo $item->getIdentity() ?>", "<?php echo $this->string()->escapeJavascript(html_entity_decode($item->getTitle())); ?>", "<?php
                   echo $this->url(array('module' => 'advancedactivity', 'controller' => 'report', 'action' => 'create', 'subject' =>
                     $action->getGuid(), 'format' => 'smoothbox'), 'default', true);
                   ?>");'>
                     <?php echo $this->translate('Report Feed'); ?>
                </a>
              </li>
              <!--<li class="sep"></li>-->
              <li>
                <a href="javascript:void(0);" onclick='hideItemFeeds("<?php echo $item->getType() ?>", "<?php echo $item->getIdentity() ?>", "", "<?php echo $action->getIdentity() ?>", "<?php echo $this->string()->escapeJavascript(html_entity_decode($item->getTitle())); ?>", "");' ><?php echo $this->translate('Hide all by %s', $item->getTitle()); ?></a>
              </li>
            <?php endif; ?>
            <?php if( $allowAction ): ?>
              <!--<li class="sep"></li>-->
              <?php if( $allowEdit ): ?>
                <li class="feed_item_option_edit">
                  <a href="<?php
                   echo $this->url(array('module' => 'advancedactivity', 'controller' => 'feed', 'action' => 'edit', 'action_id' => $action->getIdentity()), 'default', true);
                   ?>" class="seao_smoothbox" data-SmoothboxSEAOClass="aaf_feed_edit_popup"><?php echo $this->translate('Edit Feed') ?></a>
                </li>
              <?php endif; ?>
              <li class="feed_item_option_delete">
                <a href="javascript:void(0);" title="" onclick="deletefeed('<?php echo $action->action_id ?>', '0', '<?php echo $this->escape($this->url(array('route' => 'default', 'module' => 'advancedactivity', 'controller' => 'index', 'action' => 'delete'))) ?>')">
                  <?php echo $this->translate('Delete Feed') ?></a>
              </li>
              <?php if( $allowNotificationOnOff ): ?>
                <li class="feed_item_option_notification">

                  <?php
                  if( !$offNotification->isSetNotificationOff($action->action_id, $this->viewer()->getIdentity()) ):
                    $onDisplay = 'none';
                    $offDisplay = 'block';
                  else:
                    $onDisplay = 'block';
                    $offDisplay = 'none';

                  endif;
                  ?>
                  <a id="turn_on_notification_<?php echo $action->action_id ?>"  style="display:<?php echo $onDisplay; ?>" href="javascript:void(0);" title="" onclick="turnOnOffNotification('<?php echo $action->action_id ?>', 1)"><?php echo $this->translate('Turn On Notification') ?></a>
                  <a id="turn_off_notification_<?php echo $action->action_id ?>" style="display:<?php echo $offDisplay; ?>" href="javascript:void(0);" title="" onclick="turnOnOffNotification('<?php echo $action->action_id ?>', 0)"><?php echo $this->translate('Turn Off Notification') ?></a>

                </li>
              <?php endif; ?>

              <?php if( $allowEdit && $allowPin && Engine_Api::_()->core()->hasSubject() ) : ?>
              <?php
              $itemSubject = Engine_Api::_()->core()->getSubject();
              if( $itemSubject->getGuid()== $action->getObject()->getGuid()):
                if( $pinSettings->isSetPin($action->action_id) ) :
                  $onDisplay = 'none';
                  $offDisplay = 'block';
                else:
                  $onDisplay = 'block';
                  $offDisplay = 'none';
                endif;
                ?>
                <li class="feed_item_option_pinunpin">
                    <a id="turn_pin_<?php echo $action->action_id ?>"  style="display:<?php echo $onDisplay; ?>" href="javascript:void(0);" title="" onclick="setPinTime('<?php echo $action->action_id ?>', '<?php echo $action->getObject()->getGuid() ?>', 1)"><?php echo $this->translate('Pin This Post') ?></a>
                    <a id="turn_unpin_<?php echo $action->action_id ?>" style="display:<?php echo $offDisplay; ?>" href="javascript:void(0);" title="" onclick="setPinTime('<?php echo $action->action_id ?>', '<?php echo $action->getObject()->getGuid() ?>', 0)"><?php echo $this->translate('Unpin This Post') ?></a>
                </li>
                <?php endif; ?>
              <?php endif; ?>
              <?php if( $this->allowEditCategory && in_array($action->getTypeInfo()->type, array("post", "post_self", "status")) ): ?>
                <li class="feed_item_option_delete">
                  <?php
                  echo $this->htmlLink(array(
                    'route' => 'default',
                    'module' => 'advancedactivitypost',
                    'controller' => 'index',
                    'action' => 'edit-category',
                    'action_id' => $action->action_id
                    ), $this->translate('Edit Category'), array('class' => 'smoothbox'))
                  ?>
                </li>
              <?php endif; ?>
              <?php if( $action->getTypeInfo()->commentable ): ?>
                <li class="feed_item_option_delete">
                  <a href="javascript:void(0);" title="" onclick="en4.advancedactivity.updateCommentable('<?php echo $action->action_id ?>')">
                    <?php echo $this->translate(($action->commentable) ? 'Disable Comments' : 'Enable Comments') ?></a>
                </li>
              <?php endif; ?>
              <?php if( $action->getTypeInfo()->shareable > 1 || ($action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment())) ): ?>
                <li class="feed_item_option_delete">
                  <a href="javascript:void(0);" title="" onclick="en4.advancedactivity.updateShareable('<?php echo $action->action_id ?>')">
                    <?php echo $this->translate(($action->shareable) ? 'Lock this Feed' : 'Unlock this Feed') ?></a>
                </li>
              <?php endif; ?>
            <?php endif; ?>
          </ul>

        </div>
      </div>
      <span class="aaf_pulldown_btn" onclick="moreEditOptionsSwitch($(this));"></span>
    </span>
    <?php 
      $thisSubject = $this->subject();
     if( $allowPin && !empty($thisSubject) && ($thisSubject->getType() == $action->getObject()->getType()) && $pinSettings->isSetPin($action->getIdentity()) ): ?>
      <span id="pin_icon_<?php echo $action->getIdentity(); ?>" class="aaf_pin_icon" ></span> 
    <?php endif; ?>
  </div>
<?php endif; ?>