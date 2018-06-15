<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css'); ?>

<div class="sr_editor_profile_info">
  <div class="sr_editor_profile_photo">
    <?php echo $this->htmlLink($this->user->getHref(), $this->itemPhoto($this->user, 'thumb.profile', '', array('align' => 'center'))) ?>
  </div>

  <?php if (!$this->user->isSelf($this->viewer()) && $this->user->email): ?>
    <div class="sr_editor_listing_stat"><b><?php echo $this->htmlLink(array('route'=>'sitereview_review_editor','action' => 'editor-mail', 'user_id' => $this->user->user_id), $this->translate('Email %s', $this->user->getTitle()), array('class' => 'smoothbox sr_icon_send buttonlink')) ?></b></div>
  <?php endif; ?>
    
  <div class="sr_editor_listing_stat">
    <?php echo $this->htmlLink($this->user->getHref(), $this->translate('View full profile'), array('class' => 'sr_icon_editor_profile buttonlink'));?></b>
  </div>
    
  <div class="sr_editor_listing_stat">
    <?php echo $this->htmlLink(array('route' => "sitereview_review_editor", 'action' => 'home'), $this->translate('View all Editors'), array('class' => 'sr_icon_editor buttonlink')) ?>
  </div>    
</div>	