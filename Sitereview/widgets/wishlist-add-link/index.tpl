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

<div class="quicklinks">
	<ul class="navigation">
		<li> 
      <?php echo $this->addToWishlist($this->sitereview, array('classIcon' => 'sr_wishlist_href_link','text' => 'Add to Wishlist', 'classLink' => ''));?>
    </li>
	</ul>
</div>