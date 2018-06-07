<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: buysell.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
//GET API KEY
$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");
?>

<script type="text/javascript">
 // SmoothboxSEAO.addScriptFiles.push("https://maps.googleapis.com/maps/api/js?libraries=places&key=<?php echo $apiKey ?>");
  en4.core.runonce.add(function () {
    new google.maps.places.Autocomplete(document.getElementById('place'));
  });
 
</script>


<div class="global_form_popup">
  <?php  echo $this->form->render($this); ?>
</div>	
<style type="text/css">
  .settings #file-label{
    width: 0;
  }
</style>
