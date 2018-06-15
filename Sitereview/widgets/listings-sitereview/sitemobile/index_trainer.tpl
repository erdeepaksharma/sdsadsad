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
			<?php $isLarge = ($this->columnWidth > 170); ?>
			<?php if (!$this->viewmore): ?>
				<div id="main_layout">
					<ul class="p_list_grid sitevideo_get_topics_posts_ul"> 
			<?php endif; ?> 

                        <?php foreach( $this->paginator as $item ): ?>
                          <li style="height:305px;">  
                            <div class="p_list_grid_top_sec">
                              <a href="<?php echo $item->getHref(); ?>">
                            <?php
                              if( $item->photo_id ) {
                                echo $this->itemPhoto($item, 'thumb.normal');
                              } else {
                                echo '<img alt="" src="' . $this->escape($this->layout()->staticBaseUrl) . 'application/modules/Video/externals/images/video.png">';
                              }
                            ?>
                                <i class="ui-icon ui-icon-play"></i>
                              </a> 
                            <?php if( $item->duration ): ?>
                              <span class="video-duration">
                                <?php
                                  if( $item->duration >= 3600 ) {
                                    $duration = gmdate("H:i:s", $item->duration);
                                  } else {
                                    $duration = gmdate("i:s", $item->duration);
                                  }
                                  echo $duration;
                                ?>
                              </span>
                            <?php endif ?>
                              </div>
                              <div class="p_list_grid_info">
                                <div class="videos-listing-left">
                                  <p class="video-title"><?php echo $item->getTitle() ?></p>
                                  <p class="video-stats f_small t_light">
                                    <?php echo $this->translate('By'); ?>
                                    <?php echo $item->getOwner()->getTitle(); ?>
                                  </p>
                                </div>
                                <div class="videos-listing-right">
                                  <p> 
                                    <?php if( $item->rating > 0 ): ?>
                                    <?php for( $x=1; $x<=$item->rating; $x++ ): ?>
                                      <span class="rating_star_generic rating_star"></span>
                                    <?php endfor; ?>
                                    <?php if( (round($item->rating) - $item->rating) > 0): ?>
                                      <span class="rating_star_generic rating_star_half"></span>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                  </p>
                                  <p class="listing-counts">
                                    <span class="f_small"><?php echo $item->likes()->getLikeCount(); ?></span>
                                    <i class="ui-icon-thumbs-up-alt"></i>
                                    <span class="f_small"><?php echo $this->locale()->toNumber($item->comment_count) ?></span>
                                    <i class="ui-icon-comment"></i>
                                    <span class="f_small"><?php echo $this->locale()->toNumber($item->view_count) ?></span>
                                    <i class="ui-icon-eye-open"></i>
                                  </p>
                                </div>
                              </div>
                            </li>
                          <?php endforeach; ?>
                        
    <?php if (!$this->viewmore): ?> 
				</ul>
			</div>
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
     var widgetUrl = sm4.core.baseUrl + 'widget/index/mod/sitevideo/name/browse-videos-sitevideo';
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
	<div id="layout_sitevideo_browse_video_sitevideo_<?php echo $this->identity; ?>">
	</div>    
      <script type="text/javascript">
       var requestParams = $.extend(<?php echo json_encode($this->params); ?>, {'content_id': '<?php echo $this->identity; ?>','renderDefault': '0'});
        var params = {
            'detactLocation': <?php echo $this->detactLocation; ?>,
            'responseContainer': 'layout_sitevideo_browse_video_sitevideo_<?php echo $this->identity; ?>',
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
