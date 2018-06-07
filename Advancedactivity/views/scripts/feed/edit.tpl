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
?>

<div class='seao_smoothbox_lightbox_header'>
  <div class="header_title"><?php echo $this->translate('Edit Post') ?></div>
</div>
<div class="seao_smoothbox_lightbox_inner_content">
  <?php if( $this->status ): ?>
    <?php echo $this->editPost($this->action, array('inPopup' => true)); ?>
  <?php else: ?>
    <div class="tip">
      <span><?php echo $this->error; ?></span>
    </div>
  <?php endif; ?>
</div>

