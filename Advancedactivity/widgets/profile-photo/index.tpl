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

    $this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/scripts/core.js');
?>
	<div class="adv_activity_welcome">
		<div class="adv_activity_welcome_num">
		</div>
		<div class="adv_activity_welcome_cont">
			<div class="adv_activity_welcome_cont_title">
				<?php echo $this->translate("Upload a profile picture"); ?>
			</div>
			<div class="adv_activity_welcome_upphoto clr">
				<?php $viewer = Engine_Api::_()->user()->getViewer(); ?>
			  <div id="main-preview-image" class="adv_activity_welcome_upphoto_thumb">
			    <?php echo $this->itemPhoto($viewer, 'thumb.profile', "", array('id' => 'user_preview')); ?>
			  </div>
			  <?php $URL = $this->baseURL . '/advancedactivity/index/uploadimage'; ?>
        <?php $URL_webcam = $this->baseURL . '/advancedactivity/index/webcamimage?webcam_type=profile_photo'; ?>
				<div class="adv_activity_welcome_upphoto_des">
	  			<div><h3><a href="javascript: void(0);" onclick="uploadImage('<?php echo $URL; ?>');"> <?php echo $this->translate("Upload a Photo") ?> </a></h3></div>
	  			<div><?php echo $this->translate("From your computer"); ?></div>                   
		<div class="adv_activity_welcome_upphoto_sep"><?php echo $this->translate("OR") ?></div>
	  			<div><h3><a href="javascript: void(0);" onclick="uploadImage('<?php echo $URL_webcam; ?>');"> <?php echo $this->translate("Take a Photo") ?> </a></h3></div>
	  			<div><?php echo $this->translate("With your webcam"); ?></div>
	  		</div>
	  	</div>
	  </div>
	</div>