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
<?php if ($this->total_images): ?>
	 <?php if ($this->allowed_upload_photo): ?>
		<div class="profile-content-top-button" data-role="controlgroup" data-type="horizontal">
			<a data-role="button" data-icon="plus" data-iconpos="left" data-inset = 'false' data-mini="true" data-corners="true" data-shadow="true" href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id,'content_id' => $this->identity), "sitereview_photoalbumupload_listtype_$this->listingtype_id", true) ?>'  class='buttonlink icon_sitereviews_photo_new'><?php echo $this->translate('Add Photos'); ?></a>
		</div>
	<?php endif; ?>
		<ul class="thumbs thumbs_nocaptions" id="profile_sitereviewalbums">
			<?php foreach ($this->paginator as $album): ?>
				<li>
					<a class="thumbs_photo" href="<?php echo $album->getHref(); ?>">
            <span style="background-image: url(<?php echo $album->getPhotoUrl('thumb.normal'); ?>);"></span>
					</a> 
				</li>
			<?php endforeach; ?>   
		</ul>
		<?php if ($this->paginator->count() > 1): ?>
			<?php
			echo $this->paginationAjaxControl(
							$this->paginator, $this->identity, 'profile_sitereviewalbums', array('itemCount' => $this->itemCount));
			?>
		<?php endif; ?>
<?php else: ?>
	<div class="tip">
		<span>
			<?php $url = $this->url(array('listing_id' => $this->sitereview->listing_id, 'content_id' => $this->identity), "sitereview_photoalbumupload_listtype_$this->listingtype_id", true);?>
			<?php if ($this->allowed_upload_photo): ?>
				<?php echo $this->translate('You have not added any photo in your '.$this->listing_singular_lc.'. %1$sClick here%2$s to add your first photo.', "<a href='$url'>", "</a>"); ?>
			<?php else:?>
				<?php echo $this->translate('You have not added any photo in your '.$this->listing_singular_lc.'.');?>
			<?php endif;?>
		</span>
	</div>
<?php endif; ?>

<style type="text/css">

.layout_sitereview_photos_sitereview > h3 {
	display:none;
}

</style>