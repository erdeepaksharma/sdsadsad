<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: uploadimage.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<script type="text/javascript">

  window.addEvent('domready', function() {

  '<?php if( !empty($this->smoothboxClose) ): ?>'
    window.parent.document.getElementById('user_preview').src = '<?php echo $this->viewer->getPhotoUrl(); ?>';
    parent.Smoothbox.close();
  '<?php else: ?>'

     viewDisable();
    
   '<?php endif; ?>'

  });
  
  function viewDisable() {
	document.getElementById('main-preview-image').style.display = 'none';
	document.getElementById('preview-thumbnail').style.display = 'none';
	document.getElementById('thumbnail-controller').style.display = 'none';
	document.getElementById('current-label').style.display = 'none';
  }

  function viewEnable() {
	document.getElementById('main-preview-image').style.display = 'block';
	document.getElementById('preview-thumbnail').style.display = 'block';
	document.getElementById('thumbnail-controller').style.display = 'block';
	document.getElementById('current-label').style.display = 'block';
  }


</script>
<div class="global_form_popup">
	<?php
	if( empty($this->smoothboxClose) ) {
	  $this->headScript()
			  ->appendFile($this->layout()->staticBaseUrl . 'externals/moolasso/Lasso.js')
			  ->appendFile($this->layout()->staticBaseUrl . 'externals/moolasso/Lasso.Crop.js');
	
	  echo $this->form->render($this);
	}else {
	  echo $this->translate("Images has been set successfully.");
	}
	?>
</div>	