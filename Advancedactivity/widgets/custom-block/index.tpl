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
<?php if( empty($this->renderOne) ): ?>
  <div id="get_custom_profile">
<?php endif; ?>
<?php foreach( $this->getCustomBlockObj as $getCustomSettings ): ?>
  <div class="adv_activity_welcome">
    <div class="adv_activity_welcome_num">
    </div>
    <div class="adv_activity_welcome_cont">	
      <?php	
	 $URL = $this->url(array('module' => 'advancedactivity', 'controller' => 'index', 'action' => 'custom-block-view', 'id' => $getCustomSettings->customblock_id), 'default', true);
	  $getTitle = $getCustomSettings->title;
	  $getDescription = $getCustomSettings->description;
// $getTitle = utf8_encode(html_entity_decode($getTitle));
// $getDescription = utf8_encode(html_entity_decode($getDescription));
?>
			<div class="adv_activity_welcome_cont_title">
			  <?php echo $this->translate($getTitle); ?>
			</div>

			<div class="adv_activity_welcome_cont_des">
			  <?php echo $this->translate($getDescription); // nl2br($getDescription); ?>
			</div>
    </div>
  </div>  
<?php endforeach; ?>
<?php if( empty($this->renderOne) ): ?>
  </div>
<?php endif; ?>