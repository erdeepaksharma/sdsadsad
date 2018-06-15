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

<script type="text/javascript">
	var seaocore_content_type = '<?php echo $this->resource_type; ?>';
</script>

<?php
if(Engine_Api::_()->sitereview()->hasPackageEnable()) {
	if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($this->sitereview->package_id, "wishlist"))
	$canAddWishlist = 1;
	else
	$canAddWishlist = 0;
}
else {
	$canAddWishlist = 1;
}
?>

<?php
$photo_type = $this->listingType->photo_type;
$reviewApi = Engine_Api::_()->sitereview();
$expirySettings = $reviewApi->expirySettings($this->listingtype_id);
$approveDate = null;
if ($expirySettings == 2):
  $approveDate = $reviewApi->adminExpiryDuration($this->listingtype_id);
endif;
?>

<?php
$ratingValue = $this->ratingType;
$ratingShow = 'small-star';
if ($this->ratingType == 'rating_editor') {
	$ratingType = 'editor';
} elseif ($this->ratingType == 'rating_avg') {
	$ratingType = 'overall';
} else {
	$ratingType = 'user';
}
?>

<?php if ($approveDate && $this->sitereview->approved_date && $approveDate > $this->sitereview->approved_date): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('This '.strtolower($this->listingType->title_singular).' has beed expired.'); ?>
    </span>
  </div>
<?php endif; ?>

<?php if (!empty($this->sitereview->closed)) : ?>
  <div class="tip"> 
    <span> <?php echo $this->translate('This '.strtolower($this->listingType->title_singular).' has been closed by the owner.'); ?> </span>
  </div>
<?php endif; ?>

<div class="clr sr_profile_info">
  <?php if(in_array('photo', $this->showContent)): ?>
    <div class="sr_profile_photo_wrapper b_medium">
      <div class='sr_profile_photo <?php if ($this->can_edit && $photo_type == 'listing'): ?>sr_photo_edit_wrapper<?php endif; ?>'>
        <?php if($this->sitereview->photo_id ):?>
            <?php $photo= $this->sitereview->getPhoto($this->sitereview->photo_id); ?>
            <?php if($photo_type == 'listing'):?>
              <a href="<?php echo $photo->getHref(); ?>" class="thumbs_photo">
              <?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '', array('align' => 'center')); ?></a>
            <?php else:?>
              <a href="<?php echo $this->sitereview->getHref(array('profile_link' => 1)); ?>"  class="thumbs_photo">
              <?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '', array('align' => 'center')); ?></a>
            <?php endif;?>
         <?php else: ?>
           <?php if($this->listingType->photo_id == 0):?>
             <a href="<?php echo $this->sitereview->getHref(array('profile_link' => 1)); ?>" class="thumbs_photo"></a>
           <?php endif;?>
           <?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '', array('align' => 'center', 'class' =>"thumbs_photo")); ?>
        <?php endif;?>
      </div>
    </div>
  <?php endif; ?>

  <div class="sr_profile_content">
		<div class="sr_profile_title">
      <?php if (in_array('title', $this->showContent)): ?>
        <h2>
          <?php echo $this->sitereview->getTitle(); ?>
        </h2>
      <?php endif; ?>
		</div>
    <div class="sr_profile_information_stats clr">
			<?php if ($ratingValue == 'rating_both'): ?>
				<div class="clr"><?php echo $this->showRatingStar($this->sitereview->rating_editor, 'editor', $ratingShow, $this->sitereview->listingtype_id); ?></div>
				<div class="clr"><?php echo $this->showRatingStar($this->sitereview->rating_users, 'user', $ratingShow, $this->sitereview->listingtype_id); ?></div>
			<?php else: ?>
				<span><?php echo $this->showRatingStar($this->sitereview->$ratingValue, $ratingType, $ratingShow, $this->sitereview->listingtype_id); ?></span>
			<?php endif; ?>
    </div>
    <?php if (in_array('viewCount', $this->showContent) || in_array('likeCount', $this->showContent) || in_array('commentCount', $this->showContent)): ?>
			<div class="sr_profile_information_stats clr">
				<?php if (in_array('likeCount', $this->showContent)): ?>
			    <?php echo $this->translate(array('%s like', '%s likes', $this->sitereview->like_count), $this->locale()->toNumber($this->sitereview->like_count)) ?>
				<?php endif; ?>
				<?php if (in_array('viewCount', $this->showContent)): ?>
					-	 <?php echo $this->translate(array('%s view', '%s views', $this->sitereview->view_count), $this->locale()->toNumber($this->sitereview->view_count)) ?>
				<?php endif; ?>
				<?php if (in_array('commentCount', $this->showContent)): ?>
          -  <?php echo $this->translate(array('%s comment', '%s comments', $this->sitereview->comment_count), $this->locale()->toNumber($this->sitereview->comment_count)) ?>
				<?php endif; ?>
				<?php if (in_array('reviewCount', $this->showContent) && !empty($this->listingType->allow_review)): ?>
          -  <?php echo $this->translate(array('%s review', '%s reviews', $this->sitereview->review_count), $this->locale()->toNumber($this->sitereview->review_count)) ?>
				<?php endif; ?>
			</div>
    <?php endif; ?>
    <div class="sr_profile_information_stats clr">
      <?php if (in_array('postedBy', $this->showContent)): ?>
        <?php echo $this->translate(strtoupper($this->listingType->title_singular). '_POSTED_BY'); ?>
          <?php echo $this->htmlLink($this->sitereview->getOwner()->getHref(), $this->sitereview->getOwner()->getTitle()) ?>
      <?php endif; ?>
      <?php if (in_array('postedDate', $this->showContent)): ?>
      - <?php echo $this->timestamp(strtotime($this->sitereview->creation_date)) ?>
      <?php endif; ?>
    </div>
		<?php if (in_array('tags', $this->showContent)): ?>
			<?php if (count($this->sitereviewTags) > 0): $tagCount = 0; ?>
				<div class="sr_profile_information_stats clr"> <?php echo $this->translate($this->listing_singular_upper.'_TAGS'); ?> - 
				<?php foreach ($this->sitereviewTags as $tag): ?>
					<?php if (!empty($tag->getTag()->text)): ?>
						<?php $tag->getTag()->text = $this->string()->escapeJavascript($tag->getTag()->text) ?>
						<?php if (empty($tagCount)): ?>
							<a href='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_" . $this->sitereview->listingtype_id); ?>?tag=<?php echo urlencode($tag->getTag()->text) ?>&tag_id=<?php echo $tag->getTag()->tag_id ?>'>#<?php echo $tag->getTag()->text ?></a>
							<?php $tagCount++;
						else: ?>
							<a href='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_" . $this->sitereview->listingtype_id); ?>?tag=<?php echo urlencode($tag->getTag()->text) ?>&tag_id=<?php echo $tag->getTag()->tag_id ?>'>#<?php echo $tag->getTag()->text ?></a>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?></div>
			<?php endif; ?>
		<?php endif; ?>
    <?php if (in_array('endDate', $this->showContent)): ?>
      <?php if ($expirySettings == 2): $exp=$this->sitereview->getExpiryTime();
        echo '<div class="sr_profile_information_stats clr">' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'' . '</div>';
        $now = new DateTime(date("Y-m-d H:i:s"));
        $ref = new DateTime($this->locale()->toDate($exp));
        $diff = $now->diff($ref);
        echo '<div class="sr_profile_information_stats">';
        echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
        echo '</div>';
      elseif ($expirySettings == 1 && $this->sitereview->end_date && $this->sitereview->end_date !='0000-00-00 00:00:00'):
        echo '<div class="sr_profile_information_stats clr">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($this->sitereview->end_date), array('size'=>'medium'))) . '</div>';
            $now = new DateTime(date("Y-m-d H:i:s"));
            $ref = new DateTime($this->sitereview->end_date);
            $diff = $now->diff($ref);
            echo '<div class="sr_profile_information_stats">';
            echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
            echo '</div>';
      endif;?>
    <?php endif; ?>
    <?php if (in_array('location', $this->showContent) && !empty($this->sitereview->location) && $this->listingType->location): ?>
      <div class="sr_profile_information_stats clr">
        <?php echo $this->translate("Location"); ?> - <?php echo $this->htmlLink('https://maps.google.com/?q='.urlencode($this->sitereview->location), $this->sitereview->location, array('target' => 'blank')) ?>
      </div>
    <?php endif; ?>
    <?php if (in_array('phone', $this->showContent) || in_array('email', $this->showContent) || in_array('website', $this->showContent)) : ?>
			<?php if (($this->phone) || ($this->email) || ($this->website)) : ?>
				<div class="sr_profile_information_stats clr">
          <?php if (in_array('phone', $this->showContent) && !empty($this->phone)) : ?>
            <span class="sr_profile_contect_op">
              <i class="ui-icon ui-icon-phone" title="<?php echo $this->translate('Phone') ?>"></i>
              <span id="showPhoneNumber" class="o_hidden"><a href="tel:<?php echo $this->phone?>"><?php echo $this->phone?></a></span>
            </span>
          <?php endif; ?>
          <?php if (in_array('email', $this->showContent) && !empty($this->email)) : ?>
            <span class="sr_profile_contect_op">
              <i class="ui-icon ui-icon-envelope" title="E-mail"></i>
              <span id="showEmailAddress" class="o_hidden"><a href='mailto:<?php echo $this->email ?>' title="<?php echo $this->email ?>"><?php echo $this->translate('Email Me') ?></a></span>      
            </span>
          <?php endif; ?>
          <?php if (in_array('website', $this->showContent) && !empty($this->website)) : ?>
            <span class="sr_profile_contect_op">
              <i class="ui-icon ui-icon-globe"  title="<?php echo $this->translate('Website') ?>"></i>
              <span id="showWebsite">   
                <?php if (strstr($this->website, 'http://') || strstr($this->website, 'https://')): ?>
                  <a href='<?php echo $this->website ?>' target="_blank" title='<?php echo $this->website ?>' ><?php echo $this->translate(''); ?> <?php echo $this->translate('Visit Website') ?></a>
                <?php else: ?>
                  <a href='http://<?php echo $this->website ?>' target="_blank" title='<?php echo $this->website ?>' ><?php echo $this->translate(''); ?> <?php echo $this->translate('Visit Website') ?></a>
                <?php endif; ?>
              </span>
            </span>    
          <?php endif; ?>
				</div>
			<?php endif; ?>
    <?php endif; ?>
    <?php if (in_array('price', $this->showContent) && ($this->price > 0)):?>
			<div class="sr_profile_information_stats clr">
				<div class="clr sr_profile_information_option mtop5">
					<b>
						<?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->price); ?>
					</b>   
				</div>
			</div>
    <?php endif; ?>
  
    <?php if(in_array('description', $this->showContent)):?>
      <?php if(Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($this->sitereview->getIdentity(), 'about')):?>
				<div class="sr_profile_information_stats clr">
					<div class="sr_profile_information_des">
						<?php echo Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($this->sitereview->getIdentity(), 'about') ?>
					</div>
				</div>
       <?php elseif(strip_tags($this->sitereview->body)):?>
				<div class="sr_profile_information_stats t_l">
          <?php echo $this->viewMore(strip_tags($this->sitereview->body), 300, 5000) ?>
				</div>
       <?php endif;?>
	  <?php endif; ?>
  </div>
</div>

<?php if ($this->like_button || in_array('newlabel', $this->showContent) || in_array('sponsored', $this->showContent)  || in_array('featured', $this->showContent) || in_array('reviewCreate', $this->showContent) ):?>
	<div class="seaocore_profile_cover_button clr">
			<?php if((in_array('newlabel', $this->showContent) || in_array('sponsored', $this->showContent)  || in_array('featured', $this->showContent)) && (!empty($this->sitereview->newlabel) || !empty($this->sitereview->sponsored) || !empty($this->sitereview->featured))):?>
				<table cellpadding="2" cellspacing="0" style="width:100%">
					<tr>
						<?php if (in_array('newlabel', $this->showContent) && !empty($this->sitereview->newlabel)): ?>
							<td style="width:33.33%;">
								<div class="sm-sl" style='background-color:orange'>
									<?php echo $this->translate('NEW'); ?>
								</div>
							</td>
						<?php endif; ?>
						<?php if (in_array('sponsored', $this->showContent) && !empty($this->sitereview->sponsored)): ?>
							<td style="width:33.33%;">
								<div class="sm-sl" style='background: <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.sponsored.color', '#fc0505'); ?>;'>
									<?php echo $this->translate('SPONSORED'); ?>
								</div>
							</td>
						<?php endif; ?>
						<?php if (in_array('featured', $this->showContent) && !empty($this->sitereview->featured)): ?>
						<td style="width:33.33%;">
							<div class="sm-sl" style='background: <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.featured.color', '#0cf523'); ?>;'>
								<?php echo $this->translate('FEATURED'); ?>
							</div>
						</td>
						<?php endif; ?>
					</tr>
			</table>
		<?php endif; ?>
    <?php if ($this->like_button || in_array('reviewCreate', $this->showContent) || in_array('wishlist', $this->showContent)):?>
		<div class="seaocore_profile_cover_buttons">
			<table cellpadding="2" cellspacing="0">
				<tr>
					<?php if ($this->like_button):?>
						<?php if(!empty($this->viewer_id)): ?>
							<td id="seaocore_like">
								<?php $hasLike = Engine_Api::_()->getApi('like', 'seaocore')->hasLike($this->resource_type, $this->resource_id); ?>
								<a href ="javascript://" onclick = "seaocore_content_type_likes_sitemobile('<?php echo $this->resource_id; ?>', '<?php echo $this->resource_type; ?>');" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' id="<?php echo $this->resource_type; ?>_unlikes_<?php echo $this->resource_id;?>" style ='display:<?php echo $hasLike ?"block":"none"?>'>
									<i class="ui-icon-thumbs-down-alt"></i>
									<span><?php echo $this->translate('Unlike') ?></span>
								</a>
								<a href = "javascript://" onclick = "seaocore_content_type_likes_sitemobile('<?php echo $this->resource_id; ?>', '<?php echo $this->resource_type; ?>');" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' id="<?php echo $this->resource_type; ?>_most_likes_<?php echo $this->resource_id;?>" style ='display:<?php echo empty($hasLike) ?"block":"none"?>'>
									<i class="ui-icon-thumbs-up-alt"></i>
									<span><?php echo $this->translate('Like') ?></span>
								</a>
								<input type ="hidden" id = "<?php echo $this->resource_type; ?>_like_<?php echo $this->resource_id;?>" value = '<?php echo $hasLike ? $hasLike[0]['like_id'] :0; ?>' />
							</td>
						<?php endif; ?>
					<?php endif; ?>
          
					<?php if (in_array('reviewCreate', $this->showContent)):?>
            <?php $reviewButton = $this->content()->renderWidget("sitereview.review-button");?>
						<?php $reviewButtonLength = strlen($reviewButton);?>
						<?php if($reviewButtonLength > 13):?>
							<td>
								<?php echo $reviewButton; ?>
							</td>
            <?php endif; ?>
					<?php endif; ?>
           <?php
           if (Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id)->wishlist && in_array('wishlist', $this->showContent) && $canAddWishlist): ?>
					<td>
						<?php echo $this->addToWishlist($this->sitereview, array('classLink' => ''));?>
					</td>
				<?php endif; ?>
       </tr>
			</table>
    </div>
    <?php endif;?>
	</div>
<?php endif; ?>
<div class="clr widthfull"></div>