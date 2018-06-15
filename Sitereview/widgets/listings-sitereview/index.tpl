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

<?php if(!empty($this->is_ajax_load)): ?>
  <?php
   if ($this->viewType=='gridview'): 
  $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
   endif;?>

  <?php 
    $ratingValue = $this->ratingType; 
    $ratingShow = 'small-star';
    if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
  ?>

  <?php if ($this->viewType=='listview'): ?>
    <ul class="seaocore_sidebar_list">
      <?php foreach ($this->listings as $sitereview): ?>
        <li> 
          <?php echo $this->htmlLink($sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($sitereview, 'thumb.icon')) ?>
          <div class='seaocore_sidebar_list_info'>
            <div class='seaocore_sidebar_list_title'>
              <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->truncation), array('title' => $sitereview->getTitle())) ?>
            </div>
            <?php if(empty($this->category_id)):?>
            <div class='seaocore_sidebar_list_details'>
              <a href="<?php echo $this->url(array('category_id' => $sitereview->category_id, 'categoryname' => $sitereview->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $sitereview->listingtype_id); ?>"> 
              <?php echo $this->translate($sitereview->getCategory()->getTitle(true))?>
              </a>
            </div>
            <?php endif; ?>
            <?php if(!empty($this->statistics)): ?>
              <div class='seaocore_sidebar_list_details'>
                <?php 
                  $statistics = '';
                  if(!empty($this->statistics) && in_array('commentCount', $this->statistics)) {
                    $statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).', ';
                  }

                  $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
                  if(!empty($this->statistics) && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($listingtypeArray->allow_review) || isset ($sitereview->rating_editor) && $sitereview->rating_editor && $sitereview->review_count==1)) {
                    $statistics .= $this->partial(
                    '_showReview.tpl', 'sitereview', array('sitereview'=>$sitereview)).', ';
                  }

                  if(!empty($this->statistics) && in_array('viewCount', $this->statistics)) {
                    $statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).', ';
                  }

                  if(!empty($this->statistics) && in_array('likeCount', $this->statistics)) {
                    $statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).', ';
                  }                 

                  $statistics = trim($statistics);
                  $statistics = rtrim($statistics, ',');

                ?>
                <?php echo $statistics; ?>
              </div>
            <?php endif; ?>

            <?php if($ratingValue == 'rating_both'): ?>
              <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
              <br/>
              <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
            <?php else: ?>
              <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
            <?php endif; ?>
            <div class="clr mtop5">
              <?php echo $this->compareButton($sitereview); ?>   
              <span class="fright">
                <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>
              </span>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
     <?php $isLarge = ($this->columnWidth>170); ?>
     <ul  class="sr_grid_view sr_grid_view_listings o_hidden"> 
      <?php foreach ($this->listings as $sitereview): ?>
       <li class="b_medium" style="width: <?php echo $this->columnWidth; ?>px;">
        <div class="sr_product_details" style="height:<?php echo $this->columnHeight; ?>px;">
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
            <?php if($sitereview->newlabel):?>
              <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
            <?php endif;?>
          <?php endif;?>
          <a href="<?php echo $sitereview->getHref(array('profile_link' => 1)) ?>" class ="sr_thumb">
            <?php
            $url = $sitereview->getPhotoUrl($isLarge ? 'thumb.midum' : 'thumb.normal');
            if (empty($url)): $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_normal.png';
            endif;
            ?>
            <span style="background-image: url(<?php echo $url; ?>); <?php if($isLarge): ?> height:160px; <?php endif;?> "></span>
          </a>
          <div class="sr_title">
            <?php echo $this->htmlLink($sitereview->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->truncation), array('title' => $sitereview->getTitle())) ?>
          </div>
          <div class="sr_category clr">
            <a href="<?php echo $sitereview->getCategory()->getHref() ?>"> 
              <?php echo $this->translate($sitereview->getCategory()->getTitle(true)) ?>
            </a>
          </div>
          <?php if(!empty($this->statistics)): ?>    
            <div class="sr_category seaocore_txt_light">
             <?php 
                $statistics = '';
                if(!empty($this->statistics) && in_array('commentCount', $this->statistics)) {
                  $statistics .= $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)).', ';
                }
                if(!empty($this->statistics) && in_array('viewCount', $this->statistics)) {
                  $statistics .= $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)).', ';
                }

                if(!empty($this->statistics) && in_array('likeCount', $this->statistics)) {
                  $statistics .= $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)).', ';
                }                 

                $statistics = trim($statistics);
                $statistics = rtrim($statistics, ',');

              ?>
              <?php echo $statistics; ?> 
            </div>
          <?php endif; ?>    
          <div class="sr_ratingbar seaocore_txt_light">
            <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); ?>   
            <?php if(!empty($this->statistics) && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2)):  ?>
            <span class="fright">
              <?php echo $this->htmlLink($sitereview->getHref(), $this->partial(
                              '_showReview.tpl', 'sitereview', array('sitereview' => $sitereview))); ?>
            </span>
            <?php endif; ?>
            <?php if ($ratingValue == 'rating_both'): ?>
              <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
              <br />
              <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
            <?php else: ?>
              <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
            <?php endif; ?>
          </div>

          <div class="sr_grid_view_list_btm b_medium">
            <?php echo $this->compareButton($sitereview); ?>
            <span class="fright">
              <?php if ($sitereview->sponsored == 1): ?>
                <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
              <?php endif; ?>
              <?php if ($sitereview->featured == 1): ?>
                <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
              <?php endif; ?>

              <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>  
            </span>
          </div>
        </div>
      </li>
       <?php endforeach; ?>
    </ul>
  <?php endif; ?>
<?php else: ?>

  <div id="layout_sitereview_listings_sitereview_<?php echo $this->identity;?>">
<!--    <div class="seaocore_content_loader"></div>-->
  </div>

  <script type="text/javascript">
    var requestParams = $merge(<?php echo json_encode($this->params);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_listings_sitereview_<?php echo $this->identity;?>',
       requestParams: requestParams      
    };

    en4.seaocore.locationBased.startReq(params);
  </script>  

<?php endif; ?>
