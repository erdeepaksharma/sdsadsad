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

<div>
	<?php echo $this->show_fields ?>
 	<?php if(empty($this->review) && $this->contentDetails->content_id && $this->show_specificationlink): ?>
		<div class="sr_more_link">
	  	<a href="javascript:void(0);" onclick='showInfoTab();return false;'>   <?php echo $this->translate($this->show_specificationtext) . ' &raquo;'; ?></a>
	  </div>
	<?php elseif(!empty($this->review)): ?>
		<div class="sr_more_link">
	  	<a href="<?php echo $this->sitereview->getHref(array('profile_link' => 0)). '/tab/' . $this->tab_id?>"><?php echo $this->translate($this->show_specificationtext) . ' &raquo;'; ?></a>
	  </div>
  <?php endif;?>
</div>