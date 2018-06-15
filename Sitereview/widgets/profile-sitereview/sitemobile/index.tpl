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

<?php if (empty($this->isajax)) : ?> 
  <div id="listing_profile_layout" class="ui-page-content">
  <?php endif; ?>
<?php 
  $ratingValue = $this->ratingType; 
  $ratingShow = 'small-star';
    if ($this->ratingType == 'rating_editor') {$ratingType = 'editor';} elseif ($this->ratingType == 'rating_avg') {$ratingType = 'overall';} else { $ratingType = 'user';}
?>
<?php if (Engine_Api::_()->sitemobile()->isApp()): ?>
  <?php if (!$this->viewmore): ?>
    <div id="list_view">
      <ul class="p_list_grid">
  <?php endif; ?>  
        <?php foreach( $this->paginator as $sitereview ): ?>
          <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
          $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
          <li>
            <a href="<?php echo $sitereview->getHref(); ?>" class="ui-link-inherit">
              <div class="p_list_grid_top_sec">
                <div class="p_list_grid_img">
                  <?php
                    $url = $sitereview->getPhotoUrl('thumb.profile');
                    if (empty($url)): $url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_profile.png';
                    endif;
                  ?>
                  <span style="background-image: url(<?php echo $url; ?>);"></span>
                </div>
                <div class="p_list_grid_title">
                  <span>
                    <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation); ?>
                  </span>
                </div>
              </div>
              <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>
                <div class="list-label-wrap">
                  <?php if ($sitereview->sponsored == 1): ?>
                    <span class="list-label" style='background: <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.sponsored.color', '#fc0505'); ?>;'><?php echo $this->translate('SPONSORED');?></span>
                  <?php endif; ?>
                  <?php if ($sitereview->featured == 1): ?>
                    <span class="list-label" style='background: <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.featured.color', '#0cf523'); ?>;'><?php echo $this->translate('FEATURED');?></span>
                  <?php endif; ?>
                </div>
              <?php endif;?>
            </a>
            <div class="p_list_grid_info">
              <span class="p_list_grid_stats">
                <?php $contentArray = array(); ?>
                <?php $contentArray [] = $this->timestamp(strtotime($sitereview->creation_date)) ?>
                <?php if (!empty($this->statistics)): ?>
                  <?php
                  if (in_array('commentCount', $this->statistics)) {
                    $contentArray [] = $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count));
                  }
                  if (in_array('reviewCount', $this->statistics) && (!empty($listingType->allow_review) || isset ($sitereview->rating_editor) && $sitereview->rating_editor && $sitereview->review_count==1)) {
                    $contentArray[] = $this->partial(
                            '_showReview.tpl', 'sitereview', array('sitereview' => $sitereview));
                  }
                  if (in_array('viewCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count));
                  }
                  if (in_array('likeCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count));
                  }
                  ?>
                <?php endif; ?>
                <?php
                if (!empty($contentArray)) {
                  echo join(" - ", $contentArray);
                }
                ?> 
              </span>
              <span class="p_list_grid_stats">
                <?php if($ratingValue == 'rating_both'): ?>
                  <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
                  <br/>
                  <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
                <?php else: ?>
                  <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
                <?php endif; ?>
              </span>
            </div>
          </li>
        <?php endforeach; ?>
  <?php if (!$this->viewmore): ?>
        </ul>
    </div>
  <?php endif; ?> 

<?php else :?>
  <?php if (!$this->viewmore): ?>
    <div id="list_view" class="sm-content-list">
      <ul data-role="listview" data-inset="false">
  <?php endif; ?>  
        <?php foreach( $this->paginator as $sitereview ): ?>
          <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
          $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);?>
          <li data-icon="arrow-r">
            <a href="<?php echo $sitereview->getHref(); ?>">
              <?php echo $this->itemPhoto($sitereview, 'thumb.icon'); ?>
              <h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation); ?></h3>
              <p class="ui-li-desc">
                <?php if ($this->bottomLine): ?>  
                  <?php  echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getBottomLine(), 50);
             
      //$this->viewMore($sitereview->getBottomLine(), 125, 5000); ?>
                <?php else: ?>
                  <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->body, 50) ;                       
                  
                  //$this->viewMore(strip_tags($sitereview->body), 125, 5000); ?>
                <?php endif; ?>
              </p>
              <p>
                <?php $contentArray = array(); ?>
                <?php $contentArray [] = $this->timestamp(strtotime($sitereview->creation_date)) ?>
                <?php if (!empty($this->statistics)): ?>
                  <?php
                  if (in_array('commentCount', $this->statistics)) {
                    $contentArray [] = $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count));
                  }
                  if (in_array('reviewCount', $this->statistics) && (!empty($listingType->allow_review) || isset ($sitereview->rating_editor) && $sitereview->rating_editor && $sitereview->review_count==1)) {
                    $contentArray[] = $this->partial(
                            '_showReview.tpl', 'sitereview', array('sitereview' => $sitereview));
                  }
                  if (in_array('viewCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count));
                  }
                  if (in_array('likeCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count));
                  }
                  ?>
                <?php endif; ?>
                <?php
                if (!empty($contentArray)) {
                  echo join(" - ", $contentArray);
                }
                ?> 
              </p>
              <p>
                <?php if($ratingValue == 'rating_both'): ?>
                  <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
                  <br/>
                  <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
                <?php else: ?>
                  <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
                <?php endif; ?>
              </p>
              <p> 
                <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>
                  <span class="sr_browse_list_info_footer_icons">
                    <?php if ($sitereview->closed): ?>
                            <i class="sr_icon icon_sitereviews_close" title="<?php echo $this->translate('Closed'); ?>"></i>
                          <?php endif; ?> 
                  <?php if ($sitereview->sponsored == 1): ?>
                    <i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
                  <?php endif; ?>
                  <?php if ($sitereview->featured == 1): ?>
                    <i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
                  <?php endif; ?>
                  </span>
                <?php endif;?>
              </p>       
            </a>
          </li>
        <?php endforeach; ?>
  <?php if (!$this->viewmore): ?>
        </ul>
    </div>
  <?php endif; ?> 
<?php endif; ?> 
<?php if ($this->current_page < 2 && $this->totalCount > ($this->current_page * $this->limit)) : ?>
  <div class="feed_viewmore clr">
  <?php
      echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
      'id' => 'feed_viewmore_link',
      'class' => 'ui-btn-default icon_viewmore',
      'onclick' => 'showMoreProfileListings()'
      ))
    ?>
  </div>
  <div class="seaocore_loading feeds_loading" style="display: none;">
    <i class="icon_loading"></i>
  </div>
<?php endif; ?>

<?php if (empty($this->isajax)) : ?>
<script type="text/javascript">
  var totalCount = <?php echo sprintf('%d', $this->totalCount) ?>;
      //var listingType = '<?php echo $this->viewType; ?>';
     
      function showMoreProfileListings() {  
        $('.seaocore_loading').css('display', 'block');
        $('.feed_viewmore').css('display', 'none');
        allParams.page = allParams.page + 1;
        allParams.format = 'html';
        allParams.subject = sm4.core.subject.guid;
        allParams.is_ajax_load = true;
        allParams.isajax = true;
        allParams.viewmore = 1;        
        $.ajax({
          type: "GET", 
          dataType: "html",
          url: sm4.core.baseUrl + 'widget/index/mod/sitereview/name/profile-sitereview',
          data: allParams,
          success:function( responseHTML, textStatus, xhr ) { 
            
            if ($.type($('div.tab_' + '<?php echo $this->identity; ?>')) != 'undefined') {
              $('#listing_profile_layout').find('ul').append(responseHTML)
              //$.mobile.activePage.find('ul').trigger("create");
              $('#listing_profile_layout').find('ul').listview('refresh');
            }
            if (totalCount > (parseInt(allParams.page) * parseInt(allParams.limit))) {
              $.mobile.activePage.find('.seaocore_loading').css('display', 'none');
              $.mobile.activePage.find('.feed_viewmore').css('display', 'block');
            }
            else {
              $.mobile.activePage.find('.seaocore_loading').css('display', 'none');
              $.mobile.activePage.find('.feed_viewmore').css('display', 'none');            
            }
            sm4.core.dloader.refreshPage();
            sm4.core.runonce.trigger();          
            
          }
        });
        
        
      }

  
  
  </script>
  
  </div>

<?php endif;?>
<script type="text/javascript">
  var allParams = <?php echo json_encode($this->allParams); ?>;

</script>
