<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: homesponsored.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
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
  <?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
  <?php if ($this->direction == 1) { ?>
    <?php $j = 0; ?>
    <?php foreach ($this->sitereviews as $sitereview): ?>
      <?php
      echo $this->partial(
              'list_carousel.tpl', 'sitereview', array(
          'sitereview' => $sitereview,
          'title_truncation' => $this->title_truncation,
          'ratingShow' => $ratingShow,
          'ratingType' => $ratingType,
          'ratingValue' => $ratingValue,
          'vertical' => $this->vertical,
          'featuredIcon' => $this->featuredIcon,
          'sponsoredIcon' => $this->sponsoredIcon,
          'showOptions' => $this->showOptions,
					'blockHeight' => $this->blockHeight,
          'blockWidth' => $this->blockWidth,
          'newIcon' => $this->newIcon
      ));
      ?>	 
    <?php endforeach; ?>
    <?php if ($j < ($this->sponserdSitereviewsCount)): ?>
      <?php for ($j; $j < ($this->sponserdSitereviewsCount); $j++): ?>
        <div class="sr_carousel_content_item b_medium" style="visibility: hidden; height: <?php echo ($this->blockHeight) ?>px;width : <?php echo ($this->blockWidth) ?>px;">
        </div>
      <?php endfor; ?>
    <?php endif; ?>
  <?php } else { ?>

    <?php for ($i = $this->sponserdSitereviewsCount; $i < Count($this->sitereviews); $i++): ?>
      <?php $sitereview = $this->sitereviews[$i]; ?>
      <?php
      echo $this->partial(
              'list_carousel.tpl', 'sitereview', array(
          'sitereview' => $sitereview,
          'title_truncation' => $this->title_truncation,
          'ratingShow' => $ratingShow,
          'ratingType' => $ratingType,
          'ratingValue' => $ratingValue,
          'vertical' => $this->vertical,
          'featuredIcon' => $this->featuredIcon,
          'sponsoredIcon' => $this->sponsoredIcon,
          'showOptions' => $this->showOptions,
          'blockHeight' => $this->blockHeight,
          'blockWidth' => $this->blockWidth,        
          'newIcon' => $this->newIcon
      ));
      ?>	
    <?php endfor; ?>

    <?php for ($i = 0; $i < $this->sponserdSitereviewsCount; $i++): ?>
      <?php $sitereview = $this->sitereviews[$i]; ?>
      <?php
      echo $this->partial(
              'list_carousel.tpl', 'sitereview', array(
          'sitereview' => $sitereview,
          'title_truncation' => $this->title_truncation,
          'ratingShow' => $ratingShow,
          'ratingType' => $ratingType,
          'ratingValue' => $ratingValue,
          'vertical' => $this->vertical,
          'featuredIcon' => $this->featuredIcon,
          'sponsoredIcon' => $this->sponsoredIcon,
          'showOptions' => $this->showOptions,
          'blockHeight' => $this->blockHeight,
          'blockWidth' => $this->blockWidth,        
          'newIcon' => $this->newIcon
      ));
      ?>	
    <?php endfor; ?>
  <?php } ?>

