<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: pulldown.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php foreach( $this->notifications as $notification ): ?>
	<li <?php if( !$notification->read ): ?>class="notifications_unread clr"<?php endif; ?> value="<?php echo $notification->getIdentity();?>" style="overflow: hidden;">
	<span class="notification_item_general aaf_update_pulldown">
		<?php $item = $notification->getSubject() ?>
      <?php if($item):?>
	  <?php echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.icon', $item->getTitle())) ?>
	  <?php endif; ?>
	  <span class="aaf_update_pulldown_content">
	    <span class="aaf_update_pulldown_content_title fleft"><?php echo $notification->__toString() ?></span>
	    <span class="aaf_update_pulldown_content_stat notification_type_<?php echo $notification->type ?>"> 
	      <?php echo $this->timestamp(strtotime($notification->date)) ?>
	    </span>
	  </span>
	</span>
	</li>
<?php endforeach; ?>