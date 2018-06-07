<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _composeSocialengine.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php $active_icon = Engine_Api::_()->getApi('settings',
'core')->getSetting('advancedactivity_icon1',
				'application/modules/Advancedactivity/externals/images/socialengine_active.png');
	$activeIcon = $this->baseUrl() . '/' . $active_icon;


$inactive_icon = Engine_Api::_()->getApi('settings',
'core')->getSetting('advancedactivity_icon2',
				'application/modules/Advancedactivity/externals/images/socialengine_inactive.png');
	$inactiveIcon = $this->baseUrl() . '/' . $inactive_icon;
	?>
<?php 
//CHECK IF BOTH FACEBOOK AND TWITTER IS DISABLED.
$web_values = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.fb.twitter', array("0" => "facebook", "1" => "twitter"));
if ((empty($web_values)))
  return;

  // Add script
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_socialengine.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.Socialengine({
      lang : {
        'Publish this on Socialengine' : '<?php echo $this->string()->escapeJavascript($this->translate('Publish this on ' . Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.sitetabtitle'))); ?>'

      }
    }));
  });
</script>
<style type="text/css">
.composer_socialengine_toggle {
background-position:right !important;
padding-right:15px;

}

.composer_socialengine_toggle
{
  background-image: url(<?php echo $this->layout()->staticBaseUrl . Engine_Api::_()->getApi('settings',
'core')->getSetting('advancedactivity.icon2', $inactiveIcon);?>);
}

.composer_socialengine_toggle:hover
{
  background-image: url(<?php echo $this->layout()->staticBaseUrl . Engine_Api::_()->getApi('settings',
'core')->getSetting('advancedactivity.icon1', $activeIcon);?>);
  cursor: pointer;
}

.composer_socialengine_toggle_active
{
  background-image: url(<?php echo $this->layout()->staticBaseUrl . Engine_Api::_()->getApi('settings',
'core')->getSetting('advancedactivity.icon1', $activeIcon);?>);
}
</style>
