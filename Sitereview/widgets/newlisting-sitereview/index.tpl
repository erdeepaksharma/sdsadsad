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
   <?php if (Engine_Api::_()->sitereview()->hasPackageEnable()):?>
    <a href='<?php echo $this->url(array('action' => 'index'), "sitereview_package_listtype_$this->listingtype_id", true) ?>' class="buttonlink seaocore_icon_add icon_sitereview_add_listtype_<?php echo $this->listingtype_id?>" ><?php echo $this->translate("Post a New ". $this->title); ?></a>
   <?php else:?>
			<a href='<?php echo $this->url(array('action' => 'create'), "sitereview_general_listtype_$this->listingtype_id", true) ?>' class="buttonlink seaocore_icon_add icon_sitereview_add_listtype_<?php echo $this->listingtype_id?>" ><?php echo $this->translate("Post a New ". $this->title); ?></a>
   <?php endif;?>
		</li>
	</ul>
</div>
