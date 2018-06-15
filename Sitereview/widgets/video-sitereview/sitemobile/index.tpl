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

<?php if($this->paginator->getTotalItemCount() > 0) :?>

	<?php if ($this->allowed_upload_video): ?>
		<div class="profile-content-top-button" data-role="controlgroup" data-type="horizontal">
			<a data-role="button" data-icon="plus" data-iconpos="left" data-inset = 'false' data-mini="true" data-corners="true" data-shadow="true" href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id, 'content_id' => $this->identity), "sitereview_video_create_listtype_$this->listingtype_id", true) ?>'  class='buttonlink icon_sitereviews_video_new'><?php echo $this->translate('Add Video'); ?></a>
    </div>
	<?php endif; ?>

	<div class="sm-content-list ui-listgrid-view"  id="profile_sitereviewvideos">
		<ul data-role="listview" data-inset="false" data-icon="arrow-r">
		  <?php foreach( $this->paginator as $item ): ?>
				<li>  
					<a href="<?php echo $item->getHref(array('content_id' => $this->identity)); ?>">
					<?php
						if( $item->photo_id ) {
							echo $this->itemPhoto($item, 'thumb.profile');
						} else {
							echo '<img alt="" src="' . $this->escape($this->layout()->staticBaseUrl) . 'application/modules/Video/externals/images/video.png">';
						}
					?>
					<div class="ui-listview-play-btn"><i class="ui-icon ui-icon-play"></i></div>
					<h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($item->getTitle(), $this->title_truncation) ?></h3>
					<?php if( $item->duration ): ?>
						<p class="ui-li-aside">
							<?php
								if( $item->duration >= 3600 ) {
									$duration = gmdate("H:i:s", $item->duration);
								} else {
									$duration = gmdate("i:s", $item->duration);
								}
								echo $duration;
							?>
						</p>
					<?php endif ?>
					<p><?php echo $this->translate('By'); ?>
						<strong><?php echo $item->getOwner()->getTitle(); ?></strong>
					</p>
					<p class="ui-li-aside-rating"> 
						<?php if( $item->rating > 0 ): ?>
							<?php for( $x=1; $x<=$item->rating; $x++ ): ?>
								<span class="rating_star_generic rating_star"></span>
							<?php endfor; ?>
							<?php if( (round($item->rating) - $item->rating) > 0): ?>
								<span class="rating_star_generic rating_star_half"></span>
							<?php endif; ?>
						<?php endif; ?>
					</p>
					</a> 
				</li>
		  <?php endforeach; ?>
		</ul>
		<?php if ($this->paginator->count() > 1): ?>
			<?php
			echo $this->paginationAjaxControl(
							$this->paginator, $this->identity, 'profile_sitereviewvideos', array('count' => $this->itemCount, 'title_truncation' => $this->title_truncation));
			?>
		<?php endif; ?>
	</div>
<?php else :?>
	<?php if ($this->allowed_upload_video): ?>
		<div class="tip">
			<span>    
					<?php $url = $this->url(array('listing_id' => $this->sitereview->listing_id,'content_id' => $this->identity), "sitereview_video_create_listtype_$this->listingtype_id", true);?>
					<?php echo $this->translate('There are currently no videos in this '.$this->listing_singular_lc.'. %1$sClick here%2$s to add your first video.', "<a href='$url'>", "</a>"); ?>
			</span>
		</div>
	<?php else: ?>
		<?php echo $this->translate('There are currently no videos in this '.$this->listing_singular_lc.'.'); ?>
  <?php endif;?>
<?php endif; ?>

<style type="text/css">

.layout_sitereview_videos_sitereview > h3 {
	display:none;
}

</style>