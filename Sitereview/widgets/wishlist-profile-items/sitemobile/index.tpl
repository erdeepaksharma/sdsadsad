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
$favouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);
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

 <?php if(!$this->autoContentLoad):?>
<div class="sr_wishlist_view">
  <?php if(!$favouriteSetting): ?>
 <?php if(!Engine_Api::_()->sitemobile()->isApp()):?>
  <h3>
    <?php echo $this->subject()->title; ?> 
  </h3>
 <?php endif;?> 
  <div class="sm-ui-cont-head">
    <?php if ($this->postedby): ?>
      <div class="sm-ui-cont-author-photo">
        <?php echo $this->htmlLink($this->subject()->getOwner(), $this->itemPhoto($this->subject()->getOwner(), 'thumb.icon')) ?>
      </div>
    <?php endif; ?>
    <div class="sm-ui-cont-cont-info">
      <?php if ($this->postedby): ?>
        <div class="sm-ui-cont-author-name">
          <?php 
             if(!Engine_Api::_()->sitemobile()->isApp()):
                echo $this->htmlLink($this->subject()->getOwner(), $this->subject()->getOwner()->getTitle());
             else: 
               $userLink = $this->htmlLink($this->subject()->getOwner(), $this->subject()->getOwner()->getTitle());
             echo $this->translate('%s\'s wishlists', $userLink);
               
             endif;
              ?>
        </div>
      <?php endif; ?>
      <div class="sm-ui-cont-cont-date">
        <?php echo $this->timestamp($this->subject()->creation_date) ?> 
      </div>
      <?php if (!empty($this->statisticsWishlist)): ?>
        <div class="sm-ui-cont-cont-date">
          <?php
          $statistics = array();
          if (in_array('followCount', $this->statisticsWishlist)) {
            $statistics [] = $this->translate(array('<b>%s</b> Follower', '<b>%s</b> Followers', $this->subject()->follow_count), $this->locale()->toNumber($this->subject()->follow_count));
          }

          if (in_array('entryCount', $this->statisticsWishlist)) {
            $statistics[] = $this->translate(array('<b>%s</b> Entry', '<b>%s</b> Entries', $this->total_item), $this->locale()->toNumber($this->total_item));
          }

          if (in_array('viewCount', $this->statisticsWishlist)) {
            $statistics [] = $this->translate(array('<b>%s</b> View', '<b>%s</b> Views', $this->subject()->view_count), $this->locale()->toNumber($this->subject()->view_count));
          }

          if (in_array('likeCount', $this->statisticsWishlist)) {
            $statistics [] = $this->translate(array('<b>%s</b> Like', '<b>%s</b> Likes', $this->subject()->like_count), $this->locale()->toNumber($this->subject()->like_count));
          }
          ?>
          <?php echo join($statistics, ' - '); ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
  <p class="sr_wishlist_view_des mbot10">
    <?php echo $this->subject()->body; ?>
  </p>
  <?php if ($this->viewer_id && (!empty($this->followLike) || !empty($this->messageOwner))): ?>
    <div class="seaocore_profile_cover_buttons">
      <table cellpadding="2" cellspacing="0">
        <tbody> 
          <tr>
            <?php if (!empty($this->followLike) && in_array('like', $this->followLike)): ?>
              <td>
                <a href ="javascript://"  data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' id="<?php echo $this->subject()->getType() ?>_<?php echo $this->subject()->getIdentity() ?>unlike_link" <?php if (!$this->subject()->likes()->isLike($this->viewer())): ?> style="display:none;" <?php endif; ?> onclick="sm4.core.likes.unlike('<?php echo $this->subject()->getType() ?>', '<?php echo $this->subject()->getIdentity() ?>','1');">
                  <?php if(Engine_Api::_()->sitemobile()->isApp()):?>
                  <i class="ui-icon ui-icon-thumbs-up-alt feed-unlike-btn"></i>
                  <?php else :?>
                    <i class="ui-icon ui-icon-thumbs-down-alt feed-unlike-btn"></i>                  
                  <?php endif;?>
                  <span><?php echo $this->translate('Like') ?></span>
                </a>
                <a data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' href = "javascript://" id="<?php echo $this->subject()->getType() ?>_<?php echo $this->subject()->getIdentity() ?>like_link" <?php if ($this->subject()->likes()->isLike($this->viewer())): ?> style="display: none;" <?php endif; ?> onclick="sm4.core.likes.like('<?php echo $this->subject()->getType() ?>', '<?php echo $this->subject()->getIdentity() ?>','1');"  >
                  <i class="ui-icon ui-icon-thumbs-up-alt feed-like-btn"></i>
                  <span><?php echo $this->translate('Like') ?></span>
                </a>
              </td>
            <?php endif; ?>
            <?php if (!empty($this->followLike) && in_array('follow', $this->followLike)): ?>
              <td>
                <?php $check_availability = $this->subject()->follows()->isFollow($this->viewer); ?>
                <a href ="javascript://" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' id="sitereview_wishlist_unfollows_<?php echo $this->subject()->wishlist_id; ?>" onclick = "seaocore_resource_type_follows_sitemobile('<?php echo $this->subject()->wishlist_id; ?>', 'sitereview_wishlist');" style ='display:<?php echo $check_availability ? "block" : "none" ?>'>
                  <i class="ui-icon-delete"></i>
                  <span><?php echo $this->translate('Unfollow') ?></span>
                </a>
                <a href = "javascript://" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' id="sitereview_wishlist_most_follows_<?php echo $this->subject()->wishlist_id; ?>" style ='display:<?php echo empty($check_availability) ? "block" : "none" ?>' onclick = "seaocore_resource_type_follows_sitemobile('<?php echo $this->subject()->wishlist_id; ?>', 'sitereview_wishlist');" >
                  <i class="ui-icon-plus"></i>
                  <span><?php echo $this->translate('Follow') ?></span>
                </a>
                <input type ="hidden" id = "sitereview_wishlist_follow_<?php echo $this->subject()->wishlist_id; ?>" value = '<?php echo $check_availability ? $check_availability : 0; ?>' />
              </td>
            <?php endif; ?>
            <?php if (!empty($this->messageOwner)): ?>
              <td>
                <?php $url =  $this->url(array('action' => 'message-owner', 'wishlist_id' => $this->wishlist->getIdentity()), 'sitereview_wishlist_general', true); ?>
                <?php if(Engine_Api::_()->sitemobile()->isApp()): ?>
                  <a href = "<?php echo $url; ?>" class="smoothbox" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true'>
                    <i class="ui-icon-envelope"></i>
                    <span><?php echo $this->translate('Message Owner') ?></span>
                  </a>
                  <?php //echo $this->htmlLink(array('route' => 'sitereview_wishlist_general', 'action' => 'message-owner', 'wishlist_id' => $this->wishlist->getIdentity()), $this->translate('Message Owner'), array('class' => 'smoothbox icon_sitereviews_messageowner', 'data-role' => 'button', 'data-inset' => 'false', 'data-mini' => 'true', 'data-corners' => 'false', 'data-shadow' => 'true', 'data-icon' => 'envelope')) ?>
                <?php else: ?>
                  <a href="<?php echo $url; ?>" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' data-ajax ='true'>
                    <i class="ui-icon-envelope"></i>
                    <span><?php echo $this->translate('Message Owner') ?></span>
                  </a>
                  <?php //echo $this->htmlLink(array('route' => 'sitereview_wishlist_general', 'action' => 'message-owner', 'wishlist_id' => $this->wishlist->getIdentity()), $this->translate('Message Owner'), array('class' => 'icon_sitereviews_messageowner', 'data-role' => 'button', 'data-inset' => 'false', 'data-mini' => 'true', 'data-corners' => 'false', 'data-shadow' => 'true', 'data-icon' => 'envelope', 'data-ajax' => true)) ?>
                <?php endif;?>
              </td>
            <?php endif; ?>
          </tr></tbody>
      </table>
    </div>
  <?php endif; ?>
  <?php else: ?>
        <h3>
          <?php echo $this->translate('My Favourites'); ?> 
        </h3>
        <p class="sr_wishlist_view_des mbot10">
          <?php echo $this->translate('This page lists all the entries added by you as favourites.'); ?>
        </p>     
    <?php endif;?>
</div>
 <?php endif;?>   
  <?php if ($this->total_item > 0): ?>
    <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>
      <?php if(!$this->autoContentLoad):?>
        <div class="clr">
          <ul class="p_list_grid" id='profileishlists_ul'>
      <?php endif;?> 
            <?php foreach ($this->paginator as $listing): ?>
              <li>
                <a href="<?php echo $listing->getHref(array('profile_link' => 1)) ?>" class="ui-link-inherit">
                  <div class="p_list_grid_top_sec">
                    <div class="p_list_grid_img">
                      <?php
                      $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_profile.png';
                      $temp_url = $listing->getPhotoUrl('thumb.profile');
                      if (!empty($temp_url)): $url = $listing->getPhotoUrl('thumb.profile');
                      endif;
                      ?>
                      <span style="background-image: url(<?php echo $url; ?>);"> </span>
                    </div>                 
                    <div class="p_list_grid_title">
                      <span><?php echo $listing->getTitle(); ?></span>               
                    </div>
                  </div>
                </a>
                <div class="p_list_grid_info<?php if ($this->wishlist->owner_id == $this->viewer_id): ?> p_list_grid_icon_right<?php endif;?>">
                  <span class="p_list_grid_stats">
                    <span class="fleft"><?php echo $this->timestamp(strtotime($listing->date)) ?></span>
                    <?php if (!empty($listing->price) && Zend_Registry::get('listingtypeArray' . $listing->listingtype_id)->price): ?>
                      <span class="fright">
                        <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($listing->price);  ?></b>
                      </span>
                    <?php endif; ?>
                  </span>
                  <span class="p_list_grid_stats"> 
                    <?php echo $this->translate("in %s", '<b>' . $this->translate($listing->getCategory()->getTitle(true)). '</b>')?></span>
                  <span class="p_list_grid_stats">
                    <?php if ($ratingValue == 'rating_both'): ?>
                      <?php echo $this->showRatingStar($listing->rating_editor, 'editor', $ratingShow, $listing->listingtype_id); ?>
                      <br/>
                      <?php echo $this->showRatingStar($listing->rating_users, 'user', $ratingShow, $listing->listingtype_id); ?>
                    <?php else: ?>
                      <?php echo $this->showRatingStar($listing->$ratingValue, $ratingType, $ratingShow, $listing->listingtype_id); ?>
                    <?php endif; ?>
                  </span>
                  <?php if ($this->wishlist->owner_id == $this->viewer_id): ?>
                    <a data-icon ="delete" href="<?php echo $this->url(array('action' => 'remove', 'listing_id' => $listing->listing_id, 'wishlist_id' => $this->wishlist->wishlist_id),'sitereview_wishlist_general',true);?>" class="smoothbox righticon ui-btn-icon-notext ui-icon-delete"></a>
                  <?php endif; ?> 
                </div>
              </li>
            <?php endforeach; ?>
      <?php if(!$this->autoContentLoad):?>
          </ul>
        </div>
      <?php endif;?>
    <?php else : ?>
      <?php if(!$this->autoContentLoad):?>
        <div class="sm-content-list">
          <ul class="sr_reviews_listing" data-role="listview" data-icon="arrow-r" id='profileishlists_ul'>
        <?php endif;?>    
            <?php foreach ($this->paginator as $listing): ?>
              <li>
                <a href="<?php echo $listing->getHref(array('profile_link' => 1)) ?>" >
                  <?php echo $this->itemPhoto($listing, 'thumb.normal') ?>
                  <h3><?php echo $listing->getTitle(); ?></h3>
                  <p>
                    <?php if ($ratingValue == 'rating_both'): ?>
                      <?php echo $this->showRatingStar($listing->rating_editor, 'editor', $ratingShow, $listing->listingtype_id); ?>
                      <br/>
                      <?php echo $this->showRatingStar($listing->rating_users, 'user', $ratingShow, $listing->listingtype_id); ?>
                    <?php else: ?>
                      <?php echo $this->showRatingStar($listing->$ratingValue, $ratingType, $ratingShow, $listing->listingtype_id); ?>
                    <?php endif; ?>
                  </p>
                  <p> <?php echo $this->translate("in %s", '<b>' . $this->translate($listing->getCategory()->getTitle(true)). '</b>')?></p>
                  <p><?php echo $this->timestamp(strtotime($listing->date)) ?>        
                    <?php if (!empty($listing->price) && Zend_Registry::get('listingtypeArray' . $listing->listingtype_id)->price): ?>
                      - <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($listing->price); ?></b>
                    <?php endif; ?></p>
                  <p><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($listing->body, 150); ?>
                  </p>
                </a>

                <?php if ($this->wishlist->owner_id == $this->viewer_id): ?>
                      <?php if($favouriteSetting): ?>
                          <?php $removeText = 'Remove from Favourites';?>
                      <?php else: ?>
                          <?php $removeText = 'Remove from this Wishlist';?>
                      <?php endif; ?>
                <a data-icon ="delete" href="<?php echo $this->url(array('action' => 'remove', 'listing_id' => $listing->listing_id, 'wishlist_id' => $this->wishlist->wishlist_id),'sitereview_wishlist_general',true);?>" class = "smoothbox">
              <?php echo $this->translate($removeText);?>
                </a>
                  <?php endif; ?> 
              </li>
            <?php endforeach; ?>
      <?php if(!$this->autoContentLoad):?>
          </ul>
        </div>
      <?php endif;?>
  <?php endif;?>
  <?php if ($this->paginator->count() > 1 && !Engine_Api::_()->sitemobile()->isApp()): ?>
    <br />
    <?php
    echo $this->paginationControl(
            $this->paginator, null, null);
    ?>
  <?php endif; ?>
<?php else: ?>
  <div class="tip">
    <span>
     <?php if(!$favouriteSetting): ?>  
                <?php echo $this->translate('There are currently no entries in this wishlist.'); ?>
            <?php else: ?>
                <?php echo $this->translate('You have not added any entry in your favourites.'); ?>
            <?php endif; ?> 
    </span> 
  </div>
<?php endif; ?>

    <script type="text/javascript">  
    sm4.core.runonce.add(function() {   
       var activepage_id = sm4.activity.activityUpdateHandler.getIndexId();
              sm4.core.Module.core.activeParams[activepage_id] = {'currentPage' : '<?php echo sprintf('%d', $this->page) ?>', 'totalPages' : '<?php echo sprintf('%d', $this->totalPages) ?>', 'formValues' : $.extend(<?php echo json_encode($this->params); ?>, {'is_ajax_load': 1, 'isajax': 1, 'currentpage': '<?php echo sprintf('%d', ($this->page + 1)) ?>', 'subject': '<?php echo $this->wishlist->getGuid();?>'}), 'contentUrl' : sm4.core.baseUrl + 'widget/index/mod/sitereview/name/wishlist-profile-items', 'activeRequest' : false, 'container' : 'profileishlists_ul' }; 
         
       
    });

  </script>