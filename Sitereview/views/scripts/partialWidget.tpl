<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: partialWidget.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<li> 
	<?php
	echo $this->htmlLink(
					$this->sitereview_video->getHref(), $this->itemPhoto($this->sitereview_video, 'thumb.icon', $this->sitereview_video->getTitle()), array('class' => 'list_thumb', 'title' => $this->sitereview_video->getTitle())
	)
	?>
	<div class='seaocore_sidebar_list_info'>
		<div class='seaocore_sidebar_list_title'>
			<?php echo $this->htmlLink($this->sitereview_video->getHref(), Engine_Api::_()->seaocore()->seaocoreTruncateText($this->sitereview_video->getTitle(), 16), array('title' => $this->sitereview_video->getTitle(),'class'=>'sitereview_video_title')); ?> 	
		</div>
		<div class='seaocore_sidebar_list_details'>