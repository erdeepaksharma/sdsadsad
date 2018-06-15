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
<?php if ($this->is_ajax_load): ?>
<?php $this->listingType = $this->view_selected;?>
<script>
  var listingViewType = '';
 </script> 
<?php if (empty($this->viewmore)) : ?>
  <?php if (count($this->layouts_views) > 1) :?>

<div class="p_view_op ui-page-content p_l">
    <span <?php if($this->listingType == 'gridview'): ?> onclick='sm4.switchView.getViewTypeEntity("listview", <?php echo $this->identity; ?>, widgetUrl);' <?php endif;?> class="sm-widget-block"><i class="ui-icon ui-icon-th-list"></i></span>
    <span <?php if($this->listingType == 'listview'): ?> onclick='sm4.switchView.getViewTypeEntity("gridview", <?php echo $this->identity; ?>, widgetUrl);'  <?php endif;?> class="sm-widget-block"><i class="ui-icon ui-icon-th-large"></i></span>
  </div>
		
	<?php endif;?>
  <div id="main_layout" class="ui-page-content">
<?php endif; ?>

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

	<?php
	$reviewApi = Engine_Api::_()->sitereview();
	$expirySettings = $reviewApi->expirySettings($this->listingtype_id);
	$approveDate = null;
	if ($expirySettings == 2):
		$approveDate = $reviewApi->adminExpiryDuration($this->listingtype_id);
	endif;
	?>
   <?php if ($this->paginator->count() > 0): ?>
		<?php if (($this->isajax && $this->listingType == 'listview') || (!$this->isajax && $this->defaultOrder == 1)): ?>
			<?php if (!$this->viewmore): ?>
        <div id="main_layout" class="sm-content-list">
					<ul data-role="listview" data-inset="false" id="browse_listing">
      <?php endif; ?>    
			<?php foreach ($this->paginator as $sitereview): ?>
			<?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
						$listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); ?>
			<li <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>data-icon="angle-right"<?php else : ?>data-icon="arrow-r"<?php endif;?>>
				<a href="<?php echo $sitereview->getHref(); ?>">
					<?php echo $this->itemPhoto($sitereview, 'thumb.icon'); ?>
					<h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->title_truncation); ?></h3>
					<?php if ($ratingValue == 'rating_both'): ?>
						<p><?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?></p>
						<p><?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?> </p>
					<?php else: ?>
						<p><?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?> </p>
					<?php endif; ?>
					<p><?php echo '<b>' . $this->translate($sitereview->getCategory()->getTitle(true)) . '</b>' ?></p>
					<?php if (!empty($this->statistics)): ?>
						<?php $contentArray = array(); ?>
						<?php

						if (in_array('likeCount', $this->statistics)) {
							$contentArray[] = $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count));
						}

						if (in_array('viewCount', $this->statistics)) {
							$contentArray[] = $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count));
						}

						if (in_array('commentCount', $this->statistics)) {
							$contentArray [] = $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count));
						}

						                     if(in_array('reviewCount', $this->statistics) && (!empty($this->listingtypeArray->allow_review) || (isset($sitereview->rating_editor) && $sitereview->rating_editor))) {
							$contentArray[] = $this->partial(
											'_showReview.tpl', 'sitereview', array('sitereview' => $sitereview));
						}
						?>
						<?php if (!empty($contentArray)):?>
							<p><?php echo join(" - ", $contentArray); ?></p>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ($sitereview->price > 0 && (empty($this->listingtype_id) || ($this->listingtype_id && $this->listingtypeArray->price)) && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
						<p><?php echo '<b>' . Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price). '</b>'; ?></p>
					<?php endif; ?>
					<p>
						<?php $contentArray = array(); ?>
						<?php if(!empty($this->showContent) && in_array('postedDate', $this->showContent)): ?>
							<?php $contentArray[]= $this->timestamp(strtotime($sitereview->creation_date));?>
            <?php endif;?>
						<?php
						if (!empty($this->postedby)):
							$contentArray[] = $this->translate(strtoupper($this->listingtypeArray->title_singular) . '_posted_by') . '  <b>' . $sitereview->getOwner()->getTitle() . '</b>';
							?>
						<?php endif; ?>
						<?php
							if (!empty($contentArray)) {
								echo join(" - ", $contentArray);
							}
						?> 
					</p>
				<?php if ($this->showExpiry): ?>
					<?php
					if ($expirySettings == 2): $exp = $sitereview->getExpiryTime();
						echo '<p>' . $exp ? $this->translate("Expiry On: %s", $this->locale()->toDate($exp, array('size' => 'medium'))) : '' . '</p>';
						$now = new DateTime(date("Y-m-d H:i:s"));
						$ref = new DateTime($this->locale()->toDate($exp));
						$diff = $now->diff($ref);
						echo '<p>';
						echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
						echo '</p>';
					elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date != '0000-00-00 00:00:00'):
						echo '<p>' . $this->translate("Ending On: %s", $this->locale()->toDate(strtotime($sitereview->end_date), array('size' => 'medium'))) . '</p>';
						$now = new DateTime(date("Y-m-d H:i:s"));
						$ref = new DateTime($sitereview->end_date);
						$diff = $now->diff($ref);
						echo '<p>';
						echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
						echo '</p>';
					endif;
					?>
				<?php endif; ?>
				<?php if(!empty($sitereview->location) && $this->listingtypeArray->location && !empty($this->showContent) && in_array('location', $this->showContent)): ?>                  
					<p>
						<?php echo $this->translate('Location') . ': ' . $sitereview->location; ?>
					</p>
				<?php endif; ?> 
				<?php if ($this->bottomLine == 1): ?>
						<p class="ui-li-desc"><?php  echo substr($sitereview->getBottomLine(), 0, 100);
						if (strlen($sitereview->getBottomLine()) > 100) echo '...';?></p>
					<?php elseif (!$this->bottomLine): ?>
						<p class="ui-li-desc"><?php echo substr($sitereview->body, 0, 100) ;                       
						if (strlen($sitereview->body) > 100)  echo '...';
						?></p>
					<?php endif; ?>       
				</a>
			</li>
		<?php endforeach; ?>
<?php if (!$this->viewmore): ?>
	</ul>
</div>
<?php endif; ?>    

<?php elseif ((!$this->isajax && $this->defaultOrder == 2) || ($this->isajax && $this->listingType == 'gridview')): ?>
	<?php if (!$this->viewmore): ?>  
		<div id="main_layout">
			<ul class="p_list_grid" id="browse_listing"> 
	<?php endif; ?>    
	<?php $isLarge = ($this->columnWidth > 170); ?>
	<?php foreach ($this->paginator as $sitereview): ?>          
		<li style="height:<?php echo $this->columnHeight ?>px;">
			<a href="<?php echo $sitereview->getHref(); ?>" class="ui-link-inherit">
				<div class="p_list_grid_top_sec">
					<div class="p_list_grid_img">
						<?php
						$url = $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/nophoto_listing_thumb_profile.png';
						$temp_url = $sitereview->getPhotoUrl('thumb.profile');
						if (!empty($temp_url)): $url = $sitereview->getPhotoUrl('thumb.profile');
						endif;
						?>
						<span style="background-image: url(<?php echo $url; ?>);"> </span>
					</div>                 
					<div class="p_list_grid_title">
						<span><?php echo $this->string()->chunk($this->string()->truncate($sitereview->getTitle(), 45), 10); ?></span>               
					</div>
				</div>
      </a>
      <div class="p_list_grid_info">
        <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>              
          <span class="p_list_grid_stats">
            <?php $contentArray = array(); ?>
            <?php if(!empty($this->showContent) && in_array('postedDate', $this->showContent)): ?>
                <?php $contentArray[]= "<span class='fleft'>" . $this->timestamp(strtotime($sitereview->creation_date)) . '</span>';?>
            <?php endif; ?>
            <?php
            if (!empty($this->postedby)):
              $contentArray[] = "<span class='fright'>" .$this->translate(strtoupper($this->listingtypeArray->title_singular) . '_posted_by') . '  <b>' . '<a href='. $sitereview->getOwner()->getHref() . '>' . $sitereview->getOwner()->getTitle() . '</a></b></span>';
              ?>
            <?php endif; ?>
            <?php
            if (!empty($contentArray)) {
              echo join("", $contentArray);
            }
            ?> 
          </span>
        <?php endif;?>
        <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?> 
          <span class="p_list_grid_stats">
            <?php echo '<b>' . $this->translate($sitereview->getCategory()->getTitle(true)) . '</b>' ?>
          </span>
         <?php endif;?>
        <?php if (!empty($this->statistics)): ?>
          <?php $contentArray = array(); ?>
          <?php

          if (in_array('likeCount', $this->statistics)) {
            $contentArray[] = $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count));
          }

          if (in_array('viewCount', $this->statistics)) {
            $contentArray[] = $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count));
          }

          if (in_array('commentCount', $this->statistics)) {
            $contentArray [] = $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count));
          }

                              if(in_array('reviewCount', $this->statistics) && (!empty($this->listingtypeArray->allow_review) || isset ($sitereview->rating_editor) && $sitereview->rating_editor && $sitereview->review_count==1)) {
            $contentArray[] = $this->partial(
                    '_showReview.tpl', 'sitereview', array('sitereview' => $sitereview));
          }
          ?>
          <?php if (!empty($contentArray)):?>
            <span class="p_list_grid_stats"><?php echo join(" - ", $contentArray); ?></span>
          <?php endif; ?>
        <?php endif; ?> 
        <?php if ($sitereview->price > 0 && (empty($this->listingtype_id) || ($this->listingtype_id && $this->listingtypeArray->price)) && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
          <span class="p_list_grid_stats">
            <?php echo '<b>' . Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price) . '</b>'; ?>
          </span>
        <?php endif; ?>
        <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>              
          <span class="p_list_grid_stats">
            <?php $contentArray = array(); ?>
            <?php if(!empty($this->showContent) && in_array('postedDate', $this->showContent)): ?>
              <?php $contentArray[]= $this->timestamp(strtotime($sitereview->creation_date));?>
            <?php endif; ?>
            <?php
            if (!empty($this->postedby)):
              $contentArray[] = $this->translate(strtoupper($this->listingtypeArray->title_singular) . '_posted_by') . '  <b>' . $sitereview->getOwner()->getTitle() . '</b>';
              ?>
            <?php endif; ?>
            <?php
            if (!empty($contentArray)) {
              echo join(" - ", $contentArray);
            }
            ?> 
          </span>
        <?php endif;?>
        <span class="p_list_grid_stats">
          <?php if ($this->showExpiry): ?>    
            <?php
            if ($expirySettings == 2): $exp = $sitereview->getExpiryTime();
              echo '' . $exp ? $this->translate("Expiry On: %s", $this->locale()->toDate($exp, array('size' => 'medium'))) : '' . '';
              $now = new DateTime(date("Y-m-d H:i:s"));
              $ref = new DateTime($this->locale()->toDate($exp));
              $diff = $now->diff($ref);
              echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);

              elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date != '0000-00-00 00:00:00'):
              echo '' . $this->translate("Ending On: %s", $this->locale()->toDate(strtotime($sitereview->end_date), array('size' => 'medium'))) . '';
              $now = new DateTime(date("Y-m-d H:i:s"));
              $ref = new DateTime($sitereview->end_date);
              $diff = $now->diff($ref);
              echo $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i);
            endif;
            ?>                      
          <?php endif; ?>
        </span>      

        <?php if(!empty($sitereview->location) && $this->listingtypeArray->location && !empty($this->showContent) && in_array('location', $this->showContent)): ?>                  
          <span class="p_list_grid_stats">
            <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>
              <i class="ui-icon-map-marker"></i>
            <?php endif ?>
            <?php echo $this->translate('Location') . ': ' . $sitereview->location; ?>
          </span>
        <?php endif; ?> 

        <?php if ($ratingValue == 'rating_both'): ?>
          <span class="p_list_grid_stats"><?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?></span>
          <span class="p_list_grid_stats"><?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?></span>
        <?php else: ?>
          <span class="p_list_grid_stats"><?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?></span>
        <?php endif; ?> 

        <?php if ($this->bottomLineGrid == 1): ?>
          <span class="p_list_grid_stats"><?php  echo substr($sitereview->getBottomLine(), 0, 100); ?>
          <?php if (strlen($sitereview->getBottomLine()) > 100) echo '...';?></span>
        <?php elseif (!$this->bottomLineGrid): ?>
          <span class="p_list_grid_stats"><?php echo substr($sitereview->body, 0, 100) ; ?>                   
          <?php if (strlen($sitereview->body) > 100) echo '...';?></span>
        <?php endif; ?>
        </div> 
		</li>
	<?php endforeach; ?>
<?php if (!$this->viewmore): ?>
		</ul>
	</div>
<?php endif; ?>  
<?php endif; ?>
	<?php else: ?>
		<div class="tip mtop10"> 
			<span> 
				<?php echo $this->translate('No ' . strtolower($this->listingtypeArray->title_plural) . ' have been posted yet.'); ?>
			</span>
		</div>
	<?php endif; ?>

	<?php if (!Engine_Api::_()->sitemobile()->isApp() && $this->current_page < 2 && $this->totalCount > ($this->current_page * $this->limit)) : ?>
		<div class="feed_viewmore clr">
			<?php
			echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
					'id' => 'feed_viewmore_link',
					'class' => 'ui-btn-default icon_viewmore',
					'onclick' => 'sm4.switchView.viewMoreEntity(' . $this->identity . ',widgetUrl)'
			))
			?>
		</div>
		<div class="seaocore_loading feeds_loading" style="display: none;">
			<i class="icon_loading"></i>
		</div>
	<?php endif; ?>
<script type="text/javascript">
   
    var widgetUrl = sm4.core.baseUrl + 'widget/index/mod/sitereview/name/browse-listings-sitereview';
    sm4.core.runonce.add(function() {      
   
    var currentpageid = $.mobile.activePage.attr('id') + '-' + <?php echo $this->identity;?>;  
    
       sm4.switchView.pageInfo[currentpageid] = $.extend({},sm4.switchView.pageInfo[currentpageid], {'params': $.extend(<?php echo json_encode($this->allParams) ?>, {'listingType':listingViewType}), 'totalCount' : <?php echo $this->totalCount; ?>, 'responseContainer': '.layout_sitereview_browse_listings_sitereview','viewType': '<?php echo $this->listingType;?>'});
       
       var activepage_id = sm4.activity.activityUpdateHandler.getIndexId();
              sm4.core.Module.core.activeParams[activepage_id] = {'currentPage' : '<?php echo sprintf('%d', $this->page) ?>', 'totalPages' : '<?php echo sprintf('%d', $this->totalPages) ?>', 'formValues' : $.extend(<?php echo json_encode($this->allParams);?>, {'is_ajax_load': 1, 'isajax': 1, 'view_selected': '<?php echo $this->listingType; ?>'}), 'contentUrl' : sm4.core.baseUrl + 'widget/index/mod/sitereview/name/browse-listings-sitereview', 'activeRequest' : false, 'container' : 'browse_listing' }; 
         
       
    });

  </script>
  
  <?php if (empty($this->viewmore)) : ?>
	</div>
	
<?php endif; ?>
<?php else: ?>
 <div id="layout_sitereview_browse_listings_<?php echo $this->identity;?>">
 </div>
<script type="text/javascript">
    var requestParams = $.extend(<?php echo json_encode($this->paramsLocation);?>, {'content_id': '<?php echo $this->identity;?>'})
    var params = {
      'detactLocation': <?php echo $this->detactLocation; ?>,
      'responseContainer' : 'layout_sitereview_browse_listings_<?php echo $this->identity;?>',
      'locationmiles': <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.locationdefaultmiles', 1000); ?>,
       requestParams: requestParams      
    };
    sm4.core.runonce.add(function() {
          setTimeout((function() {
            $.mobile.loading().loader("show");
          }), 100);

          sm4.core.locationBased.startReq(params);
    });
</script>
<?php endif; ?>
