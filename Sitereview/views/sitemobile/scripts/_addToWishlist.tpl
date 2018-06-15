<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _addToWishlist.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity(); ?>
<?php
$favouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);

if($favouriteSetting) {
      if($this->text == 'Wishlist' || $this->text == 'wishlist') {
          $this->text = 'Favourite';
      }
      elseif($this->text == 'Add to Wishlist' || $this->text == 'Add To Wishlist') {
          $this->text = 'Add to Favourites';
      }
      else {
      }
      $mouseOverTitle = 'Add to Favourites';
 
        if($this->classLink == 'sr_wishlist_link') {
            $this->classLink = 'sr_favourite_link';
        }      
      
} else { 
    if(Engine_Api::_()->sitemobile()->isApp()) {
      $mouseOverTitle = 'Wishlist';
      if($this->text == 'Add to Wishlist')
        $this->text = 'Wishlist';
    } else
       $mouseOverTitle = 'Add to Wishlist';
}

?>
<?php if($viewer_id): ?>
    <?php if($favouriteSetting): ?>      
        <?php $wishlist_id = Engine_Api::_()->getDbTable('wishlists', 'sitereview')->recentWishlistId($viewer_id); ?>
        <?php if(Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->isItemAdded($this->item->listing_id, $viewer_id, $wishlist_id)): ?>
            <?php $showRemoveStyle = 'display:block;'; ?>
            <?php $showAddStyle = 'display:none;'; ?>
        <?php else: ?>
            <?php $showRemoveStyle = 'display:none;'; ?>
            <?php $showAddStyle = 'display:block;'; ?>                    
        <?php endif; ?>        
        <?php $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);?>

<!--This is a tip to show success meassage of favourite list-->
      <div data-role="popup" id="popupBasic" data-position-to="window" class="ui-content" data-overlay-theme="a" class="ui-corner-all">
        <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/tick.png" />&nbsp;<b><?php echo $this->translate("This entry has been added successfully to your %s.", $this->htmlLink($wishlist->getHref(), $this->translate('favourites'), array('target' => '_blank'))); ?></b>  
        <a href="#" data-rel="back" data-role="button" data-mini = "true">
         <?php echo $this->translate('Close')  ?>
        </a>
      </div>
<!--End success tip-->

<a href="#popupBasic" data-rel="popup" data-position-to="window" data-transition="pop" data-role='button' data-icon='plus' data-inset='false' data-mini='true' data-corners='false' data-shadow='true' style="<?php echo $showAddStyle; ?>"href="javascript:void(0)" onclick="addToFavourite(<?php echo $this->item->listing_id; ?>, 'add')" title="<?php echo $this->translate($mouseOverTitle); ?>" class="<?php echo $this->classIcon; ?> <?php echo $this->classLink; ?> add_sitereview_favourite_link_<?php echo $this->item->listing_id ?>"><?php echo $this->translate($this->text);?></a>
       
    <?php else: ?>

      <a href="<?php echo $this->url(array('action' => 'add', 'listing_id' => $this->item->listing_id, 'tab' => $this->tab), "sitereview_wishlist_general", true);?>" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true'>
        <i class="ui-icon-plus"></i>
        <span><?php echo $this->translate($this->text) ?></span>
      </a>
        <?php // echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'add', 'listing_id' => $this->item->listing_id), $this->translate($this->text), array("class"=>"smoothbox","data-role"=>"button" ,"data-icon"=>"plus" ,"data-inset"=>"false", "data-mini"=>"true", "data-corners"=>"false" ,"data-shadow"=>"true" , 'title' => $this->translate($mouseOverTitle))) ?>   
    <?php endif; ?>
<?php else: ?>
  <?php 
    $urlO = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    $request_url = explode('/',$urlO);
    empty($request_url['2']) ? $param = 2 : $param = 1;
    $return_url = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://":"http://";
    $currentUrl = urlencode($urlO);
  ?> 
  <?php 
  
  $addUrl = $this->url(array('action' => 'add', 'listing_id' => $this->item->listing_id, 'param' => $param,'request_url' => $request_url['1']), "sitereview_wishlist_general")."?"."return_url=".$return_url.$_SERVER['HTTP_HOST'].$currentUrl;
  echo $this->htmlLink($addUrl, $this->translate($this->text), array("data-role"=>"button" ,"data-icon"=>"plus" ,"data-inset"=>"false", "data-mini"=>"true", "data-corners"=>"false" ,"data-shadow"=>"true" , 'title' => $this->translate($mouseOverTitle))); ?>
<?php endif;?>
        
<script type="text/javascript">
    function addToFavourite(listing_id, perform) {
         $.ajax({
            url: '<?php echo $this->url(array('action' => 'add'), "sitereview_wishlist_general"); ?>',
            method: 'post',
            data: {
                format: 'json',
                listing_id: listing_id,
                perform: perform,
            },
            success: function(responseJSON) {
               showHideFavouriteLink(listing_id, perform, 0);
            }
        });
        
        showHideFavouriteLink(listing_id, perform, 1);
    }
    
    function showHideFavouriteLink(listing_id, perform, onRequest) {
            
            if(onRequest == 1) {
                $('.add_sitereview_favourite_link_'+listing_id).css('display','none');
            }
            else {            
                if(perform == 'add') {
                    $('.add_sitereview_favourite_link_'+listing_id).css('display','none');
                }
                else {
                    $('.add_sitereview_favourite_link_'+listing_id).css('display','block');
                }
            }       
                    
    }    
</script>    
