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
<?php if (!$this->viewmore): ?>
	<?php if (count($this->layouts_views) > 1) :?>
<div class="p_view_op ui-page-content p_l">
    <span <?php if($this->viewType == 'gridview'): ?> onclick='sm4.switchView.getViewTypeEntity("listview", <?php echo $this->identity; ?>, widgetUrl);' <?php endif;?> class="sm-widget-block"><i class="ui-icon ui-icon-th-list"></i></span>
    <span <?php if($this->viewType == 'listview'): ?> onclick='sm4.switchView.getViewTypeEntity("gridview", <?php echo $this->identity; ?>, widgetUrl);'  <?php endif;?> class="sm-widget-block"><i class="ui-icon ui-icon-th-large"></i></span>
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
<?php if ($this->totalCount > 0): ?>
		<?php if ($this->viewType == 'listview'): ?> 
			<?php if (!$this->viewmore): ?>
        <div id="list_view" class="sm-content-list">
          <ul data-role="listview" data-inset="false" >
			<?php endif; ?>
			<?php foreach ($this->listings as $sitereview): ?>
				<li data-icon="arrow-r">
					<a href="<?php echo $sitereview->getHref(); ?>">
						<?php echo $this->itemPhoto($sitereview, 'thumb.icon'); ?>
						<h3><?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->truncationList); ?></h3>
						<?php if ($ratingValue == 'rating_both'): ?>
							<p><?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
							<br/>
							<?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?></p>
						<?php else: ?>
							<p><?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?></p>
						<?php endif; ?>
						
						<?php if (empty($this->category_id)): ?>
							<p><?php	echo '<b>' . $this->translate($sitereview->getCategory()->getTitle(true)) . '</b>'; ?></p>
						<?php endif; ?>
						
						<?php $contentArray = array(); ?>
						<?php if (!empty($this->statistics)): ?>
							<p>
								<?php
									$statistics = '';
									if (!empty($this->statistics) && in_array('likeCount', $this->statistics)) {
										$contentArray[] = $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count));
									}

									if (!empty($this->statistics) && in_array('viewCount', $this->statistics)) {
										$contentArray[] = $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count));
									}

									if (!empty($this->statistics) && in_array('commentCount', $this->statistics)) {
										$contentArray[] = $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count));
									}

									$listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
									if (!empty($this->statistics) && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2)) {
										$contentArray[] = $this->partial(
														'_showReview.tpl', 'sitereview', array('sitereview' => $sitereview));
									}
									if (!empty($contentArray)) {
										echo join(" - ", $contentArray);
									}
								?>
							</p>
						<?php endif; ?>
						<?php if(!empty($sitereview->price) && $sitereview->price > 0 && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price  && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
							<p><b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price);?></b></p>
						<?php endif; ?>
						<p>
							<?php  $contentsArray = array();
								if(!empty($this->showContent) && in_array('postedDate', $this->showContent)):
									$contentsArray[]= $this->timestamp(strtotime($sitereview->creation_date));
               endif;
								if (!empty($this->postedby)):
									$contentsArray[] = $this->translate(strtoupper($this->listingtypeArray->title_singular) . '_posted_by') . ' <b>' . $sitereview->getOwner()->getTitle() . '</b>';
													if (!empty($contentsArray)) {
									echo join(" - ", $contentsArray);
								}
							?>
							<?php endif; ?>   
						</p> 
						<?php if(!empty($this->showContent) && in_array('endDate', $this->showContent)): ?>
							<?php
							$reviewApi = Engine_Api::_()->sitereview();
							$expirySettings = $reviewApi->expirySettings($sitereview->listingtype_id);
							$approveDate = null;
							if ($expirySettings == 2):
								$approveDate = $reviewApi->adminExpiryDuration($sitereview->listingtype_id);
							endif;
							?>
							<?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
								$b = $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'';
								echo "<p>$b</p>";
								$now = new DateTime(date("Y-m-d H:i:s"));
								$ref = new DateTime($this->locale()->toDate($exp));
								$diff = $now->diff($ref);
								echo '<p>' . $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i). '</p>';
							elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date !='0000-00-00 00:00:00'):
								echo '<p>' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($sitereview->end_date), array('size'=>'medium'))) . '</p>' ;
										$now = new DateTime(date("Y-m-d H:i:s"));
										$ref = new DateTime($sitereview->end_date);
										$diff = $now->diff($ref);
										echo '<p>' . $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i) . '</p>';
							endif;?>
						<?php endif; ?>
						<?php if (!empty($sitereview->location) && $this->enableLocation && Engine_Api::_()->authorization()->isAllowed($sitereview, $this->viewer(), 'view') && !empty($this->showContent) && in_array('location', $this->showContent)):?>  
							<p> 
								<?php echo $this->translate('Location') . ': ' . $sitereview->location; ?>
							</p>
						<?php endif; ?>
					</a>
				</li>
      <?php endforeach; ?>
      <?php if (!$this->viewmore): ?>
          </ul>
        </div>
			<?php endif; ?>
		<?php else: ?>
			<?php $isLarge = ($this->columnWidth > 170); ?>
			<?php if (!$this->viewmore): ?>
				<div id="main_layout">
					<ul class="p_list_grid"> 
			<?php endif; ?> 
			<?php foreach ($this->listings as $sitereview): ?>
				<li style="height:<?php echo $this->columnHeight ?>px;">
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
									<?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getTitle(), $this->truncationGrid) ?>
								</span>
							</div>
						</div>
          </a>
          <div class="p_list_grid_info">
            <?php if (Engine_Api::_()->sitemobile()->isApp()): ?>   
              <span class="p_list_grid_stats">
                <?php if(!empty($this->showContent) && in_array('postedDate', $this->showContent)): ?>
                <span class="fleft"><?php echo $this->timestamp(strtotime($sitereview->creation_date));?></span>
                <?php endif;?>
                <?php if (!empty($this->postedby)): ?>
                  <span class="fright"><?php	echo $this->translate(strtoupper($this->listingtypeArray->title_singular) . '_posted_by') . '  <b>' . '<a href='. $sitereview->getOwner()->getHref() . '>' . $sitereview->getOwner()->getTitle() . '</a></b>';	?></span>
                <?php endif; ?>
              </span>
            <?php endif; ?>
            <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?> 
              <span class="p_list_grid_stats">
                <?php echo '<b>' . $this->translate($sitereview->getCategory()->getTitle(true)) . '</b>' ?>
              </span>
            <?php endif; ?>
            <?php $contentArray = array(); ?>
            <?php if (!empty($this->statistics)): ?>
              <span class="p_list_grid_stats">
                <?php
                  $statistics = '';
                  if (!empty($this->statistics) && in_array('likeCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count));
                  }

                  if (!empty($this->statistics) && in_array('viewCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count));
                  }

                  if (!empty($this->statistics) && in_array('commentCount', $this->statistics)) {
                    $contentArray[] = $this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count));
                  }

                  $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
                  if (!empty($this->statistics) && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && !empty($listingtypeArray->allow_review)) {
                    $contentArray[] = $this->translate(array('%s review', '%s reviews', $sitereview->review_count), $this->locale()->toNumber($sitereview->review_count));
                  }
                  if (!empty($contentArray)) {
                    echo join(" - ", $contentArray);
                  }
                ?>
              </span>
            <?php endif; ?>
            <?php if(!empty($sitereview->price) && $sitereview->price > 0 && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price  && !empty($this->showContent) && in_array('price', $this->showContent)): ?>
              <span class="p_list_grid_stats"><b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></b></span>
            <?php endif; ?>
            <?php if (!Engine_Api::_()->sitemobile()->isApp()): ?>   
              <span class="p_list_grid_stats">
                <?php if(!empty($this->showContent) && in_array('postedDate', $this->showContent)): ?>
                  <?php echo $this->timestamp(strtotime($sitereview->creation_date));?> - 
                <?php endif;?>
                <?php if (!empty($this->postedby)): ?>
                  <?php	echo $this->translate(strtoupper($this->listingtypeArray->title_singular) . '_posted_by') . '  <b>' . $sitereview->getOwner()->getTitle() . '</b>';	?>
                <?php endif; ?>
              </span>
            <?php endif; ?>
            <?php if(!empty($this->showContent) && in_array('endDate', $this->showContent)): ?>
              <?php
              $reviewApi = Engine_Api::_()->sitereview();
              $expirySettings = $reviewApi->expirySettings($sitereview->listingtype_id);
              $approveDate = null;
              if ($expirySettings == 2):
                $approveDate = $reviewApi->adminExpiryDuration($sitereview->listingtype_id);
              endif;
              ?>
              <?php if ($expirySettings == 2): $exp=$sitereview->getExpiryTime();
                $b = $exp ? $this->translate("Expiry On: %s",$this->locale()->toDate($exp, array('size'=>'medium'))) :'';
                echo "<span class='p_list_grid_stats'>$b</span>";
                $now = new DateTime(date("Y-m-d H:i:s"));
                $ref = new DateTime($this->locale()->toDate($exp));
                $diff = $now->diff($ref);
                echo '<span class="p_list_grid_stats">' . $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i). '</span>';
              elseif ($expirySettings == 1 && $sitereview->end_date && $sitereview->end_date !='0000-00-00 00:00:00'):
                echo '<span class="p_list_grid_stats">' . $this->translate("Ending On: %s",$this->locale()->toDate(strtotime($sitereview->end_date), array('size'=>'medium'))) . '</span>' ;
                    $now = new DateTime(date("Y-m-d H:i:s"));
                    $ref = new DateTime($sitereview->end_date);
                    $diff = $now->diff($ref);
                    echo '<span class="p_list_grid_stats">' . $this->translate('Time Left: %d days, %d hours, %d minutes', $diff->days, $diff->h, $diff->i) . '</span>';
              endif;?>
            <?php endif; ?>
            <?php 
              if (!empty($sitereview->location) && $this->enableLocation && Engine_Api::_()->authorization()->isAllowed($sitereview, $this->viewer(), 'view') && !empty($this->showContent) && in_array('location', $this->showContent)):?>
              <span class="p_list_grid_stats">
                <?php echo $this->translate('Location') . ': ' . $sitereview->location; ?>
              </span>
            <?php endif; ?>
            <?php $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); ?>
            <?php if ($ratingValue == 'rating_both'): ?>
              <span class="p_list_grid_stats"><?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?><br />
              <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?></span>
            <?php else: ?>
              <span class="p_list_grid_stats"><?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?></span>
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
          <?php echo $this->translate('No items matching this criteria.'); ?>
        </span>
      </div>
<?php endif; ?>
<?php if ($this->params['page'] < 2 && $this->totalCount > ($this->params['page'] * $this->params['limit'])) : ?>
	<div class="feed_viewmore clr" >
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
     var widgetUrl = sm4.core.baseUrl + 'widget/index/mod/sitereview/name/listings-sitereview';
    sm4.core.runonce.add(function() {      
   
    var currentpageid = $.mobile.activePage.attr('id') + '-' + <?php echo $this->identity;?>;  
    
       sm4.switchView.pageInfo[currentpageid] = $.extend({},sm4.switchView.pageInfo[currentpageid], {'viewType' : '<?php echo $this->viewType; ?>', 'params': <?php echo json_encode($this->params) ?>, 'totalCount' : <?php echo $this->totalCount; ?>});    
       
    });
  </script>
<?php if (empty($this->viewmore)) : ?>
	</div>
	<style type="text/css">
		.ui-collapsible-content{padding-bottom:0;}
	</style>
<?php endif; ?>
<?php else:?>
	<div id="layout_sitereview_listings_sitereview_<?php echo $this->identity; ?>">
	</div>    
      <script type="text/javascript">
       var requestParams = $.extend(<?php echo json_encode($this->params); ?>, {'content_id': '<?php echo $this->identity; ?>','renderDefault': '0'});
        var params = {
            'detactLocation': <?php echo $this->detactLocation; ?>,
            'responseContainer': 'layout_sitereview_listings_sitereview_<?php echo $this->identity; ?>',
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
