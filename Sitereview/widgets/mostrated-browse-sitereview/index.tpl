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
			<a href='<?php echo $this->url(array('action' => 'top-rated'), "sitereview_general_listtype_$this->listingtype_id", true) ?>' class="buttonlink sr_icon_star" ><?php echo $this->translate("Browse Top Rated ". $this->title); ?></a> 
		</li>
	</ul>
</div>
