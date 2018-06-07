<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: remove-tag.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?><?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/core.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/seaomooscroll/SEAOMooVerticalScroll.js');

$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');


$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Activity/externals/scripts/core.js');

$this->videoPlayerJs();

$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Seaocore/externals/styles/style_infotooltip.css');

$this->headTranslate(array('ADVADV_SHARE', 'Who are you with?', 'with'));
?>
<?php if ($this->layout == 'feed_tip'): ?>
  <div class="info_tip_wrapper" >
    <div class="uiOverlay info_tip" style="top: 0px; ">
      <div class="info_tip_arrow <?php if ($this->columnRight): ?>info_tip_arrow_right <?php else: ?> info_tip_arrow_left <?php endif; ?>"></div>
      <div class="info_tip_content_wrapper" id="activity-feed-tip-wrapper-<?php echo $this->action_id ?>">
        <div class=" scroll_content" style="width:auto;max-height: 500px;" id="activity-feed-tip-content-<?php echo $this->action_id ?>">
        <?php endif; ?>      
        <ul class="feed" <?php if ($this->layout == 'feed_tip'): ?> style="padding: 10px;  min-width: 500px; max-width: 600px;min-height: 150px; max-height: 500px; background: white" <?php endif; ?>>
          <?php echo $this->content()->renderWidget("advancedactivity.feed", array('feedOnly' => true, 'action_id' => $this->action_id, 'viewAllLikes' => true, 'viewAllComments' => true, 'onViewPage' => true)) ?>
        </ul>
        <?php if ($this->layout == 'feed_tip'): ?>   
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      (function() {
        if (!$('activity-feed-tip-wrapper-<?php echo $this->action_id ?>'))
          return;
        if ($('activity-feed-tip-wrapper-<?php echo $this->action_id ?>').getElement('.verticalScroll'))
          return;

        var width = $('activity-feed-tip-wrapper-<?php echo $this->action_id ?>').offsetWidth;
        width = width - 2;
        $('activity-feed-tip-wrapper-<?php echo $this->action_id ?>').setStyle('width', width);
        width = width - 8;
        $('activity-feed-tip-content-<?php echo $this->action_id ?>').setStyle('width', width);
        var shortFeedVerticalScroll = new SEAOMooVerticalScroll('activity-feed-tip-wrapper-<?php echo $this->action_id ?>', 'activity-feed-tip-content-<?php echo $this->action_id ?>', {});
        Smoothbox.bind($('activity-feed-tip-wrapper-<?php echo $this->action_id ?>'));
      }).delay(500);
    })
  </script>
<?php endif; ?>  