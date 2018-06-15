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
	$this->headLinkSM()
  ->appendStylesheet($this->layout()->staticBaseUrl
    . 'application/modules/Sitereview/externals/styles/style_rating.css');
?>

<?php if ($this->type == 'editor'): ?>
  <div id='editorReviewContent' class="sm-content-list">
    <ul id="profile_editor_reviews" data-role="listview" data-icon="arrow-r">
      <?php if($this->paginator->getTotalItemCount()): ?>
				<?php foreach ($this->paginator as $review): ?>
					<li> 
            <a href="<?php echo $review->getHref();?>">
              <h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($review->title, $this->truncation);?></h3>
              <?php $ratingData = $review->getRatingData(); ?>
								<?php
								$rating_value = 0;
								foreach ($ratingData as $reviewcat):
									if (empty($reviewcat['ratingparam_name'])):
										$rating_value = $reviewcat['rating'];
										break;
									endif;
								endforeach;
								?>
							<p><?php echo $this->showRatingStar($rating_value, $review->type, 'big-star', $review->getParent()->listingtype_id); ?></p>
							<?php $sitereview = $review->getParent() ?>
							<p>
								<?php echo $this->translate('For'); ?>  
								<?php echo $sitereview->getTitle();?> -  
								<?php echo $this->translate('on %s', $this->timestamp(strtotime($review->modified_date))); ?>
							</p>
            </a>
					</li>
				<?php endforeach; ?>
      <?php else: ?>
        <div class="tip mtop10"> 
          <span> 
            <?php echo $this->translate('No Editor Review has been written yet.'); ?>
          </span>
        </div>       
      <?php endif; ?>  
    </ul>
		<?php if ($this->paginator->count() > 1): ?>
			<?php
			echo $this->paginationAjaxControl(
							$this->paginator, $this->identity, 'profile_editor_reviews');
			?>
		<?php endif; ?>
  </div>
<?php else: ?>
  <div id='userReviewContent' class="sm-content-list">
    <ul id="profile_user_reviews" data-role="listview" data-icon="arrow-r">
      <?php foreach ($this->paginator as $review): ?>
				<li> 
					<a href="<?php echo $review->getHref();?>">
						<h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($review->title, $this->truncation);?></h3>
						<?php $ratingData = $review->getRatingData(); ?>
							<?php
							$rating_value = 0;
							foreach ($ratingData as $reviewcat):
								if (empty($reviewcat['ratingparam_name'])):
									$rating_value = $reviewcat['rating'];
									break;
								endif;
							endforeach;
							?>
						<p><?php echo $this->showRatingStar($rating_value, $review->type, 'big-star', $review->getParent()->listingtype_id); ?></p>
						<?php $sitereview = $review->getParent() ?>
						<p>
							<?php echo $this->translate('For'); ?>  
							<?php echo $sitereview->getTitle();?> -  
							<?php echo $this->translate('on %s', $this->timestamp(strtotime($review->modified_date))); ?>
						</p>
					</a>
				</li>
      <?php endforeach; ?>
    </ul>
		<?php if ($this->paginator->count() > 1): ?>
			<?php
			echo $this->paginationAjaxControl(
							$this->paginator, $this->identity, 'profile_user_reviews');
			?>
		<?php endif; ?>
  </div>
<?php endif; ?>