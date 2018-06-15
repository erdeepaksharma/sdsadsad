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

<?php $review = $this->reviews; ?>
<?php $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview'); ?>
<?php $helpfulTable = Engine_Api::_()->getDbtable('helpful', 'sitereview'); ?>
<?php $reviewDescriptionsTable = Engine_Api::_()->getDbtable('reviewDescriptions', 'sitereview'); ?>

<div class="ui-page-content">
  <div class="sm-ui-cont-head">
    
     <?php if ($review->status == 0): ?>
      <div class="tip">
        <span>
          <?php echo $this->translate("This review has been written by a visitor of your site and is not visible to the users of your site. Please %s to take an appropriate action on this review.", $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'review', 'action' => 'take-action', 'review_id' => $review->review_id, 'listing_id' => $this->sitereview->listing_id), $this->translate('click over here'), array('class' => 'smoothbox'))); ?>
        </span>
      </div>
    <?php endif; ?>
    <div class="sm-ui-cont-author-photo">
      <?php if ($review->owner_id): ?>
          <?php echo $this->htmlLink($review->getOwner()->getHref(), $this->itemPhoto($review->getOwner(), 'thumb.icon', $review->getOwner()->getTitle()), array('class' => "thumb_icon")) ?>
        <?php else: ?>
          <?php $itemphoto = $this->layout()->staticBaseUrl . "application/modules/User/externals/images/nophoto_user_thumb_icon.png"; ?>
          <img src="<?php echo $itemphoto; ?>" class="thumb_icon" alt="" />
        <?php endif; ?>
    </div>
    
    <div class="sm-ui-cont-cont-info">
      <div class="sm-ui-cont-author-name">
      	<?php echo $review->title; ?> 
      </div>
      <?php if(Engine_Api::_()->sitemobile()->isApp()):?>
        <div class="sm-ui-cont-cont-date">
          <?php echo $this->translate('For');?>
          <?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->sitereview->getTitle()) ?>
        </div>
      <?php endif;?>
      <div class="sm-ui-cont-cont-date">
      	
          <?php echo $this->timestamp(strtotime($review->modified_date)); ?> - 
          <?php if (!empty($review->owner_id)): ?>
            <?php echo $this->translate('by'); ?> <?php echo $this->htmlLink($review->getOwner()->getHref(), $review->getOwner()->getTitle()) ?>
          <?php else: ?>
            <?php echo $this->translate('by'); ?> <?php echo $review->anonymous_name; ?>
          <?php endif; ?>
      </div>
    </div>
    
  </div>
  
  <div class="pr_view">
  <section class="sm-widget-block">
    <table class="sm-rating-table">
      <tbody>
    <?php $ratingData = $review->getRatingData(); ?>
    <?php $rating_value = 0;
    foreach($ratingData as $reviewcat): ?>
  <tr valign="top">
    <td class="rating-title">
        <?php if (!empty($reviewcat['ratingparam_name'])): ?>
            <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
        <?php else:?>
            <strong><?php echo $this->translate("Overall Rating");?></strong>
        <?php endif; ?>
        </td>
        <td>
       
        <?php if(!empty($reviewcat['ratingparam_name'])): ?>
          <div class="review_cat_rating">
            <?php echo $this->showRatingStar($reviewcat['rating'], 'user', 'small-box', $this->sitereview->listingtype_id, $reviewcat['ratingparam_name']);?>
          </div>
        <?php else:?>
          <div class="review_cat_rating">
           <?php echo $this->showRatingStar($reviewcat['rating'], $review->type, 'big-star', $this->sitereview->listingtype_id); ?>
          </div>
        <?php endif;?>
        </td>
      </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <section>
    <?php if($review->pros):?>
      <p>
        <strong><?php echo $this->translate("Pros: ")?></strong>
        <?php echo $review->pros; ?>
      </p>
      <?php endif;?>
      <?php if ($review->cons):?>
      <p>
        <strong><?php echo $this->translate("Cons: ")?></strong>
        <?php echo $review->cons ?>
      </p> 
    <?php endif;?>
       <?php if ($this->reviews->profile_type_review): ?>
      <p>
       
          
            <?php $custom_field_values = $this->fieldValueLoopReview($this->reviews, $this->fieldStructure); ?>
            <?php echo htmlspecialchars_decode($custom_field_values); ?>
          
       
        
      </p>
       <?php endif; ?>
      <?php if ($review->getDescription()): ?>
      <p>
        <strong><?php echo $this->translate("Summary") ?>:</strong>
        <?php echo $review->body ?>
      </p>
      <?php endif;?>    

			<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)):?>
				<?php if($review->recommend ):?>
					<p>
						<strong><?php echo $this->translate("Recommended:"); ?></strong>
						<span class="ui-icon ui-icon-ok"></span>
            <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
              <?php echo "Yes";?>
            <?php endif ?>
					</p>
				<?php else: ?>
					<p>
						<strong><?php echo $this->translate("Recommended:"); ?></strong>
						<span class="ui-icon ui-icon-remove"></span>
            <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
              <?php echo "No";?>
            <?php endif ?>
					</p>
				<?php endif;?>
      <?php endif;?>
  </section>
     
        <div class="sm-widget-block">
          <?php $this->reviewDescriptions = $reviewDescriptionsTable->getReviewDescriptions($this->reviews->review_id); ?>
          <?php if (count($this->reviewDescriptions) > 0): ?>        
              <?php foreach ($this->reviewDescriptions as $value) : ?>
                <?php if ($value->body): ?>
                 <strong><?php echo $this->translate("Updated On %s", $this->timestamp(strtotime($value->modified_date))); ?></strong><br/>
                 <?php echo $value->body; ?>
                <?php endif; ?> 
              <?php endforeach; ?>
          <?php endif; ?> 
        </div>
    <div class="sr_reviews_listing_info">
        <?php
        include APPLICATION_PATH . '/application/modules/Sitereview/views/sitemobile/scripts/_formReplyReview.tpl';
        ?> 
      </div>
</div>
  
  
  
</div>
<div class="o_hidden">

  <?php if ($this->reviews->owner_id) : ?>
    <?php //echo $this->action("list", "nestedcomment", "seaocore", array("type" => $this->reviews->getType(), "id" => $this->reviews->review_id)); ?>
  <?php echo $this->content()->renderWidget("sitemobile.comments", array('type' => $this->sitereview->getType(), 'id' => $this->sitereview->getIdentity())); ?>
  <?php else: ?>
    <?php if ($this->level_id == 1): ?>
      <div class="tip">
        <span><?php echo $this->translate("Comments on review have been disabled, as this review was written by a visitor of your site."); ?></span>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>