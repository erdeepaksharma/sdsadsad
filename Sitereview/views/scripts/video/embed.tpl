<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: embed.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div class="global_form_popup">
  <?php if ($this->error == 1): ?>
    <?php echo $this->translate('Embedding of videos has been disabled.') ?>
    <?php return ?>
  <?php elseif ($this->error == 2): ?>
    <?php echo $this->translate('Embedding of videos has been disabled for this video.') ?>
    <?php return ?>
  <?php elseif (!$this->video || $this->video->status != 1): ?>
    <?php echo $this->translate('The video you are looking for does not exist or has not been processed yet.') ?>
    <?php return ?>
  <?php endif; ?>

  <textarea cols="50" rows="4"><?php
  echo $this->embedCode;
  ?></textarea>
  <br />
  <br />
  <div>
    <button  onclick='javascript:parent.Smoothbox.close()' ><?php echo $this->translate('Close') ?></button>
  </div>
</div>