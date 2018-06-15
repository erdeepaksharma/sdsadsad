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
<?php $photo_type = $this->listingType->photo_type;?>
<div class="sr_profile_cover_photo_wrapper">
	<?php if (!empty($this->sitereview->featured) && $this->show_featured): ?> 
		<div class="sr_profile_sponsorfeatured"  style='background: <?php echo $this->featured_color; ?>;'>
			<?php echo $this->translate('FEATURED');?>	
		</div>
	<?php endif; ?>
	<div class='sr_profile_cover_photo <?php if ($this->can_edit && ($photo_type == 'listing')):?>sr_photo_edit_wrapper<?php endif;?>'>
		<?php if (!empty($this->can_edit) && ($photo_type == 'listing')) : ?>
			<a class='sr_photo_edit' href="<?php echo $this->url(array('action' => 'change-photo', 'listing_id' => $this->sitereview->listing_id), "sitereview_dashboard_listtype_$this->listingtype_id", true) ?>">
				<i class="sr_icon"></i>
				<?php echo $this->translate('Change Picture'); ?>
			</a>
		<?php endif;?>
		<?php if($this->sitereview->newlabel):?>
			<i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
		<?php endif;?>
		<?php if($this->listingType->photo_id == 0):?>
			<a href="<?php echo $this->sitereview->getHref(array('profile_link' => 1)); ?>"></a>
		<?php endif;?>
		<?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '' , array('align' => 'center')); ?>
	</div>
	<?php if (!empty($this->sitereview->sponsored) && $this->show_sponsered): ?>
		<div class="sr_profile_sponsorfeatured" style='background: <?php echo $this->sponsored_color; ?>;'>
			<?php echo $this->translate('SPONSORED'); ?>
		</div>
	<?php endif; ?>
	<?php if($this->ownerName): ?>
	  <div class='sr_profile_cover_name'>
	    <?php echo $this->htmlLink($this->sitereview->getOwner()->getHref(), $this->sitereview->getOwner()->getTitle()) ?>
	  </div>
	<?php endif; ?>
</div>

