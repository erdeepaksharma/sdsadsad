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

<ul class="seaocore_sidebar_list">
  <?php foreach( $this->wishlists as $wishlist ): ?>
    <li>
      <?php echo $this->htmlLink($wishlist->getHref(), $this->itemPhoto($wishlist->getCoverItem(), 'thumb.icon'), array('class' => 'popularmembers_thumb', 'title' => $wishlist->getTitle()), array('title' => $wishlist->getTitle())) ?>
      <div class='seaocore_sidebar_list_info'>
        <div class='seaocore_sidebar_list_title'>
          <?php echo $this->htmlLink($wishlist->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($wishlist->getTitle(), $this->title_truncation), array('title' =>  $wishlist->getTitle())) ?>
        </div>

        <div class='seaocore_sidebar_list_details'>
          <?php echo $this->translate('By %s', $wishlist->getOwner()->toString()) ?>
        </div>

        <?php if(!empty($this->statisticsWishlist)): ?>
          <div class='seaocore_sidebar_list_details'>
            <?php 
              $statistics = '';
              if(in_array('followCount', $this->statisticsWishlist)) {
                $statistics .= $this->translate(array('%s follower', '%s followers', $wishlist->follow_count), $this->locale()->toNumber($wishlist->follow_count)).', ';
              }
              
              if(in_array('entryCount', $this->statisticsWishlist)) {
                $statistics .= $this->translate(array('%s entry', '%s entries', $wishlist->total_item), $this->locale()->toNumber($wishlist->total_item)).', ';
              }              

              if(in_array('viewCount', $this->statisticsWishlist)) {
                $statistics .= $this->translate(array('%s view', '%s views', $wishlist->view_count), $this->locale()->toNumber($wishlist->view_count)).', ';
              }

              if(in_array('likeCount', $this->statisticsWishlist)) {
                $statistics .= $this->translate(array('%s like', '%s likes', $wishlist->like_count), $this->locale()->toNumber($wishlist->like_count)).', ';
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
