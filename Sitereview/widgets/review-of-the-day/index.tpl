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

<ul class="seaocore_sidebar_list">
  <li>
    <?php echo $this->htmlLink($this->review->getOwner($this->review->type), $this->itemPhoto($this->review->getOwner(), 'thumb.icon')) ?>
    <div class='seaocore_sidebar_list_info'>
    	<div class="seaocore_sidebar_list_title">
      	<?php echo $this->htmlLink($this->review->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($this->review->getTitle(),20), array('title' => $this->review->getTitle())) ?>
      </div>	
      <div class="seaocore_sidebar_list_details">
      	<?php echo $this->translate(" on "); ?>
      	<?php echo $this->htmlLink($this->review->getParent()->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($this->review->getParent()->getTitle(), 20), array('title' => $this->review->getParent()->getTitle())) ?>
      </div>
	    <div class='seaocore_sidebar_list_details'>  
	      <?php echo $this->showRatingStar($this->overallRating, $this->review->type, 'small-star', $this->review->getParent()->listingtype_id); ?>
	    </div>	
    </div>  

    
		<div class="clr sr_review_quotes">
			<b class="c-l fleft"></b>
			<?php echo Engine_Api::_()->seaocore()->seaocoreTruncateText($this->review->getDescription(), 100) ?>
			<b class="c-r fright"></b>
		</div>    
  </li>
  
  <li class="sitereview_sidebar_list_seeall">
    <?php echo $this->htmlLink($this->review->getHref(), $this->translate('More &raquo;'), array('class' => 'more_link'));?>
  </li>
  
</ul>
