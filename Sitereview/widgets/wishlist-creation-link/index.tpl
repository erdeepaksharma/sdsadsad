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
      <?php if($this->favourite && $this->wishlist_id): ?>  
        <?php $wishlist = Engine_Api::_()->getItem('sitereview_wishlist', $this->wishlist_id);?>
        <?php echo $this->htmlLink($wishlist->getHref(), $this->translate('My Favourites'), array('class' => 'buttonlink sr_icon_wishlist_add')) ?>
      <?php else: ?>  
        <?php echo $this->htmlLink(array('route' => 'sitereview_wishlist_general', 'action' => 'create'), $this->translate('Create New Wishlist'), array('class' => 'smoothbox buttonlink sr_icon_wishlist_add')) ?>
      <?php endif; ?>  
    </li>
		</li>
	</ul>
</div>