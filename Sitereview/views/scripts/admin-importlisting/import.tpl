<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: import.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
	function showLightbox() 
	{	
		$('import_form1').innerHTML = "<div><center><b class='bold'>" + '<?php echo $this->string()->escapeJavascript($this->translate("Importing file content...")) ?>' + "</b></center><center class='mtop10'><img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/loader.gif' alt='<?php echo $this->string()->escapeJavascript($this->translate("Importing file content...")) ?>' /></center></div>";
		$('import_form').style.display = 'none';
	}
</script>

<div id='import_form1' class="sitereview_upload_csv_popup_loader"></div>

<div class='clear global_form_popup sitereview_upload_csv_popup'>
  <div class='settings' id="import_form">
    <?php echo $this->form->render($this); ?>
  </div>
</div>