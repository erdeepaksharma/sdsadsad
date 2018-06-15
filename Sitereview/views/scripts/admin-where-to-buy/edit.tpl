<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: edit.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<div class='global_form_popup sr_wheretobuy_popup'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php if ($this->item->getIdentity() != 1): ?>
  <div id="photo_icon_preview">
    <div id="photo-preview" class="form-label"><label for="photo" class="optional">Icon Preview</label></div>
    <?php if ($this->item->photo_id): ?>
    	<span class="sr_price_info_image">
      	<?php echo $this->itemPhoto($this->item, null, "", array('style' => 'max-height:48px;')); ?>
      </span>
    <?php else: ?>
    <span class="sr_price_info_image">
      <?php echo $this->item->getTitle(); ?>
    </span>
    <?php endif; ?>
  </div>
  <script type="text/javascript">
    $('photo_icon_preview').inject($('photo-wrapper'),'after');
  </script>
<?php endif; ?>