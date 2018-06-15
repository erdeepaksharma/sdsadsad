<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _formReplyReview.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<div class="sr_reviews_listing_option b_medium" id= "reply-form">
  <ul> 
    <?php if ($this->checkPage == "listingProfile") : ?>
      <?php $reviewGuid = $review->getGuid() . "_0"; ?>
      <?php if (!empty($this->can_reply) && $review->owner_id) : ?>
        <li> 
          <?php echo $this->htmlLink($review->getHref() . "#comments-form_$reviewGuid", $this->translate('<b>Comment on this review</b>'), array('title' => $this->translate("Comment on this review"), 'class' => 'reply icon_sitereviews_comment')) ?>
        </li> 
        <li>|</li>  
      <?php endif; ?>
      <?php
      $commentCountSelect = $review->comments()->getCommentSelect();
      $commentsCount = Zend_Paginator::factory($commentCountSelect)->getTotalItemCount();
      ?>
      <?php if ($commentsCount): ?>
        <li> 
          <?php echo $this->htmlLink($review->getHref() . "#comments-form_$reviewGuid", $this->translate(array('Read comment (%s)', 'Read comments (%s)', $commentsCount), $this->locale()->toNumber($commentsCount)), array('title' => $this->translate("Read comment"))) ?>
        </li>
        <li>|</li>
      <?php endif; ?>
    <?php endif; ?> 
    <li> 
      <div> 
        <div id="review_helpful_<?php echo $review->review_id; ?>" style="display:block;">
          <span><?php echo $this->translate("Was this review helpful?"); ?></span> 
          <?php $this->countHelpfulReviews = $helpfulTable->getCountHelpful($review->review_id, 1); ?>
          <a href="javascript:void(0);" onclick="reviewHelpful(1, '<?php echo $review->review_id; ?>');" title="<?php echo $this->translate('Yes'); ?>"><i class="thumbup"></i></a>
          <?php echo $this->countHelpfulReviews ?>
          <?php $this->countHelpfulReviews = $helpfulTable->getCountHelpful($review->review_id, 2); ?>
          <a href="javascript:void(0);" onclick="reviewHelpful(2, '<?php echo $review->review_id; ?>');" title="<?php echo $this->translate('No'); ?>"><i class="thumbdown"></i> </a>
          <?php echo $this->countHelpfulReviews; ?>
        </div>
        <?php if ($this->viewer_id): ?>
          <div id="review_helpful_message_<?php echo $review->review_id; ?>" style="display:none;">
            <i class="sr_icon sr_icon_tick fleft mright5"></i>
            <?php echo $this->translate("Thanks for your feedback!"); ?>
          </div>
          <div id="review_helpful_already_message_<?php echo $review->review_id; ?>" style="display:none;">
            <?php echo $this->translate("You have already submitted your feedback for this Review!"); ?>
          </div>
        <?php endif; ?>
      </div> 
    </li> 
  </ul> 
  <div class="action_link"> 
    <?php if ($this->sitereview_report && $this->viewer_id): ?>
      <?php echo $this->htmlLink($this->url(array('action' => 'create', 'module' => 'core', 'controller' => 'report', 'subject' => $review->getGuid()), 'default', true), $this->translate("Report Review"), array('title' => $this->translate("Report Review"), 'class' => "seaocore_icon_report smoothbox")) ?>
    <?php endif; ?>
    <?php if ($this->sitereview_share && $review->owner_id != 0): ?>
      <?php echo $this->htmlLink($this->url(array('action' => 'share', 'module' => 'seaocore', 'controller' => 'activity', 'type' => $review->getType(), 'id' => $review->review_id, 'format' => 'smoothbox', 'not_parent_refresh' => 1), 'default', true), $this->translate("Share Review"), array('title' => $this->translate("Share Review"), 'class' => "seaocore_icon_share smoothbox")) ?>
    <?php endif; ?>
    <?php if ($this->sitereview_email): ?>
      <?php $url = $this->url(array('action' => 'email', 'listing_id' => $this->listing_id, 'review_id' => $review->review_id), "sitereview_user_general_listtype_$this->listingtype_id"); ?>
      <a class="icon_sitereviews_mail smoothbox" title="<?php echo $this->translate('Email Review'); ?>" href="<?php echo $url; ?>"><?php $this->translate("Email Review"); ?></a> 
    <?php endif; ?>
    <?php if ($this->checkPage == 'listingProfile'): ?>
      <?php echo $this->htmlLink($review->getHref(), $this->translate("Permalink"), array('title' => $this->translate("Permalink"), 'class' => "sr_icon_link")) ?>
    <?php endif; ?>
    <?php if (!empty($this->can_delete) && ($this->can_delete != 1 || $this->viewer_id == $review->owner_id)) : ?>
      <?php echo $this->htmlLink($this->url(array('action' => 'delete', 'listing_id' => $this->listing_id, 'review_id' => $review->review_id, 'tab' => Engine_Api::_()->sitereview()->existWidget('sitereview_reviews', 0, $this->listingtype_id)), "sitereview_user_general_listtype_$this->listingtype_id", true), $this->translate("Delete Review"), array('title' => $this->translate("Delete Review"), 'class' => "smoothbox seaocore_icon_delete")) ?>
    <?php endif; ?>
  </div> 
</div>

<script type="text/javascript">
  var active_request_review = false;
  function reviewHelpful(option, review_id) {
    if(active_request_review)
     return;
		<?php if (!$this->viewer_id): ?>
			window.location.href = en4.core.baseUrl+'sitereview/review/helpful/review_id/'+review_id+'/helpful/'+option+'/anonymous/1';
		<?php endif; ?>
     active_request_review = true;
    var request = new Request.JSON({ 
      url : en4.core.baseUrl+'sitereview/review/helpful',
      data : {
        format : 'html',
        review_id : review_id,
        helpful: option
      },
      onSuccess : function(responseJSON) {
        if(responseJSON.already_entry == 0 && $('review_helpful_message_' + review_id )) {        
          $('review_helpful_message_' + review_id ).style.display = 'block';
          $('review_helpful_already_message_' + review_id ).style.display = 'none';
        } else if((responseJSON.already_entry == 1 || responseJSON.already_entry == 2) &&  $('review_helpful_already_message_' + review_id )) {
          $('review_helpful_message_' + review_id ).style.display = 'none';
          $('review_helpful_already_message_' + review_id ).style.display = 'block';
        }
        $('review_helpful_' + review_id).style.display = 'none';
        active_request_review = false;
      }
    });
    request.send();
    return false;
  }

</script>