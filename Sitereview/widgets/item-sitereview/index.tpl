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
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl
        . 'application/modules/Seaocore/externals/styles/styles.css');
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

<ul class="seaocore_item_day">
  <li class="prelative">
		<?php if($this->sitereview->newlabel):?>
			<i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
		<?php endif;?>
    <?php echo $this->htmlLink($this->sitereview->getHref(), $this->itemPhoto($this->sitereview, 'thumb.profile')) ?>
    <?php echo $this->htmlLink($this->sitereview->getHref(), $this->sitereview->getTitle(), array('title' => $this->sitereview->getTitle())) ?>
    <div class='seaocore_browse_list_info_date'>
      <?php echo $this->htmlLink($this->sitereview->getCategory()->getHref(), $this->translate($this->sitereview->getCategory()->getTitle(true)), array()) ?>
    </div>
    <div class='seaocore_browse_list_info_date'>  
      <?php if ($ratingValue == 'rating_both'): ?>
        <?php echo $this->showRatingStar($this->sitereview->rating_editor, 'editor', $ratingShow, $this->sitereview->listingtype_id); ?>
        <br/>
        <?php echo $this->showRatingStar($this->sitereview->rating_users, 'user', $ratingShow, $this->sitereview->listingtype_id); ?>
      <?php else: ?>
        <?php echo $this->showRatingStar($this->sitereview->$ratingValue, $ratingType, $ratingShow, $this->sitereview->listingtype_id); ?>
      <?php endif; ?>
        
      <span class="fright">
        <?php echo $this->htmlLink($this->sitereview->getHref(), $this->partial(
                        '_showReview.tpl', 'sitereview', array('sitereview' => $this->sitereview))); ?>
      </span>
    </div>
    <div class="clr mtop5">
      <?php echo $this->compareButton($this->sitereview); ?>
      <span class="fright">
        <?php if ($this->sitereview->sponsored == 1): ?>
          <i title="<?php echo $this->translate('Sponsored'); ?>" class="sr_icon seaocore_icon_sponsored"></i>
        <?php endif; ?>
				<?php if ($this->sitereview->featured == 1): ?>
				   <i title="<?php echo $this->translate('Featured'); ?>" class="sr_icon seaocore_icon_featured"></i>
				<?php endif; ?>
        <?php echo $this->addToWishlist($this->sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>
      </span>
    </div>
  </li>
</ul>