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

<div class="sr_prpfile_photos_strip">
  <a id="sr_crousal_photoPrev_<?php echo $this->includeInWidget ? $this->includeInWidget : $this->identity ?>" class="photoPrev sr_option_button photoLeft" style="visibility: hidden; <?php if(!($this->itemCount < $this->total_images)):?>display:none; <?php endif;?>"></a>
  <div class="sr_photo_scroll" id="sr_ul_photo_scroll_<?php echo $this->includeInWidget ? $this->includeInWidget : $this->identity ?>" style="width:<?php echo ($this->itemCount * 56) ?>px">
    <ul class="">
      <?php foreach ($this->photo_paginator as $photo): ?>
        <li class="liPhoto">
          <div class='photoThumb'>
            <a href="<?php echo $photo->getHref(); ?>" class="thumbs_photo">
              <?php echo $this->itemPhoto($photo, 'thumb.icon', '', array('align' => 'center')); ?></a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <a id="sr_crousal_photoNext_<?php echo $this->includeInWidget ? $this->includeInWidget : $this->identity ?>" class="photoNext sr_option_button photoRight" style="visibility: hidden; <?php if(!($this->itemCount < $this->total_images)):?>display:none; <?php endif;?> "></a>
</div>