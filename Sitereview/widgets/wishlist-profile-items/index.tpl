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
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css')
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_board.css')
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
$this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/core.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/pinboard.js')
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/mooMasonry.js');
?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/like.js'); ?>

<script type="text/javascript">
  var seaocore_content_type = 'sitereview';
  var seaocore_like_url = en4.core.baseUrl + 'sitereview/index/globallikes';
</script>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/follow.js'); ?>

<script type="text/javascript">
  var currentPage=<?php echo $this->paginator->getCurrentPageNumber(); ?>;
<?php if (!$this->isAjax): ?>
    var toggoleViewWishListProfile=null;
    var requestActive=false;
    en4.core.runonce.add(function(){
       <?php if (in_array('pin', $this->viewTypes)): ?>  
          en4.srpinboard.masonryWidgetAllow[<?php echo $this->identity ?>]=false;
          en4.srpinboard.masonryArray.push({
            columnWidth: <?php echo $this->itemWidth; ?>,  
            singleMode: true,
            itemSelector: '.sr_list_wrapper',
            responseContainer :$('items_content'),
            allowId:<?php echo $this->identity ?>
          });     
          <?php if ($this->params['viewType'] == 'pin' && $this->total_item > 0): ?>
             en4.srpinboard.masonryWidgetAllow[<?php echo $this->identity ?>]=true;
             en4.srpinboard.setMasonryLayout();
          <?php endif; ?>
       <?php endif; ?>     
       <?php if (count($this->viewTypes) > 1): ?>        
          toggoleViewWishListProfile =function(viewtype){ 
            if(requestActive)
              return;
            var form=$('wishlist_items_filter_form');
            if(form.getElement('#viewType').value==viewtype)
              return;
            form.getElement('#viewType').value=viewtype;
            if(viewtype=='pin'){
              $('items_content').removeClass('seaocore_browse_list').addClass('sr_browse_pin');
            }else{
              $('items_content').removeClass('sr_browse_pin').addClass('seaocore_browse_list');
               $('items_content').setStyle('height', 'auto');
            }
            en4.srpinboard.masonryWidgetAllow[<?php echo $this->identity ?>]=viewtype=='pin';
            ajaxContent(1);
          }
        <?php endif; ?>
                          
        $('wishlist_items_filter_form').removeEvents('submit').addEvent('submit', function(event){
          event.stop();
          ajaxContent(1);
        });
                    
        window.addEvent('scroll', function(){
          if(requestActive)
            return;
          if(<?php echo $this->paginator->count() ?> > currentPage && currentPage!= 0){
            var elementPostionY = 0;
            if( typeof( $('srw_loading').offsetParent ) != 'undefined' ) {
              elementPostionY=$('srw_loading').offsetTop;
            }else{
              elementPostionY=$('srw_loading').y; 
            }
            if(elementPostionY <= window.getScrollTop()+(window.getSize().y -10)){
              ajaxContent(currentPage+1); 
            }
          }
        });
        var ajaxContent = function(page) {
          if(requestActive)
            return;
          if(page==1){
            $('loading_image').style.display = 'block';
            $('items_content').empty();
            $('items_content').style.display = 'none';
          }else{
            $('srw_loading').removeClass('dnone');
          }
          var params = $('wishlist_items_filter_form').toQueryString();
          requestActive=true; 
          en4.core.request.send(new Request.HTML({
            url : en4.core.baseUrl + 'widget/index/content_id/<?php echo sprintf('%d', $this->identity) ?>?'+params,
                      
            data :$merge(<?php echo json_encode($this->params) ?>, {
              format : 'html',
              method : 'get',
              subject : en4.core.subject.guid,
              currentpage : page,
              isAjax : true,
              itemCount : '<?php echo $this->itemCount; ?>',
              postedby : '<?php echo $this->postedby; ?>',
              ratingType : '<?php echo $this->ratingType; ?>'
            }),
            onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
                        
              if(page==1){
                $('loading_image').style.display = 'none';
                $('items_content').style.display = 'block';
              }else{
                $('srw_loading').addClass('dnone');
              }
              requestActive=false;
              Elements.from(responseHTML).inject($('items_content'));
              en4.core.runonce.trigger();
              Smoothbox.bind($('items_content'));
              if($('wishlist_items_filter_form').getElement('#viewType').value=='pin'){
                en4.srpinboard.setMasonryLayout();
              }
            }
          }), {
            'force': true
          })    
        }
                           
      });
<?php endif; ?>
</script>

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

 
<?php if (!$this->isAjax): ?>
  
  <div class="sr_wishlist_view">
    <?php if(!$favouriteSetting): ?>    
        <div class="sr_wishlist_view_title"> 
          <?php echo $this->wishlist->title; ?> 
        </div>
        <div class="sr_wishlist_view_des mbot10">
          <?php echo $this->wishlist->body; ?>
        </div>
    
        <div class="sr_wishlist_view_about b_medium clr o_hidden">
          <div class="sr_wishlist_view_about_left fleft">
            <?php if ($this->postedby): ?>
              <div class="thumb fleft mright5">
                <?php echo $this->htmlLink($this->wishlist->getOwner()->getHref(), $this->itemPhoto($this->wishlist->getOwner(), 'thumb.icon', '')); ?>
              </div>
            <?php endif; ?>
            <div class="o_hidden">
              <?php if ($this->postedby): ?>
                <div class="bold mbot5">
                  <?php echo $this->wishlist->getOwner()->toString(); ?>
                </div>
              <?php endif; ?>
              <div class="sr_wishlist_view_stats seaocore_txt_light">
                <?php echo $this->timestamp($this->wishlist->creation_date); ?>
              </div>
              <?php if(!empty($this->statisticsWishlist)): ?>
                <div class="sr_wishlist_view_stats seaocore_txt_light">
                  <?php 
                    $statistics = '';
                    if(in_array('followCount', $this->statisticsWishlist)) {
                      $followers = $this->translate(array('<b>%s</b> Follower', '<b>%s</b> Followers', $this->wishlist->follow_count), $this->locale()->toNumber($this->wishlist->follow_count));

                      if($this->viewer_id) {
                        $statistics .= '<a title="'.$this->translate('Click to see Followers').'", class="smoothbox" href="' . $this->url(array('module' => 'seaocore', 'controller' => 'follow', 'action' => 'get-followers', 'resource_type' => 'sitereview_wishlist', 'resource_id' => $this->wishlist->wishlist_id, 'call_status' => 'public'), 'default', true) . '">' . $followers . '</a>';
                      }
                      else {
                        $statistics .= $followers;
                      }

                      $statistics .= '&nbsp&nbsp&nbsp';
                    }

                    if(in_array('entryCount', $this->statisticsWishlist)) {
                      $statistics .= $this->translate(array('<b>%s</b> Entry', '<b>%s</b> Entries', $this->total_item), $this->locale()->toNumber($this->total_item)).'&nbsp&nbsp&nbsp';
                    }                            

                    if(in_array('viewCount', $this->statisticsWishlist)) {
                      $statistics .= $this->translate(array('<b>%s</b> View', '<b>%s</b> Views', $this->wishlist->view_count), $this->locale()->toNumber($this->wishlist->view_count)).'&nbsp&nbsp&nbsp';
                    }

                    if(in_array('likeCount', $this->statisticsWishlist)) {
                      $statistics .= $this->translate(array('<b>%s</b> Like', '<b>%s</b> Likes', $this->wishlist->like_count), $this->locale()->toNumber($this->wishlist->like_count));
                    }                 

                  ?>
                  <?php echo $statistics; ?>
                </div>  
              <?php endif; ?>
            </div>
          </div>

          <?php $widgetContent = $this->content()->renderWidget("sitereview.share", array('subject' => $this->wishlist->getGuid(), 'withoutContainer' => true,'options'=>$this->shareOptions, 'content_id' => $this->identity)) ?>
          <?php if (strlen($widgetContent) > 15): ?>
            <div class="sr_wishlist_view_about_right fright">
              <?php echo $widgetContent ?>
            </div>
          <?php endif; ?>

          <div class="sr_wishlist_view_about_middle">
            <?php if ($this->viewer_id && !empty($this->followLike) && in_array('like', $this->followLike)): ?>
              <div>
                <?php $check_availability = Engine_Api::_()->sitereview()->check_availability('sitereview_wishlist', $this->wishlist->wishlist_id); ?>

                <div class="seaocore_like_button" id="sitereview_unlikes_<?php echo $this->wishlist->wishlist_id;?>" style ='display:<?php echo $check_availability ?"block":"none"?>'>
                  <a href="javascript:void(0);" onclick = "seaocore_content_type_likes('<?php echo $this->wishlist->wishlist_id; ?>', 'sitereview_wishlist');">
                    <i class="seaocore_like_thumbdown_icon"></i>
                    <span><?php echo $this->translate('Unlike') ?></span>
                  </a>
                </div>
                <div class="seaocore_like_button" id="sitereview_most_likes_<?php echo $this->wishlist->wishlist_id;?>" style ='display:<?php echo empty($check_availability) ?"block":"none"?>'>
                  <a href = "javascript:void(0);" onclick = "seaocore_content_type_likes('<?php echo $this->wishlist->wishlist_id; ?>', 'sitereview_wishlist');">
                    <i class="seaocore_like_thumbup_icon"></i>
                    <span><?php echo $this->translate('Like') ?></span>
                  </a>
                </div>
                <input type ="hidden" id = "sitereview_like_<?php echo $this->wishlist->wishlist_id;?>" value = '<?php echo $check_availability ? $check_availability :0; ?>' />   
              </div>
            <?php endif; ?>
            <?php if ($this->viewer_id && !empty($this->followLike) && in_array('follow', $this->followLike) && !$this->wishlist->isOwner($this->viewer)): ?>
              <div>
                <?php $check_availability = $this->wishlist->follows()->isFollow($this->viewer);?>
                <div class="seaocore_follow_button_active" id="sitereview_unfollows_<?php echo $this->wishlist->wishlist_id;?>" style ='display:<?php echo $check_availability ?"block":"none"?>' >

                  <a class="seaocore_follow_button seaocore_follow_button_following" href="javascript:void(0);">
                    <i class="following"></i>
                    <span><?php echo $this->translate('Following Wishlist') ?></span>
                  </a>

                  <a class="seaocore_follow_button seaocore_follow_button_unfollow" href="javascript:void(0);" onclick = "seaocore_content_type_follows('<?php echo $this->wishlist->wishlist_id; ?>', 'sitereview_wishlist');">
                    <i class="unfollow"></i>
                    <span><?php echo $this->translate('Un-follow Wishlist') ?></span>
                  </a>

                </div>

                <div id="sitereview_most_follows_<?php echo $this->wishlist->wishlist_id;?>" style ='display:<?php echo empty($check_availability) ?"block":"none"?>'>
                  <a class="seaocore_follow_button" href="javascript:void(0);" onclick = "seaocore_content_type_follows('<?php echo $this->wishlist->wishlist_id; ?>', 'sitereview_wishlist');">
                    <i class="follow"></i>
                    <span><?php echo $this->translate('Follow Wishlist') ?></span>
                  </a>
                </div>
                <input type ="hidden" id = "sitereview_follow_<?php echo $this->wishlist->wishlist_id;?>" value = '<?php echo $check_availability ? $check_availability :0; ?>' />
              </div>          
            <?php endif; ?>
          </div>
        </div>
    
        <?php if ($this->viewer_id): ?>
          <div class="sr_wishlist_item_options clr O_hidden mtop10 pleft10">
            <?php echo $this->htmlLink(array('route' => 'sitereview_wishlist_general', 'action' => 'create'), $this->translate('Create New Wishlist'), array('class' => 'smoothbox sr_icon_wishlist_add')) ?>
            <?php if (!empty($this->messageOwner)): ?>
              <?php echo $this->htmlLink(array('route' => 'sitereview_wishlist_general', 'action' => 'message-owner', 'wishlist_id' => $this->wishlist->getIdentity()), $this->translate('Message Owner'), array('class' => 'smoothbox icon_sitereviews_messageowner')) ?>
            <?php endif; ?>
            <?php if ($this->wishlist->owner_id == $this->viewer_id || $this->level_id == 1): ?>
              <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'edit', 'wishlist_id' => $this->wishlist->getIdentity()), $this->translate('Edit Wishlist'), array('title' => $this->translate('Edit Wishlist'), 'class' => 'smoothbox seaocore_icon_edit', 'style' => 'margin-left:0px;')) ?> 
              <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'delete', 'wishlist_id' => $this->wishlist->getIdentity()), $this->translate('Delete Wishlist'), array('title' => $this->translate('Delete Wishlist'), 'class' => 'smoothbox seaocore_icon_delete')) ?>
            <?php endif; ?>
            <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'text' => $this->wishlist->getOwner()->getTitle()), $this->wishlist->getOwner()->getTitle().$this->translate("'s Wishlists"), array('title' => $this->wishlist->getOwner()->getTitle().$this->translate("'s Wishlists"), 'class' => 'sr_icon_wishlist')) ?>
          </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="sr_wishlist_view_title"> 
          <?php echo $this->translate('My Favourites'); ?> 
        </div>
        <div class="sr_wishlist_view_des mbot10">
          <?php echo $this->translate('This page lists all the entries added by you as favourites.'); ?>
        </div>     
    <?php endif;?>
		<div class="sr_item_filters_wrapper b_medium clr">
			<?php echo $this->searchForm->setAttrib('class', 'sr_item_filters')->render($this) ?>
			
		  <?php if (count($this->viewTypes) > 1 && $this->total_item > 0): ?>
		  	<div class="sr_wishlist_view_select fright">
			    <?php if (in_array('list', $this->viewTypes)): ?>
		        <span class="seaocore_tab_select_wrapper fright">
		          <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("List View"); ?></div>
		          <span class="seaocore_tab_icon tab_icon_list_view" onclick="toggoleViewWishListProfile('list');" ></span>
		        </span>
			    <?php endif; ?>
			    <?php if (in_array('pin', $this->viewTypes)): ?>
		        <span class="seaocore_tab_select_wrapper fright">
		          <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("Pinboard View"); ?></div>
		          <span class="seaocore_tab_icon tab_icon_pin_view" onclick="toggoleViewWishListProfile('pin');" ></span>
		        </span>
			    <?php endif; ?>
		    </div>
		  <?php endif; ?>
    </div>
    
    <div id="sitereview_wishlist_items">
      <div id="loading_image" class="seaocore_content_loader" style="display: none;"></div>      

      <ul class="<?php if ($this->params['viewType'] == 'pin'): ?> seaocore_browse_pin <?php else: ?> seaocore_browse_list  <?php endif; ?>" id="items_content">
<?php endif; ?>
<?php if ($this->total_item > 0): ?>
  <?php foreach ($this->paginator as $listing): ?>
    <?php if ($this->params['viewType'] == 'pin'): ?>
      <?php $countButton = count($this->show_buttons); ?>
      <?php $listingType = Zend_Registry::get('listingtypeArray' . $listing->listingtype_id); ?>

      <?php
      $noOfButtons = $countButton;
      if ($this->show_buttons):
        if (in_array('compare', $this->show_buttons)):
          $compareButton = $this->compareButton($listing, 'pinboard-button');
          if (empty($compareButton)):$noOfButtons--;
          endif;
        else:
          $compareButton = null;
        endif;

        $alllowComment = (in_array('comment', $this->show_buttons) || in_array('like', $this->show_buttons)) && $listing->authorization()->isAllowed($this->viewer(), "comment_listtype_" . $listing->listingtype_id);
        if (in_array('comment', $this->show_buttons) && !$alllowComment) {
          $noOfButtons--;
        }
        if (in_array('like', $this->show_buttons) && !$alllowComment) {
          $noOfButtons--;
        }
        if (in_array('wishlist', $this->show_buttons) && !$listingType->wishlist):
          $noOfButtons--;
        endif;
      endif;
      if ($this->wishlist->owner_id == $this->viewer_id):
        $noOfButtons++;
        if ($this->wishlist->listing_id != $listing->listing_id):
          $noOfButtons++;
        endif;
      endif;
      ?>
      <div class="sr_list_wrapper" style="width:<?php echo $this->itemWidth ?>px;">
        <div class="sr_board_list b_medium" style="width:<?php echo $this->itemWidth - 18 ?>px;">
          <div>
            <?php if ($listing->featured): ?>
              <i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
            <?php endif; ?>
            <?php if ($listing->newlabel): ?>
              <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
            <?php endif; ?>
            <div class="sr_board_list_thumb">
            	<a href="<?php echo $listing->getHref(array('profile_link' => 1)) ?>" class="sr_thumb">
	              <table style="height: <?php echo 30 * $noOfButtons ?>px;">
	                <tr valign="middle">
	                  <td>
	                               
	                    <?php
	                    $options = array('align' => 'center');
	
	                    if (isset($this->params['withoutStretch']) && $this->params['withoutStretch']):
	                      $options['style'] = 'width:auto; max-width:' . ($this->itemWidth - 18) . 'px;';
	                    endif;
	                    ?>  
	                      <?php echo $this->itemPhoto($listing, ($this->itemWidth > 300) ? 'thumb.main' : 'thumb.profile', '', $options); ?>
	                      <?php if (!empty($listing->sponsored)): ?>
	                      <div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
	                        <?php echo $this->translate('SPONSORED'); ?>                 
	                      </div>
	                    <?php endif; ?>
	                  </td> 
	                </tr> 
	              </table>
	             </a>
            </div>
            <div class="sr_board_list_cont">
              <div class="sr_title">
                <?php echo $this->htmlLink($listing->getHref(), $listing->getTitle()) ?>
              </div>
              
             <?php if($this->truncationDescription): ?>
              <div class="sr_description">
                <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($listing->getDescription(), $this->truncationDescription) ?>
              </div>  
            <?php endif;  ?>             
              
                <?php if (!empty($this->statistics)): ?>
                <div class="sr_stats seaocore_txt_light">
                    <?php
                    if (in_array('viewCount', $this->statistics)) :
                      echo $this->translate(array('%s view', '%s views', $listing->view_count), $this->locale()->toNumber($listing->view_count)) . '&nbsp;&nbsp;&nbsp;&nbsp;';
                    endif;

                    if (in_array('likeCount', $this->statistics)) :
                      echo '<span class="pin_like_st_' . $listing->getGuid() . '">' . $this->translate(array('%s like', '%s likes', $listing->like_count), $this->locale()->toNumber($listing->like_count)) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;';
                    endif;
                    if (in_array('commentCount', $this->statistics)) :
                      echo '<span id="pin_comment_st_' . $listing->getGuid() . '_' . $this->identity . '">' . $this->translate(array('%s comment', '%s comments', $listing->comment_count), $this->locale()->toNumber($listing->comment_count)) . '</span>';
                    endif;
                    ?>
                </div>
                <?php endif; ?>

              <div class="sr_stats seaocore_txt_light mtop5">
                <span class="fright">
              <?php
              $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listing->listingtype_id);
              if ($this->statistics && in_array('reviewCount', $this->statistics) && ($listingtypeArray->reviews == 3 || $listingtypeArray->reviews == 2) && (!empty($listingtypeArray->allow_review) || isset ($listing->rating_editor) && $listing->rating_editor && $listing->review_count==1)):

                echo $this->htmlLink($listing->getHref(), $this->partial(
                                '_showReview.tpl', 'sitereview', array('sitereview' => $listing))) . '';

              endif;
              ?>
                </span>
                <span class="o_hidden">
                  <?php if ($ratingValue == 'rating_both'): ?>
                    <?php echo $this->showRatingStar($listing->rating_editor, 'editor', $ratingShow, $listing->listingtype_id); ?>
                    <br />
                    <?php echo $this->showRatingStar($listing->rating_users, 'user', $ratingShow, $listing->listingtype_id); ?>
                  <?php else: ?>
                    <?php echo $this->showRatingStar($listing->$ratingValue, $ratingType, $ratingShow, $listing->listingtype_id); ?>
                  <?php endif; ?>
                </span>    
              </div>
            </div>
            <div class="sr_board_list_btm o_hidden clr">
              <?php if ($this->postedbyInList): ?>
                <?php echo $this->htmlLink($listing->getOwner()->getHref(), $this->itemPhoto($listing->getOwner(), 'thumb.icon', '', array())) ?>
              <?php endif; ?>  
              <div class="o_hidden sr_stats seaocore_txt_light">
              <?php if ($this->postedbyInList): ?>
                  <b><?php echo $this->htmlLink($listing->getOwner()->getHref(), $listing->getOwner()->getTitle()) ?></b><br />
              <?php endif; ?>
                <?php echo $this->translate("in %s", $this->htmlLink($listing->getCategory()->getHref(), $this->translate($listing->getCategory()->getTitle(true)))) ?> - 
                <?php echo $this->timestamp(strtotime($listing->creation_date)) ?>
              </div>
            </div>
            <div class="sr_board_list_comments o_hidden">
                <?php echo $this->action("list", "pin-board-comment", "sitereview", array("type" => $listing->getType(), "id" => $listing->listing_id, 'widget_id' => $this->identity)); ?>
            </div>
            <?php if ($noOfButtons): ?>
              <div class="sr_board_list_action_links">
                <?php if ($this->wishlist->owner_id == $this->viewer_id): ?>
                  
                <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'remove', 'listing_id' => $listing->listing_id, 'wishlist_id' => $this->wishlist->wishlist_id), $this->translate('Remove'), array('class' => 'smoothbox sr_board_icon seaocore_icon_delete')) ?>
                  
                  <?php if (!$favouriteSetting && $this->wishlist->listing_id != $listing->listing_id): ?>                   
                      <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'cover-photo', 'listing_id' => $listing->listing_id, 'wishlist_id' => $this->wishlist->wishlist_id), $this->translate('Make Cover'), array('class' => 'smoothbox sr_board_icon sr_icon_cover')) ?>                    
                   <?php endif; ?>
                <?php endif; ?>
                <?php $urlencode = urlencode(((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $listing->getHref()); ?>
                <?php if (in_array('wishlist', $this->show_buttons) && Zend_Registry::get('listingtypeArray' . $listing->listingtype_id)->wishlist): ?> 
                  
                  <?php if(!$favouriteSetting): ?>
                    <?php echo $this->addToWishlist($listing, array('classIcon' => 'sr_board_icon', 'classLink' => 'wishlist_icon', 'text' => $this->translate('Wishlist')));?>
                  <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($compareButton): ?>
                  <?php echo $compareButton; ?>
                <?php endif; ?>
                <?php if ((in_array('comment', $this->show_buttons) || in_array('like', $this->show_buttons)) && $alllowComment): ?>
                  <?php if (in_array('comment', $this->show_buttons)): ?>
                    <a href='javascript:void(0);' onclick="en4.srpinboard.comments.addComment('<?php echo $listing->getGuid() . "_" . $this->identity ?>')" class="sr_board_icon icon_sitereviews_comment"><?php echo $this->translate('Comment'); ?></a> 
                  <?php endif; ?>
                  <?php if (in_array('like', $this->show_buttons)): ?>
                    <a href="javascript:void(0)" class="sr_board_icon like_icon <?php echo $listing->getGuid() ?>like_link" id="<?php echo $listing->getType() ?>_<?php echo $listing->getIdentity() ?>like_link" <?php if ($listing->likes()->isLike($this->viewer())): ?>style="display: none;" <?php endif; ?>onclick="en4.srpinboard.likes.like('<?php echo $listing->getType() ?>', '<?php echo $listing->getIdentity() ?>');" ><?php echo $this->translate('Like'); ?></a>

                    <a  href="javascript:void(0)" class="sr_board_icon unlike_icon <?php echo $listing->getGuid() ?>unlike_link" id="<?php echo $listing->getType() ?>_<?php echo $listing->getIdentity() ?>unlike_link" <?php if (!$listing->likes()->isLike($this->viewer())): ?>style="display:none;" <?php endif; ?> onclick="en4.srpinboard.likes.unlike('<?php echo $listing->getType() ?>', '<?php echo $listing->getIdentity() ?>');"><?php echo $this->translate('Unlike'); ?></a> 
                  <?php endif; ?>
                <?php endif; ?>

                <?php if (in_array('share', $this->show_buttons)): ?>
                  <?php echo $this->htmlLink(array('module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'route' => 'default', 'type' => $listing->getType(), 'id' => $listing->getIdentity(), 'not_parent_refresh' => '1', 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox sr_board_icon seaocore_icon_share')); ?>
                <?php endif; ?>

                <?php if (in_array('facebook', $this->show_buttons)): ?>
                  <?php echo $this->htmlLink('http://www.facebook.com/share.php?u=' . $urlencode . '&t=' . $listing->getTitle(), $this->translate('Facebook'), array('class' => 'pb_ch_wd sr_board_icon fb_icon')) ?>
                <?php endif; ?>

                <?php if (in_array('twitter', $this->show_buttons)): ?>
                  <?php echo $this->htmlLink('http://twitter.com/share?url=' . $urlencode . '&text=' . $listing->getTitle(), $this->translate('Twitter'), array('class' => 'pb_ch_wd sr_board_icon tt_icon')) ?> 
                <?php endif; ?>

                <?php if (in_array('pinit', $this->show_buttons)): ?>
                  <a href="http://pinterest.com/pin/create/button/?url=<?php echo $urlencode; ?>&media=<?php echo urlencode((!preg_match("~^(?:f|ht)tps?://~i", $listing->getPhotoUrl('thumb.profile')) ? (((!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] ) : '') . $listing->getPhotoUrl('thumb.profile')); ?>&description=<?php echo $listing->getTitle(); ?>"  class="pb_ch_wd sr_board_icon pin_icon"  ><?php echo $this->translate('Pin It') ?></a>
                <?php endif; ?>

                <?php if (in_array('tellAFriend', $this->show_buttons)): ?>
                  <?php echo $this->htmlLink(array('action' => 'tellafriend', 'route' => 'sitereview_specific_listtype_' . $listing->listingtype_id, 'type' => $listing->getType(), 'listing_id' => $listing->getIdentity()), $this->translate('Tell a Friend'), array('class' => 'smoothbox sr_board_icon taf_icon')); ?>
                <?php endif; ?>

                <?php if (in_array('print', $this->show_buttons)): ?>
                  <?php echo $this->htmlLink(array('action' => 'print', 'route' => 'sitereview_specific_listtype_' . $listing->listingtype_id, 'type' => $listing->getType(), 'listing_id' => $listing->getIdentity()), $this->translate('Print'), array('class' => 'pb_ch_wd sr_board_icon print_icon')); ?> 
                <?php endif; ?>
              </div>
              <?php endif; ?>
          </div>
        </div>
      </div>
     <?php else: ?>
      <li>

        <div class='seaocore_browse_list_photo'>
      <?php echo $this->htmlLink($listing->getHref(array('profile_link' => 1)), $this->itemPhoto($listing, 'thumb.normal')) ?>
        </div>
        <div class='seaocore_browse_list_info'>
          <div class='seaocore_browse_list_info_title'>
            <?php if ($ratingValue == 'rating_both'): ?>
            <?php echo $this->showRatingStar($listing->rating_editor, 'editor', $ratingShow, $listing->listingtype_id); ?>
              <br/>
              <?php echo $this->showRatingStar($listing->rating_users, 'user', $ratingShow, $listing->listingtype_id); ?>
            <?php else: ?>
              <?php echo $this->showRatingStar($listing->$ratingValue, $ratingType, $ratingShow, $listing->listingtype_id); ?>
            <?php endif; ?>

            <?php echo $this->htmlLink($listing->getHref(), $listing->getTitle()); ?>                
          </div>

            <?php if ($listing->category_id): ?>
            <div class='seaocore_browse_list_info_date'>
              <a href="<?php echo $this->url(array('category_id' => $listing->category_id, 'categoryname' => $listing->getCategory()->getCategorySlug()), "sitereview_general_category_listtype_" . $listing->listingtype_id); ?>"> 
            <?php echo $this->translate($listing->getCategory()->getTitle(true)) ?>
              </a>
            </div>
              <?php endif; ?>
          <div class="seaocore_browse_list_info_date">
            <?php echo $this->timestamp(strtotime($listing->date)) ?>        
            <?php if (!empty($listing->price) && Zend_Registry::get('listingtypeArray' . $listing->listingtype_id)->price): ?>
              - <b><?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($listing->price); ?></b>
            <?php endif; ?>

            <div class='seaocore_browse_list_info_blurb'>
            <?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($listing->body, 150); ?>
            </div>
            <div class="sr_wishlist_item_options clr O_hidden mtop10">
              <?php echo $this->compareButton($listing); ?>
                
              <?php if(!$favouriteSetting): ?>  
                <?php echo $this->addToWishlist($listing, array('classIcon' => 'sr_icon_wishlist_add', 'classLink' => ''));?>  
              <?php endif; ?>  
              <?php if ($this->wishlist->owner_id == $this->viewer_id): ?>
                <?php if($favouriteSetting): ?>
                    <?php $removeText = 'Remove from Favourites';?>
                <?php else: ?>
                    <?php $removeText = 'Remove from this Wishlist';?>
                <?php endif; ?>
                <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'remove', 'listing_id' => $listing->listing_id, 'wishlist_id' => $this->wishlist->wishlist_id), $this->translate($removeText), array('class' => 'smoothbox seaocore_icon_delete')) ?>
              <?php endif; ?>                
              <?php if (!$favouriteSetting && $this->wishlist->owner_id == $this->viewer_id && $this->wishlist->listing_id != $listing->listing_id): ?>                    
                <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'cover-photo', 'listing_id' => $listing->listing_id, 'wishlist_id' => $this->wishlist->wishlist_id), $this->translate('Make Cover Photo'), array('class' => 'smoothbox sr_icon_wishlist_add')) ?>                    
              <?php endif; ?>                    
            </div>
          </div>
      </li>
     <?php endif; ?>
  <?php endforeach; ?>
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
          <?php if (!$this->isAjax): ?>
      </ul>
      <div class="seaocore_loading o_hidden dnone" id="srw_loading" >
        <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/core/loading.gif" style="margin-right: 5px;">
        <?php echo $this->translate('Loading ...') ?>
      </div>
    </div>   
  </div>

  <script type="text/javascript">

    function addOptions(element_value, element_type, element_updated, domready) {

      var element = $(element_updated);
      if(domready == 0){
        switch(element_type){
          case 'listingtype_id':
            $('category_id'+'-wrapper').style.display = 'none';
            clear($('category_id'));
            $('category_id').value = 0;

          case 'cat_dependency':
            $('subcategory_id'+'-wrapper').style.display = 'none';
            clear($('subcategory_id'));
            $('subcategory_id').value = 0;

          case 'subcat_dependency':
            $('subsubcategory_id'+'-wrapper').style.display = 'none';
            clear($('subsubcategory_id'));
            $('subsubcategory_id').value = 0;
        }
      }

      var url = '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'review', 'action' => 'categories'), "default", true); ?>';
      en4.core.request.send(new Request.JSON({      	
        url : url,
        data : {
          format : 'json',
          element_value : element_value,
          element_type : element_type
        },

        onSuccess : function(responseJSON) {
          var categories = responseJSON.categories;
          var option = document.createElement("OPTION");
          option.text = "";
          option.value = 0;
          element.options.add(option);
          for (i = 0; i < categories.length; i++) {
            var option = document.createElement("OPTION");
            option.text = categories[i]['category_name'];
            option.value = categories[i]['category_id'];
            element.options.add(option);
          }

          if(categories.length  > 0 )
            $(element_updated+'-wrapper').style.display = 'block';
          else
            $(element_updated+'-wrapper').style.display = 'none';

          if(domready == 1){
            var value=0;
            if(element_updated=='category_id'){
              value = search_category_id;
            }else if(element_updated=='subcategory_id'){
              value = search_subcategory_id;
            }else{
              value =search_subsubcategory_id;
            }
            $(element_updated).value = value;
          }
        }

      }),{'force':true});
    }

    function clear(element)
    { 
      for (var i = (element.options.length-1); i >= 0; i--)	{
        element.options[ i ] = null;
      }
    }

    var search_category_id,search_subcategory_id,search_subsubcategory_id;
    window.addEvent('domready', function() {

      search_category_id='<?php echo isset($this->requestParams['category_id']) && $this->requestParams['category_id'] ? $this->requestParams['category_id'] : 0 ?>';
      
      var listingtype_id;
      
      if($("listingtype_id"))
       listingtype_id = $("listingtype_id").value;
     else
       listingtype_id = 1;
     
      addOptions(listingtype_id,'listingtype_id', 'category_id',1);

      if(search_category_id !=0) {
        search_subcategory_id='<?php echo isset($this->requestParams['subcategory_id']) && $this->requestParams['subcategory_id'] ? $this->requestParams['subcategory_id'] : 0 ?>';

        addOptions(search_category_id,'cat_dependency', 'subcategory_id',1);

        if(search_subcategory_id !=0) {
          search_subsubcategory_id='<?php echo isset($this->requestParams['subsubcategory_id']) && $this->requestParams['subsubcategory_id'] ? $this->requestParams['subsubcategory_id'] : 0 ?>';
          addOptions(search_subcategory_id,'subcat_dependency', 'subsubcategory_id',1);
        }
      }   
    });
  </script>

<?php endif; ?>
