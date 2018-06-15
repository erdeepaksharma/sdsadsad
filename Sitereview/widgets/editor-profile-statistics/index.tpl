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

<ul class="seaocore_sidebar_list sr_reviews_breakdowns">
  <li>
    <?php echo $this->translate(
           array('Total %s editor review.', 'Total %s editor reviews.', $this->totalEditorReviews), '<b>' . $this->totalEditorReviews . '</b>') ?>
  </li>  
  <li>
    <?php echo $this->translate(
           array('Total %s user review.', 'Total %s user reviews.', $this->totalUserReviews), '<b>' . $this->totalUserReviews . '</b>') ?>
  </li>
  <li>
    <?php echo $this->translate(
           array('Total %s comment.', 'Total %s comments.', $this->totalComments), '<b>' . $this->totalComments . '</b>') ?>
  </li>
  <li>
    <?php echo $this->translate(
           array('Reviews in %s category.', 'Reviews in %s categories.', $this->totalCategoriesReview), '<b>' . $this->totalCategoriesReview . '</b>') ?>
  </li>  
  <li>
  	<b><?php echo $this->translate("Ratings Breakdown"); ?></b>
    <div class="sr_rating_breakdowns">
      <ul>
        <?php foreach ($this->ratingCount as $i => $count): ?>
          <li>
            <div class="left"><?php echo $this->translate(array("%s star:", "%s stars:", $i), $i); ?></div>
            <?php $pr = $count ? ($count * 100 / $this->totalReviews):0; ?>
            <div class="count"><?php echo $count; ?></div>
            <div class="rate_bar b_medium">
              <span style="width:<?php echo $pr; ?>%;" <?php echo empty($count) ? "class='sr_border_none'" : "" ?>></span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </li>
</ul>
