<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: custom-block-create.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<h2>
  <?php echo $this->translate('Advanced Activity Feeds / Wall Plugin') ?>
</h2>
<?php ?>

<?php if( count($this->navigation) ): ?>
<div class='seaocore_admin_tabs'>
  <?php
    // Render the menu
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
  ?>
</div>
<?php endif; ?>

<p>
	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' =>
'settings', 'action' => 'manage-custom-block'), $this->translate("Back to Custom Blocks for Welcome Tab"),
array('class'=>'seaocore_icon_back buttonlink')) ?>
</p>
<br />

<div class='seaocore_settings_form'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<style type="text/css">
.defaultSkin iframe
{
	width:600px !important;
	height:250px !important;
}
</style>


<script type="text/javascript">

window.addEvent('domready', function() {
  getlimitation($('temp_limitation').value)
 });

function validateTitleOnClick() {
  $('limitation_value').value = '';
}

function getlimitation(getId){
  if(getId == 1) {
// 	if( ($('temp_limitation').value == getId) && ($('temp_limitation_value').value != 0) ) {
// 	  $('flag').value = $('limitation_value').value = $('temp_limitation_value').value;
// 	}else {
// 	  $('flag').value = $('limitation_value').value = 'Number of friends';
// 	}
nofriend = '<?php echo $this->string()->escapeJavascript($this->translate("Number of friends")) ?>';
$('limitation_value-wrapper').style.display = 'block';
$('limitation_value-label').innerHTML = '<label class="optional" for="limitation_value">'+ nofriend
+'</label>';
<?php if( empty($this->customblock_id) ): ?>
  $('limitation_value').value = 5;
<?php endif; ?>
  }else if( getId == 2 ) {
// 	if( ($('temp_limitation').value == getId) && ($('temp_limitation_value').value != 0) ) {
// 	  $('flag').value = $('limitation_value').value = $('temp_limitation_value').value;
// 	}else {
// 	  $('flag').value = $('limitation_value').value = 'Number of days since signup.';
// 	}
noday = '<?php echo $this->string()->escapeJavascript($this->translate("Number of days since signup")) ?>';
$('limitation_value-wrapper').style.display = 'block';
$('limitation_value-label').innerHTML = '<label class="optional" for="limitation_value">' + noday +'</label>';
<?php if( empty($this->customblock_id) ): ?>
  $('limitation_value').value = 30;
<?php endif; ?>
  }else {
	$('flag').value = $('limitation_value').value = '';
	$('limitation_value-wrapper').style.display = 'none';
  }
}
</script>