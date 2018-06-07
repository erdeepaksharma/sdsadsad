<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _composeTwitter.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php 
  $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
  $twitter_apikey = $settings['key'];
	$twitter_secret = $settings['secret'];

//  // Disabled
  if((!Engine_Api::_()->getApi('settings', 'core')->getSetting('twitter.enable', Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable == 'publish'? 1:0) || empty ($this->isAFFWIDGET) || empty($twitter_apikey) || empty($twitter_secret))){
    return;
  }
  if (!function_exists('mb_strlen')) {
    return;
  }

  // Add script
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_twitter.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.AdvTwitter({
      lang : {
        'Publish this on Twitter' : '<?php echo $this->translate('Publish this on Twitter') ?>',
        'Do not publish this on Twitter' : '<?php echo $this->translate('Do not publish this on Twitter') ?>'
      }
    }));
  });
</script>

<style type="text/css">
.composer_twitter_toggle {
background-position:right !important;
padding-right:15px;

}
</style>
