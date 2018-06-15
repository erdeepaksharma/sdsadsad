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
<?php if (count($this->paginator) > 0): ?>
<?php if(!$this->autoContentLoad):?>
  <div class="ui-member-list-head">
    <?php echo $this->translate(array('%s wishlist found.', '%s wishlists found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
<?php endif;?>
<?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
  <div class="sm-content-list">
    <ul class="sr_reviews_listing" data-role="listview" data-icon="arrow-r">
      <?php foreach ($this->paginator as $wishlist): ?>
        <li>
          <a href="<?php echo $wishlist->getHref() ?>">
            <?php echo $this->itemPhoto($wishlist->getCoverItem(), 'thumb.icon') ?>
            <h3><?php echo $wishlist->title ?></h3>
            <?php if (!empty($this->statisticsWishlist)): ?>
              <p>
                <?php
                $statistics = array();
                if (in_array('followCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s follower', '%s followers', $wishlist->follow_count), $this->locale()->toNumber($wishlist->follow_count));
                }

                if (in_array('entryCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s entry', '%s entries', $wishlist->total_item), $this->locale()->toNumber($wishlist->total_item));
                }

                if (in_array('viewCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s view', '%s views', $wishlist->view_count), $this->locale()->toNumber($wishlist->view_count));
                }

                if (in_array('likeCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s like', '%s likes', $wishlist->like_count), $this->locale()->toNumber($wishlist->like_count));
                }
                ?>
                <?php echo join($statistics, " - "); ?>
              </p>
            <?php endif; ?>
            <p>
              <?php echo $this->translate('%s - created by %s', $this->timestamp($wishlist->creation_date), "<b>".$wishlist->getOwner()->getTitle()."</b>") ?>
            </p>
            <?php if (!empty($wishlist->body)): ?>
              <p>
                <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($wishlist->body,100); ?>
              </p>
            <?php endif; ?> 
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php else: ?>
<?php if(!$this->autoContentLoad):?>
   <div id="grid_view">
        <ul class="p_list_grid" id='browsewishlists_ul'>
 <?php endif;?>         
    
          <?php foreach ($this->paginator as $wishlist): ?>
            <li>
              <a href="<?php echo $wishlist->getHref(); ?>" class="ui-link-inherit">
                <div class="p_list_grid_top_sec">
                  <div class="p_list_grid_img">
                    <?php
									$url = $wishlist->getPhotoUrl('thumb.normal');
									if (empty($url)): $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_wishlist_thumb_normal.png';
									endif;
								?>
                <span style="background-image: url(<?php echo $url; ?>);"></span>
                    
                  </div>
                  <div class="p_list_grid_title">
                    <span><?php echo $this->string()->chunk($this->string()->truncate($wishlist->getTitle(), 45), 10); ?></span>
                  </div>
                </div>
              </a>
              <div class="p_list_grid_info">	

                 <?php if (!empty($this->statisticsWishlist)): ?>
             <span class="p_list_grid_stats">
                <?php
                $statistics = array();
                if (in_array('followCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s follower', '%s followers', $wishlist->follow_count), $this->locale()->toNumber($wishlist->follow_count));
                }

                if (in_array('entryCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s entry', '%s entries', $wishlist->total_item), $this->locale()->toNumber($wishlist->total_item));
                }

                if (in_array('viewCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s view', '%s views', $wishlist->view_count), $this->locale()->toNumber($wishlist->view_count));
                }

                if (in_array('likeCount', $this->statisticsWishlist)) {
                  $statistics[] = $this->translate(array('%s like', '%s likes', $wishlist->like_count), $this->locale()->toNumber($wishlist->like_count));
                }
                ?>
                <?php echo join($statistics, " - "); ?>
              </span>
            <?php endif; ?>
                 <span class="p_list_grid_stats">
              <?php echo $this->translate('%s - created by %s', $this->timestamp($wishlist->creation_date), "<b>".$this->htmlLink($wishlist->getOwner()->getHref(), $wishlist->getOwner()->getTitle())."</b>") ?>
            </span>
              </li>      
<?php endforeach;?>
  <?php if(!$this->autoContentLoad):?>
        </ul>
</div>
   <?php endif;?>             
  <?php endif;?>
  <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>
        <?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->formValues, 'pageAsQuery' => true)); ?>
  <?php endif;?>

<?php elseif ($this->isSearched > 2): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Nobody has created a wishlist with that criteria.'); ?>
    </span>
  </div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Nobody has created a wishlist yet.'); ?>
    </span>
  </div>
<?php endif; ?>
<script type="text/javascript">  
    sm4.core.runonce.add(function() {   
       var activepage_id = sm4.activity.activityUpdateHandler.getIndexId();
              sm4.core.Module.core.activeParams[activepage_id] = {'currentPage' : '<?php echo sprintf('%d', $this->page) ?>', 'totalPages' : '<?php echo sprintf('%d', $this->totalPages) ?>', 'formValues' : $.extend(<?php echo json_encode($this->params); ?>, {'is_ajax_load': 1, 'isajax': 1}), 'contentUrl' : sm4.core.baseUrl + 'widget/index/mod/sitereview/name/wishlist-browse', 'activeRequest' : false, 'container' : 'browsewishlists_ul' }; 
         
       
    });

  </script>