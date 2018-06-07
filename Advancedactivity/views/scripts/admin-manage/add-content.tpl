<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: add-content.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php include_once APPLICATION_PATH .
'/application/modules/Advancedactivity/views/scripts/admin-manage/navigation.tpl'; ?>
<div class="seaocore_settings_form">
	<div class='settings'>
	  <?php echo $this->form->render($this); ?>
	</div>
</div>	
<script type="text/javascript">
  function setModuleName(module_name){
    $('filter_type').value=module_name;
  }
</script>

