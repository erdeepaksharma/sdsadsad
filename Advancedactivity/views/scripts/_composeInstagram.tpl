<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _composeFacebook.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php 
  $instagram_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.apikey');
	$instagram_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.secretkey');
	$instagram_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.enable', 0);
  if(empty ($this->isAFFWIDGET) || empty($instagram_apikey) || empty($instagram_secret) || empty($instagram_enable)) {
    return;
  }


  // Add script
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_instagram.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.AdvInstagram({
      lang : {
        'Publish this on Instagram' : '<?php echo $this->translate('Publish this on Instagram') ?>',
        'Do not publish this on Instagram' : '<?php echo $this->translate('Do not publish this on Instagram') ?>'
      }
    }));
  });
</script>