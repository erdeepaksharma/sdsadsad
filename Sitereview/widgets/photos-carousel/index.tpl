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

<?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');
?>

<div class="sr_prpfile_photos_strip">
  <a id="sr_crousal_photoPrev_<?php echo $this->includeInWidget ? $this->includeInWidget : $this->identity ?>" class="photoPrev sr_option_button photoLeft" style="visibility: hidden; <?php if(!($this->itemCount < $this->total_images)):?>display:none; <?php endif;?>"></a>
  <div class="sr_photo_scroll" id="sr_ul_photo_scroll_<?php echo $this->includeInWidget ? $this->includeInWidget : $this->identity ?>" style="width:<?php echo ($this->itemCount * 56) ?>px">
    <ul class="">
      <?php foreach ($this->photo_paginator as $photo): ?>
        <li class="liPhoto">
          <div class='photoThumb'>
            <a href="<?php echo $photo->getHref(); ?>" <?php if (SEA_LIST_LIGHTBOX) : ?> onclick='openSeaocoreLightBox("<?php echo $photo->getHref(); ?>");return false;' <?php endif; ?>>
              <?php echo $this->itemPhoto($photo, 'thumb.icon', '', array('align' => 'center')); ?></a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <a id="sr_crousal_photoNext_<?php echo $this->includeInWidget ? $this->includeInWidget : $this->identity ?>" class="photoNext sr_option_button photoRight" style="visibility: hidden; <?php if(!($this->itemCount < $this->total_images)):?>display:none; <?php endif;?> "></a>
</div>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    new Fx.Scroll.Carousel('sr_ul_photo_scroll_<?php echo $this->includeInWidget ? $this->includeInWidget :  $this->identity ?>',{
      mode: 'horizontal',
      childSelector:'.liPhoto',
      noOfItemPerPage:<?php echo $this->itemCount?>,
      noOfItemScroll:<?php echo $this->itemCount?>,
      navs:{
        frwd:'sr_crousal_photoNext_<?php echo $this->includeInWidget ? $this->includeInWidget :  $this->identity ?>',
        prev:'sr_crousal_photoPrev_<?php echo $this->includeInWidget ? $this->includeInWidget :  $this->identity ?>'
      }
    });
        
  });
</script>
