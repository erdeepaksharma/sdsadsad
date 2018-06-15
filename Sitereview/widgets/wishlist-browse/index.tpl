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
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
?>        
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/like.js'); ?>

<script type="text/javascript">
	var seaocore_content_type = 'sitereview';
	var seaocore_like_url = en4.core.baseUrl + 'sitereview/index/globallikes';
</script>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/follow.js'); ?>

<?php if (count($this->paginator) > 0): ?>
  <script type="text/javascript">
    function switchWishlistView(el,viewtype){   
      var form=null;
      if($('filter_form')){
        form=$('filter_form');
      }else{
        form=$('wishlist_withoutsearch_form');
      }
      if(form.getElement('#viewType').value==viewtype)
        return;
      form.getElement('#viewType').value=viewtype;
      el.getParent('div').getElements('.seaocore_tab_select_wrapper').removeClass('active');
      el.addClass('active');
      var params = form.toQueryString();
      $('tab_icon_loading_view').removeClass('dnone');
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/<?php echo sprintf('%d', $this->identity) ?>?'+params,    
        data :$merge(<?php echo json_encode($this->params) ?>, {
          format : 'html',
          method : 'get',
          isAjax : true
        }),
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {          $('tab_icon_loading_view').addClass('dnone');     
          var container=document.getElement('.layout_sitereview_wishlist_browse');            
          container.empty();
          Elements.from(responseHTML).inject(container);
          en4.core.runonce.trigger();
          Smoothbox.bind(container);
          var windowUrl=window.location.href.split("?")[0];
          if(('pushState' in window.history)){
            window.history.pushState( null,  null, windowUrl+'?'+params);
          }
          if(document.getElement('.paginationControl')){
            document.getElement('.paginationControl').getElements('a').each(function(el){
              el.set('href',el.get('href').replace(en4.core.baseUrl + 'widget/index/content_id/<?php echo sprintf('%d', $this->identity) ?>',windowUrl));
            });
          }
        }
      }), {
        'force': true
      });    
        
      //form.submit();
    }
    en4.core.runonce.add(function(){
      if($('filter_form')){
        $('filter_form').getElement('#viewType').value=$('wishlist_withoutsearch_form').getElement('#viewType').value;
      }
    });
  </script>
  <form id="wishlist_withoutsearch_form" method="post">
    <input type="hidden" name="viewType" value="<?php echo $this->formValues['viewType'] ?>" id="viewType">
  </form>
  <div class="sr_browse_lists_view_options b_medium">
    <div class="fleft">
			<?php echo $this->translate(array('%s wishlist found.', '%s wishlists found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
    </div>
    <?php if(count($this->viewTypes)>1): ?>
      <?php if (in_array('list',$this->viewTypes)): ?>
        <span class="seaocore_tab_select_wrapper fright <?php if ($this->formValues['viewType'] == 'list'): ?> active <?php endif; ?>">
          <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("List View"); ?></div>
          <span class="seaocore_tab_icon tab_icon_list_view" onclick="switchWishlistView(this,'list');" ></span>
        </span>
      <?php endif; ?>
      <?php if (in_array('grid',$this->viewTypes)): ?>
        <span class="seaocore_tab_select_wrapper fright <?php if ($this->formValues['viewType'] == 'grid'): ?> active <?php endif; ?>">
          <div class="seaocore_tab_select_view_tooltip"><?php echo $this->translate("Pinboard View"); ?></div>
          <span class="seaocore_tab_icon tab_icon_pin_view" onclick="switchWishlistView(this,'grid');" ></span>
        </span>
      <?php endif; ?>
	    <span class="fright dnone mright5" id="tab_icon_loading_view">
	      <img src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/images/loading.gif'?>" />
	    </span>
    <?php endif; ?>
  </div>
  <?php if ($this->formValues['viewType'] == 'list'): ?>
    <ul class='seaocore_browse_list'>
      <?php foreach ($this->paginator as $wishlist): ?>
        <li>
          <div class='seaocore_browse_list_photo'>
            <?php echo $this->htmlLink($wishlist->getHref(), $this->itemPhoto($wishlist->getCoverItem(), 'thumb.normal')) ?>
          </div>

          <div class="seaocore_browse_list_info">
            <div class="seaocore_browse_list_info_title">
            <div class="sr_wishlist_browse_list_buttons fright">
							<?php if ($this->viewer_id && !empty($this->followLike) && in_array('like', $this->followLike)): ?>
								<div class="fleft">
		            	<?php $check_availability = Engine_Api::_()->sitereview()->check_availability('sitereview_wishlist', $wishlist->getIdentity());      ?>
		            	<div class="seaocore_like_button" id="sitereview_unlikes_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo $check_availability ?"block":"none"?>' >
			              <a href="javascript:void(0);" onclick = "seaocore_content_type_likes('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');">
			              	<i class="seaocore_like_thumbdown_icon"></i>
			                <span><?php echo $this->translate('Unlike') ?></span>
			              </a>
		            	</div>
			            <div class="seaocore_like_button" id="sitereview_most_likes_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo empty($check_availability) ?"block":"none"?>'>
			              <a href="javascript:void(0);" onclick="seaocore_content_type_likes('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');">
			                <i class="seaocore_like_thumbup_icon"></i>
			                <span><?php echo $this->translate('Like') ?></span>
			              </a>
			            </div>
		            	<input type ="hidden" id = "sitereview_like_<?php echo $wishlist->getIdentity();?>" value = '<?php echo $check_availability ? $check_availability :0; ?>' />
		            </div>	
            	<?php endif; ?>

            	<?php if ($this->viewer_id && !empty($this->followLike) && in_array('follow', $this->followLike) && !$wishlist->isOwner($this->viewer)): ?>
            		<div class="fleft">
            			<?php $check_availability = $wishlist->follows()->isFollow($this->viewer);?>
            			<div class="seaocore_follow_button_active" id="sitereview_unfollows_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo $check_availability ?"block":"none"?>' >
              			<a class="seaocore_follow_button seaocore_follow_button_following" href="javascript:void(0);">
              				<i class="following"></i>
                			<span><?php echo $this->translate('Following') ?></span>
			              </a>
										
                      <a class="seaocore_follow_button seaocore_follow_button_unfollow" href="javascript:void(0);" onclick = "seaocore_content_type_follows('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');">
              				<i class="unfollow"></i>
                			<span><?php echo $this->translate('Unfollow') ?></span>
			              </a>
										
			            </div>
			            <div id="sitereview_most_follows_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo empty($check_availability) ?"block":"none"?>'>
			              <a class="seaocore_follow_button" href="javascript:void(0);"  onclick = "seaocore_content_type_follows('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');">
			              	<i class="follow"></i>
			                <span><?php echo $this->translate('Follow') ?></span>
			              </a>
			            </div>
            			<input type ="hidden" id = "sitereview_follow_<?php echo $wishlist->getIdentity();?>" value = '<?php echo $check_availability ? $check_availability :0; ?>' />
            		</div>	
          		<?php endif; ?>
          	</div>	
              <p><?php echo $this->htmlLink($wishlist->getHref(), $wishlist->title) ?></p>
            </div>
            <div class="seaocore_browse_list_info_date">
              <?php echo $this->translate('%s - created by %s', $this->timestamp($wishlist->creation_date), $wishlist->getOwner()->toString()) ?>
            </div>
            
            <?php if(!empty($this->statisticsWishlist)): ?>
              <div class='seaocore_sidebar_list_details'>
                <?php 
                  $statistics = '';
                  if(in_array('followCount', $this->statisticsWishlist)) {
                    $statistics .= $this->translate(array('%s follower', '%s followers', $wishlist->follow_count), $this->locale()->toNumber($wishlist->follow_count)).', ';
                  }

                  if(in_array('entryCount', $this->statisticsWishlist)) {
                    $statistics .= $this->translate(array('%s entry', '%s entries', $wishlist->total_item), $this->locale()->toNumber($wishlist->total_item)).', ';
                  }                            
                  
                  if(in_array('viewCount', $this->statisticsWishlist)) {
                    $statistics .= $this->translate(array('%s view', '%s views', $wishlist->view_count), $this->locale()->toNumber($wishlist->view_count)).', ';
                  }

                  if(in_array('likeCount', $this->statisticsWishlist)) {
                    $statistics .= $this->translate(array('%s like', '%s likes', $wishlist->like_count), $this->locale()->toNumber($wishlist->like_count)).', ';
                  }                 

                  $statistics = trim($statistics);
                  $statistics = rtrim($statistics, ',');

                ?>
                <?php echo $statistics; ?>
              </div>
            <?php endif; ?>             
            
            <?php if (!empty($wishlist->body)): ?>
              <div class='seaocore_browse_list_info_blurb'>
                <?php echo $wishlist->body; ?>
              </div>
            <?php endif; ?>

          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php elseif ($this->formValues['viewType'] == 'grid'): ?>
    <ul class='sr_wishlist_browse_grid'>
      <?php foreach ($this->paginator as $wishlist): ?>
        <li>
          <div>
            <div class="sr_wishlist_title">
              <?php echo $this->htmlLink($wishlist->getHref(), $wishlist->title) ?>
            </div>
            <div class="sr_wishlist_stats seaocore_txt_light bold mbot10">
              <?php echo $this->translate('%s Entries', $wishlist->total_item) ?>
            </div>
            <div class="sr_wishlist_contener b_medium">
              <div class="item_holder">
                <div class="item_cover">
                  <?php echo $this->itemPhoto($wishlist->getCoverItem(), 'thumb.profile'); ?>
                </div>
                <div class="item_thumbs">
                  <?php $lists = $wishlist->getWishlistMap(array('limit' => $this->listThumbsCount, 'orderby' => 'random'));
                  $count = $lists->getTotalItemCount(); ?>
                  <?php foreach ($lists as $sitereview): ?>
                    <?php echo $this->itemPhoto($sitereview, 'thumb.icon'); ?>
                  <?php endforeach; ?>
                  <?php for ($i = ($this->listThumbsCount - $count); $i > 0; $i--): ?>
                    <span class="empty"></span>
                  <?php endfor; ?>
                </div>
                <?php echo $this->htmlLink($wishlist->getHref(), '', array('class' => 'wishlistlink')); ?>
              </div>
              
              <?php if ($this->viewer_id && !empty($this->followLike) && in_array('like', $this->followLike)): ?>
              
                <?php $check_availability = Engine_Api::_()->sitereview()->check_availability('sitereview_wishlist', $wishlist->getIdentity());      ?>
                <div class="sr_btm_link" id="sitereview_unlikes_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo $check_availability ?"block":"none"?>' >
                  <span onclick="seaocore_content_type_likes('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');" class="seaocore_txt_light b_medium">
                    <?php echo $this->translate('Unlike') ?>
                  </span>
                </div>
                <div class="sr_btm_link" id="sitereview_most_likes_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo empty($check_availability) ?"block":"none"?>'>
                  <span onclick="seaocore_content_type_likes('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');" class="b_medium">
                    <?php echo $this->translate('Like') ?>
                  </span>
                </div>
                <input type ="hidden" id = "sitereview_like_<?php echo $wishlist->getIdentity();?>" value = '<?php echo $check_availability ? $check_availability :0; ?>' />
                <?php endif; ?>
                
                <?php if ($this->viewer_id && !empty($this->followLike) && in_array('follow', $this->followLike) && !$wishlist->isOwner($this->viewer)): ?>
                
                <?php $check_availability = $wishlist->follows()->isFollow($this->viewer);?>

                <div class="sr_btm_link" id="sitereview_unfollows_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo $check_availability ?"block":"none"?>' >
                  <span onclick = "seaocore_content_type_follows('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');" class="seaocore_txt_light b_medium">
                    <?php echo $this->translate('Unfollow') ?>
                  </span>
                </div>

                <div class="sr_btm_link" id="sitereview_most_follows_<?php echo $wishlist->getIdentity();?>" style ='display:<?php echo empty($check_availability) ?"block":"none"?>'>
                  <span onclick="seaocore_content_type_follows('<?php echo $wishlist->getIdentity(); ?>', 'sitereview_wishlist');" class="b_medium">
                    <?php echo $this->translate('Follow') ?>
                  </span>
                </div>

                <input type ="hidden" id = "sitereview_follow_<?php echo $wishlist->getIdentity();?>" value = '<?php echo $check_availability ? $check_availability :0; ?>' />                 
              <?php endif; ?>
          	</div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else:?>
<!--  <div class="tip">
    <span>
      <?php //echo $this->translate('No selected the any view for display wishlist.please contact to Site admin for this.') ?>
    </span>
  </div>-->
  <?php endif; ?>
  <div class="seaocore_pagination">
    <?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->formValues, 'pageAsQuery' => true)); ?>
  </div>
<?php elseif ($this->isSearched > 2): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Nobody has created a wishlist with that criteria. Be the first to %1$screate%2$s one!', '<a class="smoothbox" href="' . $this->url(array('action' => 'create'), "sitereview_wishlist_general") . '">', '</a>'); ?>
    </span>
  </div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Nobody has created a wishlist yet. Be the first to %1$screate%2$s one!', '<a class="smoothbox" href="' . $this->url(array('action' => 'create'), "sitereview_wishlist_general") . '">', '</a>'); ?>
    </span>
  </div>
<?php endif; ?>
