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

<div class="sr_up_overall_rating b_medium">
  <?php if ($this->show_rating == 'both' || $this->show_rating == 'editor'): ?> 
    <div class="sr_up_overall_rating_title o_hidden">
      <div class="fright"><?php echo $this->showRatingStar($this->sitereview->rating_editor, 'editor', 'big-star', $this->sitereview->listingtype_id); ?></div>
			<div class="o_hidden"><?php echo $this->translate("Editor Rating") ?></div>
    </div>
    <?php if(count($this->ratingEditorData)>1 && $this->ratingParameter): ?>
      <div class="sr_up_overall_rating_paramerers clr">
        <?php foreach ($this->ratingEditorData as $reviewcat): ?>
          <?php if (!empty($reviewcat['ratingparam_id'])): ?>
            <div class="o_hidden">
              <div class="parameter_count">&nbsp;
              </div>
              <div class="parameter_value">
                <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'editor', 'small-box', $this->sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
              </div>

              <div class="parameter_title">
                <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if($this->sitereview->rating_editor): ?>
      <div class="sr_up_overall_rating_stat">
        <?php echo $this->translate(
      '%s contributed to this review on %1s.', $this->htmlLink($this->editorReview->getOwner('editor')->getHref(), $this->editorReview->getOwner('editor')->getTitle(), array('')),$this->timestamp(strtotime($this->editorReview->creation_date))) ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
  
  <?php if ($this->show_rating == 'both' || $this->show_rating == 'avg'): ?>

    <?php if($this->show_rating == 'both'): ?>
      <div class="sr_up_overall_rating_sep b_medium"></div>
      <div class="sr_up_overall_rating_title o_hidden">
        <div class="fright"><?php echo $this->showRatingStar($this->sitereview->rating_users, 'user', 'big-star', $this->sitereview->listingtype_id); ?></div>
        <div class="o_hidden"><?php echo $this->translate('User Ratings') ?></div>
      </div>
      <?php if(!empty($this->allow_review)):?>
				<div class="sr_up_overall_rating_stat">
					<?php echo $this->translate(
			array('Based on %s review', 'Based on %s reviews', $this->subject()->getNumbersOfUserRating($this->type)), '<b>'.$this->locale()->toNumber($this->subject()->getNumbersOfUserRating($this->type)).'</b>') ?>
				</div>
      <?php endif;?>
    <?php else: ?>
      <div class="sr_up_overall_rating_title o_hidden">
        <div class="fright"><?php echo $this->showRatingStar($this->sitereview->rating_avg, 'overall', 'big-star', $this->sitereview->listingtype_id); ?></div>
        <?php if($this->reviewsAllowed == 2): ?>
          <div class="o_hidden"><?php echo $this->translate('Average User Rating') ?></div>
        <?php else: ?>
          <div class="o_hidden"><?php echo $this->translate('Average Rating') ?></div>
        <?php endif; ?>
      </div>
      <?php if(!empty($this->allow_review)):?>
				<div class="sr_up_overall_rating_stat">
					<?php echo $this->translate(
			array('Based on %s review', 'Based on %s reviews', $this->subject()->getNumbersOfUserRating($this->type)), '<b>'.$this->locale()->toNumber($this->subject()->getNumbersOfUserRating($this->type)).'</b>') ?>
				</div>
      <?php endif;?>
    <?php endif; ?>

    <?php if(count($this->ratingData)>1 && $this->ratingParameter): ?>
      <div class="sr_up_overall_rating_paramerers clr">
        <?php foreach ($this->ratingData as $reviewcat): ?>
        <?php if (!empty($reviewcat['ratingparam_id'])): ?>
        <div class="o_hidden">
          <div class="parameter_count">
            <?php echo $this->subject()->getNumbersOfUserRating($this->type, $reviewcat['ratingparam_id']); ?> </div>
          <div class="parameter_value">
            <?php echo $this->showRatingStar($reviewcat['avg_rating'], 'user', 'small-box', $this->sitereview->listingtype_id,$reviewcat['ratingparam_name']); ?>
          </div>

          <div class="parameter_title">
            <?php echo $this->translate($reviewcat['ratingparam_name']); ?>
          </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
      
    <?php if ($this->sitereview->rating_users && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)): ?>
      <div class="sr_up_overall_rating_title mtop10">
        <?php echo $this->translate("Recommendations") ?>
      </div>
      <div class="sr_up_overall_rating_stat">
        <?php echo $this->translate("Recommended by %s users", '<b>' . $this->recommend_percentage . '%</b>'); ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
