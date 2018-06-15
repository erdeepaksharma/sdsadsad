<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _listingNonPhoto.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<span class='feed_attachment_<?php echo $this->item->getType() ?>'>
	<div>
		<?php $attribs = Array();?>
		<div>
			<div class='feed_item_link_title'>
				<?php
					echo $this->htmlLink($this->item->getHref(), $this->item->getTitle() ? $this->item->getTitle() : '', $attribs);
				?>
			</div>
			<div class='feed_item_link_desc'>
				<?php echo $this->viewMore($this->item->getDescription()) ?>
			</div>
		</div>
	</div>
</span>