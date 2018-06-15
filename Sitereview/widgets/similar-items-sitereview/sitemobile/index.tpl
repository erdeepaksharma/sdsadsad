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
  $ratingValue = $this->ratingType; 
  $ratingShow = 'small-star';
    if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
?>
 <?php if ($this->viewType=='listview'): ?>
	<div class="sm-content-list">
		<ul data-role="listview" data-inset="false" data-icon="arrow-r" id="list-view">
			<?php foreach($this->listings as $sitereview):?>
				<li>
					<a href="<?php echo $sitereview->getHref(array('profile_link' => 1));?>">
						<?php echo $this->itemPhoto($sitereview, 'thumb.icon');?>
						<h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->truncation) ?></h3>
						<p>
							<?php if ($ratingValue == 'rating_both'): ?>
								<?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?><br />
								<?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
							<?php else: ?>
								<?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
							<?php endif; ?>
						</p>
						<p>
							<b><?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?></b>
						</p>
            <p class="ui-li-aside">
							<?php if ($sitereview->sponsored == 1): ?>
								<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/sponsored.png', '', array('class' => 'icon', 'title' => $this->translate('Sponsored'))) ?>
							<?php endif; ?>
							<?php if ($sitereview->featured == 1): ?>
								<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/featured.png', '', array('class' => 'icon', 'title' => $this->translate('Featured'))) ?>
							<?php endif; ?>
            </p>
						<p>
							<?php 
								$statistics = '';
								if(in_array('likeCount', $this->statistics)) {
									$statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).' - ';
								}    
								if(in_array('viewCount', $this->statistics)) {
									$statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).' - ';
								}
								if(in_array('commentCount', $this->statistics)) {
									$statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).' - ';
								}
        	$listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); 
								                     if(in_array('reviewCount', $this->statistics) && (!empty($listingtypeArray->allow_review) || (isset($sitereview->rating_editor) && $sitereview->rating_editor))) {
									$statistics .= $this->partial(
									'_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)).'- ';
								}
								$statistics = trim($statistics);
								$statistics = rtrim($statistics, '-');
							?>
							<?php echo $statistics; ?>
						</p>
						<?php if (Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price && !empty($sitereview->price) && $sitereview->price > 0): ?>
							<p><b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b></p>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
			<?php if ($this->listings->count() > 1): ?>
				<?php
					echo $this->paginationAjaxControl(
							$this->listings, $this->identity, 'list-view', array('count' => $this->count, 'truncation' => $this->truncation, 'viewType' => $this->viewType, 'ratingType' => $this->ratingType, 'statistics'=>$this->statistics, 'columnHeight' => $this->columnHeight));
				?>
			<?php endif; ?>
	</div>
<?php else: ?>
	<div class="ui-page-content">
		<div id="grid_view">
			<ul class="p_list_grid">
				<?php foreach ($this->listings as $sitereview): ?>
					<li style="height:<?php echo $this->columnHeight ?>px;">
						<a href="<?php echo $sitereview->getHref(array('profile_link' => 1)); ?>" class="ui-link-inherit">
							<div class="p_list_grid_top_sec">
								<div class="p_list_grid_img">
									<?php $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_profile.png';
										$temp_url = $sitereview->getPhotoUrl('thumb.profile');
											if (!empty($temp_url)): $url = $sitereview->getPhotoUrl('thumb.profile');
											endif; ?>
										<span style="background-image: url(<?php echo $url; ?>);"> </span>
								</div>
							<div class="p_list_grid_title">
								<span><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->truncation); ?></span>
							</div>
						</div>
						<div class="p_list_grid_info">	
							<span class="p_list_grid_stats">
								<?php if ($ratingValue == 'rating_both'): ?>
									<?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?><br />
									<?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
								<?php else: ?>
									<?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
								<?php endif; ?>
							</span>

							<span class="p_list_grid_stats">
								<b><?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?></b>
							</span>

							<span class="p_list_grid_stats">
								<?php 
									$statistics = '';
									if($this->statistics &&  in_array('likeCount', $this->statistics)) {
										$statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).' - ';
									}  
									if($this->statistics &&  in_array('viewCount', $this->statistics)) {
										$statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).' - ';
									}
									if($this->statistics && in_array('commentCount', $this->statistics)) {
										$statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).' - ';
									}

									$listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); 
									if($this->statistics && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($listingtypeArray->allow_review) || isset ($sitereview->rating_editor) && $sitereview->rating_editor && $sitereview->review_count==1)){
									  $statistics .= $this->partial(
																	'_showReview.tpl', 'sitereview', array('sitereview' => $sitereview));
                  }

									$statistics = trim($statistics);
									$statistics = rtrim($statistics, '-');
								?>
								<?php echo $statistics; ?> 
							</span>

							<span class='p_list_grid_stats'>
								<?php if (Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price && !empty($sitereview->price) && $sitereview->price > 0): ?>
									<b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b>
								<?php endif; ?>
							</span>
							
							<span class="p_list_grid_stats">
								<?php if ($sitereview->sponsored == 1): ?>
									<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/sponsored.png', '', array('class' => 'icon', 'title' => $this->translate('Sponsored'))) ?>
								<?php endif; ?>
								<?php if ($sitereview->featured == 1): ?>
									<?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/featured.png', '', array('class' => 'icon', 'title' => $this->translate('Featured'))) ?>
								<?php endif; ?>
							</span>
						</div>
					</li>
				<?php endforeach;?>
			</ul>
			<?php if ($this->listings->count() > 1): ?>
				<?php
					echo $this->paginationAjaxControl(
							$this->listings, $this->identity, 'grid_view', array('count' => $this->count, 'truncation' => $this->truncation, 'viewType' => $this->viewType, 'ratingType' => $this->ratingType, 'statistics'=>$this->statistics, 'columnHeight' => $this->columnHeight));
				?>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<style type="text/css">

.layout_sitereview_similar_items_sitereview > h3 {
	display:none;
}

</style>