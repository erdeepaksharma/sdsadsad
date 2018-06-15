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

<div id="profile_review" class="pabsolute"></div>

<?php
$this->headLink()
        ->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_rating.css')
        ->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereviewprofile.css')
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<div class="o_hidden">
  <div class="sr_view_top">
    <?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.icon', $this->sitereview->getTitle()), array('class' => "thumb_icon", 'title' => $this->sitereview->getTitle())) ?>
    <div class="sr_review_view_right">
      <?php echo $this->content()->renderWidget("sitereview.review-button", array('listing_guid' => $this->sitereview->getGuid(), 'listing_profile_page' => 1, 'identity' => $this->identity)) ?>
      <?php if ($this->price > 0): ?>
        <div class="sr_price mtop10">
          <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->price); ?>
        </div>   
      <?php endif; ?> 
    </div>
    <h2>
      <?php echo $this->htmlLink($this->sitereview->getHref(), $this->sitereview->getTitle()) ?>
    </h2>
    <div class="O_hidden sr_view_top_options">
      <?php echo $this->compareButton($this->sitereview); ?>
      <?php echo $this->addToWishlist($this->sitereview, array('classIcon' => 'sr_icon_wishlist_add', 'classLink' => ''));?>
    </div> 
  </div>

  <div class="sr_profile_review b_medium sr_review_block">
    <div class="sr_profile_review_left">
      <div class="sr_profile_review_title">
        <?php if (empty($reviewcatTopbox['ratingparam_name'])): ?>
          <?php echo $this->translate("Average User Rating"); ?>
        <?php endif; ?>
      </div>
      <?php $iteration = 1; ?>
      <div class="sr_profile_review_stars">
        <span class="sr_profile_review_rating">
          <span class="fleft">
            <?php echo $this->showRatingStar($this->sitereview->rating_users, 'user', 'big-star', $this->sitereview->listingtype_id); ?>
          </span>
          <?php if (count($this->ratingDataTopbox) > 1): ?>
            <i class="arrow_btm fleft"></i>
          <?php endif; ?>
        </span>	
      </div>

      <?php if (count($this->ratingDataTopbox) > 1): ?>
        <div class="sr_ur_bdown_box_wrapper br_body_bg b_medium">
          <div class="sr_ur_bdown_box">
            <div class="sr_profile_review_title">
              <?php echo $this->translate("Average User Rating"); ?>
            </div>
            <div class="sr_profile_review_stars">
              <?php echo $this->showRatingStar($this->sitereview->rating_users, 'user', 'big-star', $this->sitereview->listingtype_id); ?>
            </div>

            <div class="sr_profile_rating_parameters">
              <?php $iteration = 1; ?>
              <?php foreach ($this->ratingDataTopbox as $reviewcatTopbox): ?>
                <?php if (!empty($reviewcatTopbox['ratingparam_name'])): ?>	         
                  <div class="o_hidden">
                    <div class="parameter_title">
                      <?php echo $this->translate($reviewcatTopbox['ratingparam_name']) ?>
                    </div>
                    <div class="parameter_value">
                      <?php echo $this->showRatingStar($reviewcatTopbox['avg_rating'], 'user', 'small-box', $this->sitereview->listingtype_id, $reviewcatTopbox['ratingparam_name']); ?>     
                    </div>
                    <div class="parameter_count"><?php echo $this->sitereview->getNumbersOfUserRating('user', $reviewcatTopbox['ratingparam_id']); ?></div>
                  </div>
                <?php endif; ?>
                <?php $iteration++; ?>
              <?php endforeach; ?>
            </div>
            <div class="clr"></div>
          </div>
        </div>
      <?php endif; ?>

      <div class="sr_profile_review_stat clr">
        <?php echo $this->translate(array('Based on %s review', 'Based on %s reviews', $this->totalReviews), $this->locale()->toNumber($this->totalReviews)); ?>
      </div>

			<?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)):?>
				<div class="sr_profile_review_stat clr">
					<?php echo $this->translate("Recommended by %s users", '<b>' . $this->recommend_percentage . '%</b>'); ?>
				</div>
      <?php endif;?>
      <?php if (!empty($this->viewer_id) && $this->can_create && empty($this->isajax)): ?>
        <?php $rating_value_2 = 0; ?>	
        <?php if (!empty($this->reviewRateMyData)): ?>	
          <?php foreach ($this->reviewRateMyData as $reviewRateData): ?>
            <?php if ($reviewRateData['ratingparam_id'] == 0): ?>
              <?php $rating_value_2 = $reviewRateData['rating']; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
        <div class="sr_profile_review_title mtop5" id="review-my-rating">
          <?php echo $this->translate("My Rating"); ?>
        </div>	
        <div class="sr_profile_review_stars">
          <?php echo $this->showRatingStar($rating_value_2, 'user', 'big-star', $this->sitereview->listingtype_id); ?>		     
        </div>
        <?php if (!empty($this->reviewRateMyData) && !empty($this->hasPosted) && !empty($this->can_update)): ?>
          <div class="sr_profile_review_stat mtop10">
            <?php echo $this->translate('Please %1$sclick here%2$s to update your reviews for this '.$this->listing_singular_lc.'.', "<a href='javascript:void(0);' onclick='showForm();'>", "</a>"); ?>
          </div>	
        <?php endif; ?>
        <?php if (empty($this->reviewRateMyData) && empty($this->hasPosted) && !empty($this->create_level_allow)): ?>
          <div class="sr_profile_review_stat">
            <?php echo $this->translate('Please %1$sclick here%2$s to give your review and ratings for this '.$this->listing_singular_lc.'.', "<a href='javascript:void(0);' onclick='showForm();'>", "</a>"); ?>
          </div>	
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!--Rating Breakdown Hover Box Starts-->
    <div class="sr_profile_review_right">
      <div class="sr_rating_breakdowns">
        <div class="sr_profile_review_title">
          <?php echo $this->translate("Ratings Breakdown"); ?>
        </div>
        <ul>
          <?php for ($i = 5; $i > 0; $i--): ?>
            <li>
              <div class="left"><?php echo $this->translate(array("%s star:", "%s stars:", $i), $i); ?></div>
              <?php $count = $this->sitereview->getNumbersOfUserRating('user', 0, $i);
              $pr = $count ? ($count * 100 / $this->totalReviews) : 0; ?>
              <div class="count"><?php echo $count; ?></div>
              <div class="rate_bar b_medium">
                <span style="width:<?php echo $pr; ?>%;" <?php echo empty($count) ? "class='sr_border_none'" : "" ?>></span>
              </div>
            </li>
          <?php endfor; ?>
        </ul>
      </div>
      <div class="clr"></div>
    </div>
    <!--Rating Breakdown Hover Box Ends-->
  </div>

  <ul class="sr_reviews_listing" id="profile_sitereview_content">
    <?php if ($review->status == 0): ?>
      <div class="tip">
        <span>
          <?php echo $this->translate("This review has been written by a visitor of your site and is not visible to the users of your site. Please %s to take an appropriate action on this review.", $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'review', 'action' => 'take-action', 'review_id' => $review->review_id, 'listing_id' => $this->sitereview->listing_id), $this->translate('click over here'), array('class' => 'smoothbox'))); ?>
        </span>
      </div>
    <?php endif; ?>
    <li>
      <div class="sr_reviews_listing_photo">
        <?php if ($review->owner_id): ?>
          <?php echo $this->htmlLink($review->getOwner()->getHref(), $this->itemPhoto($review->getOwner(), 'thumb.icon', $review->getOwner()->getTitle()), array('class' => "thumb_icon")) ?>
        <?php else: ?>
          <?php $itemphoto = $this->layout()->staticBaseUrl . "application/modules/User/externals/images/nophoto_user_thumb_icon.png"; ?>
          <img src="<?php echo $itemphoto; ?>" class="thumb_icon" alt="" />
        <?php endif; ?>
      </div>
      <div class="sr_reviews_listing_info">
        <div class=" sr_reviews_listing_title">
          <div class="sr_ur_show_rating_star">
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
            <span class="fright">  
              <span class="fleft">
                <?php echo $this->showRatingStar($rating_value, 'user', 'big-star', $this->sitereview->listingtype_id); ?>
              </span>
              <?php if (count($ratingData) > 1): ?>
                <i class="fright arrow_btm"></i>
              <?php endif; ?>
            </span>
            <?php if (count($ratingData) > 1): ?>
              <div class="sr_ur_show_rating  br_body_bg b_medium">
                <div class="sr_profile_rating_parameters sr_ur_show_rating_box">
                  <?php foreach ($ratingData as $reviewcat): ?>
                    <div class="o_hidden">
                      <?php if (!empty($reviewcat['ratingparam_name'])): ?>
                        <div class="parameter_title">
                          <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
                        </div>
                        <div class="parameter_value">
                          <?php echo $this->showRatingStar($reviewcat['rating'], 'user', 'small-box', $this->sitereview->listingtype_id, $reviewcat['ratingparam_name']); ?>
                        </div>
                      <?php else: ?>
                        <div class="parameter_title">
                          <?php echo $this->translate("Overall Rating"); ?>
                        </div>	
                        <div class="parameter_value">
                          <?php echo $this->showRatingStar($reviewcat['rating'], $review->type, 'big-star', $this->sitereview->listingtype_id); ?>
                        </div>
                      <?php endif; ?> 
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <?php if ($review->featured): ?>
            <i class="sr_icon seaocore_icon_featured fright" title="<?php echo $this->translate('Featured'); ?>"></i> 
          <?php endif; ?>	
          <div class="sr_review_title"><?php echo $review->getTitle() ?></div>
        </div>
        <div class="sr_reviews_listing_stat seaocore_txt_light">
          <?php if ($review->recommend && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)): ?>
            <span class="fright sr_profile_userreview_recommended">
              <?php echo $this->translate('Recommended'); ?>
              <span class='sr_icon_tick sr_icon'></span>
            </span>
          <?php endif; ?>
          <?php echo $this->timestamp(strtotime($review->modified_date)); ?> - 
          <?php if (!empty($review->owner_id)): ?>
            <?php echo $this->translate('by'); ?> <?php echo $this->htmlLink($review->getOwner()->getHref(), $review->getOwner()->getTitle()) ?>
          <?php else: ?>
            <?php echo $this->translate('by'); ?> <?php echo $review->anonymous_name; ?>
          <?php endif; ?>
        </div> 
        <div class="clr"></div>
        <?php if ($review->pros): ?>
          <div class="sr_reviews_listing_proscons">
            <b><?php echo $this->translate("Pros") ?>: </b>
            <?php echo $review->pros ?> 
          </div>
        <?php endif; ?>
        <?php if ($review->cons): ?>
          <div class="sr_reviews_listing_proscons"> 
            <b><?php echo $this->translate("Cons") ?>: </b>
            <?php echo $review->cons ?>
          </div>
        <?php endif; ?>

        <?php if ($this->reviews->profile_type_review): ?>
          <div class="sr_reviews_listing_proscons"> 
            <?php $custom_field_values = $this->fieldValueLoopReview($this->reviews, $this->fieldStructure); ?>
            <?php echo htmlspecialchars_decode($custom_field_values); ?>
          </div>	
        <?php endif; ?>

        <?php if ($review->getDescription()): ?>
          <div class="sr_reviews_listing_proscons">
            <b><?php echo $this->translate("Summary") ?>: </b>
            <?php echo $review->body ?>
          </div>
        <?php endif; ?>

        <div class="feed_item_link_desc">
          <?php $this->reviewDescriptions = $reviewDescriptionsTable->getReviewDescriptions($this->reviews->review_id); ?>
          <?php if (count($this->reviewDescriptions) > 0): ?>
            <div class="sitereview_profile_info_des_update sr_review_block">        
              <?php foreach ($this->reviewDescriptions as $value) : ?>
                <?php if ($value->body): ?>
                  <div class="b_medium">
                    <div class="sitereview_profile_info_des_update_date">
                      <?php echo $this->translate("Updated On %s", $this->timestamp(strtotime($value->modified_date))); ?>
                    </div>
                    <div>
                      <?php echo $value->body; ?>
                    </div>
                  </div>
                <?php endif; ?> 
              <?php endforeach; ?>
            </div> 
          <?php endif; ?> 
        </div>
        <?php
        include APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_formReplyReview.tpl';
        ?> 
      </div>
    </li>
  </ul>
  <?php if ($this->reviews->owner_id) : ?>
    <?php 
        include_once APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_listNestedComment.tpl';
    ?>
  <?php else: ?>
    <?php if ($this->level_id == 1): ?>
      <div class="tip">
        <span><?php echo $this->translate("Comments on review have been disabled, as this review was written by a visitor of your site."); ?></span>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="clr o_hidden b_medium sr_review_view_footer fleft">  
    <div class="fleft">
      <a href='<?php echo $this->url(array('listing_id' => $this->sitereview->listing_id, 'slug' => $this->sitereview->getSlug(), 'tab' => $this->tab_id), "sitereview_entry_view_listtype_$this->listingtype_id", true) ?>' class="buttonlink sr_item_icon_back">
        <?php echo $this->translate('Back to Reviews'); ?>
      </a>
    </div>      
    <div class="o_hidden fright sr_review_view_paging">
      <?php $pre = $this->reviews->getPreviousReview(); ?>
      <?php if ($pre): ?>
        <div id="user_group_members_previous" class="paginator_previous">
          <?php
          echo $this->htmlLink($pre->getHref(), $this->translate('Previous'), array(
              'class' => 'buttonlink icon_previous'
          ));
          ?>
        </div>
      <?php endif; ?>
      <?php $next = $this->reviews->getNextReview(); ?>
      <?php if ($next): ?>
        <div id="user_group_members_previous" class="paginator_next">
          <?php
          echo $this->htmlLink($next->getHref(), $this->translate('Next'), array(
              'class' => 'buttonlink_right icon_next'
          ));
          ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="clr fleft widthfull">
    <?php if ($this->hasPosted && $this->can_update): ?>
      <?php echo $this->update_form->setAttrib('class', 'sr_review_form global_form')->render($this) ?>
      <?php
      include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_formUpdateReview.tpl';
      ?>
    <?php endif; ?>
    <?php if (!$this->hasPosted && $this->can_create): ?>
      <?php echo $this->form->setAttrib('class', 'sr_review_form global_form')->render($this) ?>
    <?php endif; ?>
    <?php
    include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_formCreateReview.tpl';
    ?>
  </div>
</div>

<script type="text/javascript">
  var seaocore_content_type = '<?php echo $this->reviews->getType(); ?>';
  en4.core.runonce.add(function() {
		<?php if (count($this->ratingDataTopbox) > 1): ?>
			$$('.sr_profile_review_rating').addEvents({
				'mouseover': function(event) {
					document.getElements('.sr_ur_bdown_box_wrapper').setStyle('display','block');
				},
				'mouseleave': function(event) {    
					document.getElements('.sr_ur_bdown_box_wrapper').setStyle('display','none');
				}});
			$$('.sr_ur_bdown_box_wrapper').addEvents({
				'mouseenter': function(event) {
					document.getElements('.sr_ur_bdown_box_wrapper').setStyle('display','block');
				},
				'mouseleave': function(event) {
					document.getElements('.sr_ur_bdown_box_wrapper').setStyle('display','none');
				}});
		<?php endif; ?> 
	});
</script>