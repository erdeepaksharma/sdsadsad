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

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/style_board.css'); ?>
<?php
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/pinboard/pinboard.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/pinboard/mooMasonry.js');
?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_board.css'); ?>
<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css'); ?>

<?php if ($this->autoload): ?>
  <div id="pinboard_<?php echo $this->identity ?>">
      <?php if(isset ($this->params['defaultLoadingImage']) && $this->params['defaultLoadingImage']): ?>
    <div class="sr_profile_loading_image"></div>
    <?php endif; ?>
  </div>
  <script type="text/javascript">
   var layoutColumn='middle';
   if($("pinboard_<?php echo $this->identity ?>").getParent('.layout_left')){
     layoutColumn='left';
   }else if($("pinboard_<?php echo $this->identity ?>").getParent('.layout_right')){
     layoutColumn='right';
   }
    PinBoardSeaoObject[layoutColumn].add({
      contentId:'pinboard_<?php echo $this->identity ?>',
      widgetId:'<?php echo $this->identity ?>',
      totalCount:'<?php echo $this->totalCount ?>',
      requestParams :<?php echo json_encode($this->params) ?>,
      detactLocation : <?php echo $this->detactLocation; ?>,
      responseContainerClass :'layout_sitereview_pinboard_listings_sitereview'
    });

  </script>
<?php else: ?>
  <?php if (!$this->autoload && !$this->is_ajax_load): ?> 
    <div id="pinboard_<?php echo $this->identity ?>"></div>
    <script type="text/javascript">
      en4.core.runonce.add(function(){
        var pinBoardViewMore= new PinBoardSeaoViewMore({
          contentId:'pinboard_<?php echo $this->identity ?>',
          widgetId:'<?php echo $this->identity ?>',
          totalCount:'<?php echo $this->totalCount ?>',
          viewMoreId:'seaocore_view_more_<?php echo $this->identity ?>',
          loadingId:'seaocore_loading_<?php echo $this->identity ?>',
          requestParams :<?php echo json_encode($this->params) ?>,
          detactLocation : <?php echo $this->detactLocation; ?>,
          responseContainerClass :'layout_sitereview_pinboard_listings_sitereview'
        });
        PinBoardSeaoViewMoreObjects.push(pinBoardViewMore);
      });
    </script>
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

  <?php $countButton = count($this->show_buttons); ?>
  <?php foreach ($this->listings as $sitereview): ?>
    <?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id); ?>
    
    <?php
    $noOfButtons = $countButton;
    if($this->show_buttons):
      if (in_array('compare', $this->show_buttons)):
        $compareButton = $this->compareButton($sitereview, 'pinboard-button');
        if(empty ($compareButton)):$noOfButtons--; endif;
      else:
        $compareButton = null;
      endif;
      
      $alllowComment=(in_array('comment', $this->show_buttons) || in_array('like', $this->show_buttons)) && $sitereview->authorization()->isAllowed($this->viewer(), "comment_listtype_" . $sitereview->listingtype_id);
      if(in_array('comment', $this->show_buttons) && !$alllowComment){
        $noOfButtons--;
      }
       if(in_array('like', $this->show_buttons) && !$alllowComment){
        $noOfButtons--;
      }
      if (in_array('wishlist', $this->show_buttons) && !Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->wishlist):
         $noOfButtons--;
      endif;
     endif;
    ?>
    <div class="seaocore_list_wrapper" style="width:<?php echo $this->params['itemWidth'] ?>px;">
      <div class="seaocore_board_list b_medium" style="width:<?php echo $this->params['itemWidth'] - 18 ?>px;">
        <div>
          <?php if ($sitereview->featured): ?>
            <span class="seaocore_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"><?php echo $this->translate('Featured'); ?></span>
          <?php elseif ($sitereview->newlabel): ?>
            <i class="seaocore_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
          <?php endif; ?>
          <div class="seaocore_board_list_thumb">
          	<a href="<?php echo $sitereview->getHref(array('profile_link' => 1)) ?>" class="seaocore_thumb">
	            <table>
	              <tr valign="middle">
	                <td>
	                             <?php $options=  array('align' => 'center');
	                  
	                  if(isset ($this->params['withoutStretch']) && $this->params['withoutStretch']):
	                 $options['style']='width:auto; max-width:'.($this->params['itemWidth'] - 18).'px;';
	                          endif;?>  
	    <?php echo $this->itemPhoto($sitereview, ($this->params['itemWidth']>300)?'thumb.main':'thumb.profile', '', $options); ?>
	                  
			              
	                </td> 
	              </tr> 
	            </table>
            </a>
          </div>
          
          <div class="seaocore_board_list_btm">
            <?php if ($this->postedby): ?>
              <?php echo $this->htmlLink($sitereview->getOwner()->getHref(), $this->itemPhoto($sitereview->getOwner(), 'thumb.icon', '', array())) ?>
              <?php endif; ?>  
            <div class="o_hidden seaocore_stats seaocore_txt_light">
              <?php if ($this->postedby): ?>
                <b><?php echo $this->htmlLink($sitereview->getOwner()->getHref(), $sitereview->getOwner()->getTitle()) ?></b><br />
              <?php endif; ?>
              <?php echo $this->translate("in %s", $this->htmlLink($sitereview->getCategory()->getHref(), $this->translate($sitereview->getCategory()->getTitle(true)))) ?> - 
    <?php echo $this->timestamp(strtotime($sitereview->creation_date)) ?>
            </div>
          </div>
          
          <?php if (!empty($sitereview->sponsored)): ?>
            <div class="seaocore_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
            <?php echo $this->translate('SPONSORED'); ?>                 
            </div>
          <?php endif; ?>
          
          <div class="seaocore_board_list_cont">
            <div class="seaocore_title">
              <?php echo $this->htmlLink($sitereview->getHref(), $sitereview->getTitle()) ?>
            </div>
            
            <?php if($this->truncationDescription): ?>
              <div class="seaocore_description">
                <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($sitereview->getDescription(), $this->truncationDescription) ?>
              </div>  
            <?php endif;  ?>
            <?php if(!empty($sitereview->price) && $sitereview->price > 0 && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->price  && isset($this->params['price']) && $this->params['price']): ?>
                  <div class="seaocore_stats seaocore_txt_light mtop5">
                   <span><?php echo $this->translate('Price:'); ?></span>
                   <span><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($sitereview->price); ?></span>
                  </div>
             <?php endif; ?>
                        <?php if(isset($this->params['location']) && $this->params['location'] && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->location  && !empty($sitereview->location)):?>
            <div class="seaocore_stats seaocore_txt_light mtop5">
              <span><?php echo $this->translate('Location:'); ?></span>
              <span><?php echo $sitereview->location ?>&nbsp; - 
              <b>
                <?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?>
              </b>
              </span>
            </div>
          <?php endif; ?>
            <?php if (!empty($this->statistics)): ?>
              <div class="seaocore_stats seaocore_txt_light">
                <?php
                if (in_array('viewCount', $this->statistics)) {
                 echo $this->translate(array('%s view', '%s views', $sitereview->view_count), $this->locale()->toNumber($sitereview->view_count)) . '&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                if (in_array('likeCount', $this->statistics)) {
                  echo '<span class="pin_like_st_' . $sitereview->getGuid() . '">' . $this->translate(array('%s like', '%s likes', $sitereview->like_count), $this->locale()->toNumber($sitereview->like_count)) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                if (in_array('commentCount', $this->statistics)) {
                   echo  '<span id="pin_comment_st_' . $sitereview->getGuid().'_'.$this->identity . '">' .$this->translate(array('%s comment', '%s comments', $sitereview->comment_count), $this->locale()->toNumber($sitereview->comment_count)) . '</span>';
                }
             
                ?>
                <?php //echo $statistics; ?> 
              </div>
            <?php endif; ?>
            
            <div class="seaocore_stats seaocore_txt_light mtop5">
            	<span class="fright">
	            	 <?php  $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
		                if ($this->statistics && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2)):
		
		                  echo $this->htmlLink($sitereview->getHref(), $this->partial(
		                                          '_showReview.tpl', 'sitereview', array('sitereview' => $sitereview))) . '';
		
		                endif;
								?>
							</span>
							<span class="o_hidden">
	              <?php if ($ratingValue == 'rating_both'): ?>
	                <?php echo $this->showRatingStar($sitereview->rating_editor, 'editor', $ratingShow, $sitereview->listingtype_id); ?>
	                <br />
	                <?php echo $this->showRatingStar($sitereview->rating_users, 'user', $ratingShow, $sitereview->listingtype_id); ?>
	              <?php else: ?>
	                <?php echo $this->showRatingStar($sitereview->$ratingValue, $ratingType, $ratingShow, $sitereview->listingtype_id); ?>
						    <?php endif; ?>
							</span>    
            </div>
          </div>

          <?php if($this->userComment): ?> 
						<div class="seaocore_board_list_comments o_hidden">
							<?php echo $this->action("list", "pin-board-comment", "seaocore", array("type" => $sitereview->getType(), "id" => $sitereview->listing_id, 'widget_id' => $this->identity)); ?>
						</div>
          <?php endif; ?>
          <?php if (!empty($this->show_buttons)): ?>
            <div class="seaocore_board_list_action_links">
              <?php $urlencode = urlencode(((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $sitereview->getHref()); ?>
              <?php if (in_array('wishlist', $this->show_buttons) && Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->wishlist): ?> 
                <?php echo $this->addToWishlist($sitereview, array('classIcon' => 'seaocore_board_icon', 'classLink' => 'wishlist_icon', 'text' => $this->translate('') , 'title' => 'Wishlist'));?>
              <?php endif; ?>
              
              
              <?php if ((in_array('comment', $this->show_buttons) || in_array('like', $this->show_buttons)) && $alllowComment): ?>
                <?php if (in_array('comment', $this->show_buttons)): ?>
                  <a href='javascript:void(0);' onclick="en4.seaocorepinboard.comments.addComment('<?php echo $sitereview->getGuid() . "_" . $this->identity ?>')" class="seaocore_board_icon comment_icon" title="Comment"><!--<?php echo $this->translate('Comment'); ?>--></a> 
                <?php endif; ?>
                <?php if (in_array('like', $this->show_buttons)): ?>
                  <a href="javascript:void(0)" title="Like" class="seaocore_board_icon like_icon <?php echo $sitereview->getGuid() ?>like_link" id="<?php echo $sitereview->getType() ?>_<?php echo $sitereview->getIdentity() ?>like_link" <?php if ($sitereview->likes()->isLike($this->viewer())): ?>style="display: none;" <?php endif; ?>onclick="en4.seaocorepinboard.likes.like('<?php echo $sitereview->getType() ?>', '<?php echo $sitereview->getIdentity() ?>');" ><!--<?php echo $this->translate('Like'); ?>--></a>

                  <a  href="javascript:void(0)" title="Unlike" class="seaocore_board_icon unlike_icon <?php echo $sitereview->getGuid() ?>unlike_link" id="<?php echo $sitereview->getType() ?>_<?php echo $sitereview->getIdentity() ?>unlike_link" <?php if (!$sitereview->likes()->isLike($this->viewer())): ?>style="display:none;" <?php endif; ?> onclick="en4.seaocorepinboard.likes.unlike('<?php echo $sitereview->getType() ?>', '<?php echo $sitereview->getIdentity() ?>');"><!--<?php echo $this->translate('Unlike'); ?>--></a> 
                <?php endif; ?>
              <?php endif; ?>
                  
              <?php if (in_array('share', $this->show_buttons)): ?>
                <?php echo $this->htmlLink(array('module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'route' => 'default', 'type' => $sitereview->getType(), 'id' => $sitereview->getIdentity(), 'not_parent_refresh' => '1', 'format' => 'smoothbox'), $this->translate(''), array('class' => 'smoothbox seaocore_board_icon share_icon' , 'title' => 'Share')); ?>
              <?php endif; ?>
                  
              <?php if (in_array('facebook', $this->show_buttons)): ?>
                <?php echo $this->htmlLink('http://www.facebook.com/share.php?u=' . $urlencode . '&t=' . $sitereview->getTitle(), $this->translate(''), array('class' => 'pb_ch_wd seaocore_board_icon fb_icon' , 'title' => 'Facebook')) ?>
              <?php endif; ?>
                  
              <?php if (in_array('twitter', $this->show_buttons)): ?>
                <?php echo $this->htmlLink('http://twitter.com/share?url=' . $urlencode . '&text=' . $sitereview->getTitle(), $this->translate(''), array('class' => 'pb_ch_wd seaocore_board_icon tt_icon' , 'title' => 'Twitter')) ?> 
              <?php endif; ?>
                  
              <?php if (in_array('pinit', $this->show_buttons)): ?>
                <a href="http://pinterest.com/pin/create/button/?url=<?php echo $urlencode; ?>&media=<?php echo urlencode((!preg_match("~^(?:f|ht)tps?://~i", $sitereview->getPhotoUrl('thumb.profile')) ? (((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] ) : '') . $sitereview->getPhotoUrl('thumb.profile')); ?>&description=<?php echo $sitereview->getTitle(); ?>"  class="pb_ch_wd seaocore_board_icon pin_icon" title="Pin It" ><!--<?php echo $this->translate('Pin It') ?>--></a>
              <?php endif; ?>
                
              <?php if (in_array('tellAFriend', $this->show_buttons)): ?>
                <?php echo $this->htmlLink(array('action' => 'tellafriend', 'route' => 'sitereview_specific_listtype_' . $sitereview->listingtype_id, 'type' => $sitereview->getType(), 'listing_id' => $sitereview->getIdentity()), $this->translate(''), array('class' => 'smoothbox seaocore_board_icon taf_icon' , 'title' => 'Tell a Friend')); ?>
              <?php endif; ?>
                
              <?php if (in_array('print', $this->show_buttons)): ?>
                <?php echo $this->htmlLink(array('action' => 'print', 'route' => 'sitereview_specific_listtype_' . $sitereview->listingtype_id, 'type' => $sitereview->getType(), 'listing_id' => $sitereview->getIdentity()), $this->translate(''), array('class' => 'pb_ch_wd seaocore_board_icon print_icon' , 'title' => 'Print')); ?> 
              <?php endif; ?>
              <?php if ($compareButton): ?>
                <?php echo $compareButton; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$this->autoload && !$this->is_ajax_load): ?>
    <div class="seaocore_view_more mtop10 dnone" id="seaocore_view_more_<?php echo $this->identity ?>">
      <a href="javascript:void(0);" id="" class="buttonlink icon_viewmore"><?php echo$this->translate('View More') ?></a>
    </div>
    <div class="seaocore_loading dnone" id="seaocore_loading_<?php echo $this->identity ?>" >
      <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/core/loading.gif" style="margin-right: 5px;">
      <?php echo $this->translate('Loading ...') ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

