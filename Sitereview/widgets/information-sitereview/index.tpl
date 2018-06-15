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

<ul class="sr_sidebar_list_info sr_side_widget">
	<li>
		<?php if(in_array('ownerPhoto', $this->showContent)):?>
		<?php echo $this->htmlLink($this->sitereview->getParent(), $this->itemPhoto($this->sitereview->getParent(), 'thumb.icon', '' , array('align' => 'center')), array('class'=> 'sr_sidebar_list_info_photo fleft')) ?>
		<?php endif ;?>
    <?php if(in_array('ownerName', $this->showContent)):?>
    	<div class="o_hidden">
      	<?php echo $this->htmlLink($this->sitereview->getParent(), $this->sitereview->getParent()->getTitle()) ?><br /><?php echo $this->translate("(Owner)"); ?>
      </div>
    <?php endif ;?>  
	</li>
      
  <?php if (in_array('tags', $this->showContent) && count($this->sitereviewTags) > 0): $tagCount = 0; ?>
    <li>
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
    </li>
  <?php endif; ?>
    
	<li>
		<ul>
      <?php if (in_array('modifiedDate', $this->showContent)):?>
        <li>
          <?php echo $this->translate('Last updated %s', $this->timestamp($this->sitereview->modified_date)) ?>
        </li>        
      <?php endif; ?>
        
      <?php 

        $statistics = '';

        if(in_array('commentCount', $this->showContent)) {
          $statistics .= $this->translate(array('%s comment', '%s comments', $this->sitereview->comment_count), $this->locale()->toNumber($this->sitereview->comment_count)).', ';
        }

        if(in_array('viewCount', $this->showContent)) {
          $statistics .= $this->translate(array('%s view', '%s views', $this->sitereview->view_count), $this->locale()->toNumber($this->sitereview->view_count)).', ';
        }

        if(in_array('likeCount', $this->showContent)) {
          $statistics .= $this->translate(array('%s like', '%s likes', $this->sitereview->like_count), $this->locale()->toNumber($this->sitereview->like_count)).', ';
        }                 

        $statistics = trim($statistics);
        $statistics = rtrim($statistics, ',');

      ?>

      <?php echo $statistics; ?>
        
		</ul>
	</li>
  
  <?php if ($this->sitereview->price > 0 && $this->listingType->price && in_array('price', $this->showContent)): ?>
    <li>
    	<b>
      	<?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->sitereview->price);  ?>
      </b>	
    </li>   
  <?php endif; ?>  
  
  <?php if (in_array('location', $this->showContent) && !empty($this->sitereview->location) && $this->listingType->location): ?>
    <li>
      <?php echo $this->translate($this->sitereview->location); ?>&nbsp;-
      <b>
        <?php echo $this->htmlLink(array('route' => 'seaocore_viewmap', 'id' => $this->sitereview->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')); ?>
      </b>
    </li>
  <?php endif; ?> 
    
  <li>  
    <?php if(in_array('compare', $this->showContent) || in_array('addtowishlist', $this->showContent)): ?>  
      <div class="clr mtop5">

				<?php if(in_array('compare', $this->showContent)) :?>
					<?php echo $this->compareButton($this->sitereview); ?> 
				<?php endif;?>

				<?php if (Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id)->wishlist && in_array('addtowishlist', $this->showContent)): ?>
					<?php echo $this->addToWishlist($this->sitereview, array('classIcon' => 'icon_wishlist_add', 'classLink' => 'sr_wishlist_link', 'text' => ''));?>  
				<?php endif;?>

      </div>
    <?php endif; ?>

  
  </li>   
</ul>