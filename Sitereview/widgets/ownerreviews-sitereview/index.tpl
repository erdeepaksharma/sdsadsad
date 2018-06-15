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

<h3><?php echo $this->translate("%s's Reviews", $this->review->getOwner()->toString()); ?></h3>
<ul class="sr_profile_side_listing sr_side_widget">
  <?php foreach ($this->reviews as $review): ?>
    <li>

      <?php echo $this->htmlLink($review->getParent()->getHref(array('profile_link' => 1)), $this->itemPhoto($review->getParent(), 'thumb.icon'), array('class' => 'popularmembers_thumb', 'title' => $review->getParent()->title), array('title' => $review->getParent()->title)) ?>

      <div class='sr_profile_side_listing_info'>

        <div class='sr_profile_side_listing_title'>
          <?php echo $this->htmlLink($review->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($review->title, $this->title_truncation), array('title' => $review->title)) ?>
        </div>

        <div class='sr_profile_side_listing_stats seaocore_txt_light'>
          <?php echo $this->translate("on ") . $this->htmlLink($review->getParent()->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($review->getParent()->title, $this->title_truncation), array('title' => $review->getParent()->title)) ?>
        </div>

        <div class='seaocore_sidebar_list_details'>
          <?php echo $this->showRatingStar($review->rating, "$review->type", 'small-star', $review->getParent()->listingtype_id); ?>
        </div>

        <?php if ($this->type != 'editor' && !empty($this->statistics)): ?>
          <br />
          <div class='sr_profile_side_listing_stats seaocore_txt_light'>
            <?php
            $statistics = '';

            if (in_array('likeCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s like', '%s likes', $review->like_count), $this->locale()->toNumber($review->like_count)) . ', ';
            }

            if (in_array('commentCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s comment', '%s comments', $review->comment_count), $this->locale()->toNumber($review->comment_count)) . ', ';
            }

            if (in_array('viewCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s view', '%s views', $review->view_count), $this->locale()->toNumber($review->view_count)) . ', ';
            }

            if (in_array('replyCount', $this->statistics)) {
              $statistics .= $this->translate(array('%s reply', '%s replies', $review->reply_count), $this->locale()->toNumber($review->reply_count)) . ', ';
            }

            if (in_array('helpfulCount', $this->statistics) && ($review->type == 'user' || $review->type == 'vistior')) {
              $statistics .= $this->translate('%s helpful', $review->helpful_count . '%') . ', ';
            }

            $statistics = trim($statistics);
            $statistics = rtrim($statistics, ',');
            ?>
            <?php echo $statistics; ?>
          </div>  
        <?php endif; ?>

      </div>
    </li>
  <?php endforeach; ?>
</ul>