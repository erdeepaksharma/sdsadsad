<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _compareButton.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php 
  $this->headTranslate(array('Compare All', 'Remove All', 'Compare', 'Show Compare Bar', 'Please select more than one entry for the comparison.', 'Hide Compare Bar'));

	$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
	$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js');
?>

<?php if($this->buttonType=='pinboard-button'):?>
  <a><span class="compareListing sr_compare_button">
    <input type="checkbox" class="checkListing compareButtonListing<?php echo $this->item->getIdentity() ?>" name="<?php echo $this->escape($this->item->getTitle()) ?>" id="listing_<?php echo $this->item->getIdentity() ?>" value="<?php echo $this->item->getIdentity() ?>"  />
    <label class="srlbCompare" for="listing_<?php echo $this->item->getIdentity() ?>"><?php echo $this->translate('Compare') ?></label>
    <span id="listingID<?php echo $this->item->getIdentity() ?>" class="listingType<?php echo $this->category_id ?>" style="display:none;"><?php echo $this->translate($this->category_title) ?></span>
    <span id="listingUrl<?php echo $this->item->getIdentity() ?>" style="display:none;"><?php echo $this->item->getHref() ?></span>
    <span id="listingImgSrc<?php echo $this->item->getIdentity() ?>" style="display:none;"><?php echo $this->item->getPhotoUrl('thumb.icon') ? $this->item->getPhotoUrl('thumb.icon'): $this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/images/nophoto_listing_thumb_icon.png'; ?></span>
  </span></a>
<?php else:?>
  <span class="compareListing sr_compare_button">
    <input type="checkbox" class="checkListing compareButtonListing<?php echo $this->item->getIdentity() ?>" name="<?php echo $this->escape($this->item->getTitle()) ?>" id="listing_<?php echo $this->item->getIdentity() ?>" value="<?php echo $this->item->getIdentity() ?>"  />
    <label class="srlbCompare" for="listing_<?php echo $this->item->getIdentity() ?>"><?php echo $this->translate('Compare') ?></label>
    <span id="listingID<?php echo $this->item->getIdentity() ?>" class="listingType<?php echo $this->category_id ?>" style="display:none;"><?php echo $this->translate($this->category_title) ?></span>
    <span id="listingUrl<?php echo $this->item->getIdentity() ?>" style="display:none;"><?php echo $this->item->getHref() ?></span>
    <span id="listingImgSrc<?php echo $this->item->getIdentity() ?>" style="display:none;"><?php echo $this->item->getPhotoUrl() ? $this->item->getPhotoUrl(): $this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/images/nophoto_listing_thumb_icon.png'; ?></span>
  </span>
<?php endif; ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    $$('.compareButtonListing<?php echo $this->item->getIdentity()  ?>').removeEvents('click', compareSiterivewContent.compareButtonEvent.bind(compareSiterivewContent));
    $$('.compareButtonListing<?php echo $this->item->getIdentity()  ?>').addEvent('click', compareSiterivewContent.compareButtonEvent.bind(compareSiterivewContent));
    compareSiterivewContent.updateCompareButtons();
  });
</script>