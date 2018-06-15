<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: list_carousel.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
$sitereview = $this->sitereview;
$ratingShow = $this->ratingShow;
$ratingType = $this->ratingType;
$ratingValue = $this->ratingValue;
?>
  <li class="sr_carousel_content_item b_medium" style="height: <?php echo ($this->blockHeight) ?>px;width : <?php echo ($this->blockWidth) ?>px;">
    <div class="sr_product_details" style="height: <?php echo ($this->blockHeight) ?>px;">
      <center>
				<?php if($sitereview->newlabel && $this->newIcon):?>
					<i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
				<?php endif;?>
          <a href="<?php echo $sitereview->getHref(array('profile_link' => 1)) ?>" class ="sr_thumb" title="<?php echo $sitereview->getTitle()?>">
          <?php
          $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_main.png';
          $temp_url = $sitereview->getPhotoUrl('thumb.main');
          if (!empty($temp_url)): $url = $sitereview->getPhotoUrl('thumb.main');
          endif;
          ?>
          <span style="background-image: url(<?php echo $url; ?>); "></span>
        </a>
      </center>
      <div class="sr_title">
        <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation), array('title' => $sitereview->getTitle())) ?>
      </div>
      
        <?php if($sitereview->price > 0): ?>
          <div class="sr_price">
            <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?>
          </div>
        <?php endif; ?>
        
        
      <?php if(!empty($this->showOptions) && (in_array('category', $this->showOptions) || in_array('review', $this->showOptions) ||in_array('rating', $this->showOptions))): ?>
        <div class="sr_carousel_cnt clr">
          <?php if(!empty($this->showOptions) && in_array('category', $this->showOptions)): ?>
            <div class="sr_category seaocore_txt_light"> 
              <a href="<?php echo $sitereview->getCategory()->getHref() ?>"> 
                <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?>
              </a>
            </div>
          <?php endif; ?>
          <div class="sr_ratingbar seaocore_txt_light">
            <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); ?>
            <?php if(($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($this->showOptions) && in_array('review', $this->showOptions))): ?>
              <span class="fright">
                <?php echo $this->htmlLink($sitereview->getHref(), $this->partial(
                                '_showReview.tpl', 'sitereview', array('sitereview' => $sitereview))); ?>
              </span>
            <?php endif; ?>
            <?php if(!empty($this->showOptions) && in_array('rating', $this->showOptions)): ?>          
              <?php if ($ratingValue == 'rating_both'): ?>
                <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
                <br />
                <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
              <?php else: ?>
                <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
              <?php endif; ?>
            <?php endif; ?>  
          </div>
        </div>  
      <?php endif; ?>
      <?php if((!empty($this->showOptions) && (in_array('compare', $this->showOptions)||in_array('wishlist', $this->showOptions))) || $this->sponsoredIcon || $this->featuredIcon): ?>
        <div class="sr_grid_view_list_btm b_medium">
          <?php if(!empty($this->showOptions) && in_array('compare', $this->showOptions)): ?>
            <?php echo $this->compareButton($sitereview); ?>
          <?php endif; ?>
          <span class="fright">
            <?php if ($sitereview->sponsored == 1 && $this->sponsoredIcon): ?>
              <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
            <?php endif; ?>
            <?php if ($sitereview->featured == 1 && $this->featuredIcon): ?>
              <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
            <?php endif; ?>
            <?php if (Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->wishlist && !empty($this->showOptions) && in_array('wishlist', $this->showOptions)): ?> 
              <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>
            <?php endif; ?>
          </span>
        </div>
      <?php endif; ?>
    </div>
  </li>

