<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: print.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Sitereview/externals/styles/style_sitereview_print.css'); ?>
<link href="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview_print.css'?>" type="text/css" rel="stylesheet" media="print">

<?php 
  $ratingValue = 'rating_editor'; 
  $ratingShow = 'small-star';
  if($this->ratingType == 'rating_editor') { $ratingType = 'editor';} else {$ratingType = 'user';}
?> 
<div class="sr_print_page">
	<div class="sr_print_page_header">
		<span>
			<?php $site_title =  Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title', 'Advertisement'); echo $this->translate($site_title).' - '.$this->translate('Wishlist');?>
		</span>
		<div id="printdiv">
			<a href="javascript:void(0);" style="background-image: url('<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/printer.png');" class="buttonlink" onclick="printData()" align="right"><?php echo $this->translate('Take Print') ?></a>
		</div>
	</div>

  <div class="sr_wishlist_view">
    <div class="sr_wishlist_view_title"> 
      <?php echo $this->wishlist->title; ?> 
    </div>    
    <div class="sr_wishlist_view_stats">
      <?php echo $this->translate('Created by %s %s', $this->wishlist->getOwner()->toString(), $this->timestamp($this->wishlist->creation_date)) ?>
    </div>
    <div class="sr_wishlist_view_stats"> 
      <?php if(!empty($this->statisticsWishlist)): ?>

        <?php 
          $statistics = '';
          if(in_array('followCount', $this->statisticsWishlist)) {
            $statistics .= $this->translate(array('%s follower', '%s followers', $this->wishlist->follow_count), $this->locale()->toNumber($this->wishlist->follow_count)).', ';
          }

          if(in_array('entryCount', $this->statisticsWishlist)) {
            $statistics .= $this->translate(array('%s entry', '%s entries', $this->total_item), $this->locale()->toNumber($this->total_item)).', ';
          }                            

          if(in_array('viewCount', $this->statisticsWishlist)) {
            $statistics .= $this->translate(array('%s view', '%s views', $this->wishlist->view_count), $this->locale()->toNumber($this->wishlist->view_count)).', ';
          }

          if(in_array('likeCount', $this->statisticsWishlist)) {
            $statistics .= $this->translate(array('%s like', '%s likes', $this->wishlist->like_count), $this->locale()->toNumber($this->wishlist->like_count)).', ';
          }                 

          $statistics = trim($statistics);
          $statistics = rtrim($statistics, ',');

        ?>
        <?php echo $statistics; ?>

        <?php endif; ?>  
    </div>
    <div class=" sr_wishlist_view_des">
      <?php echo $this->wishlist->body; ?>
    </div>  
  </div>
	<ul class="seaocore_browse_list">
		<?php foreach($this->paginator as $listing): ?>
      <li>
        <div class='seaocore_browse_list_photo'>
          <?php echo $this->htmlLink($listing->getHref(array('profile_link' => 1)), $this->itemPhoto($listing, 'thumb.normal')) ?>
        </div>
        <div class='seaocore_browse_list_info'>
          <div class='seaocore_browse_list_info_title'>

            <?php if($ratingValue == 'rating_both'): ?>
              <?php echo $this->showRatingStar($listing->rating_editor, 'editor', $ratingShow, $listing->listingtype_id); ?>
              <br/>
              <?php echo $this->showRatingStar($listing->rating_users, 'user', $ratingShow, $listing->listingtype_id); ?>
            <?php else: ?>
              <?php echo $this->showRatingStar($listing->$ratingValue, $ratingType, $ratingShow, $listing->listingtype_id); ?>
            <?php endif; ?>

            <?php echo $this->htmlLink($listing->getHref(), $listing->getTitle());?>
          </div>

          <?php if($listing->category_id):?>
            <div class='seaocore_sidebar_list_details'>
              <a href="<?php echo $this->url(array('category_id' => $listing->category_id, 'categoryname' => $listing->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $listing->listingtype_id); ?>"> 
              <?php echo $this->translate($listing->getCategory()->getTitle(true))?>
              </a>
            </div>
          <?php endif; ?>

          <?php if(!empty($listing->price) && Zend_Registry::get('listingtypeArray' . $listing->listingtype_id)->price): ?>
            <div class="seaocore_sidebar_list_details"><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($listing->price);  ?></div>
          <?php endif; ?>

          <div class='seaocore_browse_list_info_blurb'>
            <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($listing->body, 150); ?>
          </div>
        </div>
      </li>
		<?php endforeach; ?>
	</ul>
</div>

<?php if (@$this->closeSmoothbox): ?>
	<script type="text/javascript">
		TB_close();
	</script>
<?php endif; ?>

<script type="text/javascript">
 function printData() {
		document.getElementById('printdiv').style.display = "none";
		window.print();
		setTimeout(function() {
					document.getElementById('printdiv').style.display = "block";
		}, 500);
	}
</script>