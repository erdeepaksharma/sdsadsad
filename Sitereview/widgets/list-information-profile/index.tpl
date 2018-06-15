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

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/like.js'); ?>

<script type="text/javascript">
	var seaocore_content_type = 'sitereview_listing';
	var seaocore_like_url = en4.core.baseUrl + 'seaocore/like/like';
  
//  window.addEvent('load', function() {
//    var request = new Request.JSON({
//      url : en4.core.baseUrl + 'sitereview/index/get-default-listing',
//      data : {
//        format: 'json',
//        isAjax: 1,
//        type: 'layout_sitereview'
//      },
//      'onSuccess' : function(responseJSON) {
//        if( !responseJSON.getListingType ) {
//          document.getElement("." + responseJSON.getClassName + "_list_information_profile").empty();
//          document.getElement(".layout_core_container_tabs").empty();
//        }
//      }
//    });
//    request.send();
//  });
</script>

<?php

$photo_type = $this->listingType->photo_type;
$reviewApi = Engine_Api::_()->sitereview();
$expirySettings = $reviewApi->expirySettings($this->listingtype_id);
$approveDate = null;
if ($expirySettings == 2):
  $approveDate = $reviewApi->adminExpiryDuration($this->listingtype_id);
endif;

$compare = $this->compareButton($this->sitereview);

if(Engine_Api::_()->sitereview()->hasPackageEnable()) {
	if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($this->sitereview->package_id, "wishlist"))
	$canAddWishlist = 1;
	else
	$canAddWishlist = 0;
}
else {
	$canAddWishlist = 1;
}
?>

<?php if ($approveDate && $this->sitereview->approved_date && $approveDate > $this->sitereview->approved_date): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('This '.strtolower($this->listingType->title_singular).' has beed expired.'); ?>
    </span>
  </div>
<?php endif; ?>

<?php if (!empty($this->sitereview->closed)) : ?>
  <div class="tip"> 
    <span> <?php echo $this->translate('This '.strtolower($this->listingType->title_singular).' has been closed by the owner.'); ?> </span>
  </div>
<?php endif; ?>

<div class="clr sr_profile_info">
  <?php if(in_array('photo', $this->showContent)): ?>
    <div class="sr_profile_photo_wrapper b_medium <?php if ($photo_type == 'listing'): ?>photo_zoom<?php endif; ?>">

      <?php if ($this->listingType->featured && !empty($this->sitereview->featured)): ?> 
        <i class="sr_list_featured_label" title="<?php echo $this->translate('FEATURED'); ?>"></i>
      <?php endif; ?>
      <?php if (!empty($this->sitereview->newlabel)):?>
       <i class="sr_list_new_label" title="<?php echo $this->translate('NEW'); ?>"></i>
      <?php endif; ?>

      <div class='sr_profile_photo <?php if ($this->can_edit && $photo_type == 'listing'): ?>sr_photo_edit_wrapper<?php endif; ?>'>
        <?php if (!empty($this->can_edit) && ($photo_type == 'listing')) : ?>
          <a class='sr_photo_edit' href="<?php echo $this->url(array('action' => 'change-photo', 'listing_id' => $this->sitereview->listing_id), "sitereview_dashboard_listtype_$this->listingtype_id", true) ?>">
            <i class="sr_icon"></i>
            <?php echo $this->translate('Change Picture'); ?>        
          </a>
        <?php endif; ?>
        <?php if($this->sitereview->photo_id ):?>
            <?php $photo= $this->sitereview->getPhoto($this->sitereview->photo_id); ?>
            <?php if($photo_type == 'listing'):?>
              <a href="<?php echo $photo->getHref(); ?>" <?php if (SEA_LIST_LIGHTBOX) : ?> onclick='openSeaocoreLightBox("<?php echo $photo->getHref(); ?>");return false;' <?php endif; ?>>
              <?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '', array('align' => 'center')); ?></a>
            <?php else:?>
              <a href="<?php echo $this->sitereview->getHref(array('profile_link' => 1)); ?>">
              <?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '', array('align' => 'center')); ?></a>
            <?php endif;?>
         <?php else: ?>
           <?php if($this->listingType->photo_id == 0):?>
             <a href="<?php echo $this->sitereview->getHref(array('profile_link' => 1)); ?>"></a>
           <?php endif;?>
           <?php echo $this->itemPhoto($this->sitereview, 'thumb.profile', '', array('align' => 'center')); ?>
        <?php endif;?>
      </div>

      <?php if ($this->listingType->sponsored && !empty($this->sitereview->sponsored)): ?>
        <center class="mtop5 clr">
          <span class="sr_sponsorfeatured_label" style='background: <?php echo $this->listingType->sponsored_color; ?>;' title="<?php echo $this->translate('SPONSORED'); ?>">
            <?php echo $this->translate('SPONSORED'); ?>
          </span>
        </center>
      <?php endif; ?>

      <?php if (in_array('photo', $this->showContent) && in_array('photosCarousel', $this->showContent) && $photo_type == 'listing'): 
        $widgetContent=$this->content()->renderWidget("sitereview.photos-carousel", array('includeInWidget'=>$this->identity,'minMum'=>2,'itemCount'=>3)) ?>
        <?php if(strlen($widgetContent)>15):?>
          <div class="b_medium sr_photoscarousel o_hidden">
            <?php echo $widgetContent ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($this->sitereview->photo_id && ($photo_type == 'listing')):?>
        <p class="mtop10">
          <a href="<?php echo $photo->getHref(); ?>" <?php if (SEA_LIST_LIGHTBOX) : ?> onclick='openSeaocoreLightBox("<?php echo $photo->getHref(); ?>");return false;' <?php endif; ?>>
          <?php if(in_array('photo', $this->showContent) && in_array('photosCarousel', $this->showContent)&&strlen($widgetContent)>15):?>
            <?php echo $this->translate('Click on above images to view full picture'); ?>
          <?php else: ?>
            <?php echo $this->translate('Click on above image to view full picture'); ?>
          <?php endif; ?>
          </a>
        </p>
     <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="sr_profile_content">
		<div class="sr_profile_title">
      <?php if (in_array('title', $this->showContent)): ?>
        <h2>
          <?php echo $this->sitereview->getTitle(); ?>
        </h2>
      <?php endif; ?>
		  
			<?php //LIKE BUTTON WORK
			$viewer_id = $this->viewer->getIdentity(); ?>
			<?php if (!empty($this->like_button) && $this->like_button == 1 && !empty($viewer_id)) : ?>
				<?php
					if(!empty($this->viewer))
					{
						$resourceId = Engine_Api::_()->core()->getSubject()->getIdentity();
						$check_availability = Engine_Api::_()->getApi('like', 'seaocore')->hasLike('sitereview_listing', $resourceId);
						if( !empty($check_availability) ) {
							$label = 'Unlike this';
							$unlike_show = "display:inline-block;";
							$like_show = "display:none;";
							$like_id = $check_availability;
						}
						else {
							$label = 'Like this';
							$unlike_show = "display:none;";
							$like_show = "display:inline-block;";
							$like_id = 0;
						}
					}
				?>
				<?php if(empty($this->sitereview_like)){ exit(); } ?>
				<div class="seaocore_like_button" id="sitereview_listing_unlikes_<?php echo $resourceId;?>" style ='<?php echo $unlike_show;?>' >
					<a href="javascript:void(0);" onclick = "seaocore_content_type_likes('<?php echo $resourceId; ?>', 'sitereview_listing');">
						<i class="seaocore_like_thumbdown_icon"></i>
						<span><?php echo $this->translate('Unlike') ?></span>
					</a>
				</div>
				<div class="seaocore_like_button" id="sitereview_listing_most_likes_<?php echo $resourceId;?>" style ='<?php echo $like_show;?>'>
					<a href="javascript:void(0);" onclick = "seaocore_content_type_likes('<?php echo $resourceId; ?>', 'sitereview_listing');">
						<i class="seaocore_like_thumbup_icon"></i>
						<span><?php echo $this->translate('Like') ?></span>
					</a>
				</div>
				<input type ="hidden" id = "sitereview_listing_like_<?php echo $resourceId;?>" value = '<?php echo $like_id; ?>' />
      <?php elseif ($this->like_button == 2 && $this->success_showFBLikeButton) :  ?>
				<?php echo $this->content()->renderWidget("Facebookse.facebookse-commonlike", array('module_current' => 'sitereview')); ?>
			<?php endif; ?>
			<?php //LIKE BUTTON WORK ?>
		  
		</div>
    
    <div class="sr_profile_information_stats seaocore_txt_light clr">
      <?php if (in_array('postedBy', $this->showContent)): ?>
        <span><?php echo $this->translate(strtoupper($this->listingType->title_singular). '_POSTED_BY'); ?>
          <?php echo $this->htmlLink($this->sitereview->getOwner()->getHref(), $this->sitereview->getOwner()->getTitle()) ?>
        </span>&nbsp;&nbsp;&nbsp;
      <?php endif; ?>
      <?php if (in_array('postedDate', $this->showContent)): ?>
        <span>
          <?php echo $this->timestamp(strtotime($this->sitereview->creation_date)) ?>
        </span>&nbsp;&nbsp;&nbsp;
      <?php endif; ?>
      <?php if (in_array('viewCount', $this->showContent)): ?>
        <span><?php echo $this->translate(array('%s view', '%s views', $this->sitereview->view_count), $this->locale()->toNumber($this->sitereview->view_count)) ?>
        </span>&nbsp;&nbsp;&nbsp;
      <?php endif; ?>
      <?php if (in_array('likeCount', $this->showContent)): ?>
        <span><?php echo $this->translate(array('%s like', '%s likes', $this->sitereview->like_count), $this->locale()->toNumber($this->sitereview->like_count)) ?>
        </span>&nbsp;&nbsp;&nbsp;
      <?php endif; ?>
      <?php if (in_array('commentCount', $this->showContent)): ?>
        <span><?php echo $this->translate(array('%s comment', '%s comments', $this->sitereview->comment_count), $this->locale()->toNumber($this->sitereview->comment_count)) ?>
        </span>
      <?php endif; ?>
    </div>
    
    <?php if (in_array('tags', $this->showContent)): ?>
      <div class="sr_profile_information_stats seaocore_txt_light clr">
        <?php if (count($this->sitereviewTags) > 0): $tagCount = 0; ?>
          <?php echo $this->translate($this->listing_singular_upper.'_TAGS'); ?> - 
          <?php foreach ($this->sitereviewTags as $tag): ?>
            <?php if (!empty($tag->getTag()->text)): ?>
              <?php $tag->getTag()->text = $this->string()->escapeJavascript($tag->getTag()->text) ?>
              <?php if (empty($tagCount)): ?>
                <a href='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_" . $this->sitereview->listingtype_id); ?>?tag=<?php echo urlencode($tag->getTag()->text) ?>&tag_id=<?php echo $tag->getTag()->tag_id ?>'>#<?php echo $tag->getTag()->text ?></a>
                <?php $tagCount++;
              else: ?>
                <a href='<?php echo $this->url(array('action' => 'index'), "sitereview_general_listtype_" . $this->sitereview->listingtype_id); ?>?tag=<?php echo urlencode($tag->getTag()->text) ?>&tag_id=<?php echo $tag->getTag()->tag_id ?>'>#<?php echo $tag->getTag()->text ?></a>
              <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
   
    <?php if (in_array('endDate', $this->showContent)): ?>
      <?php if ($expirySettings == 2): $exp=$this->sitereview->getExpiryTime();
        echo '<div class="sr_profile_information_stats seaocore_txt_light">' . $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'' . '</div>';
        $now = new DateTime(date("Y-m-d H:i:s"));
        $ref = new DateTime($this->locale()->toDate($exp));
        $diff = $now->diff($ref);
        echo '<div class="sr_profile_information_stats seaocore_txt_light">';
        echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
        echo '</div>';

      elseif ($expirySettings == 1 && $this->sitereview->end_date && $this->sitereview->end_date !='0000-00-00 00:00:00'):
        echo '<div class="sr_profile_information_stats seaocore_txt_light">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($this->sitereview->end_date), array('size'=>'medium'))) . '</div>';
            $now = new DateTime(date("Y-m-d H:i:s"));
            $ref = new DateTime($this->sitereview->end_date);
            $diff = $now->diff($ref);
            echo '<div class="sr_profile_information_stats seaocore_txt_light">';
            echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
            echo '</div>';

      endif;?>
    <?php endif; ?>
    
    <?php if (in_array('location', $this->showContent) && !empty($this->sitereview->location) && $this->listingType->location): ?>
      <div class="sr_profile_information_stats seaocore_txt_light">
        <?php echo $this->translate($this->sitereview->location); ?>&nbsp;-
        <b>
          <?php echo $this->htmlLink(array('route' => 'seaocore_viewmap', 'id' => $this->sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')); ?></b>
      </div>
    <?php endif; ?>
    <?php if (($this->phone) || ($this->email) || ($this->website) || ($this->price > 0) || (!empty($compare) && in_array('compare', $this->showContent)) || !empty($this->create_review)) : ?>
			<div class="sr_profile_info_sep b_medium"></div>
    <?php endif;?>
    <?php if (($this->phone) || ($this->email) || ($this->website)) : ?>
	  	<div class="clr sr_profile_contact_info o_hidden mbot10">
		    <?php if (!empty($this->phone)) : ?>
		      <div class="sr_profile_contect_op">
		        <i style="background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/mobile.png);" class="sr_icon" title="<?php echo $this->translate('Phone') ?>"></i>
		        <span id="showPhoneNumber" class="o_hidden"><?php echo $this->phone ?></span>
		      </div>
		    <?php endif; ?>
		
		    <?php if (!empty($this->email)) : ?>
		      <div class="sr_profile_contect_op">
		      	<i style="background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/send.png);" class="sr_icon" title="E-mail"></i>
		        <span id="showEmailAddress" class="o_hidden"><a href='mailto:<?php echo $this->email ?>' title="<?php echo $this->email ?>"><?php echo $this->translate('Email Me') ?></a></span>      
		      </div>
		    <?php endif; ?>
				
		    <?php if (!empty($this->website)) : ?>
		      <div class="sr_profile_contect_op">
		      	<i style="background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/icons/web.png);" class="sr_icon" title="<?php echo $this->translate('Website') ?>"></i>
		        <span id="showWebsite" class="o_hidden">   
		          <?php if (strstr($this->website, 'http://') || strstr($this->website, 'https://')): ?>
		            <a href='<?php echo $this->website ?>' target="_blank" title='<?php echo $this->website ?>' ><?php echo $this->translate(''); ?> <?php echo $this->translate('Visit Website') ?></a>
		          <?php else: ?>
		            <a href='http://<?php echo $this->website ?>' target="_blank" title='<?php echo $this->website ?>' ><?php echo $this->translate(''); ?> <?php echo $this->translate('Visit Website') ?></a>
		          <?php endif; ?>
		        </span>
		      </div>    
		    <?php endif; ?>

		  </div>
		<?php endif; ?>
      
    <?php if((Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id)->wishlist && in_array('wishlist', $this->showContent)) || ($this->price > 0) || (!empty($compare) && in_array('compare', $this->showContent)) || !empty($this->create_review)):?>
			<div class="clr sr_profile_information_option mtop5">
				<?php if ($this->price > 0): ?>
					<span class="sr_profile_price">
						<?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->price); ?>
					</span>   
				<?php endif; ?>
				<?php if(!empty($compare) && in_array('compare', $this->showContent)):?>
					<span> 
						<?php echo $compare ?>
					</span>
				<?php endif; ?>
				<?php if (Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id)->wishlist && in_array('wishlist', $this->showContent) && $canAddWishlist): ?>
					<span>
						<?php echo $this->addToWishlist($this->sitereview, array('classIcon' => 'sr_wishlist_href_link', 'classLink' => ''));?>
					</span>
				<?php endif; ?>
				<?php if(!empty($this->create_review)):?>
					<div>
						<?php echo $this->content()->renderWidget("sitereview.review-button", array('listing_guid' => $this->sitereview->getGuid(), 'listing_profile_page' => 1, 'identity' => $this->identity)) ?>
					</div>
				<?php endif; ?>
			</div>
    <?php endif; ?>

    <?php if(in_array('description', $this->showContent) && strip_tags($this->sitereview->body)):?>
		  <div class="sr_profile_info_sep b_medium"></div>
		  <div class="sr_profile_information_des">
		    <?php if($this->truncationDescription): ?>
					<?php echo $this->viewMore(strip_tags($this->sitereview->body), $this->truncationDescription, 5000) ?>
        <?php else: ?>  
          <?php echo $this->sitereview->body ?>
        <?php endif; ?>
		  </div>
	  <?php endif; ?>
  </div>
	<?php if ($this->gutterNavigation && $this->actionLinks): ?>
    <?php
      echo $this->navigation()
            ->menu()
            ->setContainer($this->gutterNavigation)
            ->setUlClass('sr_information_gutter_options b_medium clr')
            ->render();
    ?>
	<?php endif; ?>
</div>
<div class="clr widthfull"></div>
