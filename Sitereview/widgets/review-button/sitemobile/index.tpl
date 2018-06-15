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

<?php if($this->createAllow == 1):?>
  <a href="<?php echo $this->url(array('action' => 'create', 'listing_id' => $this->listing_id, 'tab' => $this->tab), "sitereview_user_general_listtype_$this->listingtype_id", true);?>" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true'>
    <i class="ui-icon-pencil"></i>
		<span><?php echo $this->translate('Write a Review') ?></span>
	</a>
<?php elseif($this->createAllow == 2):?>
	<a href="<?php echo $this->url(array('action' => 'update', 'listing_id' => $this->listing_id, 'review_id' => $this->review_id, 'tab' => $this->tab), "sitereview_user_general_listtype_$this->listingtype_id", true);?>" data-role='button' data-inset='false' data-mini='true' data-corners='false' data-shadow='true'>
    <i class="ui-icon-pencil"></i>
		<span><?php 
    if(Engine_Api::_()->sitemobile()->isApp()):
      echo $this->translate('Update Review'); 
    else :
      echo $this->translate('Update your Review');
    endif;?></span>
	</a>
<?php endif;?>


