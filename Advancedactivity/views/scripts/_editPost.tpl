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
      if(!empty($this->action->publish_date)){ 
            $scheduledDate = Engine_Api::_()->advancedactivity()->dbToUserDateTime(array('starttime' => $this->action->publish_date));
            $publish_date = $scheduledDate['starttime'];
      }
      $composerOptions = $this->settings('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy","postTarget","schedulePost")); 
      $allowTargetPost = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $this->viewer, 'aaf_targeted_post_enable') && (in_array('postTarget',$composerOptions));
      $allowScheduledPost = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $this->viewer, 'aaf_schedule_post_enable') && (in_array('schedulePost',$composerOptions)) && !empty($publish_date);
      $formId =  $this->form->body->getAttrib('id');
?>

<div class="adv_post_container feed_item_body_edit_content" style="display:<?php echo empty($this->options['inPopup']) ? 'none' : 'block'; ?>;">
  <?php ?>
  <form enctype="application/x-www-form-urlencoded" class="global_form_edit_post adv-active" action="<?php echo $this->form->getAction() ?>" method="post">
    <div class="adv_post_container_box seaocore_box_sizing">
      <div class="adv_post_box">
        <div id="compose-top-tray" class="compose-tray compose-top-tray adv_post_container_attachment" style="display:none;"></div>
        <textarea name="body" id="<?php echo $formId ?>" cols="45" rows="3" style="overflow-x: auto; overflow-y: hidden; resize: none; height: 0px;"><?php echo $this->form->body->getValue(); ?></textarea>
        <div class="aaf_postbox_options" id="aaf_postbox_edit_options">
          <?php if( in_array("emotions", $composerOptions) ) : ?>
            <?php echo $this->partial('_emojiBoard.tpl', 'advancedactivity', array('idPrefix' => 'edit_feed_' . $this->action->getIdentity() . '_', 'contentClass' => 'aaf_feed_edit_emoji')); ?>
          <?php endif; ?>
        </div>
        <div class="composer_preview_display_tray dnone" id="edit_composer_preview_display_tray"></div>
      </div>
    </div>
    <?php if( $allowScheduledPost ): ?>
          <div id="aaf_edit_schedule_time_<?php echo $formId ?>" class="dnone">
            <div class="aaf_smoothbox_popup_overlay"></div>
            <div class="aaf_smoothbox_popup seaocore_box_sizing">
              <div class="aaf_smoothbox_popup_header"><h3><?php echo $this->translate("Schedule Your Post") ?></h3></div>
              <div class="aaf_smoothbox_popup_container">
                <p><?php echo $this->translate("Select date and time on which you want to publish your post") ?></p>
                <?php
                $scheduleForm = new Advancedactivity_Form_SchedulePost(array('item' => 'edit_schedule_time'));
                $scheduleForm->edit_schedule_time->setAttrib('loadedbyAjax','true');
                $scheduleForm->edit_schedule_time->setDescription($this->userTimeZone());
                $scheduleForm->edit_schedule_time->setValue($publish_date);
                echo $scheduleForm->edit_schedule_time;
                ?>
              </div>
              <div class="aaf_smoothbox_popup_buttons">
                <button type="submit" class="aaf_edit_schedule_time_schedule"><?php echo $this->translate("Schedule"); ?></button>
                <button type="button" class="aaf_edit_schedule_time_cancel"><?php echo $this->translate("Cancel"); ?></button>
              </div>
            </div>
          </div>
    <?php endif; ?>
    <input type="hidden" name="action_id" value="<?php echo $this->form->action_id->getValue(); ?>" >
    <?php $privacy = $this->action->privacy ?>
    <input type="hidden" id="aaf_edit_auth_view" name="auth_view" value="<?php echo $privacy ?>" />
                
    <div class="adv-activeity-post-container-bottom" id="edit_advanced_compose-menu" >
      <div class="edit-activeity-post-container-right-bottom">
        <?php if( !empty($privacy) && $this->privacyDropdownList ): ?>
          <div class="advancedactivity_privacy_list"  id="edit_aaf_privacy_contents_wrapper">
            <span class="aaf_privacy_pulldown" id="pulldown_edit_privacy_list" onClick="toggleEditPrivacyPulldown(event, this)">
              <div class="aaf_pulldown_contents_wrapper">
                <div class="aaf_pulldown_contents">
                  <ul>
                    <?php $privacy_titile_array = array(); ?>
                    <?php foreach( $this->privacyDropdownList as $key => $value ): ?>
                      <?php if( $value == "separator" ): ?>
                        <!--<li class="sep"></li>-->
                      <?php elseif( $key == 'network_custom' ): ?>
                        <li onclick="editPostStatusPrivacy(this, '<?php echo $key ?>')"
                            class="aaf_custom_list" title="<?php echo $this->translate("Choose multiple Networks to share with.");
                        ?>"><i class="aaf_privacy_pulldown_icon aaf_icon_feed_custom"></i>
                          <div id="aaf_edit_network_list_custom_div"><?php echo $this->translate($value); ?></div></li>
                      <?php elseif( strpos($key, "custom") !== false ): ?>
                        <li onclick="editPostStatusPrivacy(this, '<?php echo $key ?>')"
                            class="aaf_custom_list" title="<?php echo $this->translate("Choose multiple Friend Lists to share with.");
                        ?>"><i class="aaf_privacy_pulldown_icon aaf_icon_feed_custom"></i>
                          <div id="aaf_edit_friend_list_custom_div"><?php echo $this->translate($value); ?></div></li>
                      <?php elseif( in_array($key, array("everyone", "networks", "friends", "onlyme")) ): ?>
                        <?php
                        if( $key == $privacy ):
                          $privacy_icon_class = "aaf_icon_feed_" . $key;
                          $privacy_titile = $value;
                        endif;
                        ?>
                        <li class="<?php echo ( $key == $privacy ? 'aaf_tab_active' : 'aaf_tab_unactive' ) ?> user_profile_friend_list_<?php echo $key ?> aaf_custom_list" id="aaf_edit_privacy_list_<?php echo $key ?>" onclick="editPostStatusPrivacy(this, '<?php echo $key ?>')" title="<?php echo $this->translate("Share with %s", $this->translate($value)); ?>" >
                          <i class="aaf_privacy_pulldown_icon aaf_icon_feed_<?php echo $key ?>"></i>
                          <div><?php echo $this->translate($value); ?></div>
                        </li>
                      <?php else: ?>
                        <?php
                        if( (in_array($key, explode(",", $privacy)) ) ):
                          $privacy_titile_array[] = $value;
                        endif;
                        ?>
                        <li class="<?php echo ( (in_array($key, explode(",", $privacy))) ? 'aaf_tab_active' : 'aaf_tab_unactive' ) ?> user_profile_friend_list_<?php echo $key ?> aaf_custom_list" id="aaf_edit_privacy_list_<?php echo $key ?>" onclick="editPostStatusPrivacy(this, '<?php echo $key ?>')" title="<?php echo $this->translate("Share with %s", $value); ?>">
                          <i class="aaf_privacy_pulldown_icon <?php echo strpos($key, "network_") !== false ? "aaf_icon_feed_network_list" : "aaf_icon_feed_list" ?>"></i> 
                          <div><?php echo $this->translate($value) ?></div>
                        </li>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <?php
                    if( !empty($privacy_titile_array) ):
                      $privacy_titile = join(", ", $privacy_titile_array);
                      if( Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($privacy) ):
                        $privacy_icon_class = (count($privacy_titile_array) > 1) ? "aaf_icon_feed_custom" : "aaf_icon_feed_network_list";
                      else:
                        $privacy_icon_class = (count($privacy_titile_array) > 1) ? "aaf_icon_feed_custom" : "aaf_icon_feed_list";
                      endif;
                    endif;
                    ?>
                  </ul>
                </div>
              </div>
              <p class="adv_privacy_list_tip adv_composer_tip dnone">
                <span id="adv_edit_privacy_lable_tip"> <?php echo $this->translate("Share with %s", $this->translate($privacy_titile)) ?></span>
                <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/tooltip-arrow-down.png" alt="" />
              </p>
              <a href="javascript:void(0);" id="show_aaf_edit_privacy" class="aaf_privacy_pulldown_button">
                <i class="aaf_privacy_pulldown_icon <?php echo $privacy_icon_class ?>"></i>
                <span><?php echo $this->translate($privacy_titile) ?></span>
                <i class="aaf_privacy_pulldown_arrow"></i>
              </a>
            </span>
          </div>
        <?php endif; ?>
        <div class="advanced_compose-menu-buttons" id="edit_advanced_compose-menu-buttons">
          <div>
             <?php if( $allowTargetPost ): ?>
              <!-- <a  href="javascript:void(0);" class="button_link adv_button_target_post compose-activator" title="<?php echo $this->translate("Target your Post") ?>" onclick="showHideTargetUserOptions()" ><i class="fa fa-crosshairs"></i></a> -->
            <?php endif; ?>
            <?php if( $allowScheduledPost ): ?>
                  <a  href="javascript:void(0);" class="button_link adv_button_schedule_post active" title="<?php echo $this->translate("Schedule your Post") ?>" onclick="showHideScheduleTimeOptions('aaf_edit_schedule_time_<?php echo $formId ?>')" ><i class="fa fa-calendar"></i></a>
            <?php endif; ?>
            <button name="submit" id="submit" type="submit"><?php echo $this->translate('Edit Post') ?></button>
            <button name="cancel" id="cancel" type="button" class="feed-edit-cancel"><?php echo $this->translate('Cancel') ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    en4.advancedactivity.bindEditFeed(<?php echo $this->action->getIdentity() ?>, {
      lang: {
        'Post Something...': '<?php echo $this->string()->escapeJavascript($this->translate('Post Something...')) ?>'
      },
      allowEmptyWithoutAttachment: <?php echo!empty($this->action->attachment_count) ? 1 : 0 ?>,
      inPopup: <?php echo empty($this->options['inPopup']) ? '0' : '1'; ?>,
      allowEmotions: <?php echo in_array("emotions", $composerOptions) ? 0 : 1 ?>
    });
  });

  var toggleEditPrivacyPulldown = function (event, element) {
    event = new Event(event);
    event.stop();
    $$('.advancedactivity_privacy_list').each(function (otherElement) {
      var pulldownElement = otherElement.getElement('aaf_privacy_pulldown_active');
      if (pulldownElement) {
        pulldownElement.addClass('aaf_privacy_pulldown').removeClass('aaf_privacy_pulldown_active');
      }
    });
    //  element.getParent('.advancedactivity_privacy_list').addClass('')
    if ($(element).hasClass('aaf_privacy_pulldown')) {
      element.removeClass('aaf_privacy_pulldown').addClass('aaf_privacy_pulldown_active');
    } else {
      element.addClass('aaf_privacy_pulldown').removeClass('aaf_privacy_pulldown_active');
    }
  }
  en4.core.runonce.add(function () {
      $$('.aaf_edit_schedule_time_schedule').addEvent('click', function (e) {
        e.stop();
        if ($('edit_schedule_time-date').get('value') === '' || $('edit_schedule_time-hour').get('value') === '' || $('edit_schedule_time-minute').get('value') === '') {
          return;
        }
        $('aaf_edit_schedule_time_<?php echo $formId ?>').addClass('dnone');
      });
      $$('.aaf_edit_schedule_time_cancel').addEvent('click', function(){
            $('aaf_edit_schedule_time_<?php echo $formId ?>').addClass('dnone');
      });
    });
</script>
<?php foreach( $this->composePartials as $partial ): ?>
  <?php
  echo $this->partial($partial[0], $partial[1], array("isAFFWIDGET" => 1,
    'forEdit' => $this->action->getIdentity(), 'action' => $this->action))
  ?>
<?php endforeach; ?>
<style type="text/css">
  #edit_schedule_time-element >  .event_calendar_container {
    margin-right: 5px;
  }
  #edit_schedule_time-element >  .event_calendar_container > button {
    margin-top: 5px;
  }
  #calendar_output_span_edit_schedule_time-date.calendar_output_span {
    vertical-align: middle;
  }
</style>
