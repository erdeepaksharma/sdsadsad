<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
	$this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<?php if( empty($this->isWelcomePageCorrect) && ($this->viewer->level_id == 1) ): ?>
<div class="tip">
  <span><?php echo $this->translate("You will be able to vertically adjust the position of widgets on Welcome Tab page from the Layout Editor."); ?></span>
</div>
<?php 
  endif; 
  $msgStr = $this->translate("_WELCOME_MESSAGE_STRING");
  $msgStr = str_replace("[COMMUNITY_NAME]", $this->title, $msgStr);
  $msgStr = str_replace("[VIEWER_TITLE]", $this->viewer->getTitle(), $msgStr);
?>

<div class="adv_activity_welcome_head">
  <?php echo $msgStr; ?>
</div>