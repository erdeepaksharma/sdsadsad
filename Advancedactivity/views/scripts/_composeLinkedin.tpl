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
  $linkedin_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey');
	$linkedin_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey');
	$linkedin_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.enable', 0);
  if(empty ($this->isAFFWIDGET) || empty($linkedin_apikey) || empty($linkedin_secret) || empty($linkedin_enable)) {
    return;
  }


  // Add script
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_linkedin.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.AdvLinkedin({
      lang : {
        'Publish this on LinkedIn' : '<?php echo $this->translate('Publish this on LinkedIn') ?>',
        'Do not publish this on LinkedIn' : '<?php echo $this->translate('Do not publish this on LinkedIn') ?>'
      }
    }));
  });
</script>