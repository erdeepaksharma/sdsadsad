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

<?php 
	$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<script type="text/javascript" >

function owner(thisobj) {
	var Obj_Url = thisobj.href  ;

	Smoothbox.open(Obj_Url);
}
</script>

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

    $mouseOverTitle = 'Add to Favourites';   

    if($this->classLink == 'sr_wishlist_link') {
        $this->classLink = 'sr_favourite_link';
    }      
      
} else {
    $mouseOverTitle = 'Add to Wishlist';
}

?>
<?php if($viewer_id): ?>
    <?php if($favouriteSetting): ?>    
        <?php $wishlist_id = Engine_Api::_()->getDbTable('wishlists', 'sitereview')->recentWishlistId($viewer_id); ?>
        <?php if(Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->isItemAdded($this->item->listing_id, $viewer_id, $wishlist_id)): ?>
            <?php $showRemoveStyle = 'display:inline-block;'; ?>
            <?php $showAddStyle = 'display:none;'; ?>
        <?php else: ?>
            <?php $showRemoveStyle = 'display:none;'; ?>
            <?php $showAddStyle = 'display:inline-block;'; ?>                    
        <?php endif; ?>
        
        <?php $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id);?>
        <div id="add_favourite_smoothbox_content" style="display:none;"><img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/tick.png" />&nbsp;<b><?php echo $this->translate("This entry has been added successfully to your %s.", $this->htmlLink($wishlist->getHref(), $this->translate('favourites'), array('target' => '_blank'))); ?></b><br/><br/>
        <button onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate("Close");?></button></div>
        
        <a style="<?php echo $showAddStyle; ?>"href="javascript:void(0)" onclick="addToFavourite(<?php echo $this->item->listing_id; ?>, 'add')" title="<?php echo $this->translate($mouseOverTitle); ?>" class="<?php echo $this->classIcon; ?> <?php echo $this->classLink; ?> add_sitereview_favourite_link_<?php echo $this->item->listing_id ?>"><?php echo $this->translate($this->text);?></a>
        
        <img class="favourite_loading_image_<?php echo $this->item->listing_id ?>" src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/loading.gif" style="display:none;" />   
       
    <?php else: ?>
        <?php echo $this->htmlLink(array('route' => "sitereview_wishlist_general", 'action' => 'add', 'listing_id' => $this->item->listing_id), $this->translate($this->text), array('class' => "$this->classIcon $this->classLink", 'title' => $this->translate($mouseOverTitle), 'onclick' => 'owner(this);return false')) ?>   
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
  echo $this->htmlLink($addUrl, $this->translate($this->text), array('class' => "$this->classIcon $this->classLink", 'title' => $this->translate($mouseOverTitle))); ?>
<?php endif;?>
        
<script type="text/javascript">
    function addToFavourite(listing_id, perform) {
        var request = new Request.JSON({
            url: '<?php echo $this->url(array('action' => 'add'), "sitereview_wishlist_general"); ?>',
            method: 'post',
            data: {
                format: 'json',
                listing_id: listing_id,
                perform: perform,
            },
            //responseTree, responseElements, responseHTML, responseJavaScript
            onSuccess: function(responseJSON) {
               showHideFavouriteLink(listing_id, perform, 0);
               if(perform == 'add') { 
                Smoothbox.open($('add_favourite_smoothbox_content').innerHTML);
               }
               else {
                Smoothbox.open($('remove_favourite_smoothbox_content').innerHTML);   
               }
            }
        });
        
        showHideFavouriteLink(listing_id, perform, 1);
        request.send();
    }
    
    function showHideFavouriteLink(listing_id, perform, onRequest) {
        
        $$('.favourite_loading_image_'+listing_id).each(function(element){
            if(onRequest == 0) {
                element.style.display = 'none';
            }
            else {
                element.style.display = 'inline-block';
            }

        });             
        
        $$('.add_sitereview_favourite_link_'+listing_id).each(function(element){
            
            if(onRequest == 1) {
                element.style.display = 'none';
            }
            else {            
                if(perform == 'add') {
                    element.style.display = 'none';
                }
                else {
                    element.style.display = 'inline-block';
                }
            }

        });         
                    
    }    
</script>    